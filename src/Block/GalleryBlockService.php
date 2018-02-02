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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Form\Type\ImmutableArrayType;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GalleryBlockService extends AbstractAdminBlockService
{
    /**
     * @var ManagerInterface
     */
    protected $galleryAdmin;

    /**
     * @var ManagerInterface
     */
    protected $galleryManager;

    /**
     * @param string             $name
     * @param EngineInterface    $templating
     * @param ContainerInterface $container
     * @param ManagerInterface   $galleryManager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container, ManagerInterface $galleryManager)
    {
        parent::__construct($name, $templating);

        $this->galleryManager = $galleryManager;
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getMediaPool()
    {
        return $this->container->get('sonata.media.pool');
    }

    /**
     * @return AdminInterface
     */
    public function getGalleryAdmin()
    {
        if (!$this->galleryAdmin) {
            $this->galleryAdmin = $this->container->get('sonata.media.admin.gallery');
        }

        return $this->galleryAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'gallery' => false,
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'context' => false,
            'format' => false,
            'pauseTime' => 3000,
            'startPaused' => false,
            'wrap' => true,
            'template' => '@SonataMedia/Block/block_gallery.html.twig',
            'galleryId' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $contextChoices = [];

        foreach ($this->getMediaPool()->getContexts() as $name => $context) {
            $contextChoices[$name] = $name;
        }

        $gallery = $block->getSetting('galleryId');

        $formatChoices = [];

        if ($gallery instanceof GalleryInterface) {
            $formats = $this->getMediaPool()->getFormatNamesByContext($gallery->getContext());

            foreach ($formats as $code => $format) {
                $formatChoices[$code] = $code;
            }
        }

        // simulate an association ...
        $fieldDescription = $this->getGalleryAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->getGalleryAdmin()->getClass(), 'media', [
            'translation_domain' => 'SonataMediaBundle',
        ]);
        $fieldDescription->setAssociationAdmin($this->getGalleryAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(['fieldName' => 'gallery', 'type' => ClassMetadataInfo::MANY_TO_ONE]);

        $builder = $formMapper->create('galleryId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getGalleryAdmin()->getClass(),
            'model_manager' => $this->getGalleryAdmin()->getModelManager(),
            'label' => 'form.label_gallery',
        ]);

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
                ['context', ChoiceType::class, [
                    'required' => true,
                    'choices' => $contextChoices,
                    'label' => 'form.label_context',
                ]],
                ['format', ChoiceType::class, [
                    'required' => count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
                [$builder, null, []],
                ['pauseTime', NumberType::class, [
                    'label' => 'form.label_pause_time',
                ]],
                ['startPaused', CheckboxType::class, [
                    'required' => false,
                    'label' => 'form.label_start_paused',
                ]],
                ['wrap', CheckboxType::class, [
                    'required' => false,
                    'label' => 'form.label_wrap',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $gallery = $blockContext->getBlock()->getSetting('galleryId');

        return $this->renderResponse($blockContext->getTemplate(), [
            'gallery' => $gallery,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'elements' => $gallery ? $this->buildElements($gallery) : [],
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $gallery = $block->getSetting('galleryId');

        if ($gallery) {
            $gallery = $this->galleryManager->findOneBy(['id' => $gallery]);
        }

        $block->setSetting('galleryId', $gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('galleryId', is_object($block->getSetting('galleryId')) ? $block->getSetting('galleryId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('galleryId', is_object($block->getSetting('galleryId')) ? $block->getSetting('galleryId')->getId() : null);
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

    /**
     * @param GalleryInterface $gallery
     *
     * @return array
     */
    private function buildElements(GalleryInterface $gallery)
    {
        $elements = [];
        foreach ($gallery->getGalleryHasMedias() as $galleryHasMedia) {
            if (!$galleryHasMedia->getEnabled()) {
                continue;
            }

            $type = $this->getMediaType($galleryHasMedia->getMedia());

            if (!$type) {
                continue;
            }

            $elements[] = [
                'title' => $galleryHasMedia->getMedia()->getName(),
                'caption' => $galleryHasMedia->getMedia()->getDescription(),
                'type' => $type,
                'media' => $galleryHasMedia->getMedia(),
            ];
        }

        return $elements;
    }

    /**
     * @param MediaInterface $media
     *
     * @return false|string
     */
    private function getMediaType(MediaInterface $media)
    {
        if ('video/x-flv' == $media->getContentType()) {
            return 'video';
        } elseif ('image' == substr($media->getContentType(), 0, 5)) {
            return 'image';
        }

        return false;
    }
}
