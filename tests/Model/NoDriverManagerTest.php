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
use Sonata\MediaBundle\Model\NoDriverManager;

class NoDriverManagerTest extends TestCase
{
    /**
     * @dataProvider providerMethods
     */
    public function testException($method, array $arguments): void
    {
        $this->expectException(NoDriverException::class);

        \call_user_func_array([new NoDriverManager(), $method], $arguments);
    }

    public function providerMethods()
    {
        return [
            ['getClass', []],
            ['findAll', []],
            ['findBy', [[]]],
            ['findOneBy', [[]]],
            ['find', [1]],
            ['create', []],
            ['save', [null]],
            ['delete', [null]],
            ['getTableName', []],
            ['getConnection', []],
        ];
    }
}
