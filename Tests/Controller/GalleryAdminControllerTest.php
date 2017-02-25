<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Controller;

use Prophecy\Argument;
use Sonata\MediaBundle\Controller\GalleryAdminController;

class GalleryAdminControllerTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $admin;
    private $request;
    private $controller;

    protected function setUp()
    {
        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->admin = $this->prophesize('Sonata\MediaBundle\Admin\BaseMediaAdmin');
        $this->request = $this->prophesize('Symfony\Component\HttpFoundation\Request');

        $this->configureCRUDController();

        $this->controller = new GalleryAdminController();
        $this->controller->setContainer($this->container->reveal());
    }

    public function testItIsInstantiable()
    {
        $this->assertNotNull($this->controller);
    }

    public function testListAction()
    {
        $datagrid = $this->prophesize('Sonata\AdminBundle\Datagrid\DatagridInterface');
        $form = $this->prophesize('Symfony\Component\Form\Form');
        $formView = $this->prophesize('Symfony\Component\Form\FormView');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');

        $this->configureSetFormTheme($formView->reveal(), 'filterTheme');
        $this->configureSetCsrfToken('sonata.batch');
        $this->configureRender('templateList', Argument::type('array'), 'renderResponse');
        $datagrid->setValue('context', null, 'context')->shouldBeCalled();
        $datagrid->getForm()->willReturn($form->reveal());
        $form->createView()->willReturn($formView->reveal());
        $this->admin->checkAccess('list')->shouldBeCalled();
        $this->admin->setListMode('list')->shouldBeCalled();
        $this->admin->getDatagrid()->willReturn($datagrid->reveal());
        $this->admin->getPersistentParameter('context')->willReturn('context');
        $this->admin->getFilterTheme()->willReturn('filterTheme');
        $this->admin->getTemplate('list')->willReturn('templateList');
        $this->request->get('_list_mode')->willReturn('list');
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());

        $this->controller->listAction($this->request->reveal());
    }

    private function configureCRUDController()
    {
        $pool = $this->prophesize('Sonata\AdminBundle\Admin\Pool');
        $breadcrumbsBuilder = $this->prophesize('Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface');

        $this->configureGetCurrentRequest($this->request->reveal());
        $pool->getAdminByAdminCode('admin_code')->willReturn($this->admin->reveal());
        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->request->get('_xml_http_request')->willReturn(false);
        $this->request->get('_sonata_admin')->willReturn('admin_code');
        $this->request->get('uniqid')->shouldBeCalled();
        $this->container->get('sonata.admin.pool')->willReturn($pool->reveal());
        $this->container->get('sonata.admin.breadcrumbs_builder')->willReturn($breadcrumbsBuilder->reveal());
        $this->admin->getTemplate('layout')->willReturn('layout.html.twig');
        $this->admin->isChild()->willReturn(false);
        $this->admin->setRequest($this->request->reveal())->shouldBeCalled();
    }

    private function configureGetCurrentRequest($request)
    {
        $requestStack = $this->prophesize('Symfony\Component\HttpFoundation\RequestStack');

        $this->container->has('request_stack')->willReturn(true);
        $this->container->get('request_stack')->willReturn($requestStack->reveal());
        $requestStack->getCurrentRequest()->willReturn($request);
    }

    private function configureSetCsrfToken($intention)
    {
        $tokenManager = $this->prophesize('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $token = $this->prophesize('Symfony\Component\Security\Csrf\CsrfToken');

        $tokenManager->getToken($intention)->willReturn($token->reveal());
        $token->getValue()->willReturn('token');
        $this->container->has('security.csrf.token_manager')->willReturn(true);
        $this->container->get('security.csrf.token_manager')->willReturn($tokenManager->reveal());
    }

    private function configureSetFormTheme($formView, $formTheme)
    {
        $twig = $this->prophesize('\Twig_Environment');
        $twigRenderer = $this->prophesize('Symfony\Bridge\Twig\Form\TwigRenderer');

        $this->container->get('twig')->willReturn($twig->reveal());

        // Remove this trick when bumping Symfony requirement to 3.2+.
        if (method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $twig->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->willReturn($twigRenderer->reveal());
        } else {
            $formExtension = $this->prophesize('Symfony\Bridge\Twig\Extension\FormExtension');
            $formExtension->renderer = $twigRenderer->reveal();

            $twig->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')->willReturn($formExtension->reveal());
        }
        $twigRenderer->setTheme($formView, $formTheme)->shouldBeCalled();
    }

    private function configureRender($template, $data, $rendered)
    {
        $templating = $this->prophesize('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');

        $this->admin->getPersistentParameters()->willReturn(array('param' => 'param'));
        $this->container->has('templating')->willReturn(true);
        $this->container->get('templating')->willReturn($templating->reveal());
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());
        $templating->renderResponse($template, $data, null)->willReturn($rendered);
    }
}
