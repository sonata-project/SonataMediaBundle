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
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Category;
use Sonata\MediaBundle\Tests\App\Entity\Context;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\HttpFoundation\Request;

class BaseMediaAdminTest extends TestCase
{
    /**
     * @var MockObject&Pool
     */
    private $pool;

    /**
     * @var MockObject&CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * @var MockObject&ContextManagerInterface
     */
    private $contextManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Stub&ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var TestMediaAdmin
     */
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(Pool::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->contextManager = $this->createMock(ContextManagerInterface::class);
        $this->request = new Request();
        $this->modelManager = $this->createStub(ModelManagerInterface::class);

        $this->mediaAdmin = new TestMediaAdmin(
            'media',
            BaseMedia::class,
            'SonataMediaBundle:MediaAdmin',
            $this->pool,
            $this->categoryManager,
            $this->contextManager
        );
        $this->mediaAdmin->setRequest($this->request);
        $this->mediaAdmin->setModelManager($this->modelManager);
        $this->mediaAdmin->setUniqid('uniqid');
    }

    public function testAlterNewInstance(): void
    {
        $media = new Media();
        $category = new Category();
        $context = new Context();

        $context->setId('context');
        $category->setContext($context);

        $this->configureGetPersistentParameters();

        $this->categoryManager->method('find')->with(1)->willReturn($category);
        $this->request->setMethod('POST');

        $this->mediaAdmin->alterNewInstance($media);

        $this->assertSame('context', $media->getContext());
        $this->assertSame($category, $media->getCategory());
        $this->assertSame('providerName', $media->getProviderName());
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
        $this->pool->method('getDefaultContext')->willReturn('default_context');
        $this->pool->method('getProvidersByContext')->with('context')->willReturn([$provider, $provider]);

        $persistentParameters = $this->mediaAdmin->getPersistentParameters();

        $this->assertSame([
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
        $this->request->query->set('uniqid', ['providerName' => 'providerName']);

        $this->pool->method('getDefaultContext')->willReturn('default_context');
        $this->pool->method('getProvidersByContext')->with('context')->willReturn([$provider]);
        $this->contextManager->method('find')->with('context')->willReturn($context);
        $this->categoryManager->method('getRootCategoriesForContext')->with($context)->willReturn([$category]);
    }
}
