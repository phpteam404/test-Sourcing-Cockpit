<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Report extends REST_Controller
{
    public $user_id = 0 ;
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
    public $session_user_master_contract_review_modules=NULL;
    public $session_user_contract_review_topics=NULL;
    public $session_user_master_contract_review_topics=NULL;
    public $session_user_contract_review_questions=NULL;
    public $session_user_contract_review_question_options=NULL;
    public $session_user_wadmin_relationship_categories=NULL;
    public $session_user_wadmin_relationship_classifications=NULL;
    public $session_user_report_ids=NULL;
    public $session_user_own_business_units=NULL;
    public $session_user_review_business_units=NULL;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('Download_model');
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));

        if(!in_array($this->session_user_info->user_role_id,array(2,3,6,8))){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
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
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user,'deleted'=>'true'));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units,'deleted'=>'true'));
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        $this->session_user_contract_reviews=$this->Validation_model->getContractReviews(array('contract_id'=>$this->session_user_contracts));
        $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_contract_review_modules=$this->Validation_model->getContractReviewModules(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_report_ids=$this->Validation_model->getReportIds(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_report_contract_ids=$this->Validation_model->getReportContractIds(array('report_id'=>$this->session_user_report_ids));
        $this->session_user_report_contract_module_ids=$this->Validation_model->getReportContractModuleIds(array('report_contract_id'=>$this->session_user_report_contract_ids));
    }

    public function list_get()
    {
        $data = $this->input->get();
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if(!in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['user_role_id'])) $data['user_role_id']=pk_decrypt($data['user_role_id']);
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->user_role_id!=$data['user_role_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['id_user'])) $data['id_user']=pk_decrypt($data['id_user']);
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->id_user!=$data['id_user']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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

        $data = tableOptions($data);
        $result = $this->Report_model->getReportList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_report']=pk_encrypt($v['id_report']);
            $result['data'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['data'][$k]['updated_by']=pk_encrypt($v['updated_by']);
            $business_unit_ids_exp=explode(',',$v['business_unit_ids']);
            if($v['business_unit_ids']!='' && count($business_unit_ids_exp)>0) {
                $business_unit_ids = array();
                foreach ($business_unit_ids_exp as $k1 => $v1) {
                    $business_unit_ids[] = pk_encrypt($v1);
                }
                $result['data'][$k]['business_unit_ids'] = implode(',', $business_unit_ids);
            }

            $classification_ids_exp=explode(',',$v['classification_ids']);
            if($v['classification_ids']!='' && count($classification_ids_exp)>0) {
                $classification_ids = array();
                foreach ($classification_ids_exp as $k1 => $v1) {
                    $classification_ids[] = pk_encrypt($v1);
                }
                $result['data'][$k]['classification_ids'] = implode(',', $classification_ids);
            }

            $contract_ids_exp = explode(',', $v['contract_ids']);
            if($v['contract_ids']!='' && count($contract_ids_exp)>0) {
                $contract_ids = array();
                foreach ($contract_ids_exp as $k1 => $v1) {
                    $contract_ids[] = pk_encrypt($v1);
                }
                $result['data'][$k]['contract_ids'] = implode(',', $contract_ids);
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function criteria_get(){
        $data = $this->input->get();
        // print_r($data);exit;
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
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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

        if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==6 || $this->session_user_info->user_role_id==8){
            $busniess_units=$this->Business_unit_model->getBusinessUnitList(array('business_unit_array'=>$this->session_user_business_units,'status'=>1));
            $contracts=$this->Contract_model->getContractList1(array('business_unit_id'=>$this->session_user_business_units, 'sort'=>array( 'predicate'=>'contract_name', 'reverse'=>'asc'),'deleted'=>'true'));
        }
        else{
            $busniess_units=$this->Business_unit_model->getBusinessUnitList(array('customer_id'=>$data['customer_id'],'status'=>1));
            $contracts=$this->Contract_model->getContractList1(array('customer_id'=>$data['customer_id'], 'sort'=>array( 'predicate'=>'contract_name', 'reverse'=>'asc'),'deleted'=>'true'));
        }
        $busniess_units=$busniess_units['data'];

        $relationship_categories=$this->Relationship_category_model->getRelationshipCategory(array('customer_id'=>$data['customer_id'],'relationship_category_status'=>1,'language_id'=>1));

        $contracts=$contracts['data'];

        $status=[['key'=>'rf','value'=>'Review finalized'], ['key'=>'rip','value'=>'Review in Progress'],['key'=>'wf','value'=>'Task finalized'], ['key'=>'wip','value'=>'Task in Progress']];

        // $calender_desc = $this->User_model->check_record_selected("id_calender,CONCAT(DATE_FORMAT(date,'%d/%m/%Y'),'->',workflow_name) activity_name","calender",array('customer_id'=>$data['customer_id'],'parent_calender_id'=>NULL));
        
        $business_unit_id=$this->session_user_info->user_role_id!=2?implode('|',$this->session_user_business_units):array();
        $calender_desc = $this->Report_model->getactivityNames(array('customer_id'=>$data['customer_id'],'parent_calender_id'=>NULL,'task_type'=>'main_task','status'=>1,'business_unit_id'=>$business_unit_id));
        // $calender_desc = $this->User_model->check_record_selected("id_calender,workflow_name activity_name","calender",array('customer_id'=>$data['customer_id'],'parent_calender_id'=>NULL,'task_type'=>'main_task','status'=>1));
        $result['criteria']['business_units']=$busniess_units;
        $result['criteria']['classifications']=$relationship_categories;
        $result['criteria']['contracts']=$contracts;
        $result['criteria']['description']=$calender_desc;
        $result['criteria']['status']=$status;
        foreach($result['criteria']['description'] as $k=>$v){
            $result['criteria']['description'][$k]['id_calender']=pk_encrypt($v['id_calender']);
            $result['criteria']['description'][$k]['activity_name']=$v['activity_name'];
        }
        foreach($result['criteria']['business_units'] as $k=>$v){
            $result['criteria']['business_units'][$k]['country_id']=pk_encrypt($v['country_id']);
            $result['criteria']['business_units'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['criteria']['business_units'][$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result['criteria']['business_units'][$k]['id_business_unit']=pk_encrypt($v['id_business_unit']);
            $result['criteria']['business_units'][$k]['id_country']=pk_encrypt($v['id_country']);
            $result['criteria']['business_units'][$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        foreach($result['criteria']['classifications'] as $k=>$v){
            $result['criteria']['classifications'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['criteria']['classifications'][$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result['criteria']['classifications'][$k]['id_relationship_category']=pk_encrypt($v['id_relationship_category']);
            $result['criteria']['classifications'][$k]['id_relationship_category_language']=pk_encrypt($v['id_relationship_category_language']);
            $result['criteria']['classifications'][$k]['language_id']=pk_encrypt($v['language_id']);
            $result['criteria']['classifications'][$k]['parent_relationship_category_id']=pk_encrypt($v['parent_relationship_category_id']);
            $result['criteria']['classifications'][$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result['criteria']['classifications'][$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        foreach($result['criteria']['contracts'] as $k=>$v){
            $result['criteria']['contracts'][$k]['business_unit_id']=pk_encrypt($v['business_unit_id']);
            $result['criteria']['contracts'][$k]['classification_id']=pk_encrypt($v['classification_id']);
            $result['criteria']['contracts'][$k]['contract_owner_id']=pk_encrypt($v['contract_owner_id']);
            $result['criteria']['contracts'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['criteria']['contracts'][$k]['currency_id']=pk_encrypt($v['currency_id']);
            $result['criteria']['contracts'][$k]['delegate_id']=pk_encrypt($v['delegate_id']);
            $result['criteria']['contracts'][$k]['id_contract']=pk_encrypt($v['id_contract']);
            $result['criteria']['contracts'][$k]['id_contract_review']=pk_encrypt($v['id_contract_review']);
            $result['criteria']['contracts'][$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result['criteria']['contracts'][$k]['updated_by']=pk_encrypt($v['updated_by']);

        }
        $final_result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($final_result, REST_Controller::HTTP_OK);
    }
    public function search_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data = tableOptions($data);
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        //$this->form_validator->add_rules('business_unit_ids', array('required'=>$this->lang->line('business_unit_id_req')));
        //$this->form_validator->add_rules('classification_ids', array('required'=>$this->lang->line('report_classification_id_req')));
        //$this->form_validator->add_rules('review_statuses', array('required'=>$this->lang->line('status_req')));
        //$this->form_validator->add_rules('latest_review_from_date', array('required'=>$this->lang->line('latest_review_from_date_req')));
        //$this->form_validator->add_rules('latest_review_to_date', array('required'=>$this->lang->line('latest_review_to_date_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if(!in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['business_unit_ids']) && count(explode(',',$data['business_unit_ids']))>0){
            $business_unit_ids_exp=explode(',',$data['business_unit_ids']);
            $business_unit_ids=array();
            foreach($business_unit_ids_exp as $k=>$v){
                    $business_unit_ids_chk = pk_decrypt($v);
                    if(!in_array($business_unit_ids_chk,$this->session_user_business_units)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $business_unit_ids[]=pk_decrypt($v);
            }
            $data['business_unit_ids']=implode(',',$business_unit_ids);
        }
        if(isset($data['classification_ids']) && count(explode(',',$data['classification_ids']))>0){
            $classification_ids_exp=explode(',',$data['classification_ids']);
            $classification_ids=array();
            foreach($classification_ids_exp as $k=>$v){
                    $classification_ids_chk = pk_decrypt($v);
                    if(!in_array($classification_ids_chk,$this->session_user_customer_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $classification_ids[]=pk_decrypt($v);
            }
            $data['classification_ids']=implode(',',$classification_ids);
        }
        if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0){
            $contract_ids_exp=explode(',',$data['contract_ids']);
            $contract_ids=array();
            foreach($contract_ids_exp as $k=>$v){
                    $contract_ids_chk = pk_decrypt($v);
                    if(!in_array($contract_ids_chk,$this->session_user_contracts)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $contract_ids[]=pk_decrypt($v);
            }
            $data['contract_ids']=implode(',',$contract_ids);
        }
        if(isset($data['provider_ids']) && count(explode(',',$data['provider_ids']))>0){
            $provider_ids_exp=explode(',',$data['provider_ids']);
            $provider_ids=array();
            foreach($provider_ids_exp as $k=>$v){
                $provider_ids[]=pk_decrypt($v);
            }
            $data['provider_ids']=implode(',',$provider_ids);
        }
        if(isset($data['calender_ids']) && count(explode(',',$data['calender_ids']))>0){
            $calender_ids_exp=explode(',',$data['calender_ids']);
            $calender_ids=array();
            foreach($calender_ids_exp as $k=>$v){
                $calender_ids[]=pk_decrypt($v);
            }
            $data['calender_ids']=implode(',',$calender_ids);
        }
        
        if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==6){
            $data['user_role_id']=$this->session_user_info->user_role_id;
            $data['session_user_business_units']=implode(',',$this->session_user_business_units);
            if(!isset($data['business_unit_ids']))
                $data['business_unit_ids']=implode(',',$this->session_user_business_units);
        }
        $data['deleted'] = 'true';
        $result=$this->Report_model->search($data);//echo $this->db->last_query();exit;
        // print_r($result);exit;
        $result['data']['id_report']=pk_encrypt($result['data']['id_report']);
        foreach($result['data']['report_contracts'] as $k=>$v){
            $result['data']['report_contracts'][$k]['contract_review_status']=str_replace('workflow','Task',$result['data']['report_contracts'][$k]['contract_review_status']);
            $module_score = $this->Contract_model->getContractReviewModuleScore(array('contract_review_id' => $v['id_contract_review']));
            $result['data']['report_contracts'][$k]['contract_progress'] =0;
            for($sr=0;$sr<count($module_score);$sr++)
            {
                $module_score[$sr]['score'] = getScoreByCount($module_score[$sr]);
                $result['data']['report_contracts'][$k]['contract_progress'] += $this->Contract_model->progress(array('module_id'=>$module_score[$sr]['module_id'],'contract_review_id'=>$v['id_contract_review']));
            }
            if(count($module_score)>0)
                $result['data']['report_contracts'][$k]['contract_progress'] = round($result['data']['report_contracts'][$k]['contract_progress']/count($module_score)).'%';
            else
                $result['data']['report_contracts'][$k]['contract_progress'] = '0%';
            $result['data']['report_contracts'][$k]['business_unit_id']=pk_encrypt($v['business_unit_id']);
            $result['data']['report_contracts'][$k]['classification_id']=pk_encrypt($v['classification_id']);
            $result['data']['report_contracts'][$k]['contract_owner_id']=pk_encrypt($v['contract_owner_id']);
            $result['data']['report_contracts'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['data']['report_contracts'][$k]['currency_id']=pk_encrypt($v['currency_id']);
            $result['data']['report_contracts'][$k]['delegate_id']=pk_encrypt($v['delegate_id']);
            $result['data']['report_contracts'][$k]['id_contract']=pk_encrypt($v['id_contract']);
            $result['data']['report_contracts'][$k]['id_contract_review']=pk_encrypt($v['id_contract_review']);
            $result['data']['report_contracts'][$k]['contract_review_id']=pk_encrypt($v['contract_review_id']);
            $result['data']['report_contracts'][$k]['id_report_contract']=pk_encrypt($v['id_report_contract']);
            $result['data']['report_contracts'][$k]['id_report_contract']=pk_encrypt($v['id_report_contract']);
            $result['data']['report_contracts'][$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result['data']['report_contracts'][$k]['updated_by']=pk_encrypt($v['updated_by']);
            foreach($result['data']['report_contracts'][$k]['modules'] as $km=>$vm){
                $result['data']['report_contracts'][$k]['modules'][$km]['id_report_contract_module']=pk_encrypt($vm['id_report_contract_module']);
                $result['data']['report_contracts'][$k]['modules'][$km]['module_id']=pk_encrypt($vm['module_id']);
                $result['data']['report_contracts'][$k]['modules'][$km]['topic_id']=pk_encrypt($vm['topic_id']);
                $result['data']['report_contracts'][$k]['modules'][$km]['parent_module_id']=pk_encrypt($vm['parent_module_id']);
            }
        }
        foreach($result['parent_modules'] as $k=>$v){
            $result['parent_modules'][$k]['parent_module_id']=pk_encrypt($v['parent_module_id']);
        }
        $final_result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($final_result, REST_Controller::HTTP_OK);
    }
    public function saveReport_post()
    {

        $data = $this->input->post();
        if (empty($data)) {
            $result = array('status' => FALSE, 'error' => $this->lang->line('invalid_data'), 'data' => '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('report_name', array('required'=>$this->lang->line('report_name_req')));
        $this->form_validator->add_rules('report_contracts', array('required'=>$this->lang->line('report_contracts_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('save_type', array('required'=>$this->lang->line('report_save_type')));
        $validated = $this->form_validator->validate($data);
        $report_id=NULL;
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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
        //if(isset($data['created_by'])) $data['created_by']=pk_decrypt($data['created_by']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['old_id_report'])) $data['old_id_report']=pk_decrypt($data['old_id_report']);
        if(isset($data['old_id_report'])) {
            $data['old_id_report'] = pk_decrypt($data['old_id_report']);
            if(!in_array($data['old_id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if($data['id_report']>0 && !in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['business_unit_ids']) && count(explode(',',$data['business_unit_ids']))>0){
            $business_unit_ids_exp=explode(',',$data['business_unit_ids']);
            $business_unit_ids=array();
            foreach($business_unit_ids_exp as $k=>$v){
                    $business_unit_ids_chk = pk_decrypt($v);
                    if(!in_array($business_unit_ids_chk,$this->session_user_business_units)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $business_unit_ids[]=pk_decrypt($v);
            }
            $data['business_unit_ids']=implode(',',$business_unit_ids);
        }
        if(isset($data['classification_ids']) && count(explode(',',$data['classification_ids']))>0){
            $classification_ids_exp=explode(',',$data['classification_ids']);
            $classification_ids=array();
            foreach($classification_ids_exp as $k=>$v){
                    $classification_ids_chk = pk_decrypt($v);
                    if(!in_array($classification_ids_chk,$this->session_user_customer_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $classification_ids[]=pk_decrypt($v);
            }
            $data['classification_ids']=implode(',',$classification_ids);
        }
        if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0){
            $contract_ids_exp=explode(',',$data['contract_ids']);
            $contract_ids=array();
            foreach($contract_ids_exp as $k=>$v){
                    $contract_ids_chk = pk_decrypt($v);
                    if(!in_array($contract_ids_chk,$this->session_user_contracts)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $contract_ids[]=pk_decrypt($v);
            }
            $data['contract_ids']=implode(',',$contract_ids);
        }
        //$data['save_type']='save'; //save,save as,change criteria
        if(strtolower($data['save_type'])=='change criteria'  && isset($data['old_id_report']) && $data['old_id_report']!=NULL){
            $update_report=['id_report'=>$data['old_id_report'],'report_status'=>2,'updated_by'=>$data['created_by'],'updated_on'=>currentDate()];
            $this->Report_model->updateReport($update_report);
            /*$insert_report=['name'=>$data['report_name'],'business_unit_ids'=>$data['business_unit_ids'],'classification_ids'=>$data['classification_ids'],'contract_ids'=>(isset($data['contract_ids'])?$data['contract_ids']:NULL),'latest_review_from_date'=>$data['latest_review_from_date'],'latest_review_to_date'=>$data['latest_review_to_date'],'review_statuses'=>$data['review_statuses'],'created_by'=>$data['created_by'],'created_on'=>currentDate()];
            $report_id=$this->Report_model->addReport($insert_report);*/
        }
        if(strtolower($data['save_type'])=='save' && isset($data['id_report']) && $data['id_report']!=NULL){
            //'latest_review_from_date'=>$data['latest_review_from_date'],'latest_review_to_date'=>$data['latest_review_to_date'],
            $update_report=['id_report'=>$data['id_report'],'name'=>$data['report_name'],'business_unit_ids'=>(isset($data['business_unit_ids'])?$data['business_unit_ids']:NULL),'classification_ids'=>(isset($data['classification_ids'])?$data['classification_ids']:NULL),'contract_ids'=>(isset($data['contract_ids'])?$data['contract_ids']:NULL),'review_statuses'=>(isset($data['review_statuses'])?$data['review_statuses']:NULL),'updated_by'=>$data['created_by'],'updated_on'=>currentDate()];
            $this->Report_model->updateReport($update_report);
            $report_id=$data['id_report'];
        }
        else{
            //'latest_review_from_date'=>$data['latest_review_from_date'],'latest_review_to_date'=>$data['latest_review_to_date']
            $insert_report=['name'=>$data['report_name'],'business_unit_ids'=>(isset($data['business_unit_ids'])?$data['business_unit_ids']:NULL),'classification_ids'=>(isset($data['classification_ids'])?$data['classification_ids']:NULL),'contract_ids'=>(isset($data['contract_ids'])?$data['contract_ids']:NULL),'review_statuses'=>(isset($data['review_statuses'])?$data['review_statuses']:NULL),'created_by'=>$data['created_by'],'created_on'=>currentDate()];
            $report_id=$this->Report_model->addReport($insert_report);
        }
        foreach($data['report_contracts'] as $k=>$v){
            //if(isset($v['id_report_contract']) && $v['id_report_contract']!=NULL)  $v['id_report_contract']=pk_decrypt($v['id_report_contract']);
            if(isset($v['id_report_contract']) && $v['id_report_contract']!=NULL) {
                $v['id_report_contract'] = pk_decrypt($v['id_report_contract']);
                if(!in_array($v['id_report_contract'],$this->session_user_report_contract_ids)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            //if(isset($v['business_unit_id']) && $v['business_unit_id']!=NULL)  $v['business_unit_id']=pk_decrypt($v['business_unit_id']);
            if(isset($v['business_unit_id']) && $v['business_unit_id']!=NULL) {
                $v['business_unit_id'] = pk_decrypt($v['business_unit_id']);
                if(!in_array($v['business_unit_id'],$this->session_user_business_units)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            //if(isset($v['contract_id']) && $v['contract_id']!=NULL)  $v['contract_id']=pk_decrypt($v['contract_id']);
            if(isset($v['contract_id']) && $v['contract_id']!=NULL) {
                $v['contract_id'] = pk_decrypt($v['contract_id']);
                if(!in_array($v['contract_id'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            //if(isset($v['id_contract']) && $v['id_contract']!=NULL)  $v['id_contract']=pk_decrypt($v['id_contract']);
            if(isset($v['id_contract']) && $v['id_contract']!=NULL) {
                $v['id_contract'] = pk_decrypt($v['id_contract']);
                if(!in_array($v['id_contract'],$this->session_user_contracts)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(isset($v['contract_review_id']) && $v['contract_review_id']!=NULL) {
                $v['contract_review_id'] = pk_decrypt($v['contract_review_id']);
            }
            //if(isset($v['relationship_category_id']) && $v['relationship_category_id']!=NULL)  $v['relationship_category_id']=pk_decrypt($v['relationship_category_id']);
            if(isset($v['relationship_category_id']) && $v['relationship_category_id']!=NULL) {
                $v['relationship_category_id'] = pk_decrypt($v['relationship_category_id']);
                if(!in_array($v['relationship_category_id'],$this->session_user_customer_relationship_categories)){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(strtolower($data['save_type'])=='save' && isset($v['id_report_contract']) && $v['id_report_contract']!=NULL){
                $update_report_contract='';
                $update_report_contract=['id_report_contract'=>$v['id_report_contract'],'report_id'=>$report_id,'business_unit_id'=>$v['business_unit_id'],'contract_id'=>$v['contract_id'],'contract_review_id'=>$v['contract_review_id'],'relationship_category_id'=>$v['relationship_category_id'],'latest_review_date'=>$v['latest_review_date'],'topic_red_count'=>$v['topic_red_count'],'topic_amber_count'=>$v['topic_amber_count'],'topic_green_count'=>$v['topic_green_count'],'action_items_count'=>$v['action_items_count'],'decision_required'=>$v['decision_required'],'comments'=>$v['comments'],'updated_by'=>$data['created_by'],'updated_on'=>currentDate(),'is_checked'=>$v['is_checked'],'order'=>(isset($v['order'])?$v['order']:0)];
                if(isset($v['bu_name']))
                    $update_report_contract['static_business_unit']=$v['bu_name'];
                if(isset($v['contract_name']))
                    $update_report_contract['static_contract_name']=$v['contract_name'];
                if(isset($v['provider_name']))
                    $update_report_contract['static_provider_name']=$v['provider_name'];
                if(isset($v['contract_status']))
                    $update_report_contract['static_contract_status']=$v['contract_status'];
                if(isset($v['relationship_category_name']))
                    $update_report_contract['static_relationship_category_name']=$v['relationship_category_name'];
                $this->Report_model->updateReportContract($update_report_contract);
                $report_contract_id=$v['id_report_contract'];
            }
            else{
                $insert_report_contract='';
                $insert_report_contract=['report_id'=>$report_id,'business_unit_id'=>$v['business_unit_id'],'contract_id'=>$v['id_contract'],'contract_review_id'=>$v['contract_review_id'],'relationship_category_id'=>$v['relationship_category_id'],'latest_review_date'=>$v['latest_review_date']==''?null:$v['latest_review_date'],'topic_red_count'=>$v['topic_red_count'],'topic_amber_count'=>$v['topic_amber_count'],'topic_green_count'=>$v['topic_green_count'],'action_items_count'=>$v['action_items_count'],'decision_required'=>$v['decision_required']==''?null:$v['decision_required'],'comments'=>$v['comments'],'created_by'=>$data['created_by'],'created_on'=>currentDate(),'is_checked'=>$v['is_checked'],'order'=>(isset($v['order'])?$v['order']:0)];
                if(isset($v['bu_name']))
                    $insert_report_contract['static_business_unit']=$v['bu_name'];
                if(isset($v['contract_name']))
                    $insert_report_contract['static_contract_name']=$v['contract_name'];
                if(isset($v['provider_name']))
                    $insert_report_contract['static_provider_name']=$v['provider_name'];
                if(isset($v['contract_status']))
                    $insert_report_contract['static_contract_status']=$v['contract_status'];
                if(isset($v['relationship_category_name']))
                    $insert_report_contract['static_relationship_category_name']=$v['relationship_category_name'];
                $report_contract_id=$this->Report_model->addReportContract($insert_report_contract);
            }

            foreach($v['modules'] as $km=>$vm){
                //if(isset($vm['id_report_contract_module']) && $vm['id_report_contract_module']!=NULL)  $vm['id_report_contract_module']=pk_decrypt($vm['id_report_contract_module']);
                if(isset($vm['id_report_contract_module']) && $vm['id_report_contract_module']!=NULL) {
                    $vm['id_report_contract_module'] = pk_decrypt($vm['id_report_contract_module']);
                    if(!in_array($vm['id_report_contract_module'],$this->session_user_report_contract_module_ids)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                }
                //if(isset($vm['module_id']) && $vm['module_id']!=NULL)  $vm['module_id']=pk_decrypt($vm['module_id']);
                if(isset($vm['module_id']) && $vm['module_id']!=NULL) {
                    $vm['module_id'] = pk_decrypt($vm['module_id']);
                    // if(!in_array($vm['module_id'],$this->session_user_contract_review_modules)){
                    //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                    //     $this->response($result, REST_Controller::HTTP_OK);
                    // }
                }
                if(strtolower($data['save_type'])=='save' && isset($vm['id_report_contract_module']) && $vm['id_report_contract_module']!=NULL){
                    $update_report_contract_module='';
                    $update_report_contract_module=['id_report_contract_module'=>$vm['id_report_contract_module'],'report_contract_id'=>$report_contract_id,'report_id'=>$report_id,'module_id'=>$vm['module_id'],'score'=>$vm['score'],'org_score'=>$vm['org_score'],'updated_by'=>$data['created_by'],'updated_on'=>currentDate()];
                    $this->Report_model->updateReportContractModule($update_report_contract_module);
                    $report_contract_module_id=$vm['id_report_contract_module'];
                }
                else{
                    $insert_report_contract_module='';
                    $insert_report_contract_module=['report_contract_id'=>$report_contract_id,'report_id'=>$report_id,'module_id'=>$vm['module_id'],'score'=>$vm['score'],'org_score'=>$vm['org_score'],'created_by'=>$data['created_by'],'created_on'=>currentDate()];
                    $report_contract_module_id=$this->Report_model->addReportContractModule($insert_report_contract_module);
                }

            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('report_save'), 'data'=>array('id_report'=>pk_encrypt($report_id)));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function report_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_report', array('required'=>$this->lang->line('id_report_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if(!in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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
        if(isset($data['id_report_contract']) && count(explode(',',$data['id_report_contract']))>0){
            $id_report_contract_exp=explode(',',$data['id_report_contract']);
            $id_report_contract=array();
            foreach($id_report_contract_exp as $k=>$v){
                    $report_contract_ids_chk = pk_decrypt($v);
                    if(!in_array($report_contract_ids_chk,$this->session_user_report_contract_ids)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $id_report_contract[]=pk_decrypt($v);
            }
            $data['id_report_contract']=implode(',',$id_report_contract);
        }
        $result=$this->Report_model->getReport(array('id_report'=>$data['id_report'],'customer_id'=>$data['customer_id']));
        // print_r($result);exit;
        if(isset($result['result']) && isset($result['result'][0]) && isset($result['result'][0]['report_contracts'])){
            foreach($result['result'][0]['report_contracts'] as $k=>$v){
                // $result['data'][$s]['contract_status'] = str_replace('workflow','task',$c_wflow[0]['workflow_status']);
                $result['result'][0]['report_contracts'][$k]['contract_review_status']=str_replace('workflow','task',$result['result'][0]['report_contracts'][$k]['contract_review_status']);
                if(strlen($v['relationship_category_name'])>2){
                    preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                    $result['result'][0]['report_contracts'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
                }else{
                    $result['result'][0]['report_contracts'][$k]['relationship_category_short_name'] = $v['relationship_category_name'];
                }
                // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                // $result['result'][0]['report_contracts'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            }
        }
        foreach($result['global_modules'] as $k=>$v){
            $result['global_modules'][$k]['parent_module_id']=pk_encrypt($v['parent_module_id']);
        }
        foreach($result['result'] as $k=>$v){
            $result['result'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['result'][$k]['id_report']=pk_encrypt($v['id_report']);
            $result['result'][$k]['updated_by']=pk_encrypt($v['updated_by']);
            $business_unit_ids_exp=explode(',',$v['business_unit_ids']);
            if($v['business_unit_ids']!='' && count($business_unit_ids_exp)>0) {
                $business_unit_ids = array();
                foreach ($business_unit_ids_exp as $k1 => $v1) {
                    $business_unit_ids[] = pk_encrypt($v1);
                }
                $result['result'][$k]['business_unit_ids'] = implode(',', $business_unit_ids);
            }

            $classification_ids_exp=explode(',',$v['classification_ids']);
            if($v['classification_ids']!='' && count($classification_ids_exp)>0) {
                $classification_ids = array();
                foreach ($classification_ids_exp as $k1 => $v1) {
                    $classification_ids[] = pk_encrypt($v1);
                }
                $result['result'][$k]['classification_ids'] = implode(',', $classification_ids);
            }

            $contract_ids_exp = explode(',', $v['contract_ids']);
            if($v['contract_ids']!='' && count($contract_ids_exp)>0) {
                $contract_ids = array();
                foreach ($contract_ids_exp as $k1 => $v1) {
                    $contract_ids[] = pk_encrypt($v1);
                }
                $result['result'][$k]['contract_ids'] = implode(',', $contract_ids);
            }
            foreach($result['result'][$k]['report_contracts'] as $kr=>$vr){
                $result['result'][$k]['report_contracts'][$kr]['business_unit_id']=pk_encrypt($vr['business_unit_id']);
                $result['result'][$k]['report_contracts'][$kr]['contract_id']=pk_encrypt($vr['contract_id']);
                $result['result'][$k]['report_contracts'][$kr]['created_by']=pk_encrypt($vr['created_by']);
                $result['result'][$k]['report_contracts'][$kr]['id_contract']=pk_encrypt($vr['id_contract']);
                $result['result'][$k]['report_contracts'][$kr]['contract_review_id']=pk_encrypt($vr['contract_review_id']);
                $result['result'][$k]['report_contracts'][$kr]['id_report_contract']=pk_encrypt($vr['id_report_contract']);
                $result['result'][$k]['report_contracts'][$kr]['relationship_category_id']=pk_encrypt($vr['relationship_category_id']);
                $result['result'][$k]['report_contracts'][$kr]['report_id']=pk_encrypt($vr['report_id']);
                $result['result'][$k]['report_contracts'][$kr]['updated_by']=pk_encrypt($vr['updated_by']);
                foreach($result['result'][$k]['report_contracts'][$kr]['modules'] as $km=>$vm){
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['created_by']=pk_encrypt($vm['created_by']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['id_report_contract_module']=pk_encrypt($vm['id_report_contract_module']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['module_id']=pk_encrypt($vm['module_id']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['parent_module_id']=pk_encrypt($vm['parent_module_id']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['report_contract_id']=pk_encrypt($vm['report_contract_id']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['report_id']=pk_encrypt($vm['report_id']);
                    $result['result'][$k]['report_contracts'][$kr]['modules'][$km]['updated_by']=pk_encrypt($vm['updated_by']);
                }
            }

        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
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
        $this->form_validator->add_rules('id_report', array('required'=>$this->lang->line('id_report_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if(!in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['created_by'])) $data['created_by']=pk_decrypt($data['created_by']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $delete_access = 0;
        $res_del=$this->Report_model->getReportEntry(array('id_report'=>$data['id_report']));
        if(isset($res_del[0]['id_report'])){
            if($this->session_user_info->user_role_id==2)
                $delete_access = 1;
            if($this->session_user_info->user_role_id!=2 && $res_del[0]['created_by']==$this->session_user_id){
                $delete_access = 1;
            }
        }
        else{
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($delete_access==0){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->Report_model->updateReport(array('id_report'=>$data['id_report'],'report_status'=>2,'updated_by'=>$data['created_by'],'updated_on'=>currentDate()));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('report_delete'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
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
    public function export_get(){
        $data = $this->input->get();  
        $result='';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_report', array('required'=>$this->lang->line('id_report_req')));
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
        //if(isset($data['id_report'])) $data['id_report']=pk_decrypt($data['id_report']);
        if(isset($data['id_report'])) {
            $data['id_report'] = pk_decrypt($data['id_report']);
            if(!in_array($data['id_report'],$this->session_user_report_ids)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
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
        if(isset($data['id_report_contract']) && count(explode(',',$data['id_report_contract']))>0){
            $id_report_contract_exp=explode(',',$data['id_report_contract']);
            $id_report_contract=array();
            foreach($id_report_contract_exp as $k=>$v){
                    $report_contract_ids_chk = pk_decrypt($v);
                    if(!in_array($report_contract_ids_chk,$this->session_user_report_contract_ids)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $id_report_contract[]=pk_decrypt($v);
            }
            $data['id_report_contract']=implode(',',$id_report_contract);
        }
        $this->load->library('excel');
        $result=$this->Report_model->getReport($data);

        // print_r($result);exit;
        $global_modules = array();
        foreach($result['global_modules'] as $k=>$v){
            //$global_modules[str_replace(' ','_',$v['module_name'])]=$v['module_name'];
            $global_modules[$v['parent_module_id']]=$v['module_name'];

        }  
        $contract_ids = array();
        $contract_review_ids = array();
        $export_data = array();
        $counter=0;
        $is_workflow = array();
        foreach($result['result'] as $k=>$v){
            foreach($v['report_contracts'] as $kr=>$vr) {
                // echo '<pre>'.print_r($vr);exit;
                $contract_ids[] = $vr['id_contract'];
                $is_workflow[] = $vr['is_workflow'];
                $contract_review_ids[] = $vr['contract_review_id'];
                $export_data[$counter]['business_unit'] = $vr['bu_name'];
                $export_data[$counter]['contract_unique_id'] = $vr['contract_unique_id'];
                $export_data[$counter]['supplier_contract'] = $vr['contract_name'];
                $export_data[$counter]['amber_cnt'] = $vr['topic_amber_count'];
                $export_data[$counter]['red_cnt'] = $vr['topic_red_count'];
                $export_data[$counter]['green_cnt'] = $vr['topic_green_count'];
                $export_data[$counter]['action_items'] = $vr['action_items_count'];
                $export_data[$counter]['decision_required'] = $vr['decision_required']==1?'yes':'no';
                $export_data[$counter]['comments'] = ($vr['comments']==NULL)?'':($vr['comments']);
                $export_data[$counter]['comments_length'] = ($vr['comments']==NULL)?1:strlen($vr['comments']);
                $export_data[$counter]['last_review_date'] = ($vr['latest_review_date']!=NULL && $vr['latest_review_date']!='' && $vr['latest_review_date']!='0000-00-00 00:00:00')?date("d-m-Y",strtotime($vr['latest_review_date'])):'---';
                $export_data[$counter]['classification'] = $vr['relationship_category_short_name'];
                $export_data[$counter]['status'] = $vr['contract_status'];
                foreach ($vr['modules'] as $km=>$vm) {
                    //$export_data[$counter][str_replace(' ', '_', $vm['module_name'])] = $vm['score'];
                    $export_data[$counter][$vm['parent_module_id']] = $vm['score'];
                }
                $counter=$counter+1;
            }
        }
        $add_second_sheet = true;
        // if($data['export_review'] == 'yes'){
        //     $add_second_sheet = true;        
        // }
        if(isset($data['id_report_contract']) and strlen($data['id_report_contract'])>0){
            //Getting Review ids from id_report_contract
            $id_report_contracts = explode(',',$data['id_report_contract']); 
            $query_result = $this->Report_model->getReviewIds(array('id_report_contract'=>$id_report_contracts));
            $contract_review_ids = array_map(function($i){return $i['contract_review_id'];},$query_result);
            
            $review_dashboard_export = $this->Report_model->review_module_topic_qurestions(array('contract_review_ids'=>array_unique($contract_review_ids)));
        }else{
            //Here Contract review ids will come in report result for loop.
            $review_dashboard_export = $this->Report_model->review_module_topic_qurestions(array('contract_review_ids'=>array_unique($contract_review_ids)));
        } 
        $sheet_number = -1;//0 meanse sheet 1
        //This block is hidden as per client requirement
        if(false){
            //This block is hidden as per client requirement
            /*$comments_length=array();
            foreach($export_data as $k=>$v){
                $comments_length[]=strlen($v['comments']);
            }*/
    
            $header = array('last_review'=>isset($data['last_review'])?$data['last_review']:'yes','rag'=>isset($data['rag'])?$data['rag']:'yes','action_items'=>isset($data['action_items'])?$data['action_items']:'yes','comments'=>isset($data['comments'])?$data['comments']:'yes','status'=>isset($data['status'])?$data['status']:'yes');
            $header['modules']=$global_modules;
            //echo "<pre>";print_r($result['result']);echo "</pre>";exit;
    
    
            $report_data = array('report_name'=>$result['result'][0]['name'],'customer_name'=>'Valued Customer','data'=>$export_data);
    
            $this->load->library('excel');
            //activate worksheet number 1
            $excelRowstartsfrom=3;
            $excelColumnstartsFrom=1;
            $columnBegin =$excelColumnstartsFrom;
            $excelstartsfrom=$excelRowstartsfrom;
    
            $count =$excelColumnstartsFrom+3;
            if(isset($header['last_review']) && $header['last_review']=='yes')
                $count++;
            if(isset($header['modules']))
                $count = $count + count($header['modules']);
            if(isset($header['rag']) && $header['rag']=='yes')
                $count = $count+3;
            if(isset($header['action_items']) && $header['action_items']=='yes')
                $count++;
            if(isset($header['comments']) && $header['comments']=='yes')
                $count++;
            if(isset($header['status']) && $header['status']=='yes')
                $count++;
    
            $merge1 = $this->getkey($excelColumnstartsFrom).$excelstartsfrom.':'.$this->getkey($count).($excelstartsfrom);
            $this->excel->setActiveSheetIndex(0)->mergeCells($merge1);
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $data['customer_id']));
                $logo = $customer_details[0]['company_logo'];
                $file_img = './uploads/'.$logo;
                if($logo=='')
                    $file_img = './images/company-logo.png';
                if (file_exists($file_img)) {
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Customer Signature');
                    $objDrawing->setDescription('Customer Signature');
                    //Path to signature .jpg file
                    $signature = $file_img;
                    $objDrawing->setPath($signature);
                    $objDrawing->setOffsetX(40);
                    $objDrawing->setOffsetY(40);//setOffsetX works properly
                    $objDrawing->setCoordinates($this->getkey($excelColumnstartsFrom).$excelstartsfrom);             //set image to cell E38
                    $objDrawing->setHeight(61);                     //signature height
                    $objDrawing->setWorksheet($this->excel->getActiveSheet());  //save
                }
            $this->excel->getActiveSheet()->getStyle($merge1)->applyFromArray(
                array('borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('bold'  => true,'size' =>36)));
            $this->excel->getActiveSheet()->getStyle($merge1)->getFont()->getColor()->setRGB('FFFFFF');
    
            $file_img_path1 = './images/report_img_3.png';
            if (file_exists($file_img_path1)) {
                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Customer Signature');
                $objDrawing->setDescription('Customer Signature');
                //Path to signature .jpg file
                $signature = $file_img_path1;
                $objDrawing->setPath($signature);
                $objDrawing->setOffsetX(5);
                $objDrawing->setOffsetY(25);//setOffsetX works properly
                $objDrawing->setCoordinates($this->getkey($count-4) . $excelstartsfrom);             //set image to cell E38
                $objDrawing->setHeight(110);                     //signature height
                $objDrawing->setWorksheet($this->excel->getActiveSheet());  //save
            }
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . $excelstartsfrom)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . $excelstartsfrom)->getFill()->getStartColor()->setARGB('d4b8cce4');
    
            $this->excel->getActiveSheet()->getRowDimension($excelstartsfrom)->setRowHeight(105);
    
            $this->excel->getActiveSheet()->getRowDimension($excelstartsfrom+1)->setRowHeight(115);
            $this->excel->getActiveSheet()->getRowDimension($excelstartsfrom+2)->setRowHeight(10);
            $this->excel->setActiveSheetIndex(0)
                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'LOB / Function');
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom+2) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom+2) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4376091');
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom+2) . ($excelstartsfrom+2))->applyFromArray(
                array('borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(15);
            $excelColumnstartsFrom++;
    
            $this->excel->setActiveSheetIndex(0)
                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'SUPPLIER / CONTRACT');
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                array('borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(20);
            $excelColumnstartsFrom++;
    
            if(isset($header['status']) && $header['status']=='yes'){
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Status');
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                    array('borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(17);
                $excelColumnstartsFrom++;
            }
    
    
            $this->excel->setActiveSheetIndex(0)
                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Classification');
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                array('borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4D8D8D8');
            $excelColumnstartsFrom++;
    
            if(isset($header['last_review']) && $header['last_review']=='yes'){
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Latest review date');
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                    array('borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4D8D8D8');
                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(12);
    
                $excelColumnstartsFrom++;
            }
    
            if(isset($header['modules'])){
                foreach($header['modules'] as $k=>$v){
                    $this->excel->setActiveSheetIndex(0)
                        ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),$v);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                        array('borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4b8cce4');
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
    
                    $excelColumnstartsFrom++;
    
                }
            }
            if(isset($header['rag']) && $header['rag']=='yes'){
                $color = array('Red (#)','Amber (#)','Green (#)');
                $hash = array('d4FF0000','d4FFC000','d492D050');
                for($i=0;$i<3;$i++){
                    $this->excel->setActiveSheetIndex(0)
                        ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),$color[$i]);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                        array('borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getFill()->getStartColor()->setARGB('d4dbe5f1');
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB($hash[$i]);
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
    
    
                    $excelColumnstartsFrom++;
                }
            }
            $this->excel->setActiveSheetIndex(0)
                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Decision required');
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                array('borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
            if(isset($header['action_items']) && $header['action_items']=='yes') {
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
            }else if( isset($header['comments']) && $header['comments']=='yes'){
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
            }else{
                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(35);
            }
    
    
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4CCC0DA');
    
            $excelColumnstartsFrom++;
    
            if(isset($header['action_items']) && $header['action_items']=='yes'){
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Action items');
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                    array('borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
                if(isset($header['comments']) && $header['comments']=='yes')
                {
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1))->getAlignment()->setTextRotation(45);
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(5);
    
                }else{
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(35);
                }
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4CCC0DA');
    
                $excelColumnstartsFrom++;
    
            }
            if(isset($header['comments']) && $header['comments']=='yes'){
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1),'Comments');
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->applyFromArray(
                    array('borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),'font'  => array('bold'  => true)));
    
    
    
    
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+1).':'.$this->getkey($excelColumnstartsFrom) . ($excelstartsfrom+2))->getFill()->getStartColor()->setARGB('d4b8cce4');
    
                $excelColumnstartsFrom++;
            }
            $columnEnd = $excelColumnstartsFrom-1;
    
            $excelstartsfrom+=3;
            $dataRow=$excelstartsfrom;
    
    
            foreach($report_data['data'] as $k=>$v){
                $excelColumnstartsFrom = $columnBegin;
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),$v['business_unit']);
                $excelColumnstartsFrom++;
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),$v['supplier_contract']);
                $excelColumnstartsFrom++;
                if(isset($header['status']) && $header['status']=='yes') {
                    $this->excel->setActiveSheetIndex(0)
                        ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), $v['status']);
                    $excelColumnstartsFrom++;
                }
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),$v['classification']);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->getStartColor()->setARGB('d4D8D8D8');
    
                $excelColumnstartsFrom++;
                if(isset($header['last_review']) && $header['last_review']=='yes')
                    if(isset($v['last_review_date'])) {
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), date_format(date_create($v['last_review_date']),"M d, Y"));
                        $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->getStartColor()->setARGB('d4D8D8D8');
    
                        $excelColumnstartsFrom++;
                    }
               //echo '<pre>';print_r($report_data);exit;
                foreach($header['modules'] as $mk=>$vk){
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    );
                    if(isset($v[$mk])){
                        if($v[$mk]!=''  && $v[$mk]!='N/A'){
                            /*$file_img_path = './images/'.$v[$mk].'.png';
                            if (file_exists($file_img_path)) {
                                $objDrawing = new PHPExcel_Worksheet_Drawing();
                                $objDrawing->setName('Customer Signature');
                                $objDrawing->setDescription('Customer Signature');
                                //Path to signature .jpg file
                                $signature = $file_img_path;
                                $objDrawing->setPath($signature);
                                $objDrawing->setOffsetX(12);                     //setOffsetX works properly
                                if($v['comments_length']>49)
                                    $objDrawing->setOffsetY(((int)$v['comments_length']/50)+(15*(int)$v['comments_length']/50)/2);
                                $objDrawing->setCoordinates($this->getkey($excelColumnstartsFrom) . $excelstartsfrom);             //set image to cell E38
                                $objDrawing->setHeight(15);                     //signature height
                                $objDrawing->setWorksheet($this->excel->getActiveSheet());  //save
                            }*/
                            $score = $v[$mk];
                            if(strtolower($score) == 'red')
                                $color = 'FF0000';
                            if(strtolower($score) == 'amber')
                                $color = 'ff9900';
                            if(strtolower($score) == 'green')
                                $color = '5bb166';
    
                            $this->excel->setActiveSheetIndex(0)
                                ->setCellValue($this->getkey($excelColumnstartsFrom) . $excelstartsfrom,'•');
                            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . $excelstartsfrom)->applyFromArray(
                                array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                ),'font'  => array(
                                    'bold'  => true,
                                    'color' => array('rgb' => $color),
                                    'size'  => 15,
                                    'name'  => 'Verdana'
                                )));
                            $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        }else if($v[$mk]=='N/A'){
                            $this->excel->setActiveSheetIndex(0)
                                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),' N/A');
                            $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        }
                        else{
                            $this->excel->setActiveSheetIndex(0)
                                ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),' ');
                        }
    
                    }
                    $excelColumnstartsFrom++;
                }
    
    
                if(isset($header['rag']) && $header['rag']=='yes') {
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    );
                    if (isset($v['red_cnt'])) {
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), $v['red_cnt']);
                        $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        $excelColumnstartsFrom++;
                    }
                    if (isset($v['amber_cnt'])) {
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), $v['amber_cnt']);
                        $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        $excelColumnstartsFrom++;
                    }
                    if (isset($v['green_cnt'])) {
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), $v['green_cnt']);
                        $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        $excelColumnstartsFrom++;
                    }
                }
                if($v['decision_required']=='yes'){
                    $this->excel->setActiveSheetIndex(0)
                        ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),'!');
                    $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray(
                        array('alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ),'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FF0000'),
                            'size'  => 15,
                            'name'  => 'Terminal'
                        )));
    
    
                }
                /*else
                    $file = '123';
                $file_img_path = './images/'.$file;
                if (file_exists($file_img_path)) {
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Customer Signature');
                    $objDrawing->setDescription('Customer Signature');
                    //Path to signature .jpg file
                    $signature = $file_img_path;
                    $objDrawing->setPath($signature);
                    $objDrawing->setOffsetX(10);
                    if($v['comments_length']>49)
                        $objDrawing->setOffsetY(3+((int)$v['comments_length']/50)+(15*(int)$v['comments_length']/50)/2);//setOffsetX works properly
                    else
                        $objDrawing->setOffsetY(3);
                    $objDrawing->setCoordinates($this->getkey($excelColumnstartsFrom) . $excelstartsfrom);             //set image to cell E38
                    $objDrawing->setHeight(15);                     //signature height
                    $objDrawing->setWorksheet($this->excel->getActiveSheet());  //save
    
                }*/
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->getFill()->getStartColor()->setARGB('d4CCC0DA');
    
                $excelColumnstartsFrom++;
    
    
                if(isset($header['action_items']) && $header['action_items']=='yes')
                    if(isset($v['action_items'])){
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom), $v['action_items']);
                        $style = array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            )
                        );
                        $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom))->applyFromArray($style);
                        $excelColumnstartsFrom++;
                    }
                if(isset($header['comments']) && $header['comments']=='yes')
                    if(isset($v['comments'])){
                        //echo '<pre>';print_r($v['comments']) ;exit;
                        $this->excel->setActiveSheetIndex(0)
                            ->setCellValue($this->getkey($excelColumnstartsFrom) . ($excelstartsfrom),$v['comments']);
                        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($excelColumnstartsFrom))->setWidth(50);
                        if($v['comments_length']>49){
                            $this->excel->setActiveSheetIndex(0)->getRowDimension($excelstartsfrom)->setRowHeight(15*((int)$v['comments_length']/50)+((int)$v['comments_length']/50));
                            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom) . $excelstartsfrom)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom).$excelstartsfrom)
                                ->getAlignment()->setWrapText(true);
                        }else{
                            $this->excel->setActiveSheetIndex(0)->getRowDimension($excelstartsfrom)->setRowHeight(15);
                        }
    
    
                        $excelColumnstartsFrom++;
                    }
    
                $excelstartsfrom++;
            }
            $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin).($dataRow).':'.$this->getkey($columnEnd).($excelstartsfrom-1))->applyFromArray(
                array('borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));
    
            $this->excel->getActiveSheet()->setSelectedCells('A0');
            //activate worksheet number 1
            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle('REPORT');
        }

        if($add_second_sheet){
            $sheet_number++;
            //Previously This block was used to create Second sheet in excel now this is made first by making the above var to TRUE
            //////////////second sheet starts///////////
            $excelRowstartsfrom=1;
            $excelColumnstartsFrom=0;
            $columnBegin =$excelColumnstartsFrom;
            $excelstartsfrom=$excelRowstartsfrom;
            $question_count = count($review_dashboard_export);
            
            //echo 'question count'.$question_count;exit;
            //Heading Starts
            $this->excel->createSheet();
            $this->excel->setActiveSheetIndex($sheet_number);
            $head = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+16) . $excelstartsfrom;
            $body = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+16) . ($excelstartsfrom+$question_count);
            // echo 'body'.$head;exit;
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
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+8))->setWidth(45);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+9))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+10))->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+11))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+12))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+13))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+14))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+15))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+16))->setWidth(25);
            
            $this->excel->getActiveSheet()->getStyle($head)->getFill()->getStartColor()->setARGB('D1D1D1d1');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,'Relation');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,'Contract Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,'Contract ID');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,'Category');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,'Completion Date');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,'Status');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,'Module Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,'Topic Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,'Question');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,'Answer');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,'Score');
            // $this->excel->getActiveSheet()->getColumnDimension('L')->setVisible(false);
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,'Internal feedback');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,'External feedback');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,'Validator Opinion');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,'Score');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,'Validator Feedback');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+16) . $excelstartsfrom,'Discussion');
            
    
            foreach($review_dashboard_export as $k => $v){
                // echo '<pre>'.print_r($v);exit;
                $excelstartsfrom++;
                ///answer RAG Calculation
                $ans = $v['question_option_answer'];
                if($v['question_option_answer']=='R') $ans = 'Red';
                else if($v['question_option_answer']=='A') $ans = 'Amber';
                else if($v['question_option_answer']=='G') $ans = 'Green';
                else if($v['question_type']=='date') $ans = strtotime($v['question_answer'])>18000?date('d-m-Y',strtotime($v['question_answer'])):'';
                else $ans = $v['question_option_answer'];
                ///v_answer RAG Calculation
                $ans3 = $v['v_question_option_answer'];
                if($v['v_question_option_answer']=='R') $ans3 = 'Red';
                else if($v['v_question_option_answer']=='A') $ans3 = 'Amber';
                else if($v['v_question_option_answer']=='G') $ans3 = 'Green';
                else if($v['question_type']=='date') $ans3 =!empty($v['v_question_answer'])?date("d-m-Y",strtotime($v['v_question_answer'])):'';
                else $ans3 = $v['v_question_option_answer'];
                if(empty($ans3)){ $ans3 ="---";}
                // $validator =0;
                // if(!empty($v['module_id']))
                // {
                //     $validator_modules = $this->Contract_model->getValidatormodules(array('module_id'=>$v['module_id'],'contribution_type'=>1));
                //     if(count($validator_modules) > 0){
                //         $validator = 1;
                //     }
                // }
                // if($validator == 0)
                // {
                //     $ans3 ="---";
                // }
                ///second opinion RAG Calculation
                $ans2 = $v['second_opinion'];
                if($v['second_opinion']=='R') $ans2 = 'Red';
                else if($v['second_opinion']=='A') $ans2 = 'Amber';
                else if($v['second_opinion']=='G') $ans2 = 'Green';
                else $ans2 = $v['second_opinion'];  
                if($v['provider_visibility'] == 1)
                {
                    $colourCell = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey(16) . ($excelstartsfrom);
                    $this->excel->getActiveSheet()->getStyle($colourCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('a9d18e');
                }              
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v['provider_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,$v['contract_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,$v['contract_unique_id']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,$v['relationship_category_name']);
                $format = 'mmm d,YYYY';
                $date = '';
                $dateVal = '';
                if(!empty($v['review_date']))
                {
                    $date = new DateTime($v['review_date']);
                    $dateVal = PHPExcel_Shared_Date::PHPToExcel($date);
                }
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,$dateVal);
                $this->excel->setActiveSheetIndex($sheet_number)->getStyle($this->getkey($columnBegin+4) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);
                // $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,date_format(date_create($v['review_date']),"M d, Y"));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,$v['contract_review_status']=='workflow in progress'?'Task in progress':$v['contract_review_status']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,$v['module_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,$v['topic_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,$v['question_text']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,$ans);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,questionScore($v['option_value'],$v['question_type']));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,$v['question_feedback']); 
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,$v['external_user_question_feedback']);          
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,$ans3);  
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,questionScore($v['v_option_value'],$v['question_type']));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,$v['v_question_feedback']);    
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+16) . $excelstartsfrom,$ans2);             
            }
            $this->excel->getActiveSheet()->setSelectedCells('A0');
            //activate worksheet number 1 
            $this->excel->getActiveSheet()->setTitle('Export DATA');
            // $this->excel->setActiveSheetIndex($sheet_number);
            //////////////second sheet ends///////////
        }
        //Action item Sheet starts here.
        // echo '<pre>'.print_r($contract_ids);exit;
        $action_item_block = true;
        if($action_item_block){
            $sheet_number++;
            $action_items = $this->getReportActionItems(array('contract_ids' => array_unique($contract_ids),'is_workflow_array'=>array_unique($is_workflow)));
            // print_r($action_items);exit;
            $excelRowstartsfrom=1;
            $excelColumnstartsFrom=0;
            $columnBegin =$excelColumnstartsFrom;
            $excelstartsfrom=$excelRowstartsfrom;
            $action_count = $action_items['total_records'];
            
            $this->excel->createSheet();
            $this->excel->setActiveSheetIndex($sheet_number);
            $this->excel->getActiveSheet()->setTitle('Export ACTIONS');
            //echo 'question count'.$action_count;exit;
            //Heading Starts
            $head = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+16) . $excelstartsfrom;
            $body = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+16) . ($excelstartsfrom+$action_count);
            
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
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+3))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+4))->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+5))->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+6))->setWidth(45);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+7))->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+8))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+9))->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+10))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+11))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+12))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+13))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+14))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+15))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+16))->setWidth(25);
            
            $this->excel->getActiveSheet()->getStyle($head)->getFill()->getStartColor()->setARGB('D1D1D1d1');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,'Relation');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,'Contract ID');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,'Contract Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,'Category');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,'Status');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,'Module Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,'Topic Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,'Question');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,'Answer');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,'Action Item');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,'Creation Date');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,'Owner');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,'Due Date');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,'Original Date');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,'Priority');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,'Status');
            foreach($action_items['data'] as $ak => $av){
                // echo '<pre>'.print_r($av);exit;
                $ans = $av['question_answere'];
                if($av['question_type']=='rag' && $ans=='R') $ans = 'Red';
                else if($av['question_type']=='rag' && $ans=='A') $ans = 'Amber';
                else if($av['question_type']=='rag' && $ans=='G') $ans = 'Green';
                else if($av['question_type']=='date') $ans = date('d-m-Y',strtotime($av['question_answere']));
                else $ans = $av['question_answere'];
                $excelstartsfrom++;
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$av['provider_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,$av['contract_unique_id']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,$av['contract_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,$av['category_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,$av['review_status']=='workflow in progress'?'Task in progress':$av['review_status']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,$av['module_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,$av['topic_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,$av['question_text']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,$ans);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,$av['action_item']);
                $format = 'mmm d,YYYY';
                $date = '';
                $dateVal = '';
                if(!empty($av['created_on']))
                {
                    $date = new DateTime($av['created_on']);
                    $dateVal = PHPExcel_Shared_Date::PHPToExcel($date);
                }
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,$dateVal);
                $this->excel->setActiveSheetIndex($sheet_number)->getStyle($this->getkey($columnBegin+10) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);
                //$this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,date('M d,Y',strtotime($av['created_on'])));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,$av['user_name']);


                $date = '';
                $dateVal = '';
                if(!empty($av['due_date']))
                {
                    $date = new DateTime($av['due_date']);
                    $dateVal = PHPExcel_Shared_Date::PHPToExcel($date);
                }
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,$dateVal);
                $this->excel->setActiveSheetIndex($sheet_number)->getStyle($this->getkey($columnBegin+12) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);
                $date_org = '';
                $dateVal_org = '---';
                if(!empty($av['original_date']))
                {
                    $date_org = new DateTime($av['original_date']);
                    if(date('Y-m-d',strtotime($av['original_date']))==$av['due_date']){
                        $dateVal_org = '---';
                    }
                    else{
                        $dateVal_org = PHPExcel_Shared_Date::PHPToExcel($date_org);
                    }

                }
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+13) . $excelstartsfrom,$dateVal_org);
                $this->excel->setActiveSheetIndex($sheet_number)->getStyle($this->getkey($columnBegin+13) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);


                //$this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+12) . $excelstartsfrom,date('M d,Y',strtotime($av['due_date'])));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+14) . $excelstartsfrom,$av['priority']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+15) . $excelstartsfrom,$av['status']);
                foreach($av['comments_log'] as $ack => $acv){
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+14+$ack))->setWidth(35);
                    $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+16+$ack).'1','Comments');
                    $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+16+$ack) . $excelstartsfrom,$acv['comments']);
                    
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin+16+$ack).'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin+16+$ack).'1')->getFill()->getStartColor()->setARGB('D1D1D1d1');
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin+16+$ack).'1')->applyFromArray(
                        array(
                            'alignment'=> array(
                                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                            ),
                            'borders' => array(
                                            'allborders'=> array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN
                                            )
                            )
                        )
                    );
                    // echo $this->getkey($columnBegin+14+$ack).$excelstartsfrom.':'.$this->getkey($columnBegin+14+$ack).($action_count+1);exit;
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin+16+$ack).'1'.':'.$this->getkey($columnBegin+16+$ack).($action_count+1))->applyFromArray(
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
                }
            }
        }
        //Action item sheet ends here.

        //Attachment sheet starts here
        $Attachment_block = true;
        if($Attachment_block){
            $sheet_number++;
            $Attachment = $this->getReportAttachments(array('contract_review_ids' => array_unique($contract_review_ids)));
            $excelRowstartsfrom=1;
            $excelColumnstartsFrom=0;
            $columnBegin =$excelColumnstartsFrom;
            $excelstartsfrom=$excelRowstartsfrom;
            $doc_count = count($Attachment);
            
            $this->excel->createSheet();
            $this->excel->setActiveSheetIndex($sheet_number);
            $this->excel->getActiveSheet()->setTitle('Export FILES');
            //echo 'question count'.$action_count;exit;
            //Heading Starts
            $head = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+13) . $excelstartsfrom;
            $body = $this->getkey($excelColumnstartsFrom) . $excelstartsfrom.':'.$this->getkey($columnBegin+13) . ($excelstartsfrom+$doc_count);
            
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
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+3))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+4))->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+5))->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+6))->setWidth(45);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+7))->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+8))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+9))->setWidth(15);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+10))->setWidth(25);
            $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin+11))->setWidth(25);
            
            $this->excel->getActiveSheet()->getStyle($head)->getFill()->getStartColor()->setARGB('D1D1D1d1');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,'Relation');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin2) . $excelstartsfrom,'Contract Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,'Contract ID');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,'Category');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,'Completion Date');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,'Status');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,'Module Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,'Topic Name');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,'Question');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,'Answer');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,'Files/Links');
            $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,'File Names');
            foreach($Attachment as $ak => $av){
                // echo '<pre>'.print_r($av);
                $ans = $av['question_answere'];
                if($av['question_type']=='rag' && $ans=='R') $ans = 'Red';
                else if($av['question_type']=='rag' && $ans=='A') $ans = 'Amber';
                else if($av['question_type']=='rag' && $ans=='G') $ans = 'Green';
                else if($av['question_type']=='date') $ans = strtotime($av['question_answer'])>18000?date('d-m-Y',strtotime($av['question_answere'])):'';
                else $ans = $av['question_answere'];
                $excelstartsfrom++;
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$av['provider_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+1) . $excelstartsfrom,$av['contract_unique_id']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+2) . $excelstartsfrom,$av['contract_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+3) . $excelstartsfrom,$av['category_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,$av['user_name']);
                $format = 'mmm d,YYYY';
                $date = '';
                $dateVal = '';
                if(!empty($av['uploaded_on']))
                {
                    $date = new DateTime($av['uploaded_on']);
                    $dateVal = PHPExcel_Shared_Date::PHPToExcel($date);
                }
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,$dateVal);
                $this->excel->setActiveSheetIndex($sheet_number)->getStyle($this->getkey($columnBegin+4) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);
                //$this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+4) . $excelstartsfrom,date('M d,Y',strtotime($av['uploaded_on'])));
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+5) . $excelstartsfrom,$av['review_status']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+6) . $excelstartsfrom,$av['module_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+7) . $excelstartsfrom,$av['topic_name']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+8) . $excelstartsfrom,$av['question_text']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+9) . $excelstartsfrom,$ans);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+10) . $excelstartsfrom,$av['file_count']);
                $this->excel->setActiveSheetIndex($sheet_number)->setCellValue($this->getkey($columnBegin+11) . $excelstartsfrom,$av['document_name']);
            }
        }

        //Attachment sheet ends here
        
        $this->excel->setActiveSheetIndex(0);
        $filename = $result['result'][0]['name'].'_'.date("d-m-Y",strtotime(currentDate())).'.xls';
        // echo $filename;exit;//save our workbook as this file name
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $file_path = FILE_SYSTEM_PATH.'downloads/' . $filename;
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->save($file_path);
        $view_path='downloads/' . $filename;
        $file_path = REST_API_URL.$view_path;
        $file_path = str_replace('::1','localhost',$file_path);

        $insert_id = $this->Download_model->addDownload(array('path'=>$view_path,'filename'=>$filename,'user_id'=>$this->session_user_id,'access_token'=>substr($_SERVER['HTTP_AUTHORIZATION'],7),'status'=>0,'created_date_time'=>currentDate()));

        $response = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>pk_encrypt($insert_id));
        $this->response($response, REST_Controller::HTTP_OK);
    }

    function getReportActionItems($data){
        
        if($this->session_user_info->user_role_id==2){
    
        }
        else if($this->session_user_info->user_role_id==3){
            $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
            $contributor_modules = $this->User_model->check_record('contract_user',array('status'=>1,'user_id'=>$this->session_user_id));
            if(count($contributor_modules) > 0)
                $data['module_id'] = array_map(function($i){ return $i['module_id']; },$contributor_modules);
        }
        else if($this->session_user_info->user_role_id==4) {
            $data['delegate_id'] = $data['id_user'];
            $contributor_modules = $this->User_model->check_record('contract_user',array('status'=>1,'user_id'=>$this->session_user_id));
            if(count($contributor_modules) > 0)
                $data['module_id'] = array_map(function($i){ return $i['module_id']; },$contributor_modules);
        }
        else if($this->session_user_info->user_role_id==6){
            $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
            if($this->session_user_info->is_allow_all_bu==1){
                $bu_ids = $this->User_model->check_record_selected('GROUP_CONCAT(id_business_unit) as bu_ids','business_unit',array('status'=>1,'customer_id'=>$this->session_user_info->customer_id));
                $data['business_unit_id'] = explode(',',$bu_ids[0]['bu_ids']);
            }
        }else if($this->session_user_info->user_role_id == 7){
            //$data['responsible_user_id'][] = $this->session_user_info->id_user;
            $provider_colleuges = $this->User_model->check_record('user',array('provider'=>$this->session_user_info->provider));
            $provider_colleuges = array_map(function($i){ return $i['id_user']; },$provider_colleuges);
            $data['provider_colleuges'] = $provider_colleuges;
        }
        $data['id_user'] = $this->session_user_info->id_user;
        $data['customer_id'] = $this->session_user_info->customer_id;
        $data['item_status']=1;
        $result = $this->Contract_model->getActionItems($data);
        unset($data['item_status']);
        foreach($result['data'] as $key => $value){
            $result['data'][$key]['comments_log'] = $this->Contract_model->contractReviewActionItemLog(array('id_contract_review_action_item' => $result['data'][$key]['id_contract_review_action_item']));
            $result['data'][$key]['due_date'] = date('Y-m-d',strtotime($result['data'][$key]['due_date']));
            $contract_review = $this->Contract_model->getContractReview(array('id_contract_review'=>$result['data'][$key]['contract_review_id']));
            $contract_info = $this->Contract_model->getContractDetails(array('id_contract'=>$result['data'][$key]['contract_id']));
            //echo '<pre>';print_r($contract_review);print_r($contract_info);
            $result['data'][$key]['last_review'] = date('Y-m-d',strtotime($contract_review[0]['updated_date']));
            $result['data'][$key]['contract_name'] = $contract_info[0]['contract_name'];
            $result['data'][$key]['contract_unique_id'] = $contract_info[0]['contract_unique_id'];
            $result['data'][$key]['provider_name'] = $contract_info[0]['provider_name_show'];
                $user_info = $this->User_model->getUserInfo(array('user_id'=>$value['created_by']));
            $result['data'][$key]['created_by_name'] = $user_info->first_name.' '.$user_info->last_name;
            foreach($result['data'][$key]['comments_log'] as $keyC => $valueC){
                $result['data'][$key]['comments_log'][$keyC]['contract_review_action_item_id']=pk_encrypt($result['data'][$key]['comments_log'][$keyC]['contract_review_action_item_id']);
                $result['data'][$key]['comments_log'][$keyC]['id_contract_review_action_item_log']=pk_encrypt($result['data'][$key]['comments_log'][$keyC]['id_contract_review_action_item_log']);
                $result['data'][$key]['comments_log'][$keyC]['updated_by']=pk_encrypt($result['data'][$key]['comments_log'][$keyC]['updated_by']);
            }
    
        }

        return $result;
    }
    function getReportAttachments($data){
       
        if($this->session_user_info->user_role_id==2 || $this->session_user_info->user_role_id==6){
            $Attachments = $this->Report_model->getAttachments(array('contract_review_ids' => $data['contract_review_ids']));
        }
        else{
            $module_ids = array(0);
            foreach($data['contract_review_ids'] as $rk => $rv){
                $contract_contributor = $this->User_model->check_record('contract_user',array('contract_review_id'=>$rv,'user_id'=>$this->session_user_id,'status'=>1));
                if(count($contract_contributor) > 0){
                    $module_ids = array_merge(array_map(function($i){return $i['module_id'];},$contract_contributor),$module_ids);    
                }else{
                    $review_moduels = $this->Contract_model->getTrendsModules(array('contract_review_id'=>$rv));
                    $module_ids = array_merge(array_map(function($i){return $i['id_module'];},$review_moduels),$module_ids);    
                }
            }
            $Attachments = $this->Report_model->getAttachments(array('module_ids'=>$module_ids));
        }
        // echo '<pre>'.$this->db->last_query();exit;

        return $Attachments;
    }
    /* function getReviewDashboard($contracts){
        //We are not using this function as from sprint 6.1
        $latest_contract_reviews = array();
        // foreach($contracts as $v)
        //     $latest_contract_reviews[] = $this->User_model->check_record_selected('MAX(id_contract_review) as id_contract_review,contract_id','contract_review',array('contract_id'=>$v))[0]['id_contract_review'];
        foreach($contracts as $v){
            $query_result = $this->Report_model->getReviewIds(array('contract_id'=>$v));
            $query_result = array_map(function($i){return $i['id_contract_review'];},$query_result);
            $latest_contract_reviews = array_merge($latest_contract_reviews,$query_result);
        }        
        return $this->Report_model->review_module_topic_qurestions(array('contract_review_ids'=>array_unique($latest_contract_reviews)));        
        //echo '<pre>'.print_r($module_topic_qurestions);exit;
    } */



}