<?php


namespace WebEtDesign\MediaBundle\Listener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WebEtDesign\MediaBundle\Entity\Media;

class MediaListener
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function postLoad(Media $entity)
    {
        $categories = $this->parameterBag->get('wd_media.categories');

        if (isset($categories[$entity->getCategory()]['label'])) {
            $entity->setCategoryLabel($categories[$entity->getCategory()]['label']);
        } else {
            $entity->setCategoryLabel($entity->getCategory() . ' (Deleted)');
        }
    }

    public function prePersist(Media $entity, LifecycleEventArgs $evt)
    {
        $this->updateLabel($entity);
        $this->updateMimeType($entity);
        $this->updateExtension($entity);
    }

    public function preUpdate(Media $entity, LifecycleEventArgs $evt)
    {
        $this->updateLabel($entity);
        $this->updateMimeType($entity);
        $this->updateExtension($entity);
    }

    public function updateLabel($entity)
    {
        if ($entity->getFile()) {
            /** @var UploadedFile $file */
            $file = $entity->getFile();
            if ($file instanceof UploadedFile) {
                $entity->setLabel($file->getClientOriginalName());
            }
        }
    }

    public function updateMimeType($entity)
    {
        if ($entity->getFile()) {
            /** @var UploadedFile $file */
            $file = $entity->getFile();
            $entity->setMimeType($file->getMimeType());
        }
    }

    public function updateExtension($entity)
    {
        if ($entity->getFile()) {
            /** @var UploadedFile $file */
            $file = $entity->getFile();
            $entity->setExtension($file->getExtension());
        }
    }
}
