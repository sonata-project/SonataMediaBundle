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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool as AdminPool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\MediaBundle\Controller\GalleryAdminController;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
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
    private Container $container;

    /**
     * @var MockObject&AdminInterface<GalleryInterface<GalleryItemInterface>>
     */
    private MockObject $admin;

    private Request $request;

    private GalleryAdminController $controller;

    /**
     * @var MockObject&Environment
     */
    private MockObject $twig;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->admin = $this->createMock(AdminInterface::class);
        $this->request = new Request();
        $this->twig = $this->createMock(Environment::class);
        $this->container->set('twig', $this->twig);
        $this->container->set('admin_code', $this->admin);

        $this->configureCRUDController();

        $this->controller = new GalleryAdminController();
        $this->controller->setContainer($this->container);
        $this->controller->configureAdmin($this->request);
    }

    public function testListAction(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $form = $this->createStub(Form::class);
        $formView = $this->createStub(FormView::class);
        $pool = new Pool('default');

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
        $pool = new AdminPool($this->container, [
            'admin_code' => 'admin_code',
        ]);
        $adminFetcher = new AdminFetcher($pool);
        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);
        $mutableTemplateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);

        $mutableTemplateRegistry->method('getTemplate')->willReturnMap([
            ['layout', 'layout.html.twig'],
            ['list', 'templateList'],
        ]);

        $this->configureGetCurrentRequest($this->request);

        $this->request->query->set('_xml_http_request', false);
        $this->request->query->set('_sonata_admin', 'admin_code');
        $this->request->query->set('_list_mode', 'list');

        $this->container->set('admin_code', $this->admin);
        $this->container->set('sonata.admin.pool', $pool);
        $this->container->set('admin_code.template_registry', $templateRegistry);
        $this->container->set('sonata.admin.request.fetcher', $adminFetcher);
        $this->admin->method('hasTemplateRegistry')->willReturn(true);
        $this->admin->method('getTemplateRegistry')->willReturn($mutableTemplateRegistry);

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
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $token = $this->createStub(CsrfToken::class);

        $tokenManager->method('getToken')->with($intention)->willReturn($token);
        $token->method('getValue')->willReturn('token');
        $this->container->set('security.csrf.token_manager', $tokenManager);
    }

    /**
     * @param string[] $formTheme
     */
    private function configureSetFormTheme(FormView $formView, array $formTheme): void
    {
        $twigRenderer = $this->createMock(FormRenderer::class);

        $this->twig->method('getRuntime')->with(FormRenderer::class)->willReturn($twigRenderer);
        $twigRenderer->expects(static::once())->method('setTheme')->with($formView, $formTheme);
    }

    private function configureRender(string $template, string $rendered): void
    {
        $response = $this->createStub(Response::class);
        $pool = new Pool('default');

        $this->admin->method('getPersistentParameters')->willReturn(['param' => 'param']);
        $this->container->set('sonata.media.pool', $pool);
        $response->method('getContent')->willReturn($rendered);
        $this->twig->method('render')->with($template, static::isType('array'))->willReturn($rendered);
    }
}
