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

namespace Sonata\MediaBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Security\SessionDownloadStrategy;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends TestCase
{
    public function testIsGrantedFalse(): void
    {
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');

        $session->expects($this->any())
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, 0);
        $this->assertFalse($strategy->isGranted($media, $request));
    }

    public function testIsGrantedTrue(): void
    {
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');

        $session->expects($this->any())
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $session, 1);
        $this->assertTrue($strategy->isGranted($media, $request));
    }
}
