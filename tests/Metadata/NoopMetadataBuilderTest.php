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

namespace Sonata\MediaBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\NoopMetadataBuilder;
use Sonata\MediaBundle\Model\MediaInterface;

class NoopMetadataBuilderTest extends TestCase
{
    public function testNoop(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $filename = '/test/folder/testfile.png';

        $noopmetadatabuilder = new NoopMetadataBuilder();

        $this->assertSame([], $noopmetadatabuilder->get($media, $filename));
    }
}
