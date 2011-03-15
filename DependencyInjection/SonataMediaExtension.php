<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;

use Symfony\Component\Finder\Finder;

/**
 * MediaExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataMediaExtension extends Extension
{

    /**
     * Loads the url shortener configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.xml');
        $loader->load('provider.xml');
        $loader->load('media.xml');

        $config = call_user_func_array('array_merge_recursive', $config);

        $definition = $container->getDefinition('sonata.media.pool');
        $definition->addMethodCall('setSettings', $config);

        // register template helper
        $definition = new Definition(
            'Sonata\MediaBundle\Templating\Helper\MediaHelper',
            array(
                 new Reference('sonata.media.pool'),
                 new Reference('templating')
            )
        );
        $definition->addTag('templating.helper', array('alias' => 'media'));
        $definition->addTag('templating.helper', array('alias' => 'thumbnail'));

        $container->setDefinition('templating.helper.media', $definition);

        // register the twig extension
        $container
            ->register('twig.extension.media', 'Sonata\MediaBundle\Twig\Extension\MediaExtension')
            ->addTag('twig.extension');

    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {

        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {

        return 'http://www.sonata-project.org/schema/dic/media';
    }

    public function getAlias()
    {

        return "sonata_media";
    }
}