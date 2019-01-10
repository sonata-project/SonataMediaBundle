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
use Symfony\Component\DependencyInjection\ContainerInterface;

class FixMediaContextCommandTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var ContainerAwareCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $tester;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Pool
     */
    private $pool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContextManagerInterface
     */
    private $contextManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->command = new FixMediaContextCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));

        $this->pool = $pool = $this->createMock(Pool::class);

        $this->contextManager = $contextManager = $this->createMock(ContextManagerInterface::class);

        $this->categoryManager = $categoryManager = $this->createMock(CategoryManagerInterface::class);

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($pool, $contextManager, $categoryManager) {
                switch ($id) {
                    case 'sonata.media.pool':
                        return $pool;
                    case 'sonata.classification.manager.context':
                        return $contextManager;
                    case 'sonata.media.manager.category':
                        return $categoryManager;
                }
            }));
    }

    public function testExecuteWithDisabledClassfication()
    {
        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(false));

        $this->expectException(\LogicException::class);

        $this->tester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteWithExisting()
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->will($this->returnValue($contextModel));

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteWithEmptyRoot()
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->will($this->returnValue($contextModel));

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue(null));
        $this->categoryManager->expects($this->once())->method('create')->will($this->returnValue($category));
        $this->categoryManager->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteWithNew()
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));

        $contextModel = $this->createMock(ContextInterface::class);

        $this->contextManager->expects($this->once())->method('findOneBy')->with($this->equalTo(['id' => 'foo']))->will($this->returnValue(null));
        $this->contextManager->expects($this->once())->method('create')->will($this->returnValue($contextModel));
        $this->contextManager->expects($this->once())->method('save')->with($this->equalTo($contextModel));

        $category = $this->createMock(CategoryInterface::class);

        $this->categoryManager->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue(null));
        $this->categoryManager->expects($this->once())->method('create')->will($this->returnValue($category));
        $this->categoryManager->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }
}
