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

use Sonata\MediaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $config;

    public function setUp()
    {
        $configs = array(
            'sonata_media' => array(
                'db_driver' => 'doctrine_orm',
                'default_context' => 'default',
            ),
        );
        $processor = new Processor();
        $configuration = new Configuration();
        $this->config = $processor->processConfiguration($configuration, $configs);
    }

    public function testProcess()
    {
        $this->assertArrayHasKey('resizers', $this->config);
        $this->assertArrayHasKey('default', $this->config['resizers']);
        $this->assertEquals('sonata.media.resizer.simple', $this->config['resizers']['default']);

        $this->assertArrayHasKey('adapters', $this->config);
        $this->assertArrayHasKey('default', $this->config['adapters']);
        $this->assertEquals('sonata.media.adapter.image.gd', $this->config['adapters']['default']);
    }
}
