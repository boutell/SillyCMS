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
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Validator\File;
use Zend\Validator\File;
use Zend\Validator;

/**
 * @category   Zend
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Validator
 */
class FilesSizeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->multipleOptionsDetected = false;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = array(
            array(array('min' => 0, 'max' => 2000), true, true, false),
            array(array('min' => 0, 'max' => '2 MB'), true, true, true),
            array(array('min' => 0, 'max' => '2MB'), true, true, true),
            array(array('min' => 0, 'max' => '2  MB'), true, true, true),
            array(2000, true, true, false),
            array(array('min' => 0, 'max' => 500), false, false, false),
            array(500, false, false, false)
        );

        foreach ($valuesExpected as $element) {
            $validator = new File\FilesSize($element[0]);
            $this->assertEquals(
                $element[1],
                $validator->isValid(__DIR__ . '/_files/testsize.mo'),
                "Tested with " . var_export($element, 1)
            );
            $this->assertEquals(
                $element[2],
                $validator->isValid(__DIR__ . '/_files/testsize2.mo'),
                "Tested with " . var_export($element, 1)
            );
            $this->assertEquals(
                $element[3],
                $validator->isValid(__DIR__ . '/_files/testsize3.mo'),
                "Tested with " . var_export($element, 1)
            );
        }

        $validator = new File\FilesSize(array('min' => 0, 'max' => 200));
        $this->assertEquals(false, $validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertTrue(array_key_exists('fileFilesSizeNotReadable', $validator->getMessages()));

        $validator = new File\FilesSize(array('min' => 0, 'max' => 500000));
        $this->assertEquals(true, $validator->isValid(array(
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo')));
        $this->assertEquals(true, $validator->isValid(__DIR__ . '/_files/testsize.mo'));
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\FilesSize(array('min' => 1, 'max' => 100));
        $this->assertEquals('1B', $validator->getMin());

        $validator = new File\FilesSize(array('min' => 1, 'max' => 100));
        $this->assertEquals('1B', $validator->getMin());
        
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\FilesSize(array('min' => 100, 'max' => 1));
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\FilesSize(array('min' => 1000, 'max' => 10000));
        $validator->setMin(100);
        $this->assertEquals('100B', $validator->getMin());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'less than or equal');
        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new File\FilesSize(array('min' => 1, 'max' => 100));
        $this->assertEquals('100B', $validator->getMax());

        $validator = new File\FilesSize(array('min' => 1, 'max' => 100000));
        $this->assertEquals('97.66kB', $validator->getMax());

        $validator = new File\FilesSize(2000);
        $validator->setUseByteString(false);
        $test = $validator->getMax();
        $this->assertEquals('2000', $test);
        
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\FilesSize(array('min' => 100, 'max' => 1));
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\FilesSize(array('min' => 1000, 'max' => 10000));
        $validator->setMax(1000000);
        $this->assertEquals('976.56kB', $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals('976.56kB', $validator->getMax());
    }

    public function testConstructorShouldRaiseErrorWhenPassedMultipleOptions()
    {
        $handler = set_error_handler(array($this, 'errorHandler'), E_USER_NOTICE);
        $validator = new File\FilesSize(1000, 10000);
        restore_error_handler();
    }

    /**
     * Ensures that the validator returns size infos
     *
     * @return void
     */
    public function testFailureMessage()
    {
        $validator = new File\FilesSize(array('min' => 9999, 'max' => 10000));
        $this->assertFalse($validator->isValid(array(
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo'))
            );
        $messages = $validator->getMessages();
        $this->assertContains('9.76kB', current($messages));
        $this->assertContains('1.55kB', current($messages));

        $validator = new File\FilesSize(array('min' => 9999, 'max' => 10000, 'bytestring' => false));
        $this->assertFalse($validator->isValid(array(
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo'))
            );
        $messages = $validator->getMessages();
        $this->assertContains('9999', current($messages));
        $this->assertContains('1588', current($messages));
    }

    public function errorHandler($errno, $errstr)
    {
        if (strstr($errstr, 'deprecated')) {
            $this->multipleOptionsDetected = true;
        }
    }
}
