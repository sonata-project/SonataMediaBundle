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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Context;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FixMediaContextCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var FixMediaContextCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var MockObject&Pool
     */
    private $pool;

    /**
     * @var MockObject&ContextManagerInterface
     */
    private $contextManager;

    /**
     * @var MockObject&CategoryManagerInterface
     */
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

    public function testExecuteWithNewContext(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = new Context();

        $this->contextManager->expects($this->once())->method('find')->with('foo')->willReturn($contextModel);
        $this->categoryManager->expects($this->once())->method('getRootCategoriesForContext')->with($contextModel);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertMatchesRegularExpression('@ > default context for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
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

        $this->contextManager->expects($this->once())->method('find')->with('foo')->willReturn(null);
        $this->contextManager->expects($this->once())->method('create')->willReturn($contextModel);
        $this->contextManager->expects($this->once())->method('save')->with($contextModel);

        $this->categoryManager->expects($this->once())->method('getRootCategoriesForContext')->with($contextModel);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertMatchesRegularExpression('@ > default context for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }
}
