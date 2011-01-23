<?php

namespace Symfony\Bundle\FrameworkBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Form\FieldInterface;
use Symfony\Component\Form\FieldGroupInterface;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Form is a factory that wraps Form instances.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class FormHelper extends Helper
{
    static protected $cache = array();

    protected $engine;

    public function __construct(DelegatingEngine $engine)
    {
        $this->engine = $engine;
    }

    public function getName()
    {
        return 'form';
    }

    public function attributes($attributes)
    {
        if ($attributes instanceof \Traversable) {
            $attributes = iterator_to_array($attributes);
        }

        return implode('', array_map(array($this, 'attributesCallback'), array_keys($attributes), array_values($attributes)));
    }

    private function attribute($name, $value)
    {
        return sprintf('%s="%s"', $name, true === $value ? $name : $value);
    }

    /**
     * Prepares an attribute key and value for HTML representation.
     *
     * It removes empty attributes, except for the value one.
     *
     * @param  string $name   The attribute name
     * @param  string $value  The attribute value
     *
     * @return string The HTML representation of the HTML key attribute pair.
     */
    private function attributesCallback($name, $value)
    {
        if (false === $value || null === $value || ('' === $value && 'value' != $name)) {
            return '';
        } else {
            return ' '.$this->attribute($name, $value);
        }
    }

    /**
     * Renders the form tag.
     *
     * This method only renders the opening form tag.
     * You need to close it after the form rendering.
     *
     * This method takes into account the multipart widgets.
     *
     * @param  string $url         The URL for the action
     * @param  array  $attributes  An array of HTML attributes
     *
     * @return string An HTML representation of the opening form tag
     */
    public function enctype(/*Form */$form)
    {
        return $form->isMultipart() ? ' enctype="multipart/form-data"' : '';
    }

    public function render(/*FieldInterface */$field, array $attributes = array(), array $parameters = array(), $template = null)
    {
        if (null === $template) {
            $template = $this->lookupTemplate($field);

            if (null === $template) {
                throw new \RuntimeException(sprintf('Unable to find a template to render the "%s" widget.', $field->getKey()));
            }
        }

        return trim($this->engine->render($template, array(
            'field'  => $field,
            'attr'   => $attributes,
            'params' => $parameters,
        )));
    }

    public function label(/*FieldInterface */$field, $label = false, array $parameters = array(), $template = null)
    {
        if (null === $template) {
            $template = 'FrameworkBundle:Form:label.php.html';
        }

        return $this->engine->render($template, array(
            'field'  => $field,
            'params' => $parameters,
            'label'  => $label ? $label : ucfirst(strtolower(str_replace('_', ' ', $field->getKey())))
        ));
    }

    public function errors(/*FieldInterface */$field, array $parameters = array(), $template = null)
    {
        if (null === $template) {
            $template = 'FrameworkBundle:Form:errors.php.html';
        }

        return $this->engine->render($template, array(
            'field'  => $field,
            'params' => $parameters,
        ));
    }

    public function hidden(/*FieldGroupInterface */$group, array $parameters = array(), $template = null)
    {
        if (null === $template) {
            $template = 'FrameworkBundle:Form:hidden.php.html';
        }

        return $this->engine->render($template, array(
            'field'  => $group,
            'params' => $parameters,
        ));
    }

    protected function lookupTemplate(/*FieldInterface */$field)
    {
        $fqClassName = get_class($field);
        $template = null;

        if (isset(self::$cache[$fqClassName])) {
            return self::$cache[$fqClassName];
        }

        // find a template for the given class or one of its parents
        $currentFqClassName = $fqClassName;

        do {
            $parts = explode('\\', $currentFqClassName);
            $className = array_pop($parts);

            $underscoredName = strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($className, '_', '.')));

            if ($this->engine->exists($guess = 'FrameworkBundle:Form:'.$underscoredName.'.php.html')) {
                $template = $guess;
            }

            $currentFqClassName = get_parent_class($currentFqClassName);
        } while (null === $template && false !== $currentFqClassName);

        if (null === $template && $field instanceof FieldGroupInterface) {
            $template = 'FrameworkBundle:Form:field_group.php.html';
        }

        self::$cache[$fqClassName] = $template;

        return $template;
    }
}
