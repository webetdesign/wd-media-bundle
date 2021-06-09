<?php


namespace WebEtDesign\MediaBundle\Controller\Admin;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use WebEtDesign\MediaBundle\Entity\Media;

class MediaAdminController extends CRUDController
{
    /**
     * @var CacheManager
     */
    private CacheManager $cacheManager;
    /**
     * @var UploaderHelper
     */
    private UploaderHelper $uploaderHelper;

    /**
     * @inheritDoc
     */
    public function __construct(
        CacheManager $cacheManager,
        UploaderHelper $uploaderHelper
    ) {
        $this->cacheManager   = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }


    /**
     * @inheritDoc
     */
    protected function preCreate(Request $request, $object)
    {
        if ($request->query->has('category')) {
            $object->setCategory($request->query->get('category'));
        }
    }


    /**
     * @phpstan-param T $object
     * @param Request $request
     * @param object $object
     * @return JsonResponse
     */
    protected function handleXmlHttpRequestSuccessResponse(
        Request $request,
        object $object
    ): JsonResponse {
        if (empty(array_intersect(['application/json', '*/*'],
            $request->getAcceptableContentTypes()))) {
            @trigger_error(sprintf(
                'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
                . ' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
                implode('", "', $request->getAcceptableContentTypes()),
                $request->getMethod(),
                $request->getUri()
            ), \E_USER_DEPRECATED);
        }

        if (in_array($object->getMimeType(), ['image/png', 'image/jpeg', 'image/tiff'])) {
            $path = $this->cacheManager->getBrowserPath($this->uploaderHelper->asset($object),
                'wd_media_admin_type');
        } else {
            $path = '/bundles/wdmedia/img/files/' . $object->getExtension() . '.png';
        }

        return $this->renderJson([
            'result'     => 'ok',
            'media'      => [
                'id'            => $object->getId(),
                'label'         => $object->getLabel(),
                'category'      => $object->getCategory(),
                'categoryLabel' => $object->getCategoryLabel(),
                'mimeType'      => $object->getMimeType(),
                'path'          => $path,
            ],
            'objectId'   => $this->admin->getNormalizedIdentifier($object),
            'objectName' => $this->escapeHtml($this->admin->toString($object)),
        ], Response::HTTP_OK);
    }


}
