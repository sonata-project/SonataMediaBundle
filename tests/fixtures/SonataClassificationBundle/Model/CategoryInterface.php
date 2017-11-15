<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\ClassificationBundle\Model;

interface CategoryInterface
{
    public function getContext();

    public function setContext($context);

    public function setName($name);

    public function setEnabled($enabled);

    public function setPosition($position);
}
