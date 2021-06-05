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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class SessionDownloadStrategyTest extends TestCase
{
    public function testIsGrantedFalse(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(1);

        $strategy = new SessionDownloadStrategy($translator, $session, 0);
        $this->assertFalse($strategy->isGranted($media, $request));
    }

    public function testIsGrantedTrue(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $session = $this->createMock(Session::class);

        $session
            ->method('get')
            ->willReturn(0);

        $strategy = new SessionDownloadStrategy($translator, $session, 1);
        $this->assertTrue($strategy->isGranted($media, $request));
    }

    public function testTypeError(): void
    {
        $this->expectException(\TypeError::class);

        $translator = $this->createStub(TranslatorInterface::class);

        new SessionDownloadStrategy($translator, 'foo', 1);
    }
}
