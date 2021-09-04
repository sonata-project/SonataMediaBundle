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
     * @var array
     */
    protected $config;

    protected function setUp(): void
    {
        $configs = [
            'sonata_media' => [
                'db_driver' => 'doctrine_orm',
                'default_context' => 'default',
                'http' => [
                    'client' => 'sonata.media.http.buzz_client',
                    'message_factory' => null,
                ],
            ],
        ];
        $processor = new Processor();
        $configuration = new Configuration();
        $this->config = $processor->processConfiguration($configuration, $configs);
    }

    public function testProcess(): void
    {
        static::assertArrayHasKey('resizers', $this->config);
        static::assertArrayHasKey('default', $this->config['resizers']);
        static::assertSame('sonata.media.resizer.simple', $this->config['resizers']['default']);

        static::assertArrayHasKey('adapters', $this->config);
        static::assertArrayHasKey('default', $this->config['adapters']);
        static::assertSame('sonata.media.adapter.image.gd', $this->config['adapters']['default']);
    }
}
