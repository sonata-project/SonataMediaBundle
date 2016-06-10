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

use Http\Client\HttpClient;
use Http\Message\MessageFactory;

final class HTTPlugClient implements ClientInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(HttpClient $client, MessageFactory $messageFactory)
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    public function sendRequest(string $method, string $url): string
    {
        return $this->client->sendRequest(
            $this->messageFactory->createRequest($method, $url)
        )->getBody();
    }
}
