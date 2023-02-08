<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Template extends REST_Controller
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
    public $session_user_master_contract_review_modules=NULL;
    public $session_user_contract_review_topics=NULL;
    public $session_user_master_contract_review_topics=NULL;
    public $session_user_contract_review_questions=NULL;
    public $session_user_contract_review_question_options=NULL;
    public $session_user_wadmin_relationship_categories=NULL;
    public $session_user_wadmin_relationship_classifications=NULL;
    public $session_user_master_contract_review_questions=NULL;
    public $session_user_master_contract_review_question_options=NULL;
    public $session_user_master_template_modules=NULL;
    public $session_user_master_template_module_topics=NULL;
    public $session_user_master_template_module_topic_questions=NULL;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
        if(!in_array($this->session_user_info->user_role_id,array(1,2,3,4,8))){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->session_user_master_templates=$this->Validation_model->getTemplates();
        $this->session_user_master_contract_review_modules=$this->Validation_model->getMasterContractReviewModules();
        $this->session_user_master_contract_review_topics=$this->Validation_model->getMasterContractReviewTopics();
        $this->session_user_master_contract_review_questions=$this->Validation_model->getContractReviewMasterQuestions();
        $this->session_user_master_template_modules=$this->Validation_model->getTemplateModules();
        $this->session_user_master_template_module_topics=$this->Validation_model->getTemplateModuleTopics();
        $this->session_user_master_template_module_topic_questions=$this->Validation_model->getTemplateModuleTopicQuestions();

    }

    public function list_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        // echo '<pre>'.print_r($data);exit;
        $data['topic_status'] = 1; $customer_id = array();
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 3 ||$this->session_user_info->user_role_id == 4 ||$this->session_user_info->user_role_id == 8){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }else if(isset($data['customer_id'])){
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']==0)
                unset($data['customer_id']);
        }
        $result = $this->Template_model->TemplateList($data);
        // echo '<pre>'.$this->db->last_query();exit;
        for($s=0;$s<count($result['data']);$s++)
        {
            if($result['data'][$s]['customer_id']!='') {
                $customer_id = explode(',', $result['data'][$s]['customer_id']);

                for ($sr = 0; $sr < count($customer_id); $sr++) {
                    $new = explode('@', $customer_id[$sr]);
                    $new[0]=pk_encrypt($new[0]);
                    $result['data'][$s]['customer'][$sr]['customer_id'] = $new[0];
                    $result['data'][$s]['customer'][$sr]['assigned_to'] = $new[1];
                    $customer_id[$sr]=implode('@',$new);
                }
                $result['data'][$s]['customer_id']=implode(',',$customer_id);
            }
            $result['data'][$s]['id_template']=pk_encrypt($result['data'][$s]['id_template']);
        }
        $import_subscription = (int)$this->User_model->check_record_selected('import_subscription','customer',array('id_customer'=>$this->session_user_info->customer_id))[0]['import_subscription'];
        //echo $this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records'],'import_subscription'=>$import_subscription));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function details_get()
    {
        $data = $this->input->get();
        $data['template_status'] = 1; //getting only active templates
        $data['is_workflow'] = 0; //getting only review templates
        $result = $this->Template_model->getTemplates($data);
        foreach($result as $k=>$v){
            $result[$k]['id_template']=pk_encrypt($v['id_template']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_get()
    {
        $data = $this->input->get();
        if(isset($data['id_template'])) {
            $data['id_template'] = pk_decrypt($data['id_template']);
            if(!in_array($data['id_template'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }

        }
        if(isset($data['id_template_not'])) {
            $data['id_template_not'] = pk_decrypt($data['id_template_not']);
            if(!in_array($data['id_template_not'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Template_model->getTemplate($data);
        foreach($result as $k=>$v){
            $result[$k]['id_template']=pk_encrypt($v['id_template']);
        }
        /*if only one record then */
        if(isset($result[0])){ $result = $result[0]; }
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


        $this->form_validator->add_rules('template_name', array('required'=>$this->lang->line('template_name_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['clone_template_id'])) {
            $data['clone_template_id'] = pk_decrypt($data['clone_template_id']);
            if(!in_array($data['clone_template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['clone_template_id'])){
            
            $this->Template_model->cloneTemplate($data);

            $result = array('status'=>TRUE, 'message' => $this->lang->line('template_clone'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK); exit;
        }
        if($this->session_user_info->user_role_id == 2){
            $data['parent_template_id']=0;
        }else{
            $data['parent_template_id']=NULL;
        }
        /*checking for template name already exists or not*/
        $check_name = $this->Template_model->getTemplate(array('template_name' => trim($data['template_name']),'template_status' => 1,'customer_id'=>$this->session_user_info->customer_id));
        if(!empty($check_name)){
            $result = array('status'=>FALSE, 'error' =>$this->lang->line('template_name_duplicate'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*adding record*/
        $template_id = $this->Template_model->addTemplate(array(
                            'template_name' => $data['template_name'],
                            'template_status' => 1,
                            'parent_template_id'=>$data['parent_template_id'],
                            'created_on' => currentDate()
                        ));
        //if($this->session_user_info->user_role_id == 2) 
        $insert_data=$this->User_model->insert_data('customer_template',array('template_id'=>$template_id,'customer_id'=>$this->session_user_info->customer_id,'created_by'=> $this->session_user_id,'created_on'=>currentDate(),'status'=>'1'));
            //echo '',$this->db->last_query(); exit;

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_template', array('required'=>$this->lang->line('template_id_req')));
        $this->form_validator->add_rules('template_name', array('required'=>$this->lang->line('template_name_req')));
        $this->form_validator->add_rules('template_status', array('required'=>$this->lang->line('status_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_template'])) {
            $data['id_template'] = pk_decrypt($data['id_template']);
            if(!in_array($data['id_template'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }

        }
        /*checking for template name already exists or not*/
        $check_name = $this->Template_model->getTemplate(array('id_template_not' => $data['id_template'],'template_name' => trim($data['template_name']),'template_status' => 1,'customer_id'=>$this->session_user_info->customer_id));
        //echo '<pre>'.$this->db->last_query();exit;
        if(!empty($check_name)){
            $result = array('status'=>FALSE, 'message' => $this->lang->line('template_name_duplicate'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*updating record*/
        $this->Template_model->updateTemplate(array(
            'id_template' => $data['id_template'],
            'template_name' => $data['template_name'],
            'template_status' => $data['template_status'],
            'import_status'=>$data['import_status'],
            'updated_on' => currentDate()
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function module_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $this->form_validator->add_rules('module_id', array('required'=>$this->lang->line('module_selection_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data['module_id']);$s++)
        {
            $module_id=pk_decrypt($data['module_id'][$s]);
            if(!in_array($module_id,$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $template_modules = $this->Template_model->getTemplateModules(array('template_id' => $data['template_id']));
        $template_modules_id = array_map(function($i){ return $i['module_id']; },$template_modules);
        for($s=0;$s<count($data['module_id']);$s++)
        {
            $data['module_id'][$s]=pk_decrypt($data['module_id'][$s]);
            if(!in_array($data['module_id'][$s],$template_modules_id)){
                $order = $this->User_model->check_record('template_module',array('template_id' => $data['template_id']));
                $this->Template_model->addTemplateModule(array(
                    'template_id' => $data['template_id'],
                    'module_id' => $data['module_id'][$s],
                    'module_order' => count($order),
                    'status' => 1
                ));
            }
            else{
                $this->Template_model->updateTemplateModule(array(
                    'template_id' => $data['template_id'],
                    'module_id' => $data['module_id'][$s],
                    'status' => 1
                ));
            }
        }

        /*for($s=0;$s<count($template_modules);$s++)
        {
            if(!in_array($template_modules[$s]['module_id'],$data['module_id'])){
                $this->Template_model->updateTemplateModule(array(
                    'id_template_module' => $template_modules[$s]['id_template_module'],
                    'status' => 0
                ));
            }
        }*/

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_save'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function module_delete()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_template_module', array('required'=>$this->lang->line('template_module_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_template_module'])) {
            $data['id_template_module'] = pk_decrypt($data['id_template_module']);
            if(!in_array($data['id_template_module'],$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Template_model->updateTemplateModule(array(
            'id_template_module' => $data['id_template_module'],
            'status' => 0
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function moduleList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data);
        $data['status'] = 1;
        $result = $this->Template_model->getTemplateModuleList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_module_language']=pk_encrypt($v['id_module_language']);
            $result['data'][$k]['id_template_module']=pk_encrypt($v['id_template_module']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
            $result['data'][$k]['module_id']=pk_encrypt($v['module_id']);
            $result['data'][$k]['template_id']=pk_encrypt($v['template_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function topic_delete()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_template_module_topic', array('required'=>$this->lang->line('template_module_topic_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_template_module_topic'])) {
            $data['id_template_module_topic'] = pk_decrypt($data['id_template_module_topic']);
            if(!in_array($data['id_template_module_topic'],$this->session_user_master_template_module_topics)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Template_model->updateTemplateModuleTopic(array(
            'id_template_module_topic' => $data['id_template_module_topic'],
            'status' => 0
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_topic_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function Question_delete()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_template_module_topic_question', array('required'=>$this->lang->line('template_module_topic_question_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_template_module_topic_question'])) {
            $data['id_template_module_topic_question'] = pk_decrypt($data['id_template_module_topic_question']);
            if(!in_array($data['id_template_module_topic_question'],$this->session_user_master_template_module_topic_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Template_model->updateTemplateModuleTopicQuestion(array(
            'id_template_module_topic_question' => $data['id_template_module_topic_question'],
            'status' => 0
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_topic_question_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function allModules_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data['customer_id'] = null;
        if($this->session_user_info->user_role_id == 2){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        elseif(( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ) ))
        {
            $data['customer_id'] = $this->session_user_info->customer_id; 
        }

        $result = $this->Template_model->getModules(array('template_id_not' => $data['template_id'],'status' => 1,'contract_review_id' => 0,'customer_id'=>$data['customer_id']));
        foreach($result as $k=>$v){
            $result[$k]['id_module']=pk_encrypt($v['id_module']);
        }
        //echo $this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function allTopics_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $this->form_validator->add_rules('template_module_id', array('required'=>$this->lang->line('template_module_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_module_id'])) {
            $data['template_module_id'] = pk_decrypt($data['template_module_id']);
            if(!in_array($data['template_module_id'],$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $template_module = $this->Template_model->getTemplateModules(array('id_template_module' => $data['template_module_id']));
        $module_id = 0;
        if(!empty($template_module)){ $module_id = $template_module[0]['module_id']; }

        $result = $this->Template_model->getTopics(array('template_id_not' => $data['template_id'],'module_id' => $module_id,'status' => 1));
        foreach($result as $k=>$v){
            $result[$k]['id_topic']=pk_encrypt($v['id_topic']);
        }
        //echo $this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function allQuestions_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $this->form_validator->add_rules('template_module_topic_id', array('required'=>$this->lang->line('template_module_topic_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_module_topic_id'])) {
            $data['template_module_topic_id'] = pk_decrypt($data['template_module_topic_id']);
            if(!in_array($data['template_module_topic_id'],$this->session_user_master_template_module_topics)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $template_module_topic = $this->Template_model->getTemplateModuleTopics(array('id_template_module_topic' => $data['template_module_topic_id']));
        $topic_id = 0;
        if(!empty($template_module_topic)){ $topic_id = $template_module_topic[0]['topic_id']; }
        $requestArray = array('topic_id' => $topic_id,'template_id_not' => $data['template_id'],'status' => 1,'with_relation'=>1);
        if(isset($data['template_id']))
        {
            $templateDeatils = $this->User_model->check_record('template',array('id_template' => $data['template_id']));
            $requestArray['is_workflow'] = $templateDeatils[0]['is_workflow'];
        }
        $result = $this->Template_model->getQuestions($requestArray);
        foreach($result as $k=>$v){
            $result[$k]['id_question']=pk_encrypt($v['id_question']);
        }
        //echo $this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function module_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Template_model->getTemplateModules(array('template_id' => $data['template_id'],'status' => 1));
        foreach($result as $k=>$v){
            $result[$k]['id_template_module']=pk_encrypt($v['id_template_module']);
            $result[$k]['template_id']=pk_encrypt($v['template_id']);
            $result[$k]['module_id']=pk_encrypt($v['module_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function topic_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_module_id', array('required'=>$this->lang->line('template_module_id_req')));
        $this->form_validator->add_rules('topic_id', array('required'=>$this->lang->line('topic_id_selection_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_module_id'])) {
            $data['template_module_id'] = pk_decrypt($data['template_module_id']);
            if(!in_array($data['template_module_id'],$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data['topic_id']);$s++)
        {
            $topic_id=pk_decrypt($data['topic_id'][$s]);
            /*if(!in_array($topic_id,$this->session_user_master_contract_review_topics['topic'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $template_modules = $this->Template_model->getTemplateModuleTopics(array('template_module_id' => $data['template_module_id']));
        $template_modules_id = array_map(function($i){ return $i['topic_id']; },$template_modules);
        for($s=0;$s<count($data['topic_id']);$s++)
        {
            $data['topic_id'][$s]=pk_decrypt($data['topic_id'][$s]);
            if(!in_array($data['topic_id'][$s],$template_modules_id)){
                $order = $this->User_model->check_record('template_module_topic',array('template_module_id' => $data['template_module_id']));
				//echo $this->db->last_query();exit;
                $this->Template_model->addTemplateModuleTopic(array(
                    'template_module_id' => $data['template_module_id'],
                    'topic_id' => $data['topic_id'][$s],
                    'topic_order' => count($order),
                    'status' => 1
                ));
            }
            else{
                $this->Template_model->updateTemplateModuleTopic(array(
                    'template_module_id' => $data['template_module_id'],
                    'topic_id' => $data['topic_id'][$s],
                    'status' => 1
                ));
            }
        }
        $result = $this->Template_model->getTemplateModuleTopicList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_template_module_topic']=pk_encrypt($v['id_template_module_topic']);
            $result['data'][$k]['template_module_id']=pk_encrypt($v['template_module_id']);
            $result['data'][$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result['data'][$k]['id_topic_language']=pk_encrypt($v['id_topic_language']);
            $result['data'][$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_topic_save'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function topicList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_module_id', array('required'=>$this->lang->line('template_module_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_module_id']) && $data['template_module_id']!='all') {
            $data['template_module_id'] = pk_decrypt($data['template_module_id']);
            /*if(!in_array($data['template_module_id'],$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        /*$data = tableOptions($data);*/
        $data['status'] = 1;
        $result = $this->Template_model->getTemplateModuleTopicList($data);//echo $this->db->last_query();exit;
        //echo $this->db->last_query(); exit;
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_template_module_topic']=pk_encrypt($v['id_template_module_topic']);
            $result['data'][$k]['template_module_id']=pk_encrypt($v['template_module_id']);
            $result['data'][$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result['data'][$k]['id_topic_language']=pk_encrypt($v['id_topic_language']);
            $result['data'][$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function topic_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Template_model->getTemplateModuleTopics(array('template_id' => $data['template_id'],'status' => 1));
        foreach ($result as $k=>$v) {
            $result[$k]['id_template_module_topic']=pk_encrypt($v['id_template_module_topic']);
            $result[$k]['template_module_id']=pk_encrypt($v['template_module_id']);
            $result[$k]['topic_id']=pk_encrypt($v['topic_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function questionList_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_module_topic_id', array('required'=>$this->lang->line('template_module_topic_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_module_topic_id']) && $data['template_module_topic_id']!='all') {
            $data['template_module_topic_id'] = pk_decrypt($data['template_module_topic_id']);
            if(!in_array($data['template_module_topic_id'],$this->session_user_master_template_module_topics)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*$data = tableOptions($data);*/
        $data['status'] = 1;
        $result = $this->Template_model->getTemplateModuleTopicQuestionList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_template_module_topic_question']=pk_encrypt($v['id_template_module_topic_question']);
            $result['data'][$k]['template_module_topic_id']=pk_encrypt($v['template_module_topic_id']);
            $result['data'][$k]['question_id']=pk_encrypt($v['question_id']);
            $result['data'][$k]['id_question_language']=pk_encrypt($v['id_question_language']);
            $result['data'][$k]['question_id']=pk_encrypt($v['question_id']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function question_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('template_module_topic_id', array('required'=>$this->lang->line('template_module_topic_id_req')));
        $this->form_validator->add_rules('question_id', array('required'=>$this->lang->line('question_id_select_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_module_topic_id'])) {
            $data['template_module_topic_id'] = pk_decrypt($data['template_module_topic_id']);
            if(!in_array($data['template_module_topic_id'],$this->session_user_master_template_module_topics)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data['question_id']);$s++) {
            $question_id = pk_decrypt($data['question_id'][$s]);
            /*if(!in_array($question_id,$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $template_modules = $this->Template_model->getTemplateModuleTopicQuestions(array('template_module_topic_id' => $data['template_module_topic_id']));
        $template_modules_id = array_map(function($i){ return $i['question_id']; },$template_modules);
        for($s=0;$s<count($data['question_id']);$s++)
        {
            $data['question_id'][$s]=pk_decrypt($data['question_id'][$s]);
            if(!in_array($data['question_id'][$s],$template_modules_id)){
                $order = $this->User_model->check_record('template_module_topic_question',array('template_module_topic_id' => $data['template_module_topic_id']));
                $this->Template_model->addTemplateModuleTopicQuestion(array(
                    'template_module_topic_id' => $data['template_module_topic_id'],
                    'question_id' => $data['question_id'][$s],
                    'question_order' => count($order),
                    'status' => 1
                ));
            }
            else{
                $this->Template_model->updateTemplateModuleTopicQuestion(array(
                    'template_module_topic_id' => $data['template_module_topic_id'],
                    'question_id' => $data['question_id'][$s],
                    'status' => 1
                ));
            }
        }

        $result = $this->Template_model->getTemplateModuleTopicQuestionList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_template_module_topic_question']=pk_encrypt($v['id_template_module_topic_question']);
            $result['data'][$k]['template_module_topic_id']=pk_encrypt($v['template_module_topic_id']);
            $result['data'][$k]['question_id']=pk_encrypt($v['question_id']);
            $result['data'][$k]['id_question_language']=pk_encrypt($v['id_question_language']);
            $result['data'][$k]['question_id']=pk_encrypt($v['question_id']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('template_module_topic_question_save'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function moduleOrder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['data'])){ $data = $data['data']; }
        $update_array = array();
        for($s=0;$s<count($data);$s++) {
            $id_template_module=pk_decrypt($data[$s]['id_template_module']);
            if(!in_array($id_template_module,$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data);$s++)
        {
            $data[$s]['id_template_module']=pk_decrypt($data[$s]['id_template_module']);
            $update_array[] = array(
                'id_template_module' => $data[$s]['id_template_module'],
                'module_order' => $s
            );
        }

        if(!empty($update_array)){
            $this->Template_model->updateTemplateModuleBatch($update_array);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('m_order_success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function topicOrder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['data'])){ $data = $data['data']; }
        $update_array = array();
        for($s=0;$s<count($data);$s++) {
            $id_template_module_topic=pk_decrypt($data[$s]['id_template_module_topic']);
            if(!in_array($id_template_module_topic,$this->session_user_master_template_module_topics)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data);$s++)
        {
            $data[$s]['id_template_module_topic']=pk_decrypt($data[$s]['id_template_module_topic']);
            $update_array[] = array(
                'id_template_module_topic' => $data[$s]['id_template_module_topic'],
                'topic_order' => $s
            );
        }

        if(!empty($update_array)){
            $this->Template_model->updateTemplateModuleTopicBatch($update_array);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('t_order_success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function questionOrder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['data'])){ $data = $data['data']; }
        $update_array = array();
        for($s=0;$s<count($data);$s++) {
            $id_template_module_topic_question=pk_decrypt($data[$s]['id_template_module_topic_question']);
            if(!in_array($id_template_module_topic_question,$this->session_user_master_template_module_topic_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data);$s++)
        {
            $data[$s]['id_template_module_topic_question']=pk_decrypt($data[$s]['id_template_module_topic_question']);
            $update_array[] = array(
                'id_template_module_topic_question' => $data[$s]['id_template_module_topic_question'],
                'question_order' => $s
            );
        }

        if(!empty($update_array)){
            $this->Template_model->updateTemplateModuleTopicQuestionBatch($update_array);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('q_order_success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function templateOrder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['data'])){ $data = $data['data']; }

        for($s=0;$s<count($data);$s++) {
            $id_template_module=pk_decrypt($data[$s]['id_template_module']);
            if(!in_array($id_template_module,$this->session_user_master_template_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($s['topics'])){
                $topis = array();
                for($t=0;$t<count($s['topics']);$t++) {
                    $id_template_module_topic=pk_decrypt($data[$s]['topics'][$t]['id_template_module_topic']);
                    if(!in_array($id_template_module_topic,$this->session_user_master_template_module_topics)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                        $this->response($result, REST_Controller::HTTP_OK);
                        $topis[] = array(
                            'id_template_module_topic' => $id_template_module_topic,
                            'topic_order' => $t
                        );
                    }
                    if(isset($t['questions'])){
                        $questions = array();
                        for($u=0;$u<count($t['questions']);$u++) {
                            $id_template_module_topic_question=pk_decrypt($data[$s]['topics'][$t]['questions'][$u]['id_template_module_topic_question']);
                            if(!in_array($id_template_module_topic_question,$this->session_user_master_template_module_topic_questions)){
                                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                                $this->response($result, REST_Controller::HTTP_OK);
                                $questions[] = array(
                                    'id_template_module_topic_question' => $id_template_module_topic_question,
                                    'question_order' => $u
                                );
                            }
                        }
                        if(!empty($questions)){
                            $this->Template_model->updateTemplateModuleTopicQuestionBatch($questions);
                        }
                    }
                }
                if(!empty($topis)){
                    $this->Template_model->updateTemplateModuleTopicBatch($topis);
                }
            }
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function count_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data['status'] = 1;
        $result = $this->Template_model->getModuleTopicQuestionCount($data);
        if(isset($result[0])){ $result = $result[0]; }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function templatePreviewOld_get(){

        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Template_model->getTemplatePreview($data);
        foreach($result['modules'] as $k=>$v){
            $result['modules'][$k]['id_template_module']=pk_encrypt($v['id_template_module']);
            $result['modules'][$k]['template_id']=pk_encrypt($v['template_id']);
            $result['modules'][$k]['module_id']=pk_encrypt($v['module_id']);
            foreach($result['modules'][$k]['topics'] as $kt=>$vt){
                $result['modules'][$k]['topics'][$kt]['id_template_module_topic']=pk_encrypt($result['modules'][$k]['topics'][$kt]['id_template_module_topic']);
                $result['modules'][$k]['topics'][$kt]['topic_id']=pk_encrypt($result['modules'][$k]['topics'][$kt]['topic_id']);
                foreach($result['modules'][$k]['topics'][$kt]['questions'] as $kq=>$vq){
                    $result['modules'][$k]['topics'][$kt]['questions'][$kq]['question_id']=pk_encrypt($result['modules'][$k]['topics'][$kt]['questions'][$kq]['question_id']);


                }
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);


    }

    public function templatePreview_get(){

        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $templateDetails = $this->User_model->check_record('template',array('id_template'=>$data['template_id']));
        $data['is_workflow'] = $templateDetails[0]['is_workflow'];
      //  print_r($templateDetails);
        $result = $this->Template_model->getTemplatePreview($data);
        foreach($result['modules'] as $k=>$v){
            $result['modules'][$k]['id_template_module']=pk_encrypt($v['id_template_module']);
            $result['modules'][$k]['template_id']=pk_encrypt($v['template_id']);
            $result['modules'][$k]['module_id']=pk_encrypt($v['module_id']);
            foreach($result['modules'][$k]['topics'] as $kt=>$vt){
                $result['modules'][$k]['topics'][$kt]['id_template_module_topic']=pk_encrypt($result['modules'][$k]['topics'][$kt]['id_template_module_topic']);
                $result['modules'][$k]['topics'][$kt]['topic_id']=pk_encrypt($result['modules'][$k]['topics'][$kt]['topic_id']);
                foreach($result['modules'][$k]['topics'][$kt]['questions'] as $kq=>$vq){
                    $result['modules'][$k]['topics'][$kt]['questions'][$kq]['question_id']=pk_encrypt($result['modules'][$k]['topics'][$kt]['questions'][$kq]['question_id']);
                }
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);


    }

    public function templateView_get(){

        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Template_model->getTemplatePreview($data);
        foreach($result['modules'] as $k=>$v){
            $result['modules'][$k]['id_template_module']=pk_encrypt($v['id_template_module']);
            $result['modules'][$k]['template_id']=pk_encrypt($v['template_id']);
            $result['modules'][$k]['module_id']=pk_encrypt($v['module_id']);
            unset($result['modules'][$k]['topics']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function alltemplates_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if(!in_array($data['template_id'],$this->session_user_master_templates)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = array();
        $data['status']=1;
        $templateDetails  = $this->User_model->check_record('template',array('id_template' => $data['template_id']));
        $result = $modules = $this->Template_model->getTemplateModuleList($data)['data'];
        
        for($m = 0; $m < count($modules); $m++){
            $result[$m]['topics'] = $topics = $this->Template_model->getTemplateModuleTopicList(array('status'=>1,'template_id'=>$modules[$m]['template_id'],'template_module_id'=>$modules[$m]['id_template_module']))['data'];
            $result[$m]['id_template_module'] = pk_encrypt($result[$m]['id_template_module']);
            $result[$m]['template_id'] = pk_encrypt($result[$m]['template_id']);
            $result[$m]['language_id'] = pk_encrypt($result[$m]['language_id']);
            $data['module_id'] = $result[$m]['module_id'];
            
            $topic_details = $this->Template_model->getTopics(array('template_id_not' => $modules[$m]['template_id'],'module_id' => $data['module_id'],'status' => 1));
            $result[$m]['topic_count_notassigned']=count($topic_details);
            // $result[$m]['available_topics']=$topic_details;

            $result[$m]['module_id'] = pk_encrypt($result[$m]['module_id']);            
            for($t = 0; $t < count($topics); $t++){
                $data['topic_id'] = $result[$m]['topics'][$t]['topic_id'];
                $questionDetails = $this->Template_model->getQuestions(array('topic_id' => $data['topic_id'],'template_id_not' => $modules[$m]['template_id'],'status' => 1));
                $result[$m]['topics'][$t]['question_count_notassigned']=count($questionDetails);
                // $result[$m]['topics'][$t]['available_questions'] = $questionDetails;

                $result[$m]['topics'][$t]['questions'] = $questions =  $this->Template_model->getTemplateModuleTopicQuestionList(array('status'=>1,'template_id'=>$modules[$m]['template_id'],'template_module_topic_id'=>$topics[$t]['id_template_module_topic'] ,'with_relation' => 1,'is_workflow'=>$templateDetails[0]['is_workflow']))['data'];
                $result[$m]['topics'][$t]['id_template_module_topic'] = pk_encrypt($result[$m]['topics'][$t]['id_template_module_topic']);
                $result[$m]['topics'][$t]['id_topic_language'] = pk_encrypt($result[$m]['topics'][$t]['id_topic_language']);
                $result[$m]['topics'][$t]['template_module_id'] = pk_encrypt($result[$m]['topics'][$t]['template_module_id']);
                $result[$m]['topics'][$t]['language_id'] = pk_encrypt($result[$m]['topics'][$t]['language_id']);
                for($q = 0; $q < count($questions); $q++){
                    $result[$m]['topics'][$t]['questions'][$q]['id_question_language'] = pk_encrypt($result[$m]['topics'][$t]['questions'][$q]['id_question_language']);
                    $result[$m]['topics'][$t]['questions'][$q]['id_template_module_topic_question'] = pk_encrypt($result[$m]['topics'][$t]['questions'][$q]['id_template_module_topic_question']);
                    $result[$m]['topics'][$t]['questions'][$q]['language_id'] = pk_encrypt($result[$m]['topics'][$t]['questions'][$q]['language_id']);
                    $result[$m]['topics'][$t]['questions'][$q]['question_id'] = pk_encrypt($result[$m]['topics'][$t]['questions'][$q]['question_id']);
                    $result[$m]['topics'][$t]['questions'][$q]['template_module_topic_id'] = pk_encrypt($result[$m]['topics'][$t]['questions'][$q]['template_module_topic_id']);
                }
            }
        }
        $modules_result = $this->Template_model->getModules(array('template_id_not' => $data['template_id'],'status' => 1,'contract_review_id' => 0,'customer_id'=>$this->session_user_info->customer_id));
        $module['availablemodule_count'] = count($modules_result);
        // // echo $this->db->last_query();exit;
        // $module['availablemodule_name'] = $modules_result;

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result,'module_details'=>$module);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function linkTemplateCustomer_post(){
        //This invokes when admin assignes a template
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //$this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        //$this->form_validator->add_rules('new_template_name', array('required'=>$this->lang->line('template_name_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['template_id']) && $data['template_id'] != null) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            if($this->session_user_info->user_role_id!=1){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        else{
            $data['template_id'] = $this->Template_model->addTemplate(array('template_name' => $data['new_template_name'],'template_status' => 1,'created_on' => currentDate()));
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        }
        
        $check_name = $this->Template_model->checkCustomerTemplateName($data);
        //echo '<pre>'.$this->db->last_query();exit;
        if(!empty($check_name)){
            $result = array('status'=>false, 'message' =>$this->lang->line('template_name_duplicate'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['created_by'] = $this->session_user_id;
        //echo '<pre>'.print_r($data);exit;
        //As With Admin assigns a template to a customer this will be only review template
        $data['is_workflow']=0;
        $link_status = $this->Template_model->linkTemplateCustomer($data);
        
        // $new_template_id = $link_status->row();
        // if($new_template_id){
        //     $customer_templateData = array('template_id'=>$new_template_id->template_id,
        //                                 'customer_id'=>$data['customer_id'],
        //                                 'created_by'=> $this->session_user_id,
        //                                 'created_on'=>currentDate(),
        //                                 'status'=>'1'
        //                             );
        //     $this->db->close();
        //     $this->db->initialize();
        //     if($this->db->insert('customer_template',$customer_templateData)){
        //         $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'5');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     } else {
        //         $result = array('status'=>FALSE, 'message' => $this->lang->line('customer_template_failed'), 'data'=>'7');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
            
        // } else {
        //     $result = array('status'=>FALSE, 'message' => $this->lang->line('dump_template_failed'), 'data'=>'6');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'5');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function linkCustomerTemplate_post()
    {
        //This invokes when customer import a template
        $data = $this->input->post();
        //print_r($data); exit;
        if(empty($data))
        {
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(!isset($data['customer_id']))
        {
            $data['customer_id']=$this->session_user_info->customer_id;
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['template_id'] = pk_decrypt($data['template_id']);
        if($this->session_user_info->user_role_id!=1 && $this->session_user_info->user_role_id!=2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        $check_name = $this->Template_model->checkCustomerTemplateName($data);
        if(!empty($check_name)){
            $result = array('status'=>false, 'error' =>$this->lang->line('template_name_duplicate'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['created_by'] = $this->session_user_id;
        if(isset($data['is_workflow']))
        {
            $data['is_workflow']=1;
            //print_r($data); exit;
            $this->Template_model->linkTemplateCustomerWorkflow($data);
        }
        else
        {
            $data['is_workflow']=0;
            $this->Template_model->linkTemplateCustomer($data);
        }
        //$data['is_workflow']=isset($data['is_workflow'])?1:0;
        //print_r($data); exit;
        //$link_status = $this->Template_model->linkTemplateCustomer($data);
        // print_r($link_status); exit;
        //echo '<pre>'.$this->db->last_query();exit;
        // $new_template_id = $link_status->row();
        // if($new_template_id){
        //     $customer_templateData = array('template_id'=>$new_template_id->template_id,
        //                                 'customer_id'=>$data['customer_id'],
        //                                 'created_by'=> $this->session_user_id,
        //                                 'created_on'=>currentDate(),
        //                                 'status'=>'1'
        //                             );
        //     $this->db->close();
        //     $this->db->initialize();
        //     if($this->db->insert('customer_template',$customer_templateData)){
        //         $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'5');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     } else {
        //         $result = array('status'=>FALSE, 'message' => $this->lang->line('customer_template_failed'), 'data'=>'7');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
            
        // } else {
        //     $result = array('status'=>FALSE, 'message' => $this->lang->line('dump_template_failed'), 'data'=>'6');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'5');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function getImportTemplates_get()
    {
        $data = $this->input->get();
        $data['template_status'] = 1; //getting only active templates
        $data['import_status']=1;
        $result = $this->Template_model->getImportTemplateModuleTopicQuestionCount($data);
        //echo ''.$this->db->last_query(); exit;
        foreach($result["data"] as $k=>$v)
        {
            $result["data"][$k]['id_template']=pk_encrypt($v['id_template']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    
    }


}