<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Routing\Router;

/**
 * RouterHelper manages links between pages in a template context.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class RouterHelper extends Helper
{
    protected $generator;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     */
    public function __construct(Router $router)
    {
        $this->generator = $router->getGenerator();
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param  string  $name       The name of the route
     * @param  array   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        return $this->generator->generate($name, $parameters, $absolute);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'router';
    }
}
