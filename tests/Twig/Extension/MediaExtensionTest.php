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

namespace Sonata\MediaBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * @author Geza Buza <bghome@gmail.com>
 */
class MediaExtensionTest extends TestCase
{
    private MediaExtension $mediaExtension;

    protected function setUp(): void
    {
        $this->mediaExtension = new MediaExtension();
    }

    public function testDefinesFunctions(): void
    {
        $functions = $this->mediaExtension->getFunctions();

        static::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        static::assertCount(3, $functions);

        static::assertSame('sonata_media', $functions[0]->getName());
        static::assertSame('sonata_thumbnail', $functions[1]->getName());
        static::assertSame('sonata_path', $functions[2]->getName());

        static::assertSame([MediaRuntime::class, 'media'], $functions[0]->getCallable());
        static::assertSame([MediaRuntime::class, 'thumbnail'], $functions[1]->getCallable());
        static::assertSame([MediaRuntime::class, 'path'], $functions[2]->getCallable());

        static::assertSame(['html'], $functions[0]->getSafe(new Node()));
        static::assertSame(['html'], $functions[1]->getSafe(new Node()));
    }
}
