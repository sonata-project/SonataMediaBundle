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

use Sonata\CoreBundle\Form\FormHelper;
use Sonata\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\MediaBundle\DependencyInjection\Compiler\ThumbnailCompilerPass;
use Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType;
use Sonata\MediaBundle\Form\Type\ApiGalleryHasMediaType;
use Sonata\MediaBundle\Form\Type\ApiGalleryType;
use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class SonataMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddProviderCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new ThumbnailCompilerPass());

        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // this is required by the AWS SDK (see: https://github.com/knplabs/Gaufrette)
        if (!\defined('AWS_CERTIFICATE_AUTHORITY')) {
            \define('AWS_CERTIFICATE_AUTHORITY', true);
        }

        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping([
            'sonata_media_type' => MediaType::class,
            'sonata_media_api_form_media' => ApiMediaType::class,
            'sonata_media_api_form_doctrine_media' => ApiDoctrineMediaType::class,
            'sonata_media_api_form_gallery' => ApiGalleryType::class,
            'sonata_media_api_form_gallery_has_media' => ApiGalleryHasMediaType::class,
        ]);
    }
}
