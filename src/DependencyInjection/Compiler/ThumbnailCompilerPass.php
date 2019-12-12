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

namespace Sonata\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ThumbnailCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonata.media.thumbnail.format')) {
            return;
        }

        $definition = $container->getDefinition(
            'sonata.media.thumbnail.format'
        );

        if (!\is_callable([$container->getParameterBag()->resolveValue($definition->getClass()), 'addResizer'])) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(
            'sonata.media.resizer'
        );

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addResizer',
                [$id, new Reference($id)]
            );
        }
    }
}
