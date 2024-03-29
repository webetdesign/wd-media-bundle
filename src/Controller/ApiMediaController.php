<?php


namespace WebEtDesign\MediaBundle\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Services\WDMediaService;

class ApiMediaController extends AbstractController
{

    private EntityManagerInterface $em;
    private CacheManager $cacheManager;
    private UploaderHelper $uploaderHelper;
    private WDMediaService $mediaService;

    public function __construct(
        EntityManagerInterface $em,
        CacheManager $cacheManager,
        UploaderHelper $uploaderHelper,
        WDMediaService $mediaService
    ) {
        $this->em             = $em;
        $this->cacheManager   = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
        $this->mediaService   = $mediaService;
    }

    /**
     * @param Media $media
     *
     * @Route("/api/wdmedia/{id}", name="")
     * @return JsonResponse
     */
    public function getMedia(Media $media, SerializerInterface $serializer)
    {
        if ($media->getMimeType() == 'image/svg+xml') {
            $path = $this->uploaderHelper->asset($media);
        } elseif (in_array($media->getMimeType(), ['image/png', 'image/jpeg', 'image/tiff'])) {
            $path = $this->cacheManager->getBrowserPath($this->uploaderHelper->asset($media),
                'wd_media_admin_type');
        } else {
            $path = '/bundles/wdmedia/img/files/' . $media->getExtension() . '.png';
        }

        return new JsonResponse([
            'id'            => $media->getId(),
            'label'         => $media->getLabel(),
            'category'      => $media->getCategory(),
            'categoryLabel' => $media->getCategoryLabel(),
            'mimeType'      => $media->getMimeType(),
            'path'          => $path,
            'reference'     => $this->uploaderHelper->asset($media),
            'cropData'      => json_decode($media->getCropData(), true)
        ]);
    }

    /**
     * @param Request $request
     * @param Media $media
     *
     * @Route("/api/wdmedia/setcrop/{id}", name="api_wdmedia_setcrop", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function patch(Request $request, Media $media)
    {
        if (!$media) {
            return new JsonResponse('Media not found', 404);
        }

        if (!$request->request->has('cropData')) {
            return new JsonResponse('', 400);
        }

        $media->setCropData($request->request->get('cropData'));

        $this->em->persist($media);
        $this->em->flush();


        return new JsonResponse('', 200);
    }

    /**
     * @param Media $media
     * @param null $format
     * @return RedirectResponse
     * @throws NotFoundException
     * @Route("/api/wdmedia/render/{id}", name="api_render_media", methods={"GET"})
     * @Route("/api/wdmedia/render/{id}/{format}", name="api_render_image", methods={"GET"})
     *
     * @ParamConverter("media", class="WebEtDesign\MediaBundle\Entity\Media", options={"mapping": {"id": "id"}})
     */
    public function renderMedia(
        Media $media,
        $format = null
    ): RedirectResponse
    {
        if ($format && strpos($media->getMimeType(), 'image') !== false) {
            $path = $this->mediaService->getImagePath($media, $format);
        } else {
            $path = $this->mediaService->getMediaPath($media);
        }

        if (!$path) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($path);
    }

    /**
     * @param Media $media
     * @param null $format
     * @return RedirectResponse
     * @throws NotFoundException
     * @Route("/api/wdmedia/download/{permalink}/{format}", name="api_dmedia_download_slug")
     *
     * @ParamConverter("media", class="WebEtDesign\MediaBundle\Entity\Media", options={"mapping": {"permalink": "permalink"}})
     */
    public function renderMediaPermalink(
        Media $media,
              $format = null
    ): RedirectResponse
    {
        if ($format && strpos($media->getMimeType(), 'image') !== false) {
            $path = $this->mediaService->getImagePath($media, $format);
        } else {
            $path = $this->mediaService->getMediaPath($media);
        }

        if (!$path) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($path);
    }

    //    /**
    //     * @param Request $request
    //     * @Route("/api/wdmedia/create", name="api_wdmedia_create")
    //     */
    //    public function create(Request $request)
    //    {
    //        $media = new Media();
    //
    //        if ($request->query->has('category')) {
    //            $media->setCategory($request->query->get('category'));
    //        }
    //
    //        $form = $this->createForm(MediaType::class);
    //
    //        $form->handleRequest($request);
    //        if ($form->isSubmitted() && $form->isValid()) {
    //            $this->em->persist($media);
    //            $this->em->flush();
    //        }
    //
    //        return $this->render('@WDMedia/async/media_async_form.html.twig', [
    //            'form' => $form->createView(),
    //        ]);
    //    }

}
