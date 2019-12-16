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

namespace Sonata\MediaBundle\Tests\Resizer;

use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Resizer\ImagineCompatibleResizerTrait;

class ImagineCompatibleResizerTraitTest extends TestCase
{
    /**
     * @dataProvider getModes
     */
    public function testModeConversion(string $mode, $result): void
    {
        $objectWithTrait = $this->getObjectForTrait(ImagineCompatibleResizerTrait::class);
        $reflection = new \ReflectionObject($objectWithTrait);
        $method = $reflection->getMethod('convertMode');
        $method->setAccessible(true);

        $convertedMode = $method->invokeArgs($objectWithTrait, [$mode]);

        $this->assertSame($result, $convertedMode);
    }

    public static function getModes(): array
    {
        return [
            ['inset', ImageInterface::THUMBNAIL_INSET],
            ['outbound', ImageInterface::THUMBNAIL_OUTBOUND],
        ];
    }
}
