<?php       
class ControllerCommonLogout extends Controller {   
	public function index() { 
    	$this->user->logout();
 
 		unset($this->session->data);

		$this->redirect($this->url->link('common/login', '', 'SSL'));
  	}
}  
?>