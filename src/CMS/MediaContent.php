<?php


namespace WebEtDesign\MediaBundle\CMS;


use Symfony\Component\Form\DataTransformerInterface;
use WebEtDesign\CmsBundle\Entity\CmsContent;
use WebEtDesign\CmsBundle\Services\AbstractCustomContent;

class MediaContent extends AbstractCustomContent
{

    function getFormOptions(): array
    {
        // TODO: Implement getFormOptions() method.
    }

    function getFormType(): string
    {
        // TODO: Implement getFormType() method.
    }

    function getCallbackTransformer(): DataTransformerInterface
    {
        // TODO: Implement getCallbackTransformer() method.
    }

    function render(CmsContent $content)
    {
        // TODO: Implement render() method.
    }
}
