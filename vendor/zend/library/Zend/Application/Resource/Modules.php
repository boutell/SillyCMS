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
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Application\Resource;

/**
 * Module bootstrapping resource
 *
 * @uses       ArrayObject
 * @uses       \Zend\Application\ResourceException
 * @uses       \Zend\Application\Resource\AbstractResource
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Modules extends AbstractResource
{
    /**
     * @var ArrayObject
     */
    protected $_bootstraps;

    /**
     * Constructor
     *
     * @param  mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->_bootstraps = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
        parent::__construct($options);
    }

    /**
     * Initialize modules
     *
     * @return array
     * @throws \Zend\Application\ResourceException When bootstrap class was not found
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('frontcontroller');
        $front = $bootstrap->getResource('frontcontroller');

        $modules = $front->getControllerDirectory();
        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);
        foreach ($modules as $module => $moduleDirectory) {
            $bootstrapClass = $this->_formatModuleName($module) . '\Bootstrap';
            if (!class_exists($bootstrapClass, false)) {
                $bootstrapPath  = dirname($moduleDirectory) . '/Bootstrap.php';
                if (file_exists($bootstrapPath)) {
                    $eMsgTpl = 'Bootstrap file found for module "%s" but bootstrap class "%s" not found';
                    include_once $bootstrapPath;
                    if (($default != $module)
                        && !class_exists($bootstrapClass, false)
                    ) {
                        throw new Exception\InitializationException(sprintf(
                            $eMsgTpl, $module, $bootstrapClass
                        ));
                    } elseif ($default == $module) {
                        if (!class_exists($bootstrapClass, false)) {
                            $bootstrapClass = 'Bootstrap';
                            if (!class_exists($bootstrapClass, false)) {
                                throw new Exception\InitializationException(sprintf(
                                    $eMsgTpl, $module, $bootstrapClass
                                ));
                            }
                        }
                    }
                } else {
                    continue;
                }
            }

            if ($bootstrapClass == $curBootstrapClass) {
                // If the found bootstrap class matches the one calling this
                // resource, don't re-execute.
                continue;
            }

            $moduleBootstrap = new $bootstrapClass($bootstrap);
            $moduleBootstrap->bootstrap();
            $this->_bootstraps[$module] = $moduleBootstrap;
        }

        return $this->_bootstraps;
    }

    /**
     * Get bootstraps that have been run
     *
     * @return ArrayObject
     */
    public function getExecutedBootstraps()
    {
        return $this->_bootstraps;
    }

    /**
     * Format a module name to the module class prefix
     *
     * @param  string $name
     * @return string
     */
    protected function _formatModuleName($name)
    {
        $name = strtolower($name);
        $name = str_replace(array('-', '.'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }
}
