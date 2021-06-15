<?php

namespace WebEtDesign\MediaBundle\Listener;

use Imagick;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Event\Event;
use WebEtDesign\MediaBundle\Entity\Media;

class OptimizerListener
{

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();

        switch (true) {
            case $object instanceof Media:
                $this->optimize($object, 'file');
                break;
        }
    }

    private function optimize($object, $property)
    {
        $categories = $this->parameterBag->get('wd_media.categories');
        $config     = $categories[$object->getCategory()];

        $method = 'get' . ucfirst($property);

        if (in_array($object->getMimeType(), ['image/jpeg', 'image/png', 'image/tiff'])) {
            $imagick = new Imagick($object->$method()->getPathname());
            $d       = $imagick->getImageGeometry();
            if ($d['width'] > $d['height'] && $d['width'] > $config['pre_upload']['max_width']) {
                $imagick->scaleImage($config['pre_upload']['max_width'], null);
            }
            if ($d['width'] < $d['height'] && $d['height'] > $config['pre_upload']['max_height']) {
                $imagick->scaleImage(null, $config['pre_upload']['max_height']);
            }
            $imagick->setImageFormat($object->$method()->getExtension());
            $imagick->writeImage($object->$method()->getPathname());

            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($object->$method()->getPathname());
        }
    }

}
