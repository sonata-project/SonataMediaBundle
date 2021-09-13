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
 * BlockService for view Media.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class MediaViewBreadcrumbBlockService extends BaseGalleryBreadcrumbBlockService
{
    public function getName(): string
    {
        return 'Breadcrumb View: Media';
    }

    public function getContext(): string
    {
        return 'media_view';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'media' => false,
        ]);
    }

    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $menu = $this->getRootMenu($blockContext);
        $media = $blockContext->getBlock()->getSetting('media');

        if (null !== $media) {
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
