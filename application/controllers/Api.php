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
		/* 회원데이터 조회함수 */ // 전체조회 : [도메인]/api/users
		// 특정회원 조회 : [도메인]/api/users/idx/127
		// 페이징형식 조회 : [도메인]/api/users/page/1 (limit default 5)
		// 페이징형식 조회(limit 변경시) : [도메인]/api/users/page/1/limit/2

		$parm = $this->get();

		$page_info = $this->page_info($parm);
		$users = $this->user_model->get_list($parm);

		if( $users ){
			// JSON 형태로 리턴
			$this->response(['status' => true , 'list' => $users , 'page_info' => $page_info] , 200);
		}else{
			$this->response(['status' => false , 'message' => 'No users were found'] , 404);
		}

	}

	public function users_post()
	{
		/* 회원데이터 삽입함수 */ // 형식 : [도메인]/api/users + parameters json data
		// 리턴받은 idx 값을 조회/삭제할때 사용해주세요.

		$post_data_ = file_get_contents('php://input');
		$post_data = json_decode($post_data_ , TRUE);

		$this->val_check($post_data , 'in');

		$users = $this->user_model->get_list(array ('mail' => $post_data['mail']));
		if( $users ){
			$this->response(['status' => false , 'message' => '이미 가입된 회원입니다. 수정을 원하실경우 PUT METHOD 형식으로 보내주세요.'] , 404);
			exit;
		}

		
		if($post_data['recommend']) {
			$status = $this->user_model->recommend_chk($post_data['recommend']);
			if($status['mode'] == "Y") {
				$post_data['recommend'] = $status['recommend_no'];
			} else if($status['mode'] == "N") {
				$this->response(['status' => false , 'message' => '등록하신 추천인이 이미 추천수를 초과하였습니다. 해당 추천인을 추천인으로 등록할 수 없습니다.'] , 404);
				exit;
			} else {
				$this->response(['status' => false , 'message' => '등록하신 추천인이 존재하지 않습니다.'] , 404);
				exit;
			}
		}

	
		$result = $this->user_model->users_ins_up($post_data);

		if( $result ){
			// JSON 형태로 리턴
			$this->response(['status' => true , 'idx' => $result['idx'] , 'message' => '[SYSTEM] '.$result['date'].' | 회원의 정보가 INSERT 되었습니다.'] , 200);
		}else{
			$this->response(['status' => false , 'message' => 'DB INSERT FAIL'] , 404);
		}
		
	}

	public function users_put()
	{
		/* 회원데이터 수정함수 */ // 형식 : [도메인]/api/users + parameters json data
		// 리턴받은 idx 값을 조회/삭제할때 사용해주세요.

		$post_data_ = file_get_contents('php://input');
		$post_data = json_decode($post_data_ , TRUE);
		if( !$post_data['recommend'] ){
			if( !$post_data['mail'] ){
				$this->response(['status' => false , 'message' => 'UPDATE 할 이메일을 보내주세요.'] , 404);
			}else{
				$this->val_check($post_data , 'up');

				$users = $this->user_model->get_list(array ('mail' => $post_data['mail']));
				if( !$users ){
					$this->response(['status' => false , 'message' => '가입되지 않은 회원입니다. 가입을 원하실경우 POST METHOD 형식으로 보내주세요.'] , 404);
					exit;
				}

				$result = $this->user_model->users_ins_up($post_data);

				if( $result ){
					// JSON 형태로 리턴
					$this->response(['status' => true , 'idx' => $result['idx'] , 'message' => '[SYSTEM] '.$result['date'].' | 회원의 정보가 UPDATE 되었습니다.'] , 200);
				}else{
					$this->response(['status' => false , 'message' => 'DB UPDATE FAIL'] , 404);
				}
			}
		}else{
			$this->response(['status' => false , 'message' => 'UPDATE 시에는 추천인 정보를 입력할 수 없습니다.'] , 404);
		}
	}


	public function users_delete()
	{
		/* 회원데이터 삭제함수 */ // 형식 : [도메인]/api/users/idx/1357
		// idx는 생성/수정 시 리턴받은 idx값입니다.

		$idx = $this->get('idx');

		if( $idx ){
			$result = $this->user_model->users_delete($idx);

			if( $result == "N" ){
				$this->response(['status' => false , 'message' => '등록된 회원이 존재하지 않습니다.'] , 404);
			}elseif( $result ){
				// JSON 형태로 리턴
				$this->response(['status' => true , 'inparm' => $result , 'message' => '[SYSTEM] '.$result['delete_date'].' | '.$result['id'].' 회원의 정보가 DELETE 되었습니다.'] , 200);

			}else{
				$this->response(['status' => false , 'message' => 'DB DELETE FAIL'] , 404);
			}
		}else{
			$this->response(['status' => false , 'message' => '생성/수정 시 리턴받은 idx 값을 보내주세요.'] , 404);
		}

	}

	public function val_check($post_data , $mode)
	{
		/* 유효성 처리함수 */

		if( !$post_data ){
			$this->response(['status' => false , 'message' => '생성/수정할 데이터를 JSON 형태로 보내주세요.'] , 404);
			exit;
		}else{
			$this->validation->set_data($post_data);

			$vali_arr = array (
				'name' => array ('trim|required|max_length[20]' , '/^[가-힣a-zA-Z]+$/u' , 'Only Hanguel or English') ,
				'nickname' => array ('trim|required|max_length[30]' , '/^[a-z]+$/' , 'Only small letter English') ,
				'password' => array ('trim|required|min_length[10]' , '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/' , 'Include at least one uppercase letter, one lowercase letter, one special character, and one number.') ,
				'hp' => array ('trim|required|numeric|max_length[20]' , '' , 'Only Hanguel or English') ,
				'mail' => array ('trim|required|valid_email|max_length[100]' , '' , 'Only Hanguel or English') ,
				'gender' => array ('trim|max_length[1]' , '/^[F|M]+$/' , 'Gender is only  F or M') ,
				'recommend' => array ('trim|valid_email|max_length[100]' , '' , 'Only Hanguel or English'));

			foreach ( $vali_arr as $key => $val ){
				if( ($mode == "up" && array_key_exists($key , $post_data)) || ($mode == "in") ){
					$this->validation->set_rules($key , '' , $val[0]);
					$error = $this->validation->exec('error');
					if( $error ){
						$this->response(['status' => false , 'field_name' => $key , 'message' => $error['value']] , 404);
						exit;
					}

					if( $val[1] && $post_data[$key] ){
						if( !preg_match($val[1] , $post_data[$key]) ){
							$this->response(['status' => false , 'field_name' => $key , 'message' => $val[2]] , 404);
							exit;
						}
					}
				}
			}
		}
	}

	public function page_info(&$parm)
	{
		/* 페이징 처리 함수 */
		$result_all = $this->user_model->total_cnt();
		$return['total_cnt'] = $result_all;

		if( $parm['page'] && $parm['page'] > 0 ){
			$b_max = ($parm['limit'])?$parm['limit']:5;
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

			$return['total_page'] = $total_page;
			$return['now_page'] = $page;
			$return['limit'] = $b_max;
		}
		return $return;
	}
}

?>