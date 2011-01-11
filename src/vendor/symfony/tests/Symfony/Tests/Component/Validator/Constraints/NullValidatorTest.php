<?php

namespace Symfony\Tests\Component\Validator;

use Symfony\Component\Validator\Constraints\Null;
use Symfony\Component\Validator\Constraints\NullValidator;

class NullValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    protected function setUp()
    {
        $this->validator = new NullValidator();
    }

    public function testNullIsValid()
    {
        $this->assertTrue($this->validator->isValid(null, new Null()));
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $this->assertFalse($this->validator->isValid($value, new Null()));
    }

    public function getInvalidValues()
    {
        return array(
            array(0),
            array(false),
            array(true),
            array(''),
        );
    }

    public function testSetMessage()
    {
        $constraint = new Null(array(
            'message' => 'myMessage'
        ));

        $this->assertFalse($this->validator->isValid(1, $constraint));
        $this->assertEquals($this->validator->getMessageTemplate(), 'myMessage');
        $this->assertEquals($this->validator->getMessageParameters(), array(
            '{{ value }}' => 1,
        ));
    }
}