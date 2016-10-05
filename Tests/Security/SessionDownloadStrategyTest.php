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
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testIsGrantedFalse()
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
}
