<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Console\Input;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ArgvInputTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $_SERVER['argv'] = array('cli.php', 'foo');
        $input = new TestInput();
        $this->assertEquals(array('foo'), $input->getTokens(), '__construct() automatically get its input from the argv server variable');
    }

    public function testParser()
    {
        $input = new TestInput(array('cli.php', 'foo'));
        $input->bind(new InputDefinition(array(new InputArgument('name'))));
        $this->assertEquals(array('name' => 'foo'), $input->getArguments(), '->parse() parses required arguments');

        $input->bind(new InputDefinition(array(new InputArgument('name'))));
        $this->assertEquals(array('name' => 'foo'), $input->getArguments(), '->parse() is stateless');

        $input = new TestInput(array('cli.php', '--foo'));
        $input->bind(new InputDefinition(array(new InputOption('foo'))));
        $this->assertEquals(array('foo' => true), $input->getOptions(), '->parse() parses long options without a value');

        $input = new TestInput(array('cli.php', '--foo=bar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
        $this->assertEquals(array('foo' => 'bar'), $input->getOptions(), '->parse() parses long options with a required value (with a = separator)');

        $input = new TestInput(array('cli.php', '--foo', 'bar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
        $this->assertEquals(array('foo' => 'bar'), $input->getOptions(), '->parse() parses long options with a required value (with a space separator)');

        try {
            $input = new TestInput(array('cli.php', '--foo'));
            $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
            $this->fail('->parse() throws a \RuntimeException if no value is passed to an option when it is required');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if no value is passed to an option when it is required');
            $this->assertEquals('The "--foo" option requires a value.', $e->getMessage(), '->parse() throws a \RuntimeException if no value is passed to an option when it is required');
        }

        $input = new TestInput(array('cli.php', '-f'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f'))));
        $this->assertEquals(array('foo' => true), $input->getOptions(), '->parse() parses short options without a value');

        $input = new TestInput(array('cli.php', '-fbar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
        $this->assertEquals(array('foo' => 'bar'), $input->getOptions(), '->parse() parses short options with a required value (with no separator)');

        $input = new TestInput(array('cli.php', '-f', 'bar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
        $this->assertEquals(array('foo' => 'bar'), $input->getOptions(), '->parse() parses short options with a required value (with a space separator)');

        $input = new TestInput(array('cli.php', '-f', '-b', 'foo'));
        $input->bind(new InputDefinition(array(new InputArgument('name'), new InputOption('foo', 'f', InputOption::VALUE_OPTIONAL), new InputOption('bar', 'b'))));
        $this->assertEquals(array('foo' => null, 'bar' => true), $input->getOptions(), '->parse() parses short options with an optional value which is not present');

        try {
            $input = new TestInput(array('cli.php', '-f'));
            $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_REQUIRED))));
            $this->fail('->parse() throws a \RuntimeException if no value is passed to an option when it is required');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if no value is passed to an option when it is required');
            $this->assertEquals('The "--foo" option requires a value.', $e->getMessage(), '->parse() throws a \RuntimeException if no value is passed to an option when it is required');
        }

        try {
            $input = new TestInput(array('cli.php', '-ffoo'));
            $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_NONE))));
            $this->fail('->parse() throws a \RuntimeException if a value is passed to an option which does not take one');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if a value is passed to an option which does not take one');
            $this->assertEquals('The "-o" option does not exist.', $e->getMessage(), '->parse() throws a \RuntimeException if a value is passed to an option which does not take one');
        }

        try {
            $input = new TestInput(array('cli.php', 'foo', 'bar'));
            $input->bind(new InputDefinition());
            $this->fail('->parse() throws a \RuntimeException if too many arguments are passed');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if too many arguments are passed');
            $this->assertEquals('Too many arguments.', $e->getMessage(), '->parse() throws a \RuntimeException if too many arguments are passed');
        }

        try {
            $input = new TestInput(array('cli.php', '--foo'));
            $input->bind(new InputDefinition());
            $this->fail('->parse() throws a \RuntimeException if an unknown long option is passed');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if an unknown long option is passed');
            $this->assertEquals('The "--foo" option does not exist.', $e->getMessage(), '->parse() throws a \RuntimeException if an unknown long option is passed');
        }

        try {
            $input = new TestInput(array('cli.php', '-f'));
            $input->bind(new InputDefinition());
            $this->fail('->parse() throws a \RuntimeException if an unknown short option is passed');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e, '->parse() throws a \RuntimeException if an unknown short option is passed');
            $this->assertEquals('The "-f" option does not exist.', $e->getMessage(), '->parse() throws a \RuntimeException if an unknown short option is passed');
        }

        $input = new TestInput(array('cli.php', '-fb'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f'), new InputOption('bar', 'b'))));
        $this->assertEquals(array('foo' => true, 'bar' => true), $input->getOptions(), '->parse() parses short options when they are aggregated as a single one');

        $input = new TestInput(array('cli.php', '-fb', 'bar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f'), new InputOption('bar', 'b', InputOption::VALUE_REQUIRED))));
        $this->assertEquals(array('foo' => true, 'bar' => 'bar'), $input->getOptions(), '->parse() parses short options when they are aggregated as a single one and the last one has a required value');

        $input = new TestInput(array('cli.php', '-fb', 'bar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f'), new InputOption('bar', 'b', InputOption::VALUE_OPTIONAL))));
        $this->assertEquals(array('foo' => true, 'bar' => 'bar'), $input->getOptions(), '->parse() parses short options when they are aggregated as a single one and the last one has an optional value');

        $input = new TestInput(array('cli.php', '-fbbar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f'), new InputOption('bar', 'b', InputOption::VALUE_OPTIONAL))));
        $this->assertEquals(array('foo' => true, 'bar' => 'bar'), $input->getOptions(), '->parse() parses short options when they are aggregated as a single one and the last one has an optional value with no separator');

        $input = new TestInput(array('cli.php', '-fbbar'));
        $input->bind(new InputDefinition(array(new InputOption('foo', 'f', InputOption::VALUE_OPTIONAL), new InputOption('bar', 'b', InputOption::VALUE_OPTIONAL))));
        $this->assertEquals(array('foo' => 'bbar', 'bar' => null), $input->getOptions(), '->parse() parses short options when they are aggregated as a single one and one of them takes a value');
    }

    public function testGetFirstArgument()
    {
        $input = new TestInput(array('cli.php', '-fbbar'));
        $this->assertEquals('', $input->getFirstArgument(), '->getFirstArgument() returns the first argument from the raw input');

        $input = new TestInput(array('cli.php', '-fbbar', 'foo'));
        $this->assertEquals('foo', $input->getFirstArgument(), '->getFirstArgument() returns the first argument from the raw input');
    }

    public function testHasParameterOption()
    {
        $input = new TestInput(array('cli.php', '-f', 'foo'));
        $this->assertTrue($input->hasParameterOption('-f'), '->hasParameterOption() returns true if the given short option is in the raw input');

        $input = new TestInput(array('cli.php', '--foo', 'foo'));
        $this->assertTrue($input->hasParameterOption('--foo'), '->hasParameterOption() returns true if the given short option is in the raw input');

        $input = new TestInput(array('cli.php', 'foo'));
        $this->assertFalse($input->hasParameterOption('--foo'), '->hasParameterOption() returns false if the given short option is not in the raw input');
    }

    /**
     * @dataProvider provideGetParameterOptionValues
     */
    public function testGetParameterOptionEqualSign($argv, $key, $expected)
    {
        $input = new ArgvInput($argv);
        $this->assertEquals($expected, $input->getParameterOption($key), '->getParameterOption() returns the expected value');
    }

    public function provideGetParameterOptionValues()
    {
        return array(
            array(array('app/console', 'foo:bar', '-e', 'dev'), '-e', 'dev'),
            array(array('app/console', 'foo:bar', '--env=dev'), '--env', 'dev'),
            array(array('app/console', 'foo:bar', '-e', 'dev'), array('-e', '--env'), 'dev'),
            array(array('app/console', 'foo:bar', '--env=dev'), array('-e', '--env'), 'dev'),
        );
    }
}

class TestInput extends ArgvInput
{
    public function getTokens()
    {
        return $this->tokens;
    }
}
