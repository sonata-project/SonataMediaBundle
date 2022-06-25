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
use Sonata\MediaBundle\Tests\App\AppKernel;
use Sonata\MediaBundle\Tests\App\Entity\Gallery;
use Sonata\MediaBundle\Tests\App\Entity\GalleryItem;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class GalleryItemAdminTest extends WebTestCase
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
        yield 'List Gallery Item' => ['/admin/tests/app/galleryitem/list'];
        yield 'Create Gallery Item' => ['/admin/tests/app/galleryitem/create'];
        yield 'Edit Gallery Item' => ['/admin/tests/app/galleryitem/1/edit'];
        yield 'Remove Gallery Item' => ['/admin/tests/app/galleryitem/1/delete'];
    }

    /**
     * @dataProvider provideFormUrlsCases
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
    public static function provideFormUrlsCases(): iterable
    {
        yield 'Create Gallery Item' => ['/admin/tests/app/galleryitem/create', [
            'uniqid' => 'galleryItem',
        ], 'btn_create_and_list', [
            'galleryItem[media]' => 1,
        ]];

        yield 'Edit Gallery Item' => ['/admin/tests/app/galleryitem/1/edit', [], 'btn_update_and_list'];
        yield 'Remove Gallery Item' => ['/admin/tests/app/galleryitem/1/delete', [], 'btn_delete'];
    }

    /**
     * @return class-string<KernelInterface>
     */
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareData(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
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
