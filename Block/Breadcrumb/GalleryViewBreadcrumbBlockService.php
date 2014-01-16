<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;

/**
 * BlockService for view gallery.
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
        return 'sonata.media.block.breadcrumb_view';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        if ($gallery = $blockContext->getBlock()->getSetting('gallery')) {
            $menu->addChild($gallery->getName(), array(
                'route'           => 'sonata_media_gallery_view',
                'routeParameters' => array(
                    'id' => $gallery->getId(),
                ),
            ));
        }

        return $menu;
    }
}
