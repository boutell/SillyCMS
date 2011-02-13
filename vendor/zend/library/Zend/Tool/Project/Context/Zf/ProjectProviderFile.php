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

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @uses       \Zend\CodeGenerator\Php\PhpClass
 * @uses       \Zend\CodeGenerator\Php\PhpFile
 * @uses       \Zend\CodeGenerator\Php\PhpMethod
 * @uses       \Zend\Filter\Word\DashToCamelCase
 * @uses       \Zend\Tool\Project\Context\Filesystem\File
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ProjectProviderFile extends \Zend\Tool\Project\Context\Filesystem\File
{

    /**
     * @var string
     */
    protected $_projectProviderName = null;

    /**
     * @var array
     */
    protected $_actionNames = array();

    /**
     * init()
     *
     * @return \Zend\Tool\Project\Context\Zf\ProjectProviderFile
     */
    public function init()
    {

        $this->_projectProviderName = $this->_resource->getAttribute('projectProviderName');
        $this->_actionNames = $this->_resource->getAttribute('actionNames');
        $this->_filesystemName = ucfirst($this->_projectProviderName) . 'Provider.php';

        if (strpos($this->_actionNames, ',')) {
            $this->_actionNames = explode(',', $this->_actionNames);
        } else {
            $this->_actionNames = ($this->_actionNames) ? array($this->_actionNames) : array();
        }

        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'projectProviderName' => $this->getProjectProviderName(),
            'actionNames' => implode(',', $this->_actionNames)
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectProviderFile';
    }

    /**
     * getProjectProviderName()
     *
     * @return string
     */
    public function getProjectProviderName()
    {
        return $this->_projectProviderName;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {

        $filter = new \Zend\Filter\Word\DashToCamelCase();

        $className = $filter->filter($this->_projectProviderName) . 'Provider';

        $class = new \Zend\CodeGenerator\Php\PhpClass(array(
            'name' => $className,
            'extendedClass' => '\Zend\Tool\Project\Provider\AbstractProvider'
            ));

        $methods = array();
        foreach ($this->_actionNames as $actionName) {
            $methods[] = new \Zend\CodeGenerator\Php\PhpMethod(array(
                'name' => $actionName,
                'body' => '        /** @todo Implementation */'
                ));
        }

        if ($methods) {
            $class->setMethods($methods);
        }

        $codeGenFile = new \Zend\CodeGenerator\Php\PhpFile(array(
            'classes' => array($class)
            ));

        return $codeGenFile->generate();
    }

}
