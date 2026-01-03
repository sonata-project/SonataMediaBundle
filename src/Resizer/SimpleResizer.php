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

use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use League\Flysystem\FilesystemOperator;
use Sonata\MediaBundle\Model\MediaInterface;

final class SimpleResizer implements ResizerInterface
{
    public function __construct(
        private ImagineInterface $adapter,
        private int $mode,
    ) {
    }

    public function resize(FilesystemOperator $filesystem, MediaInterface $media, string $in, string $out, string $format, array $settings): void
    {
        if (!isset($settings['width']) && !isset($settings['height'])) {
            throw new \RuntimeException(\sprintf(
                'Width or height parameter is missing in context "%s" for provider "%s"',
                $media->getContext() ?? '',
                $media->getProviderName() ?? ''
            ));
        }

        $image = $this->adapter->load($filesystem->read($in));

        $content = $image
            ->thumbnail($this->getBox($media, $settings), $this->mode)
            ->get($format, ['quality' => $settings['quality']]);

        $filesystem->write($out, $content);
    }

    public function getBox(MediaInterface $media, array $settings): Box
    {
        $size = $media->getBox();

        $width = $settings['width'];
        $height = $settings['height'];

        if (null === $width && null === $height) {
            throw new \RuntimeException(\sprintf(
                'Width/Height parameter is missing in context "%s" for provider "%s". Please add at least one parameter.',
                $media->getContext() ?? '',
                $media->getProviderName() ?? ''
            ));
        }

        if (null === $height) {
            $height = max((int) round($width * $size->getHeight() / $size->getWidth()), 1);
        }

        if (null === $width) {
            $width = max((int) round($height * $size->getWidth() / $size->getHeight()), 1);
        }

        return $this->computeBox($media, $width, $height);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function computeBox(MediaInterface $media, int $width, int $height): Box
    {
        if (!(0 !== ($this->mode & ManipulatorInterface::THUMBNAIL_INSET) || 0 !== ($this->mode & ManipulatorInterface::THUMBNAIL_OUTBOUND))) {
            throw new InvalidArgumentException('Invalid mode specified');
        }

        $size = $media->getBox();

        $ratios = [
            $width / $size->getWidth(),
            $height / $size->getHeight(),
        ];

        if (0 !== ($this->mode & ManipulatorInterface::THUMBNAIL_INSET)) {
            $ratio = min($ratios);
        } else {
            $ratio = max($ratios);
        }

        $scaledBox = $size->scale($ratio);

        return new Box(
            min($scaledBox->getWidth(), $width),
            min($scaledBox->getHeight(), $height)
        );
    }
}
