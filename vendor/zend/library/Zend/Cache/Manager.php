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
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Cache;
use Zend\Config;

/**
 * @uses       \Zend\Cache\Cache
 * @uses       \Zend\Cache\Exception
 * @category   Zend
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Manager
{
    /**
     * Constant holding reserved name for default Page Cache
     */
    const PAGECACHE = 'page';

    /**
     * Constant holding reserved name for default Page Tag Cache
     */
    const PAGETAGCACHE = 'pagetag';

    /**
     * Array of caches stored by the Cache Manager instance
     *
     * @var array
     */
    protected $_caches = array();

    /**
     * Array of ready made configuration templates for lazy
     * loading caches.
     *
     * @var array
     */
    protected $_optionTemplates = array(
        // Null Cache (Enforce Null/Empty Values)
        'skeleton' => array(
            'frontend' => array(
                'name'    => null,
                'options' => array(),
            ),
            'backend' => array(
                'name'    => null,
                'options' => array(),
            ),
        ),
        // Simple Common Default
        'default' => array(
            'frontend' => array(
                'name'    => 'Core',
                'options' => array(
                    'automatic_serialization' => true,
                ),
            ),
            'backend' => array(
                'name'    => 'File',
                'options' => array(
                    'cache_dir' => '../cache',
                ),
            ),
        ),
        // Static Page HTML Cache
        'page' => array(
            'frontend' => array(
                'name'    => 'Capture',
                'options' => array(
                    'ignore_user_abort' => true,
                ),
            ),
            'backend' => array(
                'name'    => 'Static-Backend',
                'options' => array(
                    'public_dir' => '../public',
                ),
            ),
        ),
        // Tag Cache
        'pagetag' => array(
            'frontend' => array(
                'name'    => 'Core',
                'options' => array(
                    'automatic_serialization' => true,
                    'lifetime' => null
                ),
            ),
            'backend' => array(
                'name'    => 'File',
                'options' => array(
                    'cache_dir' => '../cache',
                    'cache_file_umask' => 0644
                ),
            ),
        ),
    );

    /**
     * Set a new cache for the Cache Manager to contain
     *
     * @param  string $name
     * @param  \Zend\Cache\Frontend $cache
     * @return \Zend\Cache\Manager
     */
    public function setCache($name, Frontend $cache)
    {
        $this->_caches[$name] = $cache;
        return $this;
    }

    /**
     * Check if the Cache Manager contains the named cache object, or a named
     * configuration template to lazy load the cache object
     *
     * @param string $name
     * @return bool
     */
    public function hasCache($name)
    {
        if (isset($this->_caches[$name])
            || $this->hasCacheTemplate($name)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Fetch the named cache object, or instantiate and return a cache object
     * using a named configuration template
     *
     * @param  string $name
     * @return \Zend\Cache\Core
     */
    public function getCache($name)
    {
        if (isset($this->_caches[$name])) {
            return $this->_caches[$name];
        }
        if (isset($this->_optionTemplates[$name])) {
            if ($name == self::PAGECACHE 
                && (!isset($this->_optionTemplates[$name]['backend']['options']['tag_cache']) 
                || !$this->_optionTemplates[$name]['backend']['options']['tag_cache'] instanceof Core)
            ) {
                $this->_optionTemplates[$name]['backend']['options']['tag_cache']
                    = $this->getCache(self::PAGETAGCACHE);
            }
            $this->_caches[$name] = Cache::factory(
                $this->_optionTemplates[$name]['frontend']['name'],
                $this->_optionTemplates[$name]['backend']['name'],
                isset($this->_optionTemplates[$name]['frontend']['options']) ? $this->_optionTemplates[$name]['frontend']['options'] : array(),
                isset($this->_optionTemplates[$name]['backend']['options']) ? $this->_optionTemplates[$name]['backend']['options'] : array(),
                isset($this->_optionTemplates[$name]['frontend']['customFrontendNaming']) ? $this->_optionTemplates[$name]['frontend']['customFrontendNaming'] : false,
                isset($this->_optionTemplates[$name]['backend']['customBackendNaming']) ? $this->_optionTemplates[$name]['backend']['customBackendNaming'] : false,
                isset($this->_optionTemplates[$name]['frontendBackendAutoload']) ? $this->_optionTemplates[$name]['frontendBackendAutoload'] : false
            );
            return $this->_caches[$name];
        }
    }

    /**
     * Set a named configuration template from which a cache object can later
     * be lazy loaded
     *
     * @param  string $name
     * @param  array $options
     * @return \Zend\Cache\Manager
     */
    public function setCacheTemplate($name, $options)
    {
        if ($options instanceof Config\Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Exception('Options passed must be in'
                . ' an associative array or instance of Zend_Config');
        }
        $this->_optionTemplates[$name] = $options;
        return $this;
    }

    /**
     * Check if the named configuration template
     *
     * @param  string $name
     * @return bool
     */
    public function hasCacheTemplate($name)
    {
        if (isset($this->_optionTemplates[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Get the named configuration template
     *
     * @param  string $name
     * @return array
     */
    public function getCacheTemplate($name)
    {
        if (isset($this->_optionTemplates[$name])) {
            return $this->_optionTemplates[$name];
        }
    }

    /**
     * Pass an array containing changes to be applied to a named
     * configuration
     * template
     *
     * @param  string $name
     * @param  array $options
     * @return \Zend\Cache\Manager
     * @throws \Zend\Cache\Exception for invalid options format or if option templates do not have $name
     */
    public function setTemplateOptions($name, $options)
    {
        if ($options instanceof Config\Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Exception('Options passed must be in'
                . ' an associative array or instance of Zend_Config');
        }
        if (!isset($this->_optionTemplates[$name])) {
            throw new Exception('A cache configuration template'
                . ' does not exist with the name "' . $name . '"');
        }
        $this->_optionTemplates[$name]
            = $this->_mergeOptions($this->_optionTemplates[$name], $options);
        return $this;
    }

    /**
     * Simple method to merge two configuration arrays
     *
     * @param  array $current
     * @param  array $options
     * @return array
     */
    protected function _mergeOptions(array $current, array $options)
    {
        if (isset($options['frontend']['name'])) {
            $current['frontend']['name'] = $options['frontend']['name'];
        }
        if (isset($options['backend']['name'])) {
            $current['backend']['name'] = $options['backend']['name'];
        }
        if (isset($options['frontend']['options'])) {
            foreach ($options['frontend']['options'] as $key=>$value) {
                $current['frontend']['options'][$key] = $value;
            }
        }
        if (isset($options['backend']['options'])) {
            foreach ($options['backend']['options'] as $key=>$value) {
                $current['backend']['options'][$key] = $value;
            }
        }
        return $current;
    }
}
