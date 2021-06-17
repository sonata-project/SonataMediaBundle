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

namespace Sonata\MediaBundle\Twig;

use Sonata\MediaBundle\Extra\Pixlr;
use Sonata\MediaBundle\Provider\Pool;

/**
 * GlobalVariables.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GlobalVariables
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Pixlr|null
     */
    private $pixlr;

    public function __construct(Pool $pool, ?Pixlr $pixlr = null)
    {
        $this->pool = $pool;
        $this->pixlr = $pixlr;
    }

    /**
     * @return Pixlr|bool
     */
    public function getPixlr()
    {
        return null !== $this->pixlr ? $this->pixlr : false;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->pool;
    }
}
