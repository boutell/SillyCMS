<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Templating;

/**
 * TemplateNameParser is the default implementation of TemplateNameParserInterface.
 *
 * This implementation takes everything as the template name
 * and the extension for the engine.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class TemplateNameParser implements TemplateNameParserInterface
{
    /**
     * Parses a template to an array of parameters.
     *
     * @param string $name A template name
     *
     * @return array An array of parameters
     */
    public function parse($name)
    {
        if (is_array($name)) {
            return $name;
        }

        $engine = null;
        if (false !== $pos = strrpos($name, '.')) {
            $engine = substr($name, $pos + 1);
        }

        return array('name' => $name, 'engine' => $engine);
    }
}
