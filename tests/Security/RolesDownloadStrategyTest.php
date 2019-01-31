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
use Symfony\Component\Translation\TranslatorInterface;

class RolesDownloadStrategyTest extends TestCase
{
    public function testIsGrantedTrue(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $request = $this->createMock(Request::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $security = $this->createMock(AuthorizationCheckerInterface::class);

        $security->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $roles) {
                return \in_array('ROLE_ADMIN', $roles, true);
            }));

        $strategy = new RolesDownloadStrategy($translator, $security, ['ROLE_ADMIN']);
        $this->assertTrue($strategy->isGranted($media, $request));
    }

    public function testIsGrantedFalse(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $request = $this->createMock(Request::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $security = $this->createMock(AuthorizationCheckerInterface::class);

        $security->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $roles) {
                return \in_array('FOO', $roles, true);
            }));

        $strategy = new RolesDownloadStrategy($translator, $security, ['ROLE_ADMIN']);
        $this->assertFalse($strategy->isGranted($media, $request));
    }
}
