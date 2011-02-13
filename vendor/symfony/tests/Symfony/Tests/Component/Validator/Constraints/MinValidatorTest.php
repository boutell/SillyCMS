<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Validator;

use Symfony\Component\Validator\Constraints\Min;
use Symfony\Component\Validator\Constraints\MinValidator;

class MinValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    protected function setUp()
    {
        $this->validator = new MinValidator();
    }

    public function testNullIsValid()
    {
        $this->assertTrue($this->validator->isValid(null, new Min(array('limit' => 10))));
    }

    public function testExpectsNumericType()
    {
        $this->setExpectedException('Symfony\Component\Validator\Exception\UnexpectedTypeException');

        $this->validator->isValid(new \stdClass(), new Min(array('limit' => 10)));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new Min(array('limit' => 10));
        $this->assertTrue($this->validator->isValid($value, $constraint));
    }

    public function getValidValues()
    {
        return array(
            array(10.00001),
            array('10.00001'),
            array(10),
            array(10.0),
        );
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $constraint = new Min(array('limit' => 10));
        $this->assertFalse($this->validator->isValid($value, $constraint));
    }

    public function getInvalidValues()
    {
        return array(
            array(9.999999),
            array('9.999999'),
        );
    }

    public function testMessageIsSet()
    {
        $constraint = new Min(array(
            'limit' => 10,
            'message' => 'myMessage'
        ));

        $this->assertFalse($this->validator->isValid(9, $constraint));
        $this->assertEquals($this->validator->getMessageTemplate(), 'myMessage');
        $this->assertEquals($this->validator->getMessageParameters(), array(
            '{{ value }}' => 9,
            '{{ limit }}' => 10,
        ));
    }
}