<?php

/**
 * This file is part of SillyCMS.
 *
 * (c) 2010 P'unk Avenue LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Tom Boutell <tom@punkave.com>
 * @package SillyCMS
 * @subpackage Twig
 */
  
namespace Application\SillyCMSBundle\Twig\Extensions;
use \Twig_Filter_Method;
use \Twig_Environment;
use \Twig_Extension;

class WikiText extends Twig_Extension
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * @return Symfony\Component\Routing\Router $router
     */
    public function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'wiki_text' => new Twig_Filter_Method($this, 'twig_wikitext_filter', array('is_safe' => array('html'), 'pre_escape' => 'html'))
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'WikiText';
    }

    public function twig_wikitext_filter($text)
    {
        // Multiline regexp won't work with \r in there unless
        // autodetect is set, if that even works
        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace(
        array(
            "/(http\:.*?)([\s\]\)\}]|$)/",
            "/^= (.*?) =$/m",
            "/^== (.*?) ==$/m",
            "/^=== (.*?) ===$/m",
            "/^==== (.*?) ====$/m",
        ),
        array(
            "<a href=\"$1\">$1</a>$2",
            "<h2>$1</h2>",
            "<h3>$1</h3>",
            "<h4>$1</h4>",
            "<h5>$1</h5>"
        ),
        $text);

        //PHP 5.3 rocks so we can use anonymous function goodness even though it is overkill
        $router = $this->getRouter();
        $text = \preg_replace_callback("/\[\[(.*?)\]\]/m", function($match) use ($router) {
            $parts = \explode('|', $match[1]);
            if(count($parts) > 1)
            {
                $slug = $parts[0];
                $name = $parts[1];
            } else
            {
                $name = $slug = $match[1];
            }
            return '<a href="'.$router->generate('show', array('slug' => $slug))."\">$name</a>";

        }, $text);

        $text = str_replace("\n", "<br />\n", $text);
        // But web convention is \r\n
        $text = str_replace("\n", "\r\n", $text);
        
        return $text;
    }

}


