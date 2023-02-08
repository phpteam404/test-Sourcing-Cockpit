<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
require APPPATH . '/third_party/mailer/mailer.php';
#include_once (APPPATH . "/controllers/Contract.php");
//$this->load->library('../controllers/Contract.php');



class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $language = 'en';
        if(isset($_SERVER['HTTP_LANG']) && $_SERVER['HTTP_LANG']!=''){
            $language = $_SERVER['HTTP_LANG'];
            if(is_dir('application/language/'.$language)==0){
                $language = $this->config->item('rest_language');
            }
        }
        $this->lang->load('rest_controller', $language);
        $this->load->model('Project_model');

    }
    // send email through cron
    public function sendemails()
    {
        $limit=30;
        $mailer_data = $this->Customer_model->getMailer(array('limit'=>$limit));
        $this->load->library('sendgridlibrary');
        foreach($mailer_data as $k=>$v){
            if($v['cron_status']==0 && $v['is_cron']==1){
                $this->Customer_model->updateMailer(array('cron_status'=>1,'mailer_id'=>$v['mailer_id']));
                $from_name=$v['mail_from_name'];
                $from=$v['mail_from'];
                $subject=$v['mail_subject'];
                $body=$v['mail_message'];
                $to_name=$v['mail_to_name'];
                $to=$v['mail_to'];
                $mailer_id=$v['mailer_id'];
                $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                if($mail_sent_status==1) {
                    $this->Customer_model->updateMailer(array('status' => 1,'cron_status'=>2,'mailer_id' => $mailer_id));
                }
                else{
                    $this->Customer_model->updateMailer(array('cron_status'=>3,'mailer_id'=>$mailer_id));
                }
            }
        }

        /*$result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        echo json_encode($result);exit;*/
    }

    // change contract status to pending review when review date is approaching (calender)
    /*public function contractstatuschange-bkp(){
        $contracts=$this->Contract_model->getContractsToBeScheduled();
        foreach($contracts as $k=>$v){
            $check_review_schedule1 = $this->Contract_model->checkContractReviewCompletedSchedule(array('contract_id' => $v['id_contract']));
            if(empty($check_review_schedule1)){
                $update_data=array('contract_status'=>'pending review','id_contract' => $v['id_contract'],'reminder_date1'=>$v['reminder_date_1'],'reminder_date2'=>$v['reminder_date_2'],'reminder_date3'=>$v['reminder_date_3']);
                $update_status=$this->Contract_model->updateContract($update_data);
               // echo "<pre>";print_r($update_data);echo "</pre>";
            }
        }
    } */

    public function contractstatuschange(){
        $contracts=$this->Contract_model->getContractsToBeScheduled();
        // echo '<pre>'.$this->db->last_query();exit;
        foreach($contracts as $k=>$v){
            //print_r($v); 
            $check_review_schedule1 = $this->Contract_model->checkContractReviewCompletedSchedule(array('contract_id' => $v['id_contract'],'date'=>$v['date'],'days'=>$v['days']));
            //echo '<pre>'.$this->db->last_query();continue;
            // if($v['id_contract'] == '317')
            //print_r($check_review_schedule1); exit;
            if(empty($check_review_schedule1)){
                $update_data=array('contract_status'=>'pending review','id_contract' => $v['id_contract'],'reminder_date1'=>$v['reminder_date_1'],'reminder_date2'=>$v['reminder_date_2'],'reminder_date3'=>$v['reminder_date_3']);
                $update_status=$this->Contract_model->updateContract($update_data);
                // echo '<pre>'.$this->db->last_query();
                //echo "<pre>";print_r($update_data);echo "</pre>";
            }
        }
    }

    public function contractstatuschangeforworkflow()
    {
        $contracts=$this->Contract_model->getContractsToBeScheduledForWorkflow();
        //echo ''.$this->db->last_query(); exit;/
        //print_r($contracts); exit;
        foreach($contracts as $k =>$v)
        {
            $check_review_scheduled_workflow =$this->Contract_model->checkContractReviewCompletedScheduleForWorkflow(array('id_contract_workflow'=>$v['id_contract_workflow']));
            //print_r($check_review_scheduled_workflow); exit;
            if(empty($check_review_scheduled_workflow)){
                $update_data_Workflow=array('workflow_status'=>'pending workflow','id_contract_workflow' => $v['id_contract_workflow'],'reminder_date1'=>$v['reminder_date_1'],'reminder_date2'=>$v['reminder_date_2'],
                'reminder_date3'=>$v['reminder_date_3']);
                $update_status=$this->Contract_model->updateContractWorkflow($update_data_Workflow);
                //echo "<pre>";print_r($update_data);echo "</pre>";
            }

        }

    }
    public function contractreviewreminder($type=''){
       
        if(!empty($this->input->get('type')) && in_array($this->input->get('type'),array('r1','r2','r3'))){
            $reminder_type=trim($this->input->get('type'));
            $is_workflow=trim($this->input->get('is_workflow'));
            if(isset($is_workflow) && $is_workflow == 1){
                $contracts=$this->Contract_model->getContractsToBeScheduledForWorkflow(array('reminder'=>$reminder_type));
              //  echo $this->db->last_query();exit;
                if($reminder_type == 'r1') {
                    $module_key = 'CONTRACT_WORKFLOW_REMINDER1';
                    $update_data['reminder_date1']=NULL;
                }
                if($reminder_type == 'r2') {
                    $module_key = 'CONTRACT_WORKFLOW_REMINDER2';
                    $update_data['reminder_date2']=NULL;
                }
                if($reminder_type == 'r3') {
                    $module_key = 'CONTRACT_WORKFLOW_REMINDER3';
                    $update_data['reminder_date3']=NULL;
                }
            }else{
                $contracts=$this->Contract_model->getContractsToBeScheduled(array('reminder'=>$reminder_type));
                if($reminder_type == 'r1') {
                    $module_key = 'CONTRACT_INITIATE_REMINDER1';
                    $update_data['reminder_date1']=NULL;
                }
                if($reminder_type == 'r2') {
                    $module_key = 'CONTRACT_INITIATE_REMINDER2';
                    $update_data['reminder_date2']=NULL;
                }
                if($reminder_type == 'r3') {
                    $module_key = 'CONTRACT_INITIATE_REMINDER3';
                    $update_data['reminder_date3']=NULL;
                }
            }
            // echo '<pre>';print_r($contracts);exit;
            ///////////////////////////$relationship_info[0]['relationship_category_name']
            $contracts_array = array();
            foreach($contracts as $k => $v){
                $customer_id[]=$v['customer_id'];
                $cust_admin_list = $this->Customer_model->getCustomerAdminList(array('customer_id' => $v['customer_id']));
                $relationship_info = $this->Relationship_category_model->getRelationshipCategory(array('id_relationship_category'=>$v['relationship_category_id']));
                $delegate_info = $this->User_model->getUserInfo(array('user_id' => $v['delegate_id']));

                foreach($cust_admin_list['data'] as $ka => $va){
                    $contracts_array['customer_admin'][$va['id_user']]['contracts'][] = array('contract_name'=>$v['contract_name'],'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],'last_date_of_initiate'=>$v['date'],'customer_id'=>$v['customer_id']);
                }
                if(isset($delegate_info)){
                    $contracts_array['delegate'][$v['delegate_id']]['contracts'][] = array('delegate_id'=>$v['delegate_id'],'contract_id'=>$v['id_contract'],'contract_name'=>$v['contract_name'],'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],'last_date_of_initiate'=>$v['date'],'customer_id'=>$v['customer_id']);
                }
                $contracts_array['bu_owner'][$v['contract_owner_id']]['contracts'][] = array('contract_owner_id'=>$v['contract_owner_id'],'contract_id'=>$v['id_contract'],'contract_name'=>$v['contract_name'],'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],'last_date_of_initiate'=>$v['date'],'customer_id'=>$v['customer_id']);

                //Updating remainder dates to null ==> not to sent mails again.
                $update_data['id_contract']=$v['id_contract'];
                $update_status=$this->Contract_model->updateContract($update_data);
                //echo '<pre>'.$this->db->last_query();
            }
            //echo '<pre>';print_r($contracts_array);
            $cust_admin_content ='';            $bu_owner_content ='';            $delegate_content ='';
            if(isset($contracts_array['customer_admin'])) {

                foreach ($contracts_array['customer_admin'] as $k => $v) {
                    $cust_admin_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
                    $cust_admin_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
                    foreach ($v['contracts'] as $kc => $vc) {
                        $cust_admin_content .= "<tr><td>" . $vc['contract_name'] . "</td><td>" . $vc['relationship_category_name'] . "</td><td>" . $vc['last_date_of_initiate'] . "</td></tr>";
                    }
                    $cust_admin_content .= "</table>";
                    $contracts_array['customer_admin'][$k]['content']=$cust_admin_content;
                }
            }
            //echo '<pre>';print_r($contracts_array['bu_owner']);exit;
            if(isset($contracts_array['bu_owner'])) {
                foreach ($contracts_array['bu_owner'] as $k => $v) {
                    $bu_owner_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
                    $bu_owner_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
                    foreach ($v['contracts'] as $kc => $vc) {
                        $bu_owner_content .= "<tr><td>" . $vc['contract_name'] . "</td><td>" . $vc['relationship_category_name'] . "</td><td>" . $vc['last_date_of_initiate'] . "</td></tr>";
                    }
                    $bu_owner_content .= "</table>";
                    $contracts_array['bu_owner'][$k]['content']=$bu_owner_content;
                }
            }
            if(isset($contracts_array['delegate'])) {
                foreach($contracts_array['delegate'] as $k => $v){
                    $delegate_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
                    $delegate_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
                    foreach($v['contracts'] as $kc => $vc){
                        $delegate_content .= "<tr><td>".$vc['contract_name']."</td><td>".$vc['relationship_category_name']."</td><td>".$vc['last_date_of_initiate']."</td></tr>";
                    }
                    $delegate_content .="</table>";
                    $contracts_array['delegate'][$k]['content']=$delegate_content;
                }
            }
           // echo "<pre>";print_r($contracts_array);echo "</pre>";exit;
            //echo 'ca'.$cust_admin_content.'<br>'.'bu'.$bu_owner_content.'<br>'.'de'.$delegate_content;exit;


                // Commented due to included in above loop
                // foreach($contracts as $k => $v){
                //     $update_data['id_contract']=$v['id_contract'];
                //     $update_status=$this->Contract_model->updateContract($update_data);
                // }
                //print_r($contracts_array);exit;
                if(isset($customer_id)){

                            foreach($contracts_array['customer_admin'] as $kb =>$vb) {

                                $customer_admin_info = $this->User_model->getUserInfo(array('user_id' => $kb));
                                $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $customer_admin_info->customer_id, 'module_key' => $module_key));
                                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $customer_admin_info->customer_id));
                                if ($template_configurations_parent['total_records'] > 0) {
                                    if ($customer_details[0]['company_logo'] == '') {
                                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                                    } else {
                                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);

                                    }

                                    $template_configurations = $template_configurations_parent['data'][0];
                                    $wildcards = $template_configurations['wildcards'];
                                    $wildcards_replaces = array();
                                    $wildcards_replaces['first_name'] = $customer_admin_info->first_name;
                                    $wildcards_replaces['last_name'] = $customer_admin_info->last_name;
                                    $wildcards_replaces['contracts'] = $vb['content'];
                                    $wildcards_replaces['category'] = $relationship_info[0]['relationship_category_name'];
                                    $wildcards_replaces['logo'] = $customer_logo;
                                    $wildcards_replaces['year'] = date("Y");
                                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                                    /*$from_name=SEND_GRID_FROM_NAME;
                                    $from=SEND_GRID_FROM_EMAIL;
                                    $from_name=$cust_admin['name'];
                                    $from=$cust_admin['email'];*/
                                    $from_name = $template_configurations['email_from_name'];
                                    $from = $template_configurations['email_from'];
                                    $to = $customer_admin_info->email;
                                    $to_name = $customer_admin_info->first_name . ' ' . $customer_admin_info->last_name;
                                    $mailer_data['mail_from_name'] = $from_name;
                                    $mailer_data['mail_to_name'] = $to_name;
                                    $mailer_data['mail_to_user_id'] = $customer_admin_info->id_user;
                                    $mailer_data['mail_from'] = $from;
                                    $mailer_data['mail_to'] = $to;
                                    $mailer_data['mail_subject'] = $subject;
                                    $mailer_data['mail_message'] = $body;
                                    $mailer_data['status'] = 0;
                                    $mailer_data['send_date'] = currentDate();
                                    $mailer_data['is_cron'] = 0;
                                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                                    //print_r($mailer_data);
                                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                                    //sending mail to bu owner
                                    if ($mailer_data['is_cron'] == 0) {
                                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                                        $this->load->library('sendgridlibrary');
                                        $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                        if ($mail_sent_status == 1)
                                            $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                                    }


                                }
                            }
                            foreach($contracts_array['bu_owner'] as $kb =>$vb) {

                                $bu_info = $this->User_model->getUserInfo(array('user_id' => $kb));
                                
                                $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $bu_info->customer_id, 'module_key' => $module_key));
                                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $bu_info->customer_id));
                                if ($template_configurations_parent['total_records'] > 0) {
                                    if ($customer_details[0]['company_logo'] == '') {
                                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                                    } else {
                                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);

                                    }
                                    $template_configurations = $template_configurations_parent['data'][0];
                                    $wildcards = $template_configurations['wildcards'];
                                    $wildcards_replaces = array();
                                    $wildcards_replaces['first_name'] = $bu_info->first_name;
                                    $wildcards_replaces['last_name'] = $bu_info->last_name;
                                    $wildcards_replaces['contracts'] = $vb['content'];
                                    $wildcards_replaces['logo'] = $customer_logo;
                                    $wildcards_replaces['year'] = date("Y");
                                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                                    /*$from_name=SEND_GRID_FROM_NAME;
                                    $from=SEND_GRID_FROM_EMAIL;
                                    $from_name=$cust_admin['name'];
                                    $from=$cust_admin['email'];*/
                                    $from_name = $template_configurations['email_from_name'];
                                    $from = $template_configurations['email_from'];
                                    $to = $bu_info->email;
                                    $to_name = $bu_info->first_name . ' ' . $bu_info->last_name;
                                    $mailer_data['mail_from_name'] = $from_name;
                                    $mailer_data['mail_to_name'] = $to_name;
                                    $mailer_data['mail_to_user_id'] = $bu_info->id_user;
                                    $mailer_data['mail_from'] = $from;
                                    $mailer_data['mail_to'] = $to;
                                    $mailer_data['mail_subject'] = $subject;
                                    $mailer_data['mail_message'] = $body;
                                    $mailer_data['status'] = 0;
                                    $mailer_data['send_date'] = currentDate();
                                    $mailer_data['is_cron'] = 0;
                                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                                    //print_r($mailer_data);
                                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                                    //sending mail to bu owner
                                    if ($mailer_data['is_cron'] == 0) {
                                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                                        $this->load->library('sendgridlibrary');
                                        $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                        if ($mail_sent_status == 1)
                                            $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                                    }
                                }
                            }
                            if(isset($contracts_array['delegate'])){
                                foreach($contracts_array['delegate'] as $kd =>$vd) {
                                    $delegate_info = $this->User_model->getUserInfo(array('user_id' => $kd));
                                    $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $delegate_info->customer_id, 'module_key' => $module_key));
                                    $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $delegate_info->customer_id));
                                    if ($template_configurations_parent['total_records'] > 0) {
                                        if ($customer_details[0]['company_logo'] == '') {
                                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                                        } else {
                                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);

                                        }
                                        $template_configurations = $template_configurations_parent['data'][0];
                                        $wildcards = $template_configurations['wildcards'];
                                        $wildcards_replaces = array();
                                        $wildcards_replaces['first_name'] = $delegate_info->first_name;
                                        $wildcards_replaces['last_name'] = $delegate_info->last_name;
                                        $wildcards_replaces['contracts'] = $vd['content'];
                                        $wildcards_replaces['logo'] = $customer_logo;
                                        $wildcards_replaces['year'] = date("Y");
                                        $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                                        $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                                        $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                                        /*$from_name=SEND_GRID_FROM_NAME;
                                        $from=SEND_GRID_FROM_EMAIL;
                                        $from_name=$cust_admin['name'];
                                        $from=$cust_admin['email'];*/
                                        $from_name = $template_configurations['email_from_name'];
                                        $from = $template_configurations['email_from'];
                                        $to = $delegate_info->email;
                                        $to_name = $delegate_info->first_name . ' ' . $delegate_info->last_name;
                                        $mailer_data['mail_from_name'] = $from_name;
                                        $mailer_data['mail_to_name'] = $to_name;
                                        $mailer_data['mail_to_user_id'] = $delegate_info->id_user;
                                        $mailer_data['mail_from'] = $from;
                                        $mailer_data['mail_to'] = $to;
                                        $mailer_data['mail_subject'] = $subject;
                                        $mailer_data['mail_message'] = $body;
                                        $mailer_data['status'] = 0;
                                        $mailer_data['send_date'] = currentDate();
                                        $mailer_data['is_cron'] = 0;
                                        $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                                        //print_r($mailer_data);
                                        $mailer_id = $this->Customer_model->addMailer($mailer_data);
                                        //sending mail to bu owner
                                        if ($mailer_data['is_cron'] == 0) {
                                            //$mail_sent_status=sendmail($to, $subject, $body, $from);
                                            $this->load->library('sendgridlibrary');
                                            $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                            if ($mail_sent_status == 1)
                                                $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                                        }
                                    }
                                }
                            }

                        }









            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
            echo json_encode($result);exit;
        }
        else{
            echo 'Bad request';
        }

    }
    // send daily updates
    public function senddailymail(){
        $current_date_main=currentDate();
        $current_date=date('Y-m-d',strtotime($current_date_main .' -1 day'));
        //echo $current_date_main.' '.$current_date;exit;
        $this->Contract_model->getDailyMailData(array('run'=>1,'date'=>$current_date));
        $daily_customers=$this->Customer_model->getDailyUpdatesData(array('status'=>0,'date'=>$current_date));
        //echo '<pre>'.print_r($daily_customers);exit;

        foreach($daily_customers as $dk => $dv){
            $this->Customer_model->updateDailyMail(array('status'=>1),array('id_daily_update_customer'=>$dv['id_daily_update_customer']));
            //echo '<pre>';print_r($dv);exit;
            $review_started = $this->Contract_model->getDailyMailData(array('review_started'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $review_updated = $this->Contract_model->getDailyMailData(array('review_updated'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $review_finalized = $this->Contract_model->getDailyMailData(array('review_finalized'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $contributor_add = $this->Contract_model->getDailyMailData(array('contributor_add'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $contributor_remove = $this->Contract_model->getDailyMailData(array('contributor_remove'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $discussion_started = $this->Contract_model->getDailyMailData(array('discussion_started'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $discussion_updated = $this->Contract_model->getDailyMailData(array('discussion_updated'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $discussion_closed = $this->Contract_model->getDailyMailData(array('discussion_closed'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $action_item_created = $this->Contract_model->getDailyMailData(array('action_item_created'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $action_item_updated = $this->Contract_model->getDailyMailData(array('action_item_updated'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $action_item_closed = $this->Contract_model->getDailyMailData(array('action_item_closed'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $report_created = $this->Contract_model->getDailyMailData(array('report_created'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $report_edited = $this->Contract_model->getDailyMailData(array('report_edited'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $report_deleted = $this->Contract_model->getDailyMailData(array('report_deleted'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $changes_in_contract_data = $this->Contract_model->getDailyMailData(array('changes_in_contract'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $changes_in_contract_status_data = $this->Contract_model->getDailyMailData(array('changes_in_contract_status'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $new_contract_data = $this->Contract_model->getDailyMailData(array('new_contract'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $user_create = $this->Contract_model->getDailyMailData(array('user_create'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $user_update = $this->Contract_model->getDailyMailData(array('user_update'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            $user_delete = $this->Contract_model->getDailyMailData(array('user_delete'=>1,'date'=>$current_date,'customer_id'=>$dv['customer_id']));
            //echo '<pre>';print_r($user_create);exit;
            $total_data = array('review_started'=>$review_started,
                'review_updated'=>$review_updated,
                'review_finalized'=>$review_finalized ,
                'contributor_add'=>$contributor_add ,
                'contributor_remove'=>$contributor_remove ,
                'discussion_started'=>$discussion_started ,
                'discussion_updated'=>$discussion_updated ,
                'discussion_closed'=>$discussion_closed ,
                'action_item_created'=>$action_item_created ,
                'action_item_updated'=>$action_item_updated ,
                'action_item_closed'=>$action_item_closed ,
                'report_created'=>$report_created ,
                'report_edited'=>$report_edited ,
                'report_deleted'=>$report_deleted ,
                'changes_contract'=>$changes_in_contract_data,
                'changes_contract_status'=>$changes_in_contract_status_data,
                'new_contract'=>$new_contract_data,
                'user_create'=>$user_create,
                'user_update'=>$user_update,
                'user_delete'=>$user_delete);
            //echo $dv['customer_id'].'<pre>';print_r($total_data);

            $save_content = json_encode($total_data);
            $this->Customer_model->updateDailyMail(array('content'=>$save_content),array('id_daily_update_customer'=>$dv['id_daily_update_customer']));

           if(!empty($review_updated) || !empty($review_finalized) || !empty($contributor_add) || !empty($contributor_remove) || !empty($discussion_started)
               || !empty($discussion_updated) || !empty($discussion_closed) || !empty($action_item_created) || !empty($action_item_updated)
               || !empty($action_item_closed) || !empty($report_created) || !empty($report_edited) || !empty($report_deleted) || !empty($changes_in_contract_data)
               || !empty($new_contract_data) || !empty($user_create) || !empty($user_update) || !empty($user_delete))
           {
               $cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id'=>$dv['customer_id']));
           }


            if(isset($cust_admin['data']))
            {
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $dv['customer_id']));
                if($customer_details[0]['company_logo']=='') {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                }
                else{
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                }
                //////////making table of status change
                $status_change='';
                $user_change='';
                if(isset($review_started)){
                    $status_change= "<table border='1' style='border-collapse:collapse;font-size:9px;' cellpadding='3'width='100%' ><thead ><th colspan='7' style='text-align:center'>CONTRACTS</th></thead>";
                    $status_change.="<tr><th>User (first name + last name)</th><th>Date + time</th><th>Business Unit</th><th>Provider</th><th>Contract</th><th>Action</th>";
                    foreach ($review_started as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Review started'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($review_updated)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($review_updated as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Review updated'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($review_finalized)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($review_finalized as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Review finalized'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($contributor_add)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($contributor_add as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Contributor added'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($contributor_remove)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($contributor_remove as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Conntributor removee'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($discussion_started)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($discussion_started as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Discussion started'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($discussion_updated)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($discussion_updated as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Discussion updated'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($discussion_closed)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($discussion_closed as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Discussion closed'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($action_item_created)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($action_item_created as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Action item created'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($action_item_updated)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($action_item_updated as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Action item updated'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($action_item_closed)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($action_item_closed as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Action item closed'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($report_created)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($report_created as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'Report created'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($report_edited)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($report_edited as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'Report edited'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($report_deleted)){
                    //$status_change= "<table border='1' >";
                    //$status_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($report_deleted as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'---'."</td>"."<td>".'report_deleted'."</td>";;
                        $status_change.="</tr>";
                    }
                    //$status_change.="</table>";
                }
                if(isset($changes_in_contract_data)){
                    //$contract_change= "<table border='1' >";
                    //$contract_change.="<thead style='text-align:center'><th>by Whom</th><th>When</th><th>Provider</th><th>Contract</th><th>Business Unit</th><th>Action</th></thead>";
                    foreach ($changes_in_contract_data as $row) {
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Contract updated'."</td>";
                        $status_change.="</tr>";
                    }
                    //$contract_change.= "</table>";
                }
                if(isset($new_contract_data)){
                    foreach ($new_contract_data as $row) {
                        unset($row['customer_id']);
                        $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['date'])), new DateTimeZone('UTC'));
                        $date->setTimeZone(new DateTimeZone('CET'));
                        $date_cet= $date->format('Y-m-d H:i:s');
                        $status_change.="<tr>";
                        $status_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['bu_name']."</td>"."<td>".$row['provider_name']."</td>"."<td>".$row['contract_name']."</td>"."<td>".'Contract created'."</td>";;
                        $status_change.="</tr>";
                    }
                    $status_change.= "</table><br>";
                }

                if(!empty($user_create) || !empty($user_update) || !empty($user_delete)){
                    if(isset($user_create)){
                        $user_change.= "<table border='1' style='border-collapse:collapse;font-size:9px;' cellpadding='3'width='100%' ><thead ><th colspan='6' style='text-align:center'>USERS</th></thead>";
                        $user_change.="<th>User (first name + last name)</th><th>Date + time</th><th>Business Unit</th><th>User role</th><th>Action</th>";
                        foreach ($user_create as $row) {
                            unset($row['customer_id']);
                            $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['created_on'])), new DateTimeZone('UTC'));
                            $date->setTimeZone(new DateTimeZone('CET'));
                            $date_cet= $date->format('Y-m-d H:i:s');
                            $user_change.="<tr>";
                            $user_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['business_unit']."</td>"."<td>".$row['user_role_name']."</td>"."<td>".$row['action']."</td>";;
                            $user_change.="</tr>";
                        }

                    }
                    if(isset($user_update)){
                        foreach ($user_update as $row) {
                            unset($row['customer_id']);
                            $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['created_on'])), new DateTimeZone('UTC'));
                            $date->setTimeZone(new DateTimeZone('CET'));
                            $date_cet= $date->format('Y-m-d H:i:s');
                            $user_change.="<tr>";
                            $user_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['business_unit']."</td>"."<td>".$row['user_role_name']."</td>"."<td>".$row['action']."</td>";;
                            $user_change.="</tr>";
                        }

                    }
                    if(isset($user_delete)){
                        foreach ($user_delete as $row) {
                            unset($row['customer_id']);
                            $date = new DateTime(date('Y-m-d H:i:s',strtotime($row['created_on'])), new DateTimeZone('UTC'));
                            $date->setTimeZone(new DateTimeZone('CET'));
                            $date_cet= $date->format('Y-m-d H:i:s');
                            $user_change.="<tr>";
                            $user_change.="<td>".$row['name']."</td>"."<td>".$date_cet." CET"."</td>"."<td>".$row['business_unit']."</td>"."<td>".$row['user_role_name']."</td>"."<td>".$row['action']."</td>";;
                            $user_change.="</tr>";
                        }
                        $user_change.= "</table>";
                    }
                }

                //echo $dv['customer_id'].'Contract<br>'.$status_change.'<br/>User<br>'.print_r($user_change);
                $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $dv['customer_id'],'module_key'=>'CONTRACT_DAILY_UPDATE'));


                foreach($cust_admin['data']as $k2=>$v2){
                    $template_configurations=$template_configurations_parent;
                    if($template_configurations['total_records']>0){
                        $template_configurations=$template_configurations['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$v2['first_name'];
                        $wildcards_replaces['last_name']=$v2['last_name'];
                        $wildcards_replaces['daily_update_date']=$current_date;
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['contract_change_log']=isset($status_change)?$status_change:'';
                        $wildcards_replaces['user_log']=isset($user_change)?$user_change:'';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        /*$from_name=SEND_GRID_FROM_NAME;
                        $from=SEND_GRID_FROM_EMAIL;
                        $from_name=$cust_admin['name'];
                        $from=$cust_admin['email'];*/
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$v2['email'];
                        $to_name=$v2['first_name'].' '.$v2['last_name'];
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_to_user_id']=$v2['id_user'];
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_subject']=$subject;
                        $mailer_data['mail_message']=$body;
                        $mailer_data['status']=0;
                        $mailer_data['send_date']=currentDate();
                        $mailer_data['is_cron']=0;
                        $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                        $mailer_id=$this->Customer_model->addMailer($mailer_data);
                        if($mailer_data['is_cron']==0) {
                            //$mail_sent_status=sendmail($to, $subject, $body, $from);
                            $this->load->library('sendgridlibrary');
                            $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                            if($mail_sent_status==1){
                                $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                                $this->Customer_model->updateDailyMail(array('status'=>2),array('id_daily_update_customer'=>$dv['id_daily_update_customer']));
                            }
                            else{
                                $this->Customer_model->updateDailyMail(array('status'=>3),array('id_daily_update_customer'=>$dv['id_daily_update_customer']));

                            }

                        }
                    }
                }
            }
            else{
                $this->Customer_model->updateDailyMail(array('status'=>2),array('id_daily_update_customer'=>$dv['id_daily_update_customer']));
            }
            unset($cust_admin);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        echo json_encode($result);exit;

    }
    // to delete the hard copy of files from the file system for soft deleted attachmnets
    public function removedownloads(){
        $delete_file_after_hours=2;//delete files after 2 hours
        $dir = FCPATH."downloads/*";
        $path = FCPATH."downloads/";
        $date1 = new DateTime(null);
        $files_to_be_deleted=array();
        foreach(glob($dir) as $file)
        {
            if(!is_dir($file)) {
                $loop_file='';
                $loop_file=$path.basename($file);
                $date2=new DateTime(date("Y-m-d H:i:s",filemtime($path.basename($file))));
                $diff = $date2->diff($date1);
                $hours = $diff->h;
                $hours = $hours + ($diff->days*24);
                if($hours>$delete_file_after_hours){
                    $files_to_be_deleted[]=$loop_file;
                }
            }
        }
        $dir = FILE_SYSTEM_PATH."downloads/*";
        $path = FILE_SYSTEM_PATH."downloads/";
        $date1 = new DateTime(null);
        $files_to_be_deleted=array();
        foreach(glob($dir) as $file)
        {
            if(!is_dir($file)) {
                $loop_file='';
                $loop_file=$path.basename($file);
                $date2=new DateTime(date("Y-m-d H:i:s",filemtime($path.basename($file))));
                $diff = $date2->diff($date1);
                $hours = $diff->h;
                $hours = $hours + ($diff->days*24);
                if($hours>$delete_file_after_hours){
                    $files_to_be_deleted[]=$loop_file;
                }
            }
        }
        if(count($files_to_be_deleted)>0){
            foreach($files_to_be_deleted as $k=>$v){
                if(file_exists($v)){
                    unlink($v);
                }
            }
        }
    }

    public function contractEndDateReminder(){

        $contracts=$this->Contract_model->getContractsToBeInitiatedInSixMonths();
            
        $module_key = 'CONTRACT_END_DATE_REMINDER';
           
        //echo '<pre>';print_r($contracts);exit;
        $contracts_array = array();
        foreach($contracts as $k => $v){
            $customer_id[]=$v['customer_id'];
            $cust_admin_list = $this->Customer_model->getCustomerAdminList(array('customer_id' => $v['customer_id']));
            $relationship_info = $this->Relationship_category_model->getRelationshipCategory(array('id_relationship_category'=>$v['relationship_category_id']));
            $delegate_info = $this->User_model->getUserInfo(array('user_id' => $v['delegate_id']));
            $assignedto =  $delegate_info->first_name.' '.$delegate_info->last_name;
            //echo $delegate_info['first_name'];
            //echo '<pre>'.print_r($delegate_info);exit;

            foreach($cust_admin_list['data'] as $ka => $va){
                $contracts_array['customer_admin'][$va['id_user']]['contracts'][] = array('contract_name'=>$v['contract_name'],
                'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],
                'customer_id'=>$v['customer_id'],
                'contract_name'=>$v['contract_name'],
                'provider_name'=>$v['provider_name'],
                'contract_owner_name'=>$v['contract_owner_name'],
                'contract_start_date'=>$v['contract_start_date'],
                'contract_end_date'=>$v['contract_end_date'],
                'contract_assigned_to_user_names'=>$assignedto,
                'automatic_prolongation_status'=>$v['auto_renewal']==1?'Yes':'No'
                );
            }
            if(isset($delegate_info)){
                $contracts_array['delegate'][$v['delegate_id']]['contracts'][] = array('delegate_id'=>$v['delegate_id'],'contract_id'=>$v['id_contract'],'contract_name'=>$v['contract_name'],
                'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],
                'customer_id'=>$v['customer_id'],
                'contract_name'=>$v['contract_name'],
                'provider_name'=>$v['provider_name'],
                'contract_owner_name'=>$v['contract_owner_name'],
                'contract_start_date'=>$v['contract_start_date'],
                'contract_end_date'=>$v['contract_end_date'],
                'contract_assigned_to_user_names'=>$assignedto,
                'automatic_prolongation_status'=>$v['auto_renewal']==1?'Yes':'No'
                );
            }
            $contracts_array['bu_owner'][$v['contract_owner_id']]['contracts'][] = array('contract_owner_id'=>$v['contract_owner_id'],'contract_id'=>$v['id_contract'],
                'contract_name'=>$v['contract_name'],'relationship_category_name'=>$relationship_info[0]['relationship_category_name'],
                'customer_id'=>$v['customer_id'],
                'contract_name'=>$v['contract_name'],
                'provider_name'=>$v['provider_name'],
                'contract_owner_name'=>$v['contract_owner_name'],
                'contract_start_date'=>$v['contract_start_date'],
                'contract_end_date'=>$v['contract_end_date'],
                'contract_assigned_to_user_names'=>$assignedto,
                'automatic_prolongation_status'=>$v['auto_renewal']==1?'Yes':'No'
                );
        }
        //echo '<pre>';print_r($contracts_array);exit;
        // $cust_admin_content ='';            $bu_owner_content ='';            $delegate_content ='';
        // if(isset($contracts_array['customer_admin'])) {

        //     foreach ($contracts_array['customer_admin'] as $k => $v) {
        //         $cust_admin_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
        //         $cust_admin_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
        //         foreach ($v['contracts'] as $kc => $vc) {
        //             $cust_admin_content .= "<tr><td>" . $vc['contract_name'] . "</td><td>" . $vc['relationship_category_name'] . "</td></tr>";
        //         }
        //         $cust_admin_content .= "</table>";
        //         $contracts_array['customer_admin'][$k]['content']=$cust_admin_content;
        //     }
        // }
        // //echo '<pre>';print_r($contracts_array['bu_owner']);exit;
        // if(isset($contracts_array['bu_owner'])) {
        //     foreach ($contracts_array['bu_owner'] as $k => $v) {
        //         $bu_owner_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
        //         $bu_owner_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
        //         foreach ($v['contracts'] as $kc => $vc) {
        //             $bu_owner_content .= "<tr><td>" . $vc['contract_name'] . "</td><td>" . $vc['relationship_category_name'] . "</td></tr>";
        //         }
        //         $bu_owner_content .= "</table>";
        //         $contracts_array['bu_owner'][$k]['content']=$bu_owner_content;
        //     }
        // }
        // if(isset($contracts_array['delegate'])) {
        //     foreach($contracts_array['delegate'] as $k => $v){
        //         $delegate_content = "<table border='1' style='border-collapse:collapse;font-size:12px;' cellpadding='3' width='100%' >";
        //         $delegate_content .= "<thead align='left'><th>Contract</th><th>Category</th><th>Review deadline</th></thead>";
        //         foreach($v['contracts'] as $kc => $vc){
        //             $delegate_content .= "<tr><td>".$vc['contract_name']."</td><td>".$vc['relationship_category_name']."</td></tr>";
        //         }
        //         $delegate_content .="</table>";
        //         $contracts_array['delegate'][$k]['content']=$delegate_content;
        //     }
        // }
        //echo 'ca'.$cust_admin_content.'<br>'.'bu'.$bu_owner_content.'<br>'.'de'.$delegate_content;exit;
        ///echo "<pre>";print_r($contracts_array);echo "</pre>";exit;
        if(isset($customer_id)){ 
            foreach($contracts_array['customer_admin'] as $kb =>$vb) {
                $customer_admin_info = $this->User_model->getUserInfo(array('user_id' => $kb));
                $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $customer_admin_info->customer_id, 'module_key' => $module_key));
                //echo '<pre>'.$this->db->last_query();
                //echo '<pre>'.print_r($template_configurations_parent);//exit;
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $customer_admin_info->customer_id));
                if ($template_configurations_parent['total_records'] > 0) {
                    if ($customer_details[0]['company_logo'] == '') {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                    } else {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                    }
                    $template_configurations = $template_configurations_parent['data'][0];
                    $wildcards = $template_configurations['wildcards'];
                    $vb=$vb['contracts'][0];
                    //echo '<pre>'.print_r($vb);exit;
                    $wildcards_replaces = array();
                    $wildcards_replaces['first_name'] = $customer_admin_info->first_name;
                    $wildcards_replaces['last_name'] = $customer_admin_info->last_name;
                    $wildcards_replaces['contract_name'] = $vb['contract_name'];
                    $wildcards_replaces['provider_name'] = $vb['provider_name'];
                    $wildcards_replaces['contract_owner_name'] = $vb['contract_owner_name'];
                    $wildcards_replaces['contract_start_date'] = $vb['contract_start_date'];
                    $wildcards_replaces['contract_end_date'] = $vb['contract_end_date'];
                    $wildcards_replaces['contract_assigned_to_user_names'] = $vb['contract_assigned_to_user_names'];
                    $wildcards_replaces['automatic_prolongation_status'] = $vb['automatic_prolongation_status'];
                    $wildcards_replaces['logo'] = $customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                    //echo '<pre>'.print_r($wildcards_replaces);exit;
                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                    /*$from_name=SEND_GRID_FROM_NAME;
                    $from=SEND_GRID_FROM_EMAIL;
                    $from_name=$cust_admin['name'];
                    $from=$cust_admin['email'];*/
                    $from_name = $template_configurations['email_from_name'];
                    $from = $template_configurations['email_from'];
                    $to = $customer_admin_info->email;
                    $to_name = $customer_admin_info->first_name . ' ' . $customer_admin_info->last_name;
                    $mailer_data['mail_from_name'] = $from_name;
                    $mailer_data['mail_to_name'] = $to_name;
                    $mailer_data['mail_to_user_id'] = $customer_admin_info->id_user;
                    $mailer_data['mail_from'] = $from;
                    $mailer_data['mail_to'] = $to;
                    $mailer_data['mail_subject'] = $subject;
                    $mailer_data['mail_message'] = $body;
                    $mailer_data['status'] = 0;
                    $mailer_data['send_date'] = currentDate();
                    $mailer_data['is_cron'] = 0;
                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                    //print_r($mailer_data);
                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                    //sending mail to bu owner
                    if ($mailer_data['is_cron'] == 0) {
                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                        $this->load->library('sendgridlibrary');
                        $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                        if ($mail_sent_status == 1)
                            $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                    }
                }
            }
            foreach($contracts_array['bu_owner'] as $kb =>$vb) {
                $bu_info = $this->User_model->getUserInfo(array('user_id' => $kb));
                $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $bu_info->customer_id, 'module_key' => $module_key));
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $bu_info->customer_id));
                if ($template_configurations_parent['total_records'] > 0) {
                    if ($customer_details[0]['company_logo'] == '') {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                    } else {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                    }
                    $template_configurations = $template_configurations_parent['data'][0];
                    $wildcards = $template_configurations['wildcards'];
                    $vb=$vb['contracts'][0];
                    $wildcards_replaces = array();                    
                    $wildcards_replaces['first_name'] = $bu_info->first_name.' ';
                    $wildcards_replaces['last_name'] = $bu_info->last_name;
                    $wildcards_replaces['contract_name'] = $vb['contract_name'];
                    $wildcards_replaces['provider_name'] = $vb['provider_name'];
                    $wildcards_replaces['contract_owner_name'] = $vb['contract_owner_name'];
                    $wildcards_replaces['contract_start_date'] = $vb['contract_start_date'];
                    $wildcards_replaces['contract_end_date'] = $vb['contract_end_date'];
                    $wildcards_replaces['contract_assigned_to_user_names'] = $vb['contract_assigned_to_user_names'];
                    $wildcards_replaces['automatic_prolongation_status'] = $vb['automatic_prolongation_status'];
                    $wildcards_replaces['logo'] = $customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                    /*$from_name=SEND_GRID_FROM_NAME;
                    $from=SEND_GRID_FROM_EMAIL;
                    $from_name=$cust_admin['name'];
                    $from=$cust_admin['email'];*/
                    $from_name = $template_configurations['email_from_name'];
                    $from = $template_configurations['email_from'];
                    $to = $bu_info->email;
                    $to_name = $bu_info->first_name . ' ' . $bu_info->last_name;
                    $mailer_data['mail_from_name'] = $from_name;
                    $mailer_data['mail_to_name'] = $to_name;
                    $mailer_data['mail_to_user_id'] = $bu_info->id_user;
                    $mailer_data['mail_from'] = $from;
                    $mailer_data['mail_to'] = $to;
                    $mailer_data['mail_subject'] = $subject;
                    $mailer_data['mail_message'] = $body;
                    $mailer_data['status'] = 0;
                    $mailer_data['send_date'] = currentDate();
                    $mailer_data['is_cron'] = 0;
                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                    //print_r($mailer_data);
                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                    //sending mail to bu owner
                    if ($mailer_data['is_cron'] == 0) {
                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                        $this->load->library('sendgridlibrary');
                        $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                        if ($mail_sent_status == 1)
                            $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                    }
                }
            }
            if(isset($contracts_array['delegate'])){
                foreach($contracts_array['delegate'] as $kd =>$vd) {
                    $delegate_info = $this->User_model->getUserInfo(array('user_id' => $kd));
                    $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $delegate_info->customer_id, 'module_key' => $module_key));
                    $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $delegate_info->customer_id));
                    if ($template_configurations_parent['total_records'] > 0) {
                        if ($customer_details[0]['company_logo'] == '') {
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                        } else {
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                        }
                        $template_configurations = $template_configurations_parent['data'][0];
                        $wildcards = $template_configurations['wildcards'];
                        $vb=$vb['contracts'][0];
                        $wildcards_replaces = array();
                        $wildcards_replaces['first_name'] = $delegate_info->first_name;
                        $wildcards_replaces['last_name'] = $delegate_info->last_name;
                        $wildcards_replaces['contract_name'] = $vb['contract_name'];
                        $wildcards_replaces['provider_name'] = $vb['provider_name'];
                        $wildcards_replaces['contract_owner_name'] = $vb['contract_owner_name'];
                        $wildcards_replaces['contract_start_date'] = $vb['contract_start_date'];
                        $wildcards_replaces['contract_end_date'] = $vb['contract_end_date'];
                        $wildcards_replaces['contract_assigned_to_user_names'] = $vb['contract_assigned_to_user_names'];
                        $wildcards_replaces['automatic_prolongation_status'] = $vb['automatic_prolongation_status'];
                        $wildcards_replaces['logo'] = $customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                        $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                        /*$from_name=SEND_GRID_FROM_NAME;
                        $from=SEND_GRID_FROM_EMAIL;
                        $from_name=$cust_admin['name'];
                        $from=$cust_admin['email'];*/
                        $from_name = $template_configurations['email_from_name'];
                        $from = $template_configurations['email_from'];
                        $to = $delegate_info->email;
                        $to_name = $delegate_info->first_name . ' ' . $delegate_info->last_name;
                        $mailer_data['mail_from_name'] = $from_name;
                        $mailer_data['mail_to_name'] = $to_name;
                        $mailer_data['mail_to_user_id'] = $delegate_info->id_user;
                        $mailer_data['mail_from'] = $from;
                        $mailer_data['mail_to'] = $to;
                        $mailer_data['mail_subject'] = $subject;
                        $mailer_data['mail_message'] = $body;
                        $mailer_data['status'] = 0;
                        $mailer_data['send_date'] = currentDate();
                        $mailer_data['is_cron'] = 0;
                        $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                        //print_r($mailer_data);
                        $mailer_id = $this->Customer_model->addMailer($mailer_data);
                        //sending mail to bu owner
                        if ($mailer_data['is_cron'] == 0) {
                            //$mail_sent_status=sendmail($to, $subject, $body, $from);
                            $this->load->library('sendgridlibrary');
                            $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                            if ($mail_sent_status == 1)
                                $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                        }
                    }
                }
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        echo json_encode($result);exit;
    }

    public function testcron(){
        $this->load->library('sendgridlibrary');
        echo 'mailer status '.$mail_sent_status=$this->sendgridlibrary->sendemail('SAIPRASAD','saiprasad.b@gmail.com','subject','body','saiprasd','saiprasad.b@thresholdsoft.com',array(),'2');
    }

    public function initializeReview_get($data=null)
    {
        // $data = $this->input->get();
        // echo 'data'.'<pre>';print_r($data);exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            print_r($result);exit;
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            print_r($result);exit;
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
        }
        if(isset($data['calender_id']) && $data['calender_id'] != null && $data['calender_id'] != '') {
            $data['calender_id'] = pk_decrypt($data['calender_id']);
        }
        if(isset($data['id_contract_workflow']) && $data['id_contract_workflow'] !='0') {
            $data['id_contract_workflow'] = pk_decrypt($data['id_contract_workflow']);
            $check_contract_review = $this->Contract_model->getContractReview(array(
                'contract_id' => $data['contract_id'],
                'status' => 'workflow in progress',
                'contract_workflow_id' => $data['id_contract_workflow'],
                'is_workflow' => 1
            ));
            $msg = $this->lang->line('workflow_initiate');
            //Updating contract workflow
            $this->User_model->update_data('contract_workflow',array('workflow_status'=>'workflow in progress'),array('id_contract_workflow'=>$data['id_contract_workflow']));
        }else{
            $check_contract_review = $this->Contract_model->getContractReview(array(
                'contract_id' => $data['contract_id'],
                'status' => 'review in progress',
                'contract_workflow_id' => 0,
                'is_workflow' => 0
            ));
            $msg = $this->lang->line('review_initiate');
        }
        // echo '<pre>'.$this->db->last_query();exit;
        if(!empty($check_contract_review)){
            $this->Contract_model->updateContract(array(
                'id_contract' => $data['contract_id'],
                'contract_status' => 'review in progress', //pending review from 2 time
                'updated_by' => $data['created_by'],
                'updated_on' => currentDate(),
                'reminder_type' => NULL,
                'reminder_sent_on' => NULL,
                'reminder_date1' => NULL,
                'reminder_date2' => NULL,
                'reminder_date3' => NULL
            ));

            $result = array('status'=>TRUE, 'message' => $msg, 'data'=>pk_encrypt($check_contract_review[0]['id_contract_review']));
            $this->response($result, REST_Controller::HTTP_OK); exit;
        }
        //echo '<pre>'.$this->db->last_query();exit;
        $contract_update_data = array(
            'id_contract' => $data['contract_id'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
            'reminder_type' => NULL,
            'reminder_sent_on' => NULL,
            'reminder_date1' => NULL,
            'reminder_date2' => NULL,
            'reminder_date3' => NULL
        );
        if($data['is_workflow'] == 0){
            $contract_update_data['contract_status'] = 'review in progress';
        }
        $this->Contract_model->updateContract($contract_update_data);
        if(isset($data['is_workflow']) && $data['is_workflow'] == 1 && isset($data['id_contract_workflow']))
            $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'is_workflow'=>1,'contract_review_status'=>'finished','order' => 'DESC'));
        else
            $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'contract_review_status'=>'finished','order' => 'DESC'));
        if(!empty($review) && isset($review[0]['id_contract_review']) && $review[0]['id_contract_review']!='' && $review[0]['id_contract_review']!=0) {
            $previous_review_id=$review[0]['id_contract_review'];
        }
        $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
        $contract_review_data = array(
            'contract_id' => $data['contract_id'],
            'contract_review_due_date' => currentDate(),
            'contract_review_type' => isset($data['contract_review_type'])?$data['contract_review_type']:'',
            'created_by' => $data['created_by'],
            'created_on' => currentDate(),
            'relationship_category_id' =>$contract_info[0]['relationship_category_id'],
            'calender_id' =>isset($data['calender_id'])?$data['calender_id']:0
        );
        if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
            if($data['is_workflow'] == 1){
                $contract_review_data['is_workflow'] = $data['is_workflow'];
                $contract_review_data['contract_workflow_id'] = $data['id_contract_workflow'];
                $contract_review_data['contract_review_status'] = 'workflow in progress';
            }
        }

        $data['contract_review_id'] = $this->Contract_model->addContractReview($contract_review_data);
        $data['parent_relationship_category_id']=$contract_info[0]['relationship_category_id'];
        $data['template_id'] = $contract_info[0]['template_id'];

        if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
            if($data['is_workflow'] == 1){
                $contract_workflow = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
                if($contract_workflow[0]['workflow_id'] > 0)
                    $data['template_id'] = $contract_workflow[0]['workflow_id']; // Workflow_id == selected workflow in calendar workflow planning.
            }
        }
    
        $this->Contract_model->cloneModuleTopicQuestionForContractNew($data);
        /** */
        ///////Activating OR Deactivating the modules Based on Stored Modules Settings: Starts
        $stored_modules = $this->User_model->check_record('stored_modules',array('contract_id'=>$data['contract_id']));
        $contract_modules = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id']));

        foreach($stored_modules as $sk => $sv){

            foreach($contract_modules as $ck => $cv){

                if($sv['parent_module_id'] == $cv['parent_module_id'] && !(int)$sv['activate_in_next_review'] && $data['is_workflow'] == 0){
                    //Updating the Modulestatus to 0 if that is set to activate_in_next_review=0 in stored modules 
                    $this->User_model->update_data('module',array('module_status'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_review_id'=>$data['contract_review_id']));
                    $cv['module_status'] = 0;
                }else if($sv['parent_module_id'] == $cv['parent_module_id'] && (int)$sv['activate_in_next_review']){
                    if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                        $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'contract_id'=>$data['contract_id']));
                    }
                    else{
                        $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_id'=>$data['contract_id']));
                    }
                    if($cv['module_status'] == 1){
                        //Deleting the question_answers of current review to null if the module is static and activated in next review
                        $cqr_sql = "DELETE FROM contract_question_review WHERE question_id in(SELECT id_question FROM question q LEFT JOIN topic t ON q.topic_id = t.id_topic WHERE t.module_id = ".$cv['id_module']." )";
                        $this->db->query($cqr_sql);
                    }
                }
            }
        }

        //Updating Score for stored_modeules Starts
        if(isset($data['is_workflow']) && $data['is_workflow']==0){
            $previous_contract_review_id = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'order' => 'DESC','contract_review_status'=>'finished'));//echo $this->db->last_query();exit;
            $get_current_review_stored_module = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id'],'module_status'=>0,'static'=>1));
            if(count($get_current_review_stored_module)>0){
                foreach($get_current_review_stored_module as $module){
                    $get_previous_module=$this->User_model->check_record('module',array('contract_review_id'=>$previous_contract_review_id[0]['id_contract_review'],'parent_module_id'=>$module['parent_module_id']));
                    $this->User_model->update_data('module',array('module_score'=>$get_previous_module[0]['module_score']),array('id_module'=>$module['id_module'],'contract_review_id'=>$data['contract_review_id']));
                    $get_current_topic_details=$this->User_model->check_record('topic',array('module_id'=>$module['id_module']));
                    foreach($get_current_topic_details as $topic){
                        $get_previous_topic=$this->User_model->check_record('topic',array('module_id'=>$get_previous_module[0]['id_module'],'parent_topic_id'=>$topic['parent_topic_id']));
                        $this->User_model->update_data('topic',array('topic_score'=>$get_previous_topic[0]['topic_score']),array('id_topic'=>$topic['id_topic']));
                    }
                }
            }
        }
        

        $bu_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
        $contract_review_info = $this->Contract_model->getContractReview(array('id_contract_review' => $data['contract_review_id']));
        $cust_admin_info = $this->User_model->getUserInfo(array('customer_id' => $data['customer_id'],'user_role_id' =>2,'user_status'=>1));
        $contract_review_user = $this->User_model->getUserInfo(array('user_id' => $contract_review_info[0]['created_by']));
        $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
        if($customer_details[0]['company_logo']=='') {
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
        }
        else{
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
        }
        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
        
        if(isset($previous_review_id)){
            $migrate['old_contract_review_id']=$previous_review_id;
            $migrate['new_contract_review_id']=$data['contract_review_id'];
            $migrate['created_by']=$data['created_by'];
            $migrate_modules=$this->Contract_model->migrateContractUsersFromOldReview($migrate);
            $migrate_modules_array=array();
            foreach($migrate_modules as $km=>$vm){
                $migrate_modules_array[]=$vm['user_id'];
            }
            $migrate_modules_array=array_values(array_unique($migrate_modules_array));
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
           
            if($data['is_workflow'] == 1){
                $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'CONTRACT_WORKFLOW_INITIATE'));
            }else{
                $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'CONTRACT_REVIEW_INITIATE'));
            }
            if($template_configurations_parent['total_records']>0) {
                foreach ($migrate_modules_array as $k => $v) {
                    //$module_info = $this->Module_model->getModuleName(array('language_id' => 1, 'module_id' => $v['id_module']));
                    $To = $this->User_model->getUserInfo(array('user_id' => $v,'user_status'=>1));
                    //sending mail to bu owner
                    if ($template_configurations_parent['total_records'] > 0 && !empty($To)) {
                        $template_configurations = $template_configurations_parent['data'][0];
                        $wildcards = $template_configurations['wildcards'];
                        $wildcards_replaces = array();
                        $wildcards_replaces['first_name'] = $To->first_name;
                        $wildcards_replaces['last_name'] = $To->last_name;
                        $wildcards_replaces['contract_name'] = $contract_info[0]['contract_name'];
                        // $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                        // $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
                        if($data['is_workflow']==1){
                            $wildcards_replaces['contract_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                            $wildcards_replaces['contract_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
                        }
                        else{
                            $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                            $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
                        }
                        $wildcards_replaces['logo'] = $customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                        $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to = $To->email;
                        $to_name = $To->first_name . ' ' . $To->last_name;
                        $mailer_data['mail_from_name'] = $from_name;
                        $mailer_data['mail_to_name'] = $to_name;
                        $mailer_data['mail_to_user_id'] = $To->id_user;
                        $mailer_data['mail_from'] = $from;
                        $mailer_data['mail_to'] = $to;
                        $mailer_data['mail_subject'] = $subject;
                        $mailer_data['mail_message'] = $body;
                        $mailer_data['status'] = 0;
                        $mailer_data['send_date'] = currentDate();
                        $mailer_data['is_cron'] = 0;
                        $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                        //print_r($mailer_data);
                        $mailer_id = $this->Customer_model->addMailer($mailer_data);
                        //sending mail to bu owner
                        if ($mailer_data['is_cron'] == 0) {
                            //$mail_sent_status=sendmail($to, $subject, $body, $from);
                            $this->load->library('sendgridlibrary');
                            $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                            if ($mail_sent_status == 1)
                                $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                        }

                    }
                }
            }


        }

        if($customer_details[0]['company_logo']=='') {
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
        }
        else{
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);

        }
        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }

        //sending mail to bu owner
        if($data['is_workflow'] == 1){
            $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'CONTRACT_WORKFLOW_INITIATE'));
        }else{
            $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'CONTRACT_REVIEW_INITIATE'));
        }
        // echo $this->db->last_query();exit;
        if($template_configurations_parent['total_records']>0 && !empty($cust_admin_info)){
            $template_configurations=$template_configurations_parent['data'][0];
            $wildcards=$template_configurations['wildcards'];
            $wildcards_replaces=array();
            $wildcards_replaces['first_name']=$cust_admin_info->first_name;
            $wildcards_replaces['last_name']=$cust_admin_info->last_name;
            $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
            // $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
            // $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
            if($data['is_workflow']==1){
                $wildcards_replaces['contract_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                $wildcards_replaces['contract_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
            }
            else{
                $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
            }
            $wildcards_replaces['logo']=$customer_logo;
            $wildcards_replaces['year'] = date("Y");
            $wildcards_replaces['url']=WEB_BASE_URL.'html';
            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
            $from_name=$template_configurations['email_from_name'];
            $from=$template_configurations['email_from'];
            $to=$cust_admin_info->email;
            $to_name=$cust_admin_info->first_name.' '.$cust_admin_info->last_name;
            $mailer_data['mail_from_name']=$from_name;
            $mailer_data['mail_to_name']=$to_name;
            $mailer_data['mail_to_user_id']=$cust_admin_info->id_user;
            $mailer_data['mail_from']=$from;
            $mailer_data['mail_to']=$to;
            $mailer_data['mail_subject']=$subject;
            $mailer_data['mail_message']=$body;
            $mailer_data['status']=0;
            $mailer_data['send_date']=currentDate();
            $mailer_data['is_cron']=0;
            $mailer_data['email_template_id']=$template_configurations['id_email_template'];
            //print_r($mailer_data);
            $mailer_id=$this->Customer_model->addMailer($mailer_data);
            //sending mail to bu owner
            if($mailer_data['is_cron']==0) {
                //$mail_sent_status=sendmail($to, $subject, $body, $from);
                $this->load->library('sendgridlibrary');
                $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                if($mail_sent_status==1)
                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
            }

        }
        if(isset($contract_info[0]['delegate_id'])){
            $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
            
            if($template_configurations_parent['total_records']>0 && !empty(($delegate_info))){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$delegate_info->first_name;
                $wildcards_replaces['last_name']=$delegate_info->last_name;
                $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                // $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                // $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
                if($data['is_workflow']==1){
                    $wildcards_replaces['contract_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['contract_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
                }
                else{
                    $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject=$template_configurations['template_subject'];
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$delegate_info->email;
                $to_name=$delegate_info->first_name.' '.$delegate_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$delegate_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                //print_r($mailer_data);
                $mailer_id=$this->Customer_model->addMailer($mailer_data);

                //sending mail to delegate
                if($mailer_data['is_cron']==0){
                    //$mail_sent_status=sendmail($to_delegate, $subject, $body, $from);
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }

            }
        }
        if($template_configurations_parent['total_records']>0 && !empty($bu_info)){
            $template_configurations=$template_configurations_parent['data'][0];
            $wildcards=$template_configurations['wildcards'];
            $wildcards_replaces=array();
            $wildcards_replaces['first_name']=$bu_info->first_name;
            $wildcards_replaces['last_name']=$bu_info->last_name;
            $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
            // $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
            // $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
            if($data['is_workflow']==1){
                $wildcards_replaces['contract_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                $wildcards_replaces['contract_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
            }
            else{
                $wildcards_replaces['contract_review_initiated_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                $wildcards_replaces['contract_review_created_date']=dateFormat($contract_review_info[0]['created_on']);
            }
            $wildcards_replaces['logo']=$customer_logo;
            $wildcards_replaces['year'] = date("Y");
            $wildcards_replaces['url']=WEB_BASE_URL.'html';
            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
            $from_name=$template_configurations['email_from_name'];
            $from=$template_configurations['email_from'];
            $to=$bu_info->email;
            $to_name=$bu_info->first_name.' '.$bu_info->last_name;
            $mailer_data['mail_from_name']=$from_name;
            $mailer_data['mail_to_name']=$to_name;
            $mailer_data['mail_to_user_id']=$bu_info->id_user;
            $mailer_data['mail_from']=$from;
            $mailer_data['mail_to']=$to;
            $mailer_data['mail_subject']=$subject;
            $mailer_data['mail_message']=$body;
            $mailer_data['status']=0;
            $mailer_data['send_date']=currentDate();
            $mailer_data['is_cron']=0;
            $mailer_data['email_template_id']=$template_configurations['id_email_template'];
            //print_r($mailer_data);
            $mailer_id=$this->Customer_model->addMailer($mailer_data);
            if($mailer_data['is_cron']==0){
                //$mail_sent_status=sendmail($to_bu, $subject, $body, $from);
                $this->load->library('sendgridlibrary');
                $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                if($mail_sent_status==1)
                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
            }
        }
        //exit;
        $data['contract_review_id']=pk_encrypt($data['contract_review_id']);
        return $data['contract_review_id'];
    }

    public function autoInitiateReviewsWorkflows(){
        $is_workflow=trim($this->input->get('is_workflow'));
        //print_r(pk_decrypt('U2FsdGVkX19UaGVAMTIzNHBAoxoqOzxCVUndrDgoECo='));exit;
        if(isset($is_workflow) && $is_workflow==1){
            $get_auto_initiate_workflows = $this->User_model->check_record('calender',array('is_workflow'=>1,'auto_initiate'=>1,'plan_executed'=>1,'task_type'=>'main_task'));
            // echo $this->db->last_query();exit;
            if(count($get_auto_initiate_workflows)>0){
                foreach($get_auto_initiate_workflows as $g=>$h){
                    // if($h['provider_id']!=='' && count(explode(',',$h['provider_id']))>0){
                    //     $provider_ids_exp=explode(',',$h['provider_id']);
                    //     $provider_ids=array();
                    //     foreach($provider_ids_exp as $k=>$v){
                    //         $provider_ids[]=$v;
                    //     }
                    //     $h['provider_id']=$provider_ids;
                    // }
                    // if($h['relationship_category_id']!=='' && count(explode(',',$h['relationship_category_id']))>0){
                    //     $relationship_category_id_exp=explode(',',$h['relationship_category_id']);
                    //     $relationship_category_id=array();
                    //     foreach($relationship_category_id_exp as $k=>$v){
                    //             $relationship_category_id_chk = $v;
                    //             $relationship_category_id[]=$v;
                    //     }
                    //     $h['relationship_category_id']=$relationship_category_id;
                    // }
                    // //    print_r();exit;
                    // if($h['bussiness_unit_id']!=='' && count(explode(',',$h['bussiness_unit_id']))>0){
                    //     $business_ids_exp=explode(',',$h['bussiness_unit_id']);
                    //     $business_ids=array();
                    //     foreach($business_ids_exp as $k=>$v){           
                    //         $business_ids[]=$v;
                    //     }
                    //     $h['bussiness_unit_id']=$business_ids;
                    // }
                    //     unset($h['contract_id']);
                    //     $workflow_contracts_ids = $this->Calender_model->getContracts($h);
                    if($h['contract_id']!='' && count(explode(',',$h['contract_id']))>0){
                        $contract_ids_exp_workflow=explode(',',$h['contract_id']);
                        $workflow_contracts_ids=array();
                        foreach($contract_ids_exp_workflow as $k=>$v){    
                            $workflow_contracts_ids[]=$v;
                        }
                        $h['contract_id']=$workflow_contracts_ids;
                    }
                   
                        if(count($workflow_contracts_ids)>0){
                        foreach($workflow_contracts_ids as $w){
                            $check_workflow_status = $this->User_model->check_record('contract_workflow',array('contract_id'=>$w,'workflow_id'=>$h['workflow_id'],'workflow_status'=>'new'));//echo $this->db->last_query();exit;
                            if(count($check_workflow_status)>0){
                                foreach($check_workflow_status as $k=>$l){

                                    if($h['type']=='contract'){
                                        $workflosw_ids[]=$this->initializeReview_get(array('contract_id'=>pk_encrypt($l['contract_id']),'created_by'=>pk_encrypt($l['created_by']),'customer_id'=>pk_encrypt($h['customer_id']),'id_contract_workflow'=>pk_encrypt($l['id_contract_workflow']),'is_workflow'=>1));
                                    }
                                    if($h['type']=='project'){
                                        // $workflosw_ids[]=$this->initializeReview_get(array('contract_id'=>pk_encrypt($l['contract_id']),'created_by'=>pk_encrypt($l['created_by']),'customer_id'=>pk_encrypt($h['customer_id']),'id_contract_workflow'=>pk_encrypt($l['id_contract_workflow']),'is_workflow'=>1));
                                        $workflosw_ids[]=$this->initiateProjectMainTask_get(array('calender_id'=>pk_encrypt($h['id_calender']),'contract_id'=>pk_encrypt($l['contract_id']),'contract_review_type'=>'adhoc_workflow','created_by'=>pk_encrypt($h['created_by']),'customer_id'=>pk_encrypt($h['customer_id']),'id_contract_workflow'=>pk_encrypt($l['id_contract_workflow']),'is_workflow'=>1));
                                    }
                                    
                                }
                            }
                        }
                    }
                    if(count($workflow_ids)>0){
                        $this->User_model->update_data('calender',array('plan_executed'=>0,'updated_on'=>currentDate()),array('id_calender'=>$get_auto_initiate_workflows[$g]['id_calender']));
                    }
                }
            }
            if(count($workflow_ids)>0){
                echo 'workflows initiated successfully';
            }
            else{
                echo 'there is no auto initiate workflows';
            }
        }
        else if(isset($is_workflow) && $is_workflow==0){
            $sengle_recurrence_data=$this->User_model->check_record('calender',array('is_workflow'=>0,'plan_executed'=>1,'auto_initiate'=>1,'recurrence'=>0));
            $multiple_recurrence_data = $this->User_model->check_record('calender',array('is_workflow'=>0,'plan_executed'=>1,'auto_initiate'=>1,'month(initiate_date)'=>date('m'),'year(initiate_date)'=>date('Y'),'recurrence'=>1));
            // echo $this->db->last_query();
            $multiple_recurrence_data_2 = $this->User_model->check_record('calender',array('is_workflow'=>0,'plan_executed'=>1,'auto_initiate'=>1,'month(initiate_date)'=>date('m'),'year(initiate_date)'=>date('Y'),'recurrence'=>2));//echo $this->db->last_query();
            $multiple_recurrence_data_3 = $this->User_model->check_record('calender',array('is_workflow'=>0,'plan_executed'=>1,'auto_initiate'=>1,'month(initiate_date)'=>date('m'),'year(initiate_date)'=>date('Y'),'recurrence'=>3));
            // echo $this->db->last_query();exit;
            //Merging single,multiple recurance data
            $get_auto_initiate_reviews = array_merge($multiple_recurrence_data, $sengle_recurrence_data);
            $get_auto_initiate_reviews = array_merge($get_auto_initiate_reviews, $multiple_recurrence_data_2);
            $get_auto_initiate_reviews = array_merge($get_auto_initiate_reviews, $multiple_recurrence_data_3);
            // echo '<pre>'.print_r($get_auto_initiate_reviews);exit;
            if(count($get_auto_initiate_reviews>0)){
                foreach($get_auto_initiate_reviews as $r=>$s){
                    // if($s['provider_id']!='' && count(explode(',',$s['provider_id']))>0){
                    //     $provider_ids_exp=explode(',',$s['provider_id']);
                    //     $provider_ids=array();
                    //     foreach($provider_ids_exp as $k=>$v){
                    //         $provider_ids[]=$v;
                    //     }
                    //     $s['provider_id']=$provider_ids;
                    // }
                    // if($s['relationship_category_id']!='' && count(explode(',',$s['relationship_category_id']))>0){
                    //     $relationship_category_id_exp=explode(',',$s['relationship_category_id']);
                    //     $relationship_category_id=array();
                    //     foreach($relationship_category_id_exp as $k=>$v){
                    //              $relationship_category_id_chk = $v;
                    //             $relationship_category_id[]=$v;
                    //     }
                    //     $s['relationship_category_id']=$relationship_category_id;
                    // }
                    // if($s['bussiness_unit_id']!='' && count(explode(',',$s['bussiness_unit_id']))>0){
                    //     $business_ids_exp=explode(',',$s['bussiness_unit_id']);
                    //     $business_ids=array();
                    //     foreach($business_ids_exp as $k=>$v){    
                    //         $business_ids[]=$v;
                    //     }
                    //     $s['bussiness_unit_id']=$business_ids;
                    // }
                    // unset($s['contract_id']); 
                    
                    // $contracts_ids = $this->Calender_model->getContracts($s);

                    if($s['contract_id']!='' && count(explode(',',$s['contract_id']))>0){
                        $contract_ids_exp=explode(',',$s['contract_id']);
                        $contract_ids=array();
                        foreach($contract_ids_exp as $k=>$v){    
                            $contract_ids[]=$v;
                        }
                        $s['contract_id']=$contract_ids;
                    }
                    //print_r($contract_ids);exit;
                    if(count($contract_ids)>0){
                         foreach($contract_ids as $m=>$t){
                            //print_r($t);exit;
                            $check_review_status = $this->User_model->check_record('contract_review',array('contract_id'=>$t,'contract_review_status'=>'review in progress','is_workflow'=>0));
                            if(count($check_review_status)==0){
                                $review_ids[]=$this->initializeReview_get(array('contract_id'=>pk_encrypt($t),'created_by'=>pk_encrypt($s['created_by']),'customer_id'=>pk_encrypt($s['customer_id']),'id_contract_workflow'=>0,'is_workflow'=>0));
                              
                            }
                        }
                     }
                     
                    if(count($review_ids)>0){
                        $this->User_model->update_data('calender',array('plan_executed'=>0,'updated_on'=>currentDate()),array('id_calender'=>$get_auto_initiate_reviews[$r]['id_calender']));//echo $this->db->last_query();exit;
                    }
                }
            }
            if(count($review_ids)>0){
                echo 'reviews initiated successfully';   
            }
            else{
                echo 'there is no auto initiate reviews';
            } 
        }    
    }

    ///Update scores for old reviews
    //////
    public function updatescore(){
        $get_review_ids_query = "SELECT c.id_contract, cr.id_contract_review,cr.contract_review_status FROM contract c JOIN contract_review cr on c.id_contract=cr.contract_id";
        $get_review_ids = $this->User_model->custom_query($get_review_ids_query);
        foreach($get_review_ids as $k=>$v){
            $data['contract_review_id']=$v['id_contract_review'];
                $topic_score =  $this->Contract_model->getContractDashboard(array('contract_review_id' => $data['contract_review_id'],'provider_visibility'=>array(1,0)));
            foreach($topic_score as $t=>$s){
                $this->User_model->update_data('topic',array('topic_score'=>$s['topic_score']),array('module_id'=>$s['module_id'],'id_topic'=>$s['topic_id']));
            }
            $module_score = $this->Contract_model->getContractReviewModuleScore(array('contract_review_id' => $data['contract_review_id']));
            for ($sr = 0; $sr < count($module_score); $sr++) {
                $module_score[$sr]['score'] = getScoreByCount($module_score[$sr]);
                $this->User_model->update_data('module',array('module_score'=>$module_score[$sr]['score']),array('id_module'=>$module_score[$sr]['module_id'],'contract_review_id'=>$data['contract_review_id']));
            }
            $review_score = getScore(array_map(function ($i) { return strtolower($i['score']);}, $module_score));
            $this->User_model->update_data('contract_review',array('review_score'=>$review_score),array('id_contract_review'=>$data['contract_review_id']));
        }
        echo 'score updated successfully';
    }
    ////
    //* create new eamil template and update email template content start *//
    public function createnewemailtemplate(){
        $data=$this->input->get();
        $customer_ids=$this->User_model->custom_query('SELECT customer_id FROM email_template WHERE customer_id is NOT null GROUP BY customer_id');//get the all customerid from email templates table
        if(!empty($data['server_type'])){

            if($data['server_type']=='dev'){//for dev server
                $subject='DEV-Sourcingcockpit - Validation completed';
                $url='https://dev.sourcingcockpit.com/sprint6/';
            }
            if($data['server_type']=='test'){//for test server
                $subject='TEST-Sourcingcockpit - Validation completed';
                $url='https://test.sourcingcockpit.com/sprint6/';
            }
            if($data['server_type']=='prod'){//for production server
                $subject='Sourcingcockpit - Validation completed';
                $url='https://with.sourcingcockpit.com/app/';
            }
            ///updating the ready for validation email template content////
            $sql="UPDATE `email_template_language` SET `template_content`='<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			A new module has been prepared for you to validate :</p>\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>Provider: </strong>{provider_name}<br />\r\n            <strong>Contract name: </strong>{contract_name}<br />\r\n            <strong>Module: </strong>{module_name}<br />\r\n            <strong>Initiated by: </strong>{initiate_by}<br />\r\n             <strong>Initiated on: </strong>{initiate_on}<br />\r\n			<br />\r\n            Please engage with your validation and <strong><u>submit</u> </strong> your validated responses as soon as ready. <br /><br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=$url style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>' WHERE template_name='Contract Module Ready for Validation'";
            $update_content=$this->User_model->custom_query_insert_update($sql);
            if($update_content['affected_rows']>=0){
                echo 'email template content updated. ';
                echo "</br>";
            }
            else{
                echo 'error occured in upadating content. ';
                echo "</br>";
            }
    
            ////creating review  email template for validation complete////
            foreach($customer_ids as $k => $v){       
                // print_r();exit;  
                $check_review_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_COMPLETE','module_name'=>'Review','customer_id'=>$v['customer_id']));
                if(count($check_review_template)==0){
                    $customer_id=$v['customer_id'];

                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Review', 'VALIDATION_COMPLETE', '[\"first_name\",\"last_name\",\"logo\",\"contract_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-04-24 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')";
                    $inser_review_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_review_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Contract Module  Validation completed', '$subject', '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {name},<br />\r\n			<br />\r\n			A new module has been submitted by the validator for you to finalize :</p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>Provider: </strong>{provider_name}<br />\r\n            <strong>Contract name: </strong>{contract_name}<br />\r\n            <strong>Module: </strong>{module_name}<br />\r\n            <strong>Validated by: </strong>{validate_by}<br />\r\n             <strong>Validated on: </strong>{validate_on}<br />\r\n			<br />\r\n            Please verify the validation responses and click on <strong><u>finalize</u> </strong> to submit all responses. <br /><br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=$url style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>', '1')";
                    $inser_review_etl=$this->User_model->custom_query_insert_update($sql);
                    if($inser_review_et['affected_rows']>0 && $inser_review_etl['affected_rows']>0){
                        echo 'new review email templete created. ';
                        echo "</br>"; 
                    }
                    else{
                        echo 'erro occured in creating review email template. ';
                        echo "</br>"; 
                    }
                }
                else{
                    echo'Review email templete already exist .';
                    echo "</br>";  
                }
                ////creating work flow email template for validation complete ////
                $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_COMPLETE','module_name'=>'Workflow','customer_id'=>$v['customer_id']));
                if(count($check_workflow_template)==0){
                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Workflow', 'VALIDATION_COMPLETE', '[\"first_name\",\"last_name\",\"logo\",\"contract_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-04-24 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                    $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_workflow_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Contract Module  Validation completed', '$subject', '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {name},<br />\r\n			<br />\r\n			A new module has been submitted by the validator for you to finalize :</p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>Provider: </strong>{provider_name}<br />\r\n            <strong>Contract name: </strong>{contract_name}<br />\r\n            <strong>Module: </strong>{module_name}<br />\r\n            <strong>Validated by: </strong>{validate_by}<br />\r\n             <strong>Validated on: </strong>{validate_on}<br />\r\n			<br />\r\n            Please verify the validation responses and click on <strong><u>finalize</u> </strong> to submit all responses. <br /><br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=$url style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>', '1')";
                    $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                    if($inser_workflow_et['affected_rows']>0 && $inser_workflow_etl['affected_rows']>0){
                        echo 'new workflow email templete created. ';
                        echo "</br>"; 
                    }
                    else{
                        echo 'erro occured in creating workflow email template. ';
                        echo "</br>"; 
                    }
                }
                else{
                    echo ' workflow eamil templete  already exitst. ';
                    echo "</br>"; 
                }
            }
        }
        else{
            echo 'server type is required';
        }
    }
    //* create new eamil template and update email template content end *//
    //* updating validation status for already existing contracts start *//
    // public function updatingvalidationstatus(){
    //     $contract_review_ids=$this->User_model->check_record('contract_review',array('contract_review_status'=>'review in progress'));
    //     foreach($contract_review_ids as $ids=>$id){
    //         $modules=$this->User_model->getModuleInfo(array('contract_review_id'=>$id['id_contract_review']));print_r($modules);
    //         if(!empty($modules)){
    //             foreach($modules as $m=>$md){
    //                 if($md['module_status']==1 || $md['module_status']==0){
    //                     $no_validaton=true;
    //                 }
    //                 else{
    //                     $no_validaton=false;
    //                 break;
    //                 }
    //             }
    //             // print_r($no_validaton); exit;              
    //         }
    //     }exit;
    // }

    //* updating validation status for already existing contracts end *//



    public function updateProviderRelationships(){
        $customer_ids=$this->User_model->check_record_selected('id_customer','customer',array());
        if(!empty($customer_ids)){
            foreach($customer_ids as $k=>$v){
                $check_provider_rel_cat=$this->User_model->check_record('provider_relationship_category',array('customer_id'=>$v['id_customer']));
                if(empty($check_provider_rel_cat)){
                    $customer_id = $v['id_customer'];
                    /* updating relationship category */
                    // $relationship_category = $this->Relationship_category_model->RelationshipCategoryList(array('customer_id' => 0,'relationship_category_status' =>1));
                    $provider_relationship_category = $this->Relationship_category_model->ProviderRelationshipCategoryList(array('customer_id' => 0,'provider_relationship_category_status' =>1));
                    $relationship_category = $relationship_category['data'];
                    $provider_relationship_category = $provider_relationship_category['data'];
                    // print_r($provider_relationship_category);exit;
                    for($j=0;$j<count($provider_relationship_category);$j++)
                    {
                        $provider_inserted_id = $this->Relationship_category_model->addProviderRelationshipCategory(array(
                            'provider_relationship_category_quadrant' => $provider_relationship_category[$j]['provider_relationship_category_quadrant'],
                            'provider_relationship_category_status' => 1,
                            'parent_provider_relationship_category_id' => $provider_relationship_category[$j]['id_provider_relationship_category'],
                            'customer_id' => $customer_id,
                            'created_by' => $data['created_by'],
                            'created_on' => currentDate(),
                            'can_review'=>1
                        ));
                        $this->Relationship_category_model->addProviderRelationshipCategoryLanguage(array(
                            'provider_relationship_category_id' => $provider_inserted_id,
                            'relationship_category_name' => $provider_relationship_category[$j]['relationship_category_name'],
                            'language_id' => $provider_relationship_category[$j]['language_id']
                        ));
            
                        //Adding provider Remainder days for calender
                        // $this->User_model->insert_data('relationship_category_remainder',array('relationship_category_id'=>$inserted_id,'customer_id'=>$customer_id));
                    }
            
             
            
            
            
                    /* updating relationship classification */
                    // $relationship_classification = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => 0,'classification_status' =>1));
                    // $relationship_classification = $relationship_classification['data'];
                    /*$relationship_classification1 = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id_not' => 0,'classification_status' =>1));
                    $relationship_classification1 = $relationship_classification1['data'];
                    $relationship_classification = array_merge($relationship_classification,$relationship_classification1);*/
                    // for($s=0;$s<count($relationship_classification);$s++)
                    // {
                    //     $parent_inserted_id = $this->Relationship_category_model->addRelationshipClassification(array(
                    //         'classification_key' => $relationship_classification[$s]['classification_key'],
                    //         'classification_position' => $relationship_classification[$s]['classification_position'],
                    //         'parent_classification_id' => $relationship_classification[$s]['parent_classification_id'],
                    //         'parent_relationship_classification_id' => $relationship_classification[$s]['id_relationship_classification'],
                    //         'customer_id' => $customer_id,
                    //         'is_visible' => $relationship_classification[$s]['is_visible'],
                    //         'created_by' => $data['created_by'],
                    //         'created_on' => currentDate()
                    //     ));
                    //     $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
                    //         'relationship_classification_id' => $parent_inserted_id,
                    //         'classification_name' => $relationship_classification[$s]['classification_name'],
                    //         'language_id' => $relationship_classification[$s]['language_id']
                    //     ));
            
                    //     $relationship_classification1 = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => $relationship_classification[$s]['id_relationship_classification'],'classification_status' =>1));
                    //     $relationship_classification1 = $relationship_classification1['data'];
                    //     for($sr=0;$sr<count($relationship_classification1);$sr++)
                    //     {
                    //         $inserted_id = $this->Relationship_category_model->addRelationshipClassification(array(
                    //             'classification_key' => $relationship_classification1[$sr]['classification_key'],
                    //             'classification_position' => $relationship_classification1[$sr]['classification_position'],
                    //             'parent_classification_id' => $parent_inserted_id,
                    //             'parent_relationship_classification_id' => $relationship_classification[$s]['id_relationship_classification'],
                    //             'customer_id' => $customer_id,
                    //             'is_visible' => $relationship_classification1[$sr]['is_visible'],
                    //             'created_by' => $data['created_by'],
                    //             'created_on' => currentDate()
                    //         ));
                    //         $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
                    //             'relationship_classification_id' => $inserted_id,
                    //             'classification_name' => $relationship_classification1[$sr]['classification_name'],
                    //             'language_id' => $relationship_classification1[$sr]['language_id']
                    //         ));
                    //     }
            
            
                    // }
                    /* updating provider relationship classification */
                    $provider_relationship_classification = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => 0,'classification_status' =>1));//echo $this->db->last_query();exit;
                    $provider_relationship_classification = $provider_relationship_classification['data'];
                    for($p=0;$p<count($provider_relationship_classification);$p++)
                    {
                        $provider_parent_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                            'classification_key' => $provider_relationship_classification[$p]['classification_key'],
                            'classification_position' => $provider_relationship_classification[$p]['classification_position'],
                            'parent_classification_id' => $provider_relationship_classification[$p]['parent_classification_id'],
                            'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                            'customer_id' => $customer_id,
                            'is_visible' => $provider_relationship_classification[$p]['is_visible'],
                            'created_by' => $data['created_by'],
                            'created_on' => currentDate()
                        ));
                        $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                            'provider_relationship_classification_id' => $provider_parent_inserted_id,
                            'classification_name' => $provider_relationship_classification[$p]['classification_name'],
                            'language_id' => $provider_relationship_classification[$p]['language_id']));
                        $provider_relationship_classification1 = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],'classification_status' =>1));
                        $provider_relationship_classification1 = $provider_relationship_classification1['data'];
                        // print_r($provider_relationship_classification1);exit;
                         for($pr=0;$pr<count($provider_relationship_classification1);$pr++)
                         {
                             $provider_class_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                                 'classification_key' => $provider_relationship_classification1[$pr]['classification_key'],
                                 'classification_position' => $provider_relationship_classification1[$pr]['classification_position'],
                                 'parent_classification_id' => $provider_parent_inserted_id,
                                 'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                                 'customer_id' => $customer_id,
                                 'is_visible' => $provider_relationship_classification1[$pr]['is_visible'],
                                 'created_by' => $data['created_by'],
                                 'created_on' => currentDate()
                             ));
                             $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                                 'provider_relationship_classification_id' => $provider_class_inserted_id,
                                 'classification_name' => $provider_relationship_classification1[$pr]['classification_name'],
                                 'language_id' => $provider_relationship_classification1[$pr]['language_id']
                             ));
                         }
            
            
                    }
                }
            }
            echo 'provider relationship Category updated.';exit;
        }
    }

    public function updateProviderStakeholders(){
        // $stake_holder_lables = array('provider_id'=>$insert_id,'lable1'=>'Procurement and Sales Managers','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>$data['created_by'],'created_on' => currentDate(),'contract_id'=>0);
        $provider_ids=$this->User_model->check_record_selected('id_provider','provider',array());
        foreach($provider_ids as $k=>$v){
            $check_record_stake_holder=$this->User_model->check_record('contract_stakeholder_lables',array('provider_id'=>$v['id_provider']));
            if(empty($check_record_stake_holder)){
                $stake_holder_lables[$k] = array('provider_id'=>$v['id_provider'],'lable1'=>'Procurement and Sales Managers','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>10,'created_on' => currentDate(),'contract_id'=>0);
            }
        }
        $this->User_model->batch_insert('contract_stakeholder_lables',$stake_holder_lables);
        echo 'ProviderStakeholders Updated';exit;

    }
    public function updateProviderrelationshipstoexitstproviders(){
        $prvider_ids=$this->User_model->custom_query('SELECT id_provider FROM provider  WHERE category_id is NULL');
        foreach($prvider_ids as $k=>$v){

            $provider_relationship_category_id=$this->User_model->custom_query('SELECT provider_relationship_category_id FROM provider_relationship_category_language WHERE relationship_category_name="Category Provider" ORDER BY id_provider_relationship_category_language ASC LIMIT 1');
            $this->User_model->update_data('provider',array('category_id'=>$provider_relationship_category_id[0]['provider_relationship_category_id']),array('id_provider'=>$v['id_provider']));
        }
        echo 'updateProviderrelationships Categories.';exit;
    }
    public function sprint65quries()
    {
        $query="ALTER TABLE `contract_review_action_item` ADD COLUMN `reference_type`  varchar(255) NULL AFTER `provider_id`;";
        $this->db->query($query);
        $query="UPDATE contract_review_action_item crai1 SET crai1.reference_type='provider' WHERE crai1.id_contract_review_action_item IN(SELECT GROUP_CONCAT(id_contract_review_action_item)
        WHERE provider_id>0);";
        $this->db->query($query);
        $query="UPDATE contract_review_action_item crai1 SET crai1.reference_type='contract' WHERE crai1.id_contract_review_action_item IN(SELECT GROUP_CONCAT(id_contract_review_action_item)
        WHERE contract_id>0 AND module_id=0 AND topic_id=0 AND question_id=0);";
        $this->db->query($query);
        $query="UPDATE contract_review_action_item crai1 SET crai1.reference_type='topic' WHERE crai1.id_contract_review_action_item IN(SELECT GROUP_CONCAT(id_contract_review_action_item)
        WHERE contract_id>0 AND module_id>0 AND topic_id>0 AND question_id=0);";
        $this->db->query($query);
        $query="UPDATE contract_review_action_item crai1 SET crai1.reference_type='question' WHERE crai1.id_contract_review_action_item IN(SELECT GROUP_CONCAT(id_contract_review_action_item)
        WHERE contract_id>0 AND module_id>0 AND topic_id>0 AND question_id>0);";
        $this->db->query($query);
        $this->db->query($query);
        $query="ALTER TABLE `contract`
        MODIFY COLUMN `provider_name`  int(11) NULL DEFAULT NULL AFTER `business_unit_id`;";
        $this->db->query($query);
        echo 'quries updated sucessfully';exit;
    }
    public function updateContractID(){
       $get_customers= $this->User_model->check_record('customer',array());
       if(!empty($get_customers)){
           foreach($get_customers as $k=>$v){
               $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$v['id_customer']));
            //    echo $this->db->last_query();exit;
            if(!empty($get_contracts)){
                foreach($get_contracts as $c=>$ct){
                    $contract_unique_id='C'.str_pad($c+1, 7, '0', STR_PAD_LEFT);
                    $this->User_model->update_data('contract',array('contract_unique_id'=>$contract_unique_id),array('id_contract'=>$ct['id_contract']));
                   }
               }
           }
           echo 'contract ids are updated Successfully';
       }
    }
    public function updateProviderID(){
        $get_customer_ids=$this->User_model->custom_query('SELECT customer_id FROM provider GROUP BY customer_id');
        if(!empty($get_customer_ids)){
            foreach($get_customer_ids as $k=>$v){
                $get_providers=$this->User_model->check_record_selected('id_provider','provider',array('customer_id'=>$v['customer_id']));
                foreach($get_providers as $p=>$pr){
                    $provider_unique_id='PR'.str_pad($p+1, 7, '0', STR_PAD_LEFT);
                    $this->User_model->update_data('provider',array('unique_id'=>$provider_unique_id),array('id_provider'=>$pr['id_provider']));
                }
            }
            echo 'Provder ids are updated Successfully';

        }
    }
    public function dumpProjecttaskEmailtemplates(){
        
            /******* for PROJECT_TASK_INITIATE  start *******/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_INITIATE','module_name'=>'Project','customer_id'=>0));
            // echo $this->db->last_query();exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_INITIATE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-08 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Project Task Initiate', 'SourcingCockpit - Task Initiate', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The self-assessment for project <strong>{project_name}</strong> has been executed by {project_task_executed_user_name} on {project_task_created_date}.<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
            }

            /******* for PROJECT_TASK_INITIATE  end *******/

            /******* for PROJECT_TASK_FINALIZE  start *******/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_FINALIZE','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_FINALIZE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Project Task finalize', 'SourcingCockpit - Task Finalized', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The self-assessment for project <strong>{project_name}</strong> has been finalized by {project_task_finalized_user_name} on {project_task_finalized_date}.<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
            }
            /*** PROJECT_TASK_ASSIGN_MODULE  STARTS***/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ASSIGN_MODULE','module_name'=>'Project','customer_id'=>0));
            //  echo ''.$this->db->last_query();exit;
             if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ASSIGN_MODULE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Project Task Module Assign', 'SourcingCockpit - Module Assigned', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following module for the self-assessment of project <strong>{project_name}</strong> has been assigned to <strong>{project_workflow_assigned_module_user_name}</strong>:</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>{module_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
            }
            /*** PROJECT_TASK_ASSIGN_MODULE  ENDS***/

            /*** PROJECT_WORKFLOW_ACTION_ITEM_CREATION STARTS */
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_WORKFLOW_ACTION_ITEM_CREATION','module_name'=>'Project','customer_id'=>0));
            // echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_WORKFLOW_ACTION_ITEM_CREATION', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                //echo ''.$this->db->last_query(); exit;
                //print_r($inser_workflow_et); exit;
                $id=$inser_workflow_et['last_inserted_id'];
                //print_r($id); exit;
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`, `language_id`) VALUES ($id,'Project Task Action Item Creation', 'SourcingCockpit - New Action Item', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following action item was created:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>Created By</strong>: {action_item_created_user_name}<br />\r\n			<strong>Created On</strong>: {action_item_created_date}<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n	', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
            }

            /*** PROJECT_TASK_ACTION_ITEM_COMMENT  STARTS**/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ACTION_ITEM_COMMENT','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ACTION_ITEM_COMMENT', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`, `template_content`,`language_id`) 
                VALUES ($id,'Project Task Action Item Comment', 'SourcingCockpit - New Comment on Action Item', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following action item was updated with comments:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}<br />\r\n			<br />\r\n			<em><b>New comment</b>: {action_item_comment}</em><br />\r\n			<b>Comment added by</b>: {action_item_comment_user_name}<br />\r\n			<strong>Comment added on:</strong> {action_item_comment_date}<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //echo ''.$this->db->last_query(); exit;
                //print_r( $inser_workflow_etl); exit;
            }
            /*** PROJECT_TASK_ACTION_ITEM_COMMENT  ENDS**/

            /** PROJECT_TASK_ACTION_ITEM_FINISH STARTS */
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ACTION_ITEM_FINISH','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ACTION_ITEM_FINISH', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,  `template_content`,`language_id`) 
                VALUES ($id,'Project Task Action Item Completed', 'SourcingCockpit - Action Item Completed', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following action item was completed:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}<br />\r\n			<br />\r\n			<b>Completed by</b>: {action_item_finish_user_name}<br />\r\n			<strong>Completed on</strong>: {action_item_finish_date}<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //echo ''.$this->db->last_query(); exit;
                //print_r( $inser_workflow_etl); exit;
            }
             /** PROJECT_TASK_ACTION_ITEM_FINISH Ends */

               /** PROJECT_TASK_DISCUSSION_INITIATE STARTS */

               $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_DISCUSSION_INITIATE','module_name'=>'Project','customer_id'=>0));
               //echo ''.$this->db->last_query(); exit;
               if(count($check_workflow_template)==0){
                  $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_DISCUSSION_INITIATE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                  $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                  $id=$inser_workflow_et['last_inserted_id'];
                  $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                  VALUES ($id,'Project Task Discussion initiated', 'SourcingCockpit - Discussion initiated', 
                  '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			A new discussion has been executed :<br />\r\n			<b>Project name</b>: {project_name}<br />\r\n			<b>Module</b>: {project_workflow_module_name}<br />\r\n			<b>executed by</b>: {discussion_executed_user_name}<br />\r\n			<strong>executed on</strong>: {discussion_executed_date}<br />\r\n			<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                  //echo ''.$this->db->last_query(); exit;
                  $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                  //print_r( $inser_workflow_etl); exit;
              }
               /** PROJECT_TASK_DISCUSSION_INITIATE ENDS */
        
            /** PROJECT_TASK_DISCUSSION_CLOSE STARTS */
             $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_DISCUSSION_CLOSE','module_name'=>'Project','customer_id'=>0));
             //echo ''.$this->db->last_query(); exit;
             if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_DISCUSSION_CLOSE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'Project Task Discussion Close', 'SourcingCockpit - Discussion Closed', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following discussion has been closed:<br />\r\n			<b>Project name</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Closed by</b>: {discussion_closed_user_name}<br />\r\n			<strong>Closed on</strong>: {discussion_closed_date}<br />\r\n			<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
            }
              
             /** PROJECT_TASK_REMINDER1  STARTS*/
             $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_REMINDER1','module_name'=>'Project','customer_id'=>0));
             //echo ''.$this->db->last_query(); exit;
             if(count($check_workflow_template)==0){
               $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_REMINDER1', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
               $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
               $id=$inser_workflow_et['last_inserted_id'];
               $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
               VALUES ($id,'Project Task Invitation', 'SourcingCockpit - Invitation to execute project task', 
               '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following discussion has been closed:<br />\r\n			<b>Project name</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Closed by</b>: {discussion_closed_user_name}<br />\r\n			<strong>Closed on</strong>: {discussion_closed_date}<br />\r\n			<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
               //echo ''.$this->db->last_query(); exit;
               $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
               //print_r( $inser_workflow_etl); exit;
            }
             /** PROJECT_TASK_REMINDER1  ENDS*/

            
           /** PROJECT_TASK_REMINDER2 STARTS */
           $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_REMINDER2','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_REMINDER2', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'Project Task Reminder 1', 'SourcingCockpit - Invitation to execute project task- reminder', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			<u><b>Reminder</b></u>:<br />\r\n			You are hereby invited to execute the self-assessment for the following project(s):</span></p>\r\n\r\n			<p><br />\r\n			<span style=\"font-size: 110%;color:#74767A;\"><b>{projects}</b><br />\r\n			<br />\r\n			Please make sure you complete the task before the task deadline.<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
             }
           /** PROJECT_TASK_REMINDER2 ENDS */

           /*** PROJECT_TASK_REMINDER3  STARTS*/
           $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_REMINDER3','module_name'=>'Project','customer_id'=>0));
           //echo ''.$this->db->last_query(); exit;
           if(count($check_workflow_template)==0){
               $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_REMINDER3', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
               $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
               $id=$inser_workflow_et['last_inserted_id'];
               $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
               VALUES ($id,'Project Task Reminder 2', 'SourcingCockpit - Invitation to execute project task- reminder', 
               '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			<b><u>Reminder:</u></b><br />\r\n			You are hereby invited to execute the self-assessment for the following project(s):</span></p>\r\n\r\n			<p><br />\r\n			<span style=\"font-size: 110%;color:#74767A;\"><b>{projects}</b><br />\r\n			<br />\r\n			Please make sure you complete the task before the task deadline.<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
               //echo ''.$this->db->last_query(); exit;
               $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
               //print_r( $inser_workflow_etl); exit;
            }

           /*** PROJECT_TASK_REMINDER3  ENDS*/

           /** PROJECT_TASK_DISCUSSION_UPDATE STARTS */
           $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_DISCUSSION_UPDATE','module_name'=>'Project','customer_id'=>0));
           //echo ''.$this->db->last_query(); exit;
           if(count($check_workflow_template)==0){
            $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_DISCUSSION_UPDATE', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
            $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
            $id=$inser_workflow_et['last_inserted_id'];
            $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
            VALUES ($id,'Project Task Discussion Update', 'SourcingCockpit - Discussion Updated', 
            '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following discussion has been updated:<br />\r\n			<b>Project name</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Updated by</b>: {discussion_updated_user_name}<br />\r\n			<strong>Updated On</strong>: {discussion_updated_date}<br />\r\n			<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
            //echo ''.$this->db->last_query(); exit;
            $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
            //print_r( $inser_workflow_etl); exit;
         }
            /** PROJECT_TASK_DISCUSSION_UPDATE ENDS */

            //***PROJECT_TASK_ACTION_ITEM_CREATION_EXTERNAL_USER STARTS***/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ACTION_ITEM_CREATION_EXTERNAL_USER','module_name'=>'Project','customer_id'=>0));
           //echo ''.$this->db->last_query(); exit;
           if(count($check_workflow_template)==0){
            $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ACTION_ITEM_CREATION_EXTERNAL_USER', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
            $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
            $id=$inser_workflow_et['last_inserted_id'];
            $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
            VALUES ($id,'Project Task Action Item Creation External User', 'SourcingCockpit - New Action Item', 
            '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} ,<br />\r\n			<br />\r\n			The following action item was created:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>Created By</strong>: {action_item_created_user_name}<br />\r\n			<strong>Created On</strong>: {action_item_created_date}<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
            //echo ''.$this->db->last_query(); exit;
            $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
            //print_r( $inser_workflow_etl); exit;
         }
            //***PROJECT_TASK_ACTION_ITEM_CREATION_EXTERNAL_USER ENDS***/

            //PROJECT_TASK_ACTION_ITEM_COMMENT_EXTERNAL_USER STARTS//
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ACTION_ITEM_COMMENT_EXTERNAL_USER','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ACTION_ITEM_COMMENT_EXTERNAL_USER', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'Project Task Action Item Comment External User', 'SourcingCockpit - New Comment on Action Item', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} ,<br />\r\n			<br />\r\n			The following action item was updated with comments:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}<br />\r\n			<br />\r\n			<em><b>New comment</b>: {action_item_comment}</em><br />\r\n			<b>Comment added by</b>: {action_item_comment_user_name}<br />\r\n			<strong>Comment added on:</strong> {action_item_comment_date}<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
             }

            //PROJECT_TASK_ACTION_ITEM_COMMENT_EXTERNAL_USER ENDS//

            //PROJECT_TASK_ACTION_ITEM_FINISH_EXTERNAL_USER STARTS**//
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_TASK_ACTION_ITEM_FINISH_EXTERNAL_USER','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_TASK_ACTION_ITEM_FINISH_EXTERNAL_USER', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'Project Task Action Item Completed External User', 'SourcingCockpit - Action Item Completed', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} ,<br />\r\n			<br />\r\n			The following action item was completed:<br />\r\n			<b>Action Item Details</b>: {action_item_name}<br />\r\n			<b>Action Item Description</b>: {action_item_description}<br />\r\n			<b>Responsible User</b>: {action_item_responsible_user}<br />\r\n			<b>Due Date</b>: {action_item_due_date}</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><b>Project</b>: {project_name}<br />\r\n			<b>Module</b>: {project_task_module_name}<br />\r\n			<b>Topic</b>: {project_task_topic_name}<br />\r\n			<br />\r\n			<em><b>Closing comments</b>: {action_item_comment}</em><br />\r\n			<b>Completed by</b>: {action_item_finish_user_name}<br />\r\n			<strong>Completed on</strong>: {action_item_finish_date}<br />\r\n			<br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
             }
            //PROJECT_TASK_ACTION_ITEM_FINISH_EXTERNAL_USER ENDS**//

            //VALIDATION_READY  STARTS**/
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_READY','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'VALIDATION_READY', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'Project Module Ready for Validation', 'SourcingCockpit - Ready for Validation', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following module for the self-assessment of project <strong>{project_name}</strong> is ready to validate:</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>{module_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
             }

            //VALIDATION_READY  ENDS**/


            //Project Creation Starts //
            $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'PROJECT_CREATION','module_name'=>'Project','customer_id'=>0));
            //echo ''.$this->db->last_query(); exit;
            if(count($check_workflow_template)==0){
                $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES (0,'Project', 'PROJECT_CREATION', '[\"first_name\",\"last_name\",\"logo\",\"Project_name\",\"module_name\",\"validate_by\",\"validate_on\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                $id=$inser_workflow_et['last_inserted_id'];
                $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                VALUES ($id,'project Creation', 'Sourcing Cockpit - New project', 
                '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background: rgb(242, 242, 242); margin: 0px auto; padding: 5px; border-collapse: collapse; max-width: 720px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding: 10px 40px;\">\r\n			<p style=\"margin: 0px; color: rgb(28, 137, 199); text-decoration: none;\"><span style=\"font-size: 21px;\">SOURCING COCKPIT</span></p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 0px 40px;\">\r\n			<hr size=\"1\" style=\"margin: 0px; border-top-color: rgb(226, 226, 226); border-top-width: 1px; border-top-style: solid;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 0px 40px; font-size: 12px;\">&nbsp;\r\n			<p><span style=\"color: rgb(116, 118, 122); font-size: 110%;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following new project has been created by {project_owner_name} &amp; was assigned to {project_assigned_to_user_names} on {project_created_date}:</span></p>\r\n\r\n			<p><span style=\"color: rgb(116, 118, 122); font-size: 110%;\"><strong>{project_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color: rgb(28, 137, 199); font-size: 14px; font-style: italic;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"color: rgb(116, 118, 122); font-style: italic;\">&nbsp;</p>\r\n			</td>\r\n		</tr>\r\n        \r\n		<tr>\r\n			<td colspan=\"2\" style=\"background: rgb(231, 232, 231); padding: 10px 40px;\">\r\n			<p style=\"margin: 0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"padding: 10px 0px; width: 55px;\" /></p>\r\n\r\n			<p style=\"color: rgb(117, 117, 117); line-height: 14px; font-size: 11px; font-style: italic; float: left; display: inline-block;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                //echo ''.$this->db->last_query(); exit;
                $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                //print_r( $inser_workflow_etl); exit;
             }
            //project Creation ends//
        }
        public function encrypt_decrypt(){
            $data = $this->input->get();
            if($_SERVER['SERVER_NAME']  == 'localhost')
            {
                if(!empty($data['type']) && $data['type']=='e'){
                    //print_r(config_crypt($data['string'],$data['type']));exit;
                      print_r(pk_encrypt($data['string']));exit;
                }
                if(!empty($data['type']) && $data['type']=='d'){
                    // print_r(config_crypt($data['string'],$data['type']));exit;
                    print_r(pk_decrypt($data['string']));exit;
                }
            }
        }
        public function dumpProjectemailtemplaestoexitscust()
        {
            $get_customers=$this->User_model->check_record('customer',array());
            // print_r($get_customers);exit;
            foreach($get_customers as $k=>$v)
            {
                $email_template = $this->Customer_model->EmailTemplateList(array('customer_id' => 0,'language_id' =>1,'status'=>'0,1','module_name'=>'Project'));
                $email_template = $email_template['data'];
        
                for($s=0;$s<count($email_template);$s++)
                {
                    $inserted_id = $this->Customer_model->addEmailTemplate(array(
                        'module_name' => $email_template[$s]['module_name'],
                        'module_key' => $email_template[$s]['module_key'],
                        'wildcards' => $email_template[$s]['wildcards'],
                        'email_from_name' => $email_template[$s]['email_from_name'],
                        'email_from' => $email_template[$s]['email_from'],
                        'status' => $email_template[$s]['status'],
                        'parent_email_template_id' => $email_template[$s]['id_email_template'],
                        'customer_id' => $v['id_customer'],
                        'created_by' => $data['created_by'],
                        'recipients' => $email_template[$s]['recipients'],
                        'created_on' => currentDate()
                    ));
        
                    $this->Customer_model->addEmailTemplateLanguage(array(
                        'email_template_id' => $inserted_id,
                        'template_name' => $email_template[$s]['template_name'],
                        'template_subject' => $email_template[$s]['template_subject'],
                        'template_content' => $email_template[$s]['template_content'],
                        'language_id' => $email_template[$s]['language_id']
                    ));
                }
            }
            echo 'Project templates dumped to existing cutomers successfully';
        }
        public function initiateProjectTask_get($data=null)
        {
            if($data==null){
                $data = $this->input->get();
            }
            // echo 'data'.'<pre>';print_r($data);exit;
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
    
            $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                // $this->response($result, REST_Controller::HTTP_OK);
                print_r($result);exit;
            }
            if(isset($data['contract_id'])) {
                $data['contract_id'] = pk_decrypt($data['contract_id']);
                // // if(!in_array($data['contract_id'],$this->session_user_contracts)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                //     // $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if(isset($data['customer_id'])) {
                $data['customer_id'] = pk_decrypt($data['customer_id']);
                // if($this->session_user_info->customer_id!=$data['customer_id']){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if(isset($data['created_by'])) {
                $data['created_by'] = pk_decrypt($data['created_by']);
                // if($data['created_by']!=$this->session_user_id){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
                // if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if(isset($data['calender_id']) && $data['calender_id'] != null && $data['calender_id'] != '') {
                $data['calender_id'] = pk_decrypt($data['calender_id']);
            }
            
            //echo '<pre>'.print_r($data);exit;
            if(isset($data['id_contract_workflow']) && $data['id_contract_workflow'] !='0') {
                $data['id_contract_workflow'] = pk_decrypt($data['id_contract_workflow']);
                $check_contract_review = $this->Contract_model->getContractReview(array(
                    'contract_id' => $data['contract_id'],
                    'status' => 'workflow in progress',
                    'contract_workflow_id' => $data['id_contract_workflow'],
                    'is_workflow' => 1
                ));
                // echo '<pre>'.print_r($data);exit;
                // echo '<pre>'.$this->db->last_query();exit;
                $msg = $this->lang->line('workflow_initiate');
                //Updating contract workflow
                // print_r($data['id_contract_workflow']);exit;
                $this->User_model->update_data('contract_workflow',array('workflow_status'=>'workflow in progress'),array('id_contract_workflow'=>$data['id_contract_workflow']));
            }else{
                $check_contract_review = $this->Contract_model->getContractReview(array(
                    'contract_id' => $data['contract_id'],
                    'status' => 'review in progress',
                    'contract_workflow_id' => 0,
                    'is_workflow' => 0
                ));
                $msg = $this->lang->line('review_initiate');
            }
            if(!empty($check_contract_review)){
                $this->Contract_model->updateContract(array(
                    'id_contract' => $data['contract_id'],
                    'contract_status' => 'review in progress', //pending review from 2 time
                    'updated_by' => $data['created_by'],
                    'updated_on' => currentDate(),
                    'reminder_type' => NULL,
                    'reminder_sent_on' => NULL,
                    'reminder_date1' => NULL,
                    'reminder_date2' => NULL,
                    'reminder_date3' => NULL
                ));
    
                $result = array('status'=>TRUE, 'message' => $msg, 'data'=>pk_encrypt($check_contract_review[0]['id_contract_review']));
                $this->response($result, REST_Controller::HTTP_OK); exit;
            }
            //echo '<pre>'.$this->db->last_query();exit;
            $contract_update_data = array(
                'id_contract' => $data['contract_id'],
                'updated_by' => $data['created_by'],
                'updated_on' => currentDate(),
                'reminder_type' => NULL,
                'reminder_sent_on' => NULL,
                'reminder_date1' => NULL,
                'reminder_date2' => NULL,
                'reminder_date3' => NULL
            );
            if($data['is_workflow'] == 0){
                $contract_update_data['contract_status'] = 'review in progress';
            }
            $this->Contract_model->updateContract($contract_update_data);
            if(isset($data['is_workflow']) && $data['is_workflow'] == 1 && isset($data['id_contract_workflow']))
                $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'is_workflow'=>1,'contract_review_status'=>'finished','order' => 'DESC'));
            else
                $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'contract_review_status'=>'finished','order' => 'DESC'));
                // echo '<pre>'.$this->db->last_query();
            if(!empty($review) && isset($review[0]['id_contract_review']) && $review[0]['id_contract_review']!='' && $review[0]['id_contract_review']!=0) {
                $previous_review_id=$review[0]['id_contract_review'];
            }
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
            $contract_review_data = array(
                'contract_id' => $data['contract_id'],
                'contract_review_due_date' => currentDate(),
                'contract_review_type' => isset($data['contract_review_type'])?$data['contract_review_type']:'',
                'created_by' => $data['created_by'],
                'created_on' => currentDate(),
                'relationship_category_id' =>$contract_info[0]['relationship_category_id'],
                'calender_id' =>isset($data['calender_id'])?$data['calender_id']:0
            );
            if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
                if($data['is_workflow'] == 1){
                    $contract_review_data['is_workflow'] = $data['is_workflow'];
                    $contract_review_data['contract_workflow_id'] = $data['id_contract_workflow'];
                    $contract_review_data['contract_review_status'] = 'workflow in progress';
                }
            }
    
            $data['contract_review_id'] = $this->Contract_model->addContractReview($contract_review_data);
    
          
            $data['parent_relationship_category_id']=$contract_info[0]['relationship_category_id'];
            $data['template_id'] = $contract_info[0]['template_id'];
    
            if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
                if($data['is_workflow'] == 1){
                    $contract_workflow = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
                    if($contract_workflow[0]['workflow_id'] > 0)
                        $data['template_id'] = $contract_workflow[0]['workflow_id']; // Workflow_id == selected workflow in calendar workflow planning.
                }
            }
            if(!empty($contract_info[0]['type']) && $contract_info[0]['type']=='project'){
                $data['parent_relationship_category_id']=0;
            }
            $this->Contract_model->cloneModuleTopicQuestionForContractNew($data);
            /** */
            ///////Activating OR Deactivating the modules Based on Stored Modules Settings: Starts
            $stored_modules = $this->User_model->check_record('stored_modules',array('contract_id'=>$data['contract_id']));
            $contract_modules = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id']));
    
            foreach($stored_modules as $sk => $sv){
    
                foreach($contract_modules as $ck => $cv){
                    // if($sv['parent_module_id'] == $cv['parent_module_id'] && $data['is_workflow']==0)
                    //     $this->User_model->update_data('stored_modules',array('module_id'=>$cv['id_module']),array('parent_module_id'=>$cv['parent_module_id'],'contract_id'=>$data['contract_id']));
    
                    if($sv['parent_module_id'] == $cv['parent_module_id'] && !(int)$sv['activate_in_next_review'] && $data['is_workflow'] == 0){
                        //Updating the Modulestatus to 0 if that is set to activate_in_next_review=0 in stored modules 
                        $this->User_model->update_data('module',array('module_status'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_review_id'=>$data['contract_review_id']));
                        $cv['module_status'] = 0;
                    }else if($sv['parent_module_id'] == $cv['parent_module_id'] && (int)$sv['activate_in_next_review']){
                        if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                            $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'contract_id'=>$data['contract_id']));
                        }
                        else{
                            $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_id'=>$data['contract_id']));
                        }
                        if($cv['module_status'] == 1){
                            //Deleting the question_answers of current review to null if the module is static and activated in next review
                            $cqr_sql = "DELETE FROM contract_question_review WHERE question_id in(SELECT id_question FROM question q LEFT JOIN topic t ON q.topic_id = t.id_topic WHERE t.module_id = ".$cv['id_module']." )";
                            $this->db->query($cqr_sql);
                        }
                    }
                }
            }
    
            //Updating Score for stored_modeules Starts
            if(isset($data['is_workflow']) && $data['is_workflow']==0){
                $previous_contract_review_id = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'order' => 'DESC','contract_review_status'=>'finished'));//echo $this->db->last_query();exit;
                $get_current_review_stored_module = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id'],'module_status'=>0,'static'=>1));
                if(count($get_current_review_stored_module)>0){
                    foreach($get_current_review_stored_module as $module){
                        $get_previous_module=$this->User_model->check_record('module',array('contract_review_id'=>$previous_contract_review_id[0]['id_contract_review'],'parent_module_id'=>$module['parent_module_id']));
                        $this->User_model->update_data('module',array('module_score'=>$get_previous_module[0]['module_score']),array('id_module'=>$module['id_module'],'contract_review_id'=>$data['contract_review_id']));
                        $get_current_topic_details=$this->User_model->check_record('topic',array('module_id'=>$module['id_module']));
                        foreach($get_current_topic_details as $topic){
                            $get_previous_topic=$this->User_model->check_record('topic',array('module_id'=>$get_previous_module[0]['id_module'],'parent_topic_id'=>$topic['parent_topic_id']));
                            $this->User_model->update_data('topic',array('topic_score'=>$get_previous_topic[0]['topic_score']),array('id_topic'=>$topic['id_topic']));
                        }
                    }
                }
            }
            //Updating Score for stored_modeules Ends
    
    
            ///////Activating OR Deactivating the modules Based on Stored Modules Settings: Ends
            /** */
    
            $bu_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
            $contract_review_info = $this->Contract_model->getContractReview(array('id_contract_review' => $data['contract_review_id']));
            $cust_admin_info = $this->User_model->getUserInfo(array('customer_id' => $data['customer_id'],'user_role_id' =>2,'user_status'=>1));
            $contract_review_user = $this->User_model->getUserInfo(array('user_id' => $contract_review_info[0]['created_by']));
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            
            if(isset($previous_review_id)){
                $migrate['old_contract_review_id']=$previous_review_id;
                $migrate['new_contract_review_id']=$data['contract_review_id'];
                $migrate['created_by']=$data['created_by'];
                $migrate_modules=$this->Contract_model->migrateContractUsersFromOldReview($migrate);
                $migrate_modules_array=array();
                foreach($migrate_modules as $km=>$vm){
                    $migrate_modules_array[]=$vm['user_id'];
                }
                $migrate_modules_array=array_values(array_unique($migrate_modules_array));
                $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
               
                //print_r($data); exit;
                if($data['is_workflow'] == 1)
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_INITIATE'));
                
                if($template_configurations_parent['total_records']>0) {
                    foreach ($migrate_modules_array as $k => $v) {
                        //$module_info = $this->Module_model->getModuleName(array('language_id' => 1, 'module_id' => $v['id_module']));
                        $To = $this->User_model->getUserInfo(array('user_id' => $v,'user_status'=>1));
                        //sending mail to bu owner
                        if ($template_configurations_parent['total_records'] > 0 && !empty($To)) {
                            $template_configurations = $template_configurations_parent['data'][0];
                            $wildcards = $template_configurations['wildcards'];
                            $wildcards_replaces = array();
                            $wildcards_replaces['first_name'] = $To->first_name;
                            $wildcards_replaces['last_name'] = $To->last_name;
                            $wildcards_replaces['project_name'] = $contract_info[0]['contract_name'];
                        
                            if($data['is_workflow']==1){
                                $wildcards_replaces['project_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                                $wildcards_replaces['project_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
                            }
                            
                            $wildcards_replaces['logo'] = $customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                            $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to = $To->email;
                            $to_name = $To->first_name . ' ' . $To->last_name;
                            $mailer_data['mail_from_name'] = $from_name;
                            $mailer_data['mail_to_name'] = $to_name;
                            $mailer_data['mail_to_user_id'] = $To->id_user;
                            $mailer_data['mail_from'] = $from;
                            $mailer_data['mail_to'] = $to;
                            $mailer_data['mail_subject'] = $subject;
                            $mailer_data['mail_message'] = $body;
                            $mailer_data['status'] = 0;
                            $mailer_data['send_date'] = currentDate();
                            $mailer_data['is_cron'] = 0;
                            $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                            //print_r($mailer_data);
                            $mailer_id = $this->Customer_model->addMailer($mailer_data);
                            //sending mail to bu owner
                            if ($mailer_data['is_cron'] == 0) {
                                $this->load->library('sendgridlibrary');
                                $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                if ($mail_sent_status == 1)
                                    $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                            }
    
                        }
                    }
                }
    
    
            }
    
          
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            //sending mail to bu owner
            if($data['is_workflow'] == 1)
                $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_INITIATE'));
            
            if($template_configurations_parent['total_records']>0 && !empty($cust_admin_info)){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$cust_admin_info->first_name;
                $wildcards_replaces['last_name']=$cust_admin_info->last_name;
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$cust_admin_info->email;
                $to_name=$cust_admin_info->first_name.' '.$cust_admin_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$cust_admin_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                //print_r($mailer_data);
                //print_r( $wildcards_replaces); exit;
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                //sending mail to bu owner
                if($mailer_data['is_cron']==0) {
                  
                }
    
            }
            if(isset($contract_info[0]['delegate_id'])){
                $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                
                if($template_configurations_parent['total_records']>0 && !empty(($delegate_info))){
                    $template_configurations=$template_configurations_parent['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$delegate_info->first_name;
                    $wildcards_replaces['last_name']=$delegate_info->last_name;
                    $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                    if($data['is_workflow']==1){
                        $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                        $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                    }
                 
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject=$template_configurations['template_subject'];
                  
                    $from_name=$template_configurations['email_from_name'];
                    $from=$template_configurations['email_from'];
                    $to=$delegate_info->email;
                    $to_name=$delegate_info->first_name.' '.$delegate_info->last_name;
                    $mailer_data['mail_from_name']=$from_name;
                    $mailer_data['mail_to_name']=$to_name;
                    $mailer_data['mail_to_user_id']=$delegate_info->id_user;
                    $mailer_data['mail_from']=$from;
                    $mailer_data['mail_to']=$to;
                    $mailer_data['mail_subject']=$subject;
                    $mailer_data['mail_message']=$body;
                    $mailer_data['status']=0;
                    $mailer_data['send_date']=currentDate();
                    $mailer_data['is_cron']=0;
                    $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                    //print_r($mailer_data);
                    $mailer_id=$this->Customer_model->addMailer($mailer_data);
    
                    //sending mail to delegate
                    if($mailer_data['is_cron']==0){
                       
                    }
    
                }
            }
            if($template_configurations_parent['total_records']>0 && !empty($bu_info)){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$bu_info->first_name;
                $wildcards_replaces['last_name']=$bu_info->last_name;
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
               
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                }
              
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
              
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$bu_info->email;
                $to_name=$bu_info->first_name.' '.$bu_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$bu_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                //print_r($mailer_data);
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                if($mailer_data['is_cron']==0){
                   
                }
            }
            //exit;
            $get_workflow_data=$this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
            if($get_workflow_data[0]['parent_id']>0){
                $get_module_data=$this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id']));
                $contract_user_array=array(
                    'contract_id'=>$get_workflow_data[0]['contract_id'],
                    'user_id'=>$get_workflow_data[0]['provider_id'],
                    'status'=>1,
                    'contract_review_id'=>$data['contract_review_id'],
                    'module_id'=>$get_module_data[0]['id_module'],
                    'created_on'=>currentDate()
                );
                $this->User_model->insert_data('contract_user',$contract_user_array);
            }
            $data['contract_review_id']=pk_encrypt($data['contract_review_id']);
            return pk_encrypt($data['contract_review_id']);
            // $result = array('status'=>TRUE, 'message' => $msg, 'data'=>$data['contract_review_id']);
            // $this->response($result, REST_Controller::HTTP_OK);
        }
        public function autoInitiateProjectSubtasks(){
            $get_subtasks=$this->Project_model->getsubtask();
            // print_r($get_subtasks);exit;
            foreach($get_subtasks as $k=>$v){
                // print_r(json_encode($v,JSON_PRETTY_PRINT));exit;
                $SubtaskId=$this->initiateProjectTask_get(array('calender_id'=>pk_encrypt($v['id_calender']),'contract_id'=>pk_encrypt($v['contract_id']),'contract_review_type'=>'adhoc_workflow','created_by'=>pk_encrypt($v['created_by']),'customer_id'=>pk_encrypt($v['customer_id']),'id_contract_workflow'=>pk_encrypt($v['id_contract_workflow']),'is_workflow'=>1));
                if(!empty($SubtaskId)){
                    $this->User_model->update_data('calender',array('plan_executed'=>0),array('id_calender'=>$v['id_calender']));
                    $SubtaskIds[]=$SubtaskId;
                }
            }
            if(!empty($SubtaskIds)){
                echo 'SubTask initiated Successfully';
            }
            else{
                echo 'no subtasks to initiate';
            }
        }
        public function initiateProjectMainTask_get($data=null)
        {

            // $data = $this->input->get();
            // echo 'data'.'<pre>';print_r($data);exit;
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                print_r($result);exit;
                // $this->response($result, REST_Controller::HTTP_OK);
            }
    
            $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                // $this->response($result, REST_Controller::HTTP_OK);
                print_r($result);exit;

            }
            if(isset($data['contract_id'])) {
                $data['contract_id'] = pk_decrypt($data['contract_id']);
            }
            if(isset($data['customer_id'])) {
                $data['customer_id'] = pk_decrypt($data['customer_id']);
            }
            if(isset($data['created_by'])) {
                $data['created_by'] = pk_decrypt($data['created_by']);
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
            }
            if(isset($data['calender_id']) && $data['calender_id'] != null && $data['calender_id'] != '') {
                $data['calender_id'] = pk_decrypt($data['calender_id']);
            }
            
            // echo '<pre>'.print_r($data);exit;
            if(isset($data['id_contract_workflow']) && $data['id_contract_workflow'] !='0') {
                $data['id_contract_workflow'] = pk_decrypt($data['id_contract_workflow']);
                $check_contract_review = $this->Contract_model->getContractReview(array(
                    'contract_id' => $data['contract_id'],
                    'status' => 'workflow in progress',
                    'contract_workflow_id' => $data['id_contract_workflow'],
                    'is_workflow' => 1
                ));
                // echo '<pre>'.print_r($data);exit;
                // echo '<pre>'.$this->db->last_query();exit;
                $msg = $this->lang->line('workflow_initiate');
                //Updating contract workflow
                // print_r($data['id_contract_workflow']);exit;
                $this->User_model->update_data('contract_workflow',array('workflow_status'=>'workflow in progress'),array('id_contract_workflow'=>$data['id_contract_workflow']));
            }else{
                $check_contract_review = $this->Contract_model->getContractReview(array(
                    'contract_id' => $data['contract_id'],
                    'status' => 'review in progress',
                    'contract_workflow_id' => 0,
                    'is_workflow' => 0
                ));
                $msg = $this->lang->line('review_initiate');
            }
            if(!empty($check_contract_review)){
                $this->Contract_model->updateContract(array(
                    'id_contract' => $data['contract_id'],
                    'contract_status' => 'review in progress', //pending review from 2 time
                    'updated_by' => $data['created_by'],
                    'updated_on' => currentDate(),
                    'reminder_type' => NULL,
                    'reminder_sent_on' => NULL,
                    'reminder_date1' => NULL,
                    'reminder_date2' => NULL,
                    'reminder_date3' => NULL
                ));
    
                $result = array('status'=>TRUE, 'message' => $msg, 'data'=>pk_encrypt($check_contract_review[0]['id_contract_review']));
                // $this->response($result, REST_Controller::HTTP_OK); exit;
                print_r($result);exit;

            }
            //echo '<pre>'.$this->db->last_query();exit;
            $contract_update_data = array(
                'id_contract' => $data['contract_id'],
                'updated_by' => $data['created_by'],
                'updated_on' => currentDate(),
                'reminder_type' => NULL,
                'reminder_sent_on' => NULL,
                'reminder_date1' => NULL,
                'reminder_date2' => NULL,
                'reminder_date3' => NULL
            );
            if($data['is_workflow'] == 0){
                $contract_update_data['contract_status'] = 'review in progress';
            }
            $this->Contract_model->updateContract($contract_update_data);
            if(isset($data['is_workflow']) && $data['is_workflow'] == 1 && isset($data['id_contract_workflow']))
                $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'is_workflow'=>1,'contract_review_status'=>'finished','order' => 'DESC'));
            else
                $review = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'contract_review_status'=>'finished','order' => 'DESC'));
                // echo '<pre>'.$this->db->last_query();
            if(!empty($review) && isset($review[0]['id_contract_review']) && $review[0]['id_contract_review']!='' && $review[0]['id_contract_review']!=0) {
                $previous_review_id=$review[0]['id_contract_review'];
            }
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
            $contract_review_data = array(
                'contract_id' => $data['contract_id'],
                'contract_review_due_date' => currentDate(),
                'contract_review_type' => isset($data['contract_review_type'])?$data['contract_review_type']:'',
                'created_by' => $data['created_by'],
                'created_on' => currentDate(),
                'relationship_category_id' =>$contract_info[0]['relationship_category_id'],
                'calender_id' =>isset($data['calender_id'])?$data['calender_id']:0
            );
            if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
                if($data['is_workflow'] == 1){
                    $contract_review_data['is_workflow'] = $data['is_workflow'];
                    $contract_review_data['contract_workflow_id'] = $data['id_contract_workflow'];
                    $contract_review_data['contract_review_status'] = 'workflow in progress';
                }
            }
    
            $data['contract_review_id'] = $this->Contract_model->addContractReview($contract_review_data);
    
          
            $data['parent_relationship_category_id']=$contract_info[0]['relationship_category_id'];
            $data['template_id'] = $contract_info[0]['template_id'];
    
            if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
                if($data['is_workflow'] == 1){
                    $contract_workflow = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
                    if($contract_workflow[0]['workflow_id'] > 0)
                        $data['template_id'] = $contract_workflow[0]['workflow_id']; // Workflow_id == selected workflow in calendar workflow planning.
                }
            }
            if(!empty($contract_info[0]['type']) && $contract_info[0]['type']=='project'){
                $data['parent_relationship_category_id']=0;
            }
            $this->Contract_model->cloneModuleTopicQuestionForContractNew($data);
            /** */
            ///////Activating OR Deactivating the modules Based on Stored Modules Settings: Starts
            $stored_modules = $this->User_model->check_record('stored_modules',array('contract_id'=>$data['contract_id']));
            $contract_modules = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id']));
    
            foreach($stored_modules as $sk => $sv){
    
                foreach($contract_modules as $ck => $cv){
                    // if($sv['parent_module_id'] == $cv['parent_module_id'] && $data['is_workflow']==0)
                    //     $this->User_model->update_data('stored_modules',array('module_id'=>$cv['id_module']),array('parent_module_id'=>$cv['parent_module_id'],'contract_id'=>$data['contract_id']));
    
                    if($sv['parent_module_id'] == $cv['parent_module_id'] && !(int)$sv['activate_in_next_review'] && $data['is_workflow'] == 0){
                        //Updating the Modulestatus to 0 if that is set to activate_in_next_review=0 in stored modules 
                        $this->User_model->update_data('module',array('module_status'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_review_id'=>$data['contract_review_id']));
                        $cv['module_status'] = 0;
                    }else if($sv['parent_module_id'] == $cv['parent_module_id'] && (int)$sv['activate_in_next_review']){
                        if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                            $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_workflow_id'=>$data['id_contract_workflow'],'contract_id'=>$data['contract_id']));
                        }
                        else{
                            $this->User_model->update_data('stored_modules',array('status'=>0,'activate_in_next_review'=>0),array('parent_module_id'=>$cv['parent_module_id'],'contract_id'=>$data['contract_id']));
                        }
                        if($cv['module_status'] == 1){
                            //Deleting the question_answers of current review to null if the module is static and activated in next review
                            $cqr_sql = "DELETE FROM contract_question_review WHERE question_id in(SELECT id_question FROM question q LEFT JOIN topic t ON q.topic_id = t.id_topic WHERE t.module_id = ".$cv['id_module']." )";
                            $this->db->query($cqr_sql);
                        }
                    }
                }
            }
    
            //Updating Score for stored_modeules Starts
            if(isset($data['is_workflow']) && $data['is_workflow']==0){
                $previous_contract_review_id = $this->Contract_model->getLastReviewByContractId(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'order' => 'DESC','contract_review_status'=>'finished'));//echo $this->db->last_query();exit;
                $get_current_review_stored_module = $this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id'],'module_status'=>0,'static'=>1));
                if(count($get_current_review_stored_module)>0){
                    foreach($get_current_review_stored_module as $module){
                        $get_previous_module=$this->User_model->check_record('module',array('contract_review_id'=>$previous_contract_review_id[0]['id_contract_review'],'parent_module_id'=>$module['parent_module_id']));
                        $this->User_model->update_data('module',array('module_score'=>$get_previous_module[0]['module_score']),array('id_module'=>$module['id_module'],'contract_review_id'=>$data['contract_review_id']));
                        $get_current_topic_details=$this->User_model->check_record('topic',array('module_id'=>$module['id_module']));
                        foreach($get_current_topic_details as $topic){
                            $get_previous_topic=$this->User_model->check_record('topic',array('module_id'=>$get_previous_module[0]['id_module'],'parent_topic_id'=>$topic['parent_topic_id']));
                            $this->User_model->update_data('topic',array('topic_score'=>$get_previous_topic[0]['topic_score']),array('id_topic'=>$topic['id_topic']));
                        }
                    }
                }
            }
            //Updating Score for stored_modeules Ends
    
    
            ///////Activating OR Deactivating the modules Based on Stored Modules Settings: Ends
            /** */
    
            $bu_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
            $contract_review_info = $this->Contract_model->getContractReview(array('id_contract_review' => $data['contract_review_id']));
            $cust_admin_info = $this->User_model->getUserInfo(array('customer_id' => $data['customer_id'],'user_role_id' =>2,'user_status'=>1));
            $contract_review_user = $this->User_model->getUserInfo(array('user_id' => $contract_review_info[0]['created_by']));
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            
            if(isset($previous_review_id)){
                $migrate['old_contract_review_id']=$previous_review_id;
                $migrate['new_contract_review_id']=$data['contract_review_id'];
                $migrate['created_by']=$data['created_by'];
                $migrate_modules=$this->Contract_model->migrateContractUsersFromOldReview($migrate);
                $migrate_modules_array=array();
                foreach($migrate_modules as $km=>$vm){
                    $migrate_modules_array[]=$vm['user_id'];
                }
                $migrate_modules_array=array_values(array_unique($migrate_modules_array));
                $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
               
                //print_r($data); exit;
                if($data['is_workflow'] == 1)
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_INITIATE'));
                
                if($template_configurations_parent['total_records']>0) {
                    foreach ($migrate_modules_array as $k => $v) {
                        //$module_info = $this->Module_model->getModuleName(array('language_id' => 1, 'module_id' => $v['id_module']));
                        $To = $this->User_model->getUserInfo(array('user_id' => $v,'user_status'=>1));
                        //sending mail to bu owner
                        if ($template_configurations_parent['total_records'] > 0 && !empty($To)) {
                            $template_configurations = $template_configurations_parent['data'][0];
                            $wildcards = $template_configurations['wildcards'];
                            $wildcards_replaces = array();
                            $wildcards_replaces['first_name'] = $To->first_name;
                            $wildcards_replaces['last_name'] = $To->last_name;
                            $wildcards_replaces['project_name'] = $contract_info[0]['contract_name'];
                        
                            if($data['is_workflow']==1){
                                $wildcards_replaces['project_workflow_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                                $wildcards_replaces['project_workflow_created_date']=dateFormat($contract_review_info[0]['created_on']);
                            }
                            
                            $wildcards_replaces['logo'] = $customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                            $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to = $To->email;
                            $to_name = $To->first_name . ' ' . $To->last_name;
                            $mailer_data['mail_from_name'] = $from_name;
                            $mailer_data['mail_to_name'] = $to_name;
                            $mailer_data['mail_to_user_id'] = $To->id_user;
                            $mailer_data['mail_from'] = $from;
                            $mailer_data['mail_to'] = $to;
                            $mailer_data['mail_subject'] = $subject;
                            $mailer_data['mail_message'] = $body;
                            $mailer_data['status'] = 0;
                            $mailer_data['send_date'] = currentDate();
                            $mailer_data['is_cron'] = 0;
                            $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                            //print_r($mailer_data);
                            $mailer_id = $this->Customer_model->addMailer($mailer_data);
                            //sending mail to bu owner
                            if ($mailer_data['is_cron'] == 0) {
                                $this->load->library('sendgridlibrary');
                                $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                if ($mail_sent_status == 1)
                                    $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                            }
    
                        }
                    }
                }
    
    
            }
    
          
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            //sending mail to bu owner
            if($data['is_workflow'] == 1)
                $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_INITIATE'));
            
            if($template_configurations_parent['total_records']>0 && !empty($cust_admin_info)){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$cust_admin_info->first_name;
                $wildcards_replaces['last_name']=$cust_admin_info->last_name;
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                    $mailer_data['is_cron']=0;
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$cust_admin_info->email;
                $to_name=$cust_admin_info->first_name.' '.$cust_admin_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$cust_admin_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                //print_r($mailer_data);
                //print_r( $wildcards_replaces); exit;
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                //sending mail to bu owner
                if($mailer_data['is_cron']==0) {
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }
    
            }
            if(isset($contract_info[0]['delegate_id'])){
                $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                
                if($template_configurations_parent['total_records']>0 && !empty(($delegate_info))){
                    $template_configurations=$template_configurations_parent['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$delegate_info->first_name;
                    $wildcards_replaces['last_name']=$delegate_info->last_name;
                    $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                    if($data['is_workflow']==1){
                        $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                        $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                    }
                 
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject=$template_configurations['template_subject'];
                  
                    $from_name=$template_configurations['email_from_name'];
                    $from=$template_configurations['email_from'];
                    $to=$delegate_info->email;
                    $to_name=$delegate_info->first_name.' '.$delegate_info->last_name;
                    $mailer_data['mail_from_name']=$from_name;
                    $mailer_data['mail_to_name']=$to_name;
                    $mailer_data['mail_to_user_id']=$delegate_info->id_user;
                    $mailer_data['mail_from']=$from;
                    $mailer_data['mail_to']=$to;
                    $mailer_data['mail_subject']=$subject;
                    $mailer_data['mail_message']=$body;
                    $mailer_data['status']=0;
                    $mailer_data['send_date']=currentDate();
                    $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                    //print_r($mailer_data);
                    $mailer_id=$this->Customer_model->addMailer($mailer_data);
    
                    //sending mail to delegate
                    if($mailer_data['is_cron']==0){
                        $this->load->library('sendgridlibrary');
                        $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                        if($mail_sent_status==1)
                            $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                    }
    
                }
            }
            if($template_configurations_parent['total_records']>0 && !empty($bu_info)){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$bu_info->first_name;
                $wildcards_replaces['last_name']=$bu_info->last_name;
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
               
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_executed_user_name']=$contract_review_user->first_name.' '.$contract_review_user->last_name.' ('.$contract_review_user->user_role_name.')';
                    $wildcards_replaces['project_task_created_date']=dateFormat($contract_review_info[0]['created_on']);
                }
              
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
              
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$bu_info->email;
                $to_name=$bu_info->first_name.' '.$bu_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$bu_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                //print_r($mailer_data);
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                if($mailer_data['is_cron']==0){
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }
            }
            //exit;
            $get_workflow_data=$this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
            if($get_workflow_data[0]['parent_id']>0){
                $get_module_data=$this->User_model->check_record('module',array('contract_review_id'=>$data['contract_review_id']));
                $contract_user_array=array(
                    'contract_id'=>$get_workflow_data[0]['contract_id'],
                    'user_id'=>$get_workflow_data[0]['provider_id'],
                    'status'=>1,
                    'contract_review_id'=>$data['contract_review_id'],
                    'module_id'=>$get_module_data[0]['id_module'],
                    'created_on'=>currentDate()
                );
                $this->User_model->insert_data('contract_user',$contract_user_array);
            }
            $data['contract_review_id']=pk_encrypt($data['contract_review_id']);
            return $data['contract_review_id'];
            
            // $result = array('status'=>TRUE, 'message' => $msg, 'data'=>$data['contract_review_id']);
            // $this->response($result, REST_Controller::HTTP_OK);
        }
        public function dumpValidationcompleteEmails(){
            // $modules=array('','Review','WorkFlow');
            $get_all_customer_ids=$this->User_model->check_record_selected('id_customer','customer',array());
            $get_all_customer_ids[count($get_all_customer_ids)]['id_customer']=0;
            $cust_ids = array_column($get_all_customer_ids, 'id_customer');
            foreach($cust_ids as $customer_id)
            {
                //VALIDATION_SUBMITTED for review   STARTS**/
                $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_SUBMITTED','module_name'=>'Review','customer_id'=>$customer_id));
                // echo ''.$this->db->last_query(); exit;
                if(count($check_workflow_template)==0)
                {
                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Review', 'VALIDATION_SUBMITTED', '[\"first_name\",\"last_name\",\"logo\",\"contract_name\",\"module_name\",\"customer_name\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                    $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_workflow_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                    VALUES ($id,'Contract Module  Validation Submitted', 'SourcingCockpit - Validation Submitted', 
                    '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following module validation of contract <strong>{contract_name} </strong>has been  submitted:</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>{module_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                    //echo ''.$this->db->last_query(); exit;
                    $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                }
                //VALIDATION_READY fro review   ENDS**/
                //VALIDATION_SUBMITTED for workflow(task)   STARTS**/
                $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_SUBMITTED','module_name'=>'Workflow','customer_id'=>$customer_id));
                // echo ''.$this->db->last_query(); exit;
                if(count($check_workflow_template)==0)
                {
                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Workflow', 'VALIDATION_SUBMITTED', '[\"first_name\",\"last_name\",\"logo\",\"contract_name\",\"module_name\",\"customer_name\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                    $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_workflow_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                    VALUES ($id,'Contract Module  Validation Submitted', 'SourcingCockpit - Validation Submitted', 
                    '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following module validation of contract <strong>{contract_name} </strong>has been  submitted:</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>{module_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                    //echo ''.$this->db->last_query(); exit;
                    $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                }
                //VALIDATION_READY fro workflow(task)  ENDS**/
                //VALIDATION_SUBMITTED for project(task)   STARTS**/
                $check_workflow_template=$this->User_model->check_record('email_template',array('module_key'=>'VALIDATION_SUBMITTED','module_name'=>'Project','customer_id'=>$customer_id));
                // echo ''.$this->db->last_query(); exit;
                if(count($check_workflow_template)==0)
                {
                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Project', 'VALIDATION_SUBMITTED', '[\"first_name\",\"last_name\",\"logo\",\"project_name\",\"module_name\",\"customer_name\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-12-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                    $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_workflow_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                    VALUES ($id,'Project Module  Validation Submitted', 'SourcingCockpit - Validation Submitted', 
                    '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			The following module validation of project <strong>{project_name} </strong>has been  submitted:</span></p>\r\n\r\n			<p><span style=\"font-size: 110%;color:#74767A;\"><strong>{module_name}</strong><br />\r\n			<br />\r\n			Best regards,<br />\r\n			Your admin</span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"font-style:italic;color:#74767A;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"float:left;display:inline-block;font-size:11px;color:#757575;line-height:14px;font-style:italic;\"><span style=\"\">Powered by with - Copyright {year} with BVBA -</span> <a href=\"https://www.with-services.com/\" style=\"color:#1C89C7;\">www.with-services.com</a></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                    //echo ''.$this->db->last_query(); exit;
                    $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                }
                //VALIDATION_READY fro workflow(task)  ENDS**/
            }
            echo 'validation submited emalil dumpled successfully.';
        }
        public function dumpObligationsandRightsEmails(){
            $get_all_customer_ids=$this->User_model->check_record_selected('id_customer','customer',array());
            $get_all_customer_ids[count($get_all_customer_ids)]['id_customer']=0;
            $cust_ids = array_column($get_all_customer_ids, 'id_customer');
            foreach($cust_ids as $customer_id)
            {
                //obligations and rights email template   STARTS**/
                $check_obligations_and_rights_template=$this->User_model->check_record('email_template',array('module_key'=>'OBLIGATIONS_AND_RIGHTS','module_name'=>'Contract','customer_id'=>$customer_id));
                if(count($check_obligations_and_rights_template)==0)
                { 
                    $sql="INSERT INTO `email_template` (customer_id,`module_name`, `module_key`, `wildcards`, `email_from_name`, `email_from`, `created_by`, `created_on`, `recipients`) VALUES ($customer_id,'Contract', 'OBLIGATIONS_AND_RIGHTS', '[\"first_name\",\"last_name\",\"logo\",\"contract_name\",\"obligation_description\",\"obligation_type\",\"obligation_applicable_to\",\"obligation_notifcation_message\",\"customer_name\"]', 'Sourcing Cockpit', 'no-reply@sourcingcockpit.com', '1', '2020-04-09 15:19:12', '[\"BU Owner\",\"BU Delegate\"]')"; 
                    $inser_workflow_et=$this->User_model->custom_query_insert_update($sql);
                    $id=$inser_workflow_et['last_inserted_id'];
                    $sql="INSERT INTO `email_template_language` (email_template_id,`template_name`, `template_subject`,`template_content`, `language_id`) 
                    VALUES ($id,'Obligations and Rights notification', 'SourcingCockpit - Obligations and Rights notification', 
                    '<title></title>\r\n<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:720px;background:#F2F2F2;border-collapse: collapse;margin:0 auto;padding:5px;\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding:10px 40px;\">\r\n			<p style=\"color:#1C89C7;text-decoration:none;margin:0px;\"><span style=\"font-size:21px;\">SOURCINGCOCKPIT</span>.com</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;\">\r\n			<hr size=\"1\" style=\"border-top:1px solid #e2e2e2;margin:0px;\" /></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding:0px 40px;font-size:12px;\">&nbsp;\r\n			<p><span style=\"font-size: 110%;color:#74767A;\">Dear {first_name} {last_name},<br />\r\n			<br />\r\n			You receive this Obligations and Rights notification message for contract {contract_name} with the following characteristic:\r\n			<br />\r\n	<ul style=\"list-style-type: \'-\';padding: 0px 6px;\">\r\n			<li style =\"padding: 0px 0px 0px 24px;font-size: 110%;color:#74767A;\"><b>Description</b>: {obligation_description}</li>\r\n			<li style =\"padding: 0px 0px 0px 24px;font-size: 110%;color:#74767A;\"><b>Type</b>: {obligation_type}</li>\r\n			<li style =\"padding: 0px 0px 0px 24px;font-size: 110%;color:#74767A;\"><b>Applicable To</b>: {obligation_applicable_to}</li>\r\n			<li style =\"padding: 0px 0px 0px 24px;font-size: 110%;color:#74767A;\"><b>Notification Message</b>: {obligation_notifcation_message}</li>\r\n			</ul>			<br />\r\n			<span style=\"font-size: 110%;color:#74767A;\">Best regards,<br />\r\n			Your admin</span></span></p>\r\n			<a href=\"https://with.sourcingcockpit.com/app/#/\" style=\"color:#1C89C7;font-style:italic;font-size:14px;\" target=\"_blank\">Login to your account</a>\r\n\r\n			<p style=\"color: rgb(116, 118, 122);font-style: italic;\">&nbsp;</p>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"padding:10px 40px;background:#E7E8E7;\">\r\n			<p style=\"margin:0px;\"><img alt=\"banner\" src=\"{logo}\" style=\"width:55px;padding:10px 0px;\" /></p>\r\n\r\n			<p style=\"color: rgb(117, 117, 117); line-height: 14px; font-size: 11px; font-style: italic; float: left; display: inline-block;\">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message.</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n', '1')";
                    $inser_workflow_etl=$this->User_model->custom_query_insert_update($sql);
                }
                //obligations and rights email template ENDS**/
            }
            echo 'obligations and rights email templates dumped successfully.';
        }
        public function sendObligationsandRightsmails()
        {
            $date =date("Y-m-d");
            $obligations = $this->Project_model->getobligationsmails(array("date"=>$date));
           // print_r($obligations);exit;
            foreach($obligations as $obligation)
            {
                $customer_details = array();
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $obligation['customer_id']));
                if($customer_details[0]['company_logo']=='')
                {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                }        
                else
                {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                }
                $list_senders =array();
                $list_senders[0]['first_name']=$obligation['delgate_first_name'];
                $list_senders[0]['last_name']=$obligation['delegate_last_name'];
                $list_senders[0]['email']=$obligation['delegate_email'];
                $list_senders[0]['user_id']=$obligation['delegate_id'];
                $list_senders[1]['first_name']=$obligation['owner_first_name'];
                $list_senders[1]['last_name']=$obligation['owner_last_name'];
                $list_senders[1]['email']=$obligation['owner_email'];
                $list_senders[1]['user_id']=$obligation['contract_owner_id'];
                foreach($list_senders as $ls => $s){
                        $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $obligation['customer_id'],'module_key'=>'OBLIGATIONS_AND_RIGHTS','module_name' => 'Contract','status'=>1));
                    if($template_configurations['total_records']>0){
                        $template_configurations=$template_configurations['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        // $wildcards_replaces['name']=$s['name'];
                        $wildcards_replaces['contract_name']=$obligation['contract_name'];
                        $wildcards_replaces['first_name']=$s['first_name'];
                        $wildcards_replaces['last_name']=$s['last_name'];
                        $wildcards_replaces['obligation_description']=$obligation['description'];
                        $wildcards_replaces['obligation_type'] =$obligation['type_name'];
                        $wildcards_replaces['obligation_applicable_to'] = $obligation['applicable_to_name'];
                        $wildcards_replaces['obligation_notifcation_message'] =$obligation['notification_message'];
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$s['email'];
                        $to_name=$s['first_name']." ".$s['last_name'];
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_to_user_id']=$s['user_id'];
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_subject']=$subject;
                        $mailer_data['mail_message']=$body;
                        $mailer_data['status']=0;
                        $mailer_data['send_date']=currentDate();
                        $mailer_data['is_cron']=0;
                        $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                        $mailer_id=$this->Customer_model->addMailer($mailer_data);
                        if($mailer_data['is_cron']==0) {
                            $this->load->library('sendgridlibrary');
                            $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                            if($mail_sent_status==1)
                            {
                                $this->User_model->update_data("obligations_and_rights_mail",array("mail_status"=>1),array("id"=>$obligation['oblogationsMailId']));
                                $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                            }
                        } 
                    }
                }
                echo 'mails Sented successfully';
            }
        }
        public function dumpingprovidercatageoryandclassification(){
            $get_all_customer_ids=$this->User_model->check_record_selected('id_customer','customer',array());
            $cust_ids = array_column($get_all_customer_ids, 'id_customer');
            $provider_relationship_category = $this->Relationship_category_model->ProviderRelationshipCategoryList(array('customer_id' => 0));
            $provider_relationship_category = $provider_relationship_category['data'];
            $provider_relationship_classification = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => 0,'withOutOrder'=>true));
            $provider_relationship_classification = $provider_relationship_classification['data'];
            foreach($cust_ids as $customer_id)
            {
                for($j=0;$j<count($provider_relationship_category);$j++)
                {
                    $check_providercatageory=$this->User_model->check_record('provider_relationship_category',array('customer_id'=>$customer_id,'parent_provider_relationship_category_id'=>$provider_relationship_category[$j]['id_provider_relationship_category']));
                    if(count($check_providercatageory)==0)
                    {
                        $provider_inserted_id = $this->Relationship_category_model->addProviderRelationshipCategory(array(
                            'provider_relationship_category_quadrant' => $provider_relationship_category[$j]['provider_relationship_category_quadrant'],
                            'provider_relationship_category_status' => $provider_relationship_category[$j]['provider_relationship_category_status'],
                            'parent_provider_relationship_category_id' => $provider_relationship_category[$j]['id_provider_relationship_category'],
                            'customer_id' => $customer_id,
                            'created_by' => NULL,
                            'created_on' => currentDate(),
                            'can_review'=>1
                        ));
                        $this->Relationship_category_model->addProviderRelationshipCategoryLanguage(array(
                            'provider_relationship_category_id' => $provider_inserted_id,
                            'relationship_category_name' => $provider_relationship_category[$j]['relationship_category_name'],
                            'language_id' => $provider_relationship_category[$j]['language_id']
                        ));

                    }
                }
                for($p=0;$p<count($provider_relationship_classification);$p++)
                {
                    $check_providerclassification =array();
                    $check_providerclassification=$this->User_model->check_record('provider_relationship_classification',array('customer_id'=>$customer_id,'classification_position'=>$provider_relationship_classification[$p]['classification_position']));
                    //"parent_provider_relationship_classification_id"=>$provider_relationship_classification[$p]['id_provider_relationship_classification'],
                    //echo $this->db->last_query();exit;
                    if(count($check_providerclassification)==0)
                    {
                        $provider_parent_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                        'classification_key' => $provider_relationship_classification[$p]['classification_key'],
                        'classification_position' => $provider_relationship_classification[$p]['classification_position'],
                        'parent_classification_id' => $provider_relationship_classification[$p]['parent_classification_id'],
                        'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                        'customer_id' => $customer_id,
                        'classification_status' => $provider_relationship_classification[$p]['classification_status'],
                        'is_visible' => $provider_relationship_classification[$p]['is_visible'],
                        'created_by' => NULL,
                        'created_on' => currentDate()
                        ));
                        $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                            'provider_relationship_classification_id' => $provider_parent_inserted_id,
                            'classification_name' => $provider_relationship_classification[$p]['classification_name'],
                            'language_id' => $provider_relationship_classification[$p]['language_id']));
                    }
                    else
                    {
                        $provider_parent_inserted_id = $check_providerclassification[0]['id_provider_relationship_classification'];
                    }
                    $provider_relationship_classification1 = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],'withOutOrder'=>true));
                    $provider_relationship_classification1 = $provider_relationship_classification1['data'];     
                    for($pr=0;$pr<count($provider_relationship_classification1);$pr++)
                    {
                        $check_providerclassificationChild =array();
                        $check_providerclassificationChild=$this->User_model->check_record('provider_relationship_classification',array('customer_id'=>$customer_id,'classification_position'=>$provider_relationship_classification1[$pr]['classification_position']));
                        if(count($check_providerclassificationChild) == 0)
                        {
                            $provider_class_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                                            'classification_key' => $provider_relationship_classification1[$pr]['classification_key'],
                                            'classification_position' => $provider_relationship_classification1[$pr]['classification_position'],
                                            'parent_classification_id' => $provider_parent_inserted_id,
                                            'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                                            'customer_id' => $customer_id,
                                            'classification_status' => $provider_relationship_classification1[$pr]['classification_status'],
                                            'is_visible' => $provider_relationship_classification1[$pr]['is_visible'],
                                            'created_by' =>NULL,
                                            'created_on' => currentDate()
                                        ));
                            $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                                'provider_relationship_classification_id' => $provider_class_inserted_id,
                                'classification_name' => $provider_relationship_classification1[$pr]['classification_name'],
                                'language_id' => $provider_relationship_classification1[$pr]['language_id']
                            ));
                        }
                    }
                }
            }
            echo 'Provider classifications and categories dumped successfully';
        }
        public function preview()
        {
            $data = $this->input->get();
            $decodedfilePath =  pk_decrypt($data['file']);
            $isDocumentIntelligence = $data['is_document_intelligence'];
            $isOcr = $data['is_ocr'];
            $completePath = REST_API_URL."/uploads/".$decodedfilePath;///for localhost
            //$completePath = FILE_SYSTEM_PATH."/uploads/".$decodedfilePath;//for devserver
            $fileName ='';
            if($isDocumentIntelligence == 1)
            {
                if($isOcr == 1)
                {
                    $fileData = $this->User_model->check_record('document_intelligence',array('ocr_document_path'=>str_replace('ocr/','',$decodedfilePath)));
                    //$fileName =!empty($fileData)?str_replace(' ', '%20', str_replace('.pdf','_OCR.pdf',$fileData[0]['original_document_name'])):'';
                    $fileName =!empty($fileData)?str_replace(' ', '%20', (substr($fileData[0]['original_document_name'], 0, -4)."_OCR.pdf")):'';
                }
                else
                {
                    $fileData = $this->User_model->check_record('document_intelligence',array('original_document_path'=>$decodedfilePath));
                    $fileName =!empty($fileData)?str_replace(' ', '%20', $fileData[0]['original_document_name']):'';
                } 
            }
            else
            {
                $fileData = $this->User_model->check_record('document',array('document_source'=>$decodedfilePath));
                $fileName =!empty($fileData)?str_replace(' ', '%20', $fileData[0]['document_name']):'';
            }
            if(!empty($fileData) && $fileName!='')
            {
                $fileNameWithOutExt = substr($fileName, 0, -4);
                $fileName = preg_replace('/[,.]/', '_', $fileNameWithOutExt).".pdf";
                header("Content-Disposition: inline; filename=".$fileName);
            }
            header('Content-type: application/pdf');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            @readfile($completePath);
        }
        public function mastercurrencydump(){

            $cust_ids=$this->User_model->check_record_selected('id_customer','customer',array());
            foreach($cust_ids as $id){
                $check_exist=$this->User_model->check_record('currency',array('is_maincurrency'=>1,'customer_id'=>$id['id_customer'],'currency_name'=>'EUR','currency_full_name'=>'Euro','status'=>1));
                if(empty($check_exist)){
                    $this->User_model->insert_data('currency',array('is_maincurrency'=>1,'customer_id'=>$id['id_customer'],'currency_name'=>'EUR','currency_full_name'=>'Euro','status'=>1));
                }
            }
            echo "Master currency dumped sucessfully";
        }
        public function migrateContractData(){
            $customer_id=9; 
            $get_mig_data=$this->User_model->check_record('moodys_contract_data',array('migration_status'=>0));
            foreach($get_mig_data as $k=>$v){
                $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$customer_id));
                $countofcantracts=count($get_contracts);
                $contract_unique_id='C'.str_pad($countofcantracts+1, 7, '0', STR_PAD_LEFT);
                $contract_array['contract_unique_id']=$contract_unique_id;
                $moodys_relation_data = $this->User_model->check_record('moodys_relation_data',array('IP_Code_Tag'=>$v['IP_Code_Tag']));
                if(!empty($moodys_relation_data))
                {
                    $get_relation_id=$this->User_model->check_record('provider',array('provider_name'=>$moodys_relation_data[0]['Relation_Name'],'customer_id'=>$customer_id));
                    $contract_array['provider_name']=$get_relation_id[0]['id_provider'];
                }
                else
                {
                    $contract_array['provider_name']=null;
                }
                $contract_array['contract_name']=$v['Contract_Name'];
                $contract_array['contract_start_date']=!empty($v['Contract_Start_Date'])?$v['Contract_Start_Date']:null;
                $contract_array['contract_end_date']=!empty($v['Contract_End_Date'])?$v['Contract_End_Date']:null;
                $contract_array['auto_renewal']=$v['Automatic_Prolongation']=='YES'?1:0;
                // $get_reclid=$this->User_model->check_record('relationship_category_language',array('relationship_category_name'=>$v['Category']));
                // $contract_array['relationship_category_id']=$get_reclid[0]['relationship_category_id'];
                $contract_array['template_id']=0;
                $contract_array['can_review']=0;
                if($v['Category']=='Unclassified')
                {
                    $contract_array['relationship_category_id']='53';
                }
                elseif($v['Category']=='No Review')
                {
                    $contract_array['relationship_category_id']='52';
                }
                elseif($v['Category']=='Full Review')
                {
                    $contract_array['relationship_category_id']='51';
                    $contract_array['template_id']=58;
                    $contract_array['can_review']=1;
                }
                $get_currency=$this->User_model->check_record('currency',array('currency_name'=>$v['Currency'],'customer_id' =>$customer_id));
                $contract_array['currency_id']=$get_currency[0]['id_currency'];
                $contract_array['business_unit_id']='28';
                
                if(strtolower($v['contract_owner'])=='dale pham')
                {
                    $contract_array['contract_owner_id']='209';
                }
                elseif(strtolower($v['contract_owner'])=='edward fares')
                {
                    $contract_array['contract_owner_id']='207';
                }
                elseif(strtolower($v['contract_owner'])=='philippe lescroart')
                {
                    $contract_array['contract_owner_id']='208';
                }
                elseif(strtolower($v['contract_owner'])=='rein bouchet')
                {
                    $contract_array['contract_owner_id']='206';
                }
                elseif(strtolower($v['contract_owner'])=='simona boscolo')
                {
                    $contract_array['contract_owner_id']='211';
                }
                elseif(strtolower($v['contract_owner'])=='unallocated')
                {
                    $contract_array['contract_owner_id']='210';
                }
                else
                {
                    $contract_array['contract_owner_id']='';
                }
                $contract_array['type']='contract';
                $contract_array['is_deleted']=0;
                $contract_array['created_by']=203;
                $contract_array['contract_status']='new';
                $contract_array['parent_contract_id']=0;
                $contract_array['contract_active_status']=($v['status']=='Active')?"Active":"Closed";
                $contract_array['created_on']=currentDate();
                $contract_array['description']=$v['Contract_Description'];
                $contract_array['is_migrated']=1;
                $contract_array['contract_value']=$v['Projected_Value'];
                $contract_id=$this->User_model->insert_data('contract',$contract_array);
                $stake_holder_lables = array('contract_id'=>$contract_id,'lable1'=>'Account Managers','lable2'=>'Delivery Managers','lable3'=>'Contract Managers','created_by'=>203,'created_on' => currentDate());
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                $document_data =[];
                if(!empty($v['Add_Link_Contract_document_1']))
                {
                    $document1 =array(
                        'module_type'=>'contract_review',
                        'reference_id'=>$contract_id,
                        'reference_type'=>'contract',
                        'document_name'=>'Contract Document 1',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_Contract_document_1'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203, 
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$document1);
                }
                if(!empty($v['Add_Link_Contract_document_2']))
                {
                    $document2 =array(
                        'module_type'=>'contract_review',
                        'reference_id'=>$contract_id,
                        'reference_type'=>'contract',
                        'document_name'=>'Contract Document 2',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_Contract_document_2'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203, 
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$document2);
                }
                if(!empty($v['Add_Link_Contract_document_3']))
                {
                    $document3 =array(
                        'module_type'=>'contract_review',
                        'reference_id'=>$contract_id,
                        'reference_type'=>'contract',
                        'document_name'=>'Contract Document 3',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_Contract_document_3'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203,
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$document3);
                }
                if(count($document_data)>0){
                    $this->Document_model->addBulkDocuments($document_data);
                }
        
                $this->User_model->update_data('moodys_contract_data',array('migration_status'=>1),array('id'=>$v['id']));
                $this->User_model->insert_data('contract_tags',array('tag_id'=>77,'tag_option'=>0,'tag_option_value'=>$v['Automatic_Renewal_period_MONTHS'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('contract_tags',array('tag_id'=>78,'tag_option'=>0,'tag_option_value'=>$v['Termination_notice_period_MONTHS'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    if($v['Contract_Template']=='BVD template 1_2017-2019 MDLA'){
                        $Contract_Template_Tag_id=161;
                        $Contract_Template_Tag_Val='BVD template 1_2017-2019 MDLA';
                    }
                    elseif($v['Contract_Template']=='BVD template 2_2020+ MDLA'){
                        $Contract_Template_Tag_id=162;
                        $Contract_Template_Tag_Val='BVD template 2_2020+ MDLA';
                    }
                    elseif($v['Contract_Template']=='2020+ MDLA'){
                        $Contract_Template_Tag_id=163;
                        $Contract_Template_Tag_Val='2020+ MDLA';
                    }
                    elseif($v['Contract_Template']=='Non-Standard Agreement'){
                        $Contract_Template_Tag_id=160;
                        $Contract_Template_Tag_Val='Non-Standard Agreement';
                    }
                    else
                    {
                        $Contract_Template_Tag_id=0;
                        $Contract_Template_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>79,'tag_option'=>$Contract_Template_Tag_id,'tag_option_value'=>$Contract_Template_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    if($v['BvD_entity']=='BvD Geneva'){
                        $BvD_entity_Tag_id=164;
                        $BvD_entity_Tag_Val='BvD Geneva';
                    }
                    elseif($v['BvD_entity']=='Bureau van Dijk Editions Electroniques SRL'){
                        $BvD_entity_Tag_id=165;
                        $BvD_entity_Tag_Val='Bureau van Dijk Editions Electroniques SRL';
                    }
                    elseif($v['BvD_entity']=='BvD Brussels'){
                        $BvD_entity_Tag_id=166;
                        $BvD_entity_Tag_Val='BvD Brussels';
                    }
                    elseif($v['BvD_entity']=='Zephus UK'){
                        $BvD_entity_Tag_id=167;
                        $BvD_entity_Tag_Val='Zephus UK';
                    }
                    else
                    {
                        $BvD_entity_Tag_id=0;
                        $BvD_entity_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>80,'tag_option'=>$BvD_entity_Tag_id,'tag_option_value'=>$BvD_entity_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    if($v['Contract_Type']=='Principal'){
                        $Contract_Type_Tag_id=168;
                        $Contract_Type_Tag_Val='Principal';
                    }
                    elseif($v['Contract_Type']=='Amendment'){
                        $Contract_Type_Tag_id=169;
                        $Contract_Type_Tag_Val='Amendment';
                    }
                    elseif($v['Contract_Type']=='Termination (notice of)'){
                        $Contract_Type_Tag_id=170;
                        $Contract_Type_Tag_Val='Termination (notice of)';
                    }
                    elseif($v['Contract_Type']=='Letter (e.g change of names;  acquisition …)'){
                        $Contract_Type_Tag_id=171;
                        $Contract_Type_Tag_Val='Letter (e.g change of names;  acquisition …)';
                    }
                    elseif($v['Contract_Type']=='Order Form'){
                        $Contract_Type_Tag_id=172;
                        $Contract_Type_Tag_Val='Order Form';
                    }
                    else
                    {
                        $Contract_Type_Tag_id=0;
                        $Contract_Type_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>81,'tag_option'=>$Contract_Type_Tag_id,'tag_option_value'=>$Contract_Type_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));

                    if($v['Personal_Data']=='Yes'){
                        $Personal_Data_Tag_id=195;
                        $Personal_Data_Tag_Val='Yes';
                    }
                    elseif($v['Personal_Data']=='No'){
                        $Personal_Data_Tag_id=196;
                        $Personal_Data_Tag_Val='No';
                    }
                    else
                    {
                        $Personal_Data_Tag_id=0;
                        $Personal_Data_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>102,'tag_option'=>$Personal_Data_Tag_id,'tag_option_value'=>$Personal_Data_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>101,'tag_option'=>0,'tag_option_value'=>$v['Id_Dataset'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>100,'tag_option'=>0,'tag_option_value'=>$v['contract_id_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>88,'tag_option'=>0,'tag_option_value'=>null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>98,'tag_option'=>0,'tag_option_value'=>null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>99,'tag_option'=>0,'tag_option_value'=>null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    echo '<br>';
                    echo $v['Contract_Name'].' Moodys contract dumped successfully';
            }
        }
        public function migrateRelationData(){
            $customer_id=9;
            $get_provider_data=$this->User_model->check_record('moodys_relation_data',array('migration_status'=>0));
            foreach($get_provider_data as $k=>$v){
                $provider_data['provider_name']=$v['Relation_Name'];
                $provider_data['status']=$v['Status']=='Active'?1:0;
                $providers_count=$this->User_model->check_record_selected('count(*) as count','provider',array('customer_id'=>$customer_id));
                $unique_id='PR'.str_pad($providers_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
                $providerCountry = $this->User_model->check_record('country',array('country_name'=>$v['Country']));
                $provider_data['country']=!empty($providerCountry)?$providerCountry[0]['id_country']:0;
                $provider_data['unique_id']=$unique_id;
                switch ($v['Relation_Category']) {
                    case "Critical":
                        $provider_data['category_id']=24;
                        break;
                    case "Standard":
                        $provider_data['category_id']=21;
                        break;
                    case "Key":
                        $provider_data['category_id']=22;
                        break;
                    case "Strategic":
                        $provider_data['category_id']=23;
                        break;
                    case "Inactive":
                        $provider_data['category_id']=25;
                        break;
                    case "Commodity":
                        $provider_data['category_id']=30;
                        break;
                    default:
                        $provider_data['category_id']='';
                }
                $provider_data['customer_id']=$customer_id;
                $provider_data['created_on']=currentDate();
                $provider_data['created_by']=203;
                $provider_data['is_migrated']=1;
                //$provider_data['description']= $v['Description'];
                $provider_data['internal_contract_sponsor']= !empty($v['DIMS_Relation_Stakeholder_Internal'])?$v['DIMS_Relation_Stakeholder_Internal']:null;
                $provider_id=$this->User_model->insert_data('provider',$provider_data);
                $stake_holder_lables = array('provider_id'=>$provider_id,'lable1'=>'DIMS','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>203,'created_on' => currentDate(),'contract_id'=>0);
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                $document_data =[];
                if(!empty($v['Add_Link_Logo']))
                {
                    $logo =array(
                        'module_id'=> $provider_id,
                        'module_type'=>'provider',
                        'reference_id'=>$provider_id,
                        'reference_type'=>'provider',
                        'document_name'=>'Logo',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_Logo'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203,
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$logo);
                }
                if(!empty($v['Add_Link_Ip_folder']))
                {
                    $ip_folder =array(
                        'module_id'=> $provider_id,
                        'module_type'=>'provider',
                        'reference_id'=>$provider_id,
                        'reference_type'=>'provider',
                        'document_name'=>'IP folder',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_Ip_folder'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203,
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$ip_folder);
                }
                if(!empty($v['Add_Link_website']))
                {
                    $link_wibsite =array(
                        'module_id'=> $provider_id,
                        'module_type'=>'provider',
                        'reference_id'=>$provider_id,
                        'reference_type'=>'provider',
                        'document_name'=>'Website',
                        'document_type'=>1,
                        'document_source'=>$v['Add_Link_website'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>203,
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$link_wibsite);
                }
                if(count($document_data)>0){
                    $this->Document_model->addBulkDocuments($document_data);
                }
                if($v['Approval_Status']=='Green'){
                    $Approval_Status_tag_id='179';
                    $Approval_Status_tag_val='G';
                }
                elseif($v['Approval_Status']=='Red'){
                    $Approval_Status_tag_id='177';
                    $Approval_Status_tag_val='R';
                }
                elseif($v['Approval_Status']=='Amber'){
                    $Approval_Status_tag_id='178';
                    $Approval_Status_tag_val='A';
                }
                elseif($v['Approval_Status']=='N/A'){
                    $Approval_Status_tag_id='180';
                    $Approval_Status_tag_val='N/A';
                }
                else{
                    $Approval_Status_tag_id=0;
                    $Approval_Status_tag_val='';
                }
                if($v['Risk_Profile']=='Green'){
                    $Risk_Profile_tag_id='175';
                    $Risk_Profile_tag_val='G';
                }
                elseif($v['Risk_Profile']=='Red'){
                    $Risk_Profile_tag_id='173';
                    $Risk_Profile_tag_val='R';
                }
                elseif($v['Risk_Profile']=='Amber'){
                    $Risk_Profile_tag_id='174';
                    $Risk_Profile_tag_val='A';
                }
                elseif($v['Risk_Profile']=='N/A'){
                    $Risk_Profile_tag_id='176';
                    $Risk_Profile_tag_val='N/A';
                }
                else{
                    $Risk_Profile_tag_id=0;
                    $Risk_Profile_tag_val='';
                }
        
        
                //$this->User_model->insert_data('provider_tags',array('tag_id'=>84,'tag_option'=>$Approval_Status_tag_id,'tag_option_value'=>$Approval_Status_tag_val,'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                //$this->User_model->insert_data('provider_tags',array('tag_id'=>83,'tag_option'=>'0','tag_option_value'=>'','provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                // $this->User_model->insert_data('provider_tags',array('tag_id'=>85,'tag_option'=>'0','tag_option_value'=>$v['IP_Code_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                // $this->User_model->insert_data('provider_tags',array('tag_id'=>91,'tag_option'=>'0','tag_option_value'=>$v['IP_Commercial_Name_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                // $this->User_model->insert_data('provider_tags',array('tag_id'=>92,'tag_option'=>'0','tag_option_value'=>$v['IP_Previous_Name_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                // $this->User_model->insert_data('provider_tags',array('tag_id'=>93,'tag_option'=>'0','tag_option_value'=>$v['Sales__Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                // $this->User_model->insert_data('provider_tags',array('tag_id'=>94,'tag_option'=>'0','tag_option_value'=>$v['Billing_entity_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>83,'tag_option'=>$Approval_Status_tag_id,'tag_option_value'=> $Approval_Status_tag_val,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>82,'tag_option'=>$Risk_Profile_tag_id,'tag_option_value'=>$Risk_Profile_tag_val,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>84,'tag_option'=>'0','tag_option_value'=>$v['IP_Commercial_Name_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>86,'tag_option'=>'0','tag_option_value'=>$v['Billing_entity_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>85,'tag_option'=>'0','tag_option_value'=>$v['IP_Previous_Name_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>87,'tag_option'=>'0','tag_option_value'=>$v['IP_Code_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                if(!empty($v['IP_Type_Tag']) && $v['IP_Type_Tag'] == 'Reseller')
                {
                    $ip_type_tag = 190;
                    $ip_type_tag_value ="Reseller";
                }
                elseif(!empty($v['IP_Type_Tag']) && $v['IP_Type_Tag'] == 'Referral')
                {
                    $ip_type_tag = 191;
                    $ip_type_tag_value ="Referral";
                }
                else
                {
                    $ip_type_tag = null;
                    $ip_type_tag_value =null;
                }
                $this->User_model->insert_data('provider_tags',array('tag_id'=>95,'tag_option'=>$ip_type_tag,'tag_option_value'=>$ip_type_tag_value,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->insert_data('provider_tags',array('tag_id'=>96,'tag_option'=>'0','tag_option_value'=>$v['Commencement_Date_Tag'],'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                if(!empty($v['Personal_data_flag_Tag']) && $v['Personal_data_flag_Tag'] == 'Yes')
                {
                    $personal_data_flag_tag = 192;
                    $personal_data_flag_tag_value ="Yes";
                }
                elseif(!empty($v['Personal_data_flag_Tag']) && $v['Personal_data_flag_Tag'] == 'No')
                {
                    $personal_data_flag_tag = 193;
                    $personal_data_flag_tag_value ="No";
                }
                else
                {
                    $personal_data_flag_tag = null;
                    $personal_data_flag_tag_value =null;
                }
                $this->User_model->insert_data('provider_tags',array('tag_id'=>97,'tag_option'=>$personal_data_flag_tag,'tag_option_value'=>$personal_data_flag_tag_value,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                $this->User_model->update_data('moodys_relation_data',array('migration_status'=>1),array('id'=>$v['id']));
                $realtionUsers = $this->User_model->check_record('moodys_relation_user_data',array('IP_Code'=>$v['IP_Code_Tag']));
                if(!empty($realtionUsers))
                {
                    foreach($realtionUsers as $relationUser)
                    {
                        $Usercountry = $this->User_model->check_record('country',array('country_name'=>$v['Country']));
                        $user_data = array(
                            'user_role_id' => 7,
                            'customer_id' => $customer_id,
                            'first_name' => $relationUser['first_name'],
                            'last_name' => $relationUser['last_name'],
                            'email' => ($relationUser['email'] !='empty' && !empty($relationUser['email']))?$relationUser['email']:null,
                            'password' => '',
                            'language_id' => 1,
                            'created_by' => 203,
                            'created_on' => currentDate(),
                            'user_status' => 1,
                            'provider' => $provider_id,
                            'is_allow_all_bu' => 0,
                            'contribution_type'=>2,
                            'office_phone' =>$relationUser['office_phone'],
                            'secondary_phone' =>$relationUser['secondary_phone'],
                            'fax_number' =>$relationUser['fax'],
                            'address' =>$relationUser['address'],
                            'postal_code' =>$relationUser['postal_code'],
                            'city' =>$relationUser['city'],
                            'country' =>!empty($Usercountry)?$Usercountry[0]['id_country']:0,
                            'is_migrated' =>1,
                        );
                        $user_id = $this->User_model->createUser($user_data);
                        $this->User_model->update_data('moodys_relation_user_data',array('migration_status'=>1),array('id'=>$relationUser['id']));
                    }
                }
                echo "<br>";
                echo $v['Relation_Name'].' Moodys relations dumped successfully';
            }
        }
        public function dumpIntelligenceQuestions(){
            echo'<pre>';
            $get_docList=$this->User_model->check_record('document_intelligence',array('processing_status'=>1));
            foreach($get_docList as $k=>$v){
                $check_Question_existance=$this->User_model->check_record('document_fields',array('document_intelligence_id'=>$v['id_document_intelligence'],'intelligence_template_id'=>$v['intelligence_template_id'],'is_deleted'=>0));
                if(count($check_Question_existance)>0){
                    $this->db->delete('document_fields', array('document_intelligence_id'=>$v['id_document_intelligence']));  
                }
                $file = FILE_SYSTEM_PATH.'uploads/'.$v['original_document_path'];
                $ftp_server = '10.120.2.44'; // Server IP / HOST
                $connection = ssh2_connect($ftp_server,22) or die("Could not connect to $ftp_server");
                if (ssh2_auth_password($connection, 'treshadmin', '%.5~Q!LDb)?c2oEB%4Hadjj"'))
                {
                    ssh2_scp_send($connection, $file, OCR_FILE_PATH.$v['id_document_intelligence'].'.pdf', 0644);
                    ssh2_exec($connection, 'exit');                  
                    // echo "Successfully uploaded $file";
                }
                else
                {
                    // echo "Error uploading $file";
                }
                $this->User_model->custom_query_new('INSERT INTO document_fields (intelligence_template_id,document_intelligence_id,field_name,field_type,field_value,question,is_deleted,created_by,created_on)
                    SELECT '.$v['intelligence_template_id'].','.$v['id_document_intelligence'].',field_name,field_type,field_value,question,is_deleted,created_by,CURRENT_TIMESTAMP() FROM intelligence_template_fields WHERE intelligence_template_id='.$v['intelligence_template_id'].' AND is_deleted=0');
                    // echo $this->db->last_query();exit;
                    $this->User_model->update_data('document_intelligence',array('processing_status'=>2,'ocr_status'=>'R','md5_file_text'=>md5_file(FILE_SYSTEM_PATH.'uploads/'.$v['original_document_path'])),array('id_document_intelligence'=>$v['id_document_intelligence']));
            }
            echo 'intelligence Questions dumpled Sucessfully';
        }
        public function testarray(){
         $data=$this->User_model->check_record('document_fields',array('id_document_fields'=>66));
         echo'<pre>';
         foreach($data as $k){
              $index=getIndexOfValue(explode('||',$k['field_status']),array('A','R'));
            //   print_r(array_search("apr", );exit;
              print_r($index);exit;

         }
        }
        public function dumpFinacialHealthTag()
        {
            $getcustomerIdstoDump=$this->User_model->custom_query('select customer_id from tag t LEFT JOIN tag_language tl on tl.tag_id=t.id_tag WHERE t.type= "provider_tags" and t.tag_type="rag"   GROUP BY customer_id');
            foreach($getcustomerIdstoDump as $customerId)
            {
                $fixedTags = $this->User_model->check_record('tag',array('customer_id'=>$customerId['customer_id'],'is_fixed'=>1));
                if(!empty($fixedTags)&& count($fixedTags)>=3)
                {
                    continue;
                }
              
                $tag_id = $this->Tag_model->addTag(array(
                    'tag_order' => 2,
                    'tag_type' => 'rag',
                    'field_type' => '',
                    'customer_id' => $customerId['customer_id'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_on' => currentDate(),
                    'type'=>'provider_tags',
                    'is_fixed'=>1,
                    'label'=>'label_3'
                ));
        
                $this->Tag_model->addTagLanguage(array(
                    'tag_id' => $tag_id,
                    'tag_text' => 'Financial Health',
                    'language_id' => 1
                ));
                $fixed_tags_options=array('R','A','G','N/A');
                foreach($fixed_tags_options as $n){
                    $tag_option_id = $this->Tag_model->addTagOption(array(
                        'tag_id' => $tag_id,
                        'created_by ' => 1,
                        'created_on' => currentDate()
                    ));
                    $this->Tag_model->addTagOptionLanguage(array(
                            'tag_option_id' => $tag_option_id,
                            'tag_option_name' => $n,
                            'language_id' => 1
                    ));
                }
              
            }
            echo '<br>';
            echo ' New provider tag  dumped successfully';
        }
        public function moodysRagDump()
        {
            $get_rag_data=$this->User_model->check_record('moodys_rag',array('migration_status'=>0));
            //$get_rag_data=$this->User_model->custom_query('select * from moodys_rag where migration_status = 0 limit 1');
            $i=1;
            foreach($get_rag_data as $ragData)
            {
                $ipCode = $ragData['IP_Code'];
                $providerDetails=$this->User_model->custom_query('select * from provider_tags pt left join provider p on p.id_provider = pt.provider_id 
                where tag_id =87 and customer_id = 9 and tag_option_value = '."'$ipCode'");
                if(!empty($providerDetails))
                {
                    $i++;
                    if($ragData['IMPORTANCE']=='G'){
                        $importance_tag_id='175';//216,276
                        $importance_tag_val='G';
                    }
                    elseif($ragData['IMPORTANCE']=='R')
                    {
                        $importance_tag_id='173';//217,274
                        $importance_tag_val='R';
                    }
                    elseif($ragData['IMPORTANCE']=='A')
                    {
                        $importance_tag_id='174';//215,275
                        $importance_tag_val='A';
                    }
                    elseif($ragData['IMPORTANCE']=='N/A')
                    {
                        $importance_tag_id='176';//217,277
                        $importance_tag_val='N/A';
                    }
                   
                    $importanceTagDetails = $this->User_model->check_record('provider_tags',array('tag_id'=>82,'provider_id'=>$providerDetails[0]['id_provider']));
                    if(!empty($importanceTagDetails))
                    {
                        $this->User_model->update_data('provider_tags',array('tag_option'=>$importance_tag_id,'tag_option_value'=> $importance_tag_val),array('provider_id'=>$providerDetails[0]['id_provider'],'tag_id'=>82));
                        
                    }
                    else
                    {
                        $this->User_model->insert_data('provider_tags',array('tag_id'=>82,'tag_option'=>$importance_tag_id,'tag_option_value'=> $importance_tag_val,'provider_id'=>$providerDetails[0]['id_provider'],'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    }
                    if($ragData['ATTRACTIVENESS']=='G'){
                        $attractiveness_tag_id='179';//220,318
                        $attractiveness_tag_val='G';
                    }
                    elseif($ragData['ATTRACTIVENESS']=='R')
                    {
                        $attractiveness_tag_id='177';//218,316
                        $attractiveness_tag_val='R';
                    }
                    elseif($ragData['ATTRACTIVENESS']=='A')
                    {
                        $attractiveness_tag_id='178';//219,317
                        $attractiveness_tag_val='A';
                    }
                    elseif($ragData['ATTRACTIVENESS']=='N/A')
                    {
                        $attractiveness_tag_id='180';//221,319
                        $attractiveness_tag_val='N/A';
                    }
                   
                    $attractivenessTagDetails = $this->User_model->check_record('provider_tags',array('tag_id'=>83,'provider_id'=>$providerDetails[0]['id_provider']));
                    if(!empty($attractivenessTagDetails))
                    {
                        $this->User_model->update_data('provider_tags',array('tag_option'=>$attractiveness_tag_id,'tag_option_value'=> $attractiveness_tag_val),array('provider_id'=>$providerDetails[0]['id_provider'],'tag_id'=>83));
                        
                    }
                    else
                    {
                        $this->User_model->insert_data('provider_tags',array('tag_id'=>83,'tag_option'=>$attractiveness_tag_id,'tag_option_value'=> $attractiveness_tag_val,'provider_id'=>$providerDetails[0]['id_provider'],'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    }
                    if($ragData['SUSTAINABILITY']=='G'){
                        $sustainability_tag_id='212';//273,280
                        $sustainability_tag_val='G';
                    }
                    elseif($ragData['SUSTAINABILITY']=='R')
                    {
                        $sustainability_tag_id='210';//271,278
                        $sustainability_tag_val='R';
                    }
                    elseif($ragData['SUSTAINABILITY']=='A')
                    {
                        $sustainability_tag_id='211';//272,279
                        $sustainability_tag_val='A';
                    }
                    elseif($ragData['SUSTAINABILITY']=='N/A')
                    {
                        $sustainability_tag_id='2213';//274,281
                        $sustainability_tag_val='N/A';
                    }
                   
                    $sustainabilityTagDetails = $this->User_model->check_record('provider_tags',array('tag_id'=>106,'provider_id'=>$providerDetails[0]['id_provider']));
                    if(!empty($sustainabilityTagDetails))
                    {
                        $this->User_model->update_data('provider_tags',array('tag_option'=>$sustainability_tag_id,'tag_option_value'=> $sustainability_tag_val),array('provider_id'=>$providerDetails[0]['id_provider'],'tag_id'=>106)); 
                    }
                    else
                    {
                        $this->User_model->insert_data('provider_tags',array('tag_id'=>106,'tag_option'=>$sustainability_tag_id,'tag_option_value'=> $sustainability_tag_val,'provider_id'=>$providerDetails[0]['id_provider'],'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    }
                    $this->User_model->update_data('moodys_rag',array('migration_status'=>1),array('id'=>$ragData['id']));
                     
                }

                echo '<br>';
            echo  $i.$ragData['IP_Name'] .' provider rag  tag  updated successfully';
            }
        }
        public function CopySubtaskToContractTask()
        {
            $MappingDetails = $this->User_model->custom_query('select * from subtask_mapped_contracts where is_cron_executed =0 Limit 5');
           // $this->User_model->check_record('subtask_mapped_contracts',array('is_cron_executed'=>0));
            //taking only is cron execuated = 0 data 
            if(!empty($MappingDetails))
            {
                foreach($MappingDetails as $Detail)
                {
                    if(!empty($Detail['contract_workflow_id']) && !empty($Detail['contract_id']))
                    {
                        $data = array();
                        $data['id_contract_workflow'] = $Detail['contract_workflow_id'];
                        $data['contract_id'] = $Detail['contract_id'];
                        $contractDetails = $this->User_model->check_record('contract',array("id_contract"=>$data['contract_id']));
                        $projectSubTaskDetails = $this->Project_model->gettaskDetails($data);
                        $calenderDumpData = [];
                        //dumping project calender details to contract calender 
                        if(!empty($projectSubTaskDetails))
                        {
                            $this->db->trans_start();
                            // 1 -> dumping project task calander data to contract calander data( creating task in calender)
                            $calendeName = explode(' (',$projectSubTaskDetails[0]["workflow_name"])[0];
                            $calenderDumpData =array(
                                "customer_id"=>$projectSubTaskDetails[0]["customer_id"],
                                "date"=>$projectSubTaskDetails[0]["date"],
                                "relationship_category_id"=>$projectSubTaskDetails[0]["relationship_category_id"],
                                "created_by"=>$Detail['created_by'],
                                "created_on"=>currentDate(),
                                "status"=>1,
                                "bussiness_unit_id"=>$contractDetails[0]["business_unit_id"],
                                "provider_id"=>$projectSubTaskDetails[0]["provider_id"],
                                "recurrence"=>$projectSubTaskDetails[0]["recurrence"],
                                "recurrence_till"=>$projectSubTaskDetails[0]["recurrence_till"],
                                "is_workflow"=>1,
                                "workflow_name"=>$calendeName,
                                'workflow_id'=>$projectSubTaskDetails[0]["workflow_id"],
                                "plan_executed"=>0,
                                "auto_initiate"=>0,
                                "type"=>"contract",
                                "task_type"=>"main_task",
                                "contract_id"=>$data['contract_id'],
                                "completed_contract_id"=>null,
                                "initiate_date"=>$projectSubTaskDetails[0]["initiate_date"],
                                "provider_relationship_category_id"=>$projectSubTaskDetails[0]["provider_relationship_category_id"]
                            );

                            $data['newCalenderId'] = $this->User_model->insert_data('calender',$calenderDumpData);
                            //2=>dumping contract workflow details
                            $ProjectWorkflowDetails = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
                            $contractWorkflowDumpData = array(
                                'contract_id' => $data['contract_id'],
                                'workflow_id' => $ProjectWorkflowDetails[0]['workflow_id'],
                                'workflow_name' => $calendeName,
                                'workflow_status' => 'new',
                                'Execute_by' => $ProjectWorkflowDetails[0]['Execute_by'],
                                'created_on' => currentDate(),
                                'created_by' => $Detail['created_by'],
                                // 'updated_on' => $ProjectWorkflowDetails[0]["updated_on"],
                                // 'updated_by' => $ProjectWorkflowDetails[0]["updated_by"], 
                                'status' => 1,
                                'calender_id' => $data['newCalenderId'],
                                'reminder_date1' => $ProjectWorkflowDetails[0]['remainder_date1'],
                                'reminder_date2' => $ProjectWorkflowDetails[0]['remainder_date2'],
                                'reminder_date3' => $ProjectWorkflowDetails[0]['remainder_date3'],
                                'provider_id'=> $ProjectWorkflowDetails[0]['provider_id'],
                            );
                            $data['newContractWorkflowId'] = $this->User_model->insert_data('contract_workflow',$contractWorkflowDumpData);
                            if(!empty($projectSubTaskDetails[0]['id_contract_review']))
                            {
                                $this->User_model->update_data('contract_workflow',array('workflow_status'=>'workflow in progress'),array('id_contract_workflow'=>$data['newContractWorkflowId']));

                                $projectContractReviewDetails = $this->User_model->check_record('contract_review',array('id_contract_review'=>$projectSubTaskDetails[0]['id_contract_review']));


                                $contract_review_data = array(
                                    'contract_id' => $data['contract_id'],
                                    'contract_review_due_date' => $projectContractReviewDetails[0]['contract_review_due_date'],
                                    'contract_review_type' => 'adhoc_workflow',
                                    'created_by' => $Detail['created_by'],
                                    'created_on' => currentDate(),
                                    // 'updated_by' => $projectContractReviewDetails[0]['updated_by'],
                                    // 'updated_on' => $projectContractReviewDetails[0]['updated_on'],
                                    'relationship_category_id' =>$contractDetails[0]['relationship_category_id'],
                                    'calender_id' =>$data['newCalenderId'],
                                    'is_workflow' => 1,
                                    'contract_workflow_id' => $data['newContractWorkflowId'],
                                    'contract_review_status'=> 'workflow in progress',
                                );
                                $data['newContractReviewId'] = $this->Contract_model->addContractReview($contract_review_data);
                                $storeproc='CALL dumpModulesForContractWorkflow("'.$projectSubTaskDetails[0]["workflow_id"].'","'.$data['newContractReviewId'].'","'.$Detail['created_by'].'","'.currentDate().'","'.$contractDetails[0]['relationship_category_id'].'")';
                                $questionsDump = $this->db->query($storeproc);
                                if($questionsDump)
                                {
                                    //dumping answers to contract task
                                    $dumpResult = $this->Project_model->dumpProjectTaskAnswers(array("new_contract_review"=>$data['newContractReviewId'],"old_contract_review"=>$projectSubTaskDetails[0]['id_contract_review']));
                                    //dumping attachments to contract  Task 
                                    //old questions
                                    $oldContractReviewId =$projectSubTaskDetails[0]["id_contract_review"];
                                    $getQuestions='select id_question,parent_question_id from question q LEFT JOIN topic t on t.id_topic = q.topic_id LEFT JOIN
                                    module m on m.id_module = t.module_id WHERE m.contract_review_id='.$oldContractReviewId;
                                    $oldquestions = $this->User_model->custom_query($getQuestions);
                                    $data['Oldquestions'] = array_column($oldquestions, 'id_question');
                                    //new questions
                                    $getQuestions='select id_question as new_question_id,parent_question_id from question q LEFT JOIN topic t on t.id_topic = q.topic_id LEFT JOIN module m on m.id_module = t.module_id WHERE m.contract_review_id='.$data['newContractReviewId'];
                                    $newquestions = $this->User_model->custom_query($getQuestions);
                                    $questions = array_replace_recursive($oldquestions,$newquestions);
                                    $documentDetails = $this->Project_model->getProjecttaskAttachemnts(array("new_contract_review"=>$data['newContractReviewId'],"old_contract_review"=>$oldContractReviewId,'contract_workflow_id'=>$data['id_contract_workflow'],'Oldquestions'=>$data['Oldquestions']));
                                    $insertdocument = [];
                                    foreach($documentDetails as $document)
                                    {
                                        $newQuestionId =null;
                                        $key = array_search($document['reference_id'], array_column($questions ,'id_question'));
                                        $newQuestionId = $questions [$key]['new_question_id'];
                                        $documentdata =[];
                                        $documentdata =array(
                                            'module_id'=>$data['newContractReviewId'],
                                            'module_type'=>$document['module_type'],
                                            'contract_workflow_id'=>$data['newContractWorkflowId'],
                                            'reference_id'=>$newQuestionId,
                                            'reference_type'=>$document['reference_type'],
                                            'validator_record'=>$document['validator_record'],
                                            'document_type'=>$document['document_type'],
                                            'document_name'=>$document['document_name'],
                                            'document_source'=>$document['document_source'],
                                            'document_mime_type'=>$document['document_mime_type'],
                                            'document_status'=>$document['document_status'],
                                            'uploaded_by'=>$Detail['created_by'], 
                                            'uploaded_on'=>currentDate(),
                                            'updated_on'=>currentDate(),
                                            'is_lock'=>$document['is_lock']
                                        );
                                        if($document['document_type'] == 0 )
                                        {
                                            if(file_exists(FILE_SYSTEM_PATH.'uploads/' . $document['document_source']))
                                            {
                                                $fileDetails  = pathinfo(FILE_SYSTEM_PATH.'uploads/' . $document['document_source']);
                                                $extension    = $fileDetails['extension'];
                                                $directoryName = $fileDetails['dirname'];
                                                $fileName     = pathinfo(FILE_SYSTEM_PATH.'uploads/' . $document['document_source'],PATHINFO_FILENAME);
                                                $newFileName =$fileName . "_" . time() . "." . $extension;
                                                $e = copy(FILE_SYSTEM_PATH.'uploads/' . $document['document_source'],$directoryName.'/'.$newFileName);
                                                if($e)
                                                {
                                                    $documentdata['document_source'] =  $projectSubTaskDetails[0]["customer_id"].'/'.$newFileName;
                                                }
                                                else
                                                {
                                                    continue;
                                                }
                                            }
                                            else
                                            {
                                                continue;
                                            }
                                        }
                                        array_push($insertdocument,$documentdata);
                                    }
                                    if(count($insertdocument)>0)
                                    {
                                        $this->User_model->batch_insert('document',$insertdocument);
                                    }

                                    // //updating contract task in contract workflow ,contract review
                                    $moduleDetails = $this->Project_model->getModuleDetails(array("new_contract_review"=>$data['newContractReviewId'],"old_contract_review"=>$oldContractReviewId));
                                    $updateModule =array();
                                    foreach($moduleDetails as $moduleData)
                                    {
                                        $updateModule[] = array(
                                            'id_module'=>$moduleData['new_module_id'],
                                            'module_status'=>$moduleData['module_status'],
                                            'module_score'=>$moduleData['module_score'],
                                        );
                                    }
                                    if(count($updateModule)>0)
                                    {
                                        $this->User_model->batch_update('module',$updateModule,'id_module');
                                    }
                                    $topicDetails = $this->Project_model->getTopicDetails(array("new_contract_review"=>$data['newContractReviewId'],"old_contract_review"=>$oldContractReviewId));
                                    $updateTopic =array();
                                    foreach($topicDetails as $topicData)
                                    {
                                        $updateTopic[] = array(
                                            'id_topic'=>$topicData['new_topic_id'],
                                            'topic_status'=>$topicData['topic_status'],
                                            'topic_score'=>$topicData['topic_score'],
                                        );
                                    }
                                    if(count($updateTopic)>0)
                                    {
                                        $this->User_model->batch_update('topic',$updateTopic,'id_topic');
                                    }

                                    //finilizing contract task 

                                    $first_review_modules = $this->Module_model->getStorableModules(array('contract_review_id'=>$data['newContractReviewId'],'static'=>1,'module_status'=>array(1,2,3)));

                                    foreach($first_review_modules as $v){
                                        $insert_data = array(
                                            'parent_module_id' => $v['parent_module_id'],
                                            'module_id' => $v['id_module'],
                                            'contract_id' => $data['contract_id'],
                                            'status' => 1,
                                            'activate_in_next_review' => 0,
                                            'created_by' => $Detail['created_by'],
                                            'created_on' => CurrentDate(),
                                            'updated_by' => $Detail['created_by'],
                                            'updated_on' => CurrentDate(),
                                            'is_workflow'=> 1,
                                            'contract_workflow_id'=>$data['newContractWorkflowId'],
                                            'is_copied_from_project'=>1
                                        );
                                        $this->User_model->update_data('contract_workflow',array('status'=>0,'workflow_status'=>'workflow finlized'),array('id_contract_workflow'=>$data['newContractWorkflowId']));      
                                        $this->User_model->insert_data('stored_modules',$insert_data);
                                    }
                                    // $this->Contract_model->updateContractReview(array('id_contract_review' => $data['newContractReviewId'],'contract_review_status' => 'finished','updated_by' => 10,'updated_on' => currentDate(),'finalize_comments'=>null,'finalize_without_discussion'=>null,'contract_owner_id'=>$contractDetails[0]['contract_owner_id'],'contract_delegate_id'=>$contractDetails[0]['delegate_id'],'review_score'=>$projectContractReviewDetails[0]['review_score']));
                                    $this->Contract_model->updateContractReview(array('id_contract_review' => $data['newContractReviewId'],'contract_review_status' => 'finished','updated_by' => 10,'updated_on' => currentDate(),'finalize_comments'=>null,'finalize_without_discussion'=>null,'contract_owner_id'=>$contractDetails[0]['contract_owner_id'],'contract_delegate_id'=>$contractDetails[0]['delegate_id'],'review_score'=>$projectContractReviewDetails[0]['review_score']));
                                    $this->User_model->update_data('calender',array('status'=>0,'completed_contract_id'=>$data['contract_id'],'contract_id'=>''),array('id_calender' => $data['newCalenderId']));
                                    
                                }
                                else
                                {
                                    echo "Questions not dumped";
                                }
                            } 
                            $this->db->trans_complete(); 
                            if ($this->db->trans_status() === FALSE)
                            {
                                echo "db trans failed for ".$data['id_contract_workflow'];
                            }
                            else
                            {
                                $this->User_model->update_data('subtask_mapped_contracts',array('is_cron_executed'=>1),array('id_subtask_mapped_contracts'=>$Detail['id_subtask_mapped_contracts']));
                                echo "subtask linked to contract ".$data['contract_id'];
                            }
                        }
                        else
                        {
                            echo $this->lang->line('sub_task_does_not_exist');
                        }
                        
                    }
                    else
                    {
                        continue;
                    }
                }
            }

        }
        //* create new eamil template for MFA email verification *//
        public function createnewemailverificationtemplate(){
             /******* for MFA_EMAIL_VERIFICATION  start *******/
             $html = '<title></title>
             <table align="center" border="0" cellpadding="0" cellspacing="0" style="background: rgb(242, 242, 242); margin: 0px auto; padding: 5px; border-collapse: collapse; max-width: 720px;">
                 <tbody>
                     <tr>
                         <td style="padding: 10px 40px;">
                         <p style="margin: 0px; color: rgb(28, 137, 199); text-decoration: none;"><span style="font-size: 21px;">SOURCING COCKPIT</span></p>
                         </td>
                     </tr>
                     <tr>
                         <td style="padding: 0px 40px;">
                         <hr size="1" style="margin: 0px; border-top-color: rgb(226, 226, 226); border-top-width: 1px; border-top-style: solid;" /></td>
                     </tr>
                     <tr>
                         <td style="padding: 0px 40px; font-size: 12px;">
                         <p><span style="color: rgb(116, 118, 122); font-size: 110%;">Dear {first_name} {last_name},</span><br />
                         &nbsp;</p>
             
                         <p><span style="color: rgb(116, 118, 122); font-size: 110%;">Your Email Verification Code is: </span><span style="color: rgb(116, 118, 122); font-size: 110%;"><em>{verification_code}</em></span></p>
             
                         <p><br />
                         <span style="color: rgb(116, 118, 122); font-size: 110%;">Best regards,<br />
                         Your admin</span></p>
             
                         <p style="color: rgb(116, 118, 122); font-style: italic;">&nbsp;</p>
                         </td>
                     </tr>
                     <tr>
                         <td colspan="2" style="background: rgb(231, 232, 231); padding: 10px 40px;">
                         <p style="margin: 0px;"><img alt="banner" src="{logo}" style="padding: 10px 0px; width: 55px;" /></p>
             
                         <p style="color: rgb(117, 117, 117); line-height: 14px; font-size: 11px; font-style: italic; float: left; display: inline-block;">This email was sent from a notification-only email address and cannot accept email. Please do not reply to this message</p>
                         </td>
                     </tr>
                 </tbody>
             </table>
             ';
             $checkInAdmin=$this->User_model->check_record('email_template',array('module_key'=>'MFA_EMAIL_VERIFICATION','module_name'=>'User','customer_id'=>0));
             if(count($checkInAdmin)==0){
           

                $inserted_id = $this->Customer_model->addEmailTemplate(array(
                    'module_name' => 'User',
                    'module_key' => 'MFA_EMAIL_VERIFICATION',
                    'wildcards' => '["first_name","last_name","logo","verification_code"]',
                    'email_from_name' =>'Sourcing Cockpit',
                    'email_from' =>'no-reply@sourcingcockpit.com',
                    'status' => 1,
                    'customer_id' => 0,
                    'created_by' => 1,
                    'recipients' => '["User"]',
                    'created_on' => currentDate()
                ));
    
                $this->Customer_model->addEmailTemplateLanguage(array(
                    'email_template_id' => $inserted_id,
                    'template_name' => 'MFA Email Verification',
                    'template_subject' => 'SourcingCockpit - Email verification',
                    'template_content' => $html,
                    'language_id' => 1
                ));
                $adminEmailTemplateId =  $inserted_id;

            }
            else
            {
                $adminEmailTemplateId = $checkInAdmin[0]['id_email_template'];
            }
            $customers = $this->User_model->check_record('customer',array());
            foreach($customers as $customer){
                $checkInCustomer=$this->User_model->check_record('email_template',array('module_key'=>'MFA_EMAIL_VERIFICATION','module_name'=>'User','customer_id'=>$customer['id_customer']));
                if(count($checkInCustomer)==0){
                    $inserted_id = $this->Customer_model->addEmailTemplate(array(
                        'module_name' => 'User',
                        'module_key' => 'MFA_EMAIL_VERIFICATION',
                        'wildcards' => '["first_name","last_name","logo","verification_code"]',
                        'email_from_name' =>'Sourcing Cockpit',
                        'email_from' =>'no-reply@sourcingcockpit.com',
                        'status' => 1,
                        'parent_email_template_id' => $adminEmailTemplateId,
                        'customer_id' => $customer['id_customer'],
                        'created_by' => 1,
                        'recipients' => '["User"]',
                        'created_on' => currentDate()
                    ));
        
                    $this->Customer_model->addEmailTemplateLanguage(array(
                        'email_template_id' => $inserted_id,
                        'template_name' => 'MFA Email Verification',
                        'template_subject' => 'SourcingCockpit - Email verification',
                        'template_content' => $html,
                        'language_id' => 1
                    ));
                    echo "dumped into".$customer['company_name'];
                }
                else
                {
                    continue;
                }

            }
        }
        //* function for dumping  customer languages data againest customer *//
        public function dumpLanguagetoExistingCustomers(){
            $customers = $this->User_model->check_record('customer',array());
            foreach($customers as $customer)
            {
                $customerLanguage = $this->User_model->check_record('customer_languages' , array('customer_id' => $customer['id_customer'] , 'is_primary' => 1));
                if(empty($customerLanguage))
                {
                    $this->User_model->insert_data('customer_languages',array('customer_id'=> $customer['id_customer'] ,'language_id' =>  1 , 'is_primary' => 1 , 'status' => 1 , 'created_on' => currentDate())); 
                    echo " inserted into ".$customer['company_name'];
                    echo "</br>";
                }
            }
        }

        public function DbaccessInsert()
        {
            //$app_module = array('/all-projects/create-project');
            //$total = array(
                //  array(
                //     'module_name' => 'Project create',
                //     'module_key' => 'project_create',
                //     'module_url' => '/all-projects/create-project',
                //     'actions'=>array(
                //         array(
                //         'action_name'=> 'view',
                //         'action_key'=> 'view',
                //         'action_description' => 'Project creation view',
                //         'action_access' => array(2,3,4),
                //         )
                //     )
                //         ),
                // array(
                //     'module_name' => 'Project Details page',
                //     'module_key' => 'project_details_page',
                //     'module_url' => '/all-projects/view',
                //     'actions'=>array(
                //         array(
                //         'action_name'=> 'view',
                //         'action_key'=> 'view',
                //         'action_description' => 'Project details view',
                //         'action_access' => array(2,3,4,6),
                //         )
                //     )
                //         ),
                // array(
                //     'module_name' => 'Project task',
                //     'module_key' => 'project_task',
                //     'module_url' => '/all-projects/project-task',
                //     'actions'=>array(
                //         array(
                //         'action_name'=> 'view',
                //         'action_key'=> 'view',
                //         'action_description' => 'Project task view',
                //         'action_access' => array(2,3,4,6),
                //         )
                //     )
                //         ),
                // array(
                //     'module_name' => 'Project Module task',
                //     'module_key' => 'project_module_task',
                //     'module_url' => '/all-projects/project-module-task',
                //     'actions'=>array(
                //         array(
                //         'action_name'=> 'view',
                //         'action_key'=> 'view',
                //         'action_description' => 'Project module task view',
                //         'action_access' => array(2,3,4,6),
                //         )
                //     )
                //         ),
                        // array(
                        //     'module_name' => 'Project Logs',
                        //     'module_key' => 'project_logs',
                        //     'module_url' => '/all-projects/project-logs',
                        //     'actions'=>array(
                        //         array(
                        //         'action_name'=> 'view',
                        //         'action_key'=> 'view',
                        //         'action_description' => 'Project logs view',
                        //         'action_access' => array(2,3,4,6),
                        //         )
                        //     )
                        //         ),
                        // array(
                        //     'module_name' => 'Project Dashboard',
                        //     'module_key' => 'project_dashboard',
                        //     'module_url' => '/all-projects/project-dashboard',
                        //     'actions'=>array(
                        //         array(
                        //         'action_name'=> 'view',
                        //         'action_key'=> 'view',
                        //         'action_description' => 'Project dashboard view',
                        //         'action_access' => array(2,3,4,6),
                        //         )
                        //     )
                        // ),
                        // array(
                        //     'module_name' => 'Project relation task ',
                        //     'module_key' => 'project_relation_task',
                        //     'module_url' => '/all-project/project-task',
                        //     'actions'=>array(
                        //         array(
                        //         'action_name'=> 'view',
                        //         'action_key'=> 'view',
                        //         'action_description' => 'Project Relation Task view',
                        //         'action_access' => array(7),
                        //         )
                        //     )
                        // ),
                        // array(
                        //     'module_name' => 'Project relation dashboard',
                        //     'module_key' => 'project_relation_dashboard',
                        //     'module_url' => '/all-project/project-dashboard',
                        //     'actions'=>array(
                        //         array(
                        //         'action_name'=> 'view',
                        //         'action_key'=> 'view',
                        //         'action_description' => 'Project Relation dashboard view',
                        //         'action_access' => array(7),
                        //         )
                        //     )
                        // ),
                        // array(
                        //         'module_name' => 'Project Question discussion',
                        //         'module_key' => 'project_question_discussion',
                        //         'module_url' => '/all-projects/project-task-design',
                        //         'actions'=>array(
                        //             array(
                        //             'action_name'=> 'view',
                        //             'action_key'=> 'view',
                        //             'action_description' => 'Project Question discussion view',
                        //             'action_access' => array(2,3,4,6),
                        //             )
                        //         )
                        //     ),   
                        
                        //    array(
                        //         'module_name' => 'Project relation Questions',
                        //         'module_key' => 'project_relation_question',
                        //         'module_url' => '/all-project/project-module-task',
                        //         'actions'=>array(
                        //             array(
                        //             'action_name'=> 'view',
                        //             'action_key'=> 'view',
                        //             'action_description' => 'Project relation Question view',
                        //             'action_access' => array(7),
                        //             )
                        //         )
                        //     ),
                        // array(
                        //         'module_name' => 'Project relation Question discussion',
                        //         'module_key' => 'project_relation_question_discussion',
                        //         'module_url' => '/all-project/project-task-design',
                        //         'actions'=>array(
                        //             array(
                        //             'action_name'=> 'view',
                        //             'action_key'=> 'view',
                        //             'action_description' => 'Project relation Question discussion view',
                        //             'action_access' => array(7),
                        //             )
                        //         )
                        //     ),
                        //   array(
                        //         'module_name' => 'Project task log',
                        //         'module_key' => 'project_task_log',
                        //         'module_url' => '/all-projects/project-task-change-log',
                        //         'actions'=>array(
                        //             array(
                        //             'action_name'=> 'view',
                        //             'action_key'=> 'view',
                        //             'action_description' => 'Project task log view',
                        //             'action_access' => array(2,3,4,6),
                        //             )
                        //         )
                        //     ),
                            // array(
                            //     'module_name' => 'All Activities details',
                            //     'module_key' => 'all_activities_details',
                            //     'module_url' => '/all-activities/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities details view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract details',
                            //     'module_key' => 'contract_details',
                            //     'module_url' => '/all-contracts/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contracts details view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contracts create',
                            //     'module_key' => 'contracts_create',
                            //     'module_url' => '/all-contracts/create-contract',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contracts create view',
                            //         'action_access' => array(2,3,4),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Sub Contracts create',
                            //     'module_key' => 'sub_contracts_create',
                            //     'module_url' => '/all-contracts/sub-create',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'sub Contracts create view',
                            //         'action_access' => array(2,3,4),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract review ',
                            //     'module_key' => 'contract_review',
                            //     'module_url' => '/all-contracts/contract-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract review view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract relation review ',
                            //     'module_key' => 'contract_relation_review',
                            //     'module_url' => '/all-contract/contract-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract relation review view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract relation Task ',
                            //     'module_key' => 'contract_relation_task',
                            //     'module_url' => '/all-contract/contract-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract relation task view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract review relation questions ',
                            //     'module_key' => 'contract_review_relation_questions',
                            //     'module_url' => '/all-contract/contract-module-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract review relation questions view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract Task relation questions',
                            //     'module_key' => 'contract_task_relation_questions',
                            //     'module_url' => '/all-contract/contract-module-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task relation question view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract review',
                            //     'module_key' => 'all_activities_contract_review',
                            //     'module_url' => '/all-activities/contract-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract review view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract review change log',
                            //     'module_key' => 'all_activities_contract_review_change_log',
                            //     'module_url' => '/all-activities/contract-review-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract review change log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract review change log',
                            //     'module_key' => 'all_contracts_contract_review_change_log',
                            //     'module_url' => '/all-contracts/contract-review-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Contract review change log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            //  array(
                            //     'module_name' => 'Relation Contract review change log',
                            //     'module_key' => 'relation_contract_review_change_log',
                            //     'module_url' => '/all-contract/contract-review-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Relation Contract review change log view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract Review Question discussion',
                            //     'module_key' => 'all_activities_contract_review_discussion_question',
                            //     'module_url' => 'all-activities/contract-review-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract Review Question discussion view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract Review Question discussion',
                            //     'module_key' => 'all_contracts_contract_review_discussion_question',
                            //     'module_url' => '/all-contracts/contract-review-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Contract Review Question discussion view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract Review Questions ',
                            //     'module_key' => 'all_activities_contract_review_questions',
                            //     'module_url' => '/all-activities/contract-module-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract Review Questions view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract Review Questions ',
                            //     'module_key' => 'all_contracts_contract_review_questions',
                            //     'module_url' => '/all-contracts/contract-module-review',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Contract Review Questions view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts contract dashboard ',
                            //     'module_key' => 'all_contracts_contract_dashboard',
                            //     'module_url' => '/all-contracts/contract-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts contract dashboard view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities contract dashboard',
                            //     'module_key' => 'all_activities_contract_dashboard',
                            //     'module_url' => '/all-activities/contract-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract dashboard view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Review trends',
                            //     'module_key' => 'all_activities_review_trends',
                            //     'module_url' => '/all-activities/review-trends',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Review trends view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All contracts Review trends',
                            //     'module_key' => 'all_contracts_review_trends',
                            //     'module_url' => '/all-contracts/review-trends',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Review trends view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract relation Review trends',
                            //     'module_key' => 'contracts_relation_review_trends',
                            //     'module_url' => '/all-contract/review-trends',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract relation Review trends view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract logs',
                            //     'module_key' => 'all_activities_contract_logs',
                            //     'module_url' => '/all-activities/contract-logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract logs view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract logs',
                            //     'module_key' => 'all_contracts_contract_logs',
                            //     'module_url' => '/all-contracts/contract-logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Contract logs view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract task',
                            //     'module_key' => 'all_contracts_contract_task',
                            //     'module_url' => '/all-contracts/contract-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All contract Task view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All activities Contract task',
                            //     'module_key' => 'all_activities_contract_task',
                            //     'module_url' => '/all-activities/contract-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract Task view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All activities Contract task questions',
                            //     'module_key' => 'all_activities_contract_task',
                            //     'module_url' => '/all-activities/contract-module-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract Task Quesions view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract task questions',
                            //     'module_key' => 'all_contracts_contract_task',
                            //     'module_url' => '/all-contracts/contract-module-workflow',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts contract Task Questions view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract task dashboard',
                            //     'module_key' => 'all_activities_contract_task_dashboard',
                            //     'module_url' => '/all-activities/workflow-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract Task dashboard view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract task dashboard',
                            //     'module_key' => 'all_activities_contract_task_dashboard',
                            //     'module_url' => '/all-activities/workflow-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract Task dashboard view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract task relation dashboard',
                            //     'module_key' => 'contract_task_relation_dashboard',
                            //     'module_url' => '/all-activity/workflow-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task relations dashboard view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'all Contract task relations dashboard',
                            //     'module_key' => 'all_contract_task_relation_dashboard',
                            //     'module_url' => '/all-contract/contract-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task relations dashboard view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract task dashboard',
                            //     'module_key' => 'all_contract_task_relation_dashboard',
                            //     'module_url' => '/all-contracts/workflow-dashboard',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task dashboard view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                           
                            // array(
                            //     'module_name' => 'Contract task log',
                            //     'module_key' => 'contract_task_logs',
                            //     'module_url' => '/all-contracts/contract-workflow-logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract task log',
                            //     'module_key' => 'all_activities_contract_task_logs',
                            //     'module_url' => '/all-activities/contract-workflow-logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract task log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities Contract task change log',
                            //     'module_key' => 'all_activities_contract_task_change_logs',
                            //     'module_url' => '/all-activities/contract-workflow-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities Contract task change log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts Contract task change log',
                            //     'module_key' => 'all_contracts_contract_task_change_logs',
                            //     'module_url' => '/all-contracts/contract-workflow-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts Contract task change log view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Contract task relation change log',
                            //     'module_key' => 'contract_task_relation_change_logs',
                            //     'module_url' => '/all-contract/contract-workflow-change-log',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Contract task relation change log view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Activities contract task question discussion',
                            //     'module_key' => 'all_activities_contract_task_question_discussion',
                            //     'module_url' => '/all-activities/contract-workflow-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Activities contract task question discussion view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'All Contracts contract task question discussion',
                            //     'module_key' => 'all_contracts_contract_task_question_discussion',
                            //     'module_url' => '/all-contracts/contract-workflow-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts contract task question discussion view',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'contract task relation question discussion',
                            //     'module_key' => 'contract_task_relation_question_discussion',
                            //     'module_url' => '/all-contract/contract-workflow-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts contract task question discussion view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'contract review relation question discussion',
                            //     'module_key' => 'contract_review_relation_question_discussion',
                            //     'module_url' => '/all-contract/contract-review-design',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'All Contracts contract task question discussion view',
                            //         'action_access' => array(7),
                            //         )
                            //     )
                            // ),
                            //   array(
                            //     'module_name' => 'Document Intelligence Template Questions',
                            //     'module_key' => 'document_intelligence_template_questions',
                            //     'module_url' => '/document-intelligence/template',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Document Intelligence Template Questions view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Document Intelligence Side by side',
                            //     'module_key' => 'customer_document_intelligence_template_side_by_side',
                            //     'module_url' => '/customer-document-intelligence/side-by-side-pdfs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Document Intelligence Side by side view',
                            //         'action_access' => array(2,3,4),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Edit',
                            //     'module_key' => 'customer_edit',
                            //     'module_url' => '/customers/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Edit view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            //  array(
                            //     'module_name' => 'Customer Add',
                            //     'module_key' => 'customer_add',
                            //     'module_url' => '/customers/add',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer add view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Admin List',
                            //     'module_key' => 'customer_admin_list',
                            //     'module_url' => '/customers/admin/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Admin List view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Admin Edit',
                            //     'module_key' => 'customer_admin_edit',
                            //     'module_url' => '/customers/admin/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Admin Edit view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Admin Add',
                            //     'module_key' => 'customer_admin_add',
                            //     'module_url' => '/customers/admin/add',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Admin Add view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Users List',
                            //     'module_key' => 'customer_users_list',
                            //     'module_url' => '/customers/user/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Users List view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Users edit',
                            //     'module_key' => 'customer_users_edit',
                            //     'module_url' => '/customers/user/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Users edit view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Users add',
                            //     'module_key' => 'customer_users_add',
                            //     'module_url' => '/customers/user/add',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Users Add view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Module Topic List',
                            //     'module_key' => 'Module_topic_list',
                            //     'module_url' => '/modules/topics',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Module Topic List view',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Admin Relationship classification list',
                            //     'module_key' => 'relationship_classification_list',
                            //     'module_url' => '/relationship_category/relationship_classification/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Admin Relation classification list view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Admin Relationship provider classification',
                            //     'module_key' => 'relationship_provider_classification',
                            //     'module_url' => '/relationship_category/admin_provider_relationship_category',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Admin Relationship provider classification view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Admin Relationship provider classification list',
                            //     'module_key' => 'relationship_provider_classification_list',
                            //     'module_url' => '/relationship_category/admin_provider_relationship_classification/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Admin Relationship provider classification list view',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Email Template edit view',
                            //     'module_key' => 'email_template_edit',
                            //     'module_url' => '/email-templates/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Email Template edit view',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Questions View',
                            //     'module_key' => 'question_view',
                            //     'module_url' => '/questions/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Questions View',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            //  array(
                            //     'module_name' => 'Template Preview',
                            //     'module_key' => 'template_preview',
                            //     'module_url' => '/templates/preview',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Template Preview',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Template View',
                            //     'module_key' => 'template_view',
                            //     'module_url' => '/templates/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Template View',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            //    array(
                            //     'module_name' => 'Business unit Create',
                            //     'module_key' => 'business_unit_create',
                            //     'module_url' => '/business-unit/create',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Business unit Create View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Business unit Edit',
                            //     'module_key' => 'business_unit_edit',
                            //     'module_url' => '/business-unit/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Business unit Edit View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Provider View',
                            //     'module_key' => 'provider_view',
                            //     'module_url' => '/provider/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Provider View',
                            //         'action_access' => array(2,3,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Provider Create',
                            //     'module_key' => 'provider_create',
                            //     'module_url' => '/provider/create',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Provider Create View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Provider Edit',
                            //     'module_key' => 'provider_edit',
                            //     'module_url' => '/provider/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Provider Edit View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Provider Logs',
                            //     'module_key' => 'provider_logs',
                            //     'module_url' => '/provider/provider-logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Provider logs View',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'User Creation',
                            //     'module_key' => 'user_creation',
                            //     'module_url' => '/users/create',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'User Creation View',
                            //         'action_access' => array(2,3),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'User edit',
                            //     'module_key' => 'user_edit',
                            //     'module_url' => '/users/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'User Edit View',
                            //         'action_access' => array(2,3),
                            //         )
                            //     )
                            // ),
                            //  array(
                            //     'module_name' => 'User contract Contributions',
                            //     'module_key' => 'user_contract_contributions',
                            //     'module_url' => '/users/contract-contributions',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'User contract Contributions View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            
                            //  array(
                            //     'module_name' => 'External Users list',
                            //     'module_key' => 'external_users_list',
                            //     'module_url' => '/ext-users',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'External Users list ',
                            //         'action_access' => array(2,3,4),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'External Users create',
                            //     'module_key' => 'external_users_create',
                            //     'module_url' => '/ext-users/create',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'External Users create view ',
                            //         'action_access' => array(2,3),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'External Users edit',
                            //     'module_key' => 'external_users_edit',
                            //     'module_url' => '/ext-users/edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'External Users edit view ',
                            //         'action_access' => array(2,3),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'User Contributions',
                            //     'module_key' => 'user_contributions',
                            //     'module_url' => '/users/contributions',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'User Contributions view ',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            
                            // array(
                            //     'module_name' => 'Customer Relationship provider categories',
                            //     'module_key' => 'customer_relationship_providers_category',
                            //     'module_url' => '/customer_relationship_category/providers-categories',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Relationship provider categories view ',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Relationship classification list',
                            //     'module_key' => 'customer_relationship_classification_list',
                            //     'module_url' => '/customer_relationship_category/relationship_classification/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Relationship classification list view ',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Relationship Provider classification list',
                            //     'module_key' => 'customer_relationship_provider_classification_list',
                            //     'module_url' => '/customer_relationship_category/provider_relationship_classification/list',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Relationship Provider classification list view ',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Create Report View',
                            //     'module_key' => 'create_report',
                            //     'module_url' => '/reports/create-report',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Create Report View',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Report View',
                            //     'module_key' => 'report_view',
                            //     'module_url' => '/reports/report-view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Report View',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                         
                            // array(
                            //     'module_name' => 'Report edit view',
                            //     'module_key' => 'report_edit_view',
                            //     'module_url' => '/reports/report-edit',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Report View',
                            //         'action_access' => array(2,3,4,6),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Usage Users',
                            //     'module_key' => 'customer_usage_users',
                            //     'module_url' => '/customer-usage/users',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Usage Users View',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Usage Users logs',
                            //     'module_key' => 'customer_usage_users_logs',
                            //     'module_url' => '/customer-usage/user/logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Usage Users logs View',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Customer Usage Users actions',
                            //     'module_key' => 'customer_usage_users_actions',
                            //     'module_url' => '/customer-usage/user/actions',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Customer Usage Users actions View',
                            //         'action_access' => array(1),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'User Usage logs',
                            //     'module_key' => 'user_usage_logs',
                            //     'module_url' => '/user-usage/logs',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'User Usage logs View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Users Usage actions',
                            //     'module_key' => 'users_usage_actions',
                            //     'module_url' => '/user-usage/actions',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Users Usage actions View',
                            //         'action_access' => array(2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Workflow Topics',
                            //     'module_key' => 'workflow_topics',
                            //     'module_url' => '/workflows/topics',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Workflow Topics View',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            //      array(
                            //     'module_name' => 'Workflow question view',
                            //     'module_key' => 'workflow_question_view',
                            //     'module_url' => '/workflow-questions/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Workflow question view',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Workflow view',
                            //     'module_key' => 'workflow_preview',
                            //     'module_url' => '/workflows/preview',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Workflow preview',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            // array(
                            //     'module_name' => 'Workflow view',
                            //     'module_key' => 'workflow_view',
                            //     'module_url' => '/workflows/view',
                            //     'actions'=>array(
                            //         array(
                            //         'action_name'=> 'view',
                            //         'action_key'=> 'view',
                            //         'action_description' => 'Workflow view',
                            //         'action_access' => array(1,2),
                            //         )
                            //     )
                            // ),
                            
                            
                           
                            
                           
                          
                            
                            
                            
                            
                            
                           
                            

                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                        
                                
            //);
       
            //contract builder menu 
            // $total = array(
            //     array(
            //         'module_name' => 'Contract Builder',
            //         'module_key' => 'contract-builder',
            //         'module_url' => '/contract-builder',
            //         'module_order' => 3,
            //         'module_icon' =>'tss-editor',
            //         'is_menu' => 1,
            //         'actions'=>array(
            //             array(
            //             'action_name'=> 'list',
            //             'action_key'=> 'list',
            //             'action_description' => 'Contract Builder List',
            //             'action_access' => array(1),
            //             )
            //         )
            //     ),
            //     array(
            //         'module_name' => 'Contract Builder Preview',
            //         'module_key' => 'contract-builder-preview',
            //         'module_url' => '/contract-builder/preview',
            //         'actions'=>array(
            //             array(
            //             'action_name'=> 'view',
            //             'action_key'=> 'view',
            //             'action_description' => 'Contract Builder preview',
            //             'action_access' => array(1),
            //             )
            //         )
            //     ),
            //     array(
            //         'module_name' => 'Customer Contract Builder',
            //         'module_key' => 'customer-contract-builder',
            //         'module_url' => '/customer-contract-builder',
            //         'module_order' => 3,
            //         'module_icon' =>'tss-editor',
            //         'is_menu' => 1,
            //         'actions'=>array(
            //             array(
            //             'action_name'=> 'add',
            //             'action_key'=> 'add',
            //             'action_description' => 'Customer Contract Builder add',
            //             'action_access' => array(2),
            //             ),
            //             array(
            //                 'action_name'=> 'edit',
            //                 'action_key'=> 'edit',
            //                 'action_description' => 'Customer Contract Builder edit',
            //                 'action_access' => array(2),
            //             ),
            //             array(
            //                 'action_name'=> 'list',
            //                 'action_key'=> 'list',
            //                 'action_description' => 'Customer Contract Builder list',
            //                 'action_access' => array(2),
            //             ),
            //             array(
            //                 'action_name'=> 'delete',
            //                 'action_key'=> 'delete',
            //                 'action_description' => 'Customer Contract Builder delete',
            //                 'action_access' => array(2),
            //                 )
            //         )
            //     ),
            //     array(
            //         'module_name' => 'Customer Contract Builder side by side',
            //         'module_key' => 'customer-contract-builder-side-by-side',
            //         'module_url' => '/customer-contract-builder/template-by-side',
            //         'actions'=>array(
            //             array(
            //             'action_name'=> 'view',
            //             'action_key'=> 'view',
            //             'action_description' => 'Customer Contract Builder side by side preview',
            //             'action_access' => array(2),
            //             )
            //         )
            //     ),
            // );

            //  Catalogue
            $total = array(
                array(
                    'module_name' => 'Catalogue',
                    'module_key' => 'catalogue',
                    'module_url' => '/catalogue-list',
                    'module_order' => 3,
                    'module_icon' =>'tss-cart',
                    'is_menu' => 1,
                    'actions'=>array(
                            array(
                                'action_name'=> 'add',
                                'action_key'=> 'add',
                                'action_description' => 'Catalogue add',
                                'action_access' => array(2,3,4,6),
                                ),
                            array(
                                    'action_name'=> 'edit',
                                    'action_key'=> 'edit',
                                    'action_description' => 'Catalogue Edit',
                                    'action_access' => array(2,3,4,6),
                                    ),
                            array(
                                    'action_name'=> 'list',
                                    'action_key'=> 'list',
                                    'action_description' => 'Catalogue List',
                                    'action_access' => array(2,3,4,6),
                            )
                        )
                ),
            //need to update parent_module_id manually
                array(
                    'module_name' => 'Catalogue Tags',
                    'module_key' => 'catalogue_tags',
                    'module_url' => '/catalogue-tags',
                    'module_order' => 3,
                    'module_icon' =>'tss-cart',
                    'is_menu' => 2,
                    'actions'=>array(
                            array(
                                'action_name'=> 'add',
                                'action_key'=> 'add',
                                'action_description' => 'Catalogue Tag add',
                                'action_access' => array(2),
                                ),
                            array(
                                    'action_name'=> 'edit',
                                    'action_key'=> 'edit',
                                    'action_description' => 'Catalogue Tag Edit',
                                    'action_access' => array(2),
                                    ),
                            array(
                                    'action_name'=> 'list',
                                    'action_key'=> 'list',
                                    'action_description' => 'Catalogue Tag List',
                                    'action_access' => array(2),
                            )
                        )
                ),
            );
        
           
        

            foreach($total as $app_module)
            {
                
                    //app module
                    $module_name = $app_module['module_name'];
                    $module_key = $app_module['module_key'];
                    $module_url = $app_module['module_url'];
                    $module_order = isset($app_module['module_order'])? $app_module['module_order'] : 0;
                    $module_icon = isset($app_module['module_icon'])? $app_module['module_icon'] : Null;
                    $is_menu = isset($app_module['is_menu'])? $app_module['is_menu'] : 0;
                    
                    $sql="INSERT INTO app_module (module_name,module_key, module_url, parent_module_id, sub_module,module_icon, module_order, is_menu, is_admin_menu) VALUES ('$module_name','$module_key', '$module_url',0,0,'$module_icon',$module_order,$is_menu,0)";
                    echo $app_module['module_name'] ." dumped";
                    echo "<br>";
                    $app_module_id=$this->User_model->custom_query_insert_update($sql)['last_inserted_id'];
                    //echo $this->db->last_query();exit;
                    foreach($app_module['actions'] as $app_actions)
                    {
                        //app module action insert
                        $action_name = $app_actions['action_name'];
                        $action_key = $app_actions['action_key'];
                        $action_description = $app_actions['action_description'];
                        $sql="INSERT INTO app_module_action (app_module_id,action_name, action_key, action_description) VALUES ($app_module_id,'$action_name', '$action_key', '$action_description')";
                        //echo $sql;echo"<br>";
                        //$app_module_action_id = 101;
                        $app_module_action_id=$this->User_model->custom_query_insert_update($sql)['last_inserted_id'];
                        foreach($app_actions['action_access'] as $app_action_access)
                        {
                            //app module access insert
                            $sql="INSERT INTO app_module_access (app_module_id,app_module_action_id, user_role_id, app_module_access_status) VALUES ($app_module_id,$app_module_action_id, $app_action_access, 1)";
                            //echo $sql;echo"<br>";
                            $this->User_model->custom_query_insert_update($sql);
                        }
                    }

            }
               

        }
        
        public function moodysNewContractData(){
            $customer_id=9; 
            $get_mig_data=$this->User_model->check_record('moodys_new_contracts',array('is_moved'=>0));
            foreach($get_mig_data as $k=>$v){
                $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$customer_id));
                $countofcantracts=count($get_contracts);
                $contract_unique_id='C'.str_pad($countofcantracts+1, 7, '0', STR_PAD_LEFT);
                $contract_array['contract_unique_id']=$contract_unique_id;
                $moodys_relation_data = $this->User_model->check_record('provider',array('provider_name'=>$v['relation_name'] , 'customer_id' => $customer_id));
                if(!empty($moodys_relation_data))
                {
                    $contract_array['provider_name']=$moodys_relation_data[0]['id_provider'];
                }
                else
                {
                    $providers_count=$this->User_model->check_record_selected('count(*) as count','provider',array('customer_id'=>$customer_id));
                    $providerunique_id='PR'.str_pad($providers_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
                    $providerArray = array(
                        'provider_name' => $v['relation_name'],
                        'unique_id' => $providerunique_id,
                        'customer_id' => $customer_id,
                        'is_migrate' => 1,
                        'created_by' => 203,
                        'created_on' => currentDate()
                    );
                
                    $contract_array['provider_name']=$this->User_model->insert_data('provider',$providerArray);
        
                    $stake_holder_lables = array('provider_id'=>$contract_array['provider_name'],'lable1'=>'DIMS','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>203,'created_on' => currentDate(),'contract_id'=>0);
                    $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                    
                }
                $contract_array['contract_name']=$v['contract_name'];
                $contract_array['contract_start_date']=!empty($v['contract_start_date'])?$v['contract_start_date']:null;
                $contract_array['contract_end_date']=!empty($v['contract_end_date'])?$v['contract_end_date']:null;
                $contract_array['auto_renewal']=strtolower($v['automatic_prolongation'])=='yes'?1:0;
                
                $contract_array['template_id']=0;
                $contract_array['can_review']=0;
                if($v['category']=='Unclassified')
                {
                    $contract_array['relationship_category_id']='53';
                }
                elseif($v['category']=='No Review')
                {
                    $contract_array['relationship_category_id']='52';
                }
                elseif($v['category']=='Full Review')
                {
                    $contract_array['relationship_category_id']='51';
                    $contract_array['template_id']=58;
                    $contract_array['can_review']=1;
                }
                $get_currency=$this->User_model->check_record('currency',array('currency_name'=>$v['currency'],'customer_id' =>$customer_id));
                $contract_array['currency_id']=$get_currency[0]['id_currency'];
                $contract_array['business_unit_id']='28';
                
                if(($v['owner'])=='Dale Pham')
                {
                    $contract_array['contract_owner_id']='209';
                }
                elseif(($v['owner'])=='Edward Fares')
                {
                    $contract_array['contract_owner_id']='207';
                }
                elseif(($v['owner'])=='Philippe Lescroart')
                {
                    $contract_array['contract_owner_id']='208';
                }
                elseif(($v['owner'])=='Rein Bouchet')
                {
                    $contract_array['contract_owner_id']='206';
                }
                elseif(($v['owner'])=='Simona Boscolo')
                {
                    $contract_array['contract_owner_id']='211';
                }
                elseif(($v['owner'])=='unallocated')
                {
                    $contract_array['contract_owner_id']='210';
                } 
                else
                {
                    $contract_array['contract_owner_id']='';
                }
                $contract_array['type']='contract';
                $contract_array['is_deleted']=0;
                $contract_array['created_by']=203;
                $contract_array['contract_status']='new';
                $contract_array['parent_contract_id']=0;
                $contract_array['contract_active_status']=($v['status']=='Active')?"Active":"Closed";
                $contract_array['created_on']=currentDate();
                $contract_array['description']=$v['contract_description'];
                $contract_array['is_migrated']=1;
                $contract_array['contract_value']=0;
                //echo "<>";
                //print_r($contract_array);
        
                $contract_id=$this->User_model->insert_data('contract',$contract_array);
                //echo $this->db->last_query();exit;
        
                $stake_holder_lables = array('contract_id'=>$contract_id,'lable1'=>'DIMS','lable2'=>'Relationship and Account Managers ','lable3'=>'Executive Sponsors ','created_by'=>203,'created_on' => currentDate());
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                
        
                $this->User_model->update_data('moodys_new_contracts',array('is_moved'=>1),array('id'=>$v['id']));
        
                $this->User_model->insert_data('contract_tags',array('tag_id'=>77,'tag_option'=>0,'tag_option_value'=>$v['automatic_renewal_period_months_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                $this->User_model->insert_data('contract_tags',array('tag_id'=>78,'tag_option'=>0,'tag_option_value'=>$v['termination_notice_period_months_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                
                $this->User_model->insert_data('contract_tags',array('tag_id'=>120,'tag_option'=>0,'tag_option_value'=>$v['data_description_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    if($v['contract_template_tag']=='BVD template 1_2017-2019 MDLA'){
                        $Contract_Template_Tag_id=161;
                        $Contract_Template_Tag_Val='BVD template 1_2017-2019 MDLA';
                    }
                    elseif($v['contract_template_tag']=='BVD template 2_2020+ MDLA'){
                        $Contract_Template_Tag_id=162;
                        $Contract_Template_Tag_Val='BVD template 2_2020+ MDLA';
                    }
                //    elseif($v['Contract_Template']=='2020+ MDLA'){
                //         $Contract_Template_Tag_id=163;
                //         $Contract_Template_Tag_Val='2020+ MDLA';
                //     }
                    elseif($v['contract_template_tag']=='Non-Standard Agreement'){
                        $Contract_Template_Tag_id=160;
                        $Contract_Template_Tag_Val='Non-Standard Agreement';
                    }
                    else
                    {
                        $Contract_Template_Tag_id=0;
                        $Contract_Template_Tag_Val='';
                    }
        
                $this->User_model->insert_data('contract_tags',array('tag_id'=>79,'tag_option'=>$Contract_Template_Tag_id,'tag_option_value'=>$Contract_Template_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    if($v['bvd_entity_tag']=='BvD Geneva'){
                        $BvD_entity_Tag_id=164;
                        $BvD_entity_Tag_Val='BvD Geneva';
                    }
                    //  elseif($v['BvD_entity']=='Bureau van Dijk Editions Electroniques SRL'){
                    //     $BvD_entity_Tag_id=165;
                    //     $BvD_entity_Tag_Val='Bureau van Dijk Editions Electroniques SRL';
                    // } 
                    elseif($v['bvd_entity_tag']=='BvD Brussels'){
                        $BvD_entity_Tag_id=166;
                        $BvD_entity_Tag_Val='BvD Brussels';
                    }
                    elseif($v['bvd_entity_tag']=='Zephus UK'){
                        $BvD_entity_Tag_id=167;
                        $BvD_entity_Tag_Val='Zephus UK';
                    }
                    else
                    {
                        $BvD_entity_Tag_id=0;
                        $BvD_entity_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>80,'tag_option'=>$BvD_entity_Tag_id,'tag_option_value'=>$BvD_entity_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    if($v['type_tag']=='Principal'){
                        $Contract_Type_Tag_id=168;
                        $Contract_Type_Tag_Val='Principal';
                    }
                    elseif($v['type_tag']=='Amendment'){
                        $Contract_Type_Tag_id=169;
                        $Contract_Type_Tag_Val='Amendment';
                    }
                    elseif($v['type_tag']=='Termination (notice of)'){
                        $Contract_Type_Tag_id=170;
                        $Contract_Type_Tag_Val='Termination (notice of)';
                    }
                    elseif($v['type_tag']=='Letter (e.g change of names;  acquisition …)'){
                        $Contract_Type_Tag_id=171;
                        $Contract_Type_Tag_Val='Letter (e.g change of names;  acquisition …)';
                    }
                    // elseif($v['Contract_Type']=='Order Form'){
                    //     $Contract_Type_Tag_id=172;
                    //     $Contract_Type_Tag_Val='Order Form';
                    // } 
                    else
                    {
                        $Contract_Type_Tag_id=0;
                        $Contract_Type_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>81,'tag_option'=>$Contract_Type_Tag_id,'tag_option_value'=>$Contract_Type_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    if($v['personal_data_tag']=='Yes'){
                        $Personal_Data_Tag_id=195;
                        $Personal_Data_Tag_Val='Yes';
                    }
                    elseif($v['personal_data_tag']=='No'){
                        $Personal_Data_Tag_id=196;
                        $Personal_Data_Tag_Val='No';
                    }
                    else
                    {
                        $Personal_Data_Tag_id=0;
                        $Personal_Data_Tag_Val='';
                    }
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>102,'tag_option'=>$Personal_Data_Tag_id,'tag_option_value'=>$Personal_Data_Tag_Val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>101,'tag_option'=>0,'tag_option_value'=>$v['id_dataset_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>100,'tag_option'=>0,'tag_option_value'=>$v['contract_id_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>88,'tag_option'=>0,'tag_option_value'=>null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>98,'tag_option'=>0,'tag_option_value'=>$v['invoice_frequency_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
        
                    $this->User_model->insert_data('contract_tags',array('tag_id'=>99,'tag_option'=>0,'tag_option_value'=>$v['invoice_client_entity_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                    echo '<br>';
                    echo $k . " " .$v['contract_name'].' Moodys contract dumped successfully';
                    //exit;
            }
        }  

        public function moodysCurrenctTag()
        {
            $get_mig_data=$this->User_model->check_record('moodys_currency_tag',array('is_moved'=>0));
            foreach($get_mig_data as $k=>$v){
                if($v['currenct_tag']=='EUR'){
                    $Currency_Tag_id=294;
                    $Currency_Tag_Val='EUR';
                }
                elseif($v['currenct_tag']=='USD'){
                    $Currency_Tag_id=295;
                    $Currency_Tag_Val='USD';
                }
                elseif($v['currenct_tag']=='GBP'){
                    $Currency_Tag_id=296;
                    $Currency_Tag_Val='GBP';
                }
                elseif($v['currenct_tag']=='EUR/GBP/USD'){
                    $Currency_Tag_id=297;
                    $Currency_Tag_Val='EUR/GBP/USD';
                }
                elseif($v['currenct_tag']=='EUR/GBP'){
                    $Currency_Tag_id=298;
                    $Currency_Tag_Val='EUR/GBP';
                }
                elseif($v['currenct_tag']=='EUR/USD'){
                    $Currency_Tag_id=299;
                    $Currency_Tag_Val='EUR/USD';
                }
                elseif($v['currenct_tag']=='EUR/SGD'){
                    $Currency_Tag_id=300;
                    $Currency_Tag_Val='EUR/SGD';
                }
                elseif($v['currenct_tag']=='GBP/USD'){
                    $Currency_Tag_id=301;
                    $Currency_Tag_Val='GBP/USD';
                }
                elseif($v['currenct_tag']=='AUD'){
                    $Currency_Tag_id=302;
                    $Currency_Tag_Val='AUD';
                }
                elseif($v['currenct_tag']=='MAD'){
                    $Currency_Tag_id=303;
                    $Currency_Tag_Val='MAD';
                }
                elseif($v['currenct_tag']=='n/a'){
                    $Currency_Tag_id=304;
                    $Currency_Tag_Val='n/a';
                }
                elseif($v['currenct_tag']=='UYU'){
                    $Currency_Tag_id=305;
                    $Currency_Tag_Val='UYU';
                }
                elseif($v['currenct_tag']=='JPY'){
                    $Currency_Tag_id=306;
                    $Currency_Tag_Val='JPY';
                }
                elseif($v['currenct_tag']=='SGD'){
                    $Currency_Tag_id=307;
                    $Currency_Tag_Val='SGD';
                }
                elseif($v['currenct_tag']=='DZD'){
                    $Currency_Tag_id=308;
                    $Currency_Tag_Val='DZD';
                }
                elseif($v['currenct_tag']=='BRL'){
                    $Currency_Tag_id=309;
                    $Currency_Tag_Val='BRL';
                }
                elseif($v['currenct_tag']=='USD/HKD'){
                    $Currency_Tag_id=310;
                    $Currency_Tag_Val='USD/HKD';
                }
                elseif($v['currenct_tag']=='BOB'){
                    $Currency_Tag_id=311;
                    $Currency_Tag_Val='BOB';
                }
                else
                {
                    $Currency_Tag_id=0;
                    $Currency_Tag_Val='';
                }
                $this->User_model->insert_data('contract_tags',array('tag_id'=>152,'tag_option'=>$Currency_Tag_id,'tag_option_value'=>$Currency_Tag_Val,'contract_id'=>$v['id_contract'],'created_on'=>currentDate(),'created_by'=>203,'status'=>1));
                //echo $this->db->last_query();exit;

                $this->User_model->update_data('moodys_currency_tag',array('is_moved'=>1),array('id'=>$v['id']));

                echo '<br>';
                echo $k . " " .$v['id_contract'].' Moodys currency tag updated';

                
            }

            

        }  

        public function moodysRelationTest()
        {
            $customer_id=9; 
            $uploadedUserId = 203;
            $get_mig_data=$this->User_model->check_record('moodys_relations_data',array('is_migrated'=>0));
            echo "<pre>";
            //print_r($get_mig_data);exit;
            foreach($get_mig_data as $k=>$val)
            {
                $providers_count=$this->User_model->check_record_selected('count(*) as count','provider',array('customer_id'=>$customer_id));
                $providerunique_id='PR'.str_pad($providers_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
                if(!empty($val['country_name']))
                {
                    $country_details = $this->User_model->check_record('country',array('country_name'=>$val['country_name']));
                    if(!empty($country_details))
                    {
                        $country_id = $country_details[0]['id_country'];
                    }
                    else
                    {
                        $country_id = NULL;
                    }
                }
                else
                {
                    $country_id = NULL;
                }
                $providerArray = array(
                    'provider_name' => $val['relation_name'],
                    'unique_id' => $providerunique_id,
                    'customer_id' => $customer_id,
                    'is_migrate' => 1,
                    'created_by' => $uploadedUserId,
                    'country' => $country_id,
                    'created_on' => currentDate()
                );

            
                $provider_id=$this->User_model->insert_data('provider',$providerArray);

                //stakeholder

                $stake_holder_lables = array('provider_id'=>$provider_id,'lable1'=>'DIMS','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>$uploadedUserId,'created_on' => currentDate(),'contract_id'=>0);
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);

                //tags
                $emptyTagarray = array(82,83,84,85,86,87,95,96,97,106,118,159,160,161);
                foreach($emptyTagarray as $empTag){
                    $this->User_model->insert_data('provider_tags',array('tag_id'=>$empTag,'tag_option'=>0,'tag_option_value'=>Null,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                }
                if(!empty($val['risk']))
                {
                    if(strtolower($val['risk'])=='green'){
                        $risk_option_id=314;
                        $risk_option_val='G';
                    }
                    elseif(strtolower($val['risk'])=='red'){
                        $risk_option_id=312;
                        $risk_option_val='R';
                    }
                    elseif(strtolower($val['risk'])=='amber'){
                        $risk_option_id=313;
                        $risk_option_val='A';
                    }
                    elseif(strtolower($val['risk'])=='n/a'){
                        $risk_option_id=315;
                        $risk_option_val='N/A';
                    }
                    $this->User_model->insert_data('provider_tags',array('tag_id'=>153,'tag_option'=>$risk_option_id,'tag_option_value'=>$risk_option_val,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                }
                else
                {
                    $this->User_model->insert_data('provider_tags',array('tag_id'=>153,'tag_option'=>0,'tag_option_value'=>Null,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                }
                // $document_data = [];
                // if(!empty($val['link']))
                // {
                //     $link_wibsite =array(
                //         'module_id'=> $provider_id,
                //         'module_type'=>'provider',
                //         'reference_id'=>$provider_id,
                //         'reference_type'=>'provider',
                //         'document_name'=>'relation file 1',
                //         'document_type'=>1,
                //         'document_source'=>$val['link'],
                //         'document_mime_type'=>'URL',
                //         'validator_record'=>0,
                //         'uploaded_by'=>$uploadedUserId,
                //         'uploaded_on'=>currentDate(),
                //         'updated_on'=>currentDate()
                //     );
                //     array_push($document_data,$link_wibsite);
                // }
                // if(count($document_data)>0){
                //     $this->Document_model->addBulkDocuments($document_data);
                // }

                $this->User_model->update_data('moodys_relations_data',array('is_migrated'=>1),array('id'=>$val['id']));

                echo '<br>';
                echo $k . " " .$val['relation_name'].' Moodys relation dumped successfully';
               
            }
        }

        public function moodysRelationUserTest()
        {
            $customer_id=27; 
            $uploadedUserId = 154;
            $password = md5("Moodys@2022");
            $get_mig_data=$this->User_model->check_record('moody_relations_users',array('is_migrated'=>0));
            foreach($get_mig_data as $k=>$val)
            {
                $provider_deatils = $this->User_model->check_record('provider',array('provider_name'=>$val['relation_name'],'customer_id' => $customer_id));
                $provider_id = !empty($provider_deatils) ? $provider_deatils[0]['id_provider'] : NULL;

                if(!empty($val['email_1']))
                {
                    $val['email_1'] = trim($val['email_1'],' ');
                    $userDetails = $this->User_model->check_record('user',array('email'=>$val['email_1']));
                    if(empty($userDetails))
                    {
                        $user_data = array(
                            'user_role_id' => 7,
                            'customer_id' => $customer_id,
                            'first_name' => $val['first_name_1'],
                            'last_name' => $val['last_name_1'],
                            'email' => ($val['email_1'] !='empty' && !empty($val['email_1']))?$val['email_1']:null,
                            'password' => $password,
                            'language_id' => 1,
                            'created_by' => $uploadedUserId,
                            'created_on' => currentDate(),
                            'user_status' => 1,
                            'provider' => $provider_id,
                            'is_allow_all_bu' => 0,
                            'contribution_type'=>2,
                            'is_migrated' =>1,
                        );
                        $user_id = $this->User_model->createUser($user_data);

                    }
                    
                }
                if(!empty($val['email_2']))
                {
                    $val['email_2'] = trim($val['email_2'],' ');
                    $userDetails = $this->User_model->check_record('user',array('email'=>$val['email_2']));
                    if(empty($userDetails))
                    {
                        $user_data = array(
                            'user_role_id' => 7,
                            'customer_id' => $customer_id,
                            'first_name' => $val['first_name_2'],
                            'last_name' => $val['last_name_2'],
                            'email' => ($val['email_2'] !='empty' && !empty($val['email_2']))?$val['email_2']:null,
                            'password' => $password,
                            'language_id' => 1,
                            'created_by' => $uploadedUserId,
                            'created_on' => currentDate(),
                            'user_status' => 1,
                            'provider' => $provider_id,
                            'is_allow_all_bu' => 0,
                            'contribution_type'=>2,
                            'is_migrated' =>1,
                        );
                        $user_id = $this->User_model->createUser($user_data);
                    }
                }
                if(!empty($val['email_3']))
                {
                    $val['email_3'] = trim($val['email_3'],' ');
                    $userDetails = $this->User_model->check_record('user',array('email'=>$val['email_3']));
                    if(empty($userDetails))
                    {
                        $user_data = array(
                            'user_role_id' => 7,
                            'customer_id' => $customer_id,
                            'first_name' => $val['first_name_3'],
                            'last_name' => $val['last_name_3'],
                            'email' => ($val['email_3'] !='empty' && !empty($val['email_3']))?$val['email_3']:null,
                            'password' => $password,
                            'language_id' => 1,
                            'created_by' => $uploadedUserId,
                            'created_on' => currentDate(),
                            'user_status' => 1,
                            'provider' => $provider_id,
                            'is_allow_all_bu' => 0,
                            'contribution_type'=>2,
                            'is_migrated' =>1,
                        );
                        $user_id = $this->User_model->createUser($user_data);
                    }
                }
                $this->User_model->update_data('moody_relations_users',array('is_migrated'=>1),array('id'=>$val['id']));
                echo '<br>';
                echo $k . " ".'Moodys relation user dumped successfully';
                // exit;
            }
            

        }

        public function moodysContractTest()
        {
            echo "<pre>";
            $customer_id=9; 
            $uploadedUserId = 203;
            //$get_mig_data=$this->User_model->custom_query('SELECT * FROM moodys_contracts_upload WHERE is_migrated= 0 LIMIT 2');
           // print_r($get_mig_data);exit;
            $get_mig_data=$this->User_model->check_record('moodys_contract_relation_upload',array('is_moved'=>0));
            foreach($get_mig_data as $k=>$v){
                $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$customer_id));
                $countofcantracts=count($get_contracts);
                $contract_unique_id='C'.str_pad($countofcantracts+1, 7, '0', STR_PAD_LEFT);
                $contract_array['contract_unique_id']=$contract_unique_id;
                $moodys_relation_data = $this->User_model->check_record('provider',array('customer_id'=>$customer_id,'provider_name' => $v['relation'] ));
                if(!empty($moodys_relation_data))
                {
                    $contract_array['provider_name']=$moodys_relation_data[0]['id_provider'];
                }
                else
                {
                    $contract_array['provider_name']=null;
                }
                $contract_array['contract_name']=$v['contract_name'];
                $contract_array['contract_start_date']=!empty($v['start_date'])?$v['start_date']:null;
                $contract_array['contract_end_date']=!empty($v['end_date'])?$v['end_date']:null;
                $contract_array['auto_renewal']=strtolower($v['automatic_prolongation'])=='yes'?1:0;
                // $get_reclid=$this->User_model->check_record('relationship_category_language',array('relationship_category_name'=>$v['Category']));
                // $contract_array['relationship_category_id']=$get_reclid[0]['relationship_category_id'];
                $contract_array['template_id']=0;
                $contract_array['can_review']=0;
                if(strtolower($v['category'])=='unclassified')
                {
                    $contract_array['relationship_category_id']='53'; // 53
                }
               
                $get_currency=$this->User_model->check_record('currency',array('currency_name'=>$v['currency'],'customer_id' =>$customer_id));
                $contract_array['currency_id']=$get_currency[0]['id_currency'];
                $contract_array['business_unit_id']='83';
                
                if(strtolower($v['owner'])=='jason mcgorty')
                {
                    $contract_array['contract_owner_id']='562';
                }
                else
                {
                    $contract_array['contract_owner_id']='';
                }
                if(strtolower($v['delegate'])=='toby leith')
                {
                    $contract_array['delegate_id']='563';
                }
                else
                {
                    $contract_array['delegate_id']='';
                }
                $contract_array['type']='contract';
                $contract_array['is_deleted']=0;
                $contract_array['created_by']=$uploadedUserId;
                $contract_array['contract_status']='new';
                $contract_array['parent_contract_id']=0;
                $contract_array['contract_active_status']=(strtolower($v['status'])=='active')?"Active":"Closed";
                $contract_array['created_on']=currentDate();
                $contract_array['description']='Not Provided';
                $contract_array['is_migrated']=1;
                $contract_array['contract_value']=$v['project_value'];
                //$contract_id =1;
                // print_r($contract_array);exit;
                $contract_id=$this->User_model->insert_data('contract',$contract_array);
                $stake_holder_lables = array('contract_id'=>$contract_id,'lable1'=>'Account Managers','lable2'=>'Delivery Managers','lable3'=>'Contract Managers','created_by'=>$uploadedUserId,'created_on' => currentDate());
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                $document_data =[];
                if(!empty($v['link_1']))
                {
                    $document1 =array(
                        'module_type'=>'contract_review',
                        'reference_id'=>$contract_id,
                        'reference_type'=>'contract',
                        'document_name'=>'contract file 1',
                        'document_type'=>1,
                        'document_source'=>$v['link_1'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>$uploadedUserId, 
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$document1);
                }
                // if(!empty($v['link_2']))
                // {
                //     $document2 =array(
                //         'module_type'=>'contract_review',
                //         'reference_id'=>$contract_id,
                //         'reference_type'=>'contract',
                //         'document_name'=>'contract file 2',
                //         'document_type'=>1,
                //         'document_source'=>$v['link_2'],
                //         'document_mime_type'=>'URL',
                //         'validator_record'=>0,
                //         'uploaded_by'=>$uploadedUserId, 
                //         'uploaded_on'=>currentDate(),
                //         'updated_on'=>currentDate()
                //     );
                //     array_push($document_data,$document2);
                // }
                //print_r($document_data);
                if(count($document_data)>0){
                    $this->Document_model->addBulkDocuments($document_data);
                }
        
                 $this->User_model->update_data('moodys_contracts_upload',array('is_migrated'=>1),array('id'=>$v['id']));

                //tags
                
                $this->User_model->insert_data('contract_tags',array('tag_id'=>77,'tag_option'=>0,'tag_option_value'=>$v['automatic_renewal_period'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //77
                $this->User_model->insert_data('contract_tags',array('tag_id'=>78,'tag_option'=>0,'tag_option_value'=>$v['termination_notice_period'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //78
                $this->User_model->insert_data('contract_tags',array('tag_id'=>79,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //79
                $this->User_model->insert_data('contract_tags',array('tag_id'=>80,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //80
                $this->User_model->insert_data('contract_tags',array('tag_id'=>81,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //81
                $this->User_model->insert_data('contract_tags',array('tag_id'=>88,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>98,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>99,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>101,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>100,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>102,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //102
                $this->User_model->insert_data('contract_tags',array('tag_id'=>120,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 

                if(strtolower($v['contract_from_owner_tag'])=='aqm'){
                    $contract_from_owner_tag_id=339;
                    $contract_from_owner_tag_val='AQM';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='dialog'){
                    $contract_from_owner_tag_id=340;
                    $contract_from_owner_tag_val='Dialog';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='individual inc'){
                    $contract_from_owner_tag_id=341;
                    $contract_from_owner_tag_val='Individual Inc';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='licensor / content provider'){
                    $contract_from_owner_tag_id=342;
                    $contract_from_owner_tag_val='Licensor / Content Provider';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='syndication suite'){
                    $contract_from_owner_tag_id=343;
                    $contract_from_owner_tag_val='Syndication Suite';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='west'){
                    $contract_from_owner_tag_id=344;
                    $contract_from_owner_tag_val='West';
                }
                elseif(strtolower($v['contract_from_owner_tag'])=='yellowbrix'){
                    $contract_from_owner_tag_id=345;
                    $contract_from_owner_tag_val='YellowBrix';
                }
                else{
                    $contract_from_owner_tag_id=0;
                    $contract_from_owner_tag_val= NULL;
                }

                $this->User_model->insert_data('contract_tags',array('tag_id'=>162,'tag_option'=>$contract_from_owner_tag_id,'tag_option_value'=>$contract_from_owner_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>163,'tag_option'=>0,'tag_option_value'=>$v['initial_contract_end_date_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 

                if(strtolower($v['transalation_rights_tag'])=='hold off for now'){
                    $transalation_rights_tag_id=349;
                    $transalation_rights_tag_val='hold off for now';
                }
                elseif(strtolower($v['transalation_rights_tag'])=='in process'){
                    $transalation_rights_tag_id=348;
                    $transalation_rights_tag_val='in process';
                }
                elseif(strtolower($v['transalation_rights_tag'])=='no'){
                    $transalation_rights_tag_id=350;
                    $transalation_rights_tag_val='No';
                }
                elseif(strtolower($v['transalation_rights_tag'])=='pending'){
                    $transalation_rights_tag_id=347;
                    $transalation_rights_tag_val='pending';
                }
                elseif(strtolower($v['transalation_rights_tag'])=='see nla'){
                    $transalation_rights_tag_id=351;
                    $transalation_rights_tag_val='see NLA';
                }
                elseif(strtolower($v['transalation_rights_tag'])=='yes'){
                    $transalation_rights_tag_id=346;
                    $transalation_rights_tag_val='yes';
                }
                else{
                    $transalation_rights_tag_id=0;
                    $transalation_rights_tag_val=Null;
                }

                $this->User_model->insert_data('contract_tags',array('tag_id'=>164,'tag_option'=>$transalation_rights_tag_id,'tag_option_value'=>$transalation_rights_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 
                $this->User_model->insert_data('contract_tags',array('tag_id'=>154,'tag_option'=>0,'tag_option_value'=>$v['temp_contract_id_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); 

                
                $this->User_model->insert_data('contract_tags',array('tag_id'=>152,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1)); //this is not their in test only in prod

                //obligations
                if((!empty($v['obligation_1'])) )
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "The Retention period is specified as ".$v['obligation_1'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>null,
                        'logic'=>null,
                        'email_send_start_date'=>null,
                        'email_send_last_date'=>null,
                        'notification_message'=>'',
                        'email_notification'=>0,
                        'resend_recurrence_id'=>null,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                }
                if((!empty($v['obligation_2'])))
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "Assignment (by AQM) is ".$v['obligation_2'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>null,
                        'logic'=>null,
                        'email_send_start_date'=>null,
                        'email_send_last_date'=>null,
                        'notification_message'=>'',
                        'email_notification'=>0,
                        'resend_recurrence_id'=>null,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                }
                if(!empty($v['obligation_3_start']))
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "Date notice due is ".$v['obligation_3_start'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>30,
                        'logic'=>0,
                        'email_send_start_date'=>$v['obligation_3_start'],
                        'email_send_last_date'=>null,
                        'notification_message'=>'your notice due date for this contract is 30 days from now',
                        'email_notification'=>1,
                        'resend_recurrence_id'=>5,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                    if($obligation_id)
                    {
                        $new_dates = array();
                        //creating records for mail sending 
                        if($ins_data['email_notification'] == 1)
                        {
                            $ins_data['obligation_id'] =$obligation_id;
                            $no_of_days =$ins_data["no_of_days"];
                            $date = new DateTime($ins_data["email_send_start_date"]);
                            if($ins_data["logic"] == "0")
                            {
                                $date->modify("-$no_of_days day");
                            }
                            else{
                                $date->modify("+$no_of_days day");
                            }
                            $date1 =  $date->format("Y-m-d");
                            //$date2 = $data["email_send_last_date"];                               
                            $new_dates = array();
                            $new_dates[] = $date1;
                        }
                        if(count($new_dates)>0){
                            $insert_data = [];
                            foreach ($new_dates as $dt=>$dv) {
                                $insert_data[] = 
                                array(
                                    'contract_id'=> $ins_data['contract_id'],
                                    'obligation_id'=>$obligation_id,
                                    'date'=>$dv,
                                    'status'=>1,
                                    'mail_status'=>0,//for production 0
                                    'created_by'=> $uploadedUserId,
                                    'created_on'=> currentDate()); 
                            }
                            //print_r($insert_data);
                            //'status'=>$data['email_notification'],
                            if(count($insert_data) > 0)
                            {
                                $this->User_model->batch_insert('obligations_and_rights_mail',$insert_data);
                            }
                        }
                    }
                }

                // $obligation_id =12;
               
                echo '<br>';
                echo $k ." ".$v['contract_name'].' Moodys contract dumped successfully';
                
            }
        }
        public function dumpMoodysRelationsData(){
            $query="SELECT mr.*,p.id_provider,
            (SELECT top.id_tag_option FROM tag_option top LEFT JOIN tag_option_language tol on top.id_tag_option=tol.tag_option_id  WHERE top.tag_id=248 AND tol.tag_option_name=mr.Strategic_Level) strategic_level_tag_opt_id,
            (SELECT top.id_tag_option FROM tag_option top LEFT JOIN tag_option_language tol on top.id_tag_option=tol.tag_option_id  WHERE top.tag_id=249 AND tol.tag_option_name=mr.Risk_Type) risk_type_tag_opt_id,
            (SELECT top.id_tag_option FROM tag_option top LEFT JOIN tag_option_language tol on top.id_tag_option=tol.tag_option_id  WHERE top.tag_id=250 AND tol.tag_option_name=mr.Risk_Level) risk_level_tag_opt_id
            FROM moodys_relation_tag_data_new mr
            LEFT JOIN provider p on mr.check_Relation_name_TEST=p.provider_name
            WHERE p.customer_id=27 ";
            $get_tags_data = $this->User_model->custom_query($query);
            echo '<pre>';
            // print_r($get_tags_data);exit;
            $provider_tags_data=array();
            foreach($get_tags_data as $k=>$v){
                $provider_tags_data[]= array(
                'tag_id'=>248,
                'tag_option'=>$v['strategic_level_tag_opt_id'],
                'tag_option_value'=>$v['Strategic_Level'],
                'provider_id'=>$v['id_provider'],
                'created_on'=>currentDate(),
                'created_by'=>203,
                'status'=>1
               );
                $provider_tags_data[]= array(
                'tag_id'=>249,
                'tag_option'=>$v['risk_type_tag_opt_id'],
                'tag_option_value'=>$v['Risk_Type'],
                'provider_id'=>$v['id_provider'],
                'created_on'=>currentDate(),
                'created_by'=>203,
                'status'=>1
               );
                $provider_tags_data[]= array(
                'tag_id'=>250,
                'tag_option'=>$v['risk_level_tag_opt_id'],
                'tag_option_value'=>$v['Risk_Level'],
                'provider_id'=>$v['id_provider'],
                'created_on'=>currentDate(),
                'created_by'=>203,
                'status'=>1
               );
            }
            if(!empty($provider_tags_data)){
                $this->User_model->batch_insert('provider_tags',$provider_tags_data);
                echo 'relation tags dumped successfully';
            }
            else{
                echo'unable to dump relation tags';
            }
    
        }

        public function dumprelationsUsers(){
            echo'<pre>';    
            $customer_id=9;
            $uploadedUserId = 203;
            $password = md5("Moodys@2022");
            $query="SELECT mr.*,p.id_provider 
            FROM `moodys_relation_user_data` mr 
            LEFT JOIN provider p on mr.relation=p.provider_name
            WHERE p.customer_id=".$customer_id;
            $getuser_data=$this->User_model->custom_query($query);
            $user_data=array();
            foreach($getuser_data as $k=>$v){
                
                if(strlen($v['email1'])>0){
                    $user_data[] = array(
                        'user_role_id' => 7,
                        'customer_id' => $customer_id,
                        'first_name' => $v['first_name1'],
                        'last_name' => $v['last_name1'],
                        'email' => $v['email1'],
                        'password' => $password,
                        'language_id' => 1,
                        'created_by' => $uploadedUserId,
                        'created_on' => currentDate(),
                        'user_status' => 1,
                        'provider' => $v['id_provider'],
                        'is_allow_all_bu' => 0,
                        'contribution_type'=>2,
                        'is_migrated' =>1,
                    );
                }
                if(strlen($v['email2'])>0){
                    $user_data[] = array(
                        'user_role_id' => 7,
                        'customer_id' => $customer_id,
                        'first_name' => $v['first_name2'],
                        'last_name' => $v['last_name2'],
                        'email' => $v['email2'],
                        'password' => $password,
                        'language_id' => 1,
                        'created_by' => $uploadedUserId,
                        'created_on' => currentDate(),
                        'user_status' => 1,
                        'provider' => $v['id_provider'],
                        'is_allow_all_bu' => 0,
                        'contribution_type'=>2,
                        'is_migrated' =>1,
                    );
                }
                if(strlen($v['email3'])>0){
                    $user_data[] = array(
                        'user_role_id' => 7,
                        'customer_id' => $customer_id,
                        'first_name' => $v['first_name3'],
                        'last_name' => $v['last_name3'],
                        'email' => $v['email3'],
                        'password' => $password,
                        'language_id' => 1,
                        'created_by' => $uploadedUserId,
                        'created_on' => currentDate(),
                        'user_status' => 1,
                        'provider' => $v['id_provider'],
                        'is_allow_all_bu' => 0,
                        'contribution_type'=>2,
                        'is_migrated' =>1,
                    );
                }
            }
            if(!empty($user_data)){
                // print_r($user_data);exit;
                $this->User_model->batch_insert('user',$user_data);
                echo 'relation users dumped successfully';
            }
            else{
                echo'unable to dump relation users';
            }
        }


        public function relationTagChanges()
        {
            $get_mig_data=$this->User_model->check_record('moodys_relation_tags_changes',array('is_migrated'=>0));
            echo "<pre>";
            $customer_id = 27;
            foreach($get_mig_data as $k=>$v){
                $providerDeatils = $this->User_model->check_record('provider',array('customer_id' => $customer_id , 'unique_id' => $v['scp_id']));
                if(!empty($providerDeatils))
                {
                    $provider_id = $providerDeatils[0]['id_provider'];
                    //Strategic Level
                    $query="SELECT * from provider_tags where provider_id = $provider_id and tag_id = 159";
                    $strategic_level_data = $this->User_model->custom_query($query);
                    if($v['strategic_level'] == 'High')
                    {
                        $tag_option  = 328;
                        $tag_option_value = 'High';

                    }
                    elseif($v['strategic_level'] == 'Low')
                    {
                        $tag_option  = 330;
                        $tag_option_value = 'Low';
                    }
                    elseif($v['strategic_level'] == 'Medium')
                    {
                        $tag_option  = 329;
                        $tag_option_value = 'Medium';
                    }
                    else
                    {
                        $tag_option  = 0;
                        $tag_option_value = null;
                    }

                    if(!empty($strategic_level_data))
                    {
                        $strategic_level_data_update= array(
                            'tag_id'=>159,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value ,
                            'provider_id'=>$provider_id,
                            'updated_on'=>currentDate(),
                            'updated_by'=>203,
                            'status'=>1
                           );
                        $this->User_model->update_data('provider_tags',$strategic_level_data_update,array('id_provider_tags'=>$strategic_level_data[0]['id_provider_tag']));
                    }
                    else
                    {
                        $strategic_level_data_insert = array(
                            'tag_id'=>159,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value,
                            'provider_id'=>$provider_id,
                            'created_on'=>currentDate(),
                            'created_by'=>203,
                            'status'=>1
                           );

                        $this->User_model->insert_data('provider_tags',$strategic_level_data_insert);

                    }

                    //Risk type
                    $query="SELECT * from provider_tags where provider_id = $provider_id and tag_id = 160";
                    $risk_type_data = $this->User_model->custom_query($query);
                    if($v['risk_type'] == 'Overall service disruption')
                    {
                        $tag_option  = 331;
                        $tag_option_value = 'Overall service disruption';

                    }
                    elseif($v['risk_type'] == 'Coverage/Quality disruption')
                    {
                        $tag_option  = 332;
                        $tag_option_value = 'Coverage/Quality disruption';
                    }
                    elseif($v['risk_type'] == 'Fees increase')
                    {
                        $tag_option  = 333;
                        $tag_option_value = 'Fees increase';
                    }
                    elseif($v['risk_type'] == 'Deterioration of legal/usage terms')
                    {
                        $tag_option  = 334;
                        $tag_option_value = 'Deterioration of legal/usage terms';
                    }
                    elseif($v['risk_type'] == 'No risk identified/foreseen')
                    {
                        $tag_option  = 335;
                        $tag_option_value = 'No risk identified/foreseen';
                    }
                    else
                    {
                        $tag_option  = 0;
                        $tag_option_value = null;
                    }

                    if(!empty($risk_type_data))
                    {
                        $risk_type_data_update= array(
                            'tag_id'=>160,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value ,
                            'provider_id'=>$provider_id,
                            'updated_on'=>currentDate(),
                            'updated_by'=>203,
                            'status'=>1
                           );

                        $this->User_model->update_data('provider_tags',$risk_type_data_update,array('id_provider_tags'=>$risk_type_data[0]['id_provider_tag']));
                    }
                    else
                    {
                        $risk_type_data_insert= array(
                            'tag_id'=>160,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value,
                            'provider_id'=>$provider_id,
                            'created_on'=>currentDate(),
                            'created_by'=>203,
                            'status'=>1
                           );

                        $this->User_model->insert_data('provider_tags',$risk_type_data_insert);

                    }
                    //Risk level
                    $query="SELECT * from provider_tags where provider_id = $provider_id and tag_id = 161";
                    $risk_level_data = $this->User_model->custom_query($query);
                    if($v['risk_level'] == 'High')
                    {
                        $tag_option  = 336;
                        $tag_option_value = 'High';

                    }
                    elseif($v['risk_level'] == 'Medium')
                    {
                        $tag_option  = 337;
                        $tag_option_value = 'Medium';
                    }
                    elseif($v['risk_level'] == 'Low')
                    {
                        $tag_option  = 338;
                        $tag_option_value = 'Low';
                    }
                    else
                    {
                        $tag_option  = 0;
                        $tag_option_value = null;
                    }
 
                    if(!empty($risk_level_data))
                    {
                        $risk_level_data_update= array(
                            'tag_id'=>161,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value ,
                            'provider_id'=>$provider_id,
                            'updated_on'=>currentDate(),
                            'updated_by'=>203,
                            'status'=>1
                        );

                        $this->User_model->update_data('provider_tags',$risk_level_data_update,array('id_provider_tags'=>$risk_level_data[0]['id_provider_tag']));
                    }
                    else
                    {
                        $risk_level_data_insert= array(
                            'tag_id'=>161,
                            'tag_option'=>$tag_option,
                            'tag_option_value'=>$tag_option_value,
                            'provider_id'=>$provider_id,
                            'created_on'=>currentDate(),
                            'created_by'=>203,
                            'status'=>1
                        );

                        $this->User_model->insert_data('provider_tags',$risk_level_data_insert);

                    }

                    
                //echo $this->db->last_query();exit;

                $this->User_model->update_data('moodys_currency_tag',array('is_moved'=>1),array('id'=>$v['id']));

                    
                }
                print_r($v);
            }

        }

        public function MoodysRelationDelete()
        {
          echo "<pre>";
            $moodysRelationdata = $this->User_model->check_record('moodys_relation_delete',array('is_deleted'=>0));
            foreach($moodysRelationdata as $k=>$v){
                $relationdetails = $this->User_model->check_record('provider',array('unique_id'=>$v['relation_unique_id'] ,'customer_id' => 9));
                if(!empty($relationdetails))
                {
                    $relationContract = $this->User_model->check_record('contract',array('provider_name'=>$relationdetails[0]['id_provider'])); 
                    if(!empty($relationContract))
                    {
                        foreach($relationContract as $key => $value)
                        {
                            $this->User_model->delete('contract_tags' , array('contract_id' => $value['id_contract']));
                            $this->User_model->delete('document' , array('module_type' => 'contract_review' ,'reference_id' => $value['id_contract'] ,'reference_type' => 'contract'));

                            $contractObligations = $this->User_model->check_record('obligations_and_rights' ,array('contract_id' => $value['id_contract'] ));
                            if(!empty($contractObligations))
                            {
                                foreach($contractObligations as $obligationKey =>$obligationValue)
                                {
                                    $this->User_model->delete('obligations_and_rights_mail' , array('obligation_id' => $obligationValue['id_obligation'] ,'contract_id' => $value['id_contract']));
                                    $this->User_model->delete('obligations_and_rights' , array('id_obligation' => $obligationValue['id_obligation']));
                                }
                            }
                            $this->User_model->delete('event_feeds' , array('reference_type' => 'contract' , 'reference_id' => $value['id_contract']));
                            $this->User_model->delete('contract_stakeholder_lables' , array('contract_id' => $value['id_contract']));
                            $this->User_model->delete('contract' , array('id_contract' => $value['id_contract']));
                        }
                    
                    }
                   
                    $this->User_model->delete('provider_tags' , array('provider_id' => $relationdetails[0]['id_provider']));
                    $this->User_model->delete('user' , array('provider' => $relationdetails[0]['id_provider']));
                    $this->User_model->delete('event_feeds' , array('reference_type' =>'provider','reference_id'=> $relationdetails[0]['id_provider']));
                    $this->User_model->delete('document' , array('reference_type' =>'provider','reference_id' => $relationdetails[0]['id_provider'] ,'module_id' => $relationdetails[0]['id_provider'], 'module_type' => 'provider'));
                    $this->User_model->delete('contract_stakeholder_lables' , array('provider_id' => $relationdetails[0]['id_provider']));
                    $this->User_model->delete('provider' , array('id_provider' => $relationdetails[0]['id_provider']));
                    $this->User_model->update_data('moodys_relation_delete',array('is_deleted'=>1),array('id'=>$v['id']));
                    echo $k .$relationdetails[0]['provider_name']. " is deleted successfully";
                }
                // exit;
                // print_r($relationdetails);exit;

            }
        }

        public function uniqueIdTest()
        {
            echo uniqueId(array('module' => 'project' , 'customer_id' => 4));
        }
        public function uploadRelationContract()
        {
            echo "<pre>";
            $customer_id=9; 
            $uploadedUserId = 203;
            $getContracts=$this->User_model->check_record('moodys_contract_relation_upload',array('is_moved'=>0));

            foreach($getContracts as $k=>$v)
            {

                $contract_array['contract_unique_id'] = uniqueId(array('module' => 'contract' , 'customer_id' => $customer_id));
                $moodys_relation_data = $this->User_model->check_record('provider',array('customer_id'=>$customer_id,'provider_name' => $v['relation'] ));
                if(!empty($moodys_relation_data))
                {
                    $contract_array['provider_name']=$moodys_relation_data[0]['id_provider'];
                }
                else
                {
                    //creating new relation 
                    if(!empty($v['relation_country']))
                    {
                        $country_details = $this->User_model->check_record('country',array('country_name'=>$v['relation_country']));
                        if(!empty($country_details))
                        {
                            $country_id = $country_details[0]['id_country'];
                        }
                        else
                        {
                            $country_id = NULL;
                        }
                    }
                    else
                    {
                        $country_id = NULL;
                    }
                    $provider_unique_id = null ;
                    $provider_unique_id = uniqueId(array('module' => 'provider' , 'customer_id' => $customer_id));
                    $providerArray = array(
                        'provider_name' => $v['relation'],
                        'unique_id' => $provider_unique_id,
                        'customer_id' => $customer_id,
                        'is_migrated' => 1,
                        'created_by' => $uploadedUserId,
                        'country' => $country_id,
                        'created_on' => currentDate()
                    );
                    // print_r($providerArray);exit;
                    
                    $contract_array['provider_name'] = $provider_id=$this->User_model->insert_data('provider',$providerArray);
                  

                    //stakeholder

                    $stake_holder_lables = array('provider_id'=>$provider_id,'lable1'=>'DIMS','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>$uploadedUserId,'created_on' => currentDate(),'contract_id'=>0);
                    $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);

                    //tags
                    $emptyTagarray = array(82,83,84,85,86,87,95,96,97,106,118,153,159,160,161);
                    foreach($emptyTagarray as $empTag){
                    $this->User_model->insert_data('provider_tags',array('tag_id'=>$empTag,'tag_option'=>0,'tag_option_value'=>Null,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                    }

                    if(!empty($v['relation_tag_2023_risk']))
                    {
                        if(strtolower($v['relation_tag_2023_risk'])=='1'){
                            $relation_tag_2023_risk_option_id=403;
                            $relation_tag_2023_risk_option_val='1';
                        }
                        elseif(strtolower($v['relation_tag_2023_risk'])=='2'){
                            $relation_tag_2023_risk_option_id=404;
                            $relation_tag_2023_risk_option_val='2';
                        }
                        elseif(strtolower($v['relation_tag_2023_risk'])=='3'){
                            $relation_tag_2023_risk_option_id=405;
                            $relation_tag_2023_risk_option_val='3';
                        }
                        elseif(strtolower($v['relation_tag_2023_risk'])=='4'){
                            $relation_tag_2023_risk_option_id=406;
                            $relation_tag_2023_risk_option_val='4';
                        }
                        elseif(strtolower($v['relation_tag_2023_risk'])=='5'){
                            $relation_tag_2023_risk_option_id=407;
                            $relation_tag_2023_risk_option_val='5';
                        }
                        elseif(strtolower($v['relation_tag_2023_risk'])=='n/a'){
                            $relation_tag_2023_risk_option_id=485;
                            $relation_tag_2023_risk_option_val='N/A';
                        }
                        else{
                            $relation_tag_2023_risk_option_id=0;
                            $relation_tag_2023_risk_option_val=Null;
                        }
                        $this->User_model->insert_data('provider_tags',array('tag_id'=>190,'tag_option'=>$relation_tag_2023_risk_option_id,'tag_option_value'=>$relation_tag_2023_risk_option_val,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                    }
                    else
                    {
                        $this->User_model->insert_data('provider_tags',array('tag_id'=>153,'tag_option'=>0,'tag_option_value'=>Null,'provider_id'=>$provider_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1));
                    }
                    
                }
                $contract_array['contract_name']=$v['contract_name'];
                $contract_array['contract_start_date']=!empty($v['contract_start_date'])?$v['contract_start_date']:null;
                $contract_array['contract_end_date']=!empty($v['contract_end_date'])?$v['contract_end_date']:null;
                $contract_array['auto_renewal']=strtolower($v['automatic_prolongation'])=='yes'?1:0;
                $contract_array['template_id']=0;
                $contract_array['can_review']=0;
                $contract_array['relationship_category_id']='53'; 
                $contract_array['currency_id'] = 185;
                $contract_array['business_unit_id']=83;
                $contract_array['contract_owner_id']=562;
                $contract_array['delegate_id']=563;
                $contract_array['type']='contract';
                $contract_array['is_deleted']=0;
                $contract_array['created_by']=$uploadedUserId;
                $contract_array['contract_status']='new';
                $contract_array['parent_contract_id']=0;
                $contract_array['contract_active_status']=(strtolower($v['contract_status'])=='active')?"Active":"Closed";
                $contract_array['created_on']=currentDate();
                $contract_array['description']='No Description';
                $contract_array['is_migrated']=1;
                $contract_array['contract_value']=0;
                $contract_id=$this->User_model->insert_data('contract',$contract_array);
                $stake_holder_lables = array('contract_id'=>$contract_id,'lable1'=>'Account Managers','lable2'=>'Delivery Managers','lable3'=>'Contract Managers','created_by'=>$uploadedUserId,'created_on' => currentDate());
                $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
                $document_data =[];
                if(!empty($v['contract_link_1']))
                {
                    $document1 =array(
                        'module_type'=>'contract_review',
                        'reference_id'=>$contract_id,
                        'reference_type'=>'contract',
                        'document_name'=>'contract file 1',
                        'document_type'=>1,
                        'document_source'=>$v['contract_link_1'],
                        'document_mime_type'=>'URL',
                        'validator_record'=>0,
                        'uploaded_by'=>$uploadedUserId, 
                        'uploaded_on'=>currentDate(),
                        'updated_on'=>currentDate()
                    );
                    array_push($document_data,$document1);
                }
                    
                if(count($document_data)>0){
                    $this->Document_model->addBulkDocuments($document_data);
                }
        
                $this->User_model->update_data('moodys_contrcat_relation_upload',array('is_moved'=>1),array('id'=>$v['id']));

                //tags

                $tags = [];
                $tags[0] = array(
                    'tag_id'=>77,
                    'tag_option'=>0,
                    'tag_option_value'=>$v['automatic_renewal_period_tag'],
                    'contract_id'=>$contract_id,
                    'created_on'=>currentDate(),
                    'created_by'=>$uploadedUserId,
                    'status'=>1
                );
                $tags[1] = array(
                    'tag_id'=>78,'tag_option'=>0,'tag_option_value'=>$v['termination_notice_period_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[2] = array(
                    'tag_id'=>79,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[3] = array(
                    'tag_id'=>80,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[4] = array(
                    'tag_id'=>81,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[5] = array(
                    'tag_id'=>88,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[6] = array(
                    'tag_id'=>98,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[7] = array(
                    'tag_id'=>99,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[8] = array(
                    'tag_id'=>100,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[9] = array(
                    'tag_id'=>101,'tag_option'=>0,'tag_option_value'=>NULL,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[10] = array(
                    'tag_id'=>102,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[11] = array(
                    'tag_id'=>120,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[12] = array(
                    'tag_id'=>152,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[13] = array(
                    'tag_id'=>154,'tag_option'=>0,'tag_option_value'=>Null,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
              
      

                //new tags

                if(strtolower($v['contract_form_owner_tag'])=='aqm'){
                    $contract_form_owner_tag_id=339;
                    $contract_form_owner_tag_val='AQM';
                }
                elseif(strtolower($v['contract_form_owner_tag'])=='dialog'){
                    $contract_form_owner_tag_id=340;
                    $contract_form_owner_tag_val='Dialog';
                }
                elseif(strtolower($v['contract_form_owner_tag'])=='individual inc'){
                    $contract_form_owner_tag_id=341;
                    $contract_form_owner_tag_val='Individual Inc';
                }
                elseif(strtolower($v['contract_form_owner_tag'])=='licensor / content provider'){
                    $contract_form_owner_tag_id=342;
                    $contract_form_owner_tag_val='Licensor / Content Provider';
                }
               
                elseif(strtolower($v['contract_form_owner_tag'])=='west'){
                    $contract_form_owner_tag_id=344;
                    $contract_form_owner_tag_val='West';
                }
                elseif(strtolower($v['contract_form_owner_tag'])=='yellowbrix'){
                    $contract_form_owner_tag_id=345;
                    $contract_form_owner_tag_val='YellowBrix';
                }
                elseif(strtolower($v['contract_form_owner_tag'])=='qm'){
                    $contract_form_owner_tag_id=402;
                    $contract_form_owner_tag_val='QM';
                }
                else{
                    $contract_form_owner_tag_id=0;
                    $contract_form_owner_tag_val=Null;
                }

                $tags[14] = array(
                    'tag_id'=>162,'tag_option'=>$contract_form_owner_tag_id,'tag_option_value'=>$contract_form_owner_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[15] = array(
                    'tag_id'=>163,'tag_option'=>0,'tag_option_value'=>$v['initial_contract_end_date_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

                if(strtolower($v['translation_rights_tag'])=='yes'){
                    $translation_rights_tag_id=346;
                    $translation_rights_tag_val='Yes';
                }
                elseif(strtolower($v['translation_rights_tag'])=='no'){
                    $translation_rights_tag_id=350;
                    $translation_rights_tag_val='no';
                }
                else{
                    $translation_rights_tag_id=0;
                    $translation_rights_tag_val=Null;
                }
                $tags[16] = array(
                    'tag_id'=>164,'tag_option'=>$translation_rights_tag_id,'tag_option_value'=>$translation_rights_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );


                if(strtolower($v['type_tag'])=='news'){
                    $type_tag_id=408;
                    $type_tag_val='News';
                }
                elseif(strtolower($v['type_tag'])=='data'){
                    $type_tag_id=409;
                    $type_tag_val='Data';
                }
                else{
                    $type_tag_id=0;
                    $type_tag_val=Null;
                }

                $tags[17] = array(
                    'tag_id'=>191,'tag_option'=>$type_tag_id,'tag_option_value'=>$type_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[18] = array(
                    'tag_id'=>192,'tag_option'=>0,'tag_option_value'=>$v['aqm_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

      

              

                if(strtolower($v['full_feed_tag'])=='y'){
                    $full_feed_tag_id=410;
                    $full_feed_tag_val='Yes';
                }
                elseif(strtolower($v['full_feed_tag'])=='n'){
                    $full_feed_tag_id=411;
                    $full_feed_tag_val='No';
                }
                elseif(strtolower($v['full_feed_tag'])=='n/a'){
                    $full_feed_tag_id=412;
                    $full_feed_tag_val='N/A';
                }
                else{
                    $full_feed_tag_id=0;
                    $full_feed_tag_val=Null;
                }
                $tags[19] = array(
                    'tag_id'=>193,'tag_option'=>$full_feed_tag_id,'tag_option_value'=>$full_feed_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                

                if(strtolower($v['full_feed_pricing_tag'])=='royalty free'){
                    $full_feed_pricing_tag_id=413;
                    $full_feed_pricing_tag_val='Royalty Free';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='aqm discretion'){
                    $full_feed_pricing_tag_id=414;
                    $full_feed_pricing_tag_val='AQM Discretion';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='ip discretion'){
                    $full_feed_pricing_tag_id=415;
                    $full_feed_pricing_tag_val='IP Discretion';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='aqm discretion, ip approval'){
                    $full_feed_pricing_tag_id=416;
                    $full_feed_pricing_tag_val='AQM Discretion, IP Approval';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='ip list pricing'){
                    $full_feed_pricing_tag_id=417;
                    $full_feed_pricing_tag_val='IP List Pricing';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='direct bill'){
                    $full_feed_pricing_tag_id=418;
                    $full_feed_pricing_tag_val='Direct Bill';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='ip discretion, ip list pricing'){
                    $full_feed_pricing_tag_id=419;
                    $full_feed_pricing_tag_val='IP Discretion, IP List Pricing';
                }
                elseif(strtolower($v['full_feed_pricing_tag'])=='n/a'){
                    $full_feed_pricing_tag_id=422;
                    $full_feed_pricing_tag_val='N/A';
                }
                else{
                    $full_feed_pricing_tag_id=0;
                    $full_feed_pricing_tag_val=Null;
                }

                $tags[20] = array(
                    'tag_id'=>194,'tag_option'=>$full_feed_pricing_tag_id,'tag_option_value'=>$full_feed_pricing_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

              

                if(strtolower($v['full_feed_schedule_tag'])=='annual'){
                    $full_feed_schedule_tag_id=423;
                    $full_feed_schedule_tag_val='Annual';
                }
                elseif(strtolower($v['full_feed_schedule_tag'])=='direct bill'){
                    $full_feed_schedule_tag_id=424;
                    $full_feed_schedule_tag_val='Direct Bill';
                }
                elseif(strtolower($v['full_feed_schedule_tag'])=='monthly'){ 
                    $full_feed_schedule_tag_id=425;
                    $full_feed_schedule_tag_val='Monthly';
                }
                elseif(strtolower($v['full_feed_schedule_tag'])=='n/a'){
                    $full_feed_schedule_tag_id=426;
                    $full_feed_schedule_tag_val='N/A';
                }
                elseif(strtolower($v['full_feed_schedule_tag'])=='quarterly'){
                    $full_feed_schedule_tag_id=428;
                    $full_feed_schedule_tag_val='Quarterly';
                }
                elseif(strtolower($v['full_feed_schedule_tag'])=='royalty free'){
                    $full_feed_schedule_tag_id=429;
                    $full_feed_schedule_tag_val='Royalty Free';
                }
                else{
                    $full_feed_schedule_tag_id=0;
                    $full_feed_schedule_tag_val=Null;
                }

               

                $tags[21] = array(
                    'tag_id'=>195,'tag_option'=>$full_feed_schedule_tag_id,'tag_option_value'=>$full_feed_schedule_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );


                if(strtolower($v['full_feed_invoice_tag'])=='aqm'){
                    $full_feed_invoice_tag_id=430;
                    $full_feed_invoice_tag_val='AQM';
                }
                elseif(strtolower($v['full_feed_invoice_tag'])=='ip'){
                    $full_feed_invoice_tag_id=431;
                    $full_feed_invoice_tag_val='IP';
                }
                else{
                    $full_feed_invoice_tag_id=0;
                    $full_feed_invoice_tag_val=Null;
                }
                $tags[22] = array(
                    'tag_id'=>196,'tag_option'=>$full_feed_invoice_tag_id,'tag_option_value'=>$full_feed_invoice_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

                $tags[23] = array(
                    'tag_id'=>197,'tag_option'=>0,'tag_option_value'=>$v['full_feed_note_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
     
                

            
                if(strtolower($v['topics_package_newsedge_tag'])=='y'){
                    $topics_package_newsedge_tag_id=432;
                    $topics_package_newsedge_tag_val='Yes';
                }
                elseif(strtolower($v['topics_package_newsedge_tag'])=='n'){
                    $topics_package_newsedge_tag_id=433;
                    $topics_package_newsedge_tag_val='No';
                }
                elseif(strtolower($v['topics_package_newsedge_tag'])=='n/a'){
                    $topics_package_newsedge_tag_id=434;
                    $topics_package_newsedge_tag_val='N/A';
                }
                else{
                    $topics_package_newsedge_tag_id=0;
                    $topics_package_newsedge_tag_val=Null;
                }
                $tags[24] = array(
                    'tag_id'=>198,'tag_option'=>$topics_package_newsedge_tag_id,'tag_option_value'=>$topics_package_newsedge_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

             

                if(strtolower($v['topics_package_ics_tag'])=='y'){
                    $topics_package_ics_tag_id=435;
                    $topics_package_ics_tag_val='Yes';
                }
                elseif(strtolower($v['topics_package_ics_tag'])=='n'){
                    $topics_package_ics_tag_id=436;
                    $topics_package_ics_tag_val='No';
                }
                elseif(strtolower($v['topics_package_ics_tag'])=='n/a'){
                    $topics_package_ics_tag_id=437;
                    $topics_package_ics_tag_val='N/A';
                }
                else{
                    $topics_package_ics_tag_id=0;
                    $topics_package_ics_tag_val=Null;
                }

                $tags[25] = array(
                    'tag_id'=>199,'tag_option'=>$topics_package_ics_tag_id,'tag_option_value'=>$topics_package_ics_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

               

                if(strtolower($v['topics_package_ccs_tag'])=='y'){
                    $topics_package_ccs_tag_id=438;
                    $topics_package_ccs_tag_val='Yes';
                }
                elseif(strtolower($v['topics_package_ccs_tag'])=='n'){
                    $topics_package_ccs_tag_id=439;
                    $topics_package_ccs_tag_val='No';
                }
                elseif(strtolower($v['topics_package_ccs_tag'])=='n/a'){
                    $topics_package_ccs_tag_id=440;
                    $topics_package_ccs_tag_val='N/A';
                }
                else{
                    $topics_package_ccs_tag_id=0;
                    $topics_package_ccs_tag_val=Null;
                }
                $tags[26] = array(
                    'tag_id'=>200,'tag_option'=>$topics_package_ccs_tag_id,'tag_option_value'=>$topics_package_ccs_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                
                
                if(strtolower($v['topics_package_ocs_tag'])=='y'){
                    $topics_package_ocs_tag_id=441;
                    $topics_package_ocs_tag_val='Yes';
                }
                elseif(strtolower($v['topics_package_ocs_tag'])=='n'){
                    $topics_package_ocs_tag_id=442;
                    $topics_package_ocs_tag_val='No';
                }
                elseif(strtolower($v['topics_package_ocs_tag'])=='n/a'){
                    $topics_package_ocs_tag_id=443;
                    $topics_package_ocs_tag_val='N/A';
                }
                else{
                    $topics_package_ocs_tag_id=0;
                    $topics_package_ocs_tag_val=Null;
                }
                $tags[27] = array(
                    'tag_id'=>201,'tag_option'=>$topics_package_ocs_tag_id,'tag_option_value'=>$topics_package_ocs_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
            

                if(strtolower($v['topics_pool_payment_schedule_tag'])=='royalty free'){
                    $topics_pool_payment_schedule_tag_id=444;
                    $topics_pool_payment_schedule_tag_val='Royalty Free';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='aqm discretion'){
                    $topics_pool_payment_schedule_tag_id=445;
                    $topics_pool_payment_schedule_tag_val='aqm discretion';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='ip discretion'){
                    $topics_pool_payment_schedule_tag_id=446;
                    $topics_pool_payment_schedule_tag_val='IP Discretion';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='ip list pricing'){
                    $topics_pool_payment_schedule_tag_id=447;
                    $topics_pool_payment_schedule_tag_val='IP List Pricing';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='direct bill'){
                    $topics_pool_payment_schedule_tag_id=448;
                    $topics_pool_payment_schedule_tag_val='Direct Bill';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='annual'){
                    $topics_pool_payment_schedule_tag_id=482;
                    $topics_pool_payment_schedule_tag_val='Annual';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='monthly'){
                    $topics_pool_payment_schedule_tag_id=483;
                    $topics_pool_payment_schedule_tag_val='Monthly';
                }
                elseif(strtolower($v['topics_pool_payment_schedule_tag'])=='quarterly'){
                    $topics_pool_payment_schedule_tag_id=484;
                    $topics_pool_payment_schedule_tag_val='Quarterly';
                }
                else{
                    $topics_pool_payment_schedule_tag_id=0;
                    $topics_pool_payment_schedule_tag_val=Null;
                }
                $tags[28] = array(
                    'tag_id'=>202,'tag_option'=>$topics_pool_payment_schedule_tag_id,'tag_option_value'=>$topics_pool_payment_schedule_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );



                if(strtolower($v['minimum_guarantee_tag'])=='mg'){
                    $minimum_guarantee_tag_id=450;
                    $minimum_guarantee_tag_val='MG';
                }
                elseif(strtolower($v['minimum_guarantee_tag'])=='ff'){
                    $minimum_guarantee_tag_id=451;
                    $minimum_guarantee_tag_val='FF';
                }
                else{
                    $minimum_guarantee_tag_id=0;
                    $minimum_guarantee_tag_val=Null;
                }
                $tags[29] = array(
                    'tag_id'=>203,'tag_option'=>$minimum_guarantee_tag_id,'tag_option_value'=>$minimum_guarantee_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
              

                if(strtolower($v['topics_invoice_tag'])=='aqm'){
                    $topics_invoice_tag_id=452;
                    $topics_invoice_tag_val='AQM';
                }
                elseif(strtolower($v['topics_invoice_tag'])=='ip'){
                    $topics_invoice_tag_id=453;
                    $topics_invoice_tag_val='IP';
                }
                
                else{
                    $topics_invoice_tag_id=0;
                    $topics_invoice_tag_val=Null;
                }
                $tags[30] = array(
                    'tag_id'=>204,'tag_option'=>$topics_invoice_tag_id,'tag_option_value'=>$topics_invoice_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

          

                if(strtolower($v['reporting_requirements_tag'])=='quarterly source-level allocation with feed identifier'){
                    $reporting_requirements_tag_id=454;
                    $reporting_requirements_tag_val='Quarterly Source-Level Allocation with Feed Identifier';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='quarterly source-level allocation'){
                    $reporting_requirements_tag_id=455;
                    $reporting_requirements_tag_val='Quarterly Source-Level Allocation';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='full feed users'){
                    $reporting_requirements_tag_id=456;
                    $reporting_requirements_tag_val='Full feed users';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='notification of changes only'){
                    $reporting_requirements_tag_id=457;
                    $reporting_requirements_tag_val='Notification of changes only';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='aqm needs to report earnings to ip at end of each quarter for them to send correct invoice'){
                    $reporting_requirements_tag_id=458;
                    $reporting_requirements_tag_val='AQM needs to report earnings to IP at end of each quarter for them to send correct invoice';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='quarterly source-level allocation; full feed users'){
                    $reporting_requirements_tag_id=459;
                    $reporting_requirements_tag_val='Quarterly Source-Level Allocation; Full feed users';
                }
                elseif(strtolower($v['reporting_requirements_tag'])=='none'){
                    $reporting_requirements_tag_id=486;
                    $reporting_requirements_tag_val='None';
                }
                else{
                    $reporting_requirements_tag_id=0;
                    $reporting_requirements_tag_val=Null;
                }
                $tags[31] = array(
                    'tag_id'=>205,'tag_option'=>$reporting_requirements_tag_id,'tag_option_value'=>$reporting_requirements_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
               
                if(strtolower($v['permissioning_requirements_tag'])=='archive'){
                    $permissioning_requirements_tag_id=460;
                    $permissioning_requirements_tag_val='Archive';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ccs'){
                    $permissioning_requirements_tag_id=461;
                    $permissioning_requirements_tag_val='CCS';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='full feed'){
                    $permissioning_requirements_tag_id=462;
                    $permissioning_requirements_tag_val='Full Feed';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ir room'){
                    $permissioning_requirements_tag_id=463;
                    $permissioning_requirements_tag_val='IR Room';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ocs'){
                    $permissioning_requirements_tag_id=464;
                    $permissioning_requirements_tag_val='OCS';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ics'){
                    $permissioning_requirements_tag_id=465;
                    $permissioning_requirements_tag_val='ICS';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='uk reg'){
                    $permissioning_requirements_tag_id=466;
                    $permissioning_requirements_tag_val='UK Reg';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ccs;ocs;archive;uk reg;ir room'){
                    $permissioning_requirements_tag_id='461,464,460,466,463';
                    $permissioning_requirements_tag_val='CCS;OCS;Archive;UK Reg;IR Room';
                }
                elseif(strtolower($v['permissioning_requirements_tag'])=='ics;ccs'){
                    $permissioning_requirements_tag_id='465,461';
                    $permissioning_requirements_tag_val='ICS;CCS';
                }
                
                else{
                    $permissioning_requirements_tag_id=0;
                    $permissioning_requirements_tag_val=Null;
                }
                $tags[32] = array(
                    'tag_id'=>206,'tag_option'=>$permissioning_requirements_tag_id,'tag_option_value'=>$permissioning_requirements_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
      

                if(strtolower($v['market_restrictions_tag'])=='academic'){
                    $market_restrictions_tag_id=467;
                    $market_restrictions_tag_val='Academic';
                }
                elseif(strtolower($v['market_restrictions_tag'])=='broadcast'){
                    $market_restrictions_tag_id=468;
                    $market_restrictions_tag_val='Broadcast';
                }
                elseif(strtolower($v['market_restrictions_tag'])=='media monitoring'){
                    $market_restrictions_tag_id=469;
                    $market_restrictions_tag_val='Media-Monitoring';
                }
                elseif(strtolower($v['market_restrictions_tag'])=='academic; broadcast'){
                    $market_restrictions_tag_id='467,468';
                    $market_restrictions_tag_val='Academic; Broadcast';
                }
                
                else{
                    $market_restrictions_tag_id=0;
                    $market_restrictions_tag_val=Null;
                }
                $tags[33] = array(
                    'tag_id'=>207,'tag_option'=>$market_restrictions_tag_id,'tag_option_value'=>$market_restrictions_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );

                $tags[34] = array(
                    'tag_id'=>208,'tag_option'=>0,'tag_option_value'=>$v['customer_restrictions_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[35] = array(
                    'tag_id'=>209,'tag_option'=>0,'tag_option_value'=>$v['regional_restrictions_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[36] = array(
                    'tag_id'=>210,'tag_option'=>0,'tag_option_value'=>$v['agreement_assignment_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
               
             


                if(strtolower($v['3rd_party_licensing_group_tag'])=='triumvirate'){
                    $rd_party_licensing_group_tag_id=470;
                    $rd_party_licensing_group_tag_val='Triumvirate';
                }
                elseif(strtolower($v['3rd_party_licensing_group_tag'])=='c2 consulting'){
                    $rd_party_licensing_group_tag_id=471;
                    $rd_party_licensing_group_tag_val='C2 Consulting';
                }
                elseif(strtolower($v['3rd_party_licensing_group_tag'])=='nordot'){
                    $rd_party_licensing_group_tag_id=472;
                    $rd_party_licensing_group_tag_val='Nordot';
                }
                elseif(strtolower($v['3rd_party_licensing_group_tag'])=='marcinko'){
                    $rd_party_licensing_group_tag_id=473;
                    $rd_party_licensing_group_tag_val='Marcinko';
                }
                else{
                    $rd_party_licensing_group_tag_id=0;
                    $rd_party_licensing_group_tag_val=Null;
                }
                $tags[37] = array(
                    'tag_id'=>211,'tag_option'=>$rd_party_licensing_group_tag_id,'tag_option_value'=>$rd_party_licensing_group_tag_val,'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[38] = array(
                    'tag_id'=>212,'tag_option'=>0,'tag_option_value'=>$v['ap_number_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                $tags[39] = array(
                    'tag_id'=>213,'tag_option'=>0,'tag_option_value'=>$v['aka_fka_tag'],'contract_id'=>$contract_id,'created_on'=>currentDate(),'created_by'=>$uploadedUserId,'status'=>1
                );
                //end of tags

                $this->User_model->batch_insert('contract_tags',$tags);

                //obligations

                if((!empty($v['obligation_1'])) )
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "The Retention period is specified as ".$v['obligation_1'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>null,
                        'logic'=>null,
                        'email_send_start_date'=>null,
                        'email_send_last_date'=>null,
                        'notification_message'=>'',
                        'email_notification'=>0,
                        'resend_recurrence_id'=>null,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                }
                if((!empty($v['obligation_2'])))
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "Assignment (by AQM) is ".$v['obligation_2'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>null,
                        'logic'=>null,
                        'email_send_start_date'=>null,
                        'email_send_last_date'=>null,
                        'notification_message'=>'',
                        'email_notification'=>0,
                        'resend_recurrence_id'=>null,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                }
                if(!empty($v['obligation_3_date_notice']))
                {
                    $ins_data = array(
                        'contract_id'=> $contract_id,
                        'description'=> "Date notice due is ".$v['obligation_3_date_notice'],
                        'type'=>1,
                        'type_name'=>'Right',
                        'calendar'=>0,
                        'applicable_to'=>null,
                        'applicable_to_name'=>null,
                        'detailed_description'=>'',
                        'recurrence_id'=>null,
                        'recurrence_start_date'=>null,
                        'recurrence_end_date'=>null,
                        'no_of_days'=>30,
                        'logic'=>0,
                        'email_send_start_date'=>$v['obligation_3_date_notice'],
                        'email_send_last_date'=>null,
                        'notification_message'=>'your notice due date for this contract is 30 days from now',
                        'email_notification'=>1,
                        'resend_recurrence_id'=>5,
                        'created_by'=> $uploadedUserId,
                        'created_on'=> currentDate()
                    );
                    //print_r($ins_data);
                    $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
                    if($obligation_id)
                    {
                        $new_dates = array();
                        //creating records for mail sending 
                        if($ins_data['email_notification'] == 1)
                        {
                            $ins_data['obligation_id'] =$obligation_id;
                            $no_of_days =$ins_data["no_of_days"];
                            $date = new DateTime($ins_data["email_send_start_date"]);
                            if($ins_data["logic"] == "0")
                            {
                                $date->modify("-$no_of_days day");
                            }
                            else{
                                $date->modify("+$no_of_days day");
                            }
                            $date1 =  $date->format("Y-m-d");
                            //$date2 = $data["email_send_last_date"];                               
                            $new_dates = array();
                            $new_dates[] = $date1;
                        }
                        if(count($new_dates)>0){
                            $insert_data = [];
                            foreach ($new_dates as $dt=>$dv) {
                                $insert_data[] = 
                                array(
                                    'contract_id'=> $ins_data['contract_id'],
                                    'obligation_id'=>$obligation_id,
                                    'date'=>$dv,
                                    'status'=>1,
                                    'mail_status'=>0,//for production 0
                                    'created_by'=> $uploadedUserId,
                                    'created_on'=> currentDate()); 
                            }
                            //print_r($insert_data);
                            //'status'=>$data['email_notification'],
                            if(count($insert_data) > 0)
                            {
                                $this->User_model->batch_insert('obligations_and_rights_mail',$insert_data);
                            }
                        }
                    }
                }

                $this->User_model->update_data('moodys_contract_relation_upload',array('is_moved'=>1),array('id'=>$v['id']));
                echo $k .$v['contract_name']. " is created  successfully";
            }
        }
}