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

use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimensionValidator;
use Sonata\MediaBundle\Validator\FormatValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.validator.format', FormatValidator::class)
            ->tag('validator.constraint_validator', ['alias' => 'sonata.media.validator.format'])
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
            ])

        ->set(ImageUploadDimensionValidator::class)
            ->tag('validator.constraint_validator')
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.image.default'),
                new ReferenceConfigurator('sonata.media.provider.image'),
            ]);
};
