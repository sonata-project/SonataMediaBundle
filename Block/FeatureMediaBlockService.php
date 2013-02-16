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

use Sonata\BlockBundle\Model\BlockInterface;

use Symfony\Component\HttpFoundation\Response;

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
    public function getDefaultSettings()
    {
        return array(
            'media'   => false,
            'orientation' => 'left',
            'title'   => false,
            'content' => false,
            'context' => false,
            'format'  => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $contextChoices = $this->getContextChoices();
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
                array('context', 'choice', array('required' => true, 'choices' => $contextChoices)),
                array('format', 'choice', array('required' => count($formatChoices) > 0, 'choices' => $formatChoices)),
                array($this->getMediaBuilder($formMapper), null, array()),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $media = $settings['mediaId'];

        return $this->renderResponse('SonataMediaBundle:Block:block_feature_media.html.twig', array(
            'media'     => $media,
            'block'     => $block,
            'settings'  => $settings
        ), $response);
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
