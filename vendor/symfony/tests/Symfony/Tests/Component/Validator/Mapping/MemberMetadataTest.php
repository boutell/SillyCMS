<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Validator\Mapping;

require_once __DIR__.'/../Fixtures/ConstraintA.php';
require_once __DIR__.'/../Fixtures/ConstraintB.php';
require_once __DIR__.'/../Fixtures/ClassConstraint.php';

use Symfony\Tests\Component\Validator\Fixtures\ConstraintA;
use Symfony\Tests\Component\Validator\Fixtures\ConstraintB;
use Symfony\Tests\Component\Validator\Fixtures\ClassConstraint;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Mapping\MemberMetadata;

class MemberMetadataTest extends \PHPUnit_Framework_TestCase
{
    protected $metadata;

    protected function setUp()
    {
        $this->metadata = new TestMemberMetadata(
            'Symfony\Tests\Component\Validator\Fixtures\Entity',
            'getLastName',
            'lastName'
        );
    }

    public function testAddValidSetsMemberToCascaded()
    {
        $result = $this->metadata->addConstraint(new Valid());

        $this->assertEquals(array(), $this->metadata->getConstraints());
        $this->assertEquals($result, $this->metadata);
        $this->assertTrue($this->metadata->isCascaded());
    }

    public function testAddOtherConstraintDoesNotSetMemberToCascaded()
    {
        $result = $this->metadata->addConstraint($constraint = new ConstraintA());

        $this->assertEquals(array($constraint), $this->metadata->getConstraints());
        $this->assertEquals($result, $this->metadata);
        $this->assertFalse($this->metadata->isCascaded());
    }

    public function testAddConstraintRequiresClassConstraints()
    {
        $this->setExpectedException('Symfony\Component\Validator\Exception\ConstraintDefinitionException');

        $this->metadata->addConstraint(new ClassConstraint());
    }

    public function testSerialize()
    {
        $this->metadata->addConstraint(new ConstraintA(array('property1' => 'A')));
        $this->metadata->addConstraint(new ConstraintB(array('groups' => 'TestGroup')));

        $metadata = unserialize(serialize($this->metadata));

        $this->assertEquals($this->metadata, $metadata);
    }
}

class TestMemberMetadata extends MemberMetadata
{
    public function getValue($object)
    {
    }

    protected function newReflectionMember()
    {
    }
}
