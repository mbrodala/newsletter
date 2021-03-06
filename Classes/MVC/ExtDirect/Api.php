<?php

namespace Ecodev\Newsletter\MVC\ExtDirect;

use ReflectionException;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Dennis Ahrens <dennis.ahrens@fh-hannover.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * A Service that provides the Ext.Direct Api
 *
 * @author      Dennis Ahrens <dennis.ahrens@fh-hannover.de>
 */
class Api
{
    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var array
     */
    protected $frameworkConfiguration;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
    }

    /**
     * Injects the reflection service
     *
     * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
     */
    public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * Creates the remote api based on the module/plugin configuration using the extbase
     * reflection features.
     *
     * @param string $routeUrl
     * @param string $namespace
     * @return array
     */
    public function createApi($routeUrl, $namespace)
    {
        $api = array();
        $api['url'] = $routeUrl;
        $api['type'] = 'remoting';
        $api['namespace'] = $namespace;
        $api['actions'] = array();

        if (empty($this->frameworkConfiguration['controllerConfiguration'])) {
            # @todo improve me! Hack for fetching API of newsletter the hard way!
            # It looks $this->frameworkConfiguration['controllerConfiguration'] is empty as of TYPO3 6.1. Bug or feature?
            $this->frameworkConfiguration['controllerConfiguration'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Newsletter']['modules']['web_NewsletterTxNewsletterM1']['controllers'];
        }

        foreach ($this->frameworkConfiguration['controllerConfiguration'] as $controllerName => $allowedControllerActions) {
            $unstrippedControllerName = $controllerName . 'Controller';
            $controllerObjectName = 'Ecodev\\Newsletter\\Controller\\' . $unstrippedControllerName;
            $controllerActions = array();
            foreach ($allowedControllerActions['actions'] as $actionName) {
                $unstrippedActionName = $actionName . 'Action';
                try {
                    $actionParameters = $this->reflectionService->getMethodParameters($controllerObjectName, $unstrippedActionName);
                    $controllerActions[] = array(
                        'len' => count($actionParameters),
                        'name' => $unstrippedActionName,
                    );
                } catch (ReflectionException $re) {
                    if ($unstrippedActionName !== 'extObjAction') {
                        \Ecodev\Newsletter\Tools::getLogger(__CLASS__)->critical('You have a not existing action (' . $controllerObjectName . '::' . $unstrippedActionName . ') in your module/plugin configuration. This action will not be available for Ext.Direct remote execution.');
                    }
                }
            }
            $api['actions'][$unstrippedControllerName] = $controllerActions;
        }

        return $api;
    }
}
