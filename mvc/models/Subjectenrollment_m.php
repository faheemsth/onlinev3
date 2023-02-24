<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class subjectenrollment_m extends MY_Model {

    protected $_table_name = 'subjectenrollment';
    protected $_primary_key = 'subjectenrollmentID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "subjectenrollmentID asc";

    function __construct() {
        parent::__construct();
    }

    function get_subjectenrollment($array=NULL, $signal=FALSE) {
        $query = parent::get($array, $signal);
        return $query;
    }

    public function get_subjectenrollment_join_student_by_array($array=NULL){
        error_reporting(0);
        $this->db->select('*');
        $this->db->from('subjectenrollment');
        $this->db->join('student', 'student.studentID = subjectenrollment.studentID', 'LEFT'); 
        if ($array!=NULL) {
            $this->db->where($array);
        }
        $this->db->order_by("subjectenrollmentID", "DESC");
        //$this->db->limit(2000);
        $query = $this->db->get();
        if($query == 1) {
               $results =   $query->result();
               return       $results;
            } else{
                 return array();
            }
    }

    function get_single_subjectenrollment($array) {
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_subjectenrollment($array=NULL) {
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_subjectenrollment($array) {
        $id = parent::insert($array);
        return $id;
    }

    function update_subjectenrollment($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_subjectenrollment($id){
        parent::delete($id);
    }
    

    public function insert_batch_subjectenrollment($array) {
        $id = parent::insert_batch($array);
        return $id;
    }

    public function update_subjectenrollment_by_array($data, $array = NULL) {
        $this->db->set($data);
        $this->db->where($array);
        $this->db->update($this->_table_name);
    }
}
