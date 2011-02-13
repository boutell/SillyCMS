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

require_once __DIR__ . '/../DateTimeTestCase.php';

use Symfony\Component\Form\ValueTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Tests\Component\Form\DateTimeTestCase;

class DateTimeToLocalizedStringTransformerTest extends DateTimeTestCase
{
    protected $dateTime;
    protected $dateTimeWithoutSeconds;

    protected function setUp()
    {
        parent::setUp();

        \Locale::setDefault('de_AT');

        $this->dateTime = new \DateTime('2010-02-03 04:05:06 UTC');
        $this->dateTimeWithoutSeconds = new \DateTime('2010-02-03 04:05:00 UTC');
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        if ($expected instanceof \DateTime && $actual instanceof \DateTime) {
            $expected = $expected->format('c');
            $actual = $actual->format('c');
        }

        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public function testTransformShortDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'short',
        ));
        $this->assertEquals('03.02.10 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformMediumDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'medium',
        ));

        $this->assertEquals('03.02.2010 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformLongDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'long',
        ));

        $this->assertEquals('03. Februar 2010 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformFullDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'full',
        ));

        $this->assertEquals('Mittwoch, 03. Februar 2010 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformShortTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'short',
        ));

        $this->assertEquals('03.02.2010 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformMediumTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'medium',
        ));

        $this->assertEquals('03.02.2010 04:05:06', $transformer->transform($this->dateTime));
    }

    public function testTransformLongTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'long',
        ));

        $this->assertEquals('03.02.2010 04:05:06 GMT+00:00', $transformer->transform($this->dateTime));
    }

    public function testTransformFullTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'full',
        ));

        $this->assertEquals('03.02.2010 04:05:06 GMT+00:00', $transformer->transform($this->dateTime));
    }

    public function testTransformToDifferentLocale()
    {
        \Locale::setDefault('en_US');

        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
        ));

        $this->assertEquals('Feb 3, 2010 4:05 AM', $transformer->transform($this->dateTime));
    }

    public function testTransform_empty()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransform_differentTimezones()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'America/New_York',
            'output_timezone' => 'Asia/Hong_Kong',
        ));


        $input = new \DateTime('2010-02-03 04:05:06 America/New_York');

        $dateTime = clone $input;
        $dateTime->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($dateTime->format('d.m.Y H:i'), $transformer->transform($input));
    }

    public function testTransformRequiresValidDateTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->transform('2010-01-01');
    }

    public function testTransformWrapsIntlErrors()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        // HOW TO REPRODUCE?

        //$this->setExpectedException('Symfony\Component\Form\ValueTransformer\Transdate_formationFailedException');

        //$transformer->transform(1.5);
    }

    public function testReverseTransformShortDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'short',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('03.02.10 04:05', null));
    }

    public function testReverseTransformMediumDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'medium',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('03.02.2010 04:05', null));
    }

    public function testReverseTransformLongDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'long',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('03. Februar 2010 04:05', null));
    }

    public function testReverseTransformFullDate()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'date_format' => 'full',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('Mittwoch, 03. Februar 2010 04:05', null));
    }

    public function testReverseTransformShortTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'short',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('03.02.2010 04:05', null));
    }

    public function testReverseTransformMediumTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'medium',
        ));

        $this->assertDateTimeEquals($this->dateTime, $transformer->reverseTransform('03.02.2010 04:05:06', null));
    }

    public function testReverseTransformLongTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'long',
        ));

        $this->assertDateTimeEquals($this->dateTime, $transformer->reverseTransform('03.02.2010 04:05:06 GMT+00:00', null));
    }

    public function testReverseTransformFullTime()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
            'time_format' => 'full',
        ));

        $this->assertDateTimeEquals($this->dateTime, $transformer->reverseTransform('03.02.2010 04:05:06 GMT+00:00', null));
    }

    public function testReverseTransformFromDifferentLocale()
    {
        \Locale::setDefault('en_US');

        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
        ));

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('Feb 3, 2010 04:05 AM', null));
    }

    public function testReverseTransform_differentTimezones()
    {
        $transformer = new DateTimeToLocalizedStringTransformer(array(
            'input_timezone' => 'America/New_York',
            'output_timezone' => 'Asia/Hong_Kong',
        ));


        $dateTime = new \DateTime('2010-02-03 04:05:00 Asia/Hong_Kong');
        $dateTime->setTimezone(new \DateTimeZone('America/New_York'));

        $this->assertDateTimeEquals($dateTime, $transformer->reverseTransform('03.02.2010 04:05', null));
    }

    public function testReverseTransform_empty()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->assertSame(null, $transformer->reverseTransform('', null));
    }

    public function testReverseTransformRequiresString()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->reverseTransform(12345, null);
    }

    public function testReverseTransformWrapsIntlErrors()
    {
        $transformer = new DateTimeToLocalizedStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\ValueTransformer\TransformationFailedException');

        $transformer->reverseTransform('12345', null);
    }

    public function testValidateDateFormatOption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        new DateTimeToLocalizedStringTransformer(array('date_format' => 'foobar'));
    }

    public function testValidateTimeFormatOption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        new DateTimeToLocalizedStringTransformer(array('time_format' => 'foobar'));
    }
}
