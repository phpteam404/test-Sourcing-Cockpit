<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Project extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('Project_model');
        $this->load->model('Contract_model');
        $this->load->model('User_model');
        // echo pk_decrypt('U2FsdGVkX19UaGVAMTIzNImwEIlPu6ntOI2v4F06PBU=');exit;
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        // echo '<pre>'.$this->db->last_query();exit;
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));

        
        //api access check 
        if($this->session_user_info->user_role_id == 7)
        {
            $apiaccess = Apiaccess($this->session_user_info->user_role_id , $_SERVER['PATH_INFO']);
            if(!$apiaccess)
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')));
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        
        if($this->session_user_info->user_role_id<3 || $this->session_user_info->user_role_id==5)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
        else if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4 || $this->session_user_info->user_role_id==8)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        else if($this->session_user_info->user_role_id==6){
            if($this->session_user_info->is_allow_all_bu==1)
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
            else
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        }
        $this->session_user_own_business_units=$this->session_user_business_units;
        $this->session_user_review_business_units=$this->Validation_model->getReviewBusinessUnits(array('id_user'=>$this->session_user_id));
        $this->session_user_business_units=array_merge($this->session_user_business_units,$this->session_user_review_business_units);
        // echo '<pre>'.print_r($this->session_user_business_units);exit;
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units));
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        $assigned_contracts=$this->Validation_model->getContributorContract(array('customer_user'=>$this->session_user_info->id_user));
        $this->session_user_contracts=array_merge($this->session_user_contracts,$assigned_contracts);
        $this->session_user_contract_reviews=$this->Validation_model->getContractReviews(array('contract_id'=>$this->session_user_contracts));
        $review_documents=$this->Validation_model->getContractReviewDocuments(array('contract_review_id'=>$this->session_user_contract_reviews));
        $documents=$this->Validation_model->getContractDocuments(array('contract_id'=>$this->session_user_contracts));
        $this->session_user_contract_documents=array_merge($review_documents,$documents);
        $getContractActionItems=$this->Validation_model->getContractActionItems(array('contract_id'=>$this->session_user_contracts));
        $getContractActionItemsByUser=$this->Validation_model->getContractActionItemsByUser(array('user_id'=>$this->session_user_id));
        $this->session_user_contract_action_items=array_merge($getContractActionItems,$getContractActionItemsByUser);
        $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        $this->session_user_reporting_owners=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>6));
        $this->session_user_bu_owners=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>3));
        $this->session_user_customer_admins=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>2));
        $this->session_user_customer_all_users=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id)));

        $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_relationship_classifications=$this->Validation_model->getCustomerRelationshipClassifications(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_master_currency=$this->Validation_model->getCurrency();
        // $this->session_user_master_language=$this->Validation_model->getLanguage();
        // $this->session_user_master_countries=$this->Validation_model->getCountries();
        // $this->session_user_master_templates=$this->Validation_model->getTemplates();
        // $this->session_user_master_customers=$this->Validation_model->getCustomers();
        // $this->session_user_master_users=$this->Validation_model->getUsers();
        $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();
        $this->session_user_contract_review_modules=$this->Validation_model->getContractReviewModules(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_master_contract_review_modules=$this->Validation_model->getMasterContractReviewModules();
        $this->session_user_contract_review_topics=$this->Validation_model->getContractReviewTopics(array('module_id'=>$this->session_user_contract_review_modules));
        $this->session_user_master_contract_review_topics=$this->Validation_model->getMasterContractReviewTopics();
        $this->session_user_contract_review_questions=$this->Validation_model->getContractReviewQuestions(array('topic_id'=>$this->session_user_contract_review_topics));
        // $this->session_user_master_contract_review_questions=$this->Validation_model->getContractReviewMasterQuestions();
        // $this->session_user_contract_review_question_options=$this->Validation_model->getContractReviewQuestionOptions(array('question_id'=>$this->session_user_contract_review_questions));
        // $this->session_user_master_contract_review_question_options=$this->Validation_model->getContractReviewMasterQuestionOptions();

        // $this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
        // $this->session_user_wadmin_relationship_classifications=$this->Validation_model->getCustomerRelationshipClassifications(array('customer_id'=>array(0)));

        // $this->session_user_master_template_modules=$this->Validation_model->getTemplateModules();
        // $this->session_user_master_template_module_topics=$this->Validation_model->getTemplateModuleTopics();
        // $this->session_user_master_template_module_topic_questions=$this->Validation_model->getTemplateModuleTopicQuestions();

        $this->session_user_contract_review_discussions=$this->Validation_model->getContractReviewDiscussions(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_contract_review_discussion_questions=$this->Validation_model->getContractReviewDiscussionQuestions(array('contract_review_discussion_id'=>$this->session_user_contract_review_discussions));

        $this->session_user_wadmin_email_templates=$this->Validation_model->getCustomerEmailTemplates(array('customer_id'=>array(0)));
        $this->session_user_customer_email_templates=$this->Validation_model->getCustomerEmailTemplates(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->load->model('Download_model');
        $this->router->fetch_method();
    }


    //////start ur code from here/////

    public function createProject_post(){
        $data = $this->input->post();
        if(isset($data['contract'])){
            $data = $data['contract'];
        }
        //print_r($data); exit;
        if(isset($_FILES['file']))
            $totalFilesCount = count($_FILES['file']['name']);
        else
            $totalFilesCount=0;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('contract_name', array('required'=>$this->lang->line('Project_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('project_unique_id', array('required'=>$this->lang->line('Project_uniqid_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
         }
         if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
         }
         if(isset($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all') {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
        }
         if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            if($this->session_user_info->user_role_id==4){
                $data['delegate_id']=$this->session_user_info->id_user;
            }
        }
         if(isset($data['currency_id'])) {
            $data['currency_id'] = pk_decrypt($data['currency_id']);
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if($this->session_user_info->user_role_id==3){
                $data['contract_owner_id']=$this->session_user_info->id_user;
            }
        }
        // if(!empty($data['project_unique_id'])){
        //     $check_project_unique_id_exitst=$this->User_model->getProjectsBybuid(array('project_unique_id'=>$data['project_unique_id'],'customer_id'=>$data['customer_id'],'type'=>'project'));//echo $this->db->last_query();exit;
        //     if(!empty($check_project_unique_id_exitst)){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('project_unique_id_exists')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        // $projects_contracts=$this->User_model->getProjectsBybuid(array('customer_id'=>$data['customer_id']));
        // $countofprojects=count($projects_contracts);
        // $data['project_unique_id']='PJ'.str_pad($countofprojects+1, 7, '0', STR_PAD_LEFT);
        $data['project_unique_id'] = uniqueId(array('module' => 'project' , 'customer_id' => $this->session_user_info->customer_id));

        $add = array(
            'contract_unique_id'=>$data['project_unique_id'],
            'contract_name'=>$data['contract_name'],
            'contract_start_date'=>$data['project_start_date'],
            'contract_end_date'=>!empty($data['project_end_date'])?$data['project_end_date']:null,
            'contract_value'=>$data['contract_value'],
            'business_unit_id'=>$data['business_unit_id'],
            'contract_owner_id'=>$data['contract_owner_id'],
            'currency_id'=>!empty($data['currency_id'])?$data['currency_id']:2,
            'delegate_id'=>$data['delegate_id'],
            'description'=>$data['description'],
            'created_by'=>$data['created_by'],
            'created_on'=>currentDate(),
            'type'=>'project',
            'template_id'=>0,
            'parent_contract_id'=>0,
            'is_deleted'=>0,
            'project_status'=>isset($data['status']) && $data['status']==1?1:0
        );
        $insert_id = $this->User_model->insert_data('contract',$add);
        $customer_id=$data['customer_id'];
        $path=FILE_SYSTEM_PATH.'uploads/';
        $project_documents=array();
        // print_r($_FILES);exit;
        if(!is_dir($path.$customer_id)){ mkdir($path.$customer_id); }
        if(isset($_FILES) && $totalFilesCount>0)
        {
            $i_attachment=0;
            for($i_attachment=0; $i_attachment<$totalFilesCount; $i_attachment++) {
                $imageName = doUpload(array(
                    'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                    'image' => $_FILES['file']['name'][$i_attachment],
                    'upload_path' => $path,
                    'folder' => $customer_id));
                $project_documents[$i_attachment]['module_id']=$insert_id;
                $project_documents[$i_attachment]['module_type']='project';
                $project_documents[$i_attachment]['reference_id']=$insert_id;
                $project_documents[$i_attachment]['reference_type']='project';
                $project_documents[$i_attachment]['document_name']=$_FILES['file']['name'][$i_attachment];
                $project_documents[$i_attachment]['document_type'] = 0;
                $project_documents[$i_attachment]['document_source']=$imageName;
                $project_documents[$i_attachment]['document_mime_type']=$_FILES['file']['type'][$i_attachment];
                $project_documents[$i_attachment]['document_status']=1;
                $project_documents[$i_attachment]['uploaded_by']=$this->session_user_id;
                $project_documents[$i_attachment]['uploaded_on']=currentDate();
            }
        }
        if(count($project_documents)>0){
            $this->Document_model->addBulkDocuments($project_documents);
        }
        $provider_links = array();
        if(isset($data['links']))
            foreach($data['links'] as $k => $v){
                $project_links[$k]['module_id'] = $insert_id;
                $project_links[$k]['module_type'] = 'project';
                $project_links[$k]['reference_id'] = $insert_id;
                $project_links[$k]['reference_type'] = 'project';
                $project_links[$k]['document_name'] = $v['title'];
                $project_links[$k]['document_type'] = 1;
                $project_links[$k]['document_source'] = $v['url'];
                $project_links[$k]['document_mime_type'] = 'URL';
                $project_links[$k]['uploaded_by'] = $this->session_user_id;
                $project_links[$k]['uploaded_on'] = currentDate();
                $project_links[$k]['updated_on'] = currentDate();
            }
        if(count($project_links)>0){
            $this->Document_model->addBulkDocuments($project_links);
        }
        $result = $this->User_model->getUserInfo(array('user_id' => $data['created_by']));
        $user_info = $this->User_model->getUserInfo(array('user_id' => $data['contract_owner_id']));
        $contract_assigned_to_user_names=$user_info->first_name.' '.$user_info->last_name.' ('.$user_info->user_role_name.')';
        $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $result->customer_id));

        if($customer_details[0]['company_logo']=='') {
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
        }
        else{
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);

        }

        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }

        $customer_admin_list=$this->Customer_model->getCustomerAdminList(array('customer_id'=>$user_info->customer_id,'user_status'=>1));

        if(isset($data['delegate_id']) && $data['delegate_id']!=NULL && $data['delegate_id']>0) {
            $assigned = $this->User_model->getUserInfo(array('user_id' => $add['delegate_id']));
            $contract_assigned_to_user_names.=', '.$assigned->first_name.' '.$assigned->last_name.' ('.$assigned->user_role_name.')';
        }
        $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $user_info->customer_id,'module_key'=>'PROJECT_CREATION'));
      
        $template_configurations_parent=$template_configurations;
        if($template_configurations_parent['total_records']>0){
            foreach($customer_admin_list['data'] as $kd=>$vd){ 
                $mailer_data=array();
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$vd['first_name'];
                $wildcards_replaces['last_name']=$vd['last_name'];
                $wildcards_replaces['project_name']=$data['contract_name'];
                $wildcards_replaces['project_owner_name']=$result->first_name.' '.$result->last_name;
                $wildcards_replaces['project_created_date']=dateFormat($add['created_on']);
                $wildcards_replaces['project_assigned_to_user_names']=$contract_assigned_to_user_names;
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$vd['email'];
                $to_name=$vd['first_name'].' '.$vd['last_name'];
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$vd['id_user'];
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
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }
            }
            $mailer_data=array();
            $template_configurations=$template_configurations_parent['data'][0];
            $wildcards=$template_configurations['wildcards'];
            $wildcards_replaces=array();
            $wildcards_replaces['first_name']=$user_info->first_name;
            $wildcards_replaces['last_name']=$user_info->last_name;
            $wildcards_replaces['project_name']=$data['contract_name'];
            $wildcards_replaces['project_owner_name']=$result->first_name.' '.$result->last_name;
            $wildcards_replaces['project_created_date']=dateFormat($add['created_on']);
            $wildcards_replaces['project_assigned_to_user_names']=$contract_assigned_to_user_names;
            $wildcards_replaces['logo']=$customer_logo;
            $wildcards_replaces['year'] = date("Y");
            $wildcards_replaces['url']=WEB_BASE_URL.'html';
            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
            $from_name=$template_configurations['email_from_name'];
            $from=$template_configurations['email_from'];
            $to=$user_info->email;
            $to_name=$user_info->first_name.' '.$user_info->last_name;
            $mailer_data['mail_from_name']=$from_name;
            $mailer_data['mail_to_name']=$to_name;
            $mailer_data['mail_to_user_id']=$user_info->id_user;
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
                if($mail_sent_status==1)
                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
            }

            $assigned = $this->User_model->getUserInfo(array('user_id' => $add['delegate_id']));
            if(isset($data['delegate_id']) && $data['delegate_id']!=NULL && $data['delegate_id']>0 && !empty($assigned)){
                $mailer_data=array();
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$assigned->first_name;
                $wildcards_replaces['last_name']=$assigned->last_name;
                $wildcards_replaces['project_name']=$data['contract_name'];
                $wildcards_replaces['project_owner_name']=$result->first_name.' '.$result->last_name;
                $wildcards_replaces['project_created_date']=dateFormat($add['created_on']);
                $wildcards_replaces['project_assigned_to_user_names']=$contract_assigned_to_user_names;
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to = $assigned->email;
                $to_name=$assigned->first_name.' '.$assigned->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$assigned->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                if($mailer_data['is_cron']==0){
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }

            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('project_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function projectList_get(){
        $data = $this->input->get();

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
        }
        if(isset($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all') {
            $data['id_business_unit'] = pk_decrypt($data['business_unit_id']);
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if($this->session_user_info->user_role_id != 7)
            if(!in_array($data['id_business_unit'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            if(!in_array($data['delegate_id'],$this->session_user_delegates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['relationship_category_id'])) {
            $data['relationship_category_id'] = pk_decrypt($data['relationship_category_id']);
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if(!in_array($data['contract_owner_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(in_array($this->session_user_info->user_role_id,array(3,4,8))){
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
            $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            $data['session_user_role']=$this->session_user_info->user_role_id;
            $data['session_user_id']=$this->session_user_id;
        }
        if($this->session_user_info->user_role_id==6){
            $data['business_unit_id'] = $this->session_user_business_units;
            if(count($data['business_unit_id'])==0 && $this->session_user_info->is_allow_all_bu==0)
            {
                $data['business_unit_id']=array(0);
            }
        }
        if($this->session_user_info->user_role_id == 7){
            $data['provider_id'] = $this->session_user_info->provider;
        }
        
        if(isset($data['parent_contract_id'])) {
            $data['parent_contract_id'] = pk_decrypt($data['parent_contract_id']);
            if($this->session_user_info->user_role_id == 4){
                $data['delegate_id'] = $this->session_user_id;
            }
        }
        $data = tableOptions($data);
        if(strlen($data['advancedsearch_get'])>2) 
            $data['advancedsearch_get']=json_decode($data['advancedsearch_get']);
        else
            $data['advancedsearch_get']=false;
        $data['type']='project';
        if(count($data['business_unit_id'])==0)
        unset($data['business_unit_id']);
        // if(isset($data['can_access'])  && $data['can_access']==1){
        //     $data['project_status']=1;
        // }
        // else{
        //     $data['project_status']=0;
        // }
        // $data['deleted']=1;
        if(isset($data['is_advance_filter']) && $data['is_advance_filter'] == 1)
        {
            $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_projects_list','is_union_table'=>0));
            $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_projects_list','is_union_table'=>1));
            $data['adv_union_filters']=$get_union_filters;
            $data['adv_filters']=$get_filters;
        }
        $result = $this->Contract_model->getAllContractList($data);//echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            if(!empty($v['project_status']) && $v['project_status']==1){
                $result['data'][$k]['status']='1';
            }
            else{
                $result['data'][$k]['status']='0';
            }
            $action_data = array('id_contract' => $v['id_contract']);
            if (isset($data['id_user']))
                $action_data['id_user'] = $data['id_user'];
            if (isset($data['user_role_id']))
                $action_data['user_role_id'] = $data['user_role_id'];
            $action_data['item_status'] = 1;
            //$action_data['id_contract_review'] = $review[0]['id_contract_review'];
            $action_data['responsible_user_id'] = $data['id_user'];
            $action_data['status'] = 'open';

            // $project_task_data = $this->User_model->check_record('contract_workflow',array('contract_id' => $result[0]['id_contract']));
            // $project_task['acitvity_name']=$project_task_data[0]['workflow_name'];
            // $project_task['recurrence_till']=$project_task_data[0]['Execute_by'];
            // $project_task['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
            // $project_task['id_contract_workflow'] = pk_encrypt($project_task_data[0]['id_contract_workflow']);
            // $get_data_project_data = $this->Contract_model->getcontractworkflow(array('contract_id'=>$v['id_contract']));//echo $this->db->last_query();exit;
            // $get_data_project_data = $this->Project_model->getProjectWorkflow(array('contract_id' => $v['id_contract']));
            // foreach($get_data_project_data as $l=>$m){
            //     // print_r($m);
            //     // print_r($m);exit;
            //     $project_task[$l]['activity_name']=$m['review_name'];
            //     $project_task[$l]['id_contract_workflow']=pk_encrypt($get_data_project_data[$l]['id_contract_workflow']);
            //     $project_task[$l]['is_workflow']=1;
            //     $project_task[$l]['id_contract']=pk_encrypt($v['id_contract']);
            //     $project_task[$l]['calender_id']=pk_encrypt($m['calender_id']);
            //     $project_task[$l]['id_contract_review']=pk_encrypt($m['id_contract_review']);
            //     $project_task[$l]['validation_status']=isset($m['validation_status'])?$m['validation_status']:0;//for disable the access of workflow which is in validation on going
            //     if(isset($m['workflow_status']) && $m['workflow_status']=='workflow in progress'){
            //         $project_task[$l]['initiated']=true;
            //     }
            //     else{
            //         $project_task[$l]['initiated']=false;
            //     }
        
            // }
            // // print_r($this->session_user_info->user_role_id);exit;
            // $new_project_task_data = array();
            // foreach($project_task as $v){
            //     if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6)
            //         $new_project_task_data[]=$v;
            //     else if($this->session_user_info->user_role_id == 3){
            //         if(count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'contract_owner_id'=>$this->session_user_id)))>0 || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
            //         // echo $this->db->last_query();exit;
            //             $new_project_task_data[]=$v;
            //     }
            //     else if($this->session_user_info->user_role_id == 4){
            //         if(count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'delegate_id'=>$this->session_user_id)))>0 || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
            //             $new_project_task_data[]=$v;
            //     }
            //     else if($this->session_user_info->user_role_id == 7 && count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0)
            //             $new_project_task_data[]=$v;
                
            // }

            ///////////////////////// commented for performance  optimization   start ///////

            //     $project_task=array();
            //     $new_project_task_data = array();

            //     $project_task_data = $this->Project_model->getProjectWorkflow(array('contract_id' => $v['id_contract'],'parent_id'=>0));
            //     if(!empty($project_task_data)){
            //         $validation_info = '';
            //         if(!empty($project_task_data[0]['id_contract_review']))
            //         { 
            //             // print_r($project_task_data);exit;
            //             $validatorsmodules =array();
            //             $validatorsmodules = $this->Contract_model->getValidatormodules(array('contract_review_id'=>$project_task_data[0]['id_contract_review'],'contribution_type'=>1)); //getting validator modules 
            //             //  echo $this->db->last_query();exit;
            //             $validator_exists=count($validatorsmodules)>0?true:false;
            //             // if($validator_exists)
            //             // {
            //             //     $validation_info = 0;
            //             //     if((int)$project_task_data[0]['validation_status']>=2)
            //             //     {
            //             //         if($validatorsmodules[0]['module_status'] ==2)
            //             //         {
            //             //             $validation_info = 2;
            //             //         }
            //             //         elseif ($validatorsmodules[0]['module_status'] == 3) 
            //             //         {
            //             //             $validation_info = (int)$project_task_data[0]['validation_status'] == 3 ? 3:2;
            //             //         }
            //             //     }
            //             // }
            //             if($validator_exists)
            //             {
            //                 $progress_task_reviews=$this->calculateScoreAndProgress(array('id_contract_review'=>$project_task_data[0]['id_contract_review'],'user_id'=>0,'owner_id'=>$v['contract_owner_id'],'delegate_id'=>$v['delegate_id']));
            //                 $validation_info = 1;
            //                 if(str_replace('%','',$progress_task_reviews['contract_progress'])=='100'){
            //                     $validation_info = 4;
            //                 }
            //                 if((int)$project_task_data[0]['validation_status'] == 2)
            //                 {
            //                     $validation_info = 2;
            //                 }
            //                 elseif((int)$project_task_data[0]['validation_status'] == 3)
            //                 {
            //                     $validation_info = 3; 
            //                 }
            //             }
            //         }
            //         $project_task_score=$this->calculateScoreAndProgress(array('id_contract_review'=>$project_task_data[0]['id_contract_review'],'user_id'=>0,'owner_id'=>$v['contract_owner_id'],'delegate_id'=>$v['delegate_id']));
            //         $project_task[0]['score']=$project_task_score['score'];
            //         $project_task[0]['contract_progress']=$project_task_score['contract_progress'];
            //         $project_task[0]['validation_info'] = $validation_info;
            //         $project_task[0]['activity_name']=$project_task_data[0]['workflow_name'];
            //         $project_task[0]['recurrence_till']=$project_task_data[0]['Execute_by'];
            //         $project_task[0]['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
            //         $project_task[0]['id_contract_workflow'] = pk_encrypt($project_task_data[0]['id_contract_workflow']);
            //         $project_task[0]['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
            //         $project_task[0]['id_contract_review'] = pk_encrypt($project_task_data[0]['id_contract_review']);
            //         $project_task[0]['is_workflow'] = 1;
            //         $project_task[0]['id_contract'] = pk_encrypt($project_task_data[0]['contract_id']); 
            //         $project_task[0]['is_subtask'] = 0; 
            //         $project_task[0]['validation_status']=isset($project_task_data[0]['validation_status'])?$project_task_data[0]['validation_status']:0;
            //         // $result[0]['project_task_count']=count($project_task_data);          
            //         if(isset($project_task_data[0]['workflow_status']) && $project_task_data[0]['workflow_status']=='workflow in progress'){
            //             // $result[0]['initiated']=true;
            //             $project_task[0]['initiated']=true;
            //         }
            //         else{
            //             // $result[0]['initiated']=false;
            //             $project_task[0]['initiated']=false;
            //         }
            //         $get_data_project_data = $this->Project_model->getProjectWorkflow(array('contract_id'=>$v['id_contract'],'not_contract_workflow_id'=>$project_task_data[0]['id_contract_workflow'],'parent_id'=>0));
            //         foreach($get_data_project_data as $l=>$m){
            //             $validation_info = '';
            //             if(!empty($get_data_project_data[$l]['id_contract_review']))
            //             { 
            //                 $validatorsmodules =array();
            //                 $validatorsmodules = $this->Contract_model->getValidatormodules(array('contract_review_id'=>$get_data_project_data[$l]['id_contract_review'],'contribution_type'=>1)); //getting validator modules 
            //                 $validator_exists=count($validatorsmodules)>0?true:false;
            //                 // if($validator_exists)
            //                 // {
            //                 //     $validation_info = 0;
            //                 //     if((int)$get_data_project_data[$l]['validation_status']>=2)
            //                 //     {
            //                 //         if($validatorsmodules[0]['module_status'] ==2)
            //                 //         {
            //                 //             $validation_info = 2;
            //                 //         }
            //                 //         elseif ($validatorsmodules[0]['module_status'] == 3) 
            //                 //         {
            //                 //             $validation_info = (int)$get_data_project_data[$l]['validation_status'] == 3 ? 3:2;
            //                 //         }
            //                 //     }
            //                 // }
            //                 if($validator_exists)
            //                 {
            //                     $validation_info = 1;
            //                     $progress_task_reviews=$this->calculateScoreAndProgress(array('id_contract_review'=>$get_data_project_data[$l]['id_contract_review'],'user_id'=>0,'owner_id'=>$v['contract_owner_id'],'delegate_id'=>$v['delegate_id']));
            //                     if(str_replace('%','',$progress_task_reviews['contract_progress'])=='100'){
            //                         $validation_info = 4;
            //                     }
            //                     if((int)$get_data_project_data[$l]['validation_status'] == 2)
            //                     {
            //                         $validation_info = 2;
            //                     }
            //                     elseif((int)$get_data_project_data[$l]['validation_status'] == 3)
            //                     {
            //                         $validation_info = 3; 
            //                     }
            //                 }
            //             }
            //             $project_task_score=$this->calculateScoreAndProgress(array('id_contract_review'=>$get_data_project_data[$l]['id_contract_review'],'user_id'=>0,'owner_id'=>$v['contract_owner_id'],'delegate_id'=>$v['delegate_id']));
            //             $project_task[$l+1]['score']=$project_task_score['score'];
            //             $project_task[$l+1]['contract_progress']=$project_task_score['contract_progress'];
                    
            //             $project_task[$l+1]['validation_info'] = $validation_info;
            //             $project_task[$l+1]['activity_name']=$m['workflow_name'];
            //             $project_task[$l+1]['calender_id']=pk_encrypt($get_data_project_data[$l]['calender_id']);
            //             $project_task[$l+1]['id_contract_workflow']=pk_encrypt($get_data_project_data[$l]['id_contract_workflow']);
            //             $project_task[$l+1]['is_workflow']=1;
            //             $project_task[$l+1]['is_subtask']=0;
            //             $project_task[$l+1]['id_contract_review']=pk_encrypt($get_data_project_data[$l]['id_contract_review']);
            //             $project_task[$l+1]['id_contract']=pk_encrypt($result[0]['id_contract']);
            //             $project_task[$l+1]['validation_status']=isset($get_data_project_data[$l]['validation_status'])?$get_data_project_data[$l]['validation_status']:0;//for disable the access of workflow which is in validation on going
            //             if(isset($get_data_project_data[$l]['workflow_status']) && $get_data_project_data[$l]['workflow_status']=='workflow in progress'){
            //                 $project_task[$l+1]['initiated']=true;
            //             }
            //             else{
            //                 $project_task[$l+1]['initiated']=false;
            //             }
                
            //         }
            //         // print_r($this->session_user_info->contribution_type);exit;
            //     foreach($project_task as $v){
            //         if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6)
            //             $new_project_task_data[]=$v;
            //         else if($this->session_user_info->user_role_id == 3){
            //             // if($this->session_user_info->contribution_type==1){
            //             //     if(count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'contract_owner_id'=>$this->session_user_id)))>0 || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
            //             //     $new_project_task_data[]=$v;
            //             // }else{
            //             //     $new_project_task_data[]=$v;
            //             // }
            //             $new_project_task_data[]=$v;
            //         }
            //         else if($this->session_user_info->user_role_id == 4){
            //             // if($this->session_user_info->contribution_type==1){
            //             //     if(count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'delegate_id'=>$this->session_user_id)))>0 || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
            //             //         $new_project_task_data[]=$v;
            //             // }
            //             // else{
            //             //     $new_project_task_data[]=$v;
            //             // }
            //             $new_project_task_data[]=$v;
            //         }
            //         else if($this->session_user_info->user_role_id == 7 && count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0)
            //                 $new_project_task_data[]=$v;
                    
            //     }

            // }

            // $subtask_data=array();
            // foreach($new_project_task_data as $kn=>$vn){
            //     // print_r($vn);exit;
            //     $subtask_data[]=$vn;
            //     $get_subtasks_data = $this->Project_model->getProjectWorkflow(array('contract_id'=>pk_decrypt($vn['id_contract']),'parent_id'=>pk_decrypt($vn['id_contract_workflow'])));
            //     // echo $this->db->last_query();exit;
            //     foreach($get_subtasks_data as $ks=>$vs){
            //         $project_sub_task['activity_name']=$get_subtasks_data[$ks]['workflow_name'];
            //         $project_sub_task['recurrence_till']=$get_subtasks_data[$ks]['Execute_by'];
            //         $project_sub_task['calender_id'] = pk_encrypt($get_subtasks_data[$ks]['calender_id']);
            //         $project_sub_task['id_contract_workflow'] = pk_encrypt($get_subtasks_data[$ks]['id_contract_workflow']);
            //         $project_sub_task['calender_id'] = pk_encrypt($get_subtasks_data[$ks]['calender_id']);
            //         $project_sub_task_score=$this->calculateScoreAndProgress(array('id_contract_review'=>$get_subtasks_data[$ks]['id_contract_review'],'user_id'=>0,'is_subtask'=>1));
            //         $project_sub_task['score']=$project_sub_task_score['score'];
            //         $project_sub_task['contract_progress']=$project_sub_task_score['contract_progress'];
            //         $project_sub_task['id_contract_review'] = pk_encrypt($get_subtasks_data[$ks]['id_contract_review']);
            //         $project_sub_task['is_workflow'] = 1;
            //         $project_sub_task['is_subtask'] = 1;
            //         $project_sub_task['id_contract'] = pk_encrypt($get_subtasks_data[$ks]['contract_id']); 
            //         // $result[0]['project_task_count']=count($project_task_data);          
            //         if(isset($get_subtasks_data[$ks]['workflow_status']) && $get_subtasks_data[$ks]['workflow_status']=='workflow in progress'){
            //             // $result[0]['initiated']=true;
            //             $project_sub_task['initiated']=true;
            //         }
            //         else{
            //             // $result[0]['initiated']=false;
            //             $project_sub_task['initiated']=false;
            //         }
            //         $subtask_data[]=$project_sub_task;
            //     }
            //     // print_r($subtask_data);
            //     // print_r($project_sub_task[0]);exit;
            // }


            // $result['data'][$k]['project_task']=$subtask_data;
            // unset($project_task);
            ///////////////////////// commented for performance  optimization   end ///////
            $result['data'][$k]['action_item_count'] = count($this->Contract_model->getContractReviewActionItemsList($action_data));
            // echo $this->db->last_query();exit;
            $result['data'][$k]['id_contract']=pk_encrypt($result['data'][$k]['id_contract']);
            $result['data'][$k]['business_unit_id']=pk_encrypt($result['data'][$k]['business_unit_id']);
            $result['data'][$k]['contract_owner_id']=pk_encrypt($v['contract_owner_id']);
            $result['data'][$k]['currency_id']=pk_encrypt($v['currency_id']);
            $result['data'][$k]['delegate_id']=pk_encrypt($v['delegate_id']);
            $result['data'][$k]['template_id']=pk_encrypt($v['template_id']);
            $result['data'][$k]['contract_id']=pk_encrypt($v['contract_id']);
            $result['data'][$k]['project_id']=pk_encrypt($v['contract_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function projectInfo_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('project_id', array('required'=>$this->lang->line('project_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['project_id'])){
            $data['project_id']=pk_decrypt($data['project_id']);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        if(isset($data['id_contract_workflow'])){
            $contract_workflow_Id = $data['id_contract_workflow'];
            $data['id_contract_workflow']=pk_decrypt($data['id_contract_workflow']);
        }

        /* checking the external user access  start*/
        if($this->session_user_info->user_role_id==7){
            $workflow_id=$data['id_contract_workflow'];
            $project_id=$data['project_id'];
            $check_access=$this->User_model->custom_query('SELECT cu.* FROM contract_user cu  LEFT JOIN contract_review cr  on cu.contract_review_id=cr.id_contract_review WHERE cr.contract_workflow_id='.$workflow_id.' AND cu.contract_id='.$project_id.' AND cu.user_id='.$this->session_user_info->id_user);
            if(empty($check_access)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /* checking the external user access  end*/
        
        $taskDetails = $this->User_model->check_record("contract_workflow",array("id_contract_workflow"=>$data['id_contract_workflow']));
        if(!empty($taskDetails)){
            if($taskDetails[0]['parent_id']>0)
            {
                $subTaskVal = 1;//subtask
            }
            else
            {
                $subTaskVal = 0;//not a subtask
            }
        }

        $result=$this->Project_model->getProjectinfo($data);
        // print_r($result[0]['contract_end_date']);exit;
        // 0000-00-00 00:00:00
        // if($result[0]['contract_end_date']=='0000-00-00 00:00:00'){
        //     // unset($result[0]['contract_end_date']);
        //     $result[0]['contract_end_date']='';
        // }
        // print_r($result);exit;
        $inner_data=array();
        $inner_data['reference_id']=$data['project_id'];
        $inner_data['reference_type']='project';
        $inner_data['module_type']='project';
        $inner_data['document_status']=1;
        $inner_data['document_type'] = 0;
        $result[0]['attachment']['documents'] = $result[0]['unique_attachment']['documents'] = $this->Document_model->getDocumentsList($inner_data);
        $inner_data['document_type'] = array(0,1);
        $result[0]['unique_attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
        $result[0]['attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
        $inner_data['document_type'] = 1;
        $result[0]['attachment']['links'] = $result[0]['unique_attachment']['links'] = $this->Document_model->getDocumentsList($inner_data);
        foreach($result[0]['attachment']['documents'] as $ka=>$va){
            $result[0]['attachment']['documents'][$ka]['updated_by']=0;
        }
        foreach($result[0]['attachment']['links'] as $ka=>$va){
            $result[0]['attachment']['links'][$ka]['updated_by']=0;
        }
        // print_r($result);exit;
        if(isset($result[0]['project_status']) && $result[0]['project_status']==1){
            $result[0]['status']='1';
        }
        else{
            $result[0]['status']='0';
        }
        $result[0]['is_subtask']=$subTaskVal; 
        // $inner_data['updated_by']=isset($data['updated_by'])?$data['updated_by']:1;
        // $result[0]['attachment']['links'] = array_merge($this->Document_model->getDocumentsList($inner_data),$result[0]['attachment']['links']);
        // // echo $this->db->last_query();exit;
        // unset($inner_data['document_type']);
        // $result[0]['attachment']['documents'] = array_merge($this->Document_model->getDocumentsList($inner_data),$result[0]['attachment']['documents']);
        foreach($result[0]['attachment']['documents'] as $ka=>$va){
            $result[0]['attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
            $result[0]['attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
            $result[0]['attachment']['documents'][$ka]['id_document']=pk_encrypt($result[0]['attachment']['documents'][$ka]['id_document']);
            $result[0]['attachment']['documents'][$ka]['module_id']=pk_encrypt($result[0]['attachment']['documents'][$ka]['module_id']);
            $result[0]['attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[0]['attachment']['documents'][$ka]['reference_id']);
            $result[0]['attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[0]['attachment']['documents'][$ka]['uploaded_by']);
            $result[0]['attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[0]['attachment']['documents'][$ka]['user_role_id']);
            $result[0]['attachment']['documents'][$ka]['action']=0;
            if(((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
            {
                $result[0]['attachment']['documents'][$ka]['action']=1;
            }
            if(($result[0]['attachment']['documents'][$ka]['is_lock']==1))
            {
                if(
                    !((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||
                    (in_array($this->session_user_info->user_role_id,array(2))))
                    )
                {
                    unset($result[0]['attachment']['documents'][$ka]);
                }
            }
        }
        $result[0]['attachment']['documents']= array_values($result[0]['attachment']['documents']);
        foreach($result[0]['attachment']['links'] as $ka=>$va){
            $result[0]['attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
            $result[0]['attachment']['links'][$ka]['id_document']=pk_encrypt($result[0]['attachment']['links'][$ka]['id_document']);
            $result[0]['attachment']['links'][$ka]['module_id']=pk_encrypt($result[0]['attachment']['links'][$ka]['module_id']);
            $result[0]['attachment']['links'][$ka]['reference_id']=pk_encrypt($result[0]['attachment']['links'][$ka]['reference_id']);
            $result[0]['attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[0]['attachment']['links'][$ka]['uploaded_by']);
            $result[0]['attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[0]['attachment']['links'][$ka]['user_role_id']);
            $result[0]['attachment']['links'][$ka]['action']=0;
            if(((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
            {
                $result[0]['attachment']['links'][$ka]['action']=1;
            }
            if(($result[0]['attachment']['links'][$ka]['is_lock']==1))
            {
                if(
                    !((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||
                    (in_array($this->session_user_info->user_role_id,array(2))))
                    )
                {
                    unset($result[0]['attachment']['links'][$ka]);
                }
            }
        }
        $result[0]['attachment']['links']= array_values($result[0]['attachment']['links']);
        foreach($result[0]['attachment']['all_records'] as $ka=>$va){
            $result[0]['attachment']['all_records'][$ka]['document_source_exactpath']=($va['document_source']);
            $result[0]['attachment']['all_records'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
            $result[0]['attachment']['all_records'][$ka]['id_document']=pk_encrypt($result[0]['attachment']['all_records'][$ka]['id_document']);
            $result[0]['attachment']['all_records'][$ka]['module_id']=pk_encrypt($result[0]['attachment']['all_records'][$ka]['module_id']);
            $result[0]['attachment']['all_records'][$ka]['reference_id']=pk_encrypt($result[0]['attachment']['all_records'][$ka]['reference_id']);
            $result[0]['attachment']['all_records'][$ka]['uploaded_by']=pk_encrypt($result[0]['attachment']['all_records'][$ka]['uploaded_by']);
            $result[0]['attachment']['all_records'][$ka]['user_role_id']=pk_encrypt($result[0]['attachment']['all_records'][$ka]['user_role_id']);
            $result[0]['attachment']['all_records'][$ka]['action']=0;
            if(
                ((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||
                (in_array($this->session_user_info->user_role_id,array(2))))
                )
            {
                $result[0]['attachment']['all_records'][$ka]['action']=1;
            }
            if(($result[0]['attachment']['all_records'][$ka]['is_lock']==1))
            {
                if(
                    !((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||
                    (in_array($this->session_user_info->user_role_id,array(2))))
                    )
                {
                    unset($result[0]['attachment']['all_records'][$ka]);
                }
            }
        }
        $result[0]['attachment']['all_records']= array_values($result[0]['attachment']['all_records']);
        // print_r($result[0]['unique_attachment']);exit;
        foreach($result[0]['unique_attachment']['documents'] as $attch =>$uniqattachs){
            $result[0]['unique_attachment']['documents'][$attch]['id_document']=pk_encrypt($result[0]['unique_attachment']['documents'][$attch]['id_document']);
            $result[0]['unique_attachment']['documents'][$attch]['encryptedPath']=pk_encrypt($result[0]['unique_attachment']['documents'][$attch]['document_source']);
            if($result[0]['unique_attachment']['documents'][$attch]['is_lock']==1)
            {
                if(!((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
                {
                    unset($result[0]['unique_attachment']['documents'][$attch]);
                }
            }
        }
        $result[0]['unique_attachment']['documents']= array_values($result[0]['unique_attachment']['documents']);
        
        foreach($result[0]['unique_attachment']['links'] as $attch =>$uniqattachs){
            $result[0]['unique_attachment']['links'][$attch]['id_document']=pk_encrypt($result[0]['unique_attachment']['links'][$attch]['id_document']);
            if($result[0]['unique_attachment']['links'][$attch]['is_lock']==1)
            {
                if(!((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
                {
                    unset($result[0]['unique_attachment']['links'][$attch]);
                }
            }
        }
        $result[0]['unique_attachment']['all_records']= array_values($result[0]['unique_attachment']['all_records']);
        foreach($result[0]['unique_attachment']['all_records'] as $attch =>$uniqattachs){
            $result[0]['unique_attachment']['all_records'][$attch]['id_document']=pk_encrypt($result[0]['unique_attachment']['all_records'][$attch]['id_document']);
            $result[0]['unique_attachment']['all_records'][$attch]['encryptedPath']=pk_encrypt($result[0]['unique_attachment']['all_records'][$attch]['document_source']);
            if($result[0]['unique_attachment']['all_records'][$attch]['is_lock']==1)
            {
                if(!((in_array($this->session_user_info->id_user,array($result[0]['delegate_id'],$result[0]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
                {
                    unset($result[0]['unique_attachment']['all_records'][$attch]);
                }
            }
        }
        $result[0]['unique_attachment']['all_records']= array_values($result[0]['unique_attachment']['all_records']);
        $projectInfoarray =array('contract_unique_id','contract_name','contract_start_date','contract_end_date','contract_value','business_unit_id','currency_id','contract_owner_id','delegate_id','project_status','description');
        $projectinfoFilledFields =0;
        foreach ($projectInfoarray as $k => $v) {
                 if(!empty($result[0][$v])||($result[0][$v]=='project_status')){
                    $projectinfoFilledFields++;
                //array_push($projectinfoFilledFields,$v);
            }
        }
        $result[0]['project_information'] = $projectinfoFilledFields."/11";
        $connectedcontract = $this->Contract_model->getConnectedProjectsContracts(array('customer_id'=>$data['customer_id'],'project_id'=>$data['project_id']));
        // if(isset($data['id_contract_workflow'])){
        //     $data['id_contract_workflow']=pk_decrypt($data['id_contract_workflow']);
        // }
        $project_task=array();
        $project_first_task=array();
        // print_r($data);exit;
        if(isset($data['is_workflow']) && $data['is_workflow']==1 && isset($data['id_contract_workflow']) && $data['id_contract_workflow'] > 0){
            $project_task=array();
            $project_task_data = $this->Project_model->getProjectWorkflow(array('id_contract_workflow' => $data['id_contract_workflow'],'contract_review_status_not'=>'finished'));//echo $this->db->last_query();exit;
            if(!empty($project_task_data)){
                $project_task[0]['activity_name']=$project_task_data[0]['workflow_name'];
                $project_task[0]['recurrence_till']=$project_task_data[0]['Execute_by'];
                $project_task[0]['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
                $project_task[0]['id_contract_workflow'] = pk_encrypt($data['id_contract_workflow']);
                $project_task[0]['is_workflow'] = 1;
                $project_task[0]['id_contract_review'] = pk_encrypt($project_task_data[0]['id_contract_review']); 
                $project_task[0]['contract_review_id'] = pk_encrypt($project_task_data[0]['id_contract_review']); 
                $project_task[0]['id_contract'] = pk_encrypt($project_task_data[0]['contract_id']); 
                $project_task[0]['workflow_status'] = $project_task_data[0]['workflow_status']; 
                $project_task[0]['validation_status'] = isset($project_task_data[0]['validation_status'])?$project_task_data[0]['validation_status']:0; 
                // $result[0]['project_task_count']=count($project_task_data);          
                if(isset($project_task_data[0]['workflow_status']) && $project_task_data[0]['workflow_status']=='workflow in progress'){
                    // $result[0]['initiated']=true;
                    $project_task[0]['initiated']=true;
                }
                else{
                    $project_task[0]['initiated']=false;
                    // $result[0]['initiated']=false;getcontractworkflow
                }
                if(isset($project_task_data[0]['parent_id']) && $project_task_data[0]['parent_id']>0){
                    $project_task[0]['is_subtask'] = 1;
                }
                else{
                    $project_task[0]['is_subtask'] = 0;
                }
                $check_contribution=array();
                // print_r($this->session_user_info->user_role_id);exit;
                if($this->session_user_info->user_role_id==4 || $this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==8){
                    $check_contribution=$this->User_model->check_record('contract_user',array('contract_id'=>$data['project_id'],'user_id'=>$this->session_user_info->id_user,'status'=>1));
                }
                 //if($this->session_user_info->user_role_id!=7 && $this->session_user_info->contribution_type==0 && count($check_contribution)==0)
                if($this->session_user_info->user_role_id!=7 && ($this->session_user_info->contribution_type==0||$this->session_user_info->contribution_type==1))
                $get_data_project_data = $this->Project_model->getProjectWorkflow(array('contract_id'=>$data['project_id'],'not_contract_workflow_id'=>$data['id_contract_workflow'],'parent_id'=>0));//echo $this->db->last_query();exit;
                foreach($get_data_project_data as $l=>$m){
                    $project_task[$l+1]['activity_name']=$m['workflow_name'];
                    $project_task[$l+1]['id_contract_workflow']=pk_encrypt($get_data_project_data[$l]['id_contract_workflow']);
                    $project_task[$l+1]['calender_id']=pk_encrypt($get_data_project_data[$l]['calender_id']);
                    $project_task[$l+1]['id_contract_review']=pk_encrypt($get_data_project_data[$l]['id_contract_review']);
                    $project_task[$l+1]['contract_review_id']=pk_encrypt($get_data_project_data[$l]['id_contract_review']);
                    $project_task[$l+1]['is_workflow']=1;
                    $project_task[$l+1]['id_contract']=pk_encrypt($data['project_id']);
                    $project_task[0]['workflow_status'] = $project_task_data[0]['workflow_status']; 
                    $project_task[$l+1]['validation_status']=isset($get_data_project_data[$l]['validation_status'])?$get_data_project_data[$l]['validation_status']:0;//for disable the access of workflow which is in validation on going
                    if(isset($get_data_project_data[$l]['workflow_status']) && $get_data_project_data[$l]['workflow_status']=='workflow in progress'){
                        $project_task[$l+1]['initiated']=true;
                    }
                    else{
                        $project_task[$l+1]['initiated']=false;
                    }
                    if(isset($get_data_project_data[$l]['parent_id']) && $get_data_project_data[$l]['parent_id']>0){
                        $project_task[$l+1]['is_subtask']=1;
                    }
                    else{
                        $project_task[$l+1]['is_subtask']=0;
                    }
            
                }
            }

        }
        else{
            $project_task=array();
            $project_task_data = $this->Project_model->getProjectWorkflow(array('contract_id' => $data['project_id'],'parent_id'=>0));
            // echo $this->db->last_query();exit;
            if(!empty($project_task_data)){
                $project_task[0]['activity_name']=$project_task_data[0]['workflow_name'];
                $project_task[0]['recurrence_till']=$project_task_data[0]['Execute_by'];
                $project_task[0]['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
                $project_task[0]['id_contract_workflow'] = pk_encrypt($project_task_data[0]['id_contract_workflow']);
                $project_task[0]['calender_id'] = pk_encrypt($project_task_data[0]['calender_id']);
                $project_task[0]['id_contract_review'] = pk_encrypt($project_task_data[0]['id_contract_review']);
                $project_task[0]['contract_review_id'] = pk_encrypt($project_task_data[0]['id_contract_review']);
                $project_task[0]['is_workflow'] = 1;
                $project_task[0]['is_subtask'] = 0;
                $project_task[0]['id_contract'] = pk_encrypt($project_task_data[0]['contract_id']); 
                $project_task[0]['workflow_status'] = $project_task_data[0]['workflow_status']; 
                $project_task[0]['validation_status'] = isset($project_task_data[0]['validation_status'])?$project_task_data[0]['validation_status']:0; 
                $result[0]['project_task_count']=count($project_task_data);          
                if(isset($project_task_data[0]['workflow_status']) && $project_task_data[0]['workflow_status']=='workflow in progress'){
                    // $result[0]['initiated']=true;
                    $project_task[0]['initiated']=true;
                }
                else{
                    // $result[0]['initiated']=false;
                    $project_task[0]['initiated']=false;
                }
                $get_data_project_data = $this->Project_model->getProjectWorkflow(array('contract_id'=>$data['project_id'],'not_contract_workflow_id'=>$project_task_data[0]['id_contract_workflow'],'parent_id'=>0));
                foreach($get_data_project_data as $l=>$m){
                    $project_task[$l+1]['activity_name']=$m['workflow_name'];
                    $project_task[$l+1]['calender_id']=pk_encrypt($get_data_project_data[$l]['calender_id']);
                    $project_task[$l+1]['id_contract_workflow']=pk_encrypt($get_data_project_data[$l]['id_contract_workflow']);
                    $project_task[$l+1]['is_workflow']=1;
                    $project_task[$l+1]['is_subtask']=0;
                    $project_task[$l+1]['id_contract_review']=pk_encrypt($get_data_project_data[$l]['id_contract_review']);
                    $project_task[$l+1]['contract_review_id']=pk_encrypt($get_data_project_data[$l]['id_contract_review']);
                    $project_task[$l+1]['id_contract']=pk_encrypt($data['project_id']);
                    $project_task[$l+1]['validation_status']=isset($get_data_project_data[$l]['validation_status'])?$get_data_project_data[$l]['validation_status']:0;//for disable the access of workflow which is in validation on going
                    if(isset($get_data_project_data[$l]['workflow_status']) && $get_data_project_data[$l]['workflow_status']=='workflow in progress'){
                        $project_task[$l+1]['initiated']=true;
                    }
                    else{
                        $project_task[$l+1]['initiated']=false;
                    }
            
                }
            }
        }
        $contract_workflow_id=!empty($data['id_contract_workflow'])?$data['id_contract_workflow']:$project_task_data[0]['id_contract_workflow'];
        $new_project_task_data = array();
        foreach($project_task as $v){
            if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6)
                $new_project_task_data[]=$v;
            else if($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id==8){
                $isProjectManager = false;
                if($this->session_user_info->user_role_id == 8)
                {
                    //checking he/she is manager of this project 
                    $managerBu = $this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
                    $ProjectDetails = $this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract'])));
                    if(in_array($ProjectDetails[0]['business_unit_id'],$managerBu))
                    {
                        $isProjectManager = true;
                    }
                }
                $added_in_new_project_task_data = 0; //( new c)
                if((count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'contract_owner_id'=>$this->session_user_id)))>0||  $isProjectManager) || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
                {
                    $new_project_task_data[]=$v;
                    //can remove condition is written because if he/she is owner/manager of that project should not removed from list 
                    if( count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'contract_owner_id'=>$this->session_user_id)))==1 )
                    {
                        $canRemove = false;
                    }
                    else
                    {
                        $canRemove = ($isProjectManager == true)?false:true;
                    }
                    $added_in_new_project_task_data = 1;
                    //removing task from list if that task validation status is not 'validation ongoing'(2) 
                    if((($v['is_subtask'] != 1) && ($canRemove) &&(count($this->User_model->check_record('contract_review',array('id_contract_review'=>pk_decrypt($v['id_contract_review']),'validation_status'=>array(0,1,3))))>0 ) &&(!(count ($this->Project_model->getProjectTaskUsers(array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'contribution_type'=> 'expert','user_id'=>$this->session_user_id)))>0)))
                    )
                    {
                        array_pop($new_project_task_data);
                        $added_in_new_project_task_data = 0;
                    }
                }
                if(($v['is_subtask'] == 1)&&($added_in_new_project_task_data == 0)) //( new )
                {
                    $TaskData = $this->User_model->check_record("contract_workflow",array("id_contract_workflow"=>pk_decrypt($v['id_contract_workflow'])));
                    if(!empty($TaskData[0]))
                    {
                        $parenttaskDetails = $this->User_model->check_record("contract_workflow",array("id_contract_workflow"=>$TaskData[0]['parent_id']));
                         $parentWorkflowId = $parenttaskDetails[0]['id_contract_workflow'];
                         $parentContractReviewId = $this->User_model->check_record("contract_review",array("contract_workflow_id"=>$parentWorkflowId));
                        if(
                            count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'status'=>1,'user_id'=>$this->session_user_id,'contract_review_id'=>$parentContractReviewId[0]['id_contract_review'])))>0
                        )
                        {
                            $new_project_task_data[]=$v;
                        } 
                    }
                }     
            }
            else if($this->session_user_info->user_role_id == 4){
                $added_in_new_project_task_data = 0; //( new )
                if(count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'delegate_id'=>$this->session_user_id)))>0 || (count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0 && (count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']),'contract_workflow_id'=>pk_decrypt($v['id_contract_workflow']))))>0 || count($this->User_model->check_record('contract_review',array('contract_id'=>pk_decrypt($v['id_contract']),'id_contract_review'=>pk_decrypt($v['id_contract_review']))))>0)))
                {
                    $new_project_task_data[]=$v;
                    $added_in_new_project_task_data = 1;
                    //removing task from list if that task validation status is not 'validation ongoing'(2) 
                    if((($v['is_subtask'] != 1) && (count($this->User_model->check_record('contract',array('id_contract'=>pk_decrypt($v['id_contract']),'delegate_id'=>$this->session_user_id)))==0) &&(count($this->User_model->check_record('contract_review',array('id_contract_review'=>pk_decrypt($v['id_contract_review']),'validation_status'=>array(0,1,3))))>0 ) &&(!(count ($this->Project_model->getProjectTaskUsers(array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'contribution_type'=> 'expert','user_id'=>$this->session_user_id)))>0)))
                    )
                    {
                        array_pop($new_project_task_data);
                        $added_in_new_project_task_data = 0;
                    }
                }
                if(($v['is_subtask'] == 1)&&($added_in_new_project_task_data == 0)) //( new )
                {
                    $TaskData = $this->User_model->check_record("contract_workflow",array("id_contract_workflow"=>pk_decrypt($v['id_contract_workflow'])));
                    if(!empty($TaskData[0]))
                    {
                        $parenttaskDetails = $this->User_model->check_record("contract_workflow",array("id_contract_workflow"=>$TaskData[0]['parent_id']));
                         $parentWorkflowId = $parenttaskDetails[0]['id_contract_workflow'];
                         $parentContractReviewId = $this->User_model->check_record("contract_review",array("contract_workflow_id"=>$parentWorkflowId));
                        if(
                            count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'status'=>1,'user_id'=>$this->session_user_id,'contract_review_id'=>$parentContractReviewId[0]['id_contract_review'])))>0
                        )
                        {
                            $new_project_task_data[]=$v;
                        } 
                    }
                }      
            }
            else if($this->session_user_info->user_role_id == 7 && count($this->User_model->check_record('contract_user',array('contract_id'=>pk_decrypt($v['id_contract']),'contract_review_id'=>pk_decrypt($v['id_contract_review']),'status'=>1,'user_id'=>$this->session_user_id)))>0)
            //print_r($project_task);
            //echo $this->db->last_query();
                    $new_project_task_data[]=$v;
            
        }
        //print_r($new_project_task_data);
        //exit;
        //print_r($taskDetails);exit;
        if(empty($taskDetails))
        {
            if(!empty($new_project_task_data[0]['workflow_status']) && $new_project_task_data[0]['workflow_status']=='workflow in progress'){
            $result[0]['initiated']=true;
            }
            else{
                $result[0]['initiated']=false;
            }
        }
        else{
            if(!empty($taskDetails[0]['workflow_status']) && $taskDetails[0]['workflow_status']=='workflow in progress'){
                $result[0]['initiated']=true;
            }
            else{
                $result[0]['initiated']=false;
            }

        }

       

        // if(!empty($new_project_task_data[0]['workflow_status']) && $new_project_task_data[0]['workflow_status']=='workflow in progress'){
        //     $result[0]['initiated']=true;
        // }
        // else{
        //     $result[0]['initiated']=false;
        // }
        $result[0]['project_task_count']=count($new_project_task_data);          
        $subtask_data=array();
        // if($this->session_user_info->user_role_id==3 ||$this->session_user_info->user_role_id==4){

        //     // print_r($data);exit;
        //     $check_is_owner=$this->User_model->check_record('contract',array('id_contract'=>$data['project_id']));
        //     if(!empty($check_is_owner[0]['contract_owner_id']) && $this->session_user_info->id_user==$check_is_owner[0]['contract_owner_id']){
        //         $is_owner=1;
        //     }
        //     else{
        //         $is_owner=0;
        //     }
        //     if(!empty($check_is_owner[0]['delegate_id']) && $this->session_user_info->id_user==$check_is_owner[0]['delegate_id']){
        //         $is_delegate=1;
        //     }
        //     else{
        //         $is_delegate=0;
        //     }          
        // }
        // print_r($new_project_task_data);exit;
        foreach($new_project_task_data as $kn=>$vn){
            $validation_info = '';
            $calculatedProgress=$this->calculateScoreAndProgress(array('id_contract_review'=>pk_decrypt($vn['id_contract_review']),'user_id'=>!empty($this->session_user_id)?$this->session_user_id:0,'is_subtask'=>$vn['is_subtask'],'owner_id'=>$result[0]['contract_owner_id'],'delegate_id'=>$result[0]['delegate_id']));
            $vn['score']=$calculatedProgress['score'];
            $vn['contract_progress']=$calculatedProgress['contract_progress'];
            if(!empty($vn['id_contract_review']))
            { 
                $validatorsmodules =array();
                $validatorsmodules = $this->Contract_model->getValidatormodules(array('contract_review_id'=>pk_decrypt($vn['id_contract_review']),'contribution_type'=>1)); //getting validator modules 
                $validator_exists=count($validatorsmodules)>0?true:false;
                if($validator_exists)
                {
                    $validation_info = 1;
                    if(str_replace('%','',$vn['contract_progress'])=='100'){
                        $validation_info = 4;
                    }
                    if((int)$vn['validation_status'] == 2)
                    {
                        $validation_info = 2;
                    }
                    elseif((int)$vn['validation_status'] == 3)
                    {
                        $validation_info = 3; 
                    }
                }
            }
            $vn['validation_info'] = $validation_info;
            $subtask_data[]=$vn;
            // $is_subtask_access=0;
            // if(!empty($vn['contract_review_id'])){
            //     $check_exp_or_val=$this->User_model->check_record('contract_user',array('contract_review_id'=>pk_decrypt($vn['contract_review_id']),'contract_id'=>$data['project_id'],'status'=>1,'user_id'=>$this->session_user_info->id_user));
            //     if(empty($check_exp_or_val) && $is_delegate>0 || $is_owner>0){
            //         $is_subtask_access=1;
            //     }
            // }
            // if($this->session_user_info->user_role_id==2){
            //     $is_subtask_access=1;
            // }
            //if($this->session_user_info->user_role_id!=7 && $this->session_user_info->contribution_type==0 && $is_subtask_access==1)
            if($this->session_user_info->user_role_id!=7 && $this->session_user_info->contribution_type!=3)
            // if(empty($check_user_as_val_exp))
            $get_subtasks_data =array();
            $get_subtasks_data = $this->Project_model->getProjectWorkflow(array('contract_id'=>pk_decrypt($vn['id_contract']),'parent_id'=>pk_decrypt($vn['id_contract_workflow'])));
            foreach($get_subtasks_data as $ks=>$vs){
                $project_sub_task['activity_name']=$get_subtasks_data[$ks]['workflow_name'];
                $project_sub_task['recurrence_till']=$get_subtasks_data[$ks]['Execute_by'];
                $project_sub_task['calender_id'] = pk_encrypt($get_subtasks_data[$ks]['calender_id']);
                $project_sub_task['id_contract_workflow'] = pk_encrypt($get_subtasks_data[$ks]['id_contract_workflow']);
                $project_sub_task['calender_id'] = pk_encrypt($get_subtasks_data[$ks]['calender_id']);
                $calculatedProgressSubtask=$this->calculateScoreAndProgress(array('id_contract_review'=>$get_subtasks_data[$ks]['id_contract_review'],'user_id'=>0,'is_subtask'=>1));
                $project_sub_task['score']=$calculatedProgressSubtask['score'];
                $project_sub_task['contract_progress']=$calculatedProgressSubtask['contract_progress'];
                $project_sub_task['validation_info']='';
                $project_sub_task['id_contract_review'] = pk_encrypt($get_subtasks_data[$ks]['id_contract_review']);
                $project_sub_task['is_workflow'] = 1;
                $project_sub_task['is_subtask'] = 1;
                $project_sub_task['id_contract'] = pk_encrypt($get_subtasks_data[$ks]['contract_id']); 
                // $result[0]['project_task_count']=count($project_task_data);          
                if(isset($get_subtasks_data[$ks]['workflow_status']) && $get_subtasks_data[$ks]['workflow_status']=='workflow in progress'){
                    // $result[0]['initiated']=true;
                    $project_sub_task['initiated']=true;
                }
                else{
                    // $result[0]['initiated']=false;
                    $project_sub_task['initiated']=false;
                }
                if($data['id_contract_workflow']!=$get_subtasks_data[$ks]['id_contract_workflow']){
                    $subtask_data[]=$project_sub_task;
                }
            }
        }
        $key = array_search($contract_workflow_Id, array_column($subtask_data, 'id_contract_workflow'));
        $first_task_to_display=$subtask_data[$key];
        unset($subtask_data[$key]);
        array_unshift($subtask_data,$first_task_to_display);
        $result[0]['validation_status']=$new_project_task_data[0]['validation_status'];
        // print_r($result[0]['contract_status']);exit;
        $result[0]['contract_status']=$new_project_task_data[0]['workflow_status'];
        $result[0]['is_workflow']=1;
        $result[0]['contribution_type']=$this->session_user_info->contribution_type;
        $result[0]['project_task']=$subtask_data;
        // $result[0]['project_first_task']=$project_first_task;
        $reminder_days = $this->User_model->check_record('relationship_category_remainder',array('customer_id'=>$data['customer_id'],'relationship_category_id'=>null));
        if(count($reminder_days) == 0){
            $reminder_days[0]['days'] = 0;
        }
        $result[0]['review_scheduled'] = 0;
        if(isset($data['is_workflow']) && $data['is_workflow']==1 && isset($contract_workflow_id) && $contract_workflow_id > 0){
            $check_review_schedule = $this->Contract_model->check_workflow_in_calender(
                                                            array(
                                                                'id_contract_workflow' => $contract_workflow_id,
                                                                'days' => $reminder_days[0]['days']
                                                            ));
        }
        $check_review_schedule1 = $this->Contract_model->checkContractReviewCompletedSchedule(
                                                        array(
                                                            'contract_id' => $data['project_id'],
                                                            'is_workflow' => 1
                                                        ));//echo '<pre>'.$this->db->last_query();exit;
        if(!empty($check_review_schedule) && empty($check_review_schedule1)){
            $result[0]['review_scheduled'] = 1;
        }
        unset($project_task);
        $currentReviewId = $this->Contract_model->getCurrentContractReviewId(array('contract_id' =>$data['project_id'],'is_workflow'=>1,'contract_Workflow_id'=>$contract_workflow_id));
         $get_last_review=$this->Project_model->getlastReview(array('contract_workflow_id'=>$contract_workflow_id));
        $result[0]['contract_last_reviewed_on']=isset($get_last_review[0]['updated_on'])?date('Y-m-d',strtotime($get_last_review[0]['updated_on'])):'---';
        $result[0]['contract_user_access']=$this->session_user_info->access;
        $result[0]['score']='';
        $result[0]['contract_progress']='';
        $result['all_modles_validated'] = true;
        // print_r($currentReviewId);
        if(count($currentReviewId)>0){
            $data['contract_review_id'] = $currentReviewId[0]['id_contract_review'];
            if($this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$currentReviewId[0]['id_contract_review'],'id_user'=>$this->session_user_info->id_user))>0){
                $result[0]['contract_user_access']='co';
            }
            $contract_progress_score=$this->calculateScoreAndProgress(array('id_contract_review'=>isset($data['contract_review_id'])?$data['contract_review_id']:$currentReviewId[0]['id_contract_review'],'user_id'=>!empty($this->session_user_id)?$this->session_user_id:0,'is_subtask'=>$subTaskVal,'owner_id'=>$result[0]['contract_owner_id'],'delegate_id'=>$result[0]['delegate_id']));//new funcion for calculating  the score and contract progress
            $result[0]['score']=$contract_progress_score['score'];
            $result[0]['contract_progress']=$contract_progress_score['contract_progress'];
            $data['contribution_type'] = 1;
            $modules = $this->Contract_model->getValidatormodules(array('contract_review_id'=>$currentReviewId[0]['id_contract_review'],'contribution_type'=>1));
            // print_r($modules);
            foreach($modules as $k => $v)
                if((int)$v['module_status'] != 3){
                    $result['all_modles_validated'] = false;
                    break;
                }
            //Changing the column dynamically.
            $answer_column = 'question_answer';
            if((int)$this->session_user_info->contribution_type == 1)
                $answer_column = 'v_question_answer';
            
            //echo '<pre>'.print_r($data);exit;
            $data['dynamic_column'] = $answer_column;
            $data['contribution_type'] = 1;
            $modules = $this->Contract_model->getValidatormodules($data);
            foreach($modules as $k => $v)
                if((int)$v['module_status'] != 3){
                    $result[0]['all_modles_validated'] = false;
                    break;
                }
            //commented in 8.2 sprint for getting validator button even if progress is <100%    
            // foreach($modules as $k => $v){
            //     if((int)$v['module_status'] ==2){
            //         $ready_for_validation = true;
            //     }
            //     else{
            //         $ready_for_validation = false;
            //         break;
            //     }
            // }
            // if($ready_for_validation && $result[0]['contract_progress']== '100%'){
            //     $result[0]['ready_for_validation']=true;
            // }
            // else{
            //     $result[0]['ready_for_validation']=false;
            // }
            foreach($modules as $k => $v){
                $module_id = $v['id_module'];
                $validators_on_module = $this->User_model->custom_query('SELECT * from contract_user cu JOIN user u on u.id_user = cu.user_id WHERE cu.module_id ='.$module_id.' AND u.contribution_type = 1 AND cu.status = 1');
                if(!empty($validators_on_module) && ((int)$v['module_status'] ==1||(int)$v['module_status'] ==2))
                {
                    $ready_for_validation = true;  
                    break;
                }
                else
                {
                    $ready_for_validation = false;
                }
            }
            if($ready_for_validation){
                $result[0]['ready_for_validation']=true;
            }
            else{
                $result[0]['ready_for_validation']=false;
            }
        }


        $result[0]['ideedi'] = "annus";
        $result[0]['reaaer'] = "annus";
        if(count($currentReviewId) > 0){
            $result[0]['ideedi']=(count($this->Contract_model->getContractReviewDiscussionModuleCount(array('id_contract_review'=>$currentReviewId[0]['id_contract_review'],'discussion_status'=>1)))>0)?"itako":'annus';
            $get_reaaer=$this->User_model->check_record('contract_review',array('id_contract_review'=>$currentReviewId[0]['id_contract_review']));
            if(!empty($get_reaaer) && $get_reaaer[0]['contract_review_status']=='workflow in progress'){
                $result[0]['reaaer'] = "itako";
            }
        }
        $result[0]['id_contract']=pk_encrypt($data['project_id']);
        $result[0]['business_unit_id']=pk_encrypt($result[0]['business_unit_id']);
        $result[0]['contract_owner_id']=pk_encrypt($result[0]['contract_owner_id']);
        $result[0]['delegate_id']=pk_encrypt($result[0]['delegate_id']);
        $currency_details=$this->User_model->getCurrencyDetails(array('contract_id'=>pk_decrypt($result[0]['id_contract'])));
        $result[0]['currency_id']=pk_encrypt($currency_details[0]['currency_id']);
        $result[0]['created_by']=pk_encrypt($result[0]['created_by']);
        $result[0]['contract_end_date']=!empty($result[0]['contract_end_date'])?$result[0]['contract_end_date']:null;
        //$result[0]['validation_contributor']=$this->session_user_info->contribution_type == 1?true:false;
        $result[0]['validation_contributor']= false;
        if((!empty($contract_workflow_id))&&($this->session_user_info->contribution_type == 1))
        {
            $contractReviewDetails = $this->User_model->check_record('contract_review',array('contract_workflow_id'=>$contract_workflow_id));
            if(!empty($contractReviewDetails[0])&& ($contractReviewDetails[0]['validation_status'] == 2))
            {
                //$result[0]['validation_contributor']= true;
                $contractUser = $this->User_model->check_record("contract_user",array("contract_review_id"=>$contractReviewDetails[0]['id_contract_review'],"user_id"=>$this->session_user_info->id_user));
                if(!empty($contractUser[0]))
                {
                    $result[0]['validation_contributor']= true;
                } 
            }
        }
        //$this->session_user_info->contribution_type == 1?true:false;
        if(is_null($result[0]['contract_end_date'])){
            $result[0]['contract_end_date']='';
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result ,'project_info'=>$result[0]['project_information'],'project_attachments'=>count($result[0]['attachment']['all_records']),'connected_contracts'=>count($connectedcontract));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updateProject_post()
    {
        $data = $this->input->post();

        if(isset($data['contract'])){
            $data = $data['contract'];
        }

        if(isset($_FILES['file']))
            $totalFilesCount = count($_FILES['file']['name']);
        else
            $totalFilesCount=0;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('id_contract_req')));
        $this->form_validator->add_rules('business_unit_id', array('required'=>$this->lang->line('business_unit_id_req')));
        $this->form_validator->add_rules('contract_name', array('required'=>$this->lang->line('contract_name_req')));
        $this->form_validator->add_rules('contract_owner_id', array('required'=>$this->lang->line('contract_owner_id_req')));
        $this->form_validator->add_rules('description', array('required'=>$this->lang->line('contract_description_req')));
        $this->form_validator->add_rules('currency_id', array('required'=>$this->lang->line('currency_id_req')));
        $this->form_validator->add_rules('contract_start_date', array(
                'required'=>$this->lang->line('contract_start_date_req'),
                'date' => $this->lang->line('contract_start_date_invalid')
                ));
        // $this->form_validator->add_rules('contract_end_date', array(
        //         'required'=>$this->lang->line('contract_end_date_req'),
        //         'date' => $this->lang->line('contract_end_date_invalid')
        //         ));
        $this->form_validator->add_rules('contract_value', array('required'=>$this->lang->line('contract_value_req')));
        $this->form_validator->add_rules('currency_id', array('required'=>$this->lang->line('currency_id_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        if(empty($data['currency_id'])){
            // $result = array('status'=>FALSE,'error'=>$this->lang->line('currency_id_req'),'data'=>'');
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('currency_id_req')), 'data'=>'3');

            $this->response($result, REST_Controller::HTTP_OK);
        }
        $validated = $this->form_validator->validate($data);

        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
      
        if(isset($data['id_contract'])) {
            $data['id_contract'] = pk_decrypt($data['id_contract']);
            // if(!in_array($data['id_contract'],$this->session_user_contracts)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all') {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            // if(!in_array($data['business_unit_id'],$this->session_user_business_units)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            // if(!in_array($data['contract_owner_id'],$this->session_user_customer_all_users)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
       
       
        $contract_rc_type = $this->User_model->check_record('contract',array('id_contract'=>$data['id_contract']));
        
         
        if(isset($data['currency_id'])) {
            $data['currency_id'] = pk_decrypt($data['currency_id']);
            // if(!in_array($data['currency_id'],$this->session_user_master_currency)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
           
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
           
        }
        if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($data['delegate_id'] > 0 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message9'=>$this->lang->line('permission_not_allowed')), 'data'=>'10');
                
            // }
        }
       
    
        $contract_info = $this->User_model->check_record_selected('contract_end_date','contract',array('id_contract'=>$data['id_contract']));
       
        if(round((strtotime($contract_info[0]['contract_end_date']) - strtotime($data['contract_end_date']))/ (60 * 60 * 24))>0)
            $this->User_model->update_data('spent_lines',array('status'=>0,'updated_by' => $data['created_by'],'updated_on' => currentDate()),array('contract_id'=>$data['id_contract']));
        if(!empty($data['contract_end_date']) && $data['contract_start_date']>$data['contract_end_date']){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('contract_start_data_is_less')), 'data'=>'3');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $add = array(
            'can_review' => $data['can_review'],
            'id_contract' => $data['id_contract'],
            'business_unit_id' => $data['business_unit_id'],
            'contract_name' => $data['contract_name'],
            'contract_owner_id' => $data['contract_owner_id'],
            'contract_start_date' => $data['contract_start_date'],
            'contract_end_date' => !empty($data['contract_end_date'])?$data['contract_end_date']:null,
            'contract_value' => $data['contract_value'],
            'currency_id' => $data['currency_id'],
            'delegate_id' => isset($data['delegate_id'])?(int)$data['delegate_id']:null,
            'description' => isset($data['description'])?$data['description']:'',
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
            'parent_contract_id' => isset($data['parent_contract_id'])?$data['parent_contract_id']:0,
            'project_status'=>isset($data['status']) && $data['status']==1?1:0,
            'is_deleted'=>0

        );
     
        
        $this->project_change_log($data);
        $this->Project_model->updateProject($add);
        // echo ''.$this->db->last_query(); exit;
    
        $customer_id=$this->session_user_info->customer_id;
        $path=FILE_SYSTEM_PATH.'uploads/';

        $contract_documents=array();
        if(!is_dir($path.$customer_id)){ mkdir($path.$customer_id); }
        if(isset($_FILES) && $totalFilesCount>0)
        {
            for($i_attachment=0; $i_attachment<$totalFilesCount; $i_attachment++) {
                $imageName = doUpload(array(
                    'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                    'image' => $_FILES['file']['name'][$i_attachment],
                    'upload_path' => $path,
                    'folder' => $customer_id));
                $contract_documents[$i_attachment]['module_id']=$customer_id;
                $contract_documents[$i_attachment]['module_type']='customer';
                $contract_documents[$i_attachment]['reference_id']=$data['id_contract'];
                $contract_documents[$i_attachment]['reference_type']='contract';
                $contract_documents[$i_attachment]['document_name']=$_FILES['file']['name'][$i_attachment];
                $contract_documents[$i_attachment]['document_type']=0;
                $contract_documents[$i_attachment]['document_source']=$imageName;
                $contract_documents[$i_attachment]['document_mime_type']=$_FILES['file']['type'][$i_attachment];
                $contract_documents[$i_attachment]['document_status']=1;
                $contract_documents[$i_attachment]['uploaded_by']=$data['created_by'];
                $contract_documents[$i_attachment]['uploaded_on']=currentDate();
            }
        }

        if(count($contract_documents)>0){
            $this->Document_model->addBulkDocuments($contract_documents);
        }

        $contract_documents = array();
        if(isset($data['links']))
        foreach($data['links'] as $k => $v){
            $contract_documents[$k]['module_id'] = $customer_id;
            $contract_documents[$k]['module_type'] = 'customer';
            $contract_documents[$k]['reference_id'] = $data['id_contract'];
            $contract_documents[$k]['reference_type'] = 'contract';
            $contract_documents[$k]['document_name'] = $v['title'];
            $contract_documents[$k]['document_type'] = 1;
            $contract_documents[$k]['document_source'] = $v['url'];
            $contract_documents[$k]['document_mime_type'] = 'URL';
            $contract_documents[$k]['uploaded_by'] = $data['uploaded_by'];
            $contract_documents[$k]['uploaded_on'] = currentDate();
            $contract_documents[$k]['updated_on'] = currentDate();
        }
        if(count($contract_documents)>0){
            $this->Document_model->addBulkDocuments($contract_documents);
        }
        
        if(isset($data['attachment_delete'])) { //for deleted options
            for ($s = 0; $s < count($data['attachment_delete']); $s++) {
                $data['attachment_delete'][$s]['id_document']=pk_decrypt($data['attachment_delete'][$s]['id_document']);
                $this->Document_model->updateDocument(array(
                    'id_document' => $data['attachment_delete'][$s]['id_document'],
                    'document_status' => 0
                ));
            }
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('project_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function mappingProviderToProject_post(){
        $data = $this->input->post();
        // print_r($data);exit;
        $data = $data['params'];
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('provider_id', array('required'=>$this->lang->line('provider_id_req')));
        $this->form_validator->add_rules('project_id', array('required'=>$this->lang->line('project_id_req')));

        $validated = $this->form_validator->validate($data);

        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['provider_id'])){
            $data['provider_id']=pk_decrypt($data['provider_id']);
        }
        if(isset($data['project_id'])){
            $data['project_id']=pk_decrypt($data['project_id']);
        }
        // print_r($data);exit;
        $check_record_exists=$this->User_model->check_record('project_providers',array('project_id'=>$data['project_id'],'provider_id'=>$data['provider_id']));
        if(!empty($check_record_exists)){
            if(isset($check_record_exists[0]['is_linked']) && $check_record_exists[0]['is_linked']==1){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('provider_alrady_link')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else{
                $this->User_model->update_data('project_providers',array('is_linked'=>1),array('project_id'=>$data['project_id'],'provider_id'=>$data['provider_id']));

            }
        }
        else{
            $this->User_model->insert_data('project_providers',array('project_id'=>$data['project_id'],'provider_id'=>$data['provider_id'],'is_linked'=>1));
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_linked'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function initiateProjectTask_get()
    {
        $data = $this->input->get();
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
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
            if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
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
            $check_task_type=$this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['id_contract_workflow']));
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
            if($data['is_workflow'] == 1 && $check_task_type[0]['parent_id']==0)
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
        if($data['is_workflow'] == 1 && $check_task_type[0]['parent_id']==0)
            $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_INITIATE'));
        
        if($template_configurations_parent['total_records']>0 && !empty($cust_admin_info) && $check_task_type[0]['parent_id']==0){
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
                $this->load->library('sendgridlibrary');
                $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                if($mail_sent_status==1)
                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
            }

        }
        if(isset($contract_info[0]['delegate_id'])){
            $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
            
            if($template_configurations_parent['total_records']>0 && !empty(($delegate_info)) && $check_task_type[0]['parent_id']==0){
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
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }

            }
        }
        if($template_configurations_parent['total_records']>0 && !empty($bu_info) && $check_task_type[0]['parent_id']==0){
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
        $result = array('status'=>TRUE, 'message' => $msg, 'data'=>$data['contract_review_id']);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function getrojectProviders_get(){
        $data = $this->input->get();
        // echo 'data'.'<pre>';print_r($data);exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('project_id', array('required'=>$this->lang->line('project_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['project_id'])) {
            $data['project_id'] = pk_decrypt($data['project_id']);
        }
        $get_active_provider=$this->Project_model->getactiveprojectProvider($data);  
        $providers=array();
        foreach($get_active_provider as $k=>$v){
            // print_r($v);exit;
            $providers[$k]['provider_id']=pk_encrypt($v['provider_id']);
            $providers[$k]['provider_name']=$v['provider_name'];
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$providers);
        $this->response($result, REST_Controller::HTTP_OK);

    }
        //* calculate the contract progress and score start *//
        function calculateScoreAndProgress($data){
            if($data['is_subtask']==1){
                // $this->session_user_info->contribution_type=3;
            }
            else{
                $userData = $this->User_model->check_record('user',array('id_user' => $this->session_user_info->id_user));
                $this->session_user_info->contribution_type= $userData[0]['contribution_type'];
            }
            if(!empty($data['id_contract_review']) && $data['id_contract_review'] > 0){
                //Changing the column dynamically.
                $answer_column = 'question_answer';
                // if((int)$this->session_user_info->contribution_type == 1)
                //     $answer_column = 'v_question_answer';
                if((int)$this->session_user_info->contribution_type == 1)
                {
                    if(
                        ((isset($data['owner_id']))&&($this->session_user_info->id_user==$data['owner_id']))
                        ||
                        ((isset($data['delegate_id']))&&($this->session_user_info->id_user == $data['delegate_id']))
                        )
                    {
                        $answer_column = 'question_answer';
                    }
                    else{
                        $answer_column = 'v_question_answer';
                    }
                }
    
                $provider_visibility = array(1,0);
                $contributor_modules = $this->User_model->check_record_selected('module_id','contract_user',array('contract_review_id'=>$data['id_contract_review'],'user_id'=>$data['user_id'],'status'=>1));//echo $this->db->last_query();exit;
                $contributor_modules = array_map(function($i){ return $i['module_id']; },$contributor_modules);
                $contract_progress=0;
                if($data['is_subtask']==1){
                    // $this->session_user_info->contribution_type=3;
                    // $provider_visibility = array(1);
                }
                $modules = $this->Contract_model->getContractReviewModuleScoreProgress(array('contract_review_id' => $data['id_contract_review'],'dynamic_column'=>$answer_column));
                if(count($contributor_modules)>0){
                    if((int)$this->session_user_info->contribution_type == 3)
                        $provider_visibility = array(1);
                    foreach($modules as $k=>$v){
                        foreach($contributor_modules as $c=>$d){
                            if($v['module_id']==$d){
                                $new_modules_array[]=$v;
                            }
                        }
                    }
                }
                else{
                    $new_modules_array=$modules;
                }
                foreach($new_modules_array as $n=>$m){
                    $contract_progress += $this->Contract_model->progress(array('module_id'=>$m['module_id'],'contract_review_id'=>$data['id_contract_review'],'provider_visibility'=>$provider_visibility,'dynamic_column'=>$answer_column));
                }
                if(count($new_modules_array)>0){
                    $contract_progress = round($contract_progress/count($new_modules_array)).'%'; 
                }
                else{
                    $contract_progress = '0%';
                }
                // $module_score = $this->User_model->check_record('module',array('contract_review_id' => $data['id_contract_review']));
                // $score = getScore($scope = array_map(function($i){ return strtolower($i['module_score']); },$module_score));
                
                //The following line is for global.
                $module_score = $this->Contract_model->getContractReviewModuleScore(array('contract_review_id' => $data['id_contract_review'],'is_subtask'=>$data['is_subtask']));
                $score_module = array();
                for($s=0;$s<count($module_score);$s++)
                {
                    $score_module[$s] = getScoreByCount($module_score[$s]);                
                }
                // print_r($score_module);exit; 
                $score = getScore($scope = array_map(function($i){ return strtolower($i); },$score_module));
                // echo $this->db->last_query();exit;
                //echo json_encode($result);exit;
                if($this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$data['id_contract_review'],'id_user'=>$this->session_user_info->id_user))>0){
                    //print_r($module_ids);
                    
                    $data['contract_review_id'] = $data['id_contract_review'];
                    $data['id_user'] = $this->session_user_info->id_user;
                    $q = 'SELECT *,cu.module_id as id_module from contract_user cu  WHERE cu.contract_review_id = '.$data['id_contract_review'].' AND cu.user_id = '.$this->session_user_id.'  AND cu.status = 1';
                    $new_user_modules = $this->User_model->custom_query($q);
                    // $new_user_modules = $this->Contract_model->getContractReviewModule($data);
                    //echo $this->db->last_query();exit;
                    $module_ids = array_map(function ($i){ return $i['id_module'];},$new_user_modules);
                    
                    $module_score = $this->Contract_model->getContributorContractReviewModuleScore(array('contract_review_id' => $data['id_contract_review'],'provider_visibility'=>$provider_visibility,'module_ids' => $module_ids,'dynamic_column'=>$answer_column));
                    for ($sr = 0; $sr < count($module_score); $sr++) {
                        $module_score[$sr]['score'] = getScoreByCount($module_score[$sr]);
                    }
                    // echo $this->db->last_query();exit;
                    $new_module_score = array();
                    foreach($new_user_modules as $usm){
                        for ($sr = 0; $sr < count($module_score); $sr++) {
                            //echo '<pre>'.$module_score[$sr]['module_id'].' == '.$usm['id_module'];
                            //$module_score[$sr]['score'] = 'green';
                            if($module_score[$sr]['module_id'] == $usm['id_module']){
                                $module_score[$sr]['score'] = getScoreByCount($module_score[$sr]);
                                $new_module_score[]=$module_score[$sr];
                            }
                        }
                    }
                    
                    $score = getScore(array_map(function ($i) {
                        return strtolower($i['score']);
                    }, $module_score));
                }
                return array('contract_progress'=>$contract_progress,'score'=>$score);
            }
            else{
                return array('contract_progress'=>'0%','score'=>'');
            }
        }
        //* calculate the contract progress and score end *//
        public function contractContributor_post(){
            $data = $this->input->post();
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
    
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $this->form_validator->add_rules('module_id', array('required'=>$this->lang->line('module_id_req')));
            $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
            $data['contributors_add']=isset($data['contributors_add'])?$data['contributors_add']:'';
            $data['contributors_remove']=isset($data['contributors_remove'])?$data['contributors_remove']:'';
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($data['contract_id'])) {
                $data['contract_id'] = pk_decrypt($data['contract_id']);
                if(!in_array($data['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['module_id'])) {
                $data['module_id'] = pk_decrypt($data['module_id']);
                if(!in_array($data['module_id'],$this->session_user_contract_review_modules)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['created_by'])) {
                $data['created_by'] = pk_decrypt($data['created_by']);
                if($data['created_by']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_contract'])) {
                $data['id_contract'] = pk_decrypt($data['id_contract']);
                if(!in_array($data['id_contract'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['topic_id'])) {
                $data['topic_id'] = pk_decrypt($data['topic_id']);
                if(!in_array($data['topic_id'],$this->session_user_contract_review_topics)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['user_role_id'])) {
                $data['user_role_id'] = pk_decrypt($data['user_role_id']);
                if($data['user_role_id']!=$this->session_user_info->user_role_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['customer_id'])) {
                $data['customer_id'] = pk_decrypt($data['customer_id']);
                if($this->session_user_info->customer_id!=$data['customer_id']){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
    
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
                if($data['contract_review_id']>0 && !in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>$this->lang->line('you_dont_have_permissions_to_this_module'), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            $data['contributors_add'] = $data['expert']['contributors_add'];
            $data['contributors_remove'] = $data['expert']['contributors_remove'];
            //.','.$data['validator']['contributors_add'].','.$data['provider']['contributors_add'];
            if(isset($data['validator']['contributors_add']) && $data['validator']['contributors_add']!='')
                $data['contributors_add'] .= ','.$data['validator']['contributors_add'];
            if(isset($data['provider']['contributors_add']) && $data['provider']['contributors_add']!='')
                $data['contributors_add'] .= ','.$data['provider']['contributors_add'];
            if(isset($data['validator']['contributors_remove']) && $data['validator']['contributors_remove']!='')
                $data['contributors_remove'] .= ','.$data['validator']['contributors_remove'];
            if(isset($data['provider']['contributors_remove']) && $data['provider']['contributors_remove']!='')
                $data['contributors_remove'] .= ','.$data['provider']['contributors_remove'];
            
            if($data['contributors_add'][0] == ',')
                $data['contributors_add'] = ltrim($data['contributors_add'], $data['contributors_add'][0]);
            if($data['contributors_remove'][0] == ',')
                $data['contributors_remove'] = ltrim($data['contributors_remove'], $data['contributors_remove'][0]);
            $contributors_add_exp=explode(',',$data['contributors_add']);
            $contributors_add_exp_new=array();
            foreach($contributors_add_exp as $k=>$v){
                $contributors_add_exp_new[]=$cntr=pk_decrypt($v);
            }
            $data['contributors_add']=implode(',',$contributors_add_exp_new);
    
            $contributors_remove_exp=explode(',',$data['contributors_remove']);
            // echo '<pre>'.print_r($contributors_remove_exp);exit;
            $contributors_remove_exp_new=array();
            foreach($contributors_remove_exp as $k=>$v){
                $contributors_remove_exp_new[]=$cntr=pk_decrypt($v);
            }
            $data['contributors_remove']=implode(',',$contributors_remove_exp_new);
            $update = array(
                'contract_id' => $data['contract_id'],
                'created_by' => $data['created_by'],
                'contributors_add' => explode(',',$data['contributors_add']),
                'contributors_remove' => explode(',',$data['contributors_remove']),
                'module_id' => $data['module_id'],
                'created_on' => currentDate()
            );
            //echo 'update <pre>'.print_r($update);exit;
            if(isset($data['contract_review_id']))
                $update['contract_review_id']=$data['contract_review_id'];
    
            if(isset($data['provider']['contributors_add']) && $data['provider']['contributors_add']!=''){
                $this->create_subtask($data);
            }
            if(isset($data['provider']['contributors_remove']) && $data['provider']['contributors_remove']!=''){
                $this->remove_subtask($data);
            }
            $to_id = $this->Contract_model->addContractContributors($update);
            $bu_info = $this->Contract_model->getContractCurrentDetails(array('contract_id'=>$update['contract_id']));
            $module_id = $data['module_id'];
            $q = 'SELECT * from contract_user cu JOIN user u on u.id_user = cu.user_id WHERE cu.module_id ='.$module_id.' AND u.contribution_type = 1 AND cu.status = 1';
            $validators_on_module = $this->User_model->custom_query($q);
            $send['contract_review_id'] = $data['contract_review_id'];
            $send['module_id'] = $module_id;
            $module_progress = $this->Contract_model->progress($send);
            if(count($validators_on_module) > 0 && (int)$module_progress == 100)
                $this->User_model->update_data('module',array('module_status'=>2),array('id_module'=>$module_id));  
            if(count($validators_on_module) == 0)
                $this->User_model->update_data('module',array('module_status'=>1),array('id_module'=>$module_id)); 
    
            $msg = $this->lang->line('contributor_updated_successfully');
    
            //Mailing...
            $module_info = $this->Module_model->getModuleName(array('language_id'=>1,'module_id'=>$data['module_id']));
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
            $cust_admin_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['created_by']));
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
            /*$cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
           $cust_admin = $cust_admin['data'][0];*/
    
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            foreach($to_id as $k => $v)
            {
                $To = $this->User_model->getUserInfo(array('user_id' => $to_id[$k],'user_status'=>1));
    
                //sending mail to bu owner
                if($module_info[0]['is_workflow'] == 1){
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_ASSIGN_MODULE'));
                    // echo $this->db->last_query();exit;
                }
                if($template_configurations_parent['total_records']>0 && !empty($To)){
                    $template_configurations=$template_configurations_parent['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$To->first_name;
                    $wildcards_replaces['last_name']=$To->last_name;
                    $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                    if($module_info[0]['is_workflow'] == 1){
                        $wildcards_replaces['project_workflow_assigned_module_user_name']=$To->first_name.' '.$To->last_name.' ('.$To->user_role_name.')';
                    }
                    $wildcards_replaces['module_name']=$module_info[0]['module_name'];
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                    $from_name=$template_configurations['email_from_name'];
                    $from=$template_configurations['email_from'];
                    $to=$To->email;
                    $to_name=$To->first_name.' '.$To->last_name;
                    $mailer_data['mail_from_name']=$from_name;
                    $mailer_data['mail_to_name']=$to_name;
                    $mailer_data['mail_to_user_id']=$To->id_user;
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
            }
            $result = array('status'=>TRUE, 'message' => $msg, 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        function create_subtask($provider_data){
            // $check_provider_existance=$this->User_model->check_record_in('contract_review','')
            $add_provider_ids=explode(',',$provider_data['provider']['contributors_add']);
            $add_provider_ids_encrypt=array_filter($add_provider_ids);
            $add_provider_ids_decrypt = array_map(function($i){ return pk_decrypt($i); },$add_provider_ids_encrypt);            
            foreach($add_provider_ids_decrypt as $v){
                $check_existance_provider=$this->User_model->check_record('contract_user',array('contract_review_id'=>$provider_data['contract_review_id'],'contract_id'=>$provider_data['contract_id'],'module_id'=>$provider_data['module_id'],'user_id'=>$v,'status'=>1));
                $get_user_name=$this->User_model->check_record_selected(array('CONCAT(first_name," ",last_name) as user_name','provider'),'user',array('id_user'=>$v));
                $get_provider_name = $this->User_model->check_record_selected('provider_name','provider',array('id_provider'=>$get_user_name[0]['provider']));
                if(empty($check_existance_provider)){
                    // print_r();exit;
                    $getmaintask_info=$this->Project_model->getmaintaskinfo(array('contract_review_id'=>$provider_data['contract_review_id']));
                    $get_calender_record=$this->User_model->check_record('calender',array('id_calender'=>$getmaintask_info[0]['calender_id']));
                    $calender_data_array=array(
                        'customer_id'=>$get_calender_record[0]['customer_id'],
                        'date'=>date('Y-m-d'),
                        'created_on'=>currentDate(),
                        'created_by'=>$get_calender_record[0]['created_by'],
                        'status'=>$get_calender_record[0]['status'],
                        'bussiness_unit_id'=>$get_calender_record[0]['bussiness_unit_id'],
                        'contract_id'=>$get_calender_record[0]['contract_id'],
                        'recurrence_till'=>$get_calender_record[0]['recurrence_till'],
                        'is_workflow'=>$get_calender_record[0]['is_workflow'],
                        'workflow_name'=>$getmaintask_info[0]['workflow_name'].' ('.$get_provider_name[0]['provider_name'].' - '.$get_user_name[0]['user_name'].')',
                        'workflow_id'=>$get_calender_record[0]['workflow_id'],
                        'plan_executed'=>1,
                        'auto_initiate'=>1,
                        'type'=>$get_calender_record[0]['type'],
                        'task_type'=>'sub_task',
                        'relationship_category_id'=>'',
                        'provider_id'=>'',
                        'recurrence'=>0,
                    );
                    $inserted_calender_id=$this->User_model->insert_data('calender',$calender_data_array);
                    $contract_workflow_array=array(
                        'contract_id'=>$getmaintask_info[0]['contract_id'],
                        'workflow_id'=>$getmaintask_info[0]['workflow_id'],
                        'workflow_name'=>$getmaintask_info[0]['workflow_name'].' ('.$get_provider_name[0]['provider_name'].' - '.$get_user_name[0]['user_name'].')',
                        'Execute_by'=>$getmaintask_info[0]['Execute_by'],
                        'calender_id'=>$inserted_calender_id,
                        'created_on'=>currentDate(),
                        'created_by'=>$provider_data['created_by'],
                        'workflow_status'=>'new',
                        'provider_id'=>$v,
                        'parent_id'=>$getmaintask_info[0]['id_contract_workflow'],

                    );
                    $inserted_calender_id=$this->User_model->insert_data('contract_workflow',$contract_workflow_array);
                }
            }
            
        }
        function remove_subtask($removing_data=null){
            $remove_provider_ids=explode(',',$removing_data['contributors_remove']);
            $remove_provider_ids=array_filter($remove_provider_ids);
            foreach($remove_provider_ids as $k){
                $get_workflow_id=$this->User_model->check_record('contract_review',array('id_contract_review'=>$removing_data['contract_review_id']));
                $get_subtask_details=$this->User_model->check_record('contract_workflow',array('parent_id'=>$get_workflow_id[0]['contract_workflow_id'],'provider_id'=>$k,'status'=>1));
                $getting_contractReview_details = $this->User_model->check_record('contract_review',array('contract_id'=>$get_subtask_details[0]['contract_id'],'calender_id'=>$get_subtask_details[0]['calender_id'],'contract_workflow_id'=>$get_subtask_details[0]['id_contract_workflow']));
                if(!empty($getting_contractReview_details[0]))
                {
                    $this->Project_model->delete('contract_user',array('contract_id'=>$getting_contractReview_details[0]['contract_id'],'user_id'=>$k,'contract_review_id'=>$getting_contractReview_details[0]['id_contract_review']));
                }
                $this->User_model->update_data('contract_workflow',array('status'=>0),array('id_contract_workflow'=>$get_subtask_details[0]['id_contract_workflow']));
            }
        }
        public function projecttaskfinalize_post()
        {
            $data = $this->input->post();
            //echo 'data'.'<pre>';print_r($data);
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
    
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $this->form_validator->add_rules('contract_review_id', array('required'=>$this->lang->line('contract_review_id_req')));
            $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($data['contract_id'])) {
                $data['contract_id'] = pk_decrypt($data['contract_id']);
                if(!in_array($data['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
                $check_project=$this->User_model->check_record('contract',array('id_contract'=>$data['contract_id']));
                if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews) && $check_project[0]['type']=='contract'){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['created_by'])) {
                $data['created_by'] = pk_decrypt($data['created_by']);
                if($data['created_by']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_user'])) {
                $data['id_user'] = pk_decrypt($data['id_user']);
                if($data['id_user']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_workflow_id'])){
                $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
            }
            // print_r($this->session_user_info); 
            $getsubtasks=$this->Project_model->getsubtaskstofinalize(array('parent_id'=>$data['contract_workflow_id']));
            // print_r($getsubtasks); 
            // print_r($data);
            if(!empty($getsubtasks)){
                foreach($getsubtasks as $sb=>$sub){
                    $this->User_model->update_data('contract_workflow',array('workflow_status'=>'workflow finlized','status'=>0),array('id_contract_workflow'=>$sub['id_contract_workflow']));
                    if(empty($sub['id_contract_review']) || $sub['workflow_status']=='new'){
                    }
                    else{
                        // $this->subtaskfinalize_post(array('contract_review_id'=>pk_encrypt($sub['id_contract_review']),'contract_id'=>pk_encrypt($sub['contract_id']),'created_by'=>pk_encrypt($sub['created_by']),'is_workflow'=>1,'user_role_id'=>pk_encrypt($this->session_user_info->user_role_id),'id_user'=>pk_encrypt($this->session_user_info->id_user),'contract_workflow_id'=>pk_encrypt($sub['id_contract_workflow'])));
                        $this->User_model->update_data('contract_review',array('contract_review_status'=>'finished'),array('id_contract_review'=>$sub['id_contract_review']));
                    }
                }
            }

            $is_without_discussion=isset($data['finalize_without_discussion'])?$data['finalize_without_discussion']:NULL;
            $finalize_comments=isset($data['finalize_comments'])?$data['finalize_comments']:NULL;
            $contract_reviews = $this->Contract_model->getContractReview(array('contract_id'=>$data['contract_id']));
            $msg = $this->lang->line('review_finalize');
            if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
                $msg = $this->lang->line('workflow_finalize');
            // For Every Review Finalize we are storing the Static modules into stored modules.
            // Here we are storing the the static modules in to the static module table
            $first_review_modules = $this->Module_model->getStorableModules(array('contract_review_id'=>$data['contract_review_id'],'static'=>1,'module_status'=>array(1,2,3)));
            foreach($first_review_modules as $v){
                $insert_data = array(
                    'parent_module_id' => $v['parent_module_id'],
                    'module_id' => $v['id_module'],
                    'contract_id' => $data['contract_id'],
                    'status' => 1,
                    'activate_in_next_review' => 0,
                    'created_by' => $this->session_user_id,
                    'created_on' => CurrentDate(),
                    'updated_by' => $this->session_user_id,
                    'updated_on' => CurrentDate(),
                );
                $check_for_already_existance = $this->User_model->check_record('stored_modules',array('parent_module_id' => $v['parent_module_id'],'contract_id' => $data['contract_id']));
                
                if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                    $insert_data['is_workflow'] = 1;
                    $insert_data['contract_workflow_id'] = $data['contract_workflow_id'];
                    $this->User_model->update_data('contract_workflow',array('status'=>0,'workflow_status'=>'workflow finlized'),array('id_contract_workflow'=>$data['contract_workflow_id']));
                    $check_for_already_existance = $this->User_model->check_record('stored_modules',array('parent_module_id' => $v['parent_module_id'],'contract_workflow_id'=>$data['contract_workflow_id'],'contract_id' => $data['contract_id']));
                    //Deleting calendar planning.          
                }
                
                if(empty($check_for_already_existance))
                    $this->User_model->insert_data('stored_modules',$insert_data);
                else
                    $this->User_model->update_data('stored_modules',array('status' => 1,'next_plan'=>null,'updated_by' => $this->session_user_id,'updated_on' => CurrentDate(),'module_id' => $v['id_module']),array('parent_module_id' => $v['parent_module_id'],'contract_id' => $data['contract_id']));
            }
            //echo '<pre>'.print_r($first_review_modules);exit;            
            
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
            $this->Contract_model->updateContractReview(array('id_contract_review' => $data['contract_review_id'],'contract_review_status' => 'finished','updated_by' => $data['created_by'],'updated_on' => currentDate(),'finalize_comments'=>$finalize_comments,'finalize_without_discussion'=>$is_without_discussion,'contract_owner_id'=>$contract_info[0]['contract_owner_id'],'contract_delegate_id'=>$contract_info[0]['delegate_id']));
            //echo ''.$this->db->last_query(); exit;
    
            //Updating contract status only if is_workflow === 0 
            if(isset($data['is_workflow']) && $data['is_workflow'] == 0)
                $this->Contract_model->updateContract(array('id_contract' => $data['contract_id'],'contract_status' => 'review finalized'));
            $pending_discussions=$this->Contract_model->getContractDiscussion(array('id_contract_review'=>$data['contract_review_id'],'discussion_status'=>1));
            foreach($pending_discussions as $k=>$v){
                $this->closereviewdiscussion(array('id_contract_review_discussion'=>$v['id_contract_review_discussion'],'contract_id'=>$data['contract_id'],'module_id'=>$v['module_id'],'contract_review_id'=>$data['contract_review_id'],'created_by'=>$data['created_by']));
            }
            
            
            //Unlocking Contract for Review
            $check_review_schedule = $this->Contract_model->check_contract_in_calender(
                array(
                    'contract_id' => $data['contract_id'],
                    'business_unit_id' => $contract_info[0]['business_unit_id'],
                    'relationship_category_id' => $contract_info[0]['relationship_category_id'],
                    'provider_id' => $contract_info[0]['provider_name'],
                    'is_workflow' => $data['is_workflow'],
                    'only_one_contract' => true
                ));
                //echo '<pre>'.$this->db->last_query();
            if($data['is_workflow'] == '0'){
                if($check_review_schedule[0]['recurrence'] == 0){
                    //If There is no future planning reviews
                    $this->User_model->update_data('contract',array('is_lock'=>0),array('id_contract'=>$data['contract_id']));
                    //echo '<pre>'.$this->db->last_query();
                }else if(count($check_review_schedule) <= 1){
                    //If There is no future planning reviews
                    $this->User_model->update_data('contract',array('is_lock'=>0),array('id_contract'=>$data['contract_id']));
                    //echo '<pre>'.$this->db->last_query();
                }
            }
            
            //Checking for Past plannings on this contract
            $check_review_schedule = $this->Contract_model->check_contract_in_calender(
                array(
                    'contract_id' => $data['contract_id'],
                    'business_unit_id' => $contract_info[0]['business_unit_id'],
                    'relationship_category_id' => $contract_info[0]['relationship_category_id'],
                    'provider_id' => $contract_info[0]['provider_name'],
                    'only_one_contract' => true,
                    'id_calender' => $check_review_schedule[0]['id_calender']
                ));
                //echo '<pre>'.$this->db->last_query();
            foreach($check_review_schedule as $cal){
                //String to array of contract ids
                $contracts = explode(',',$cal['contract_id']);                    
                //echo '<pre>'.print_r($contracts);
                foreach($contracts as $con_k => $con_v){
                    //Checking for current contract to remove from calendar planning.
                    //echo '<br>IF'.$con_v.' == '.$data['contract_id'];
                    if($con_v == $data['contract_id']){
                        //Unset if the contract is exists in this planning.
                        unset($contracts[$con_k]);
                        //Add it to completed column.
                        $update_sql = "UPDATE calender SET completed_contract_id = IF(completed_contract_id=''||completed_contract_id IS NULL,".$data['contract_id'].",CONCAT(completed_contract_id,',',".$data['contract_id'].")) WHERE id_calender = ".$cal['id_calender'];
                        $this->User_model->custom_update_query($update_sql);
                    }
                }
                $contracts = implode(',',$contracts);
                $update_data = array('contract_id' => $contracts);
                
                if(strlen($contracts) < 1){
                    //If there are no contracts available making status to '0'
                    $update_data['status'] = 0;
                }
                 //echo '<pre>'.print_r($update_data);
                $this->User_model->update_data('calender',$update_data,array('id_calender' => $cal['id_calender']));
                 //echo '<pre>'.$this->db->last_query();
                
            }
            //echo '<pre>'.$this->db->last_query();exit;
            ////
            $business_unit = $this->Business_unit_model->getBusinessUnitDetails(array('id_business_unit'=>$contract_info[0]['business_unit_id']));
            $bu_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
            $contract_review_info = $this->Contract_model->getContractReview(array('contract_id' => $data['contract_id']));
            $cust_admin_info = $this->User_model->getUserInfo(array('customer_id' => $business_unit[0]['customer_id'],'user_role_id' =>2,'user_status'=>1));
            //$contract_review_user = $this->User_model->getUserInfo(array('user_id' => $contract_review_info[0]['created_by']));
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
            /*$cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
            $cust_admin = $cust_admin['data'][0];*/
            /*echo 'contract_info'.'<pre>';print_r($cust_admin_info);
            echo 'business'.'<pre>';print_r($business_unit);exit;
            echo 'buinfo'.'<pre>';print_r($bu_info);*/
            //echo 'contract_Review_user'.'<pre>';print_r($contract_review_user);exit;
    
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
            }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            $finalised_user = $this->User_model->getUserInfo(array('user_id' => $data['id_user']));
            // if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
            //     $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'language_id' =>1,'module_key'=>'PROJECT_TASK_FINALIZE'));
            // }else{
            // }
            $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,'module_key'=>'PROJECT_TASK_FINALIZE'));
            if($template_configurations_parent['total_records']>0 && !empty($cust_admin_info)){
                $template_configurations=$template_configurations_parent['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$cust_admin_info->first_name;
                $wildcards_replaces['last_name']=$cust_admin_info->last_name;
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                // $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                // $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                    $wildcards_replaces['project_task_finalized_date']=dateFormat(currentDate());
                }
                else{
                    $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                    $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                /*$from_name=SEND_GRID_FROM_NAME;
                $from=SEND_GRID_FROM_EMAIL;
                $from_name=$cust_admin['name'];
                $from=$cust_admin['email'];*/
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
                // print_r($mailer_data);exit;
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
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
                if(isset($delegate_info))
                    if($template_configurations_parent['total_records']>0 && !empty($delegate_info)){
                    $template_configurations=$template_configurations_parent['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$delegate_info->first_name;
                    $wildcards_replaces['last_name']=$delegate_info->last_name;
                    $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                    // $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                    // $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                    if($data['is_workflow']==1){
                        $wildcards_replaces['project_task_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                        $wildcards_replaces['project_task_finalized_date']=dateFormat(currentDate());
                    }
                    else{
                        $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                        $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                    }
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                    /*$from_name = SEND_GRID_FROM_NAME;
                    $from = SEND_GRID_FROM_EMAIL;
                    $from_name=$cust_admin['name'];
                    $from=$cust_admin['email'];*/
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
                    if($mailer_data['is_cron']==0) {
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
                $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                // $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                // $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                if($data['is_workflow']==1){
                    $wildcards_replaces['project_task_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                    $wildcards_replaces['project_task_finalized_date']=dateFormat(currentDate());
                }
                else{
                    $wildcards_replaces['contract_review_finalized_user_name']=$finalised_user->first_name.' '.$finalised_user->last_name.' ('.$finalised_user->user_role_name.')';
                    $wildcards_replaces['contract_review_finalized_date']=dateFormat(currentDate());
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                /*$from_name = SEND_GRID_FROM_NAME;
                $from = SEND_GRID_FROM_EMAIL;
                $from_name=$cust_admin['name'];
                $from=$cust_admin['email'];*/
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
                if($mailer_data['is_cron']==0) {
                    //$mail_sent_status=sendmail($to, $subject, $body, $from);
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }
    
            }
    
            $result = array('status'=>TRUE, 'message' => $msg, 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        public function reviewdiscussion_post()
        {
            $data = $this->input->post();
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $this->form_validator->add_rules('contract_review_id', array('required'=>$this->lang->line('contract_review_id_req')));
            $this->form_validator->add_rules('review_discussion', array('required'=>$this->lang->line('contract_review_id_req')));
            $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($data['contract_id'])) {
                $data['contract_id'] = pk_decrypt($data['contract_id']);
                if(!in_array($data['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
                if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['created_by'])) {
                $data['created_by'] = pk_decrypt($data['created_by']);
                if($data['created_by']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_user'])) {
                $data['id_user'] = pk_decrypt($data['id_user']);
                if($data['id_user']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['user_role_id'])) {
                $data['user_role_id'] = pk_decrypt($data['user_role_id']);
                if($data['user_role_id']!=$this->session_user_info->user_role_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            $contract_review_discussion_id=NULL;
            if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
                $data['contract_user'] = $data['id_user'];
                if(!in_array($data['contract_user'],$this->session_user_contributors)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            $result_parsed=array();
            $insert=$update=array();
            $recent_module_id=NULL;
            $message=$this->lang->line('review_discussion_save_success');
            $mail_type='';
            foreach($data['review_discussion'] as $k=>$v){
                $data['review_discussion'][$k]['id_module']=pk_decrypt($data['review_discussion'][$k]['id_module']);
                if($data['review_discussion'][$k]['id_module']>0 && !in_array($data['review_discussion'][$k]['id_module'],$this->session_user_contract_review_modules)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $data['review_discussion'][$k]['id_topic']=pk_decrypt($data['review_discussion'][$k]['id_topic']);
                if($data['review_discussion'][$k]['id_topic']>0 && !in_array($data['review_discussion'][$k]['id_topic'],$this->session_user_contract_review_topics)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $data['review_discussion'][$k]['id_contract_review_discussion']=pk_decrypt($data['review_discussion'][$k]['id_contract_review_discussion']);
                if($data['review_discussion'][$k]['id_contract_review_discussion']>0 && !in_array($data['review_discussion'][$k]['id_contract_review_discussion'],$this->session_user_contract_review_discussions)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $data['review_discussion'][$k]['contract_review_id']=pk_decrypt($data['review_discussion'][$k]['contract_review_id']);
                if($data['review_discussion'][$k]['contract_review_id']>0 && !in_array($data['review_discussion'][$k]['contract_review_id'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $data['review_discussion'][$k]['id_contract_review_discussion_question']=pk_decrypt($data['review_discussion'][$k]['id_contract_review_discussion_question']);
                if($data['review_discussion'][$k]['id_contract_review_discussion_question']>0 && !in_array($data['review_discussion'][$k]['id_contract_review_discussion_question'],$this->session_user_contract_review_discussion_questions)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $data['review_discussion'][$k]['id_question']=pk_decrypt($data['review_discussion'][$k]['id_question']);
                if($data['review_discussion'][$k]['id_question']>0 && !in_array($data['review_discussion'][$k]['id_question'],$this->session_user_contract_review_questions)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            foreach($data['review_discussion'] as $k=>$v){
                
                $recent_module_id=$v['id_module'];
                $id_contract_review_discussion=NULL;
                $discussion_initiated_user_info = $this->User_model->getUserInfo(array('user_id' => $data['created_by']));
                if($v['id_contract_review_discussion']=='' || $v['id_contract_review_discussion']==NULL){
                    $modulediscussion=$this->Contract_model->getContractReviewDiscussion(array('id_module'=>$v['id_module'],'contract_review_id'=>$v['contract_review_id']));
                    if(isset($modulediscussion['id_contract_review_discussion']) && $modulediscussion['id_contract_review_discussion']>0){
                        $id_contract_review_discussion=$modulediscussion['id_contract_review_discussion'];
                    }
                    else{
                        $insertModuleDiscussion=['contract_review_id'=>$v['contract_review_id'],'created_on'=>currentDate(),'created_by'=>$data['created_by'],'module_id'=>$v['id_module']];
                        $id_contract_review_discussion=$this->Contract_model->addContractReviewDiscussion($insertModuleDiscussion);
                        $message=$this->lang->line('review_discussion_initiate_success');
                        $mail_type='insert';
                    }
                }
                else{
                    $id_contract_review_discussion=$v['id_contract_review_discussion'];
                    $mail_type='update';
                }
                if($v['id_contract_review_discussion_question']=='' || $v['id_contract_review_discussion_question']==NULL)
                    $insert[]=['contract_review_discussion_id'=>$id_contract_review_discussion,'question_id'=>$v['id_question'],'remarks'=>$v['remarks'],'created_by'=>$data['created_by'],'created_on'=>currentDate()];
                else
                    $update[]=['id_contract_review_discussion_question'=>$v['id_contract_review_discussion_question'],'contract_review_discussion_id'=>$id_contract_review_discussion,'question_id'=>$v['id_question'],'remarks'=>$v['remarks'],'updated_by'=>$data['created_by'],'updated_on'=>currentDate(),'status'=>$v['status']];
                $this->User_model->update_data('contract_question_review',array('second_opinion'=>isset($v['second_opinion'])?$v['second_opinion']:'','updated_by'=>$data['created_by'],"updated_on" => Currentdate()),array('question_id'=>$v['id_question']));            
            }
            if(count($insert)>0){
                $this->Contract_model->addContractReviewDiscussionQuestion($insert);
            }
            if(count($update)>0){
                $this->Contract_model->updateContractReviewDiscussionQuestion($update);
            }
            
            $result_parsed['recent_module_id']=pk_encrypt($recent_module_id);
            $result=($result_parsed);
            if($mail_type!='' && $data['type']=='contract'){
                $module_info = $this->Module_model->getModuleName(array('language_id'=>1,'module_id'=>$recent_module_id));
                if($module_info[0]['is_workflow'] == 1){
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>($mail_type=='insert'?'CONTRACT_WORKFLOW_DISCUSSION_INITIATE':'CONTRACT_WORKFLOW_DISCUSSION_UPDATE')));
                }else{
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>($mail_type=='insert'?'CONTRACT_REVIEW_DISCUSSION_INITIATE':'CONTRACT_REVIEW_DISCUSSION_UPDATE')));
                }
                if($template_configurations_parent['total_records']>0){
                    $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
                    $bu_owner_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
                    $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $discussion_initiated_user_info->customer_id));
                    if(isset($contract_info[0]['delegate_id']) && $contract_info[0]['delegate_id']!=NULL && $contract_info[0]['delegate_id']>0){
                        $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                    }
                    $customer_admin_list=$this->Customer_model->getCustomerAdminList(array('customer_id'=>$discussion_initiated_user_info->customer_id,'user_status'=>1));
                    if($customer_details[0]['company_logo']=='') {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                    }
                    else{
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                    }
                    if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
    
                    //mail to customer admins
                    foreach($customer_admin_list['data'] as $kd=>$vd){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$vd['first_name'];
                        $wildcards_replaces['last_name']=$vd['last_name'];
                        $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                        if($module_info[0]['is_workflow'] == 1){
                            if($mail_type=='insert') {
                                $wildcards_replaces['discussion_executed_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_executed_date'] = dateFormat(currentDate());
                                $wildcards_replaces['contract_workflow_module_name']=$module_info[0]['module_name'];
    
                            }else{
                                $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                                $wildcards_replaces['contract_workflow_module_name']=$module_info[0]['module_name'];
    
                            }
                        }
                        else{
                            if($mail_type=='insert') {
                                $wildcards_replaces['discussion_initiated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_initiated_date'] = dateFormat(currentDate());
                                $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
    
                            }else{
                                $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                                $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
    
                            }
    
                        }
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$vd['email'];
                        $to_name=$vd['first_name'].' '.$vd['last_name'];
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$vd['id_user'];
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
                    //mail to bu owner
                    if(isset($bu_owner_info->first_name)){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$bu_owner_info->first_name;
                        $wildcards_replaces['last_name']=$bu_owner_info->last_name;
                        $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                        if($mail_type=='insert') {
                            $wildcards_replaces['discussion_initiated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_initiated_date'] = dateFormat(currentDate());
                        }else{
                            $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                        }
                        $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$bu_owner_info->email;
                        $to_name=$bu_owner_info->first_name.' '.$bu_owner_info->last_name;
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$bu_owner_info->id_user;
                        $mailer_data['mail_subject']=$subject;
                        $mailer_data['mail_message']=$body;
                        $mailer_data['status']=0;
                        $mailer_data['send_date']=currentDate();
                        $mailer_data['is_cron']=0;
                        $mailer_data['email_template_id']=$template_configurations['id_email_template'];
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
                   
                    if(isset($delegate_info) && isset($delegate_info->first_name)){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$delegate_info->first_name;
                        $wildcards_replaces['last_name']=$delegate_info->last_name;
                        $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                        if($mail_type=='insert') {
                            $wildcards_replaces['discussion_initiated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_initiated_date'] = dateFormat(currentDate());
                        }else{
                            $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                        }
                        $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$delegate_info->email;
                        $to_name=$delegate_info->first_name.' '.$delegate_info->last_name;
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$delegate_info->id_user;
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
                }
            }
            else 
            {
                $module_info = $this->Module_model->getModuleName(array('language_id'=>1,'module_id'=>$recent_module_id));
                if($module_info[0]['is_workflow'] == 1){
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>($mail_type=='insert'?'PROJECT_TASK_DISCUSSION_INITIATE':'PROJECT_TASK_DISCUSSION_UPDATE')));
                }
                if($template_configurations_parent['total_records']>0){
                    $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
                    $bu_owner_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
                    $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $discussion_initiated_user_info->customer_id));
                    if(isset($contract_info[0]['delegate_id']) && $contract_info[0]['delegate_id']!=NULL && $contract_info[0]['delegate_id']>0){
                        $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                    }
                    $customer_admin_list=$this->Customer_model->getCustomerAdminList(array('customer_id'=>$discussion_initiated_user_info->customer_id,'user_status'=>1));
                    if($customer_details[0]['company_logo']=='') {
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                    }
                    else{
                        $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                    }
                    if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
    
                    //mail to customer admins
                    foreach($customer_admin_list['data'] as $kd=>$vd){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$vd['first_name'];
                        $wildcards_replaces['last_name']=$vd['last_name'];
                        $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                        if($module_info[0]['is_workflow'] == 1){
                            if($mail_type=='insert') {
                                $wildcards_replaces['discussion_executed_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_executed_date'] = dateFormat(currentDate());
                                $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
    
                            }else{
                                $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                                $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                                $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
    
                            }
                        }
                       
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$vd['email'];
                        $to_name=$vd['first_name'].' '.$vd['last_name'];
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$vd['id_user'];
                        $mailer_data['mail_subject']=$subject;
                        $mailer_data['mail_message']=$body;
                        $mailer_data['status']=0;
                        $mailer_data['send_date']=currentDate();
                        $mailer_data['is_cron']=0;
                        $mailer_data['email_template_id']=$template_configurations['id_email_template'];
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
                    //mail to bu owner
                    if(isset($bu_owner_info->first_name)){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$bu_owner_info->first_name;
                        $wildcards_replaces['last_name']=$bu_owner_info->last_name;
                        $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                        if($mail_type=='insert') {
                            $wildcards_replaces['discussion_initiated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_initiated_date'] = dateFormat(currentDate());
                        }else{
                            $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                        }
                        $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$bu_owner_info->email;
                        $to_name=$bu_owner_info->first_name.' '.$bu_owner_info->last_name;
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$bu_owner_info->id_user;
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
                                $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                        }
                    }
                   
                    if(isset($delegate_info) && isset($delegate_info->first_name)){
                        $mailer_data = array();
                        $template_configurations=$template_configurations_parent['data'][0];
                        $wildcards=$template_configurations['wildcards'];
                        $wildcards_replaces=array();
                        $wildcards_replaces['first_name']=$delegate_info->first_name;
                        $wildcards_replaces['last_name']=$delegate_info->last_name;
                        $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                        if($mail_type=='insert') {
                            $wildcards_replaces['discussion_initiated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_initiated_date'] = dateFormat(currentDate());
                        }else{
                            $wildcards_replaces['discussion_updated_user_name'] = $discussion_initiated_user_info->first_name . ' ' . $discussion_initiated_user_info->last_name . ' (' . $discussion_initiated_user_info->user_role_name . ')';
                            $wildcards_replaces['discussion_updated_date'] = dateFormat(currentDate());
                        }
                        $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
                        $wildcards_replaces['logo']=$customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url']=WEB_BASE_URL.'html';
                        $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                        $from_name=$template_configurations['email_from_name'];
                        $from=$template_configurations['email_from'];
                        $to=$delegate_info->email;
                        $to_name=$delegate_info->first_name.' '.$delegate_info->last_name;
                        $mailer_data['mail_from_name']=$from_name;
                        $mailer_data['mail_to_name']=$to_name;
                        $mailer_data['mail_from']=$from;
                        $mailer_data['mail_to']=$to;
                        $mailer_data['mail_to_user_id']=$delegate_info->id_user;
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
                                $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                        }
                    }
                }
            }
            $result = array('status'=>TRUE, 'message' => $message, 'data'=>($result));
            $this->response($result, REST_Controller::HTTP_OK);
        }

        public function reviewdiscussion_get()
        {
            $data = $this->input->get();
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($data['contract_id'])) {
                $contract_id = $data['contract_id'] = pk_decrypt($data['contract_id']);
                if(!in_array($data['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_contract'])) {
                $contract_id = $data['id_contract'] = pk_decrypt($data['id_contract']);
                if(!in_array($data['id_contract'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_user'])) {
                $data['id_user'] = pk_decrypt($data['id_user']);
                if($data['id_user']!=$this->session_user_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }

            if(isset($data['user_role_id'])) {
                $data['user_role_id'] = pk_decrypt($data['user_role_id']);
                if($data['user_role_id']!=$this->session_user_info->user_role_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_review_id'])) {
                $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
                if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_contract_review'])) {
                $data['id_contract_review'] = pk_decrypt($data['id_contract_review']);
                if(!in_array($data['id_contract_review'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_user'])) {
                $data['contract_user'] = pk_decrypt($data['contract_user']);
                if(!in_array($data['contract_user'],$this->session_user_customer_all_users)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['id_contract_review_discussion'])) {
                $data['id_contract_review_discussion'] = pk_decrypt($data['id_contract_review_discussion']);
                if(!in_array($data['id_contract_review_discussion'],$this->session_user_contract_review_discussions)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'8');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($data['contract_workflow_id'])) {
                $contract_workflow_id = $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
            }
            if(isset($data['id_contract_workflow'])) {
                $contract_workflow_id = $data['id_contract_workflow'] = pk_decrypt($data['id_contract_workflow']);
            }
            $actions_allowed=0;//0 - dont allow ,1- allowed
            $logged_user='other user';
            if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
                $data['contract_user'] = $data['id_user'];
                $actions_allowed=1;
                $logged_user='contributor';
                if(!in_array($data['contract_user'],$this->session_user_contributors)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            else{
                $actions_allowed=0;
            }
       
            if(isset($data['is_workflow']) && $data['is_workflow']==1){
                // if is workflow is 1 then considering the review id of type is_workflow = 1 and of perticular contract workflow id.
                $where = array('contract_id' =>$contract_id,'is_workflow'=>1,'contract_workflow_id'=>$contract_workflow_id);
            }else{
                $where = array('contract_id' =>$contract_id,'is_workflow'=>0);
            }
            $currentReviewId = $this->Contract_model->getCurrentContractReviewId($where);
            $discussion['contract_review_id'] = $currentReviewId[0]['id_contract_review'];
            $discussion['id_user'] = $this->session_user_id;
            $user_modules = $this->Contract_model->getContractReviewModule($discussion);
            $data['module_ids'] = $module_ids = array_map(function ($i) { return strtolower($i['id_module']); }, $user_modules);
    
            if(!isset($data['contract_review_id'])){
                if(isset($contract_workflow_id) && $contract_workflow_id>0)
                    $pending_discussions=$this->Contract_model->getContractDiscussion(array('id_contract'=>$data['contract_id'],'is_workflow'=>1,'contract_workflow_id'=>$contract_workflow_id,'discussion_status'=>1,'module_ids'=>$module_ids,'id'=>1));
                else
                    $pending_discussions=$this->Contract_model->getContractDiscussion(array('id_contract'=>$data['contract_id'],'is_workflow'=>0,'discussion_status'=>1,'module_ids'=>$module_ids,'id'=>2));
                //echo '<pre>'.$this->db->last_query();exit;
                if(isset($pending_discussions[0]['contract_review_id'])){
                    $data['contract_review_id']=$pending_discussions[0]['contract_review_id'];
                }
                else{
                    if(isset($contract_workflow_id) && $contract_workflow_id>0)
                        $recent_reviews=$this->Contract_model->getContractReview(array('contract_id' => $data['contract_id'],'is_workflow'=>1,'contract_workflow_id'=>$contract_workflow_id,'order'=>'DESC'));
                    else
                        $recent_reviews=$this->Contract_model->getContractReview(array('contract_id' => $data['contract_id'],'is_workflow'=>0,'order'=>'DESC'));

                    if(isset($recent_reviews[0]['id_contract_review'])){
                        $data['contract_review_id']=$recent_reviews[0]['id_contract_review'];
                    }
                }
            }
            if(isset($data['id_contract_review_discussion'])){
                $pending_discussions=$this->Contract_model->getContractDiscussion(array('id_contract_review_discussion'=>$data['id_contract_review_discussion']));
                if(isset($pending_discussions[0]['contract_review_id'])){
                    $data['contract_review_id']=$pending_discussions[0]['contract_review_id'];
                }
            }
            $idaadi=$this->Contract_model->checkContributorForContractReview(array('contract_review_id'=>$data['contract_review_id'],'id_user'=>$data['id_user']));
            if($idaadi===true){
                $data['contract_user'] = $data['id_user'];
                $actions_allowed=1;
                $logged_user='contributor';
            }
            $data['contribution_type']  = $this->session_user_info->contribution_type;
            $review_information=$this->Contract_model->getContractReview(array('id_contract_review'=>$data['contract_review_id']));
            $result = $this->Contract_model->getContractReviewDisucussionData($data);
       
            $result_parsed=array();
            $contract_review_discussion_id=NULL;
            foreach($result as $k=>$v){
                if($v['status']==1){
                    $result_parsed[$v['id_module']]['module_name']=$v['module_name'];
                    $result_parsed[$v['id_module']]['id_module']=$v['id_module'];
                    $result_parsed[$v['id_module']]['is_auto_close']=$v['is_auto_close'];
        
                    $result_parsed[$v['id_module']]['topics'][$v['id_topic']]['topic_name']=$v['topic_name'];
                    $result_parsed[$v['id_module']]['topics'][$v['id_topic']]['id_topic']=$v['id_topic'];
                    $result_parsed[$v['id_module']]['topics'][$v['id_topic']]['questions'][]=$v;
                    //if($v['id_contract_review_discussion']!='' || $v['id_contract_review_discussion']!=NULL)
        
                    if($v['id_contract_review_discussion']!='' || $v['id_contract_review_discussion']!=NULL) {
                        $contract_review_discussion_id=$v['id_contract_review_discussion'];
                        $result_parsed[$v['id_module']]['id_contract_review_discussion']=$contract_review_discussion_id;
                        $result_parsed[$v['id_module']]['discussion_created_by'] = $v['discussion_created_by'];
                        $result_parsed[$v['id_module']]['discussion_created_on'] = $v['discussion_created_on'];
                        $result_parsed[$v['id_module']]['discussion_closed_by'] = $v['discussion_closed_by'];
                        $result_parsed[$v['id_module']]['discussion_closed_on'] = $v['discussion_closed_on'];
                        $result_parsed[$v['id_module']]['discussion_status'] = $v['discussion_status'];
                        $result_parsed[$v['id_module']]['diaaid']='annus';
                        $result_parsed[$v['id_module']]['dcmaamcd']='annus';
                        $result_parsed[$v['id_module']]['dcaacd']='annus';
                        $result_parsed[$v['id_module']]['dclaalcd']='annus';
                        $result_parsed[$v['id_module']]['dsaasd']='annus';
                        if(isset($review_information[0]['contract_review_status']) && ($review_information[0]['contract_review_status']=='review in progress' || $review_information[0]['contract_review_status']=='workflow in progress')) {
                            $result_parsed[$v['id_module']]['diaaid'] = 'annus';
                            if ($v['discussion_status'] == 1) {
                                if ($logged_user == 'contributor') {
                                    $result_parsed[$v['id_module']]['dcaacd']='itako';
                                }
                                $result_parsed[$v['id_module']]['dcmaamcd'] = 'itako';
                                $result_parsed[$v['id_module']]['dclaalcd'] = 'itako';
                                $result_parsed[$v['id_module']]['dsaasd']='itako';
                            } else {
                                $result_parsed[$v['id_module']]['dcmaamcd'] = 'annus';
                                $result_parsed[$v['id_module']]['dclaalcd'] = 'annus';
                            }
                        }
        
        
                    }
    
                    if(!isset($result_parsed[$v['id_module']]['id_contract_review_discussion'])) {
                        $result_parsed[$v['id_module']]['id_contract_review_discussion']=NULL;
                        $result_parsed[$v['id_module']]['discussion_created_by'] = NULL;
                        $result_parsed[$v['id_module']]['discussion_created_on'] = NULL;
                        $result_parsed[$v['id_module']]['discussion_closed_by'] = NULL;
                        $result_parsed[$v['id_module']]['discussion_closed_on'] = NULL;
                        $result_parsed[$v['id_module']]['discussion_status'] = NULL;
                        $result_parsed[$v['id_module']]['diaaid']='annus';
                        $result_parsed[$v['id_module']]['dcaacd']='annus';
                        $result_parsed[$v['id_module']]['dcmaamcd']='annus';
                        $result_parsed[$v['id_module']]['dclaalcd']='annus';
                        $result_parsed[$v['id_module']]['dsaasd']='annus';
                        if(isset($review_information[0]['contract_review_status']) && ($review_information[0]['contract_review_status']=='review in progress' || $review_information[0]['contract_review_status']=='workflow in progress')) {
                            if ($logged_user == 'contributor') {
                                $result_parsed[$v['id_module']]['diaaid'] = 'itako';
                                $result_parsed[$v['id_module']]['dcmaamcd'] = 'itako';
                                $result_parsed[$v['id_module']]['dcaacd'] = 'itako';
                            }
                            else
                                $result_parsed[$v['id_module']]['diaaid'] = 'annus';
        
                        }
                    }
                }            
            }
            $result=array_values($result_parsed);
    
            foreach($result as $k=>$v){
                $result[$k]['id_contract_review_discussion']=pk_encrypt($result[$k]['id_contract_review_discussion']);
                $result[$k]['id_module']=pk_encrypt($result[$k]['id_module']);
                foreach($result[$k]['topics'] as $kt=>$vt){
                    $result[$k]['topics'][$kt]['id_topic']=pk_encrypt($result[$k]['topics'][$kt]['id_topic']);
                    foreach($result[$k]['topics'][$kt]['questions'] as $kq=>$vq){
                        $cqr = $this->Contract_model->getSecondOpenion($result[$k]['topics'][$kt]['questions'][$kq]['id_question']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['question_answer']=$cqr['question_answer'];
                        $result[$k]['topics'][$kt]['questions'][$kq]['second_opinion']=$cqr['second_opinion'];
                        $result[$k]['topics'][$kt]['questions'][$kq]['contract_review_id']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['contract_review_id']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['id_contract_review_discussion']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['id_contract_review_discussion']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['id_contract_review_discussion_question']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['id_contract_review_discussion_question']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['id_module']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['id_module']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['id_question']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['id_question']);
                        $result[$k]['topics'][$kt]['questions'][$kq]['id_topic']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['id_topic']);
                        foreach($result[$k]['topics'][$kt]['questions'][$kq]['change_log'] as $kc=>$vc){
                            $result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['contract_review_discussion_question_id']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['contract_review_discussion_question_id']);
                            $result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['created_by']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['created_by']);
                            $result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['id_contract_review_discussion_question_log']=pk_encrypt($result[$k]['topics'][$kt]['questions'][$kq]['change_log'][$kc]['id_contract_review_discussion_question_log']);
                        }
                    }
                }
            }
            foreach($review_information as $kr=>$vr){
                $review_information[$kr]['contract_id']=pk_encrypt($review_information[$kr]['contract_id']);
                $review_information[$kr]['created_by']=pk_encrypt($review_information[$kr]['created_by']);
                $review_information[$kr]['id_contract_review']=pk_encrypt($review_information[$kr]['id_contract_review']);
                $review_information[$kr]['relationship_category_id']=pk_encrypt($review_information[$kr]['relationship_category_id']);
                $review_information[$kr]['updated_by']=pk_encrypt($review_information[$kr]['updated_by']);
            }

            $is_discussion_exist=(count($this->Contract_model->getContractReviewDiscussionModuleCount(array('id_contract_review'=>$data['contract_review_id'],'discussion_status'=>1)))>0)?"itako":'annus';
            $existing_discussions=$this->Contract_model->getContractDiscussion(array('id_contract'=>$data['contract_id'],'discussion_status'=>2,'contract_review_status'=>'finished','module_ids'=>$module_ids,'id'=>3));
            foreach($existing_discussions as $ke=>$ve){
                $existing_discussions[$ke]['id_contract_review_discussion']=pk_encrypt($existing_discussions[$ke]['id_contract_review_discussion']);
                $existing_discussions[$ke]['contract_review_id']=pk_encrypt($existing_discussions[$ke]['contract_review_id']);
                $existing_discussions[$ke]['module_id']=pk_encrypt($existing_discussions[$ke]['module_id']);
                $existing_discussions[$ke]['created_by']=pk_encrypt($existing_discussions[$ke]['created_by']);
                $existing_discussions[$ke]['updated_by']=pk_encrypt($existing_discussions[$ke]['updated_by']);
            }
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('contract_review_discussion_id'=>pk_encrypt($contract_review_discussion_id),'contract_review_id'=>pk_encrypt($data['contract_review_id']),'closed_discussions'=>$existing_discussions,'review_discussion'=>$result,'actions_allowed'=>$actions_allowed,'ideedi'=>$is_discussion_exist,'idieeidi'=>($contract_review_discussion_id!=NULL && $contract_review_discussion_id>0)?"itako":'annus','review_information'=>$review_information));
            $this->response($result, REST_Controller::HTTP_OK);
    }

    public function reviewdiscussionclose_post(){
        // updateContractReviewDiscussion
         $data = $this->input->post();
         $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
         $this->form_validator->add_rules('module_id', array('required'=>$this->lang->line('module_id_req')));
         $this->form_validator->add_rules('contract_review_id', array('required'=>$this->lang->line('contract_review_id_req')));
         $this->form_validator->add_rules('contract_review_discussion_id', array('required'=>$this->lang->line('contract_review_discussion_id_req')));
         $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
         if(empty($data)){
             $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
             $this->response($result, REST_Controller::HTTP_OK);
         }
         $validated = $this->form_validator->validate($data);
         if($validated != 1)
         {
             $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
             $this->response($result, REST_Controller::HTTP_OK);
         }
         if(isset($data['contract_id'])) {
             $data['contract_id'] = pk_decrypt($data['contract_id']);
             if(!in_array($data['contract_id'],$this->session_user_contracts)){
                 $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                 $this->response($result, REST_Controller::HTTP_OK);
             }
         }
         if(isset($data['module_id'])) {
             $data['module_id'] = pk_decrypt($data['module_id']);
             if(!in_array($data['module_id'],$this->session_user_contract_review_modules)){
                 $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                 $this->response($result, REST_Controller::HTTP_OK);
             }
         }
         if(isset($data['contract_review_id'])) {
             $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
             if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                 $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                 $this->response($result, REST_Controller::HTTP_OK);
             }
         }
         if(isset($data['created_by'])) {
             $data['created_by'] = pk_decrypt($data['created_by']);
             if($data['created_by']!=$this->session_user_id){
                 $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                 $this->response($result, REST_Controller::HTTP_OK);
             }
         }
         //
         $send['contract_review_id'] = $data['contract_review_id'];
         $module_id = $send['module_id'] = $data['module_id'];
         $module_progress = $this->Contract_model->progress($send);
         
         //Checking the validtors on the module
         $q = 'SELECT * from contract_user cu JOIN user u on u.id_user = cu.user_id WHERE cu.module_id ='.$module_id.' AND u.contribution_type = 1 AND cu.status = 1';
         $validators_on_module = $this->User_model->custom_query($q);
         $module_info = $this->Module_model->getModuleName(array('language_id'=>1,'module_id'=>$module_id));
         if($module_progress == 100 && count($validators_on_module) > 0 && (int)$module_info[0]['module_status'] == 1){
             //update module to ready for validation if module progress is 100 %
             $this->User_model->update_data('module',array('module_status'=>2),array('id_module'=>$module_id));
         }
 
         $modules=explode(',',$data['contract_review_discussion_id']);
         foreach ($modules as $k=>$v) {
             $v=pk_decrypt($v);
             if($v!=''){
                 if(!in_array($v,$this->session_user_contract_review_discussions)){
                     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                     $this->response($result, REST_Controller::HTTP_OK);
                 }
                 $this->Contract_model->updateContractReviewDiscussion(array('id_contract_review_discussion'=>$v,'updated_by'=>$data['created_by'],'updated_on'=>currentDate(),'discussion_status'=>2));
                 $discussion_initiated_user_info = $this->User_model->getUserInfo(array('user_id' => $data['created_by']));
                 $module_info = $this->Module_model->getModuleName(array('language_id'=>1,'module_id'=>$data['module_id']));
                 if($data['type']=='contract'){
                    if($module_info[0]['is_workflow'] == 1){
                        $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>'CONTRACT_WORKFLOW_DISCUSSION_CLOSE'));
                    }else{
                        $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>'CONTRACT_REVIEW_DISCUSSION_CLOSE'));
                    }
                    if($template_configurations_parent['total_records']>0){
                        $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
                        $bu_owner_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
                        $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $discussion_initiated_user_info->customer_id));
                        if(isset($contract_info[0]['delegate_id']) && $contract_info[0]['delegate_id']!=NULL && $contract_info[0]['delegate_id']>0){
                            $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                        }
                        $customer_admin_list=$this->Customer_model->getCustomerAdminList(array('customer_id'=>$discussion_initiated_user_info->customer_id,'user_status'=>1));
                        if($customer_details[0]['company_logo']=='') {
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                        }
                        else{
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                        }
                        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
    
                        //mail to customer admins
                        foreach($customer_admin_list['data'] as $kd=>$vd){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$vd['first_name'];
                            $wildcards_replaces['last_name']=$vd['last_name'];
                            $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['contract_workflow_module_name']=$module_info[0]['module_name'];
                            }
                            else{
                                $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to=$vd['email'];
                            $to_name=$vd['first_name'].' '.$vd['last_name'];
                            $mailer_data['mail_from_name']=$from_name;
                            $mailer_data['mail_to_name']=$to_name;
                            $mailer_data['mail_to_user_id']=$vd['id_user'];
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
                                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                            }
                        }
                        //mail to bu owner
                        if(isset($bu_owner_info->first_name)){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$bu_owner_info->first_name;
                            $wildcards_replaces['last_name']=$bu_owner_info->last_name;
                            $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['contract_workflow_module_name']=$module_info[0]['module_name'];
                            }
                            else{
                                $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to=$bu_owner_info->email;
                            $to_name=$bu_owner_info->first_name.' '.$bu_owner_info->last_name;
                            $mailer_data['mail_from_name']=$from_name;
                            $mailer_data['mail_to_name']=$to_name;
                            $mailer_data['mail_to_user_id']=$bu_owner_info->id_user;
                            $mailer_data['mail_from']=$from;
                            $mailer_data['mail_to']=$to;
                            $mailer_data['mail_subject']=$subject;
                            $mailer_data['mail_message']=$body;
                            $mailer_data['status']=0;
                            $mailer_data['send_date']=currentDate();
                            $mailer_data['is_cron']=0;
                            $mailer_data['email_template_id']=$template_configurations['id_email_template'];
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
                        //mail to delegate
                        if(isset($delegate_info) && isset($delegate_info->first_name)){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$delegate_info->first_name;
                            $wildcards_replaces['last_name']=$delegate_info->last_name;
                            $wildcards_replaces['contract_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['contract_workflow_module_name']=$module_info[0]['module_name'];
                            }
                            else{
                                $wildcards_replaces['contract_review_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
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
                    }
                 }
                 else{
                    $template_configurations_parent=$this->Customer_model->EmailTemplateList(array('customer_id' => $discussion_initiated_user_info->customer_id,'module_key'=>'PROJECT_TASK_DISCUSSION_CLOSE'));
                    if($template_configurations_parent['total_records']>0){
                        $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $data['contract_id']));
                        $bu_owner_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['contract_owner_id'],'user_status'=>1));
                        $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $discussion_initiated_user_info->customer_id));
                        if(isset($contract_info[0]['delegate_id']) && $contract_info[0]['delegate_id']!=NULL && $contract_info[0]['delegate_id']>0){
                            $delegate_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['delegate_id'],'user_status'=>1));
                        }
                        $customer_admin_list=$this->Customer_model->getCustomerAdminList(array('customer_id'=>$discussion_initiated_user_info->customer_id,'user_status'=>1));
                        if($customer_details[0]['company_logo']=='') {
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                        }
                        else{
                            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                        }
                        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
    
                        //mail to customer admins
                        foreach($customer_admin_list['data'] as $kd=>$vd){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$vd['first_name'];
                            $wildcards_replaces['last_name']=$vd['last_name'];
                            $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to=$vd['email'];
                            $to_name=$vd['first_name'].' '.$vd['last_name'];
                            $mailer_data['mail_from_name']=$from_name;
                            $mailer_data['mail_to_name']=$to_name;
                            $mailer_data['mail_to_user_id']=$vd['id_user'];
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
                                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                            }
                        }
                        //mail to bu owner
                        if(isset($bu_owner_info->first_name)){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$bu_owner_info->first_name;
                            $wildcards_replaces['last_name']=$bu_owner_info->last_name;
                            $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                            $from_name=$template_configurations['email_from_name'];
                            $from=$template_configurations['email_from'];
                            $to=$bu_owner_info->email;
                            $to_name=$bu_owner_info->first_name.' '.$bu_owner_info->last_name;
                            $mailer_data['mail_from_name']=$from_name;
                            $mailer_data['mail_to_name']=$to_name;
                            $mailer_data['mail_to_user_id']=$bu_owner_info->id_user;
                            $mailer_data['mail_from']=$from;
                            $mailer_data['mail_to']=$to;
                            $mailer_data['mail_subject']=$subject;
                            $mailer_data['mail_message']=$body;
                            $mailer_data['status']=0;
                            $mailer_data['send_date']=currentDate();
                            $mailer_data['is_cron']=0;
                            $mailer_data['email_template_id']=$template_configurations['id_email_template'];
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
                        //mail to delegate
                        if(isset($delegate_info) && isset($delegate_info->first_name)){
                            $template_configurations=$template_configurations_parent['data'][0];
                            $wildcards=$template_configurations['wildcards'];
                            $wildcards_replaces=array();
                            $wildcards_replaces['first_name']=$delegate_info->first_name;
                            $wildcards_replaces['last_name']=$delegate_info->last_name;
                            $wildcards_replaces['project_name']=$contract_info[0]['contract_name'];
                            $wildcards_replaces['discussion_closed_user_name']=$discussion_initiated_user_info->first_name.' '.$discussion_initiated_user_info->last_name.' ('.$discussion_initiated_user_info->user_role_name.')';
                            $wildcards_replaces['discussion_closed_date']=dateFormat(currentDate());
                            if($module_info[0]['is_workflow'] == 1){
                                $wildcards_replaces['project_task_module_name']=$module_info[0]['module_name'];
                            }
                            $wildcards_replaces['logo']=$customer_logo;
                            $wildcards_replaces['year'] = date("Y");
                            $wildcards_replaces['url']=WEB_BASE_URL.'html';
                            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
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
                    }
                 }
                
             }
         }
         $result = array('status'=>TRUE, 'message' => $this->lang->line('review_discussion_close_success'), 'data'=>array('recent_module_id'=>pk_encrypt($data['module_id'])));
         $this->response($result, REST_Controller::HTTP_OK);
     }
     function projectdashboardexport_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        $this->form_validator->add_rules('contract_review_id', array('required'=>$this->lang->line('contract_review_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // if($this->session_user_info->customer_id==4){
        //     $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'));
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if($this->session_user_info->user_role_id!=7)
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_contract_review'])) {
            $data['id_contract_review'] = pk_decrypt($data['id_contract_review']);
            if(!in_array($data['id_contract_review'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
            if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_workflow_id'])) {
            $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
            }
        // $data['export_type']='trends';
       
        $result_array = array();
        $data['order'] = 'DESC';
        $contributor_modules=array();
        $contribution_type = array('expert','validator','provider');

        if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
            $data['contract_user'] = $data['id_user'];
            if(!in_array($data['contract_user'],$this->session_user_contributors)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            $contributor_modules=$this->Contract_model->getContractContributors(array('user_id'=>$data['contract_user']));
            //echo "<pre>";print_r($contributor_modules);echo "</pre>";
            $contributor_modules_array = array();
            foreach($contribution_type as $v){
                //echo '<pre>'.print_r($v);exit;
                $contributor_modules_array = array_merge($contributor_modules_array,array_map(function($i){ return ($i['module_id']); },$contributor_modules[$v]['data']));
            }
            $contributor_modules = $contributor_modules_array;  
        }
        else{
            $contributor_modules=$this->Contract_model->getContractContributors(array('user_id'=>$data['id_user']));
            //echo "<pre>";print_r($contributor_modules);echo "</pre>";
            $contributor_modules_array = array();
            foreach($contribution_type as $v){
                //echo '<pre>'.print_r($v);exit;
                $contributor_modules_array = array_merge($contributor_modules_array,array_map(function($i){ return ($i['module_id']); },$contributor_modules[$v]['data']));
            }
            $contributor_modules = $contributor_modules_array;
        }

            $reviews = $this->Contract_model->getContractReview($data);//echo $this->db->last_query();exit;
            // print_r($reviews);exit;
            for($s=0;$s<count($reviews);$s++)
            {
                if(!in_array($reviews[$s]['business_unit_id'],$this->session_user_own_business_units)){
                    if($this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$reviews[$s]['id_contract_review'],'id_user'=>$this->session_user_id))>0){

                    }
                    else{
                        unset($reviews[$s]);
                    }
                }
            }
           
            $reviews=array_values($reviews);            //print_r($reviews);exit;
            $contract_review_id = array_map(function($i){ return $i['id_contract_review']; },$reviews);//print_r($contract_review_id);exit;
            $data['contract_review_id']=(isset($data['contract_review_id']) && $data['contract_review_id']>0)?$data['contract_review_id']:(isset($reviews[0]['id_contract_review'])?$reviews[0]['id_contract_review']:0);
            $module_data=array();
            if(isset($data['contract_review_id'])){
                $index = array_search($data['contract_review_id'],$contract_review_id);
                for($s=0;$s<count($reviews);$s++)
                {
                    if($reviews[$s]['id_contract_review']==$data['contract_review_id']){
                            $result_array['review_date'] = ($reviews[$s]['updated_date']!='')?date('Y-m-d',strtotime($reviews[$s]['updated_date'])):'';
                            $result_array['review_status'] = ($reviews[$s]['contract_review_status']=='finished')?'review finalized':$reviews[$s]['contract_review_status'];
                    }
                }
           
                if($data['contract_review_id']>0){
                    // print_r($this->session_user_info);
                    if((int)$this->session_user_info->user_role_id == 7)
                        $module_data =  $this->Contract_model->getContractDashboard(array('contract_review_id' => $data['contract_review_id'],'provider_visibility'=>array(1)));
                    else
                        $module_data =  $this->Contract_model->getContractDashboard(array('contract_review_id' => $data['contract_review_id'],'provider_visibility'=>array(1,0)));
                    $contributor_modules=$this->Contract_model->getContractContributors(array('user_id'=>$data['id_user'],'contract_review_id' => $data['contract_review_id']));
                    //echo '<pre>';print_r($module_data);exit;
                    $contributor_modules_array = array();
                    foreach($contribution_type as $v){
                        //echo '<pre>'.print_r($v);exit;
                        $contributor_modules_array = array_merge($contributor_modules_array,array_map(function($i){ return ($i['module_id']); },$contributor_modules[$v]['data']));
                    }
                    $contributor_modules = $contributor_modules_array;
                }
            }
            else{
                $result_array['review_date'] = ($reviews[0]['updated_on']!='')?date('Y-m-d',strtotime($reviews[0]['updated_on'])):'';
                $result_array['review_status'] = $reviews[0]['contract_review_status'];
                //$result_array['next'] = 0;
                //$result_array['prev'] = isset($reviews[1])?$reviews[1]['id_contract_review']:0;
                if($data['contract_review_id']>0) {
                    if((int)$this->session_user_info->user_role_id == 7)
                        $module_data = $this->Contract_model->getContractDashboard(array('contract_review_id' => $reviews[0]['id_contract_review'],'provider_visibility'=>array(1)));
                    else
                        $module_data = $this->Contract_model->getContractDashboard(array('contract_review_id' => $reviews[0]['id_contract_review'],'provider_visibility'=>array(1,0)));
                    $contributor_modules=$this->Contract_model->getContractContributors(array('user_id'=>$data['id_user'],'contract_review_id' => $data['contract_review_id']));
                    //echo '<pre>'.$this->db->last_query();exit;
                    $contributor_modules_array = array();
                    foreach($contribution_type as $v){
                        //echo '<pre>'.print_r($v);exit;
                        $contributor_modules_array = array_merge($contributor_modules_array,array_map(function($i){ return ($i['module_id']); },$contributor_modules[$v]['data']));
                    }
                    $contributor_modules = $contributor_modules_array;
                }
            }
            $result_array['modules'] = array();
            $contract_reviewDetails = $this->User_model->check_record('contract_review',array('id_contract_review'=>$data['contract_review_id']));
            $taskDetails = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$contract_reviewDetails[0]['contract_workflow_id']));
            $result_array['taskDetails'] = $taskDetails;
           // print_r($result_array['taskDetails']);exit;
            $subtask =array();
            $result_array['contract'] = $this->Contract_model->getContractDetails(array('id_contract'=>$data['contract_id']))[0];
            if(((in_array($this->session_user_info->id_user,array($result[$s]['delegate_id'],$result[$s]['contract_owner_id'])))||(in_array($this->session_user_info->user_role_id,array(2)))))
            {
                if(!empty($contract_reviewDetails))
                {
                    $subtask = $this->User_model->check_record('contract_workflow',array('parent_id'=>$contract_reviewDetails[0]['contract_workflow_id']));
                }
            }
            $result_array['sub_task'] = $subtask;
            for($s=0;$s<count($module_data);$s++)
            {
                if(count($contributor_modules)==0 || (count($contributor_modules)>0 && in_array($module_data[$s]['module_id'],$contributor_modules))) {
                    $validator =0;
                    if(!empty($module_data[$s]['module_id']))
                    {
                        
                        $validator_modules = $this->Contract_model->getValidatormodules(array('module_id'=>$module_data[$s]['module_id'],'contribution_type'=>1));
                        if(count($validator_modules) > 0){
                            $validator = 1;
                        }
                    }
                    if((int)$this->session_user_info->user_role_id == 7)
                        $questions = $this->Contract_model->getTopicData(array('id_topic'=>$module_data[$s]['topic_id'],'provider_visibility'=>1));
                    else
                        $questions = $this->Contract_model->getTopicData(array('id_topic'=>$module_data[$s]['topic_id']));
                        foreach($questions['questions'] as $k => $v){
                            foreach($questions['questions'][$k]['attachments'] as $key =>$val){

                                $questions['questions'][$k]['attachments'][$key]['document_source'] =($questions['questions'][$k]['attachments'][$key]['document_source']);
                                $questions['questions'][$k]['attachments'][$key]['id_document'] =pk_encrypt($questions['questions'][$k]['attachments'][$key]['id_document']);
                                $questions['questions'][$k]['attachments'][$key]['module_id'] =pk_encrypt($questions['questions'][$k]['attachments'][$key]['module_id']);
                                $questions['questions'][$k]['attachments'][$key]['reference_id'] =pk_encrypt($questions['questions'][$k]['attachments'][$key]['reference_id']);
                            }
                            foreach($subtask as $ke =>$va)
                            {
                               
                                $questions['questions'][$k][$va['workflow_name']]=array(
                                    'id_question'=>'',
                                    'question_answer'=>'',
                                    'question_option_answer'=>'',
                                    'question_type'=>''
                                );;
                                $subTask_contract_review_details =$this->User_model->check_record('contract_review',array('contract_workflow_id'=>$va['id_contract_workflow']));
                                if(!empty($subTask_contract_review_details[0]))
                                {
                                    $subTask_contract_review_id = $subTask_contract_review_details[0]['id_contract_review'];
                                    $subtask_module_data =array();
                                    $subtask_module_data =  $this->Contract_model->getContractDashboard(array('contract_review_id' => $subTask_contract_review_id,'provider_visibility'=>array(1,0)));
                                    $subtaskquestions = $this->Contract_model->getTopicData(array('id_topic'=>$subtask_module_data[$s]['topic_id']));
                                    if($subtaskquestions['questions'][$k]['provider_visibility']==1)
                                    {
                                        $questions['questions'][$k][$va['workflow_name']]=
                                        array(
                                            'id_question'=>pk_encrypt($subtaskquestions['questions'][$k]['id_question']),
                                            'question_answer'=>$subtaskquestions['questions'][$k]['question_answer'],
                                            'question_option_answer'=>$subtaskquestions['questions'][$k]['question_option_answer'],
                                            'question_type'=>$subtaskquestions['questions'][$k]['question_type']
                                        );
                                    }
                                }

                            }
                           $questions['questions'][$k]['id_question']=pk_encrypt($questions['questions'][$k]['id_question']);
                        }
                    $result_array['modules'][$module_data[$s]['module_id']]['module_id'] = pk_encrypt($module_data[$s]['module_id']);
                    $result_array['modules'][$module_data[$s]['module_id']]['validator'] = $validator;
                    if(!(int)$module_data[$s]['module_status'])
                        $module_data[$s]['module_name'] = $module_data[$s]['module_name'].' (Stored)';
                    $result_array['modules'][$module_data[$s]['module_id']]['module_name'] = $module_data[$s]['module_name'];
                    $result_array['modules'][$module_data[$s]['module_id']]['topics'][] = array(
                        'topic_id' => pk_encrypt($module_data[$s]['topic_id']),
                        'topic_name' => $module_data[$s]['topic_name'],
                        'topic_score' => $module_data[$s]['topic_score'],
                        'questions' => $questions['questions']
                    );
                }
            }

            $result_array['modules'] = array_values($result_array['modules']);
            //echo '<pre>'.print_r($result_array['modules']);exit;
           
            //echo "<pre>";print_r($result_array['modules']);echo "</pre>";

            for($s=0;$s<count($result_array['modules']);$s++) //getting score for module by topics score // getScore is a helper function
            {
                $result_array['modules'][$s]['module_score'] = getScore($score = array_map(function($i){ return strtolower($i['topic_score']); },$result_array['modules'][$s]['topics']));
            }
            //$result_array['next'] = pk_encrypt(isset($result_array['next'])?$result_array['next']:NULL);
            //$result_array['prev'] = pk_encrypt(isset($result_array['prev'])?$result_array['prev']:NULL);
            $result_array['review_score'] = getScore($score = array_map(function($i){ return strtolower($i['module_score']); },$result_array['modules']));
            $result_array['contract_review_id']=pk_encrypt($data['contract_review_id']);
            $result_array['contract_workflow_id']=$data['contract_workflow_id'];
            $result_array['is_workflow']=$data['is_workflow'];
           // echo '<pre>'.print_r($result_array);exit;
            $result_array['only_module'] = isset($data['only_module'])?$data['only_module']:true;
        $this->exportprojectdashboard($result_array);
    }
    public function exportprojectdashboard($data)
    {
        $this->load->library('excel');
        //activate worksheet number 1
        $excelRowstartsfrom=1;
        $excelColumnstartsFrom=0;
        $columnBegin =$excelColumnstartsFrom;
        $excelstartsfrom=$excelRowstartsfrom;
        $question_count = 0;
       
        foreach($data['modules'] as $k => $v){
            foreach($data['modules'][$k]['topics'] as $m => $n){
                $question_count = $question_count + count($n['questions']);
            }
        }
        //echo 'question count'.$question_count;exit;
        //Heading Starts
        $countofcol = 16 +count($data['sub_task']);
        $head = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+$countofcol) . $excelstartsfrom;
        $body = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+$countofcol) . ($excelstartsfrom+$question_count);
       
        //echo 'body'.$body;exit;
        $this->excel->getActiveSheet()->getStyle($body)->applyFromArray(
            array('borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font'  => array(
                    'bold'  => false
                )
            )
        );
        $this->excel->getActiveSheet()->getStyle($head)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+1))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+2))->setWidth(20);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+3))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+4))->setWidth(20);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+5))->setWidth(20);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+6))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+7))->setWidth(45);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+8))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+9))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+10))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+11))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+12))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+13))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+14))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+15))->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+16))->setWidth(15);
        for ($i=1; $i <=count($data['sub_task']) ; $i++) {
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+16+$i))->setWidth(35);
        }
       
        $this->excel->getActiveSheet()->getStyle($head)->getFill()->getStartColor()->setARGB('D1D1D1d1');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,'Project Name');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,'Project ID');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,'Project Owner');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,'Project Delegate');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,'Project Start Date');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,'Project End Date');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,'Status');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,'Module Name');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,'Topic Name');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,'Question');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,'Answer');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,'Score');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,'Validator Opinion');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,'Score');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,'Your Feedback');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,'Validator Feedback');
        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+16) . $excelstartsfrom,'Discussion');
        for ($i=1; $i <=count($data['sub_task']) ; $i++) {
           //echo $data['sub_task'][$i-1]['workflow_name'];exit;
            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+16+$i) . $excelstartsfrom,$data['sub_task'][$i-1]['workflow_name']);
        }

       
        //Heading Ends
       
        //print_r($data);exit;
            $workflowReviews=$this->User_model->check_record('contract_review',array('id_contract_review'=>pk_decrypt($data['contract_review_id'])));// echo $this->db->last_query();exit;
            foreach($data['modules'] as $k => $v){
                foreach($data['modules'][$k]['topics'] as $m => $n){
                    foreach($data['modules'][$k]['topics'][$m]['questions'] as $o => $p){
                       
                        $excelstartsfrom++;
                        ///answer RAG Calculation
                        $ans = $p['question_option_answer'];
                        if($p['question_option_answer']=='R') $ans = 'Red';
                        else if($p['question_option_answer']=='A') $ans = 'Amber';
                        else if($p['question_option_answer']=='G') $ans = 'Green';
                        else if($p['question_type']=='date') $ans =!empty($p['question_answer'])?date("d-m-Y",strtotime($p['question_answer'])):'';
                        else $ans = $p['question_option_answer'];
                        ///v_answer RAG Calculation
                        $ans3 = $p['v_question_option_answer'];
                        if($p['v_question_option_answer']=='R') $ans3 = 'Red';
                        else if($p['v_question_option_answer']=='A') $ans3 = 'Amber';
                        else if($p['v_question_option_answer']=='G') $ans3 = 'Green';
                        else if($p['question_type']=='date') $ans3 =!empty($p['v_question_answer'])?date("d-m-Y",strtotime($p['v_question_answer'])):'';
                        else $ans3 = $p['v_question_option_answer'];
                        if(empty($ans3)){ $ans3 ="---";}
                        // if(isset($v['validator'])&&$v['validator'] == 0)
                        // {
                        //     $ans3 ="---";
                        // }
                        ///second opinion RAG Calculation
                        $ans2 = $p['second_opinion'];
                        if($p['second_opinion']=='R') $ans2 = 'Red';
                        else if($p['second_opinion']=='A') $ans2 = 'Amber';
                        else if($p['second_opinion']=='G') $ans2 = 'Green';
                        else $ans2 = $p['second_opinion'];
                        if($p['provider_visibility'] == 1)
                        {
                            $colourCell = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey(16+count($data['sub_task'])) . ($excelstartsfrom);
                            $this->excel->getActiveSheet()->getStyle($colourCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('a9d18e');
                        }    
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$data['contract']['contract_name']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,$data['contract']['contract_unique_id']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,$data['contract']['responsible_user_name']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,$data['contract']['delegate_user_name']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,date_format(date_create($data['contract']['contract_start_date']),"M d, Y"));
                        $dataval="";
                        if(!empty($data['contract']['contract_end_date']))
                        {
                            $dataval = date_format(date_create($data['contract']['contract_end_date']),"M d, Y");
                        }
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,$dataval);
                   
                        if(isset($data['is_workflow']) && $data['is_workflow']==1){
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,ucwords(str_replace('workflow','task',$workflowReviews[0]['contract_review_status'])));
                        }
                        else{
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,$data['review_status']);
                        }
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,$v['module_name']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,$n['topic_name']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,$p['question_text']);
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,$ans);//11
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,questionScore($p['option_value'],$p['question_type']));
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,$ans3);//12
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,questionScore($p['v_option_value'],$p['question_type']));
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,$p['question_feedback']);//13
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,$p['v_question_feedback']);//14
                        $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+16) . $excelstartsfrom,$ans2);//15
                        for ($i=1; $i <=count($data['sub_task']) ; $i++) {
                           // echo"123";
                           $subTaskAnswer =array();
                            $subTaskAnswer = $p[$data['sub_task'][$i-1]['workflow_name']];
                             $subtaskans = $subTaskAnswer['question_option_answer'];
                             if($subTaskAnswer['question_option_answer']=='R') $subtaskans = 'Red';
                             else if($subTaskAnswer['question_option_answer']=='A') $subtaskans = 'Amber';
                             else if($subTaskAnswer['question_option_answer']=='G') $subtaskans = 'Green';
                             else if($subTaskAnswer['question_type']=='date') $subtaskans =!empty($subTaskAnswer['question_answer'])?date("d-m-Y",strtotime($subTaskAnswer['question_answer'])):'';
                             else $subtaskans = $subTaskAnswer['question_option_answer'];
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin+16+$i) . $excelstartsfrom,
                            $subtaskans);
                        }
                       
                    }
                }
            }
       
       
        if(isset($data['is_workflow']) && $data['is_workflow']==1){
            $this->excel->getActiveSheet()->setTitle('WORKFLOW DASHBOARD');
        }
        else{
            $this->excel->getActiveSheet()->setTitle('REVIEW DASHBOARD');
        }
       
        if($data_trends['export_type']!=='' && $data_trends['export_type']=='trends'){
            $filename = preg_replace("/[^a-z0-9\_\-\.]/i", '',$data['taskDetails'][0]['workflow_name']).'_'.date("d-m-Y",strtotime($data_trends['review_date'])).'.xls'; //save our workbook as this file
        }
        else{
            $filename = preg_replace("/[^a-z0-9\_\-\.]/i", '',$data['taskDetails'][0]['workflow_name']).'_'.date("d-m-Y",strtotime($data['review_date'])).'.xls'; //save our workbook as this file
        }
        $filename;
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $file_path = FILE_SYSTEM_PATH.'downloads/' . $filename;
        $objWriter->save($file_path);
        $view_path='downloads/' . $filename;
        $file_path = REST_API_URL.$view_path;
        $file_path = str_replace('::1','localhost',$file_path);
        $insert_id = $this->Download_model->addDownload(array('path'=>$view_path,'filename'=>$filename,'user_id'=>$this->session_user_id,'access_token'=>substr($_SERVER['HTTP_AUTHORIZATION'],7),'status'=>0,'created_date_time'=>currentDate()));

        $response = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>pk_encrypt($insert_id));
        $this->response($response, REST_Controller::HTTP_OK);

    }
    function getkey($pos){
        //this function used to return ascii value based on position used int export_get function
        $numeric = $pos % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($pos / 26);
        if ($num2 > 0) {
            return $this->getkey($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
        function project_change_log($data){
        $project_chaged = 0;
        $project_curent_info = $this->User_model->check_record('contract',array('id_contract'=>$data['id_contract']));
        if(isset($project_curent_info[0])){
            if($project_curent_info[0]['contract_name'] != $data['contract_name']) $project_chaged = 1;
            if($project_curent_info[0]['contract_start_date'] != $data['contract_start_date'].' 00:00:00') $project_chaged = 1;
            if($project_curent_info[0]['contract_end_date'] != $data['contract_end_date'].' 00:00:00') $project_chaged = 1;
            if($project_curent_info[0]['contract_value'] != $data['contract_value']) $project_chaged = 1;
            if($project_curent_info[0]['business_unit_id'] != $data['business_unit_id']) $project_chaged = 1;
            if($project_curent_info[0]['currency_id'] != $data['currency_id']) $project_chaged = 1;
            if($project_curent_info[0]['contract_owner_id'] != $data['contract_owner_id']) $project_chaged = 1;
            if($project_curent_info[0]['delegate_id'] != $data['delegate_id']) $project_chaged = 1;
            if($project_curent_info[0]['description'] != $data['description']) $project_chaged = 1;
            if($project_curent_info[0]['project_status'] != $data['status']) $project_chaged = 1;
        }
        //echo $project_chaged;exit;

        if($project_chaged == 1){ 
            $log_add_data = array(
                'project_id' => $project_curent_info[0]['id_contract'],
                'project_name' => $project_curent_info[0]['contract_name'],
                'project_startdate' => $project_curent_info[0]['contract_start_date'],
                'project_enddate' => $project_curent_info[0]['contract_end_date'],
                'budget_spend' => $project_curent_info[0]['contract_value'],
                'business_unit' => $project_curent_info[0]['business_unit_id'],
                'owner_id' => $project_curent_info[0]['contract_owner_id'],
                'delegate_id' => $project_curent_info[0]['delegate_id'],
                'project_description' => $project_curent_info[0]['description'],
                'currency' => $project_curent_info[0]['currency_id'],     
                'created_by' => isset($data['updated_by'])&&$data['updated_by']!=''?$data['updated_by']:$project_curent_info[0]['created_by'],
                'created_on' => currentdate(), 
                'is_status_change' => $project_curent_info[0]['project_status']
            );
             $this->User_model->insert_data('project_log',$log_add_data);
            // echo $this->db->last_query();
        }
    }

    public function project_log_get(){
        $data = $this->input->get();
        if (empty($data)) {
            $result = array('status' => FALSE, 'error' => $this->lang->line('invalid_data'), 'data' => '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['project_id'])) {
            $data['project_id'] = pk_decrypt($data['project_id']);
            if($this->session_user_info->user_role_id!=7)
            if(!in_array($data['project_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['project_log_id'])) $data['project_log_id']=pk_decrypt($data['project_log_id']);
        $current_project_details=array();
        $project_log_options=array();
        if(isset($data['project_id'])){
            $current_project_details = $this->Contract_model->getContractCurrentDetails(array('contract_id'=>$data['project_id']));//echo $this->db->last_query();exit;
            //$project_log_options = $this->Contract_model->getContractLogId($data);
            $project_log_options = $this->Project_model->getProjectLogId($data);
        }
        $project_log_details=array();
        if(isset($data['project_log_id'])){
            $project_log_details = $this->Project_model->getProjectLogDetails($data);
       
        }
        //echo '<pre>'.print_r($project_log_details);exit;
        foreach($project_log_options as $k=>$v){
            $project_log_options[$k]['id_project_log']=pk_encrypt($project_log_options[$k]['id_project_log']);
        }
        foreach($current_project_details as $k=>$v){
            if($current_project_details[$k]['project_status']==1)
            {
                $current_project_details[$k]['project_status']="Active";
            }
            elseif($current_project_details[$k]['project_status']==0)
            {
                $current_project_details[$k]['project_status']="Closed";
            }
            else{
                $current_project_details[$k]['project_status']="---";
            }
            $current_project_details[$k]['project_status']=="1"?"Active":"Closed";
            $current_project_details[$k]['business_unit_id']=pk_encrypt($current_project_details[$k]['business_unit_id']);
            $current_project_details[$k]['contract_owner_id']=pk_encrypt($current_project_details[$k]['contract_owner_id']);
            $current_project_details[$k]['currency_id']=pk_encrypt($current_project_details[$k]['currency_id']);
            $current_project_details[$k]['delegate_id']=pk_encrypt($current_project_details[$k]['delegate_id']);
            $current_project_details[$k]['id_contract']=pk_encrypt($current_project_details[$k]['id_contract']);
           // $current_project_details[$k]['relationship_category_id']=pk_encrypt($current_project_details[$k]['relationship_category_id']);
            $current_project_details[$k]['updated_by']=pk_encrypt($current_project_details[$k]['updated_by']);
        }
        foreach($project_log_details as $k=>$v){ 
            if($project_log_details[$k]['project_status']==1)
            {
                $project_log_details[$k]['project_status']="Active";
            }
            elseif($project_log_details[$k]['project_status']==0)
            {
                $project_log_details[$k]['project_status']="Closed";
            }
            else{
                $project_log_details[$k]['project_status']="---";
            }
            $project_log_details[$k]['business_unit_id']=pk_encrypt($project_log_details[$k]['business_unit_id']);
            $project_log_details[$k]['contract_owner_id']=pk_encrypt($project_log_details[$k]['contract_owner_id']);
            $project_log_details[$k]['currency_id']=pk_encrypt($project_log_details[$k]['currency_id']);
            $project_log_details[$k]['delegate_id']=pk_encrypt($project_log_details[$k]['delegate_id']);
            $project_log_details[$k]['id_contract']=pk_encrypt($project_log_details[$k]['id_contract']);
            $project_log_details[$k]['id_project_log']=pk_encrypt($project_log_details[$k]['id_project_log']);
            
        }
        $result =array('current_project_details'=>$current_project_details,'project_log_options'=>$project_log_options,'project_log_details'=>$project_log_details);
        //echo '<pre>';print_r($contract_log_options);exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function projectDashboard_get($DATA=null){
        if(isset($DATA) && !empty($DATA)){
            $data = $DATA;
        }else{
            $data = $this->input->get();
        }
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('project_id', array('required'=>$this->lang->line('project_id_req')));
        // $this->form_validator->add_rules('contract_review_id', array('required'=>$this->lang->line('contract_review_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1){
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['contract_id'])){
            $data['contract_id'] = pk_decrypt($data['contract_id']);
        }
        if(isset($data['project_id'])){
            $data['project_id'] = pk_decrypt($data['project_id']);
        }
        if(isset($data['contract_review_id'])){
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
        }
        if(isset($data['contract_workflow_id'])){
            $contract_workflow_id = $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
        }else{
            $contract_workflow_id = $data['contract_workflow_id'] = 0;
        }
        $data['external_user'] = false;
        if($this->session_user_info->user_role_id == 7){
            $data['external_user'] = true;
        }
        // echo '<pre>'.print_r($data);exit;
        // $offset = isset($data['offset'])?$data['offset']:0;
        // if(isset($data['trend_type']) && $data['trend_type'] == 'prev')
        //     $offset = $offset + 5;
        // else if(isset($data['trend_type']) && $data['trend_type'] == 'next')
        //     $offset = $offset - 5;
        // else
        //     $offset = 0;

        // $current_review = $this->Contract_model->getCurrentReview($data);
        // // echo $this->db->last_query();exit;
        // //echo '<pre>'.print_r($current_review);
        // //Declaring Result arrays starts
        // $result_array = $review_modules = $review_topics = $review_dates = array();
        // //Declaring Result arrays end

        // //Current Review Data Starts
        // $result_array['offset'] = $offset;
        // $result_array['review_date'] = isset($current_review[0])?$current_review[0]['created_on']:'';
        // $result_array['review_status'] = isset($current_review[0])?$current_review[0]['contract_review_status']:'';
        // $result_array['review_score'] = isset($current_review[0])?$current_review[0]['review_score']:'';
        // $result_array['template_name'] = isset($current_review[0])?$current_review[0]['template_name']:'';
        // $result_array['contract_workflow_id'] = (isset($data['contract_workflow_id']) && $data['contract_workflow_id']>0)?pk_encrypt($data['contract_workflow_id']):0;
        // $result_array['contract_review_id'] = pk_encrypt($data['contract_review_id']);

        // //Initializing Prev
        // $result_array['prev'] = 0;
        // //Checking previous reviews exists or not
        // $contract_contributor = $this->User_model->check_record('contract_user',array('contract_id'=>$data['contract_id'],'user_id'=>$this->session_user_id));
        // if($offset+5 >= 0){
        //     if($this->session_user_info->user_role_id == 7 || count($contract_contributor) > 0){
        //         $get_review_trends = 'SELECT cr.id_contract_review,cr.created_on FROM `contract_review` cr LEFT JOIN contract_user cu ON cr.id_contract_review = cu.contract_review_id WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND cr.contract_id = '.$data['contract_id'].' AND cu.user_id = '.$this->session_user_id.' GROUP BY id_contract_review ORDER BY id_contract_review DESC LIMIT '.($offset+5).',5';
        //     }else{
        //         $get_review_trends = 'SELECT id_contract_review,created_on FROM `contract_review` WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND contract_id = '.$data['contract_id'].' ORDER BY id_contract_review DESC LIMIT '.($offset+5).',5';
        //     }
        //     $get_review_trends = $this->User_model->custom_query($get_review_trends);
        //     //Updating Prev
        //     if(count($get_review_trends))
        //         $result_array['prev'] = 1;
        // }
        // // echo $this->db->last_query();exit;
        // //Initializing Next
        // $result_array['next'] = 0;
        // //Checking Next reviews exists or not
        // // echo 'offset'.$offset;
        // if($offset-5 >= 0){
        //     if($this->session_user_info->user_role_id == 7 || count($contract_contributor) > 0){
        //         $get_review_trends = 'SELECT cr.id_contract_review,cr.created_on FROM `contract_review` cr LEFT JOIN contract_user cu ON cr.id_contract_review = cu.contract_review_id WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND cr.contract_id = '.$data['contract_id'].' AND cu.user_id = '.$this->session_user_id.' GROUP BY id_contract_review ORDER BY id_contract_review DESC LIMIT '.($offset-5).',5';
        //     }else{
        //         $get_review_trends = 'SELECT id_contract_review,created_on FROM `contract_review` WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND contract_id = '.$data['contract_id'].' ORDER BY id_contract_review DESC LIMIT '.($offset-5).',5';
        //     }
        //     $get_review_trends = $this->User_model->custom_query($get_review_trends);
        //     //Updating Next
        //     if(count($get_review_trends))
        //         $result_array['next'] = 1;
        // }
        
        // $result_array['dates'] = $result_array['modules'] = array();        
        // //Current Review Data Ends

        // //Checking offset is lessthan or not.
        // if($offset < 0)$offset=0;
        // if($this->session_user_info->user_role_id == 7 || count($contract_contributor) > 0){
        //     $get_review_trends = 'SELECT cr.id_contract_review,cr.updated_on as created_on FROM `contract_review` cr LEFT JOIN contract_user cu ON cr.id_contract_review = cu.contract_review_id WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND cr.contract_id = '.$data['contract_id'].' AND cu.user_id = '.$this->session_user_id.' GROUP BY id_contract_review ORDER BY id_contract_review DESC LIMIT '.$offset.',5';
        // }else{
        //     $get_review_trends = 'SELECT id_contract_review,updated_on as created_on FROM `contract_review` WHERE id_contract_review < '.$data['contract_review_id'].' AND contract_workflow_id = '.$contract_workflow_id.' AND contract_id = '.$data['contract_id'].' ORDER BY id_contract_review DESC LIMIT '.$offset.',5';
        // }

        $contributors_modules = array();
        $$contributors_reviews_ids = array();
        if($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4){
            $get_contributors_modules = $this->User_model->check_record('contract_user',array('contract_id'=>$data['project_id'],'status'=>1,'user_id'=>$this->session_user_id));
            //print_r();
            if(count($get_contributors_modules)>0){

                $contributors_modules = array_map(function($i){ return ($i['module_id']); },$get_contributors_modules);
                $contributors_reviews_ids = array_map(function($i){ return ($i['contract_review_id']); },$get_contributors_modules);

            }
        }
        // print_r($contributors_reviews_ids);exit;
        // $get_review_trends = $this->User_model->custom_query($get_review_trends);
        // print_r($data);exit;
        $check_task=$this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['contract_workflow_id']));
        // print_r($data);exit;
        if($check_task[0]['parent_id']>0){
            $get_review_trends = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'workflow_id'=>$data['contract_workflow_id']));
            // echo $this->db->last_query();exit;
        }
        else{            
            if($this->session_user_info->user_role_id == 7){
                $get_review_trends = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'provider_id'=>$this->session_user_id));
            }
            else{
                $get_review_trends_child=array();
                $get_review_trends_parent = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'workflow_id'=>$data['contract_workflow_id']));//echo $this->db->last_query();exit;
                if(empty($get_contributors_modules))
                $get_review_trends_child = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'parent_id'=>$data['contract_workflow_id']));
                $get_review_trends=array_merge($get_review_trends_parent,$get_review_trends_child);
                $get_bu_name=$this->Project_model->getBuname(array('contract_id'=>$data['project_id']));
                // print_r($get_review_trends[0]['parent_id']);exit;
                if($get_review_trends[0]['parent_id']==0)
                $get_review_trends[0]['provider_name']=$get_bu_name[0]['bu_name'];
            }
        }
        // print_r($get_review_trends);exit;
        if($data['type']=='archieve'){
            $get_review_trends_parent = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'type'=>'archive','workflow_id'=>$data['contract_workflow_id']));
            $check_project_owner_delegate=$this->User_model->check_record('contract',array('id_contract'=>$data['project_id']));
            $is_subtask_visible=0;
            if($this->session_user_info->user_role_id==2){
                $is_subtask_visible=1;
            }
            if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4){
                $check_contribution=$this->User_model->check_record('contract_user',array('contract_id'=>$data['project_id'],'contract_review_id'=>$data['contract_review_id'],'user_id'=>$this->session_user_id,'status'=>1));
                if(empty($check_contribution)){
                    $is_subtask_visible=1;
                }
            }
            if($is_subtask_visible==1)
            $get_review_trends_child = $this->Project_model->get_subtaskList(array('project_id'=>$data['project_id'],'type'=>'archive','parent_id'=>$data['contract_workflow_id']));
            $get_review_trends=array_merge($get_review_trends_parent,$get_review_trends_child);
            // print_r($get_review_trends);exit;
            // echo $this->db->last_query();exit;
            $get_bu_name=$this->Project_model->getBuname(array('contract_id'=>$data['project_id']));
            // print_r($get_review_trends[0]['parent_id']);exit;
            if($get_review_trends[0]['parent_id']==0)
            $get_review_trends[0]['provider_name']=$get_bu_name[0]['bu_name'];
        }
        $data['contract_review_id']=$get_review_trends[0]['id_contract_review'];
        $index = 0;
        // print_r($validator_modules);exit;
        // if(count($get_review_trends) > 0){
        //     // $get_review_trends = array_reverse($get_review_trends);
        // }
            // foreach($get_review_trends as $reviews){
            //     //Review Dates Object Starts
            //     $subtask_list[$index]['date'] = date('M d, Y',strtotime($reviews['created_on']));
            //     $subtask_list[$index]['contract_review_id'] = $reviews['id_contract_review'];
            //     $subtask_list[$index]['contract_workflow_id'] = $result_array['contract_workflow_id'];
            //     //Review Dates Object Ends
            //     $index++;
            // }
            // $next_trend_index = count($review_dates);
            // $review_dates[$next_trend_index]['date'] = 'Current Score';
            // $review_dates[$next_trend_index]['contract_review_id'] = $current_review[0]['contract_review_id'];
            // $review_dates[$next_trend_index]['contract_workflow_id'] = $result_array['contract_workflow_id'];
            // echo '<pre>'.print_r($review_dates);exit;

            //Checking for contributor
            
            $contributors_modules = array();
            if($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4 || $this->session_user_info->user_role_id == 7){
                $contributors_modules = $this->User_model->check_record('contract_user',array('contract_id'=>$data['project_id'],'contract_review_id'=>$data['contract_review_id'],'status'=>1,'user_id'=>$this->session_user_id));
                 //echo '<pre>'.$this->db->last_query();
                if(count($contributors_modules)>0){

                    $contributors_modules = array_map(function($i){ return ($i['module_id']); },$contributors_modules);
                }
            }
             //echo '<pre>'.print_r($contributors_modules);exit;
            ////

            $user_role_id=$this->session_user_info->user_role_id;
            if($check_task[0]['parent_id']>0){
                // $user_role_id=7;
                // $data['external_user']=1;
            }
            //Preparing Module object of current review
            $parent_modules = $this->Contract_model->getTrendsModules(array('contract_review_id'=>$data['contract_review_id'],'contributors_modules'=>$contributors_modules,'user_role_id'=>$user_role_id));
            // echo $this->db->last_query();exit;
            foreach($parent_modules as $key => $module){
                $validator_exists = count($this->Contract_model->getValidatormodules(array('contract_review_id'=> $data['contract_review_id'],'module_id'=>$module['id_module'],'contribution_type'=>1)))>0?true:false;
                if($validator_exists && ($this->session_user_info->user_role_id ==2 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4 || $this->session_user_info->user_role_id == 6)){
                    $review_modules[$key]['side_by_side_validation'] = true;
                }
                else{
                    $review_modules[$key]['side_by_side_validation'] = false;
                }
                $review_modules[$key]['id_module'] = pk_encrypt($module['id_module']);
                $review_modules[$key]['module_name'] = $module['module_name'];
                $review_modules[$key]['static'] = $module['static'];
                $review_modules[$key]['module_status'] = $module['module_status'];
                $review_modules[$key]['is_workflow'] = $module['is_workflow'];
                $review_modules[$key]['parent_module_id'] = pk_encrypt($module['parent_module_id']);
                // print_r($get_review_trends);exit;
                foreach($get_review_trends as $rkey => $reviews){
                    // print_r($reviews);exit;
                    //Geting Module_score from Module table
                    $module_score = $this->User_model->check_record('module',array('contract_review_id' => $reviews['id_contract_review'],'parent_module_id' => $module['parent_module_id']));//echo '<pre>'.$this->db->last_query();exit;
                    //If the module status is 1 then only considering the score.
                    // if((int)$module_score[0]['module_status'])
                    //     $review_modules[$key]['module_scores'][] = isset($module_score[0])?$module_score[0]['module_score']:0;
                    // else
                    //     $review_modules[$key]['module_scores'][] = '';
                        $review_modules[$key]['module_scores'][] = isset($module_score[0])?$module_score[0]['module_score']:0;
                    $review_modules[$key]['module_ids'][] = isset($module_score[0])?$module_score[0]['id_module']:0;
                }
                // print_r($review_modules);exit;
                $parent_topics = $this->Contract_model->getTrendsTopics(array('module_id'=>$module['id_module'],'external_user'=>$data['external_user']));
                // print_r($data);exit;
                // echo $this->db->last_query();exit;
                //Preparing the topic ids againest reviews
                $review_topics = array();
                foreach($parent_topics as $tkey => $topic){
                    $review_topics[$tkey]['id_topic'] = $topic['id_topic'];
                    $review_topics[$tkey]['topic_name'] = $topic['topic_name'];
                    $review_topics[$tkey]['parent_topic_id'] = $topic['parent_topic_id'];
                }
                $review_modules[$key]['topics'] = $review_topics;
            }
            $validator_modules = $this->Contract_model->getValidatormodules(array('contract_review_id'=>$data['contract_review_id'],'user_id'=>$this->session_user_id,'contribution_type'=>1));
            //Topic level
            // print_r($review_modules);exit;
            foreach($review_modules as $rm => $rmodule){
                foreach($rmodule['topics'] as $rt => $rtopic){
                    //Geting Topic_score from Module table
                    foreach($rmodule['module_ids'] as $v){
                        if(count($validator_modules)>0){
                            //$topic_score =  $this->Project_model->getContractDashboard_old(array('contract_review_id' => $data['contract_review_id'],'provider_visibility'=>array(1,0),'topic_id'=>$rtopic['id_topic']));
                            $topic_score = $this->User_model->check_record('topic',array('id_topic'=>$rtopic['id_topic'])); 
                            $review_modules[$rm]['topics'][$rt]['topic_scores'][] = isset($topic_score[0])?$topic_score[0]['topic_score']:0;
                        }
                        else{
                            $topic_score = $this->Contract_model->getTrendsTopicScore(array('module_ids'=>$v,'parent_topic_id'=>$rtopic['parent_topic_id']));
                            $review_modules[$rm]['topics'][$rt]['topic_scores'][] = isset($topic_score[0])?$topic_score[0]['topic_score']:0;
                        }
                        $review_modules[$rm]['topics'][$rt]['id_topic']= pk_encrypt($rtopic['id_topic']);
                        $review_modules[$rm]['topics'][$rt]['parent_topic_id'] = pk_encrypt($rtopic['parent_topic_id']);
                        $topic_score = $this->Contract_model->getTrendsTopicScore(array('module_ids'=>$v,'parent_topic_id'=>$rtopic['parent_topic_id'])); 
                        $review_modules[$rm]['topics'][$rt]['topic_ids'][] = isset($topic_score[0]['topic_id'])?$topic_score[0]['topic_id']:$topic_score[0]['id_topic'];
                        // $review_modules[$rm]['topics'][$rt]['topic_ids'][] = $rtopic['id_topic'];
                    }
                    $parent_questions = $this->Contract_model->getTrendsQuestions(array('topic_id'=>$rtopic['id_topic'],'external_user'=>$data['external_user']));
                    // echo $this->db->last_query();exit;
                    //Preparing the question ids againest reviews
                    $review_questions = array();
                    foreach($parent_questions as $qkey => $question){
                        $review_questions[$qkey]['id_question'] = $question['id_question'];
                        $review_questions[$qkey]['question_text'] = $question['question_text'];
                        $review_questions[$qkey]['parent_question_id'] = $question['parent_question_id'];
                        $review_questions[$qkey]['question_type'] = $question['question_type'];
                        $review_questions[$qkey]['provider_visibility'] = $question['provider_visibility'];
                    }
                    $review_modules[$rm]['topics'][$rt]['questions'] = $review_questions;
                }
                //unsetting after used;
                unset($review_modules[$rm]['module_ids']);
            }
            //print_r($review_modules);exit;
            //Question level
            foreach($review_modules as $rm => $rmodule){
                foreach($rmodule['topics'] as $rt => $rtopic){
                    $new_array = []; 
                    foreach($rtopic['questions'] as $rq => $rquestion){ 
                        foreach($rtopic['topic_ids'] as $v){
                            if(count($validator_modules)>0){
                                $is_validator=1;
                            }
                            else{
                                $is_validator=0;
                            }
                            //$question_answeres = $this->Contract_model->getTrendsQuestionAnsweres(array('topic_ids'=>$v,'question_type'=>$rquestion['question_type'],'parent_question_id'=>$rquestion['parent_question_id'],'is_validator'=>$is_validator));
                            $question_answeres = $this->Contract_model->getprojectdashboardQuestionAnsweres(array('topic_ids'=>$v,'question_type'=>$rquestion['question_type'],'parent_question_id'=>$rquestion['parent_question_id']));
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_answeres'][]=isset($question_answeres[0])?$question_answeres[0]['question_answer']:'';
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'][]=isset($question_answeres[0])?$question_answeres[0]['v_question_answer']:'';
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['validatorIsAnswerEmpty'][]=isset($question_answeres[0])?false:true;
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_feedback'][]=isset($question_answeres[0])?$question_answeres[0]['question_feedback']:'';
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_question_feedback'][]=isset($question_answeres[0])?$question_answeres[0]['v_question_feedback']:'';
                            $v_attachment_count = 0;
                            $attachment_count = 0;
                            $attachmentArray=array();
                            $v_attachmentArray=array();
                            if(!empty($question_answeres[0]))
                            {
                                $all_attachments = $this->Document_model->getDocumentsList(array("reference_id"=>$question_answeres[0]['id_question'],"reference_type"=>"question","module_type"=>"contract_review","document_type"=>array(0,1)));
                                if(!empty($all_attachments))
                                {
                                    foreach($all_attachments as $attachment)
                                    {
                                        //((int)$attachment['validator_record'])?$v_attachment_count++:$attachment_count++;
                                        $attachment['id_document'] = pk_encrypt($attachment['id_document']);
                                        $attachment['reference_id'] =  pk_encrypt($attachment['reference_id']);
                                        if($attachment['validator_record'])
                                        {
                                            $v_attachment_count++;
                                            $v_attachmentArray[] = $attachment;
                                        }
                                        else{
                                            $attachment_count++;
                                            $attachmentArray[] = $attachment;
                                        }
                                    }
                                } 
                            }
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['attachment_count'][]=$attachment_count;
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_attachment_count'][]=$v_attachment_count;
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['attachment'][]=$attachmentArray;
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_attachment'][]=$v_attachmentArray;
                            //$review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_answeres'][]=isset($question_answeres[0])?$question_answeres[0]['question_answere']:'';
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['id_question'] = pk_encrypt($rquestion['id_question']);
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['provider_visibility'] = $rquestion['provider_visibility'];
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['parent_question_id'] = pk_encrypt($rquestion['parent_question_id']);
                            // if($is_validator && $rmodule['module_status']!=3)
                            // {
                            //     $review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_answeres'][0]=isset($question_answeres[0])?$question_answeres[0]['v_question_answer']:'';
                            // }
                        }
                        array_push($new_array,$review_modules[$rm]['topics'][$rt]['questions'][$rq]);
                        //this block is for creating duplicate question in result set when side by side validation is true
                        if($review_modules[$rm]['side_by_side_validation'])
                        {
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_validator']=1;
                            if((empty($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'][0]))&&(($review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_type'] == "input")||($review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_type'] == "date")))
                            {
                                $review_modules[$rm]['topics'][$rt]['questions'][$rq]['validatorIsAnswerEmpty'][0] =true;
                            }
                            if(strtolower($review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_answeres'][0]) ==  strtolower($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'][0]) )
                            {
                                $review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_green_diffference'] =1;  
                            }
                            elseif(empty($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'][0])||($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'][0] =="---") )
                            {
                                $review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_blue_difference'] =1;  
                            }
                            else{
                                $review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_red_difference'] =1; 
                            }
                            if($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validatorIsAnswerEmpty'][0] ==true)
                            {
                                unset($review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_green_diffference']);
                                unset($review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_red_difference']);
                                $review_modules[$rm]['topics'][$rt]['questions'][$rq]['is_blue_difference'] =1; 
                            }
                            unset($review_modules[$rm]['topics'][$rt]['questions'][$rq]['validatorIsAnswerEmpty']);
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_answeres'] = $review_modules[$rm]['topics'][$rt]['questions'][$rq]['validator_answeres'];
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['question_feedback'] = $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_question_feedback'];
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['attachment'] = $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_attachment'];
                            $review_modules[$rm]['topics'][$rt]['questions'][$rq]['attachment_count'] = $review_modules[$rm]['topics'][$rt]['questions'][$rq]['v_attachment_count'];
                            array_push($new_array,$review_modules[$rm]['topics'][$rt]['questions'][$rq]);
                        } 
                    }
                    unset($review_modules[$rm]['topics'][$rt]['questions']);
                    $review_modules[$rm]['topics'][$rt]['questions'] = $new_array;
                    //unsetting after used;
                    unset($review_modules[$rm]['topics'][$rt]['topic_ids']);
                }
                // if(count($validator_modules)>0)
                // $review_modules[$rm]['module_scores'][$rm] = getScore(array_column($rmodule['topics'],'topic_scores'));
                if(count($validator_modules)>0)
                {
                    $moduleScore = $this->User_model->check_record('module',array('id_module' => pk_decrypt($rmodule['id_module'])));
                    $review_modules[$rm]['module_scores'][$rm] = $moduleScore[0]['module_score'];
                }
            }
            //print_r($review_modules);exit;
            // if(count($validator_modules)>0){
            //     // print_r(pk_decrypt($rmodule['id_module']));exit;
            //     foreach($review_modules as $m=>$md){
            //         foreach($md['topics'] as $tp => $tpc){
            //             print_r($review_modules[$m]['topics'][$tp]['topic_scores'][$tp]);exit;
            //             $module_socre = $this->Contract_model->getContributorContractReviewModuleScore(array('dynamic_column'=>'v_question_answer','contract_review_id'=>$data['contract_review_id'],'module_ids'=>array(pk_decrypt($md['id_module']))));
            //             $review_modules[$m]['topics'][$t]['topic_scores'] = getScoreByCount($module_socre[0]);
    
            //         }
            //     }
            // }

            //Assigning Modules,dates to result array.
            $result_array['modules'] = $review_modules;
            $result_array['subtask_lists'] = $get_review_trends;

            //Encrypting Ids starts
            foreach($result_array['subtask_lists'] as $k => $v){
                // print_r($v);exit;
                $result_array['subtask_lists'][$k]['contract_review_id'] = pk_encrypt($v['id_contract_review']);
                $result_array['subtask_lists'][$k]['module_id'] = pk_encrypt($v['module_id']);
                $result_array['subtask_lists'][$k]['id_contract_workflow'] = pk_encrypt($v['id_contract_workflow']);
                $result_array['subtask_lists'][$k]['id_contract_review'] = pk_encrypt($v['id_contract_review']);
                $result_array['subtask_lists'][$k]['contract_id'] = pk_encrypt($v['contract_id']);
                $result_array['subtask_lists'][$k]['parent_id'] = pk_encrypt($v['parent_id']);
                $result_array['subtask_lists'][$k]['templateName'] = $v['template_name'];
            }
            //Encrypting Ids ends
            
        //}
        $reviews = $this->Contract_model->getContractReview(array('contract_id'=>$data['project_id'],'contract_workflow_id'=>$data['contract_workflow_id']));
        $contract_progress_score=$this->calculateScoreAndProgress(array('id_contract_review'=>isset($data['contract_review_id'])?$data['contract_review_id']:$currentReviewId[0]['id_contract_review'],'user_id'=>!empty($this->session_user_id)?$this->session_user_id:0,'is_subtask'=>$subTaskVal));//new funcion for calculating  the score and contract progress
        $result[0]['score']=$contract_progress_score['score'];
        // print_r($review_modules[0]['module_scores'][0]);exit;
        $result_array['data'] = array(
            'review_date' => ($reviews[0]['updated_on']!='')?date('Y-m-d',strtotime($reviews[0]['updated_on'])):'',
            'review_status' => $reviews[0]['contract_review_status'],
            // if(count($validator_modules)>0)
            //'review_score'=>count($validator_modules)>0?$contract_progress_score['score']:$reviews[0]['review_score'],
             'review_score'=>!empty($reviews[0]['review_score'])?$reviews[0]['review_score']:'',
            'contract_review_id' => pk_encrypt($reviews[0]['id_contract_review']),
            'contract_workflow_id' => pk_encrypt($reviews[0]['contract_workflow_id']),
            
        );
        //$result_array['modules'][0]['module_scores'][0]=count($validator_modules)>0?$contract_progress_score['score']:$reviews[0]['review_score'];
        if(count($validator_modules)==0)
        {
            $result_array['modules'][0]['module_scores'][0]=$reviews[0]['review_score'];
        }
       
        // print_r($result_array['modules'][0]['module_scores']);exit;
        $check_tast_type=$this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$data['contract_workflow_id']));
        if($check_tast_type[0]['parent_id']>0){
            $result_array['data']['is_subtask']=1;
        }
        else{
            $result_array['data']['is_subtask']=0;
        }
        $result_array['modules'][0]['module_name']=$check_tast_type[0]['workflow_name'];
        $result_array['data']['is_workflow']=1;
        $result_array['data']['template_name']=$get_review_trends[0]['module_name'];
        $result_array['template_name']=$check_tast_type[0]['workflow_name'];
        $result_array['data']['is_workflow']=1;
        if(isset($DATA)){
            return $result_array;
        }else{
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result_array);
            $this->response($result, REST_Controller::HTTP_OK);
        }

    }
    public function unmappingProviderToProject_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('provider_id', array('required'=>$this->lang->line('provider_id_req')));
        $this->form_validator->add_rules('project_id', array('required'=>$this->lang->line('project_id_req')));

        $validated = $this->form_validator->validate($data);

        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['provider_id'])){
            $data['provider_id']=pk_decrypt($data['provider_id']);
        }
        if(isset($data['project_id'])){
            $data['project_id']=pk_decrypt($data['project_id']);
        }

        $check_record_exists=$this->User_model->check_record('project_providers',array('project_id'=>$data['project_id'],'provider_id'=>$data['provider_id']));

        if(empty($check_record_exists)){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('invalid_provider')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            if(isset($check_record_exists[0]['is_linked']) && $check_record_exists[0]['is_linked']==1){
                $mappedUsers = $this->User_model->check_record("user",array("provider"=>$data['provider_id']));
                if(!empty($mappedUsers))
                {
                    $data['user_id'] = array_map(function($i){ return $i['id_user']; },$mappedUsers);
                    $connectedsubtasks =$this->Project_model->getConnectedSubtasks(array("contract_id"=>$data['project_id'],"provider_id"=>$data['user_id']));
                    if(empty($connectedsubtasks))
                    {
                        $this->User_model->update_data('project_providers',array("is_linked"=>0),array("project_id"=>$data['project_id'],"provider_id"=>$data['provider_id']));
                        $result = array('status'=>TRUE, 'error' =>'', 'data'=>$this->lang->line('provider_deleted'));
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                    else{
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('provider_having_subtask')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                }
                else{
                    $this->User_model->update_data('project_providers',array("is_linked"=>0),array("project_id"=>$data['project_id'],"provider_id"=>$data['provider_id']));
                    $result = array('status'=>TRUE, 'error' =>'', 'data'=>$this->lang->line('provider_deleted'));
                    $this->response($result, REST_Controller::HTTP_OK);
                }

            }
            else{
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('invalid_provider')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
    }

    public function service_catalogue_post(){
        $data = $this->input->post();
        //$data['catalogue_id'] = 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('id_contract_req')));
        $this->form_validator->add_rules('catalogue_id', array('required'=>$this->lang->line('id_catalogue_req')));
        // if(!isset($data['id_service_catalogue'])) {
        //     $this->form_validator->add_rules('catalogue_item_name', array('required'=>$this->lang->line('catalogue_item_req')));
        // }
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if((isset($data['period_start_date'])) && (isset($data['period_end_date'])))
        {
            if ($data['period_start_date'] > $data['period_end_date']) 
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('period_start_date_should_be_less_than_period_end_date')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //checking permissions wit business unit id
        //    if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0){
        //     $business_unit_id_exp=$data['business_unit_id'];
        //     $business_unit_id=array();
        //     foreach($business_unit_id_exp as $k=>$v){
        //             $business_unit_id_chk = pk_decrypt($v);
        //             if(!in_array($business_unit_id_chk,$this->session_user_business_units)){
        //                 $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
        //                 $this->response($result, REST_Controller::HTTP_OK);
        //             }
        //         $business_unit_id[]=pk_decrypt($v);
        //     }
        //     $data['business_unit_id']=implode(',',$business_unit_id);
        // }
        if(isset($data['payment_periodicity']))
        {
            $data['payment_periodicity'] = pk_decrypt($data['payment_periodicity']);
        }
        if(isset($data['catalogue_id']))
        {
            $data['catalogue_id'] = pk_decrypt($data['catalogue_id']);
        }

        if(!(isset($data['calculated_total_item_spend_add_to_chart']) && ($data['calculated_total_item_spend_add_to_chart']!="")))
        {
            $data['calculated_total_item_spend_add_to_chart'] = null;
        }
        if(!(isset($data['manual_total_item_spend_add_to_chart']) && ($data['manual_total_item_spend_add_to_chart']!="")))
        {
            $data['manual_total_item_spend_add_to_chart'] = null;
        }
        if(isset($data['id_service_catalogue'])){
            $data['id_service_catalogue'] = pk_decrypt($data['id_service_catalogue']);
            // if($data['calculated_total_item_spend_add_to_chart']==0||$data['manual_total_item_spend_add_to_chart']==1)
            // {
            //     $data['unit_price']="";
            //     $data['quantity']="";
            //     $data['calculated_total_item_spend'] = "";
            // }
            // if(empty($data['business_unit_id']))
            // {
            //     $data['business_unit_id'] = "";
            // }
            $upd_data = array(
                'contract_id'=> $data['contract_id'],
                'catalogue_id'=> $data['catalogue_id'],
                //'catalogue_item_name'=>$data['catalogue_item_name'],
                'unit_price'=>!empty($data['unit_price'])?$data['unit_price']:null,
                'unit_type'=>isset($data['unit_type'])?$data['unit_type']:'',
                'quantity'=>!empty($data['quantity'])?$data['quantity']:null,
                //'business_unit_id'=>isset($data['business_unit_id'])?$data['business_unit_id']:'',
                'payment_periodicity_id' => isset($data['payment_periodicity'])?$data['payment_periodicity']:0,
                'period_start_date'=>!empty($data['period_start_date'])?$data['period_start_date']:null,
                'period_end_date'=>!empty($data['period_end_date'])?$data['period_end_date']:null,
                'calculated_total_item_spend'=>!empty($data['calculated_total_item_spend'])?$data['calculated_total_item_spend']:null,
                'calculated_total_item_spend_add_to_chart'=> $data['calculated_total_item_spend_add_to_chart'],
                'manual_total_item_spend'=>!empty($data['manual_total_item_spend'])?$data['manual_total_item_spend']:null,
                'manual_total_item_spend_add_to_chart' => $data['manual_total_item_spend_add_to_chart'],
                'comment'=>isset($data['comments'])?$data['comments']:'',
                'updated_by'=> $this->session_user_id,
                'updated_on'=> currentDate(),
                // 'status'=>isset($data['status'])?$data['status']:1
                );  
                // print_r($upd_data);exit;
            if($this->User_model->update_data('service_catalogue',$upd_data,array('id_service_catalogue'=>$data['id_service_catalogue'])))
            {
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('service_catalogue_update_success'),'data'=>''), REST_Controller::HTTP_OK);
            }
               
            else
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
        }
        else{
            //add_to_chart 0=no, 1 = yes
            // if($data['calculated_total_item_spend_add_to_chart']==0||$data['manual_total_item_spend_add_to_chart']==1)
            // {
            //     $data['unit_price']="";
            //     $data['quantity']="";
            //     $data['calculated_total_item_spend'] = "";
            // }
            $ins_data = array(
                'contract_id'=> $data['contract_id'],
                'catalogue_id'=> $data['catalogue_id'],
                //'catalogue_item_name'=>$data['catalogue_item_name'],
                'unit_price'=>!empty($data['unit_price'])?$data['unit_price']:null,
                'unit_type'=>isset($data['unit_type'])?$data['unit_type']:'',
                'quantity'=>!empty($data['quantity'])?$data['quantity']:null,
                //'business_unit_id'=>isset($data['business_unit_id'])?$data['business_unit_id']:'',
                'payment_periodicity_id' => isset($data['payment_periodicity'])?$data['payment_periodicity']:0,
                'period_start_date'=>!empty($data['period_start_date'])?$data['period_start_date']:null,
                'period_end_date'=>!empty($data['period_end_date'])?$data['period_end_date']:null,
                'calculated_total_item_spend'=>!empty($data['calculated_total_item_spend'])?$data['calculated_total_item_spend']:null,
                'calculated_total_item_spend_add_to_chart' => $data['calculated_total_item_spend_add_to_chart'],
                'manual_total_item_spend'=>!empty($data['manual_total_item_spend'])?$data['manual_total_item_spend']:null,
                'manual_total_item_spend_add_to_chart' => $data['manual_total_item_spend_add_to_chart'],
                'comment'=>isset($data['comments'])?$data['comments']:'',
                'created_by'=> $this->session_user_id,
                'created_on'=> currentDate());   
                // $this->User_model->insert_data('service_catalogue',$ins_data);
                //  echo $this->db->last_query();exit; 
            if($this->User_model->insert_data('service_catalogue',$ins_data))
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('service_catalogue_add_success'),'data'=>''), REST_Controller::HTTP_OK);
            else
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'2'), REST_Controller::HTTP_OK);
        }
    }
    public function service_catalogue_delete(){
        $data = $this->input->get();
        $msg='';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_service_catalogue', array('required'=>$this->lang->line('service_catalogue_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_service_catalogue'])) {
            $data['id_service_catalogue'] = pk_decrypt($data['id_service_catalogue']);
        }
        if(isset($data['id_service_catalogue'])){
            $upd_data = array(
                 'status'=>0,
                 'updated_by'=> $this->session_user_id,
                 'updated_on'=> currentDate(),
                );  
            if($this->User_model->update_data('service_catalogue',$upd_data,array('id_service_catalogue'=>$data['id_service_catalogue'])))
            {
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('service_catalogue_deleted_sucessfully'),'data'=>''), REST_Controller::HTTP_OK);
            }  
            else
            {
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
            }    
        }
    }

    function encriptCommaSeparatedValues($commaSeparatedValue){
        $encripted_ids = "";
        foreach(explode(",",$commaSeparatedValue) as $v){
            $encripted_ids .= pk_encrypt($v).",";
        }
        return explode(",",rtrim($encripted_ids,","));
    }
    public function service_catalogue_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!isset($data['id_service_catalogue']) )
        {
            $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        }
        

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        if(isset($data['id_contract'])) {
            $data['id_contract'] = pk_decrypt($data['id_contract']);
        }

        if(isset($data['id_service_catalogue'])) {
            $data['id_service_catalogue'] = pk_decrypt($data['id_service_catalogue']);
        } 
        $data = tableOptions($data);
        $ServiceCatalogList = $this->Contract_model->getServiceCatalogue($data);
        // echo $this->db->last_query();
        $result  = $ServiceCatalogList['data'];
        $count =$ServiceCatalogList['total_records'];
        foreach($result as $k =>$v)
        {
            $result[$k]['id_service_catalogue'] = pk_encrypt($v['id_service_catalogue']);
            $result[$k]['contract_id'] = pk_encrypt($v['contract_id']);
            $result[$k]['id_currency'] = pk_encrypt($v['id_currency']);
            $result[$k]['created_by'] = pk_encrypt($v['created_by']);
            $result[$k]['updated_by'] = pk_encrypt($v['updated_by']);
            $result[$k]['period_start_date'] = is_null($v['period_start_date'])?'':$v['period_start_date'];
            $result[$k]['period_end_date'] = is_null($v['period_end_date'])?'':$v['period_end_date'];            
            $result[$k]['payment_periodicity'] = pk_encrypt($v['payment_periodicity_id']);
            $result[$k]['catalogue_id'] = pk_encrypt($v['catalogue_id']);
            $result[$k]['comments'] = $v['comment'];
            unset($result[$k]['payment_periodicity_id']);
            unset($result[$k]['comment']);
            // $result[$k]["bu_name"] = [];
            // $result[$k]["business_unit_id"] = [];
            // if($v["business_unit_id"]>0){
            //     $bu_name=$this->Calender_model->getbunameswithcountryname(array('id_business_unit'=>explode(',',$v["business_unit_id"])));
            //     $result[$k]["bu_name"] = explode(',',$bu_name[0]['bu_name']);
            //     //passing comma separated string and returing encripted array
            //     $result[$k]["business_unit_id"] = $this->encriptCommaSeparatedValues($v["business_unit_id"]);
            // }
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result,'total_records'=>$count), REST_Controller::HTTP_OK);

    }
    public function payment_periodicity_get(){
        $result = $this->User_model->check_record('payment_periodicity',array('status'=>1,"id_payment_periodicity !="=>6));
        foreach($result as $k=>$v)
        {
            $result[$k]['id_payment_periodicity'] = pk_encrypt($v['id_payment_periodicity']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function evidences_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('spent_line_id', array('required'=>$this->lang->line('spent_line_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        } 
        if(isset($data['spent_line_id'])) {
            $data['spent_line_id'] = pk_decrypt($data['spent_line_id']);
        }
        //check_record_selected
        $spent_line_info = $this->User_model->check_record_selected(array('id','created_by','updated_by'),'spent_lines',array('id'=>$data['spent_line_id'],'status'=>1));
        foreach($spent_line_info as $k => $v){ 
            $spent_line_info[$k]['id'] = pk_encrypt($v['id']);
            $spent_line_info[$k]['created_by'] = pk_encrypt($v['created_by']);
            $spent_line_info[$k]['updated_by'] = pk_encrypt($v['updated_by']);
            $inner_data['reference_id']=$v['id'];
            $inner_data['reference_type']='spent_lines';
            $inner_data['document_status']=1;
            $inner_data['document_type'] = 0;
            $spent_line_info[$k]['unique_attachment']['documents'] = $this->Document_model->getDocumentsList($inner_data);
            $inner_data['document_type'] = array(0,1);
            $spent_line_info[$k]['unique_attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
            $inner_data['document_type'] = 1;
            $spent_line_info[$k]['unique_attachment']['links'] = $this->Document_model->getDocumentsList($inner_data);
            $spent_line_info[$k]['attachment_count'] = count($spent_line_info[$k]['unique_attachment']['all_records']);
            foreach($spent_line_info[$k]['unique_attachment']['all_records'] as $ka=>$va){
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['document_source_exactpath']=($va['document_source']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['id_document']=pk_encrypt($spent_line_info[$k]['unique_attachment']['all_records'][$ka]['id_document']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['module_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['all_records'][$ka]['module_id']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['reference_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['all_records'][$ka]['reference_id']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['uploaded_by']=pk_encrypt($spent_line_info[$k]['unique_attachment']['all_records'][$ka]['uploaded_by']);
                $spent_line_info[$k]['unique_attachment']['all_records'][$ka]['user_role_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['all_records'][$ka]['user_role_id']);
            }
            foreach($spent_line_info[$k]['unique_attachment']['documents'] as $ka=>$va){
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['id_document']=pk_encrypt($spent_line_info[$k]['unique_attachment']['documents'][$ka]['id_document']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['module_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['documents'][$ka]['module_id']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['reference_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['documents'][$ka]['reference_id']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($spent_line_info[$k]['unique_attachment']['documents'][$ka]['uploaded_by']);
                $spent_line_info[$k]['unique_attachment']['documents'][$ka]['user_role_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['documents'][$ka]['user_role_id']);
            }
            foreach($spent_line_info[$k]['unique_attachment']['links'] as $ka=>$va){
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['id_document']=pk_encrypt($spent_line_info[$k]['unique_attachment']['links'][$ka]['id_document']);
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['module_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['links'][$ka]['module_id']);
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['reference_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['links'][$ka]['reference_id']);
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['uploaded_by']=pk_encrypt($spent_line_info[$k]['unique_attachment']['links'][$ka]['uploaded_by']);
                $spent_line_info[$k]['unique_attachment']['links'][$ka]['user_role_id']=pk_encrypt($spent_line_info[$k]['unique_attachment']['links'][$ka]['user_role_id']);
            }
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$spent_line_info), REST_Controller::HTTP_OK);
    }
    public function recurrence_dropdown_get(){
        //$result = $this->User_model->check_record('payment_periodicity',array('status'=>1));
        $result = $this->Project_model->get_Record_order('payment_periodicity',array('status'=>1),"id_payment_periodicity","DESC");
        $new_array =[];
        foreach($result as $k=>$v)
        {
            $new_array[$k]['id'] =pk_encrypt($v['id_payment_periodicity']);
            $new_array[$k]['name'] =$v['payment_periodicity_name'];
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$new_array);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function resend_recurrence_dropdown_get(){
        $result = $this->Project_model->get_Record_order('payment_periodicity',array('status'=>1,"id_payment_periodicity !="=>6),"id_payment_periodicity","DESC");
        //$result = $this->User_model->check_record('payment_periodicity',array('status'=>1,"id_payment_periodicity !="=>6));
        $new_array =[];
        foreach($result as $k=>$v)
        {
            $new_array[$k]['id'] =pk_encrypt($v['id_payment_periodicity']);
            $new_array[$k]['name'] =$v['payment_periodicity_name'];
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$new_array);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    //obligations_and_rights
    public function createobligations_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('id_contract_req')));
        $this->form_validator->add_rules('description', array('required'=>$this->lang->line('description_req')));
        $this->form_validator->add_rules('type', array('required'=>$this->lang->line('type_req')));
        if(isset($data['email_notification'])&&($data['email_notification'] == 1))
        {
            $this->form_validator->add_rules('no_of_days', array('required'=>$this->lang->line('no_of_days_req')));
            $this->form_validator->add_rules('logic', array('required'=>$this->lang->line('logic_req')));
            $this->form_validator->add_rules('email_send_start_date', array('required'=>$this->lang->line('date_req')));
            $this->form_validator->add_rules('notification_message', array('required'=>$this->lang->line('notification_message_req')));
            $this->form_validator->add_rules('resend_recurrence_id', array('required'=>$this->lang->line('resend_recurrence_req')));
            if(isset($data['resend_recurrence_id'])&&(pk_decrypt($data['resend_recurrence_id'])!=5))
            {
                $this->form_validator->add_rules('email_send_last_date', array('required'=>$this->lang->line('email_send_last_date_req')));
            }
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // if(isset($data['email_notification'])&&($data['email_notification'] == 1))
        // {
        //     if ($data['email_send_start_date'] > $data['email_send_last_date']) 
        //     {
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('start_date_be_less_than_end_date')), 'data'=>'2');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'21');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['recurrence_id'])){
            $data['recurrence_id'] = pk_decrypt($data['recurrence_id']);
        }
        if((isset($data['recurrence_id'])) && ($data['recurrence_id'] == 6))
        {
            if($data['recurrence_start_date']!="") 
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_start_date_should_be_empty')), 'data'=>'22');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['recurrence_end_date']!="")
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_end_date_should_be_empty')), 'data'=>'23');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['calendar']==1)
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('calender_should_be_off')), 'data'=>'24');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if((isset($data['recurrence_id'])) && ($data['recurrence_id'] == 5))
        {
            if($data['recurrence_end_date']!="")
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_end_date_should_be_empty')), 'data'=>'25');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['calendar']==1)
            {
                if($data['recurrence_start_date']=="") 
                {
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_start_date_should_not_be_empty')), 'data'=>'26');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
        }
        if((isset($data['recurrence_id'])) && (($data['recurrence_id'] != 5)&&($data['recurrence_id'] != 6)))
        {
            if($data['calendar']==1)
            {
                if($data['recurrence_start_date']=="") 
                {
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_start_date_should_not_be_empty')), 'data'=>'27');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                if($data['recurrence_end_date']=="")
                {
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_end_date_should_not_be_empty')), 'data'=>'28');
                    $this->response($result, REST_Controller::HTTP_OK);
                } 
            }
        }
        if((isset($data['email_send_start_date'])) && (isset($data['email_send_last_date'])) && (!empty($data['email_send_start_date'])) && (!empty(($data['email_send_last_date']))))
        {
            if ($data['email_send_start_date'] > $data['email_send_last_date']) 
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('email_send_start_date_should_be_less_than_email_send_last_date')), 'data'=>'29');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if((isset($data['recurrence_start_date'])) && (isset($data['recurrence_end_date'])) && (!empty($data['recurrence_start_date'])) && (!empty(($data['recurrence_end_date']))))
        {
            if ($data['recurrence_start_date'] > $data['recurrence_end_date']) 
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('recurrence_start_date_should_be_less_than_recurrence_end_date')), 'data'=>'20');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['resend_recurrence_id']))
        {
            $data['resend_recurrence_id'] = pk_decrypt($data['resend_recurrence_id']);
        }
        $data['type_name'] = $data['type'] == 1?"Right":"Obligation";
        if($data['applicable_to']!="")
        {
            if($data['applicable_to']==0)
            {
                $data['applicable_to_name']  = "Customer";
            }
            elseif($data['applicable_to']==1)
            {
                //$data['applicable_to_name']  = "Provider";
                $data['applicable_to_name']  = "Relation";
            }
            elseif($data['applicable_to']==2)
            {
                $data['applicable_to_name']  = "Mutual";
            }
            else{
                $data['applicable_to_name']  = null;
            }
        }
        else{
            $data['applicable_to_name']  = null;
        }
        // exit;
        if(isset($data['id_document_fields']) && !empty($data['id_document_fields']))
        {
            $data['id_document_fields'] = pk_decrypt($data['id_document_fields']);
        }
        if(isset($data['id_obligation'])){
            $data['id_obligation'] = pk_decrypt($data['id_obligation']);
            $old_obligationdetails = $this->User_model->check_record("obligations_and_rights",array('id_obligation'=>$data['id_obligation']));
            $upd_data = array(
                'contract_id'=> $data['contract_id'],
                'description'=>$data['description'],
                'type'=>$data['type'],
                'type_name'=>$data['type_name'],
                'calendar'=>isset($data['calendar'])?$data['calendar']:0,
                //'applicable_to'=>isset($data['applicable_to'])?$data['applicable_to']:null,
                'applicable_to'=>(isset($data['applicable_to'])&&!empty($data['applicable_to']))?$data['applicable_to']:null,
                'applicable_to_name'=>$data['applicable_to_name'],
                'detailed_description'=>isset($data['detailed_description'])?$data['detailed_description']:'',
                'recurrence_id'=>isset($data['recurrence_id'])?$data['recurrence_id']:null,
                'recurrence_start_date'=>!empty($data['recurrence_start_date'])?$data['recurrence_start_date']:null,
                'recurrence_end_date'=>!empty($data['recurrence_end_date'])?$data['recurrence_end_date']:null,
                'no_of_days'=>isset($data['no_of_days'])?$data['no_of_days']:null,
                'logic'=>isset($data['logic'])?$data['logic']:null,
                'email_send_start_date'=>!empty($data['email_send_start_date'])?$data['email_send_start_date']:null,
                'email_send_last_date'=>!empty($data['email_send_last_date'])?$data['email_send_last_date']:null,
                'notification_message'=>isset($data['notification_message'])?$data['notification_message']:'',
                'email_notification'=>isset($data['email_notification'])?$data['email_notification']:0,
                'resend_recurrence_id'=>isset($data['resend_recurrence_id'])?$data['resend_recurrence_id']:null,
                'updated_by'=> $this->session_user_id,
                'updated_on'=> currentDate(),
                );
            $Recordchecking =   $upd_data;
            unset($Recordchecking['updated_by']);
            unset($Recordchecking['updated_on']);
            $Recordchecking['id_obligation'] =$data['id_obligation'];
            $existingRecord = $this->User_model->check_record("obligations_and_rights",$Recordchecking);
            //no change in update date we are not deleting the duplicate records
            if(empty($existingRecord))
            {
                $this->db->where(array('parent_obligation_id'=>$data['id_obligation']))->delete('obligations_and_rights');
                
            }
            $createNewRecord = false;
            //if their any cahange in resend recurrence id,email start date ,email last date and email notifiaction deleting old records
            if(($old_obligationdetails[0]['resend_recurrence_id']!= $data['resend_recurrence_id'])||($old_obligationdetails[0]['email_send_start_date']!= $data['email_send_start_date'])||($old_obligationdetails[0]['email_send_last_date']!= $data['email_send_last_date'])||($old_obligationdetails[0]['email_notification']!= $data['email_notification'])||($old_obligationdetails[0]['no_of_days']!= $data['no_of_days'])||($old_obligationdetails[0]['logic']!= $data['logic']))
            {
                
                $createNewRecord = true;
                $this->db->where(array('obligation_id'=>$data['id_obligation']))->delete('obligations_and_rights_mail');
                
            }
            if($this->User_model->update_data('obligations_and_rights',$upd_data,array('id_obligation'=>$data['id_obligation'])))
            {
                if(empty($existingRecord))
                {
                    $upd_data['parent_obligation_id'] =$data['id_obligation'];
                    unset($upd_data['updated_by']);
                    unset($upd_data['updated_on']);
                    $upd_data['created_by']=$this->session_user_id;
                    $upd_data['created_on']=currentDate();
                    if((in_array($data['recurrence_id'],array(1,2,3,4))) && ($data['calendar'] == 1))
                    {     
                        $this->creatingRecurrenceEntry($upd_data);           
                    }
                    if($createNewRecord && ($upd_data['email_notification'] == 1))
                    {
                        $upd_data['obligation_id'] =$data['id_obligation'];
                        $this->creatingmailerEntry($upd_data);
                    }
                }
                $message = $this->lang->line('obligation_and_right_updated_success');
                $this->response(array('status'=>TRUE,'message'=>$message,'data'=>''), REST_Controller::HTTP_OK);
            }
            else
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
        }
        else{
            $ins_data = array(
                'contract_id'=> $data['contract_id'],
                'description'=>$data['description'],
                'type'=>$data['type'],
                'type_name'=>$data['type_name'],
                'calendar'=>isset($data['calendar'])?$data['calendar']:0,
                'applicable_to'=>isset($data['applicable_to'])?$data['applicable_to']:null,
                'applicable_to'=>(isset($data['applicable_to'])&&!empty($data['applicable_to']))?$data['applicable_to']:null,
                'applicable_to_name'=>$data['applicable_to_name'],
                'detailed_description'=>isset($data['detailed_description'])?$data['detailed_description']:'',
                'recurrence_id'=>isset($data['recurrence_id'])?$data['recurrence_id']:null,
                'recurrence_start_date'=>!empty($data['recurrence_start_date'])?$data['recurrence_start_date']:null,
                'recurrence_end_date'=>!empty($data['recurrence_end_date'])?$data['recurrence_end_date']:null,
                'no_of_days'=>isset($data['no_of_days'])?$data['no_of_days']:null,
                'logic'=>isset($data['logic'])?$data['logic']:null,
                'email_send_start_date'=>!empty($data['email_send_start_date'])?$data['email_send_start_date']:null,
                'email_send_last_date'=>!empty($data['email_send_last_date'])?$data['email_send_last_date']:null,
                'notification_message'=>isset($data['notification_message'])?$data['notification_message']:'',
                'email_notification'=>isset($data['email_notification'])?$data['email_notification']:0,
                'resend_recurrence_id'=>isset($data['resend_recurrence_id'])?$data['resend_recurrence_id']:null,
                'created_by'=> $this->session_user_id,
                'created_on'=> currentDate()
            );
            $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
            if($obligation_id)
            {
                //creating duplicate enters for recurrence in calendar
                if((in_array($data['recurrence_id'],array(1,2,3,4)))&&($data['calendar'] == 1))
                {     
                    $ins_data['parent_obligation_id'] =$obligation_id;
                    $this->creatingRecurrenceEntry($ins_data);           
                }
                //creating records for mail sending 
                if($ins_data['email_notification'] == 1)
                {
                    $ins_data['obligation_id'] =$obligation_id;
                    $this->creatingmailerEntry($ins_data);        
                }
                if(isset($data['id_document_fields']) && !empty($data['id_document_fields']))
                {
                    $this->User_model->update_data('document_fields',array('is_moved'=>1),array('id_document_fields'=>$data['id_document_fields']));
                }
                $message = $this->lang->line('obligation_and_right_added_success');
                $this->response(array('status'=>TRUE,'message'=>$message,'data'=>''), REST_Controller::HTTP_OK);
            }  
            else
            {
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'2'), REST_Controller::HTTP_OK);
            }
               
        }
    }
    public function deleteobligations_delete(){
        $data = $this->input->get();
        $msg='';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_obligation', array('required'=>$this->lang->line('obligation_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // if(isset($data['id_obligation'])) {
        //     $data['id_obligation'] = pk_decrypt($data['id_obligation']);
        // }
        if(isset($data['id_obligation'])) {
            $data['id_obligation'] = pk_decrypt($data['id_obligation']);
            $oblicationData = $this->User_model->check_record("obligations_and_rights",array("id_obligation"=>$data['id_obligation']));
            if(!empty($oblicationData[0]))
            {
                if($oblicationData[0]['parent_obligation_id']>0)
                {
                    $data['id_obligation'] =$oblicationData[0]['parent_obligation_id'];
                }
            }
        } 
        if(isset($data['id_obligation'])){
            $upd_data = array(
                 'status'=>0,
                 'updated_by'=> $this->session_user_id,
                 'updated_on'=> currentDate(),
                ); 
            $recurrencerecords = $this->User_model->check_record("obligations_and_rights",array("parent_obligation_id"=>$data['id_obligation']));
            if(!empty($recurrencerecords))
            {
                foreach ($recurrencerecords as $record) {
                    $re_upd_data = array(
                        'status'=>0,
                        'updated_by'=> $this->session_user_id,
                        'updated_on'=> currentDate(),
                       ); 
                    $this->User_model->update_data('obligations_and_rights',$re_upd_data,array('id_obligation'=>$record['id_obligation']));
                }
            }
            //deleting obligations_and_rights_mail mails records
            $this->db->where(array('obligation_id'=>$data['id_obligation']))->delete('obligations_and_rights_mail');
            if($this->User_model->update_data('obligations_and_rights',$upd_data,array('id_obligation'=>$data['id_obligation'])))
            {
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('deleted_sucessfully'),'data'=>''), REST_Controller::HTTP_OK);
            }  
            else
            {
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
            }    
        }
    }
    public function getobligations_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if((!isset($data['id_obligation']))&&(!isset($data['customer_id'])))
        {
            $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        }
        

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }   
        }

        if(isset($data['id_obligation'])) {
            $data['id_obligation'] = pk_decrypt($data['id_obligation']);
            $oblicationData = $this->User_model->check_record("obligations_and_rights",array("id_obligation"=>$data['id_obligation']));
            if(!empty($oblicationData[0]))
            {
                if($oblicationData[0]['parent_obligation_id']>0)
                {
                    $data['id_obligation'] =$oblicationData[0]['parent_obligation_id'];
                }
            }
        }
        if(isset($data['id_contract'])) {
            $data['id_contract'] = pk_decrypt($data['id_contract']);
            $data['get_parent'] = true;
        }
        $data = tableOptions($data);
        if($this->session_user_info->user_role_id==8){
            $data['business_units']=$this->session_user_business_units;
        }
        $obligationslist = $this->Project_model->getObligations($data);
        $result  = $obligationslist['data'];
        $count =$obligationslist['total_records'];
        foreach($result as $k =>$v)
        {
            $result[$k]['id_obligation'] = pk_encrypt($v['id_obligation']);
            $result[$k]['contract_id'] = pk_encrypt($v['contract_id']);
            $result[$k]['created_by'] = pk_encrypt($v['created_by']);
            $result[$k]['updated_by'] = pk_encrypt($v['updated_by']);
            $result[$k]['calendar'] = (int)$v['calendar'];
            $result[$k]['email_notification'] = (int)$v['email_notification'];           
            $result[$k]['recurrence_id'] = pk_encrypt($v['recurrence_id']);
            $result[$k]['resend_recurrence_id'] = pk_encrypt($v['resend_recurrence_id']);
            $result[$k]['obligation_access'] = 0; 
            if((in_array($this->session_user_id,array($v['delegate_id'],$v['contract_owner_id'])))||((int)$this->session_user_info->user_role_id==2))
            {
                $result[$k]['obligation_access'] = 1;
            }

            //for getting recurrence_start_date of parent obligation
            if((isset($data['calendar']))&&($data['calendar']==1))
            {
                if(!empty($v['parent_obligation_id']))
                {
                    $parentObligation = $this->User_model->check_record("obligations_and_rights",array("id_obligation"=>$v['parent_obligation_id']));
                    if(!empty($parentObligation[0]))
                    {
                        $result[$k]['recurrence_start_date'] = is_null($parentObligation[0]['recurrence_start_date'])?'':$parentObligation[0]['recurrence_start_date'];
                    }
                }
                else{
                    $result[$k]['recurrence_start_date'] = is_null($v['recurrence_start_date'])?'':$v['recurrence_start_date'];
                }
            }
            else{
                $result[$k]['recurrence_start_date'] = is_null($v['recurrence_start_date'])?'':$v['recurrence_start_date'];
            }
            //$result[$k]['recurrence_start_date'] = is_null($v['recurrence_start_date'])?'':$v['recurrence_start_date'];
            $result[$k]['recurrence_end_date'] = is_null($v['recurrence_end_date'])?'':$v['recurrence_end_date'];  
            $result[$k]['email_send_start_date'] = is_null($v['email_send_start_date'])?'':$v['email_send_start_date'];
            $result[$k]['email_send_last_date'] = is_null($v['email_send_last_date'])?'':$v['email_send_last_date'];
            $result[$k]['parent_obligation_id'] = pk_encrypt($v['parent_obligation_id']);         
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result,'total_records'=>$count), REST_Controller::HTTP_OK);
    }
    function creatingRecurrenceEntry($data){
        //writing recurrence of months. montly or quarterly or yearly or semi - annually
        $countOfMonths = 0;
        $recurrence = $data["recurrence_id"];
        //if recurrence value grater than zero then only 
        if($recurrence>0){
                switch($recurrence){
                    case '1':
                        $countOfMonths = 12;//yearly
                        break;
                    case '2':
                        $countOfMonths = 6;//semi - annually
                        break;
                    case '3':
                        $countOfMonths = 3;//quartely
                        break;
                    case '4':
                        $countOfMonths = 1;//monthly  
                }
                /*Counting Dates Between two dates starts*/
                $date1 =  $data["recurrence_start_date"];  
                $date2 = $data["recurrence_end_date"];                
                $year = (int)substr($date1,0,4);
                $month = (int)substr($date1, 5, 2);
                $date = substr($date1, 8, 2);                
                $new_dates = array();
                for($i = 0;$i>=0;$i++){
                    if($month<10)
                        $month = '0'.$month;
                        $new_date = $year.'-'.$month.'-'.$date;
                    if(validateDate($new_date)>0){		
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }
                    else{
                        $date_test = new DateTime($year.'-'.$month.'-01');
                        $date_test->modify('last day of this month');
                            $new_date = $date_test->format('Y-m-d');
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }	
                    if($month > 12){		
                        $month = 0;
                        $year++;
                    }	
                    $month += $countOfMonths;	
                    if($month > 12){
                        $month = $month-12;
                        $year++;
                    }    
                }
            if(count($new_dates)>1){
                foreach ($new_dates as $dt=>$dv) {
                    if($dt == 0)
                    {
                        continue;
                    }
                    else{
                        $insert_data[] = 
                        array(
                            'contract_id'=> $data['contract_id'],
                            'description'=>$data['description'],
                            'type'=>$data['type'],
                            'type_name'=>$data['type_name'],
                            'applicable_to'=>isset($data['applicable_to'])?$data['applicable_to']:null,
                            'calendar'=>isset($data['calendar'])?$data['calendar']:0,
                            'applicable_to_name'=>$data['applicable_to_name'],
                            'detailed_description'=>isset($data['detailed_description'])?$data['detailed_description']:'',
                            'recurrence_id'=>isset($data['recurrence_id'])?$data['recurrence_id']:null,
                            'recurrence_start_date'=>$dv,
                            'recurrence_end_date'=>!empty($data['recurrence_end_date'])?$data['recurrence_end_date']:null,
                            'no_of_days'=>isset($data['no_of_days'])?$data['no_of_days']:null,
                            'logic'=>isset($data['logic'])?$data['logic']:null,
                            'email_send_start_date'=>!empty($data['email_send_start_date'])?$data['email_send_start_date']:null,
                            'email_send_last_date'=>!empty($data['email_send_last_date'])?$data['email_send_last_date']:null,
                            'notification_message'=>isset($data['notification_message'])?$data['notification_message']:'',
                            'email_notification'=>isset($data['email_notification'])?$data['email_notification']:0,
                            'resend_recurrence_id'=>isset($data['resend_recurrence_id'])?$data['resend_recurrence_id']:null,
                            'parent_obligation_id'=>$data['parent_obligation_id'],
                            'created_by'=> $this->session_user_id,
                            'created_on'=> currentDate()
                        );
                    }
                }
                //print_r($insert_data);
                $this->User_model->batch_insert('obligations_and_rights',$insert_data);
            }
        }
    }
    public function contractInfoTabs_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_contract'])) {
            $data['id_contract'] = pk_decrypt($data['id_contract']);
            $data['contract_id'] = $data['id_contract'];
            $data['get_parent'] = true;
            $data['parent_contract_id'] = $data['id_contract'];
            if($this->session_user_info->user_role_id == 4){
                $data['delegate_id'] = $this->session_user_id;
            }
        }
        if(!empty($data['action_item_type']) && $data['action_item_type']=='outside' && !empty($data['contract_id'])){
            $data['reference_type']=array('contract','question','topic');
        }
        if(!empty($data['action_item_type']) && $data['action_item_type']=='outside' && !empty($data['provider_id'])){
            $data['reference_type']=array('provider');
        }
        if(!empty($data['action_item_type']) && $data['action_item_type']=='inside' && !empty($data['contract_id'])){
            $data['reference_type']=array('topic','question');
            if(isset($data['is_workflow']) && $data['is_workflow']==1 ){
                unset($data['contract_review_id']);
            }
        }
        if(!empty($data['action_item_type']) && $data['action_item_type']=='inside' && !empty($data['module_id']) && !empty($data['module_id']) && !empty($data['topic_id'])){
            $data['reference_type']=array('topic','question');
        }
        if($data['type']=='project_actionitems')
        {
            $data['reference_type']=array('project','topic','question');
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['user_role_id']==7){
                $provider_colleuges = $this->User_model->check_record('user',array('provider'=>$this->session_user_info->provider));
                $provider_colleuges = array_map(function($i){ return $i['id_user']; },$provider_colleuges);
                $data['provider_colleuges'] = $provider_colleuges;
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data);
        $data['item_status']=1;
        if(in_array($this->session_user_info->user_role_id,array(3,4,8))){
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
            $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            $data['session_user_role']=$this->session_user_info->user_role_id;
            $data['session_user_id']=$this->session_user_id;
        }
        $subAgremment = $this->Contract_model->getAllContractList($data);
        $reviewActionItems = $this->Contract_model->getContractReviewActionItems($data);
        $result[] = array("heading"=>$this->lang->line('contract_information'),"content"=>"contract-information.html");
        $result[] =  array("heading"=>$this->lang->line('contract_tags'),"content"=>"contract-tags.html");
        $result[] = array("heading"=>$this->lang->line('action_items')." (".($reviewActionItems['total_records']).")","content"=>"action-items-list.html");
        $obligationslist = $this->Project_model->getObligations($data);
        $result[] =  array("heading"=>$this->lang->line('obligations_rights')." (".($obligationslist['total_records']).")","content"=>"obligations-list.html");
        $ServiceCatalogList = $this->Contract_model->getServiceCatalogue($data);
        $result[] =  array("heading"=>$this->lang->line('service_catalogue')." (".($ServiceCatalogList['total_records']).")","content"=>"service-catalogue-list.html");
        $eventFeedsList = $this->Project_model->getEventFeeds(array('reference_type'=>'contract','reference_id'=>$data['id_contract']));
        $result[] =  array("heading"=>$this->lang->line('contract_event_feed')." (".($eventFeedsList['total_records']).")","content"=>"event-feed-list.html");
        $contractDetails = $this->User_model->check_record("contract",array("id_contract"=>$data['id_contract']));
        if(!empty($contractDetails[0])&&($contractDetails[0]['parent_contract_id'] == 0)&&(!$this->session_user_info->access!="eu"))
        {
            $result[] =  array("heading"=>$this->lang->line('sub_agreements')." (".($subAgremment['total_records']).")","content"=>"sub-aggrements.html");
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result), REST_Controller::HTTP_OK);
    }
    function creatingmailerEntry($data){
        //writing recurrence of months. montly or quarterly or yearly or semi - annually
        $countOfMonths = 0;
        $recurrence = $data["resend_recurrence_id"];
        //if recurrence value grater than zero then only 
        if($recurrence>0){
            if($recurrence!=5)
            {
                switch($recurrence){
                    case '1':
                        $countOfMonths = 12;//yearly
                        break;
                    case '2':
                        $countOfMonths = 6;//semi - annually
                        break;
                    case '3':
                        $countOfMonths = 3;//quartely
                        break;
                    case '4':
                        $countOfMonths = 1;//monthly  
                }
                /*Counting Dates Between two dates starts*/
                $no_of_days =$data["no_of_days"];
                $date = new DateTime($data["email_send_start_date"]);
                if($data["logic"] == "0")
                {
                    $date->modify("-$no_of_days day");
                }
                else{
                    $date->modify("+$no_of_days day");
                }
                $date1 =  $date->format("Y-m-d");
                $date2 = $data["email_send_last_date"];                
                $year = (int)substr($date1,0,4);
                $month = (int)substr($date1, 5, 2);
                $date = substr($date1, 8, 2);                
                $new_dates = array();
                for($i = 0;$i>=0;$i++){
                    if($month<10)
                        $month = '0'.$month;
                        $new_date = $year.'-'.$month.'-'.$date;
                    if(validateDate($new_date)>0){		
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }
                    else{
                        $date_test = new DateTime($year.'-'.$month.'-01');
                        $date_test->modify('last day of this month');
                            $new_date = $date_test->format('Y-m-d');
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }	
                    if($month > 12){		
                        $month = 0;
                        $year++;
                    }	
                    $month += $countOfMonths;	
                    if($month > 12){
                        $month = $month-12;
                        $year++;
                    }    
                }
            }
            elseif($recurrence== 5)
            {
                $no_of_days =$data["no_of_days"];
                $date = new DateTime($data["email_send_start_date"]);
                if($data["logic"] == "0")
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
                foreach ($new_dates as $dt=>$dv) {
                    $insert_data[] = 
                    array(
                        'contract_id'=> $data['contract_id'],
                        'obligation_id'=>$data['obligation_id'],
                        'date'=>$dv,
                        'status'=>1,
                        'mail_status'=>0,
                        'created_by'=> $this->session_user_id,
                        'created_on'=> currentDate()); 
                }
                //'status'=>$data['email_notification'],
                $this->User_model->batch_insert('obligations_and_rights_mail',$insert_data);
            }
        }
    }
    //this function is for getting contract , projects list fastly
    public function allContractsandProjects_get()
    {
        $data = $this->input->get();

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all') {
            $data['id_business_unit'] = pk_decrypt($data['business_unit_id']);
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if($this->session_user_info->user_role_id != 7)
            if(!in_array($data['id_business_unit'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            if(!in_array($data['delegate_id'],$this->session_user_delegates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['relationship_category_id'])) {
            $data['relationship_category_id'] = pk_decrypt($data['relationship_category_id']);
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if(!in_array($data['contract_owner_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(in_array($this->session_user_info->user_role_id,array(3,4))){
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
            $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            $data['session_user_role']=$this->session_user_info->user_role_id;
            $data['session_user_id']=$this->session_user_id;
        }
        if($this->session_user_info->user_role_id==6){
            $data['business_unit_id'] = $this->session_user_business_units;
            if(count($data['business_unit_id'])==0 && $this->session_user_info->is_allow_all_bu==0)
            {
                $data['business_unit_id']=array(0);
            }
        }
        if($this->session_user_info->user_role_id == 7){
            $data['provider_id'] = $this->session_user_info->provider;
        }
        
        if(isset($data['parent_contract_id'])) {
            $data['parent_contract_id'] = pk_decrypt($data['parent_contract_id']);
            if($this->session_user_info->user_role_id == 4){
                $data['delegate_id'] = $this->session_user_id;
            }
        }
        if(count($data['business_unit_id'])==0)
            unset($data['business_unit_id']);
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        if(strlen($data['advancedsearch_get'])>2) 
            $data['advancedsearch_get']=json_decode($data['advancedsearch_get']);
        else
            $data['advancedsearch_get']=false;

        if( $data['type'] == 'project')
        {
            if(count($data['business_unit_id'])==0)
            unset($data['business_unit_id']);
            if(isset($data['can_access'])  && $data['can_access']==1){
                $data['project_status']=1;
            }
            else{
                $data['project_status']=0;
            }
        }
        
        $data['get_all_records'] =true;
        $result = $this->Contract_model->getAllContractList($data);
        for($s=0;$s<count($result['data']);$s++) 
        {
            //due to union query we wear getting error with same column name. that's why we are managing one column without changing old code.
            $result['data'][$s]['provider_name'] = $result['data'][$s]['providerName'];
            unset($result['data'][$s]['providerName']);
            if(strlen($result['data'][$s]['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($result['data'][$s]['relationship_category_name'])), $matches);
                $result['data'][$s]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result['data'][$s]['relationship_category_short_name'] = $result['data'][$s]['relationship_category_name'];
            }
            $result['data'][$s]['business_unit_id']=pk_encrypt($result['data'][$s]['business_unit_id']);
            $result['data'][$s]['classification_id']=pk_encrypt($result['data'][$s]['classification_id']);
            $result['data'][$s]['contract_owner_id']=pk_encrypt($result['data'][$s]['contract_owner_id']);
            $result['data'][$s]['created_by']=pk_encrypt($result['data'][$s]['created_by']);
            $result['data'][$s]['currency_id']=pk_encrypt($result['data'][$s]['currency_id']);
            $result['data'][$s]['id_contract']=pk_encrypt($result['data'][$s]['id_contract']);
            $result['data'][$s]['contract_id']=pk_encrypt($result['data'][$s]['contract_id']);
            $result['data'][$s]['relationship_category_id']=pk_encrypt($result['data'][$s]['relationship_category_id']);
            $result['data'][$s]['updated_by']=pk_encrypt($result['data'][$s]['updated_by']);
            if($result['data'][$s]['id_contract_review'] == '0')//Encrypting if value is not = '0' 
                $result['data'][$s]['id_contract_review'] = null;
            else
                $result['data'][$s]['id_contract_review']=pk_encrypt($result['data'][$s]['id_contract_review']);
            $result['data'][$s]['parent_contract_id']=pk_encrypt($result['data'][$s]['parent_contract_id']);

            $result['data'][$s]['template_id']=pk_encrypt($result['data'][$s]['template_id']);
            $result['data'][$s]['id_template']=pk_encrypt($result['data'][$s]['id_template']);
            $result['data'][$s]['workflow_id']=pk_encrypt($result['data'][$s]['workflow_id']);
            $result['data'][$s]['id_contract_workflow']=pk_encrypt($result['data'][$s]['id_contract_workflow']);
            
            if((int)$result['data'][$s]['can_review'] == 0 && (int)$result['data'][$s]['is_workflow']==0){
                $result['data'][$s]['is_workflow'] = null;
                $result['data'][$s]['review_name'] = null;
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function ReviewActionItemadd_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(empty($data['provider_id']))
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        if(empty($data['contract_id']))
        $this->form_validator->add_rules('provider_id', array('required'=>$this->lang->line('provider_id_req')));
        $this->form_validator->add_rules('action_item', array('required'=>$this->lang->line('action_item_req')));
        $this->form_validator->add_rules('responsible_user_id', array('required'=>$this->lang->line('responsible_user_id_req')));
        $this->form_validator->add_rules('due_date', array('required'=>$this->lang->line('due_date_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['contract_id']) && count(explode(',',$data['contract_id']))>0){
            $contract_id_exp=explode(',',$data['contract_id']);
            $contract_ids=array();
            foreach($contract_id_exp as $k=>$v){
                $contract_ids[]=pk_decrypt($v);
            }
            
            $data['contract_id']=implode(',',$contract_ids);
        }
        if(isset($data['provider_id']) && count(explode(',',$data['provider_id']))>0){
            $provider_id_exp=explode(',',$data['provider_id']);
            $provider_ids=array();
            foreach($provider_id_exp as $k=>$v){
                $provider_ids[]=pk_decrypt($v);
            }
            $data['provider_id']=implode(',',$provider_ids);
        }
        
        
        // if(isset($data['contract_id'])) {
        //     $data['contract_id'] = pk_decrypt($data['contract_id']);
        // }
        // if(!empty($data['provider_id'])){
        //     $data['provider_id']=pk_decrypt($data['provider_id']);
        // }
        // if(isset($data['contract_review_id'])){
        //     $data['contract_review_id']=pk_decrypt($data['contract_review_id']);
        // }
        // if(isset($data['module_id'])) {
        //     $data['module_id'] = pk_decrypt($data['module_id']);
        // }
        // if(isset($data['topic_id'])) {
        //     $data['topic_id'] = pk_decrypt($data['topic_id']);
        // }
        // if(isset($data['question_id'])) {
        //     $data['question_id'] = pk_decrypt($data['question_id']);
        // }
        if(isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt($data['responsible_user_id']);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        // if(isset($data['id_contract_review_action_item'])) {
        //     $data['id_contract_review_action_item'] = pk_decrypt($data['id_contract_review_action_item']);
        // }
        if(isset($data['contract_workflow_id'])) {
            $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
        }
        //Separation from validator attachments with a flag
        $validation_status = 0; 
        if($this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$data['contract_review_id'],'id_user'=>$this->session_user_info->id_user))>0){
            if($this->session_user_info->contribution_type==1)
                $validation_status = 1;
        }
        $is_workflow = 0;
        // if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
        //     $is_workflow = 1;
        if($data['reference_type']  == "project" || $data['reference_type'] == "contract")
        {
            //unset($provider_ids);
            $provider_ids =[];
            unset($data['provider_id']);
        }
        elseif($data['reference_type']  == "provider")
        {
            //unset($contract_ids);
            $contract_ids =[];
            unset($data['contract_id']);
        }
        $contractAndProviderIds = array_merge($contract_ids,$provider_ids);
        foreach($contractAndProviderIds as $contractAndProviderId)
        {
            $contractId =null;
            $providerId= null;
            if($data['reference_type']  == "project" || $data['reference_type'] == "contract")
            {
                $contractId = $contractAndProviderId;
            }
            elseif($data['reference_type']  == "provider")
            {
                $providerId = $contractAndProviderId;
            }
            $update = array(
                'contract_id' => $contractId,
                'action_item' => $data['action_item'],
                'responsible_user_id' => $data['responsible_user_id'],
                'external_users' => isset($data['external_users'])?$data['external_users']:'',
                'due_date' => explode('T',$data['due_date'])[0],
                'contract_review_id' => isset($data['contract_review_id'])?$data['contract_review_id']:0,
                'module_id' => isset($data['module_id'])?$data['module_id']:0,
                'topic_id' => isset($data['topic_id'])?$data['topic_id']:0,
                'question_id' => isset($data['question_id'])?$data['question_id']:0,
                'is_workflow' => $is_workflow,
                'validator_record' => $validation_status,
                'priority' => $data['priority']
            );
            if(isset($data['contract_workflow_id']) && $data['contract_workflow_id'] > 0)
                $update['contract_workflow_id'] = $data['contract_workflow_id'];
            if(isset($data['comments']))
                $update['comments']=$data['comments'];
            if(isset($data['description']))
                $update['description']=$data['description'];
    
            if(!isset($data['id_contract_review_action_item'])){
                $update['original_date'] = explode('T',$data['due_date'])[0];
                $update['created_by'] = $data['created_by'];
                $update['created_on'] = currentDate();
                $update['provider_id'] = $providerId;
                $update['reference_type'] = isset($data['reference_type'])?$data['reference_type']:'';
                $this->Contract_model->addContractReviewActionItem($update);
                //echo '<pre>'.$this->db->last_query();exit;
                $msg = $this->lang->line('contract_review_action_item_add');
            }
            if(!isset($data['id_contract_review_action_item'])) {
                $module_info = $this->Module_model->getModuleName(array('language_id' => 1, 'module_id' => $data['module_id']));
                $contract_info = $this->Contract_model->getContractDetails(array('id_contract' => $contractId));
                $topic_info = $this->Topic_model->getTopicName(array('topic_id' => $data['topic_id']));
                $cust_admin_info = $this->User_model->getUserInfo(array('user_id' => $contract_info[0]['created_by']));
                $created_user_info = $this->User_model->getUserInfo(array('user_id' => $data['created_by']));
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $cust_admin_info->customer_id));
            
               
                if ($customer_details[0]['company_logo'] == '') {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                } else {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                }
                if (!empty($customer_details)) {
                    $customer_name = $customer_details[0]['company_name'];
                }
    
                $To = $this->User_model->getUserInfo(array('user_id' => $data['responsible_user_id'],'user_status'=>1));
                if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                    $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'CONTRACT_WORKFLOW_ACTION_ITEM_CREATION'));
                }else{
                    $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'CONTRACT_REVIEW_ACTION_ITEM_CREATION'));
                }
                $check_type=$this->User_model->check_record('contract',array('id_contract'=>$contractId));
                if($check_type[0]['type'] =='project'){
                    $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'PROJECT_WORKFLOW_ACTION_ITEM_CREATION'));
                    // echo $this->db->last_query();exit;
                    if ($template_configurations_parent['total_records'] > 0 && !empty($To)) {
                        $template_configurations = $template_configurations_parent['data'][0];
                        $wildcards = $template_configurations['wildcards'];
                        $wildcards_replaces = array();
                        $wildcards_replaces['first_name'] = $To->first_name;
                        $wildcards_replaces['last_name'] = $To->last_name;
                        $wildcards_replaces['project_name'] = $contract_info[0]['contract_name'];
                        $wildcards_replaces['action_item_responsible_user'] = $To->first_name . ' ' . $To->last_name . ' (' . $To->user_role_name . ')';
                        $wildcards_replaces['action_item_name'] = $data['action_item'];
                        $wildcards_replaces['action_item_description'] ='';
                        if (isset($data['description']))
                        $wildcards_replaces['action_item_description'] = $data['description'];
                        //$wildcards_replaces['action_item_due_date'] = dateFormat($data['due_date']);
                        $wildcards_replaces['action_item_due_date'] = explode('T',$data['due_date'])[0];
                        $wildcards_replaces['project_task_topic_name'] = '';
                        $wildcards_replaces['project_task_module_name'] ='';
                        if($data['module_id'] && $data['module_id']>0){
                           $wildcards_replaces['project_task_topic_name'] = isset($topic_info[0]['topic_name'])?$topic_info[0]['topic_name']:'';
                           $wildcards_replaces['project_task_module_name'] = isset($module_info[0]['module_name'])?$module_info[0]['module_name']:'';
                            
                        }
                        
                        $wildcards_replaces['action_item_created_user_name'] = $created_user_info->first_name . ' ' . $created_user_info->last_name . ' (' . $created_user_info->user_role_name . ')';
                        $wildcards_replaces['action_item_created_date'] = dateFormat($update['created_on']);
                        $wildcards_replaces['logo'] = $customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                        $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                        $from_name = $template_configurations['email_from_name'];
                        $from = $template_configurations['email_from'];
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
                        $mailer_data['is_cron'] = 1;
                        $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                        $mailer_id = $this->Customer_model->addMailer($mailer_data);
                        // print_r($mailer_data);exit;
                        // if ($mailer_data['is_cron'] == 0) {
                        //     $this->load->library('sendgridlibrary');
                        //     $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                        //     if ($mail_sent_status == 1)
                        //         $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                        // }
        
                    }
    
                    if(isset($data['external_users']) && count(explode(',', $data['external_users']))>0){  
                        if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                            $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'PROJECT_TASK_ACTION_ITEM_CREATION_EXTERNAL_USER'));
                        }
                        if ($template_configurations_parent['total_records'] > 0) {
                            $template_configurations = $template_configurations_parent['data'][0];
                            $wildcards = $template_configurations['wildcards'];
                            $wildcards_replaces = array();
                            $external_users = explode(',', $data['external_users']);
                            foreach($external_users as $v){
                                if(!empty($v))
                                {
                                    $wildcards_replaces['first_name'] = $v;
                                    $wildcards_replaces['project_name'] = $contract_info[0]['contract_name'];
                                    $wildcards_replaces['action_item_responsible_user'] = $To->first_name . ' ' . $To->last_name . ' (' . $To->user_role_name . ')';
                                    // $wildcards_replaces['contract_review_module_name'] = $module_info[0]['module_name'];
                                    $wildcards_replaces['action_item_name'] = $data['action_item'];
                                    $wildcards_replaces['action_item_description'] = '';
                                    if (isset($data['description']))
                                        $wildcards_replaces['action_item_description'] = $data['description'];
                                    //$wildcards_replaces['action_item_due_date'] = dateFormat($data['due_date']);
                                    $wildcards_replaces['action_item_due_date'] = explode('T',$data['due_date'])[0];
                                    // $wildcards_replaces['contract_review_topic_name'] = $topic_info[0]['topic_name'];
                                    $wildcards_replaces['project_task_topic_name'] = '';
                                    $wildcards_replaces['project_task_module_name'] = '';
                                    if($data['module_id'] && $data['module_id']>0){
                                        if($data['is_workflow']==1){
                                            $wildcards_replaces['project_task_topic_name'] = $topic_info[0]['topic_name'];
                                            $wildcards_replaces['project_task_module_name'] = $module_info[0]['module_name'];
                                        }
                                    }
                                    $wildcards_replaces['action_item_created_user_name'] = $created_user_info->first_name . ' ' . $created_user_info->last_name . ' (' . $created_user_info->user_role_name . ')';
                                    $wildcards_replaces['action_item_created_date'] = dateFormat($update['created_on']);
                                    $wildcards_replaces['logo'] = $customer_logo;
                                    $wildcards_replaces['year'] = date("Y");
                                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                                    $from_name = $template_configurations['email_from_name'];
                                    $from = $template_configurations['email_from'];
                                    $to = $v;
                                    $mailer_data['mail_from_name'] = $from_name;
                                    $mailer_data['mail_to_name'] = '';
                                    $mailer_data['mail_to_user_id'] = 0;
                                    $mailer_data['mail_from'] = $from;
                                    $mailer_data['mail_to'] = $to;
                                    $mailer_data['mail_subject'] = $subject;
                                    $mailer_data['mail_message'] = $body;
                                    $mailer_data['status'] = 0;
                                    $mailer_data['send_date'] = currentDate();
                                    $mailer_data['is_cron'] = 1;
                                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                                    //sending mail to bu owner
                                    // if ($mailer_data['is_cron'] == 0) {
                                    //     $this->load->library('sendgridlibrary');
                                    //     $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                    //     if ($mail_sent_status == 1)
                                    //         $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                                    // }
                                }
                            }
            
                        }
                    }
                }
                else{
                    if ($template_configurations_parent['total_records'] > 0 && !empty($To)) {
                        $template_configurations = $template_configurations_parent['data'][0];
                        $wildcards = $template_configurations['wildcards'];
                        $wildcards_replaces = array();
                        $wildcards_replaces['first_name'] = $To->first_name;
                        $wildcards_replaces['last_name'] = $To->last_name;
                        $wildcards_replaces['contract_name'] = !empty($contractId)?$contract_info[0]['contract_name']:"";
                        $wildcards_replaces['action_item_responsible_user'] = $To->first_name . ' ' . $To->last_name . ' (' . $To->user_role_name . ')';
                        $wildcards_replaces['action_item_name'] = $data['action_item'];
                        if (isset($data['description']))
                        $wildcards_replaces['action_item_description'] = $data['description'];
                        $wildcards_replaces['action_item_due_date'] = explode('T',$data['due_date'])[0];
                       // dateFormat($data['due_date']);
                       
                        if($data['module_id'] && $data['module_id']>0){
                            if($data['is_workflow']==1){
                            $wildcards_replaces['contract_workflow_topic_name'] = $topic_info[0]['topic_name'];
                            $wildcards_replaces['contract_workflow_module_name'] = $module_info[0]['module_name'];
                            }
                            else{
                                $wildcards_replaces['contract_review_topic_name'] = $topic_info[0]['topic_name'];
                                $wildcards_replaces['contract_review_module_name'] = $module_info[0]['module_name'];
                            }
                        }
                        
                        $wildcards_replaces['action_item_created_user_name'] = $created_user_info->first_name . ' ' . $created_user_info->last_name . ' (' . $created_user_info->user_role_name . ')';
                        $wildcards_replaces['action_item_created_date'] = dateFormat($update['created_on']);
                        $wildcards_replaces['logo'] = $customer_logo;
                        $wildcards_replaces['year'] = date("Y");
                        $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                        $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                        $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                        $from_name = $template_configurations['email_from_name'];
                        $from = $template_configurations['email_from'];
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
                        $mailer_data['is_cron'] = 1;
                        $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                        $mailer_id = $this->Customer_model->addMailer($mailer_data);
                        //sending mail to bu owner
                        // if ($mailer_data['is_cron'] == 0) {
                        //     //$mail_sent_status=sendmail($to, $subject, $body, $from);
                        //     $this->load->library('sendgridlibrary');
                        //     $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                        //     if ($mail_sent_status == 1)
                        //         $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                        // }
        
                    }
                    if(isset($data['external_users']) && count(explode(',', $data['external_users']))>0){  
                        if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
                            $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'CONTRACT_WORKFLOW_ACTION_ITEM_CREATION_EXTERNAL_USER'));
                        }else{
                            $template_configurations_parent = $this->Customer_model->EmailTemplateList(array('customer_id' => $cust_admin_info->customer_id,  'module_key' => 'CONTRACT_REVIEW_ACTION_ITEM_CREATION_EXTERNAL_USER'));
                        }
                        if ($template_configurations_parent['total_records'] > 0) {
                            $template_configurations = $template_configurations_parent['data'][0];
                            $wildcards = $template_configurations['wildcards'];
                            $wildcards_replaces = array();
                            $external_users = explode(',', $data['external_users']);
                            foreach($external_users as $v){
                                if(!empty($v))
                                {
                                    $wildcards_replaces['first_name'] = $v;
                                    $wildcards_replaces['contract_name'] =!empty($contractId)?$contract_info[0]['contract_name']:"";
                                    $wildcards_replaces['action_item_responsible_user'] = $To->first_name . ' ' . $To->last_name . ' (' . $To->user_role_name . ')';
                                    $wildcards_replaces['action_item_name'] = $data['action_item'];
                                    if (isset($data['description']))
                                        $wildcards_replaces['action_item_description'] = $data['description'];
                                    $wildcards_replaces['action_item_due_date'] = explode('T',$data['due_date'])[0];
                                    // dateFormat($data['due_date']);
                                    if($data['module_id'] && $data['module_id']>0){
                                        if($data['is_workflow']==1){
                                            $wildcards_replaces['contract_workflow_topic_name'] = $topic_info[0]['topic_name'];
                                            $wildcards_replaces['contract_workflow_module_name'] = $module_info[0]['module_name'];
                                        }
                                        else{
                                            $wildcards_replaces['contract_review_topic_name'] = $topic_info[0]['topic_name'];
                                            $wildcards_replaces['contract_review_module_name'] = $module_info[0]['module_name'];
                                        }
                                    }
                                    $wildcards_replaces['action_item_created_user_name'] = $created_user_info->first_name . ' ' . $created_user_info->last_name . ' (' . $created_user_info->user_role_name . ')';
                                    $wildcards_replaces['action_item_created_date'] = dateFormat($update['created_on']);
                                    $wildcards_replaces['logo'] = $customer_logo;
                                    $wildcards_replaces['year'] = date("Y");
                                    $wildcards_replaces['url'] = WEB_BASE_URL . 'html';
                                    $body = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_content']);
                                    $subject = wildcardreplace($wildcards, $wildcards_replaces, $template_configurations['template_subject']);
                                    $from_name = $template_configurations['email_from_name'];
                                    $from = $template_configurations['email_from'];
                                    $to = $v;
                                    $mailer_data['mail_from_name'] = $from_name;
                                    $mailer_data['mail_to_name'] = '';
                                    $mailer_data['mail_to_user_id'] = 0;
                                    $mailer_data['mail_from'] = $from;
                                    $mailer_data['mail_to'] = $to;
                                    $mailer_data['mail_subject'] = $subject;
                                    $mailer_data['mail_message'] = $body;
                                    $mailer_data['status'] = 0;
                                    $mailer_data['send_date'] = currentDate();
                                    $mailer_data['is_cron'] = 1;
                                    $mailer_data['email_template_id'] = $template_configurations['id_email_template'];
                                    $mailer_id = $this->Customer_model->addMailer($mailer_data);
                                    //sending mail to bu owner
                                    // if ($mailer_data['is_cron'] == 0) {
                                    //     //$mail_sent_status=sendmail($to, $subject, $body, $from);
                                    //     $this->load->library('sendgridlibrary');
                                    //     $mail_sent_status = $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array(), $mailer_id);
                                    //     if ($mail_sent_status == 1)
                                    //         $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
                                    // }
                                }
                            }
            
                        }
                    }
                }
               
               
            }

        }
        $result = array('status'=>TRUE, 'message' => $msg, 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function moveAllObligation_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_document_intelligence', array('required'=>$this->lang->line('document_inteligence_id_req')));
        if(isset($data['id_document_intelligence'])){
            $data['id_document_intelligence']=pk_decrypt($data['id_document_intelligence']);
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $obligationsAndrights = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['id_document_intelligence'],'field_status'=>array('A','E'),'field_type'=>array('Obligation','Right'),'is_moved'=>0));
        foreach($obligationsAndrights as $ObligationOrRight)
        {
            if(isset($ObligationOrRight['contract_id'])) {
                //$ObligationOrRight['contract_id'] = pk_decrypt($ObligationOrRight['contract_id']);
                if(!in_array($ObligationOrRight['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            $ObligationOrRight['type_name'] = $ObligationOrRight['field_type'];
            $ObligationOrRight['type'] = $ObligationOrRight['field_type'] =='Right'?1:0;
            if($ObligationOrRight['applicable_to']!="")
            {
                if($ObligationOrRight['applicable_to']==0)
                {
                    $ObligationOrRight['applicable_to_name']  = "Customer";
                }
                elseif($ObligationOrRight['applicable_to']==1)
                {
                    //$ObligationOrRight['applicable_to_name']  = "Provider";
                    $ObligationOrRight['applicable_to_name']  = "Relation";
                }
                elseif($ObligationOrRight['applicable_to']==2)
                {
                    $ObligationOrRight['applicable_to_name']  = "Mutual";
                }
                else{
                    $ObligationOrRight['applicable_to_name']  = null;
                }
            }
            else{
                $ObligationOrRight['applicable_to_name']  = null;
            }
            $ObligationOrRight['detailed_description'] = implode(" ",getValues($ObligationOrRight['field_status'],$ObligationOrRight['field_value'],array('A','E')));
            $ins_data = array(
                'contract_id'=> $ObligationOrRight['contract_id'],
                'description'=>$ObligationOrRight['field_name'],
                'type'=>$ObligationOrRight['type'],
                'type_name'=>$ObligationOrRight['type_name'],
                'calendar'=>isset($ObligationOrRight['calendar'])?$ObligationOrRight['calendar']:0,
                'applicable_to'=>isset($ObligationOrRight['applicable_to'])?$ObligationOrRight['applicable_to']:null,
                'applicable_to_name'=>$ObligationOrRight['applicable_to_name'],
                'detailed_description'=>isset($ObligationOrRight['detailed_description'])?$ObligationOrRight['detailed_description']:'',
                'recurrence_id'=>isset($ObligationOrRight['recurrence_id'])?$ObligationOrRight['recurrence_id']:null,
                'recurrence_start_date'=>!empty($ObligationOrRight['recurrence_start_date'])?$ObligationOrRight['recurrence_start_date']:null,
                'recurrence_end_date'=>!empty($ObligationOrRight['recurrence_end_date'])?$ObligationOrRight['recurrence_end_date']:null,
                'no_of_days'=>isset($ObligationOrRight['no_of_days'])?$ObligationOrRight['no_of_days']:null,
                'logic'=>isset($ObligationOrRight['logic'])?$ObligationOrRight['logic']:null,
                'email_send_start_date'=>!empty($ObligationOrRight['email_send_start_date'])?$ObligationOrRight['email_send_start_date']:null,
                'email_send_last_date'=>!empty($ObligationOrRight['email_send_last_date'])?$ObligationOrRight['email_send_last_date']:null,
                'notification_message'=>isset($ObligationOrRight['notification_message'])?$ObligationOrRight['notification_message']:'',
                'email_notification'=>isset($ObligationOrRight['email_notification'])?$ObligationOrRight['email_notification']:0,
                'resend_recurrence_id'=>isset($ObligationOrRight['resend_recurrence_id'])?$ObligationOrRight['resend_recurrence_id']:null,
                'created_by'=> $this->session_user_id,
                'created_on'=> currentDate()
            );
            $obligation_id =$this->User_model->insert_data('obligations_and_rights',$ins_data);
            if($obligation_id)
            {
                $this->User_model->update_data('document_fields',array('is_moved'=>1),array('id_document_fields'=>$ObligationOrRight['id_document_fields']));
            }  
        }
        $message = $this->lang->line('obligation_and_right_added_success');
        $this->response(array('status'=>TRUE,'message'=>$message,'data'=>''), REST_Controller::HTTP_OK);
    }
    
    //event feeds store and update

    public function eventFeed_post()
    {
        $data = $this->input->post();
        
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('reference_type', array('required'=>$this->lang->line('reference_type_req')));
        $this->form_validator->add_rules('reference_id', array('required'=>$this->lang->line('reference_id_req')));
        $this->form_validator->add_rules('subject', array('required'=>$this->lang->line('subject_req')));
        $this->form_validator->add_rules('responsible_user_id', array('required'=>$this->lang->line('responsible_user_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
        }
        if(isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt($data['responsible_user_id']);
        }

        if(isset($data['id_event_feed'])){
            $event_id = $data['id_event_feed'] = pk_decrypt($data['id_event_feed']);
            $upd_data = array(
                'subject'=> $data['subject'],
                'responsible_user_id'=>$data['responsible_user_id'],
                'stakeholders'=> (isset($data['stakeholders']) && (!empty($data['stakeholders'])))?$data['stakeholders']:null,
                'date'=> (isset($data['date']) && (!empty($data['date'])))?$data['date']:null,
                'type'=> (isset($data['type']) && (!empty($data['type'])))?$data['type']:null,
                'description'=> (isset($data['description']) && (!empty($data['description'])))?$data['description']:null,
                'reference_type'=> $data['reference_type'],
                'reference_id'=> $data['reference_id'],
                'updated_by'=> $this->session_user_id,
                'updated_on'=> currentDate()
            );
            //attachments in edit should be added
            $update = $this->User_model->update_data('event_feeds',$upd_data,array('id_event_feed'=>$data['id_event_feed']));   
            if($update)
            {
                $msg = $this->lang->line('event_feed_updated_successfully');
                $status = TRUE;
            }
            else
            {
                $msg = $this->lang->line('operation_failed');
                $status = FALSE;
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
            }
              
        }else{
            
            //add

            $ins_data = array(
                'subject'=> $data['subject'],
                'responsible_user_id'=>$data['responsible_user_id'],
                'stakeholders'=> (isset($data['stakeholders']) && (!empty($data['stakeholders'])))?$data['stakeholders']:null,
                'date'=> (isset($data['date']) && (!empty($data['date'])))?$data['date']:null,
                'type'=> (isset($data['type']) && (!empty($data['type'])))?$data['type']:null,
                'description'=> (isset($data['description']) && (!empty($data['description'])))?$data['description']:null,
                'reference_type'=> $data['reference_type'],
                'reference_id'=> $data['reference_id'],
                'created_by'=> $this->session_user_id,
                'created_on'=> currentDate());
             
            $event_id = $this->User_model->insert_data('event_feeds',$ins_data);
            if($event_id)
            {
                $msg = $this->lang->line('event_feed_added_successfully');
                $status = TRUE;
            }
            else
            {
                $msg = $this->lang->line('operation_failed');
                $status = FALSE;
            }
            
          
        }

        // add attachments
        if($event_id)
        {
            if(isset($_FILES['file']))
            {
                $totalFilesCount = count($_FILES['file']['name']);
            }
            else
            {
                $totalFilesCount=0;
            }
            $customer_id=$this->session_user_info->customer_id;
            $path=FILE_SYSTEM_PATH.'uploads/';
            $event_feed_documents=array();
            if(!is_dir($path.$customer_id)){ mkdir($path.$customer_id); }
            if(isset($_FILES) && $totalFilesCount>0)
            {
                $i_attachment=0;
                for($i_attachment=0; $i_attachment<$totalFilesCount; $i_attachment++) {
                    $imageName = doUpload(array(
                        'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                        'image' => $_FILES['file']['name'][$i_attachment],
                        'upload_path' => $path,
                        'folder' => $customer_id));
                    $event_feed_documents[$i_attachment]['module_id']=$data['reference_id'];
                    $event_feed_documents[$i_attachment]['module_type']=$data['reference_type'];
                    $event_feed_documents[$i_attachment]['reference_id']=$event_id;
                    $event_feed_documents[$i_attachment]['reference_type']='event_feed';
                    $event_feed_documents[$i_attachment]['document_name']=$_FILES['file']['name'][$i_attachment];
                    $event_feed_documents[$i_attachment]['document_type'] = 0;
                    $event_feed_documents[$i_attachment]['document_source']=$imageName;
                    $event_feed_documents[$i_attachment]['document_mime_type']=$_FILES['file']['type'][$i_attachment];
                    $event_feed_documents[$i_attachment]['document_status']=1;
                    $event_feed_documents[$i_attachment]['uploaded_by']=$this->session_user_id;
                    $event_feed_documents[$i_attachment]['uploaded_on']=currentDate();
                    
                }
            }

            if(count($event_feed_documents)>0){
                $this->Document_model->addBulkDocuments($event_feed_documents);
            }
            $event_feed_links = array();
            if(isset($data['links']))
            {
                foreach($data['links'] as $k => $v){
                    $event_feed_links[$k]['module_id'] = $data['reference_id'];
                    $event_feed_links[$k]['module_type'] = $data['reference_type'];
                    $event_feed_links[$k]['reference_id'] = $event_id;
                    $event_feed_links[$k]['reference_type'] = 'event_feed';
                    $event_feed_links[$k]['document_name'] = $v['title'];
                    $event_feed_links[$k]['document_type'] = 1;
                    $event_feed_links[$k]['document_source'] = $v['url'];
                    $event_feed_links[$k]['document_mime_type'] = 'URL';
                    $event_feed_links[$k]['uploaded_by'] = $this->session_user_id;
                    $event_feed_links[$k]['uploaded_on'] = currentDate();
                }
            }
            if(count($event_feed_links)>0){
                $this->Document_model->addBulkDocuments($event_feed_links);
            }
        } 

        // result outpt
        if($status)
        {
            $this->response(array('status'=>$status,'message'=>$msg,'data'=>''), REST_Controller::HTTP_OK);
        }
        else
        {
            $this->response(array('status'=>$status,'message'=>$msg,'data'=>''), REST_Controller::HTTP_OK);
        }
    }

    // event feeds Delete
     
    public function eventFeed_delete(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_event_feed', array('required'=>$this->lang->line('event_feed_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_event_feed'])) {
            $data['id_event_feed'] = pk_decrypt($data['id_event_feed']);
        }
        if(isset($data['id_event_feed'])){
            $upd_data = array(
                 'status'=>0,
                 'updated_by'=> $this->session_user_id,
                 'updated_on'=> currentDate(),
                );  
            if($this->User_model->update_data('event_feeds',$upd_data,array('id_event_feed'=>$data['id_event_feed'])))
            {
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('event_feed_deleted_sucessfully'),'data'=>''), REST_Controller::HTTP_OK);
            }  
            else
            {
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
            }    
        }
    }

    // event feeds list

    public function eventFeed_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!isset($data['id_event_feed'])) {
            $this->form_validator->add_rules('reference_type', array('required'=>$this->lang->line('reference_type_req')));
            $this->form_validator->add_rules('reference_id', array('required'=>$this->lang->line('reference_id_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
        }
        if(isset($data['id_event_feed'])) {
            $data['id_event_feed'] = pk_decrypt($data['id_event_feed']);
        }
        $data = tableOptions($data);
        $eventFeedList = $this->Project_model->getEventFeeds($data);
        $result  = $eventFeedList['data'];
        $count =$eventFeedList['total_records'];
        foreach($result as $k =>$v)
        {
            $inner_data=array();
            $inner_data['reference_id']=$v['id_event_feed'];
            $inner_data['reference_type']='event_feed';
            $inner_data['document_status']=1;
            
            $result[$k]['attachment']['documents'] = $this->Document_model->getDocumentsList($inner_data);
            $inner_data['document_type'] = array(0,1);
            $result[$k]['attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
            $inner_data['document_type'] = 1;
            $result[$k]['attachment']['links'] = $this->Document_model->getDocumentsList($inner_data);

            foreach($result[$k]['attachment']['all_records'] as $ka=>$va){
                $result[$k]['attachment']['all_records'][$ka]['document_source_exactpath']=($va['document_source']);
                $result[$k]['attachment']['all_records'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $result[$k]['attachment']['all_records'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['id_document']);
                $result[$k]['attachment']['all_records'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['module_id']);
                $result[$k]['attachment']['all_records'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['reference_id']);
                $result[$k]['attachment']['all_records'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['uploaded_by']);
                $result[$k]['attachment']['all_records'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['user_role_id']);
            }
            $result[$k]['attachment']['all_records']= array_values($result[$k]['attachment']['all_records']);
            foreach($result[$k]['attachment']['documents'] as $ka=>$va){
                $result[$k]['attachment']['documents'][$ka]['show_icon']=false;
                $result[$k]['attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                $result[$k]['attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $result[$k]['attachment']['documents'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['id_document']);
                $result[$k]['attachment']['documents'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['module_id']);
                $result[$k]['attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['reference_id']);
                $result[$k]['attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['uploaded_by']);
                $result[$k]['attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['user_role_id']);
            }
            $result[$k]['attachment']['documents']= array_values($result[$k]['attachment']['documents']);
            foreach($result[$k]['attachment']['links'] as $ka=>$va){
                $result[$k]['attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                $result[$k]['attachment']['links'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['links'][$ka]['id_document']);
                $result[$k]['attachment']['links'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['module_id']);
                $result[$k]['attachment']['links'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['reference_id']);
                $result[$k]['attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['links'][$ka]['uploaded_by']);
                $result[$k]['attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['user_role_id']);
            }
            $result[$k]['attachment']['links']= array_values($result[$k]['attachment']['links']);

            $result[$k]['responsible_user_id'] = pk_encrypt($v['responsible_user_id']);
            $result[$k]['id_event_feed'] = pk_encrypt($v['id_event_feed']);
            $result[$k]['reference_id'] = pk_encrypt($v['reference_id']);
            $result[$k]['created_by'] = pk_encrypt($v['created_by']);
            $result[$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result,'total_records'=>$count), REST_Controller::HTTP_OK);
    }

    //event feed responsible User

    public function eventFeedResponsibleUsers_get()
    {
        //getting all internal users with role delegate,owner,read-only-users,manager 
        $data =array(
            'customer_id' => $this->session_user_info->customer_id,
            'id_user' => $this->session_user_id,
            'user_type' => 'internal',
            'user_role_not' => array(1,2)
        );
        $users = $this->Customer_model->getCustomerUserList($data);
        foreach($users['data'] as $user)
        {
            $userDetails['id_user'] = pk_encrypt($user['id_user']);
            $userDetails['name'] = $user['name']." ( ".$user['email']." | ".$user['user_role_name']." | ".$user['bu_name']." )";
            $result[] = $userDetails;
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result), REST_Controller::HTTP_OK);
      
        
    }
    //////////// for performance optimization new project taskslist  API start ////////
    public function getProjectTasks_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('project_id', [
            'required' => $this->lang->line('project_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['project_id'])) {
            $data['project_id'] = pk_decrypt($data['project_id']);
        }
        // print_r($data);exit;
        $project_task = [];
        $new_project_task_data = [];

        $project_task_data = $this->Project_model->getProjectWorkflow([
            'contract_id' => $data['project_id'],
            'parent_id' => 0,
        ]);
        if (!empty($project_task_data)) {
            $validation_info = '';
            if (!empty($project_task_data[0]['id_contract_review'])) {
                $validatorsmodules = [];
                $validatorsmodules = $this->Contract_model->getValidatormodules(
                    [
                        'contract_review_id' =>
                            $project_task_data[0]['id_contract_review'],
                        'contribution_type' => 1,
                    ]
                ); //getting validator modules
                $validator_exists =
                    count($validatorsmodules) > 0 ? true : false;
                if ($validator_exists) {
                    $progress_task_reviews = $this->calculateScoreAndProgress([
                        'id_contract_review' =>
                            $project_task_data[0]['id_contract_review'],
                        'user_id' => 0,
                        'owner_id' => $v['contract_owner_id'],
                        'delegate_id' => $v['delegate_id'],
                    ]);
                    $validation_info = 1;
                    if (
                        str_replace(
                            '%',
                            '',
                            $progress_task_reviews['contract_progress']
                        ) == '100'
                    ) {
                        $validation_info = 4;
                    }
                    if ((int) $project_task_data[0]['validation_status'] == 2) {
                        $validation_info = 2;
                    } elseif (
                        (int) $project_task_data[0]['validation_status'] == 3
                    ) {
                        $validation_info = 3;
                    }
                }
            }
            $project_task_score = $this->calculateScoreAndProgress([
                'id_contract_review' =>
                    $project_task_data[0]['id_contract_review'],
                'user_id' => 0,
                'owner_id' => $v['contract_owner_id'],
                'delegate_id' => $v['delegate_id'],
            ]);
            $project_task[0]['score'] = $project_task_score['score'];
            $project_task[0]['contract_progress'] =
                $project_task_score['contract_progress'];
            $project_task[0]['validation_info'] = $validation_info;
            $project_task[0]['activity_name'] =
                $project_task_data[0]['workflow_name'];
            $project_task[0]['recurrence_till'] =
                $project_task_data[0]['Execute_by'];
            $project_task[0]['calender_id'] = pk_encrypt(
                $project_task_data[0]['calender_id']
            );
            $project_task[0]['id_contract_workflow'] = pk_encrypt(
                $project_task_data[0]['id_contract_workflow']
            );
            $project_task[0]['calender_id'] = pk_encrypt(
                $project_task_data[0]['calender_id']
            );
            $project_task[0]['id_contract_review'] = pk_encrypt(
                $project_task_data[0]['id_contract_review']
            );
            $project_task[0]['is_workflow'] = 1;
            $project_task[0]['id_contract'] = pk_encrypt(
                $project_task_data[0]['contract_id']
            );
            $project_task[0]['is_subtask'] = 0;
            $project_task[0]['validation_status'] = isset(
                $project_task_data[0]['validation_status']
            )
                ? $project_task_data[0]['validation_status']
                : 0;
            if (
                isset($project_task_data[0]['workflow_status']) &&
                $project_task_data[0]['workflow_status'] ==
                    'workflow in progress'
            ) {
                $project_task[0]['initiated'] = true;
            } else {
                $project_task[0]['initiated'] = false;
            }
            $get_data_project_data = $this->Project_model->getProjectWorkflow([
                'contract_id' => $data['project_id'],
                'not_contract_workflow_id' =>
                    $project_task_data[0]['id_contract_workflow'],
                'parent_id' => 0,
            ]);
            foreach ($get_data_project_data as $l => $m) {
                $validation_info = '';
                if (!empty($get_data_project_data[$l]['id_contract_review'])) {
                    $validatorsmodules = [];
                    $validatorsmodules = $this->Contract_model->getValidatormodules(
                        [
                            'contract_review_id' =>
                                $get_data_project_data[$l][
                                    'id_contract_review'
                                ],
                            'contribution_type' => 1,
                        ]
                    ); //getting validator modules
                    $validator_exists =
                        count($validatorsmodules) > 0 ? true : false;
                    if ($validator_exists) {
                        $validation_info = 1;
                        $progress_task_reviews = $this->calculateScoreAndProgress(
                            [
                                'id_contract_review' =>
                                    $get_data_project_data[$l][
                                        'id_contract_review'
                                    ],
                                'user_id' => 0,
                                'owner_id' => $v['contract_owner_id'],
                                'delegate_id' => $v['delegate_id'],
                            ]
                        );
                        if (
                            str_replace(
                                '%',
                                '',
                                $progress_task_reviews['contract_progress']
                            ) == '100'
                        ) {
                            $validation_info = 4;
                        }
                        if (
                            (int) $get_data_project_data[$l][
                                'validation_status'
                            ] == 2
                        ) {
                            $validation_info = 2;
                        } elseif (
                            (int) $get_data_project_data[$l][
                                'validation_status'
                            ] == 3
                        ) {
                            $validation_info = 3;
                        }
                    }
                }
                $project_task_score = $this->calculateScoreAndProgress([
                    'id_contract_review' =>
                        $get_data_project_data[$l]['id_contract_review'],
                    'user_id' => 0,
                    'owner_id' => $v['contract_owner_id'],
                    'delegate_id' => $v['delegate_id'],
                ]);
                $project_task[$l + 1]['score'] = $project_task_score['score'];
                $project_task[$l + 1]['contract_progress'] =
                    $project_task_score['contract_progress'];

                $project_task[$l + 1]['validation_info'] = $validation_info;
                $project_task[$l + 1]['activity_name'] = $m['workflow_name'];
                $project_task[$l + 1]['calender_id'] = pk_encrypt(
                    $get_data_project_data[$l]['calender_id']
                );
                $project_task[$l + 1]['id_contract_workflow'] = pk_encrypt(
                    $get_data_project_data[$l]['id_contract_workflow']
                );
                $project_task[$l + 1]['is_workflow'] = 1;
                $project_task[$l + 1]['is_subtask'] = 0;
                $project_task[$l + 1]['id_contract_review'] = pk_encrypt(
                    $get_data_project_data[$l]['id_contract_review']
                );
                $project_task[$l + 1]['id_contract'] = pk_encrypt(
                    $result[0]['id_contract']
                );
                $project_task[$l + 1]['validation_status'] = isset(
                    $get_data_project_data[$l]['validation_status']
                )
                    ? $get_data_project_data[$l]['validation_status']
                    : 0; //for disable the access of workflow which is in validation on going
                if (
                    isset($get_data_project_data[$l]['workflow_status']) &&
                    $get_data_project_data[$l]['workflow_status'] ==
                        'workflow in progress'
                ) {
                    $project_task[$l + 1]['initiated'] = true;
                } else {
                    $project_task[$l + 1]['initiated'] = false;
                }
            }
            foreach ($project_task as $v) {
                if (
                    $this->session_user_info->user_role_id == 2 ||
                    $this->session_user_info->user_role_id == 6
                ) {
                    $new_project_task_data[] = $v;
                } elseif ($this->session_user_info->user_role_id == 3) {
                    $new_project_task_data[] = $v;
                } elseif ($this->session_user_info->user_role_id == 4) {
                    $new_project_task_data[] = $v;
                } elseif (
                    $this->session_user_info->user_role_id == 7 &&
                    count(
                        $this->User_model->check_record('contract_user', [
                            'contract_id' => pk_decrypt($v['id_contract']),
                            'contract_review_id' => pk_decrypt(
                                $v['id_contract_review']
                            ),
                            'status' => 1,
                            'user_id' => $this->session_user_id,
                        ])
                    ) > 0
                ) {
                    $new_project_task_data[] = $v;
                }
            }
        }

        $subtask_data = [];
        foreach ($new_project_task_data as $kn => $vn) {
            $subtask_data[] = $vn;
            $get_subtasks_data = $this->Project_model->getProjectWorkflow([
                'contract_id' => pk_decrypt($vn['id_contract']),
                'parent_id' => pk_decrypt($vn['id_contract_workflow']),
            ]);
            foreach ($get_subtasks_data as $ks => $vs) {
                $project_sub_task['activity_name'] =
                    $get_subtasks_data[$ks]['workflow_name'];
                $project_sub_task['recurrence_till'] =
                    $get_subtasks_data[$ks]['Execute_by'];
                $project_sub_task['calender_id'] = pk_encrypt(
                    $get_subtasks_data[$ks]['calender_id']
                );
                $project_sub_task['id_contract_workflow'] = pk_encrypt(
                    $get_subtasks_data[$ks]['id_contract_workflow']
                );
                $project_sub_task['calender_id'] = pk_encrypt(
                    $get_subtasks_data[$ks]['calender_id']
                );
                $project_sub_task_score = $this->calculateScoreAndProgress([
                    'id_contract_review' =>
                        $get_subtasks_data[$ks]['id_contract_review'],
                    'user_id' => 0,
                    'is_subtask' => 1,
                ]);
                $project_sub_task['score'] = $project_sub_task_score['score'];
                $project_sub_task['contract_progress'] =
                    $project_sub_task_score['contract_progress'];
                $project_sub_task['id_contract_review'] = pk_encrypt(
                    $get_subtasks_data[$ks]['id_contract_review']
                );
                $project_sub_task['is_workflow'] = 1;
                $project_sub_task['is_subtask'] = 1;
                $project_sub_task['id_contract'] = pk_encrypt(
                    $get_subtasks_data[$ks]['contract_id']
                );
                if (
                    isset($get_subtasks_data[$ks]['workflow_status']) &&
                    $get_subtasks_data[$ks]['workflow_status'] ==
                        'workflow in progress'
                ) {
                    $project_sub_task['initiated'] = true;
                } else {
                    $project_sub_task['initiated'] = false;
                }
                $subtask_data[] = $project_sub_task;
            }
        }
        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $subtask_data
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }
    //////////// for performance optimization new project taskslist  API end ////////

}