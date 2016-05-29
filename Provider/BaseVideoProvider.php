<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Buzz\Browser;
use Gaufrette\Filesystem;
use Imagine\Image\Box;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

abstract class BaseVideoProvider extends BaseProvider
{
    /**
     * @var Browser
     */
    protected $browser;

    /**
     * @var MetadataBuilderInterface
     */
    protected $metadata;

    /**
     * @param string                        $name
     * @param Filesystem                    $filesystem
     * @param CDNInterface                  $cdn
     * @param GeneratorInterface            $pathGenerator
     * @param ThumbnailInterface            $thumbnail
     * @param Browser                       $browser
     * @param MetadataBuilderInterface|null $metadata
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, Browser $browser, MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        // NEXT_MAJOR: remove this check!
        if (!method_exists($this, 'getReferenceUrl')) {
            @trigger_error('The method "getReferenceUrl" is required with the next major release.', E_USER_DEPRECATED);
        }

        $this->browser = $browser;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', array('class' => 'fa fa-video-camera'));
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
            $metadata = $this->metadata ? $this->metadata->get($media, $referenceFile->getName()) : array();
            $referenceFile->setContent($this->browser->get($this->getReferenceImage($media))->getContent(), $metadata);
        }

        return $referenceFile;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        return $this->getCdn()->getPath(sprintf('%s/thumb_%s_%s.jpg',
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
        return sprintf('%s/thumb_%s_%s.jpg',
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
        $formMapper->add('enabled', null, array('required' => false));
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add(
            'binaryContent',
            // NEXT_MAJOR: Remove ternary and keep 'Symfony\Component\Form\Extension\Core\Type\TextType' value
            // (when requirement of Symfony is >= 2.8)
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
                : 'text',
            array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add(
            'binaryContent',
            // NEXT_MAJOR: Remove ternary and keep 'Symfony\Component\Form\Extension\Core\Type\TextType' value
            // (when requirement of Symfony is >= 2.8)
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
                : 'text',
            array(
                'constraints' => array(
                    new NotBlank(),
                    new NotNull(),
                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildMediaType(FormBuilder $formBuilder)
    {
        // NEXT_MAJOR: Remove ternary and keep 'Symfony\Component\Form\Extension\Core\Type\TextType'
        // (when requirement of Symfony is >= 2.8)
        $formBuilder->add(
            'binaryContent',
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
                : 'text',
            array(
                'label' => 'widget_label_binary_content',
            )
        );
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

        $media->resetBinaryContent();
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(MediaInterface $media)
    {
    }

    // NEXT_MAJOR: Uncomment this method
    /*
     * Get provider reference url.
     *
     * @param MediaInterface $media
     *
     * @return string
     */
    // abstract public function getReferenceUrl(MediaInterface $media);

    /**
     * @throws \RuntimeException
     *
     * @param MediaInterface $media
     * @param string         $url
     *
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, $url)
    {
        try {
            $response = $this->browser->get($url);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :'.$url, null, $e);
        }

        $metadata = json_decode($response->getContent(), true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :'.$url);
        }

        return $metadata;
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return Box
     */
    protected function getBoxHelperProperties(MediaInterface $media, $format, $options = array())
    {
        if ($format == 'reference') {
            return $media->getBox();
        }

        if (isset($options['width']) || isset($options['height'])) {
            $settings = array(
                'width' => isset($options['width']) ? $options['width'] : null,
                'height' => isset($options['height']) ? $options['height'] : null,
            );
        } else {
            $settings = $this->getFormat($format);
        }

        return $this->resizer->getBox($media, $settings);
    }
}
