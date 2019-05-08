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

namespace Sonata\MediaBundle\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;
use Sonata\MediaBundle\Validator\FormatValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

class FormatValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test', [], ['format1' => []]);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getDefaultFormat')->willReturn('format1');
        $gallery->expects($this->once())->method('getContext')->willReturn('test');

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }

    public function testValidateNotValidDefaultFormat(): void
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test', [], ['format1' => []]);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getDefaultFormat')->willReturn('non_existing_format');
        $gallery->expects($this->once())->method('getContext')->willReturn('test');

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->once())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }

    public function testValidateOnlyReferenceIsAllowedIfNotFormats(): void
    {
        $pool = new Pool('defaultContext');
        $pool->addContext('test');

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getDefaultFormat')->willReturn('format_that_is_not_reference');
        $gallery->expects($this->once())->method('getContext')->willReturn('test');

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->once())->method('addViolation');

        $validator = new FormatValidator($pool);
        $validator->initialize($context);

        $validator->validate($gallery, new ValidMediaFormat());
    }
}
