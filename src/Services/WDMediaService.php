<?php


namespace WebEtDesign\MediaBundle\Services;


use Exception;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use WebEtDesign\MediaBundle\Entity\Media;

class WDMediaService
{

    private ParameterBagInterface $parameterBag;
    private UploaderHelper        $uploaderHelper;
    private CacheManager          $cacheManager;
    private FilterService         $filterService;

    public function __construct(
        ParameterBagInterface $parameterBag,
        UploaderHelper $uploaderHelper,
        CacheManager $cacheManager,
        FilterService $filterService,
    ) {
        $this->parameterBag   = $parameterBag;
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager   = $cacheManager;
        $this->filterService  = $filterService;
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

        if (isset($runtimeConfig['quality'])) {
            $quality = $runtimeConfig['quality'];
            unset($runtimeConfig['quality']);
        } else {
            $quality = null;
        }

        $filter = $quality ? ("wd_media_$quality") : 'wd_media';

        return $this->cacheManager->getBrowserPath(
            $path,
            $filter,
            $runtimeConfig,
            null,
            $absoluteUrl ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::RELATIVE_PATH
        );
    }

    public function getImagePathForSeo(Media $media, $format, $device = null, $useWep = true): ?string
    {
        $path          = $this->uploaderHelper->asset($media);
        $runtimeConfig = $this->getRuntimeConfig($media, $format, $device);

        if (isset($runtimeConfig['quality'])) {
            $quality = $runtimeConfig['quality'];
            unset($runtimeConfig['quality']);
        } else {
            $quality = null;
        }

        $filter = $quality ? ("wd_media_$quality") : 'wd_media';

        // Pour ne pas obtenir l'url vers le controller de resolve, qui ne sera pas être suivie par les crawlers,
        // on retourne direct le lien de la ressource en cache.
        // Problème avec cette solution si la ressource n'existe pas en cache, elle sera créer immédiatement avant que la réponse soit envoyé au client
        // cela peut causer des lenteurs au chargement de la page.

        return $this->filterService->getUrlOfFilteredImageWithRuntimeFilters($path, $filter, $runtimeConfig, null, $useWep);
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

        if (isset($config['quality'])) {
            $filters['quality'] = $config['quality'];
        }

        return $filters;
    }

}
