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

namespace Sonata\MediaBundle\Tests\Controller\Api;

class GalleryTest
{
    /**
     * @var int
     */
    private $id;

    public function __construct()
    {
        $this->id = random_int(0, getrandmax());
    }

    public function getId(): int
    {
        return $this->id;
    }
}
