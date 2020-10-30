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

namespace Sonata\MediaBundle\CDN;

use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\Exception\CloudFrontException;

/**
 * From http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html.
 *
 * Invalidating Objects (Web Distributions Only)
 * If you need to remove an object from CloudFront edge-server caches before it
 * expires, you can do one of the following:
 * Invalidate the object. The next time a viewer requests the object, CloudFront
 * returns to the origin to fetch the latest version of the object.
 * Use object versioning to serve a different version of the object that has a
 * different name. For more information, see Updating Existing Objects Using
 * Versioned Object Names.
 * Important:
 * You can invalidate most types of objects that are served by a web
 * distribution, but you cannot invalidate media files in the Microsoft Smooth
 * Streaming format when you have enabled Smooth Streaming for the corresponding
 * cache behavior. In addition, you cannot invalidate objects that are served by
 * an RTMP distribution. You can invalidate a specified number of objects each
 * month for free. Above that limit, you pay a fee for each object that you
 * invalidate. For example, to invalidate a directory and all of the files in
 * the directory, you must invalidate the directory and each file individually.
 * If you need to invalidate a lot of files, it might be easier and less
 * expensive to create a new distribution and change your object paths to refer
 * to the new distribution. For more information about the charges for
 * invalidation, see Paying for Object Invalidation.
 *
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @todo Remove this class when support for aws/aws-sdk-php < 3.0 is dropped.
 *
 * @uses CloudFrontClient for establishing a connection with CloudFront service
 *
 * @see http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.htmlInvalidating Objects (Web Distributions Only)
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CloudFront implements CDNInterface
{
    private const AVAILABLE_STATUSES = [
        self::STATUS_OK => 'Completed',
        self::STATUS_WAITING => 'InProgress',
    ];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var string
     */
    protected $path;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var string
     */
    protected $key;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var string
     */
    protected $secret;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var string
     */
    protected $distributionId;

    /**
     * @var CloudFrontClient
     */
    protected $client;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $version;

    /**
     * NEXT_MAJOR: Use `CloudFrontClient $client, string $distributionId, string $path` as signature for this method.
     *
     * @param CloudFrontClient|string $clientOrPath
     * @param string                  $distributionIdOrKey
     * @param string                  $pathOrSecret
     * @param string|null             $distributionId
     */
    public function __construct(
        $clientOrPath,
        $distributionIdOrKey,
        $pathOrSecret,
        $distributionId = null
        /* , ?string $region = null, ?string $version = null */
    ) {
        // NEXT_MAJOR: Remove the following conditional block.
        if (!$clientOrPath instanceof CloudFrontClient) {
            @trigger_error(sprintf(
                'Passing another type than %s as argument 1 for "%s()" is deprecated since sonata-project/media-bundle 3.28'
                .' and will throw a %s error in version 4.0. You must pass these arguments: CDN client, CDN distribution id, CDN path.',
                CloudFrontClient::class,
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);

            $this->path = rtrim($clientOrPath, '/');
            $this->key = $distributionIdOrKey;
            $this->secret = $pathOrSecret;
            $this->distributionId = $distributionId;

            $args = \func_get_args();

            $this->region = $args[4] ?? null;
            $this->version = $args[5] ?? null;

            return;
        }

        if (\func_num_args() > 3) {
            throw new \InvalidArgumentException(sprintf(
                'Number of arguments passed to "%s()" cannot be higher than 3 when using the new signature.'
                .' You must pass these arguments: CDN client, CDN distribution id, CDN path.',
                __METHOD__
            ));
        }
        $this->client = $clientOrPath;
        $this->distributionId = $distributionIdOrKey;
        $this->path = rtrim($pathOrSecret, '/');
    }

    public function getPath($relativePath, $isFlushable = false)
    {
        return sprintf('%s/%s', $this->path, ltrim($relativePath, '/'));
    }

    public function flushByString($string)
    {
        return $this->flushPaths([$string]);
    }

    public function flush($string)
    {
        return $this->flushPaths([$string]);
    }

    /**
     * {@inheritdoc}
     *
     * @see http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.CloudFront.CloudFrontClient.html#_createInvalidation
     */
    public function flushPaths(array $paths)
    {
        if (empty($paths)) {
            throw new \RuntimeException('Unable to flush: expected at least one path.');
        }
        // Normalizes paths due possible typos since all the CloudFront's
        // objects starts with a leading slash
        $normalizedPaths = array_map(static function (string $path): string {
            return '/'.ltrim($path, '/');
        }, $paths);

        try {
            $result = $this->client->createInvalidation([
                'DistributionId' => $this->distributionId,
                'Paths' => [
                    'Quantity' => \count($normalizedPaths),
                    'Items' => $normalizedPaths,
                ],
                'CallerReference' => $this->getCallerReference($normalizedPaths),
            ]);

            $status = $result->get('Status');

            if (false === array_search($status, self::AVAILABLE_STATUSES, true)) {
                throw new \RuntimeException(sprintf('Unable to determine the flush status from the given response: "%s".', $status));
            }

            return $result->get('Id');
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException(sprintf('Unable to flush paths "%s".', implode('", "', $paths), 0, $ex));
        }
    }

    public function getFlushStatus($identifier)
    {
        try {
            $result = $this->client->getInvalidation([
                'DistributionId' => $this->distributionId,
                'Id' => $identifier,
            ]);

            $status = array_search($result->get('Status'), self::AVAILABLE_STATUSES, true);

            if (false === $status) {
                @trigger_error(sprintf(
                    'Returning a value not present in the `%s::STATUS_*` constants from %s() is deprecated since sonata-project/media-bundle 3.28'
                    .' and will not be possible in version 4.0.',
                    CDNInterface::class,
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR: Remove the previous deprecation and uncomment the following exception.
                // throw new \RuntimeException(sprintf('Unable to determine the flush status from the given response: "%s".', $status));
            }

            return $status;
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException(sprintf('Unable to retrieve flush status for identifier %s.', $identifier), 0, $ex);
        }
    }

    /**
     * @deprecated since sonata-project/media-bundle 3.28, to be removed in version 4.0.
     *
     * @static
     *
     * @return string[]
     */
    public static function getStatusList()
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/media-bundle 3.28 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        // @todo: check for a complete list of available CloudFront statuses
        return [
            self::STATUS_OK => 'Completed',
            self::STATUS_TO_SEND => 'STATUS_TO_SEND',
            self::STATUS_TO_FLUSH => 'STATUS_TO_FLUSH',
            self::STATUS_ERROR => 'STATUS_ERROR',
            self::STATUS_WAITING => 'InProgress',
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.28, to be removed in version 4.0.
     *
     * @param CloudFrontClient $client
     */
    public function setClient($client)
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/media-bundle 3.28 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!$this->client) {
            $config = [
                'key' => $this->region,
                'secret' => $this->version,
            ];

            if (null !== $this->region) {
                $config['region'] = $this->region;
            }

            if (null !== $this->version) {
                $config['version'] = $this->version;
            }

            $this->client = CloudFrontClient::factory($config);
        }
    }

    /**
     * Generates a valid caller reference from given paths regardless its order.
     *
     * @return string a md5 representation
     */
    protected function getCallerReference(array $paths)
    {
        sort($paths);

        return md5(implode(',', $paths));
    }
}
