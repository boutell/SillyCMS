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
namespace Zend\Tool\Framework\System\Provider;
use Zend\Tool\Framework;

/**
 * Configuration Provider
 *
 * @uses       ReflectionClass
 * @uses       \Zend\Config\Config
 * @uses       \Zend\Config\Writer\Ini
 * @uses       \Zend\Loader
 * @uses       \Zend\Tool\Framework\Exception
 * @uses       \Zend\Tool\Framework\Provider\AbstractProvider
 * @category   Zend
 * @package    Zend_Tool
 * @package    Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Config extends Framework\Provider\AbstractProvider
{
    /**
     * @var array
     */
    protected $_levelCompleted = array();

    /**
     * array of specialties handled by this provider
     * 
     * @var array
     */
    protected $_specialties = array('Manifest', 'Provider');
    
    /**
     * @param string $type
     */
    public function create()
    {
        /* @var $userConfig Zend_Tool_Framework_Client_Config */
        $userConfig = $this->_registry->getConfig();

        $resp = $this->_registry->getResponse();
        if ($userConfig->exists()) {
            throw new Framework\Exception(
                "A configuration already exists, cannot create a new one.");
        }

        $homeDirectory = $this->_detectHomeDirectory();

        $writer = new end\Config\Writer\Ini();
        $writer->setRenderWithoutSections();
        $filename = $homeDirectory."/.zf.ini";

        $config = array(
            'php' => array(
                'includepath' => get_include_path(),
            ),
        );
        $writer->write($filename, new end\Config\Config($config));

        $resp = $this->_registry->getResponse();
        $resp->appendContent("Successfully written Zend Tool config.");
        $resp->appendContent("It is located at: ".$filename);
    }

    /**
     * @return string
     */
    protected function _detectHomeDirectory()
    {
        $envVars = array("ZF_HOME", "HOME", "HOMEPATH");
        foreach($envVars AS $env) {
            $homeDirectory = getenv($env);
            if ($homeDirectory != false && file_exists($homeDirectory)) {
                return $homeDirectory;
            }
        }
        throw new Framework\Exception("Cannot detect user home directory, set ZF_HOME enviroment variable.");
    }

    /**
     * Show Zend Tool User Configuration
     *
     * @return void
     */
    public function show()
    {
        $userConfig = $this->_loadUserConfigIfExists();
        $configArray = $userConfig->getConfigInstance()->toArray();

        $resp = $this->_registry->getResponse();

        $i = 0;
        $tree = "";
        foreach($configArray AS $k => $v) {
            $i++;
            $tree .= $this->_printTree($k, $v, 1, count($configArray)==$i);
        }
        $resp->appendContent("User Configuration: ".$userConfig->getConfigFilepath(), array("color" => "green"));
        $resp->appendContent($tree, array("indention" => 2));
    }

    /**
     *
     * @param string $key
     * @param string $value
     * @param int $level
     * @return string
     */
    protected function _printTree($key, $value, $level=1, $isLast=false)
    {
        $this->_levelCompleted[$level] = false;

        $prefix = "";
        for ($i = 1; $i < $level; $i++) {
            if ($this->_levelCompleted[$i] == true) {
                $prefix .= "    ";
            } else {
                $prefix .= "|   ";
            }
        }
        if ($isLast) {
            $pointer = "`-- ";
        } else {
            $pointer = "|-- ";
        }

        $tree = "";
        if (is_array($value)) {
            $tree .= $prefix.$pointer.$key.PHP_EOL;

            if ($isLast == true) {
                $this->_levelCompleted[$level] = true;
            }

            $i = 0;
            foreach ($value as $k => $v) {
                $i++;
                $tree .= $this->_printTree($k, $v, $level+1, (count($value)==$i));
            }
        } else {
            $tree .= $prefix.$pointer.$key.": ".trim($value).PHP_EOL;
        }

        return $tree;
    }

    public function enable()
    {
        $resp = $this->_registry->getResponse();
        $resp->appendContent('Use either "zf enable config.provider" or "zf enable config.manifest".');
    }

    public function disable()
    {
        $resp = $this->_registry->getResponse();
        $resp->appendContent('Use either "zf disable config.provider" or "zf disable config.manifest".');
    }

    /**
     * @param string $className
     */
    public function enableProvider($className)
    {
        end\Loader::loadClass($className);
        $reflClass = new \ReflectionClass($className);
        if (!in_array("Zend_Tool_Framework_Provider_Interface", $reflClass->getInterfaceNames())) {
            throw new Framework\Exception("Given class is not a provider");
        }
        $this->_doEnable($className);
    }

    protected function _doEnable($className)
    {

        $userConfig = $this->_loadUserConfigIfExists();

        if (!isset($userConfig->basicloader)) {
            $userConfig->basicloader = array();
        }
        if (!isset($userConfig->basicloader->classes)) {
            $userConfig->basicloader->classes = array();
        }

        $providerClasses = $userConfig->basicloader->classes->toArray();
        if (!in_array($className, $providerClasses)) {
            if (count($providerClasses)) {
                $pos = max(array_keys($providerClasses))+1;
            } else {
                $pos = 0;
            }
            $userConfig->basicloader->classes->$pos = $className;

            if ($userConfig->save()) {
                $this->_registry->getResponse()->appendContent(
                    "Provider/Manifest '".$className."' was enabled for usage with Zend Tool.",
                    array("color" => "green", "aligncenter" => true)
                );
            } else {
                throw new Framework\Exception(
                    "Could not write user configuration to persistence."
                );
            }
        } else {
            throw new Framework\Exception(
                "Provider/Manifest '".$className."' is already enabled."
            );
        }
    }

    /**
     * @param string $className
     */
    public function enableManifest($className)
    {
        end\Loader::loadClass($className);
        $reflClass = new \ReflectionClass($className);
        if (!in_array("Zend_Tool_Framework_Manifest_Interface", $reflClass->getInterfaceNames())) {
            throw new Framework\Exception("Given class is not a manifest.");
        }
        $this->_doEnable($className);
    }

    /**
     * @param string $className
     */
    public function disableManifest($className)
    {
        $this->disableProvider($className);
    }

    /**
     * @param string $className
     */
    public function disableProvider($className)
    {
        $userConfig = $this->_loadUserConfigIfExists();

        if (!isset($userConfig->basicloader)) {
            $userConfig->basicloader = array();
        }
        if (!isset($userConfig->basicloader->classes)) {
            $userConfig->basicloader->classes = array();
        }

        $providerClasses = $userConfig->basicloader->classes->toArray();
        if (($key = array_search($className, $providerClasses)) !== false) {
            unset($userConfig->basicloader->classes->$key);

            if ($userConfig->save()) {
                $this->_registry->getResponse()->appendContent(
                    "Provider/Manifest '".$className."' was disabled.",
                    array("color" => "green", "aligncenter" => true)
                );
            } else {
                throw new Framework\Exception(
                    "Could not write user configuration to persistence."
                );
            }
        } else {
            throw new Framework\Exception(
                "Provider/Manifest '".$className."' is not enabled."
            );
        }
    }

    /**
     * @return \Zend\Tool\Framework\Client\Config
     */
    protected function _loadUserConfigIfExists()
    {
        /* @var $userConfig Zend_Tool_Framework_Client_Config */
        $userConfig = $this->_registry->getConfig();

        $resp = $this->_registry->getResponse();
        if (!$userConfig->exists()) {
            $resp->appendContent("User has no config file.", array("aligncenter" => true, "color" => array('hiWhite', 'bgRed')));
        }

        return $userConfig;
    }
}
