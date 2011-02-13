<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\ValueTransformer;

require_once __DIR__ . '/../LocalizedTestCase.php';

use Symfony\Component\Form\ValueTransformer\PercentToLocalizedStringTransformer;
use Symfony\Tests\Component\Form\LocalizedTestCase;

class PercentToLocalizedStringTransformerTest extends LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \Locale::setDefault('de_AT');
    }

    public function testTransform()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertEquals('10', $transformer->transform(0.1));
        $this->assertEquals('15', $transformer->transform(0.15));
        $this->assertEquals('12', $transformer->transform(0.1234));
        $this->assertEquals('200', $transformer->transform(2));
    }

    public function testTransform_empty()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertEquals('', $transformer->transform(null));
    }

    public function testTransformWithInteger()
    {
        $transformer = new PercentToLocalizedStringTransformer(array(
            'type' => 'integer',
        ));

        $this->assertEquals('0', $transformer->transform(0.1));
        $this->assertEquals('1', $transformer->transform(1));
        $this->assertEquals('15', $transformer->transform(15));
        $this->assertEquals('16', $transformer->transform(15.9));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new PercentToLocalizedStringTransformer(array(
            'precision' => 2,
        ));

        $this->assertEquals('12,34', $transformer->transform(0.1234));
    }

    public function testReverseTransform()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertEquals(0.1, $transformer->reverseTransform('10', null));
        $this->assertEquals(0.15, $transformer->reverseTransform('15', null));
        $this->assertEquals(0.12, $transformer->reverseTransform('12', null));
        $this->assertEquals(2, $transformer->reverseTransform('200', null));
    }

    public function testReverseTransform_empty()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->assertSame(null, $transformer->reverseTransform('', null));
    }

    public function testReverseTransformWithInteger()
    {
        $transformer = new PercentToLocalizedStringTransformer(array(
            'type' => 'integer',
        ));

        $this->assertEquals(10, $transformer->reverseTransform('10', null));
        $this->assertEquals(15, $transformer->reverseTransform('15', null));
        $this->assertEquals(12, $transformer->reverseTransform('12', null));
        $this->assertEquals(200, $transformer->reverseTransform('200', null));
    }

    public function testReverseTransformWithPrecision()
    {
        $transformer = new PercentToLocalizedStringTransformer(array(
            'precision' => 2,
        ));

        $this->assertEquals(0.1234, $transformer->reverseTransform('12,34', null));
    }

    public function testTransformExpectsNumeric()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->transform('foo');
    }

    public function testReverseTransformExpectsString()
    {
        $transformer = new PercentToLocalizedStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->reverseTransform(1, null);
    }
}
