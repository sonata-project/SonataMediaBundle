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

final class GuzzleClient implements ClientInterface
{
    /**
     * @var Browser
     */
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    public function sendRequest(string $method, string $url): string
    {
        return $this->browser->call($url, $method)->getContent();
    }
}
