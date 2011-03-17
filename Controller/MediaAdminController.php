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

        return array(
            'provider' => $this->get('request')->get('provider'),
            'context'  => $this->get('request')->get('context'),
            'uniqid'   => $this->admin->getUniqId(),
        );
    }
    
    public function createAction($form = null)
    {

        $parameters = $this->getParameters();

        if (!$parameters['provider']) {
            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers'         => $this->get('sonata.media.pool')->getProviders(),
                'configuration'     => $this->admin,
                'params'            => $parameters,
                'base_template' => $this->getBaseTemplate()
            ));
        }

        $provider = $this->get('sonata.media.pool')->getProvider($parameters['provider']);

        $media = new \Application\Sonata\MediaBundle\Entity\Media;
        $media->setProviderName($parameters['provider']);
        $media->setContext($parameters['context']);

        if ($form instanceof Form) {
            $media = $form->getData();
        } else {
            $form = $this->admin->getForm($media);
        }

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {

                $provider->prePersist($media);
                $this->admin->getModelManager()->persist($media);
                $this->admin->getModelManager()->flush();
                $provider->postPersist($media);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok', 'objectId' => $media->getId()));
                }

                return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $media->getId())));
            }
        }

        return $this->render($provider->getTemplate('admin_create'), array(
            'form'   => $form,
            'media'  => $media,
            'parameters' => $parameters,
            'admin'  => $this->admin,
            'base_template' => $this->getBaseTemplate()
        ));
    }

    public function editAction($id)
    {

        if ($id instanceof Form) {
            $media = $id->getData();
            $form   = $id;
        } else {
            $media = $this->admin->getObject($this->get('request')->get('id'));

            if (!$media) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $form = $this->admin->getForm($media);
        }

        $provider = $this->get('sonata.media.pool')->getProvider($media->getProviderName());

        $parameters = $this->getParameters();
        $parameters['id'] = $media->getId();

        return $this->render($provider->getTemplate('admin_edit'), array(
            'form'   => $form,
            'media'  => $media,
            'admin'  => $this->admin,
            'parameters' => $parameters,
            'base_template' => $this->getBaseTemplate()
        ));
    }

    public function updateAction()
    {

        if ($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $media = $this->admin->getObject($this->get('request')->get('id'));

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $this->get('request')->get('id')));
        }

        $form = $this->admin->getForm($media);
        $form->bind($this->get('request'));

        if ($form->isValid()) {

            $this->get('sonata.media.pool')->preUpdate($media);
            $this->admin->getModelManager()->persist($form->getData());
            $this->admin->getModelManager()->flush();
            $this->get('sonata.media.pool')->postUpdate($media);

            $parameters = $this->getParameters();
            $parameters['id'] = $media->getId();
            
            return new RedirectResponse($this->admin->generateUrl('edit', $parameters));
        }

        return $this->forward(sprintf('%s:edit', $this->getBaseControllerName()), array(
            'id' => $form
        ));
    }
}