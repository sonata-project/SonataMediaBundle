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

namespace Sonata\MediaBundle\Client;

use Buzz\Browser;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/media-bundle 3.28, to be removed in version 4.0.
 */
final class BuzzClient implements ClientInterface
{
    /**
     * @var Browser
     */
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->browser->sendRequest($request);
    }
}
