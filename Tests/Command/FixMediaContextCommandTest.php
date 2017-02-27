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

use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FixMediaContextCommandTest extends PHPUnit_Framework_TestCase
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
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();

        $this->command = new FixMediaContextCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:fix-media-context'));

        $this->pool = $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $this->contextManger = $contextManger = $this->getMockBuilder('Sonata\ClassificationBundle\Model\ContextManagerInterface')->getMock();
        $this->categoryManger = $categoryManger = $this->createMock('Sonata\MediaBundle\Model\CategoryManagerInterface');

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($pool, $contextManger, $categoryManger) {
                switch ($id) {
                    case 'sonata.media.pool':
                        return $pool;
                    case 'sonata.classification.manager.context':
                        return $contextManger;
                    case 'sonata.media.manager.category':
                        return $categoryManger;
                }

                return;
            }));
    }

    public function testExecuteWithDisabledClassfication()
    {
        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(false));

        $this->setExpectedException('\LogicException');

        $this->tester->execute(array('command' => $this->command->getName()));
    }

    public function testExecuteWithExisting()
    {
        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMockBuilder('Sonata\ClassificationBundle\Model\ContextInterface')->getMock();

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue($contextModel));

        $category = $this->getMockBuilder('Sonata\ClassificationBundle\Model\CategoryInterface')->getMock();

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

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMockBuilder('Sonata\ClassificationBundle\Model\ContextInterface')->getMock();

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue($contextModel));

        $category = $this->getMockBuilder('Sonata\ClassificationBundle\Model\CategoryInterface')->getMock();

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

        $this->container->method('has')->with($this->equalTo('sonata.media.manager.category'))
            ->will($this->returnValue(true));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $contextModel = $this->getMockBuilder('Sonata\ClassificationBundle\Model\ContextInterface')->getMock();

        $this->contextManger->expects($this->once())->method('findOneBy')->with($this->equalTo(array('id' => 'foo')))->will($this->returnValue(null));
        $this->contextManger->expects($this->once())->method('create')->will($this->returnValue($contextModel));
        $this->contextManger->expects($this->once())->method('save')->with($this->equalTo($contextModel));

        $category = $this->getMockBuilder('Sonata\ClassificationBundle\Model\CategoryInterface')->getMock();

        $this->categoryManger->expects($this->once())->method('getRootCategory')->with($this->equalTo($contextModel))->will($this->returnValue(null));
        $this->categoryManger->expects($this->once())->method('create')->will($this->returnValue($category));
        $this->categoryManger->expects($this->once())->method('save')->with($this->equalTo($category));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@ > default category for \'foo\' is missing, creating one\s+Done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }
}
