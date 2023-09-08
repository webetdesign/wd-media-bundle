<?php


namespace WebEtDesign\MediaBundle\Form\Type;


use JsonException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\MediaBundle\Entity\Media;
use WebEtDesign\MediaBundle\Repository\MediaRepository;

class WDMediaType extends AbstractType
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private MediaRepository $mediaRepo
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (?Media $media): ?int {
                return $media?->getId();
            },
            function (?int $mediaId): ?Media {
                if ($mediaId === null) {
                    return null;
                }
                return $this->mediaRepo->find($mediaId);
            }
        ));
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['media']           = $this->getMedia($form->getData());
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

    public function getMedia($data): ?Media
    {
        if ($data instanceof Media) {
            return $data;
        }

        try {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($data) && isset($data['id'])) {
                return $this->mediaRepo->find($data['id']);
            }
        } catch (JsonException $e) {}

        return null;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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
        return IntegerType::class;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix(): string
    {
        return 'wd_media';
    }
}