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
 * @package    Zend_Validator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Validator;
use Zend\Validator;

/**
 * @see Zend_Validator_PostCode
 */

/**
 * @category   Zend
 * @package    Zend_Validator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Validator
 */
class PostCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Zend_Validator_PostCode object
     *
     * @var Zend_Validator_PostCode
     */
    protected $_validator;

    /**
     * Creates a new Zend\Validator\PostCode object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        $this->_validator = new Validator\PostCode('de_AT');
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = array(
            array('2292', true),
            array('1000', true),
            array('0000', true),
            array('12345', false),
            array(1234, true),
            array(9821, true),
            array('21A4', false),
            array('ABCD', false),
            array(true, false),
            array('AT-2292', false),
            array(1.56, false)
        );

        foreach ($valuesExpected as $element) {
            $this->assertEquals($element[1], $this->_validator->isValid($element[0]),
                'Test failed with ' . var_export($element, 1));
        }
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals(array(), $this->_validator->getMessages());
    }

    /**
     * Ensures that a region is available
     */
    public function testSettingLocalesWithoutRegion()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'Unable to detect a region');
        $this->_validator->setLocale('de');
    }

    /**
     * Ensures that the region contains postal codes
     */
    public function testSettingLocalesWithoutPostalCodes()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'Unable to detect a postcode format');
        $this->_validator->setLocale('gez_ER');
    }

    /**
     * Ensures locales can be retrieved
     */
    public function testGettingLocale()
    {
        $this->assertEquals('de_AT', $this->_validator->getLocale());
    }

    /**
     * Ensures format can be set and retrieved
     */
    public function testSetGetFormat()
    {
        $this->_validator->setFormat('\d{1}');
        $this->assertEquals('/^\d{1}$/', $this->_validator->getFormat());

        $this->_validator->setFormat('/^\d{1}');
        $this->assertEquals('/^\d{1}$/', $this->_validator->getFormat());

        $this->_validator->setFormat('/^\d{1}$/');
        $this->assertEquals('/^\d{1}$/', $this->_validator->getFormat());

        $this->_validator->setFormat('\d{1}$/');
        $this->assertEquals('/^\d{1}$/', $this->_validator->getFormat());
    }
    
    public function testSetGetFormatThrowsExceptionOnNullFormat()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'A postcode-format string has to be given');
        $this->_validator->setFormat(null);        
    }
    
    public function testSetGetFormatThrowsExceptionOnEmptyFormat()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'A postcode-format string has to be given');
        $this->_validator->setFormat('');
    }

    /**
     * @group ZF-9212
     */
    public function testErrorMessageText()
    {
        $this->assertFalse($this->_validator->isValid('hello'));
        $message = $this->_validator->getMessages();
        $this->assertContains('not appear to be a postal code', $message['postcodeNoMatch']);
    }
}
