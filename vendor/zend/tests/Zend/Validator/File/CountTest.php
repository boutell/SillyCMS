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
 * @see Zend_Validator_File_Count
 */

/**
 * @category   Zend
 * @package    Zend_Validator_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Validator
 */
class CountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = array(
            array(5, true, true, true, true),
            array(array('min' => 0, 'max' => 3), true, true, true, false),
            array(array('min' => 2, 'max' => 3), false, true, true, false),
            array(array('min' => 2), false, true, true, true),
            array(array('max' => 5), true, true, true, true),
            );

        foreach ($valuesExpected as $element) {
            $validator = new File\Count($element[0]);
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
            $this->assertEquals(
                $element[4],
                $validator->isValid(__DIR__ . '/_files/testsize4.mo'),
                "Tested with " . var_export($element, 1)
            );
        }
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\Count(array('min' => 1, 'max' => 5));
        $this->assertEquals(1, $validator->getMin());
    }
    
    public function testGetMinGreaterThanOrEqualThrowsException()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\Count(array('min' => 5, 'max' => 1));
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\Count(array('min' => 1000, 'max' => 10000));
        $validator->setMin(100);
        $this->assertEquals(100, $validator->getMin());

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
        $validator = new File\Count(array('min' => 1, 'max' => 100));
        $this->assertEquals(100, $validator->getMax());

        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException', 'greater than or equal');
        $validator = new File\Count(array('min' => 5, 'max' => 1));
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\Count(array('min' => 1000, 'max' => 10000));
        $validator->setMax(1000000);
        $this->assertEquals(1000000, $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals(1000000, $validator->getMax());
    }
}
