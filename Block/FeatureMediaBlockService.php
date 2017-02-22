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
        $resolver->setDefaults(array(
            'media' => false,
            'orientation' => 'left',
            'title' => false,
            'content' => false,
            'context' => false,
            'mediaId' => null,
            'format' => false,
            'template' => 'SonataMediaBundle:Block:block_feature_media.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $immutableArrayType = 'Sonata\CoreBundle\Form\Type\ImmutableArrayType';
            $textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
            $textareaType = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';
            $choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
        } else {
            $immutableArrayType = 'sonata_type_immutable_array';
            $textType = 'text';
            $textareaType = 'textarea';
            $choiceType = 'choice';
        }

        $formMapper->add('settings', $immutableArrayType, array(
            'keys' => array(
                array('title', $textType, array(
                    'required' => false,
                    'label' => 'form.label_title',
                )),
                array('content', $textareaType, array(
                    'required' => false,
                    'label' => 'form.label_content',
                )),
                array('orientation', $choiceType, array(
                    'required' => false,
                    'choices' => array(
                        'left' => 'form.label_orientation_left',
                        'right' => 'form.label_orientation_right',
                    ),
                    'label' => 'form.label_orientation',
                )),
                array($this->getMediaBuilder($formMapper), null, array()),
                array('format', $choiceType, array(
                    'required' => count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                )),
            ),
            'translation_domain' => 'SonataMediaBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/sonatamedia/blocks/feature_media/theme.css',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataMediaBundle', array(
            'class' => 'fa fa-picture-o',
        ));
    }
}
