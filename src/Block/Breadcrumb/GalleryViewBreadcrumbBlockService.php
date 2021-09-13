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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * NEXT_MAJOR: remove this file.
 *
 * BlockService for view gallery.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class GalleryViewBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    public function getName(): string
    {
        return 'Breadcrumb View: Media Gallery';
    }

    public function getContext(): string
    {
        return 'gallery_view';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'gallery' => false,
        ]);
    }

    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $menu = $this->getRootMenu($blockContext);
        $gallery = $blockContext->getBlock()->getSetting('gallery');

        if (null !== $gallery) {
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
