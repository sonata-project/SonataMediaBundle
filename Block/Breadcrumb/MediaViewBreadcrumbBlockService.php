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
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        if ($media = $blockContext->getBlock()->getSetting('media')) {
            $menu->addChild($media->getName(), array(
                'route'           => 'sonata_media_view',
                'routeParameters' => array(
                    'id' => $media->getId(),
                ),
            ));
        }

        return $menu;
    }
}
