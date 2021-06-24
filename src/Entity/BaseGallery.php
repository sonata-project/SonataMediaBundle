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

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\Gallery;

abstract class BaseGallery extends Gallery
{
    public function __construct()
    {
        $this->galleryItems = new ArrayCollection();
    }

    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
