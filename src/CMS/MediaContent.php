<?php


namespace WebEtDesign\MediaBundle\CMS;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use WebEtDesign\CmsBundle\Entity\CmsContent;
use WebEtDesign\CmsBundle\Services\AbstractCustomContent;
use WebEtDesign\MediaBundle\CMS\transformer\MediaContentTransformer;
use WebEtDesign\MediaBundle\Form\Type\WDMediaType;

class MediaContent extends AbstractCustomContent
{
    const NAME = "MEDIA";

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    function getFormType(): string
    {
        return WDMediaType::class;
    }

    function getFormOptions(): array
    {
        $opts = $this->getContentOptions();

        if (!isset($opts['category'])) {
            $opts['category'] = 'default_cms';
        }

        return $opts;
    }

    function getCallbackTransformer(): DataTransformerInterface
    {
        return new MediaContentTransformer($this->em);
    }

    function render(CmsContent $content)
    {
        return $this->getCallbackTransformer()->transform($content->getValue());
    }
}
