<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/third_party/mailer/mailer.php';

class Download extends CI_Controller
{
    public $session_user_id=NULL;
    public function __construct()
    {
        parent::__construct();
        if(isset($_SERVER['HTTP_USER'])){
            $this->user_id = pk_decrypt($_SERVER['HTTP_USER']);
        }
        $language = 'en';
        if(isset($_SERVER['HTTP_LANG']) && $_SERVER['HTTP_LANG']!=''){
            $language = $_SERVER['HTTP_LANG'];
            if(is_dir('application/language/'.$language)==0){
                $language = $this->config->item('rest_language');
            }
        }
        $this->lang->load('rest_controller', $language);
        $this->load->model('Download_model');
        $this->load->model('Validation_model');
        // $getLoggedUserId=$this->User_model->getLoggedUserId();
        // $this->session_user_id=$getLoggedUserId[0]['id'];
        // $this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));

    }

    /*function downloadReport($file='') {
        if(DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $_GET['path']=rawurldecode(urlencode($_GET['path']));
            $_GET['name']=rawurldecode(urlencode($_GET['name']));
            $_GET['path'] = $aesObj->decrypt($_GET['path'],AES_KEY);
            $_GET['name'] = $aesObj->decrypt($_GET['name'],AES_KEY);
        }
        $file=$_GET['path'];
        $file =str_replace(REST_API_URL,"./",$_GET['path']);
        $file_name=$_GET['name'];
        //echo basename($file);exit;
        //$file ='./uploads/38/Schermafbeelding_2017-05-11_om_15_1494582538_1495601217.png';
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            if (readfile($file))
            {
            }

    }*/
    function downloadReportNew(){
        /*if(DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $_GET['user_id'] = $aesObj->decrypt($_GET['user_id'],AES_KEY);
            $_GET['id_download'] = $aesObj->decrypt($_GET['id_download'],AES_KEY);
            $_GET['access_token'] = $aesObj->decrypt($_GET['access_token'],AES_KEY);
            $_GET['id_download'] = $aesObj->decrypt($_GET['id_download'],AES_KEY);
        }*/
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            echo json_encode($result);exit;
        }
        $this->form_validator->add_rules('user_id', array('required'=> $this->lang->line('user_id_req')));
        $this->form_validator->add_rules('id_download', array('required'=> $this->lang->line('document_id_req')));
        $this->form_validator->add_rules('access_token', array('required'=> $this->lang->line('access_token_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            echo json_encode($result);exit;
        }
        $access_token=$data['access_token'];
        $custom_query="SELECT IF(child_user_id IS NULL, parent_user_id, child_user_id) as id, child_user_id, parent_user_id FROM user_login u WHERE access_token='".$access_token."'";
        $getLoggedUserId=$this->User_model->custom_query($custom_query);
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
        // if($this->session_user_info->customer_id==4){
        //     $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'');
        //     echo json_encode($result);exit;
        // }        
        if(isset($data['user_id'])) {
            //$data['user_id'] = pk_decrypt($data['user_id']);
            $data['user_id'] = pk_decrypt(rawurldecode(urlencode($data['user_id'])));
            /*if($data['user_id']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->load->view('errors/html/error_404',array('heading'=>'Not Allowed','message'=>'Not allowed to access.'));
            }*/
        }


        if(isset($data['id_download']))
            $data['id_download'] = pk_decrypt(rawurldecode(urlencode($data['id_download'])));
        // $this->validateDocumentDownload($data);
        if($this->session_user_info->id_user!=$data['user_id']){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'1');
        }
        $check=$this->Download_model->checkDownload(array('id_download'=>$data['id_download'],'user_id'=>$data['user_id'],'access_token'=>$data['access_token'],'status'=>0));
        if($check['document_id']>0){
            $this->validateDocumentDownload($data);
        }
        if($check===false){
            $this->load->view('errors/html/error_404',array('heading'=>$this->lang->line('not_allowed'),'message'=>$this->lang->line('not_allowed_to_access')));
        }
        else{
            if(isset($check['id_download'])){
                $file=$check['path'];
                $file =FILE_SYSTEM_PATH.$check['path'];
                //print_r($file);exit;
                if(is_file($file) && !is_dir($file) && file_exists($file) && is_writable(dirname($file))){
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    if(!in_array($ext,array('php'))){
                        $file_name=$check['filename'];
                        //echo basename($file);exit;
                        //$file ='./uploads/38/Schermafbeelding_2017-05-11_om_15_1494582538_1495601217.png';
                        $this->Download_model->updateDownload(array('id_download'=>$data['id_download'],'status'=>1));
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file));
                        ob_clean();
                        flush();
                        if (readfile($file))
                        {
                        }
                    }
                    else{
                        $this->load->view('errors/html/error_404',array('heading'=>$this->lang->line('file_not_found'),'message'=>$this->lang->line('the_file_you_requested_are_not_found')));
                    }
                }
                else{
                    $this->load->view('errors/html/error_404',array('heading'=>$this->lang->line('file_not_found'),'message'=>$this->lang->line('the_file_you_requested_are_not_found')));
                }
            }
        }
    }
    /*function downloadReport($file='') {
        if(DATA_ENCRYPT)
        {
            $aesObj = new AES();
            $_GET['path']=rawurldecode(urlencode($_GET['path']));
            $_GET['name']=rawurldecode(urlencode($_GET['name']));
            $_GET['path'] = $aesObj->decrypt($_GET['path'],AES_KEY);
            $_GET['name'] = $aesObj->decrypt($_GET['name'],AES_KEY);
        }
        $file=$_GET['path'];
        $file =str_replace(REST_API_URL,"./",$_GET['path']);
        if(is_file($file) && !is_dir($file) && file_exists($file) && is_writable(dirname($file))){
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if(!in_array($ext,array('php'))){
                $file_name=$_GET['name'];
                //echo basename($file);exit;
                //$file ='./uploads/38/Schermafbeelding_2017-05-11_om_15_1494582538_1495601217.png';
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                ob_clean();
                flush();
                if (readfile($file))
                {
                }
            }
        }
    }*/
    function downloadFile($file='') {
        $file ='./uploads/inventory_invoice/'.$file;
        if ($file and file_exists($file))
        {
            header('Content-Description: File Transfer'); 
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
        }
        if (readfile($file))
        {

        }
    }
    function test(){
        $data=$this->input->get();

        $this->Customer_model->updatedailynotificationcount($data);
    }
    function validateDocumentDownload($data){
        if($this->session_user_info->id_user!=$data['user_id']){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'2');
            echo json_encode($result);exit;
        }
        $query="SELECT  *
        FROM download d
        LEFT JOIN  document dc on d.document_id=dc.id_document
        WHERE d.document_id>0
        AND  d.id_download=".$data['id_download'];
        $doc_data = $this->User_model->custom_query($query)[0];
        if(empty($doc_data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'3');
            echo json_encode($result);exit;
        }
        if(($doc_data['reference_type']=='contract' && $doc_data['module_type']=='customer') || ($doc_data['reference_type']=='project' && $doc_data['module_type']=='project')){
            if(in_array($this->session_user_info->user_role_id,array(3))){
                $check_contract_owner_access=$this->User_model->check_record('contract',array('id_contract'=>$doc_data['reference_id'],'contract_owner_id'=>$this->session_user_info->id_user,'is_deleted'=>0));
                if(empty($check_contract_owner_access)){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'4');
                    echo json_encode($result);exit;
                }
            }
            if(in_array($this->session_user_info->user_role_id,array(4))){
                /* checking contract attachments access for delegate */ 
                $check_contract_delgate_access=$this->User_model->check_record('contract',array('id_contract'=>$doc_data['reference_id'],'delegate_id'=>$this->session_user_info->id_user,'is_deleted'=>0));
                if(empty($check_contract_delgate_access)){
                     $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'5');
                     echo json_encode($result);exit;
                }
            }
            if(in_array($this->session_user_info->user_role_id,array(6))){
                /* checking contract attachments access for read-only user */
                $contract_id=$doc_data['reference_id'];
                $relation=$this->checkBuwithContract($doc_data['reference_id'], $this->session_user_info->id_user);              
                if(!$relation){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'6');
                    echo json_encode($result);exit;
                }
            }
            if(in_array($this->session_user_info->user_role_id,array(7))){
            /* checking contract attachments access for external user */
                $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'7');
                echo json_encode($result);exit;
            }
        }
        if($doc_data['reference_type']=='provider'|| ($doc_data['reference_type']=='catalogue' && $doc_data['module_type']=='catalogue')){
            if(in_array($this->session_user_info->user_role_id,array(3,4,6))){
                if($doc_data['reference_type']=='provider')
                {
                    $get_customer_id=$this->User_model->check_record('provider',array('id_provider'=>$doc_data['reference_id']));
                }
                else
                {
                    $get_customer_id=$this->User_model->check_record('catalogue',array('id_catalogue'=>$doc_data['reference_id']));
                }
                if($this->session_user_info->customer_id!=$get_customer_id[0]['customer_id']){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'8');
                    echo json_encode($result);exit;
                }
                    
            }
            if(in_array($this->session_user_info->user_role_id,array(7))){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'9');
                echo json_encode($result);exit;
            }

        }
        if($doc_data['reference_type']=='contract' && $doc_data['module_type']=='contract_review'){
            if(in_array($this->session_user_info->user_role_id,array(4))){
               /* checking contract attachments access for delegate */
               $delegate_access=0;
               $check_delegate_access=$this->User_model->check_record('contract_user',array('contract_id'=>$doc_data['reference_id'],'user_id'=>$this->session_user_info->id_user,'contract_review_id'=>$doc_data['module_id'],'status'=>1));
               if(!empty($check_delegate_access)){
                   $delegate_access=1;
               }  
               $check_contract_delgate_access=$this->User_model->check_record('contract',array('id_contract'=>$doc_data['reference_id'],'delegate_id'=>$this->session_user_info->id_user,'is_deleted'=>0));
               if(!empty($check_contract_delgate_access)){
                    $delegate_access=1;
               }
               if($delegate_access==0){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'10');
                    echo json_encode($result);exit;
               }
           }
           if(in_array($this->session_user_info->user_role_id,array(3))){
               /* checking contract attachments access for owner */
                $owner_access=0;
                $check_contract_owner_access=$this->User_model->check_record('contract',array('id_contract'=>$doc_data['reference_id'],'contract_owner_id'=>$this->session_user_info->id_user,'is_deleted'=>0));
                if(!empty($check_contract_owner_access)){
                     $owner_access=1;
                }
                $check_owner_access=$this->User_model->check_record('contract_user',array('contract_id'=>$doc_data['reference_id'],'user_id'=>$this->session_user_info->id_user,'contract_review_id'=>$doc_data['module_id'],'status'=>1));
                if(!empty($check_owner_access)){
                     $owner_access=1;
                }
                if($owner_access==0){
                     $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'11');
                     echo json_encode($result);exit;
                 }  
           }
           if(in_array($this->session_user_info->user_role_id,array(6))){
            /* checking contract attachments access for read-only user */
                $contract_id=$doc_data['reference_id'];
                $relation=$this->checkBuwithContract($doc_data['reference_id'], $this->session_user_info->id_user);              
                if(!$relation){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'12');
                    echo json_encode($result);exit;
                }
           }
           if(in_array($this->session_user_info->user_role_id,array(7))){
            /* checking contract attachments access for external user */
                $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'13');
                echo json_encode($result);exit;
           }
        }
        if($doc_data['module_type']=='contract_review' && $doc_data['reference_type']=='question'){
            if(in_array($this->session_user_info->user_role_id,array(3,4,6,7))){
                /* checking contract review attachments access for owner,delegate,readonly,external user */
                $question_id=$doc_data['reference_id'];
                $extuser_access=0;
                $query="SELECT m.id_module FROM module m  LEFT JOIN topic t on m.id_module=t.module_id LEFT JOIN question q on t.id_topic=q.topic_id
                WHERE q.id_question=".$question_id."
                GROUP BY m.id_module;";
                $get_module_id = $this->User_model->custom_query($query)[0];
                $check_extuser_access=$this->User_model->check_record('contract_user',array('module_id'=>$get_module_id['id_module'],'user_id'=>$this->session_user_info->id_user,'contract_review_id'=>$doc_data['module_id'],'status'=>1));
                if(!empty($check_extuser_access)){
                    $extuser_access=1;
                }  
                if($extuser_access==0){
                     $result = array('status'=>FALSE,'error'=>$this->lang->line('unable_download_file'),'data'=>'14');
                     echo json_encode($result);exit;
                }
            }
        }        
    }
    public function  checkBuwithContract($contract_id,$user_id){
        $query="SELECT business_unit_id FROM contract WHERE id_contract=".$contract_id;
        $contract_buid = $this->User_model->custom_query($query)[0];
        $query1="SELECT business_unit_id FROM business_unit_user WHERE  status=1 AND user_id =".$user_id;
        $get_user_buids = $this->User_model->custom_query($query1);
        $user_buids=array_column($get_user_buids,'business_unit_id');
        return in_array($contract_buid['business_unit_id'],$user_buids);
    }


}