<?php
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
use Mojavi\View\BasicView;

class DefaultIndexView extends BasicView
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @return void
	 */
	public function execute ()
	{
		parent::execute();
		// set our template

		// set the title
		$this->setTitle('SMTA');

		$this->setDecoratorTemplate(MO_TEMPLATE_DIR . "/index.shell.php");

	}
	
	/**
	 * Returns the menu
	 * @return Zend\Navigation
	 */
	function getMenu() {		
		$navigation_config = MO_WEBAPP_DIR . '/config/navigation.xml';
		
		if (file_exists($navigation_config)) {
			$navigation_contents = file_get_contents($navigation_config);
						
			// Load the modified menu
			$reader = new \Zend\Config\Reader\Xml();
			$data   = $reader->fromString($navigation_contents);
			$zend_navigation = new \Zend\Navigation\Navigation($data);
			
			return $zend_navigation;
		}
		return null;
	}

}