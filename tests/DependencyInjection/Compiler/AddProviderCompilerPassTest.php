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

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddProviderCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        // Create ContainerBuilder and set up basic sonata_media config with formats
        $container = new ContainerBuilder();
        $extension = new SonataMediaExtension();
        $container->registerExtension($extension);
        $container->loadFromExtension(
            $extension->getAlias(),
            [
                'db_driver' => 'doctrine_orm',
                'default_context' => 'default',
                'providers' => [
                    'image' => [
                        'filesystem' => 'foo_filesystem',
                    ],
                ],
                'contexts' => [
                    'default' => [
                        'providers' => [
                            'foo_provider',
                        ],
                        'formats' => '%foo_formats%',
                    ],
                ],
            ]
        );

        // Define 'foo_formats' parameter, parameter reference in 'sonata_media' config should resolve to this value.
        $container->setParameter('foo_formats', [
            'foo_format' => [
                'width' => 350,
                'height' => 200,
                'quality' => 70,
            ],
        ]);

        // Register parameters and services needed by compiler pass
        $container->setParameter('sonata.media.admin_format', [
            'width' => 200,
            'height' => false,
            'quality' => 90,
        ]);
        $container
            ->register('foo_filesystem')
            ->setPublic(false);
        $container
            ->register('foo_provider')
            ->setPublic(false);
        $container
            ->register('sonata.media.pool')
            ->setPublic(false);

        (new AddProviderCompilerPass())->process($container);

        // 'foo_provider' should have 1 'addFormat' method call with correctly resolved config values.
        $calls = $container->getDefinition('foo_provider')->getMethodCalls();
        $callFound = false;
        $expectedCall = [
            'default_foo_format',
            [
                'width' => 350,
                'height' => 200,
                'quality' => 70,
                'format' => 'jpg',
                'constraint' => true,
                'resizer' => false,
                'resizer_options' => [],
            ],
        ];
        foreach ($calls as $call) {
            if ('addFormat' === $call[0]) {
                $callFound = true;
                $this->assertSame(
                    $expectedCall,
                    $call[1],
                    'Format config of "foo_provider" doesn\'t match the expected config.'
                );
            }
        }
        $this->assertTrue(
            $callFound,
            'Expected "addFormat" method call on "foo_provider" service was not registered.'
        );
    }
}
