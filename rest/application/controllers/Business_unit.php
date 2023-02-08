<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Business_unit extends REST_Controller
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
    public $session_user_contract_review_topics=NULL;
    public $session_user_contract_review_questions=NULL;
    public $session_user_contract_review_question_options=NULL;
    public $session_user_wadmin_relationship_categories=NULL;
    public $session_user_wadmin_relationship_classifications=NULL;
    public $session_user_own_business_units=NULL;
    public $session_user_review_business_units=NULL;
    public function __construct()
    {
        parent::__construct();
        if(isset($_SERVER['HTTP_USER'])){
            $this->user_id = pk_decrypt($_SERVER['HTTP_USER']);
        }
        $this->load->model('Validation_model');
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
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
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        $this->session_user_own_business_units=$this->session_user_business_units;
        $this->session_user_master_countries=$this->Validation_model->getCountries();
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_review_business_units=$this->Validation_model->getReviewBusinessUnits(array('id_user'=>$this->session_user_id));
        $this->session_user_business_units=array_merge($this->session_user_business_units,$this->session_user_review_business_units);
    }

    public function list_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('user_role_id', array('required'=>$this->lang->line('user_role_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
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
        if(isset($data['user_role_id']) || isset($data['login_as_user_role_id'])){
            $user_role_id=$data['user_role_id'];
            if(isset($data['login_as_user_role_id']))
                $user_role_id=$data['login_as_user_role_id'];
        }
        $user_id=isset($data['id_user'])?$data['id_user']:$this->user_id;
        if(isset($data['id_user']) || isset($data['login_as_id_user'])){
            $user_id=$data['id_user'];
            if(isset($data['login_as_id_user']))
                $user_id=$data['login_as_id_user'];
        }
        if(isset($user_role_id) && in_array($user_role_id,array(3,4,8))){ //3 means bu owner, can able to get own business units //4 delegate user //5 contributor
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $this->session_user_id,'status' => 1));
            $business_unit_id = array_map(function($i){ return $i['business_unit_id']; },$business_unit);
            $data['business_unit_array'] = $business_unit_id;
            // $reviewBusinessUnits=$this->Validation_model->getReviewBusinessUnits(array('id_user'=>$this->session_user_id));
            // $data['business_unit_array']=array_merge($data['business_unit_array'],$reviewBusinessUnits);
            //print_r($data['business_unit_array']);exit;
        }
        if(isset($user_role_id) && $user_role_id==6){
            $data['business_unit_array']=$this->session_user_business_units;
        }
        if(isset($user_role_id) && $user_role_id==7){
            $data['business_unit_array']=$this->Business_unit_model->getProviderBusinessUnits(array('user_id'=>$this->session_user_id));
            $data['business_unit_array'] = array_map(function($i){ return $i['bulist']; },$data['business_unit_array']);
        }
        if(isset($data['business_unit_array']) && count($data['business_unit_array'])==0)
            $data['business_unit_array'] = array(0);

            
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $result = $this->Business_unit_model->getBusinessUnitList($data);//print_r($data);exit;
        //echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['iobuuboi']='annus';
            if(!in_array($result['data'][$k]['id_business_unit'],$this->session_user_own_business_units) && in_array($result['data'][$k]['id_business_unit'],$this->session_user_review_business_units)){
                $result['data'][$k]['iobuuboi']='itako';
            }
            $result['data'][$k]['id_business_unit']=pk_encrypt($result['data'][$k]['id_business_unit']);
            $result['data'][$k]['country_id']=pk_encrypt($result['data'][$k]['country_id']);
            $result['data'][$k]['created_by']=pk_encrypt($result['data'][$k]['created_by']);
            $result['data'][$k]['customer_id']=pk_encrypt($result['data'][$k]['customer_id']);
            $result['data'][$k]['id_country']=pk_encrypt($result['data'][$k]['id_country']);
            $result['data'][$k]['updated_by']=pk_encrypt($result['data'][$k]['updated_by']);

        }
        // $bu_ids_array=implode(',',array_column($result['data'],'id_business_unit'));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records'],'array_bu_ids'=>$bu_ids_array));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function details_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_business_unit', array('required'=>$this->lang->line('business_unit_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_business_unit'])) {
            $data['id_business_unit'] = pk_decrypt($data['id_business_unit']);
            if(!in_array($data['id_business_unit'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Business_unit_model->getBusinessUnitDetails(array('id_business_unit' => $data['id_business_unit']));
        foreach($result as $k=>$v){
            $result[$k]['id_business_unit']=pk_encrypt($result[$k]['id_business_unit']);
            $result[$k]['customer_id']=pk_encrypt($result[$k]['customer_id']);
            $result[$k]['country_id']=pk_encrypt($result[$k]['country_id']);
            $result[$k]['created_by']=pk_encrypt($result[$k]['created_by']);
            $result[$k]['updated_by']=pk_encrypt($result[$k]['updated_by']);
            $result[$k]['id_country']=pk_encrypt($result[$k]['id_country']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function add_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('bu_name', array('required'=>$this->lang->line('bu_name_req')));
        $this->form_validator->add_rules('bu_responsibility', array('required'=>$this->lang->line('bu_responsibility_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
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
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['country_id'])) {
            $data['country_id'] = pk_decrypt($data['country_id']);
            if(!in_array($data['country_id'],$this->session_user_master_countries)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $add = array(
            'customer_id' => $data['customer_id'],
            'bu_name' => $data['bu_name'],
            'bu_responsibility' => $data['bu_responsibility'],
            'company_address' => (isset($data['company_address']) && !empty($data['company_address']))?$data['company_address']:null,
            'postal_code' => (isset($data['postal_code']) && !empty($data['postal_code']))?$data['postal_code']:null,
            'city' => (isset($data['city']) && !empty($data['city']))?$data['city']:null,
            'country_id' => (isset($data['country_id']) && !empty($data['country_id']))?$data['country_id']:null,
            'vat_number' => (isset($data['vat_number']) && !empty($data['vat_number']))?$data['vat_number'] : null,
            'created_by' => $data['created_by'],
            'created_on' => currentDate()
        );

        //if(!isset($data['postal_code'])){ unset($add['postal_code']); }
        //if(!isset($data['country_id'])){ unset($add['country_id']); }

        $this->Business_unit_model->addBusinessUnit($add);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('business_unit_create'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_business_unit', array('required'=>$this->lang->line('business_unit_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('bu_name', array('required'=>$this->lang->line('bu_name_req')));
        $this->form_validator->add_rules('bu_responsibility', array('required'=>$this->lang->line('bu_responsibility_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_business_unit'])) {
            $data['id_business_unit'] = pk_decrypt($data['id_business_unit']);
            if(!in_array($data['id_business_unit'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
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
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['country_id'])) {
            $data['country_id'] = pk_decrypt($data['country_id']);
            if(!in_array($data['country_id'],$this->session_user_master_countries)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $update = array(
            'id_business_unit' => $data['id_business_unit'],
            'customer_id' => $data['customer_id'],
            'bu_name' => $data['bu_name'],
            'bu_responsibility' => $data['bu_responsibility'],
            'company_address' => (isset($data['company_address']) && !empty($data['company_address']))?$data['company_address']:null,
            'postal_code' => (isset($data['postal_code']) && !empty($data['postal_code']))?$data['postal_code']:null,
            'city' => (isset($data['city']) && !empty($data['city']))?$data['city']:null,
            'country_id' => (isset($data['country_id']) && !empty($data['country_id']))?$data['country_id']:null,
            'updated_by' => $this->session_user_id,
            'status' => $data['status'],
            'vat_number' => (isset($data['vat_number']) && !empty($data['vat_number']))?$data['vat_number'] : null,
            'updated_on' => currentDate()
        );
        //if(!isset($data['postal_code'])){ unset($update['postal_code']); }
        //if(!isset($data['country_id'])){ unset($update['country_id']); }

        $this->Business_unit_model->updateBusinessUnit($update);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('business_unit_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function bulist_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('user_role_id', array('required'=>$this->lang->line('user_role_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
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
        if(isset($data['user_role_id']) || isset($data['login_as_user_role_id'])){
            $user_role_id=$data['user_role_id'];
            if(isset($data['login_as_user_role_id']))
                $user_role_id=$data['login_as_user_role_id'];
        }
        $user_id=isset($data['id_user'])?$data['id_user']:$this->user_id;
        if(isset($data['id_user']) || isset($data['login_as_id_user'])){
            $user_id=$data['id_user'];
            if(isset($data['login_as_id_user']))
                $user_id=$data['login_as_id_user'];
        }


        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $result = $this->Business_unit_model->getBusinessUnitList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['iobuuboi']='annus';
            if(!in_array($result['data'][$k]['id_business_unit'],$this->session_user_own_business_units) && in_array($result['data'][$k]['id_business_unit'],$this->session_user_review_business_units)){
                $result['data'][$k]['iobuuboi']='itako';
            }
            $result['data'][$k]['id_business_unit']=pk_encrypt($result['data'][$k]['id_business_unit']);
            $result['data'][$k]['country_id']=pk_encrypt($result['data'][$k]['country_id']);
            $result['data'][$k]['created_by']=pk_encrypt($result['data'][$k]['created_by']);
            $result['data'][$k]['customer_id']=pk_encrypt($result['data'][$k]['customer_id']);
            $result['data'][$k]['id_country']=pk_encrypt($result['data'][$k]['id_country']);
            $result['data'][$k]['updated_by']=pk_encrypt($result['data'][$k]['updated_by']);
        }
        $provider_bulist = $this->Business_unit_model->getProviderUserBusinessUnits($data);
        foreach($provider_bulist as $kp => $vp){
            $provider_bulist[$kp]['iobuuboi']='annus';
            if(!in_array($provider_bulist[$kp]['id_business_unit'],$this->session_user_own_business_units) && in_array($provider_bulist[$kp]['id_business_unit'],$this->session_user_review_business_units)){
                $provider_bulist[$kp]['iobuuboi']='itako';
            }
            $provider_bulist[$kp]['id_business_unit']=pk_encrypt($provider_bulist[$kp]['id_business_unit']);
            $provider_bulist[$kp]['country_id']=pk_encrypt($provider_bulist[$kp]['country_id']);
            $provider_bulist[$kp]['created_by']=pk_encrypt($provider_bulist[$kp]['created_by']);
            $provider_bulist[$kp]['customer_id']=pk_encrypt($provider_bulist[$kp]['customer_id']);
            $provider_bulist[$kp]['id_country']=pk_encrypt($provider_bulist[$kp]['id_country']);
            $provider_bulist[$kp]['updated_by']=pk_encrypt($provider_bulist[$kp]['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('expert' =>$result['data'],'validator' => $provider_bulist));
        $this->response($result, REST_Controller::HTTP_OK);
    }

}