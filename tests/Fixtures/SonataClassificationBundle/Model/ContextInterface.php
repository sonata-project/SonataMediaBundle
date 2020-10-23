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
}
