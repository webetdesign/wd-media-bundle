<?php

declare(strict_types=1);

namespace WebEtDesign\MediaBundle\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Form\Type\CategoryType;

final class MediaAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setTranslationDomain('wd_media');
    }


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
            ->add('label')
            ->add('mimeType', null, [
                'label' => 'Type de fichier'
            ])
            ->add('category', ChoiceFilter::class, [
                'show_filter' => !$this->getRequest()->isXmlHttpRequest(),
                'field_type'  => CategoryType::class,
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $modes = $this->getListModes();
        unset($modes['mosaic']);
        $this->setListModes($modes);

        $list
            ->add('label', null, [
                'label' => 'Fichier'
            ])
            ->add('link', null, [
                'label' => 'Lien'
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Media $subject */
        $subject = $this->getSubject();
        $em      = $this->em;

        $this->setFormTheme(array_merge($this->getFormTheme(), [
            '@WDMedia/admin/Media/file_type.html.twig',
        ]));

        if ($subject->getCategory()) {
            $fileConstraintsOptions = $this->getFileConstraints($subject->getCategory());
        }

        $form->with('tab.file', ['class' => 'col-md-6', 'box_class' => 'box box-primary']);

        if (!$this->getSubject()->getId()) {
            $form->add('category', CategoryType::class);
        }

        $form
            ->add('file', FileType::class, [
                'required'    => !$this->getSubject()->getId(),
                'attr'        => [
                    'categories' => json_encode($this->parameterBag->get('wd_media.categories')),
                    'responsive' => json_encode($this->parameterBag->get('wd_media.responsive')),
                ],
                'constraints' => [
                    new File($fileConstraintsOptions ?? [])
                ]
            ])
            ->end();

        if ($this->getSubject()->getId()) {
            $form
                ->with('tab.properties', ['class' => 'col-md-6', 'box_class' => 'box box-warning'])
                ->add('label')
                ->add('alt')
                ->add('permalink', TextType::class, [
                    'label'       => 'Raccourci URL',
                    'required'    => false,
                    'help'        => 'Cette valeur permet de générer un lien sur l\'url /api/wdmedia/download/{votre-raccourci}/{format-image}',
                    'constraints' => [
                        new Regex([
                            'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
                        ]),
                        new Callback([
                            'callback' => function ($value, ExecutionContextInterface $context) use (
                                $em,
                                $subject
                            ) {
                                if (!$subject instanceof Media) {
                                    return;
                                }


                                if ($value && strlen($value) != 0) {
                                    /** @var Media $item */
                                    foreach ($em->getRepository(Media::class)->findBy(['permalink' => $value]) as $item) {
                                        if ($item->getId() !== $subject->getId()) {
                                            $context->buildViolation("Ce raccourci URL existe déjà pour le fichier : " . $item->getLabel())
                                                ->atPath('permalink')
                                                ->addViolation();
                                        }
                                    }
                                }
                            }
                        ])
                    ]
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr'     => [
                        'data-controller' => 'char-counter',
                    ],
                ])
                ->end();
        }

        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $media = $event->getData();
                $form  = $event->getForm();

                if (!$media) {
                    return;
                }

                if ($media->getCategory()) {
                    $form->add('category', HiddenType::class);
                }
            });

        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $media = $event->getData();
                $form  = $event->getForm();

                if (!$media) {
                    return;
                }

                if ($media['category']) {
                    $form->remove('file');
                    $form
                        ->add('file', FileType::class, [
                            'required'    => !$this->getSubject()->getId(),
                            'constraints' => [
                                new File($this->getFileConstraints($media['category']))
                            ],
                        ]);
                }
            });
    }


    public function getFileConstraints(string $category)
    {
        $categories = $this->parameterBag->get('wd_media.categories');
        $catConfig  = $categories[$category] ?? null;
        if (!$catConfig) {
            return null;
        }
        return $catConfig['pre_upload']['file_constraints'];
    }

}
