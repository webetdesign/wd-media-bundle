<?php
namespace WebEtDesign\MediaBundle\Tests;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\ListenersInvoker;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Listener\MediaListener;

final class MediaLifecycleCompatibilityTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ClassMetadata $metadata;
    private ListenersInvoker $invoker;
    private ?string $fixturePath = null;

    protected function setUp(): void
    {
        $source = dirname(__DIR__).'/src';
        self::assertSame($source.'/Entity/Media.php', (new \ReflectionClass(Media::class))->getFileName());
        self::assertSame($source.'/Listener/MediaListener.php', (new \ReflectionClass(MediaListener::class))->getFileName());

        $this->metadata = new ClassMetadata(Media::class);
        $this->metadata->initializeReflection(new \Doctrine\Persistence\Mapping\RuntimeReflectionService());
        (new AttributeDriver([$source.'/Entity']))->loadMetadataForClass(Media::class, $this->metadata);
        $configuration = new Configuration();
        $configuration->getEntityListenerResolver()->register(new MediaListener(new ParameterBag([
            'wd_media.categories' => ['documents' => ['label' => 'Documents']],
        ])));
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method('getConfiguration')->willReturn($configuration);
        $this->entityManager->method('getEventManager')->willReturn(new EventManager());
        $this->entityManager->method('getClassMetadata')->with(Media::class)->willReturn($this->metadata);
        foreach (['getConnection', 'persist', 'remove', 'flush'] as $method) {
            $this->entityManager->expects(self::never())->method($method);
        }
        $this->invoker = new ListenersInvoker($this->entityManager);
    }

    protected function tearDown(): void
    {
        if ($this->fixturePath !== null && is_file($this->fixturePath)) {
            unlink($this->fixturePath);
        }
    }

    public function testPrePersistDerivesMediaPropertiesFromUploadedFile(): void
    {
        $media = $this->uploadedMedia();
        $event = new PrePersistEventArgs($media, $this->entityManager);
        $this->invoke(Events::prePersist, $media, $event);
        $this->assertUploadedMedia($media);
    }

    public function testPreUpdateDerivesMediaPropertiesWithoutChangingCollections(): void
    {
        $media = $this->uploadedMedia();
        $relatedMedia = new Media();
        $collection = new \Doctrine\ORM\PersistentCollection(
            $this->entityManager,
            $this->metadata,
            new \Doctrine\Common\Collections\ArrayCollection([$relatedMedia]),
        );
        $collection->takeSnapshot();
        $changeSet = ['label' => ['Previous label', 'client-document.txt'], 'relatedMedia' => $collection];
        $expectedChangeSet = $changeSet;
        $event = new \Doctrine\ORM\Event\PreUpdateEventArgs($media, $this->entityManager, $changeSet);
        $this->invoke(Events::preUpdate, $media, $event);
        $this->assertUploadedMedia($media);
        self::assertSame($expectedChangeSet, $event->getEntityChangeSet());
        self::assertSame([$relatedMedia], $collection->toArray());
        self::assertSame([$relatedMedia], $collection->getSnapshot());
        self::assertFalse($collection->isDirty());
        self::assertSame('', $relatedMedia->getLabel());
    }

    public function testPrePersistPreservesMediaPropertiesWithoutAFile(): void
    {
        $media = $this->storedMedia();
        $this->invoke(Events::prePersist, $media, new PrePersistEventArgs($media, $this->entityManager));
        $this->assertStoredMedia($media);
    }

    public function testPreUpdatePreservesMediaPropertiesWithoutAFile(): void
    {
        $media = $this->storedMedia();
        $changeSet = ['description' => [null, 'Updated description']];
        $expectedChangeSet = $changeSet;
        $event = new \Doctrine\ORM\Event\PreUpdateEventArgs($media, $this->entityManager, $changeSet);
        $this->invoke(Events::preUpdate, $media, $event);
        $this->assertStoredMedia($media);
        self::assertSame($expectedChangeSet, $event->getEntityChangeSet());
    }

    public function testPostLoadResolvesConfiguredCategoryLabel(): void
    {
        $media = $this->storedMedia();
        $this->invoke(Events::postLoad, $media, new \Doctrine\ORM\Event\PostLoadEventArgs($media, $this->entityManager));
        self::assertSame('Documents', $media->getCategoryLabel());
        self::assertSame('documents', $media->getCategory());
        self::assertSame('Stored label', $media->getLabel());
    }

    public function testPostLoadMarksDeletedCategoryWithoutChangingItsKey(): void
    {
        $media = $this->storedMedia()->setCategory('removed');
        $this->invoke(Events::postLoad, $media, new \Doctrine\ORM\Event\PostLoadEventArgs($media, $this->entityManager));
        self::assertSame('removed (Deleted)', $media->getCategoryLabel());
        self::assertSame('removed', $media->getCategory());
        self::assertSame('Stored label', $media->getLabel());
    }

    private function storedMedia(): Media
    {
        $media = new Media();
        $media->setLabel('Stored label')->setMimeType('image/png')->setExtension('png');
        $media->setFileName('stored.png')->setCategory('documents')->setCropData('{"stored":true}');
        return $media;
    }

    private function assertStoredMedia(Media $media): void
    {
        self::assertSame('Stored label', $media->getLabel());
        self::assertSame('image/png', $media->getMimeType());
        self::assertSame('png', $media->getExtension());
        self::assertSame('stored.png', $media->getFileName());
        self::assertSame('documents', $media->getCategory());
        self::assertSame('{"stored":true}', $media->getCropData());
        self::assertNull($media->getFile());
        self::assertNull($media->getId());
        self::assertNull($media->getUpdatedAt());
    }

    private function uploadedMedia(): Media
    {
        $scratch = getenv('CNCE_TEST_SCRATCH') ?: sys_get_temp_dir();
        $path = tempnam($scratch, 'wd-media-lifecycle-');
        self::assertNotFalse($path);
        $this->fixturePath = $path.'.txt';
        self::assertTrue(rename($path, $this->fixturePath));
        self::assertNotFalse(file_put_contents($this->fixturePath, "Synthetic media lifecycle fixture.\n"));
        $media = new Media();
        $media->setLabel('Previous label')->setMimeType('application/octet-stream')->setExtension('old');
        $media->setCategory('documents')->setFileName('existing-storage-name.txt');
        $media->setFile(new UploadedFile($this->fixturePath, 'client-document.txt', 'text/plain', UPLOAD_ERR_OK, true));
        $media->setCropData('{"keep":"unchanged"}');
        return $media;
    }

    private function invoke(string $eventName, Media $media, \Doctrine\Common\EventArgs $event): void
    {
        self::assertSame([
            ['class' => MediaListener::class, 'method' => $eventName],
        ], $this->metadata->entityListeners[$eventName]);
        $systems = $this->invoker->getSubscribedSystems($this->metadata, $eventName);
        self::assertSame(ListenersInvoker::INVOKE_LISTENERS, $systems);
        $this->invoker->invoke($this->metadata, $eventName, $media, $event, $systems);
    }

    private function assertUploadedMedia(Media $media): void
    {
        self::assertSame('client-document.txt', $media->getLabel());
        self::assertSame('text/plain', $media->getMimeType());
        self::assertSame('txt', $media->getExtension());
        self::assertSame('existing-storage-name.txt', $media->getFileName());
        self::assertSame('{"keep":"unchanged"}', $media->getCropData());
        self::assertSame('documents', $media->getCategory());
        self::assertNull($media->getId());
        self::assertSame($this->fixturePath, $media->getFile()->getPathname());
        self::assertSame("Synthetic media lifecycle fixture.\n", file_get_contents($this->fixturePath));
    }
}
