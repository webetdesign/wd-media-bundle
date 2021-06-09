<?php


namespace WebEtDesign\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="wd_media__category")
 */
class Category
{

    /**
     * @var int|null $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected ?int $id = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private string $label;

    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Category
     */
    public function setLabel(string $label): Category
    {
        $this->label = $label;
        return $this;
    }

}
