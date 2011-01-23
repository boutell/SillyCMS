<?php

namespace Symfony\Component\Templating;

use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\Templating\Loader\LoaderInterface;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PhpEngine is an engine able to render PHP templates.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class PhpEngine implements EngineInterface, \ArrayAccess
{
    protected $loader;
    protected $current;
    protected $helpers;
    protected $parents;
    protected $stack;
    protected $charset;
    protected $cache;
    protected $escapers;
    protected $globals;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader  A loader instance
     * @param array           $helpers An array of helper instances
     */
    public function __construct(LoaderInterface $loader, array $helpers = array())
    {
        $this->loader  = $loader;
        $this->parents = array();
        $this->stack   = array();
        $this->charset = 'UTF-8';
        $this->cache   = array();
        $this->globals = array();

        $this->setHelpers($helpers);

        $this->initializeEscapers();
        foreach ($this->escapers as $context => $escaper) {
            $this->setEscaper($context, $escaper);
        }
    }

    /**
     * Renders a template.
     *
     * @param string $name       A template name
     * @param array  $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \InvalidArgumentException if the template does not exist
     * @throws \RuntimeException         if the template cannot be rendered
     */
    public function render($name, array $parameters = array())
    {
        $template = $this->load($name);

        $this->current = $name;
        $this->parents[$name] = null;

        // attach the global variables
        $parameters = array_replace($this->getGlobals(), $parameters);

        // render
        if (false === $content = $this->evaluate($template, $parameters)) {
            throw new \RuntimeException(sprintf('The template "%s" cannot be rendered.', $name));
        }

        // decorator
        if ($this->parents[$name]) {
            $slots = $this->get('slots');
            $this->stack[] = $slots->get('_content');
            $slots->set('_content', $content);

            $content = $this->render($this->parents[$name], $parameters);

            $slots->set('_content', array_pop($this->stack));
        }

        return $content;
    }

    /**
     * Returns true if the template exists.
     *
     * @param string $name A template name
     *
     * @return Boolean true if the template exists, false otherwise
     */
    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Loads the given template.
     *
     * @param string $name A template name
     *
     * @return Storage A Storage instance
     *
     * @throws \InvalidArgumentException if the template cannot be found
     */
    public function load($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // load
        $template = $this->loader->load($name);

        if (false === $template) {
            throw new \InvalidArgumentException(sprintf('The template "%s" does not exist.', $name));
        }

        $this->cache[$name] = $template;

        return $template;
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string $name A template name
     *
     * @return boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        return false !== strpos($name, '.php');
    }

    /**
     * Evaluates a template.
     *
     * @param Storage $template   The template to render
     * @param array   $parameters An array of parameters to pass to the template
     *
     * @return string|false The evaluated template, or false if the engine is unable to render the template
     */
    protected function evaluate(Storage $template, array $parameters = array())
    {
        $__template__ = $template;
        if ($__template__ instanceof FileStorage) {
            extract($parameters);
            $view = $this;
            ob_start();
            require $__template__;

            return ob_get_clean();
        } elseif ($__template__ instanceof StringStorage) {
            extract($parameters);
            $view = $this;
            ob_start();
            eval('; ?>'.$__template__.'<?php ;');

            return ob_get_clean();
        }

        return false;
    }

    /**
     * Gets a helper value.
     *
     * @param string $name The helper name
     *
     * @return mixed The helper value
     *
     * @throws \InvalidArgumentException if the helper is not defined
     */
    public function offsetGet($name)
    {
        return $this->$name = $this->get($name);
    }

    /**
     * Returns true if the helper is defined.
     *
     * @param string  $name The helper name
     *
     * @return Boolean true if the helper is defined, false otherwise
     */
    public function offsetExists($name)
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Sets a helper.
     *
     * @param HelperInterface $value The helper instance
     * @param string          $alias An alias
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Removes a helper.
     *
     * @param string $name The helper name
     */
    public function offsetUnset($name)
    {
        throw new \LogicException(sprintf('You can\'t unset a helper (%s).', $name));
    }

    /**
     * @param Helper[] $helpers An array of helper
     */
    public function addHelpers(array $helpers = array())
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, is_int($alias) ? null : $alias);
        }
    }

    public function setHelpers(array $helpers)
    {
        $this->helpers = array();
        $this->addHelpers($helpers);
    }

    /**
     * Sets a helper.
     *
     * @param HelperInterface $value The helper instance
     * @param string          $alias An alias
     */
    public function set(HelperInterface $helper, $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }

        $helper->setCharset($this->charset);
    }

    /**
     * Returns true if the helper if defined.
     *
     * @param string  $name The helper name
     *
     * @return Boolean true if the helper is defined, false otherwise
     */
    public function has($name)
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Gets a helper value.
     *
     * @param string $name The helper name
     *
     * @return HelperInterface The helper instance
     *
     * @throws \InvalidArgumentException if the helper is not defined
     */
    public function get($name)
    {
        if (!isset($this->helpers[$name])) {
            throw new \InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name));
        }

        return $this->helpers[$name];
    }

    /**
     * Decorates the current template with another one.
     *
     * @param string $template  The decorator logical name
     */
    public function extend($template)
    {
        $this->parents[$this->current] = $template;
    }

    /**
     * Escapes a string by using the current charset.
     *
     * @param mixed $value A variable to escape
     *
     * @return string The escaped value
     */
    public function escape($value, $context = 'html')
    {
        return call_user_func($this->getEscaper($context), $value);
    }

    /**
     * Sets the charset to use.
     *
     * @param string $charset The charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Gets the current charset.
     *
     * @return string The current charset
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Adds an escaper for the given context.
     *
     * @param string $name    The escaper context (html, js, ...)
     * @param mixed  $escaper A PHP callable
     */
    public function setEscaper($context, $escaper)
    {
        $this->escapers[$context] = $escaper;
    }

    /**
     * Gets an escaper for a given context.
     *
     * @param  string $name The context name
     *
     * @return mixed  $escaper A PHP callable
     */
    public function getEscaper($context)
    {
        if (!isset($this->escapers[$context])) {
            throw new \InvalidArgumentException(sprintf('No registered escaper for context "%s".', $context));
        }

        return $this->escapers[$context];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Returns the assigned globals.
     *
     * @return array
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Initializes the built-in escapers.
     *
     * Each function specifies a way for applying a transformation to a string
     * passed to it. The purpose is for the string to be "escaped" so it is
     * suitable for the format it is being displayed in.
     *
     * For example, the string: "It's required that you enter a username & password.\n"
     * If this were to be displayed as HTML it would be sensible to turn the
     * ampersand into '&amp;' and the apostrophe into '&aps;'. However if it were
     * going to be used as a string in JavaScript to be displayed in an alert box
     * it would be right to leave the string as-is, but c-escape the apostrophe and
     * the new line.
     *
     * For each function there is a define to avoid problems with strings being
     * incorrectly specified.
     */
    protected function initializeEscapers()
    {
        $that = $this;

        $this->escapers = array(
            'html' =>
                /**
                 * Runs the PHP function htmlspecialchars on the value passed.
                 *
                 * @param string $value the value to escape
                 *
                 * @return string the escaped value
                 */
                function ($value) use ($that)
                {
                    // Numbers and boolean values get turned into strings which can cause problems
                    // with type comparisons (e.g. === or is_int() etc).
                    return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, $that->getCharset(), false) : $value;
                },

            'js' =>
                /**
                 * A function that escape all non-alphanumeric characters
                 * into their \xHH or \uHHHH representations
                 *
                 * @param string $value the value to escape
                 * @return string the escaped value
                 */
                function ($value) use ($that)
                {
                    if ('UTF-8' != $that->getCharset()) {
                        $string = $that->convertEncoding($string, 'UTF-8', $that->getCharset());
                    }

                    $callback = function ($matches) use ($that)
                    {
                        $char = $matches[0];

                        // \xHH
                        if (!isset($char[1])) {
                            return '\\x'.substr('00'.bin2hex($char), -2);
                        }

                        // \uHHHH
                        $char = $that->convertEncoding($char, 'UTF-16BE', 'UTF-8');

                        return '\\u'.substr('0000'.bin2hex($char), -4);
                    };

                    if (null === $string = preg_replace_callback('#[^\p{L}\p{N} ]#u', $callback, $string)) {
                        throw new \InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
                    }

                    if ('UTF-8' != $that->getCharset()) {
                        $string = $that->convertEncoding($string, $that->getCharset(), 'UTF-8');
                    }

                    return $string;
                },
        );
    }

    public function convertEncoding($string, $to, $from)
    {
        if (function_exists('iconv')) {
            return iconv($from, $to, $string);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $to, $from);
        } else {
            throw new \RuntimeException('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
        }
    }

    /**
     * Gets the loader associated with this engine.
     *
     * @return LoaderInterface A LoaderInterface instance
     */
    public function getLoader()
    {
        return $this->loader;
    }
}
