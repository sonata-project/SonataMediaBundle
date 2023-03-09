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

namespace Sonata\MediaBundle\Tests\App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Entity\BaseGallery;
use Sonata\MediaBundle\Model\GalleryItemInterface;

/**
 * @phpstan-extends BaseGallery<GalleryItemInterface>
 */
#[ORM\Entity]
#[ORM\Table(name: 'media__gallery')]
class Gallery extends BaseGallery
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    public function getId()
    {
        return $this->id;
    }
}
