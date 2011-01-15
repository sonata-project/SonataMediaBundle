<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\MediaBundle\Controller;

use Bundle\Sonata\BaseApplicationBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Form;

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
        
        if(!$params['provider']) {
            return $this->render('Sonata/MediaBundle:MediaAdmin:select_provider.twig', array(
                'providers'         => $this->get('media.provider')->getProviders(),
                'configuration'     => $this->admin,
                'params'            => $params
            ));
        }

        $provider = $this->get('media.provider')->getProvider($params['provider']);

        $media = new \Application\Sonata\MediaBundle\Entity\Media;
        $media->setProviderName($params['provider']);

        if($form instanceof Form) {
            $media = $form->getData();
        } else {
            $form = new Form('data', $media, $this->get('validator'));
            $provider->buildCreateForm($form);
        }

        if($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));
            
            if($form->isValid()) {

                if($params['context']) {
                    $media->setContext($params['context']);
                }
                $this->get('media.provider')->prePersist($media);
                $this->admin->getEntityManager()->persist($media);
                $this->admin->getEntityManager()->flush();
                $this->get('media.provider')->postPersist($media);

                return $this->redirect($this->admin->generateUrl('edit', array('id' => $media->getId())));
            }
        }

        $template = sprintf('Sonata/MediaBundle:MediaAdmin:provider_create_%s.twig', $params['provider']);


        
        return $this->render($template, array(
            'form'   => $form,
            'media'  => $media,
            'params' => $params,
            'admin'  => $this->admin,
        ));
    }

    public function editAction($id)
    {

        $this->get('session')->start();

        if($id instanceof Form) {
            $media = $id->getData();
            $form   = $id;
        } else {
            $media = $this->admin->getObject($this->get('request')->get('id'));

            if(!$media) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $provider = $this->get('media.provider')->getProvider($media->getProviderName());

            $form = new Form('data', $media, $this->get('validator'));
            $provider->buildEditForm($form);
        }

        $template = sprintf('Sonata/MediaBundle:MediaAdmin:provider_edit_%s.twig', $media->getProviderName());

        return $this->render($template, array(
            'form'   => $form,
            'media'  => $media,
            'admin'  => $this->admin,
            'params' => $this->getParameters()
        ));
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $media = $this->admin->getObject($this->get('request')->get('id'));

        if(!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $this->get('request')->get('id')));
        }

        $provider = $this->get('media.provider')->getProvider($media->getProviderName());

        $form = new Form('data', $media, $this->get('validator'));
        $provider->buildEditForm($form);

        $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));

        if($form->isValid()) {

            $this->get('media.provider')->preUpdate($media);
            $this->admin->getEntityManager()->persist($form->getData());
            $this->admin->getEntityManager()->flush();
            $this->get('media.provider')->postUpdate($media);

            return $this->redirect($this->admin->generateUrl('edit', array(
                'id' => $media->getId(),
                'params' => $this->getParameters()
            )));
        }

        return $this->forward(sprintf('%s:edit', $this->getBaseControllerName()), array(
            'id' => $form
        ));
    }
}