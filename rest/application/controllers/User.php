<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller
{
    public $order_data = array();
    public $cnt =1;

    public $user_id = 0 ;
    public $session_user_id=NULL;
    public $session_user_parent_id=NULL;
    public $session_user_id_acting=NULL;
    public $session_user_info=NULL;
    public $session_user_business_units=NULL;
    public $session_user_business_units_user=NULL;
    public $session_user_contracts=NULL;
    public $session_user_contract_reviews=NULL;
    public $session_user_contract_documents=NULL;
    public $session_user_contract_action_items=NULL;
    public $session_user_delegates=NULL;
    public $session_user_contributors=NULL;
    public $session_user_reporting_owners=NULL;
    public $session_user_bu_owners=NULL;
    public $session_user_customer_admins=NULL;
    public $session_user_customer_all_users=NULL;
    public $session_user_customer_relationship_categories=NULL;
    public $session_user_customer_relationship_classifications=NULL;
    public $session_user_customer_calenders=NULL;
    public $session_user_master_currency=NULL;
    public $session_user_master_language=NULL;
    public $session_user_master_countries=NULL;
    public $session_user_master_templates=NULL;
    public $session_user_master_customers=NULL;
    public $session_user_master_users=NULL;
    public $session_user_master_user_roles=NULL;
    public $session_user_contract_review_modules=NULL;
    public $session_user_contract_review_topics=NULL;
    public $session_user_contract_review_questions=NULL;
    public $session_user_contract_review_question_options=NULL;
    public $session_user_wadmin_relationship_categories=NULL;
    public $session_user_wadmin_relationship_classifications=NULL;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('User_model');
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_parent_id=$getLoggedUserId[0]['parent_user_id'];
        $this->session_user_id_acting=$getLoggedUserId[0]['child_user_id'];
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

        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));


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
        $this->session_user_master_language=$this->Validation_model->getLanguage();
        $this->session_user_master_countries=$this->Validation_model->getCountries();
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_master_users=$this->Validation_model->getUsers();
        $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();

    }

    public function changePassword_post()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $password=$data['password'];
        $passwordRules               = array(
            'required'=> $this->lang->line('password_req'),
            'min_len-8' => $this->lang->line('password_num_min_len'),
            'max_len-12' => $this->lang->line('password_num_max_len'),
        );
        $confirmPasswordRules        = array(
            'required'=>$this->lang->line('confirm_password_req'),
            'match_field-password'=>$this->lang->line('password_match')
        );

        $req = array(
            'required'=> $this->lang->line('user_id_req')
        );

        if(isset($_POST['requestData']) && DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $data = $aesObj->decrypt($_POST['requestData'],AES_KEY);
            $data = (array) json_decode($data,true);
            $_POST = $data;
        }

        /*$this->form_validator->add_rules('user_id', $req);*/
        $this->form_validator->add_rules('oldpassword', array('required'=>$this->lang->line('old_password_req')));
        $this->form_validator->add_rules('password', $passwordRules);
        $this->form_validator->add_rules('cpassword', $confirmPasswordRules);
        $validated = $this->form_validator->validate($data);

        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!validatePassword($data['password'])){
            // var_dump(validatePassword($data['password']));exit;
            $result = array('status'=>FALSE,'error'=>array('password'=>$this->lang->line("reg_exp_not_match")),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($data['password'] == $data['oldpassword']){
            $result = array('status'=>FALSE,'error'=>array('password'=>$this->lang->line("old_new_password_same")),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($data['password'] != $data['cpassword']){
            $result = array('status'=>FALSE,'error'=>array('password'=>$this->lang->line("new_password_confirm_password_notmatch")),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['user_id'] = $_SERVER['HTTP_USER'];
        //$data['user_id']=pk_decrypt($data['user_id']);
        if(isset($data['user_id'])) {
            $data['user_id'] = pk_decrypt($data['user_id']);
            if($data['user_id']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $passwordExist=$this->User_model->passwordExist($data);

        if(empty($passwordExist))
        {
            $result = array('status'=>FALSE,'error'=>array('oldpassword'=>$this->lang->line("old_password_not_match")),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // print_r($data);exit;
        $result = $this->User_model->changePassword($data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('password_changed'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function unblock_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //validating data
        $this->form_validator->add_rules('email', array('required'=> $this->lang->line('email_req'),
            'valid_email' => $this->lang->line('email_invalid')
        ));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        $result = $this->User_model->check_email(array('email' => $data['email']));
        if(empty($result)){
            $result = array('status'=>FALSE, 'error' => array('email'=> $this->lang->line('email_wrong')), 'data'=>'');
            echo json_encode($result);exit;
        }
        else
        {
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $result->customer_id));
            if(isset($result->customer_id)) {
                if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$result->customer_id){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($result->customer_id,$this->session_user_master_customers)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }

            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'company');
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'company');*/
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'profile');*/
            }
            $customer_name='';
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            $this->User_model->changePassword(array('user_id' => $result->id_user,'password' => $result->password));

            $new_password = generatePassword(8);
            $this->User_model->updatePassword($new_password,$result->id_user);
            $this->User_model->updateUser(array('is_blocked'=>0,'no_of_password_attempts'=>0),$result->id_user);

            $user_info = $this->User_model->getUserInfo(array('user_id' => $result->id_user));
            //$user_info->email='parameshwar.v@thresholdsoft.com';
           
            $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $user_info->customer_id,'module_key'=>'FORGOT_PASSWORD'));
            if($template_configurations['total_records']>0){
                $template_configurations=$template_configurations['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$user_info->first_name;
                $wildcards_replaces['last_name']=$user_info->last_name;
                $wildcards_replaces['customer_name']=$customer_name;
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['email']=$user_info->email;
                $wildcards_replaces['role']=$user_info->user_role_name;
                $wildcards_replaces['password']=$new_password;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject=$template_configurations['template_subject'];
                $from_name=SEND_GRID_FROM_NAME;
                $from=SEND_GRID_FROM_EMAIL;
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
            }

            $result = array('status'=>TRUE, 'message' => $this->lang->line('new_password'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function list_get($type)
    {
        if(empty($type)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['type'] = $type;
        if(isset($data['type']))
            $data['type']=pk_decrypt($data['type']);
        //validating data
        $this->form_validator->add_rules('type', array('required' => $this->lang->line('type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $result = $this->User_model->getUsersList($data);
        foreach($result as $k=>$v){
            $result[$k]['id_user']=pk_encrypt($result[$k]['id_user']);
            $result[$k]['customer_id']=pk_encrypt($result[$k]['customer_id']);
            $result[$k]['user_role_id']=pk_encrypt($result[$k]['user_role_id']);
            $result[$k]['created_by']=pk_encrypt($result[$k]['created_by']);
            $result[$k]['updated_by']=pk_encrypt($result[$k]['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //validating data

        $this->form_validator->add_rules('user_id', array('required'=> $this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*if(isset($data['user_id']))
            $data['user_id']=pk_decrypt($data['user_id']);*/
        // if(isset($data['user_id'])) {
        //     $data['user_id'] = pk_decrypt($data['user_id']);
        //     if($data['user_id']!=$this->session_user_id){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        $data['user_id'] = $this->session_user_id ;
        /*if(isset($data['user_role_id']))
            $data['user_role_id']=pk_decrypt($data['user_role_id']);*/
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*if(isset($data['customer_id']))
            $data['customer_id']=pk_decrypt($data['customer_id']);*/
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->User_model->getUserInfo($data);
        $result->business_unit = array();

        if($result->provider == 0)
            $result->business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $result->id_user,'status' =>1));

        if($result->user_role_id == 6 && $result->is_allow_all_bu == 1)//To show All business units in User profile
            $result->business_unit = $this->Business_unit_model->getBusinessUnitUser(array('status' =>1,'customer_id'=>$result->customer_id));

        foreach($result->business_unit as $k=>$v){
            $result->business_unit[$k]['business_unit_id']=pk_encrypt($result->business_unit[$k]['business_unit_id']);
            $result->business_unit[$k]['country_id']=pk_encrypt($result->business_unit[$k]['country_id']);
            $result->business_unit[$k]['created_by']=pk_encrypt($result->business_unit[$k]['created_by']);
            $result->business_unit[$k]['customer_id']=pk_encrypt($result->business_unit[$k]['customer_id']);
            $result->business_unit[$k]['id_business_unit']=pk_encrypt($result->business_unit[$k]['id_business_unit']);
            $result->business_unit[$k]['id_business_unit_user']=pk_encrypt($result->business_unit[$k]['id_business_unit_user']);
            $result->business_unit[$k]['updated_by']=pk_encrypt($result->business_unit[$k]['updated_by']);
            $result->business_unit[$k]['user_id']=pk_encrypt($result->business_unit[$k]['user_id']);
        }
        if($result->profile_image!='') {
            $result->profile_image_medium = getImageUrl($result->profile_image, 'profile', MEDIUM_IMAGE,'profile_images/');
            $result->profile_image_small = getImageUrl($result->profile_image, 'profile', SMALL_IMAGE,'profile_images/');
            $result->profile_image = getImageUrl($result->profile_image, 'profile','','profile_images/');
        }
        if(isset($result->id_user))
            $result->id_user=pk_encrypt($result->id_user);
        if(isset($result->customer_id))
            $result->customer_id=pk_encrypt($result->customer_id);
        if(isset($result->user_role_id))
            $result->user_role_id=pk_encrypt($result->user_role_id);
        

        $result->contribution_type = (int)$result->contribution_type;
        $result->content_administator_relation = (int)$result->content_administator_relation;
        $result->content_administator_review_templates = (int)$result->content_administator_review_templates;
        $result->content_administator_task_templates = (int)$result->content_administator_task_templates;
        $result->content_administator_currencies = (int)$result->content_administator_currencies;
        $result->legal_and_content_administator = (int)$result->legal_and_content_administator;   
        $result->content_administator_catalogue = (int)$result->content_administator_catalogue;   
        $result->language_id=pk_encrypt($result->language_id); 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $firstNameRules               = array(
            'required'=> $this->lang->line('first_name_req'),
            'max_len-100' => $this->lang->line('first_name_len'),
        );
        $lastNameRules               = array(
            'required'=> $this->lang->line('last_name_req'),
            'max_len-100' => $this->lang->line('last_name_len'),
        );
        $emailRules = array(
            'required'=> $this->lang->line('email_req'),
            'valid_email' => $this->lang->line('email_invalid')
        );
        $passwordRules  = array(
            'required'=> $this->lang->line('password_req')
        );

        if(isset($data['user'])){
            $data = $data['user'];
        }

        $this->form_validator->add_rules('first_name', $firstNameRules);
        $this->form_validator->add_rules('last_name', $lastNameRules);
        $this->form_validator->add_rules('email', $emailRules);

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*if(isset($data['id_user']))
            $data['id_user'] = pk_decrypt($data['id_user']);*/
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*if(isset($data['id_customer']))
            $data['id_customer'] = pk_decrypt($data['id_customer']);*/
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed').'1'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed').'2'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data['email']=$this->session_user_info->email;
        $email_check = $this->User_model->check_email(array('email' => $data['email'],'id' => $data['id_user']));
        if(!empty($email_check)){
            $result = array('status'=>FALSE,'error'=>array('email' => $this->lang->line('email_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(isset($data['language_id'])){$data['language_id'] = pk_decrypt($data['language_id']);}
        $user_data = array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'gender' => isset($data['gender'])?$data['gender']:'',
            'language_id' => isset($data['language_id'])?$data['language_id']:1,
            'other_gender_value' => (isset($data['other_gender_value']) && !empty($data['other_gender_value']) && $data['gender'] == 'other') ? $data['other_gender_value'] : null,
        );


        $path='profile_images/';
        if(isset($_FILES) && !empty($_FILES['file']['name']['profile_image']))
        {
            $imageName = doUpload(array(
                'temp_name' => $_FILES['file']['tmp_name']['profile_image'],
                'image' => $_FILES['file']['name']['profile_image'],
                'upload_path' => $path,
                'folder' => isset($data['id_customer'])?$data['id_customer']:''));
            $user_data['profile_image'] = $imageName;
            imageResize($path.$imageName);
            /* getting previous image to delete*/

            $user_info = $this->User_model->getUserInfo(array('user_id' => $data['id_user']));
            if(!empty($user_info)){
                deleteProfileImage($user_info->profile_image);
            }
        }
        else{
            unset($user_data['profile_image']);
        }
        $result = $this->User_model->updateUser($user_data,$data['id_user']);
        $UserDetails = $this->User_model->getUserInfo(array('user_id'=>$data['id_user']));
        $menu = $this->User_model->menu(array('user_role_id' => $UserDetails->user_role_id,'user_id'=>$data['id_user'],'language_iso_code' => $UserDetails->language_iso_code));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('user_update'), 'data'=>$result ,'menu' => $menu);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_put()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $firstNameRules               = array(
            'required'=> $this->lang->line('first_name_req'),
            'max_len-100' => $this->lang->line('first_name_len'),
        );
        $lastNameRules               = array(
            'required'=> $this->lang->line('last_name_req'),
            'max_len-100' => $this->lang->line('last_name_len'),
        );
        $phoneRules  = array(
            'required'=> $this->lang->line('phone_num_req'),
            'numeric'=>  $this->lang->line('phone_num_num'),
            'min_len-7' => $this->lang->line('phone_num_min_len'),
            'max_len-10' => $this->lang->line('phone_num_max_len'),
        );

        $this->form_validator->add_rules('first_name', $firstNameRules);
        $this->form_validator->add_rules('last_name', $lastNameRules);
        $this->form_validator->add_rules('phone_number', $phoneRules);
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $result = $this->User_model->updateUserInfo($data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_delete()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $result = $this->User_model->deleteUser($data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function logout_post()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(isset($_SERVER['HTTP_USER']))
            $_SERVER['HTTP_USER'] = pk_decrypt($_SERVER['HTTP_USER']);
        if(isset($data['id']))
            $data['id'] = pk_decrypt($data['id']);

        $previous_session = $this->User_model->getPreviousUserSessions(array('user_id' => $_SERVER['HTTP_USER'],'access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])));
        if(!empty($previous_session)){
            for($sr=0;$sr<count($previous_session);$sr++)
            {
                $this->User_model->updateOauthAccessToken(array('id' => $previous_session[$sr]['access_token_id'],'expire_time' => '-'.$previous_session[$sr]['expire_time'],'updated_at' => currentDate(),'expired_date_time' => currentDate()));
            }
        }
        /*if(!empty($this->session->userdata('session_user_id_acting')))
            $this->session->unset_userdata('session_user_id_acting');
        if(!empty($this->session->userdata('session_user_id')))
            $this->session->unset_userdata('session_user_id');*/
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>[]);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function access_get()
    {
        //////////////
        //App Version Script
        //echo '<pre>'.print_r($_SERVER);
        $versionUpdated = false;
        if(isset($_SERVER['HTTP_USER']))
            $_SERVER['HTTP_USER'] = pk_decrypt($_SERVER['HTTP_USER']);

        if(!isset($_SERVER['HTTP_APPVERSION'])){
            //echo '<pre>'.print_r($_SERVER);exit;
            $previous_session = $this->User_model->getPreviousUserSessions(array('user_id' => $_SERVER['HTTP_USER'],'access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])));
            if(!empty($previous_session)){
                for($sr=0;$sr<count($previous_session);$sr++)
                {
                    $this->User_model->updateOauthAccessToken(array('id' => $previous_session[$sr]['access_token_id'],'expire_time' => '-'.$previous_session[$sr]['expire_time'],'updated_at' => currentDate(),'expired_date_time' => currentDate()));
                }
            }  
            $versionUpdated = true;          
        }
        if($_SERVER['HTTP_APPVERSION'] != AppVersion){
            $previous_session = $this->User_model->getPreviousUserSessions(array('user_id' => $_SERVER['HTTP_USER'],'access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])));
            if(!empty($previous_session)){
                for($sr=0;$sr<count($previous_session);$sr++)
                {
                    $this->User_model->updateOauthAccessToken(array('id' => $previous_session[$sr]['access_token_id'],'expire_time' => '-'.$previous_session[$sr]['expire_time'],'updated_at' => currentDate(),'expired_date_time' => currentDate()));
                }
            } 
            $versionUpdated = true;
        }
        //////////////

        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //validating data
        $this->form_validator->add_rules('module_url', array('required'=> $this->lang->line('module_url_req')));
        $this->form_validator->add_rules('user_role_id', array('required'=> $this->lang->line('user_role_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        /*if(isset($data['user_role_id']))
            $data['user_role_id']=pk_decrypt($data['user_role_id']);*/
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //$session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $session_user_id=$this->session_user_id;
        $session_user_info=$this->User_model->getUserInfo(array('user_role_id'=>$data['user_role_id'],'user_id'=>$session_user_id));
        if(count($session_user_info)==0)
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['module_url'] = str_replace('#','',$data['module_url']);
        if(($data['module_url'] == '/templates' || $data['module_url'] == '/modules' || $data['module_url'] == '/questions' || $data['module_url'] == '/modules/topics' || $data['module_url'] == '/questions/view' || $data['module_url'] == '/templates/preview' || $data['module_url'] == '/templates/view') && ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4 ) && ($this->session_user_info->content_administator_review_templates == 1) )
        {
            //if condition for module url is /templates or /modules  and user have content administor review template access then changing user role id to 2 because for templates and Manage Review Template having same module url , modules and Manage Review Modules same  module url and questions and Manage Review Question same  module url
            // we are change user role id to customer admin
            $data['user_role_id'] = 2 ;
        }
        //print_r($data);exit;
        $result = $this->User_model->getModules($data);

        for($s=0;$s<count($result);$s++)
        {
            if($result[$s]['module_url']==$data['module_url'])
            {
                if( $result[$s]['sub_module']==0){
                    for($st=0;$st<count($result);$st++){

                        if($result[$s]['id_app_module']==$result[$st]['app_module_id']){


                            // $this->order_data[] = 
                            // array(
                            //     $result[$st]['action_key'] => ($result[$st]['app_module_access_status']==1)? true : false
                            // );
                            //echo $result[$st]['module_key'];
                            if($result[$st]['app_module_access_status']==1)
                            {
                                $appModuleaccess =true;
                            }
                            elseif(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4 ) && in_array($result[$st]['module_key'] , array('manage-workflows','currency','manage-workflow-questions','provider_create','workflow_topics','workflow_question_view','customer-contract-builder')))
                            {
                                
                                if(($result[$st]['module_key'] == 'manage-workflows' || $result[$st]['module_key'] == 'manage-workflow-questions' || $result[$st]['module_key'] == 'workflow_topics' || $result[$st]['module_key'] == 'workflow_question_view' || $result[$st]['module_key'] == 'workflow_preview' || $result[$st]['module_key'] == 'workflow_view') && $this->session_user_info->content_administator_task_templates == 1)
                                {
                                    $appModuleaccess =true;
                                }
                                elseif($result[$st]['module_key'] == 'currency' && $this->session_user_info->content_administator_currencies == 1)
                                {
                                    $appModuleaccess =true;
                                }
                                elseif($result[$st]['module_key'] == 'provider_create' && $this->session_user_info->content_administator_relation == 1)
                                {
                                    $appModuleaccess =true;
                                }
                                elseif($result[$st]['module_key'] == 'customer-contract-builder' && $this->session_user_info->legal_and_content_administator == 1)
                                {
                                    $appModuleaccess =true;
                                }
                                elseif($result[$st]['module_key'] == 'catalogue' && $this->session_user_info->content_administator_catalogue == 1)
                                {
                                    $appModuleaccess =true;
                                }
                                else{
                                    $appModuleaccess =false;
                                }
                            }
                            // elseif($this->session_user_info_role_i)
                            else
                            {
                                $appModuleaccess =false;
                            }
                            $this->order_data[] = array($result[$st]['action_key'] => $appModuleaccess);
                        }
                    }
                }

                $this->getChildNodes($result,$result[$s]['id_app_module']);
                break;
            }
        }
        if($this->cnt==0){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //$result = array('status'=>TRUE, 'message' =>'success', 'data'=>$this->order_data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$this->order_data ,'versionUpdated' => $versionUpdated);
        $this->response($result, REST_Controller::HTTP_OK);
        //echo json_encode($result); exit;
    }

    public function getChildNodes($data,$parent_id)
    {
        for($s=0;$s<count($data);$s++)
        {
            if($data[$s]['parent_module_id']==$parent_id){
                if( $data[$s]['sub_module']==0){
                    for($st=0;$st<count($data);$st++){
                        if($data[$s]['id_app_module']==$data[$st]['app_module_id']){
                            if($data[$st]['app_module_access_status']==1){ $this->cnt=1; }
                            if($data[$st]['action_name']=='list' || $data[$st]['action_name']=='add'){
                                $this->order_data[] = array(
                                    $data[$st]['action_key'] => false
                                );
                            }else{
                                $this->order_data[] = array(
                                    $data[$st]['action_key'] => ($data[$st]['app_module_access_status']==1)? true : false
                                );
                            }
                        }
                    }
                }
                $this->getChildNodes($data,$data[$s]['id_app_module']);
            }
        }
        return $this->order_data;
    }

    public function accessLog_post()
    {
        $data = $this->input->post();
        // print_r($data);exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('user_id', array('required'=> $this->lang->line('user_id_req')));
        $this->form_validator->add_rules('action_name', array('required'=> $this->lang->line('action_name_req')));
        $this->form_validator->add_rules('action_url', array('required'=> $this->lang->line('action_url_req')));
        //$this->form_validator->add_rules('action_description', array('required'=> $this->lang->line('action_description_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*if(isset($data['user_id']))
            $data['user_id']=pk_decrypt($data['user_id']);*/
        //echo $data['user_id'].'userid '.$this->session_user_id.'sess';exit;
        if(isset($data['user_id'])) {
            $data['user_id'] = pk_decrypt($data['user_id']);
            if($data['user_id']!=$this->session_user_parent_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*if(isset($data['acting_user_id']))
            $data['acting_user_id']=pk_decrypt($data['acting_user_id']);*/
        if(isset($data['acting_user_id'])) {
            $data['acting_user_id'] = pk_decrypt($data['acting_user_id']);
            if($data['acting_user_id']!=$this->session_user_id_acting){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id']))
            $data['id']=pk_decrypt($data['id']);
        $this->User_model->addAccessLog(array(
            'user_id' => $data['user_id'],
            'acting_user_id' => isset($data['acting_user_id'])?$data['acting_user_id']:NULL,
            'access_token' => isset($data['access_token'])?$data['access_token']:NULL,
            'name' => isset($data['name'])?$data['name']:'',
            'id' => isset($data['id'])?$data['id']:'',
            'module_type' => isset($data['module_type'])?$data['module_type']:'',
            'action_name' => $data['action_name'],
            'action_description' => isset($data['action_description'])?$data['action_description']:NULL,
            'action_url' => $data['action_url'],
            'created_on' => currentDate()
        ));

        $result = array('status'=>TRUE, 'message' =>'success', 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function loginasuser_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //$this->form_validator->add_rules('login_as_id_user', array('required'=>$this->lang->line('user_role_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*if(isset($data['user_role_id']))
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);*/
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['login_as_user_role_id']))
            $data['login_as_user_role_id'] = pk_decrypt($data['login_as_user_role_id']);
        if(isset($data['id_user']))
            $data['id_user'] = pk_decrypt($data['id_user']);
        /*if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }*/
        if(isset($data['login_as_id_user']))
            $data['login_as_id_user'] = pk_decrypt($data['login_as_id_user']);

        if(isset($data['user_role_id']) || isset($data['login_as_user_role_id'])){
            $user_role_id=$data['user_role_id'];
            if(isset($data['login_as_user_role_id']))
                $user_role_id=$data['login_as_user_role_id'];
        }
        if(isset($data['id_user']) || isset($data['login_as_id_user'])){
            $user_id=$data['id_user'];
            if(isset($data['login_as_id_user']))
                $user_id=$data['login_as_id_user'];
        }
        $result = $this->User_model->getUserInfo(array('user_id'=>$user_id));
        if($result->contribution_type=='2' || $result->contribution_type=='3'){   
            $result->user_type = "external";
        }else{
            $result->user_type = "internal";
        }
        if($result->contribution_type=='2')
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $result->language_id = pk_encrypt($result->language_id);
        if($this->session_user_id_acting==NULL) {
            if ($this->session_user_info->user_role_id>2) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if ($this->session_user_info->user_role_id == 1 && $result->user_role_id==1) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if ($this->session_user_info->user_role_id == 2 && $result->user_role_id==2) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if ($this->session_user_info->user_role_id == 2 && !in_array($user_id, $this->session_user_customer_all_users)) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        else{
            if ($user_id != $this->session_user_parent_id) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }



        $access_token = '';
        if(empty($result))
        {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('login_error')),'data'=>'');
            echo json_encode($result);exit;
        }
        else
        {
            if($result->profile_image!='') {
                $result->profile_image_medium = getImageUrl($result->profile_image, 'profile', MEDIUM_IMAGE,'profile_images/');
                $result->profile_image_small = getImageUrl($result->profile_image, 'profile', SMALL_IMAGE,'profile_images/');
                $result->profile_image = getImageUrl($result->profile_image, 'profile','','profile_images/');
            }

            if($result->user_role_id!=1) {
                $customer = $this->Customer_model->getCustomer(array('id_customer' => $result->customer_id));
                if(!empty($customer)){
                    if($customer[0]['company_logo']=='') {
                        $result->customer_logo_medium = getImageUrl($customer[0]['company_logo'], 'company');
                        $result->customer_logo_small = getImageUrl($customer[0]['company_logo'], 'company');
                        $result->customer_logo = getImageUrl($customer[0]['company_logo'], 'company');
                    }
                    else{
                        $result->customer_logo_medium = getImageUrl($customer[0]['company_logo'], 'profile', MEDIUM_IMAGE);
                        $result->customer_logo_small = getImageUrl($customer[0]['company_logo'], 'profile', SMALL_IMAGE);
                        $result->customer_logo = getImageUrl($customer[0]['company_logo'], 'profile');
                    }
                }
            }

            if(!in_array($result->user_role_id,array(1,2))) {
                $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $result->id_user));//echo $this->db->last_query();exit;
                $result->business_unit = array();
                for($s=0;$s<count($business_unit);$s++)
                {
                    $result->business_unit[] = array(
                        'business_unit_id' => $business_unit[$s]['id_business_unit'],
                        'bu_name' => $business_unit[$s]['bu_name']
                    );
                }
            }
            // print_r(array_column($result->business_unit,'business_unit_id'));exit;
            
            if($result->user_role_id==2){

                $bu_units = $this->Business_unit_model->getBusinessUnitList(array('customer_id'=>$result->customer_id,'business_unit_array'=>array()));
            }
            else{
                // $bu_units = $this->Business_unit_model->getBusinessUnitList(array('customer_id'=>$result->customer_id,'business_unit_array'=>array_column($result->business_unit,'business_unit_id')));
                $bu_units['data'] = $this->Business_unit_model->getBusinessUnitUser(array('customer_id'=>$result->customer_id,"user_id"=>$result->id_user));
            }
            if(!empty($bu_units)){
                $buids=array_column($bu_units['data'],'id_business_unit');
                $business_unit_id_encrypted = array_map(function($i){ return pk_encrypt($i); },$buids);
                $result->encrypted_bu_ids=implode(',',$business_unit_id_encrypted);
            }
            else
            {
                $result->encrypted_bu_ids='';  
            }
            // print_r(implodse(',',$business_unit_id_encrypted));exit;
            $result->iroori='annus';
            if($result->user_role_id==6) {
                $result->iroori="itako";
            }
            $menu = $this->User_model->menu(array('user_role_id' => $result->user_role_id,'user_id'=>$result->id_user,'language_iso_code' => $result->language_iso_code));
            $this->session->set_userdata('session_user_id_acting',$result->id_user);

            /*if(!empty($this->session->userdata('session_user_id_acting')) && ($this->session->userdata('session_user_id_acting')==$this->session->userdata('session_user_id'))) {
                $this->session->set_userdata('session_user_id_acting',NULL);
                $this->session->unset_userdata('session_user_id_acting');
            }*/
            $this->User_model->updateUserLogin(array(
                'child_user_id' => $result->id_user,
                'access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])
            ));

            $getUserLogin=$this->User_model->getUserLogin(array('access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])));
            if(isset($getUserLogin[0]['parent_user_id'])){
                if($getUserLogin[0]['parent_user_id']==$getUserLogin[0]['child_user_id']){
                    $this->User_model->updateUserLogin(array(
                        'child_user_id' => NULL,
                        'access_token' => str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION'])
                    ));
                }
            }
        }
        if(isset($result->id_user))
            $result->id_user=pk_encrypt($result->id_user);
        if(isset($result->customer_id)){
            $result->import_subscription = (int)$this->User_model->check_record_selected('import_subscription','customer',array('id_customer'=>$result->customer_id))[0]['import_subscription'];
            $result->customer_id=pk_encrypt($result->customer_id);

        }
        if(isset($result->user_role_id))
            $result->user_role_id=pk_encrypt($result->user_role_id);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' => $result,'menu' => $menu));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function ldapdata_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id']))
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        $chk_rec = $this->User_model->check_record('customer_ldap',array('customer_id'=>$data['customer_id']));
        if(count($chk_rec)>0){
            $result = array('status'=>true, 'message' => $this->lang->line('success'), 'data'=> $chk_rec[0]);
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result = array('status'=>true, 'message' => $this->lang->line('success'), 'data'=> '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function ldap_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('host', array('required'=>$this->lang->line('host_req')));
        $this->form_validator->add_rules('dc', array('required'=>$this->lang->line('dc_req')));
        $this->form_validator->add_rules('port', array('required'=>$this->lang->line('port_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status_req')));

        if(isset($data['status'])&&($data['status'] == 1))
        {
            $this->form_validator->add_rules('sso_check', array('required'=>$this->lang->line('sso_check_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id']))
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        if(isset($data['id_user'])){
            $data['id_user'] = pk_decrypt($data['id_user']);
            if (['id_user']!=1) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $chk_rec = $this->User_model->check_record('customer_ldap',array('customer_id'=>$data['customer_id']));
        if(count($chk_rec)>0){
            $update_data = array(
                'customer_id'=>$data['customer_id'],
                'dc'=>$data['dc'],
                'host'=>$data['host'],
                'port'=>$data['port'],
                'updated_on'=>currentDate(),
                'status'=>$data['status'],
                'sso_check'=>$data['sso_check'],
                'updated_by'=>$this->session_user_id
            );
            $update = $this->User_model->update_data('customer_ldap',$update_data,array('customer_id'=>$data['customer_id']));
            if($update){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('updated'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }else{
                $result = array('status'=>false, 'message' => $this->lang->line('not_updated'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }else{
            $insert_data = array(
                'customer_id'=>$data['customer_id'],
                'dc'=>$data['dc'],
                'host'=>$data['host'],
                'port'=>$data['port'],
                'created_on'=>currentDate(),
                'status'=>$data['status'],
                'sso_check'=>$data['sso_check'],
                'created_by'=>$this->session_user_id
            );
            $insert_id = $this->User_model->insert_data('customer_ldap',$insert_data);
            if($insert_id>0){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('inserted'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }else{
                $result = array('status'=>false, 'message' => $this->lang->line('not_inserted'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
    }

    //saml 
    public function samldata_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id']))
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        $chk_rec = $this->User_model->check_record('customer_saml',array('customer_id'=>$data['customer_id']));
        if(count($chk_rec)>0){
            $result = array('status'=>true, 'message' => $this->lang->line('success'), 'data'=> $chk_rec[0]);
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result = array('status'=>true, 'message' => $this->lang->line('success'), 'data'=> '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function saml_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('issuer_url', array('required'=>$this->lang->line('issuer_url_req')));
        $this->form_validator->add_rules('certificate', array('required'=>$this->lang->line('certificate_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status_req')));
        if(isset($data['status'])&&($data['status'] == 1))
        {
            $this->form_validator->add_rules('sso_check', array('required'=>$this->lang->line('sso_check_req')));
        }
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id']))
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        if(isset($data['id_user'])){
            $data['id_user'] = pk_decrypt($data['id_user']);
            if (['id_user']!=1) {
                $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('permission_not_allowed')), 'data' => '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $chk_rec = $this->User_model->check_record('customer_saml',array('customer_id'=>$data['customer_id']));
        if(count($chk_rec)>0){
            $update_data = array(
                'customer_id'=>$data['customer_id'],
                'issuer_url'=>$data['issuer_url'],
                'certificate'=>$data['certificate'],
                'login_url'=>$data['login_url'],
                'logout_url'=>$data['logout_url'],
                'updated_on'=>currentDate(),
                'status'=>$data['status'],
                'sso_check'=>$data['sso_check'],
                'updated_by'=>$this->session_user_id
            );
            $update = $this->User_model->update_data('customer_saml',$update_data,array('customer_id'=>$data['customer_id']));
            if($update){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('saml_updated'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }else{
                $result = array('status'=>false, 'message' => $this->lang->line('not_updated'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }else{
            $insert_data = array(
                'customer_id'=>$data['customer_id'],
                'issuer_url'=>$data['issuer_url'],
                'certificate'=>$data['certificate'],
                'login_url'=>$data['login_url'],
                'logout_url'=>$data['logout_url'],
                'created_on'=>currentDate(),
                'status'=>$data['status'],
                'sso_check'=>$data['sso_check'],
                'created_by'=>$this->session_user_id
            );
            $insert_id = $this->User_model->insert_data('customer_saml',$insert_data);
            if($insert_id>0){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('saml_inserted'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }else{
                $result = array('status'=>false, 'message' => $this->lang->line('not_inserted'), 'data'=> '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
    }

    public function mfa_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('is_email_verification_active', array('required'=>$this->lang->line('is_email_verification_active_req')));
        $this->form_validator->add_rules('is_mfa_active', array('required'=>$this->lang->line('is_mfa_active_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id']))
        $data['customer_id'] = pk_decrypt($data['customer_id']);
        if($data['is_mfa_active'] == 1 && $data['is_email_verification_active'] == 0)
        {
            $result = array('status' => FALSE, 'error' => array('message'=>$this->lang->line('email_verification_should_active')), 'data' => '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $update_data = array(
            'is_email_verification_active'=>$data['is_email_verification_active'],
            'is_mfa_active'=>$data['is_mfa_active'],
            'updated_by'=>$this->session_user_id
        );
        $update = $this->User_model->update_data('customer',$update_data,array('id_customer'=>$data['customer_id']));
        if($update){
            $result = array('status'=>TRUE, 'message' => $this->lang->line('mfa_updated'), 'data'=> '');
            $this->response($result, REST_Controller::HTTP_OK);
        }else{
            $result = array('status'=>false, 'message' => $this->lang->line('not_updated'), 'data'=> '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function UserRecorCount_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(count($data) > 0){
            $this->User_model->Update_data('user',array("display_rec_count"=>$data['display_rec_count']),array("id_user"=>$this->session_user_id));
            // echo '<pre>'.$this->db->last_query();
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=> '');
            $this->response($result, REST_Controller::HTTP_OK);
        }else{
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }
    public function getResponsibleUserForFilter_get(){
        $data = $this->input->get();
        $customer_id = $this->session_user_info->customer_id;
        //$this->Business_unit_model->getBusinessUnitUser(array('customer_id'=>$customer_id,"user_id"=>$this->session_user_id));
        $query = 'select u.id_user,u.user_role_id,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name from business_unit_user buu LEFT JOIN user u on u.id_user = buu.user_id LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role LEFT JOIN business_unit bu ON bu.id_business_unit=buu.business_unit_id WHERE business_unit_id IN (select id_business_unit from business_unit where customer_id='.$customer_id.') and  buu.status = 1 and  u.user_role_id not in (2,5,6) GROUP BY id_user';
        $users = $this->User_model->custom_query($query);
        foreach($users as $k=>$val)
        {
            $users[$k]['id_user'] = pk_encrypt($users[$k]['id_user']);
        }
        $result = array('status'=>true, 'data' => $users,'message' => $this->lang->line('success'));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function getContributersForFilter_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('contributor_type', array('required'=>$this->lang->line('contribution_type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $customer_id = $this->session_user_info->customer_id;
        $data['customer_id'] =  $customer_id;
        $data['type'] = 'contributor';
        $users = $this->Contract_model->getCustomerUsers_add($data);
        foreach($users as $k=>$val)
        {
            $users[$k]['id_user'] = pk_encrypt($users[$k]['id_user']);
        }
        $result = array('status'=>true, 'data' => $users,'message' => $this->lang->line('success'));
        $this->response($result, REST_Controller::HTTP_OK);
    }
}