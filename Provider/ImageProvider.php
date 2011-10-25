<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;

class ImageProvider extends FileProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $format
     * @param array $options
     * @return array
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        $format_configuration = $this->getFormat($format);

        return array_merge(array(
          'title'    => $media->getName(),
          'src'      => $this->generatePublicUrl($media, $format),
          'width'    => $format_configuration['width'],
        ), $options);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf('%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $this->getReferenceImage($media);
        } else {
            $path = $this->generateFullPath($media, $format, 'thumb', 'jpg');
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return $this->generateFullPath($media, $format, 'thumb', 'jpg');
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preRemove(MediaInterface $media)
    {
        $path = $this->getReferenceImage($media);

        if ($this->getFilesystem()->has($path)) {
            $this->getFilesystem()->delete($path);
        }

        // delete the differents formats
        foreach ($this->formats as $format => $definition) {
            $path = $this->generatePrivateUrl($media, $format);
            if ($this->getFilesystem()->has($path)) {
                $this->getFilesystem()->delete($path);
            }
        }
    }
}