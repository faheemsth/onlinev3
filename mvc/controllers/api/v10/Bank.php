<?php
// use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank extends CI_Controller 
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model("invoice_m");
        $this->load->model("Bank_m");
        $this->load->model("user_m");
        $this->load->model("setting_m"); 
        $this->load->model("maininvoice_m"); 
        $this->load->model("globalpayment_m"); 
        $this->load->model("payment_m"); 
        $this->load->model("feetypes_m"); 
        $this->load->model("studentrelation_m"); 
        $this->load->model("globalpayment_m"); 
    }
    
    public function challan_get() 
    {

        if($_POST){
           
            $username   = $this->input->post("username");
            $password   = $this->input->post("password");
            $challanNo  = $this->input->post("challanNo");
            $campusID   = $this->input->post("campusID");
            $error  =   0;

            if ($username=='') {
                $error++;
                $returnmsg  =   'Username is required';
            }
            
            if ($password=='') {
                $error++;
                $returnmsg  =   'Password is required';
            }
            
            if ($challanNo=='') {
                $error++;
                $returnmsg  =   'Challan number is required';
            }
            
            if ($campusID=='') {
                $error++;
                $returnmsg  =   'CampusID is required';
            }
            if ($campusID!=1) {
                $error++;
                $returnmsg  =   'Campus ID is not valid';
            }

            if ($error>0) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => $returnmsg
                ];
                echo json_encode($message);
                exit();
            } 

            $row = $this->user_m->get_user_table('user',$username,$password);
            

              

            if ($row==NULL) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => 'Invalid Username/Password'
                ];
                echo json_encode($message);
                exit();
            } 

            if ($row->active==0) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => 'User Blocked Please Contact Admin'
                ];
                echo json_encode($message);
                exit();
            } 


          

             $challan_result = $this->Bank_m->get_challan($challanNo);

             
            if ($challan_result != NULL) {

                if ($challan_result->paidstatus==2) {
                      $message = [
                    'code'    => 404,
                    'Description'   => 'Challan is already paid'
                ];
                echo json_encode($message);
                exit();
                 } 
                $now        = time(); // or your date as well
                $fine       =   0;
                $your_date  = strtotime($challan_result->due_date);
                $datediff   = $now - $your_date;

                $ndays      = round($datediff / (60 * 60 * 24))-1;
                $setting    = $this->setting_m->get_setting();

                
                 
                if ($ndays>0) {
                    if ($setting->fine_type==2) {
                        $fine =  $ndays*$setting->latepayment_fine_amount;
                    }else{
                        $fine =  $setting->latepayment_fine_amount;
                    }
                }


                
                 
               
                   
                    
                    $message = array(
                            'CHALLANO'              => $challan_result->refrence_no,
                            'CAMPUSID'              => 1,
                            'CHALLANDUEDATE'        => date('d-m-Y',strtotime($challan_result->due_date)),
                            'CHALLANVALUE'          => $challan_result->net_fee,
                            'CHALLANAFTERDUEDATE'   => ($challan_result->net_fee+$fine),
                            'STUDENTID'             => $challan_result->studentID,
                            'STUDENTNAME'           => $challan_result->name, 
                            'CLASSID'               => $challan_result->classesID,
                            'SECTIONID'             => $challan_result->sectionID,
                            'STUDENTCODE'           => $challan_result->accounts_reg,
                            'CATEGORYID'            => 1,
                            'Code'                  => 200,
                            'Description'           => 'Success'
                        );
                
                        $upload_cpv_bpv_array  = array(
                                'cpv_bpv_recordID'  => 1,
                                'pay_type'          => 'bank',
                                'payment_type'      => 'bpv',
                                'payment_date'      => date('Y-m-d'),  
                                'payment_date_status'=> 1,  
                                'student_id'        => $challan_result->studentID, 
                                'student_id_status' => 1, 
                                'student_roll'      => $challan_result->roll, 
                                'student_roll_status'=> 1,
                                'fine_amount'       => $fine, 
                                'invoice_amount'    => ($challan_result->net_fee+$fine), 
                                'maininvoiceID'     => $challan_result->maininvoiceID, 
                                'amount_status'     => 1, 
                                'challan_no'        => $challan_result->refrence_no, 
                                'challan_no_status' => 1, 
                                'status'            => 1, 
                                'UserID'            => $row->userID, 
                                         );
                        $this->db->insert('bank_challan_record',$upload_cpv_bpv_array);

            }else{
                 $message = [
                    'code'    => 404,
                    'Description'   => 'Challan Number is not valid'
                ];
            }

            echo json_encode($message);
        }
    }

    public function challan_post() 
    {
        if($_POST){


            $username   = $this->input->post("username");
            $password   = $this->input->post("password");
            $challanNo  = $this->input->post("challanNo");
            $campusID   = $this->input->post("campusID");
            $PaidAmount = $this->input->post("PaidAmount");
            $PaidDate   = $this->input->post("PaidDate");
            $error      = 0;

            if ($username=='') {
                $error++;
                $returnmsg  =   'Username is required';
            }
            
            if ($password=='') {
                $error++;
                $returnmsg  =   'Password is required';
            }
            
            if ($challanNo=='') {
                $error++;
                $returnmsg  =   'Challan number is required';
            }
            
            if ($campusID=='') {
                $error++;
                $returnmsg  =   'CampusID is required';
            }
            
            if ($PaidAmount=='') {
                $error++;
                $returnmsg  =   'Paid Amount is required';
            }
            
            if ($PaidDate=='') {
                $error++;
                $returnmsg  =   'Paid Date is required';
            }
            
            if (date('Y-m-d',strtotime($PaidDate))!=date('Y-m-d')) {
                $error++;
                $returnmsg  =   'Date is not valid';
            }
            
            if ($campusID!=1) {
                $error++;
                $returnmsg  =   'Campus ID not valid';
            }

          

            if ($error>0) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => $returnmsg
                ];
                echo json_encode($message);
                exit();
            } 

            
            $row_user = $this->user_m->get_user_table('user',$username,$password);
            
            $this->data['siteinfos'] = $this->setting_m->get_setting();
              

            if ($row_user==NULL) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => 'Invalid Username/Password'
                ];
                echo json_encode($message);
                exit();
            } 

            if ($row_user->active==0) {
             
                 $message = [
                    'code'    => 404,
                    'Description'   => 'User Blocked Please Contact Admin'
                ];
                echo json_encode($message);
                exit();
            } 


          

             $challan_result = $this->Bank_m->get_challan_record($challanNo);
          
             
             
            if ($challan_result != NULL) {

                if ($challan_result->post_status==1) {
                      $message = [
                    'code'    => 404,
                    'Description'   => 'Challan is already paid'
                ];
                echo json_encode($message);
                exit();
                 } 

                if ($challan_result->invoice_amount!=$PaidAmount) {
                      $message = [
                    'code'    => 404,
                    'Description'   => 'Challan Amount not match'
                ];
                echo json_encode($message);
                exit();
                 }   
                

            $data = $this->invoice_m->get_reconcile_data_bank($challan_result->id);

            //error_reporting(E_ALL);

            $msg     = "";
            $journal_items_ar       =   [];
            $student_ledgers_ar     =   [];

            foreach ($data as $row){
               
                $id             = $row->id;
                $student_id     = $row->student_id;
                $student_roll   = $row->student_roll;
                $payment_date   = $row->payment_date;
                $challan_no     = $row->challan_no;
                $amount         = $row->invoice_amount-$row->fine_amount;
                $paymenttype    = $row->pay_type;
                $pay_type       = $row->payment_type;
                $student        = $this->student_m->get_student_select('studentID, student_id, classesID, sectionID, registerNO, name', ['studentID' => $student_id, 'roll' => $student_roll]);
                 
 
                if (customCompute($student)){
                    $maininvoice_result = $this->maininvoice_m->get_maininvoice_with_cpv($student->studentID,'','',$challan_no);
                    $excel_amount = $amount;
                    $r_amount = 0;
                    $p_amount = $excel_amount;
                    $date = $payment_date;
                    if(customCompute($maininvoice_result)){
                        foreach ($maininvoice_result as $invoice){
                            if (($p_amount == ceil($invoice->maininvoicenet_fee)) && $invoice->maininvoicestatus != 2) {
                      
                                $p_amount = $p_amount - ceil($invoice->maininvoicenet_fee);
                                
                               
                                
                                $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $invoice->maininvoiceID, 'deleted_at' => 1));

                                //var_dump($this->data['invoices']);
                                
                                $globalpayment = array(
                                            'classesID'             => $student->classesID,
                                            'sectionID'             => $student->sectionID,
                                            'studentID'             => $student->studentID,
                                            'clearancetype'         => 'partial',
                                            'invoicename'           => $student->registerNO .'-'. $student->name,
                                            'invoicedescription'    => '',
                                            'paymentyear'           => date("Y", strtotime($date)),
                                            'schoolyearID'          => 1,
                                        );
                                $this->globalpayment_m->insert_globalpayment($globalpayment);
                                $globalLastID = $this->db->insert_id();
                                $paymentArray = array(
                                                'invoiceID'         => $this->data['invoices'][0]->invoiceID,
                                                'schoolyearID'      => 1,
                                                'studentID'         => $student->studentID,
                                                'paymentamount'     => ceil($invoice->maininvoicenet_fee),
                                                'paymenttype'       => $paymenttype,
                                                'paymentdate'       => $date,
                                                'paymentday'        => date("d", strtotime($date)),
                                                'paymentmonth'      => date("m", strtotime($date)),
                                                'paymentyear'       => date("Y", strtotime($date)),
                                                'userID'            => $row_user->userID,
                                                'usertypeID'        => $row_user->usertypeID,
                                                'uname'             => $row_user->name,
                                                'transactionID'     => 'CASHANDCHEQUE'.random19(),
                                                'globalpaymentID'   => $globalLastID,
                                            );
                               
                                $this->payment_m->insert_payment($paymentArray);
                                $paymentLastID = $this->db->insert_id();

                                 if ($invoice->maininvoicestatus == 1) {
                                    $main_net = $invoice->maininvoicetotal_fee - $invoice->maininvoice_discount;
                                    $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 2, 'maininvoicecreate_date' => $date, 'maininvoicenet_fee' => $main_net],$invoice->maininvoiceID);
                                }else{
                                     $main_net = $invoice->maininvoicenet_fee;
                                    $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 2, 'maininvoicecreate_date' => $date],$invoice->maininvoiceID);
                                }


                $journal_entries_id   =     $this->invoice_m->journal_entries_id(array('studentID'=>$paymentArray['studentID']));

                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'account'       =>  $this->data['siteinfos']->student_cr_account, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'feetypeID'     =>  $this->data['invoices'][0]->feetypeID, 
                            'debit'         =>  0, 
                            'credit'        =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'CR', 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'feetypeID'     =>  $this->data['invoices'][0]->feetypeID,
                            'account'       =>  $row_user->phone, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'         =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'DR', 
                            'credit'        =>  0, 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment', 
                            'feetypeID'                =>  $this->data['invoices'][0]->feetypeID, 
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'CR', 
                            'account'                  =>  $this->data['siteinfos']->student_cr_account, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  0, 
                            'credit'                   =>  $paymentArray['paymentamount'], 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment',
                            'feetypeID'                =>  $this->data['invoices'][0]->feetypeID,  
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'DR', 
                            'account'                  =>  $row_user->phone, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  $paymentArray['paymentamount'], 
                            'credit'                   =>  0, 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                 

                               
                  
                                $paidstatus = 2;
                                $this->invoice_m->update_invoice(array('paidstatus' => $paidstatus,'pay_type' => $pay_type,'create_date' => $date), $this->data['invoices'][0]->invoiceID);
                                $this->globalpayment_m->update_globalpayment(array('clearancetype' => 'paid'), $globalLastID);
                                $this->invoice_m->update_post_status_by_id($id);

                            }else if ($p_amount < ceil($invoice->maininvoicenet_fee) && $p_amount > 0 && $invoice->maininvoicestatus != 2) {
                                
                                $this->maininvoice_m->update_maininvoice(['maininvoicenet_fee' => $invoice->maininvoicenet_fee - $p_amount],$invoice->maininvoiceID);
                                 $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 1, 'maininvoicecreate_date' => $date], $invoice->maininvoiceID);
                                 $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $invoice->maininvoiceID, 'deleted_at' => 1));
                                
                                $globalpayment = array(
                                            'classesID'             => $student->classesID,
                                            'sectionID'             => $student->sectionID,
                                            'studentID'             => $student->studentID,
                                            'clearancetype'         => 'partial',
                                            'invoicename'           => $student->registerNO .'-'. $student->name,
                                            'invoicedescription'    => '',
                                            'paymentyear'           => date("Y", strtotime($date)),
                                            'schoolyearID'          => 1,
                                        );
                                $this->globalpayment_m->insert_globalpayment($globalpayment);
                                $globalLastID = $this->db->insert_id();
                                $paymentArray = array(
                                                'invoiceID'         => $this->data['invoices'][0]->invoiceID,
                                                'schoolyearID'      => 1,
                                                'studentID'         => $student->studentID,
                                                'paymentamount'     => $p_amount,
                                                'paymenttype'       => $paymenttype,
                                                'paymentdate'       => $date,
                                               'paymentday'         => date("d", strtotime($date)),
                                                'paymentmonth'      => date("m", strtotime($date)),
                                                'paymentyear'       => date("Y", strtotime($date)),
                                                'userID'            => $row_user->userID,
                                                'usertypeID'        => $row_user->usertypeID,
                                                'uname'             => $row_user->name,
                                                'transactionID'     => 'CASHANDCHEQUE'.random19(),
                                                'globalpaymentID'   => $globalLastID,
                                            );
                                $this->payment_m->insert_payment($paymentArray);
                                $paymentLastID = $this->db->insert_id();

                                $journal_entries_id   =     $this->invoice_m->journal_entries_id(array('studentID'=>$paymentArray['studentID']));

                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'account'       =>  $this->data['siteinfos']->student_cr_account, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'feetypeID'     =>  99999, 
                            'debit'         =>  0, 
                            'credit'        =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'CR', 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'feetypeID'     =>  99999,
                            'account'       =>  $row->phone, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'         =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'DR', 
                            'credit'        =>  0, 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment', 
                            'feetypeID'                =>  99999, 
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'CR', 
                            'account'                  =>  $this->data['siteinfos']->student_cr_account, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  0, 
                            'credit'                   =>  $paymentArray['paymentamount'], 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment',
                            'feetypeID'                =>  99999,  
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'DR', 
                            'account'                  =>  $row->phone, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  $paymentArray['paymentamount'], 
                            'credit'                   =>  0, 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                 


                                $p_amount = $p_amount - ceil($invoice->maininvoicenet_fee);
                                $paidstatus = 1;
                                $this->invoice_m->update_invoice(array('paidstatus' => $paidstatus,'pay_type' => $pay_type,'create_date' => $date), $this->data['invoices'][0]->invoiceID);
                                $this->globalpayment_m->update_globalpayment(array('clearancetype' => 'partial'), $globalLastID);
                                $this->invoice_m->update_post_status_by_id($id);
                            }
                        }
                    }

                    
                }
            } 

            $this->invoice_m->push_invoice_to_journal_items( $journal_items_ar );
            $this->invoice_m->push_invoice_to_studnet_ledgers( $student_ledgers_ar );

            if ($row->fine_amount>0) {
               $this->add_fine($row->fine_amount,$student->studentID,$student->classesID,$student->sectionID,$row_user,$this->input->post("challanNo"));
            }
             
                
             $message = [
                    'code'    => 200,
                    'Description'   => 'Challan Posted'
                ];            

            }else{
                 $message = [
                    'code'    => 404,
                    'Description'   => 'Challan Number is not valid'
                ];
            }
        
            echo json_encode($message);
        }

       
    }
    public function add_fine($amount_fine,$student_idd,$classid,$sectionid,$row_user,$ref_challan)
       {
                        $this->data['siteinfos']    = $this->setting_m->get_setting();
                        $invoiceMainArray   = [];
                        $invoiceArray       = [];
                        $paymentArray       = [];
                        $studentArray       = [];
                        $reg_no             = [];
                        $ref_array          = [];

                        $schoolyearID       = 1;
                        $feess_type         = $this->data['siteinfos']->latepayment_fine_ID;
                        $get_feetype        = $this->feetypes_m->get_feetypes($feess_type);
                        $feetypeIDs         = $get_feetype->feetypesID;
                        $feetype            = $get_feetype->feetypes;
                        $to_date            = date('Y-m-d');
                        $studentID          = $student_idd;
                        $classesID          = $classid;
                        $sectionID          = $sectionid;
                        $invoice_type_v     = 'other_charges';
                        $last_date          = NUll;
                        // $ref_no = $this->invoice_m->get_invoice_ref_by_date(
                        //     $this->input->post("date")
                        // );
                        // $ref_array[date('ym',strtotime($this->input->post("date")))] = $ref_no;

                        


                        if (((int) $studentID || $studentID == 0) &&
                            ((int) $classesID || $classesID == 0)) {
                            if ($studentID != 0) {
                                $studentArrays = [
                                                    "srstudentID"       => $studentID,
                                                    "srclassesID"       => $classesID,
                                                    "srschoolyearID"    => $schoolyearID,
                                                    "active"            => [0, 1],
                                                    ];
                        if ((int) $sectionID) { $studentArrays["srsectionID"] = $sectionID; }
                            if ($this->data['siteinfos']->enrolment_feetype==$feess_type) {
                                $studentArrays['checkdate']     =   $last_date;
                                $getstudents = $this->studentrelation_m->get_order_by_student_enrollment($studentArrays);
                            }else{
                                $getstudents = $this->studentrelation_m->get_order_by_student_api($studentArrays);
                            }
                               // $getstudents = $this->studentrelation_m->get_order_by_student($studentArrays);
                            } else {
                                $studentArrays = [
                                                    "srschoolyearID" => $schoolyearID,
                                                    "active" => [0, 1],
                                                  ];

                                if ((int) $classesID) { $studentArrays["srclassesID"] = $classesID; }
                                if ((int) $sectionID) { $studentArrays["srsectionID"] = $sectionID; }
                                if ($invoice_type_v == "hostel_fee") {  $studentArrays["hostel"] = 1;  }
                                if ($invoice_type_v == "Transport_fee") {  $studentArrays["transport"] = 1; }

                                    //$getstudents = $this->studentrelation_m->get_order_by_student($studentArrays);
                                   if ($this->data['siteinfos']->enrolment_feetype==$feess_type) {
                                        $studentArrays['checkdate']     =   $last_date;
                                        $getstudents = $this->studentrelation_m->get_order_by_student_enrollment($studentArrays);
                                         
                                         
                                    }else{
                                        $getstudents = $this->studentrelation_m->get_order_by_student_api($studentArrays);
                                    }
                                  }

 

                               

                            if (customCompute($getstudents)) {
                                    $paymentStatus      = 0;
                                    $paymentStatus      = $this->input->post("statusID");
                                    $payment_type       = 2;
                                    $clearancetype      = "unpaid";

                                    $mainlastID         = $this->maininvoice_m->get_maininvoice_last_id();



                                foreach ($getstudents as $key => $getstudent) {
                                    if ($payment_type == 1 and $feetypeIDs==17) {
                                        $instcounter = $getstudent->no_installment;
                                    } else {
                                        $instcounter = 1;
                                    }
                                    $data_counter = 0;
                                    for ($y = 1; $y <= $instcounter; $y++) {
                                        if ($data_counter == 0) {
                                            $from_date  = date("Y-m-d");
                                            $due_date   = date("Y-m-d");

                                        if (!isset($ref_array[date('ym',strtotime($from_date))])) {
                                           
                                            $ref_no    = $this->invoice_m->get_invoice_ref_by_date($from_date);
                                            $ref_array[date('ym',strtotime($from_date))] = $ref_no;
                                                                      
                                                    }
                                        } else {
                                            $from_date  = strtotime(date("Y-m-d"));
                                            $due_date   = strtotime(date("Y-m-d"));
                                            $month      = "+" . $data_counter . " month";
                                            $from_date  = date("Y-m-d",strtotime($month, $from_date));
                                            $due_date   = date("Y-m-d",strtotime($month, $due_date));
                                                if (!isset($ref_array[date('ym',strtotime($from_date))])) {
                                                    $ref_no = $this->invoice_m->get_invoice_ref_by_date($from_date);
                                                    $ref_array[date('ym',strtotime($from_date))] = $ref_no;
                                                                              
                                                  }
                                        }
                                           

                             
                             

                            if ($invoice_type_v == "other_charges") {
                               
                                $discount   = 0; 
                                $total_fee  = $amount_fine;
                                $net_fee    = $amount_fine;
                                $breakup_info_ar      =   [];
                                $breakup_info_ar[]    = array(
                                                                'feetypeID'         => $get_feetype->feetypesID, 
                                                                'feetypes'          => $get_feetype->feetypes, 
                                                                'fee_discount'      => $discount, 
                                                                'fee_amount'        => $total_fee, 
                                                                'fee_discount_a'    => $discount, 
                                                                'net_amount'        => $net_fee, 
                                                                'discount_left'     => 0,  
                                                                'feetypedetail'     => $get_feetype, 
                                                                'S_DIS'             => $discount, 
                                                    );
                                  
                                    $fee_breakup    =   serialize($breakup_info_ar);
                            }

                           
                            if ($net_fee) {
                                $mainlastID++;
                                $singleinvoiceMainArray = [
                                                            "maininvoiceID"         => $mainlastID,
                                                            "maininvoiceschoolyearID"   => $schoolyearID,
                                                            "maininvoiceclassesID"  => $getstudent->srclassesID,
                                                            "maininvoicestudentID"  => $getstudent->srstudentID,
                                                            "maininvoicesectionID"  => $getstudent->srsectionID,
                                                            "maininvoicestatus"     => 0,
                                                            "maininvoiceuserID"     => $row_user->userID,
                                                            "maininvoiceusertypeID" => $row_user->usertypeID,
                                                            "maininvoiceuname"      => $row_user->name,
                                                            "maininvoicedate"       => $from_date,
                                                            "maininvoicedue_date"   => $due_date,
                                                            "fee_breakup"           => $fee_breakup,
                                                            "maininvoicecreate_date"=> date("Y-m-d"),
                                                            "maininvoicestd_srid"   =>$getstudent->srsection,
                                                            "maininvoiceday"        => date("d"),
                                                            "maininvoicemonth"      => date("m"),
                                                            "maininvoiceyear"       => date("Y"),
                                                            "maininvoice_type_v"    => $invoice_type_v,
                                                            "maininvoicedeleted_at" => 1,
                                                ];

                                $singleinvoiceMainArray["maininvoicetotal_fee"] = ceil($total_fee);
                                $singleinvoiceMainArray["maininvoice_discount"] = ceil($discount);
                                $singleinvoiceMainArray["maininvoicenet_fee"]   = ceil($net_fee); 

                                $invoiceMainArray[] = $singleinvoiceMainArray;
                                $data_counter++;
                                        } // net fee
                                    } // for increment

                                $studentArray[] = $getstudent->srstudentID;
                                $reg_no[$getstudent->srstudentID] = $getstudent->accounts_reg;
                                }

                                if (customCompute($invoiceMainArray)) {
                                        $count = customCompute($invoiceMainArray);

                                        $firstID = $this->maininvoice_m->insert_batch_maininvoice($invoiceMainArray);

                                        $lastID = $firstID + ($count - 1);
                                                                         

                                        if ($lastID >= $firstID) {
                                            $j = 0;
                                            for ($i = $firstID;$i <= $lastID; $i++) {
                                                $current_y = date("Y");
                                                $current_m = date("m");
                                            if ($invoiceMainArray[$j]["maininvoicestd_srid"] < 10) {
                                                    $maininvoicestd_srid ="0" .
                                                    $invoiceMainArray[$j]["maininvoicestd_srid"];
                                            } else {
                                                    $maininvoicestd_srid =    $invoiceMainArray[$j]["maininvoicestd_srid"];
                                            }

                                                if (!isset($ref_array[date('ym',strtotime($ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))]))])) {
                                                # code...
                                           
                                             $ref_no = $this->invoice_m->get_invoice_ref_by_date($ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))]);
                                           
                                            $ref_array[date('ym',strtotime( $ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))]))] = $ref_no;
                                                                          
                                                        }

                                              if (!isset($ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))])) {
                                                    $ref_no = $this->invoice_m->get_invoice_ref_by_date($invoiceMainArray[$j]["maininvoicedate"]);
                                                    $ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))] = $ref_no;
                                                                              
                                                  }
                                             

                                        $invoiceArray[] = [
                                            "schoolyearID"  =>  $invoiceMainArray[$j]["maininvoiceschoolyearID"],
                                            "classesID"     =>  $invoiceMainArray[$j]["maininvoiceclassesID"],
                                            "type_v"        =>  $invoice_type_v,
                                            "studentID"     =>  $invoiceMainArray[$j]["maininvoicestudentID"],
                                            "sectionID"     =>  $invoiceMainArray[$j]["maininvoicesectionID"],
                                            "fee_breakup"   =>  $invoiceMainArray[$j]["fee_breakup"],
                                            "feetypeID"     =>  $feetypeIDs,
                                            "feetype"       =>  $feetype,
                                            "refrence_no"   =>  $ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))],
                                            "amount"        => isset($invoiceMainArray[$j]["maininvoicetotal_fee"])? $invoiceMainArray[$j]["maininvoicetotal_fee"]: 0,
                                            "discount"      =>  isset($invoiceMainArray[$j]["maininvoice_discount"])?$invoiceMainArray[$j]["maininvoice_discount"]: 0,
                                            "net_fee"       =>  isset($invoiceMainArray[$j]["maininvoicenet_fee"])? $invoiceMainArray[$j]["maininvoicenet_fee"]: 0,
                                            "paidstatus"    =>  NULL,
                                            "userID"        =>  $invoiceMainArray[$j]["maininvoiceuserID"],
                                            "usertypeID"    =>  $invoiceMainArray[$j]["maininvoiceusertypeID"],
                                            "uname"         =>  $invoiceMainArray[$j]["maininvoiceuname"],
                                            "date"          =>  $invoiceMainArray[$j]["maininvoicedate"],
                                            "create_date"   =>  $invoiceMainArray[$j]["maininvoicecreate_date"],
                                            "due_date"      =>  $invoiceMainArray[$j]["maininvoicedue_date"],
                                            "day"           =>  $invoiceMainArray[$j]["maininvoiceday"],
                                            "month"         =>  $invoiceMainArray[$j]["maininvoicemonth"],
                                            "year"          =>  $invoiceMainArray[$j]["maininvoiceyear"],
                                            "deleted_at"    =>  $invoiceMainArray[$j]["maininvoicedeleted_at"],
                                            "maininvoiceID" =>  $invoiceMainArray[$j]["maininvoiceID"],
                                                        ];

                                            $net_feess = isset($invoiceMainArray[$j][ "maininvoicenet_fee" ]) ? $invoiceMainArray[$j]["maininvoicenet_fee"  ] : 0;
                                            $ref_array[date('ym',strtotime($invoiceMainArray[$j]["maininvoicedate"]))]++;

                                            if ($invoice_type_v=='invoice') {

                                            $all_fee_breakups   =    unserialize($invoiceMainArray[$j][ "fee_breakup" ]);
                                                                
                                            foreach($all_fee_breakups  as $new_fb){
                                                    $getfeetype        =   $new_fb['feetypedetail'];
                                                                    
                                                    $ledger_array[] = [
                                                    "maininvoiceID" => $invoiceMainArray[$j][ "maininvoiceID" ],
                                                    "studentID"     => $invoiceMainArray[$j][ "maininvoicestudentID" ],
                                                    "classesID"     => $invoiceMainArray[$j][ "maininvoiceclassesID" ],
                                                    "net_fee"       => $new_fb['net_amount'],
                                                    "refrence_no"   => $ref_array[date('ym',strtotime($invoiceMainArray[$j][ "maininvoicedate" ]))],    
                                                    'description'   =>  'Late Payment Fine Charged For the Invoice Number '.$ref_challan.',by BAH WEB API.',
                                                    "accounts_reg"  => $reg_no[$invoiceMainArray[$j][ "maininvoicestudentID" ] ], 
                                                    "account_credit"=> $getfeetype->credit_id, 
                                                    "account_debit" => $getfeetype->debit_id, 
                                                    "feetypeID"     => $getfeetype->feetypesID,
                                                    "date"          => $invoiceMainArray[$j]["maininvoicedate"], 
                                                    "discount"      => $invoiceMainArray[$j]["maininvoice_discount"], ];

                                                                }
                                                            
                                                        }else{ 

                                                $ledger_array[] = [
                                                    "maininvoiceID" => $invoiceMainArray[$j][ "maininvoiceID" ],
                                                    "studentID"     => $invoiceMainArray[$j][ "maininvoicestudentID" ],
                                                    "classesID"     => $invoiceMainArray[$j][ "maininvoiceclassesID" ],
                                                    "net_fee"       => isset( $invoiceMainArray[$j][ "maininvoicenet_fee" ] ) ? $invoiceMainArray[$j][ "maininvoicenet_fee" ] : 0,
                                                    "refrence_no"   => $ref_array[date('ym',strtotime($invoiceMainArray[$j][ "maininvoicedate" ]))],    
                                                    'description'   =>  'Late Payment Fine Charged For the Invoice Number '.$ref_challan.',by BAH WEB API.',
                                                    "accounts_reg"  => $reg_no[ $invoiceMainArray[$j][ "maininvoicestudentID" ] ], 
                                                    "account_credit"=> $get_feetype->credit_id, 
                                                    "account_debit" => $get_feetype->debit_id, 
                                                    "feetypeID"     => $get_feetype->feetypesID,  
                                                    "date"          => $invoiceMainArray[$j]["maininvoicedate"],
                                                    "discount"      => $invoiceMainArray[$j]["maininvoice_discount"], ];
                                                                }
                                                                                
                                                            //$ref_no = (int) $ref_no + 1;
                                            
                                                                                 
                                            $j++;
                                                                            }
                                                                        }
                            }
 

                 
                $invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);

                
                $student_ledgers_ar     =   [];
                $journal_items_ar       =   [];
                foreach ($ledger_array as $in_ar) {
                $journal_entries_id   =     $this->invoice_m->journal_entries_id($in_ar);

                $journal_items_ar[]  = array(
                                            'journal'       =>  $journal_entries_id, 
                                            'referenceID'   =>  $in_ar['maininvoiceID'],
                                            'reference_type'=>  'invoice',
                                            'account'       =>  $in_ar['account_credit'], 
                                            'description'   =>  $in_ar['description'], 
                                            'feetypeID'     =>  $in_ar['feetypeID'], 
                                            'debit'         =>  0, 
                                            'credit'        =>  $in_ar['net_fee'], 
                                            'entry_type'    =>  'CR', 
                                            'created_at'    =>  date('Y-m-d h:i:s'), 
                                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                                            'journal'       =>  $journal_entries_id, 
                                            'referenceID'   =>  $in_ar['maininvoiceID'],
                                            'reference_type'=>  'invoice',
                                            'feetypeID'     =>  $in_ar['feetypeID'],
                                            'account'       =>  $in_ar['account_debit'], 
                                            'description'   =>  $in_ar['description'], 
                                            'debit'         =>  $in_ar['net_fee'], 
                                            'entry_type'    =>  'DR', 
                                            'credit'        =>  0, 
                                            'created_at'    =>  date('Y-m-d h:i:s'), 
                                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                                'journal_entries_id'       =>  $journal_entries_id, 
                                'maininvoice_id'           =>  $in_ar['maininvoiceID'], 
                                'reference_type'           =>  'invoice', 
                                'feetypeID'                =>  $in_ar['feetypeID'], 
                                'date'                     =>  date('Y-m-d',strtotime($in_ar['date'])), 
                                'type'                     =>  'CR', 
                                'account'                  =>  $in_ar['account_credit'], 
                                'vr_no'                    =>  $in_ar['refrence_no'], 
                                'narration'                =>  $in_ar['description'], 
                                'debit'                    =>  0, 
                                'credit'                   =>  $in_ar['net_fee'], 
                                'balance'                  =>  $in_ar['net_fee'],  
                                'created_at'               =>  date('Y-m-d h:i:s'), 
                                'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                                'journal_entries_id'       =>  $journal_entries_id, 
                                'maininvoice_id'           =>  $in_ar['maininvoiceID'], 
                                'reference_type'           =>  'invoice',
                                'feetypeID'                =>  $in_ar['feetypeID'],  
                                'date'                     =>  date('Y-m-d',strtotime($in_ar['date'])), 
                                'type'                     =>  'DR', 
                                'account'                  =>  $in_ar['account_debit'], 
                                'vr_no'                    =>  $in_ar['refrence_no'], 
                                'narration'                =>  $in_ar['description'], 
                                'debit'                    =>  $in_ar['net_fee'], 
                                'credit'                   =>  0, 
                                'balance'                  =>  $in_ar['net_fee'],  
                                'created_at'               =>  date('Y-m-d h:i:s'), 
                                'updated_at'               =>  date('Y-m-d h:i:s'), );
                if ($in_ar['discount']>0) {
                    

                $journal_items_ar[]  = array(
                                            'journal'       =>  $journal_entries_id, 
                                            'account'       =>  $this->data["siteinfos"]->discount_credit, 
                                            'description'   =>  $in_ar['description'], 
                                            'debit'         =>  0, 
                                            'credit'        =>  $in_ar['discount'], 
                                            'entry_type'    =>  'CR',  
                                            'referenceID'   =>  $in_ar['maininvoiceID'],
                                            'reference_type'=>  'invoice',
                                            'feetypeID'     =>  $in_ar['feetypeID'],
                                            'created_at'    =>  date('Y-m-d h:i:s'), 
                                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                                                'journal'       =>  $journal_entries_id, 
                                                'account'       =>  $this->data["siteinfos"]->discount_debit, 
                                                'description'   =>  $in_ar['description'], 
                                                'debit'         =>  $in_ar['discount'], 
                                                'entry_type'    =>  'DR', 
                                                'credit'        =>  0,  
                                                'referenceID'   =>  $in_ar['maininvoiceID'],
                                                'reference_type'=>  'invoice',
                                                'feetypeID'     =>  $in_ar['feetypeID'],
                                                'created_at'    =>  date('Y-m-d h:i:s'), 
                                                'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                                'journal_entries_id'       =>  $journal_entries_id, 
                                'maininvoice_id'           =>  $in_ar['maininvoiceID'],   
                                'date'                     =>  date('Y-m-d',strtotime($in_ar['date'])), 
                                'type'                     =>  'CR', 
                                'account'                  =>  $this->data["siteinfos"]->discount_credit, 
                                'vr_no'                    =>  $in_ar['refrence_no'], 
                                'narration'                =>  $in_ar['description'], 
                                'debit'                    =>  0, 
                                'reference_type'           =>  'invoice',
                                'feetypeID'                =>  $in_ar['feetypeID'],
                                'credit'                   =>  $in_ar['discount'], 
                                'balance'                  =>  $in_ar['discount'],  
                                'created_at'               =>  date('Y-m-d h:i:s'), 
                                'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                                'journal_entries_id'       =>  $journal_entries_id, 
                                'maininvoice_id'           =>  $in_ar['maininvoiceID'],  
                                'date'                     =>  date('Y-m-d',strtotime($in_ar['date'])), 
                                'type'                     =>  'DR', 
                                'account'                  =>  $this->data["siteinfos"]->discount_debit, 
                                'vr_no'                    =>  $in_ar['refrence_no'], 
                                'narration'                =>  $in_ar['description'], 
                                'debit'                    =>  $in_ar['discount'], 
                                'credit'                   =>  0, 
                                'reference_type'           =>  'invoice',
                                'feetypeID'                =>  $in_ar['feetypeID'],
                                'balance'                  =>  $in_ar['discount'],  
                                'created_at'               =>  date('Y-m-d h:i:s'), 
                                'updated_at'               =>  date('Y-m-d h:i:s'), );


                }

                                }
                                $this->invoice_m->push_invoice_to_journal_items( $journal_items_ar );
                                $this->invoice_m->push_invoice_to_studnet_ledgers( $student_ledgers_ar );

                                  

                                
                                $retArray["status"] = true;
                                $retArray["message"] = "Success";

                                 
                                 
                            } else {
                                $retArray["error"] = ["student" => "Student not found.",];
                               
                            }
                        } else {
                            $retArray["error"] = ["classstudent" =>"Class and Student not found.",];
                             
                        }

            $invoiceArray   =   $invoiceArray[0];
            $upload_cpv_bpv_array  = array(
                    'cpv_bpv_recordID'  => 1,
                    'pay_type'          => 'bank',
                    'payment_type'      => 'bpv',
                    'payment_date'      => date('Y-m-d'),  
                    'payment_date_status'=> 1,  
                    'student_id'        => $invoiceArray['studentID'], 
                    'student_id_status' => 1, 
                    'student_roll'      => $invoiceArray['refrence_no'], 
                    'student_roll_status'=> 1,
                    'fine_amount'       => 0, 
                    'invoice_amount'    => $amount_fine, 
                    'maininvoiceID'     => $invoiceArray['maininvoiceID'], 
                    'amount_status'     => 1, 
                    'challan_no'        => $invoiceArray['refrence_no'], 
                    'challan_no_status' => 1, 
                    'status'            => 1, 
                    'UserID'            => $row_user->userID, 
                             );
            $this->db->insert('bank_challan_record',$upload_cpv_bpv_array);
            $globalLastID = $this->db->insert_id();

            $data = $this->invoice_m->get_reconcile_data_bank($globalLastID);

            //error_reporting(E_ALL);

            $msg     = "";
            $journal_items_ar       =   [];
            $student_ledgers_ar     =   [];

            foreach ($data as $row){


               
                $id             = $row->id;
                $student_id     = $row->student_id;
                $student_roll   = $row->student_roll;
                $payment_date   = $row->payment_date;
                $challan_no     = $row->challan_no;
                $amount         = $row->invoice_amount-$row->fine_amount;
                $paymenttype    = $row->pay_type;
                $pay_type       = $row->payment_type;
                $student        = $this->student_m->get_student_select('studentID, student_id, classesID, sectionID, registerNO, name', ['studentID' => $student_id]);
                 
               // var_dump($student);
                if (customCompute($student)){
                    $maininvoice_result = $this->maininvoice_m->get_maininvoice_with_cpv($student->studentID,'','',$challan_no);
                    $excel_amount = $amount;
                    $r_amount = 0;
                    $p_amount = $excel_amount;
                    $date = $payment_date;
                    if(customCompute($maininvoice_result)){
                        foreach ($maininvoice_result as $invoice){
                            if (($p_amount == ceil($invoice->maininvoicenet_fee)) && $invoice->maininvoicestatus != 2) {
                      
                                $p_amount = $p_amount - ceil($invoice->maininvoicenet_fee);
                                
                               
                                
                                $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $invoice->maininvoiceID, 'deleted_at' => 1));

                                //var_dump($this->data['invoices']);
                                
                                $globalpayment = array(
                                            'classesID'             => $student->classesID,
                                            'sectionID'             => $student->sectionID,
                                            'studentID'             => $student->studentID,
                                            'clearancetype'         => 'partial',
                                            'invoicename'           => $student->registerNO .'-'. $student->name,
                                            'invoicedescription'    => '',
                                            'paymentyear'           => date("Y", strtotime($date)),
                                            'schoolyearID'          => 1,
                                        );
                                $this->globalpayment_m->insert_globalpayment($globalpayment);
                                $globalLastID = $this->db->insert_id();
                                $paymentArray = array(
                                                'invoiceID'         => $this->data['invoices'][0]->invoiceID,
                                                'schoolyearID'      => 1,
                                                'studentID'         => $student->studentID,
                                                'paymentamount'     => ceil($invoice->maininvoicenet_fee),
                                                'paymenttype'       => $paymenttype,
                                                'paymentdate'       => $date,
                                                'paymentday'        => date("d", strtotime($date)),
                                                'paymentmonth'      => date("m", strtotime($date)),
                                                'paymentyear'       => date("Y", strtotime($date)),
                                                'userID'            => $row_user->userID,
                                                'usertypeID'        => $row_user->usertypeID,
                                                'uname'             => $row_user->name,
                                                'transactionID'     => 'CASHANDCHEQUE'.random19(),
                                                'globalpaymentID'   => $globalLastID,
                                            );
                               
                                $this->payment_m->insert_payment($paymentArray);
                                $paymentLastID = $this->db->insert_id();

                                 if ($invoice->maininvoicestatus == 1) {
                                    $main_net = $invoice->maininvoicetotal_fee - $invoice->maininvoice_discount;
                                    $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 2, 'maininvoicecreate_date' => $date, 'maininvoicenet_fee' => $main_net],$invoice->maininvoiceID);
                                }else{
                                     $main_net = $invoice->maininvoicenet_fee;
                                    $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 2, 'maininvoicecreate_date' => $date],$invoice->maininvoiceID);
                                }


                $journal_entries_id   =     $this->invoice_m->journal_entries_id(array('studentID'=>$paymentArray['studentID']));

                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'account'       =>  $this->data['siteinfos']->student_cr_account, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'feetypeID'     =>  $this->data['invoices'][0]->feetypeID, 
                            'debit'         =>  0, 
                            'credit'        =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'CR', 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'feetypeID'     =>  $this->data['invoices'][0]->feetypeID,
                            'account'       =>  $row_user->phone, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'         =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'DR', 
                            'credit'        =>  0, 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment', 
                            'feetypeID'                =>  $this->data['invoices'][0]->feetypeID, 
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'CR', 
                            'account'                  =>  $this->data['siteinfos']->student_cr_account, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  0, 
                            'credit'                   =>  $paymentArray['paymentamount'], 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment',
                            'feetypeID'                =>  $this->data['invoices'][0]->feetypeID,  
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'DR', 
                            'account'                  =>  $row_user->phone, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  $paymentArray['paymentamount'], 
                            'credit'                   =>  0, 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                 

                               
                  
                                $paidstatus = 2;
                                $this->invoice_m->update_invoice(array('paidstatus' => $paidstatus,'pay_type' => $pay_type,'create_date' => $date), $this->data['invoices'][0]->invoiceID);
                                $this->globalpayment_m->update_globalpayment(array('clearancetype' => 'paid'), $globalLastID);
                                $this->invoice_m->update_post_status_by_id($id);

                            }else if ($p_amount < ceil($invoice->maininvoicenet_fee) && $p_amount > 0 && $invoice->maininvoicestatus != 2) {
                                
                                $this->maininvoice_m->update_maininvoice(['maininvoicenet_fee' => $invoice->maininvoicenet_fee - $p_amount],$invoice->maininvoiceID);
                                 $this->maininvoice_m->update_maininvoice(['maininvoicestatus' => 1, 'maininvoicecreate_date' => $date], $invoice->maininvoiceID);
                                 $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $invoice->maininvoiceID, 'deleted_at' => 1));
                                
                                $globalpayment = array(
                                            'classesID'             => $student->classesID,
                                            'sectionID'             => $student->sectionID,
                                            'studentID'             => $student->studentID,
                                            'clearancetype'         => 'partial',
                                            'invoicename'           => $student->registerNO .'-'. $student->name,
                                            'invoicedescription'    => '',
                                            'paymentyear'           => date("Y", strtotime($date)),
                                            'schoolyearID'          => 1,
                                        );
                                $this->globalpayment_m->insert_globalpayment($globalpayment);
                                $globalLastID = $this->db->insert_id();
                                $paymentArray = array(
                                                'invoiceID'         => $this->data['invoices'][0]->invoiceID,
                                                'schoolyearID'      => 1,
                                                'studentID'         => $student->studentID,
                                                'paymentamount'     => $p_amount,
                                                'paymenttype'       => $paymenttype,
                                                'paymentdate'       => $date,
                                               'paymentday'         => date("d", strtotime($date)),
                                                'paymentmonth'      => date("m", strtotime($date)),
                                                'paymentyear'       => date("Y", strtotime($date)),
                                                'userID'            => $row_user->userID,
                                                'usertypeID'        => $row_user->usertypeID,
                                                'uname'             => $row_user->name,
                                                'transactionID'     => 'CASHANDCHEQUE'.random19(),
                                                'globalpaymentID'   => $globalLastID,
                                            );
                                $this->payment_m->insert_payment($paymentArray);
                                $paymentLastID = $this->db->insert_id();

                                $journal_entries_id   =     $this->invoice_m->journal_entries_id(array('studentID'=>$paymentArray['studentID']));

                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'account'       =>  $this->data['siteinfos']->student_cr_account, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'feetypeID'     =>  99999, 
                            'debit'         =>  0, 
                            'credit'        =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'CR', 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $journal_items_ar[]  = array(
                            'journal'       =>  $journal_entries_id, 
                            'referenceID'   =>  $paymentLastID,
                            'reference_type'=>  'payment',
                            'feetypeID'     =>  99999,
                            'account'       =>  $row->phone, 
                            'description'   =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'         =>  $paymentArray['paymentamount'], 
                            'entry_type'    =>  'DR', 
                            'credit'        =>  0, 
                            'created_at'    =>  date('Y-m-d h:i:s'), 
                            'updated_at'    =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment', 
                            'feetypeID'                =>  99999, 
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'CR', 
                            'account'                  =>  $this->data['siteinfos']->student_cr_account, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  0, 
                            'credit'                   =>  $paymentArray['paymentamount'], 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                $student_ledgers_ar[]  = array(
                            'journal_entries_id'       =>  $journal_entries_id, 
                            'maininvoice_id'           =>  $paymentLastID, 
                            'reference_type'           =>  'payment',
                            'feetypeID'                =>  99999,  
                            'date'                     =>  date('Y-m-d'), 
                            'type'                     =>  'DR', 
                            'account'                  =>  $row->phone, 
                            'vr_no'                    =>  $this->data['invoices'][0]->refrence_no, 
                            'narration'                =>  'Payment for Invoice '.$this->data['invoices'][0]->refrence_no, 
                            'debit'                    =>  $paymentArray['paymentamount'], 
                            'credit'                   =>  0, 
                            'balance'                  =>  $paymentArray['paymentamount'],  
                            'created_at'               =>  date('Y-m-d h:i:s'), 
                            'updated_at'               =>  date('Y-m-d h:i:s'), );
                 


                                $p_amount = $p_amount - ceil($invoice->maininvoicenet_fee);
                                $paidstatus = 1;
                                $this->invoice_m->update_invoice(array('paidstatus' => $paidstatus,'pay_type' => $pay_type,'create_date' => $date), $this->data['invoices'][0]->invoiceID);
                                $this->globalpayment_m->update_globalpayment(array('clearancetype' => 'partial'), $globalLastID);
                                $this->invoice_m->update_post_status_by_id($id);
                            }
                        }
                    }

                    
                }
            } 

            $this->invoice_m->push_invoice_to_journal_items( $journal_items_ar );
            $this->invoice_m->push_invoice_to_studnet_ledgers( $student_ledgers_ar );

                    
       }   
}
