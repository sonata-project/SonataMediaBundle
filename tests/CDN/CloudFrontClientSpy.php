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

namespace Sonata\MediaBundle\Tests\CDN;

use Aws\CloudFront\CloudFrontClient;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CloudFrontClientSpy extends CloudFrontClient
{
    public function createInvalidation()
    {
        return parent::createInvalidation();
    }

    public function getInvalidation()
    {
        return parent::getInvalidation();
    }
}
