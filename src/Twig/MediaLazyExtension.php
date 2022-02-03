<?php


namespace WebEtDesign\MediaBundle\Twig;


use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\UX\LazyImage\BlurHash\BlurHashInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Services\WDMediaService;

class MediaLazyExtension extends AbstractExtension
{
    private BlurHashInterface     $blurHash;
    private ParameterBagInterface $parameterBag;
    private WDMediaService        $mediaService;
    private Environment           $twig;

    public function __construct(
        ParameterBagInterface $parameterBag,
        WDMediaService $mediaService,
        Environment $twig,
        BlurHashInterface $blurHash
    ) {

        $this->blurHash     = $blurHash;
        $this->parameterBag = $parameterBag;
        $this->mediaService = $mediaService;
        $this->twig         = $twig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wd_media_image_lazy', [$this, 'lazyImage'],
                ['is_safe' => ['html_attr']]),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('wd_media_image_lazy_attr', [$this, 'lazyImage'],
                ['is_safe' => ['html_attr']]),
        ];
    }

    /**
     * @param Media|null $media
     * @param $format
     * @param null $device
     * @return string|null
     * @throws NotFoundException
     */
    public function lazyImage(?Media $media, $format, $device = null): ?string
    {
        if (!$media) {
            return null;
        }

        $originale = $this->parameterBag->get('kernel.project_dir') . '/public' . $this->mediaService->getMediaPath($media);

        if (!file_exists($originale)) {
            return null;
        }

        $path = $this->mediaService->getImagePath($media, $format, $device);
        $size = getimagesize($originale);

        if (!$path || !$size) {
            return null;
        }

        $blur = $this->blurHash->createDataUriThumbnail($originale, $size[0] / 10, $size[1] / 10);

        return sprintf('src="%s" data-hd-src="%s" data-controller="symfony--ux-lazy-image--lazy-image"',
            $blur, $path);
    }
}
