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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GalleryBlockService extends AbstractBlockService implements EditableBlockService
{
    private Pool $pool;

    /**
     * @var AdminInterface<GalleryInterface<GalleryItemInterface>>|null
     */
    private ?AdminInterface $galleryAdmin;

    private GalleryManagerInterface $galleryManager;

    /**
     * @param AdminInterface<GalleryInterface<GalleryItemInterface>>|null $galleryAdmin
     */
    public function __construct(
        Environment $twig,
        Pool $pool,
        ?AdminInterface $galleryAdmin,
        GalleryManagerInterface $galleryManager
    ) {
        parent::__construct($twig);

        $this->pool = $pool;
        $this->galleryAdmin = $galleryAdmin;
        $this->galleryManager = $galleryManager;
    }

    public function configureSettings(OptionsResolver $resolver): void
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
            'template' => '@SonataMedia/Block/block_gallery.html.twig',
            'galleryId' => null,
        ]);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $contextChoices = [];

        foreach ($this->pool->getContexts() as $name => $context) {
            $contextChoices[$name] = $name;
        }

        $gallery = $block->getSetting('galleryId');

        $formatChoices = [];

        if ($gallery instanceof GalleryInterface) {
            $context = $gallery->getContext();

            if (null !== $context && $this->pool->hasContext($context)) {
                $formats = $this->pool->getFormatNamesByContext($context);

                foreach ($formats as $code => $format) {
                    $formatChoices[$code] = $code;
                }
            }
        }

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
                ['context', ChoiceType::class, [
                    'required' => true,
                    'choices' => $contextChoices,
                    'label' => 'form.label_context',
                ]],
                ['format', ChoiceType::class, [
                    'required' => \count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
                [$this->getGalleryBuilder(), null, []],
                ['pauseTime', NumberType::class, [
                    'label' => 'form.label_pause_time',
                ]],
                ['startPaused', CheckboxType::class, [
                    'required' => false,
                    'label' => 'form.label_start_paused',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $gallery = $blockContext->getBlock()->getSetting('galleryId');
        $template = $blockContext->getTemplate();
        \assert(\is_string($template));

        return $this->renderResponse($template, [
            'gallery' => $gallery,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'elements' => null !== $gallery ? $this->buildElements($gallery) : [],
        ], $response);
    }

    public function load(BlockInterface $block): void
    {
        $galleryId = $block->getSetting('galleryId');

        if (null === $galleryId || $galleryId instanceof GalleryInterface) {
            return;
        }

        $gallery = $this->galleryManager->findOneBy(['id' => $galleryId]);

        if (null === $gallery) {
            return;
        }

        $block->setSetting('galleryId', $gallery);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.media.block.gallery', null, null, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    private function getGalleryBuilder(): FormBuilderInterface
    {
        if (null === $this->galleryAdmin) {
            throw new \LogicException('The SonataAdminBundle is required to render the edit form.');
        }

        $fieldDescription = $this->galleryAdmin->createFieldDescription('gallery', [
            'translation_domain' => 'SonataMediaBundle',
            'edit' => 'list',
        ]);

        return $this->galleryAdmin->getFormBuilder()->create('galleryId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->galleryAdmin->getClass(),
            'model_manager' => $this->galleryAdmin->getModelManager(),
            'label' => 'form.label_gallery',
        ]);
    }

    /**
     * @return array<string, MediaInterface|string|null>
     *
     * @phpstan-param GalleryInterface<GalleryItemInterface> $gallery
     * @phpstan-return array{
     *     title: string|null,
     *     caption: string|null,
     *     type: string,
     *     media: MediaInterface
     * }[]
     */
    private function buildElements(GalleryInterface $gallery): array
    {
        $elements = [];
        foreach ($gallery->getGalleryItems() as $galleryItem) {
            if (!$galleryItem->getEnabled()) {
                continue;
            }

            $media = $galleryItem->getMedia();

            if (null === $media) {
                continue;
            }

            $type = $this->getMediaType($media);

            if (null === $type) {
                continue;
            }

            $elements[] = [
                'title' => $media->getName(),
                'caption' => $media->getDescription(),
                'type' => $type,
                'media' => $media,
            ];
        }

        return $elements;
    }

    private function getMediaType(MediaInterface $media): ?string
    {
        $contentType = $media->getContentType();

        if (null === $contentType) {
            return null;
        }

        if ('video/x-flv' === $contentType) {
            return 'video';
        }

        if ('image' === substr($contentType, 0, 5)) {
            return 'image';
        }

        return null;
    }
}
