<?php

class User_model extends CI_Model
{

	public function __construct()
	{
		// Call the CI_Model constructor
		parent::__construct();

		$this->load->database();

	}

	public function total_cnt()
	{

		$qry = "select count(*) as cnt from users";

		$querys = $this->db->query($qry);
		$total_cnt = $querys->row_array();

		return $total_cnt['cnt'];

	}

	public function get_list($parm)
	{

		$have_id = ($parm['id'])?"and id = '".$parm['id']."'":"";
		$qry = "select * from users where 1=1 ".$have_id;

		if( $parm['max'] ){
			if( !$parm['skip'] )
				$parm['skip'] = 0;
			$qry .= "limit ".$parm['skip'].",".$parm['max'];
		}

		$querys = $this->db->query($qry);
		$users = $querys->result_array();

		return $users;

	}

	public function users_ins_up($parm)
	{

		// 현재시간
		$now_date = date('Y-m-d H:i:s');

		$users_filed = array ("name" , "nickname" , "password" , "hp" , "mail" , "gender" , "recommend");

		foreach ( $parm as $key => $val ){
			if( in_array($key , $users_filed) ){
				$set_parm[] = $key." = '".$val."'";
			}
		}

		$set_parm[] = (empty($parm['id']))?"insert_date = '".$now_date."'":"update_date = '".$now_date."'";

		if( empty($parm['id']) ){

			/*
			ID 필드는 과제에 명시되어 있지 않으나 필요하다고 판단하여 추가하되
			uniqid 함수를 통하여 랜덤값으로 생성 후 DB 에 insert 함
			*/

			echo "123S";
			exit;
			$id = uniqid();
			$set_parm[] = "id = '".$id."'";

			$qry = "insert into users set ".implode(" , " , $set_parm);
			$res = $this->db->query($qry);

		}else{

			if( count($set_parm) > 0 ){
				$id = $parm['id'];

				$search_qry_ = "select * from users where id= '".$id."'";
				$search_qry = $this->db->query($search_qry_);

				$user = $search_qry->row_array();

				if( !empty($user['no']) ){

					$qry = "UPDATE users SET ".implode(" , " , $set_parm)." where no = '".$user['no']."'";
					$res = $this->db->query($qry);

				}else{
					$result = "N";
					return $result;
				}
			}
		}

		if( $res ){
			return $result = array ("date" => $now_date , "id" => $id);
		}

	}

	public function users_delete($id)
	{

		// 현재시간
		$now_date = date('Y-m-d H:m:s');

		$search_qry_ = "select * from users where id= '".$id."'";
		$search_qry = $this->db->query($search_qry_);
		$user = $search_qry->row_array();

		if( !empty($user['no']) ){

			$qry = "DELETE FROM users WHERE no = '".$user['no']."'";
			$res = $this->db->query($qry);

			if( $res ){
				$result = array ("delete_date" => $now_date , "id" => $id);
			}

		}else{

			$result = "N";
		}

		return $result;

	}
}

?>