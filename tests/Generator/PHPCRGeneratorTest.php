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
use Sonata\MediaBundle\Generator\PHPCRGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

/**
 * @group legacy
 */
class PHPCRGeneratorTest extends TestCase
{
    public function testPHPCRGenerator(): void
    {
        $generator = new PHPCRGenerator();

        $media = new Media();
        $media->setContext('user');

        $media->setId('nodename');
        self::assertSame('user', $generator->generatePath($media));

        $media->setId('/media/nodename');
        self::assertSame('user/media', $generator->generatePath($media));

        $media->setId('/media/sub/path/nodename');
        self::assertSame('user/media/sub/path', $generator->generatePath($media));
    }
}
