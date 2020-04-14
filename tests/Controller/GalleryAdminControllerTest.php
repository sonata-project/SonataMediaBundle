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
use Prophecy\Argument;
use Prophecy\Argument\Token\TypeToken;
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
        $this->admin = $this->prophesize(BaseMediaAdmin::class);
        $this->request = $this->prophesize(Request::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->container->set('twig', $this->twig->reveal());

        $this->configureCRUDController();

        $this->controller = new GalleryAdminController();
        $this->controller->setContainer($this->container);
    }

    public function testItIsInstantiable(): void
    {
        $this->assertNotNull($this->controller);
    }

    public function testListAction(): void
    {
        $datagrid = $this->prophesize(DatagridInterface::class);
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $pool = $this->prophesize(Pool::class);

        $this->configureSetFormTheme($formView->reveal(), ['filterTheme']);
        $this->configureSetCsrfToken('sonata.batch');
        $this->configureRender('templateList', Argument::type('array'), 'renderResponse');
        $datagrid->setValue('context', null, 'context')->shouldBeCalled();
        $datagrid->getForm()->willReturn($form->reveal());
        $form->createView()->willReturn($formView->reveal());
        $this->admin->checkAccess('list')->shouldBeCalled();
        $this->admin->setListMode('list')->shouldBeCalled();
        $this->admin->getDatagrid()->willReturn($datagrid->reveal());
        $this->admin->getPersistentParameter('context')->willReturn('context');
        $this->admin->getFilterTheme()->willReturn(['filterTheme']);
        $this->admin->getTemplate('list')->willReturn('templateList');
        $this->request->get('_list_mode')->willReturn('list');
        $this->container->set('sonata.media.pool', $pool->reveal());

        $this->controller->listAction($this->request->reveal());
    }

    private function configureCRUDController(): void
    {
        $pool = $this->prophesize(AdminPool::class);
        $breadcrumbsBuilder = $this->prophesize(BreadcrumbsBuilderInterface::class);
        $templateRegistry = $this->prophesize(TemplateRegistryInterface::class);

        $this->configureGetCurrentRequest($this->request->reveal());
        $pool->getAdminByAdminCode('admin_code')->willReturn($this->admin->reveal());
        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->request->get('_xml_http_request')->willReturn(false);
        $this->request->get('_sonata_admin')->willReturn('admin_code');
        $this->request->get('uniqid')->shouldBeCalled();
        $this->container->set('sonata.admin.pool', $pool->reveal());
        $this->container->set('sonata.admin.breadcrumbs_builder', $breadcrumbsBuilder->reveal());
        $this->container->set('admin_code.template_registry', $templateRegistry->reveal());
        $this->admin->getTemplate('layout')->willReturn('layout.html.twig');
        $this->admin->isChild()->willReturn(false);
        $this->admin->setRequest($this->request->reveal())->shouldBeCalled();
        $this->admin->getCode()->willReturn('admin_code');
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->prophesize(RequestStack::class);

        $this->container->set('request_stack', $requestStack->reveal());
        $requestStack->getCurrentRequest()->willReturn($request);
    }

    private function configureSetCsrfToken(string $intention): void
    {
        $tokenManager = $this->prophesize(CsrfTokenManagerInterface::class);
        $token = $this->prophesize(CsrfToken::class);

        $tokenManager->getToken($intention)->willReturn($token->reveal());
        $token->getValue()->willReturn('token');
        $this->container->set('security.csrf.token_manager', $tokenManager->reveal());
    }

    private function configureSetFormTheme(FormView $formView, array $formTheme): void
    {
        $twigRenderer = $this->prophesize(FormRenderer::class);

        $this->twig->getRuntime(FormRenderer::class)->willReturn($twigRenderer->reveal());
        $twigRenderer->setTheme($formView, $formTheme)->shouldBeCalled();
    }

    private function configureRender(string $template, TypeToken $data, string $rendered): void
    {
        $response = $this->prophesize(Response::class);
        $pool = $this->prophesize(Pool::class);

        $this->admin->getPersistentParameters()->willReturn(['param' => 'param']);
        $this->container->set('sonata.media.pool', $pool->reveal());
        $response->getContent()->willReturn($rendered);
        $this->twig->render($template, $data)->willReturn($rendered);
    }
}
