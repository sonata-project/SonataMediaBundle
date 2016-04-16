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

use Sonata\MediaBundle\Security\RolesDownloadStrategy;

class RolesDownloadStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testIsGrantedTrue()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        // Prefer the Symfony 2.6+ API if available
        if (interface_exists('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')) {
            $security = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        } else {
            $security = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        }

        $security->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $roles) {
                return in_array('ROLE_ADMIN', $roles);
            }));

        $strategy = new RolesDownloadStrategy($translator, $security, array('ROLE_ADMIN'));
        $this->assertTrue($strategy->isGranted($media, $request));
    }

    public function testIsGrantedFalse()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        // Prefer the Symfony 2.6+ API if available
        if (interface_exists('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')) {
            $security = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        } else {
            $security = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        }

        $security->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (array $roles) {
                return in_array('FOO', $roles);
            }));

        $strategy = new RolesDownloadStrategy($translator, $security, array('ROLE_ADMIN'));
        $this->assertFalse($strategy->isGranted($media, $request));
    }
}
