<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests;

use Sonata\MediaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testAdminFormat()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(
            array(
                'db_driver' => 'foo',
                'default_context' => 'default',
            ),
        ));

        $this->assertNotNull($config['admin_format']);
        $this->assertSame(200, $config['admin_format']['width']);
        $this->assertSame(false, $config['admin_format']['height']);
        $this->assertSame(90, $config['admin_format']['quality']);
        $this->assertSame('jpg', $config['admin_format']['format']);
        $this->assertSame(true, $config['admin_format']['constraint']);
    }
}
