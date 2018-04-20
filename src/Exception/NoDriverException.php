<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Exception;

/**
 * @author Andrey F. Mindubaev <covex.mobile@gmail.com>
 */
class NoDriverException extends \RuntimeException
{
    const DEFAULT_MESSAGE = 'The child node "db_driver" at path "sonata_media" must be configured.';

    public function __construct($message = self::DEFAULT_MESSAGE, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
