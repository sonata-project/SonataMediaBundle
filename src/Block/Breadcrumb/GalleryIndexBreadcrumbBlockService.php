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

/**
 * BlockService for view gallery.
 *
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class GalleryIndexBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    public function getName()
    {
        return 'Breadcrumb Index: Media Gallery';
    }

    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        return $menu;
    }
}
