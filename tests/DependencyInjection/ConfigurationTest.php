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

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testMinimalConfigurationRequired(): void
    {
        $this->assertConfigurationIsInvalid([]);
        $this->assertConfigurationIsValid([
            'sonata_media' => [
                'default_context' => 'default',
            ],
        ]);
    }

    public function testDefaultAdapter(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'adapters' => ['default' => 'sonata.media.adapter.image.gd'],
        ], 'adapters');
    }

    public function testDefaultResizer(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'resizers' => ['default' => 'sonata.media.resizer.simple'],
        ], 'resizers');
    }

    public function testMessengerConfiguration(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'messenger' => [
                'enabled' => false,
                'generate_thumbnails_bus' => 'messenger.default_bus',
            ],
        ], 'messenger');
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
