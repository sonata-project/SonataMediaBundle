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
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseMediaBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @var BaseMediaAdmin
     */
    protected $mediaAdmin;

    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    public function __construct(
        Environment $twig,
        ManagerInterface $mediaManager,
        BaseMediaAdmin $mediaAdmin
    ) {
        parent::__construct($twig);

        $this->mediaManager = $mediaManager;
        $this->mediaAdmin = $mediaAdmin;
    }

    public function getMediaPool(): Pool
    {
        return $this->getMediaAdmin()->getPool();
    }

    public function getMediaAdmin(): BaseMediaAdmin
    {
        return $this->mediaAdmin;
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

    public function configureCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $this->configureEditForm($formMapper, $block);
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

    protected function getMediaBuilder(FormMapper $formMapper): FormBuilderInterface
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media', [
            'translation_domain' => 'SonataMediaBundle',
        ]);
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
            'fieldName' => 'media',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);

        return $formMapper->create('mediaId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getMediaAdmin()->getClass(),
            'model_manager' => $this->getMediaAdmin()->getModelManager(),
            'label' => 'form.label_media',
        ]);
    }
}
