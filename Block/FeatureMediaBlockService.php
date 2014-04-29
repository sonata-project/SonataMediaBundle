<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * PageExtension
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FeatureMediaBlockService extends MediaBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Feature Media';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'media'   => false,
            'orientation' => 'left',
            'title'   => false,
            'content' => false,
            'context' => false,
            'mediaId'  => null,
            'format'  => false,
            'template' => 'SonataMediaBundle:Block:block_feature_media.html.twig'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $translator = $this->container->get('translator');

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false)),
                array('content', 'textarea', array('required' => false)),
                array('orientation', 'choice', array('choices' => array(
                    'left'  => $translator->trans('feature_left_choice', array(), 'SonataMediaBundle'),
                    'right' => $translator->trans('feature_right_choice', array(), 'SonataMediaBundle')
                ))),
                array($this->getMediaBuilder($formMapper), null, array()),
                array('format', 'choice', array('required' => count($formatChoices) > 0, 'choices' => $formatChoices)),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/sonatamedia/blocks/feature_media/theme.css'
        );
    }
}
