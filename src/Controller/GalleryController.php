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

namespace Sonata\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class GalleryController extends Controller
{
    /**
     * NEXT_MAJOR: remove this method.
     *
     * @return Response
     *
     * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
     */
    public function indexAction()
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since 3.x, to be removed in 4.0.',
            \E_USER_DEPRECATED
        );

        $galleries = $this->get('sonata.media.manager.gallery')->findBy([
            'enabled' => true,
        ]);

        return $this->render('@SonataMedia/Gallery/index.html.twig', [
            'galleries' => $galleries,
        ]);
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param string $id
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     *
     * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
     */
    public function viewAction($id)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since 3.x, to be removed in 4.0.',
            \E_USER_DEPRECATED
        );

        $gallery = $this->get('sonata.media.manager.gallery')->findOneBy([
            'id' => $id,
            'enabled' => true,
        ]);

        if (!$gallery) {
            throw new NotFoundHttpException('unable to find the gallery with the id');
        }

        return $this->render('@SonataMedia/Gallery/view.html.twig', [
            'gallery' => $gallery,
        ]);
    }
}
