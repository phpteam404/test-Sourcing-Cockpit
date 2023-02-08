<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Tag extends REST_Controller
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
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
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
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $data['customer_id'] = $this->session_user_info->customer_id;
        // echo '<pre>'.print_r($this->session_user_info->id_user);exit;
        if(isset($data['tag_type']) && $data['tag_type']=='provider_tags'){
            $check_rag_tax_exitst = $this->User_model->check_record('tag',array('customer_id'=>$this->session_user_info->customer_id,'tag_type'=>'rag'));
            if(empty($check_rag_tax_exitst)){
                $all_tags = $this->User_model->check_record('tag',array('customer_id'=>$this->session_user_info->customer_id));
                $fixed_tags=array('Risk Profile','Approval Status','Financial Health');
                $fixed_tags_labels=array('label_1','label_2','label_3');
                foreach($fixed_tags as $i => $j){
                    $tag_id = $this->Tag_model->addTag(array(
                        'tag_order' => count($all_tags)+$i,
                        'tag_type' => 'rag',
                        'field_type' => '',
                        'customer_id' => $this->session_user_info->customer_id,
                        'created_by' => $this->session_user_info->id_user,
                        'status' => 1,
                        'created_on' => currentDate(),
                        'type'=>'provider_tags',
                        'is_fixed'=>1,
                        'label'=>$fixed_tags_labels[$i]
                    ));
            
                    $this->Tag_model->addTagLanguage(array(
                        'tag_id' => $tag_id,
                        'tag_text' => $j,
                        'language_id' => 1
                    ));
                    $fixed_tags_options=array('R','A','G','N/A');
                    foreach($fixed_tags_options as $n){
                        $tag_option_id = $this->Tag_model->addTagOption(array(
                            'tag_id' => $tag_id,
                            'created_by ' => $this->session_user_info->id_user,
                            'created_on' => currentDate()
                        ));
                        $this->Tag_model->addTagOptionLanguage(array(
                                'tag_option_id' => $tag_option_id,
                                'tag_option_name' => $n,
                                'language_id' => 1
                        ));
                    }

                }
            }
        }
        $result = $this->Tag_model->TagList($data);
        //echo '<pre>'.print_r($this->session_user_info->customer_id);exit;
        foreach($result as $k=>$v){
            if(isset($data['status']))
                $result[$k]['tag_options']=$this->Tag_model->getTagOptions(array('tag_id'=>$v['id_tag'],'status'=>$data['status']));
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['id_tag']=pk_encrypt($v['id_tag']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_tag_language']=pk_encrypt($v['id_tag_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['tag_id']=pk_encrypt($v['tag_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
            $result[$k]['business_unit_id']=pk_encrypt($v['business_unit_id']);
        }
        
        // print_r($result);exi
        usort($result, function ($item1, $item2) {
            return $item1['tag_order'] <=> $item2['tag_order'];
        });
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function tags_get()
    {
        $data = $this->input->get();
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
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
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $data['customer_id'] = $this->session_user_info->customer_id;
        $data['orderBy'] ='tag_order';
        $result = $this->Tag_model->TagList($data);
        foreach($result as $k=>$v){
            if(isset($data['status']))
                $result[$k]['tag_options']=$this->Tag_model->getTagOptions(array('tag_id'=>$v['id_tag'],'status'=>$data['status']));
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['id_tag']=pk_encrypt($v['id_tag']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_tag_language']=pk_encrypt($v['id_tag_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['tag_id']=pk_encrypt($v['tag_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result_new = array();
        // foreach($result as $k=>$v){
        //     //if($k % 4 == 0)
        //         $result_new[] = $v;
        // }
        // foreach($result as $k=>$v){
        //     //if($k % 4 != 0)
        //         $result_new[] = $v;
        // }

        $result_new_info = array();
        // foreach($result as $k => $v){
        //     if($k == 0 || $k == 4 || $k == 8 )
        //     $result_new_info[] = $v;
        // }
        // foreach($result as $k => $v){
        //     if($k == 1 || $k == 5 || $k == 9 )
        //     $result_new_info[] = $v;
        // }
        // foreach($result as $k => $v){
        //     if($k == 2 || $k == 6 || $k == 10 )
        //     $result_new_info[] = $v;
        // }
        // foreach($result as $k => $v){
        //     if($k == 3 || $k == 7 || $k == 11 )
        //     $result_new_info[] = $v;
        // }
        usort($result, function ($item1, $item2) {
            return $item1['tag_order'] <=> $item2['tag_order'];
        });
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result, 'contract_tags_info_page'=>$result_new_info);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function Groupedtags_get()
    {
        $data = $this->input->get();
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
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
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $data['customer_id'] = $this->session_user_info->customer_id;
        $data['orderBy'] ='tag_order';
        $tagLists = $this->Tag_model->TagList($data);
        foreach($tagLists as $k=>$v){
            if(isset($data['status']))
            {
                //$tagLists[$k]['tag_options']=$this->Tag_model->getTagOptions(array('tag_id'=>$v['id_tag'],'status'=>$data['status']));
                $tagLists[$k]['options']=$this->Tag_model->getTagOptions(array('tag_id'=>$v['id_tag'],'status'=>$data['status']));
            }
            $tagLists[$k]['created_by']=pk_encrypt($v['created_by']);
            $tagLists[$k]['id_tag']=pk_encrypt($v['id_tag']);
            $tagLists[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $tagLists[$k]['id_tag_language']=pk_encrypt($v['id_tag_language']);
            $tagLists[$k]['language_id']=pk_encrypt($v['language_id']);
            $tagLists[$k]['tag_id']=pk_encrypt($v['tag_id']);
            $tagLists[$k]['business_unit_id'] = pk_encrypt($v['business_unit_id']);
            $tagLists[$k]['bu_name'] = $v['bu_name'];
            $tagLists[$k]['selected_field'] = $v['selected_field'];
            $tagLists[$k]['multi_select'] = $v['multi_select'];
            $tagLists[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
   
        usort($tagLists, function ($item1, $item2) {
            return $item1['tag_order'] <=> $item2['tag_order'];
        });

        //grouping tags with business unit

        $groupTag = [];
        $businessUnitArray = array_unique(array_column($tagLists, 'business_unit_id'));
        $i=0;

        foreach($businessUnitArray as $buK=>$buV)
        {
            $groupTag[$i] = array(
                'business_unit_id' => $buV,
                'tag_details' =>[],
                'count' => 0,
                'count_without_rag' => 0
            );
            $count_without_rag = 0;
            foreach($tagLists as $tagK => $tagV)
            {
                if($tagV['business_unit_id'] == $buV)
                {
                    $groupTag[$i]['tag_details'][]=$tagLists[$tagK];
                    $groupTag[$i]['bu_name']=$tagLists[$tagK]['bu_name'];
                    $groupTag[$i]['status']=$tagLists[$tagK]['business_unit_status'];

                    if($tagLists[$tagK]['tag_type'] != 'rag')
                    {
                        $count_without_rag++;
                    }
                }
            }

            $groupTag[$i]['count'] = count($groupTag[$i]['tag_details']);
            $groupTag[$i]['count_without_rag'] = $count_without_rag;
            $i++;
        }
      
        $columns = array_column($groupTag, 'bu_name');
        array_multisort($columns, SORT_ASC, $groupTag);


        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$groupTag);
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
        //echo '<pre>'.print_r($data);exit;
        if(isset($data['id_tag'])) {
            $data['id_tag'] = pk_decrypt($data['id_tag']);            
        }
        if(isset($data['tag_id'])) {
            $data['tag_id'] = pk_decrypt($data['tag_id']);            
        }
        $result = $this->Tag_model->getTagInfo($data);
        if($result[0]['id_tag_option_language']!='') { //providing options as array
            $result[0]['id_tag_option_language'] = explode(',', $result[0]['id_tag_option_language']);
            $result[0]['option_name'] = explode(',', $result[0]['option_name']);
        }
        /*if single record is present then*/
        //echo '<pre>'.print_r($result);exit;
        foreach($result as $k=>$v){
            $result[$k]['multi_select']=(int)($v['multi_select']);
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['id_tag']=pk_encrypt($v['id_tag']);
            $result[$k]['business_unit_id']=pk_encrypt($v['business_unit_id']);
            $result[$k]['id_tag_language']=pk_encrypt($v['id_tag_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['tag_id']=pk_encrypt($v['tag_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
            if(is_array($result[$k]['id_tag_option_language'])) {
                foreach ($result[$k]['id_tag_option_language'] as $kl => $vl) {
                    $result[$k]['id_tag_option_language'][$kl] = pk_encrypt($vl);
                }
            }
            else {
                $result[$k]['id_tag_option_language']=pk_encrypt($v['id_tag_option_language']);

            }
            //echo '<pre>'.print_r($result[$k]['option_names']);exit;
            foreach($result[$k]['option_names'] as $ko=>$vo){
                $result[$k]['option_names'][$ko]['created_by']=pk_encrypt($vo['created_by']);
                $result[$k]['option_names'][$ko]['id_tag_option']=pk_encrypt($vo['id_tag_option']);
                $result[$k]['option_names'][$ko]['id_tag_option_language']=pk_encrypt($vo['id_tag_option_language']);
                $result[$k]['option_names'][$ko]['language_id']=pk_encrypt($vo['language_id']);
                $result[$k]['option_names'][$ko]['tag_id']=pk_encrypt($vo['tag_id']);
                $result[$k]['option_names'][$ko]['tag_option_id']=pk_encrypt($vo['tag_option_id']);
                $result[$k]['option_names'][$ko]['updated_by']=pk_encrypt($vo['updated_by']);
                $result[$k]['option_names'][$ko]['option_name']=$vo['tag_option_name'];
            }
        }
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
 
        $this->form_validator->add_rules('tag_text', array('required'=>$this->lang->line('tag_text_req')));
        $this->form_validator->add_rules('tag_type', array('required'=>$this->lang->line('tag_type_req')));
        if(isset($data['tag_type']) && $data['tag_type']!='input' && $data['tag_type']!='date' && $data['tag_type']!='selected')
        {
            $this->form_validator->add_rules('option_name', array('required'=>$this->lang->line('option_name_req')));
        }
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status')));
        if($data['tag_type'] == 'selected')
        {
            $this->form_validator->add_rules('selected_field', array('required'=>$this->lang->line('selected_field_is_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
        }

        if(isset($data['multi_select']))
        {
            if( $data['multi_select'] == 1 && ($data['type'] == "dropdown" || $data['type'] == "selected"))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('multi_select_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        
        $check_six_tags = $this->User_model->check_record('tag',array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'type'=>$data['type']));
        if(count($check_six_tags)>(NO_OF_TAGS - 1) && (int)$data['status']==1)
        $this->response(array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('tag_error')), 'data'=>'1'), REST_Controller::HTTP_OK);
        $all_tags = $this->User_model->check_record('tag',array('customer_id'=>$this->session_user_info->customer_id));
        $selected_field = NULL;
        if($data['tag_type'] == 'selected')
        {
            $selected_field = $data['selected_field'];
        }
        $tag_id = $this->Tag_model->addTag(array(
            'tag_order' => count($all_tags),
            'tag_type' => $data['tag_type'],
            'field_type' => isset($data['field_type'])?$data['field_type']:$data['field_type'],
            'customer_id' => $this->session_user_info->customer_id,
            'created_by' => $this->session_user_id,
            'status' => $data['status'],
            'created_on' => currentDate(),
            'type'=>$data['type'],
            'business_unit_id' => isset($data['business_unit_id']) ? $data['business_unit_id'] : NULL,
            'multi_select' => isset($data['multi_select']) ? $data['multi_select'] : 0,
            'selected_field' => $selected_field
        ));
        $this->Tag_model->addTagLanguage(array(
            'tag_id' => $tag_id,
            'tag_text' => $data['tag_text'],
            'language_id' => 1
        ));

        //if($data['tag_type']!='input' && $data['tag_type']!='date') { 
        if($data['tag_type']!='input' && $data['tag_type']!='date' && $data['tag_type']!='selected') {

            for($s=0;$s<count($data['option_name']);$s++)
            {

                if($data['tag_type']=='dropdown') { 
                    $tag_option_id = $this->Tag_model->addTagOption(array(
                        'tag_id' => $tag_id,
                        'created_by ' => $this->session_user_id,
                        'created_on' => currentDate()
                    ));
                    $this->Tag_model->addTagOptionLanguage(array(
                            'tag_option_id' => $tag_option_id,
                            'tag_option_name' => $data['option_name'][$s]['tag_option'],
                            'language_id' => 1
                    ));
                }
                else {
                    $tag_option_id = $this->Tag_model->addTagOption(array(
                        'tag_id' => $tag_id,
                        'created_by ' => $this->session_user_id,
                        'created_on' => currentDate()
                    ));
                    $this->Tag_model->addTagOptionLanguage(array(
                        'tag_option_id' => $tag_option_id,
                        'tag_option_name' => $data['tag_type']=='rag'?$data['option_name'][$s]['option']:$data['option_name'][$s]['tag_option'],
                        'language_id' => 1
                    ));
                }
            }
        }


        $result = array('status'=>TRUE, 'message' => $this->lang->line('tag_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        /*echo "<pre>";print_r($data);exit;*/
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_tag', array('required'=>$this->lang->line('tag_id_req')));
        $this->form_validator->add_rules('id_tag_language', array('required'=>$this->lang->line('tag_language_id_req')));
        $this->form_validator->add_rules('tag_text', array('required'=>$this->lang->line('tag_text_req')));
        $this->form_validator->add_rules('tag_type', array('required'=>$this->lang->line('tag_type_req')));
        if(isset($data['tag_type']) && $data['tag_type']!='input' && $data['tag_type']!='date' && $data['tag_type']!='selected')
        {
            $this->form_validator->add_rules('option_name', array('required'=>$this->lang->line('option_name_req')));
        }
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status')));
        if($data['tag_type'] == 'selected')
        {
            $this->form_validator->add_rules('selected_field', array('required'=>$this->lang->line('selected_field_is_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_tag'])) {
            $data['id_tag'] = pk_decrypt($data['id_tag']);
            /*if(!in_array($data['id_tag'],$this->session_user_master_contract_review_tags)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        if(isset($data['updated_by'])) {
            $data['created_by'] = $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
        }

        if(isset($data['multi_select']))
        {
            if( $data['multi_select'] == 1 && ($data['type'] == "dropdown" || $data['type'] == "selected"))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('multi_select_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['id_tag_language'])) $data['id_tag_language']=pk_decrypt($data['id_tag_language']);
        $check_six_tags = $this->User_model->check_record_adv('tag',array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'type'=>$data['type']),array('id_tag'=>$data['id_tag']));
        //echo '<pre>'.$this->db->last_query();exit;
        if(count($check_six_tags)>(NO_OF_TAGS - 1) && (int)$data['status']==1)
            $this->response(array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('tag_error')), 'data'=>'1'), REST_Controller::HTTP_OK);
        $selected_field = NULL;
        $oldTagDetails = $this->User_model->check_record("tag" , array('id_tag' => $data['id_tag']));
        if(($data['tag_type'] == "dropdown" || $data['tag_type'] == "selected" ) && (isset($data['multi_select']) && $data['multi_select'] == 0 && $oldTagDetails[0]['multi_select'] == 1))
        {
            //removing existing data from Tag (downgrading multi select to Single select)
            if($oldTagDetails[0]['type'] == "contract_tags")
            {
                $table = "contract_tags";
            }
            elseif($oldTagDetails[0]['type'] == "provider_tags")
            {
                $table = "provider_tags";
            }
            else
            {
                $table = "catalogue_tags";
            }
            $this->Tag_model->emptyingTagData(array('tag_id' => $data['id_tag'] , 'table' => $table ,'updateData' => array('tag_option' => 0 ,'tag_option_value' => '')));
        }
        if($data['tag_type'] == 'selected')
        {
            $selected_field = $data['selected_field'];
        }
        $this->Tag_model->updateTag(array(
            'id_tag' => $data['id_tag'],
            'tag_order' => isset($data['tag_order'])?$data['tag_order']:'1',
            'tag_type' => $data['tag_type'],
            'field_type' => isset($data['field_type'])?$data['field_type']:$data['field_type'],
            'status' => $data['status'],
            'updated_by' => $this->session_user_id,
            'updated_on' => currentDate(),
            'business_unit_id' => isset($data['business_unit_id']) ? $data['business_unit_id'] : NULL,
            'multi_select' => isset($data['multi_select']) ? $data['multi_select'] : 0,
            // 'selected_field' => $selected_field
        ));
        $this->User_model->update_data('contract_tags',array('status'=>$data['status']),array('tag_id'=>$data['id_tag']));
        $this->Tag_model->updateTagLanguage(array(
            'id_tag_language' => $data['id_tag_language'],
            'tag_text' => $data['tag_text'],
            'language_id' => 1
        ));

        //if($data['tag_type']!='input') { //input type tags don't have any options
        if($data['tag_type']!='input' && $data['tag_type']!='date' && $data['tag_type']!='selected') { //input type tags don't have any options


            for($s=0;$s<count($data['option_name']);$s++)
            {//echo '<pre>'.print_r($data);exit;
                if($data['tag_type']=='dropdown') { //if dropdown tag, tag options format is different from client side
                    if ($data['option_name'][$s]['type'] == 'update') {
                        $data['option_name'][$s]['id_tag_option']=pk_decrypt($data['option_name'][$s]['id_tag_option']);
                        $data['option_name'][$s]['id_tag_option_language']=pk_decrypt($data['option_name'][$s]['id_tag_option_language']);
                        $this->Tag_model->updateTagOption(array(
                            'id_tag_option' => $data['option_name'][$s]['id_tag_option'],
                            'updated_by ' => $this->session_user_id,
                            'updated_on' => currentDate()
                        ));

                        $this->Tag_model->updateTagOptionLanguage(array(
                            'id_tag_option_language' => $data['option_name'][$s]['id_tag_option_language'],
                            'tag_option_name' => $data['option_name'][$s]['tag_option'],
                            'language_id' => 1
                        ));
                    } else {
                        //echo '<pre>'.print_r($data['option_name']);exit;
                        $tag_option_id = $this->Tag_model->addTagOption(array(
                            'tag_id' => $data['id_tag'],
                            'created_by ' => $this->session_user_id,
                            'created_on' => currentDate()
                        ));

                        $this->Tag_model->addTagOptionLanguage(array(
                            'tag_option_id' => $tag_option_id,
                            'tag_option_name' => $data['option_name'][$s]['tag_option'],
                            'language_id' => 1
                        ));
                    }
                }
                else{           
                    //echo '<pre>'.print_r($data['option_name']);exit;         
                    if(isset($data['option_name'][$s]['id_tag_option'])){
                        $data['option_name'][$s]['id_tag_option']=pk_decrypt($data['option_name'][$s]['id_tag_option']);
                        $data['option_name'][$s]['id_tag_option_language']=pk_decrypt($data['option_name'][$s]['id_tag_option_language']);
                        $this->Tag_model->updateTagOption(array(
                            'id_tag_option' => $data['option_name'][$s]['id_tag_option'],
                            'updated_by ' => $this->session_user_id,
                            'updated_on' => currentDate()
                        ));
    
                        $this->Tag_model->updateTagOptionLanguage(array(
                            'id_tag_option_language' => $data['option_name'][$s]['id_tag_option_language'],
                            'tag_option_name' => $data['option_name'][$s]['tag_option'],
                            'language_id' => 1
                        ));
                    }
                }

            }

            if(isset($data['option_delete'])) { //for deleted options
                for ($s = 0; $s < count($data['option_delete']); $s++) {
                    $data['option_delete'][$s]['id_tag_option']=pk_decrypt($data['option_delete'][$s]['id_tag_option']);
                    //$data['option_delete'][$s]['id_tag_option_language']=pk_decrypt($data['option_delete'][$s]['id_tag_option_language']);
                    $this->Tag_model->updateTagOption(array(
                        'id_tag_option' => $data['option_delete'][$s]['id_tag_option'],
                        'status' => 0
                    ));
                    // $this->Tag_model->updateTagOptionLanguage(array(
                    //     'id_tag_option_language' => $data['option_delete'][$s]['id_tag_option_language'],
                    //     'status' => 0
                    // ));
                }
            }

        }

        

        $result = array('status'=>TRUE, 'message' => $this->lang->line('tag_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function updateRelationshipCategories_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_tag', array('required'=>$this->lang->line('tag_id_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        //$this->form_validator->add_rules('id_relationship_category_tag', array('required'=>$this->lang->line('id_relationship_category_tag_req')));
        $this->form_validator->add_rules('id_relationship_category', array('required'=>$this->lang->line('id_relationship_category_req')));
        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('updateRelationshipCategories_status_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_tag'])) {
            $data['id_tag'] = pk_decrypt($data['id_tag']);
            /*if(!in_array($data['id_tag'],$this->session_user_master_contract_review_tags)){
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
        if(isset($data['id_relationship_category_tag'])) $data['id_relationship_category_tag']=pk_decrypt($data['id_relationship_category_tag']);



        if(isset($data['id_relationship_category_tag'])){ //updating and adding new categories for this tags

                if($data['id_relationship_category_tag']==''){
                    $this->Tag_model->addRelationshipCategoryTag(array(
                        'relationship_category_id' => $data['id_relationship_category'],
                        'tag_id' => $data['id_tag'],
                        'status' => $data['status']
                    ));
                }
                else
                    $this->Tag_model->updateRelationshipCategoryTag(array(
                        'id_relationship_category_tag' => $data['id_relationship_category_tag'],
                        'relationship_category_id' => $data['id_relationship_category'],
                        'tag_id' => $data['id_tag'],
                        'status' => $data['status']
                    ));

        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('tag_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updateStatus_post() //disable or enable the tag
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_tag', array('required'=>$this->lang->line('tag_id_req')));
        $this->form_validator->add_rules('tag_status', array('required'=>$this->lang->line('tag_status_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }



        if($this->session_user_info->user_role_id>2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_tag'])) {
            $data['id_tag'] = pk_decrypt($data['id_tag']);
            /*if(!in_array($data['id_tag'],$this->session_user_master_contract_review_tags)){
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

        $this->Tag_model->updateTag(array(
            'id_tag' => $data['id_tag'],
            'tag_status' => $data['tag_status'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate()
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('tag_update'), 'data'=>'');
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
        if(isset($data['tag_id'])) {
            $data['tag_id'] = pk_decrypt($data['tag_id']);
            /*if($data['tag_id']>0 && !in_array($data['tag_id'],$this->session_user_master_contract_review_tags)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }*/
        }
        $data['status'] = 1;
        $data['customer_id'] = 0;
        if($this->session_user_info->user_role_id == 2){
            $data['customer_id'] = $this->session_user_info->customer_id;
        }
        $result = $this->Tag_model->getTagRelationshipCategory($data);
        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_category']=pk_encrypt($v['id_relationship_category']);
            $result[$k]['id_relationship_category_tag']=pk_encrypt($v['id_relationship_category_tag']);
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
            $id_tag = pk_decrypt($data[$s]['id_tag']);
        }

        for($s=0;$s<count($data);$s++)
        {
            $data[$s]['id_tag']=pk_decrypt($data[$s]['id_tag']);
            $update_array[] = array(
                'id_tag' => $data[$s]['id_tag'],
                'tag_order' => $s
            );
        }

        if(!empty($update_array)){
            $this->Tag_model->updateTagBacth($update_array);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function tagmasteroptions_get()
    {
        $data = $this->input->get();
        $result = $this->Tag_model->getTagMasterOptions($data);
        foreach($result as $k=>$v){
            $result[$k]['id_tag_type_option']=pk_encrypt($v['id_tag_type_option']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function fixedTaglabelUpdate_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_tag', array('required'=>$this->lang->line('tag_id_req')));
        $this->form_validator->add_rules('id_tag_language', array('required'=>$this->lang->line('tag_language_id_req')));
        $this->form_validator->add_rules('tag_text', array('required'=>$this->lang->line('tag_text_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id>2){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_tag'])) {
            $data['id_tag'] = pk_decrypt($data['id_tag']);
        }
        if(isset($data['id_tag_language'])) {
            $data['id_tag_language'] = pk_decrypt($data['id_tag_language']);
        }
        $tagdetails = $this->User_model->check_record('tag',array('id_tag'=>$data['id_tag']));
        if($tagdetails[0]['is_fixed'] !=1)
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('can_update_fixed_tag_only')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $updateResult = $this->User_model->update_data('tag_language',array('tag_text'=>$data['tag_text']),array('id_tag_language'=>$data['id_tag_language']));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('tag_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function getTagOptions_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_tag', array('required'=>$this->lang->line('tag_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!empty($data['id_tag'])){
            $data['id_tag']=pk_decrypt($data['id_tag']);
        }
        $tagDetails = $this->User_model->check_record('tag',array('id_tag'=>$data['id_tag']));
        // if($tagDetails)
        $query="SELECT id_tag_option,tol.tag_option_name FROM tag_option top LEFT JOIN tag_option_language tol on top.id_tag_option=tol.tag_option_id WHERE top.`status`=1 and top.tag_id=".$data['id_tag'];
        $getTagOptions=$this->User_model->custom_query($query);
        // print_r($getTagOptions);exit;?
        foreach($getTagOptions as $i =>$tagopt){
            $getTagOptions[$i]['id_tag_option']=pk_encrypt($getTagOptions[$i]['id_tag_option']);
            if($tagDetails[0]['tag_type'] == "rag")
            {
                switch($getTagOptions[$i]['tag_option_name'])
                {
                    case'R':
                        $getTagOptions[$i]['tag_option_display_name']="Red";
                        break;
                    case'A':
                        $getTagOptions[$i]['tag_option_display_name']="Amber";
                        break;
                    case'G':
                        $getTagOptions[$i]['tag_option_display_name']="Green";
                        break;
                    case'N/A':
                        $getTagOptions[$i]['tag_option_display_name']="N/A";
                        break;
                }
            }
            else
            {
                $getTagOptions[$i]['tag_option_display_name']=$getTagOptions[$i]['tag_option_name'];  
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$getTagOptions);
        $this->response($result, REST_Controller::HTTP_OK);
    }

}