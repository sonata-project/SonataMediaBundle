<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Command;

use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tests\Command\CommandTest;

class FixMediaContextCommandTest extends CommandTest
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
    private $contextManger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CategoryManagerInterface
     */
    private $categoryManger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->command = new FixMediaContextCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));

        $this->pool = $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();

        $this->contextManger = $contextManger = $this->getMock('Sonata\ClassificationBundle\Model\ContextManagerInterface');

        $this->categoryManger = $categoryManger = $this->getMockBuilder('Sonata\ClassificationBundle\Entity\CategoryManager')->disableOriginalConstructor()->getMock();

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($pool, $contextManger, $categoryManger) {
                switch ($id) {
                    case 'sonata.media.pool':
                        return $pool;
                    case 'sonata.classification.manager.context':
                        return $contextManger;
                    case 'sonata.classification.manager.category':
                        return $categoryManger;
                }

                return;
            }));
    }

    public function testExecuteWithExisting()
    {
        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMock('Sonata\ClassificationBundle\Model\ContextInterface');

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue($contextModel));

        $category = $this->getMock('Sonata\ClassificationBundle\Model\CategoryInterface');

        $this->categoryManger->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue($category));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteWithEmptyRoot()
    {
        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMock('Sonata\ClassificationBundle\Model\ContextInterface');

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue($contextModel));

        $category = $this->getMock('Sonata\ClassificationBundle\Model\CategoryInterface');

        $this->categoryManger->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue(null));
        $this->categoryManger->expects($this->once())->method('create')->will($this->returnValue($category));
        $this->categoryManger->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteWithNew()
    {
        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMock('Sonata\ClassificationBundle\Model\ContextInterface');

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue(null));
        $this->contextManger->expects($this->once())->method('create')->will($this->returnValue($contextModel));
        $this->contextManger->expects($this->once())->method('save')->with($this->equalTo($contextModel));

        $category = $this->getMock('Sonata\ClassificationBundle\Model\CategoryInterface');

        $this->categoryManger->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue(null));
        $this->categoryManger->expects($this->once())->method('create')->will($this->returnValue($category));
        $this->categoryManger->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }
}
