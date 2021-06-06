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

namespace Sonata\MediaBundle;

use Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\ThumbnailCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SonataMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddProviderCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new ThumbnailCompilerPass());
        $container->addCompilerPass(new TwigStringExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }

    public function boot(): void
    {
        // this is required by the AWS SDK (see: https://github.com/knplabs/Gaufrette)
        if (!\defined('AWS_CERTIFICATE_AUTHORITY')) {
            \define('AWS_CERTIFICATE_AUTHORITY', true);
        }
    }
}
