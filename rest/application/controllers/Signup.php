<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
require APPPATH . '/third_party/mailer/mailer.php';

class Signup extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        if(!empty($getLoggedUserId))
        {
            $userDetails=$this->User_model->getUserInfo(array('user_id'=>$getLoggedUserId[0]['id']));
            if(isset($userDetails) && !empty($userDetails->language_iso_code)){
                $language = $userDetails->language_iso_code;
            }
        }
        else
        {
            $language = 'en';
        }
        
        if(isset($_SERVER['HTTP_LANG']) && $_SERVER['HTTP_LANG']!=''){
            $language = $_SERVER['HTTP_LANG'];
            if(is_dir('application/language/'.$language)==0){
                $language = $this->config->item('rest_language');
            }
        }
        $this->lang->load('rest_controller', $language);
    }



    public function login()
    {
        $this->load->library('oauth/oauth');
        $this->config->load('rest');

        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }

        if(isset($_POST['requestData']) && DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $data = $aesObj->decrypt($_POST['requestData'],AES_KEY);
            $data = (array) json_decode($data,true);
            $_POST = $data;
        }

        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'message'=>$this->lang->line('login_error'),'data'=>'');
            echo json_encode($result); exit;
        }
        //validating inputs
        if(!(isset($data['login_with_saml']) && ($data['login_with_saml'] == 1)))
        {
            $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req'),'valid_email' => $this->lang->line('email_invalid')));
            $this->form_validator->add_rules('password', array('required'=> $this->lang->line('password_req')));
        }
        else
        {
            $tokenVerification = $this->User_model->check_record('saml_log' , array('uuid' => $data['token']));
            if(!empty($tokenVerification))
            {
                $data['email_id'] = $tokenVerification[0]['email'];
                if(empty($tokenVerification[0]['user_id']))
                {
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('contact_your_administator')),'data'=>'');
                    echo json_encode($result); exit;
                }
            }
            else
            {
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'','logout_url'=>SAML_LOGOUT);
                echo json_encode($result); exit;
            }
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        if($data['email_id'] == PUBLIC_API_EMAIL_ID)
        {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
            echo json_encode($result); exit;
        }

        //decoding password
        $data['password'] = base64_decode($data['password']);

        $customer = $this->User_model->check_email(array('email'=>$data['email_id'] ,'contribution_type_not_equal_to' => 2));
        if(count($customer)==0){
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
            echo json_encode($result); exit;
        }
        $ldap_status = $this->User_model->check_record('customer_ldap',array('customer_id'=>$customer->customer_id,'status'=>1));
        $saml_status = $this->User_model->check_record('customer_saml',array('customer_id'=>$customer->customer_id,'status'=>1));

        //through error message for saml and ldap users who direct login with cockpit application
        $userType = ($customer->contribution_type=='2' || $customer->contribution_type=='3')?"external":"internal";
        $mailExt = substr($data['email_id'], strripos($data['email_id'],"@"));
        $customerInfo = $this->User_model->check_record('customer',array('id_customer'=>$customer->customer_id));
        if(!empty($saml_status)  && $userType == 'internal' && (!(isset($data['login_with_saml']) && $data['login_with_saml']==1)))
        {
            if(in_array($mailExt,explode(',',$saml_status[0]['sso_check'])))
            {
                // login with SSO and through error message
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                echo json_encode($result); exit;
            }
        }
        if(!empty($ldap_status)  && $userType == 'internal' && (!(isset($data['login_with_ldap']) && $data['login_with_ldap']==1)))
        {
            if(in_array($mailExt,explode(',',$ldap_status[0]['sso_check'])))
            {
                // login with LDAP and through error message
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                echo json_encode($result); exit;
            }
        }
        if((!empty($customerInfo)) && ($customerInfo[0]['is_mfa_active'] == 1) && ($customerInfo[0]['is_email_verification_active'] ==1) && (!(isset($data['login_with_saml']) && $data['login_with_saml']==1)) && (!(isset($data['login_with_ldap']) && $data['login_with_ldap']==1)))
        {
            if(isset($data['device_id']) && $data['device_id']!="")
            {
                $trustedDevices = $this->User_model->check_record('trusted_device',array('device_uuid'=>$data['device_id'],'user_id'=>$customer->id_user,'client_broswer' => $_SERVER['HTTP_USER_AGENT'] , 'client_remote_address' => $_SERVER['REMOTE_ADDR'] , 'status' => 1));
                //echo $this->db->last_query();
                if(!empty($trustedDevices))
                {
                    if(!(($trustedDevices[0]['validity_type'] == "multiple" &&  $trustedDevices[0]['valid_upto'] > date('Y-m-d H:i:s'))||($trustedDevices[0]['validity_type'] == "once")))
                    {
                        // login with MFA and through error message
                        $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                        echo json_encode($result); exit;
                    }
                }
                else
                {
                    // login with MFA and through error message
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                    echo json_encode($result); exit;
                }
            }
            else
            {
                // login with MFA and through error message
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                echo json_encode($result); exit;
            }
            
        }
            if(isset($data['login_with_ldap']) && $data['login_with_ldap']==1){
                if(count($ldap_status)>0){
                    $params=array('host'=>$ldap_status[0]['host'],'port'=>$ldap_status[0]['port'],'dc'=>$ldap_status[0]['dc']);
                    $this->load->library('LdapAuthentication',$params);
                    $is_login=$this->ldapauthentication->login($data['email_id'],$data['password']);
                    if($is_login['status']===true){
                        $result = $this->User_model->ldap_login($data);
                    }
                    else{
                        //echo 'invalid ';
                        $result = array('status'=>FALSE,'error'=>array('message'=>$is_login['message']),'data'=>'');
                        //echo json_encode($result); exit;
                    }
                }else{
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                    echo json_encode($result); exit;
                }
            }if(isset($data['login_with_saml']) && $data['login_with_saml']==1){
                if(count($saml_status)>0){
                    $is_login=$this->samltokenverification(array('email_id' => $data['email_id'] , 'token' => $data['token']));
                    if($is_login['status']===true){
                        $result = $this->User_model->saml_login($data);
                    }
                    else{
                        $result = array('status'=>FALSE,'error'=>array('message'=>$is_login['message']),'data'=>'');
                    }
                }else{
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'','logout_url'=>SAML_LOGOUT);
                    echo json_encode($result); exit;
                }
            }
            else{
                $result = $this->User_model->login($data);
            }//echo '<pre>'.print_r($result);exit;
        $access_token = '';
        if(empty($result) || (isset($data['login_with_ldap']) && $data['login_with_ldap']==1 && $is_login['status']===false) || (isset($data['login_with_saml']) && $data['login_with_saml']==1 && $is_login['status']===false))
        {
            $user_info = $this->User_model->check_email(array('email'=>$data['email_id']));
            if(empty($user_info)){
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('invaid_user')),'data'=>'');
                echo json_encode($result);exit;
            }
            $is_blocked=$user_info->is_blocked;
            $last_password_attempt_date=$user_info->last_password_attempt_date;
            $no_of_password_attempts=$user_info->no_of_password_attempts;
            //echo '$is_blocked'.$is_blocked.' '.'$last_password_attempt_date'.$last_password_attempt_date.' '.'$no_of_password_attempts'.' '.$no_of_password_attempts;
            if($last_password_attempt_date==null){
                // || $last_password_attempt_date != date("Y-m-d")
                $attempt_date = date("Y-m-d");
                $no_of_password_attempts=1;
                $this->User_model->updateUser(array('no_of_password_attempts'=>1,'last_password_attempt_date'=>$attempt_date,'is_blocked'=>0),$user_info->id_user);
                $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$_SERVER['HTTP_USER_AGENT'],'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                //$result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('two_more_attempts')),'data'=>'');
                //echo json_encode($result);exit;
            }
            else{
                //$last_password_attempt_date == date("Y-m-d")
                $no_of_password_attempts=$no_of_password_attempts+1;
                $this->User_model->updateUser(array('no_of_password_attempts'=>$no_of_password_attempts,'last_password_attempt_date'=>date("Y-m-d"),'is_blocked'=>0),$user_info->id_user);
                $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$_SERVER['HTTP_USER_AGENT'],'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                /*if($no_of_password_attempts<3){
                    if($no_of_password_attempts >= 2){
                        $this->User_model->updateUser(array('no_of_password_attempts'=>$no_of_password_attempts+1,'last_password_attempt_date'=>date("Y-m-d"),'is_blocked'=>1),$user_info->id_user);
                        $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('account_block_error')),'data'=>'');
                        echo json_encode($result);exit;
                    }else{
                        $this->User_model->updateUser(array('no_of_password_attempts'=>$no_of_password_attempts+1,'last_password_attempt_date'=>date("Y-m-d")),$user_info->id_user);
                        $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('one_more_attempts')),'data'=>'');
                        echo json_encode($result);exit;
                    }
                }else{
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('account_block_error')),'data'=>'');
                    echo json_encode($result);exit;
                }*/
            }
            if($no_of_password_attempts>=MAX_INVALID_PASSWORD_ATTEMPTS){
                $this->User_model->updateUser(array('is_blocked'=>1),$user_info->id_user);
                $client_browser = getUserBrowser($_SERVER['HTTP_USER_AGENT']);
                $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$client_browser,'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                $result = array('status'=>FALSE,'error'=>array('message'=>str_replace('%s',MAX_INVALID_PASSWORD_ATTEMPTS,$this->lang->line('account_block_error'))),'data'=>'');
                echo json_encode($result);exit;
            }
            else{
                $result = array('status'=>FALSE,'error'=>array('message'=>str_replace('%s',MAX_INVALID_PASSWORD_ATTEMPTS-$no_of_password_attempts,$this->lang->line('one_more_attempts'))),'data'=>'');
                echo json_encode($result);exit;
            }
            /*if($last_password_attempt_date != date("Y-m-d")){
                $this->User_model->updateUser(array('no_of_password_attempts'=>1,'last_password_attempt_date'=>date("Y-m-d"),'is_blocked'=>0),$user_info->id_user);
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('two_more_attempts')),'data'=>'');
                echo json_encode($result);exit;
            }*/

        }
        else
        {
            if($result->user_status!=1) {
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('login_inactive_error')),'data'=>'');
                echo json_encode($result);exit;
            }
            if($result->is_blocked==1) {
                // && $result->last_password_attempt_date==date("Y-m-d")
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('account_block_error')),'data'=>'');
                echo json_encode($result);exit;
            }
            else{
                if($result->contribution_type=='2' || $result->contribution_type=='3'){   
                    $result->user_type = "external";
                }else{
                    $result->user_type = "internal";
                }
                $this->User_model->updateUser(array('no_of_password_attempts'=>0,'last_password_attempt_date'=>NULL,'is_blocked'=>0),$result->id_user);
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
                    $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $result->id_user));
                    $result->business_unit = array();
                    for($s=0;$s<count($business_unit);$s++)
                    {
                        $result->business_unit[] = array(
                            'business_unit_id' => $business_unit[$s]['id_business_unit'],
                            'bu_name' => $business_unit[$s]['bu_name']
                        );
                    }
                }
                if($result->user_role_id==2){
                    $bu_units = $this->Business_unit_model->getBusinessUnitList(array('customer_id'=>$result->customer_id,'business_unit_array'=>array()));
                }
                else{
                    // $bu_units = $this->Business_unit_model->getBusinessUnitUser(array('customer_id'=>$result->customer_id,'business_unit_array'=>array_column($result->business_unit,'business_unit_id'),'user_role_id'=>$result->user_role_id));
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
                //print_r($result->encrypted_bu_ids);exit;


                $menu = $this->User_model->menu(array('user_role_id' => $result->user_role_id,'user_id'=>$result->id_user,'language_iso_code' => $result->language_iso_code));
                $rest_auth = strtolower($this->config->item('rest_auth'));
                if($rest_auth=='oauth'){
                    $client_credentials = $this->User_model->createOauthCredentials($result->id_user,$result->first_name,$result->last_name);
                    $client_id = $client_credentials["client_id"];
                    $secret  =$client_credentials["client_secret"];
                    $this->load->library('Oauth');

                    $_REQUEST['grant_type'] = 'client_credentials';
                    $_REQUEST['client_id'] = $client_id;
                    $_REQUEST['client_secret'] = $secret;
                    $_REQUEST['scope'] = '';
                    $oauth = $this->oauth;
                    $token =(object) $oauth->generateAccessToken();
                    $access_token = $token->token_type.' '.$token->access_token;
                }

                /* Updating last Login*/
                $this->User_model->updateUser(array('last_logged_on' => currentDate()),$result->id_user);

                /* User log start */
                $server = $_SERVER;
                $this->User_model->addUserLog(array(
                    'user_id' => $result->id_user,
                    'client_browser' => $server['HTTP_USER_AGENT'],
                    'client_os' => getUserOS($server['HTTP_USER_AGENT']),
                    'client_remote_address' => $server['REMOTE_ADDR'],
                    'logged_on' => currentDate()
                ));
                /* User log end */
                $result->iroori='annus';
                if($result->user_role_id==6) {
                    $result->iroori="itako";
                }
                /*if(!empty($this->session->userdata('session_user_id_acting')))
                    $this->session->unset_userdata('session_user_id_acting');
                if(!empty($this->session->userdata('session_user_id')))
                    $this->session->unset_userdata('session_user_id');
                $this->session->set_userdata('session_user_id',$result->id_user);*/
                $this->User_model->addUserLogin(array(
                    'parent_user_id' => $result->id_user,
                    'child_user_id' => NULL,
                    'access_token' => isset($token->access_token)?$token->access_token:NULL
                ));
            }
        }
        $result->language_id=pk_encrypt($result->language_id);
        if(isset($result->id_user))
            $result->id_user=pk_encrypt($result->id_user);
        if(isset($result->customer_id)){
            $result->import_subscription = (int)$this->User_model->check_record_selected('import_subscription','customer',array('id_customer'=>$result->customer_id))[0]['import_subscription'];
            $result->customer_id=pk_encrypt($result->customer_id);
        }
        if(isset($result->user_role_id))
            $result->user_role_id=pk_encrypt($result->user_role_id);
        if(isset($data['login_with_saml']) && $data['login_with_saml']==1)
        {
            $isSamlLogin = true;
            $SamlLogOutUrl = SAML_LOGOUT;
        }  
        else{
            $isSamlLogin = false;
            $SamlLogOutUrl = '';
        }  
        if(!empty($trustedDevices) && $trustedDevices[0]['validity_type'] == "once")
        {
            $this->User_model->update_data('trusted_device',array('status' => 0),array('id_trust_device' => $trustedDevices[0]['id_trust_device']));
            //echo $this->db->last_query();exit;
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' => $result,'menu' => $menu ,'isSamlLogin' => $isSamlLogin ,'SamlLogOutUrl' => $SamlLogOutUrl), 'access_token' => $access_token);
        echo json_encode($result);exit;
    }

    public function forgetPassword()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        if(isset($_POST['requestData']) && DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $data = $aesObj->decrypt($_POST['requestData'],AES_KEY);
            $data = (array) json_decode($data,true);
            $_POST = $data;
        }
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            echo json_encode($result);exit;
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
            //Message should not be shown
            $result = array('status'=>TRUE, 'message' => $this->lang->line('new_password'), 'data'=>'');
            echo json_encode($result);exit;
        }
        else
        {
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $result->customer_id));

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
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
            $this->User_model->changePassword(array('user_id' => $result->id_user,'password' => $result->password));

            $new_password = generatePassword(8);
            $this->User_model->updatePassword($new_password,$result->id_user);

            //$user_info = $this->User_model->getUserInfo(array('user_id' => $result->id_user));
           /* $message = str_replace(array('{first_name}','{last_name}','{password}'),array($result->first_name,$result->last_name,$new_password),$this->lang->line('forget_password_mail'));

            $template_data = array(
                'web_base_url' => WEB_BASE_URL,
                'message' => $message,
                'mail_footer' => $this->lang->line('mail_footer')
            );
            $subject = $this->lang->line('forget_password_subject');
            $template_data = $this->parser->parse('templates/notification.html',$template_data);
            sendmail($data['email'],$subject,$template_data);*/

            $user_info = $this->User_model->getUserInfo(array('user_id' => $result->id_user));
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
            echo json_encode($result);exit;
        }
    }

    public function activeAccount($code)
    {
        $user = $this->User_model->activeAccount($code);
        if($user==1){
            echo "<h3>Account activated successfully.</h3>";
        }
        else{
            echo "<h3>Invalid request.</h3>";
        }
        redirect(WEB_BASE_URL);
    }
    public function getEncryptionSettings()
    {
        $data['AES_KEY']=AES_KEY;
        $data['DATA_ENCRYPT']=DATA_ENCRYPT;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$data);
        echo base64_encode(json_encode($result));exit;
    }

    public function renewalToken()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            echo json_encode($result);exit;
        }
        $access_token = $data['Authorization'];
        $user_id = $data['User'];
        $res = $this->User_model->getTokenDetails($access_token,$user_id);
        if(empty($res)){
            $result = array('status'=>FALSE,'error'=>'Invalid token','data'=>'');
            echo json_encode($result);exit;
        }
        if(((time() - $res[0]['expire_time']) > 0)){
            $new_token = file_get_contents(REST_API_URL.'welcome/oauth?grant_type=client_credentials&client_id='.$res[0]['client_id'].'&client_secret='.$res[0]['secret'].'&scope=');
            $new_token = json_decode($new_token);
            $access_token = $new_token->token_type.' '.$new_token->access_token;
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'', 'access_token' => $access_token);
        }
        else{
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'', 'access_token' => $res[0]['access_token']);
        }
        echo json_encode($result);exit;
    }
    public function test(){
        $path='uploads/';
        $data['customer_id']='test';
        $path=FILE_SYSTEM_PATH.'uploads/';
        if(!is_dir($path.$data['customer_id'])){ mkdir($path.$data['customer_id']); }
    }
    function ldaptest(){
        $params=array('host'=>'ldaps://ldaps.with-services.com','port'=>'636','dc'=>'with-services,com');
        $this->load->library('LdapAuthentication',$params);
        //testuserscp@with-services.com
        $is_login=$this->ldapauthentication->logintest('testuserscp@with-services.com','Source2018!');
        var_dump($is_login);
        if($is_login===true){
            echo 'valid';
        }
        else{
            echo 'invalid';
        }
    }
    public function checkmail(){
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'message'=>$this->lang->line('login_error'),'data'=>'');
            echo json_encode($result); exit;
        }
         //validating inputs
         $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req'),
         'valid_email' => $this->lang->line('email_invalid')
        ));
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        $userInfo = $this->User_model->check_email(array('email'=>$data['email_id'] ,'contribution_type_not_equal_to' => 2));//echo '<pre>'.$this->db->last_query();exit;
        if(count($userInfo)==0){
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
            echo json_encode($result); exit;
        }
        if($userInfo->user_status!=1) {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('login_inactive_error')),'data'=>'');
            echo json_encode($result);exit;
        }
        if($userInfo->is_blocked==1) {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('account_block_error')),'data'=>'');
            echo json_encode($result);exit;
        }
        $email = $data['email_id'];
        if(isset($data['trusted_device_id']))
        {
            $trusted_device_id = $data['trusted_device_id'];
        }
        else{
            $trusted_device_id = null;
        }
        
        $ldap = $this->User_model->check_record('customer_ldap',array('customer_id'=>$userInfo->customer_id,'status'=>1));
        $sso = $this->User_model->check_record('customer_saml',array('customer_id'=>$userInfo->customer_id,'status'=>1));
        $customerInfo = $this->User_model->check_record('customer',array('id_customer'=>$userInfo->customer_id));
        $data =array();
        $data['show_password'] = false;
        $data['show_sign_in_button'] = false;
        $data['show_forgot_password_buttton'] = false;
        $data['show_sso_button'] = false;
        $data['show_ldap_button'] = false;
        $data['show_verify_login_with_mfa'] = false;
        $data['is_sign_in_disabled'] = false;
        $data['is_forgot_password_buttton_disabled'] = false;
        $data['is_verify_login_with_mfa_disable'] = false;
        $data['sso_login_url'] = '';
        $data['email_id'] = $email;
        $data['imiv'] = 'imiiv';
        $mailExt = substr($email, strripos($email,"@"));
        $userType = ($userInfo->contribution_type=='2' || $userInfo->contribution_type=='3')?"external":"internal";
        if($userInfo->customer_id== 0 )
        {
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }

        elseif(!empty($ldap) && ($ldap[0]['status'] ==1) && ($userType == 'internal'))
        {
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            if(in_array($mailExt,explode(',',$ldap[0]['sso_check'])))
            {
                // login with LDAP
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data['show_ldap_button'] = true;
            }
            elseif($customerInfo[0]['is_mfa_active'] == 1 && $customerInfo[0]['is_email_verification_active'])
            {
                // login with MFA
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data['show_verify_login_with_mfa'] = true;
                $data = $this->trustedDevice($userInfo,$data,$trusted_device_id);
            }
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }

        elseif(!empty($sso) && $sso[0]['status']==1 && $userType == 'internal')
        {
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            if(in_array($mailExt,explode(',',$sso[0]['sso_check'])))
            {
                // login with SSO
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data['show_sso_button'] = true;
                $data['sso_login_url'] = SAML_LOGIN;
            }
            elseif($customerInfo[0]['is_mfa_active'] == 1 && $customerInfo[0]['is_email_verification_active'])
            {
                // login with MFA
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data['show_verify_login_with_mfa'] = true;
                $data = $this->trustedDevice($userInfo,$data,$trusted_device_id);
            }
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }
        elseif($userType == 'external')
        {
            if($customerInfo[0]['is_mfa_active'] == 1 && $customerInfo[0]['is_email_verification_active'])
            {
                // login with MFA
                $data['show_verify_login_with_mfa'] = true;
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data = $this->trustedDevice($userInfo,$data,$trusted_device_id);
            }
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }
        elseif($userType == 'internal' && empty($ldap) && empty($sso))
        {
            if($customerInfo[0]['is_mfa_active'] == 1 && $customerInfo[0]['is_email_verification_active'])
            {
                // login with MFA
                $data['show_verify_login_with_mfa'] = true;
                $data['is_sign_in_disabled'] = true;
                $data['is_forgot_password_buttton_disabled'] = true;
                $data = $this->trustedDevice($userInfo,$data,$trusted_device_id);
            }
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }
        else
        {
            $data['show_password'] = true;
            $data['show_sign_in_button'] = true;
            $data['show_forgot_password_buttton'] = true;
            $result = array('status'=>TRUE,'data'=>$data);
            echo json_encode($result); exit;
        }
    }
    public function trustedDevice($userInfo,$data,$trusted_device_id){
        if(isset($trusted_device_id) && !empty($trusted_device_id))
        {
            $trustedDevices = $this->User_model->check_record('trusted_device',array('device_uuid'=>$trusted_device_id,'user_id'=>$userInfo->id_user,'client_broswer' => $_SERVER['HTTP_USER_AGENT'] , 'client_remote_address' => $_SERVER['REMOTE_ADDR'] , 'status' => 1));
            if(!empty($trustedDevices) &&  (($trustedDevices[0]['validity_type'] == "multiple" && $trustedDevices[0]['valid_upto'] > date('Y-m-d H:i:s') )||($trustedDevices[0]['validity_type'] == "once")) )
            {
                $data['is_verify_login_with_mfa_disable'] = true;
                $data['is_sign_in_disabled'] = false;
                $data['is_forgot_password_buttton_disabled'] = false;
                $data['imiv'] = 'imiv';
                return $data;
            }
            else{
                return $data;
            }
        }
        // elseif($userInfo->mfa_flag == 1 )
        // {
        //     $data['is_verify_login_with_mfa_disable'] = true;
        //     $data['is_sign_in_disabled'] = false;
        //     $data['is_forgot_password_buttton_disabled'] = false;
        //     //updating mfa_flag to 0 for non trusted users for not showing sing in button in check mail service for second time
        //     $this->User_model->updateUser(array('mfa_flag'=>0),$userInfo->id_user);
        //     return $data;
        // }
        else{
            return $data;
        }
    } 
    public function sendVerificationCode()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'message'=>$this->lang->line('login_error'),'data'=>'');
            echo json_encode($result); exit;
        }
         //validating inputs
        $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req'),
         'valid_email' => $this->lang->line('email_invalid')
        ));
        $this->form_validator->add_rules('verification_method', array('required'=> $this->lang->line('verification_method_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        $userInfo = $this->User_model->check_email(array('email'=>$data['email_id']));
        if(count($userInfo) == 0)
        {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
            echo json_encode($result); exit;
        }
        //print_r($userInfo->customer_id);exit;
        $code =substr(str_shuffle("qwertyuiopasdfghjklzxcvbnm1234567890"),0,10);
        //add 1 hour, 30 minutes and 45 seconds to time
        $expiry_on = date('Y-m-d H:i:s',strtotime('+10 minutes',strtotime(date("Y-m-d H:i:s"))));
        $CustomerDetails = $this->User_model->check_record('customer',array('id_customer'=>$userInfo->customer_id));

        //storing verification code in users table with 10 min expire 
        $updateData = array(
            "verification_code" => $code,
            "verification_code_expiry_on"=>$expiry_on
        );
        if($data['verification_method'] =='email')
        {
            $emailTemplate=$this->Customer_model->EmailTemplateList(array('customer_id' => $userInfo->customer_id,'module_key'=>'MFA_EMAIL_VERIFICATION','status' =>1));
            if($emailTemplate['total_records']>0){
                $template_configurations=$emailTemplate['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$userInfo->first_name;
                $wildcards_replaces['last_name']=$userInfo->last_name;
                if($CustomerDetails[0]['company_logo']=='') {
                    $customer_logo = getImageUrlSendEmail($CustomerDetails[0]['company_logo'], 'company');
                }
                else{
                    $customer_logo = getImageUrlSendEmail($CustomerDetails[0]['company_logo'], 'profile', SMALL_IMAGE);
    
                }
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['verification_code']=$code;
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$data['email_id'];
                $to_name=$userInfo->first_name.' '.$userInfo->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$userInfo->id_user;
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
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                    {
                        $sentStatus =1;
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id)); 
                    }
                        
                }
    
            }
        }
        if(isset($sentStatus) && $sentStatus ==1 )
        {
            $upadteUser = $this->User_model->updateUser($updateData,$userInfo->id_user);
            if($upadteUser)
            {
                $result = array('status'=>TRUE, 'message' => $this->lang->line('verification_code_sent_successfully'), 'data'=>'');
                echo json_encode($result); exit;
            }
            else
            {
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('something_went_wrong')),'data'=>'');
                echo json_encode($result); exit;
    
            }
        }
        else
        {
          
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('something_went_wrong')),'data'=>'');
            echo json_encode($result); exit;

        }
    }
    public function verifyCode()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if($data){ $_POST = $data; }
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'message'=>$this->lang->line('login_error'),'data'=>'');
            echo json_encode($result); exit;
        }
         //validating inputs
        $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req'),
         'valid_email' => $this->lang->line('email_invalid')
        ));
        $this->form_validator->add_rules('verification_code', array('required'=> $this->lang->line('verification_code_req')));
        $this->form_validator->add_rules('verification_method', array('required'=> $this->lang->line('verification_method_req')));
        $userInfo = $this->User_model->check_email(array('email'=>$data['email_id']));
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        if($userInfo->verification_code == $data['verification_code'])
        {
            if(date("Y-m-d H:i:s") < $userInfo->verification_code_expiry_on)
            {
                $this->User_model->updateUser(array('mfa_verified_on'=>date("Y-m-d H:i:s")),$userInfo->id_user);
                if(isset($data['is_trust']) && $data['is_trust'] == 1)
                {
                    $valid_upto = date('Y-m-d H:i:s',strtotime('+30 days',strtotime(date("Y-m-d H:i:s"))));
                    $validity_type = "multiple";
                }
                else
                {
                    $valid_upto = NULL;
                    $validity_type = "once";
                }
                $trustDevice = array(
                    'user_id'=>$userInfo->id_user,
                    'device_uuid'=>uniqid().strtotime('now'),
                    'client_broswer'=>$_SERVER['HTTP_USER_AGENT'],
                    'client_remote_address'=>$_SERVER['REMOTE_ADDR'],
                    'verified_on'=>date("Y-m-d H:i:s"),
                    'valid_upto'=>$valid_upto,
                    'validity_type' => $validity_type,
                    'status' => 1
                );
                //print_r($trustDevice);
                $trustId = $this->User_model->insert_data('trusted_device',$trustDevice);
                $trust_device = array(
                    'user_id'=>pk_encrypt($userInfo->id_user),
                    'device_id'=>$trustDevice['device_uuid'],
                    'email_id'=>$userInfo->email,
                );

                // if(isset($data['is_trust']) && $data['is_trust'] == 1)
                // {
                //     $trustDevice = array(
                //         'user_id'=>$userInfo->id_user,
                //         'device_uuid'=>uniqid().strtotime('now'),
                //         'client_broswer'=>$_SERVER['HTTP_USER_AGENT'],
                //         'client_remote_address'=>$_SERVER['REMOTE_ADDR'],
                //         'verified_on'=>date("Y-m-d H:i:s"),
                //         'valid_upto'=>date('Y-m-d H:i:s',strtotime('+30 days',strtotime(date("Y-m-d H:i:s"))))
                //     );
                //     $trustId = $this->User_model->insert_data('trusted_device',$trustDevice);
                //     $trust_device = array(
                //         'user_id'=>pk_encrypt($userInfo->id_user),
                //         'device_id'=>$trustDevice['device_uuid'],
                //         'email_id'=>$userInfo->email,
                //     );
                // }
                // else
                // {
                //     //updating mfa_flag to 1 for non trusted users for showing sing in button in check mail service
                //     $this->User_model->updateUser(array('mfa_flag'=>1),$userInfo->id_user);
                //     $trust_device =array();
                // }
                $result = array('status'=>true,'message'=>$this->lang->line('success'),'trust_device'=>$trust_device);
                echo json_encode($result); exit;
            }
            else
            {
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('verification_code_expired')),'data'=>'');
                echo json_encode($result); exit;
            }

        }
        else
        {
            $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('invalid_verification_code')),'data'=>'');
            echo json_encode($result); exit;
        }
    }
    public function samltokenverification($data)
    {
        $email_id = $data['email_id'];
        $token = $data['token'];
        $samlLogData = $this->User_model->samlLogVerify($data);
        if(!empty($samlLogData))
        {
            //verified user
            //updating login record status as 2
            $this->User_model->update_data('saml_log',array('status' => 2 ) , array('id_saml_log' =>$samlLogData[0]['id_saml_log']));
            $samlStatus = true;
            $message = $this->lang->line('success');
        }
        else
        {
            $samlStatus = false;
            $message = $this->lang->line('something_went_wrong');

        }
        return array('status'=>$samlStatus,'message'=>$message);

    }
}