<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;

/**
 * Class MediaExtensionTest.
 *
 * Unit test of MediaExtension class.
 *
 * @author Geza Buza <bghome@gmail.com>
 */
class MediaExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    private $provider;

    /**
     * @var Twig_TemplateInterface
     */
    private $template;

    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @var Sonata\MediaBundle\Model\Media
     */
    private $media;

    public function setUp()
    {
        if (false === interface_exists('Symfony\Component\Validator\ExecutionContextInterface')) {
            $this->markTestSkipped('Test only available for < SF3.0');
        }
    }

    public function testThumbnailCanRenderHtmlAttributesGivenByTheProvider()
    {
        $mediaExtension = new MediaExtension($this->getMediaService(), $this->getMediaManager());
        $mediaExtension->initRuntime($this->getEnvironment());

        $media = $this->getMedia();
        $format = 'png';
        $options = array('title' => 'Test title');

        $provider = $this->getProvider();
        $provider->expects($this->once())->method('getHelperProperties')->with($media, $format, $options)
            ->willReturn(array('title' => 'Test title', 'data-custom' => 'foo'));

        $template = $this->getTemplate();
        $template->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo(
                    array(
                        'media'   => $media,
                        'options' => array('title' => 'Test title', 'data-custom' => 'foo'),
                    )
                )
            );

        $mediaExtension->thumbnail($media, $format, $options);
    }

    public function getMediaService()
    {
        $mediaService = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $mediaService->method('getProvider')->willReturn($this->getProvider());

        return $mediaService;
    }

    public function getMediaManager()
    {
        return $this->getMock('Sonata\CoreBundle\Model\ManagerInterface');
    }

    public function getProvider()
    {
        if (is_null($this->provider)) {
            $this->provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
            $this->provider->method('getFormatName')->will($this->returnArgument(1));
        }

        return $this->provider;
    }

    public function getTemplate()
    {
        if (is_null($this->template)) {
            $this->template = $this->getMock('Twig_TemplateInterface');
        }

        return $this->template;
    }

    public function getEnvironment()
    {
        if (is_null($this->environment)) {
            $this->environment = $this->getMockBuilder('Twig_Environment')
                ->disableOriginalConstructor()
                ->getMock();
            $this->environment->method('loadTemplate')->willReturn($this->getTemplate());
        }

        return $this->environment;
    }

    public function getMedia()
    {
        if (is_null($this->media)) {
            $this->media = $this->getMock('Sonata\MediaBundle\Model\Media');
            $this->media->method('getProviderStatus')->willReturn(MediaInterface::STATUS_OK);
        }

        return $this->media;
    }
}
