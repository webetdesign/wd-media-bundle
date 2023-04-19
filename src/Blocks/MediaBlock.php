<?php

namespace WebEtDesign\MediaBundle\Blocks;

use WebEtDesign\CmsBundle\Attribute\AsCmsBlock;
use WebEtDesign\CmsBundle\CMS\Block\AbstractBlock;
use WebEtDesign\MediaBundle\Form\Type\WDMediaType;

#[AsCmsBlock(name: self::code)]
class MediaBlock extends AbstractBlock
{
    const code = 'MEDIA';

    protected string $formType = WDMediaType::class;

    protected array $formOptions = [
        'category' => 'default',
    ];

    protected ?string $template = '@WDMedia/blocks/cms_media.html.twig';

    protected array $settings = [
        'responsive'   => true,
        'format'       => null,
        'device'       => null,
        'absolute_url' => false
    ];
}
