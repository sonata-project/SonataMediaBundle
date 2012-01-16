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
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Block\BaseBlockService;

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;

/**
 * PageExtension
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaBlockService extends BaseBlockService
{
    protected $mediaAdmin;

    protected $mediaManager;

    public function __construct($name, EngineInterface $templating, ContainerInterface $container, MediaManagerInterface $mediaManager)
    {
        parent::__construct($name, $templating);

        $this->mediaManager = $mediaManager;
        $this->container    = $container;
    }

    public function getName()
    {
        return 'Media';
    }

    public function getMediaPool()
    {
        return $this->getMediaAdmin()->getPool();
    }

    public function getMediaAdmin()
    {
        if (!$this->mediaAdmin) {
            $this->mediaAdmin = $this->container->get('sonata.media.admin.media');
        }

        return $this->mediaAdmin;
    }

    public function getDefaultSettings()
    {
        return array(
            'media'    => false,
            'title'    => false,
            'context'  => false,
            'format'   => false,
        );
    }

    public function buildEditForm(CmsManagerInterface $manager, FormMapper $formMapper, BlockInterface $block)
    {
        $contextChoices = $this->getContextChoices();
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false)),
                array('context', 'choice', array('required' => true, 'choices' => $contextChoices)),
                array('format', 'choice', array('required' => count($formatChoices) > 0, 'choices' => $formatChoices)),
                array($this->getMediaBuilder($formMapper), null, array()),
            )
        ));
    }

    /**
     * @return array
     */
    protected function getContextChoices()
    {
        $contextChoices = array();

        foreach ($this->getMediaPool()->getContexts() as $name => $context) {
            $contextChoices[$name] = $name;
        }

        return $contextChoices;
    }

    /**
     * @param null|\Sonata\MediaBundle\Model\MediaInterface $media
     * @return array
     */
    protected function getFormatChoices(MediaInterface $media = null)
    {
        $formatChoices = array();

        if ($media instanceof MediaInterface) {
            $formats = $this->getMediaPool()->getFormatNamesByContext($media->getContext());

            foreach ($formats as $code => $format) {
                $formatChoices[$code] = $code;
            }
        }

        return $formatChoices;
    }

    protected function getMediaBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $formMapper->getAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media' );
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array('fieldName' => 'media', 'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE));

        return $formMapper->create('mediaId', 'sonata_type_model', array(
            'sonata_field_description' => $fieldDescription,
            'class'             => $this->getMediaAdmin()->getClass(),
            'model_manager'     => $this->getMediaAdmin()->getModelManager()
        ));
    }

    public function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block)
    {

    }

    protected function getTemplate()
    {
        return 'SonataMediaBundle:Block:block_media.html.twig';
    }

    public function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $media = $settings['mediaId'];

        return $this->renderResponse($this->getTemplate(), array(
            'media'     => $media,
            'block'     => $block,
            'settings'  => $settings
        ), $response);
    }

    public function load(CmsManagerInterface $manager, BlockInterface $block)
    {
        $media = $block->getSetting('mediaId', null);

        if ($media) {
            $media = $this->mediaManager->findOneBy(array('id' => $media));
        }

        $block->setSetting('mediaId', $media);
    }

    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('mediaId', is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('mediaId', is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
    }
}
