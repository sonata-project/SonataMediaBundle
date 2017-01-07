<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Metadata;

use Guzzle\Http\Mimetypes;
use Sonata\MediaBundle\Model\MediaInterface;

class AmazonMetadataBuilder implements MetadataBuilderInterface
{
    const PRIVATE_ACCESS = 'private';
    const PUBLIC_READ = 'public-read';
    const PUBLIC_READ_WRITE = 'public-read-write';
    const AUTHENTICATED_READ = 'authenticated-read';
    const BUCKET_OWNER_READ = 'bucket-owner-read';
    const BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

    const STORAGE_STANDARD = 'STANDARD';
    const STORAGE_REDUCED = 'REDUCED_REDUNDANCY';
    const STORAGE_GLACIER = 'GLACIER';

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string[]
     */
    protected $acl = array(
        'private' => self::PRIVATE_ACCESS,
        'public' => self::PUBLIC_READ,
        'open' => self::PUBLIC_READ_WRITE,
        'auth_read' => self::AUTHENTICATED_READ,
        'owner_read' => self::BUCKET_OWNER_READ,
        'owner_full_control' => self::BUCKET_OWNER_FULL_CONTROL,
    );

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
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
        $output = array();
        if (isset($this->settings['acl']) && !empty($this->settings['acl'])) {
            $output['ACL'] = $this->acl[$this->settings['acl']];
        }

        //merge storage
        if (isset($this->settings['storage'])) {
            if ($this->settings['storage'] == 'standard') {
                $output['storage'] = static::STORAGE_STANDARD;
            } elseif ($this->settings['storage'] == 'reduced') {
                $output['storage'] = static::STORAGE_REDUCED;
            }
        }

        //merge meta
        if (isset($this->settings['meta']) && !empty($this->settings['meta'])) {
            $output['meta'] = $this->settings['meta'];
        }

        //merge cache control header
        if (isset($this->settings['cache_control']) && !empty($this->settings['cache_control'])) {
            $output['CacheControl'] = $this->settings['cache_control'];
        }

        //merge encryption
        if (isset($this->settings['encryption']) && !empty($this->settings['encryption'])) {
            if ($this->settings['encryption'] == 'aes256') {
                $output['encryption'] = 'AES256';
            }
        }

        return $output;
    }

    /**
     * Gets the correct content-type.
     *
     * @param string $filename
     *
     * @return array
     */
    protected function getContentType($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = Mimetypes::getInstance()->fromExtension($extension);

        return array('contentType' => $contentType);
    }
}
