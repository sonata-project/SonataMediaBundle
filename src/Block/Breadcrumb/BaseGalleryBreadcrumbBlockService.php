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
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;

/**
 * NEXT_MAJOR: remove this file.
 *
 * Abstract class for media breadcrumbs.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.34, to be removed in 4.0.
 */
abstract class BaseGalleryBreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    protected function getRootMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getRootMenu($blockContext);

        $menu->addChild('sonata_media_gallery_index', [
            'route' => 'sonata_media_gallery_index',
            'extras' => ['translation_domain' => 'SonataMediaBundle'],
        ]);

        return $menu;
    }
}
