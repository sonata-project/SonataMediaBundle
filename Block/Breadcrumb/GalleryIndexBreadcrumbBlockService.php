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
class GalleryIndexBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    public function getName()
    {
        return 'Breadcrumb Index: Media Gallery';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = $this->getRootMenu($blockContext);

        return $menu;
    }
}
