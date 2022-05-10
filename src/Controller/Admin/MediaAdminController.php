<?php


namespace WebEtDesign\MediaBundle\Controller\Admin;


use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use WebEtDesign\MediaBundle\Entity\Media;
use const E_USER_DEPRECATED;

class MediaAdminController extends CRUDController
{
    private CacheManager $cacheManager;
    private UploaderHelper         $uploaderHelper;
    private EntityManagerInterface $em;

    public function __construct(
        CacheManager           $cacheManager,
        UploaderHelper         $uploaderHelper,
        EntityManagerInterface $entityManager
    )
    {
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
        $this->em = $entityManager;
    }


    /**
     * @inheritDoc
     */
    protected function preCreate(Request $request, $object): ?Response
    {
        if ($request->query->has('category')) {
            $object->setCategory($request->query->get('category'));
        }
        return null;
    }


    /**
     * @phpstan-param $object
     * @param Request $request
     * @param object $object
     * @return JsonResponse
     */
    protected function handleXmlHttpRequestSuccessResponse(
        Request $request,
        object  $object
    ): JsonResponse
    {
        if (empty(array_intersect(['application/json', '*/*'],
            $request->getAcceptableContentTypes()))) {
            @trigger_error(sprintf(
                'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
                . ' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
                implode('", "', $request->getAcceptableContentTypes()),
                $request->getMethod(),
                $request->getUri()
            ), E_USER_DEPRECATED);
        }

        if (in_array($object->getMimeType(), ['image/png', 'image/jpeg', 'image/tiff'])) {
            $path = $this->cacheManager->getBrowserPath($this->uploaderHelper->asset($object),
                'wd_media_admin_type');
        } else {
            $path = '/bundles/wdmedia/img/files/' . $object->getExtension() . '.png';
        }

        return $this->renderJson([
            'result' => 'ok',
            'media' => [
                'id' => $object->getId(),
                'label' => $object->getLabel(),
                'category' => $object->getCategory(),
                'categoryLabel' => $object->getCategoryLabel(),
                'mimeType' => $object->getMimeType(),
                'path' => $path,
            ],
            'objectId' => $this->admin->getNormalizedIdentifier($object),
            'objectName' => $this->escapeHtml($this->admin->toString($object)),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function ckeditorBrowserAction(Request $request): Response
    {

        $this->admin->checkAccess('list');

        $datagrid = $this->admin->getDatagrid();

        $category = $request->get('category');

        $datagrid->setValue('category', null, $category);

        $formView = $datagrid->getForm()->createView();

        $twig = $this->get('twig');
        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());

        return $this->renderWithExtraParams($this->admin->getTemplateRegistry()->getTemplate('browser'), [
            'action' => 'browser',
            'form' => $formView,
            'datagrid' => $datagrid,
        ]);
    }

    public function ckeditorUploadAction(Request $request): Response
    {

        $this->admin->checkAccess('create');

        $file = $request->files->get('upload');
        $category = $request->get('category');

        $media = new Media();

        $media->setFile($file)
            ->setCategory($category);

        $this->em->persist($media);
        $this->em->flush();

        return $this->json([
                "uploaded" => 1,
                "fileName" => $media->getFileName(),
                "url" => $this->uploaderHelper->asset($media)
            ]
        );
    }

    protected function handleXmlHttpRequestErrorResponse (Request $request, FormInterface $form): ?JsonResponse
    {
        if (empty(array_intersect(['application/json', '*/*'], $request->getAcceptableContentTypes()))) {
            @trigger_error(sprintf(
                'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
                .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
                implode('", "', $request->getAcceptableContentTypes()),
                $request->getMethod(),
                $request->getUri()
            ), \E_USER_DEPRECATED);

            return null;
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $this->renderJson([
            'result' => 'error',
            'errors' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

}
