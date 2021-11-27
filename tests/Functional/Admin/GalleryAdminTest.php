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

namespace Sonata\ClassificationBundle\Tests\App\Action;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Tests\App\AppKernel;
use Sonata\MediaBundle\Tests\App\Entity\Gallery;
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
    }

    /**
     * @return class-string<\Symfony\Component\HttpKernel\KernelInterface>
     */
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    private function prepareData(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $gallery = new Gallery();
        $gallery->setName('name');
        $gallery->setContext('default');

        $manager->persist($gallery);
        $manager->flush();
    }
}
