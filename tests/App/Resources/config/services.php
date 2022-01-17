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

use Psr\Container\ContainerInterface;
use Sonata\MediaBundle\Tests\App\Admin\FooAdmin;
use Sonata\MediaBundle\Tests\App\Controller\TruncateController;
use Sonata\MediaBundle\Tests\App\Entity\Foo;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set(FooAdmin::class)
            ->public()
            ->args([
                null,
                Foo::class,
                null
            ])
            ->tag('sonata.admin', ['manager_type' => 'orm'])

        ->set(TruncateController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [new ReferenceConfigurator(ContainerInterface::class)]);
};
