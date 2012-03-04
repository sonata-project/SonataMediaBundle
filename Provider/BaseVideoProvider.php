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

use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Media\ResizerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Buzz\Browser;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\FormBuilder;

abstract class BaseVideoProvider extends BaseProvider
{
    protected $browser;

    /**
     * @param $name
     * @param \Gaufrette\Filesystem $filesystem
     * @param \Sonata\MediaBundle\CDN\CDNInterface $cdn
     * @param \Sonata\MediaBundle\Generator\GeneratorInterface $pathGenerator
     * @param \Sonata\MediaBundle\Thumbnail\ThumbnailInterface $thumbnail
     * @param \Buzz\Browser $browser
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, Browser $browser)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        $this->browser = $browser;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceFile(MediaInterface $media)
    {
        $key = $this->generatePrivateUrl($media, 'reference');

        // the reference file is remote, get it and store it with the 'reference' format
        if ($this->getFilesystem()->has($key)) {
            $referenceFile = $this->getFilesystem()->get($key);
        } else {
            $referenceFile = $this->getFilesystem()->get($key, true);
            $referenceFile->setContent(file_get_contents($this->getReferenceImage($media)));
        }

        return $referenceFile;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        return $this->getCdn()->getPath(sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        ), $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', 'text', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function buildMediaType(FormBuilder $formBuilder)
    {
        $formBuilder->add('binaryContent', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(MediaInterface $media)
    {
        $this->postPersist($media);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $this->generateThumbnails($media);
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(MediaInterface $media)
    {
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $url
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, $url)
    {
        try {
            $response = $this->browser->get($url);
        } catch(\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :' . $url, null, $e);
        }

        $metadata = json_decode($response->getContent(), true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :' . $url);
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        $format_configuration = $this->getFormat($format);

        $width = $media->getWidth();
        $height = $media->getHeight();

        if (isset($format_configuration['width']) && isset($format_configuration['height'])) {
            $width = $format_configuration['width'];
            $height = $format_configuration['height'];
        }
        else if (isset($format_configuration['height'])) {
            $width *= $format_configuration['height'];
            $width /= $height;
            $height = $format_configuration['height'];
        }
        else if (isset($format_configuration['width'])) {
            $height *= $format_configuration['width'];
            $height /= $width;
            $width = $format_configuration['width'];
        }

        $params = array(
            'width'             => isset($options['width'])             ? $options['width']  : $width,
            'height'            => isset($options['height'])            ? $options['height'] : $height,
        );

        return $params;
    }
}
