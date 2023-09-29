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

namespace Sonata\MediaBundle\Tests\Functional\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Tests\App\Entity\Gallery;
use Sonata\MediaBundle\Tests\App\Entity\GalleryItem;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GalleryAdminTest extends WebTestCase
{
    /**
     * @dataProvider provideCrudUrlsCases
     */
    public function testCrudUrls(string $url): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string[]>
     *
     * @phpstan-return iterable<array{string}>
     */
    public static function provideCrudUrlsCases(): iterable
    {
        yield 'List Gallery' => ['/admin/tests/app/gallery/list'];
        yield 'Create Gallery' => ['/admin/tests/app/gallery/create'];
        yield 'Edit Gallery' => ['/admin/tests/app/gallery/1/edit'];
        yield 'Remove Gallery' => ['/admin/tests/app/gallery/1/delete'];
    }

    /**
     * @dataProvider provideFormsUrlsCases
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $fieldValues
     */
    public function testFormsUrls(string $url, array $parameters, string $button, array $fieldValues = []): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', $url, $parameters);
        $client->submitForm($button, $fieldValues);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: string, 1: array<string, mixed>, 2: string, 3?: array<string, mixed>}>
     */
    public static function provideFormsUrlsCases(): iterable
    {
        yield 'Create Gallery' => ['/admin/tests/app/gallery/create', [
            'uniqid' => 'gallery',
        ], 'btn_create_and_list', [
            'gallery[name]' => 'Name',
        ]];

        yield 'Edit Gallery' => ['/admin/tests/app/gallery/1/edit', [], 'btn_update_and_list'];
        yield 'Remove Gallery' => ['/admin/tests/app/gallery/1/delete', [], 'btn_delete'];
    }

    private function prepareData(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $media = new Media();
        $media->setName('name.jpg');
        $media->setProviderStatus(MediaInterface::STATUS_OK);
        $media->setContext('default');
        $media->setProviderReference('name.jpg');
        $media->setProviderName('sonata.media.provider.image');
        $media->setBinaryContent(realpath(__DIR__.'/../../Fixtures/logo.png'));

        $gallery = new Gallery();
        $gallery->setName('name');
        $gallery->setContext('default');

        $galleryItem = new GalleryItem();
        $galleryItem->setMedia($media);
        $galleryItem->setGallery($gallery);
        $galleryItem->setPosition(1);

        $manager->persist($media);
        $manager->persist($gallery);
        $manager->persist($galleryItem);

        $manager->flush();
    }
}
