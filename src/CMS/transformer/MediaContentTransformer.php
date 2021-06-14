<?php
namespace WebEtDesign\MediaBundle\CMS\transformer;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use WebEtDesign\MediaBundle\Entity\Media;

class MediaContentTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if ($value !== null) {
            $value = $this->em->getRepository(Media::class)->find($value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if ($value instanceof Media) {
            return $value->getId();
        }
        return null;
    }
}
