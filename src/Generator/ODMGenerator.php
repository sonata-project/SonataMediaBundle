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

namespace Sonata\MediaBundle\Generator;

if (!class_exists(UuidGenerator::class, false)) {
    @trigger_error(sprintf(
        'The %s\ODMGenerator class is deprecated since sonata/media-bundle 3.4 and will be removed in 4.0.'
        .' Use \Sonata\MediaBundle\Generator\UuidGenerator instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(UuidGenerator::class, ODMGenerator::class);

if (false) {
    /**
     * @deprecated 3.4 Use Sonata\MediaBundle\Generator\UuidGenerator
     */
    class ODMGenerator extends UuidGenerator implements GeneratorInterface
    {
    }
}
