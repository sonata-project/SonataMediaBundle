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
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\ContextInterface;
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

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new Container();

        $this->command = new FixMediaContextCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));

        $this->pool = $pool = $this->createMock(Pool::class);

        $this->contextManager = $contextManager = $this->createMock(ContextManagerInterface::class);

        $this->categoryManager = $categoryManager = $this->createMock(CategoryManagerInterface::class);

        $this->container->set('sonata.media.pool', $pool);
        $this->container->set('sonata.classification.manager.context', $contextManager);
        $this->container->set('sonata.media.manager.category', $categoryManager);
    }

    public function testExecuteWithDisabledClassification(): void
    {
        $this->container->set('sonata.media.manager.category', null);

        $this->expectException(\LogicException::class);

        $this->tester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteWithExisting(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->willReturn($contextModel);

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->willReturn($category);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteWithEmptyRoot(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->willReturn($contextModel);

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->willReturn(null);
        $this->categoryManager->expects($this->once())->method('create')->willReturn($category);
        $this->categoryManager->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

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

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->willReturn(null);
        $this->contextManager->expects($this->once())->method('create')->willReturn($contextModel);
        $this->contextManager->expects($this->once())->method('save')->with($this->equalTo($contextModel));

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->willReturn(null);
        $this->categoryManager->expects($this->once())->method('create')->willReturn($category);
        $this->categoryManager->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }
}
