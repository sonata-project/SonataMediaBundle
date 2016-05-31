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

use Sonata\MediaBundle\Command\CleanMediaCommand;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

if (class_exists('Symfony\Component\Filesystem\Tests\FilesystemTestCase')) {
    class TestCase extends FilesystemTestCase
    {
    }
} else {
    class TestCase extends \PHPUnit_Framework_TestCase
    {
    }
}

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 *
 * @requires function Symfony\Component\Filesystem\Tests\FilesystemTestCase::setUpBeforeClass
 */
class CleanMediaCommandTest extends TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Local
     */
    private $fileSystemLocal;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->command = new CleanMediaCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:clean-uploads'));

        $this->pool = $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();

        $this->mediaManager = $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $this->fileSystemLocal = $fileSystemLocal = $this->getMockBuilder('Sonata\MediaBundle\Filesystem\Local')->disableOriginalConstructor()->getMock();
        $this->fileSystemLocal->expects($this->once())->method('getDirectory')->will($this->returnValue($this->workspace));

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($pool, $mediaManager, $fileSystemLocal) {
                switch ($id) {
                    case 'sonata.media.pool':
                        return $pool;
                    case 'sonata.media.manager.media':
                        return $mediaManager;
                    case 'sonata.media.adapter.filesystem.local':
                        return $fileSystemLocal;
                }

                return;
            }));
    }

    public function testExecuteDirectoryNotExists()
    {
        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->pool->expects($this->once())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@\'.+\' does not exist\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteEmptyDirectory()
    {
        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'foo');

        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $this->pool->expects($this->once())->method('getContexts')->will($this->returnValue(array('foo' => $context)));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@Context: foo\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteFilesExists()
    {
        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $provider = $this->getMockBuilder('Sonata\MediaBundle\Provider\FileProvider')->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue(array($provider)));

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(array('id' => 1, 'context' => 'foo')))
            ->will($this->returnValue(array($media)));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(array('providerReference' => 'qwertz.ext', 'providers' => array('fooprovider'))))
            ->will($this->returnValue(array($media)));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertRegExp('@Context: foo\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteFilesExistsVerbose()
    {
        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $provider = $this->getMockBuilder('Sonata\MediaBundle\Provider\FileProvider')->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue(array($provider)));

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(array('id' => 1, 'context' => 'foo')))
            ->will($this->returnValue(array($media)));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(array('providerReference' => 'qwertz.ext', 'providers' => array('fooprovider'))))
            ->will($this->returnValue(array($media)));

        $output = $this->tester->execute(array('command' => $this->command->getName(), '--verbose' => true));

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            array(
                '\'qwertz.ext\' found',
                '\'thumb_1_bar.ext\' found',
            ),
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    public function testExecuteDryRun()
    {
        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $provider = $this->getMockBuilder('Sonata\MediaBundle\Provider\FileProvider')->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue(array($provider)));

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(array('id' => 1, 'context' => 'foo')))
            ->will($this->returnValue(array()));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(array('providerReference' => 'qwertz.ext', 'providers' => array('fooprovider'))))
            ->will($this->returnValue(array()));

        $output = $this->tester->execute(array('command' => $this->command->getName(), '--dry-run' => true));

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            array(
                '\'qwertz.ext\' is orphanend',
                '\'thumb_1_bar.ext\' is orphanend',
            ),
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    public function testExecute()
    {
        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = array(
            'providers' => array(),
            'formats' => array(),
            'download' => array(),
        );

        $provider = $this->getMockBuilder('Sonata\MediaBundle\Provider\FileProvider')->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(array('foo' => $context)));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue(array($provider)));

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(array('id' => 1, 'context' => 'foo')))
            ->will($this->returnValue(array()));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(array('providerReference' => 'qwertz.ext', 'providers' => array('fooprovider'))))
            ->will($this->returnValue(array()));

        $output = $this->tester->execute(array('command' => $this->command->getName()));

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            array(
                '\'qwertz.ext\' was successfully removed',
                '\'thumb_1_bar.ext\' was successfully removed',
            ),
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    /**
     * Asserts whether all expected texts can be found in the output within a given context.
     *
     * @param string $extract  PCRE regex expected to have a single matching group, extracting the content of a context
     * @param array  $expected Excerpts of text expected to be found in the output
     * @param string $output   Searched output
     */
    private function assertOutputFoundInContext($extractor, $expected, $output)
    {
        preg_match_all($extractor, $output, $matches);

        $found = false;
        foreach ($matches[1] as $match) {
            if ($this->containsAll($match, $expected)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, sprintf(
            'Unable to find "%s" in "%s" with extractor "%s"',
            implode('", "', $expected),
            $output,
            $extractor
        ));
    }

    /**
     * Returns whether every needle can be found as a substring of the haystack.
     *
     * @param string $haystack
     * @param array  $needles  Array of (potential) substrings of the haystack
     */
    private function containsAll($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (false === strpos($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }
}
