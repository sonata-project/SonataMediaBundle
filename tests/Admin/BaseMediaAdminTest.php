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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Category;
use Sonata\MediaBundle\Tests\App\Entity\Context;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\HttpFoundation\Request;

class BaseMediaAdminTest extends TestCase
{
    private Pool $pool;

    /**
     * @var MockObject&CategoryManagerInterface
     */
    private MockObject $categoryManager;

    /**
     * @var MockObject&ContextManagerInterface
     */
    private MockObject $contextManager;

    private Request $request;

    /**
     * @var Stub&ModelManagerInterface<MediaInterface>
     */
    private Stub $modelManager;

    private TestMediaAdmin $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = new Pool('default_context');
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->contextManager = $this->createMock(ContextManagerInterface::class);
        $this->request = new Request();
        $this->modelManager = $this->createStub(ModelManagerInterface::class);

        $this->mediaAdmin = new TestMediaAdmin(
            $this->pool,
            $this->categoryManager,
            $this->contextManager
        );
        $this->mediaAdmin->setModelClass(Media::class);
        $this->mediaAdmin->setRequest($this->request);
        $this->mediaAdmin->setModelManager($this->modelManager);
        $this->mediaAdmin->setUniqId('uniqId');
    }

    public function testAlterNewInstance(): void
    {
        $category = new Category();
        $context = new Context();

        $context->setId('context');
        $category->setContext($context);

        $this->configureGetPersistentParameters();

        $this->categoryManager->method('find')->with(1)->willReturn($category);
        $this->request->setMethod('POST');

        $media = $this->mediaAdmin->getNewInstance();

        static::assertSame('context', $media->getContext());
        static::assertSame($category, $media->getCategory());
        static::assertSame('providerName', $media->getProviderName());
    }

    public function testGetPersistentParametersWithMultipleProvidersInContext(): void
    {
        $category = new Category();
        $category->setId(1);

        $context = new Context();

        $provider = $this->createStub(MediaProviderInterface::class);

        $this->request->setMethod('POST');
        $this->request->query->set('filter', []);
        $this->request->query->set('provider', 'providerName');
        $this->request->query->set('hide_context', true);
        $this->request->query->set('context', 'context');

        $this->contextManager->method('find')->with('context')->willReturn($context);
        $this->categoryManager->method('getRootCategoriesForContext')->with($context)->willReturn([$category]);
        $this->pool->addProvider('provider1', $provider);
        $this->pool->addProvider('provider2', $provider);
        $this->pool->addContext('context', ['provider1', 'provider2']);

        $persistentParameters = $this->mediaAdmin->getPersistentParameters();

        static::assertSame([
            'provider' => 'providerName',
            'context' => 'context',
            'category' => 1,
            'hide_context' => true,
        ], $persistentParameters);
    }

    private function configureGetPersistentParameters(): void
    {
        $provider = $this->createStub(MediaProviderInterface::class);
        $category = new Category();
        $category->setId(1);

        $context = new Context();

        $this->request->query->set('filter', []);
        $this->request->query->set('hide_context', true);
        $this->request->query->set('context', 'context');
        $this->request->request->set('uniqId', ['providerName' => 'providerName']);

        $this->pool->addProvider('provider', $provider);
        $this->pool->addContext('context', ['provider']);
        $this->contextManager->method('find')->with('context')->willReturn($context);
        $this->categoryManager->method('getRootCategoriesForContext')->with($context)->willReturn([$category]);
    }
}
