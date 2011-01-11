<?php

namespace Doctrine\Tests\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Mapping\Driver\XmlDriver,
    Doctrine\ORM\Mapping\Driver\YamlDriver;

require_once __DIR__ . '/../../TestInit.php';

class XmlMappingDriverTest extends AbstractMappingDriverTest
{
    protected function _loadDriver()
    {
        return new XmlDriver(__DIR__ . DIRECTORY_SEPARATOR . 'xml');
    }

    public function testClassTableInheritanceDiscriminatorMap()
    {
        $className = 'Doctrine\Tests\ORM\Mapping\CTI';
        $mappingDriver = $this->_loadDriver();

        $class = new ClassMetadata($className);
        $mappingDriver->loadMetadataForClass($className, $class);

        $expectedMap = array(
            "foo" => "Doctrine\Tests\ORM\Mapping\CTIFoo",
            "bar" => "Doctrine\Tests\ORM\Mapping\CTIBar",
            "baz" => "Doctrine\Tests\ORM\Mapping\CTIBaz",
        );

        $this->assertEquals(3, count($class->discriminatorMap));
        $this->assertEquals($expectedMap, $class->discriminatorMap);
    }

    /**
     * @param string $xmlMappingFile
     * @dataProvider dataValidSchema
     */
    public function testValidateXmlSchema($xmlMappingFile)
    {
        $xsdSchemaFile = __DIR__ . "/../../../../../doctrine-mapping.xsd";

        $dom = new \DOMDocument('UTF-8');
        $dom->load($xmlMappingFile);
        $this->assertTrue($dom->schemaValidate($xsdSchemaFile));
    }

    static public function dataValidSchema()
    {
        return array(
            array(__DIR__ . "/xml/Doctrine.Tests.ORM.Mapping.CTI.dcm.xml"),
            array(__DIR__ . "/xml/Doctrine.Tests.ORM.Mapping.User.dcm.xml"),
            array(__DIR__ . "/xml/CatNoId.dcm.xml"),
        );
    }
}

class CTI
{
    public $id;
}

class CTIFoo extends CTI {}
class CTIBar extends CTI {}
class CTIBaz extends CTI {}