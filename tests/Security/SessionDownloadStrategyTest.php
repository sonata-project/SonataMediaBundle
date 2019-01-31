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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Security\SessionDownloadStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends TestCase
{
    public function testIsGrantedFalseWithGreaterSessionTime(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);

        $session->expects($this->any())
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, 0);
        $this->assertFalse($strategy->isGranted($media, $request));
    }

    public function testIsGrantedTrue(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);

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
    public function testIsGrantedTrueWithContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $translator = $this->createMock(TranslatorInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $request = $this->createMock(Request::class);
        $session = $this->createMock(Session::class);

        $session->expects($this->any())
            ->method('get')
            ->willReturn(0);

        $container->expects($this->once())
            ->method('get')
            ->willReturn($session);

        $strategy = new SessionDownloadStrategy($translator, $container, 1);

        $this->assertTrue($strategy->isGranted($media, $request));
    }

    public function testInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $translator = $this->createMock(TranslatorInterface::class);

        new SessionDownloadStrategy($translator, 'foo', 1);
    }
}
