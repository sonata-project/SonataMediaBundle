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

use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="media__foo")
 */
class Foo
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Media")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private ?MediaInterface $media = null;

    public function getId()
    {
        return $this->id;
    }

    public function setMedia(?MediaInterface $media): void
    {
        $this->media = $media;
    }

    public function getMedia(): ?MediaInterface
    {
        return $this->media;
    }
}
