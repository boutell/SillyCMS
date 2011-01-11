<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\View\Helper;
use Zend;

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * @uses       \Zend\Registry
 * @uses       \Zend\View\Exception
 * @uses       \Zend\View\Helper\Placeholder\Container\AbstractContainer
 * @uses       \Zend\View\Helper\Placeholder\Container\Standalone
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class HeadTitle extends Placeholder\Container\Standalone
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_HeadTitle';

    /**
     * Whether or not auto-translation is enabled
     * @var boolean
     */
    protected $_translate = false;

    /**
     * Translation object
     *
     * @var \Zend\Translator\Adapter\Adapter
     */
    protected $_translator;

    /**
     * Default title rendering order (i.e. order in which each title attached)
     *
     * @var string
     */
    protected $_defaultAttachOrder = null;

    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType
     * @param  string $separator
     * @return \Zend\View\Helper\HeadTitle
     */
    public function direct($title = null, $setType = null)
    {
        if ($setType === null && is_null($this->getDefaultAttachOrder())) {
            $setType = Placeholder\Container\AbstractContainer::APPEND;
        } elseif ($setType === null && !is_null($this->getDefaultAttachOrder())) {
            $setType = $this->getDefaultAttachOrder();
        }
        $title = (string) $title;
        if ($title !== '') {
            if ($setType == Placeholder\Container\AbstractContainer::SET) {
                $this->set($title);
            } elseif ($setType == Placeholder\Container\AbstractContainer::PREPEND) {
                $this->prepend($title);
            } else {
                $this->append($title);
            }
        }

        return $this;
    }

    /**
     * Set a default order to add titles
     *
     * @param string $setType
     */
    public function setDefaultAttachOrder($setType)
    {
        if (!in_array($setType, array(
            Placeholder\Container\AbstractContainer::APPEND,
            Placeholder\Container\AbstractContainer::SET,
            Placeholder\Container\AbstractContainer::PREPEND
        ))) {
            throw new Zend\View\Exception("You must use a valid attach order: 'PREPEND', 'APPEND' or 'SET'");
        }
        $this->_defaultAttachOrder = $setType;
    }

    /**
     * Get the default attach order, if any.
     *
     * @return mixed
     */
    public function getDefaultAttachOrder()
    {
        return $this->_defaultAttachOrder;
    }

    /**
     * Sets a translation Adapter for translation
     *
     * @param  Zend_Translate|\Zend\Translator\Adapter\Adapter $translate
     * @return \Zend\View\Helper\HeadTitle
     */
    public function setTranslator($translate)
    {
        if ($translate instanceof \Zend\Translator\Adapter) {
            $this->_translator = $translate;
        } elseif ($translate instanceof \Zend\Translator\Translator) {
            $this->_translator = $translate->getAdapter();
        } else {
            $e = new \Zend\View\Exception("You must set an instance of Zend_Translate or Zend_Translate_Adapter");
            $e->setView($this->view);
            throw $e;
        }
        return $this;
    }

    /*
     * Retrieve translation object
     *
     * If none is currently registered, attempts to pull it from the registry
     * using the key 'Zend_Translate'.
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if (null === $this->_translator) {
            if (\Zend\Registry::isRegistered('Zend_Translate')) {
                $this->setTranslator(\Zend\Registry::get('Zend_Translate'));
            }
        }
        return $this->_translator;
    }

    /**
     * Enables translation
     *
     * @return \Zend\View\Helper\HeadTitle
     */
    public function enableTranslation()
    {
        $this->_translate = true;
        return $this;
    }

    /**
     * Disables translation
     *
     * @return \Zend\View\Helper\HeadTitle
     */
    public function disableTranslation()
    {
        $this->_translate = false;
        return $this;
    }

    /**
     * Turn helper into string
     *
     * @param  string|null $indent
     * @param  string|null $locale
     * @return string
     */
    public function toString($indent = null, $locale = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $items = array();

        if($this->_translate && $translator = $this->getTranslator()) {
            foreach ($this as $item) {
                $items[] = $translator->translate($item, $locale);
            }
        } else {
            foreach ($this as $item) {
                $items[] = $item;
            }
        }

        $separator = $this->getSeparator();
        $output = '';
        if(($prefix = $this->getPrefix())) {
            $output  .= $prefix;
        }
        $output .= implode($separator, $items);
        if(($postfix = $this->getPostfix())) {
            $output .= $postfix;
        }

        $output = ($this->_autoEscape) ? $this->_escape($output) : $output;

        return $indent . '<title>' . $output . '</title>';
    }
}
