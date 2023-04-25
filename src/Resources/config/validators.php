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

use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimensionValidator;
use Sonata\MediaBundle\Validator\FormatValidator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.validator.format', FormatValidator::class)
            ->tag('validator.constraint_validator', ['alias' => 'sonata.media.validator.format'])
            ->args([
                service('sonata.media.pool'),
            ])

        ->set(ImageUploadDimensionValidator::class)
            ->tag('validator.constraint_validator')
            ->args([
                service('sonata.media.adapter.image.default'),
                service('sonata.media.provider.image'),
            ]);
};
