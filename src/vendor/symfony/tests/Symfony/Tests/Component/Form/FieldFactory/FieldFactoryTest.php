<?php

namespace Symfony\Tests\Component\Form\FieldFactory;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Form\FieldFactory\FieldFactory;
use Symfony\Component\Form\FieldFactory\FieldFactoryGuess;
use Symfony\Component\Form\FieldFactory\FieldFactoryClassGuess;

class FieldFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testConstructThrowsExceptionIfNoGuesser()
    {
        new FieldFactory(array(new \stdClass()));
    }

    public function testGetInstanceCreatesClassWithHighestConfidence()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser1 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser1->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\TextField',
                    array('max_length' => 10),
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));

        $guesser2 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser2->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\PasswordField',
                    array('max_length' => 7),
                    FieldFactoryGuess::HIGH_CONFIDENCE
                )));

        $factory = new FieldFactory(array($guesser1, $guesser2));
        $field = $factory->getInstance($object, 'firstName');

        $this->assertEquals('Symfony\Component\Form\PasswordField', get_class($field));
        $this->assertEquals(7, $field->getMaxLength());
    }

    public function testGetInstanceThrowsExceptionIfNoClassIsFound()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(null));

        $factory = new FieldFactory(array($guesser));

        $this->setExpectedException('\RuntimeException');

        $field = $factory->getInstance($object, 'firstName');
    }

    public function testOptionsCanBeOverridden()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\TextField',
                    array('max_length' => 10),
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));

        $factory = new FieldFactory(array($guesser));
        $field = $factory->getInstance($object, 'firstName', array('max_length' => 11));

        $this->assertEquals('Symfony\Component\Form\TextField', get_class($field));
        $this->assertEquals(11, $field->getMaxLength());
    }

    public function testGetInstanceUsesMaxLengthIfFoundAndTextField()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser1 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser1->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\TextField',
                    array('max_length' => 10),
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));
        $guesser1->expects($this->once())
                ->method('guessMaxLength')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryGuess(
                	15,
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));

        $guesser2 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser2->expects($this->once())
                ->method('guessMaxLength')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryGuess(
                	20,
                    FieldFactoryGuess::HIGH_CONFIDENCE
                )));

        $factory = new FieldFactory(array($guesser1, $guesser2));
        $field = $factory->getInstance($object, 'firstName');

        $this->assertEquals('Symfony\Component\Form\TextField', get_class($field));
        $this->assertEquals(20, $field->getMaxLength());
    }

    public function testGetInstanceUsesMaxLengthIfFoundAndSubclassOfTextField()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\PasswordField',
                    array('max_length' => 10),
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));
        $guesser->expects($this->once())
                ->method('guessMaxLength')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryGuess(
                	15,
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));

        $factory = new FieldFactory(array($guesser));
        $field = $factory->getInstance($object, 'firstName');

        $this->assertEquals('Symfony\Component\Form\PasswordField', get_class($field));
        $this->assertEquals(15, $field->getMaxLength());
    }

    public function testGetInstanceUsesRequiredSettingWithHighestConfidence()
    {
        $object = new \stdClass();
        $object->firstName = 'Bernhard';

        $guesser1 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser1->expects($this->once())
                ->method('guessClass')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryClassGuess(
                	'Symfony\Component\Form\TextField',
                    array(),
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));
        $guesser1->expects($this->once())
                ->method('guessRequired')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryGuess(
                	true,
                    FieldFactoryGuess::MEDIUM_CONFIDENCE
                )));

        $guesser2 = $this->getMock('Symfony\Component\Form\FieldFactory\FieldFactoryGuesserInterface');
        $guesser2->expects($this->once())
                ->method('guessRequired')
                ->with($this->equalTo($object), $this->equalTo('firstName'))
                ->will($this->returnValue(new FieldFactoryGuess(
                	false,
                    FieldFactoryGuess::HIGH_CONFIDENCE
                )));

        $factory = new FieldFactory(array($guesser1, $guesser2));
        $field = $factory->getInstance($object, 'firstName');

        $this->assertFalse($field->isRequired());
    }
}