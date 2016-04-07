<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Extension;

use Sonata\FormatterBundle\Extension\BaseProxyExtension;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;

class FormatterMediaExtension extends BaseProxyExtension
{
    /**
     * @var \Twig_Extension
     */
    protected $twigExtension;

    /**
     * @param \Twig_Extension $twigExtension
     */
    public function __construct(\Twig_Extension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTags()
    {
        return array(
            'media',
            'path',
            'thumbnail',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return array(
            'Sonata\MediaBundle\Model\MediaInterface' => array(
                'getproviderreference',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser($this->getName()),
            new ThumbnailTokenParser($this->getName()),
            new PathTokenParser($this->getName()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTwigExtension()
    {
        return $this->twigExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_formatter_media';
    }

    /**
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function media($media = null, $format, $options = array())
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function thumbnail($media = null, $format, $options = array())
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * @param int    $media
     * @param string $format
     *
     * @return string
     */
    public function path($media = null, $format)
    {
        return $this->getTwigExtension()->path($media, $format);
    }
}
