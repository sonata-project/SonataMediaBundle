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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Category;
use Sonata\MediaBundle\Tests\App\Entity\Context;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class BaseMediaAdminTest extends TestCase
{
    private $pool;

    private $categoryManager;

    private $request;

    private $modelManager;

    /**
     * @var TestMediaAdmin
     */
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = $this->createStub(Pool::class);
        $this->categoryManager = $this->createStub(CategoryManagerInterface::class);
        $this->request = $this->createStub(Request::class);
        $this->modelManager = $this->createStub(ModelManagerInterface::class);

        $this->mediaAdmin = new TestMediaAdmin(
            'media',
            BaseMedia::class,
            'SonataMediaBundle:MediaAdmin',
            $this->pool,
            $this->categoryManager
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
        $this->request->method('isMethod')->with('POST')->willReturn(true);

        $this->mediaAdmin->alterNewInstance($media);

        $this->assertSame('context', $media->getContext());
        $this->assertSame($category, $media->getCategory());
        $this->assertSame('providerName', $media->getProviderName());
    }

    public function testGetPersistentParametersWithMultipleProvidersInContext(): void
    {
        $category = new Category();
        $category->setId(1);
        $provider = $this->createStub(MediaProviderInterface::class);

        $this->categoryManager->method('getRootCategory')->with('context')->willReturn($category);
        $this->request->method('isMethod')->with('POST')->willReturn(true);
        $this->request->method('get')->willReturnMap([
            ['filter', null, []],
            ['provider', null, 'providerName'],
            ['category', null, null],
            ['hide_context', null, true],
            ['context', 'default_context', 'context'],
        ]);
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

        $this->request->query = new ParameterBag();

        $this->pool->method('getDefaultContext')->willReturn('default_context');
        $this->pool->method('getProvidersByContext')->with('context')->willReturn([$provider]);
        $this->categoryManager->method('getRootCategory')->with('context')->willReturn($category);
        $this->request->method('get')->willReturnMap([
            ['filter', null, []],
            ['provider', null, null],
            ['category', null, null],
            ['hide_context', null, true],
            ['context', 'default_context', 'context'],
            ['context', null, 'context'],
            ['uniqid', null, ['providerName' => 'providerName']],
            ['id', null, null],
        ]);
    }
}
