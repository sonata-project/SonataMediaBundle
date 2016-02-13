<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\DependencyInjection;

use Sonata\MediaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultOptions()
    {
        $processor     = new Processor();
        $configuration = new Configuration();
        $config        = $processor->processConfiguration($configuration, $this->getConfig());
    }

    public function testAdminOptions()
    {
        $adminConfig = array(
            'admin' => array(
                'media' => array(
                    'show_in_dashboard' => false,
                    'class' => 'SomeCustomClass',
                    'controller' => 'SomeCustomController',
                ),
                'gallery' => array(
                    'translation' => 'SomeCustomDomain',
                )
            )
        );

        $processor     = new Processor();
        $configuration = new Configuration();
        $config        = $processor->processConfiguration($configuration, $this->getConfig($adminConfig));

        $this->assertArrayHasKey('class', $config['admin']['media']);
        $this->assertArrayHasKey('controller', $config['admin']['media']);
        $this->assertArrayHasKey('translation', $config['admin']['media']);
        $this->assertArrayHasKey('show_in_dashboard', $config['admin']['media']);

        $this->assertEquals($config['admin']['media']['class'], 'SomeCustomClass');
        $this->assertEquals($config['admin']['media']['controller'], 'SomeCustomController');
        $this->assertEquals($config['admin']['media']['translation'], 'SonataMediaBundle');
        $this->assertFalse($config['admin']['media']['show_in_dashboard']);


        $this->assertFalse(array_key_exists('class', $config['admin']['gallery']));
        $this->assertArrayHasKey('controller', $config['admin']['gallery']);
        $this->assertArrayHasKey('translation', $config['admin']['gallery']);
        $this->assertArrayHasKey('show_in_dashboard', $config['admin']['gallery']);

        $this->assertEquals($config['admin']['gallery']['controller'], 'SonataMediaBundle:GalleryAdmin');
        $this->assertEquals($config['admin']['gallery']['translation'], 'SomeCustomDomain');
        $this->assertTrue($config['admin']['gallery']['show_in_dashboard']);


        $this->assertFalse(array_key_exists('class', $config['admin']['gallery_has_media']));
        $this->assertArrayHasKey('controller', $config['admin']['gallery_has_media']);
        $this->assertArrayHasKey('translation', $config['admin']['gallery_has_media']);
        $this->assertArrayHasKey('show_in_dashboard', $config['admin']['gallery_has_media']);

        $this->assertEquals($config['admin']['gallery_has_media']['controller'], 'SonataAdminBundle:CRUD');
        $this->assertEquals($config['admin']['gallery_has_media']['translation'], 'SonataMediaBundle');
        $this->assertFalse($config['admin']['gallery_has_media']['show_in_dashboard']);
    }

    private function getConfig($config = array()) {
        $default = array(
            'default_context' => 'default',
            'db_driver' => 'orm',
            'contexts' => array(
                'default' => array(
                    'providers' => array(
                        'sonata.media.provider.file'
                    ),
                    'formats' => array(
                        'small' => array(
                            'width' => 100,
                            'quality' => 70,
                        )
                    )
                )
            ),
            'cdn' => array(
                'server' => array(
                    'path' => '/uploads/media'
                )
            ),
            'filesystem' => array(
                'local' => array(
                    'directory' => '%kernel.root_dir%/../web/uploads/media',
                    'create' => false,
                )
            )
        );

        return (array($default, $config));
    }
}