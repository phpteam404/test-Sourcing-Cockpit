<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Question extends REST_Controller
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
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_master_contract_review_topics=$this->Validation_model->getMasterContractReviewTopics();
        $this->session_user_master_contract_review_questions=$this->Validation_model->getContractReviewMasterQuestions();
        $this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
    }

    public function list_get()
    {
        $data = $this->input->get();
        if(isset($data['contract_review_id'])) {
            $data['contract_review_id'] = pk_decrypt($data['contract_review_id']);
            if(!in_array($data['contract_review_id'],$this->session_user_contract_reviews)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if($this->session_user_info->user_role_id == 2 || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 || $this->session_user_info->content_administator_task_templates == 1 ) )){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        else if(isset($data['customer_id'])){
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']==0)
                unset($data['customer_id']);
        }
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $data['topic_status'] = 1;
        $data['contract_review_id'] = 0;
        $result = $this->Question_model->ModuleTopicQuestionList($data);
        foreach($result['data'] as $k=>$v){
            $requestarray = array('module_id' => $v['id_module'] ,'topic_id' => $v['id_topic']);
            if(isset($data['is_workflow']) && $data['is_workflow'] == true){
                $requestarray['is_workflow'] = 1;
            }
            $RealtionQuestions = $this->Topic_model->getRelationquestions($requestarray);
            $result['data'][$k]['relation_question_count'] = (count($RealtionQuestions) > 0) ? count($RealtionQuestions) : '---' ;;
            $result['data'][$k]['id_module']=pk_encrypt($v['id_module']);
            $result['data'][$k]['id_topic']=pk_encrypt($v['id_topic']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function topicQuestions_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_topic', array('required'=>$this->lang->line('topic_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_topic'])) {
            $data['id_topic'] = pk_decrypt($data['id_topic']);
            /*if(!in_array($data['id_topic'],$this->session_user_master_contract_review_topics['topic'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data);//helper function for ordering smart table options
        $data['customer_id'] = 0;
        // if($this->session_user_info->user_role_id == 2){
        //     $data['customer_id'] = $this->session_user_info->customer_id;
        // }
        if($this->session_user_info->user_role_id == 2 || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ||  $this->session_user_info->content_administator_task_templates == 1 ))){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $result = $this->Question_model->QuestionList($data);
        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['id_question']=pk_encrypt($v['id_question']);
            $result[$k]['id_question_language']=pk_encrypt($v['id_question_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['parent_question_id']=pk_encrypt($v['parent_question_id']);
            $result[$k]['question_id']=pk_encrypt($v['question_id']);
            $result[$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
            $result[$k]['provider_visibility']=0;
            $category_on_of = [];
            foreach($result[$k]['relationship_categories'] as $kr=>$vr){
                $result[$k]['relationship_categories'][$kr]['created_by']=pk_encrypt($vr['created_by']);
                $result[$k]['relationship_categories'][$kr]['customer_id']=pk_encrypt($vr['customer_id']);
                $result[$k]['relationship_categories'][$kr]['id_relationship_category']=pk_encrypt($vr['id_relationship_category']);
                $result[$k]['relationship_categories'][$kr]['id_relationship_category_question']=pk_encrypt($vr['id_relationship_category_question']);
                $result[$k]['relationship_categories'][$kr]['parent_relationship_category_id']=pk_encrypt($vr['parent_relationship_category_id']);
                $result[$k]['relationship_categories'][$kr]['updated_by']=pk_encrypt($vr['updated_by']);
                $result[$k]['provider_visibility'] = $vr['provider_visibility'];
                $category_on_of[] = (int)$vr['status'];
            }
            //**If all categorys are off then provider question is becomes off with the following
            if($category_on_of[0] == 0 && $category_on_of[1] == 0 && $category_on_of[2] == 0 && $category_on_of[3] == 0 && !isset($data['is_workflow'])){
                $result[$k]['provider_visibility']=0;
                $this->User_model->update_data('relationship_category_question',array('provider_visibility'=>0),array('question_id'=>$v['question_id']));
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_get()
    {
        $data = $this->input->get();
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_question'])) {
            $data['id_question'] = pk_decrypt($data['id_question']);
            /*if(!in_array($data['id_question'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['question_id'])) {
            $data['question_id'] = pk_decrypt($data['question_id']);
            /*if(!in_array($data['question_id'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $result = $this->Question_model->getQuestionInfo($data);
        //echo '<pre>'.$this->db->last_query();exit;
        if($result[0]['id_question_option_language']!='') { //providing options as array
            $result[0]['id_question_option_language'] = explode(',', $result[0]['id_question_option_language']);
            $result[0]['option_name'] = explode(',', $result[0]['option_name']);
        }
        /*if single record is present then*/

        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['id_question']=pk_encrypt($v['id_question']);
            //Geting provider_visibilty by question from relationship_category_question
            $result[$k]['provider_visibility'] = $this->User_model->check_record('relationship_category_question',array('question_id'=>$v['id_question']))[0]['provider_visibility'];
            $result[$k]['id_question_language']=pk_encrypt($v['id_question_language']);
            $result[$k]['id_question_option']=pk_encrypt($v['id_question_option']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['parent_question_id']=pk_encrypt($v['parent_question_id']);
            $result[$k]['parent_question_option_id']=pk_encrypt($v['parent_question_option_id']);
            $result[$k]['question_id']=pk_encrypt($v['question_id']);
            $result[$k]['topic_id']=pk_encrypt($v['topic_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
            if(is_array($result[$k]['id_question_option_language'])) {
                foreach ($result[$k]['id_question_option_language'] as $kl => $vl) {
                    $result[$k]['id_question_option_language'][$kl] = pk_encrypt($vl);
                }
            }
            else {
                $result[$k]['id_question_option_language']=pk_encrypt($v['id_question_option_language']);

            }
            foreach($result[$k]['option_names'] as $ko=>$vo){
                $result[$k]['option_names'][$ko]['created_by']=pk_encrypt($vo['created_by']);
                $result[$k]['option_names'][$ko]['id_question_option']=pk_encrypt($vo['id_question_option']);
                $result[$k]['option_names'][$ko]['id_question_option_language']=pk_encrypt($vo['id_question_option_language']);
                $result[$k]['option_names'][$ko]['language_id']=pk_encrypt($vo['language_id']);
                $result[$k]['option_names'][$ko]['parent_question_option_id']=pk_encrypt($vo['parent_question_option_id']);
                $result[$k]['option_names'][$ko]['question_id']=pk_encrypt($vo['question_id']);
                $result[$k]['option_names'][$ko]['question_option_id']=pk_encrypt($vo['question_option_id']);
                $result[$k]['option_names'][$ko]['updated_by']=pk_encrypt($vo['updated_by']);
            }
            $data['id_topic'] = $v['topic_id'];
            $result1=$this->Question_model->getQuestionModuleTemplate($data);
            $result[$k]['module_name']=$result1[0]['module_name'];
            $result[$k]['template_name']=$result1[0]['template_name'];
            $result[$k]['module_id']=$result1[0]['module_id'];
        }
        if(isset($result[0])){ $result = $result[0]; }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function add_post()
    {
        // $data = $this->input->post();
        $data=$_POST;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_topic', array('required'=>$this->lang->line('topic_id_req')));
        $this->form_validator->add_rules('question_text', array('required'=>$this->lang->line('question_text_req')));
        $this->form_validator->add_rules('question_type', array('required'=>$this->lang->line('question_type_req')));
        if(isset($data['question_type']) && $data['question_type']!='input' && $data['question_type']!='date')
        $this->form_validator->add_rules('option_name', array('required'=>$this->lang->line('option_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            if( !( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ||  $this->session_user_info->content_administator_task_templates == 1 ) ) )
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_topic'])) {
            $data['id_topic'] = pk_decrypt($data['id_topic']);
            /*if(!in_array($data['id_topic'],$this->session_user_master_contract_review_topics['topic'])){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $question_id = $this->Question_model->addQuestion(array(
            'topic_id' => $data['id_topic'],
            'question_order' => isset($data['question_order'])?$data['question_order']:'1',
            'question_required' => isset($data['question_required'])?$data['question_required']:'1',
            'question_type' => $data['question_type'],
            'help_text' => $data['help_text'],
            'created_by' => $data['created_by'],
            'provider_visibility' => $data['provider_visibility'],
            'created_on' => currentDate()
        ));

        $this->Question_model->addQuestionLanguage(array(
            'question_id' => $question_id,
            'question_text' => $data['question_text'],
            'request_for_proof' => isset($data['request_for_proof'])?$data['request_for_proof']:'',
            'language_id' => 1
        ));

        if($data['question_type']!='input' && $data['question_type']!='date') { // input type question don't have any options,only input box


            for($s=0;$s<count($data['option_name']);$s++)
            {

                /*$question_option_id = $this->Question_model->addQuestionOption(array(
                    'question_id' => $question_id,
                    'option_value' => isset($data['option_name'][$s]['value'])?$data['option_name'][$s]['value']:'',
                    'created_by ' => $data['created_by'],
                    'created_on' => currentDate()
                ));*/

                if($data['question_type']=='dropdown') { //if question is dropdown question option format is different
                    $question_option_id = $this->Question_model->addQuestionOption(array(
                        'question_id' => $question_id,
                        'option_value' => isset($data['option_name'][$s]['question_value'])?$data['option_name'][$s]['question_value']:'',
                        'created_by ' => $data['created_by'],
                        'created_on' => currentDate()
                    ));
                    $this->Question_model->addQuestionOptionLanguage(array(
                            'question_option_id' => $question_option_id,
                            'option_name' => $data['option_name'][$s]['question_option'],
                            'language_id' => 1
                    ));
                }
                else {
                    $question_option_id = $this->Question_model->addQuestionOption(array(
                        'question_id' => $question_id,
                        'option_value' => isset($data['option_name'][$s]['value'])?$data['option_name'][$s]['value']:'',
                        'created_by ' => $data['created_by'],
                        'created_on' => currentDate()
                    ));
                    $this->Question_model->addQuestionOptionLanguage(array(
                        'question_option_id' => $question_option_id,
                        'option_name' => $data['option_name'][$s]['option'],
                        'language_id' => 1
                    ));
                }
            }
        }

        if(isset($data['categories'])){ //adding relationship categories to question
            //echo '<pre>'.print_r($data['categories']);exit;
            for($s=0;$s<count($data['categories']);$s++)
            {
                $data['categories'][$s]['id_relationship_category']=pk_decrypt($data['categories'][$s]['id_relationship_category']);
                $this->Question_model->addRelationshipCategoryQuestion(array(
                    'relationship_category_id' => $data['categories'][$s]['id_relationship_category'],
                    'question_id' => $question_id,
                    'status' => $data['categories'][$s]['status'],
                    'provider_visibility' => isset($data['provider_visibility'])?$data['provider_visibility']:0
                ));
            }
        }
        $questionTopic =$this->User_model->check_record('template_module_topic',array('topic_id'=>$data['id_topic']));
        //echo ''.$this->db->last_query(); 
        //print_r($questionTopic); exit;
        //for checking workflow or not if it is workflow we are adding topic id and question id to the template_module_topic_question table
        if(isset($data['is_workflow']) && $data['is_workflow']==TRUE)
        {       
            //we are adding to the below table based on topic id and question id template_module_topic_question has a relation with topic so template_module_topic_id is topic_id
            $order = $this->User_model->check_record('template_module_topic_question',array('template_module_topic_id' => $data['template_module_topic_id']));
            //print_r($questiontopic); exit;
             $this->Template_model->addTemplateModuleTopicQuestion(array(
            'template_module_topic_id' => $questionTopic[0]['id_template_module_topic'],
            'question_id' => $question_id,
            'question_order' => count($order),
            'status' => 1
            ));
            //echo ''.$this->db->last_query(); exit;
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('question_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        // $data = $this->input->post();
        $data = $_POST;
        /*echo "<pre>";print_r($data);exit;*/
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_question', array('required'=>$this->lang->line('question_id_req')));
        $this->form_validator->add_rules('id_question_language', array('required'=>$this->lang->line('question_language_id_req')));
        $this->form_validator->add_rules('question_text', array('required'=>$this->lang->line('question_text_req')));
        $this->form_validator->add_rules('question_type', array('required'=>$this->lang->line('question_type_req')));
        if(isset($data['question_type']) && $data['question_type']!='input' && $data['question_type']!='date')
            $this->form_validator->add_rules('option_name', array('required'=>$this->lang->line('option_name_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            if( !( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 || $this->session_user_info->content_administator_task_templates == 1) ) )
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_question'])) {
            $data['id_question'] = pk_decrypt($data['id_question']);
            /*if(!in_array($data['id_question'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_question_language'])) $data['id_question_language']=pk_decrypt($data['id_question_language']);
        $this->Question_model->updateQuestion(array(
            'id_question' => $data['id_question'],
            'question_order' => isset($data['question_order'])?$data['question_order']:'1',
            'question_required' => isset($data['question_required'])?$data['question_required']:'1',
            'question_type' => $data['question_type'],
            'provider_visibility' =>  $data['provider_visibility'],
            'help_text' => $data['help_text'],
            'updated_on' => currentDate()
        ));

        $this->Question_model->updateQuestionLanguage(array(
            'id_question_language' => $data['id_question_language'],
            'question_text' => $data['question_text'],
            'request_for_proof' => isset($data['request_for_proof'])?$data['request_for_proof']:'',
            'language_id' => 1
        ));

        if($data['question_type']!='input' && $data['question_type']!='date') { //input and date type questions don't have any options
            for($s=0;$s<count($data['option_name']);$s++)
            {
                if($data['question_type']=='dropdown') { //if dropdown question, question options format is different from client side
                    if ($data['option_name'][$s]['type'] == 'update') {
                        $data['option_name'][$s]['id_question_option']=pk_decrypt($data['option_name'][$s]['id_question_option']);
                        $data['option_name'][$s]['id_question_option_language']=pk_decrypt($data['option_name'][$s]['id_question_option_language']);
                        $this->Question_model->updateQuestionOption(array(
                            'id_question_option' => $data['option_name'][$s]['id_question_option'],
                            'option_value' => isset($data['option_name'][$s]['question_value'])?$data['option_name'][$s]['question_value']:'',
                            'updated_by ' => $data['updated_by'],
                            'updated_on' => currentDate()
                        ));

                        $this->Question_model->updateQuestionOptionLanguage(array(
                            'id_question_option_language' => $data['option_name'][$s]['id_question_option_language'],
                            'option_name' => $data['option_name'][$s]['question_option'],
                            'language_id' => 1
                        ));
                    } else {
                        $question_option_id = $this->Question_model->addQuestionOption(array(
                            'question_id' => $data['id_question'],
                            'option_value' => isset($data['option_name'][$s]['question_value'])?$data['option_name'][$s]['question_value']:'',
                            'created_by ' => $data['created_by'],
                            'created_on' => currentDate()
                        ));

                        $this->Question_model->addQuestionOptionLanguage(array(
                            'question_option_id' => $question_option_id,
                            'option_name' => $data['option_name'][$s]['question_option'],
                            'language_id' => 1
                        ));
                    }
                }
                else{
                    /*$this->Question_model->updateQuestionOptionLanguage(array(
                        'id_question_option_language' => $data['id_question_option_language'][$s],
                        'option_name' => $data['option_name'][$s],
                        'language_id' => 1
                    ));*/
                    $data['option_name'][$s]['id_question_option']=pk_decrypt($data['option_name'][$s]['id_question_option']);
                    $data['option_name'][$s]['id_question_option_language']=pk_decrypt($data['option_name'][$s]['id_question_option_language']);
                    $this->Question_model->updateQuestionOption(array(
                        'id_question_option' => $data['option_name'][$s]['id_question_option'],
                        'option_value' => isset($data['option_name'][$s]['value'])?$data['option_name'][$s]['value']:'',
                        'updated_by ' => $data['updated_by'],
                        'updated_on' => currentDate()
                    ));

                    $this->Question_model->updateQuestionOptionLanguage(array(
                        'id_question_option_language' => $data['option_name'][$s]['id_question_option_language'],
                        'option_name' => $data['option_name'][$s]['option'],
                        'language_id' => 1
                    ));
                }

            }

            if(isset($data['option_delete'])) { //for deleted options
                for ($s = 0; $s < count($data['option_delete']); $s++) {
                    $data['option_delete'][$s]['id_question_option']=pk_decrypt($data['option_delete'][$s]['id_question_option']);
                    $data['option_delete'][$s]['id_question_option_language']=pk_decrypt($data['option_delete'][$s]['id_question_option_language']);
                    $this->Question_model->updateQuestionOption(array(
                        'id_question_option' => $data['option_delete'][$s]['id_question_option'],
                        'status' => 0
                    ));
                    $this->Question_model->updateQuestionOptionLanguage(array(
                        'id_question_option_language' => $data['option_delete'][$s]['id_question_option_language'],
                        'status' => 0
                    ));
                }
            }

        }

        
        $category_status = array();
        if(isset($data['categories'])){ //updating and adding new categories for this questions
            for($s=0;$s<count($data['categories']);$s++)
            {
                $data['categories'][$s]['id_relationship_category']=pk_decrypt($data['categories'][$s]['id_relationship_category']);
                if($data['categories'][$s]['id_relationship_category_question']==''){
                    $this->Question_model->addRelationshipCategoryQuestion(array(
                        'relationship_category_id' => $data['categories'][$s]['id_relationship_category'],
                        'question_id' => $data['id_question'],
                        'status' => $data['categories'][$s]['status'],
                        'provider_visibility' => isset($data['provider_visibility'])?$data['provider_visibility']:0
                    ));
                }
                else {
                    $data['categories'][$s]['id_relationship_category_question']=pk_decrypt($data['categories'][$s]['id_relationship_category_question']);
                    $this->Question_model->updateRelationshipCategoryQuestion(array(
                        'id_relationship_category_question' => $data['categories'][$s]['id_relationship_category_question'],
                        'relationship_category_id' => $data['categories'][$s]['id_relationship_category'],
                        'question_id' => $data['id_question'],
                        'status' => $data['categories'][$s]['status'],
                        'provider_visibility' => isset($data['provider_visibility'])?$data['provider_visibility']:0
                    ));
                }
                $category_status[] = $data['categories'][$s]['status'];
            }
//echo '<pre>'.print_r($category_status);exit;
            if(isset($data['provider_visibility']) && $data['provider_visibility']=='1' && !isset($data['is_workflow'])){
                $prev_category_question_relation = $this->User_model->check_record('relationship_category_question',array('question_id'=>$data['id_question']));
                //echo '<pre>'.print_r($prev_category_question_relation);exit;
                if(in_array($category_status,1)){
                    if(count($prev_category_question_relation)>0){
                        if($prev_category_question_relation[0]['status'] == 0 && $prev_category_question_relation[1]['status'] == 0 && $prev_category_question_relation[2]['status'] == 0 && $prev_category_question_relation[3]['status'] == 0)
                            $this->response(array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('enable_category')), 'data'=>array('provider_visibility'=>$prev_category_question_relation[0]['provider_visibility'])), REST_Controller::HTTP_OK);
                    }
                }
                else{
                    if(count($prev_category_question_relation)>0){
                        if($prev_category_question_relation[0]['status'] == 0 && $prev_category_question_relation[1]['status'] == 0 && $prev_category_question_relation[2]['status'] == 0 && $prev_category_question_relation[3]['status'] == 0)
                            $this->response(array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('enable_category')), 'data'=>array('provider_visibility'=>$prev_category_question_relation[0]['provider_visibility'])), REST_Controller::HTTP_OK);
                    }
                }
            }
        }

        
            

        $result = array('status'=>TRUE, 'message' => $this->lang->line('question_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updateRelationshipCategories_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_question', array('required'=>$this->lang->line('question_id_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        //$this->form_validator->add_rules('id_relationship_category_question', array('required'=>$this->lang->line('id_relationship_category_question_req')));
        if(!isset($data['provider_visibility'])){
            $this->form_validator->add_rules('id_relationship_category', array('required'=>$this->lang->line('id_relationship_category_req')));
            $this->form_validator->add_rules('status', array('required'=>$this->lang->line('updateRelationshipCategories_status_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            if( !( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ||  $this->session_user_info->content_administator_task_templates == 1 ) ) )
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            // $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            // $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_question'])) {
            $data['id_question'] = pk_decrypt($data['id_question']);
            /*if(!in_array($data['id_question'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_category'])){
            $data['id_relationship_category']=pk_decrypt($data['id_relationship_category']);
            /*if(!in_array($data['id_relationship_category'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        
        if(isset($data['provider_visibility']) && $data['provider_visibility']=='1' && !isset($data['is_workflow'])){
            $prev_category_question_relation = $this->User_model->check_record('relationship_category_question',array('question_id'=>$data['id_question']));
            //echo '<pre>'.print_r($prev_category_question_relation);exit;
            if(count($prev_category_question_relation)>0){
                if($prev_category_question_relation[0]['status'] == 0 && $prev_category_question_relation[1]['status'] == 0 && $prev_category_question_relation[2]['status'] == 0 && $prev_category_question_relation[3]['status'] == 0)
                    $this->response(array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('enable_category')), 'data'=>'3'), REST_Controller::HTTP_OK);
            }
        }        
        //var_dump($data);exit;
        if(isset($data['id_relationship_category_question'])) $data['id_relationship_category_question']=pk_decrypt($data['id_relationship_category_question']);

        if(isset($data['provider_visibility']))
            $this->User_model->update_data('relationship_category_question',array('provider_visibility'=>$data['provider_visibility']),array('question_id'=>$data['id_question']));

            if(isset($data['id_relationship_category_question'])){ //updating and adding new categories for this questions

                if($data['id_relationship_category_question']==''){
                    $this->Question_model->addRelationshipCategoryQuestion(array(
                        'relationship_category_id' => $data['id_relationship_category'],
                        'question_id' => $data['id_question'],
                        'status' => $data['status']
                    ));
                }
                else
                    $this->Question_model->updateRelationshipCategoryQuestion(array(
                        'id_relationship_category_question' => $data['id_relationship_category_question'],
                        'relationship_category_id' => $data['id_relationship_category'],
                        'question_id' => $data['id_question'],
                        'status' => $data['status']
                    ));

        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('question_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updateStatus_post() //disable or enable the question
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_question', array('required'=>$this->lang->line('question_id_req')));
        $this->form_validator->add_rules('question_status', array('required'=>$this->lang->line('question_status_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }



        if($this->session_user_info->user_role_id>2){
            if( !( ($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ||  $this->session_user_info->content_administator_task_templates == 1 ) ) )
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            // $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            // $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_question'])) {
            $data['id_question'] = pk_decrypt($data['id_question']);
            /*if(!in_array($data['id_question'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $this->Question_model->updateQuestion(array(
            'id_question' => $data['id_question'],
            'question_status' => $data['question_status'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate()
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('question_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function category_get()
    {
        $data = $this->input->get();
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id>2 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['question_id'])) {
            $data['question_id'] = pk_decrypt($data['question_id']);
            /*if($data['question_id']>0 && !in_array($data['question_id'],$this->session_user_master_contract_review_questions)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $data['status'] = 1;
        $data['customer_id'] = 0;
        // if($this->session_user_info->user_role_id == 2){
        //     $data['customer_id'] = $this->session_user_info->customer_id;
        // }
        if($this->session_user_info->user_role_id == 2 || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_review_templates == 1 ||  $this->session_user_info->content_administator_task_templates == 1 ))){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $result = $this->Question_model->getQuestionRelationshipCategory($data);
        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_category']=pk_encrypt($v['id_relationship_category']);
            $result[$k]['id_relationship_category_question']=pk_encrypt($v['id_relationship_category_question']);
            $result[$k]['parent_relationship_category_id']=pk_encrypt($v['parent_relationship_category_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function order_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $update_array = array();
        $data = $data['data'];

        for($s=0;$s<count($data);$s++) {
            $id_question = pk_decrypt($data[$s]['id_question']);
            // if(!in_array($id_question,$this->session_user_master_contract_review_questions)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }

        for($s=0;$s<count($data);$s++)
        {
            $data[$s]['id_question']=pk_decrypt($data[$s]['id_question']);
            $update_array[] = array(
                'id_question' => $data[$s]['id_question'],
                'question_order' => $s
            );
        }

        if(!empty($update_array)){
            $this->Question_model->updateQuestionBacth($update_array);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function questionmasteroptions_get()
    {
        $data = $this->input->get();
        $result = $this->Question_model->getQuestionMasterOptions($data);
        foreach($result as $k=>$v){
            $result[$k]['id_question_type_option']=pk_encrypt($v['id_question_type_option']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
}