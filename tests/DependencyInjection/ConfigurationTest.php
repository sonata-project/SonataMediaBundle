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

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $config;

    protected function setUp(): void
    {
        $configs = [
            'sonata_media' => [
                'db_driver' => 'doctrine_orm',
                'default_context' => 'default',
                'http' => [
                    'client' => 'sonata.media.http.base_client',
                    'message_factory' => 'sonata.media.http.base_message_factory',
                ],
            ],
        ];
        $processor = new Processor();
        $configuration = new Configuration();
        $this->config = $processor->processConfiguration($configuration, $configs);
    }

    public function testProcess(): void
    {
        self::assertArrayHasKey('resizers', $this->config);
        self::assertArrayHasKey('default', $this->config['resizers']);
        self::assertSame('sonata.media.resizer.simple', $this->config['resizers']['default']);

        self::assertArrayHasKey('adapters', $this->config);
        self::assertArrayHasKey('default', $this->config['adapters']);
        self::assertSame('sonata.media.adapter.image.gd', $this->config['adapters']['default']);
    }
}
