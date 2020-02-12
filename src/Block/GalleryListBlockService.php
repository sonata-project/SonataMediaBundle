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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class GalleryListBlockService extends AbstractBlockService
{
    /**
     * @var GalleryManagerInterface
     */
    protected $galleryManager;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * NEXT_MAJOR: Remove `$templating` argument.
     *
     * @param Environment|string $twigOrName
     */
    public function __construct($twigOrName, ?EngineInterface $templating, GalleryManagerInterface $galleryManager, Pool $pool)
    {
        parent::__construct($twigOrName, $templating);

        $this->galleryManager = $galleryManager;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $contextChoices = [];

        foreach ($this->pool->getContexts() as $name => $context) {
            $contextChoices[$name] = $name;
        }

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
                        'public' => 'form.label_mode_public',
                        'admin' => 'form.label_mode_admin',
                    ],
                ]],
                ['order', ChoiceType::class,  [
                    'label' => 'form.label_order',
                    'choices' => [
                        'name' => 'form.label_order_name',
                        'createdAt' => 'form.label_order_created_at',
                        'updatedAt' => 'form.label_order_updated_at',
                    ],
                ]],
                ['sort', ChoiceType::class, [
                    'label' => 'form.label_sort',
                    'choices' => [
                        'desc' => 'form.label_sort_desc',
                        'asc' => 'form.label_sort_asc',
                    ],
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
        $context = $blockContext->getBlock()->getSetting('context');

        $criteria = [
            'mode' => $blockContext->getSetting('mode'),
            'context' => $context,
        ];

        $order = [
            $blockContext->getSetting('order') => $blockContext->getSetting('sort'),
        ];

        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'pager' => $this->galleryManager->getPager(
                $criteria,
                1,
                $blockContext->getSetting('number'),
                $order
            ),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
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

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
    }
}
