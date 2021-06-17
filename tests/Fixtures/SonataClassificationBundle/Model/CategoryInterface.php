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

use Doctrine\Common\Collections\Collection as DoctrineCollection;

interface CategoryInterface
{
    /**
     * @return mixed
     */
    public function getId();

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
     * Set slug.
     *
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * Get slug.
     *
     * @return string $slug
     */
    public function getSlug();

    /**
     * Set description.
     *
     * @param string|null $description
     */
    public function setDescription($description);

    /**
     * Get description.
     *
     * @return string|null $description
     */
    public function getDescription();

    /**
     * @param int|null $position
     */
    public function setPosition($position);

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * Add Children.
     *
     * @param CategoryInterface $child
     * @param bool              $nested
     */
    public function addChild(self $child, $nested = false);

    /**
     * Get Children.
     *
     * @return DoctrineCollection|CategoryInterface[] $children
     */
    public function getChildren();

    /**
     * Set children.
     *
     * @param array $children
     */
    public function setChildren($children);

    /**
     * Return true if category has children.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Set Parent.
     *
     * @param CategoryInterface|null $parent
     * @param bool                   $nested
     */
    public function setParent(?self $parent = null, $nested = false);

    /**
     * Get Parent.
     *
     * @return CategoryInterface|null $parent
     */
    public function getParent();

    public function setContext(ContextInterface $context);

    /**
     * @return ContextInterface
     */
    public function getContext();
}
