<?php

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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GlobalVariables.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pixlr|bool
     */
    public function getPixlr()
    {
        return $this->container->has('sonata.media.extra.pixlr') ? $this->container->get('sonata.media.extra.pixlr') : false;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->container->get('sonata.media.pool');
    }
}
