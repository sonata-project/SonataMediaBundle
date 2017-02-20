<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Admin;

use Sonata\MediaBundle\Admin\BaseMediaAdmin;

class TestMediaAdmin extends BaseMediaAdmin
{
}

class EntityWithGetId
{
    public function getId()
    {
    }
}

class BaseMediaAdminTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $categoryManager;
    private $request;
    private $modelManager;
    private $mediaAdmin;

    protected function setUp()
    {
        $this->pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $this->categoryManager = $this->prophesize('Sonata\MediaBundle\Model\CategoryManagerInterface');
        $this->request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $this->modelManager = $this->prophesize('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->mediaAdmin = new TestMediaAdmin(
            null,
            'Sonata\MediaBundle\Entity\BaseMedia',
            'SonataMediaBundle:MediaAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
        $this->mediaAdmin->setRequest($this->request->reveal());
        $this->mediaAdmin->setModelManager($this->modelManager->reveal());
        $this->mediaAdmin->setUniqid('uniqid');
    }

    public function testGetNewInstance()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\Media');
        $category = $this->prophesize();
        $category->willExtend('Sonata\MediaBundle\Tests\Admin\EntityWithGetId');
        $category->willImplement('Sonata\ClassificationBundle\Model\CategoryInterface');
        $context = $this->prophesize();
        $context->willExtend('Sonata\MediaBundle\Tests\Admin\EntityWithGetId');
        $context->willImplement('Sonata\ClassificationBundle\Model\ContextInterface');

        $this->configureGetPersistentParameters();
        $this->configureGetProviderName($media);
        $this->modelManager->getModelInstance('Sonata\MediaBundle\Entity\BaseMedia')->willReturn($media->reveal());
        $this->categoryManager->find(1)->willReturn($category->reveal());
        $this->request->isMethod('POST')->willReturn(true);
        $category->getContext()->willReturn($context->reveal());
        $media->setContext('context')->shouldBeCalled();

        $this->assertSame($media->reveal(), $this->mediaAdmin->getNewInstance());
    }

    private function configureGetPersistentParameters()
    {
        $category = $this->prophesize();
        $category->willExtend('Sonata\MediaBundle\Tests\Admin\EntityWithGetId');
        $category->willImplement('Sonata\ClassificationBundle\Model\CategoryInterface');

        $this->categoryManager->getRootCategory('context')->willReturn($category->reveal());
        $this->request->get('filter')->willReturn(array());
        $this->request->get('provider')->willReturn('provider');
        $this->request->get('category')->willReturn(1);
        $this->request->get('hide_context')->willReturn(true);
        $this->request->get('context', null)->willReturn('context');
        $category->getId()->willReturn(1);
    }

    private function configureGetProviderName($media)
    {
        // NEXT_MAJOR remove this block when dropping sf < 2.8 compatibility
        if (method_exists('Symfony\Component\HttpFoundation\JsonResponse', 'transformJsonError')) {
            $this->request->get('uniqid[providerName]')->willReturn('providerName');
        } else {
            $this->request->get('uniqid')->willReturn(array('providerName' => 'providerName'));
        }
        $media->setProviderName('providerName')->shouldBeCalled();
    }
}
