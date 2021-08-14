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
 * @uses CloudFrontClient for establishing a connection with CloudFront service
 *
 * @see https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.htmlInvalidating Objects (Web Distributions Only)
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class CloudFrontVersion3 implements CDNInterface
{
    private const AVAILABLE_STATUSES = [
        self::STATUS_OK => 'Completed',
        self::STATUS_WAITING => 'InProgress',
    ];

    /**
     * @var CloudFrontClient
     */
    private $client;

    /**
     * @var string
     */
    private $distributionId;

    /**
     * @var string
     */
    private $path;

    public function __construct(CloudFrontClient $client, string $distributionId, string $path)
    {
        $this->client = $client;
        $this->distributionId = $distributionId;
        $this->path = rtrim($path, '/');
    }

    public function getPath($relativePath, $isFlushable = false): string
    {
        return sprintf('%s/%s', $this->path, ltrim($relativePath, '/'));
    }

    public function flushByString($string): string
    {
        return $this->flushPaths([$string]);
    }

    public function flush($string): string
    {
        return $this->flushPaths([$string]);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.CloudFront.CloudFrontClient.html#_createInvalidation
     */
    public function flushPaths(array $paths): string
    {
        if ([] === $paths) {
            throw new \RuntimeException('Unable to flush : expected at least one path');
        }
        // Normalizes paths due possible typos since all the CloudFront's
        // objects starts with a leading slash
        $normalizedPaths = array_map(static function (string $path): string {
            return '/'.ltrim($path, '/');
        }, $paths);

        try {
            $result = $this->client->createInvalidation([
                'DistributionId' => $this->distributionId,
                'InvalidationBatch' => [
                    'Paths' => [
                        'Quantity' => \count($normalizedPaths),
                        'Items' => $normalizedPaths,
                    ],
                    'CallerReference' => $this->getCallerReference(),
                ],
            ]);
            $status = $result->get('Invalidation')['Status'];

            if (false === array_search($status, self::AVAILABLE_STATUSES, true)) {
                throw new \RuntimeException(sprintf('Unable to determine the flush status from the given response: "%s".', $status));
            }

            return $result->get('Invalidation')['Id'];
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException(sprintf('Unable to flush paths "%s".', implode('", "', $paths)), 0, $ex);
        }
    }

    public function getFlushStatus($identifier): int
    {
        try {
            $result = $this->client->getInvalidation([
                'DistributionId' => $this->distributionId,
                'Id' => $identifier,
            ]);

            $status = array_search($result->get('Invalidation')['Status'], self::AVAILABLE_STATUSES, true);

            if (false !== $status) {
                return $status;
            }

            throw new \RuntimeException(sprintf('Unable to determine the flush status from the given response: "%s".', $status));
        } catch (CloudFrontException $ex) {
            throw new \RuntimeException(sprintf('Unable to retrieve flush status for identifier %s.', $identifier), 0, $ex);
        }
    }

    /**
     * Generates a caller reference.
     *
     * @see https://docs.aws.amazon.com/cloudfront/latest/APIReference/API_InvalidationBatch.html.
     *
     * NEXT_MAJOR: Generate the reference based on the `MediaInterface::getUpdatedAt()` property for the given media.
     */
    private function getCallerReference(): string
    {
        return (string) time();
    }
}
