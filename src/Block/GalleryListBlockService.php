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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryListBlockService extends AbstractAdminBlockService
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
     * @param string                  $name
     * @param EngineInterface         $templating
     * @param GalleryManagerInterface $galleryManager
     * @param Pool                    $pool
     */
    public function __construct($name, EngineInterface $templating, GalleryManagerInterface $galleryManager, Pool $pool)
    {
        parent::__construct($name, $templating);

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

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $immutableArrayType = 'Sonata\CoreBundle\Form\Type\ImmutableArrayType';
            $textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';
            $integerType = 'Symfony\Component\Form\Extension\Core\Type\IntegerType';
            $choiceType = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
        } else {
            $immutableArrayType = 'sonata_type_immutable_array';
            $textType = 'text';
            $integerType = 'integer';
            $choiceType = 'choice';
        }

        $formMapper->add('settings', $immutableArrayType, [
            'keys' => [
                ['title', $textType, [
                    'label' => 'form.label_title',
                    'required' => false,
                ]],
                ['number', $integerType, [
                    'label' => 'form.label_number',
                    'required' => true,
                ]],
                ['context', $choiceType, [
                    'required' => true,
                    'label' => 'form.label_context',
                    'choices' => $contextChoices,
                ]],
                ['mode', $choiceType, [
                    'label' => 'form.label_mode',
                    'choices' => [
                        'public' => 'form.label_mode_public',
                        'admin' => 'form.label_mode_admin',
                    ],
                ]],
                ['order', $choiceType,  [
                    'label' => 'form.label_order',
                    'choices' => [
                        'name' => 'form.label_order_name',
                        'createdAt' => 'form.label_order_created_at',
                        'updatedAt' => 'form.label_order_updated_at',
                    ],
                ]],
                ['sort', $choiceType, [
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
            'title' => 'Gallery List',
            'template' => 'SonataMediaBundle:Block:block_gallery_list.html.twig',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataMediaBundle', [
            'class' => 'fa fa-picture-o',
        ]);
    }
}
