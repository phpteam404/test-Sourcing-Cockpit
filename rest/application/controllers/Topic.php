<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Topic extends REST_Controller
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
        $this->session_user_master_language=$this->Validation_model->getLanguage();
        $this->session_user_contract_review_modules=$this->Validation_model->getContractReviewModules(array('contract_review_id'=>$this->session_user_contract_reviews));
        $this->session_user_master_contract_review_modules=$this->Validation_model->getMasterContractReviewModules();
        $this->session_user_master_contract_review_topics=$this->Validation_model->getMasterContractReviewTopics();
    }

    public function list_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        /*if(isset($data['language_id']))
            $data['language_id'] = pk_decrypt($data['language_id']);*/
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*if(isset($data['module_id']))
            $data['module_id'] = pk_decrypt($data['module_id']);*/
        //echo '<pre>';print_r($this->session_user_id);exit;
        if(isset($data['module_id'])) {
            $data['module_id'] = pk_decrypt($data['module_id']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['module_id'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            /*if($this->session_user_info->user_role_id!=1 && !in_array($data['module_id'],$this->session_user_contract_review_modules)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $result = $this->Topic_model->TopicList($data);
        foreach($result['data'] as $k => $v){
            $requestarray = array('module_id' => $v['module_id'] ,'topic_id' => $v['id_topic']);
            if(isset($data['is_workflow']) && $data['is_workflow'] == true){
                $requestarray['is_workflow'] = 1;
            }
            $RealtionQuestions = $this->Topic_model->getRelationquestions($requestarray);
            $result['data'][$k]['relation_question_count'] = (count($RealtionQuestions) > 0) ? count($RealtionQuestions) : '---' ;;
            $result['data'][$k]['id_topic'] = pk_encrypt($v['id_topic']);
            $result['data'][$k]['topic_id'] = pk_encrypt($v['topic_id']);
            $result['data'][$k]['module_id'] = pk_encrypt($v['module_id']);
            $result['data'][$k]['created_by'] = pk_encrypt($v['created_by']);
            $result['data'][$k]['updated_by'] = pk_encrypt($v['updated_by']);
            if(isset($v['language_id']))
                $result['data'][$k]['language_id'] = pk_encrypt($v['language_id']);
            if(isset($v['id_question']))
                $result[$k]['data']['id_question'] = pk_encrypt($v['id_question']);
            if(isset($v['parent_topic_id']))
                $result[$k]['data']['parent_topic_id'] = pk_encrypt($v['parent_topic_id']);
            $result['data'][$k]['id_topic_language'] = pk_encrypt($v['id_topic_language']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function types_get()
    {
        $result[] = array('key'=>'simple','value'=>'Simple');
        $result[] = array('key'=>'general','value'=>'General');
        //$result[] = array('key'=>'data','value'=>'Data');
        //$result[] = array('key'=>'relationship','value'=>'Relationship');
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function name_get()
    {
        $data = $this->input->get();
        /*if(isset($data['language_id']))
            $data['language_id'] = pk_decrypt($data['language_id']);*/
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Topic_model->getTopics($data);
        foreach($result as $k=>$v)
            $result[$k]['id_topic']=pk_encrypt($v['id_topic']);
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

        $this->form_validator->add_rules('id_module', array('required'=>$this->lang->line('module_id_req')));
        $this->form_validator->add_rules('topic_name', array('required'=>$this->lang->line('topic_name_req')));
        $this->form_validator->add_rules('topic_type', array('required'=>$this->lang->line('topic_type_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $moduledetails = $this->User_model->check_record('module' , array('id_module' => pk_decrypt($data['id_module'])));
            if(!(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ((($moduledetails[0]['is_workflow'] == 1) && ($this->session_user_info->content_administator_task_templates == 1)) || (($moduledetails[0]['is_workflow'] == 0) && $this->session_user_info->content_administator_review_templates == 1))))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['id_module']))         $data['id_module'] = pk_decrypt($data['id_module']);
        if(isset($data['id_module'])) {
            $data['id_module'] = pk_decrypt($data['id_module']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_module'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $order = $this->User_model->check_record('topic',array('module_id' => $data['id_module']));
        $topic_id = $this->Topic_model->addTopic(array(
            'module_id' => $data['id_module'],
            'topic_order' => count($order),
            'created_by' => $data['created_by'],
            'created_on' => currentDate(),
            'type' => $data['topic_type']
        ));

        $this->Topic_model->addTopicLanguage(array(
            'topic_id' => $topic_id,
            'topic_name' => $data['topic_name'],
            'language_id' => 1
        ));
        $topicModule =$this->User_model->check_record('template_module',array('module_id'=>$data['id_module']));
        //echo ''.$this->db->last_query(); exit;
        //print_r($topicModule); exit;
        //for checking of workflow if it is workflow we are adding topic id and module id to the tempalte_module_topic
        if(isset($data['is_workflow']) && $data['is_workflow']==TRUE)
        {
                //we are adding to the table from the above obtained module id and topic id (template_module_id has a relation with module so template_module_id is module_id)
        //print_r($topicModule); exit;
		$template_module_id = $this->User_model->check_record('template_module',array('module_id' => $data['id_module']));
                $data['template_module_id'] = $template_module_id[0]['id_template_module'];
              $order = $this->User_model->check_record('template_module_topic',array('template_module_id' => $data['template_module_id']));
                $this->Template_model->addTemplateModuleTopic(array(
                'template_module_id' => $topicModule[0]['id_template_module'],
                'topic_id' => $topic_id,
                'topic_order' => count($order),
                'status' => 1
                ));
                //echo ''.$this->db->last_query(); exit;
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('topic_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_module', array('required'=>$this->lang->line('module_id_req')));
        $this->form_validator->add_rules('id_topic', array('required'=>$this->lang->line('topic_id_req')));
        $this->form_validator->add_rules('id_topic_language', array('required'=>$this->lang->line('topic_id_req')));
        $this->form_validator->add_rules('topic_name', array('required'=>$this->lang->line('topic_name_req')));
        $this->form_validator->add_rules('topic_type', array('required'=>$this->lang->line('topic_type_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('topic_status', array('required'=>$this->lang->line('topic_status_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $moduledetails = $this->User_model->check_record('module' , array('id_module' => pk_decrypt($data['id_module'])));
            if(!(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ((($moduledetails[0]['is_workflow'] == 1) && ($this->session_user_info->content_administator_task_templates == 1)) || (($moduledetails[0]['is_workflow'] == 0) && $this->session_user_info->content_administator_review_templates == 1))))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //if(isset($data['id_module']))            $data['id_module'] = pk_decrypt($data['id_module']);
        //echo $this->session_user_info->user_role_id.' sess';
        if(isset($data['id_module'])) {
            $data['id_module'] = pk_decrypt($data['id_module']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_module'],$this->session_user_master_contract_review_modules['module_id'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //session_user_master_contract_review_topics
        //if(isset($data['id_topic']))            $data['id_topic'] = pk_decrypt($data['id_topic']);
        //echo $data['created_by'].'created ';

        //echo in_array($data['id_topic'],$this->session_user_master_contract_review_topics);
        //echo '//'.$this->session_user_id;
        if(isset($data['id_topic'])) {
            $data['id_topic'] = pk_decrypt($data['id_topic']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_topic'],$this->session_user_master_contract_review_topics['topic'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        /*if(isset($data['id_topic_language']))
            $data['id_topic_language'] = pk_decrypt($data['id_topic_language']);*/
        if(isset($data['id_topic_language'])) {
            $data['id_topic_language'] = pk_decrypt($data['id_topic_language']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_topic_language'],$this->session_user_master_contract_review_topics['topic_lang'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            /*if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }

        $this->Topic_model->updateTopic(array(
            'id_topic' => $data['id_topic'],
            'module_id' => $data['id_module'],
            'topic_order' => isset($data['topic_order'])?$data['topic_order']:'1',
            'topic_status' => $data['topic_status'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
            'type' => $data['topic_type']
        ));

        $this->Topic_model->updateTopicLanguage(array(
            'id_topic_language' => $data['id_topic_language'],
            'topic_name' => $data['topic_name']
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('topic_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function delete_delete()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_topic', array('required'=>$this->lang->line('topic_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*if(isset($data['id_topic']))
            $data['id_topic'] = pk_decrypt($data['id_topic']);*/
        if(isset($data['id_topic'])) {
            $data['id_topic'] = pk_decrypt($data['id_topic']);
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_topic'],$this->session_user_master_contract_review_topics['topic'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $this->Topic_model->updateTopic(array(
            'id_topic' => $data['id_topic'],
            'topic_status' => 0
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('topic_inactive'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
}