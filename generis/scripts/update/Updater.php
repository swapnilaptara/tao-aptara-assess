<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\generis\scripts\update;

use core_kernel_impl_ApiModelOO;
use common_Logger;
use common_ext_ExtensionsManager;
use oat\generis\model\data\permission\PermissionManager;
use oat\generis\model\data\ModelManager;

/**
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater {
    
    /**
     * 
     * @param string $currentVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion) {

        $currentVersion = $initialVersion;
        if ($currentVersion == '2.7') {
        
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'widgetdefinitions_2.7.1.rdf';
        
            $api = core_kernel_impl_ApiModelOO::singleton();
            $success = $api->importXmlRdf('http://www.tao.lu/datatypes/WidgetDefinitions.rdf', $file);
            
            if ($success) {
                $currentVersion = '2.7.1';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.1') {
        
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'widgetdefinitions_2.7.2.rdf';
        
            $api = core_kernel_impl_ApiModelOO::singleton();
            $success = $api->importXmlRdf('http://www.tao.lu/datatypes/WidgetDefinitions.rdf', $file);
        
            if ($success) {
                $currentVersion = '2.7.2';
            } else{
                common_Logger::w('Import failed for '.$file);
            }
        }
        
        if ($currentVersion == '2.7.2') {
            $implClass = common_ext_ExtensionsManager::singleton()->getExtensionById('generis')->getConfig(PermissionManager::CONFIG_KEY);
            if (is_string($implClass)) {
                if (class_exists($implClass)) {
                    $impl = new $implClass();
                    PermissionManager::setPermissionModel($impl);
                    $currentVersion = '2.7.3';
                } else {
                    common_Logger::w('Unexpected permission manager config type: '.gettype($implClass));
                }
            } else {
                common_Logger::w('Unexpected permission manager config type: '.gettype($implClass));
            }
        }
        
        if ($currentVersion == '2.7.3') {
            ModelManager::setModel(new \core_kernel_persistence_smoothsql_SmoothModel(array(
                \core_kernel_persistence_smoothsql_SmoothModel::OPTION_PERSISTENCE => 'default',
                \core_kernel_persistence_smoothsql_SmoothModel::OPTION_READABLE_MODELS => $this->getReadableModelIds(),
                \core_kernel_persistence_smoothsql_SmoothModel::OPTION_WRITEABLE_MODELS => array('1'),
                \core_kernel_persistence_smoothsql_SmoothModel::OPTION_NEW_TRIPLE_MODEL => '1'
            )));
            $currentVersion = '2.7.4';
        }

        if ($currentVersion == '2.7.4' && defined('GENERIS_URI_PROVIDER')) {
            if (in_array(GENERIS_URI_PROVIDER, array('DatabaseSerialUriProvider', 'AdvKeyValueUriProvider'))) {
                $uriProviderClassName = '\core_kernel_uri_' . GENERIS_URI_PROVIDER;
                $options = array(
                	\core_kernel_uri_DatabaseSerialUriProvider::OPTION_PERSISTENCE => 'default',
                    \core_kernel_uri_DatabaseSerialUriProvider::OPTION_NAMESPACE => LOCAL_NAMESPACE.'#'
                );
                $provider = new $uriProviderClassName($options);
            } else {
                $uriProviderClassName = '\common_uri_' . GENERIS_URI_PROVIDER;
                $provider = new $uriProviderClassName();
            }
            \core_kernel_uri_UriService::singleton()->setUriProvider($provider);
            $currentVersion = '2.7.5';
        }

        return $currentVersion;
    }
    
    private function getReadableModelIds() {
        $extensionManager = \common_ext_ExtensionsManager::singleton();
        \common_ext_NamespaceManager::singleton()->reset();
        
        $uris = array(LOCAL_NAMESPACE.'#');
        foreach ($extensionManager->getModelsToLoad() as $subModelUri){
            if(!preg_match("/#$/", $subModelUri)){
                $subModelUri .= '#';
            }
            $uris[] = $subModelUri;
        }
        $ids = array();
        foreach(\common_ext_NamespaceManager::singleton()->getAllNamespaces() as $namespace){
            if(in_array($namespace->getUri(), $uris)){
                $ids[] = $namespace->getModelId();
            }
        }
        return array_unique($ids);
    }
}
