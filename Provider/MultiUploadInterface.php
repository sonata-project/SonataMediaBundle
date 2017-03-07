<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Builder\FormContractor;
use Symfony\Component\HttpFoundation\Request;

 /**
 * Interface MultiUploadInterface.
 *
 * @author Maximilian Behrsing <m.behrsing@gmail.com>
 */
interface MultiUploadInterface
{
    /*
     * @param Request        $request
     * @param FormContractor $formContractor
     * @param string         $context
     *
     * @return array
     */
    public function configureMultiUpload(FormMapper $formMapper);
}
