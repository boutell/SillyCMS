<?php

namespace Symfony\Tests\Component\Validator;

require_once __DIR__.'/Fixtures/Entity.php';
require_once __DIR__.'/Fixtures/Reference.php';
require_once __DIR__.'/Fixtures/ConstraintA.php';
require_once __DIR__.'/Fixtures/ConstraintAValidator.php';
require_once __DIR__.'/Fixtures/FailingConstraint.php';
require_once __DIR__.'/Fixtures/FailingConstraintValidator.php';
require_once __DIR__.'/Fixtures/FakeClassMetadataFactory.php';

use Symfony\Tests\Component\Validator\Fixtures\Entity;
use Symfony\Tests\Component\Validator\Fixtures\Reference;
use Symfony\Tests\Component\Validator\Fixtures\FakeClassMetadataFactory;
use Symfony\Tests\Component\Validator\Fixtures\ConstraintA;
use Symfony\Tests\Component\Validator\Fixtures\FailingConstraint;
use Symfony\Component\Validator\GraphWalker;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Valid;

class GraphWalkerTest extends \PHPUnit_Framework_TestCase
{
    const CLASSNAME = 'Symfony\Tests\Component\Validator\Fixtures\Entity';

    protected $factory;
    protected $walker;
    protected $metadata;

    protected function setUp()
    {
        $this->factory = new FakeClassMetadataFactory();
        $this->walker = new GraphWalker('Root', $this->factory, new ConstraintValidatorFactory());
        $this->metadata = new ClassMetadata(self::CLASSNAME);
    }

    public function testWalkClassValidatesConstraints()
    {
        $this->metadata->addConstraint(new ConstraintA());

        $this->walker->walkClass($this->metadata, new Entity(), 'Default', '');

        $this->assertEquals(1, count($this->walker->getViolations()));
    }

    public function testWalkClassValidatesPropertyConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new ConstraintA());

        $this->walker->walkClass($this->metadata, new Entity(), 'Default', '');

        $this->assertEquals(1, count($this->walker->getViolations()));
    }

    public function testWalkClassValidatesGetterConstraints()
    {
        $this->metadata->addGetterConstraint('lastName', new ConstraintA());

        $this->walker->walkClass($this->metadata, new Entity(), 'Default', '');

        $this->assertEquals(1, count($this->walker->getViolations()));
    }

    public function testWalkClassInDefaultGroupTraversesGroupSequence()
    {
        $entity = new Entity();

        $this->metadata->addPropertyConstraint('firstName', new FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new FailingConstraint(array(
            'groups' => 'Default',
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->walker->walkClass($this->metadata, $entity, 'Default', '');

        // After validation of group "First" failed, no more group was
        // validated
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            array(),
            'Root',
            'firstName',
            ''
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkClassInGroupSequencePropagatesDefaultGroup()
    {
        $entity = new Entity();
        $entity->reference = new Reference();

        $this->metadata->addPropertyConstraint('reference', new Valid());
        $this->metadata->setGroupSequence(array($this->metadata->getDefaultGroup()));

        $referenceMetadata = new ClassMetadata(get_class($entity->reference));
        $referenceMetadata->addConstraint(new FailingConstraint(array(
            // this constraint is only evaluated if group "Default" is
            // propagated to the reference
            'groups' => 'Default',
        )));
        $this->factory->addClassMetadata($referenceMetadata);

        $this->walker->walkClass($this->metadata, $entity, 'Default', '');

        // The validation of the reference's FailingConstraint in group
        // "Default" was launched
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            array(),
            'Root',
            'reference',
            $entity->reference
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkClassInOtherGroupTraversesNoGroupSequence()
    {
        $entity = new Entity();

        $this->metadata->addPropertyConstraint('firstName', new FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new FailingConstraint(array(
            'groups' => $this->metadata->getDefaultGroup(),
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->walker->walkClass($this->metadata, $entity, $this->metadata->getDefaultGroup(), '');

        // Only group "Second" was validated
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            array(),
            'Root',
            'lastName',
            ''
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkPropertyValueValidatesConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new ConstraintA());

        $this->walker->walkPropertyValue($this->metadata, 'firstName', 'value', 'Default', '');

        $this->assertEquals(1, count($this->walker->getViolations()));
    }

    public function testWalkCascadedPropertyValidatesReferences()
    {
        $entity = new Entity();
        $entityMetadata = new ClassMetadata(get_class($entity));
        $this->factory->addClassMetadata($entityMetadata);

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new FailingConstraint());

        // validate entity when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Valid());

        // invoke validation on an object
        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            $entity,  // object!
            'Default',
            'path'
        );

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            array(),
            'Root',
            'path',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyValidatesArrays()
    {
        $entity = new Entity();
        $entityMetadata = new ClassMetadata(get_class($entity));
        $this->factory->addClassMetadata($entityMetadata);

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            array('key' => $entity), // array!
            'Default',
            'path'
        );

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            array(),
            'Root',
            'path[key]',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyDoesNotValidateNullValues()
    {
        $this->metadata->addPropertyConstraint('reference', new Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            null,
            'Default',
            ''
        );

        $this->assertEquals(0, count($this->walker->getViolations()));
    }

    public function testWalkCascadedPropertyRequiresObjectOrArray()
    {
        $this->metadata->addPropertyConstraint('reference', new Valid());

        $this->setExpectedException('Symfony\Component\Validator\Exception\UnexpectedTypeException');

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            'no object',
            'Default',
            ''
        );
    }

    public function testWalkConstraintBuildsAViolationIfFailed()
    {
        $constraint = new ConstraintA();

        $this->walker->walkConstraint($constraint, 'foobar', 'Default', 'firstName.path');

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            'message',
            array('param' => 'value'),
            'Root',
            'firstName.path',
            'foobar'
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkConstraintBuildsNoViolationIfSuccessful()
    {
        $constraint = new ConstraintA();

        $this->walker->walkConstraint($constraint, 'VALID', 'Default', 'firstName.path');

        $this->assertEquals(0, count($this->walker->getViolations()));
    }
}