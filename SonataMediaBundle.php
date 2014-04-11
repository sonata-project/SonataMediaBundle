<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle;

use Sonata\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;

class SonataMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddProviderCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // this is required by the AWS SDK (see: https://github.com/knplabs/Gaufrette)
        if (!defined("AWS_CERTIFICATE_AUTHORITY")) {
            define("AWS_CERTIFICATE_AUTHORITY", true);
        }
    }
}
