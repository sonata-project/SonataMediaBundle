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

use PHPUnit\Framework\TestCase;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Twig\Environment;
use Twig\Template;

/**
 * @author Geza Buza <bghome@gmail.com>
 */
class MediaExtensionTest extends TestCase
{
    /**
     * @var Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    private $provider;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var Sonata\MediaBundle\Model\Media
     */
    private $media;

    public function testThumbnailHasAllNecessaryAttributes(): void
    {
        $mediaExtension = new MediaExtension($this->getMediaService(), $this->getMediaManager());
        $mediaExtension->initRuntime($this->getEnvironment());

        $media = $this->getMedia();
        $format = 'png';
        $options = [
            'title' => 'Test title',
            'alt' => 'Test title',
        ];

        $provider = $this->getProvider();
        $provider->expects($this->once())->method('generatePublicUrl')->with($media, $format)
            ->willReturn('http://some.url.com');

        $template = $this->getTemplate();
        $template->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo(
                    [
                        'media' => $media,
                        'options' => [
                            'title' => 'Test title',
                            'alt' => 'Test title',
                            'src' => 'http://some.url.com',
                        ],
                    ]
                )
            );

        $mediaExtension->thumbnail($media, $format, $options);
    }

    public function getMediaService(): Pool
    {
        $mediaService = $this->createMock(Pool::class);
        $mediaService->method('getProvider')->willReturn($this->getProvider());

        return $mediaService;
    }

    public function getMediaManager(): ManagerInterface
    {
        return $this->createMock(ManagerInterface::class);
    }

    public function getProvider(): MediaProviderInterface
    {
        if (null === $this->provider) {
            $this->provider = $this->createMock(MediaProviderInterface::class);
            $this->provider->method('getFormatName')->willReturnArgument(1);
            $this->provider->method('getFormat')->willReturn(false);
        }

        return $this->provider;
    }

    public function getTemplate(): Template
    {
        if (null === $this->template) {
            $this->template = $this->createMock(Template::class);
        }

        return $this->template;
    }

    public function getEnvironment(): Environment
    {
        if (null === $this->environment) {
            $this->environment = $this->createMock(Environment::class);
            $this->environment->method('loadTemplate')->willReturn($this->getTemplate());
        }

        return $this->environment;
    }

    public function getMedia(): Media
    {
        if (null === $this->media) {
            $this->media = $this->createMock(Media::class);
            $this->media->method('getProviderStatus')->willReturn(MediaInterface::STATUS_OK);
        }

        return $this->media;
    }
}
