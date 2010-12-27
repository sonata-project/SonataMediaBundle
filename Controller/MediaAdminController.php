<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Controller;

use Bundle\BaseApplicationBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Form;

class MediaAdminController extends Controller
{

    protected $class = 'Application\MediaBundle\Entity\Media';

    protected $list_fields = array(
        'id',
        'image' => array('template' => 'MediaBundle:MediaAdmin:list_image.twig'),
        'name',
        'enabled',
        'provider_name',
        'dimension' => array('template' => 'MediaBundle:MediaAdmin:list_dimension.twig'),
        'context',
        'cdn_flush_at'
    );

    protected $form_fields = array(
        'enabled',
        'name',
        'description',
        'author_name',
        'copyright',
        'cdn_is_flushable'
    );

    protected $base_route = 'media_admin';

    // don't know yet how to get this value
    protected $base_controller_name = 'MediaBundle:MediaAdmin';


    public function createAction($form = null)
    {
        $this->get('session')->start();

        $provider_name = $this->get('request')->get('provider');

        if(!$provider_name) {
            return $this->render('MediaBundle:MediaAdmin:select_provider.twig', array(
                'providers' => $this->get('media.provider')->getProviders(),
                'urls'      => $this->getUrls()
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

                $this->get('media.provider')->prePersist($media);
                $this->getEntityManager()->persist($media);
                $this->getEntityManager()->flush();
                $this->get('media.provider')->postPersist($media);

                return $this->redirect($this->generateUrl('media_admin_edit', array('id' => $media->getId())));
            }
        }

        $template = sprintf('MediaBundle:MediaAdmin:provider_create_%s.twig', $provider_name);
        
        return $this->render($template, array(
            'form'   => $form,
            'media'  => $media,
            'provider_name' => $provider_name,
            'urls'   => $this->getUrls()
        ));
    }

    public function editAction($id)
    {

        $this->get('session')->start();

        if($id instanceof Form) {
            $media = $id->getData();
            $form   = $id;
        } else {
            $media = $this->get('doctrine.orm.default_entity_manager')->find($this->getClass(), $id);

            if(!$media) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $provider = $this->get('media.provider')->getProvider($media->getProviderName());

            $form = new Form('data', $media, $this->get('validator'));
            $provider->buildEditForm($form);
        }

        $template = sprintf('MediaBundle:MediaAdmin:provider_edit_%s.twig', $media->getProviderName());

        return $this->render($template, array(
            'form'   => $form,
            'media'  => $media,
            'urls'   => $this->getUrls()
        ));
    }

    public function updateAction()
    {

        $this->get('session')->start();

        if($this->get('request')->getMethod() != 'POST') {
           throw new \RuntimeException('invalid request type, POST expected');
        }

        $media = $this->get('doctrine.orm.default_entity_manager')->find($this->getClass(), $this->get('request')->get('id'));

        if(!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $this->get('request')->get('id')));
        }

        $provider = $this->get('media.provider')->getProvider($media->getProviderName());

        $form = new Form('data', $media, $this->get('validator'));
        $provider->buildEditForm($form);

        $form->bind($this->get('request')->get('data'), $this->get('request')->files->get('data'));

        if($form->isValid()) {

            $this->get('media.provider')->preUpdate($media);
            $this->getEntityManager()->persist($form->getData());
            $this->getEntityManager()->flush();
            $this->get('media.provider')->postUpdate($media);

            // redirect to edit mode
            $url = $this->getUrl('edit');

            return $this->redirect($this->generateUrl($url['url'], array('id' => $media->getId())));
        }

        return $this->forward(sprintf('%s:edit', $this->getBaseControllerName()), array(
            'id' => $form
        ));
    }
}