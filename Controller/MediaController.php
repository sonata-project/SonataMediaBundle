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

class MediaController extends Controller
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param $id
     * @param string $format
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function viewAction($id, $format = 'reference')
    {
        $media = $this->get('sonata.media.manager.media')->findMediaBy(array('id' => $id));

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->render('SonataMediaBundle:Media:view.html.twig', array(
            'media'     => $media,
            'formats'   => $this->get('sonata.media.pool')->getProvider($media->getProviderName())->getFormats(),
            'format'    => $format
        ));
    }
}