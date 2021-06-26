<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Block;

use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FeatureMediaBlockService extends MediaBlockService
{
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'media' => false,
            'orientation' => 'left',
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'content' => false,
            'context' => false,
            'mediaId' => null,
            'format' => false,
            'template' => '@SonataMedia/Block/block_feature_media.html.twig',
        ]);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'label' => 'form.label_title',
                    'required' => false,
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'required' => false,
                ]],
                ['class', TextType::class, [
                    'label' => 'form.label_class',
                    'required' => false,
                ]],
                ['content', TextareaType::class, [
                    'required' => false,
                    'label' => 'form.label_content',
                ]],
                ['orientation', ChoiceType::class, [
                    'required' => false,
                    'choices' => [
                        'left' => 'form.label_orientation_left',
                        'right' => 'form.label_orientation_right',
                    ],
                    'label' => 'form.label_orientation',
                ]],
                [$this->getMediaBuilder($form), null, []],
                ['format', ChoiceType::class, [
                    'required' => \count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    public function getStylesheets($media)
    {
        return [
            '/bundles/sonatamedia/blocks/feature_media/theme.css',
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.media.block.feature_media', null, null, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }
}
