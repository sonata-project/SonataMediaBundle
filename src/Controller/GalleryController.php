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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GalleryController extends AbstractController
{
    public function indexAction(): Response
    {
        $galleries = $this->get('sonata.media.manager.gallery')->findBy([
            'enabled' => true,
        ]);

        return $this->render('@SonataMedia/Gallery/index.html.twig', [
            'galleries' => $galleries,
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function viewAction(string $id): Response
    {
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
