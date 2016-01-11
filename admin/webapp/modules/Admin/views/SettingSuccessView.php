<?php
/**
* SettingSuccessView goes to the default page for REST verbs for the setting table.
*  
* @author Mark Hobson 
* @since 03/13/2013 6:38 pm 
*/

require_once(MO_MODULE_DIR . "/Admin/views/AdminIndexView.php");

class SettingSuccessView extends AdminIndexView {

	/**
	 * Perform any execution code for this view
	 * @return void
	 */
	public function execute ()
	{
		parent::execute();
		
		// set our template
		$this->setTemplate("SettingSuccess.php");

		// set the title
		$this->setTitle("System Settings");
	}
} 
?>