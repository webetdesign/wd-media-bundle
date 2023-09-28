<?php
declare(strict_types=1);


namespace WebEtDesign\MediaBundle\Form\Type;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @inheritDoc
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices'          => $this->buildChoices(),
            'operator_type'    => null,
            'operator_options' => null,
            'field_type'       => null,
            'field_options'    => null,
        ]);
    }

    protected function buildChoices(): array
    {
        $categories = $this->parameterBag->get('wd_media.categories');

        $choices = [];
        foreach ($categories as $code => $category) {
            $choices[$category['label']] = $code;
        }

        return $choices;
    }

}
