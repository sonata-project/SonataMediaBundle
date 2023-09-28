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

namespace Sonata\MediaBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Exception\NoDriverException;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\NoDriverGalleryManager;

class NoDriverGalleryManagerTest extends TestCase
{
    /**
     * @dataProvider provideExceptionCases
     *
     * @param mixed[] $arguments
     */
    public function testException(string $method, array $arguments): void
    {
        $this->expectException(NoDriverException::class);

        (new NoDriverGalleryManager())->$method(...$arguments);
    }

    public function testIsInstanceOfGalleryManagerInterface(): void
    {
        static::assertInstanceOf(GalleryManagerInterface::class, new NoDriverGalleryManager());
    }

    /**
     * @phpstan-return iterable<array{string, mixed[]}>
     */
    public function provideExceptionCases(): iterable
    {
        yield ['getClass', []];
        yield ['findAll', []];
        yield ['findBy', [[]]];
        yield ['findOneBy', [[]]];
        yield ['find', [1]];
        yield ['create', []];
        yield ['save', [null]];
        yield ['delete', [null]];
        yield ['getTableName', []];
        yield ['getConnection', []];
    }
}
