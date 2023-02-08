<?php

// use Contract_builder;


// require_once("Contract_builder.php");


defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(1);
require APPPATH . '/libraries/REST_Controller.php';
// require APPPATH . '/controllers/Contract_builder.php';


class Document extends REST_Controller
{
    public $session_user_id=NULL;
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

        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
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

        if($this->session_user_info->user_role_id<3 || $this->session_user_info->user_role_id==6 || $this->session_user_info->user_role_id==5)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
        else if($this->session_user_info->user_role_id>=3)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
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
        
        $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        $this->session_user_customer_all_users=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_contract_review_modules=$this->Validation_model->getContractReviewModules(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_contract_review_topics=$this->Validation_model->getContractReviewTopics(array('module_id'=>$this->session_user_contract_review_modules));
        $this->session_user_contract_review_questions=$this->Validation_model->getContractReviewQuestions(array('topic_id'=>$this->session_user_contract_review_topics));
      
    }

    public function list_get()
    {
        $data = $this->input->get();

        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
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
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
            if(!in_array($data['reference_id'],$this->session_user_contracts) && !in_array($data['reference_id'],$this->session_user_contract_review_questions)  && !in_array($data['reference_id'],$this->session_user_contract_review_topics) ){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['module_id'])) {
            $data['module_id'] = pk_decrypt($data['module_id']);
            if(!in_array($data['module_id'],$this->session_user_master_customers) && !in_array($data['module_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_workflow_id'])) {
            $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
        }

        if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
            $data['contract_user'] = $data['id_user'];
            if(!in_array($data['contract_user'],$this->session_user_contributors)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'8');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if($this->session_user_info->user_role_id != 2 && $data['module_type'] == 'contract_review'){
            //The following block is for contributors to see only current review attachments
            /* $contributor = $this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$data['module_id'],'id_user'=>$this->session_user_info->id_user,'return_result'=>true));
            // echo '<pre>'.print_r($contributor);exit;
            if(count($contributor)>0){            
                $data['module_ids']=array_column($contributor,'module_id');
             }*/
        }
        if($this->session_user_info->user_role_id == 7){
            $data['external_user'] = true;
        }
        if(isset($data['deleted'])){

        }else{
            $data['document_status']=1;
        }
        //$data['document_status']=isset($data['deleted'])?0:1;
        // print_r($data);exit;
        if(isset($data['updated_by'])){
            unset($data['updated_by']);

            $result = $this->Document_model->getDocLogList($data);
            // echo $this->db->last_query();exit;
            $data['document_type'] = 1;
            $links_count = $this->Document_model->getDocLogList($data)['total_records'];
            $data['document_type'] = 0;
            $document_count = $this->Document_model->getDocLogList($data)['total_records'];

            foreach($result['data'] as $ka=>$va) {
                $result['data'][$ka]['updated_by']=0;
            }
            $data['updated_by']=isset($data['updated_by'])?$data['updated_by']:1;
            unset($data['document_type']);
            $result2 = $this->Document_model->getDocLogList($data);
            
            $data['document_type'] = 1;
            $links_count += $this->Document_model->getDocLogList($data)['total_records'];
            $data['document_type'] = 0;
            $document_count += $this->Document_model->getDocLogList($data)['total_records'];

            $result['total_records']+=$result2['total_records'];

            $result['data'] = array_merge($result['data'],$result2['data']);
            foreach($result['data'] as $ka=>$va){
                if($va['updated_by']>0){
                    $result['data'][$ka]['uploaded_on']=null;
                    $result['data'][$ka]['datetime']=$result['data'][$ka]['updated_on'];
                    $result['data'][$ka]['action']='Added';
                }
                if($va['updated_by']==0){
                    $result['data'][$ka]['updated_on']=null;
                    $result['data'][$ka]['datetime']=$result['data'][$ka]['uploaded_on'];
                    $result['data'][$ka]['action']='Deleted';
                }
            }
            function date_compare1($a, $b)
            {
                $t1 = strtotime($a['datetime']);
                $t2 = strtotime($b['datetime']);
                return $t2 - $t1;
            }
            usort($result['data'], 'date_compare1');
            //echo '<pre>'.print_r($data).'</pre>';
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='first_name'){
                if($data['sort']['reverse']=='ASC'){
                    function sortByOrder($a, $b) {
                        return strcmp($a['first_name'] , $b['first_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }else{
                    function sortByOrder($a, $b) {
                        return strcmp($b['first_name'] , $a['first_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }
            }
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='module_name'){
                if($data['sort']['reverse']=='ASC'){
                    function sortByOrder($a, $b) {
                        return strcmp($a['module_name'] , $b['module_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }else{
                    function sortByOrder($a, $b) {
                        return strcmp($b['module_name'] , $a['module_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }
            }
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='action'){
                if($data['sort']['reverse']=='ASC'){
                    function sortByOrder($a, $b) {
                        return strcmp($a['action'] , $b['action']);
                    }
                    usort($result['data'], 'sortByOrder');
                }else{
                    function sortByOrder($a, $b) {
                        return strcmp($b['action'] , $a['action']);
                    }
                    usort($result['data'], 'sortByOrder');
                }
            }
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='datetime' ){
                if($data['sort']['reverse'] == 'ASC'){
                    function date_compare($a, $b)
                    {
                        $t1 = strtotime($a['datetime']);
                        $t2 = strtotime($b['datetime']);
                        return $t1 - $t2;
                    }
                    usort($result['data'], 'date_compare');
                }else{
                    function date_compare($a, $b)
                    {
                        $t1 = strtotime($a['datetime']);
                        $t2 = strtotime($b['datetime']);
                        return $t2 - $t1;
                    }
                    usort($result['data'], 'date_compare');
                }
            }

            foreach($result['data'] as $ka=>$va){
                $result['data'][$ka]['document_source_exactpath']=($va['document_source']);
                $result['data'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $result['data'][$ka]['id_module']=pk_encrypt($va['id_module']);
                $result['data'][$ka]['id_document']=pk_encrypt($va['id_document']);
                $result['data'][$ka]['module_id']=pk_encrypt($va['module_id']);
                $result['data'][$ka]['reference_id']=pk_encrypt($va['reference_id']);
                $result['data'][$ka]['user_role_id']=pk_encrypt($va['user_role_id']);
                $result['data'][$ka]['contract_owner_id']=pk_encrypt($va['contract_owner_id']);
                $result['data'][$ka]['delegate_id']=pk_encrypt($va['delegate_id']);
                $result['data'][$ka]['question_id']=pk_encrypt($va['question_id']);
                $result['data'][$ka]['contract_review_id']=pk_encrypt($va['contract_review_id']);
                $result['data'][$ka]['updated_by']=pk_encrypt($va['updated_by']);
            }
         } else{
                $result = $this->Document_model->getDocList($data);
                // echo '<pre>'.print_r($data);exit;
                // echo $this->db->last_query();exit;
                foreach($result['data'] as $ka=>$va){
                    $result['data'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result['data'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result['data'][$ka]['id_module']=pk_encrypt($va['id_module']);
                    $result['data'][$ka]['id_document']=pk_encrypt($va['id_document']);
                    $result['data'][$ka]['module_id']=pk_encrypt($va['module_id']);
                    $result['data'][$ka]['reference_id']=pk_encrypt($va['reference_id']);
                    $result['data'][$ka]['user_role_id']=pk_encrypt($va['user_role_id']);
                    $result['data'][$ka]['contract_owner_id']=pk_encrypt($va['contract_owner_id']);
                    $result['data'][$ka]['delegate_id']=pk_encrypt($va['delegate_id']);
                    $result['data'][$ka]['question_id']=pk_encrypt($va['question_id']);
                    $result['data'][$ka]['contract_review_id']=pk_encrypt($va['contract_review_id']);
                    $result['data'][$ka]['updated_by']=pk_encrypt($va['updated_by']);
                }

                $data['document_type']=1;
                $links_count = $this->Document_model->getDocList($data)['total_records'];
                $data['document_type']=0;
                $document_count = $this->Document_model->getDocList($data)['total_records'];
        } 

        // print_r($result);die;
        $documents = array();
        $links = array(); 
        foreach($result['data'] as $k => $v){
            if((int)$v['document_type'] == 1)
                $links[] = $v;
            else
                $documents[] = $v;
        }
        
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('documents'=>array('total_records'=>$document_count,'data'=>$documents),'links'=>array('total_records'=>$links_count,'data'=>$links),'all_records'=>$result['data'],'result'=>$result));
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function ContractDoclist_get()
    {
        $data = $this->input->get();

        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
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
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
            $data['contract_user'] = $data['id_user'];
            if(!in_array($data['contract_user'],$this->session_user_contributors)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'8');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['deleted'])){

        }else{
            $data['document_status']=1;
        }
        //$data['document_status']=isset($data['deleted'])?0:1;
        if(isset($data['updated_by'])){
            unset($data['updated_by']);unset($data['document_status']);
            $result = $this->Document_model->getContractDocumentsList($data);
            // $data['document_type'] = 1;
            // $links_count = $this->Document_model->getContractDocumentsList($data)['total_records'];
            // $data['document_type'] = 0;
            // $document_count = $this->Document_model->getContractDocumentsList($data)['total_records'];

            foreach($result['data'] as $ka=>$va) {
                $result['data'][$ka]['updated_by']=0;
            }
            $data['updated_by']=isset($data['updated_by'])?$data['updated_by']:1;

            $result2 = $this->Document_model->getContractDocumentsList($data);

            // $data['document_type'] = 1;
            // $links_count += $this->Document_model->getContractDocumentsList($data)['total_records'];
            // $data['document_type'] = 0;
            // $document_count += $this->Document_model->getContractDocumentsList($data)['total_records'];

            $result['total_records']+=$result2['total_records'];
            $result['data'] = array_merge($result['data'],$result2['data']);
            foreach($result['data'] as $ka=>$va){
                if($va['updated_by']>0){
                    $result['data'][$ka]['uploaded_on']=null;
                    $result['data'][$ka]['datetime']=$result['data'][$ka]['updated_on'];
                    $result['data'][$ka]['action']='Deleted';
                }
                if($va['updated_by']==0){
                    $result['data'][$ka]['updated_on']=null;
                    $result['data'][$ka]['datetime']=$result['data'][$ka]['uploaded_on'];
                    $result['data'][$ka]['action']='Added';
                }
            }
            function date_compare1($a, $b)
            {
                $t1 = strtotime($a['datetime']);
                $t2 = strtotime($b['datetime']);
                return $t2 - $t1;
            }
            usort($result['data'], 'date_compare1');
            //echo '<pre>'.print_r($data).'</pre>';exit;
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='first_name'){
                if($data['sort']['reverse']=='ASC'){
                    function sortByOrder($a, $b) {
                        return strcmp($a['first_name'] , $b['first_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }else{
                    function sortByOrder($a, $b) {
                        return strcmp($b['first_name'] , $a['first_name']);
                    }
                    usort($result['data'], 'sortByOrder');
                }
            }
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='action'){
                if($data['sort']['reverse']=='ASC'){
                    function sortByOrder($a, $b) {
                        return strcmp($a['action'] , $b['action']);
                    }
                    usort($result['data'], 'sortByOrder');
                }else{
                    function sortByOrder($a, $b) {
                        return strcmp($b['action'] , $a['action']);
                    }
                    usort($result['data'], 'sortByOrder');
                }
            }
            if(isset($data['sort']['predicate']) && $data['sort']['predicate']=='datetime' ){
                if($data['sort']['reverse'] == 'ASC'){
                    function date_compare($a, $b)
                    {
                        $t1 = strtotime($a['datetime']);
                        $t2 = strtotime($b['datetime']);
                        return $t1 - $t2;
                    }
                    usort($result['data'], 'date_compare');
                }else{
                    function date_compare($a, $b)
                    {
                        $t1 = strtotime($a['datetime']);
                        $t2 = strtotime($b['datetime']);
                        return $t2 - $t1;
                    }
                    usort($result['data'], 'date_compare');
                }
            }

            foreach($result['data'] as $ka=>$va){
                $result['data'][$ka]['document_source_exactpath']=($va['document_source']);
                $result['data'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $result['data'][$ka]['id_document']=pk_encrypt($va['id_document']);
                $result['data'][$ka]['module_id']=pk_encrypt($va['module_id']);
                $result['data'][$ka]['reference_id']=pk_encrypt($va['reference_id']);
                $result['data'][$ka]['user_role_id']=pk_encrypt($va['user_role_id']);
                $result['data'][$ka]['updated_by']=(int)($va['updated_by']);
            }
        } else{
            $result = $this->Document_model->getContractDocumentsList($data);
            foreach($result['data'] as $ka=>$va){
                $result['data'][$ka]['document_source_exactpath']=($va['document_source']);
                $result['data'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                $result['data'][$ka]['id_module']=pk_encrypt($va['id_module']);
                $result['data'][$ka]['id_document']=pk_encrypt($va['id_document']);
                $result['data'][$ka]['module_id']=pk_encrypt($va['module_id']);
                $result['data'][$ka]['reference_id']=pk_encrypt($va['reference_id']);
                $result['data'][$ka]['user_role_id']=pk_encrypt($va['user_role_id']);
                $result['data'][$ka]['contract_owner_id']=pk_encrypt($va['contract_owner_id']);
                $result['data'][$ka]['delegate_id']=pk_encrypt($va['delegate_id']);
            }
            // $data['document_type']=1;
            // $links_count = $this->Document_model->getContractDocumentsList($data)['total_records'];
            // $data['document_type']=0;
            // $document_count = $this->Document_model->getContractDocumentsList($data)['total_records'];
        }

        // $documents = array();
        // $links = array(); 
        // foreach($result['data'] as $k => $v){
        //     if((int)$v['document_type'] == 1)
        //         $links[] = $v;
        //     else
        //         $documents[] = $v;
        // }

        //print_r($result);die;
        //$result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('documents'=>array('total_records'=>$document_count,'data'=>$documents),'links'=>array('total_records'=>$links_count,'data'=>$links)));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }

    /*public function documentlist_get()
    {          // to bring documents of all reviews, in contract review page
        $data = $this->input->get();

        $data = tableOptions($data);
        $data['document_status']=1;
        if(isset($data['id_user']) && isset($data['user_role_id']) && $data['user_role_id']==5){
            $data['contract_user'] = $data['id_user'];
        }
        $result = $this->Document_model->getAllDoccumentList($data);
        foreach($result as $ka=>$va){
            $result[$ka]['document_source_exactpath']=getExactImageUrl($va['document_source']);
        }
        //print_r($result);die;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }*/

    public function add_post()
    {

        $data = $this->input->post();
        // print_r($_FILES);exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(isset($data['document_type']) && $data['document_type'] == 0)
            if(!isset($_FILES['file']) || empty($_FILES['file'])) {
                $result = array('status' => FALSE, 'error' => array('document' => $this->lang->line('document_error').$this->lang->line('allowed_formats')), 'data' => '');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        if(isset($data['document_type']) && $data['document_type'] == 1)
            if(!isset($data['file']) || empty($data['file'])) {
                $result = array('status' => FALSE, 'error' => array('document' =>$this->lang->line('no_link_added')), 'data' => '');
                $this->response($result, REST_Controller::HTTP_OK);
            }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('module_id', array('required'=>$this->lang->line('module_id_req')));
        $this->form_validator->add_rules('module_type', array('required'=>$this->lang->line('module_type_req')));
        $this->form_validator->add_rules('reference_id', array('required'=>$this->lang->line('reference_id_req')));
        $this->form_validator->add_rules('reference_type', array('required'=>$this->lang->line('reference_type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
            if($data['reference_type']=='contract'){
                if($data['reference_id']>0 && !in_array($data['reference_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if($data['reference_type']=='question'){
                if($data['reference_id']>0 && !in_array($data['reference_id'],$this->session_user_contract_review_questions)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }

        }
        if(isset($data['module_id'])) {
            $data['module_id'] = pk_decrypt($data['module_id']);
            if($data['module_type']=='customer'){
                if($data['module_id']>0 && !in_array($data['module_id'],$this->session_user_master_customers)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if($data['module_type']=='contract_review'){
                if($data['module_id']>0 && !in_array($data['module_id'],$this->session_user_contract_reviews)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }

        }
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
        if(isset($data['uploaded_by'])) {
            $data['uploaded_by'] = pk_decrypt($data['uploaded_by']);
            if($data['uploaded_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);

        }
        if(isset($data['parent_question_id'])) {
            $data['parent_question_id'] = pk_decrypt($data['parent_question_id']);
        }
        if(isset($data['contract_workflow_id'])) {
            $data['contract_workflow_id'] = pk_decrypt($data['contract_workflow_id']);
        }

        //Separation from validator attachments with a flag
        $validation_status = 0; 
        if($this->Contract_model->checkReviewUserAccess(array('contract_review_id'=>$data['contract_review_id'],'id_user'=>$this->session_user_info->id_user))>0){
            if($this->session_user_info->contribution_type==1)
                $validation_status = 1;
        }
        if(isset($_FILES['file']))
            $totalFilesCount = count($_FILES['file']['name']);
        $document_data = array();
        $path=FILE_SYSTEM_PATH.'uploads/';
        if(!is_dir($path.$data['customer_id'])){ mkdir($path.$data['customer_id']); }
        if(!is_dir($path.$data['customer_id'].'/deleted')){ mkdir($path.$data['customer_id'].'/deleted'); }
        //echo '<pre>'.print_r($totalFilesCount);exit;
        if(isset($_FILES) && count($totalFilesCount)>0)
        {
            for($i_attachment=0;$i_attachment<$totalFilesCount;$i_attachment++) {

                $imageName = doUpload(array(
                    'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                    'image' => $_FILES['file']['name'][$i_attachment],
                    'upload_path' => $path,
                    'folder' => $data['customer_id']));
                $document_data[$i_attachment]['module_id'] = $data['module_id'];
                if(isset($data['contract_workflow_id']))
                    $document_data[$i_attachment]['contract_workflow_id'] = $data['contract_workflow_id'];
                $document_data[$i_attachment]['module_type'] = $data['module_type'];
                $document_data[$i_attachment]['reference_id'] = $data['reference_id'];
                $document_data[$i_attachment]['reference_type'] = $data['reference_type'];
                $document_data[$i_attachment]['document_name'] = $_FILES['file']['name'][$i_attachment];
                $document_data[$i_attachment]['document_type'] = 0;
                $document_data[$i_attachment]['document_source'] = $imageName;
                $document_data[$i_attachment]['document_mime_type'] = $_FILES['file']['type'][$i_attachment];
                $document_data[$i_attachment]['validator_record'] = $validation_status;
                $document_data[$i_attachment]['uploaded_by'] = $data['uploaded_by'];
                $document_data[$i_attachment]['uploaded_on'] = currentDate();
                $document_data[$i_attachment]['updated_on'] = currentDate();
            }
        } 

        if(count($document_data)>0){
            $this->Document_model->addBulkDocuments($document_data);
        }
        // echo '<pre>'.$this->db->last_query();exit;
        $document_data = array();
        if(isset($data['file']))
            foreach($data['file'] as $k => $v){
                $document_data[$k]['module_id'] = $data['module_id'];
                $document_data[$k]['module_type'] = $data['module_type'];
                if(isset($data['contract_workflow_id']))
                    $document_data[$k]['contract_workflow_id'] = $data['contract_workflow_id'];
                $document_data[$k]['reference_id'] = $data['reference_id'];
                $document_data[$k]['reference_type'] = $data['reference_type'];
                $document_data[$k]['document_name'] = $v['title'];
                $document_data[$k]['document_type'] = 1;
                $document_data[$k]['document_source'] = $v['url'];
                $document_data[$k]['document_mime_type'] = 'URL';
                $document_data[$k]['validator_record'] = $validation_status;
                $document_data[$k]['uploaded_by'] = $data['uploaded_by'];
                $document_data[$k]['uploaded_on'] = currentDate();
                $document_data[$k]['updated_on'] = currentDate();
            }


        if(count($document_data)>0){
            $this->Document_model->addBulkDocuments($document_data);
        }

        if(isset($data['parent_question_id'])){
            $change = $this->User_model->check_record('contract_question_review',array('contract_review_id' => $data['contract_review_id'],'question_id' => $data['reference_id']));
            // when document added to particular question then updating details
    
            $contractQuestion_ReviewData = array(
                'contract_review_id' => $data['contract_review_id'],
                'question_id' => $data['reference_id'],
                'parent_question_id' => $data['parent_question_id'],
                'updated_by' => $data['uploaded_by'],
                'updated_on' => currentDate(),
            );
    
            if($change){
                $this->Contract_model->updateReviewQuestionAnswer($contractQuestion_ReviewData);
            } else {
                // $this->db->insert("contract_question_review",$contractQuestion_ReviewData);
            }
            //echo '<pre>'.$this->db->last_query();exit;
            $this->User_model->update_data('module',array('updated_by'=>$this->session_user_id,'updated_on'=>currentDate()),array('id_module'=>$data['module_id']));
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('document_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function delete_delete()
    {
        $data = $this->input->get();
        $result='';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_document', array('required'=>$this->lang->line('document_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_document'])) {
            $data['id_document'] = pk_decrypt($data['id_document']);
            // if(!in_array($data['id_document'],$this->session_user_contract_documents)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['module_id'])) {
            $data['module_id'] = pk_decrypt($data['module_id']);
            if(!in_array($data['module_id'],$this->session_user_master_customers) && !in_array($data['module_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['reference_id'])) {
            $data['reference_id'] = pk_decrypt($data['reference_id']);
            if(!in_array($data['reference_id'],$this->session_user_contracts) && !in_array($data['reference_id'],$this->session_user_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            //echo $data['user_role_id'].'!='.$this->session_user_info->user_role_id;
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if(!in_array($data['contract_owner_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if(!in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
        }
        if(isset($data['parent_question_id'])) {
            $data['parent_question_id'] = pk_decrypt($data['parent_question_id']);
        }

        if(isset($data['question_id'])) {
            $data['question_id'] = pk_decrypt($data['question_id']);
        }
        

        $details=$this->Document_model->getDocument($data);

        if($details[0]['module_type']=='contract_review'){
            $contract_id=$details[0]['module_id'];
            $cnt_details=$this->Contract_model->getContractDetails(array('contract_review_id'=>$contract_id));
        }

        //contract builder document delete and unlinking 
        // print_r($details);
        //  print_r($this->load->controller('Contract_builder'));
        if($details[0]['module_type']=='contract_builder' && !empty($details[0]['linked_id'])){
            $linkedId = $details[0]['linked_id'];

            //   $contractBuilder = new Contract_builder();;
            $unlinkingUrl = CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contract_links/".$linkedId;
            
            // echo $unlinkingUrl;

            $ch = \curl_init($unlinkingUrl);
            // Set HTTP Header for request 
            $headers = array(
                'Connection: Keep-Alive',
                'X-AUTH-TOKEN: '.CONTRACT_BUILDER_API_AUTH_TOKEN,
                'Authorization: Basic '. base64_encode("congen:q7RQzZVgnr") ,
                'Accept: application/json'
            );
            \curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            $response = \curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = \curl_error($ch);
            $errno    = \curl_errno($ch);
            
            if (\is_resource($ch)) {
                \curl_close($ch);
            }

            if (0 !== $errno) {
                throw new \RuntimeException($error, $errno);
            }
            $rsp =json_decode($response, true);
            // print_r($rsp);
    

        }
        // exit;
        if($details[0]['reference_type']=='contract'){
            $contract_id=$details[0]['reference_id'];
            $cnt_details=$this->Contract_model->getContractDetails(array('id_contract'=>$contract_id));
        }
        if(isset($cnt_details[0]['id_contract'])) {
            $user_info=$this->User_model->getUserInfo(array('user_id'=>$details[0]['uploaded_by']));
            $delete_access = 0;
            if (isset($this->session_user_id) && isset($this->session_user_info->user_role_id)) {
                if ($this->session_user_info->user_role_id == 6) {
                    $delete_access = 0;
                    if ($details[0]['uploaded_by'] == $this->session_user_id) {
                        $delete_access = 1;
                    }
                }
                else if ($this->session_user_info->user_role_id == 5) {
                    $delete_access = 1;
                    if ($details[0]['uploaded_by'] == $this->session_user_id) {
                        $delete_access = 1;
                    }
                } else if ($this->session_user_info->user_role_id == 4 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 1) {
                    if ($details[0]['uploaded_by'] == $this->session_user_id || $user_info->user_role_id > $this->session_user_info->user_role_id) {
                        $delete_access = 1;
                    }
                }
                if ($this->session_user_id == $cnt_details[0]['contract_owner_id'] || $this->session_user_id == $cnt_details[0]['delegate_id']) {
                    $delete_access = 1;
                }
            } else {
                $delete_access = 1;
            }
            if($delete_access==0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $path=getExactImageDirectoryUrl($details[0]['document_source']);
        if(file_exists($path)) {
            if(copy($path, str_replace($details[0]['document_source'], str_replace('/', '/deleted/', $details[0]['document_source']), $path)))
                unlink($path);
        }
        // $this->Document_model->deleteDocument($this->session_user_id,$data,$details[0]['document_source']);//echo $this->db->last_query();exit;
        $this->Document_model->deleteDocument($this->session_user_id,$data,$details[0]['document_source']);
        //echo $this->db->last_query();exit;


        if(isset($data['question_id'])){
            $contractQuestion_ReviewData = array(
                'contract_review_id' => $data['contract_review_id'],
                'question_id' => $data['question_id'],
                'parent_question_id' => $data['parent_question_id'],
                'updated_by' => $this->session_user_id,
                'updated_on' => currentDate(),
            );
            $this->Contract_model->updateReviewQuestionAnswer($contractQuestion_ReviewData);
        }


        $result = array('status'=>TRUE, 'message' => $this->lang->line('document_delete'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function verifylink_get()
    {
        // $data = $this->input->get();
        // $result='';
        // if(empty($data)){
        //     $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        // $this->form_validator->add_rules('url', array('required'=>$this->lang->line('document_id_req')));
        // $validated = $this->form_validator->validate($data);
        // if($validated != 1)
        // {
        //     $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://safebrowsing.googleapis.com/v4/threatMatches:find?key=AIzaSyD16y9kpz3S7C_lLDgXItssdz7gjwbYkAc",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '  {
            "client": {
              "clientId":      "yourcompanyname",
              "clientVersion": "1.5.2"
            },
            "threatInfo": {
              "threatTypes":      ["MALWARE", "SOCIAL_ENGINEERING"],
              "platformTypes":    ["WINDOWS"],
              "threatEntryTypes": ["URL"],
              "threatEntries": [
                {"url": "https://mail.google.com/mail/ca/u/0/#sent/FFNDWNXqqKHxnNsXFSmCQxRltxlKHvrQ"}
              ]
            }
        }',
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: b05b8d34-85f2-49cf-0f8e-03686a71e4e9"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }

    }
    public function lock_unlock_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
       
        
        $this->form_validator->add_rules('id_document', array('required'=>$this->lang->line('document_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_document'])) {
            $data['id_document'] = pk_decrypt($data['id_document']);
        }
        $documentDetails =$this->Document_model->getDocument($data);
       
        if(empty($documentDetails[0]))
        {
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $update_lock_variable = $documentDetails[0]['is_lock'] == 0 ? 1 : 0 ;
       
        $updatedata = array("is_lock"=>$update_lock_variable);
      
        $condition = array("id_document"=> $data['id_document']);
        $this->User_model->update_data("document",$updatedata,$condition);
        $udateddocumentDetails =$this->Document_model->getDocument($data);
        $docStatus = $udateddocumentDetails[0]['is_lock'] == 0 ? "document_unlocked" : "document_locked" ;
        $result = array('status'=>TRUE, 'message' => $this->lang->line($docStatus), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    } 
    public function createIntelligencetemplate_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_name', array('required'=>$this->lang->line('int_temp_name_req')));
        if($data['available_for_all_customers']<1){
            $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($data['available_for_all_customers']<1){
            $cust_ids=array();
            $cust_ids = array_map(function($i){ return pk_decrypt($i); },explode(",",$data['customer_id']));
        }
        $add_array=array(
            'template_name'=>$data['template_name'],
            'available_for_all_customers'=>$data['available_for_all_customers'],
            'status'=>isset($data['status'])?$data['status']:0,
            'is_deleted'=>0,
            'created_by'=>$this->session_user_id,
            'created_on'=>currentDate()
        );
        $temp_id=$this->User_model->insert_data('intelligence_template',$add_array);
        // print_r($cust_ids);exit;
        foreach($cust_ids as $key=>$val){
            $bulk_temp_custids[$key]['template_id']=$temp_id;
            $bulk_temp_custids[$key]['customer_id']=$val;
            $bulk_temp_custids[$key]['is_deleted']=0;
            $bulk_temp_custids[$key]['created_by']=$this->session_user_id;
            $bulk_temp_custids[$key]['created_on']=currentDate();

        }
        // print_r($bulk_temp_custids);exit;
        $this->db->insert_batch('inteligence_template_customers', $bulk_temp_custids); 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }   
    public function intelligencetemplateLlist_get(){
        $data = $this->input->get();
        $data = tableOptions($data);
        if(!empty($data['id_intelligence_template'])){
            $data['id_intelligence_template']=pk_decrypt($data['id_intelligence_template']);
        }
        $list=$this->Document_model->intelligenceTemplateList($data);//print_r($list);exit;
        //echo $this->db->last_query();exit;
        foreach($list['data'] as $k=>$v){
            if(!empty($v['customers'])){
                $list['data'][$k]['customers']=explode(",",$v['customers']);
            }
            else{
                $list['data'][$k]['customers']=array();
            }
            if(!empty($data['id_intelligence_template'])){
                $get_customer_data=array();
                $get_customer_data=$this->Document_model->get_customer_ids_names(array('template_id'=>$v['id_intelligence_template']));
                foreach($get_customer_data as $index=>$ids){
                    // $get_customer_result[$index]['id_customer']=pk_encrypt($ids['id_customer']);
                    $get_customer_result[]=pk_encrypt($ids['id_customer']);
                    
                }
                // print_r((int)$num);exit;
            }
            // print_r($get_customer_data);exit;
            // print_r($v['available_for_all_customers']);exit;
            $list['data'][$k]['available_for_all_customers']=(int)$v['available_for_all_customers'];
            $list['data'][$k]['customer_id']=$get_customer_result;

            $list['data'][$k]['id_intelligence_template']=pk_encrypt($v['id_intelligence_template']);

        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$list);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updateDocumemtTemplate_post(){
        $data = $this->input->post(); 
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_name', array('required'=>$this->lang->line('int_temp_name_req')));
        $this->form_validator->add_rules('id_intelligence_template', array('required'=>$this->lang->line('intelligence_template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $update_array=array(
            'available_for_all_customers'=>$data['available_for_all_customers'],
            'template_name'=>$data['template_name'],
            'status'=>isset($data['status'])?$data['status']:1,
            'updated_by'=>$this->session_user_id,
            'updated_on'=>currentDate()
            
        );
        $this->User_model->update_data('intelligence_template',$update_array,array('id_intelligence_template'=>pk_decrypt($data['id_intelligence_template'])));
        if($data['available_for_all_customers']>0){
            $this->db->delete('inteligence_template_customers', array('template_id'=>pk_decrypt($data['id_intelligence_template'])));
        }
        else{
            $this->db->delete('inteligence_template_customers', array('template_id'=>pk_decrypt($data['id_intelligence_template'])));
            $cust_ids=array();
            $cust_ids = array_map(function($i){ return pk_decrypt($i); },explode(",",$data['customer_id']));
            foreach($cust_ids as $key=>$val){
                $bulk_temp_custids[$key]['template_id']=pk_decrypt($data['id_intelligence_template']);
                $bulk_temp_custids[$key]['customer_id']=$val;
                $bulk_temp_custids[$key]['is_deleted']=0;
                $bulk_temp_custids[$key]['created_by']=$this->session_user_id;
                $bulk_temp_custids[$key]['created_on']=currentDate();
            }
            
        }
        $this->db->insert_batch('inteligence_template_customers', $bulk_temp_custids); 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function addTemplatequestions_post(){
        $data = $this->input->post(); 
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_intelligence_template', array('required'=>$this->lang->line('intelligence_template_id_req')));
        $this->form_validator->add_rules('field_name', array('required'=>$this->lang->line('field_name_req')));
        $this->form_validator->add_rules('field_type', array('required'=>$this->lang->line('field_type_req')));
        $this->form_validator->add_rules('question', array('required'=>$this->lang->line('question_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // print_r($data);exit;
        $create_array=array(
            'document_intelligence_id'=>0,
            'intelligence_template_id'=>pk_decrypt($data['id_intelligence_template']),
            'field_name'=>$data['field_name'],
            'field_type'=>$data['field_type'],
            'question'=>$data['question'],
            'created_by'=>$this->session_user_id,
            'created_on'=>currentDate(),
            'is_deleted'=>0
        );
        $this->User_model->insert_data('intelligence_template_fields',$create_array);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_question_add'), 'data'=>'');
         $this->response($result, REST_Controller::HTTP_OK);
    }
    public function templateQuestionList_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_intelligence_template'])){
            $data['id_intelligence_template']=pk_decrypt($data['id_intelligence_template']);
        }
        if(!empty($data['id_intelligence_template_fields'])){
            $data['id_intelligence_template_fields']=pk_decrypt($data['id_intelligence_template_fields']);
        }
        else{
            $this->form_validator->add_rules('id_intelligence_template', array('required'=>$this->lang->line('intelligence_template_id_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data = tableOptions($data);
        $question=$this->Document_model->getQuestionList($data); 
        foreach($question['data'] as $k=>$v){
            $question['data'][$k]['id_intelligence_template_fields']=pk_encrypt($v['id_intelligence_template_fields']);
            $question['data'][$k]['document_intelligence_id']=pk_encrypt($v['document_intelligence_id']);
            $question['data'][$k]['intelligence_template_id']=pk_encrypt($v['intelligence_template_id']);
        }
        
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$question);
         $this->response($result, REST_Controller::HTTP_OK);
    }
    public function deleteQuestion_delete(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_intelligence_template_fields', array('required'=>$this->lang->line('intelligence_template_field_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_intelligence_template_fields'])){
            $data['id_intelligence_template_fields']=pk_decrypt($data['id_intelligence_template_fields']);
        }
        $this->User_model->update_data('intelligence_template_fields',array('is_deleted'=>1),array('id_intelligence_template_fields'=>$data['id_intelligence_template_fields']));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('question_deleted'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updateQuestion_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_intelligence_template_fields', array('required'=>$this->lang->line('intelligence_template_field_id_req')));
        $this->form_validator->add_rules('field_name', array('required'=>$this->lang->line('field_name_req')));
        $this->form_validator->add_rules('field_type', array('required'=>$this->lang->line('field_type_req')));
        $this->form_validator->add_rules('question', array('required'=>$this->lang->line('question_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_intelligence_template_fields'])){
            $data['id_intelligence_template_fields']=pk_decrypt($data['id_intelligence_template_fields']);
        }
        $update_array=array(
            'document_intelligence_id'=>0,
            'field_name'=>$data['field_name'],
            'field_type'=>$data['field_type'],
            'question'=>$data['question'],
            'updated_by'=>$this->session_user_id,
            'updated_on'=>currentDate(),
        );
        $this->User_model->update_data('intelligence_template_fields',$update_array,array('id_intelligence_template_fields'=>$data['id_intelligence_template_fields']));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_question_update'), 'data'=>'');
         $this->response($result, REST_Controller::HTTP_OK);
    }
    public function customerTemplates_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        $get_customer_templates=$this->Document_model->get_cust_templates($data);
        foreach($get_customer_templates as $k=>$v){
            $get_customer_templates[$k]['id_intelligence_template']=pk_encrypt($v['id_intelligence_template']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$get_customer_templates);
         $this->response($result, REST_Controller::HTTP_OK);
    }
    public function createDocumentIntelligence_post(){

            // $srcfile = 'uploads/4/Change_Document_1633604344.pdf';
            // // $destfile = 'uploads/4/test_new.pdf';
            // $destfile = 'http://3.109.23.34/Bank-Of-Kigali/rest/images/test_new.pdf';
            
            // if (!copy($srcfile, $destfile)) {
            //     echo "File cannot be copied! \n";
            // }
            // else {
            //     echo "File has been copied!";
            // }
            // exit;
        $data = $this->input->post();

        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // print_r($_FILES);exit;
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_req')));
        $this->form_validator->add_rules('owner_id', array('required'=>$this->lang->line('owner_req')));
        $this->form_validator->add_rules('id_intelligence_template', array('required'=>$this->lang->line('intelligence_template_id_req')));
        if(!(isset($data['document_id']) && (!empty($data['document_id']))))
        {
            if(count($_FILES['file']) == 0)
            {
                $result = array('status'=>FALSE, 'error' =>$this->lang->line('please_select_file'), 'data'=>'21');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        if(isset($data['owner_id'])){
            $data['owner_id']=pk_decrypt($data['owner_id']);
        }
        if(isset($data['delegate_id'])){
            $data['delegate_id']=pk_decrypt($data['delegate_id']);
        }
        if(isset($data['id_intelligence_template'])){
            $data['id_intelligence_template']=pk_decrypt($data['id_intelligence_template']);
        }
        if(isset($data['document_id'])){
            $data['document_id']=pk_decrypt($data['document_id']);
        }
        if(isset($_FILES['file']))
        $totalFilesCount = count($_FILES['file']['name']);
        $document_data = array();
        
        $path=FILE_SYSTEM_PATH.'uploads/';
        $upload_path=FILE_SYSTEM_PATH.'uploads/'.$data['customer_id'].'/';
        if(!is_dir(''.$data['customer_id'])){ mkdir($path.$data['customer_id']); }
        if(!is_dir($path.$data['customer_id'].'/deleted')){ mkdir($path.$data['customer_id'].'/deleted'); }
        // foreach($_FILES['file']['size'] as $index=>$size){
        //     // if($size>40000000){
        //     //     $result = array('status'=>FALSE,'error'=>$_FILES['file']['name'][$index].'file size is exceed 40MB','data'=>'');
        //     //     $this->response($result, REST_Controller::HTTP_OK);
        //     // }
            
        // }
        foreach($_FILES['file']['type'] as $t=> $type){
            if($type!='application/pdf'){
                $result = array('status'=>FALSE,'error'=>$_FILES['file']['name'][$t].' '.$this->lang->line('file_is_not_in_pdf_format'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            
        }
        if($totalFilesCount>20){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('upload_limited_to_max_20_files_at_once'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(empty($_FILES) && empty($data['document_id'])){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('upload_at_least_one_file'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($_FILES) && count($totalFilesCount)>0)
        {
            for($i_attachment=0;$i_attachment<$totalFilesCount;$i_attachment++) {
                // print_r($_FILES['file']['name'][$i_attachment]);exit;
                // $imageName = doUpload(array(
                //     'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                //     // 'image' => $_FILES['file']['name'][$i_attachment],
                //     'image' => '1.pdf',
                //     'upload_path' => $path,
                //     'folder' => $data['customer_id']));
                // print_r($path.$data['customer_id']);exit;
                    $create_array=array(
                        'intelligence_template_id'=>$data['id_intelligence_template'],
                        'original_document_name'=>$_FILES['file']['name'][$i_attachment],
                        'original_document_path'=>'',
                        'ocr_document_name'=>'',
                        'ocr_document_path'=>'',
                        //'ocr_status'=>'R',
                        'customer_id'=>$data['customer_id'],
                        'owner_id'=>$data['owner_id'],
                        'delegate_id'=>$data['delegate_id'],
                        'created_by'=>$this->session_user_id,
                        'created_on'=>currentDate(),
                        'is_deleted'=>0,
                        'processing_status'=>1
                    );
                    $inserted_id=$this->User_model->insert_data('document_intelligence',$create_array);
                    move_uploaded_file($_FILES['file']['tmp_name'][$i_attachment], $upload_path.$inserted_id.'.pdf');
                    $this->User_model->update_data('document_intelligence',array('original_document_path'=>$data['customer_id'].'/'.$inserted_id.'.pdf'),array('id_document_intelligence'=>$inserted_id));
            }
        } 
        if(isset($data['document_id']))
        {
            $documentDetails = $this->User_model->check_record('document',array('id_document'=>$data['document_id'])); 
            if(file_exists(FILE_SYSTEM_PATH.'uploads/'.$documentDetails[0]['document_source']) == 1)
            {
                $create_array=array(
                    'intelligence_template_id'=>$data['id_intelligence_template'],
                    'original_document_name'=>$documentDetails[0]['document_name'],
                    'original_document_path'=>'',
                    'ocr_document_name'=>'',
                    'ocr_document_path'=>'',
                    //'ocr_status'=>'R',
                    'customer_id'=>$data['customer_id'],
                    'owner_id'=>$data['owner_id'],
                    'delegate_id'=>$data['delegate_id'],
                    'created_by'=>$this->session_user_id,
                    'created_on'=>currentDate(),
                    'is_deleted'=>0,
                    'processing_status'=>1
                );
                $inserted_id=$this->User_model->insert_data('document_intelligence',$create_array);
                $source = FILE_SYSTEM_PATH.'uploads/'.$documentDetails[0]['document_source'];
                $destination = FILE_SYSTEM_PATH.'uploads/'.$data['customer_id'].'/'.$inserted_id.'.pdf';
                if(copy($source,$destination)){
                    $this->User_model->update_data('document_intelligence',array('original_document_path'=>$data['customer_id'].'/'.$inserted_id.'.pdf'),array('id_document_intelligence'=>$inserted_id));
                }
                else
                {
                    $this->db->delete('document_intelligence',array('id_document_intelligence'=>$inserted_id));
                    $result = array('status'=>False, 'message' => $this->lang->line('something_went_wrong'), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            else
            {
                $result = array('status'=>False, 'message' => $this->lang->line('path_does_not_exist'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        // $this->db->insert_batch('document_intelligence', $create_array); 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('document_inteligence_create'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function documentIntelligenceList_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        // $data['id_document_intelligence']='U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=';
        if(!empty($data['id_document_intelligence'])){
            $data['id_document_intelligence']=pk_decrypt($data['id_document_intelligence']);
        }
        $data = tableOptions($data);
        $int_list=$this->Document_model->getDocumentIntList($data);
        foreach($int_list['data'] as $k=>$v){
            $int_list['data'][$k]['id_document_intelligence']=pk_encrypt($v['id_document_intelligence']);
            $int_list['data'][$k]['encrypted_original_document_path']=pk_encrypt($v['original_document_path']);
            $int_list['data'][$k]['encrypted_ocr_document_path']=!empty($v['ocr_document_path'])?pk_encrypt('ocr/'.$v['ocr_document_path']):'';
            $int_list['data'][$k]['intelligence_template_id']=pk_encrypt($v['intelligence_template_id']);
            $int_list['data'][$k]['customer_id']=pk_encrypt($v['customer_id']);
            $int_list['data'][$k]['owner_id']=pk_encrypt($v['owner_id']);
            $int_list['data'][$k]['delegate_id']=pk_encrypt($v['delegate_id']);
            $int_list['data'][$k]['created_by']=pk_encrypt($v['created_by']);
            $int_list['data'][$k]['contract_id']=pk_encrypt($v['contract_id']);
            $int_list['data'][$k]['validation_percentage']='0%';
            if(!empty($v['ocr_document_path']))
            {
                //$int_list['data'][$k]['ocr_display_name']= str_replace('.pdf','_OCR.pdf',$v['original_document_name']);
                $int_list['data'][$k]['ocr_display_name']= substr($v['original_document_name'], 0, -4)."_OCR.pdf";
            }
            else
            {
                $int_list['data'][$k]['ocr_display_name']= !empty($v['ocr_document_path'])?$v['ocr_document_path']:'---';
            }
            if($v['validate_status'] == 'P')
            {
                $TotalValidation = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$v['id_document_intelligence'],'field_status'=>array('A','E','R','V')));
                $appOrrejOrEdt = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$v['id_document_intelligence'],'field_status'=>array('A','E','R')));
                $validationComplitionPercentage = (int)((count($appOrrejOrEdt)/count($TotalValidation))*100).'%';
                $int_list['data'][$k]['validation_percentage']=$validationComplitionPercentage;
            }
           
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$int_list);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updateDocumemtIntelligence_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_document_intelligence', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $this->form_validator->add_rules('owner_id', array('required'=>$this->lang->line('owner_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // $data['id_document_intelligence']='U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=';
        if(!empty($data['id_document_intelligence'])){
            $data['id_document_intelligence']=pk_decrypt($data['id_document_intelligence']);
        }
        if(isset($data['owner_id'])){
            $data['owner_id']=pk_decrypt($data['owner_id']);
        }
        if(isset($data['delegate_id'])){
            $data['delegate_id']=pk_decrypt($data['delegate_id']);
        }
        $update_array=array(
            'owner_id'=>$data['owner_id'],
            'delegate_id'=>$data['delegate_id'],
            'updated_by'=>$this->session_user_id,
            'updated_on'=>currentDate()
        );
        $this->User_model->update_data('document_intelligence',$update_array,array('id_document_intelligence'=>$data['id_document_intelligence']));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('document_inteligence_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function deleteDocumentIntelligence_delete(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_document_intelligence', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_document_intelligence'])){
            $data['id_document_intelligence']=pk_decrypt($data['id_document_intelligence']);
        }
        $check_status=$this->User_model->check_record('document_intelligence',array('id_document_intelligence'=>$data['id_document_intelligence']));
        if(true){
            $this->User_model->update_data('document_intelligence',array('is_deleted'=>1),array('id_document_intelligence'=>$data['id_document_intelligence']));
            $result = array('status'=>TRUE, 'message' => $this->lang->line('document_inteligence_delete'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('unable_delete_doc_int')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function intelligenceQuestionAnswersList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('field_type', array('required'=>$this->lang->line('field_type_req')));
        $this->form_validator->add_rules('document_intelligence_id', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['document_intelligence_id'])){
            $data['document_intelligence_id']=pk_decrypt($data['document_intelligence_id']);
        }
        $data = tableOptions($data);
        $data['field_type']=explode(',',$data['field_type']);
        $data['field_status']=array('A','E');
        $data['is_moved']=0;
        $questionAnswers=$this->Document_model->getDoumentQuestionsAnswers($data);
        // echo $this->db->last_query();exit;
        $approvedOrEdited = [];
        foreach($questionAnswers as $k=>$v){
        
            $approvedOrEdited[$k]['id_document_fields'] = pk_encrypt($v['id_document_fields']);
            $approvedOrEdited[$k]['document_intelligence_id'] = pk_encrypt($v['document_intelligence_id']);
            $approvedOrEdited[$k]['intelligence_template_id'] = pk_encrypt($v['intelligence_template_id']);
            $approvedOrEdited[$k]['field_name'] = $v['field_name'];
            $approvedOrEdited[$k]['field_type'] = $v['field_type'];
            $approvedOrEdited[$k]['question'] = $v['question'];
            $approvedOrEdited[$k]['options'] = getValues($v['field_status'],$v['field_value'],array('A','E'));
        }
        unset($data['field_status']);
        $data['field_status'] = array('R');
        $rejectedAnswers = [];
        $rejAnswers=$this->Document_model->getDoumentQuestionsAnswers($data);
        foreach($rejAnswers as $k=>$v){
        
            $rejectedAnswers[$k]['id_document_fields'] = pk_encrypt($v['id_document_fields']);
            $rejectedAnswers[$k]['document_intelligence_id'] = pk_encrypt($v['document_intelligence_id']);
            $rejectedAnswers[$k]['intelligence_template_id'] = pk_encrypt($v['intelligence_template_id']);
            $rejectedAnswers[$k]['field_name'] = $v['field_name'];
            $rejectedAnswers[$k]['field_type'] = $v['field_type'];
            $rejectedAnswers[$k]['question'] = $v['question'];
            $rejectedAnswers[$k]['options'] = getValues($v['field_status'],$v['field_value'],array('R'));
        }
        // print_r(count($approvedOrEdited));exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('approvedOrEdited'=>$approvedOrEdited,'rejectedAnswers'=>$rejectedAnswers,'approved_records_count'=>count($approvedOrEdited)));
            $this->response($result, REST_Controller::HTTP_OK);
    }
    public function intelligenceValidationAnswersList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //$this->form_validator->add_rules('field_type', array('required'=>$this->lang->line('field_type_req')));
        $this->form_validator->add_rules('document_intelligence_id', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['document_intelligence_id'])){
            $data['document_intelligence_id']=pk_decrypt($data['document_intelligence_id']);
        }
        $data = tableOptions($data);
        if(isset($data['status']) && !empty($data['status']))
        {
            $data['field_status'] = array($data['status']);
        }
        else{
            $data['field_status'] = array('A','E','V','R');
        }
        
        $questionAnswers=$this->Document_model->getDoumentQuestionsAnswers($data);
        $validateAnswers = [];
        foreach($questionAnswers as $k=>$v){
            $validateAnswers[$k]['id_document_fields'] = pk_encrypt($v['id_document_fields']);
            $validateAnswers[$k]['document_intelligence_id'] = pk_encrypt($v['document_intelligence_id']);
            $validateAnswers[$k]['intelligence_template_id'] = pk_encrypt($v['intelligence_template_id']);
            $validateAnswers[$k]['field_name'] = $v['field_name'];
            $validateAnswers[$k]['field_type'] = $v['field_type'];
            $validateAnswers[$k]['question'] = $v['question'];
            $validateAnswers[$k]['options'] = getValues($v['field_status'],$v['field_value'],array('A','E','V','R'));
            $validateAnswers[$k]['percentage'] = getValues($v['field_status'],$v['percentage'],array('A','E','V','R'));
            $validateAnswers[$k]['status'] = getValues($v['field_status'],$v['field_status'],array('A','E','V','R'));
        }
        
        $TotalValidation = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R','V')));
        $appOrrejOrEdt = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R')));
        $validafationInfo = count($appOrrejOrEdt).'/'.count($TotalValidation);
        $validationComplitionPercentage = (int)((count($appOrrejOrEdt)/count($TotalValidation))*100).'%';
        $submit_validation = (int)((count($appOrrejOrEdt)/count($TotalValidation))*100)==100?true:false;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('validateAnswers'=>$validateAnswers,'validafationInfo'=>$validafationInfo,'validationComplitionPercentage'=>$validationComplitionPercentage,'submit_validation'=>$submit_validation));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function saveValidation_post()
    {
        $data = $this->input->post();

        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('document_intelligence_id', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $this->form_validator->add_rules('validateAnswers', array('required'=>$this->lang->line('validate_answers_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['validateAnswers'])&& !empty($data['validateAnswers']))
        {
            foreach($data['validateAnswers'] as $documentField)
            {
                $oldDocumentField = $this->User_model->check_record('document_fields',array('id_document_fields'=>pk_decrypt($documentField['id_document_fields'])));
                $UpdateDocumentField[] = array(
                    'id_document_fields'=>pk_decrypt($documentField['id_document_fields']),
                    'field_value'=>implode('||',$documentField['options']),
                    'field_status'=>implode('||',$documentField['status']),
                    'percentage'=>implode('||',$documentField['percentage']),
                    'updated_by'=>$this->session_user_id,
                    'updated_on'=>currentDate()
                ); 
            }
            if(!empty($UpdateDocumentField))
            {$this->db->update_batch('document_fields', $UpdateDocumentField, 'id_document_fields');}
        }
        if(isset($data['document_intelligence_id'])){
            $data['document_intelligence_id']=pk_decrypt($data['document_intelligence_id']);
        }
        $this->User_model->update_data('document_intelligence',array('validate_status'=>'P','validate_update_on'=>currentDate()),array('id_document_intelligence'=>$data['document_intelligence_id']));
        $TotalValidation = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R','V')));
        $appOrrejOrEdt = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R')));
        $validafationInfo = count($appOrrejOrEdt).'/'.count($TotalValidation);
        $validationComplitionPercentage = (int)((count($appOrrejOrEdt)/count($TotalValidation))*100).'%';
        $submit_validation = (int)((count($appOrrejOrEdt)/count($TotalValidation))*100)==100?true:false;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('validation_saved_successfully'), 'data'=>array('validafationInfo'=>$validafationInfo,'validationComplitionPercentage'=>$validationComplitionPercentage,'submit_validation'=>$submit_validation));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function moveDocument_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('reference_type', array('required'=>$this->lang->line('reference_type_req')));
        $this->form_validator->add_rules('reference_id', array('required'=>$this->lang->line('reference_id_req')));
        $this->form_validator->add_rules('id_document_intelligence', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_document_intelligence'])){
            $data['id_document_intelligence']=pk_decrypt($data['id_document_intelligence']);
        }
        $documentIntelligence = $this->User_model->check_record('document_intelligence',array('id_document_intelligence'=>$data['id_document_intelligence']));
        if($data['is_ocr'] == 1)
        {
            $isFileExist = file_exists(FILE_SYSTEM_PATH.'uploads/ocr/'.$documentIntelligence[0]['ocr_document_path']);
            $mineType = mime_content_type(FILE_SYSTEM_PATH.'uploads/ocr/'.$documentIntelligence[0]['ocr_document_path']);
            $documentPath = "ocr/".$documentIntelligence[0]['ocr_document_path'];
            $documentName = str_replace('.pdf','_OCR.pdf',$documentIntelligence[0]['original_document_name']);
        }
        else
        {
            $isFileExist = file_exists(FILE_SYSTEM_PATH.'uploads/'.$documentIntelligence[0]['original_document_path']);
            $mineType = mime_content_type(FILE_SYSTEM_PATH.'uploads/'.$documentIntelligence[0]['original_document_path']);
            $documentPath = $documentIntelligence[0]['original_document_path'];
            $documentName = $documentIntelligence[0]['original_document_name'];
        }
        if(!empty($documentIntelligence)  && $isFileExist == 1)
        {
            $document_data['module_id'] = 0;
            $document_data['module_type'] = 'contract_review';
            $document_data['reference_id'] = pk_decrypt($data['reference_id']);
            $document_data['reference_type'] = $data['reference_type'];
            $document_data['document_name'] = $documentName;
            $document_data['module_type'] = $data['module_type'];
            $document_data['module_id'] = $data['id_document_intelligence'];
            $document_data['document_type'] = 0;
            $document_data['document_source'] = $documentPath;
            $document_data['document_mime_type'] = $mineType;
            $document_data['validator_record'] = 0;
            $document_data['uploaded_by'] = $this->session_user_id;
            $document_data['uploaded_on'] = currentDate();
            if($this->db->insert('document', $document_data))
            {
                $intelligenceUpdate = (isset($data['is_ocr']) && $data['is_ocr'] == 1)?array('is_ocr_moved'=>1):array('is_original_moved'=>1);
                $this->User_model->update_data('document_intelligence',$intelligenceUpdate,array('id_document_intelligence'=>$data['id_document_intelligence']));
                $result = array('status'=>TRUE,'message'=>$this->lang->line('moved_successfully'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result = array('status'=>FALSE,'error'=>$this->lang->line('something_went_wrong'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        else
        {
            $result = array('status'=>FALSE,'error'=>$this->lang->line('path_does_not_exist'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }
    public function updateDocumentIntelligenceStatus_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('document_intelligence_id', array('required'=>$this->lang->line('document_inteligence_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['document_intelligence_id'])){
            $data['document_intelligence_id']=pk_decrypt($data['document_intelligence_id']);
        }
        if($data['submit_validation'] == 1)
        {
            $TotalValidation = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R','V')));
            $appOrrejOrEdt = $this->Document_model->getDoumentQuestionsAnswers(array('document_intelligence_id'=>$data['document_intelligence_id'],'field_status'=>array('A','E','R')));
            if((int)((count($appOrrejOrEdt)/count($TotalValidation))*100) ==100)
            {
                $this->User_model->update_data('document_intelligence',array('updated_by'=>$this->session_user_id,'updated_on'=>currentDate(),'validate_status'=>'C'),array('id_document_intelligence'=>$data['document_intelligence_id']));
                $this->User_model->update_data('document_intelligence',array('updated_by'=>$this->session_user_id,'updated_on'=>currentDate(),'create_status'=>'R'),array('id_document_intelligence'=>$data['document_intelligence_id']));
                $result = array('status'=>TRUE,'message'=>$this->lang->line('validation_submitted_successfully'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            } 
            else
            {
                $result = array('status'=>FALSE,'error'=>$this->lang->line('cant_submit_validation'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if($data['complete_process'] == 1)
        {
            $DocumentIntelligence = $this->User_model->check_record('document_intelligence',array('id_document_intelligence' => $data['document_intelligence_id']));
            if($DocumentIntelligence[0]['validate_status'] == 'C')
            {
                $this->User_model->update_data('document_intelligence',array('updated_by'=>$this->session_user_id,'updated_on'=>currentDate(),'create_status'=>'C'),array('id_document_intelligence'=>$data['document_intelligence_id']));
                $result = array('status'=>FALSE,'error'=>$this->lang->line('process_completed_successfuly'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result = array('status'=>FALSE,'error'=>$this->lang->line('first_submit_validation'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
    }
}
