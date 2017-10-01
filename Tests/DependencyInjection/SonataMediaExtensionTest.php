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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;

class SonataMediaExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', array('SonataAdminBundle' => true));
    }

    public function testLoadWithDefaultAndCustomCategoryManager()
    {
        $this->load(array(
            'class' => array(
                'category' => '\stdClass',
            ),
            'category_manager' => 'dummy.service.name',
        ));

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category', 'dummy.service.name');
    }

    public function testLoadWithForceDisableTrueAndWithCategoryManager()
    {
        $this->load(array(
            'class' => array(
                'category' => '\stdClass',
            ),
            'category_manager' => 'dummy.service.name',
            'force_disable_category' => true,
        ));

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnable()
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category');
        $this->assertContainerBuilderHasService(
            'sonata.media.manager.category.default',
            'Sonata\MediaBundle\Model\CategoryManager'
        );
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndForceDisableCategory()
    {
        $this->load(array(
            'force_disable_category' => true,
        ));

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndCustomCategoryManager()
    {
        $this->load(array(
            'class' => array(
                'category' => '\stdClass',
            ),
            'category_manager' => 'dummy.service.name',
        ));

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category', 'dummy.service.name');
    }

    public function testDefaultAdapter()
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.adapter.image.default', 'sonata.media.adapter.image.gd');
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
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
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
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.resizer.default', 'sonata.media.resizer.simple');
        if (extension_loaded('gd')) {
            $this->assertContainerBuilderHasService(
                'sonata.media.resizer.default',
                'Sonata\\MediaBundle\\Resizer\\SimpleResizer'
            );
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
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
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

    protected function getMinimalConfiguration()
    {
        return array(
            'default_context' => 'default',
            'db_driver' => 'doctrine_orm',
            'contexts' => array(
                'default' => array(
                    'formats' => array(
                        'small' => array(
                            'width' => 100,
                            'quality' => 50,
                        ),
                    ),
                ),
            ),
            'filesystem' => array(
                'local' => array(
                    'directory' => '/tmp/',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(
            new SonataMediaExtension(),
        );
    }
}
