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
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PageExtension
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaBlockService extends BaseBlockService
{
    protected $mediaAdmin;

    protected $mediaManager;

    /**
     * @param string             $name
     * @param EngineInterface    $templating
     * @param ContainerInterface $container
     * @param ManagerInterface   $mediaManager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container, ManagerInterface $mediaManager)
    {
        parent::__construct($name, $templating);

        $this->mediaManager = $mediaManager;
        $this->container    = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Media';
    }

    /**
     * @return mixed
     */
    public function getMediaPool()
    {
        return $this->getMediaAdmin()->getPool();
    }

    /**
     * @return mixed
     */
    public function getMediaAdmin()
    {
        if (!$this->mediaAdmin) {
            $this->mediaAdmin = $this->container->get('sonata.media.admin.media');
        }

        return $this->mediaAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'media'    => false,
            'title'    => false,
            'context'  => false,
            'mediaId'  => null,
            'format'   => false,
            'template' => 'SonataMediaBundle:Block:block_media.html.twig'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $contextChoices = $this->getContextChoices();

        if (!$block->getSetting('mediaId') instanceof MediaInterface) {
            $this->load($block);
        }

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
     *
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

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function getMediaBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media');
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array(
            'fieldName' => 'media',
            'type'      => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE
        ));

        return $formMapper->create('mediaId', 'sonata_type_model', array(
            'sonata_field_description' => $fieldDescription,
            'class'                    => $this->getMediaAdmin()->getClass(),
            'model_manager'            => $this->getMediaAdmin()->getModelManager()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            'media'     => $blockContext->getSetting('mediaId'),
            'block'     => $blockContext->getBlock(),
            'settings'  => $blockContext->getSettings()
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $media = $block->getSetting('mediaId', null);

        if ($media) {
            $media = $this->mediaManager->findOneBy(array('id' => $media));
        }

        $block->setSetting('mediaId', $media);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('mediaId', is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('mediaId', is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
    }
}
