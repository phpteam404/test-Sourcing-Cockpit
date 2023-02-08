<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Module extends REST_Controller
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
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
        if($this->session_user_info->user_role_id<3 || $this->session_user_info->user_role_id==6 || $this->session_user_info->user_role_id==5)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
        else if($this->session_user_info->user_role_id>=3)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units));
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        $this->session_user_contract_reviews=$this->Validation_model->getContractReviews(array('contract_id'=>$this->session_user_contracts));
        $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_master_language=$this->Validation_model->getLanguage();
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_contract_review_modules=$this->Validation_model->getContractReviewModules(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_master_contract_review_modules=$this->Validation_model->getMasterContractReviewModules();
    }

    public function list_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data['contract_review_id']= 0;
        $data = tableOptions($data);
        //if(isset($data['language_id'])) $data['language_id']=pk_decrypt($data['language_id']);
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = $data['contract_review_id'];
            /*if($this->session_user_info->user_role_id!=1 && !in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }else if(isset($data['customer_id'])){
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']==0)
                unset($data['customer_id']);
        }
        //echo '<pre>';print_r($data);exit;
        /*if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
            if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                ;$result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }*/
        $result = $this->Module_model->moduleList($data);
        //echo '<pre>'.$this->db->last_query();exit;
        foreach($result['data'] as $km=>$vm){
            $requestarray = array('module_id' => $vm['id_module'] );
            if(isset($data['is_workflow']) && $data['is_workflow'] == true){
                $requestarray['is_workflow'] = 1;
            }
            $RealtionQuestions = $this->Topic_model->getRelationquestions($requestarray);
            $result['data'][$km]['relation_question_count'] = (count($RealtionQuestions) > 0) ? count($RealtionQuestions) : '---' ;
            $result['data'][$km]['contract_review_id']=pk_encrypt($vm['contract_review_id']);
            $result['data'][$km]['created_by']=pk_encrypt($vm['created_by']);
            $result['data'][$km]['id_module']=pk_encrypt($vm['id_module']);
            $result['data'][$km]['id_module_language']=pk_encrypt($vm['id_module_language']);
            $result['data'][$km]['language_id']=pk_encrypt($vm['language_id']);
            $result['data'][$km]['module_id']=pk_encrypt($vm['module_id']);
            $result['data'][$km]['to_avail_template']=pk_encrypt($vm['to_avail_template']);
            $result['data'][$km]['parent_module_id']=pk_encrypt($vm['parent_module_id']);
            $result['data'][$km]['updated_by']=pk_encrypt($vm['updated_by']);
            $result['data'][$km]['workflow_template_id']=pk_encrypt($vm['workflow_template_id']);
            $result['data'][$km]['template_id']=($vm['to_avail_template']!=0)?pk_encrypt($vm['to_avail_template']):'';
            if(isset($vm['import_status']))
                $result['data'][$km]['import_status']=(int)$vm['import_status'];
        }
        //echo $this->db->last_query(); exit;
        $import_subscription = $this->User_model->check_record('customer',array('id_customer'=>$this->session_user_info->customer_id));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records'],'import_subscription'=>(int)$import_subscription[0]['import_subscription']));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function name_get()
    {
        $data = $this->input->get();
        //if(isset($data['language_id'])) $data['language_id']=pk_decrypt($data['language_id']);
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['module_id'])) $data['module_id']=pk_decrypt($data['module_id']);
        if(isset($data['module_id'])) {
            $data['module_id'] = pk_decrypt($data['module_id']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['module_id'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Module_model->getModuleName($data);
        foreach($result as $km=>$vm){
            $result[$km]['id_module']=pk_encrypt($vm['id_module']);
            $result[$km]['id_module_language']=pk_encrypt($vm['id_module_language']);
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

        $this->form_validator->add_rules('module_name', array('required'=>$this->lang->line('module_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            if(!(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && (((isset($data['is_workflow']) && $data['is_workflow']==TRUE) && ($this->session_user_info->content_administator_task_templates == 1)) || (!isset($data['is_workflow']) && $this->session_user_info->content_administator_review_templates == 1))))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = (int)pk_decrypt($data['template_id']);
        }
        $data['customer_id'] = null;
        if($this->session_user_info->user_role_id == 2){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }elseif( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ) ){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }

        $module_id = $this->Module_model->addModule(array(
            'module_order' => isset($data['module_order'])?$data['module_order']:'1',
            'type' => isset($data['type'])?$data['type']:'dynamic',
            'created_by' => $data['created_by'],
            'created_on' => currentDate(),
            'customer_id' => $data['customer_id'],
            'to_avail_template' => isset($data['template_id'])?$data['template_id']:0,//when we add workflow at that time we will not get template_id 
            'static' => isset($data['is_workflow'])?1:0,
            'is_workflow' =>isset($data['is_workflow'])?$data['is_workflow']:0,
        ));
        // echo $this->db->last_query();exit;
        if($module_id){
            $this->Module_model->addModuleLanguage(array(
                'module_id' => $module_id,
                'module_name' => $data['module_name'],
                'language_id' => 1
            ));

            //when is_workflow avaialbe then template should add dynamically
            if(isset($data['is_workflow']) && $data['is_workflow']==TRUE)
            {
                // we are creating workflow template with module id
                // we need to know template id from template table(latest inserted id)
                $template_id = $this->Template_model->addTemplate(array(
                    'template_name' => $data['module_name'],
                    'template_status' => 1,
                    'parent_template_id'=>$data['parent_template_id'],
                    'is_workflow' =>1,
                    'import_status'=>isset($data['import_status']),
                    'created_on' => currentDate()
                ));
                // we need to add templateModule id from templateModule table
                $this->Template_model->addTemplateModule(array(
                    'template_id' => $template_id,
                    'module_id' => $module_id,
                    'module_order' => 0,
                    'status' => 1
                ));
                $updateModule=$this->Module_model->updateModule(array('id_module'=>$module_id,'to_avail_template'=>$template_id));
            }//workflow if condition end
            $result = array('status'=>TRUE, 'message' => $this->lang->line('module_add'), 'data'=>'3');
            $this->response($result, REST_Controller::HTTP_OK);
        }//module if condition has end
        else {
            $result = array('status'=>FALSE, 'message' => $this->lang->line('module_not_added'), 'data'=>'4');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $data = $this->input->post();
        //print_r($data); exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_module', array('required'=>$this->lang->line('module_id_req')));
        $this->form_validator->add_rules('id_module_language', array('required'=>$this->lang->line('module_language_id_req')));
        $this->form_validator->add_rules('module_name', array('required'=>$this->lang->line('module_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('module_status', array('required'=>$this->lang->line('module_status_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            if(!(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && (((isset($data['is_workflow']) && $data['is_workflow']==TRUE) && ($this->session_user_info->content_administator_task_templates == 1)) || ((isset($data['is_workflow']) && $data['is_workflow'] =='0') && $this->session_user_info->content_administator_review_templates == 1))))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['id_module'])) $data['id_module']=pk_decrypt($data['id_module']);
        if(isset($data['id_module'])) {
            $data['id_module'] = pk_decrypt($data['id_module']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_module'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        //if(isset($data['id_module_language'])) $data['id_module_language']=pk_decrypt($data['id_module_language']);
        if(isset($data['id_module_language'])) {
            $data['id_module_language'] = pk_decrypt($data['id_module_language']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_module_language'],$this->session_user_master_contract_review_modules['module_lang'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id>2 && $this->session_user_info->customer_id!=$data['customer_id']){
                if(!(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && (((isset($data['is_workflow']) && $data['is_workflow']==TRUE) && ($this->session_user_info->content_administator_task_templates == 1)) || ((isset($data['is_workflow']) && $data['is_workflow'] =='0') && $this->session_user_info->content_administator_review_templates == 1))))
                {
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                // $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                // $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            /*if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['template_id']) && $data['template_id']!="") {
            $data['template_id'] = pk_decrypt($data['template_id']);
        }
        if(isset($data['to_avail_template']))
        {
            $data['to_avail_template']=pk_decrypt($data['to_avail_template']);
        }

        $this->Module_model->updateModule(array(
            'id_module' => $data['id_module'],
            'customer_id' => isset($data['customer_id'])?$data['customer_id']:'0',
            'module_order' => isset($data['module_order'])?$data['module_order']:'1',
            'updated_by' => $data['created_by'],
            'module_status' => $data['module_status'],
            'type' => (int)$data['static']==1?'static':'dynamic',
            'updated_on' => currentDate(),
            'static' => $data['static'],
            'to_avail_template'=>($data['template_id'])?$data['template_id']:'0'
        ));

        $this->Module_model->updateModuleLanguage(array(
            'id_module_language' => $data['id_module_language'],
            'module_name' => $data['module_name']
        ));
        //$updatestatus=$this->User_model->check_record('template',array('id_template'=>$data['template_id']));
        //echo ''.$this->db->last_query(); exit;
       //print_r($updatestatus); exit;
        if(isset($data['is_workflow']) && $data['is_workflow']==TRUE)
        {
            $updateImportStatus=$this->User_model->update_data('template',array('import_status'=>$data['import_status'],'template_name'=>$data['module_name']),array('id_template'=>$data['to_avail_template']));
            //echo ''.$this->db->last_query(); exit;
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('module_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function delete_delete()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_module', array('required'=>$this->lang->line('module_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id==1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //if(isset($data['id_module'])) $data['id_module']=pk_decrypt($data['id_module']);
        if(isset($data['id_module'])) {
            $data['id_module'] = pk_decrypt($data['id_module']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_module'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Module_model->updateModule(array(
            'id_module' => $data['id_module'],
            'module_status' => 0
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('module_inactive'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
}