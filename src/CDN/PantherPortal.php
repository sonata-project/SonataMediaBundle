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
/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
class PantherPortal implements CDNInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $siteId;

    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $wsdl;

    /**
     * @param string $path
     * @param string $username
     * @param string $password
     * @param string $siteId
     * @param string $wsdl
     */
    public function __construct($path, $username, $password, $siteId, $wsdl = 'https://pantherportal.cdnetworks.com/wsdl/flush.wsdl')
    {
        $this->path = $path;
        $this->username = $username;
        $this->password = $password;
        $this->siteId = $siteId;
        $this->wsdl = $wsdl;
    }

    public function getPath($relativePath, $isFlushable)
    {
        return sprintf('%s/%s', $this->path, $relativePath);
    }

    public function flushByString($string)
    {
        return $this->flushPaths([$string]);
    }

    public function flush($string)
    {
        return $this->flushPaths([$string]);
    }

    public function flushPaths(array $paths)
    {
        $result = $this->getClient()->flush($this->username, $this->password, 'paths', $this->siteId, implode("\n", $paths), true, false);

        if ('Flush successfully submitted.' !== $result) {
            throw new \RuntimeException('Unable to flush : '.$result);
        }

        return $result;
    }

    /**
     * For testing only.
     *
     * @param $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getFlushStatus($identifier)
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
