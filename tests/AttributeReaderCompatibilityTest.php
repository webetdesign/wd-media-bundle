<?php
namespace WebEtDesign\MediaBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Attribute\Groups;
use WebEtDesign\MediaBundle\Entity\Media;

final class AttributeReaderCompatibilityTest extends TestCase
{
    public function testLegacyReaderCanInspectPropertiesWithoutLosingNativeGroups(): void
    {
        $reader = new AnnotationReader();
        $reflection = new \ReflectionClass(Media::class);
        foreach (['id', 'label', 'category', 'categoryLabel', 'fileName', 'mimeType', 'extension', 'cropData', 'description', 'permalink'] as $name) {
            $property = $reflection->getProperty($name);
            self::assertIsArray($reader->getPropertyAnnotations($property), $name);
            self::assertSame(['media'], $property->getAttributes(Groups::class)[0]->newInstance()->groups, $name);
        }
        self::assertIsArray($reader->getClassAnnotations($reflection));
        self::assertIsArray($reader->getPropertyAnnotations($reflection->getProperty('file')));
        self::assertSame('integer', $reflection->getProperty('id')->getAttributes(Column::class)[0]->newInstance()->type);
    }
}
