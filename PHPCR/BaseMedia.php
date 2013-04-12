<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\PHPCR;

use Sonata\MediaBundle\Model\Media;

abstract class BaseMedia extends Media
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    private $uuid;

    /**
     * The basepath of the id
     *
     * @var string
     */
    protected $idPrefix;

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the basepath of the id
     *
     * @return string
     */
    public function getIdPrefix()
    {
        return $this->idPrefix;
    }

    /**
     * Set the basepath of the id
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
    }

    /**
     * Get universal unique id
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
