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

namespace Sonata\MediaBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TruncateControllerTest extends WebTestCase
{
    /**
     * @requires extension gd
     */
    public function testTruncate(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/u_truncate_test');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertSame('test', $client->getResponse()->getContent());
    }
}
