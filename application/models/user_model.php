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

		$where_q  = ($parm['idx']) ? " and no = '".$parm['idx']."'":"";
		$where_q  .= ($parm['mail']) ? " and mail = '".$parm['mail']."'":"";

		$qry = "select * from users where 1=1 ".$where_q;

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

		$now_date = date('Y-m-d H:i:s');

		$db_field = array ("name" , "nickname" , "password" , "hp" , "mail" , "gender" , "recommend", "insert_date");
		
		for($i=0; $i<count($db_field); $i++) {
			$key = $db_field[$i];

			$field[] = $key;
			if($key == "insert_date") {
				$value_f[] = "'".$now_date."'";
			} else {
				if($key == "password") {
					$parm[$key] = $hash = password_hash($parm[$key], PASSWORD_BCRYPT);
				}
				$value_f[] = "'".$parm[$key]."'";
			}

			if(array_key_exists($key, $parm)){
				$set_parm[] = $key." = values(".$key.")";
			}
		}
		
		
		$qry = "insert into users (".implode(",", $field).") ";
		$qry .= "values (".implode(",", $value_f).")";
		$qry .= " on duplicate key update ".implode(",", $set_parm).", update_date=now()";
		$res = $this->db->query($qry);
		$idx = $this->db->insert_id();
	
		if( $res ){
			return $result = array ("date" => $now_date , "idx" => $idx);
		}

	}

	public function users_delete($idx)
	{

		// 현재시간
		$now_date = date('Y-m-d H:m:s');

		$search_qry_ = "select * from users where no= '".$idx."'";
		$search_qry = $this->db->query($search_qry_);
		$user = $search_qry->row_array();

		if( !empty($user['no']) ){

			$qry = "DELETE FROM users WHERE no = '".$user['no']."'";
			$res = $this->db->query($qry);

			if( $res ){
				$result = array ("delete_date" => $now_date , "idx" => $idx);
			}

		}else{

			$result = "N";
		}

		return $result;

	}

	public function recommend_chk($mail)
	{
		//추천인 체크
		$status = array ("recommend_no" => "" , "mode" => "Y");;

		$search_qry_ = "select no from users where mail = '".$mail."'";
		$search_qry = $this->db->query($search_qry_);
		$result = $search_qry->row_array();

		$status['recommend_no'] = $result['no'];

		if($result['no']){
			$search_qry_ = "select count(*) cnt from users where recommend = '".$result['no']."'";
			$search_qry = $this->db->query($search_qry_);
			$user = $search_qry->row_array();

			if($user['cnt']>=5){
				$status['mode'] = "N";
			}
		}else{
			$status['mode'] = "X";
		}

		return $status;

	}
}

?>