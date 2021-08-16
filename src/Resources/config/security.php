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

use Sonata\MediaBundle\Security\ForbiddenDownloadStrategy;
use Sonata\MediaBundle\Security\PublicDownloadStrategy;
use Sonata\MediaBundle\Security\RolesDownloadStrategy;
use Sonata\MediaBundle\Security\SessionDownloadStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.security.public_strategy', PublicDownloadStrategy::class)
            ->args([
                new ReferenceConfigurator('translator'),
            ])

        ->set('sonata.media.security.forbidden_strategy', ForbiddenDownloadStrategy::class)
            ->args([
                new ReferenceConfigurator('translator'),
            ])

        ->set('sonata.media.security.superadmin_strategy', RolesDownloadStrategy::class)
            ->args([
                new ReferenceConfigurator('translator'),
                new ReferenceConfigurator('security.authorization_checker'),
                ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'],
            ])

        ->set('sonata.media.security.session_strategy', SessionDownloadStrategy::class)
            ->args([
                new ReferenceConfigurator('translator'),
                new ReferenceConfigurator('request_stack'),
                1,
            ])

        ->set('sonata.media.security.connected_strategy', RolesDownloadStrategy::class)
            ->args([
                new ReferenceConfigurator('translator'),
                new ReferenceConfigurator('security.authorization_checker'),
                ['IS_AUTHENTICATED_FULLY', 'IS_AUTHENTICATED_REMEMBERED'],
            ]);
};
