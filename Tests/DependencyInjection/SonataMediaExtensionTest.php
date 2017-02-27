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
use Symfony\Component\DependencyInjection\Definition;

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
    protected function setUp()
    {
        $configs = $this->getConfigs();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', array());

        $this->extension = $this->getExtension();
        $this->extension->load($configs, $this->container);
    }

    public function testLoadWithDefaultAndCustomCategoryManager()
    {
        $container = $this->getContainer(array(array(
            'class' => array('category' => '\stdClass'),
            'category_manager' => 'dummy.service.name',
        )));

        $this->assertTrue($container->hasAlias('sonata.media.manager.category'));
        $this->assertSame($container->getAlias('sonata.media.manager.category')->__toString(), 'dummy.service.name');
    }

    public function testLoadWithForceDisableTrueAndWithCategoryManager()
    {
        $container = $this->getContainer(array(array(
            'class' => array('category' => '\stdClass'),
            'category_manager' => 'dummy.service.name',
            'force_disable_category' => true,
        )));

        $this->assertFalse($container->hasDefinition('sonata.media.manager.category'));
    }

    public function testLoadWithDefaultAndClassificationBundleEnable()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasAlias('sonata.media.manager.category'));
        $this->assertSame($container->getDefinition('sonata.media.manager.category.default')->getClass(), 'Sonata\MediaBundle\Model\CategoryManager');
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndForceDisableCategory()
    {
        $container = $this->getContainer(array(array('force_disable_category' => true)));

        $this->assertFalse($container->hasDefinition('sonata.media.manager.category'));
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndCustomCategoryManager()
    {
        $container = $this->getContainer(array(array(
            'class' => array('category' => '\stdClass'),
            'category_manager' => 'dummy.service.name',
        )));

        $this->assertTrue($container->hasAlias('sonata.media.manager.category'));
        $this->assertSame($container->getAlias('sonata.media.manager.category')->__toString(), 'dummy.service.name');
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

    private function getContainer(array $config = array())
    {
        $defaults = array(array(
            'default_context' => 'default',
            'db_driver' => 'doctrine_orm',
            'contexts' => array('default' => array('formats' => array('small' => array('width' => 100, 'quality' => 50)))),
            'filesystem' => array('local' => array('directory' => '/tmp/')),
        ));

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array('SonataAdminBundle' => true));
        $container->setDefinition('translator', new Definition('\stdClass'));
        $container->setDefinition('security.authorization_checker', new Definition('\stdClass'));
        $container->setDefinition('doctrine', new Definition('\stdClass'));
        $container->setDefinition('session', new Definition('\stdClass'));

        if (isset($config[0]['category_manager'])) {
            $container->setDefinition($config[0]['category_manager'], new Definition('\stdClass'));
        }

        $container->setDefinition('sonata.classification.manager.category', new Definition('Sonata\ClassificationBundle\Model\CategoryManager'));

        $loader = new SonataMediaExtension();
        $loader->load(array_merge($defaults, $config), $container);
        $container->compile();

        return $container;
    }
}
