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

/**
 * @phpstan-template T of ContextInterface
 */
interface CategoryInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @phpstan-return T|null
     */
    public function getContext(): ?ContextInterface;

    /**
     * @return static
     *
     * @phpstan-param T $context
     */
    public function setContext(ContextInterface $context);

    public function getName(): ?string;

    /**
     * @return static
     */
    public function setName(string $name);

    public function getEnabled(): ?bool;

    /**
     * @return static
     */
    public function setEnabled(bool $enabled);

    public function getPosition(): ?int;

    /**
     * @return static
     */
    public function setPosition(int $position);
}
