<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH.'../system/libraries/Form_validation.php');
class validation extends CI_Form_validation {
public $error_array;
	function __construct() {
		parent::__construct();	
	}
	
	function set_error_array(){
		if(count($this->_error_array)>0){
			$keys = array_keys($this->_error_array);
			$values = array_values($this->_error_array);
			$this->error_array = array('key'=>$keys[0],'value'=>$values[0]);
			return $this->error_array;
		}
	}

	function exec($type){
		$ret = & $this->run();
		$error = $this->set_error_array();
		if($type == 'error') {
			return $error;
		} else{
			return $ret;
		}
	}
}

// END validation Class

/* End of file validation.php */
/* Location: ./app/libraries/validation.php */
