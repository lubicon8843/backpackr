<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH.'libraries/RestController.php';

class Api extends RestController
{

	function __construct()
	{
		// Construct the parent class
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('user_model');
	}

	public function users_get()
	{
		// 조회
		$parm = $this->get();

		$result_all = $this->user_model->total_cnt();

		if( $parm['page'] && $parm['page'] > 0 ){
			$b_max = ($parm['limit'])?$parm['limit']:2;
			$page = $parm['page'];

			$total_all = ($result_all > 0)?$result_all:0;

			if( $total_all ){
				$total_page = intval($total_all / $b_max);
				if( $total_page * $b_max != $total_all )
					$total_page ++;
				$skip = ($page - 1) * $b_max;
			}else{
				$total_page = 1;
				$total_all = 0;
			}

			$parm['skip'] = $skip;
			$parm['max'] = $b_max;

			$pinfo['total_cnt'] = $result_all;
			$pinfo['total_page'] = $total_page;
			$pinfo['now_page'] = $page;
			$pinfo['limit'] = $b_max;

		}else{
			$pinfo = "";
		}


		$users = $this->user_model->get_list($parm);

		if( $users ){
			// JSON 형태로 리턴
			$this->response(['status' => true , 'list' => $users , 'page_info' => $pinfo] , 200);
		}else{
			$this->response(['status' => false , 'message' => 'No users were found'] , 404);
		}

	}

	public function users_post()
	{
		//삽입

		$post_data_ = file_get_contents('php://input');
		$post_data = json_decode($post_data_ , TRUE);

		$this->val_check($post_data , 'in');

		$result = $this->user_model->users_ins_up($post_data);

		if( $result ){
			// JSON 형태로 리턴
			$this->response(['status' => true , 'inparm' => $result , 'message' => '[SYSTEM] '.$result['date'].' | '.$result['id'].' 회원의 정보가 INSERT 되었습니다.'] , 200);
		}else{
			$this->response(['status' => false , 'message' => 'DB INSERT FAIL'] , 404);
		}

		$this->response(['recive' => $post_data] , 200);

	}

	public function users_put()
	{
		//수정
		$post_data_ = file_get_contents('php://input');
		$post_data = json_decode($post_data_ , TRUE);

		$this->val_check($post_data , 'up');

		$result = $this->user_model->users_ins_up($post_data);

		if( $result == "N" ){

			$this->response(['status' => false , 'message' => '등록된 ID 가 존재하지 않습니다.'] , 404);

		}elseif( $result ){

			// JSON 형태로 리턴
			$this->response(['status' => true , 'inparm' => $result , 'message' => '[SYSTEM] '.$result['date'].' | '.$result['id'].' 회원의 정보가 UPDATE 되었습니다.'] , 200);

		}else{

			$this->response(['status' => false , 'message' => 'DB UPDATE FAIL'] , 404);

		}

		$this->response(['recive' => $post_data] , 200);


	}

	public function users_delete()
	{
		//삭제

		$id = $this->get('id');

		if( $id ){
			$result = $this->user_model->users_delete($id);

			if( $result == "N" ){

				$this->response(['status' => false , 'message' => '등록된 ID 가 존재하지 않습니다.'] , 404);

			}elseif( $result ){

				// JSON 형태로 리턴
				$this->response(['status' => true , 'inparm' => $result , 'message' => '[SYSTEM] '.$result['delete_date'].' | '.$result['id'].' 회원의 정보가 DELETE 되었습니다.'] , 200);

			}else{

				$this->response(['status' => false , 'message' => 'DB DELETE FAIL'] , 404);

			}

			$this->response(['recive' => $post_data] , 200);

		}

	}

	public function val_check($post_data , $mode)
	{

		$this->validation->set_data($post_data);

		$vali_arr = array (
			'name' => array ('trim|required|max_length[20]' , '/^[가-힣a-zA-Z]+$/u' , 'Only Hanguel or English') ,
			'nickname' => array ('trim|required|max_length[30]' , '/^[a-z]+$/' , 'Only small letter English') ,
			'password' => array ('trim|required|min_length[10]' , '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/' , 'Include at least one uppercase letter, one lowercase letter, one special character, and one number.') ,
			'hp' => array ('trim|required|numeric|max_length[20]' , '' , 'Only Hanguel or English') ,
			'mail' => array ('trim|required|valid_email|max_length[100]' , '' , 'Only Hanguel or English') ,
			'gender' => array ('trim|max_length[1]' , '/^[F|M]+$/' , 'Gender is only  F or M')
		);

		foreach ( $vali_arr as $key => $val ){
			if( ($mode == "up" && in_array($key , $post_data)) || ($mode == "in") ){
				$this->validation->set_rules($key , '' , $val[0]);
				$error = $this->validation->exec('error');
				if( $error ){
					$this->response(['status' => false , 'field_name' => $key , 'message' => $error['value']] , 404);
					exit;
				}

				if( $val[1] ){
					if( !preg_match($val[1] , $post_data[$key]) ){
						$this->response(['status' => false , 'field_name' => $key , $val[2]] , 404);
						exit;
					}
				}
			}
		}
	}
}

?>