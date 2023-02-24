<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bank_m extends MY_Model {
    protected $_table_name = 'TBLTM';
	protected $_primary_key = 'id';
	protected $_primary_filter = 'intval';
	protected $_order_by = "id desc";
	
	public function __construct() {
		parent::__construct();
	}

	public function insert_tblm($array) {
		$id = parent::insert($array);
		return $id;
	}
    
    public function get_challan($challanNo){
    	$this->db->select('invoice.*,student.name,student.student_id,student.accounts_reg,student.roll');
		$this->db->from('invoice');
		$this->db->join('student', 'student.studentID = invoice.studentID', 'LEFT');
		$this->db->where('invoice.refrence_no', $challanNo);
		$query = $this->db->get();
    	return $query->row();
    }
    
    public function get_challan_record($challanNo){
    	$this->db->select('*');
		$this->db->from('bank_challan_record'); 
		$this->db->where('challan_no', $challanNo);
		$query = $this->db->get();
    	return $query->row();
    }
    
}