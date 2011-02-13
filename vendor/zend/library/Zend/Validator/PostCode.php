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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Validator;
use Zend;
use Zend\Locale;

/**
 * @see        Zend_Locale
 * @see        Zend_Locale_Format
 * @see        Zend_Registry
 * @uses       \Zend\Validator\AbstractValidator
 * @uses       \Zend\Validator\Exception
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class PostCode extends AbstractValidator
{
    const INVALID  = 'postcodeInvalid';
    const NO_MATCH = 'postcodeNoMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID  => "Invalid type given. String or integer expected",
        self::NO_MATCH => "'%value%' does not appear to be a postal code",
    );

    /**
     * Locale to use
     *
     * @var string
     */
    protected $_locale;

    /**
     * Manual postal code format
     *
     * @var unknown_type
     */
    protected $_format;

    /**
     * Constructor for the integer validator
     *
     * Accepts either a string locale, a Zend_Locale object, or an array or
     * Zend_Config object containing the keys "locale" and/or "format".
     *
     * @param string|Zend_Locale|array|\Zend\Config\Config $options
     * @throws \Zend\Validator\Exception On empty format
     */
    public function __construct($options = null)
    {
        if ($options instanceof \Zend\Config\Config) {
            $options = $options->toArray();
        }

        if (empty($options)) {
            if (\Zend\Registry::isRegistered('Zend_Locale')) {
                $this->setLocale(\Zend\Registry::get('Zend_Locale'));
            }
        } elseif (is_array($options)) {
            // Received
            if (array_key_exists('locale', $options)) {
                $this->setLocale($options['locale']);
            }

            if (array_key_exists('format', $options)) {
                $this->setFormat($options['format']);
            }
        } elseif ($options instanceof Locale\Locale || is_string($options)) {
            // Received Locale object or string locale
            $this->setLocale($options);
        }

        $format = $this->getFormat();
        if (empty($format)) {
            throw new Exception\InvalidArgumentException("A postcode-format string has to be given for validation");
        }
    }

    /**
     * Returns the set locale
     *
     * @return string|\Zend\Locale\Locale The set locale
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale to use
     *
     * @param string|\Zend\Locale\Locale $locale
     * @throws \Zend\Validator\Exception On unrecognised region
     * @throws \Zend\Validator\Exception On not detected format
     * @return \Zend\Validator\PostCode  Provides fluid interface
     */
    public function setLocale($locale = null)
    {
        $this->_locale = Locale\Locale::findLocale($locale);
        $locale        = new Locale\Locale($this->_locale);
        $region        = $locale->getRegion();
        if (empty($region)) {
            throw new Exception\InvalidArgumentException("Unable to detect a region for the locale '$locale'");
        }

        $format = Locale\Locale::getTranslation(
            $locale->getRegion(),
            'postaltoterritory',
            $this->_locale
        );

        if (empty($format)) {
            throw new Exception\InvalidArgumentException("Unable to detect a postcode format for the region '{$locale->getRegion()}'");
        }

        $this->setFormat($format);
        return $this;
    }

    /**
     * Returns the set postal code format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets a self defined postal format as regex
     *
     * @param string $format
     * @throws \Zend\Validator\Exception On empty format
     * @return \Zend\Validator\PostCode  Provides fluid interface
     */
    public function setFormat($format)
    {
        if (empty($format) || !is_string($format)) {
            throw new Exception\InvalidArgumentException("A postcode-format string has to be given for validation");
        }

        if ($format[0] !== '/') {
            $format = '/^' . $format;
        }

        if ($format[strlen($format) - 1] !== '/') {
            $format .= '$/';
        }

        $this->_format = $format;
        return $this;
    }

    /**
     * Returns true if and only if $value is a valid postalcode
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if (!is_string($value) && !is_int($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $format = $this->getFormat();
        if (!preg_match($format, $value)) {
            $this->_error(self::NO_MATCH);
            return false;
        }

        return true;
    }
}
