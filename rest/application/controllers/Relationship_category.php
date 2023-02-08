<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Relationship_category extends REST_Controller
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
    public $session_user_customer_provider_relationship_classifictions= NULL;
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
    public $session_user_wadmin_provider_relationship_classifications=NULL;
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
        if(!in_array($this->session_user_info->user_role_id,array(1,2,3,4))){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'constructor');
            $this->response($result, REST_Controller::HTTP_NOT_FOUND);
        }
        $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_provider_relationship_categories=$this->Validation_model->getCustomerProviderRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_relationship_classifications=$this->Validation_model->getCustomerRelationshipClassifications(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_provider_relationship_classifications=$this->Validation_model->getCustomerProviderRelationshipClassifications(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
        $this->session_user_wadmin_relationship_classifications=$this->Validation_model->getCustomerRelationshipClassifications(array('customer_id'=>array(0)));
    }

    public function list_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        //$data['relationship_category_status'] = 1;
        if(isset($data['can_review']))
            $data['can_review']= 1;
        $result = $this->Relationship_category_model->RelationshipCategoryList($data);
        // print_r($data);
        // echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            if(strlen($v['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result['data'][$k]['relationship_category_short_name'] = $v['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $result['data'][$k]['created_by'] = pk_encrypt($v['created_by']);
            $result['data'][$k]['customer_id'] = pk_encrypt($v['customer_id']);
            $result['data'][$k]['id_relationship_category'] = pk_encrypt($v['id_relationship_category']);
            $result['data'][$k]['id_relationship_category_language'] = pk_encrypt($v['id_relationship_category_language']);
            $result['data'][$k]['language_id'] = pk_encrypt($v['language_id']);
            $result['data'][$k]['parent_relationship_category_id'] = pk_encrypt($v['parent_relationship_category_id']);
            $result['data'][$k]['relationship_category_id'] = pk_encrypt($v['relationship_category_id']);
            $result['data'][$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }
        $graph = $this->relationCategoryGraph($result,$data['customer_id']);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records'], 'graph'=> $graph));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    
    public function relationCategoryGraph($data,$customer_id){
        if(isset($data['data']) && count($data['data']>0)){
            $data = $data['data'];
            $quadrantLabelTR = $quadrantLabelTL = $quadrantLabelBL = $quadrantLabelBR = ' -- ';
            foreach($data as $k=>$v){
                if($v['relationship_category_quadrant'] == 'Q1'){
                    if($v['relationship_category_status'] == '1')
                        $quadrantLabelTR = $v['relationship_category_name'];
                }
                if($v['relationship_category_quadrant'] == 'Q2'){
                    if($v['relationship_category_status'] == '1')
                        $quadrantLabelTL = $v['relationship_category_name'];
                }
                if($v['relationship_category_quadrant'] == 'Q3'){
                    if($v['relationship_category_status'] == '1')
                        $quadrantLabelBL = $v['relationship_category_name'];
                }
                if($v['relationship_category_quadrant'] == 'Q4'){
                    if($v['relationship_category_status'] == '1')
                        $quadrantLabelBR = $v['relationship_category_name'];
                }
            }

            $xaxis = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id));//echo $this->db->last_query();exit;
            if($xaxis && is_array($xaxis) && isset($xaxis[0]['classification_position'])){
                foreach($xaxis as $k=>$v){
                    if($v['classification_position'] == 'x'){
                        $xaxis = $v['is_visible'];
                    }
                    if($v['classification_position'] == 'y'){
                        $yaxis = $v['is_visible'];
                    }
                }
            }

            if($xaxis == '0'){
                $left = '';
                $right = '';
            } else {
                $left = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id, 'classification_position' => 'left','classification_status'=>1));
                if($left && is_array($left) && isset($left[0]['classification_name'])){
                    $left = $left[0]['classification_name'];
                }
                $right = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id, 'classification_position' => 'right','classification_status'=>1));
                if($right && is_array($right) && isset($right[0]['classification_name'])){
                    $right = $right[0]['classification_name'];
                }
            }

            if($yaxis == '0'){
                $high = '';
                $low = '';
            } else {
                $low = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id, 'classification_position' => 'low','classification_status'=>1));
                if($low && is_array($low) && isset($low[0]['classification_name'])){
                    $low = $low[0]['classification_name'];
                }
                $high = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id, 'classification_position' => 'high','classification_status'=>1));
                if($high && is_array($high) && isset($high[0]['classification_name'])){
                    $high = $high[0]['classification_name'];
                }
            }

            $result = array(
                'chart' =>  array(
                    "xAxisMinValue" =>  "0",
                    "xAxisMaxValue" =>  100,
                    "yAxisMinValue" =>  0,
                    "yAxisMaxValue" =>  100,
                    "plotFillAlpha" => 80,
                    "showYAxisvalue" => "0",
                    "numDivlines" =>  "0",
                    "showValues" => "0",
                    "showTrendlineLabels" =>  "0",
                    "quadrantLabelTL" =>  $quadrantLabelTL,
                    "quadrantLabelTR" =>  $quadrantLabelTR,
                    "quadrantLabelBL" =>  $quadrantLabelBL,
                    "quadrantLabelBR" =>  $quadrantLabelBR,
                    "quadrantLabelFontBold" => "1",
                    "quadrantLabelFontSize" => "11",
                    "quadrantLabelFont" => "verdana",
                    "toolTipBgColor" =>  "#ECECEC",
                    "toolTipBorderColor" =>  "#000",
                    "drawQuadrant"  =>  "1",
                    "quadrantXVal" =>  "50",
                    "quadrantYVal" =>  "50",
                    "quadrantLineAlpha"  =>  "50",
                    "quadrantLineThickness"  =>  "1",
                    "theme" =>  "fint",
                    "showHoverEffect" => '0',
                    'maxLabelWidthPercent ' => '70'
                ),
                "categories" => array(
                    "category" => array(
                        array(
                            "label" =>  ' ',
                            "x" =>  "0"
                        ),
                        array(
                            "label" =>  ' ',
                            "x" =>  "100"
                        )
                    )
                ),
                "dataset" => array(
                    array(
                        "color" => "#00aee4",
                        "data" =>  []
                    )
                ),
                "classficationRelation" => array(
                    'left' => $left,
                    'right' => $right,
                    'low' => $low,
                    'high' => $high,
                )
            );

            return $result;
        }
    }
    public function providerRelationCategoryGraph($data,$customer_id){
        if(isset($data['data']) && count($data['data']>0)){
            $data = $data['data'];
            $quadrantLabelTR = $quadrantLabelTL = $quadrantLabelBL = $quadrantLabelBR = ' -- ';
            foreach($data as $k=>$v){
                if($v['provider_relationship_category_quadrant'] == 'Q1'){
                    if($v['provider_relationship_category_status'] == '1')
                        $quadrantLabelTR = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q2'){
                    if($v['provider_relationship_category_status'] == '1')
                        $quadrantLabelTL = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q3'){
                    if($v['provider_relationship_category_status'] == '1')
                        $quadrantLabelBL = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q4'){
                    if($v['provider_relationship_category_status'] == '1')
                        $quadrantLabelBR = $v['relationship_category_name'];
                }
            }
            $xaxis = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $customer_id));//echo $this->db->last_query();exit;
            // $xaxis = $this->Relationship_category_model->getRelationshipClassification(array('customer_id' => $customer_id));
            $xaxis=$xaxis['data'];
            if($xaxis && is_array($xaxis) && isset($xaxis[0]['classification_position'])){
                foreach($xaxis as $k=>$v){
                    if($v['classification_position'] == 'x'){
                        $xaxis = $v['is_visible'];
                    }
                    if($v['classification_position'] == 'y'){
                        $yaxis = $v['is_visible'];
                    }
                }
            }

            if($xaxis == '0'){
                $left = '';
                $right = '';
            } else {
                $left = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $customer_id, 'classification_position' => 'left','classification_status'=>1));//echo $this->db->last_query();exit;
                $left=$left['data'];
                if($left && is_array($left) && isset($left[0]['classification_name'])){
                    $left = $left[0]['classification_name'];
                }
                $right = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $customer_id, 'classification_position' => 'right','classification_status'=>1));
                $right=$right['data'];
                if($right && is_array($right) && isset($right[0]['classification_name'])){
                    $right = $right[0]['classification_name'];
                }
            }

            if($yaxis == '0'){
                $high = '';
                $low = '';
            } else {
                $low = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $customer_id, 'classification_position' => 'low','classification_status'=>1,''));
                $low=$low['data'];
                if($low && is_array($low) && isset($low[0]['classification_name'])){
                    $low = $low[0]['classification_name'];
                }
                $high = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $customer_id, 'classification_position' => 'high','classification_status'=>1));$high=$high['data'];
                if($high && is_array($high) && isset($high[0]['classification_name'])){
                    $high = $high[0]['classification_name'];
                }
            }

            $result = array(
                'chart' =>  array(
                    "xAxisMinValue" =>  "0",
                    "xAxisMaxValue" =>  100,
                    "yAxisMinValue" =>  0,
                    "yAxisMaxValue" =>  100,
                    "plotFillAlpha" => 80,
                    "showYAxisvalue" => "0",
                    "numDivlines" =>  "0",
                    "showValues" => "0",
                    "showTrendlineLabels" =>  "0",
                    "quadrantLabelTL" =>  $quadrantLabelTL,
                    "quadrantLabelTR" =>  $quadrantLabelTR,
                    "quadrantLabelBL" =>  $quadrantLabelBL,
                    "quadrantLabelBR" =>  $quadrantLabelBR,
                    "quadrantLabelFontBold" => "1",
                    "quadrantLabelFontSize" => "11",
                    "quadrantLabelFont" => "verdana",
                    "toolTipBgColor" =>  "#ECECEC",
                    "toolTipBorderColor" =>  "#000",
                    "drawQuadrant"  =>  "1",
                    "quadrantXVal" =>  "50",
                    "quadrantYVal" =>  "50",
                    "quadrantLineAlpha"  =>  "50",
                    "quadrantLineThickness"  =>  "1",
                    "theme" =>  "fint",
                    "showHoverEffect" => '0',
                    'maxLabelWidthPercent ' => '70'
                ),
                "categories" => array(
                    "category" => array(
                        array(
                            "label" =>  ' ',
                            "x" =>  "0"
                        ),
                        array(
                            "label" =>  ' ',
                            "x" =>  "100"
                        )
                    )
                ),
                "dataset" => array(
                    array(
                        "color" => "#00aee4",
                        "data" =>  []
                    )
                ),
                "classficationRelation" => array(
                    'left' => $left,
                    'right' => $right,
                    'low' => $low,
                    'high' => $high,
                )
            );

            return $result;
        }
    }
    public function add_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('relationship_category_quadrant', array('required'=>$this->lang->line('relationship_category_quadrant_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
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
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $check_duplicate = $this->Relationship_category_model->getRelationshipCategory(array('relationship_category_quadrant' => $data['relationship_category_quadrant'],'relationship_category_status'=>1));
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('relationship_category_quadrant_error' => $this->lang->line('relationship_category_quadrant_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $relationship_category_id = $this->Relationship_category_model->addRelationshipCategory(array(
            'relationship_category_quadrant' => $data['relationship_category_quadrant'],
            'created_by' => $data['created_by'],
            'created_on' => currentDate()
        ));

        $this->Relationship_category_model->addRelationshipCategoryLanguage(array(
            'relationship_category_id' => $relationship_category_id,
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_category_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_relationship_category', array('required'=>$this->lang->line('relationship_category_id_rey')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_relationship_category'])) {
            $data['id_relationship_category'] = pk_decrypt($data['id_relationship_category']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_category'] ,$this->session_user_customer_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_category']>0 && !in_array($data['id_relationship_category'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_category_not'])) {
            $data['id_relationship_category_not'] = pk_decrypt($data['id_relationship_category_not']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_category_not'] ,$this->session_user_customer_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_category_not']>0 && !in_array($data['id_relationship_category_not'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Relationship_category_model->getRelationshipCategory($data);
        foreach($result as $k=>$v){
            $result[$k]['id_relationship_category']=pk_encrypt($v['id_relationship_category']);
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
            $result[$k]['parent_relationship_category_id']=pk_encrypt($v['parent_relationship_category_id']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_category_language']=pk_encrypt($v['id_relationship_category_language']);
            $result[$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);

        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_relationship_category', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('id_relationship_category_language', array('required'=>$this->lang->line('relationship_category_language_id_req')));
        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('relationship_category_quadrant', array('required'=>$this->lang->line('relationship_category_quadrant_req')));
        $this->form_validator->add_rules('relationship_category_status', array('required'=>$this->lang->line('relationship_category_status_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_relationship_category'])) {
            $data['id_relationship_category'] = pk_decrypt($data['id_relationship_category']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_category'] ,$this->session_user_customer_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_category']>0 &&  !in_array($data['id_relationship_category'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_category_language'])) $data['id_relationship_category_language']=pk_decrypt($data['id_relationship_category_language']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
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
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = 0;
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }

        //checking for duplicate relationship_category_quadrant
        $check_duplicate = $this->Relationship_category_model->getRelationshipCategory(array('id_relationship_category_not' => $data['id_relationship_category'],'relationship_category_quadrant' => $data['relationship_category_quadrant'],'relationship_category_status'=>1,'customer_id' => $customer_id));

        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('relationship_category_quadrant_error' => $this->lang->line('relationship_category_quadrant_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->Relationship_category_model->updateRelationshipCategory(array(
            'id_relationship_category' => $data['id_relationship_category'],
            'relationship_category_quadrant' => $data['relationship_category_quadrant'],
            'relationship_category_status' => $data['relationship_category_status'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate()
        ));

        $this->Relationship_category_model->updateRelationshipCategoryLanguage(array(
            'id_relationship_category_language' => $data['id_relationship_category_language'],
            'relationship_category_id' => $data['id_relationship_category'],
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_category_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }



    public function updateProviderCategories_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_provider_relationship_category', array('required'=>$this->lang->line('provider_relationship_category_id_req')));
        $this->form_validator->add_rules('id_provider_relationship_category_language', array('required'=>$this->lang->line('Provider_relationship_category_language_id_req')));
        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('provider_relationship_category_quadrant', array('required'=>$this->lang->line('provider_relationship_category_quadrant_req')));
        $this->form_validator->add_rules('provider_relationship_category_status', array('required'=>$this->lang->line('provider_relationship_category_status_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['customer_id'] = pk_decrypt($data['customer_id']);
        if(isset($data['id_provider_relationship_category'])) {
            $data['id_provider_relationship_category'] = pk_decrypt($data['id_provider_relationship_category']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['id_provider_relationship_category'] ,$this->session_user_customer_relationship_categories)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
            if($this->session_user_info->user_role_id==1 && $data['id_provider_relationship_category']>0 &&  !in_array($data['id_provider_relationship_category'],$this->session_user_wadmin_relationship_categories)  && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider_relationship_category_language'])) $data['id_provider_relationship_category_language']=pk_decrypt($data['id_provider_relationship_category_language']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
           
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id'] && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)  && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = 0;
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }
        //checking for duplicate relationship_category_quadrant
        $check_duplicate = $this->Relationship_category_model->getProviderRelationshipCategory(array('id_provider_relationship_category_not' => $data['id_provider_relationship_category'],'provider_relationship_category_quadrant' => $data['provider_relationship_category_quadrant'],'provider_relationship_category_status'=>1,'customer_id' => $customer_id));

        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('relationship_category_quadrant_error' => $this->lang->line('relationship_category_quadrant_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->Relationship_category_model->updateProviderReltionshipCategory(array(
            'id_provider_relationship_category' => $data['id_provider_relationship_category'],
            'provider_relationship_category_quadrant' => $data['provider_relationship_category_quadrant'],
            'provider_relationship_category_status' => $data['provider_relationship_category_status'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate()
        ));
        $this->Relationship_category_model->updateProviderRelationshipCategoryLanguage(array(
            'id_provider_relationship_category_language' => $data['id_provider_relationship_category_language'],
            'provider_relationship_category_id' => $data['id_provider_relationship_category'],
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));
        // echo $this->db->last_query();exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_category_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function addProviderCategories_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('provider_relationship_category_quadrant', array('required'=>$this->lang->line('provider_relationship_category_quadrant_req')));
        // $this->form_validator->add_rules('provider_relationship_category_status', array('required'=>$this->lang->line('provider_relationship_category_status_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        
        
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            // if($data['created_by']!=$this->session_user_id){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = 0;
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }

        //checking for duplicate relationship_category_quadrant
        $check_duplicate = $this->Relationship_category_model->getProviderRelationshipCategory(array('provider_relationship_category_quadrant' => $data['provider_relationship_category_quadrant'],'provider_relationship_category_status'=>1,'customer_id' => $customer_id));
        // echo $this->db->last_query();exit;
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('relationship_category_quadrant_error' => $this->lang->line('relationship_category_quadrant_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
       $providerCategoryarray =array(
            'provider_relationship_category_quadrant' => $data['provider_relationship_category_quadrant'],
            'provider_relationship_category_status' => isset($data['provider_relationship_category_status'])?$data['provider_relationship_category_status']:1,
            'created_by' => $data['created_by'],
            'created_on' => currentDate(),
            'customer_id'=>$customer_id,
            'can_review'=>1
        );
        $provider_category_id=$this->User_model->insert_data('provider_relationship_category',$providerCategoryarray);
        // echo $this->db->last_query();exit;
        $this->User_model->insert_data('provider_relationship_category_language',array(
            'provider_relationship_category_id' => $provider_category_id,
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_category_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function classificationList_get()
    {
        $data = $this->input->get();
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
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
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id_not'])) {
            $data['parent_classification_id_not'] = pk_decrypt($data['parent_classification_id_not']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id_not'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id_not']>0 && !in_array($data['parent_classification_id_not'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data); //helper function for ordering smart table grid options
        $data['parent_classification_id'] = 0; //for parent classifications
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        $result = $this->Relationship_category_model->RelationshipClassificationList($data);//echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['data'][$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result['data'][$k]['id_relationship_classification']=pk_encrypt($v['id_relationship_classification']);
            $result['data'][$k]['id_relationship_classification_language']=pk_encrypt($v['id_relationship_classification_language']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
            $result['data'][$k]['parent_classification_id']=pk_encrypt($v['parent_classification_id']);
            $result['data'][$k]['parent_relationship_classification_id']=pk_encrypt($v['parent_relationship_classification_id']);
            $result['data'][$k]['relationship_classification_id']=pk_encrypt($v['relationship_classification_id']);
            $result['data'][$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function classificationAdd_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('classification_name', array('required'=>$this->lang->line('classification_name_req')));
        $this->form_validator->add_rules('classification_position', array('required'=>$this->lang->line('classification_position_req')));
        $this->form_validator->add_rules('is_visible', array('required'=>$this->lang->line('is_visible_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $check_duplicate = $this->Relationship_category_model->getRelationshipClassification(array('classification_position' => $data['classification_position'],'classification_status'=>1));
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('classification_position_error' => $this->lang->line('classification_position_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $relationship_classification_id = $this->Relationship_category_model->addRelationshipClassification(array(
            'classification_position' => $data['classification_position'],
            'parent_classification_id' => isset($data['parent_classification_id'])?$data['parent_classification_id']:'0',
            'is_visible' => $data['is_visible'],
            'created_by' => $data['created_by'],
            'created_on' => currentDate(),
        ));

        $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
            'relationship_classification_id' => $relationship_classification_id,
            'classification_name' => $data['classification_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_classification_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function classificationChild_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('parent_classification_id', array('required'=>$this->lang->line('parent_classification_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_classification_not'])) {
            $data['id_relationship_classification_not'] = pk_decrypt($data['id_relationship_classification_not']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_classification_not'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_classification_not']>0 && !in_array($data['id_relationship_classification_not'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Relationship_category_model->getClassificationValue($data);//echo $this->db->last_query();exit;
        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_classification']=pk_encrypt($v['id_relationship_classification']);
            $result[$k]['id_relationship_classification_language']=pk_encrypt($v['id_relationship_classification_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['parent_classification_id']=pk_encrypt($v['parent_classification_id']);
            $result[$k]['parent_relationship_classification_id']=pk_encrypt($v['parent_relationship_classification_id']);
            $result[$k]['relationship_classification_id']=pk_encrypt($v['relationship_classification_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    
    //This Service is for prepopulating the manage info
    public function providerclassificationChild_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('parent_classification_id', array('required'=>$this->lang->line('parent_classification_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
            $data['customer_id'] = pk_decrypt($data['customer_id']);
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_provider_relationship_classifications) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_provider_relationship_classifications) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider_relationship_classification_not'])) {
            $data['id_provider_relationship_classification_not'] = pk_decrypt($data['id_provider_relationship_classification_not']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_provider_relationship_classification_not'] ,$this->session_user_customer_provider_relationship_classifications) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_provider_relationship_classification_not']>0 && !in_array($data['id_provider_relationship_classification_not'],$this->session_user_wadmin_provider_relationship_classifications) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Relationship_category_model->getProviderClassificationValue($data);
        // echo ''.$this->db->last_query(); exit;
        foreach($result as $k=>$v){
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_provider_relationship_classification']=pk_encrypt($v['id_provider_relationship_classification']);
            $result[$k]['id_provider_relationship_classification_language']=pk_encrypt($v['id_provider_relationship_classification_language']);
            $result[$k]['language_id']=pk_encrypt($v['language_id']);
            $result[$k]['parent_classification_id']=pk_encrypt($v['parent_classification_id']);
            $result[$k]['parent_provider_relationship_classification_id']=pk_encrypt($v['parent_provider_relationship_classification_id']);
            $result[$k]['relationship_classification_id']=pk_encrypt($v['relationship_classification_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function classificationChildAdd_post()
    {
        $data = $this->input->post();
        //echo "<pre>"; print_r($data); exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('parent_classification_id', array('required'=>$this->lang->line('parent_classification_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data['classification']);$s++)
        {
            if(isset($data['classification'][$s]['id_relationship_classification'])) $data['classification'][$s]['id_relationship_classification']=pk_decrypt($data['classification'][$s]['id_relationship_classification']);
            if(isset($data['classification'][$s]['id_relationship_classification']) && $data['classification'][$s]['id_relationship_classification']!=0){
                $relationship_classification_id = $this->Relationship_category_model->updateRelationshipClassification(array(
                    'id_relationship_classification' => $data['classification'][$s]['id_relationship_classification'],
                    'classification_position' => $data['classification'][$s]['classification_position'],
                    'parent_classification_id' => $data['parent_classification_id'],
                    'created_by' => $data['created_by'],
                    'created_on' => currentDate(),
                ));
            }
            else{
                $relationship_classification_id = $this->Relationship_category_model->addRelationshipClassification(array(
                    'classification_position' => $data['classification'][$s]['classification_position'],
                    'parent_classification_id' => $data['parent_classification_id'],
                    'created_by' => $data['created_by'],
                    'created_on' => currentDate(),
                ));
            }
            if(isset($data['classification'][$s]['id_relationship_classification_language'])) $data['classification'][$s]['id_relationship_classification_language']=pk_decrypt($data['classification'][$s]['id_relationship_classification_language']);
            if(isset($data['classification'][$s]['id_relationship_classification_language']) && $data['classification'][$s]['id_relationship_classification_language']!=0){
                $this->Relationship_category_model->updateRelationshipClassificationLanguage(array(
                    'id_relationship_classification_language' => $data['classification'][$s]['id_relationship_classification_language'],
                    'classification_name' => $data['classification'][$s]['classification_name'],
                    'language_id' => 1
                ));
            }
            else {
                $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
                    'relationship_classification_id' => $relationship_classification_id,
                    'classification_name' => $data['classification'][$s]['classification_name'],
                    'language_id' => 1
                ));
            }
        }


        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_classification_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

   // This service is for manage icon addition
    public function providerclassificationChildAdd_post()
    {
        $data = $this->input->post();
        // echo "<pre>"; print_r($data); exit;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('parent_classification_id', array('required'=>$this->lang->line('parent_classification_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'],$this->session_user_customer_provider_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            // print_r($this->session_user_wadmin_provider_relationship_classifications);exit;
            if(!empty($this->session_user_wadmin_provider_relationship_classifications) &&$this->session_user_info->user_role_id!=1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_provider_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        for($s=0;$s<count($data['classification']);$s++)
        {
            if(isset($data['classification'][$s]['id_provider_relationship_classification'])) $data['classification'][$s]['id_provider_relationship_classification']=pk_decrypt($data['classification'][$s]['id_provider_relationship_classification']);
            if(isset($data['classification'][$s]['id_provider_relationship_classification']) && $data['classification'][$s]['id_provider_relationship_classification']!=0){
                $relationship_classification_id = $this->Relationship_category_model->updateProviderReltionshipclassification(array(
                    'id_provider_relationship_classification' => $data['classification'][$s]['id_provider_relationship_classification'],
                    'classification_position' => $data['classification'][$s]['classification_position'],
                    'parent_classification_id' => $data['parent_classification_id'],
                    'created_by' => $data['created_by'],
                    'created_on' => currentDate(),
                ));
            }
            else{
                $relationship_classification_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                    'classification_position' => $data['classification'][$s]['classification_position'],
                    'parent_classification_id' => $data['parent_classification_id'],
                    'created_by' => $data['created_by'],
                    'created_on' => currentDate(),
                ));
            }
            if(isset($data['classification'][$s]['id_provider_relationship_classification_language'])) $data['classification'][$s]['id_provider_relationship_classification_language']=pk_decrypt($data['classification'][$s]['id_provider_relationship_classification_language']);
            if(isset($data['classification'][$s]['id_provider_relationship_classification_language']) && $data['classification'][$s]['id_provider_relationship_classification_language']!=0){
                $this->Relationship_category_model->updateProviderRelationshipClassificationLanguage(array(
                    'id_provider_relationship_classification_language' => $data['classification'][$s]['id_provider_relationship_classification_language'],
                    'classification_name' => $data['classification'][$s]['classification_name'],
                    'language_id' => 1
                ));
            }
            else {
                $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                    'provider_relationship_classification_id' => $relationship_classification_id,
                    'classification_name' => $data['classification'][$s]['classification_name'],
                    'language_id' => 1
                ));
                //echo ''.$this->db->last_query(); exit;
            }
        }


        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_classification_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function classificationUpdate_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_relationship_classification', array('required'=>$this->lang->line('relationship_classification_id_req')));
        $this->form_validator->add_rules('id_relationship_classification_language', array('required'=>$this->lang->line('relationship_classification_language_id_req')));
        $this->form_validator->add_rules('classification_name', array('required'=>$this->lang->line('classification_name_req')));
        $this->form_validator->add_rules('classification_position', array('required'=>$this->lang->line('classification_position_req')));
        $this->form_validator->add_rules('classification_status', array('required'=>$this->lang->line('classification_status_req')));
        $this->form_validator->add_rules('is_visible', array('required'=>$this->lang->line('is_visible_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_relationship_classification'])) {
            $data['id_relationship_classification'] = pk_decrypt($data['id_relationship_classification']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_classification'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_classification']>0 && !in_array($data['id_relationship_classification'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_classification_language'])) $data['id_relationship_classification_language']=pk_decrypt($data['id_relationship_classification_language']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
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
            if($this->session_user_info->user_role_id==1 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($data['parent_classification_id']>0 && $this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['parent_classification_id']>0 && $this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = '0';
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }

        $check_duplicate = $this->Relationship_category_model->getRelationshipClassification(array('id_relationship_classification_not'=>$data['id_relationship_classification'],'classification_position' => $data['classification_position'],'classification_status'=>1,'customer_id' => $customer_id));
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('classification_position_error' => $this->lang->line('classification_position_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->Relationship_category_model->updateRelationshipClassification(array(
            'id_relationship_classification' => $data['id_relationship_classification'],
            'classification_position' => $data['classification_position'],
            'parent_classification_id' => isset($data['parent_classification_id'])?$data['parent_classification_id']:'0',
            'classification_status' => $data['classification_status'],
            'is_visible' => $data['is_visible'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
        ));

        $this->Relationship_category_model->updateRelationshipClassificationLanguage(array(
            'id_relationship_classification_language' => $data['id_relationship_classification_language'],
            'classification_name' => $data['classification_name'],
            'language_id' => 1
        ));
        //echo $this->db->last_query(); exit;

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_classification_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }


    //This service is for updateing the provider Classifications
    public function providerclassificationUpdate_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_provider_relationship_classification', array('required'=>$this->lang->line('provider_classification_id_req')));
        $this->form_validator->add_rules('id_provider_relationship_classification_language', array('required'=>$this->lang->line('provider_classification_language_id_req')));
        $this->form_validator->add_rules('classification_position', array('required'=>$this->lang->line('classification_name_req')));
        $this->form_validator->add_rules('classification_status', array('required'=>$this->lang->line('classification_status_req')));
        $this->form_validator->add_rules('is_visible', array('required'=>$this->lang->line('is_visible_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['customer_id'] = pk_decrypt($data['customer_id']);
        //print_r($this->session_user_info->user_role_id);
       //print_r($data['id_provider_relationship_classification']); exit;
        if(isset($data['id_provider_relationship_classification'])) {
            $data['id_provider_relationship_classification'] = pk_decrypt($data['id_provider_relationship_classification']);
            //print_r($this->session_user_customer_provider_relationship_classifications); exit;
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_provider_relationship_classification'] ,$this->session_user_customer_provider_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_provider_relationship_classification']>0 && !in_array($data['id_provider_relationship_classification'],$this->session_user_wadmin_provider_relationship_classifications) && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'45');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider_relationship_classification_language'])) $data['id_provider_relationship_classification_language']=pk_decrypt($data['id_provider_relationship_classification_language']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id'] && $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)&& $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['parent_provider_relationship_classification_id'])) {
            $data['parent_provider_relationship_classification_id'] = pk_decrypt($data['parent_provider_relationship_classification_id']);
            if($data['parent_provider_relationship_classification_id']>0 && $this->session_user_info->user_role_id!=1 && !in_array($data['parent_provider_relationship_classification_id'] ,$this->session_user_customer_provider_relationship_classifications)&& $data['customer_id']>0){
                // $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'a');
                // $this->response($result, REST_Controller::HTTP_OK);
            }
            if($data['parent_provider_relationship_classification_id']>0 && $this->session_user_info->user_role_id==1 && $data['parent_provider_relationship_classification_id']>0 && !in_array($data['parent_provider_relationship_classification_id'],$this->session_user_wadmin_provider_relationship_classifications)&& $data['customer_id']>0){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'c');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = '0';
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }

        $check_duplicate = $this->Relationship_category_model->getProviderRelationshipClassification(array('id_provider_relationship_classification_not'=>$data['id_provider_relationship_classification'],'classification_position' => $data['classification_position'],'classification_status'=>1,'customer_id' => $customer_id));
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('provider_classification_position_error' => $this->lang->line('provider_classification_position_duplicate')),'data'=>'g');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->Relationship_category_model->updateProviderRelationshipClassifiction(array(
            'id_provider_relationship_classification' => $data['id_provider_relationship_classification'],
            'classification_position' => $data['classification_position'],
            'parent_provider_relationship_classification_id' => isset($data['parent_provider_relationship_classification_id'])?$data['parent_provider_relationship_classification_id']:'0',
            'classification_status' => $data['classification_status'],
            'is_visible' => $data['is_visible'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
        ));

        $this->Relationship_category_model->updateProviderRelationshipClassificationLanguage(array(
            'id_provider_relationship_classification_language' => $data['id_provider_relationship_classification_language'],
            'classification_name' => $data['classification_name'],
            'language_id' => 1
        ));
        // echo $this->db->last_query();exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_category_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function providerclassificationAdd_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('classification_position', array('required'=>$this->lang->line('classification_name_req')));
        // $this->form_validator->add_rules('classification_status', array('required'=>$this->lang->line('classification_status_req')));
        $this->form_validator->add_rules('is_visible', array('required'=>$this->lang->line('is_visible_req')));
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
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_id = '0';
        if(isset($data['customer_id'])){ $customer_id = $data['customer_id']; }

        $check_duplicate = $this->Relationship_category_model->getProviderRelationshipClassification(array('classification_position' => $data['classification_position'],'classification_status'=>1,'customer_id' => $customer_id));
        if(!empty($check_duplicate)){
            $result = array('status'=>FALSE,'error'=>array('provider_classification_position_error' => $this->lang->line('provider_classification_position_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $classification_id=$this->User_model->insert_data('provider_relationship_classification',array(
            'classification_position' => $data['classification_position'],
            'parent_provider_relationship_classification_id' => isset($data['parent_provider_relationship_classification_id'])?$data['parent_provider_relationship_classification_id']:'0',
            'parent_classification_id'=>0,
            'is_visible' => $data['is_visible'],
            'created_by' => pk_decrypt($data['created_by']),
            'created_on' => currentDate(),
            'customer_id'=>$customer_id
        ));
        $this->User_model->insert_data('provider_relationship_classification_language',array(
            'provider_relationship_classification_id' => $classification_id,
            'classification_name' => $data['classification_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_classification_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }


    public function classification_delete()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_relationship_classification', array('required'=>$this->lang->line('relationship_classification_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_relationship_classification'])) {
            $data['id_relationship_classification'] = pk_decrypt($data['id_relationship_classification']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_classification'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_classification']>0 && !in_array($data['id_relationship_classification'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Relationship_category_model->deleteClassificationLanguage($data);
        $this->Relationship_category_model->deleteClassification($data);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_classification_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function addnrcategory_post(){
        // Adds a non review category
        // This category contracts cannot be reviewed

        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $relationship_category_id = $this->Relationship_category_model->addRelationshipCategory(array(
            'customer_id' => $data['customer_id'],
            'created_by' => $data['created_by'],
            'can_review' => 0,
            'created_on' => currentDate()
        ));

        $this->Relationship_category_model->addRelationshipCategoryLanguage(array(
            'relationship_category_id' => $relationship_category_id,
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_category_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updatenrcategory_post(){
        // Updates a non review category
        // This category contracts cannot be reviewed

        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_relationship_category', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('id_relationship_category_language', array('required'=>$this->lang->line('relationship_category_language_id_req')));
        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('relationship_category_status', array('required'=>$this->lang->line('relationship_category_status_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_relationship_category'])) {
            $data['id_relationship_category'] = pk_decrypt($data['id_relationship_category']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_relationship_category'] ,$this->session_user_customer_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_category']>0 &&  !in_array($data['id_relationship_category'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_relationship_category_language'])) $data['id_relationship_category_language']=pk_decrypt($data['id_relationship_category_language']);
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
         
        $this->User_model->update_data('relationship_category',array(
            'relationship_category_status' => $data['relationship_category_status'],
            'updated_by' => $data['updated_by'],
            'updated_on' => currentDate()
        ),array('id_relationship_category' => $data['id_relationship_category']));

        $this->User_model->update_data('relationship_category_language',array(
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ),array('id_relationship_category_language' => $data['id_relationship_category_language']));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_category_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function nrlist_get(){
        // Lists a non review category
        // This category contracts cannot be reviewed

        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        //$data['relationship_category_status'] = 1;
        $data['can_review'] = 0;
        $result = $this->Relationship_category_model->RelationshipCategoryList($data);//echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            if(strlen($v['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result['data'][$k]['relationship_category_short_name'] = $v['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $result['data'][$k]['created_by'] = pk_encrypt($v['created_by']);
            $result['data'][$k]['customer_id'] = pk_encrypt($v['customer_id']);
            $result['data'][$k]['id_relationship_category'] = pk_encrypt($v['id_relationship_category']);
            $result['data'][$k]['id_relationship_category_language'] = pk_encrypt($v['id_relationship_category_language']);
            $result['data'][$k]['language_id'] = pk_encrypt($v['language_id']);
            $result['data'][$k]['parent_relationship_category_id'] = pk_encrypt($v['parent_relationship_category_id']);
            $result['data'][$k]['relationship_category_id'] = pk_encrypt($v['relationship_category_id']);
            $result['data'][$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function ProviderRelationshipCategoriesList_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        //$data['relationship_category_status'] = 1;
        if(isset($data['can_review']))
            $data['can_review']= 1;
        $result = $this->Relationship_category_model->ProviderRelationshipCategoryList($data);//echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$v){
            if(strlen($v['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result['data'][$k]['relationship_category_short_name'] = $v['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $result['data'][$k]['created_by'] = pk_encrypt($v['created_by']);
            $result['data'][$k]['customer_id'] = pk_encrypt($v['customer_id']);
            $result['data'][$k]['id_provider_relationship_category'] = pk_encrypt($v['id_provider_relationship_category']);
            $result['data'][$k]['id_provider_relationship_category_language'] = pk_encrypt($v['id_provider_relationship_category_language']);
            $result['data'][$k]['language_id'] = pk_encrypt($v['language_id']);
            $result['data'][$k]['parent_provider_relationship_category_id'] = pk_encrypt($v['parent_provider_relationship_category_id']);
            $result['data'][$k]['provider_relationship_category_id'] = pk_encrypt($v['provider_relationship_category_id']);
            $result['data'][$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }
        $data['can_review']= 0;
        $result1 = $this->Relationship_category_model->ProviderRelationshipCategoryList($data);//echo $this->db->last_query();exit;
        foreach($result['data'] as $i=>$j){
            if(strlen($v['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($j['relationship_category_name'])), $matches);
                $result1['data'][$i]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result1['data'][$i]['relationship_category_short_name'] = $j['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $result1['data'][$i]['created_by'] = pk_encrypt($j['created_by']);
            $result1['data'][$i]['customer_id'] = pk_encrypt($j['customer_id']);
            $result1['data'][$i]['id_provider_relationship_category'] = pk_encrypt($j['id_provider_relationship_category']);
            $result1['data'][$i]['id_provider_relationship_category_language'] = pk_encrypt($j['id_provider_relationship_category_language']);
            $result1['data'][$i]['language_id'] = pk_encrypt($j['language_id']);
            $result1['data'][$i]['parent_provider_relationship_category_id'] = pk_encrypt($j['parent_provider_relationship_category_id']);
            $result1['data'][$i]['provider_relationship_category_id'] = pk_encrypt($j['provider_relationship_category_id']);
            $result1['data'][$i]['updated_by'] = pk_encrypt($j['updated_by']);
        }
        $graph = $this->providerRelationCategoryGraph($result,$data['customer_id']);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records'], 'graph'=> $graph));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function ProviderRelationshipclassificationList_get()
    {
        $data = $this->input->get();
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
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
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id'])) {
            $data['parent_classification_id'] = pk_decrypt($data['parent_classification_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id']>0 && !in_array($data['parent_classification_id'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['parent_classification_id_not'])) {
            $data['parent_classification_id_not'] = pk_decrypt($data['parent_classification_id_not']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['parent_classification_id_not'] ,$this->session_user_customer_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['parent_classification_id_not']>0 && !in_array($data['parent_classification_id_not'],$this->session_user_wadmin_relationship_classifications)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data); //helper function for ordering smart table grid options
        $data['parent_classification_id'] = 0; //for parent classifications
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        $result = $this->Relationship_category_model->ProviderRelationshipClassificationList($data);
        // echo $this->db->last_query();exit;
        // print_r($result);exit;
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['created_by']=pk_encrypt($v['created_by']);
            $result['data'][$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result['data'][$k]['id_provider_relationship_classification']=pk_encrypt($v['id_provider_relationship_classification']);
            $result['data'][$k]['id_provider_relationship_classification_language']=pk_encrypt($v['id_provider_relationship_classification_language']);
            $result['data'][$k]['language_id']=pk_encrypt($v['language_id']);
            $result['data'][$k]['parent_classification_id']=pk_encrypt($v['parent_classification_id']);
            $result['data'][$k]['parent_provider_relationship_classification_id']=pk_encrypt($v['parent_provider_relationship_classification_id']);
            $result['data'][$k]['provider_relationship_classification_id']=pk_encrypt($v['provider_relationship_classification_id']);
            $result['data'][$k]['updated_by']=pk_encrypt($v['updated_by']);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function AdditionalProviderCategories_get(){

        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
            if(!in_array($data['language_id'],$this->session_user_master_language)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(!isset($data['customer_id'])){ $data['customer_id'] = 0; }
        //$data['relationship_category_status'] = 1;
        $data['can_review']= 0;
        $result1 = $this->Relationship_category_model->ProviderRelationshipCategoryList($data);
         //echo $this->db->last_query();exit;
        foreach($result1['data'] as $i=>$j){
            if(strlen($j['relationship_category_name'])>2){
                preg_match_all('/[A-Z0-9]/', ucwords(strtolower($j['relationship_category_name'])), $matches);
                $result1['data'][$i]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result1['data'][$i]['relationship_category_short_name'] = $j['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result['data'][$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $result1['data'][$i]['created_by'] = pk_encrypt($j['created_by']);
            $result1['data'][$i]['customer_id'] = pk_encrypt($j['customer_id']);
            $result1['data'][$i]['id_provider_relationship_category'] = pk_encrypt($j['id_provider_relationship_category']);
            $result1['data'][$i]['id_provider_relationship_category_language'] = pk_encrypt($j['id_provider_relationship_category_language']);
            $result1['data'][$i]['language_id'] = pk_encrypt($j['language_id']);
            $result1['data'][$i]['parent_provider_relationship_category_id'] = pk_encrypt($j['parent_provider_relationship_category_id']);
            $result1['data'][$i]['provider_relationship_category_id'] = pk_encrypt($j['provider_relationship_category_id']);
            $result1['data'][$i]['updated_by'] = pk_encrypt($j['updated_by']);
        }
        // $graph = $this->relationCategoryGraph($result,$data['customer_id']);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result1['data'],'total_records' => $result1['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function AddAdditionalProviderCategories_post(){
        // Adds a non review category
        // This category contracts cannot be reviewed

        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $relationship_category_id = $this->Relationship_category_model->addProviderRelationshipCategory(array(
            'customer_id' => $data['customer_id'],
            'created_by' => $data['created_by'],
            'can_review' => 0,
            'created_on' => currentDate(),
            'provider_relationship_category_status'=>1
        ));

        $this->Relationship_category_model->addProviderRelationshipCategoryLanguage(array(
            'provider_relationship_category_id' => $relationship_category_id,
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_category_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updateAdditionalProviderCategories_post(){
        // Updates a non review category
        // This category contracts cannot be reviewed

        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_provider_relationship_category', array('required'=>$this->lang->line('provider_relationship_category_id_req')));
        $this->form_validator->add_rules('id_provider_relationship_category_language', array('required'=>$this->lang->line('Provider_relationship_category_language_id_req')));
        $this->form_validator->add_rules('relationship_category_name', array('required'=>$this->lang->line('relationship_category_name_req')));
        $this->form_validator->add_rules('provider_relationship_category_status', array('required'=>$this->lang->line('provider_relationship_category_status_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_provider_relationship_category'])) {
            $data['id_provider_relationship_category'] = pk_decrypt($data['id_provider_relationship_category']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_provider_relationship_category'] ,$this->session_user_customer_provider_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['id_relationship_category']>0 &&  !in_array($data['id_relationship_category'],$this->session_user_wadmin_relationship_categories)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider_relationship_category_language'])) $data['id_provider_relationship_category_language']=pk_decrypt($data['id_provider_relationship_category_language']);
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
         
        $this->User_model->update_data('provider_relationship_category',array(
            'provider_relationship_category_status' => $data['provider_relationship_category_status'],
            'updated_by' => $data['updated_by'],
            'updated_on' => currentDate()
        ),array('id_provider_relationship_category' => $data['id_provider_relationship_category']));

        $this->User_model->update_data('provider_relationship_category_language',array(
            'relationship_category_name' => $data['relationship_category_name'],
            'language_id' => 1
        ),array('id_provider_relationship_category_language' => $data['id_provider_relationship_category_language']));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_relationship_category_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }
    

}