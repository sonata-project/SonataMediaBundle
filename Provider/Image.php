<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Provider;

use Bundle\MediaBundle\Entity\BaseMedia as Media;

class Image extends File {


    public function requireThumbnails() {
        return true;
    }

    public function getReferenceImage(Media $media) {

        return sprintf('%s/%s',
            $this->generatePrivatePath($media),
            $media->getProviderReference()
        );
    }

    public function getAbsolutePath(Media $media) {

        return $this->getReferenceImage($media);
    }


    public function generatePublicUrl(Media $media, $format) {

        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePublicPath($media),
            $media->getId(),
            $format
        );
    }

    public function generatePrivateUrl(Media $media, $format) {

        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePrivatePath($media),
            $media->getId(),
            $format
        );
    }

    public function postRemove(Media $media) {

        $files = array(
            $this->getReferenceImage($media),
        );

        foreach($this->formats as $format => $definition) {
            $files[] = $this->generatePrivateUrl($media, $format);
        }


        foreach($files as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }
}