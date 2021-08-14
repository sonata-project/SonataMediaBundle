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
 * @final since sonata-project/media-bundle 3.21.0
 */
class AmazonMetadataBuilder implements MetadataBuilderInterface
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
     * @var array
     */
    protected $settings;

    /**
     * @var string[]
     */
    protected $acl = [
        'private' => self::PRIVATE_ACCESS,
        'public' => self::PUBLIC_READ,
        'open' => self::PUBLIC_READ_WRITE,
        'auth_read' => self::AUTHENTICATED_READ,
        'owner_read' => self::BUCKET_OWNER_READ,
        'owner_full_control' => self::BUCKET_OWNER_FULL_CONTROL,
    ];

    /**
     * @var MimeTypes
     */
    private $mimeTypes;

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
     * @return array
     */
    protected function getDefaultMetadata()
    {
        //merge acl
        $output = [];
        if (isset($this->settings['acl'])) {
            $output['ACL'] = $this->acl[$this->settings['acl']];
        }

        //merge storage
        if (isset($this->settings['storage'])) {
            if ('standard' === $this->settings['storage']) {
                $output['storage'] = static::STORAGE_STANDARD;
            } elseif ('reduced' === $this->settings['storage']) {
                $output['storage'] = static::STORAGE_REDUCED;
            }
        }

        //merge meta
        if (isset($this->settings['meta'])) {
            $output['meta'] = $this->settings['meta'];
        }

        //merge cache control header
        if (isset($this->settings['cache_control'])) {
            $output['CacheControl'] = $this->settings['cache_control'];
        }

        //merge encryption
        if (isset($this->settings['encryption'])) {
            if ('aes256' === $this->settings['encryption']) {
                $output['encryption'] = 'AES256';
            }
        }

        return $output;
    }

    /**
     * Gets the correct content-type.
     *
     * @param string $filename path to the file inside the S3 bucket
     *
     * @return array
     * @phpstan-return array{contentType: string}
     */
    protected function getContentType($filename)
    {
        $ext = pathinfo($filename, \PATHINFO_EXTENSION);
        $mimeTypes = $this->mimeTypes->getMimeTypes($ext);

        return ['contentType' => current($mimeTypes)];
    }
}
