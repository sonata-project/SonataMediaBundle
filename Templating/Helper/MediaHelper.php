<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Templating\Helper;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * MediaHelper manages action inclusions.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaHelper extends Helper
{
    /**
     * @var Pool|null
     */
    protected $pool = null;

    /**
     * @var EngineInterface|null
     */
    protected $templating = null;

    /**
     * @param Pool            $pool
     * @param EngineInterface $templating
     */
    public function __construct(Pool $pool, EngineInterface $templating)
    {
        $this->pool       = $pool;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_media';
    }

    /**
     * Returns the provider view for the provided media.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function media($media, $format, $options = array())
    {
        if (!$media) {
            return '';
        }

        $provider = $this->getProvider($media);

        $format = $provider->getFormatName($media, $format);

        $options = $provider->getHelperProperties($media, $format, $options);

        return $this->templating->render($provider->getTemplate('helper_view'), array(
             'media'    => $media,
             'format'   => $format,
             'options'  => $options,
        ));
    }

    /**
     * @param MediaInterface $media
     *
     * @return MediaProviderInterface
     */
    private function getProvider(MediaInterface $media)
    {
        return $this->pool->getProvider($media->getProviderName());
    }

    /**
     * Returns the thumbnail for the provided media.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = array())
    {
        if (!$media) {
            return '';
        }

        $provider = $this->getProvider($media);

        $format = $provider->getFormatName($media, $format);
        $formatDefinition = $provider->getFormat($format);

        // build option
        $options = array_merge(array(
            'title' => $media->getName(),
            'width' => $formatDefinition['width'],
        ), $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->getTemplating()->render($provider->getTemplate('helper_thumbnail'), array(
            'media'    => $media,
            'options'  => $options,
        ));
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        if (!$media) {
            return '';
        }

        $provider = $this->getProvider($media);

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }
}
