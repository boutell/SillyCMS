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

use Symfony\Component\Form\ValueTransformer\DateTimeToTimestampTransformer;
use Symfony\Tests\Component\Form\DateTimeTestCase;

class DateTimeToTimestampTransformerTest extends DateTimeTestCase
{
    public function testTransform()
    {
        $transformer = new DateTimeToTimestampTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
        ));

        $input = new \DateTime('2010-02-03 04:05:06 UTC');
        $output = $input->format('U');

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransform_empty()
    {
        $transformer = new DateTimeToTimestampTransformer();

        $this->assertSame(null, $transformer->transform(null));
    }

    public function testTransform_differentTimezones()
    {
        $transformer = new DateTimeToTimestampTransformer(array(
            'input_timezone' => 'Asia/Hong_Kong',
            'output_timezone' => 'America/New_York',
        ));

        $input = new \DateTime('2010-02-03 04:05:06 America/New_York');
        $output = $input->format('U');
        $input->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformFromDifferentTimezone()
    {
        $transformer = new DateTimeToTimestampTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'Asia/Hong_Kong',
        ));

        $input = new \DateTime('2010-02-03 04:05:06 Asia/Hong_Kong');

        $dateTime = clone $input;
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $output = $dateTime->format('U');

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformExpectsDateTime()
    {
        $transformer = new DateTimeToTimestampTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->transform('1234');
    }

    public function testReverseTransform()
    {
        $reverseTransformer = new DateTimeToTimestampTransformer(array(
            'input_timezone' => 'UTC',
            'output_timezone' => 'UTC',
        ));

        $output = new \DateTime('2010-02-03 04:05:06 UTC');
        $input = $output->format('U');

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input, null));
    }

    public function testReverseTransform_empty()
    {
        $reverseTransformer = new DateTimeToTimestampTransformer();

        $this->assertSame(null, $reverseTransformer->reverseTransform(null, null));
    }

    public function testReverseTransform_differentTimezones()
    {
        $reverseTransformer = new DateTimeToTimestampTransformer(array(
            'input_timezone' => 'Asia/Hong_Kong',
            'output_timezone' => 'America/New_York',
        ));

        $output = new \DateTime('2010-02-03 04:05:06 America/New_York');
        $input = $output->format('U');
        $output->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input, null));
    }

    public function testReverseTransformExpectsValidTimestamp()
    {
        $reverseTransformer = new DateTimeToTimestampTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $reverseTransformer->reverseTransform('2010-2010-2010', null);
    }
}
