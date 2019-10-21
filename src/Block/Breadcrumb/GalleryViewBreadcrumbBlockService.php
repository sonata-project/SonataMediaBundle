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

namespace Sonata\MediaBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BlockService for view gallery.
 *
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class GalleryViewBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb View: Media Gallery';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'gallery' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        if ($gallery = $blockContext->getBlock()->getSetting('gallery')) {
            $menu->addChild($gallery->getName(), [
                'route' => 'sonata_media_gallery_view',
                'routeParameters' => [
                    'id' => $gallery->getId(),
                ],
            ]);
        }

        return $menu;
    }
}
