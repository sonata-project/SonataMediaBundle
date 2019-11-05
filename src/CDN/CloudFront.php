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
 * @uses \CloudFrontClient for stablish connection with CloudFront service
 *
 * @see http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.htmlInvalidating Objects (Web Distributions Only)
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CloudFront implements CDNInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $distributionId;

    /**
     * @var CloudFrontClient
     */
    protected $client;

    /**
     * @param string $path
     * @param string $key
     * @param string $secret
     * @param string $distributionId
     */
    public function __construct($path, $key, $secret, $distributionId)
    {
        $this->path = $path;
        $this->key = $key;
        $this->secret = $secret;
        $this->distributionId = $distributionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($relativePath, $isFlushable = false)
    {
        return sprintf('%s/%s', rtrim($this->path, '/'), ltrim($relativePath, '/'));
    }

    /**
     * {@inheritdoc}
     */
    public function flushByString($string)
    {
        return $this->flushPaths([$string]);
    }

    /**
     * {@inheritdoc}
     */
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
            throw new \RuntimeException('Unable to flush : expected at least one path');
        }
        // Normalizes paths due possible typos since all the CloudFront's
        // objects starts with a leading slash
        $normalizedPaths = array_map(static function ($path) {
            return '/'.ltrim($path, '/');
        }, $paths);

        try {
            $result = $this->getClient()->createInvalidation([
                'DistributionId' => $this->distributionId,
                'Paths' => [
                    'Quantity' => \count($normalizedPaths),
                    'Items' => $normalizedPaths,
                ],
                'CallerReference' => $this->getCallerReference($normalizedPaths),
            ]);

            if (!\in_array($status = $result->get('Status'), ['Completed', 'InProgress'], true)) {
                throw new \RuntimeException('Unable to flush : '.$status);
            }

            return $result->get('Id');
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException('Unable to flush : '.$ex->getMessage());
        }
    }

    /**
     * For testing only.
     *
     * @param CloudFrontClient $client
     */
    public function setClient($client)
    {
        if (!$client instanceof CloudFrontClient) {
            @trigger_error('The '.__METHOD__.' expects a CloudFrontClient as parameter.', E_USER_DEPRECATED);
        }

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlushStatus($identifier)
    {
        try {
            $result = $this->getClient()->getInvalidation([
                'DistributionId' => $this->distributionId,
                'Id' => $identifier,
            ]);

            return array_search($result->get('Status'), self::getStatusList(), true);
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException('Unable to retrieve flush status : '.$ex->getMessage());
        }
    }

    /**
     * @static
     *
     * @return string[]
     */
    public static function getStatusList()
    {
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
     * Generates a valid caller reference from given paths regardless its order.
     *
     * @return string a md5 representation
     */
    protected function getCallerReference(array $paths)
    {
        sort($paths);

        return md5(implode(',', $paths));
    }

    private function getClient(): CloudFrontClient
    {
        if (!$this->client) {
            $this->client = CloudFrontClient::factory([
                'key' => $this->key,
                'secret' => $this->secret,
            ]);
        }

        return $this->client;
    }
}
