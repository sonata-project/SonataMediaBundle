<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Resizer;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;
use Imagine\Exception\InvalidArgumentException;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;

class SimpleResizer implements ResizerInterface
{
    protected $adapter;

    protected $mode;

    protected $metadata;

    /**
     * @param ImagineInterface $adapter
     * @param string           $mode
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct(ImagineInterface $adapter, $mode, MetadataBuilderInterface $metadata)
    {
        $this->adapter  = $adapter;
        $this->mode     = $mode;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings)
    {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        $image = $this->adapter->load($in->getContent());

        $content = $this
            ->thumbnail($image, $this->getBox($media, $settings), $this->mode, $this->computeRatio($media, $settings))
            ->get($format, array('quality' => $settings['quality']));

        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    /**
     * {@inheritdoc}
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        $size = $media->getBox();

        return $size->scale($this->computeRatio($media, $settings));
    }

    /**
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     *
     * @param MediaInterface $media
     * @param array          $settings
     *
     * @return float         $ratio
     */
    private function computeRatio(MediaInterface $media, array $settings)
    {
        if ($this->mode !== ImageInterface::THUMBNAIL_INSET && $this->mode !== ImageInterface::THUMBNAIL_OUTBOUND) {
            throw new InvalidArgumentException('Invalid mode specified');
        }

        $size = $media->getBox();

        if ($settings['width'] == null && $settings['height'] == null) {
            throw new \RuntimeException(sprintf('Width/Height parameter is missing in context "%s" for provider "%s". Please add at least one parameter.', $media->getContext(), $media->getProviderName()));
        }

        if ($settings['height'] == null) {
            $settings['height'] = $settings['width'] * $size->getHeight() / $size->getWidth();
        }

        if ($settings['width'] == null) {
            $settings['width'] = $settings['height'] * $size->getWidth() / $size->getHeight();
        }

        $ratios = array(
            $settings['width'] / $size->getWidth(),
            $settings['height'] / $size->getHeight()
        );

        if ($this->mode === ImageInterface::THUMBNAIL_INSET) {
            $ratio = min($ratios);
        } else {
            $ratio = max($ratios);
        }

        return $ratio;
    }


    /**
     * @param ImageInterface $image
     * @param BoxInterface $size
     * @param string $mode
     * @param float $ratio
     *
     * @return \Imagine\Image\ManipulatorInterface
     */
    private function thumbnail(ImageInterface $image, BoxInterface $size, $mode = ImageInterface::THUMBNAIL_INSET, $ratio)
    {

        $imageSize = $image->getSize();
        $thumbnail = $image->copy();

        // if target width is larger than image width
        // AND target height is longer than image height
        if ($size->contains($imageSize)) {
            return $thumbnail;
        }

        if ($mode === ImageInterface::THUMBNAIL_OUTBOUND) {
            if (!$imageSize->contains($size)) {
                $size = new Box(
                    min($imageSize->getWidth(), $size->getWidth()),
                    min($imageSize->getHeight(), $size->getHeight())
                );
            } else {
                $imageSize = $thumbnail->getSize()->scale($ratio);
                $thumbnail->resize($imageSize);
            }
            $thumbnail->crop(new Point(
                max(0, round(($imageSize->getWidth() - $size->getWidth()) / 2)),
                max(0, round(($imageSize->getHeight() - $size->getHeight()) / 2))
            ), $size);
        } else {
            if (!$imageSize->contains($size)) {
                $imageSize = $imageSize->scale($ratio);
                $thumbnail->resize($imageSize);
            } else {
                $imageSize = $thumbnail->getSize()->scale($ratio);
                $thumbnail->resize($imageSize);
            }
        }

        return $thumbnail;
    }
}
