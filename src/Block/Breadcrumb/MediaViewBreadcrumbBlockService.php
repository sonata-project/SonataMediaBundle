<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BlockService for view Media.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class MediaViewBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb View: Media';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'media' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        if ($media = $blockContext->getBlock()->getSetting('media')) {
            $menu->addChild($media->getName(), [
                'route' => 'sonata_media_view',
                'routeParameters' => [
                    'id' => $media->getId(),
                ],
            ]);
        }

        return $menu;
    }
}
