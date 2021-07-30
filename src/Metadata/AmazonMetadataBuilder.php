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

    /**
     * @var array<string, mixed>
     */
    private $settings;

    /**
     * @var string[]
     */
    private $acl = [
        'private' => self::PRIVATE_ACCESS,
        'public' => self::PUBLIC_READ,
        'open' => self::PUBLIC_READ_WRITE,
        'auth_read' => self::AUTHENTICATED_READ,
        'owner_read' => self::BUCKET_OWNER_READ,
        'owner_full_control' => self::BUCKET_OWNER_FULL_CONTROL,
    ];

    /**
     * @var MimeTypesInterface
     */
    private $mimeTypes;

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings, ?MimeTypesInterface $mimeTypes = null)
    {
        $this->settings = $settings;
        $this->mimeTypes = $mimeTypes ?? new MimeTypes();
    }

    public function get(MediaInterface $media, $filename)
    {
        return array_replace_recursive(
            $this->getDefaultMetadata(),
            $this->getContentType($filename)
        );
    }

    /**
     * Get data passed from the config.
     *
     * @phpstan-return array{
     *     ACL?: string,
     *     storage?: self::STORAGE_STANDARD|self::STORAGE_REDUCED,
     *     meta?: array<string, mixed>,
     *     CacheControl?: string,
     *     encryption?: 'AES256'
     * }
     */
    private function getDefaultMetadata()
    {
        //merge acl
        $output = [];
        $acl = $this->settings['acl'] ?? null;
        if (null !== $acl) {
            $output['ACL'] = $this->acl[$acl];
        }

        //merge storage
        if (isset($this->settings['storage'])) {
            if ('standard' === $this->settings['storage']) {
                $output['storage'] = self::STORAGE_STANDARD;
            } elseif ('reduced' === $this->settings['storage']) {
                $output['storage'] = self::STORAGE_REDUCED;
            }
        }

        //merge meta
        $meta = $this->settings['meta'] ?? null;
        if (null !== $meta) {
            $output['meta'] = $meta;
        }

        //merge cache control header
        $cacheControl = $this->settings['cache_control'] ?? null;
        if (null !== $cacheControl) {
            $output['CacheControl'] = $cacheControl;
        }

        //merge encryption
        $encryption = $this->settings['encryption'] ?? null;
        if ('aes256' === $encryption) {
            $output['encryption'] = 'AES256';
        }

        return $output;
    }

    /**
     * Gets the correct content-type.
     *
     * @param string $filename path to the file inside the S3 bucket
     *
     * @return array
     *
     * @phpstan-return array{contentType: string}
     */
    private function getContentType($filename)
    {
        $ext = pathinfo($filename, \PATHINFO_EXTENSION);
        $mimeTypes = $this->mimeTypes->getMimeTypes($ext);
        $mimeType = current($mimeTypes);

        if (false === $mimeType) {
            throw new \RuntimeException(sprintf('Unable to determine the mime type for file %s', $filename));
        }

        return ['contentType' => $mimeType];
    }
}
