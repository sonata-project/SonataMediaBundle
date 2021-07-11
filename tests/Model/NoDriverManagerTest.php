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
use Sonata\MediaBundle\Model\NoDriverManager;

class NoDriverManagerTest extends TestCase
{
    /**
     * @dataProvider providerMethods
     *
     * @param mixed[] $arguments
     */
    public function testException(string $method, array $arguments): void
    {
        $this->expectException(NoDriverException::class);

        \call_user_func_array([new NoDriverManager(), $method], $arguments);
    }

    public function testIsInstanceOfGalleryManagerInterface(): void
    {
        $this->assertInstanceOf(GalleryManagerInterface::class, new NoDriverManager());
    }

    /**
     * @phpstan-return iterable<array{0: string, 1: mixed[]}>
     */
    public function providerMethods(): iterable
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
        yield ['getPager', [[], 1]];
    }
}
