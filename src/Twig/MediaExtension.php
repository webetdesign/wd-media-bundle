<?php


namespace WebEtDesign\MediaBundle\Twig;


use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Services\WDMediaService;

class MediaExtension extends AbstractExtension
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    private WDMediaService        $mediaService;
    private Environment           $twig;

    public function __construct(
        ParameterBagInterface $parameterBag,
        WDMediaService $mediaService,
        Environment $twig
    ) {
        $this->parameterBag = $parameterBag;
        $this->mediaService = $mediaService;
        $this->twig         = $twig;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('wd_media_path', [$this, 'media']),
            new TwigFunction('wd_media_image_path', [$this, 'mediaImage']),
            new TwigFunction('wd_media_image_responsive', [$this, 'mediaResponsive'],
                ['is_safe' => ['html']]),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('wd_media_path', [$this, 'media']),
            new TwigFilter('wd_media_image_path', [$this, 'mediaImage']),
            new TwigFilter('wd_media_image_responsive', [$this, 'mediaResponsive'],
                ['is_safe' => ['html']]),
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

    /**
     * @throws NotFoundException
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function mediaResponsive(Media $media, $format): string
    {
        $responsiveConfig = $this->parameterBag->get('wd_media.responsive');

        $devices = [];

        foreach ($responsiveConfig as $device => $config) {
            $devices[$device] = [
                'path'  => $this->mediaService->getImagePath($media, $format, $device),
                'width' => $config['width'],
            ];
        }

        return $this->twig->render('@WDMedia/responsive_picture_element.html.twig', [
            'devices' => $devices,
            'media'   => $media
        ]);
    }
}
