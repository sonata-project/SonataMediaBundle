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

namespace Sonata\MediaBundle\Model;

/**
 * This a workaround to maintain BC within SonataMediaBundle v3.
 * NEXT_MAJOR: remove this interface, move all methods into GalleryInterface.
 */
interface GalleryMediaCollectionInterface
{
    public function addGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia);

    public function removeGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia);
}
