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

class Category implements CategoryInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var ContextInterface|null
     */
    private $context;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool|null
     */
    private $enabled;

    /**
     * @var int|null
     */
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    public function setContext(ContextInterface $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
