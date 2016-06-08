<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Security;

use Sonata\MediaBundle\Security\SessionDownloadStrategy;

/**
 * Class SessionDownloadStrategyTest.
 *
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testIsGrantedFalseWithGreaterSessionTime()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');

        $session->expects($this->any())
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, 0);
        $this->assertFalse($strategy->isGranted($media, $request));
    }

    public function testIsGrantedTrue()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');

        $session->expects($this->any())
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $session, 1);
        $this->assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testIsGrantedTrueWithContainer()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');

        $session->expects($this->any())
            ->method('get')
            ->willReturn(0);

        $container->expects($this->once())
            ->method('get')
            ->willReturn($session);

        $strategy = new SessionDownloadStrategy($translator, $container, 1);

        $this->assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentException()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        new SessionDownloadStrategy($translator, 'foo', 1);
    }
}
