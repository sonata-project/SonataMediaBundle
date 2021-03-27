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

namespace Sonata\MediaBundle\Tests\DependencyInjection;

use Aws\CloudFront\CloudFrontClient;
use Aws\Sdk;
use Imagine\Gd\Imagine as GdImagine;
use Imagine\Gmagick\Imagine as GmagicImagine;
use Imagine\Imagick\Imagine as ImagicImagine;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\MediaBundle\CDN\CloudFront;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;
use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;
use Sonata\MediaBundle\Model\CategoryManager;
use Sonata\MediaBundle\Resizer\SimpleResizer;
use Sonata\MediaBundle\Resizer\SquareResizer;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class SonataMediaExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', [
            'SonataDoctrineBundle' => true,
            'SonataAdminBundle' => true,
        ]);
    }

    public function testLoadWithDefaultAndCustomCategoryManager(): void
    {
        $this->load([
            'class' => [
                'category' => \stdClass::class,
            ],
            'category_manager' => 'dummy.service.name',
        ]);

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category', 'dummy.service.name');
    }

    public function testLoadWithForceDisableTrueAndWithCategoryManager(): void
    {
        $this->load([
            'class' => [
                'category' => \stdClass::class,
            ],
            'category_manager' => 'dummy.service.name',
            'force_disable_category' => true,
        ]);

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnable(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category');
        $this->assertContainerBuilderHasService(
            'sonata.media.manager.category.default',
            CategoryManager::class
        );
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndForceDisableCategory(): void
    {
        $this->load([
            'force_disable_category' => true,
        ]);

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndCustomCategoryManager(): void
    {
        $this->load([
            'class' => [
                'category' => \stdClass::class,
            ],
            'category_manager' => 'dummy.service.name',
        ]);

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category', 'dummy.service.name');
    }

    public function testDefaultAdapter(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.adapter.image.default', 'sonata.media.adapter.image.gd');
    }

    /**
     * @dataProvider dataAdapter
     */
    public function testAdapter(string $serviceId, string $extension, string $type): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
        if (\extension_loaded($extension)) {
            $this->assertInstanceOf($type, $this->container->get($serviceId));
        }
    }

    public function dataAdapter(): array
    {
        return [
            ['sonata.media.adapter.image.gd', 'gd', GdImagine::class],
            ['sonata.media.adapter.image.gmagick', 'gmagick', GmagicImagine::class],
            ['sonata.media.adapter.image.imagick', 'imagick', ImagicImagine::class],
        ];
    }

    public function testDefaultResizer(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.resizer.default', 'sonata.media.resizer.simple');
        if (\extension_loaded('gd')) {
            $this->assertContainerBuilderHasService(
                'sonata.media.resizer.default',
                SimpleResizer::class
            );
        }
    }

    public function testDefaultHasDefaultHttp(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.http.client', 'sonata.media.http.base_client');
        $this->assertContainerBuilderHasAlias('sonata.media.http.message_factory', 'sonata.media.http.base_message_factory');
    }

    public function testWithHttpClient(): void
    {
        $this->load([
            'http' => [
                'client' => 'acme_client',
                'message_factory' => 'acme_factory',
            ],
        ]);

        $this->assertContainerBuilderHasAlias('sonata.media.http.client', 'acme_client');
        $this->assertContainerBuilderHasAlias('sonata.media.http.message_factory', 'acme_factory');
    }

    /**
     * @dataProvider dataResizer
     */
    public function testResizer(string $serviceId, string $type): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
        if (\extension_loaded('gd')) {
            $this->assertInstanceOf($type, $this->container->get($serviceId));
        }
    }

    public function dataResizer(): array
    {
        return [
            ['sonata.media.resizer.simple', SimpleResizer::class],
            ['sonata.media.resizer.square', SquareResizer::class],
        ];
    }

    public function testLoadWithSonataAdminDefaults(): void
    {
        $this->load();

        $this->assertSame(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']
        );
    }

    public function testLoadWithSonataAdminCustomConfiguration(): void
    {
        $fakeContainer = $this->createMock(ContainerBuilder::class);

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

        $this->assertSame(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_FOO', 'ROLE_BAR']
        );
    }

    /**
     * @dataProvider dataFilesystemConfigurationAwsV3
     */
    public function testLoadWithFilesystemConfigurationV3(array $expected, array $configs): void
    {
        if (!class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires aws/aws-sdk-php 3.x.');
        }

        $this->load($configs);

        $this->assertSame(
            $expected,
            $this->container->getDefinition('sonata.media.adapter.service.s3')->getArgument(0)
        );
    }

    public function dataFilesystemConfigurationAwsV3(): iterable
    {
        yield [
            [
                'region' => 'region',
                'version' => 'version',
                'credentials' => [
                    'secret' => 'secret',
                    'key' => 'access',
                ],
            ],
            [
                'filesystem' => [
                    's3' => [
                        'bucket' => 'bucket_name',
                        'region' => 'region',
                        'version' => 'version',
                        'secretKey' => 'secret',
                        'accessKey' => 'access',
                    ],
                ],
            ],
        ];

        yield [
            [
                'region' => 'region',
                'version' => 'version',
                'endpoint' => 'endpoint',
                'credentials' => [
                    'secret' => 'secret',
                    'key' => 'access',
                ],
            ],
            [
                'filesystem' => [
                    's3' => [
                        'bucket' => 'bucket_name',
                        'sdk_version' => 3,
                        'region' => 'region',
                        'version' => 'version',
                        'endpoint' => 'endpoint',
                        'secretKey' => 'secret',
                        'accessKey' => 'access',
                    ],
                ],
            ],
        ];

        yield [
            [
                'region' => 's3.amazonaws.com',
                'version' => 'latest',
                'credentials' => [
                    'secret' => 'secret',
                    'key' => 'access',
                ],
            ],
            [
                'filesystem' => [
                    's3' => [
                        'bucket' => 'bucket_name',
                        'secretKey' => 'secret',
                        'accessKey' => 'access',
                    ],
                ],
            ],
        ];

        yield [
            [
                'region' => 's3.amazonaws.com',
                'version' => null,
                'credentials' => [
                    'secret' => 'secret',
                    'key' => 'access',
                ],
            ],
            [
                'filesystem' => [
                    's3' => [
                        'bucket' => 'bucket_name',
                        'version' => null,
                        'secretKey' => 'secret',
                        'accessKey' => 'access',
                    ],
                ],
            ],
        ];
    }

    public function testLoadWithSdkVersion2(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Can not use 2 for "sdk_version" since the installed version of aws/aws-sdk-php is not 2.x.');

        $this->load([
            'filesystem' => [
                's3' => [
                    'bucket' => 'bucket_name',
                    'version' => null,
                    'sdk_version' => 2,
                    'secretKey' => 'secret',
                    'accessKey' => 'access',
                ],
            ],
        ]);
    }

    public function testMediaPool(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('sonata.media.pool');
        $this->assertContainerBuilderHasAlias('%sonata.media.pool.class%', 'sonata.media.pool');
    }

    public function testCdnCloudFront(): void
    {
        if (!class_exists(CloudFrontClient::class) || class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires "aws/aws-sdk-php:^2.0" to be installed.');
        }

        $this->load([
            'cdn' => [
                'cloudfront' => [
                    'path' => '/foo',
                    'distribution_id' => '$some_id$',
                    'key' => 'cloudfront_key',
                    'secret' => 'cloudfront_secret',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('sonata.media.cdn.cloudfront.client', CloudFrontClient::class);
        $this->assertSame(
            $this->container->getDefinition('sonata.media.cdn.cloudfront.client')->getArgument(0),
            [
                'key' => 'cloudfront_key',
                'secret' => 'cloudfront_secret',
            ]
        );
        $this->assertSame([CloudFrontClient::class, 'factory'], $this->container->getDefinition('sonata.media.cdn.cloudfront.client')->getFactory());
        $this->assertContainerBuilderHasService('sonata.media.cdn.cloudfront', CloudFront::class);
    }

    public function testCdnCloudFrontVersion3(): void
    {
        if (!class_exists(CloudFrontClient::class) || !class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires "aws/aws-sdk-php:^3.0" to be installed.');
        }

        $this->load([
            'cdn' => [
                'cloudfront' => [
                    'path' => '/foo',
                    'distribution_id' => '$some_id$',
                    'key' => 'cloudfront_key',
                    'secret' => 'cloudfront_secret',
                    'region' => 'cloudfront_region',
                    'version' => 'cloudfront_version',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('sonata.media.cdn.cloudfront.client', CloudFrontClient::class);
        $this->assertSame(
            $this->container->getDefinition('sonata.media.cdn.cloudfront.client')->getArgument(0),
            [
                'region' => 'cloudfront_region',
                'version' => 'cloudfront_version',
                'credentials' => [
                    'key' => 'cloudfront_key',
                    'secret' => 'cloudfront_secret',
                ],
            ]
        );
        $this->assertNull($this->container->getDefinition('sonata.media.cdn.cloudfront.client')->getFactory());
        $this->assertContainerBuilderHasService('sonata.media.cdn.cloudfront', CloudFrontVersion3::class);
    }

    protected function getMinimalConfiguration(): array
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

    protected function getContainerExtensions(): array
    {
        return [
            new SonataMediaExtension(),
        ];
    }
}
