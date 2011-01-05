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

    public function createAction($form = null)
    {
        $this->get('session')->start();

        $provider_name = $this->get('request')->get('provider');
        $context       = $this->get('request')->get('context');

        $params = array(
            'provider' => $provider_name,
            'context'  => $context
        );
        
        if(!$provider_name) {
            return $this->render('Sonata/MediaBundle:MediaAdmin:select_provider.twig', array(
                'providers'         => $this->get('media.provider')->getProviders(),
                'configuration'     => $this->configuration,
                'params'            => $params
            ));
        }

        $provider = $this->get('media.provider')->getProvider($provider_name);

        $media = new \Application\MediaBundle\Entity\Media;
        $media->setProviderName($provider_name);

        if($form instanceof Form) {
            $media = $form->getData();
        } else {
            $form = new Form('data', $media, $this->get('validator'));
            $provider->buildCreateForm($form);
        }

        if($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));
            
            if($form->isValid()) {

                if($context) {
                    $media->setContext($context);
                }
                $this->get('media.provider')->prePersist($media);
                $this->configuration->getEntityManager()->persist($media);
                $this->configuration->getEntityManager()->flush();
                $this->get('media.provider')->postPersist($media);

                return $this->redirect($this->configuration->generateUrl('edit', array('id' => $media->getId())));
            }
        }

        $template = sprintf('Sonata/MediaBundle:MediaAdmin:provider_create_%s.twig', $provider_name);


        
        return $this->render($template, array(
            'form'   => $form,
            'media'  => $media,
            'params' => $params,
            'configuration'     => $this->configuration,
        ));
    }

    public function editAction($id)
    {

        $this->get('session')->start();

        if($id instanceof Form) {
            $media = $id->getData();
            $form   = $id;
        } else {
            $media = $this->configuration->getObject($this->get('request')->get('id'));

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
            'configuration'     => $this->configuration,
        ));
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $media = $this->configuration->getObject($this->get('request')->get('id'));

        if(!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $this->get('request')->get('id')));
        }

        $provider = $this->get('media.provider')->getProvider($media->getProviderName());

        $form = new Form('data', $media, $this->get('validator'));
        $provider->buildEditForm($form);

        $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));

        if($form->isValid()) {

            $this->get('media.provider')->preUpdate($media);
            $this->configuration->getEntityManager()->persist($form->getData());
            $this->configuration->getEntityManager()->flush();
            $this->get('media.provider')->postUpdate($media);

            return $this->redirect($this->configuration->generateUrl('edit', array('id' => $media->getId())));
        }

        return $this->forward(sprintf('%s:edit', $this->getBaseControllerName()), array(
            'id' => $form
        ));
    }
}