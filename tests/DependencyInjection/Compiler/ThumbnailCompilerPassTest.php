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

namespace Sonata\MediaBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\DependencyInjection\Compiler\ThumbnailCompilerPass;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ThumbnailCompilerPassTest extends TestCase
{
    /**
     * @dataProvider provideProcessCases
     *
     * @phpstan-param class-string $class
     */
    public function testProcess(bool $expected, string $class, ?ParameterBagInterface $parameterBag = null): void
    {
        $container = new ContainerBuilder($parameterBag);
        $container
            ->register('foobar')
            ->addTag('sonata.media.resizer');
        $thumbnailDefinition = $container->register('sonata.media.thumbnail.format', $class);

        (new ThumbnailCompilerPass())->process($container);

        static::assertSame($expected, $thumbnailDefinition->hasMethodCall('addResizer'));
    }

    /**
     * @phpstan-return iterable<array{0: bool, 1: class-string|string, 2?: ParameterBagInterface}>
     */
    public function provideProcessCases(): iterable
    {
        yield [true, FormatThumbnail::class];
        yield [false, MessengerThumbnail::class];
        yield [true, '%foo%', new ParameterBag(['foo' => FormatThumbnail::class])];
        yield [false, '%bar%', new ParameterBag(['bar' => TestUncallableAddResizerMethod::class])];
    }
}

/**
 * Here we create a class with an addResizer method that is not callable on purpose,
 * we want to test that we will not call it if it can't be called. Please do not remove
 * this method even if some tools report it as unused, it is intended.
 */
final class TestUncallableAddResizerMethod
{
    // @phpstan-ignore-next-line
    private function addResizer(): void
    {
    }
}
