<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetLocale()
    {
        $translator = new Translator('en', new MessageSelector());

        $this->assertEquals('en', $translator->getLocale());

        $translator->setLocale('fr');
        $this->assertEquals('fr', $translator->getLocale());
    }

    public function testSetFallbackLocale()
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en');
        $translator->addResource('array', array('bar' => 'foobar'), 'fr');

        // force catalogue loading
        $translator->trans('bar');

        $translator->setFallbackLocale('fr');
        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    public function testTransWithFallbackLocale()
    {
        $translator = new Translator('en_US', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en_US');
        $translator->addResource('array', array('bar' => 'foobar'), 'en');

        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWhenAResourceHasNoRegisteredLoader()
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en');

        $translator->trans('foo');
    }

    /**
     * @dataProvider getTransTests
     */
    public function testTrans($expected, $id, $translation, $parameters, $locale, $domain)
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array($id => $translation), $locale, $domain);

        $this->assertEquals($expected, $translator->trans($id, $parameters, $domain, $locale));
    }

    /**
     * @dataProvider getFlattenedTransTests
     */
    public function testFlattenedTrans($expected, $messages, $id)
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', $messages, 'fr', '');

        $this->assertEquals($expected, $translator->trans($id, array(), '', 'fr'));
    }

    /**
     * @dataProvider getTransChoiceTests
     */
    public function testTransChoice($expected, $id, $translation, $number, $parameters, $locale, $domain)
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array($id => $translation), $locale, $domain);

        $this->assertEquals($expected, $translator->transChoice($id, $number, $parameters, $domain, $locale));
    }

    public function getTransTests()
    {
        return array(
            array('Symfony2 est super !', 'Symfony2 is great!', 'Symfony2 est super !', array(), 'fr', ''),
            array('Symfony2 est awesome !', 'Symfony2 is %what%!', 'Symfony2 est %what% !', array('%what%' => 'awesome'), 'fr', ''),
        );
    }

    public function getFlattenedTransTests()
    {
        $messages = array(
            'symfony2' => array(
                'is' => array(
                    'great' => 'Symfony2 est super!'
                )
            ),
            'foo' => array(
                'bar' => array(
                    'baz' => 'Foo Bar Baz'
                ),
                'baz' => 'Foo Baz',
            ),
        );

        return array(
            array('Symfony2 est super!', $messages, 'symfony2.is.great'),
            array('Foo Bar Baz', $messages, 'foo.bar.baz'),
            array('Foo Baz', $messages, 'foo.baz'),
        );
    }

    public function getTransChoiceTests()
    {
        return array(
            array('Il y a 0 pomme', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il y a 0 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il y a 0 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il n\'y a aucune pomme', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),
        );
    }
}
