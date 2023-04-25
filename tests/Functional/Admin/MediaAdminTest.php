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
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MediaAdminTest extends WebTestCase
{
    /**
     * @dataProvider provideCrudUrlsCases
     *
     * @param array<string, mixed> $parameters
     */
    public function testCrudUrls(string $url, array $parameters = []): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', $url, $parameters);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: string, 1?: array<string, mixed>}>
     */
    public static function provideCrudUrlsCases(): iterable
    {
        yield 'List Media' => ['/admin/tests/app/media/list'];
        yield 'Create Media' => ['/admin/tests/app/media/create'];

        yield 'Create Media Image' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.image',
        ]];

        yield 'Create Media File' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.file',
        ]];

        yield 'Create Media Vimeo' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.vimeo',
        ]];

        yield 'Create Media Youtube' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.youtube',
        ]];

        yield 'Create Media Dailymotion' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.dailymotion',
        ]];

        yield 'Edit Media' => ['/admin/tests/app/media/1/edit'];
        yield 'Remove Media' => ['/admin/tests/app/media/1/delete'];
        yield 'Download Media' => ['/media/download/1'];
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
        yield 'Create Media Image' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.image',
            'uniqid' => 'media',
        ], 'btn_create_and_list', [
            'media[binaryContent]' => __DIR__.'/../../Fixtures/logo.png',
        ]];

        yield 'Create Media File' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.file',
            'uniqid' => 'media',
        ], 'btn_create_and_list', [
            'media[binaryContent]' => __DIR__.'/../../Fixtures/file.txt',
        ]];

        yield 'Create Media Vimeo' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.vimeo',
            'uniqid' => 'media',
        ], 'btn_create_and_list', [
            'media[binaryContent]' => 'https://vimeo.com/236357509',
        ]];

        yield 'Create Media Youtube' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.youtube',
            'uniqid' => 'media',
        ], 'btn_create_and_list', [
            'media[binaryContent]' => 'https://www.youtube.com/watch?v=WsZWdVj5uTI',
        ]];

        yield 'Create Media Dailymotion' => ['/admin/tests/app/media/create', [
            'provider' => 'sonata.media.provider.dailymotion',
            'uniqid' => 'media',
        ], 'btn_create_and_list', [
            'media[binaryContent]' => 'https://www.dailymotion.com/video/x5slhr8',
        ]];

        yield 'Edit Media' => ['/admin/tests/app/media/1/edit', [], 'btn_update_and_list'];
        yield 'Remove Media' => ['/admin/tests/app/media/1/delete', [], 'btn_delete'];
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

        $manager->persist($media);

        $manager->flush();
    }
}
