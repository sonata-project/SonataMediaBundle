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

use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->alias('sonata.media.manager.category', 'sonata.classification.manager.category')

        ->alias('sonata.media.manager.context', 'sonata.classification.manager.context')

        ->alias(CategoryManagerInterface::class, 'sonata.media.manager.category')

        ->alias(ContextManagerInterface::class, 'sonata.media.manager.context');
};
