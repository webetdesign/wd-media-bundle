<?php


namespace WebEtDesign\MediaBundle\Twig;


use http\Exception\RuntimeException;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Services\WDMediaService;

class MediaExtension extends AbstractExtension
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    private WDMediaService        $mediaService;

    public function __construct(ParameterBagInterface $parameterBag, WDMediaService $mediaService) {
        $this->parameterBag = $parameterBag;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('wd_media_path', [$this, 'media']),
            new TwigFilter('wd_media_image_path', [$this, 'mediaImage']),
        ];
    }

    public function media(Media $media): ?string
    {
        return $this->mediaService->getMediaPath($media);
    }

    /**
     * @throws NotFoundException
     */
    public function mediaImage(Media $media, $format, $device = null): ?string
    {
        return $this->mediaService->getImagePath($media, $format, $device);
    }
}
