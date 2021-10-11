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
    private Application $application;

    private FixMediaContextCommand $command;

    private CommandTester $tester;

    private Pool $pool;

    /**
     * @var MockObject&ContextManagerInterface
     */
    private MockObject $contextManager;

    /**
     * @var MockObject&CategoryManagerInterface
     */
    private MockObject $categoryManager;

    protected function setUp(): void
    {
        $this->pool = new Pool('default');
        $this->contextManager = $this->createMock(ContextManagerInterface::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);

        $this->command = new FixMediaContextCommand($this->pool, $this->categoryManager, $this->contextManager);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));
    }

    public function testExecuteWithDisabledClassification(): void
    {
        $pool = new Pool('default');

        $command = new FixMediaContextCommand($pool, null, null);

        $application = new Application();
        $application->add($command);

        $this->expectException(\LogicException::class);

        $tester = new CommandTester($application->find('sonata:media:fix-media-context'));

        $tester->execute(['command' => $command->getName()]);
    }

    public function testExecuteWithExisting(): void
    {
        $this->pool->addContext('foo');

        $contextModel = new Context();

        $this->contextManager->expects(static::once())->method('find')->with('foo')->willReturn($contextModel);
        $this->categoryManager->expects(static::once())->method('getRootCategoriesForContext')->with($contextModel);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@Done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteWithNew(): void
    {
        $this->pool->addContext('foo');

        $contextModel = new Context();

        $this->contextManager->expects(static::once())->method('find')->with('foo')->willReturn(null);
        $this->contextManager->expects(static::once())->method('create')->willReturn($contextModel);
        $this->contextManager->expects(static::once())->method('save')->with($contextModel);

        $this->categoryManager->expects(static::once())->method('getRootCategoriesForContext')->with($contextModel);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@ > default context for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }
}
