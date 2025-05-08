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
use Sonata\MediaBundle\Security\ForbiddenDownloadStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ForbiddenDownloadStrategyTest extends TestCase
{
    public function testIsGranted(): void
    {
        $media = static::createStub(MediaInterface::class);
        $request = static::createStub(Request::class);
        $translator = static::createStub(TranslatorInterface::class);

        $strategy = new ForbiddenDownloadStrategy($translator);
        static::assertFalse($strategy->isGranted($media, $request));
    }
}
