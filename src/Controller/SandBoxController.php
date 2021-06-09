<?php


namespace WebEtDesign\MediaBundle\Controller;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\CmsBundle\Controller\BaseCmsController;
use WebEtDesign\MediaBundle\Repository\MediaRepository;

class SandBoxController extends BaseCmsController
{

    /**
     * @var MediaRepository
     */
    private MediaRepository $mediaRepository;

    public function __construct(
        MediaRepository $mediaRepository
    ) {
        $this->mediaRepository = $mediaRepository;
    }


    /**
     * @Route("/msb", name="msb")
     * @return Response
     */
    public function index()
    {
        $media = $this->mediaRepository->find(12);

        return $this->render('@WDMedia/sandbox.html.twig', [
            'media' => $media
        ]);
    }

}
