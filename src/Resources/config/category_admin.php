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

use Sonata\MediaBundle\Admin\Extension\CategoryAdminExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set('sonata.media.admin_extension.category', CategoryAdminExtension::class)
            ->tag('sonata.admin.extension', ['target' => 'sonata.media.admin.media'])
            ->args([
                new ReferenceConfigurator('sonata.media.manager.category'),
                new ReferenceConfigurator('sonata.media.manager.context'),
            ]);
};
