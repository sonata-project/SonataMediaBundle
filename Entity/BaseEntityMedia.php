<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Entity;

/**
 * Bundle\MediaBundle\Entity\BaseEntityMedia
 */
class BaseEntityMedia
{
    /**
     * @var string $classname
     */
    private $classname;

    /**
     * @var integer $object_id
     */
    private $object_id;

    /**
     * @var boolean $enabled
     */
    private $enabled;

    /**
     * @var boolean $is_main
     */
    private $is_main;

    /**
     * @var integer $order
     */
    private $order;

    /**
     * @var datetime $updated_at
     */
    private $updated_at;

    /**
     * @var datetime $created_at
     */
    private $created_at;

    /**
     * Set classname
     *
     * @param string $classname
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
    }

    /**
     * Get classname
     *
     * @return string $classname
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * Set object_id
     *
     * @param integer $objectId
     */
    public function setObjectId($objectId)
    {
        $this->object_id = $objectId;
    }

    /**
     * Get object_id
     *
     * @return integer $objectId
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set is_main
     *
     * @param boolean $isMain
     */
    public function setIsMain($isMain)
    {
        $this->is_main = $isMain;
    }

    /**
     * Get is_main
     *
     * @return boolean $isMain
     */
    public function getIsMain()
    {
        return $this->is_main;
    }

    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}