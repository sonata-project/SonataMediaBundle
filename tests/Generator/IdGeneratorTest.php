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
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

final class IdGeneratorTest extends TestCase
{
    public function testIdGenerator(): void
    {
        $generator = new IdGenerator();

        $media = new Media();
        $media->setContext('user');

        $media->setId(10);
        static::assertSame('user/0001/01', $generator->generatePath($media));

        $media->setId(10000);
        static::assertSame('user/0001/11', $generator->generatePath($media));

        $media->setId(12_341_230);
        static::assertSame('user/0124/42', $generator->generatePath($media));

        $media->setId(999_999_999);
        static::assertSame('user/10000/100', $generator->generatePath($media));
    }
}
