<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\DependencyInjection;

use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SonataMediaExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SonataMediaExtension
     */
    private $extension;

    /**
     * Root name of the configuration.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $configs = $this->getConfigs();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', array());

        $this->extension = $this->getExtension();
        $this->extension->load($configs, $this->container);
    }

    public function testDefaultAdapter()
    {
        $this->assertTrue($this->container->hasAlias('sonata.media.adapter.image.default'));
        $this->assertEquals('sonata.media.adapter.image.gd', $this->container->getAlias('sonata.media.adapter.image.default'));
    }

    /**
     * @param string $serviceId
     * @param string $extension
     * @param string $type
     *
     * @dataProvider dataAdapter
     */
    public function testAdapter($serviceId, $extension, $type)
    {
        $this->assertTrue($this->container->has($serviceId));

        if (extension_loaded($extension)) {
            $this->isInstanceOf($type, $this->container->get($serviceId));
        }
    }

    public function dataAdapter()
    {
        return array(
            array('sonata.media.adapter.image.gd', 'gd', 'Imagine\\Gd\\Imagine'),
            array('sonata.media.adapter.image.gmagick', 'gmagick', 'Imagine\\Gmagick\\Imagine'),
            array('sonata.media.adapter.image.imagick', 'imagick', 'Imagine\\Imagick\\Imagine'),
        );
    }

    public function testDefaultResizer()
    {
        $this->assertTrue($this->container->hasAlias('sonata.media.resizer.default'));
        $this->assertEquals('sonata.media.resizer.simple', $this->container->getAlias('sonata.media.resizer.default'));
        if (extension_loaded('gd')) {
            $this->isInstanceOf('Sonata\\MediaBundle\\Resizer\\SimpleResizer', $this->container->get('sonata.media.resizer.default'));
        }
    }

    /**
     * @param $serviceId
     * @param $type
     *
     * @dataProvider dataResizer
     */
    public function testResizer($serviceId, $type)
    {
        $this->assertTrue($this->container->has($serviceId));
        if (extension_loaded('gd')) {
            $this->isInstanceOf($type, $this->container->get($serviceId));
        }
    }

    public function dataResizer()
    {
        return array(
            array('sonata.media.resizer.simple', 'Sonata\\MediaBundle\\Resizer\\SimpleResizer'),
            array('sonata.media.resizer.square', 'Sonata\\MediaBundle\\Resizer\\SquareResizer'),
        );
    }

    /**
     * @return SonataMediaExtension
     */
    protected function getExtension()
    {
        return new SonataMediaExtension();
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        return new ContainerBuilder();
    }

    /**
     * @return array
     */
    protected function getConfigs()
    {
        $configs = array(
            'sonata_media' => array(
                'db_driver' => 'doctrine_orm',
                'default_context' => 'default',
                'contexts' => array(
                    'default' => array(
                        'providers' => array(
                            'sonata.media.provider.image',
                        ),
                        'formats' => array(
                            'default' => array(
                                'width' => 100,
                                'quality' => 100,
                            ),
                        ),
                    ),
                ),
                'cdn' => array(
                    'server' => array(
                        'path' => '/uploads/media',
                    ),
                ),
                'filesystem' => array(
                    'local' => array(
                        'directory' => '%kernel.root_dir%/../web/uploads/media',
                    ),
                ),
            ),
        );

        return $configs;
    }
}
