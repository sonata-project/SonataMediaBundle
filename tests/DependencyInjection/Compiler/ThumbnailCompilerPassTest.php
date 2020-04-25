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

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\DependencyInjection\Compiler\ThumbnailCompilerPass;
use Sonata\MediaBundle\Thumbnail\ConsumerThumbnail;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ThumbnailCompilerPassTest extends TestCase
{
    /**
     * @dataProvider processProvider
     */
    public function testProcess(bool $expected, string $class, ?ParameterBagInterface $parameterBag = null): void
    {
        $container = new ContainerBuilder($parameterBag);
        $container
            ->register('foobar')
            ->addTag('sonata.media.resizer');
        $thumbnailDefinition = $container->register('sonata.media.thumbnail.format', $class);

        (new ThumbnailCompilerPass())->process($container);

        $this->assertSame($expected, $thumbnailDefinition->hasMethodCall('addResizer'));
    }

    public function processProvider(): array
    {
        return [
            [true, FormatThumbnail::class],
            [false, ConsumerThumbnail::class],
            [true, '%foo%', new ParameterBag(['foo' => FormatThumbnail::class])],
            [false, '%bar%', new ParameterBag(['bar' => TestUncallableAddResizerMethod::class])],
        ];
    }
}

final class TestUncallableAddResizerMethod
{
    private function addResizer(): void
    {
    }
}
