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

namespace Sonata\MediaBundle\Tests\Functional\Block;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\MediaBundle\Tests\App\Entity\Gallery;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class GalleryListBlockTest extends KernelTestCase
{
    public function testRenderPublicMode(): void
    {
        $this->prepareData();

        $crawler = $this->render(['mode' => 'public']);

        static::assertSame(['Disabled', 'Enabled', 'Other context'], $crawler->filterXPath('//*[contains(@class, "list-group-item")]')->each(
            static fn (Crawler $item): string => trim($item->text())
        ));
        static::assertCount(0, $crawler->filterXPath('//a'));
    }

    public function testRenderAdminMode(): void
    {
        $this->prepareData();

        $crawler = $this->render(['mode' => 'admin', 'context' => 'default']);

        $items = $crawler->filterXPath('//a[contains(@class, "list-group-item")]');
        static::assertCount(2, $items);
        static::assertStringContainsString('Disabled', $items->eq(0)->text());
        static::assertCount(1, $items->eq(0)->filterXPath('//span[contains(@class, "label-danger")]'));
        static::assertMatchesRegularExpression('#^/admin/tests/app/gallery/\d+/edit\?context=default$#', (string) $items->eq(0)->attr('href'));
        static::assertStringContainsString('Enabled', $items->eq(1)->text());
        static::assertCount(1, $items->eq(1)->filterXPath('//span[contains(@class, "label-success")]'));
        static::assertSame('/admin/tests/app/gallery/list?context=default', $crawler->filterXPath('//a[contains(@class, "btn-primary")]')->attr('href'));
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function render(array $settings): Crawler
    {
        $blockHelper = static::getContainer()->get('sonata.block.templating.helper');
        static::assertInstanceOf(BlockHelper::class, $blockHelper);

        return new Crawler($blockHelper->render(['type' => 'sonata.media.block.gallery_list'], $settings + [
            'order' => 'name',
            'sort' => 'asc',
        ]));
    }

    private function prepareData(): void
    {
        $manager = static::getContainer()->get('doctrine.orm.entity_manager');
        static::assertInstanceOf(EntityManagerInterface::class, $manager);

        foreach ([['Enabled', 'default', true], ['Disabled', 'default', false], ['Other context', 'other', true]] as [$name, $context, $enabled]) {
            $gallery = new Gallery();
            $gallery->setName($name);
            $gallery->setContext($context);
            $gallery->setEnabled($enabled);
            $manager->persist($gallery);
        }

        $manager->flush();
    }
}
