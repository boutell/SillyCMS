<?php
namespace ZendTest\Form\Element\TestAsset;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Form
 */
class SessionContainer
{
    protected static $_word;

    public function __get($name)
    {
        if ('word' == $name) {
            return self::$_word;
        }

        return null;
    }

    public function __set($name, $value)
    {
        if ('word' == $name) {
            self::$_word = $value;
        } else {
            $this->$name = $value;
        }
    }

    public function __isset($name)
    {
        if (('word' == $name) && (null !== self::$_word))  {
            return true;
        }

        return false;
    }

    public function __call($method, $args)
    {
        switch ($method) {
            case 'setExpirationHops':
            case 'setExpirationSeconds':
                $this->$method = array_shift($args);
                break;
            default:
        }
    }
}
