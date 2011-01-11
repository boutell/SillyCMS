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
 * @uses       \Zend\Filter\FilterChain
 * @uses       \Zend\Filter\StringToLower
 * @uses       \Zend\Filter\Word\CamelCaseToDash
 * @uses       \Zend\Tool\Project\Context\Filesystem\File
 * @uses       \Zend\Tool\Project\Exception
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ViewScriptFile extends \Zend\Tool\Project\Context\Filesystem\File
{

    /**
     * @var string
     */
    protected $_filesystemName = 'view.phtml';

    /**
     * @var string
     */
    protected $_forActionName = null;

    /**
     * @var string
     */
    protected $_scriptName = null;

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ViewScriptFile';
    }

    /**
     * init()
     *
     * @return \Zend\Tool\Project\Context\Zf\ViewScriptFile
     */
    public function init()
    {
        if ($forActionName = $this->_resource->getAttribute('forActionName')) {
            $this->_forActionName = $forActionName;
            $this->_filesystemName = $this->_convertActionNameToFilesystemName($forActionName) . '.phtml';
        } elseif ($scriptName = $this->_resource->getAttribute('scriptName')) {
            $this->_scriptName = $scriptName;
            $this->_filesystemName = $scriptName . '.phtml';
        } else {
            throw Zend_Tool_Project_Exception('Either a forActionName or scriptName is required.');
        }

        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return unknown
     */
    public function getPersistentAttributes()
    {
        $attributes = array();

        if ($this->_forActionName) {
            $attributes['forActionName'] = $this->_forActionName;
        }

        if ($this->_scriptName) {
            $attributes['scriptName'] = $this->_scriptName;
        }

        return $attributes;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {
        $contents = '';

        if ($this->_filesystemName == 'error.phtml') {  // should also check that the above directory is forController=error
            $contents .= <<<'EOS'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Zend Framework Default Application</title>
</head>
<body>
  <h1>An error occurred</h1>
  <h2><?php echo $this->vars('message') ?></h2>

  <?php if ($this->vars('exception')): ?>

  <h3>Exception information:</h3>
  <p>
      <b>Message:</b> <?php echo $this->vars('exception')->getMessage() ?>
  </p>

  <h3>Stack trace:</h3>
  <pre><?php echo $this->vars('exception')->getTraceAsString() ?>
  </pre>

  <h3>Request Parameters:</h3>
  <pre><?php echo var_export($this->vars('request')->getParams(), true) ?>
  </pre>
  <?php endif ?>

</body>
</html>

EOS;
        } elseif ($this->_forActionName == 'index' && $this->_resource->getParentResource()->getAttribute('forControllerName') == 'Index') {

            $contents =<<<'EOS'
<style>
    a:link,
    a:visited
    {
        color: #0398CA;
    }

    span#zf-name
    {
        color: #91BE3F;
    }

    div#welcome
    {
        color: #FFFFFF;
        background-image: url(http://framework.zend.com/images/bkg_header.jpg);
        width:  600px;
        height: 400px;
        border: 2px solid #444444;
        overflow: hidden;
        text-align: center;
    }

    div#more-information
    {
        background-image: url(http://framework.zend.com/images/bkg_body-bottom.gif);
        height: 100%;
    }
</style>
<div id="welcome">
    <h1>Welcome to the <span id="zf-name">Zend Framework!</span></h1>

    <h3>This is your project's main page</h3>

    <div id="more-information">
        <p><img src="http://framework.zend.com/images/PoweredBy_ZF_4LightBG.png" /></p>
        <p>
            Helpful Links: <br />
            <a href="http://framework.zend.com/">Zend Framework Website</a> |
            <a href="http://framework.zend.com/manual/en/">Zend Framework Manual</a>
        </p>
    </div>
</div>
EOS;

        } else {
            $contents = '<br /><br /><center>View script for controller <b>' . $this->_resource->getParentResource()->getAttribute('forControllerName') . '</b>'
            . ' and script/action name <b>' . $this->_forActionName . '</b></center>';
        }
        return $contents;
    }

    protected function _convertActionNameToFilesystemName($actionName)
    {
        $filter = new \Zend\Filter\FilterChain();
        $filter->addFilter(new \Zend\Filter\Word\CamelCaseToDash())
        ->addFilter(new \Zend\Filter\StringToLower());
        return $filter->filter($actionName);
    }

}
