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

namespace Sonata\MediaBundle\Tests\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\RestBundle\FOSRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
use Sonata\MediaBundle\SonataMediaBundle;
use Sonata\SeoBundle\SonataSeoBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles()
    {
        return [
            new DoctrineBundle(),
            new FOSRestBundle(),
            new FrameworkBundle(),
            new JMSSerializerBundle(),
            new KnpMenuBundle(),
            new NelmioApiDocBundle(),
            new SecurityBundle(),
            new SonataAdminBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataDoctrineORMAdminBundle(),
            new SonataMediaBundle(),
            new SonataSeoBundle(),
            new TwigBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * TODO: Drop RouteCollectionBuilder when support for Symfony < 5.1 is dropped.
     *
     * @param RoutingConfigurator|RouteCollectionBuilder $routes
     */
    protected function configureRoutes($routes): void
    {
        if ($routes instanceof RouteCollectionBuilder) {
            $routes->import(__DIR__.'/Resources/config/routing/routes.yml', '/', 'yaml');
            $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v3.yml', '/', 'yaml');

            return;
        }

        $routes->import(__DIR__.'/Resources/config/routing/routes.yml');
        $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v3.yml');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/Resources/config/config.yml');

        if (class_exists(AuthenticatorManager::class)) {
            $loader->load(__DIR__.'/Resources/config/config_symfony_v5.yml');
        }

        $loader->load(__DIR__.'/Resources/config/services.php');
        $container->setParameter('app.base_dir', $this->getBaseDir());
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-media-bundle/var/';
    }
}
