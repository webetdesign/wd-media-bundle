<?php


namespace WebEtDesign\MediaBundle\Twig;


use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
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
    protected ParameterBagInterface $parameterBag;
    protected WDMediaService        $mediaService;
    protected Environment           $twig;
    private HttpClientInterface     $httpClient;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        ParameterBagInterface $parameterBag,
        WDMediaService $mediaService,
        Environment $twig,
        HttpClientInterface $httpClient,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->parameterBag = $parameterBag;
        $this->mediaService = $mediaService;
        $this->twig         = $twig;
        $this->httpClient   = $httpClient;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wd_media_path', [$this, 'media']),
            new TwigFunction('wd_media_image_path', [$this, 'mediaImage']),
            new TwigFunction('wd_media_image_path_autoload', [$this, 'mediaImageAutoload']),
            new TwigFunction('wd_media_image_responsive', [$this, 'mediaResponsive'],
                ['is_safe' => ['html']]),
            new TwigFunction('wd_media_link', [$this, 'mediaLink']),
            new TwigFunction('wd_media_formats', [$this, 'mediaFormats']),

        ];
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('wd_media_path', [$this, 'media']),
            new TwigFilter('wd_media_image_path', [$this, 'mediaImage']),
            new TwigFilter('wd_media_image_path_autoload', [$this, 'mediaImageAutoload']),
            new TwigFilter('wd_media_image_responsive', [$this, 'mediaResponsive'],
                ['is_safe' => ['html']]),
            new TwigFilter('wd_media_link', [$this, 'mediaLink']),
            new TwigFilter('wd_media_formats', [$this, 'mediaFormats']),


        ];
    }

    public function media(?Media $media): ?string
    {
        if (!$media) {
            return null;
        }

        return $this->mediaService->getMediaPath($media);
    }

    /**
     * @throws NotFoundException
     */
    public function mediaImage(?Media $media, $format, $device = null, $absoluteUrl = false): ?string
    {
        if (!$media) {
            return null;
        }

        return $this->mediaService->getImagePath($media, $format, $device, $absoluteUrl);
    }

    /**
     * @throws NotFoundException
     */
    public function mediaImageAutoload(?Media $media, $format, $device = null, $absoluteUrl = false): ?string
    {
        if (!$media) {
            return null;
        }

        $path = $this->mediaService->getImagePath($media, $format, $device, $absoluteUrl);
        if (preg_match('/\/resolve\//', $path)) {
            $response = $this->httpClient->request('GET', $path);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            return $this->mediaService->getImagePath($media, $format, $device, $absoluteUrl);
        }

        return $path;
    }

    /**
     * @throws NotFoundException
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function mediaResponsive(?Media $media, $format, $absoluteUrl = false): ?string
    {
        if (!$media) {
            return null;
        }

        $responsiveConfig = $this->parameterBag->get('wd_media.responsive');
        $categoryConfig   = $this->parameterBag->get('wd_media.categories')[$media->getCategory()];

        $devices = [];

        foreach ($responsiveConfig as $device => $config) {
            if (isset($categoryConfig['formats'][$format][$device])) {
                $devices[$device] = [
                    'path'  => $this->mediaService->getImagePath($media, $format, $device, $absoluteUrl),
                    'width' => $config['width'],
                ];
            }
        }

        return $this->twig->render('@WDMedia/responsive_picture_element.html.twig', [
            'devices' => $devices,
            'media'   => $media
        ]);
    }

    public function mediaLink (?Media $media = null, ?string $format = null): string
    {
        if (!$media || !$media->getId()){
            return '';
        }

        $categories = $this->parameterBag->get('wd_media.categories');

        if (!$format){
            $format     = isset($categories[$media->getCategory()]) ? array_key_first($categories[$media->getCategory()]['formats']) : null;
        }

        return $this->urlGenerator->generate($media->getPermalink() ? 'api_dmedia_download_slug' : 'api_render_image', array_merge(
            $media->getPermalink() ? ['permalink' => $media->getPermalink()] : ['id' => $media->getId()],
            [
                'format' => $format
            ]
        ), UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function mediaFormats (?Media $media = null ): array
    {
        if (!$media || !$media->getId()){
            return [];
        }
        $categories = $this->parameterBag->get('wd_media.categories');

        return isset($categories[$media->getCategory()]) ? array_keys($categories[$media->getCategory()]['formats']) : [];
    }
}
