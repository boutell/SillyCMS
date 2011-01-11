<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Tool\Project\Context\Zf;
use Zend\CodeGenerator\Php;

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @uses       \Zend\CodeGenerator\Php\PhpClass
 * @uses       \Zend\CodeGenerator\Php\PhpFile
 * @uses       \Zend\CodeGenerator\Php\PhpMethod
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ControllerFile extends \Zend\Tool\Project\Context\Filesystem\File
{

    /**
     * @var string
     */
    protected $_controllerName = 'index';

    /**
     * @var string
     */
    protected $_moduleName = null;
    
    /**
     * @var string
     */
    protected $_filesystemName = 'controllerName';

    /**
     * init()
     *
     */
    public function init()
    {
        $this->_controllerName = $this->_resource->getAttribute('controllerName');
        $this->_moduleName = $this->_resource->getAttribute('moduleName');
        $this->_filesystemName = ucfirst($this->_controllerName) . 'Controller.php';
        parent::init();
    }

    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'controllerName' => $this->getControllerName()
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ControllerFile';
    }

    /**
     * getControllerName()
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controllerName;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {
        $className = ($this->_moduleName) ? ucfirst($this->_moduleName) . '\\' : '';
        $className .= ucfirst($this->_controllerName) . 'Controller';
        
        $codeGenFile = new Php\PhpFile(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Php\PhpClass(array(
                    'name' => $className,
                    'extendedClass' => '\Zend\Controller\Action',
                    'methods' => array(
                        new Php\PhpMethod(array(
                            'name' => 'init',
                            'body' => '/* Initialize action controller here */',
                        	))
                    	)
                	))
            	)
        	));


        if ($className == 'ErrorController') {

            $codeGenFile = new Php\PhpFile(array(
                'fileName' => $this->getPath(),
                'classes' => array(
                    new Php\PhpClass(array(
                        'name' => $className,
                        'extendedClass' => 'Zend\Controller\Action',
                        'methods' => array(
                            new Php\PhpMethod(array(
                                'name' => 'errorAction',
                                'body' => <<<'EOS'
$errors = $this->_getParam('error_handler');

switch ($errors->type) {
    case \Zend\Controller\Plugin\ErrorHandler::EXCEPTION_NO_ROUTE:
    case \Zend\Controller\Plugin\ErrorHandler::EXCEPTION_NO_CONTROLLER:
    case \Zend\Controller\Plugin\ErrorHandler::EXCEPTION_NO_ACTION:

        // 404 error -- controller or action not found
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->vars()->message = 'Page not found';
        break;
    default:
        // application error
        $this->getResponse()->setHttpResponseCode(500);
        $this->view->vars()->message = 'Application error';
        break;
}

// Log exception, if logger available
if (($log = $this->getLog())) {
    $log->crit($this->view->vars()->message, $errors->exception);
}

// conditionally display exceptions
if ($this->getInvokeArg('displayExceptions') == true) {
    $this->view->vars()->exception = $errors->exception;
}

$this->view->vars()->request = $errors->request;
EOS
                                )),
                            new Php\PhpMethod(array(
                                'name' => 'getLog',
                                'body' => <<<'EOS'
$bootstrap = $this->getInvokeArg('bootstrap');
if (!$bootstrap->hasPluginResource('Log')) {
    return false;
}
$log = $bootstrap->getResource('Log');
return $log;
EOS
                                )),
                            )
                        ))
                    )
                ));

        }

        // store the generator into the registry so that the addAction command can use the same object later
        Php\PhpFile::registerFileCodeGenerator($codeGenFile); // REQUIRES filename to be set
        return $codeGenFile->generate();
    }

    /**
     * addAction()
     *
     * @param string $actionName
     */
    public function addAction($actionName)
    {
        $classCodeGen = $this->getCodeGenerator();
        $classCodeGen->setMethod(array('name' => $actionName . 'Action', 'body' => '        // action body here'));
        file_put_contents($this->getPath(), $classCodeGen->generate());
    }

    /**
     * getCodeGenerator()
     *
     * @return \Zend\CodeGenerator\Php\PhpClass
     */
    public function getCodeGenerator()
    {
        $codeGenFile = Php\PhpFile::fromReflectedFileName($this->getPath());
        $codeGenFileClasses = $codeGenFile->getClasses();
        $class = array_shift($codeGenFileClasses);
        return $class;
    }

}
