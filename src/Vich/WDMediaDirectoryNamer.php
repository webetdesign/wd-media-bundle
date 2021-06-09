<?php


namespace WebEtDesign\MediaBundle\Vich;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class WDMediaDirectoryNamer implements DirectoryNamerInterface
{

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag) {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @inheritDoc
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
//        $categories = $this->parameterBag->get('wd_media.categories');
        return $object->getCategory();
    }
}
