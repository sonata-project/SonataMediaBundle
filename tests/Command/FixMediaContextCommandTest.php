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

namespace Sonata\MediaBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\Category;
use Sonata\ClassificationBundle\Model\Context;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

class FixMediaContextCommandTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var FixMediaContextCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $tester;

    private $pool;

    private $contextManager;

    private $categoryManager;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(Pool::class);
        $this->contextManager = $this->createMock(ContextManagerInterface::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);

        $this->command = new FixMediaContextCommand($this->pool, $this->categoryManager, $this->contextManager);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));
    }

    public function testExecuteWithDisabledClassification(): void
    {
        $pool = $this->createStub(Pool::class);

        $command = new FixMediaContextCommand($pool, null, null);

        $application = new Application();
        $application->add($command);

        $this->expectException(\LogicException::class);

        $tester = new CommandTester($application->find('sonata:media:fix-media-context'));

        $tester->execute(['command' => $command->getName()]);
    }

    public function testExecuteWithExisting(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = new Context();

        $this->contextManager->expects(static::once())->method('findOneBy')->with(static::equalTo(['id' => 'foo']))->willReturn($contextModel);

        $category = new Category();

        $this->categoryManager->expects(static::once())->method('getRootCategory')->with(static::equalTo($contextModel))->willReturn($category);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@Done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteWithEmptyRoot(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = new Context();

        $this->contextManager->expects(static::once())->method('findOneBy')->with(static::equalTo(['id' => 'foo']))->willReturn($contextModel);

        $category = new Category();

        $this->categoryManager->expects(static::once())->method('getRootCategory')->with(static::equalTo($contextModel))->willReturn(null);
        $this->categoryManager->expects(static::once())->method('create')->willReturn($category);
        $this->categoryManager->expects(static::once())->method('save')->with(static::equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteWithNew(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = new Context();

        $this->contextManager->expects(static::once())->method('findOneBy')->with(static::equalTo(['id' => 'foo']))->willReturn(null);
        $this->contextManager->expects(static::once())->method('create')->willReturn($contextModel);
        $this->contextManager->expects(static::once())->method('save')->with(static::equalTo($contextModel));

        $category = new Category();

        $this->categoryManager->expects(static::once())->method('getRootCategory')->with(static::equalTo($contextModel))->willReturn(null);
        $this->categoryManager->expects(static::once())->method('create')->willReturn($category);
        $this->categoryManager->expects(static::once())->method('save')->with(static::equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }
}
