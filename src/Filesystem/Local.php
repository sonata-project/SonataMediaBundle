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

namespace Sonata\MediaBundle\Filesystem;

use Gaufrette\Adapter\Local as BaseLocal;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Local extends BaseLocal
{
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
