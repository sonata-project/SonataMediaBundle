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

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class GalleryListBlockService extends AbstractBlockService implements EditableBlockService
{
    public function __construct(
        Environment $twig,
        private GalleryManagerInterface $galleryManager,
        private Pool $pool
    ) {
        parent::__construct($twig);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $contextChoices = [];

        foreach ($this->pool->getContexts() as $name => $context) {
            $contextChoices[$name] = $name;
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
                ['number', IntegerType::class, [
                    'label' => 'form.label_number',
                    'required' => true,
                ]],
                ['context', ChoiceType::class, [
                    'required' => true,
                    'label' => 'form.label_context',
                    'choices' => $contextChoices,
                ]],
                ['mode', ChoiceType::class, [
                    'label' => 'form.label_mode',
                    'choices' => [
                        'form.label_mode_public' => 'public',
                        'form.label_mode_admin' => 'admin',
                    ],
                ]],
                ['order', ChoiceType::class,  [
                    'label' => 'form.label_order',
                    'choices' => [
                        'form.label_order_name' => 'name',
                        'form.label_order_created_at' => 'createdAt',
                        'form.label_order_updated_at' => 'updatedAt',
                    ],
                ]],
                ['sort', ChoiceType::class, [
                    'label' => 'form.label_sort',
                    'choices' => [
                        'form.label_sort_desc' => 'desc',
                        'form.label_sort_asc' => 'asc',
                    ],
                ]],
            ],
            'translation_domain' => 'SonataMediaBundle',
        ]);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $context = $blockContext->getBlock()->getSetting('context');

        $criteria = [
            'mode' => $blockContext->getSetting('mode'),
            'context' => $context,
        ];

        $order = $blockContext->getSetting('order');
        \assert(\is_string($order));
        $sortDirection = $blockContext->getSetting('sort');
        \assert(\in_array($sortDirection, ['asc', 'desc'], true));
        $template = $blockContext->getTemplate();

        return $this->renderResponse($template, [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'galleries' => $this->galleryManager->findBy(
                $criteria,
                [$order => $sortDirection],
                $blockContext->getSetting('number')
            ),
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-images',
            'class' => null,
            'template' => '@SonataMedia/Block/block_gallery_list.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.media.block.gallery_list', null, null, 'SonataMediaBundle', [
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
}
