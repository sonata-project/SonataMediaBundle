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
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class SonataMediaExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', ['SonataAdminBundle' => true]);
    }

    public function testLoadWithDefaultAndCustomCategoryManager()
    {
        $this->load([
            'class' => [
                'category' => '\stdClass',
            ],
            'category_manager' => 'dummy.service.name',
        ]);

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category', 'dummy.service.name');
    }

    public function testLoadWithForceDisableTrueAndWithCategoryManager()
    {
        $this->load([
            'class' => [
                'category' => '\stdClass',
            ],
            'category_manager' => 'dummy.service.name',
            'force_disable_category' => true,
        ]);

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
        $this->load([
            'force_disable_category' => true,
        ]);

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndCustomCategoryManager()
    {
        $this->load([
            'class' => [
                'category' => '\stdClass',
            ],
            'category_manager' => 'dummy.service.name',
        ]);

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
        return [
            ['sonata.media.adapter.image.gd', 'gd', 'Imagine\\Gd\\Imagine'],
            ['sonata.media.adapter.image.gmagick', 'gmagick', 'Imagine\\Gmagick\\Imagine'],
            ['sonata.media.adapter.image.imagick', 'imagick', 'Imagine\\Imagick\\Imagine'],
        ];
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
        return [
            ['sonata.media.resizer.simple', 'Sonata\\MediaBundle\\Resizer\\SimpleResizer'],
            ['sonata.media.resizer.square', 'Sonata\\MediaBundle\\Resizer\\SquareResizer'],
        ];
    }

    public function testLoadWithSonataAdminDefaults()
    {
        $this->load();

        $this->assertEquals(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']
        );
    }

    public function testLoadWithSonataAdminCustomConfiguration()
    {
        $fakeContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['getParameter', 'getExtensionConfig'])
            ->getMock();

        $fakeContainer->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('kernel.bundles'))
            ->willReturn($this->container->getParameter('kernel.bundles'));

        $fakeContainer->expects($this->once())
            ->method('getExtensionConfig')
            ->with($this->equalTo('sonata_admin'))
            ->willReturn([[
                'security' => [
                    'role_admin' => 'ROLE_FOO',
                    'role_super_admin' => 'ROLE_BAR',
                ],
            ]]);

        $configs = [$this->getMinimalConfiguration()];
        foreach ($this->getContainerExtensions() as $extension) {
            if ($extension instanceof PrependExtensionInterface) {
                $extension->prepend($fakeContainer);
            }

            $extension->load($configs, $this->container);
        }

        $this->assertEquals(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_FOO', 'ROLE_BAR']
        );
    }

    protected function getMinimalConfiguration()
    {
        return [
            'default_context' => 'default',
            'db_driver' => 'doctrine_orm',
            'contexts' => [
                'default' => [
                    'formats' => [
                        'small' => [
                            'width' => 100,
                            'quality' => 50,
                        ],
                    ],
                ],
            ],
            'filesystem' => [
                'local' => [
                    'directory' => '/tmp/',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new SonataMediaExtension(),
        ];
    }
}
