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

class File extends BaseProvider {

    public function getReferenceImage(Media $media) {

        return sprintf('%s/%s',
            $this->generatePrivatePath($media),
            $media->getProviderReference()
        );
    }

    public function getAbsolutePath(Media $media) {

        return $this->getReferenceImage($media);
    }

    public function requireThumbnails() {
        return false;
    }

    public function postPersist(Media $media)
    {

        if(!$media->getBinaryContent()) {
            return;
        }

        $filename = sprintf('%s/%s',
            $this->buildDirectory($media),
            $media->getProviderReference()
        );

        copy($media->getBinaryContent()->getPath(), $filename);

        $this->generateThumbnails($media);
    }

    public function prePersist(Media $media) {

        if(!$media->getBinaryContent()) {

            return;
        }

        // if the binary content is a filename => convert to a valid File
        if (!$media->getBinaryContent() instanceof \Symfony\Component\HttpFoundation\File\File) {

            if (!is_file($media->getBinaryContent())) {
                throw new RuntimeException('The file does not exist : ' . $media->getBinaryContent());
            }

            $infos = pathinfo($media->getBinaryContent());
            $binary_content = new \Symfony\Component\HttpFoundation\File\File($infos['basename']);

            $media->setBinaryContent($binary_content);
        }

        $media->setProviderName($this->name);
        
        // this is the original name
        if(!$media->getName()) {
            $media->setName($media->getBinaryContent()->getName());
        }

        // this is the name used to store the file
        if(!$media->getProviderReference()) {
           $media->setProviderReference(sha1($media->getBinaryContent()->getName() . rand(11111, 99999)) . $media->getBinaryContent()->getExtension()); 
        }
    }

    public function generatePublicUrl(Media $media, $format) {

        $path = sprintf('/media_bundle/images/files/%s/file.png',
            $format
        );

        if($this->settings['cdn_enabled']) {

            $path = sprintf('%s%s', $this->settings['cdn_path'], $path);
        }

        return $path;
    }

    public function generatePrivateUrl(Media $media, $format) {

        return false;
    }

    public function postRemove(Media $media)
    {
        $files = array(
            $this->getReferenceImage($media),
        );

        foreach($files as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function postUpdate(Media $media)
    {

    }

    public function preUpdate(Media $media)
    {

    }

    public function preRemove(Media $media)
    {

    }


}