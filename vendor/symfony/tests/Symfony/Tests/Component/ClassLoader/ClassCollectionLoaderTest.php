<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\ClassLoader;

use Symfony\Component\ClassLoader\ClassCollectionLoader;

class ClassCollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testFixNamespaceDeclarations()
    {
        $source = <<<EOF
<?php

namespace Foo;
class Foo {}
namespace   Bar ;
class Foo {}
namespace Foo\Bar;
class Foo {}
namespace Foo\Bar\Bar
{
    class Foo {}
}
namespace
{
    class Foo {}
}
EOF;

        $expected = <<<EOF
<?php

namespace Foo
{
class Foo {}
}
namespace   Bar 
{
class Foo {}
}
namespace Foo\Bar
{
class Foo {}
}
namespace Foo\Bar\Bar
{
    class Foo {}
}
namespace
{
    class Foo {}
}
EOF;

        $this->assertEquals($expected, ClassCollectionLoader::fixNamespaceDeclarations($source));
    }
}
