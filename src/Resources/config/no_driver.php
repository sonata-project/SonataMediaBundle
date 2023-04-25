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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\MediaBundle\Generator\NoDriverGenerator;
use Sonata\MediaBundle\Model\NoDriverGalleryManager;
use Sonata\MediaBundle\Model\NoDriverMediaManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.manager.media', NoDriverMediaManager::class)

        ->set('sonata.media.manager.gallery', NoDriverGalleryManager::class)

        ->set('sonata.media.generator.default', NoDriverGenerator::class);
};
