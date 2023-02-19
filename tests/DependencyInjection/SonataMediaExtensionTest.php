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

use AsyncAws\SimpleS3\SimpleS3Client;
use Aws\CloudFront\CloudFrontClient;
use Aws\S3\S3Client;
use Aws\Sdk;
use Gaufrette\Adapter\AsyncAwsS3;
use Gaufrette\Adapter\AwsS3;
use Imagine\Gd\Imagine as GdImagine;
use Imagine\Gmagick\Imagine as GmagicImagine;
use Imagine\Imagick\Imagine as ImagicImagine;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Admin\GalleryItemAdmin;
use Sonata\MediaBundle\Admin\ODM\MediaAdmin as ODMMediaAdmin;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin as ORMMediaAdmin;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;
use Sonata\MediaBundle\Controller\GalleryAdminController;
use Sonata\MediaBundle\Controller\MediaAdminController;
use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;
use Sonata\MediaBundle\Messenger\GenerateThumbnailsHandler;
use Sonata\MediaBundle\Resizer\SimpleResizer;
use Sonata\MediaBundle\Resizer\SquareResizer;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;
use Sonata\MediaBundle\Twig\Extension\FormatterMediaExtension;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Twig\GlobalVariables;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class SonataMediaExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', [
            'SonataClassificationBundle' => true,
            'SonataDoctrineBundle' => true,
            'SonataAdminBundle' => true,
        ]);
    }

    public function testLoadWithForceDisableTrue(): void
    {
        $this->load([
            'class' => [
                'category' => \stdClass::class,
            ],
            'force_disable_category' => true,
        ]);

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnable(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.manager.category');
    }

    public function testLoadWithDefaultAndClassificationBundleEnableAndForceDisableCategory(): void
    {
        $this->load([
            'force_disable_category' => true,
        ]);

        $this->assertContainerBuilderNotHasService('sonata.media.manager.category');
    }

    public function testDefaultAdapter(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.media.adapter.image.default', 'sonata.media.adapter.image.gd');
    }

    /**
     * @dataProvider dataAdapter
     *
     * @phpstan-param class-string $type
     */
    public function testAdapter(string $serviceId, string $type): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId, $type);
    }

    /**
     * @phpstan-return iterable<array{string, class-string}>
     */
    public function dataAdapter(): iterable
    {
        yield ['sonata.media.adapter.image.gd', GdImagine::class];
        yield ['sonata.media.adapter.image.gmagick', GmagicImagine::class];
        yield ['sonata.media.adapter.image.imagick', ImagicImagine::class];
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
     *
     * @phpstan-param class-string $type
     */
    public function testResizer(string $serviceId, string $type): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId, $type);
    }

    /**
     * @phpstan-return iterable<array{string, class-string}>
     */
    public function dataResizer(): iterable
    {
        yield ['sonata.media.resizer.simple', SimpleResizer::class];
        yield ['sonata.media.resizer.square', SquareResizer::class];
    }

    public function testLoadWithSonataAdminDefaults(): void
    {
        $this->load(['db_driver' => 'no_driver']);

        static::assertSame(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']
        );

        $this->assertContainerBuilderHasService('sonata.media.controller.media.admin', MediaAdminController::class);
        $this->assertContainerBuilderHasService('sonata.media.controller.gallery.admin', GalleryAdminController::class);
        $this->assertContainerBuilderNotHasService('sonata.media.admin.media');
        $this->assertContainerBuilderNotHasService('sonata.media.admin.gallery');
        $this->assertContainerBuilderNotHasService('sonata.media.admin.gallery_item');
    }

    public function testLoadWithSonataAdminOrm(): void
    {
        $this->load(['db_driver' => 'doctrine_orm']);

        $this->assertContainerBuilderHasService('sonata.media.admin.media', ORMMediaAdmin::class);
        $this->assertContainerBuilderHasService('sonata.media.admin.gallery', GalleryAdmin::class);
        $this->assertContainerBuilderHasService('sonata.media.admin.gallery_item', GalleryItemAdmin::class);
    }

    public function testLoadWithSonataAdminMongoDB(): void
    {
        $this->load(['db_driver' => 'doctrine_mongodb']);

        $this->assertContainerBuilderHasService('sonata.media.admin.media', ODMMediaAdmin::class);
        $this->assertContainerBuilderHasService('sonata.media.admin.gallery', GalleryAdmin::class);
        $this->assertContainerBuilderHasService('sonata.media.admin.gallery_item', GalleryItemAdmin::class);
    }

    public function testLoadWithSonataAdminCustomConfiguration(): void
    {
        $fakeContainer = $this->createMock(ContainerBuilder::class);

        $fakeContainer->expects(static::once())
            ->method('getParameter')
            ->with('kernel.bundles')
            ->willReturn($this->container->getParameter('kernel.bundles'));

        $fakeContainer->expects(static::once())
            ->method('getExtensionConfig')
            ->with('sonata_admin')
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

        static::assertSame(
            $this->container->getDefinition('sonata.media.security.superadmin_strategy')->getArgument(2),
            ['ROLE_FOO', 'ROLE_BAR']
        );
    }

    /**
     * @dataProvider dataFilesystemConfigurationAwsV3
     *
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $configs
     */
    public function testLoadWithFilesystemConfigurationV3(
        array $expected,
        array $configs
    ): void {
        if (!class_exists(Sdk::class)) {
            static::markTestSkipped('This test requires aws/aws-sdk-php 3.x.');
        }

        $this->load($configs);

        static::assertSame(
            S3Client::class,
            $this->container->getDefinition('sonata.media.adapter.service.s3')->getClass()
        );

        static::assertSame(
            $expected,
            $this->container->getDefinition('sonata.media.adapter.service.s3')->getArgument(0)
        );

        static::assertSame(
            AwsS3::class,
            $this->container->getDefinition('sonata.media.adapter.filesystem.s3')->getClass()
        );
    }

    /**
     * @phpstan-return iterable<array{array<string, mixed>, array<string, mixed>}>
     */
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

    public function testLoadWithFilesystemConfigurationV3ASync(): void
    {
        if (!class_exists(SimpleS3Client::class)) {
            static::markTestSkipped('This test requires async-aws/simple-s3.');
        }

        $expected = [
            'region' => 'region',
            'endpoint' => 'endpoint',
            'accessKeyId' => 'access',
            'accessKeySecret' => 'secret',
        ];

        $configs = [
            'filesystem' => [
                's3' => [
                    'async' => true,
                    'bucket' => 'bucket_name',
                    'region' => 'region',
                    'version' => 'version',
                    'endpoint' => 'endpoint',
                    'secretKey' => 'secret',
                    'accessKey' => 'access',
                ],
            ],
        ];

        $this->load($configs);

        static::assertSame(
            SimpleS3Client::class,
            $this->container->getDefinition('sonata.media.adapter.service.s3.async')->getClass()
        );

        static::assertSame(
            $expected,
            $this->container->getDefinition('sonata.media.adapter.service.s3.async')->getArgument(0)
        );

        static::assertSame(
            AsyncAwsS3::class,
            $this->container->getDefinition('sonata.media.adapter.filesystem.s3')->getClass()
        );
    }

    public function testMediaPool(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('sonata.media.pool');
    }

    public function testCdnCloudFrontVersion3(): void
    {
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
        static::assertSame(
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
        static::assertNull($this->container->getDefinition('sonata.media.cdn.cloudfront.client')->getFactory());
        $this->assertContainerBuilderHasService('sonata.media.cdn.cloudfront', CloudFrontVersion3::class);
    }

    public function testMessengerEnabled(): void
    {
        $this->load([
            'messenger' => [
                'enabled' => true,
                'generate_thumbnails_bus' => 'my.custom.bus',
            ],
        ]);

        $this->assertContainerBuilderHasService('sonata.media.messenger.generate_thumbnails', GenerateThumbnailsHandler::class);
        $this->assertContainerBuilderHasService('sonata.media.thumbnail.messenger', MessengerThumbnail::class);
        $this->assertContainerBuilderHasAlias('sonata.media.messenger.generate_thumbnails_bus', 'my.custom.bus');
    }

    public function testMessengerDisabled(): void
    {
        $this->load([]);

        $this->assertContainerBuilderNotHasService('sonata.media.messenger.generate_thumbnails');
        $this->assertContainerBuilderNotHasService('sonata.media.thumbnail.messenger');
    }

    public function testTwigExtensions(): void
    {
        $this->load([]);

        $this->assertContainerBuilderHasService('sonata.media.twig.extension', MediaExtension::class);
        $this->assertContainerBuilderHasService('sonata.media.twig.global', GlobalVariables::class);
        $this->assertContainerBuilderHasService('sonata.media.twig.runtime', MediaRuntime::class);
        $this->assertContainerBuilderHasService('sonata.media.formatter.twig', FormatterMediaExtension::class);
    }

    public function testFallbackCDN(): void
    {
        $this->load([
            'cdn' => [
                'fallback' => [
                    'primary' => 'sonata.media.cdn.cloudfront',
                    'fallback' => 'sonata.media.cdn.server',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('sonata.media.cdn.fallback', 0, 'sonata.media.cdn.cloudfront');
    }

    public function testReplicateFilesystem(): void
    {
        $this->load([
            'filesystem' => [
                'replicate' => [
                    'primary' => 'sonata.media.adapter.filesystem.s3',
                    'secondary' => 'sonata.media.adapter.filesystem.local',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('sonata.media.adapter.filesystem.replicate', 0, 'sonata.media.adapter.filesystem.s3');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('sonata.media.adapter.filesystem.replicate', 1, 'sonata.media.adapter.filesystem.local');
    }

    /**
     * @return array<string, mixed>
     */
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
