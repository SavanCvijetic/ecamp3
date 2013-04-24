<?php
/*
 * Copyright (C) 2011 Urban Suppiger
 *
 * This file is part of eCamp.
 *
 * eCamp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * eCamp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with eCamp.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/**
 * This is the base class for all plugin strategies
 *
 * Every pluginstrategy is *only* responsible for rendering a plugin and declaring some basic
 * support, but *not* for updating its configuration etc. For this purpose, use controllers
 * and models.
 */

namespace EcampCore\Plugin;
 
use EcampCore\Entity\Medium;
use EcampCore\Entity\PluginInstance;

use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractStrategy 
{

	public function __construct(
		ServiceLocatorInterface $serviceLocator, 
		PluginInstance $pluginInstance
	){
		$this->serviceLocator = $serviceLocator;
		$this->pluginInstance = $pluginInstance;
	}
	
	/**
	 * @var Zend\ServiceManager\ServiceLocatorInterface
	 */
	private $serviceLocator;
	
	/**
	 * @return Zend\ServiceManager\ServiceLocatorInterface
	 */
	protected function getServiceLocator(){
		return $this->serviceLocator;
	}
	
	
	/**
	 * @var EcampCore\Entity\PluginInstance
	 */
	private $pluginInstance;
	
	/**
	 * @return EcampCore\Entity\PluginInstance
	 */
	protected function getPluginInstance(){
		return $this->pluginInstance;
	}
	
	
	/**
	 * @param Medium $medium
	 * @return Zend\View\Model\ViewModel
	 */
	abstract function render(Medium $medium);
	
	
	/**
	 * @deprecated
	 */
	abstract function renderFrontend();
	
	/**
	* @deprecated
	*/
	abstract function renderBackend();
}