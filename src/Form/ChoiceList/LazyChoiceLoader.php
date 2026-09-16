<?php

namespace WebEtDesign\MediaBundle\Form\ChoiceList;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * Décore un ChoiceLoaderInterface pour un champ dont la liste n'est jamais rendue.
 *
 * ChoiceType::buildView() construit systématiquement la vue de la liste, ce qui
 * appelle loadChoiceList() et charge la table entière — même quand le gabarit ne
 * rend aucune balise <option>. Symfony 7.2 répond à ce besoin avec l'option
 * choice_lazy et la classe Form\ChoiceList\Loader\LazyChoiceLoader, dont cette
 * classe est le rétroportage ; sur les versions antérieures, court-circuiter
 * loadChoiceList() est le seul moyen de l'éviter.
 *
 * Les valeurs sélectionnées et soumises passent par le loader décoré, qui les
 * résout par identifiant : la transformation et la validation restent donc
 * celles d'un EntityType ordinaire.
 */
class LazyChoiceLoader implements ChoiceLoaderInterface
{
    private ChoiceLoaderInterface $decorated;

    private ?ChoiceListInterface $choiceList = null;

    public function __construct(ChoiceLoaderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     *
     * Le paramètre n'est pas typé : ChoiceLoaderInterface ne le type pas avant
     * Symfony 7.2, et le restreindre ici romprait la compatibilité de signature.
     */
    public function loadChoiceList($value = null): ChoiceListInterface
    {
        return $this->choiceList ?? $this->choiceList = new ArrayChoiceList([], $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null): array
    {
        return $this->decorated->loadChoicesForValues($values, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null): array
    {
        return $this->decorated->loadValuesForChoices($choices, $value);
    }
}
