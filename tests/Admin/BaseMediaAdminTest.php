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

namespace Sonata\MediaBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\ContextInterface;
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\Fixtures\EntityWithGetId;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class BaseMediaAdminTest extends TestCase
{
    private $pool;
    private $categoryManager;
    private $request;
    private $modelManager;
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = $this->prophesize(Pool::class);
        $this->categoryManager = $this->prophesize(CategoryManagerInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->modelManager = $this->prophesize(ModelManagerInterface::class);

        $this->mediaAdmin = new TestMediaAdmin(
            null,
            BaseMedia::class,
            'SonataMediaBundle:MediaAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
        $this->mediaAdmin->setRequest($this->request->reveal());
        $this->mediaAdmin->setModelManager($this->modelManager->reveal());
        $this->mediaAdmin->setUniqid('uniqid');
    }

    public function testGetNewInstance(): void
    {
        $media = $this->prophesize(Media::class);
        $category = $this->prophesize();
        $category->willExtend(EntityWithGetId::class);
        $category->willImplement(CategoryInterface::class);
        $context = $this->prophesize();
        $context->willExtend(EntityWithGetId::class);
        $context->willImplement(ContextInterface::class);

        $this->configureGetPersistentParameters();
        $this->configureGetProviderName($media);
        $this->modelManager->getModelInstance(BaseMedia::class)->willReturn($media->reveal());
        $this->categoryManager->find(1)->willReturn($category->reveal());
        $this->request->isMethod('POST')->willReturn(true);
        $this->request->get('context')->willReturn('context');
        $this->request->get('id')->willReturn(null);
        $category->getContext()->willReturn($context->reveal());
        $context->getId()->willReturn('context');
        $media->setContext('context')->shouldBeCalled();
        $media->setCategory($category->reveal())->shouldBeCalled();

        $this->assertSame($media->reveal(), $this->mediaAdmin->getNewInstance());
    }

    public function testGetPersistentParametersWithMultipleProvidersInContext()
    {
        $category = $this->prophesize();
        $category->willExtend(EntityWithGetId::class);
        $category->willImplement(CategoryInterface::class);
        $category->getId()->willReturn(1);
        $this->categoryManager->getRootCategory('context')->willReturn($category->reveal());
        $this->request->isMethod('POST')->willReturn(true);
        $this->request->get('filter')->willReturn([]);
        $this->request->get('context', 'default_context')->willReturn('context');
        $this->request->get('provider')->willReturn('providerName');
        $this->request->get('category')->willReturn(null);
        $this->request->get('hide_context')->willReturn(true);
        $provider = $this->prophesize(MediaProviderInterface::class);
        $this->pool->getDefaultContext()->willReturn('default_context');
        $this->pool->getProvidersByContext('context')->willReturn([$provider->reveal(), $provider->reveal()]);
        $this->assertSame(
            [
                'provider' => 'providerName',
                'context' => 'context',
                'category' => 1,
                'hide_context' => true,
            ], $this->mediaAdmin->getPersistentParameters());
    }

    private function configureGetPersistentParameters()
    {
        $provider = $this->prophesize(MediaProviderInterface::class);
        $category = $this->prophesize();
        $category->willExtend(EntityWithGetId::class);
        $category->willImplement(CategoryInterface::class);
        $query = $this->prophesize(ParameterBag::class);
        $this->request->query = $query->reveal();
        $query->set('provider', null)->shouldBeCalled();

        $this->pool->getDefaultContext()->willReturn('default_context');
        $this->pool->getProvidersByContext('context')->willReturn([$provider->reveal()]);
        $this->categoryManager->getRootCategory('context')->willReturn($category->reveal());
        $this->request->get('filter')->willReturn([]);
        $this->request->get('provider')->willReturn(null);
        $this->request->get('category')->willReturn(null);
        $this->request->get('hide_context')->willReturn(true);
        $this->request->get('context', 'default_context')->willReturn('context');
        $category->getId()->willReturn(1);
    }

    private function configureGetProviderName(ObjectProphecy $media): void
    {
        $this->request->get('uniqid')->willReturn(['providerName' => 'providerName']);
        $media->setProviderName('providerName')->shouldBeCalled();
    }
}
