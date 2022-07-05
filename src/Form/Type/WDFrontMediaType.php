<?php


namespace WebEtDesign\MediaBundle\Form\Type;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use WebEtDesign\MediaBundle\Entity\Media;

class WDFrontMediaType extends AbstractType
{
    const IMAGE_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/bmp',
        'image/webp',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('category', HiddenType::class, [
            'data' => $options['category']
        ]);
        $builder->add('file', VichFileType::class, [
            'required' => $options['required'],
            'allow_delete' => $options['allow_delete'],
            'delete_label' => $options['allow_delete'] ? 'wd_front_media_type.file.deleted_label' : null,
            'download_label' => $options['allow_download'] ? 'wd_front_media_type.file.dowload_label' : false,
            'constraints' => $this->getFileConstraints($options),
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmit']
        );
    }

    public function onPreSubmit(PreSubmitEvent $event):void
    {
        dd($event);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_delete'] = $options['allow_delete'];
        $view->vars['allow_crop'] = $options['allow_crop'];
        $view->vars['allow_download'] = $options['allow_download'];
        $view->vars['allow_edit'] = $options['allow_edit'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => Media::class,
            'category'  => null,
            'required'  => false,
            'allow_delete' => false,
            'allow_download' => false,
            'allow_crop' => false,
            'allow_edit' => false,
            'translation_domain' => 'wd_front_media_type',
            'mime_types' => null,
        ]);
        
        $resolver->isRequired('category');
    }

    public function getBlockPrefix()
    {
        return 'wd_front_media';
    }

    private function getFileConstraints(array $options)
    {
        if ($options['mime_types'] != null && is_array($options['mime_types']) && count($options['mime_types']) > 0) {
            return $fileConstraints = [
                new File([
                    'mimeTypes' => $options['mime_types']
                ]),
            ];
        }
        
        return [];
    }
}
