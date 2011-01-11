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
namespace Zend\Tool\Project\Provider;

use Zend\Tool\Project\Profile\Profile as ProjectProfile;

/**
 * @uses       \Zend\Tool\Framework\Provider\Pretendable
 * @uses       \Zend\Tool\Project\Exception
 * @uses       \Zend\Tool\Project\Provider\AbstractProvider
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Layout 
    extends AbstractProvider 
    implements \Zend\Tool\Framework\Provider\Pretendable
{
    
    public static function createResource(ProjectProfile $profile, $layoutName = 'layout')
    {
        $applicationDirectory = $profile->search('applicationDirectory');
        $layoutDirectory = $applicationDirectory->search('layoutsDirectory');
        
        if ($layoutDirectory == false) {
            $layoutDirectory = $applicationDirectory->createResource('layoutsDirectory');
        }
        
        $layoutScriptsDirectory = $layoutDirectory->search('layoutScriptsDirectory');
        
        if ($layoutScriptsDirectory == false) {
            $layoutScriptsDirectory = $layoutDirectory->createResource('layoutScriptsDirectory');
        }
        
        $layoutScriptFile = $layoutScriptsDirectory->search('layoutScriptFile', array('layoutName' => 'layout'));

        if ($layoutScriptFile == false) {
            $layoutScriptFile = $layoutScriptsDirectory->createResource('layoutScriptFile', array('layoutName' => 'layout'));
        }
        
        return $layoutScriptFile;
    }
    
    public function enable()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        
        $applicationConfigResource = $profile->search('ApplicationConfigFile');

        if (!$applicationConfigResource) {
            throw new \Zend\Tool\Project\Exception('A project with an application config file is required to use this provider.');
        }
        
        $zc = $applicationConfigResource->getAsZendConfig();
        
        if (isset($zc->resources) && isset($zf->resources->layout)) {
            $this->_registry->getResponse()->appendContent('A layout resource already exists in this project\'s application configuration file.');
            return;
        }
        
        $layoutPath = 'APPLICATION_PATH "/layouts/scripts/"';
        
        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would add "resources.layout.layoutPath" key to the application config file.');
        } else {
            $applicationConfigResource->addStringItem('resources.layout.layoutPath', $layoutPath, 'production', false);
            $applicationConfigResource->create(); 
            
            $layoutScriptFile = self::createResource($profile);
            
            $layoutScriptFile->create();
            
            $this->_registry->getResponse()->appendContent(
                'Layouts have been enabled, and a default layout created at ' 
                . $layoutScriptFile->getPath()
                );
                
            $this->_registry->getResponse()->appendContent('A layout entry has been added to the application config file.');
        }
        
       
        
    }
    
    public function disable()
    {
        // @todo
    }
    
    
    
}
