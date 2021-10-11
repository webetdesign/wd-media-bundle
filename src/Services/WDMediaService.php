<?php


namespace WebEtDesign\MediaBundle\Services;


use Exception;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use WebEtDesign\MediaBundle\Entity\Media;

class WDMediaService
{

    private ParameterBagInterface $parameterBag;
    private UploaderHelper        $uploaderHelper;
    private CacheManager          $cacheManager;

    public function __construct(
        ParameterBagInterface $parameterBag,
        UploaderHelper $uploaderHelper,
        CacheManager $cacheManager
    ) {
        $this->parameterBag   = $parameterBag;
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager   = $cacheManager;
    }

    public function getMediaPath(Media $media): ?string
    {
        return $this->uploaderHelper->asset($media);
    }

    /**
     * @throws NotFoundException
     */
    public function getImagePath(Media $media, $format, $device = null, $absoluteUrl = false): ?string
    {
        $path          = $this->uploaderHelper->asset($media);
        $runtimeConfig = $this->getRuntimeConfig($media, $format, $device);

        return $this->cacheManager->getBrowserPath(
            $path,
            'wd_media',
            $runtimeConfig,
            null,
            $absoluteUrl ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::RELATIVE_PATH
        );
    }

    protected function getRuntimeConfig(Media $media, $format, $device = null): ?array
    {
        $categories = $this->parameterBag->get('wd_media.categories');
        $config     = $categories[$media->getCategory()];

        if (!isset($config['formats'][$format])) {
            throw new NotFoundException('WD Media format not found');
        }

        if ($device === null) {
            $device = array_key_first($config['formats'][$format]);
        }

        $filters = $config['formats'][$format][$device]['filters'];


        if (($crop = $media->getCropDataForFormatDevice($format, $device))) {
            $cropFilter = [
                'start' => [$crop['x'], $crop['y']],
                'size'  => [$crop['width'], $crop['height']]
            ];

            $filters = array_merge(['crop' => $cropFilter], $filters);
        }

        return $filters;
    }

}
