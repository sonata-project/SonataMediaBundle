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
     * NEXT_MAJOR: remove this method.
     *
     * @return Pixlr|bool
     *
     * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
     */
    public function getPixlr()
    {
        return null !== $this->pixlr ? $this->pixlr : false;
    }

    public function getPool(): Pool
    {
        return $this->pool;
    }
}
