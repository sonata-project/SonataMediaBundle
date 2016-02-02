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

use Sonata\MediaBundle\DependencyInjection\SonataMediaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SonataMediaExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadWithDefaultNoClassificationBundle()
    {
        $container = $this->getContainer();

        $this->assertFalse($container->hasDefinition('sonata.media.manager.category'));
    }

    public function testLoadWithDefaultAndCustomCategoryManager()
    {
        $container = $this->getContainer(
            array(array(
            'class'            => array('category' => '\stdClass'),
            'category_manager' => 'dummy.service.name',
        )));

        $this->assertTrue($container->hasAlias('sonata.media.manager.category'));
        $this->assertSame($container->getAlias('sonata.media.manager.category')->__toString(), 'dummy.service.name');
    }

    public function testLoadWithForceDisableTrueAndWithCategoryManager()
    {
        $container = $this->getContainer(
            array(array(
            'class'                  => array('category' => '\stdClass'),
            'category_manager'       => 'dummy.service.name',
            'force_disable_category' => true,
        )));

        $this->assertFalse($container->hasDefinition('sonata.media.manager.category'));
    }

    public function testMockClassificationBundleCategoryInterface()
    {
        require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'CategoryInterface.php';
        require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'CategoryManager.php';
        $this->assertTrue(interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface'));
    }

    /**
     * @depends testMockClassificationBundleCategoryInterface
     */
    public function testLoadWithDefaultAndClassificationBundleEnable()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('sonata.media.manager.category'));
        $this->assertSame($container->getDefinition('sonata.media.manager.category')->getClass(), 'Sonata\MediaBundle\Model\CategoryManager');
    }

    /**
     * @depends testMockClassificationBundleCategoryInterface
     */
    public function testLoadWithDefaultAndClassificationBundleEnableAndForceDisableCategory()
    {
        $container = $this->getContainer(array(array('force_disable_category' => true)));

        $this->assertFalse($container->hasDefinition('sonata.media.manager.category'));
    }

    /**
     * @depends testMockClassificationBundleCategoryInterface
     */
    public function testLoadWithDefaultAndClassificationBundleEnableAndCustomCategoryManager()
    {
        $container = $this->getContainer(
            array(array(
                'class'            => array('category' => '\stdClass'),
                'category_manager' => 'dummy.service.name',
            )));

        $this->assertTrue($container->hasAlias('sonata.media.manager.category'));
        $this->assertSame($container->getAlias('sonata.media.manager.category')->__toString(), 'dummy.service.name');
    }

    protected function getContainer(array $config = array())
    {
        $defaults = array(array(
            'default_context' => 'default',
            'db_driver'       => 'doctrine_orm',
            'contexts'        => array('default' => array('formats' => array('small' => array('width' => 100, 'quality' => 50)))),
            'filesystem'      => array('local' => array('directory' => '/tmp/')),
        ));

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array('SonataAdminBundle' => true));
        $container->setDefinition('translator', new Definition('\stdClass'));
        $container->setDefinition('security.context', new Definition('\stdClass'));
        $container->setDefinition('doctrine', new Definition('\stdClass'));

        if (isset($config[0]['category_manager'])) {
            $container->setDefinition($config[0]['category_manager'], new Definition('\stdClass'));
        }

        // Mock dependent load of service [SonataClassificationBundle]
        if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {
            $container->setDefinition('sonata.classification.manager.category', new Definition('Sonata\ClassificationBundle\Model\CategoryManager'));
        }

        $loader = new SonataMediaExtension();
        $loader->load(array_merge($defaults, $config), $container);
        $container->compile();

        return $container;
    }
}
