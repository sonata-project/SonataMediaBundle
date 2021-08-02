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

use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GalleryController extends AbstractController
{
    /**
     * @var GalleryManagerInterface
     */
    private $galleryManager;

    public function __construct(GalleryManagerInterface $galleryManager)
    {
        $this->galleryManager = $galleryManager;
    }

    public function indexAction(): Response
    {
        $galleries = $this->galleryManager->findBy([
            'enabled' => true,
        ]);

        return $this->render('@SonataMedia/Gallery/index.html.twig', [
            'galleries' => $galleries,
        ]);
    }

    /**
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     */
    public function viewAction($id): Response
    {
        $gallery = $this->galleryManager->findOneBy([
            'id' => $id,
            'enabled' => true,
        ]);

        if (null === $gallery) {
            throw new NotFoundHttpException('unable to find the gallery with the id');
        }

        return $this->render('@SonataMedia/Gallery/view.html.twig', [
            'gallery' => $gallery,
        ]);
    }
}
