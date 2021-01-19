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

namespace Sonata\MediaBundle\Tests\Filesystem;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Filesystem\Local;

class LocalTest extends TestCase
{
    public function testReplicate(): void
    {
        $local = new Local('/tmp');

        // check if OS is Mac OS X where /tmp is a symlink to /private/tmp
        $result = 'Darwin' === \PHP_OS ? '/private/tmp' : '/tmp';

        $this->assertSame($result, $local->getDirectory());
    }
}
