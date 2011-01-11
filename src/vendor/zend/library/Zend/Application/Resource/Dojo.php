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
 * Resource for settings Dojo options
 *
 * @uses       \Zend\Application\Resource\AbstractResource
 * @uses       \Zend\Dojo\Dojo
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Dojo extends AbstractResource
{
    /**
     * @var \Zend\Dojo\View\Helper\Dojo\Container
     */
    protected $_dojo;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return \Zend\Dojo\View\Helper\Dojo\Container
     */
    public function init()
    {
        return $this->getDojo();
    }

    /**
     * Retrieve Dojo View Helper
     *
     * @return Zend_Dojo_View_Dojo_Container
     */
    public function getDojo()
    {
        if (null === $this->_dojo) {
            $this->getBootstrap()->bootstrap('view');
            $view = $this->getBootstrap()->view;

            \Zend\Dojo\Dojo::enableView($view);
            $view->dojo()->setOptions($this->getOptions());

            $this->_dojo = $view->dojo();
        }

        return $this->_dojo;
    }
}
