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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FeatureMediaBlockService extends MediaBlockService
{
    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
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

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $formMapper->add('settings', ImmutableArrayType::class, [
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
                [$this->getMediaBuilder($formMapper), null, []],
                ['format', ChoiceType::class, [
                    'required' => \count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return [
            '/bundles/sonatamedia/blocks/feature_media/theme.css',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
    }
}
