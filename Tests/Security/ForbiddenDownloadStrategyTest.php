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

use Sonata\MediaBundle\Security\ForbiddenDownloadStrategy;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class ForbiddenDownloadStrategyTest extends PHPUnit_Framework_TestCase
{
    public function testIsGranted()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');

        $strategy = new ForbiddenDownloadStrategy($translator);
        $this->assertFalse($strategy->isGranted($media, $request));
    }
}
