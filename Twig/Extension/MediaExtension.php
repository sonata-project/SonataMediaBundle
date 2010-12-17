<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Twig\Extension;

use Symfony\Bundle\TwigBundle\TokenParser\HelperTokenParser;

class MediaExtension extends \Twig_Extension
{

    public function getTokenParsers()
    {

        return array(

            // {% render media_object with { 'width': 2 }%}
            new HelperTokenParser('media', '<media>, <format> with <attributes:hash>', 'templating.helper.media', 'media'),
            new HelperTokenParser('thumbnail', '<media>, <format> with <attributes:hash>', 'templating.helper.media', 'thumbnail'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'media';
    }
}

