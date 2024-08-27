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

namespace Sonata\MediaBundle\Metadata;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @phpstan-type AmazonSettings = array{
 *     acl: 'private'|'public'|'open'|'auth_read'|'owner_read'|'owner_full_control',
 *     storage: 'standard'|'reduced',
 *     encryption: 'aes256',
 *     meta: array<string, mixed>,
 *     cache_control: string
 * }
 */
final class AmazonMetadataBuilder implements MetadataBuilderInterface
{
    public const PRIVATE_ACCESS = 'private';
    public const PUBLIC_READ = 'public-read';
    public const PUBLIC_READ_WRITE = 'public-read-write';
    public const AUTHENTICATED_READ = 'authenticated-read';
    public const BUCKET_OWNER_READ = 'bucket-owner-read';
    public const BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

    public const STORAGE_STANDARD = 'STANDARD';
    public const STORAGE_REDUCED = 'REDUCED_REDUNDANCY';
    public const STORAGE_GLACIER = 'GLACIER';

    private const ACL = [
        'private' => self::PRIVATE_ACCESS,
        'public' => self::PUBLIC_READ,
        'open' => self::PUBLIC_READ_WRITE,
        'auth_read' => self::AUTHENTICATED_READ,
        'owner_read' => self::BUCKET_OWNER_READ,
        'owner_full_control' => self::BUCKET_OWNER_FULL_CONTROL,
    ];

    private const STORAGE = [
        'standard' => self::STORAGE_STANDARD,
        'reduced' => self::STORAGE_REDUCED,
    ];

    private MimeTypesInterface $mimeTypes;

    /**
     * @param array<string, mixed> $settings
     *
     * @phpstan-param AmazonSettings $settings
     */
    public function __construct(
        private array $settings,
        ?MimeTypesInterface $mimeTypes = null
    ) {
        $this->mimeTypes = $mimeTypes ?? new MimeTypes();
    }

    public function get(MediaInterface $media, string $filename): array
    {
        return array_replace_recursive(
            $this->getDefaultMetadata(),
            $this->getContentType($filename),
            $this->getContentLength($media)
        );
    }

    /**
     * Get data passed from the config.
     *
     * @return array<string, array|string>
     *
     * @phpstan-return array{
     *     ACL: self::PRIVATE_ACCESS|self::PUBLIC_READ|self::PUBLIC_READ_WRITE|self::AUTHENTICATED_READ|self::BUCKET_OWNER_READ|self::BUCKET_OWNER_FULL_CONTROL,
     *     storage: self::STORAGE_STANDARD|self::STORAGE_REDUCED,
     *     meta: array<string, mixed>,
     *     CacheControl: string,
     *     encryption: 'AES256'
     * }
     */
    private function getDefaultMetadata(): array
    {
        return [
            'ACL' => self::ACL[$this->settings['acl']],
            'storage' => self::STORAGE[$this->settings['storage']],
            'meta' => $this->settings['meta'],
            'CacheControl' => $this->settings['cache_control'],
            'encryption' => 'AES256',
        ];
    }

    /**
     * Gets the correct content-type.
     *
     * @return array<string, string>
     *
     * @phpstan-return array{contentType: string}
     */
    private function getContentType(string $filename): array
    {
        $ext = pathinfo($filename, \PATHINFO_EXTENSION);
        $mimeTypes = $this->mimeTypes->getMimeTypes($ext);
        $mimeType = current($mimeTypes);

        if (false === $mimeType) {
            throw new \RuntimeException(\sprintf('Unable to determine the mime type for file %s', $filename));
        }

        return ['contentType' => $mimeType];
    }

    /**
     * @return array<string, int>
     *
     * @phpstan-return array{contentLength?: int}
     */
    private function getContentLength(MediaInterface $media): array
    {
        $size = $media->getSize();
        if ($size > 0) {
            return ['contentLength' => $size];
        }

        return [];
    }
}
