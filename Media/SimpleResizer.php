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
use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;

class SimpleResizer implements ResizerInterface
{
    protected $adapterClass;

    protected $mode;

    /**
     * @param \Imagine\Image\ImagineInterface $adapter
     * @param string $mode
     */
    public function __construct(ImagineInterface $adapter, $mode)
    {
        $this->adapter = $adapter;
        $this->mode    = $mode;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * @return void
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, $settings)
    {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        $image = $this->getAdapter()->load($in->getContent());

        if ($settings['height'] == null) {
            $size = $image->getSize();
            $settings['height'] = (int) ($settings['width'] * $size->getHeight() / $size->getWidth());
        }

        $content = $image
            ->thumbnail(new Box($settings['width'], $settings['height']), $this->getMode())
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

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}