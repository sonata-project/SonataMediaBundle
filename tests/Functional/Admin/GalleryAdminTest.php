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

    public function testDelete(): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/gallery/1/delete');
        $client->submitForm('btn_delete');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }

    /**
     * @return class-string<\Symfony\Component\HttpKernel\KernelInterface>
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
