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

namespace Sonata\MediaBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Generator\PathGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

final class PathGeneratorTest extends TestCase
{
    public function testPathGenerator(): void
    {
        $generator = new PathGenerator();

        $media = new Media();
        $media->setContext('user');

        $media->setId('nodename');
        $this->assertSame('user', $generator->generatePath($media));

        $media->setId('/media/nodename');
        $this->assertSame('user/media', $generator->generatePath($media));

        $media->setId('/media/sub/path/nodename');
        $this->assertSame('user/media/sub/path', $generator->generatePath($media));
    }
}
