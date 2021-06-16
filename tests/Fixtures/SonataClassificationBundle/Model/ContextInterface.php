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

namespace Sonata\ClassificationBundle\Model;

interface ContextInterface
{
    public const DEFAULT_CONTEXT = 'default';

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId($id);

    /**
     * Get id.
     *
     * @return string $id
     */
    public function getId();

    /**
     * Set created_at.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * Get created_at.
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updated_at.
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * Get updated_at.
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt();
}
