<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MediaAdminController extends Controller
{

    public function getParameters()
    {
        $provider_name = $this->get('request')->get('provider');
        $context       = $this->get('request')->get('context');

        return array(
            'provider' => $provider_name,
            'context'  => $context
        );
    }
    
    public function createAction($form = null)
    {

        $this->get('session')->start();

        $params = $this->getParameters();
        
        if (!$params['provider']) {
            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers'         => $this->get('sonata.media.pool')->getProviders(),
                'configuration'     => $this->admin,
                'params'            => $params,
                'base_template' => $this->getBaseTemplate()
            ));
        }

        $provider = $this->get('sonata.media.pool')->getProvider($params['provider']);

        $media = new \Application\Sonata\MediaBundle\Entity\Media;
        $media->setProviderName($params['provider']);

        if ($form instanceof Form) {
            $media = $form->getData();
        } else {
            $form = new Form('data', array(
                'data'      => $media,
                'validator' => $this->get('validator'),
                'context'   => $this->get('form.context'),
            ));
            $provider->buildCreateForm($form);
        }

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
            
            if ($form->isValid()) {

                if ($params['context']) {
                    $media->setContext($params['context']);
                }
                $this->get('sonata.media.pool')->prePersist($media);
                $this->admin->getModelManager()->persist($media);
                $this->admin->getModelManager()->flush();
                $this->get('sonata.media.pool')->postPersist($media);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok', 'objectId' => $media->getId()));
                }
                
                return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $media->getId())));
            }
        }

        return $this->render($provider->getTemplate('admin_create'), array(
            'form'   => $form,
            'media'  => $media,
            'params' => $params,
            'admin'  => $this->admin,
            'base_template' => $this->getBaseTemplate()
        ));
    }

    public function editAction($id)
    {

        $this->get('session')->start();

        if ($id instanceof Form) {
            $media = $id->getData();
            $form   = $id;

            $this->get('sonata.media.pool')->getProvider($media->getProviderName());
            
        } else {
            $media = $this->admin->getObject($this->get('request')->get('id'));

            if (!$media) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $provider = $this->get('sonata.media.pool')->getProvider($media->getProviderName());

            $form = new Form('data', array(
                'data'      => $media,
                'validator' => $this->get('validator'),
                'context'   => $this->get('form.context'),
            ));
            $provider->buildEditForm($form);
        }

        return $this->render($provider->getTemplate('admin_edit'), array(
            'form'   => $form,
            'media'  => $media,
            'admin'  => $this->admin,
            'params' => $this->getParameters(),
            'base_template' => $this->getBaseTemplate()
        ));
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $media = $this->admin->getObject($this->get('request')->get('id'));

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $this->get('request')->get('id')));
        }

        $provider = $this->get('sonata.media.pool')->getProvider($media->getProviderName());

        $form = new Form('data', $media, $this->get('validator'));
        $provider->buildEditForm($form);

        $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));

        if ($form->isValid()) {

            $this->get('sonata.media.pool')->preUpdate($media);
            $this->admin->getModelManager()->persist($form->getData());
            $this->admin->getModelManager()->flush();
            $this->get('sonata.media.pool')->postUpdate($media);

            return new RedirectResponse($this->admin->generateUrl('edit', array(
                'id' => $media->getId(),
                'params' => $this->getParameters()
            )));
        }

        return $this->forward(sprintf('%s:edit', $this->getBaseControllerName()), array(
            'id' => $form
        ));
    }
}