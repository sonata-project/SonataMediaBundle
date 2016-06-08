<?php

/*
 * This file is part of the Sonata Project package.
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
     * {@inheritdoc}
     */
    public function getPath($relativePath, $isFlushable)
    {
        $path = sprintf('%s/%s', rtrim($this->path, '/'), ltrim($relativePath, '/'));

        return $this->assetsHelper->getUrl($path);
    }

    /**
     * {@inheritdoc}
     */
    public function flushByString($string)
    {
        // nothing to do
    }

    /**
     * {@inheritdoc}
     */
    public function flush($string)
    {
        // nothing to do
    }

    /**
     * {@inheritdoc}
     */
    public function flushPaths(array $paths)
    {
        // nothing to do
    }
}
