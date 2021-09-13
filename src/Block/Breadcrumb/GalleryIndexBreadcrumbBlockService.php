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

use Knp\Menu\ItemInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;

/**
 * NEXT_MAJOR: remove this file.
 *
 * BlockService for view gallery.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class GalleryIndexBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    public function getName(): string
    {
        return 'Breadcrumb Index: Media Gallery';
    }

    public function getContext(): string
    {
        return 'gallery_index';
    }

    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $menu = $this->getRootMenu($blockContext);

        return $menu;
    }
}
