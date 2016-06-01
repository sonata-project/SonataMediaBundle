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

use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class Server implements CDNInterface
{

    protected $path;

    /**
     *
     * @var AssetsHelper
     */
    private $assetsHelper;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param AssetsHelper $assetsHelper
     */
    public function setAssetsHelper(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($relativePath, $isFlushable)
    {
        $path = sprintf('%s/%s', rtrim($this->path, '/'), ltrim($relativePath, '/'));
        return $this->assetsHelper->getUrl($path);
    }

    /**
     * {@inheritDoc}
     */
    public function flushByString($string)
    {
        // nothing to do
    }

    /**
     * {@inheritDoc}
     */
    public function flush($string)
    {
        // nothing to do
    }

    /**
     * {@inheritDoc}
     */
    public function flushPaths(array $paths)
    {
        // nothing to do
    }
}
