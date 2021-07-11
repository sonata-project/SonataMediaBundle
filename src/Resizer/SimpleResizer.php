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

namespace Sonata\MediaBundle\Resizer;

use Gaufrette\File;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;

final class SimpleResizer implements ResizerInterface
{
    /**
     * @var ImagineInterface
     */
    private $adapter;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var MetadataBuilderInterface
     */
    private $metadata;

    public function __construct(ImagineInterface $adapter, int $mode, MetadataBuilderInterface $metadata)
    {
        $this->adapter = $adapter;
        $this->mode = $mode;
        $this->metadata = $metadata;
    }

    public function resize(MediaInterface $media, File $in, File $out, string $format, array $settings): void
    {
        if (!isset($settings['width']) && !isset($settings['height'])) {
            throw new \RuntimeException(sprintf('Width or height parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        $image = $this->adapter->load($in->getContent());

        $content = $image
            ->thumbnail($this->getBox($media, $settings), $this->mode)
            ->get($format, ['quality' => $settings['quality']]);

        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    public function getBox(MediaInterface $media, array $settings): Box
    {
        $size = $media->getBox();

        if (null === $settings['width'] && null === $settings['height']) {
            throw new \RuntimeException(sprintf('Width/Height parameter is missing in context "%s" for provider "%s". Please add at least one parameter.', $media->getContext(), $media->getProviderName()));
        }

        if (null === $settings['height']) {
            $settings['height'] = (int) round($settings['width'] * $size->getHeight() / $size->getWidth());
        }

        if (null === $settings['width']) {
            $settings['width'] = (int) round($settings['height'] * $size->getWidth() / $size->getHeight());
        }

        return $this->computeBox($media, $settings);
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @throws InvalidArgumentException
     */
    private function computeBox(MediaInterface $media, array $settings): Box
    {
        if (!($this->mode & ManipulatorInterface::THUMBNAIL_INSET || $this->mode & ManipulatorInterface::THUMBNAIL_OUTBOUND)) {
            throw new InvalidArgumentException('Invalid mode specified');
        }

        $size = $media->getBox();

        $ratios = [
            $settings['width'] / $size->getWidth(),
            $settings['height'] / $size->getHeight(),
        ];

        if ($this->mode & ManipulatorInterface::THUMBNAIL_INSET) {
            $ratio = min($ratios);
        } else {
            $ratio = max($ratios);
        }

        $scaledBox = $size->scale($ratio);

        return new Box(
            min($scaledBox->getWidth(), $settings['width']),
            min($scaledBox->getHeight(), $settings['height'])
        );
    }
}
