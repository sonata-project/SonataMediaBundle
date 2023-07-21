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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class MediaBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @param AdminInterface<MediaInterface>|null $mediaAdmin
     */
    public function __construct(
        Environment $twig,
        private Pool $pool,
        private ?AdminInterface $mediaAdmin,
        private MediaManagerInterface $mediaManager
    ) {
        parent::__construct($twig);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'media' => false,
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'context' => false,
            'mediaId' => null,
            'format' => false,
            'template' => '@SonataMedia/Block/block_media.html.twig',
        ]);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        if (!$block->getSetting('mediaId') instanceof MediaInterface) {
            $this->load($block);
        }

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
                [$this->getMediaBuilder(), null, []],
                ['format', ChoiceType::class, [
                    'required' => \count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        // make sure we have a valid format
        $media = $blockContext->getBlock()->getSetting('mediaId');

        if ($media instanceof MediaInterface) {
            $choices = $this->getFormatChoices($media);

            if (!\array_key_exists($blockContext->getSetting('format'), $choices)) {
                $blockContext->setSetting('format', key($choices));
            }
        }

        $template = $blockContext->getTemplate();

        return $this->renderResponse($template, [
            'media' => $blockContext->getSetting('mediaId'),
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }

    public function load(BlockInterface $block): void
    {
        $mediaId = $block->getSetting('mediaId');

        if (null === $mediaId || $mediaId instanceof MediaInterface) {
            return;
        }

        $media = $this->mediaManager->findOneBy(['id' => $mediaId]);

        if (null === $media) {
            return;
        }

        $block->setSetting('mediaId', $media);
    }

    public function prePersist(BlockInterface $block): void
    {
        $block->setSetting('mediaId', $block->getSetting('mediaId') instanceof MediaInterface ? $block->getSetting('mediaId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        $block->setSetting('mediaId', $block->getSetting('mediaId') instanceof MediaInterface ? $block->getSetting('mediaId')->getId() : null);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.media.block.media', null, null, 'SonataMediaBundle', [
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

    /**
     * @return array<string, string>
     */
    private function getFormatChoices(?MediaInterface $media = null): array
    {
        if (!$media instanceof MediaInterface) {
            return [];
        }

        $context = $media->getContext();

        if (null === $context || !$this->pool->hasContext($context)) {
            return [];
        }

        $formatChoices = [];
        $formats = $this->pool->getFormatNamesByContext($context);

        foreach ($formats as $code => $format) {
            $formatChoices[$code] = $code;
        }

        return $formatChoices;
    }

    private function getMediaBuilder(): FormBuilderInterface
    {
        if (null === $this->mediaAdmin) {
            throw new \LogicException('The SonataAdminBundle is required to render the edit form.');
        }

        $fieldDescription = $this->mediaAdmin->createFieldDescription('media', [
            'translation_domain' => 'SonataMediaBundle',
            'edit' => 'list',
        ]);

        return $this->mediaAdmin->getFormBuilder()->create('mediaId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->mediaAdmin->getClass(),
            'model_manager' => $this->mediaAdmin->getModelManager(),
            'label' => 'form.label_media',
        ]);
    }
}
