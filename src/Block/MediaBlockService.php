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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @var BaseMediaAdmin
     */
    protected $mediaAdmin;

    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        Environment $twig,
        ContainerInterface $container,
        ManagerInterface $mediaManager
    ) {
        parent::__construct($twig);

        $this->mediaManager = $mediaManager;
        $this->container = $container;
    }

    public function getMediaPool(): Pool
    {
        return $this->getMediaAdmin()->getPool();
    }

    public function getMediaAdmin(): BaseMediaAdmin
    {
        if (!$this->mediaAdmin) {
            $this->mediaAdmin = $this->container->get('sonata.media.admin.media');
        }

        return $this->mediaAdmin;
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

        return $this->renderResponse($blockContext->getTemplate(), [
            'media' => $blockContext->getSetting('mediaId'),
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }

    public function load(BlockInterface $block): void
    {
        $media = $block->getSetting('mediaId', null);

        if (\is_int($media)) {
            $media = $this->mediaManager->findOneBy(['id' => $media]);
        }

        $block->setSetting('mediaId', $media);
    }

    public function prePersist(BlockInterface $block): void
    {
        $block->setSetting('mediaId', \is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        $block->setSetting('mediaId', \is_object($block->getSetting('mediaId')) ? $block->getSetting('mediaId')->getId() : null);
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

    protected function getFormatChoices(?MediaInterface $media = null): array
    {
        $formatChoices = [];

        if (!$media instanceof MediaInterface) {
            return $formatChoices;
        }

        $formats = $this->getMediaPool()->getFormatNamesByContext($media->getContext());

        foreach ($formats as $code => $format) {
            $formatChoices[$code] = $code;
        }

        return $formatChoices;
    }

    protected function getMediaBuilder(FormMapper $form): FormBuilderInterface
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media', [
            'translation_domain' => 'SonataMediaBundle',
        ]);
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($form->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
            'fieldName' => 'media',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);

        return $form->create('mediaId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getMediaAdmin()->getClass(),
            'model_manager' => $this->getMediaAdmin()->getModelManager(),
            'label' => 'form.label_media',
        ]);
    }
}
