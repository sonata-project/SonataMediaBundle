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
use Sonata\MediaBundle\Security\RolesDownloadStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RolesDownloadStrategyTest extends TestCase
{
    public function testIsGrantedTrue(): void
    {
        $media = static::createStub(MediaInterface::class);
        $request = static::createStub(Request::class);
        $translator = static::createStub(TranslatorInterface::class);
        $security = $this->createMock(AuthorizationCheckerInterface::class);

        $security
            ->method('isGranted')
            ->willReturnCallback(
                static fn (string $role): bool => 'ROLE_ADMIN' === $role
            );

        $strategy = new RolesDownloadStrategy($translator, $security, ['ROLE_ADMIN']);
        static::assertTrue($strategy->isGranted($media, $request));
    }

    public function testIsGrantedFalse(): void
    {
        $media = static::createStub(MediaInterface::class);
        $request = static::createStub(Request::class);
        $translator = static::createStub(TranslatorInterface::class);
        $security = $this->createMock(AuthorizationCheckerInterface::class);

        $security
            ->method('isGranted')
            ->willReturnCallback(static fn (string $role): bool => 'FOO' === $role);

        $strategy = new RolesDownloadStrategy($translator, $security, ['ROLE_ADMIN']);
        static::assertFalse($strategy->isGranted($media, $request));
    }
}
