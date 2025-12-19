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

use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

return static function (RoutingConfigurator $routes) {
    foreach (debug_backtrace() as $trace) {
        if (isset($trace['object'], $trace['args'])
            /* @phpstan-ignore-next-line */
            && $trace['object'] instanceof XmlFileLoader
            && $trace['args'][0] === __DIR__.'/media.php'
            && $trace['args'][3] === __DIR__.'/media.xml'
        ) {
            @trigger_error(
                sprintf(
                    'The "%s/media.xml" routing configuration is deprecated since sonata-project/media-bundle 4.19. Import "media.php" instead.',
                    __DIR__,
                ),
                \E_USER_DEPRECATED
            );

            break;
        }
    }

    $routes->add('sonata_media_download', '/download/{id}/{format}')
        ->controller('sonata.media.action.media_download')
        ->defaults(['format' => MediaProviderInterface::FORMAT_REFERENCE])
        ->requirements(['id' => '.*']);
};
