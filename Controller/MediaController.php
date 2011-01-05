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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{

    public function viewAction($id, $format = 'reference')
    {

        $media = $this->get('doctrine.orm.default_entity_manager')->find('MediaBundle:Media', $id);

        if(!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->render('MediaBundle:Media:view.twig', array(
            'media'     => $media,
            'formats'   => $this->get('media.provider')->getProvider($media->getProviderName())->getFormats(),
            'format'    => $format
        ));
    }
}