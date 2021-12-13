<?php


namespace WebEtDesign\MediaBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\MediaBundle\Entity\Media;

class WDMediaType extends AbstractType
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['category']        = $options['category'];
        $view->vars['format']          = $options['format'];
        $view->vars['allow_add']       = $options['allow_add'];
        $view->vars['allow_edit']      = $options['allow_edit'];
        $view->vars['allow_list']      = $options['allow_list'];
        $view->vars['allow_delete']    = $options['allow_delete'];
        $view->vars['allow_crop']      = $options['allow_crop'];
        $view->vars['wd_media_config'] = [
            'categories' => $this->parameterBag->get('wd_media.categories'),
            'responsive' => $this->parameterBag->get('wd_media.responsive'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'        => Media::class,
            'format'       => null,
            'allow_add'    => true,
            'allow_edit'   => true,
            'allow_list'   => true,
            'allow_delete' => true,
            'allow_crop'   => true,
        ]);

        $resolver->setRequired([
            'category',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix(): string
    {
        return 'wd_media';
    }


}
