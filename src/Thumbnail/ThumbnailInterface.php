<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

interface ThumbnailInterface
{
    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string                 $format
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string                 $format
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string|array           $formats
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null);
}
