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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FeatureMediaBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminInterface<MediaInterface>
     */
    private $mediaAdmin;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @param AdminInterface<MediaInterface> $mediaAdmin
     */
    public function __construct(
        Environment $twig,
        Pool $pool,
        AdminInterface $mediaAdmin,
        MediaManagerInterface $mediaManager
    ) {
        parent::__construct($twig);

        $this->pool = $pool;
        $this->mediaAdmin = $mediaAdmin;
        $this->mediaManager = $mediaManager;
    }

    public function configureSettings(OptionsResolver $resolver): void
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

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
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
                ['content', TextareaType::class, [
                    'required' => false,
                    'label' => 'form.label_content',
                ]],
                ['orientation', ChoiceType::class, [
                    'required' => false,
                    'choices' => [
                        'form.label_orientation_left' => 'left',
                        'form.label_orientation_right' => 'right',
                    ],
                    'label' => 'form.label_orientation',
                ]],
                [$this->getMediaBuilder($form), null, []],
                ['format', ChoiceType::class, [
                    'required' => \count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
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
        \assert(\is_string($template));

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
        return new Metadata('sonata.media.block.feature_media', null, null, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
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

    private function getMediaBuilder(FormMapper $form): FormBuilderInterface
    {
        $fieldDescription = $this->mediaAdmin->createFieldDescription('media', [
            'translation_domain' => 'SonataMediaBundle',
            'edit' => 'list',
        ]);

        return $form->create('mediaId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->mediaAdmin->getClass(),
            'model_manager' => $this->mediaAdmin->getModelManager(),
            'label' => 'form.label_media',
        ]);
    }
}
