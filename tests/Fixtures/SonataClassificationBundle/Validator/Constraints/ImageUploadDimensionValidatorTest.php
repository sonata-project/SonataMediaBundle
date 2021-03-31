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

namespace Sonata\ClassificationBundle\Validator\Constraints;

use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimension;
use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimensionValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class ImageUploadDimensionValidatorTest extends ConstraintValidatorTestCase
{
    public const TEST_CONTEXT = 'test';

    /**
     * @var ImagineInterface
     */
    private $imagineAdapter;

    /**
     * @var ImageProvider
     */
    private $imageProvider;

    protected function setUp(): void
    {
        $this->imagineAdapter = $this->createStub(ImagineInterface::class);
        $this->imageProvider = $this->createStub(ImageProvider::class);

        parent::setUp();
    }

    public function testValidateNoMedia(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $object = new \stdClass();

        $constraint = new ImageUploadDimension();

        $this->validator->validate($object, $constraint);
    }

    public function testValidateNoGallery(): void
    {
        $media = $this->mockMedia();

        $constraint = new ImageUploadDimension();

        $this->validator->validate($media, $constraint);

        $this->assertNoViolation();
    }

    public function testWithNoConstraints(): void
    {
        $media = $this->mockMedia();

        $this->imageProvider->method('getFormatsForContext')
            ->with(self::TEST_CONTEXT)
            ->willReturn([]);

        $constraint = new ImageUploadDimension();

        $this->validator->validate($media, $constraint);

        $this->assertNoViolation();
    }

    public function testWithTooSmallImage(): void
    {
        $media = $this->mockMedia();

        $this->imageProvider->method('getFormatsForContext')
            ->with(self::TEST_CONTEXT)
            ->willReturn([
                ['constraint' => false, 'width' => 1000, 'height' => 1000],
                ['constraint' => true, 'width' => 100, 'height' => 100],
                ['constraint' => true, 'width' => 50, 'height' => 50],
            ]);

        $image = $this->mockImage(80, 80);

        $this->imagineAdapter->method('open')
            ->willReturn($image);

        $constraint = new ImageUploadDimension();

        $this->validator->validate($media, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.binaryContent')
            ->setParameters([
                    '%min_width%' => 100,
                    '%min_height%' => 100,
                ])
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ImageUploadDimensionValidator(
            $this->imagineAdapter,
            $this->imageProvider
        );
    }

    /**
     * @return ImageInterface&MockObject
     */
    private function mockImage(int $width, int $height): ImageInterface
    {
        $box = $this->createStub(BoxInterface::class);
        $box->method('getWidth')->willReturn($width);
        $box->method('getHeight')->willReturn($height);

        $image = $this->createStub(ImageInterface::class);
        $image->method('getSize')->willReturn($box);

        return $image;
    }

    /**
     * @return MockObject&MediaInterface
     */
    private function mockMedia(): MediaInterface
    {
        $binaryContent = $this->createStub(UploadedFile::class);
        $binaryContent->method('getPathname')->willReturn(tmpfile());

        $media = $this->createStub(MediaInterface::class);
        $media->method('getContext')->willReturn(self::TEST_CONTEXT);
        $media->method('getBinaryContent')->willReturn($binaryContent);

        return $media;
    }
}
