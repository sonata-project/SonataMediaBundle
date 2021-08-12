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

namespace Sonata\MediaBundle\Tests\Admin;

use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @phpstan-template T of MediaInterface
 * @phpstan-extends BaseMediaAdmin<T>
 */
class TestMediaAdmin extends BaseMediaAdmin
{
}
