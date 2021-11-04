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

namespace Sonata\MediaBundle\Tests\Listener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;

/**
 * @author Mathieu Lemoine <mlemoine@mlemoine.name>
 */
class MediaEventSubscriberTest extends TestCase
{
    /**
     * @see https://github.com/sonata-project/SonataClassificationBundle/issues/60
     * @see https://github.com/sonata-project/SonataMediaBundle/pull/780
     */
    public function testRefetchCategoriesAfterClear(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $pool = new Pool('default');
        $pool->addProvider('provider', $provider);

        $category = $this->createMock(CategoryInterface::class);
        $catManager = $this->createMock(CategoryManagerInterface::class);

        $catManager->expects(static::exactly(2))
            ->method('getAllRootCategories')
            ->willReturn(['context' => $category]);

        $subscriber = new MediaEventSubscriber($pool, $catManager);

        static::assertContains(Events::onClear, $subscriber->getSubscribedEvents());

        $media1 = new Media();
        $media1->setProviderName('provider');
        $media1->setContext('context');

        $args1 = $this->createMock(LifecycleEventArgs::class);
        $args1->method('getObject')->willReturn($media1);

        $subscriber->prePersist($args1);

        $subscriber->onClear();

        $media2 = new Media();
        $media2->setProviderName('provider');
        $media2->setContext('context');

        $args2 = $this->createMock(LifecycleEventArgs::class);
        $args2->method('getObject')->willReturn($media2);

        $subscriber->prePersist($args2);
    }
}
