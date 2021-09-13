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

use Sonata\MediaBundle\Thumbnail\ResizableThumbnailInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class ThumbnailCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sonata.media.thumbnail.format')) {
            return;
        }

        $definition = $container->getDefinition('sonata.media.thumbnail.format');
        $resolvedClass = $container->getParameterBag()->resolveValue($definition->getClass());

        if (!is_a($resolvedClass, ResizableThumbnailInterface::class, true)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('sonata.media.resizer');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addResizer', [$id, new Reference($id)]);
        }
    }
}
