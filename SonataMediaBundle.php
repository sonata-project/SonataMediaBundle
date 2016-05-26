<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle;

use Sonata\CoreBundle\Form\FormHelper;
use Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\SecurityContextCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddProviderCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new SecurityContextCompilerPass());

        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // this is required by the AWS SDK (see: https://github.com/knplabs/Gaufrette)
        if (!defined('AWS_CERTIFICATE_AUTHORITY')) {
            define('AWS_CERTIFICATE_AUTHORITY', true);
        }

        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping(array(
            'sonata_media_type' => 'Sonata\MediaBundle\Form\Type\MediaType',
            'sonata_media_api_form_media' => 'Sonata\MediaBundle\Form\Type\ApiMediaType',
            'sonata_media_api_form_doctrine_media' => 'Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType',
            'sonata_media_api_form_gallery' => 'Sonata\MediaBundle\Form\Type\ApiGalleryType',
            'sonata_media_api_form_gallery_has_media' => 'Sonata\MediaBundle\Form\Type\ApiGalleryHasMediaType',
        ));
    }
}
