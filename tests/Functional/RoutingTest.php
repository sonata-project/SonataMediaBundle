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

namespace Sonata\MediaBundle\Tests\Functional\Routing;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\MediaBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class RoutingTest extends WebTestCase
{
    /**
     * @group legacy
     *
     * @dataProvider getRoutes
     */
    public function testRoutes(string $name, string $path, array $methods): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $route = $router->getRouteCollection()->get($name);

        $this->assertNotNull($route);
        $this->assertSame($path, $route->getPath());
        $this->assertEmpty(array_diff($methods, $route->getMethods()));

        // define {provider} for data set #17
        $path = str_replace('{provider}', 'test', $path);

        $matchingPath = $path;
        $matchingFormat = '';
        if (\strlen($matchingPath) >= 10 && false !== strpos($matchingPath, '.{_format}', -10)) {
            $matchingFormat = '.json';
            $matchingPath = str_replace('.{_format}', $matchingFormat, $path);
        }

        $matcher = $router->getMatcher();
        $requestContext = $router->getContext();

        foreach ($methods as $method) {
            $requestContext->setMethod($method);

            // Check paths like "/api/user/users.json".
            $match = $matcher->match($matchingPath);

            $this->assertSame($name, $match['_route']);

            if ($matchingFormat) {
                $this->assertSame(ltrim($matchingFormat, '.'), $match['_format']);
            }

            $matchingPathWithStrippedFormat = str_replace('.{_format}', '', $path);

            // Check paths like "/api/user/users".
            $match = $matcher->match($matchingPathWithStrippedFormat);

            $this->assertSame($name, $match['_route']);

            if ($matchingFormat) {
                $this->assertSame(ltrim($matchingFormat, '.'), $match['_format']);
            }
        }
    }

    public function getRoutes(): iterable
    {
        // API
        if (class_exists(Operation::class)) {
            yield ['app.swagger_ui', '/api/doc', ['GET']];
            yield ['app.swagger', '/api/doc.json', ['GET']];
        } else {
            yield ['nelmio_api_doc_index', '/api/doc/{view}', ['GET']];
        }

        // API - Gallery
        yield ['sonata_api_media_gallery_get_galleries', '/api/media/galleries.{_format}', ['GET']];
        yield ['sonata_api_media_gallery_get_gallery', '/api/media/galleries/{id}.{_format}', ['GET']];
        yield ['sonata_api_media_gallery_get_gallery_medias', '/api/media/galleries/{id}/medias.{_format}', ['GET']];
        yield ['sonata_api_media_gallery_get_gallery_galleryhasmedias', '/api/media/galleries/{id}/galleryhasmedias.{_format}', ['GET']];
        yield ['sonata_api_media_gallery_post_gallery', '/api/media/galleries.{_format}', ['POST']];
        yield ['sonata_api_media_gallery_put_gallery', '/api/media/galleries/{id}.{_format}', ['PUT']];
        yield ['sonata_api_media_gallery_post_gallery_media_galleryhasmedia', '/api/media/galleries/{galleryId}/media/{mediaId}/galleryhasmedia.{_format}', ['POST']];
        yield ['sonata_api_media_gallery_put_gallery_media_galleryhasmedia', '/api/media/galleries/{galleryId}/media/{mediaId}/galleryhasmedia.{_format}', ['PUT']];
        yield ['sonata_api_media_gallery_delete_gallery_media_galleryhasmedia', '/api/media/galleries/{galleryId}/media/{mediaId}/galleryhasmedia.{_format}', ['DELETE']];
        yield ['sonata_api_media_gallery_delete_gallery', '/api/media/galleries/{id}.{_format}', ['DELETE']];

        // API - Media
        yield ['sonata_api_media_media_get_media', '/api/media/media.{_format}', ['GET']];
        yield ['sonata_api_media_media_get_medium', '/api/media/media/{id}.{_format}', ['GET']];
        yield ['sonata_api_media_media_get_medium_formats', '/api/media/media/{id}/formats.{_format}', ['GET']];
        yield ['sonata_api_media_media_get_medium_binary', '/api/media/media/{id}/binaries/{format}.{_format}', ['GET']];
        yield ['sonata_api_media_media_delete_medium', '/api/media/media/{id}.{_format}', ['DELETE']];
        yield ['sonata_api_media_media_put_medium', '/api/media/media/{id}.{_format}', ['PUT']];
        yield ['sonata_api_media_media_post_provider_medium', '/api/media/media/providers/{provider}/media.{_format}', ['POST']];
        yield ['sonata_api_media_media_put_medium_binary_content', '/api/media/media/{id}/binary/content.{_format}', ['PUT']];
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
