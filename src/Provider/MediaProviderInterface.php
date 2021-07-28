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

namespace Sonata\MediaBundle\Provider;

use Gaufrette\File;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-type FormatOptions = array{
 *  width: int|null,
 *  height: int|null,
 *  quality: int|null,
 *  format: string|null,
 *  constraint: bool|null,
 *  resizer: string|null,
 *  resizer_options: array<string, string|bool|int|null>,
 * }
 */
interface MediaProviderInterface
{
    // This format is used to display thumbnails in Sonata Admin
    public const FORMAT_ADMIN = 'admin';

    // This format holds the original media
    public const FORMAT_REFERENCE = 'reference';

    /**
     * @phpstan-param FormatOptions $settings
     */
    public function addFormat(string $name, array $settings): void;

    /**
     * return the format settings.
     *
     * @return array|false the format settings
     *
     * @phpstan-return FormatOptions|false
     */
    public function getFormat(string $name);

    /**
     * return true if the media related to the provider required thumbnails (generation).
     */
    public function requireThumbnails(): bool;

    /**
     * Generated thumbnails linked to the media, a thumbnail is a format used on the website.
     */
    public function generateThumbnails(MediaInterface $media): void;

    /**
     * remove linked thumbnails.
     *
     * @param string|string[] $formats
     */
    public function removeThumbnails(MediaInterface $media, $formats = null): void;

    public function getReferenceFile(MediaInterface $media): File;

    /**
     * return the correct format name : providerName_format.
     */
    public function getFormatName(MediaInterface $media, string $format): string;

    /**
     * return the reference image of the media, can be the video thumbnail or the original uploaded picture.
     */
    public function getReferenceImage(MediaInterface $media): string;

    public function preUpdate(MediaInterface $media): void;

    public function postUpdate(MediaInterface $media): void;

    public function preRemove(MediaInterface $media): void;

    public function postRemove(MediaInterface $media): void;

    /**
     * build the related create form.
     *
     * @phpstan-param FormMapper<MediaInterface> $form
     */
    public function buildCreateForm(FormMapper $form): void;

    /**
     * build the related create form.
     *
     * @phpstan-param FormMapper<MediaInterface> $form
     */
    public function buildEditForm(FormMapper $form): void;

    public function prePersist(MediaInterface $media): void;

    public function postPersist(MediaInterface $media): void;

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function getHelperProperties(MediaInterface $media, string $format, array $options = []): array;

    /**
     * Generate the media path.
     */
    public function generatePath(MediaInterface $media): string;

    /**
     * Generate the public path.
     */
    public function generatePublicUrl(MediaInterface $media, string $format): string;

    /**
     * Generate the private path.
     */
    public function generatePrivateUrl(MediaInterface $media, string $format): string;

    /**
     * @phpstan-return array<string, FormatOptions>
     */
    public function getFormats(): array;

    public function setName(string $name): void;

    public function getName(): string;

    public function getProviderMetadata(): MetadataInterface;

    /**
     * @param string[] $templates
     */
    public function setTemplates(array $templates): void;

    /**
     * @return string[]
     */
    public function getTemplates(): array;

    public function getTemplate(string $name): ?string;

    /**
     * Mode can be x-file.
     *
     * @param array<string, mixed> $headers
     */
    public function getDownloadResponse(MediaInterface $media, string $format, string $mode, array $headers = []): Response;

    public function getResizer(): ?ResizerInterface;

    public function getFilesystem(): Filesystem;

    public function getCdn(): CDNInterface;

    public function getCdnPath(string $relativePath, bool $isFlushable = false): string;

    public function transform(MediaInterface $media): void;

    public function validate(ErrorElement $errorElement, MediaInterface $media): void;

    public function buildMediaType(FormBuilderInterface $formBuilder): void;

    public function updateMetadata(MediaInterface $media, bool $force = false): void;
}
