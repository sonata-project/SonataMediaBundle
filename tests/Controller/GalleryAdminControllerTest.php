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

namespace Sonata\MediaBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool as AdminPool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Controller\GalleryAdminController;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class GalleryAdminControllerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    private $admin;

    private $request;

    private $controller;

    private $twig;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->admin = $this->createStub(BaseMediaAdmin::class);
        $this->request = $this->createStub(Request::class);
        $this->twig = $this->createStub(Environment::class);
        $this->container->set('twig', $this->twig);

        $this->configureCRUDController();

        $this->controller = new GalleryAdminController();
        $this->controller->setContainer($this->container);
    }

    public function testItIsInstantiable(): void
    {
        static::assertNotNull($this->controller);
    }

    public function testListAction(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $form = $this->createStub(Form::class);
        $formView = $this->createStub(FormView::class);
        $pool = $this->createStub(Pool::class);

        $this->configureSetFormTheme($formView, ['filterTheme']);
        $this->configureSetCsrfToken('sonata.batch');
        $this->configureRender('templateList', 'renderResponse');
        $datagrid->expects(static::once())->method('setValue')->with('context', null, 'context');
        $datagrid->method('getForm')->willReturn($form);
        $form->method('createView')->willReturn($formView);
        $this->admin->expects(static::once())->method('checkAccess')->with('list');
        $this->admin->expects(static::once())->method('setListMode')->with('list');
        $this->admin->method('getDatagrid')->willReturn($datagrid);
        $this->admin->method('getPersistentParameter')->with('context')->willReturn('context');
        $this->admin->method('getFilterTheme')->willReturn(['filterTheme']);
        $this->container->set('sonata.media.pool', $pool);

        $this->controller->listAction($this->request);
    }

    private function configureCRUDController(): void
    {
        $pool = $this->createStub(AdminPool::class);
        $breadcrumbsBuilder = $this->createStub(BreadcrumbsBuilderInterface::class);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);

        $this->configureGetCurrentRequest($this->request);
        $pool->method('getAdminByAdminCode')->with('admin_code')->willReturn($this->admin);
        $this->request->method('isXmlHttpRequest')->willReturn(false);
        $this->request->method('get')->willReturnMap([
            ['_xml_http_request', null, false],
            ['_list_mode', null, 'list'],
            ['_sonata_admin', null, 'admin_code'],
        ]);
        $this->container->set('sonata.admin.pool.do-not-use', $pool);
        $this->container->set('sonata.admin.breadcrumbs_builder.do-not-use', $breadcrumbsBuilder);
        $this->container->set('admin_code.template_registry', $templateRegistry);
        $this->admin->method('getTemplate')->willReturnMap([
            ['layout', 'layout.html.twig'],
            ['list', 'templateList'],
        ]);
        $this->admin->method('isChild')->willReturn(false);
        $this->admin->expects(static::once())->method('setRequest')->with($this->request);
        $this->admin->method('getCode')->willReturn('admin_code');
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $this->container->set('request_stack', $requestStack);
        $requestStack->method('getCurrentRequest')->willReturn($request);
    }

    private function configureSetCsrfToken(string $intention): void
    {
        $tokenManager = $this->createStub(CsrfTokenManagerInterface::class);
        $token = $this->createStub(CsrfToken::class);

        $tokenManager->method('getToken')->with($intention)->willReturn($token);
        $token->method('getValue')->willReturn('token');
        $this->container->set('security.csrf.token_manager', $tokenManager);
    }

    private function configureSetFormTheme(FormView $formView, array $formTheme): void
    {
        $twigRenderer = $this->createMock(FormRenderer::class);

        $this->twig->method('getRuntime')->with(FormRenderer::class)->willReturn($twigRenderer);
        $twigRenderer->expects(static::once())->method('setTheme')->with($formView, $formTheme);
    }

    private function configureRender(string $template, string $rendered): void
    {
        $response = $this->createStub(Response::class);
        $pool = $this->createStub(Pool::class);

        $this->admin->method('getPersistentParameters')->willReturn(['param' => 'param']);
        $this->container->set('sonata.media.pool', $pool);
        $response->method('getContent')->willReturn($rendered);
        $this->twig->method('render')->with($template, static::isType('array'))->willReturn($rendered);
    }
}
