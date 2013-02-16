<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\CDN;

class Fallback implements CDNInterface
{
    protected $cdn;

    protected $fallback;

    /**
     * @param CDNInterface $cdn
     * @param CDNInterface $fallback
     */
    public function __construct(CDNInterface $cdn, CDNInterface $fallback)
    {
        $this->cdn      = $cdn;
        $this->fallback = $fallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($relativePath, $isFlushable)
    {
        if ($isFlushable) {
            return $this->fallback->getPath($relativePath, $isFlushable);
        }

        return $this->cdn->getPath($relativePath, $isFlushable);
    }

    /**
     * {@inheritdoc}
     */
    public function flushByString($string)
    {
        $this->cdn->flushByString($string);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($string)
    {
        $this->cdn->flush($string);
    }

    /**
     * {@inheritdoc}
     */
    public function flushPaths(array $paths)
    {
        $this->cdn->flushPaths($paths);
    }
}
