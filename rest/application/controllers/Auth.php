<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Auth extends REST_Controller
{
    public function login_post()
    {
        $this->load->library('oauth/oauth');
    
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'message'=>$this->lang->line('login_error'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        //validating inputs
        $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req'),'valid_email' => $this->lang->line('email_invalid')));
        $this->form_validator->add_rules('password', array('required'=> $this->lang->line('password_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(isset($data['email_id']) && $data['email_id'] == PUBLIC_API_EMAIL_ID)
        {
            $customer = $this->User_model->check_email(array('email'=>$data['email_id']));
            if(count($customer)==0){
                $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('text_rest_invalid_credentials')),'data'=>'');
                $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
            }
            $result = $this->User_model->login($data);
            $access_token = '';
            if(empty($result))
            {
                $user_info = $this->User_model->check_email(array('email'=>$data['email_id']));
                if(empty($user_info)){
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('invaid_user')),'data'=>'');
                    $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
                }
                $is_blocked=$user_info->is_blocked;
                $last_password_attempt_date=$user_info->last_password_attempt_date;
                $no_of_password_attempts=$user_info->no_of_password_attempts;
                if($last_password_attempt_date==null){
                    $attempt_date = date("Y-m-d");
                    $no_of_password_attempts=1;
                    $this->User_model->updateUser(array('no_of_password_attempts'=>1,'last_password_attempt_date'=>$attempt_date,'is_blocked'=>0),$user_info->id_user);
                    $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$_SERVER['HTTP_USER_AGENT'],'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                }
                else{
                    $no_of_password_attempts=$no_of_password_attempts+1;
                    $this->User_model->updateUser(array('no_of_password_attempts'=>$no_of_password_attempts,'last_password_attempt_date'=>date("Y-m-d"),'is_blocked'=>0),$user_info->id_user);
                    $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$_SERVER['HTTP_USER_AGENT'],'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                }
                if($no_of_password_attempts>=MAX_INVALID_PASSWORD_ATTEMPTS){
                    $this->User_model->updateUser(array('is_blocked'=>1),$user_info->id_user);
                    $client_browser = getUserBrowser($_SERVER['HTTP_USER_AGENT']);
                    $this->User_model->addLoginAttempts(array('email'=>$data['email_id'],'password'=>md5($data['password']),'client_browser'=>$client_browser,'client_remote_address'=>filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ),'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
                    $result = array('status'=>FALSE,'error'=>array('message'=>str_replace('%s',MAX_INVALID_PASSWORD_ATTEMPTS,$this->lang->line('account_block_error'))),'data'=>'');
                    $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
                }
                else{
                    $result = array('status'=>FALSE,'error'=>array('message'=>str_replace('%s',MAX_INVALID_PASSWORD_ATTEMPTS-$no_of_password_attempts,$this->lang->line('one_more_attempts'))),'data'=>'');
                    $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
                }
            }
            else
            {
                if($result->user_status!=1) {
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('login_inactive_error')),'data'=>'');
                    $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
                }
                if($result->is_blocked==1) {
                    $result = array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('account_block_error')),'data'=>'');
                    $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
                }
                else{
                   
                    $this->User_model->updateUser(array('no_of_password_attempts'=>0,'last_password_attempt_date'=>NULL,'is_blocked'=>0),$result->id_user);
                
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
                
                    $this->User_model->addUserLogin(array(
                        'parent_user_id' => $result->id_user,
                        'child_user_id' => NULL,
                        'access_token' => isset($token->access_token)?$token->access_token:NULL
                    ));
                }
            }
            if(isset($result->id_user)){$result->id_user=pk_encrypt($result->id_user);} 

            //userDetails
            $userDetails = array(
                'user_role_name' => $result->user_role_name,
                'id_user' => $result->id_user,
                'first_name' => $result->first_name,
                'last_name' => $result->last_name,
                'email' => $result->email,
                'user_status' => $result->user_status,
                'access_token' => $access_token
            );

            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$userDetails );
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result = array('status'=>FALSE,'error'=>$this->lang->line('text_rest_invalid_credentials'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
}
