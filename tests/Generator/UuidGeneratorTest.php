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
use Sonata\MediaBundle\Generator\UuidGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

final class UuidGeneratorTest extends TestCase
{
    public function testUuidGenerator(): void
    {
        $generator = new UuidGenerator();

        $media = new Media();
        $media->setContext('user');

        $media->setId('550e8400-e29b-41d4-a716-446655440000');
        $this->assertSame('user/550e/84', $generator->generatePath($media));
    }
}
