<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Media;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;

class SimpleCropper implements CropperInterface
{
    protected $adapterClass;

    /**
     * @param \Imagine\Image\ImagineInterface $adapter
     * @param string $mode
     */
    public function __construct(ImagineInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * @return void
     */
    public function crop(MediaInterface $media, File $in, File $out, $format, $settings)
    {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderClass()));
        }

        $image = $this->getAdapter()->load($in->getContent());

        $content = $image
            ->crop(new Point($settings['x'], $settings['y']), $settings['width'], $settings['height'])
            ->get($format);

        $out->setContent($content);
    }

    /**
     *
     * @return \Imagine\Image\ImagineInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}