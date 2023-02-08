<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Master extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('Project_model');
        $this->load->model('Customer_model');
        $this->load->model('Catalogue_model');
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
        else if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4)
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

    public function countryList_get()
    {
        $data = $this->input->get();
        $result = $this->Master_model->getCountryList($data);
        foreach($result as $k=>$v)
            $result[$k]['id_country'] = pk_encrypt($v['id_country']);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function role_get()
    {
        $data = $this->input->get();
        if(isset($data['user_role_id']))
            $data['user_role_id']=pk_decrypt($data['user_role_id']);
        // $result = $this->Master_model->getUserRole($data);
        if($data['user_role_id']==2)
        $data['user_role_ids']=array(3,4,6,8);
        if($data['user_role_id']==3)
        $data['user_role_ids']=array(4);
        if($data['user_role_id']==8)
        $data['user_role_ids']=array(3,4);
        $result=$this->User_model->getRoles($data);
        foreach($result as $k=>$v){
            $result[$k]['id_user_role']=pk_encrypt($result[$k]['id_user_role']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function currencyList_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        // $result = $this->Master_model->getCurrencyList($data);echo $this->db->last_query();exit;
        $result=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'status'=>1,'is_deleted'=>0));
        // echo $this->db->last_query();exit;
        foreach($result as $k=>$v){
            $result[$k]['id_currency'] = pk_encrypt($v['id_currency']);
            $result[$k]['customer_id'] = pk_encrypt($v['customer_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function GeneratecontractId_get(){
        $data = $this->input->get();
        //print_r($data['type']); exit;
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        // $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$data['customer_id']));
        // // echo $this->db->last_query();exit;
        // $countofcantracts=count($get_contracts);
        // $contract_unique_id='C'.str_pad($countofcantracts+1, 7, '0', STR_PAD_LEFT);
        $contract_unique_id = uniqueId(array('module' => 'contract' , 'customer_id' => $this->session_user_info->customer_id));
        if(isset($data['type'])=='sub_contract'){
            $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => array('sub_contract_unique_id'=>$contract_unique_id));
        }
        else{
            $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => array('contract_unique_id'=>$contract_unique_id));
        }
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function GenerateProductId_get(){
        $data = $this->input->get();
        //print_r($data['type']); exit;
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
        }
        // $projects_contracts=$this->User_model->getProjectsBybuid(array('customer_id'=>$data['customer_id']));
        // $countofprojects=count($projects_contracts);
        // $project_unique_id='PJ'.str_pad($countofprojects+1, 7, '0', STR_PAD_LEFT);
        $project_unique_id=uniqueId(array('module' => 'project' , 'customer_id' => $this->session_user_info->customer_id));
        $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => array('project_unique_id'=>$project_unique_id));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function getConnectedContractsProjects_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['project_id'])){
            $data['project_id'] =pk_decrypt($data['project_id']);
        }
        if(isset($data['contract_id'])){
            $data['contract_id']=pk_decrypt($data['contract_id']);
        }
        $result = $this->Contract_model->getConnectedProjectsContracts($data);
        // echo ''.$this->db->last_query(); exit;
        // print_r($this->session_user_info->user_role_id!=2);exit;
        foreach($result as $k=>$v){
            $result[$k]['is_contract_access']=0;
            if($this->session_user_info->user_role_id==2){
                $result[$k]['is_contract_access']=1;
            }
            // print_r($this->session_user_info->id_user);exit;
            if(in_array($this->session_user_info->user_role_id,array(3,4))){
                
                if($v['contract_owner_id']==$this->session_user_info->id_user || $v['delegate_id']==$this->session_user_info->id_user){
                    $result[$k]['is_contract_access']=1;
                }
            }
            $result[$k]['contract_owner_id']=pk_encrypt($v['contract_owner_id']);
            $result[$k]['id_contract']= pk_encrypt($v['id_contract']);
            $result[$k]['delegate_id']=pk_encrypt($v['delegate_id']);
        }
        $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' =>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function deleteConnectedcontracts_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
        }

        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
           
        }
        if(isset($data['project_id'])) {
            $data['project_id'] = pk_decrypt($data['project_id']);
        }
        $this->User_model->update_data('contract_projects',array('is_linked'=>0),array('contract_id'=>$data['contract_id'],'project_id'=>$data['project_id']));
        //echo ''.$this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('contract_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function addContractToProject_post()
    {
        $data= $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['contract_id']);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
           
        }
        if(isset($data['project_id'])) {
            $data['project_id'] = pk_decrypt($data['project_id']);
        }
        // print_r($data);exit;
        $check_record_exist=$this->User_model->check_record('contract_projects',array('contract_id'=>$data['contract_id'],'project_id'=>$data['project_id']));
        // echo $this->db->last_query();exit;
        if(!empty($check_record_exist)){
            if(isset($check_record_exist[0]['is_linked']) && $check_record_exist[0]['is_linked']==1){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('contract_alrady_link')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($check_record_exist[0]['is_linked']) && $check_record_exist[0]['is_linked']==0){
                $this->User_model->update_data('contract_projects',array('is_linked'=>1),array('contract_id'=>$data['contract_id'],'project_id'=>$data['project_id']));
            }
        }
        else{
            $this->User_model->insert_data('contract_projects',array('contract_id'=>$data['contract_id'],'project_id'=>$data['project_id'],'is_linked'=>1));
        }
        //echo ''.$this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('contract_linked'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function getMasterCurrency_get(){
        $data= $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['customer_id'] = pk_decrypt($data['customer_id']);
        $master_currency=$this->User_model->check_record_order('currency',array('customer_id'=>0,'status'=>1),"currency_full_name","ASC");
        foreach($master_currency as $k=>$v){
            $master_currency[$k]['id_currency']=pk_encrypt($master_currency[$k]['id_currency']);
            $master_currency[$k]['customer_id']=pk_encrypt($master_currency[$k]['customer_id']);
            $master_currency[$k]['currency_full_name']=$master_currency[$k]['currency_full_name']." ( ".$master_currency[$k]['currency_name']." )";
        }
        $main_currency=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1,'status'=>1,'is_deleted'=>0));
        $main_currency[0]['id_currency']=pk_encrypt($main_currency[0]['id_currency']);
        $main_currency[0]['customer_id']=pk_encrypt($main_currency[0]['customer_id']);

        $data = tableOptions($data);
        // print_r($data);exit;

        // $additional_currencies=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>0));
        $get_additional_currencies=$this->Master_model->getadditionalcurs(array('customer_id'=>$data['customer_id'],'search'=>$data['search'],'sort'=>$data['sort'],'pagination'=>$data['pagination'],'can_access'=>$data['can_access']));
        $additional_currencies=$get_additional_currencies['data'];
        // print_r($additional_currencies);exit;
        // echo $this->db->last_query();exit;
        foreach($additional_currencies as $k1=>$v1){
            $additional_currencies[$k1]['id_currency']=pk_encrypt($additional_currencies[$k1]['id_currency']);
            $additional_currencies[$k1]['customer_id']=pk_encrypt($additional_currencies[$k1]['customer_id']);
        }
        $is_disable_master_currency=0;
        if(!empty($additional_currencies)){
            $is_disable_master_currency=1;
        }
        else{
            $is_disable_master_currency=0;
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$master_currency,'main_currency'=>$main_currency,'additional_currencies'=>$additional_currencies,'is_disable_master_currency'=>$is_disable_master_currency,'total_records'=>$get_additional_currencies['total_records']);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function createAdditionalCurrency_post(){
        $data= $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('currency_full_name', array('required'=>$this->lang->line('currency_name_req')));
        $this->form_validator->add_rules('currency_code', array('required'=>$this->lang->line('currency_code_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        if(!is_numeric(str_replace(',','',$data['exchange_rate'])) || str_replace(',','',$data['exchange_rate'])<=0){
            // $result = array('status'=>FALSE,'error'=>$this->lang->line('exchange_rate_is_numaric'),'data'=>'');
            // $this->response($result, REST_Controller::HTTP_OK);

            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('exchange_rate_is_numaric')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }


        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        }
        // print_r(!is_numeric(str_replace(',','',$data['exchange_rate'])));exit;
        $currency_data=array(
            'currency_name'=>$data['currency_code'],
            'currency_full_name'=>explode(" (",$data['currency_full_name'])[0],
            'customer_id'=>$data['customer_id'],
            'is_maincurrency'=>0,
            'status'=>isset($data['status'])?$data['status']:1,
            'euro_equivalent_value'=>isset($data['exchange_rate'])?$data['exchange_rate']:0,
            'created_date_time'=>currentDate(),
            'created_by'=>$_SERVER['HTTP_LOGGEDIN_USER']
        );
        $this->User_model->insert_data('currency',$currency_data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('currency_added'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function getAvailableCurrencies_get()
    {
        $data= $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['customer_id'] = pk_decrypt($data['customer_id']);   
        
        $get_already_existing_cur=$this->User_model->check_record_selected('currency_name','currency',array('customer_id'=>$data['customer_id'],'is_deleted'=>0));
        $existing_cur=array_column($get_already_existing_cur,'currency_name');
        if(!empty($data['type']) && !empty($data['currency_code'])){
            foreach($existing_cur as $cur=>$val){
                if($data['currency_code']==$val){
                    unset($existing_cur[$cur]);
                }
            }
        }
        $getAvailableCurrencies=$this->Master_model->getAvailableCurrencies(array('not_in_codes'=>$existing_cur,'customer_id'=>$data['customer_id'],));
        foreach($getAvailableCurrencies as $k=>$v){
            $getAvailableCurrencies[$k]['id_currency']=pk_encrypt($getAvailableCurrencies[$k]['id_currency']);
            $getAvailableCurrencies[$k]['customer_id']=pk_encrypt($getAvailableCurrencies[$k]['customer_id']);
            $getAvailableCurrencies[$k]['currency_full_name']=$getAvailableCurrencies[$k]['currency_full_name']." ( ".$getAvailableCurrencies[$k]['currency_name']." )";
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'availableCurrencies'=>$getAvailableCurrencies);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updatemastercurrency_post(){
        $data= $this->input->post();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('new_currency_code', array('required'=>$this->lang->line('customer_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['customer_id'] = pk_decrypt($data['customer_id']);
        $this->User_model->update_data('currency',array('is_maincurrency'=>0,'is_deleted'=>1),array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));      
      //  echo $this->db->last_query();exit;
        //$this->db->delete('currency', array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));  
        $get_currency_info=$this->User_model->check_record('currency',array('currency_name'=>$data['new_currency_code']));
        // print_r($get_currency_info);exit; 
        $this->User_model->insert_data('currency',array('currency_name'=>$get_currency_info[0]['currency_name'],'currency_full_name'=>$get_currency_info[0]['currency_full_name'],'euro_equivalent_value'=>$get_currency_info[0]['euro_equivalent_value'],'customer_id'=>$data['customer_id'],'is_maincurrency'=>1,'status'=>1,'updated_by'=>$_SERVER['HTTP_LOGGEDIN_USER'],'updated_date_time'=>currentDate()));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('main_currency_updated'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function updateAddtionalcurency_post(){
        $data= $this->input->post();
        $this->form_validator->add_rules('id_currency', array('required'=>$this->lang->line('currency_id_req')));
        if(!is_numeric(str_replace(',','',$data['exchange_rate'])) || str_replace(',','',$data['exchange_rate'])<=0){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('exchange_rate_is_numaric')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $validated = $this->form_validator->validate($data); 
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['id_currency']=pk_decrypt($data['id_currency']);
        if(!empty($data['exchange_rate'])){
            $update_data['euro_equivalent_value']=$data['exchange_rate']; 
        }
        if(isset($data['status'])){
            $update_data['status']=$data['status']; 
        }
        $update_data['updated_date_time']=currentDate(); 
        $update_data['updated_by']=$_SERVER['HTTP_LOGGEDIN_USER']; 
        $this->User_model->update_data('currency',$update_data,array('id_currency'=>$data['id_currency']));
        // echo $this->db->last_query();exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('additional_currency_updated'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function currencyInfo_get(){
        $data= $this->input->get();
        $this->form_validator->add_rules('id_currency', array('required'=>$this->lang->line('currency_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['id_currency'] = pk_decrypt($data['id_currency']);   
        $currency_info=$this->User_model->check_record_selected('id_currency,currency_name  as currency_code,euro_equivalent_value as exchange_rate,currency_full_name,created_by,customer_id,`status`,updated_by','currency',array('id_currency'=>$data['id_currency'],'is_deleted'=>0));
        foreach($currency_info as $k=>$v){
            $currency_info[$k]['currency_full_name']=$currency_info[$k]['currency_full_name']." ( ".$currency_info[$k]['currency_code']." )";
            $currency_info[$k]['id_currency']=pk_encrypt($currency_info[$k]['id_currency']);
            $currency_info[$k]['customer_id']=pk_encrypt($currency_info[$k]['customer_id']);
            $currency_info[$k]['created_by']=pk_encrypt($currency_info[$k]['created_by']);
            $currency_info[$k]['updated_by']=pk_encrypt($currency_info[$k]['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'info'=>$currency_info);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function mapChildContract_post(){
        
        $data= $this->input->post();
        $this->form_validator->add_rules('parent_contract_id', array('required'=>$this->lang->line('parent_contract_id_req')));
        $this->form_validator->add_rules('child_contract_id', array('required'=>$this->lang->line('child_contract_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['parent_contract_id'])){
            $data['parent_contract_id']=pk_decrypt($data['parent_contract_id']);
        }
        if(!empty($data['child_contract_id'])){
            $data['child_contract_id']=pk_decrypt($data['child_contract_id']);
        }
        // print_r($data);exit;
        if($data['parent_contract_id']==$data['child_contract_id']){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('same_not_possible'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $check_single=$this->User_model->check_record('contract',array('parent_contract_id'=>$data['child_contract_id'],'is_deleted'=>0));
        if(count($check_single)==0){
            $this->User_model->update_data('contract',array('parent_contract_id'=>$data['parent_contract_id']),array('id_contract'=>$data['child_contract_id']));
            $result = array('status'=>TRUE, 'message' => $this->lang->line('sub_arg_mapped'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result = array('status'=>FALSE,'error'=>$this->lang->line('signle_contracts_only_mpped'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }
    public function unmapChildContract_post(){
        $data= $this->input->post();
        $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['id_contract'])){
            $data['id_contract']=pk_decrypt($data['id_contract']);
        }
        $this->User_model->update_data('contract',array('parent_contract_id'=>'0'),array('id_contract'=>$data['id_contract']));
        // echo $this->db->last_query();exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('sub_arg_un_mapped'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function contractStaticTabsCount_get(){
        $get_tags=$this->User_model->check_record_selected('id_tag','tag',array('customer_id'=>$this->session_user_info->customer_id,'type'=>'contract_tags','status'=>1));
        $get_contracts=$this->User_model->getcontractsBybuid(array('customer_id'=>$this->session_user_info->customer_id));
        // echo $this->db->last_query();exit;
        $countofcantracts=count($get_contracts);
        $contract_unique_id='C'.str_pad($countofcantracts+1, 7, '0', STR_PAD_LEFT);
            $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'obligations_count'=>'0','contract_information'=>'1/15','contract_spent_managment'=>'0/6','contract_stake_holder'=>'0/6','contract_tags'=>'0/'.count($get_tags),'contract_attachments'=>'0',
            'contract_unique_id'=>$contract_unique_id
        );
            $this->response($result, REST_Controller::HTTP_OK);    
    }
    public function getMasterDomains_get(){
        $data= $this->input->get();
        $this->form_validator->add_rules('domain_module', array('required'=>$this->lang->line('domain_module_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $getDomains=$this->User_model->check_record_selected('id_master_domain,module,domain','master_domain',array('module'=>$data['domain_module'],'status'=>1));
        foreach($getDomains as $k=>$v){
            $getDomains[$k]['id_master_domain']=pk_encrypt($v['id_master_domain']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'),'data'=>$getDomains);
        $this->response($result, REST_Controller::HTTP_OK);    
    }
    public function getMasterDomainFields_get(){
        $data= $this->input->get();
        $this->form_validator->add_rules('id_master_domain', array('required'=>$this->lang->line('id_master_domain_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['id_master_domain'])){
            $data['id_master_domain']=pk_decrypt($data['id_master_domain']);
        }
        $get_domain=$this->User_model->check_record('master_domain',array('id_master_domain'=>$data['id_master_domain']));
        if($get_domain[0]['domain'] == 'Relation Tags' || $get_domain[0]['domain'] == 'Contract Tags' || $get_domain[0]['domain'] == 'Catalogue Tags'){
            if($get_domain[0]['domain'] == 'Relation Tags')
            {
                $tagType="provider_tags"; 
                $orderBy ="ORDER BY t.is_fixed desc,t.tag_order asc";
                $where ="";
            }elseif($get_domain[0]['domain'] == 'Contract Tags')
            {
                $tagType="contract_tags";
                $orderBy ="ORDER BY t.tag_order ASC";
                $where ="";
            }elseif($get_domain[0]['domain'] == 'Catalogue Tags')
            {
                $tagType="catalogue_tags";
                $orderBy ="ORDER BY t.tag_order ASC";
                $where ="";
            }
            $customer_id=$this->session_user_info->customer_id;
            $query="SELECT t.tag_type,l.tag_text as field_name,t.id_tag as id_master_domain_fields,CASE WHEN t.tag_type='input' and (t.field_type='number' or t.field_type='currency') THEN 'numeric_text' WHEN t.tag_type='dropdown' or  t.tag_type='rag' or t.tag_type='radio' or t.tag_type='selected' then 'drop_down' WHEN t.tag_type='input'and t.field_type='text' then 'free_text' else 'date' END as field_type ,t.selected_field FROM `tag` `t` LEFT JOIN `tag_language` `l` ON `t`.`id_tag`=`l`.`tag_id` WHERE `t`.`customer_id` = $customer_id AND `t`.`status` = '1' AND `t`.`type` = '$tagType' $where $orderBy";
            $getDomainsFields=$this->User_model->custom_query($query);
        }
        else{
            $getDomainsFields=$this->User_model->check_record_selected('id_master_domain_fields,master_domain_id,field_name,field_type,table_alias,`status`','master_domain_fields',array('master_domain_id'=>$data['id_master_domain'],'status'=>1));
        }
        foreach($getDomainsFields as $k=>$v){
            $getDomainsFields[$k]['id_master_domain_fields']=pk_encrypt($v['id_master_domain_fields']);
            $getDomainsFields[$k]['master_domain_id']=pk_encrypt($v['master_domain_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'),'data'=>$getDomainsFields);
        $this->response($result, REST_Controller::HTTP_OK);    
    }
    public function createFilter_post()
    {
        // $data = $this->input->post();
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // $this->form_validator->add_rules('user_id', array('required'=>$this->lang->line('user_id_req')));
        $this->form_validator->add_rules('value', array('required'=>$this->lang->line('value_req')));
        $this->form_validator->add_rules('master_domain_id', array('required'=>$this->lang->line('id_master_domain_req')));
        $this->form_validator->add_rules('master_domain_field_id', array('required'=>$this->lang->line('master_domain_field_id_req')));
        $this->form_validator->add_rules('condition', array('required'=>$this->lang->line('condition_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(isset($data['master_domain_id'])){
            $data['master_domain_id']=pk_decrypt($data['master_domain_id']);
        }
        if(isset($data['master_domain_field_id'])){
            $data['master_domain_field_id']=pk_decrypt($data['master_domain_field_id']);
        }
        // if(isset($data['user_id'])){
        //     $data['user_id']=pk_decrypt($data['user_id']);
        // }
        $data['user_id'] = $this->session_user_id;
        if(isset($data['id_master_filter'])){
            $data['id_master_filter']=pk_decrypt($data['id_master_filter']);
        }
        $masterDomainDetails = $this->User_model->check_record('master_domain',array('id_master_domain'=>$data['master_domain_id']));
        $masterDomainFieldDetails = $this->User_model->check_record('master_domain_fields',array('id_master_domain_fields'=>$data['master_domain_field_id']));
        if((empty($masterDomainDetails) || empty($masterDomainFieldDetails)))
        {
            if(!(!empty($masterDomainDetails) && ($masterDomainDetails[0]['domain'] == 'Contract Tags' || $masterDomainDetails[0]['domain'] == 'Relation Tags' || $masterDomainDetails[0]['domain'] == 'Catalogue Tags')))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('domain_or_field_is_invalid')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if($this->session_user_info->user_role_id == 7  && $masterDomainDetails[0]['module'] != 'action_items')
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //$masterDomainFieldDetails = $this->User_model->check_record('master_domain_fields',array('id_master_domain_fields'=>$data['master_domain_field_id']));
        if($masterDomainDetails[0]['domain'] == 'Contract Tags' || $masterDomainDetails[0]['domain']=='Relation Tags' || $masterDomainDetails[0]['domain']=='Catalogue Tags'){
            $TagDetails = $this->Tag_model->getTagInfo(array("id_tag"=>$data['master_domain_field_id']));
            $masterDomainFieldDetails[0]['database_field']='tag_option_value';
            if(!empty($TagDetails))
            {
                $masterDomainFieldDetails[0]['field_type']=$TagDetails[0]['field_type'];
                
                if($TagDetails[0]['tag_type'] == "input") {
                    $masterDomainFieldDetails[0]['field_type'] = ($TagDetails[0]['field_type'] == "number") ? "numeric_text" : "free_text";
                }
                elseif($TagDetails[0]['tag_type'] == "dropdown" || $TagDetails[0]['tag_type'] == "rag" || $TagDetails[0]['tag_type'] == "radio" || $TagDetails[0]['tag_type'] == "selected"){
                    $masterDomainFieldDetails[0]['field_type'] = 'drop_down';
                    $masterDomainFieldDetails[0]['database_field']='tag_option';
                }
                elseif($TagDetails[0]['tag_type'] == "date" ){
                    $masterDomainFieldDetails[0]['field_type'] = 'date';
                }
                $masterDomainFieldDetails[0]['field_name']=$TagDetails[0]['tag_text'];  
            }
           
            // $masterDomainFieldDetails[0]['field_type']=$data['field_type'];
            // $masterDomainFieldDetails[0]['field_name']=$data['field'];
            $masterDomainFieldDetails[0]['table_alias']=Null;
            $masterDomainFieldDetails[0]['is_union_table'] = 0;
        }
        if($masterDomainDetails[0]['domain']=='Contract Attachments' || $masterDomainDetails[0]['domain']=='Project Attachments'|| $masterDomainDetails[0]['domain']=='Relation Attachments' || $masterDomainDetails[0]['domain']=='Archive Attachments' ||$masterDomainDetails[0]['domain']=='Activity Attachments' || $masterDomainDetails[0]['domain']=='Catalogue Attachments'){
            $masterDomainFieldDetails[0]['database_field']='document_names';
            //$masterDomainFieldDetails[0]['field_type']=$data['field_type'];
            $masterDomainFieldDetails[0]['field_type']='free_text';
            $masterDomainFieldDetails[0]['field_name']=$data['field'];
            $masterDomainFieldDetails[0]['table_alias']=Null;
            $masterDomainFieldDetails[0]['is_union_table'] = 1;
            $data['condition'] = "like";
            if($data['field']=='Link URL'){
                $masterDomainFieldDetails[0]['database_field']='document_urls';
            }
        }
        if(in_array($data['field'],array('Review Module Name','Submitted by','Activity topic'))){
                $masterDomainFieldDetails[0]['is_union_table'] = 1;
        }
        $donotDecrypt = ['contract_active_status','auto_renewal','hierarchy','invoice_status','type_name','applicable_to_name','calendar','email_notification','tag_answers','project_status','status','risk_profile','approval_status','finacial_health','tag_option_value','review_score','typeOfActivity','submited_by','type','connected_to','priority','allocation','activity_status','validation_status'];
        if($masterDomainFieldDetails[0]['field_type'] == 'drop_down' && !in_array($masterDomainFieldDetails[0]['database_field'],$donotDecrypt))
        {
            $data['to_explode'] = $data['value'];
            $data['value'] = implode(',',array_map(function($i){ return pk_decrypt($i); }, explode(',',$data['to_explode'])));
        }
        if(empty($data['condition']))
        {
            $data['condition'] = "=";
        }
        if(!isset($data['id_master_filter']))
        {
            $create_array=array(
                'user_id'=>$this->session_user_info->id_user,
                'module'=>$masterDomainDetails[0]['module'],
                'domain'=>$masterDomainDetails[0]['domain'],
                'database_field'=>$masterDomainFieldDetails[0]['database_field'],
                'table_alias'=>$masterDomainFieldDetails[0]['table_alias'],
                'field_type'=>$masterDomainFieldDetails[0]['field_type'],
                'field'=>$masterDomainFieldDetails[0]['field_name'],
                'condition'=>$data['condition'],
                'value'=>$data['value'],
                'created_by'=>$this->session_user_id,
                'created_date_time'=>currentDate(),
                'status'=>1,
                'master_domain_id'=>$data['master_domain_id'],
                'master_domain_field_id'=>$data['master_domain_field_id'],
                'is_union_table'=>$masterDomainFieldDetails[0]['is_union_table']
            );
            if($this->User_model->insert_data('user_advanced_filters',$create_array))
            {
                $result = array('status'=>true, 'message' => $this->lang->line('filter_added_successfully'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result = array('status'=>False, 'message' => $this->lang->line('something_went_wrong'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        else
        {
            $update_array=array(
                'user_id'=>$data['user_id'],
                'module'=>$masterDomainDetails[0]['module'],
                'domain'=>$masterDomainDetails[0]['domain'],
                'database_field'=>$masterDomainFieldDetails[0]['database_field'],
                'table_alias'=>$masterDomainFieldDetails[0]['table_alias'],
                'field_type'=>$masterDomainFieldDetails[0]['field_type'],
                'field'=>$masterDomainFieldDetails[0]['field_name'],
                'condition'=>$data['condition'],
                'value'=>$data['value'],
                'updated_by'=>$this->session_user_id,
                'updated_date_time'=>currentDate(),
                'status'=>1,
                'master_domain_id'=>$data['master_domain_id'],
                'master_domain_field_id'=>$data['master_domain_field_id'],
                'is_union_table'=>$masterDomainFieldDetails[0]['is_union_table']
            );
            if($this->User_model->update_data('user_advanced_filters',$update_array,array('id_master_filter'=>$data['id_master_filter'])))
            {
                $result = array('status'=>true, 'message' => $this->lang->line('filter_updated_successfully'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result = array('status'=>False, 'message' => $this->lang->line('something_went_wrong'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
    }
    public function filtersList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // $this->form_validator->add_rules('user_id', array('required'=>$this->lang->line('user_id_req')));
        $this->form_validator->add_rules('module', array('required'=>$this->lang->line('domain_module_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        
        if($this->session_user_info->user_role_id == 7  && $data['module'] != 'action_items')
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        $data['user_id'] = $this->session_user_info->id_user ;
        if(isset($data['id_master_filter']))
        {
            $data['id_master_filter']=pk_decrypt($data['id_master_filter']);
        }
        $filters = $this->Master_model->getFilters($data);
        foreach($filters as $k=>$val)
        {
            if($val['module']=='action_items' && $val['database_field']=='status'){
                $filters[$k]['value']=ucfirst($val['value']);    
            }
            $filters[$k]['id_master_filter'] = pk_encrypt($filters[$k]['id_master_filter']);
            $filters[$k]['user_id'] = pk_encrypt($filters[$k]['user_id']);
            $filters[$k]['master_domain_id'] = pk_encrypt($filters[$k]['master_domain_id']);
            $filters[$k]['master_domain_field_id'] = pk_encrypt($filters[$k]['master_domain_field_id']);
            $donotDecrypt = ['contract_active_status','auto_renewal','hierarchy','invoice_status','type_name','applicable_to_name','calendar','email_notification','tag_answers','project_status','status','risk_profile','approval_status','finacial_health','tag_option_value','review_score','typeOfActivity','submited_by','type','connected_to','priority','allocation','activity_status','validation_status'];
            $explodedData =  explode(',',$filters[$k]['value']);
            if($filters[$k]['field_type'] == 'drop_down' )
            {
                switch ($filters[$k]['database_field']) {
                    case 'provider_id':
                    case 'RelationId':
                    case 'provider_name':
                        $provider_name=array_column($this->User_model->check_record_whereIn('provider','id_provider',$explodedData,array('provider_name')), 'provider_name');
                        $filters[$k]['value_names'] =$provider_name;
                        break;   
                    case 'relationship_category_id':
                        $relationship_category_names=array_column($this->User_model->check_record_whereIn('relationship_category_language','relationship_category_id',$explodedData,array('relationship_category_name')), 'relationship_category_name');
                        $filters[$k]['value_names'] =$relationship_category_names;
                        break; 
                    case 'category_id':
                        $category_names=array_column($this->User_model->check_record_whereIn('provider_relationship_category_language','provider_relationship_category_id',$explodedData,array('relationship_category_name')), 'relationship_category_name');
                        $filters[$k]['value_names'] =$category_names;
                        break;
                    case 'template_id':
                        $template_names=array_column($this->User_model->check_record_whereIn('template','id_template',$explodedData,array('template_name')), 'template_name');
                        $filters[$k]['value_names'] =$template_names;
                        break; 
                    case 'auto_renewal':
                    case 'calendar':
                    case 'email_notification':    
                        $yesOrNo = array_map(function($i){ if($i ==1){return 'Yes';}elseif($i == 0){return "No";} }, $explodedData);
                        $filters[$k]['value_names'] =$yesOrNo;
                        break;
                    case 'currency_id':
                        $currency_names=array_column($this->User_model->check_record_whereIn('currency','id_currency',$explodedData,array('currency_name')), 'currency_name');
                        $filters[$k]['value_names'] =$currency_names;
                        break;
                    case 'business_unit_id':
                        $business_unit_name=array_column($this->User_model->check_record_whereIn('business_unit','id_business_unit',$explodedData,array('bu_name')), 'bu_name');
                        $filters[$k]['value_names'] =$business_unit_name;
                        break;   
                    case 'contract_owner_id':
                        $contract_owner_names =array_map(function($i){ return $i['first_name']." ".$i['last_name']; },$this->User_model->check_record_whereIn('user','id_user',$explodedData,array('first_name','last_name')));
                        $filters[$k]['value_names'] =$contract_owner_names;
                        break;
                    case 'contract_delegate_id':    
                    case 'delegate_id':
                        $delegate_name =array_map(function($i){ return $i['first_name']." ".$i['last_name']; },$this->User_model->check_record_whereIn('user','id_user',$explodedData,array('first_name','last_name')));
                        $filters[$k]['value_names'] =$delegate_name;
                        break; 
                    case 'expertContributer':
                    case 'validatorContributer':
                    case 'relationContributer':
                        $query = 'SELECT u.id_user,CONCAT(u.first_name, " ", u.last_name, " (", u.email, " | ", ur.user_role_name, " | ", IFNULL(GROUP_CONCAT((SELECT IF(ctry.country_name!="", CONCAT(bu1.bu_name, " - ", ctry.country_name), bu1.bu_name) as bu_name FROM business_unit bu1 LEFT JOIN country ctry ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=bu.id_business_unit)), p.provider_name), ")") as name, p.id_provider FROM user u LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role LEFT JOIN business_unit_user bur ON bur.user_id=u.id_user and bur.status=1 LEFT JOIN business_unit bu ON bur.business_unit_id=bu.id_business_unit and bu.status=1 LEFT JOIN provider p ON u.provider = p.id_provider WHERE  u.id_user IN ('.$filters[$k]['value'].') GROUP BY u.id_user';
                        $cUsers = $this->User_model->custom_query($query);                    
                        $contributers_names=array_column($cUsers, 'name');
                        $filters[$k]['value_names'] =$contributers_names; 
                        break;
                    case 'invoice_status':
                        $invoice_status = array_map(function($i){ 
                            if($i == 0){return "Disputed";}
                            elseif($i == 1){return "Partial";}
                            elseif($i == 2){return "Activated";}
                            elseif($i == 3){return "Overdue";}
                            elseif($i == 4){return "Draft";}
                            elseif($i == 5){return "Paid";}
                        }, $explodedData);
                        $filters[$k]['value_names'] =$invoice_status;
                        break;
                    case 'recurrence_id':
                    case 'resend_recurrence_id':
                    case 'payment_periodicity_id':    
                        $payment_periodicity_names=array_column($this->User_model->check_record_whereIn('payment_periodicity','id_payment_periodicity',$explodedData,array('payment_periodicity_name')), 'payment_periodicity_name');
                        $filters[$k]['value_names'] =$payment_periodicity_names;
                        break;  
                    case 'status': 
                    case 'project_status':
                        $project_status = array_map(function($i){
                            if(is_string($i))
                            {
                                if($i === '0'){return "Closed";}
                                elseif($i === '1'){return "Active";}
                                else{return $i;}
                            }
                            else
                            {
                                if($i == 0){return "Closed";}
                                elseif($i == 1){return "Active";}
                                else{return $i;}
                            }
                        }, $explodedData);
                        $filters[$k]['value_names'] =$project_status;
                        break;  
                    case 'country_id':    
                    case 'country':    
                        $country_names=array_column($this->User_model->check_record_whereIn('country','id_country',$explodedData,array('country_name')), 'country_name');
                        $filters[$k]['value_names'] =$country_names;
                        break;  
                    case 'responsible_user_id':
                        $query = 'select u.id_user,u.user_role_id,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name from business_unit_user buu LEFT JOIN user u on u.id_user = buu.user_id LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role LEFT JOIN business_unit bu ON bu.id_business_unit=buu.business_unit_id WHERE u.id_user IN ('.$filters[$k]['value'].') GROUP BY id_user';
                        $users = $this->User_model->custom_query($query);
                        $User_names=array_column($users, 'name');
                        $filters[$k]['value_names'] =$User_names;
                        break;  
                    case 'activity_status':
                        $activity_status = array_map(function($i){
                            if($i == 'new'){return "New";}
                            elseif($i == 'pending review'){return "Reviews to Initiate";}
                            elseif($i == 'review in progress'){return "Reviews in Progress";}
                            elseif($i == 'review finalized'){return "Reviews Finalized";}
                            elseif($i == 'pending workflow'){return "Tasks to Initiate";}
                            elseif($i == 'workflow in progress'){return "Tasks in Progress";}
                            elseif($i == 'workflow finalized'){return "Tasks Finalized";}
                            else{return $i;}
                        }, $explodedData);
                        $filters[$k]['value_names'] =$activity_status;
                        break;
                    case 'allocation':
                        $allocation = array_map(function($i){
                            if($i == 'assigned_to_me'){return "Assigned to me";}
                            elseif($i == 'created_by_me'){return "Created by me";}
                        }, $explodedData);
                        $filters[$k]['value_names'] =$allocation;
                        break;    
                    default:
                        $filters[$k]['value_names'] =$explodedData;  
                }
                if($filters[$k]['domain'] == 'Relation Tags' || $filters[$k]['domain'] == 'Contract Tags' || $filters[$k]['domain'] == 'Catalogue Tags')
                {
                   
                    $getTagDetails = $this->User_model->check_record('tag',array('id_tag'=>pk_decrypt($filters[$k]['master_domain_field_id'])));
                    $filters[$k]['selected_field'] = $getTagDetails[0]['selected_field']; // field for getting  contract,project,relation,catalogue list
                    if(!empty($getTagDetails))
                    {
                        if($getTagDetails[0]['tag_type'] == 'selected')
                        {
                            if($getTagDetails[0]['selected_field'] == 'contract' || $getTagDetails[0]['selected_field'] == 'project')
                            {
                                $table = 'contract';
                                $wherecoloum = 'id_contract';
                                $field_name = 'contract_name';
                            }
                            elseif($getTagDetails[0]['selected_field'] == 'relation')
                            {
                                $table = 'provider';
                                $wherecoloum = 'id_provider';
                                $field_name = 'provider_name';
                            }
                            else{
                                $table = 'catalogue';
                                $wherecoloum = 'id_catalogue';
                                $field_name = 'catalogue_name';
                            }
                        }
                        else
                        {
                            $table = 'tag_option_language';
                            $wherecoloum = 'tag_option_id';
                            $field_name = 'tag_option_name';
                           
                        }

                        $tag_option_name=array_column($this->User_model->check_record_whereIn($table,$wherecoloum,$explodedData,array($field_name)), $field_name);
                        //echo $this->db->last_query();

                        $filters[$k]['value_names'] =$tag_option_name;
                    }
                    else
                    {
                        $filters[$k]['value_names'] = '';
                    }
                    
                }
                // if($filters[$k]['domain']=='Relation Tags'){
                //     $filters[$k]['value_names'] = array_map(function($i){
                //             if($i == 'R'){return "Red";}
                //             elseif($i == 'A'){return "Amber";}
                //             elseif($i == 'G'){return "Green";}
                //             elseif($i == 'N/A'){return "N/A";}
                //             else{return $i;}
                //         }, $filters[$k]['value_names']);
                // }
                if(!in_array($filters[$k]['database_field'],$donotDecrypt))
                {
                    $filters[$k]['value'] = array_map(function($i){ return pk_encrypt($i); },$explodedData);
                }
                else{
                    
                    $filters[$k]['value'] = $explodedData;
                }
            }
            else
            {
                $filters[$k]['value_names'] = array($filters[$k]['value']);
            }
            if($filters[$k]['field_type'] == 'drop_down' && count($explodedData) >1)
            {
                $filters[$k]['filter_display_value']="Multiple Selected";
            }
            else
            {
                $filters[$k]['filter_display_value']=$filters[$k]['value_names'][0];
            }
            $filters[$k]['filter_condition_name']='';
            switch($filters[$k]['condition']){
                case '=':
                    $filters[$k]['filter_condition_name'] = "Equals";
                    break;
                case '<':
                    $filters[$k]['filter_condition_name'] = "Smaller than";
                    break;
                case '>':
                    $filters[$k]['filter_condition_name'] = "Greater than";
                    break;
                case 'like':
                    $filters[$k]['filter_condition_name'] = "Contains";
                    break;     
                default:
                    $filters[$k]['filter_condition_name'] = $filters[$k]['condition'];
            }
            $filters[$k]['value_names_string']=implode('</br>',$filters[$k]['value_names']);

        }
        $result = array('status'=>true, 'data' => $filters,'message' => $this->lang->line('success'));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function filter_delete(){
        $data = $this->input->get();
        $msg='';
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_master_filter', array('required'=>$this->lang->line('id_master_filter_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_master_filter'])) {
            $data['id_master_filter'] = pk_decrypt($data['id_master_filter']);
        }
        if(isset($data['id_master_filter'])){
           
            if($this->User_model->delete('user_advanced_filters',array('id_master_filter'=>$data['id_master_filter'])))
            {
                $this->response(array('status'=>TRUE,'message'=>$this->lang->line('filter_deleted_successfully'),'data'=>''), REST_Controller::HTTP_OK);
            }  
            else
            {
                $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('operation_failed')),'data'=>'1'), REST_Controller::HTTP_OK);
            }    
        }
    }
    public function mapSubtaskToContract_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_contract_workflow', array('required'=>$this->lang->line('contract_workflow_id_req')));
        $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_contract_workflow'])) {
            $data['id_contract_workflow'] = pk_decrypt($data['id_contract_workflow']);
        }
        if(isset($data['id_contract'])) {
            $data['id_contract'] = pk_decrypt($data['id_contract']);
        }
        $check_existance=$this->User_model->check_record('subtask_mapped_contracts',array('contract_workflow_id'=>$data['id_contract_workflow'],'contract_id'=>$data['id_contract']));
        if(!empty($check_existance)){
            $this->response(array('status'=>FALSE,'error'=>array('message'=>$this->lang->line('subtask_already_mapped_to_contract')),'data'=>''), REST_Controller::HTTP_OK);
        }
        else{
            $this->User_model->insert_data('subtask_mapped_contracts',array('contract_workflow_id'=>$data['id_contract_workflow'],'contract_id'=>$data['id_contract'],'created_date_time'=>currentDate(),'created_by'=>$_SERVER['HTTP_LOGGEDIN_USER']));
            $this->response(array('status'=>TRUE,'message'=>$this->lang->line('sub_task_mapped'),'data'=>''), REST_Controller::HTTP_OK);
        }
    }

    //for language

    public function language_get()
    {
        $data = $this->input->get();
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        }
        
        if((isset($data['secondary_language']) && $data['secondary_language'] == true)) {
            $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        }
       
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if((isset($data['user_languages']) && $data['user_languages'] == true))
        {
            if(!isset($data['customer_id'])){
                 $data['id_user'] = pk_decrypt($data['id_user']);
                $userDetails = $this->User_model->check_record('user', array('id_user'=>$data['id_user']));
                if(!empty($userDetails))
                {
                    $data['customer_id'] = $userDetails[0]['customer_id'];
                }
            }
        }

        if(isset($data['secondary_language']) && $data['secondary_language'] == true && isset($data['customer_id'])) {
            $customerPrimaryLanguage = $this->User_model->check_record('customer_languages',array('customer_id'=>$data['customer_id'],'is_primary'=>1,'status' => 1));
            if(!empty($customerPrimaryLanguage))
            {
                $data['language_not_in'] = array($customerPrimaryLanguage[0]['language_id']);
            }
            else
            {
                $data['language_not_in'] = array();
            }
        }
        if(isset($data['user_languages']) && $data['user_languages'] == true)
        {
            $languages = $this->Master_model->getUserLanguages($data);
        }
        else
        {
            $languages = $this->Master_model->getLanguages($data);
        }
        
        foreach($languages as $k=>$v)
        {
            $languages[$k]['id_language'] = pk_encrypt($languages[$k]['id_language']);
        }
      
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$languages), REST_Controller::HTTP_OK);

    }

    public function list_get()
    {
        $data = $this->input->get();

        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('type', array('required'=>$this->lang->line('type_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
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



        if($data['type'] == 'contract' || $data['type'] == 'project')
        {
            $coloumName = ($data['type'] == 'contract')? 'id_contract':'id_project';
            $conditions = array(
                'type' => $data['type'],
                'customer_id' => $data['customer_id'],
            );
            if(isset($data['can_access']) && $data['can_access'] == 1 ){
                $conditions['can_access'] = 1;
            }
            $list = $this->Contract_model->listing($conditions);
            foreach($list as $k=>$v)
            {
                $list[$k][$coloumName] = pk_encrypt($list[$k]['id_contract']);
                if($data['type'] == 'project'){unset($list[$k]['id_contract']);}
            }
        }
        elseif($data['type'] == 'provider')
        {
            $conditions = array(
                'customer_id' => $data['customer_id'],
            );
            $list = $this->Customer_model->providerList($conditions);
            foreach($list as $k=>$v)
            {
                $list[$k]['id_provider'] = pk_encrypt($list[$k]['id_provider']);
                
            }
        }
        elseif($data['type'] == 'catalogue')
        {
            $conditions = array(
                'customer_id' => $data['customer_id'],
            );
            $list = $this->Catalogue_model->simpleCatalogueList($conditions);
            foreach($list as $k=>$v)
            {
                $list[$k]['id_catalogue'] = pk_encrypt($list[$k]['id_catalogue']);
            }
        }

        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$list), REST_Controller::HTTP_OK);
    }
    public function getSpendData_get(){
        $data= $this->input->get();
        $this->form_validator->add_rules('id_contract', array('required'=>$this->lang->line('contract_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['id_contract'])){
            $data['id_contract']=pk_decrypt($data['id_contract']);
        }
        $spent_info = $this->User_model->check_record_selected('contract_value,contract_value_period,additional_recurring_fees,additional_recurring_fees_period,additonal_one_off_fees,po_number,contract_value_description,additional_recurring_value_description,additonal_one_off_value_description,contract_budget_data,currency_id','contract',array('id_contract'=>$data['id_contract']));
        if(!empty($spent_info[0]['contract_budget_data'])){
            $spent_info[0]['contract_budget_data']=json_decode($spent_info[0]['contract_budget_data']);
        }
        $currencyDetails = $this->User_model->check_record("currency" , array("id_currency" => $spent_info[0]['currency_id']));
        $spent_info[0]['currency_name'] = $currencyDetails[0]['currency_name'];
        foreach($spent_info[0]['contract_budget_data'] as $k=>$v){
            if(!empty($v->amount)){
                $spent_info[0]['contract_budget_data'][$k]->amount=(int)$v->amount;                
            }
        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$spent_info), REST_Controller::HTTP_OK);
    }

}