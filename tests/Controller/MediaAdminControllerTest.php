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
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Controller\MediaAdminController;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Category;
use Sonata\MediaBundle\Tests\App\Entity\Context;
use Sonata\MediaBundle\Tests\Entity\Media;
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

class MediaAdminControllerTest extends TestCase
{
    private Container $container;

    /**
     * @var MockObject&AdminInterface<MediaInterface>
     */
    private MockObject $admin;

    private Request $request;

    private MediaAdminController $controller;

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

        $this->configureCRUDController();

        $this->controller = new MediaAdminController();
        $this->controller->setContainer($this->container);
        $this->controller->configureAdmin($this->request);
    }

    public function testCreateActionToSelectProvider(): void
    {
        $pool = new Pool('default_context');

        $this->configureRender(
            '@SonataMedia/MediaAdmin/select_provider.html.twig',
            'renderResponse'
        );
        $pool->addProvider('provider', $this->createStub(MediaProviderInterface::class));
        $pool->addContext('context', ['provider']);
        $this->admin->expects(static::once())->method('checkAccess')->with('create');
        $this->container->set('sonata.media.pool', $pool);
        $this->request->query->set('context', 'context');

        $response = $this->controller->createAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
    }

    public function testCreateAction(): void
    {
        $this->configureCreateAction(Media::class);
        $this->configureRender('template', 'renderResponse');
        $this->admin
            ->expects(static::atLeastOnce())
            ->method('checkAccess')
            ->with('create');

        $this->admin
            ->method('getIdParameter')
            ->willReturn('id');
        $this->request->query->set('provider', 'provider');
        $response = $this->controller->createAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
    }

    public function testListAction(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $categoryManager = $this->createMock(CategoryManagerInterface::class);
        $contextManager = $this->createMock(ContextManagerInterface::class);
        $category = new Category();
        $category->setId(1);
        $context = new Context();
        $form = $this->createStub(Form::class);
        $formView = $this->createStub(FormView::class);

        $this->configureSetFormTheme($formView, ['filterTheme']);
        $this->configureSetCsrfToken('sonata.batch');
        $this->configureRender('templateList', 'renderResponse');

        /**
         * @psalm-suppress DeprecatedClass
         */
        $datagrid->expects(static::exactly(3))->method('setValue')->withConsecutive(
            ['context', null, 'another_context'],
            ['category', null, 1]
        );
        $datagrid->method('getForm')->willReturn($form);
        $contextManager->method('find')->with('another_context')->willReturn($context);
        $categoryManager->method('getRootCategoriesForContext')->with($context)->willReturn([$category]);
        $categoryManager->method('findOneBy')->with([
            'id' => 2,
            'context' => 'another_context',
        ])->willReturn($category);
        $form->method('createView')->willReturn($formView);
        $this->container->set('sonata.media.manager.category', $categoryManager);
        $this->container->set('sonata.media.manager.context', $contextManager);
        $this->admin->expects(static::once())->method('checkAccess')->with('list');
        $this->admin->expects(static::once())->method('setListMode')->with('mosaic');
        $this->admin->method('getDatagrid')->willReturn($datagrid);
        $this->admin->method('getPersistentParameter')->with('context')->willReturn('another_context');
        $this->admin->method('getFilterTheme')->willReturn(['filterTheme']);
        $this->request->query->set('_list_mode', 'mosaic');
        $this->request->query->set('filter', []);
        $this->request->query->set('category', 2);

        $response = $this->controller->listAction($this->request);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
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
            ['edit', 'template'],
            ['list', 'templateList'],
        ]);

        $this->configureGetCurrentRequest($this->request);

        $this->request->query->set('_xml_http_request', false);
        $this->request->query->set('_sonata_admin', 'admin_code');

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

    /**
     * @phpstan-param class-string $class
     */
    private function configureCreateAction(string $class): void
    {
        $object = $this->createStub(Media::class);
        $form = $this->createMock(Form::class);
        $formView = $this->createStub(FormView::class);

        $this->configureSetFormTheme($formView, ['formTheme']);
        $this->admin->method('hasActiveSubClass')->willReturn(false);
        $this->admin->method('getClass')->willReturn($class);
        $this->admin->method('getNewInstance')->willReturn($object);
        $this->admin->expects(static::once())->method('setSubject')->with($object);
        $this->admin->method('getForm')->willReturn($form);
        $this->admin->method('getFormTheme')->willReturn(['formTheme']);
        $form->method('createView')->willReturn($formView);
        $form->expects(static::once())->method('setData')->with($object);
        $form->expects(static::once())->method('handleRequest')->with($this->request);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('all')->willReturn(['field' => null]);
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $this->container->set('request_stack', $requestStack);
        $requestStack->method('getCurrentRequest')->willReturn($request);
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

    private function configureSetCsrfToken(string $intention): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $token = $this->createStub(CsrfToken::class);

        $tokenManager->method('getToken')->with($intention)->willReturn($token);
        $token->method('getValue')->willReturn('token');
        $this->container->set('security.csrf.token_manager', $tokenManager);
    }

    private function configureRender(string $template, string $rendered): void
    {
        $response = $this->createStub(Response::class);
        $pool = new Pool('context');

        $response->method('getContent')->willReturn($rendered);

        $this->admin->method('getPersistentParameters')->willReturn(['param' => 'param']);
        $this->container->set('sonata.media.pool', $pool);
        $this->twig->method('render')->with($template, static::isType('array'))->willReturn($rendered);
    }
}
