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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends TestCase
{
    public function testIsGrantedFalseWithGreaterSessionTime(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $session = $this->createMock(Session::class);

        $request = new Request();
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $session
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $requestStack, 0);
        static::assertFalse($strategy->isGranted($media, $request));
    }

    public function testIsGrantedTrue(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $session = $this->createMock(Session::class);

        $request = new Request();
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $session
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $requestStack, 1);
        static::assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testIsGrantedTrueWithSession(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $session, 1);
        static::assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testIsGrantedTrueWithContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(0);

        $container->expects(static::once())
            ->method('get')
            ->willReturn($session);

        $strategy = new SessionDownloadStrategy($translator, $container, 1);

        static::assertTrue($strategy->isGranted($media, $request));
    }

    public function testTypeError(): void
    {
        $this->expectException(\TypeError::class);

        $translator = $this->createStub(TranslatorInterface::class);

        new SessionDownloadStrategy($translator, 'foo', 1);
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyIsGrantedFalseWithGreaterSessionTime(): void
    {
        $translator = $this->createStub(LegacyTranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, 0);
        static::assertFalse($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyIsGrantedTrue(): void
    {
        $translator = $this->createStub(LegacyTranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $session, 1);
        static::assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyIsGrantedTrueWithContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $translator = $this->createStub(LegacyTranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(0);

        $container->expects(static::once())
            ->method('get')
            ->willReturn($session);

        $strategy = new SessionDownloadStrategy($translator, $container, 1);

        static::assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyTypeError(): void
    {
        $this->expectException(\TypeError::class);

        $translator = $this->createStub(LegacyTranslatorInterface::class);

        new SessionDownloadStrategy($translator, 'foo', 1);
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyTimeAsString(): void
    {
        $translator = $this->createStub(LegacyTranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, '0');
        static::assertFalse($strategy->isGranted($media, $request));
    }
}
