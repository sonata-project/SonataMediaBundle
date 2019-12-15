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

/**
 * SecurityContextCompilerPass.
 *
 * This compiler pass provides compatibility with Symfony < 2.6 security.context service
 * and 2.6+ security.authorization_checker service.
 *
 * NEXT_MAJOR: Remove this class
 *
 * @deprecated since sonata-project/media-bundle 3.22, to be removed in version 4.0.
 *
 * @final since sonata-project/media-bundle 3.21.0
 */
class SecurityContextCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Prefer the security.authorization_checker service
        if ($container->hasDefinition('security.authorization_checker')) {
            $security = $container->getDefinition('security.authorization_checker');
        } else {
            $security = $container->getDefinition('security.context');
        }

        $container->getDefinition('sonata.media.security.superadmin_strategy')
            ->replaceArgument(1, $security);

        $container->getDefinition('sonata.media.security.connected_strategy')
            ->replaceArgument(1, $security);
    }
}
