<?php

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
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
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
            'title' => false,
            'content' => false,
            'context' => false,
            'mediaId' => null,
            'format' => false,
            'template' => 'SonataMediaBundle:Block:block_feature_media.html.twig',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $formMapper->add('settings', 'Sonata\CoreBundle\Form\Type\ImmutableArrayType', [
            'keys' => [
                ['title', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'required' => false,
                    'label' => 'form.label_title',
                ]],
                ['content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                    'required' => false,
                    'label' => 'form.label_content',
                ]],
                ['orientation', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'required' => false,
                    'choices' => [
                        'left' => 'form.label_orientation_left',
                        'right' => 'form.label_orientation_right',
                    ],
                    'label' => 'form.label_orientation',
                ]],
                [$this->getMediaBuilder($formMapper), null, []],
                ['format', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                    'required' => count($formatChoices) > 0,
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
