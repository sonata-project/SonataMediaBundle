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
use Sonata\MediaBundle\Security\PublicDownloadStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PublicDownloadStrategyTest extends TestCase
{
    public function testIsGranted(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $translator = $this->createStub(TranslatorInterface::class);

        $strategy = new PublicDownloadStrategy($translator);
        $this->assertTrue($strategy->isGranted($media, $request));
    }

    /**
     * @group legacy
     * NEXT_MAJOR: remove this method
     */
    public function testLegacyIsGranted(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $request = $this->createStub(Request::class);
        $translator = $this->createStub(LegacyTranslatorInterface::class);

        $strategy = new PublicDownloadStrategy($translator);
        $this->assertTrue($strategy->isGranted($media, $request));
    }
}
