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

namespace Sonata\MediaBundle\Entity;

use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;

/**
 * @phpstan-extends BaseEntityManager<MediaInterface>
 */
final class MediaManager extends BaseEntityManager implements MediaManagerInterface
{
}
