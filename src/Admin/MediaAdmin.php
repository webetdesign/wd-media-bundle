<?php

declare(strict_types=1);

namespace WebEtDesign\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use WebEtDesign\MediaBundle\Form\Type\CategoryType;

final class MediaAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('ckeditor_browser', 'ckeditor_browser', [
//            '_controller' => 'SonataFormatterBundle:CkeditorAdmin:browser',
        ]);

        $collection->add('ckeditor_upload', 'ckeditor_upload', [
//            '_controller' => 'SonataFormatterBundle:CkeditorAdmin:upload',
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('label')
            ->add('category', CategoryType::class, [
                'show_filter' => !$this->getRequest()->isXmlHttpRequest()
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $modes = $this->getListModes();
        unset($modes['mosaic']);
        $this->setListModes($modes);

        $list
            ->add('label')
            ->add('categoryLabel')
            ->add('fileName')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if (!$this->getSubject()->getId()) {
            $form
                ->add('category', CategoryType::class);
        }

        $form
            ->add('file', FileType::class, [
                'required'     => true,
            ]);

        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $media = $event->getData();
            $form = $event->getForm();

            if (!$media) {
                return;
            }

            if ($media->getCategory()) {
                $form->add('category', HiddenType::class);
            }
        });
    }

}
