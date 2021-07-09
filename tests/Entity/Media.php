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

namespace Sonata\MediaBundle\Tests\Entity;

use Sonata\MediaBundle\Entity\BaseMedia;

class Media extends BaseMedia
{
    /**
     * @var int|string|object|null
     */
    protected $id;

    /**
     * @param int|string|object|null $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
