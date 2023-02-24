<?php
// use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank extends CI_Controller 
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('Student_m');
        $this->load->model("invoice_m");
        $this->load->model("Bank_m");
    }

    public function challan_get() 
    {

        if($_POST){
           
            $username = $this->input->post("username");
            $password = md5($this->input->post("password"));
            $challanNo = $this->input->post("challanNo");
            $campusID = $this->input->post("campusID");
            
            $data = array(
                    'username' => $username,
                    'password' => $password
                );
            $rows = $this->Student_m->get_numrows('student',$data);

            if ($rows == 1) {
                $student_result = $this->Student_m->get_single_username('student',$data);
                $invoice_ary = array(
                        'refrence_no' => $challanNo,
                        'studentID' => $student_result->studentID,
                    );

                if ($this->Student_m->get_numrows('invoice',$invoice_ary) == 1){
                    $challan_result = $this->Bank_m->get_challan($challanNo);
                    
                    $message = array(
                            'CHALLANO' => $challan_result->CHALLANO,
                            'CAMPUSID' => $challan_result->CAMPUSID,
                            'CHALLANDUEDATE' => $challan_result->CHALLANDUEDATE,
                            'CHALLANVALUE' => $challan_result->CHALLANAFTERDUEDATE,
                            'STUDENTID' => $challan_result->studentID,
                            'STUDENTNAME' => $challan_result->name,
                            'roll_no' => $challan_result->roll,
                            'registerNO' => $challan_result->registerNO,
                            'classesID' => $challan_result->classesID,
                            'sectionID' => $challan_result->sectionID,
                            'STUDENTCODE' => $challan_result->student_id,
                            'CATEGORYID' => $challan_result->CATEGORYID,
                            'Code' => 200,
                            'Description' => 'Success'
                        );
                    
                }else{
                     $message = [
                        'code'          => 404,
                        'Description'   => 'ChallanNo is Wrong'
                    ];
                }

            }else{
                 $message = [
                    'code'    => 404,
                    'Description'   => 'Student Not Found'
                ];
            }

            echo json_encode($message);
        }
    }

    public function challan_post() 
    {
        if($_POST){
            $UserID = $this->input->post("UserID");
            $password = md5($this->input->post("password"));
            $rollNo = $this->input->post("rollNo");
            $registerno = $this->input->post("registerno");
            $studentname = $this->input->post("studentname");
            $challanNo = $this->input->post("challanNo");
            $campusId = $this->input->post("campusId");
            $PaidAmount = $this->input->post("PaidAmount");
            $PaidDate = $this->input->post("PaidDate");
          
            $data = array(
                    'studentID' => $UserID,
                    'password' => $password,
                    'roll' => $rollNo,
                    'registerNO' => $registerno,
                    'name' => $studentname
                );
            $rows = $this->Student_m->get_numrows('student',$data);

            if ($rows == 1) {
               $student_result = $this->Student_m->get_single_username('student',$data);
               
               $invoice_ary = array(
                    'refrence_no' => $challanNo,
                    'studentID' => $UserID,
                );

               if ($this->Student_m->get_numrows('invoice',$invoice_ary) == 1) {
                    $data_tbl = array(
                        'CHALLANO' => $challanNo
                        );
                    $tblrows = $this->Student_m->get_numrows('TBLTM',$data_tbl);
                    
                    if ($tblrows == 1) {
                            $message = [
                                    'code'    => 404,
                                    'Description'   => 'ChallanNo already paid'
                                ];
                    }else{
                            $invoice_result = $this->Student_m->get_single_username('invoice',$invoice_ary);
                            $tbltm = array(
                                    'MID' => 0,
                                    'VTYPE' => 'bpv',
                                    'STUDENTID' => $UserID,
                                    'FROMDATE' => $invoice_result->create_date,
                                    'TODATE' => $invoice_result->due_date,
                                    'CAMPUSID' => 0,
                                    'CHALLANO' => $challanNo,
                                    'CHALLANDUEDATE' => $invoice_result->due_date,
                                    'LATEFEEFINE' => 100,
                                    'AREARS' => 0,
                                    'CHALLANVALUE' => $invoice_result->net_fee,
                                    'CHALLANAFTERDUEDATE' => $invoice_result->net_fee + 100,
                                    'PAIDDATE' => date("Y-m-d"),
                                    'STATUS' => 1,
                                    'CREATEDBY' => 'Bank',
                                    'CREATIONDATE' => date("Y-m-d"),
                                    'MODIFIEDBY' => '',
                                    'MODIFICATIONDATE' => '',
                                    'CLASSID' => $student_result->classesID,
                                    'SECTIONID' => $student_result->sectionID,
                                    'CATEGORYID' => 0,
                                    'STUDENTCODE' => $student_result->student_id,
                                    'ADJUSTMENT' => 0,
                                    'ADJUSTMENTREMARKS' => '',
                                    'PAIDAMOUNT' => $invoice_result->net_fee
                                );
                              
                                $res_tblm = $this->Bank_m->insert_tblm($tbltm);
                                $message = [
                                    'code'    => 200,
                                    'Description'   => 'Success'
                                ];
                    }
                    
               }else{
                    $message = [
                        'code'    => 404,
                        'Description'   => 'ChallanNo is Wrong'
                    ];
               }

            }else{
                 $message = [
                        'code'    => 404,
                        'Description'   => 'Student Not Found'
                    ];
            }
        
            echo json_encode($message);
        }

       
    }   
}
