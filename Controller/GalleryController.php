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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryController extends Controller
{

    public function indexAction()
    {

        $galleries = $this->get('sonata.media.entity_manager')
            ->getRepository('Application\Sonata\MediaBundle\Entity\Gallery')
            ->findBy(array(
                'enabled' => true
            ));

        return $this->render('SonataMediaBundle:Gallery:index.html.twig', array(
            'galleries'   => $galleries,
        ));
    }
    
    public function viewAction($id)
    {

        $gallery = $this->get('sonata.media.entity_manager')->find('Application\Sonata\MediaBundle\Entity\Gallery', $id);

        if (!$gallery) {
            throw new NotFoundHttpException('unable to find the gallery with the id');
        }

        return $this->render('SonataMediaBundle:Gallery:view.html.twig', array(
            'gallery'   => $gallery,
        ));
    }
}