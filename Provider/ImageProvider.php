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

use Sonata\MediaBundle\Entity\BaseMedia as Media;

class ImageProvider extends FileProvider
{

    public function getHelperProperties(Media $media, $format, $options = array(), $settings = array())
    {
        $format_configuration = $this->getFormat($format);

        $base_media = $settings['cdn_enabled'] ? $settings['cdn_path'] : '';

         // the media is flushable, so we are working with a recent version not yet handled by the cdn
        if ($media->getCdnIsFlushable()) {
            $base_media = '';
        }

        return array_merge(array(
          'title'    => $media->getName(),
          'src'      => sprintf('%s%s', $base_media, $this->generatePublicUrl($media, $format)),
          'width'    => $format_configuration['width'],
        ), $options);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return string
     */
    public function getReferenceImage(Media $media)
    {

        return sprintf('%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return string
     */
    public function getAbsolutePath(Media $media)
    {

        return $this->getReferenceImage($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @param  $format
     * @return string
     */
    public function generatePublicUrl(Media $media, $format)
    {
        return $this->getCdn()->getPath(sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        ));
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @param  $format
     * @return string
     */
    public function generatePrivateUrl(Media $media, $format)
    {

        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        );
    }
}