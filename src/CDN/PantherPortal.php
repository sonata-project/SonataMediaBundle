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

/**
 *  From https://pantherportal.cdnetworks.com/wsdl/flush.wsdl.
 *
 *  flushRequest:
 *     username, password: credentials of a user that has web service access.
 *     siteId: the numeric id of the site to flush from.
 *     flushtype: the type of flush: "all" or "paths".
 *     paths: a newline-separated list of paths to flush. empty if flushtype is "all".
 *     wildcard: a boolean to activate wildcard mode. may be true only when flushtype is "paths".
 *     use_ims: a boolean to activate fetching with If-Modified-Since. may be true only when flushtype is "all" or wildcard is true.
 *
 *     Please see the documentation available on the Panther Customer Portal
 *     for more detail about the effects of these parameters.
 *
 *    Please note that an error with response "Over the limit of flush requests per hour" means that the flush is rate limited.
 *    Any requests until the rate limit is no longer exceeded will receive this response.
 */
final class PantherPortal implements CDNInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $siteId;

    /**
     * @var string
     */
    private $wsdl;

    /**
     * @var \SoapClient|null
     */
    private $client;

    public function __construct(string $path, string $username, string $password, string $siteId, string $wsdl = 'https://pantherportal.cdnetworks.com/wsdl/flush.wsdl')
    {
        $this->path = $path;
        $this->username = $username;
        $this->password = $password;
        $this->siteId = $siteId;
        $this->wsdl = $wsdl;
    }

    public function getPath(string $relativePath, bool $isFlushable = false): string
    {
        return sprintf('%s/%s', $this->path, $relativePath);
    }

    public function flushByString(string $string): string
    {
        return $this->flushPaths([$string]);
    }

    public function flush(string $string): string
    {
        return $this->flushPaths([$string]);
    }

    public function flushPaths(array $paths): string
    {
        $result = $this->getClient()->flush($this->username, $this->password, 'paths', $this->siteId, implode("\n", $paths), true, false);

        if ('Flush successfully submitted.' !== $result) {
            throw new \RuntimeException('Unable to flush : '.$result);
        }

        return $result;
    }

    public function setClient(\SoapClient $client): void
    {
        $this->client = $client;
    }

    public function getFlushStatus(string $identifier): int
    {
        return CDNInterface::STATUS_OK;
    }

    private function getClient(): \SoapClient
    {
        if (!$this->client) {
            $this->client = new \SoapClient($this->wsdl);
        }

        return $this->client;
    }
}
