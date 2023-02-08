<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Calender extends REST_Controller
{
    public $user_id = 0 ;
    public $session_user_id=NULL;
    public $session_user_info=NULL;
    public $session_user_business_units=NULL;
    public $session_user_business_units_user=NULL;
    public $session_user_contracts=NULL;
    public $session_user_delegates=NULL;
    public $session_user_contributors=NULL;
    public $session_user_customer_all_users=NULL;
    public $session_user_customer_relationship_categories=NULL;
    public $session_user_customer_provider_relationship_categories=NULL;
    public $session_user_customer_calenders=NULL;
    public $session_user_master_countries=NULL;
    public $session_user_master_templates=NULL;
    public $session_user_master_customers=NULL;
    public $session_user_master_users=NULL;
    public $session_user_master_user_roles=NULL;
    public function __construct()
    {
        parent::__construct();
        if(isset($_SERVER['HTTP_USER'])){
            $this->user_id = pk_decrypt($_SERVER['HTTP_USER']);
        }
        $this->load->model('Validation_model');
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        // echo currentDate();exit;
        // echo pk_decrypt('U2FsdGVkX19UaGVAMTIzNLCog7koQtBsrktN/QUM894=');exit;
        //echo '<pre>'.$this->db->last_query();exit;
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
        if($this->session_user_info->user_role_id <3 || $this->session_user_info->user_role_id==5)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
        else if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4 || $this->session_user_info->user_role_id==8)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        else if($this->session_user_info->user_role_id==6){
            if($this->session_user_info->is_allow_all_bu==1)
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
            else
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        }
        // echo $this->db->last_query();exit;
        $this->session_user_own_business_units=$this->session_user_business_units;
        $this->session_user_review_business_units=$this->Validation_model->getReviewBusinessUnits(array('id_user'=>$this->session_user_id));
        
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units));
            // echo '<pre>'.$this->db->last_query();exit;
        // $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        // $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        // $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        // $this->session_user_customer_all_users=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_provider_relationship_categories=$this->Validation_model->getCustomerProviderRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_master_countries=$this->Validation_model->getCountries();
        // $this->session_user_master_templates=$this->Validation_model->getTemplates();
        // $this->session_user_master_customers=$this->Validation_model->getCustomers();
        // $this->session_user_master_users=$this->Validation_model->getUsers();
        // $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();

        //echo '$this->session_user_id'.$this->session_user_id;
        //$this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
    }

    /**
     * using this function we can add reviews and workflow 
     * 
     */
    public function add_post(){
        //get post values and storing into $data variable

        $data = $this->input->post();
        //  print_r($data);exit;
        //checking loggedin user has permissions or not
        // print_r($this->session_user_info);exit;
        if(in_array($this->session_user_info->user_role_id,array("2,3,4"))){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'sddsds');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //validate post values
        if(isset($data['is_workflow'])&& $data['is_workflow']==1){
            $this->form_validator->add_rules('workflow_name', array('required'=>$this->lang->line('workflow_name_req')));
        }else{
            $this->form_validator->add_rules('business_unit_id', array('required'=>$this->lang->line('business_unit_id_req')));
        }
        // $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        // $this->form_validator->add_rules('provider_id', array('required'=>$this->lang->line('provider_id_req')));
        // $this->form_validator->add_rules('relationship_category_id', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));// treate this param as review_by
        // if(!isset($data['is_workflow'])){
        //     $this->form_validator->add_rules('recurrence_till', array('required'=>$this->lang->line('recurrence_till_req')));
        //     $this->form_validator->add_rules('recurrence', array('required'=>$this->lang->line('recurrence_req')));//recurrence means montly, queterly, halferly, yearly
        // }
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));//recurrence means montly, queterly, halferly, yearly

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'data validataion');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //checking permissions with customer id
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'customer data not found');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        //checking permissions wit business unit id
        if(isset($data['business_unit_id']) && count(explode(',',$data['business_unit_id']))>0){
            $business_unit_id_exp=explode(',',$data['business_unit_id']);
            $business_unit_id=array();
            foreach($business_unit_id_exp as $k=>$v){
                    $business_unit_id_chk = pk_decrypt($v);
                    if(!in_array($business_unit_id_chk,$this->session_user_business_units)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $business_unit_id[]=pk_decrypt($v);
            }
            $data['business_unit_id']=implode(',',$business_unit_id);
        }

        if(isset($data['provider_id']) && count(explode(',',$data['provider_id']))>0){
            $provider_id_exp=explode(',',$data['provider_id']);
            $provider_id=array();
            foreach($provider_id_exp as $k=>$v){
                $provider_id[]=pk_decrypt($v);
            }
            $data['provider_id']=implode(',',$provider_id);
        }

        if(isset($data['relationship_category_id']) && count(explode(',',$data['relationship_category_id']))>0 && $data['relationship_category_id']!=""){
            $relationship_category_id_exp=explode(',',$data['relationship_category_id']);
            $relationship_category_id=array();
            foreach($relationship_category_id_exp as $k=>$v){
                     $relationship_category_id_chk = pk_decrypt($v);
                    if(!in_array($relationship_category_id_chk,$this->session_user_customer_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $relationship_category_id[]=pk_decrypt($v);
            }
            $data['relationship_category_id']=implode(',',$relationship_category_id);
        }
        if(isset($data['provider_relationship_category_id']) && count(explode(',',$data['provider_relationship_category_id']))>0 && $data['provider_relationship_category_id']!=""){
            $provider_relationship_category_id_exp=explode(',',$data['provider_relationship_category_id']);
            $provider_relationship_category_id=array();
            foreach($provider_relationship_category_id_exp as $k=>$v){
                     $provider_relationship_category_id_chk = pk_decrypt($v);
                    if(!in_array($provider_relationship_category_id_chk,$this->session_user_customer_provider_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $provider_relationship_category_id[]=pk_decrypt($v);
            }
            $data['provider_relationship_category_id']=implode(',',$provider_relationship_category_id);
        }

        if(isset($data['contract_id']) && count(explode(',',$data['contract_id']))>0 && $data['contract_id']!=""){
            $contract_id_exp=explode(',',$data['contract_id']);
            $contract_id=array();
            // print_r($this->session_user_contracts);exit;
            foreach($contract_id_exp as $k=>$v){
                    $contract_id_chk = pk_decrypt($v);
                    // if(!in_array($contract_id_chk,$this->session_user_contracts)){
                    //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                    //     $this->response($result, REST_Controller::HTTP_OK);
                    // }
                $contract_id[]=pk_decrypt($v);
            }
            // print_r($contract_id);exit;
            $data['contract_id']=implode(',',$contract_id);
        }else{
            //If No Contract is Selected We are selecting all available contracts
            $contract_filters = array(
                'business_ids' => $business_unit_id,
                'relationship_category_id' => $relationship_category_id,
                'provider_relationship_category_id'=>$provider_relationship_category_id,
                'provider_ids' => $provider_id,
                'customer_id' => $this->session_user_info->customer_id
            );
            if($data['type']=='project'){
                $contract_filters['type']='project';
            }
            else{
                $contract_filters['type']='contract';
            }
            if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4){
                $contract_filters['user_id'] = $this->session_user_id;
            }
            if(isset($data['is_workflow']))
                $contract_filters['is_workflow'] = $data['is_workflow'];
            $contracts = $this->Calender_model->getContracts($contract_filters);
            // echo $this->db->last_query();exit;
            $contract_id = array_map(function($i){ return ($i['id_contract']); },$contracts);
            $data['contract_id']=implode(',',$contract_id);
        }

        if(isset($data['completed_contract_id']) && count($data['completed_contract_id'])>0 && $data['completed_contract_id']!=""){
            //This is an array not comma separated so this is different from the previous 4 datas
            $contract_id_exp=$data['completed_contract_id'];
            $contract_id=array();
            // print_r($this->session_user_contracts);exit;
            foreach($contract_id_exp as $k=>$v){
                    $contract_id_chk = pk_decrypt($v);
                    
                $contract_id[]=pk_decrypt($v);
            }
            $data['completed_contract_id']=implode(',',$contract_id);
        }
        
        //checking permission with created by id
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        
        //adding data to review data array
        $review_data["bussiness_unit_id"] = isset($data["business_unit_id"])?$data["business_unit_id"]:"";
        $review_data["completed_contract_id"] = isset($data["completed_contract_id"])?$data["completed_contract_id"]:"";
        $review_data["contract_id"] = isset($data["contract_id"])?$data["contract_id"]:"";
        $review_data["provider_id"] = isset($data["provider_id"])?$data["provider_id"]:"";
        $review_data["relationship_category_id"] = isset($data["relationship_category_id"])?$data["relationship_category_id"]:"";
        $review_data["provider_relationship_category_id"] = isset($data["provider_relationship_category_id"])?$data["provider_relationship_category_id"]:"";
        $review_data["date"] = $data["date"];
        $review_data["recurrence_till"] = isset($data["recurrence_till"])?$data["recurrence_till"]:$review_data["date"];
        $review_data["recurrence"] = isset($data["recurrence"])&&$data["recurrence"]>0?$data["recurrence"]:0;
        $review_data["customer_id"] = $data["customer_id"];
        $review_data["created_by"] = $data["created_by"];
        $review_data["workflow_name"] = $data["workflow_name"];
        $review_data["auto_initiate"] = isset($data["auto_initiate"])?$data["auto_initiate"]:0;
        $review_data["plan_executed"] = isset($data["auto_initiate"])?$data["auto_initiate"]:0;
        $review_data["created_on"] = currentDate();
        $review_data["type"] = !empty($data['type']) && $data['type']=='project'?'project':'contract';
        $review_data["task_type"] = "main_task";
        $review_data["initiate_date"] = date('Y-m-d');
        // print_r($data);exit;
        //if $data['id_calender'] does not isset then need to create the record or update
        if(!isset($data["id_calender"])){
            //If workflow is creating (is set is_workflow), only workflow creates and exits from this block.
            if(isset($data['is_workflow'])&& $data['is_workflow']==1)
            {
                $review_data['workflow_id'] = pk_decrypt($data['workflow_template_id']);
                $review_data["is_workflow"] = 1;
                // print_r('Sita Rama');exit;
                $calender_insert_id=$this->User_model->insert_data('calender',$review_data);
                if($calender_insert_id){
                    // start explodeing string values to array 
                    if(isset($data["business_unit_id"]) && $data["business_unit_id"] != ''){
                        $data["business_unit_id"] = explode(",",$data["business_unit_id"]);
                    }
                    if(isset($data["contract_id"]) && $data["contract_id"] != ''){
                        $data["contract_id"] = explode(",",$data["contract_id"]);
                    }
                    if(isset($data["provider_id"]) && $data["provider_id"] != ''){
                        $data["provider_id"] = explode(",",$data["provider_id"]);
                    }
                    if(isset($data["relationship_category_id"]) && $data["relationship_category_id"] != ''){
                        $data["relationship_category_id"] = explode(",",$data["relationship_category_id"]);
                    }
                    if(isset($data["provider_relationship_category_id"]) && $data["provider_relationship_category_id"] != ''){
                        $data["provider_relationship_category_id"] = explode(",",$data["provider_relationship_category_id"]);
                    }
                    // end explodeing string values to array 
                    // getting all contracts
                    $contracts = $this->Calender_model->getContracts($data);
                    // echo $this->db->last_query();exit;
                    //echo '<pre>'.$this->db->last_query();
                    $add_contract = "";
                    foreach($contracts as $k=>$v){
                        //print_r($v['id_contract']);exit;
                        $add_workflow_contract_data[]=array(
                            'workflow_id'=>pk_decrypt($data['workflow_template_id']),
                            'workflow_name'=>$data['workflow_name'],
                            'contract_id'=>$v['id_contract'],
                            'Execute_by'=>$data['date'],
                            'created_by'=>$this->session_user_id,
                            'created_on'=>currentDate(),
                            'calender_id'=>$calender_insert_id,
                            'status'=>1
                        );
                    }
                    //print_r($add_workflow_contract_data);exit;
                    $add_contract=$this->Calender_model->addContract_workflow($add_workflow_contract_data);
                    // echo '<pre>'.$this->db->last_query();
                    if($add_contract){
                        $result = array('status'=>TRUE,'message'=>$this->lang->line('workflow_added'),'data'=>'');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }else{
                        $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'Workflow not created 1');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                }else{
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'Workflow not created 2');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            } //end workflow

            //unset post data
            unset($data);

            //Creating Calender new entry
            $this->CreateCalenderEntry($review_data);
            
        } else {
            $review_data["updated_by"] = $data["created_by"];
            $review_data["updated_on"] = currentDate();

            $id_calender = pk_decrypt($data['id_calender']);
            $where["id_calender"] = $id_calender;

            

            //if it is workflow edit then below condition will satisfy
            if(isset($data['workflow_info'])&& $data['workflow_info']==1)
            {  

                $workflows=$this->User_model->check_record('contract_workflow',array('calender_id'=>$id_calender));
                $oldCalenderData =$this->User_model->check_record("calender",$where);
                if(($oldCalenderData[0]['workflow_name'] != $data["workflow_name"])||($oldCalenderData[0]['date'] != $data["date"]))
                {
                    //if workflow name or date changed then below condition will satisfy
                    $upadteCalenderTask =array(
                        'workflow_name' => $data["workflow_name"],
                        'recurrence_till'=>$data["date"],
                        'date'=>$data["date"],
                        'updated_by'=>$data["created_by"],
                        'updated_on'=>currentDate()
                    );
                    $this->db->where($where)->update('calender',$upadteCalenderTask); //updating main in task calender
                    $upadteWorkflowTask =array(
                        'workflow_name' => $data["workflow_name"],
                        'Execute_by'=>$data["date"],
                    );
                    if(!empty($workflows)){
                        foreach($workflows as $workflowData) //loop for multiple project taskes
                        {
                            $this->db->where(array('id_contract_workflow'=>$workflowData['id_contract_workflow']))->update('contract_workflow',$upadteWorkflowTask); //updating main task in contract_workflow
                            if($data['type'] == 'project')
                            {
                                $subTaskProjectWorkflows=$this->User_model->check_record('contract_workflow',array('parent_id'=>$workflowData['id_contract_workflow'],'status'=>1));
                                foreach($subTaskProjectWorkflows as $subTaskProjectWorkflow)
                                {
                                    $updateSubtaskworkflow = array();
                                    $updateSubInCalender =array();
                                    $get_user_name=$this->User_model->check_record_selected(array('CONCAT(first_name," ",last_name) as user_name','provider'),'user',array('id_user'=>$subTaskProjectWorkflow['provider_id']));
                                    $get_provider_name = $this->User_model->check_record_selected('provider_name','provider',array('id_provider'=>$get_user_name[0]['provider']));
                                    $updateSubtaskworkflow = array(
                                        'workflow_name' => $data['workflow_name'].' ('.$get_provider_name[0]['provider_name'].' - '.$get_user_name[0]['user_name'].')',
                                        'Execute_by'=>$data["date"],
                                    );
                                    $this->db->where(array('id_contract_workflow'=>$subTaskProjectWorkflow['id_contract_workflow']))->update('contract_workflow',$updateSubtaskworkflow); //updating work flow name and execute_by in contractworkflow for subtasks
                                    $updateSubInCalender =array(
                                        'workflow_name' => $data['workflow_name'].' ('.$get_provider_name[0]['provider_name'].' - '.$get_user_name[0]['user_name'].')',
                                        'recurrence_till'=>$data["date"],
                                        'date'=>$data["date"],
                                        'updated_by'=>$data["created_by"],
                                        'updated_on'=>currentDate()
                                    );
                                    $this->db->where(array('id_calender'=>$subTaskProjectWorkflow['calender_id']))->update('calender',$updateSubInCalender); //updating work flow name and execute_by in calender for subtasks
                                }
                            }
                        }
                    }
                    $result = array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>'Task updated successfully');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                else{
                    //if workflow name or date not changed while updating
                    $result = array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>'Nothing changed');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }else{
                //Getting the Previous Calender planning.
                $calender = $this->User_model->check_record('calender',array('id_calender'=>$id_calender));

                //Unlocking the contrcts of prevous plan.
                if($calender[0]['relationship_category_id'] != '')
                    $this->db->where_in('relationship_category_id',explode(',',$calender[0]['relationship_category_id']));
                // if($calender[0]['provider_relationship_category_id'] != '')
                //     $this->db->where_in('provider_relationship_category_id',explode(',',$calender[0]['provider_relationship_category_id']));    
                if($calender[0]['bussiness_unit_id'] != '')
                    $this->db->where_in('business_unit_id',explode(',',$calender[0]['bussiness_unit_id']));
                if($calender[0]['provider_id'] != '')
                    $this->db->where_in('provider_name',explode(',',$calender[0]['provider_id']));
                if($calender[0]['contract_id'] != '')
                    $this->db->where_in('id_contract',explode(',',$calender[0]['contract_id']));
                if(!$this->db->update('contract',array('is_lock'=>0))){
                    $result = array('status'=>FALSE, 'message' => $this->lang->line('contract_not_unlock'), 'data'=>[]);
                    $this->response($result, REST_Controller::HTTP_OK);
                }

                //Deleting the last planned Calender Criteria
                if($calender[0]['parent_calender_id'] == null){
                    $cal_id = $calender[0]['id_calender'];                    
                }
                else{
                    $cal_id = $calender[0]['parent_calender_id'];
                    $calender_review_date = $this->User_model->check_record('calender',array('id_calender'=>$cal_id));
                    $review_data['date'] = $calender_review_date[0]['date'];
                }
                $this->db->where(array('id_calender'=>$cal_id))->or_where(array('parent_calender_id'=>$cal_id))->delete('calender');

                //Creating the newly planned Calender Criteria
                $this->CreateCalenderEntry($review_data);
            }
        }//end edit record if condition end 
    }

    public function smart_filter_get(){
        $data = $this->input->get();

        // print_r($data);exit;
        //checking permissions with customer id
        if(isset($data['customer_id'])) {
             $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'customer data not found');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        //checking permissions wit business unit id
        if(isset($data['business_ids']) && count(explode(',',$data['business_ids']))>0){
            $business_ids_exp=explode(',',$data['business_ids']);
            $business_ids=array();
            foreach($business_ids_exp as $k=>$v){
                //echo '<pre>'.print_r($this->session_user_business_units);exit;
                    $business_ids_chk = pk_decrypt($v);
                    if(!in_array($business_ids_chk,$this->session_user_business_units)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'Business id not found');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $business_ids[]=pk_decrypt($v);
            }
            $data['business_ids']=$business_ids;
        }

        if(isset($data['provider_ids']) && count(explode(',',$data['provider_ids']))>0){
            $provider_ids_exp=explode(',',$data['provider_ids']);
            $provider_ids=array();
            foreach($provider_ids_exp as $k=>$v){
                $provider_ids[]=pk_decrypt($v);
            }
            $data['provider_ids']=$provider_ids;
        }

        if(isset($data['relationship_category_id']) && count(explode(',',$data['relationship_category_id']))>0){
            $relationship_category_id_exp=explode(',',$data['relationship_category_id']);
            $relationship_category_id=array();
            foreach($relationship_category_id_exp as $k=>$v){
                     $relationship_category_id_chk = pk_decrypt($v);
                    if(!in_array($relationship_category_id_chk,$this->session_user_customer_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $relationship_category_id[]=pk_decrypt($v);
            }
            $data['relationship_category_id']=$relationship_category_id;
        }
        if(isset($data['provider_relationship_category_id']) && count(explode(',',$data['provider_relationship_category_id']))>0){
            $provider_relationship_category_id_exp=explode(',',$data['provider_relationship_category_id']);
            $provider_relationship_category_id=array();
            foreach($provider_relationship_category_id_exp as $k=>$v){
                     $provider_relationship_category_id_chk = pk_decrypt($v);
                    if(!in_array($provider_relationship_category_id_chk,$this->session_user_customer_provider_relationship_categories)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $provider_relationship_category_id[]=pk_decrypt($v);
            }
            $data['provider_relationship_category_id']=$provider_relationship_category_id;
        }

        if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0){
            $contract_ids_exp=explode(',',$data['contract_ids']);
            $contract_ids=array();
             //print_r($this->session_user_contracts);
            foreach($contract_ids_exp as $k=>$v){
                    $contract_ids_chk = pk_decrypt($v);
                    if(!in_array($contract_ids_chk,$this->session_user_contracts)){
                        // echo '-'.$contract_ids_chk;
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $contract_ids[]=pk_decrypt($v);
            }
            // print_r($contract_ids);exit;
            $data['contract_ids']=$contract_ids;
        }
    

        // print_r($data);exit;
        //getting list of categories by customer_id
        $result =[];

        
        //getting list of contracts based on relationship caregory id and if contract_id is exist then it will add to where condition
        // getting bussiness list
        if($this->session_user_info->user_role_id !=2 && $this->session_user_info->user_role_id !=8){
            $user = array("user_id"=>$this->session_user_id);
            $data['user_id'] = $this->session_user_id;
        }
        else
            $user = [];
        
        if(!isset($data['is_workflow'])){
            $data['can_review'] = 1;
            $data["is_lock"] = 1;
        }
        

        $data['status'] = $user['status']=1;
        $data['customer_id'] = $user['customer_id']=$this->session_user_info->customer_id;
        if(isset($data['id_calender'])) {
            $data['id_calender'] = pk_decrypt($data['id_calender']);
            $calender = $this->User_model->check_record('calender',array('id_calender'=>$data['id_calender']));
            $calender = $calender[0];
            // echo $this->db->last_query();exit;
            if($calender['contract_id']!=""){
                $data['calender_contracts'] = explode(',',$calender['contract_id']);
                if($calender["completed_contract_id"])
                    $data["completed_contracts"] = explode(',',$calender["completed_contract_id"]);
            }else{
                if($calender["id_business_unit"])
                    $data["calender_bus"] = explode(',',$calender["id_business_unit"]);
                
                if($calender["relationship_category_id"])
                    $data["calender_cat_ids"] = explode(',',$calender["relationship_category_id"]);

                if($calender["provider_relationship_category_id"])
                    $data["calender_provider_cat_ids"] = explode(',',$calender["provider_relationship_category_id"]);    

                if($calender["provider_id"])
                    $data["calender_providers"] = explode(',',$calender["provider_id"]);

                if($calender["completed_contract_id"])
                    $data["completed_contracts"] = explode(',',$calender["completed_contract_id"]);
                
                //getting contracts by provider, business_unit_id and cat_id
                $calender = $this->Calender_model->check_contract_in($data);
                

                $data['calender_contracts'] = array_map(function($i){ return $i['id_contract']; },$calender);
                // print_r($data);exit;
            }
        }

        $result["business_unit"] = $this->Calender_model->getBusinessUnits($data);
        foreach($result["business_unit"] as $k=>$v){
            // print_r($v);exit;
            $result["business_unit"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
        }
        $result["relationship_list"] = $this->Calender_model->getContractRelationshipCategory($data);
        unset($data['can_review']);
        $result["provider_relationship_category"] = $this->Calender_model->getProviderRelationshipCategory($data);
        if($data['type'] == 'project')
        {
            $result["provider_relationship_category"] =[]; 
        }
        // echo '<pre>'.$this->db->last_query();exit;
        $data_relationship_category = $data['relationship_category_id'];
        $data['relationship_category_id'][] = '0';//Added to smart filter.
        foreach($result["relationship_list"] as $k=>$v){
            if(!empty($data_relationship_category)){
                if(in_array($v['id_relationship_category'], $data_relationship_category))
                    $data['relationship_category_id'][] = $v['id_relationship_category'];
            }else{
                $data['relationship_category_id'][] = $v['id_relationship_category'];
            }
            $result["relationship_list"][$k]['id_relationship_category'] = pk_encrypt($v['id_relationship_category']);
        }
        $relationship_category_data = $data['relationship_category_id'];
        // echo 'data ';
        // echo '<pre>'.print_r($data_relationship_category);
        // echo 'req data';
        // echo '<pre>'.print_r($data_relationship_category);exit;
        //$data['relationship_category_id'] = array(0);
        // foreach($relationship_category_data as $v){
        //     if(in_array($v, $data_relationship_category)){
        //         // echo 'true ';
        //         // echo '<pre>'.print_r($v);
        //         $data['relationship_category_id'][] = $v;
        //     }
        // }
        $data['relationship_category_id']=array_filter($data['relationship_category_id']);        
        $data_provider_relationship_category = $data['provider_relationship_category_id'];
        $data['provider_relationship_category_id'][] = '0';//Added to smart filter.
        foreach($result["provider_relationship_category"] as $k=>$v){
            if(!empty($data_provider_relationship_category)){
                if(in_array($v['id_provider_relationship_category'], $data_provider_relationship_category))
                    $data['provider_relationship_category_id'][] = $v['id_provider_relationship_category'];
            }else{
                $data['provider_relationship_category_id'][] = $v['id_provider_relationship_category'];
            }
            $result["provider_relationship_category"][$k]['id_provider_relationship_category'] = pk_encrypt($v['id_provider_relationship_category']);
        }
        $relationship_category_data = $data['provider_relationship_category_id'];
        $data['provider_relationship_category_id']=array_filter($data['provider_relationship_category_id']);
        $result["provider"] = $this->Calender_model->getProvider($data);

        $data_provider = $data['provider_ids'];
        $data['provider_ids'] = array(0);
        foreach($result["provider"] as $k=>$v){
            // print_r($v);exit;
            // $result["provider"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
            // $result["provider"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
            if(!empty($data_provider)){
                if(in_array($v['id_provider'], $data_provider))
                    $data['provider_ids'][] = $v['id_provider'];
            }else{
                $data['provider_ids'][] = $v['id_provider'];
            }
            $result["provider"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
        }
        $data['provider_ids']=array_filter($data['provider_ids']);
        //unset($data['relationship_category_id']);
        // print_r($data);exit;
        $result["contract"] = $this->Calender_model->getContracts($data);
        // print_r($data);
        //  echo $this->db->last_query();exit;
        foreach($result["contract"] as $k=>$v){
            // $result["contract"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
            // $result["contract"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
            $result["contract"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
        }
        
        //completed_contracts
        // echo '<pre>'.print_r($data);exit;
        $result["completed_contracts"] = $this->Calender_model->getCompletedContracts($data);
        foreach($result["completed_contracts"] as $k=>$v){
            // $result["contract"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
            // $result["contract"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
            $result["completed_contracts"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
        }

        
        
        // print_r($result);exit;
        $result = array('status'=>TRUE, 'message' =>array('message'=>$this->lang->line('sucess')), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function list_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));//check the customer id given or not
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));// treate this param as review_by
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'data validataion');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //customer id validation
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'customer data not found1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }        
        $data = tableOptions($data);
        //getting all review by of particular date
        $data['business_unit_id']=implode('|',$this->session_user_business_units);//concatinate the all login user bussinessunits with '|' symbol
        $data['user_role_id']=$this->session_user_info->user_role_id;
        // echo '<pre>'.print_r($data);exit;
        $result=$this->Calender_model->getCalenderReview($data);
        // echo $this->db->last_query();exit;
        foreach($result['data'] as $k=>$r){

            //print_r($r);
             //echo '<pre>'.print_r($result);exit;
            //$result[$k]["contract_name"] = array();
            if($r["completed_contract_id"]>0){
                $contract_name = $this->Calender_model->getNames_by_ID("id_contract",$r["completed_contract_id"],"contract_name","contract");
                $result['data'][$k]["completed_contract_name"] = array_unique(explode(',',$contract_name->contract_name));
                //passing comma separated string and returing encripted array
                $result['data'][$k]["completed_contract_id"] = $this->encriptCommaSeparatedValues($r["completed_contract_id"]);
            }

            if($r["contract_id"]>0){
                $contract_name = $this->Calender_model->getNames_by_ID("id_contract",$r["contract_id"],"contract_name","contract");
                $result['data'][$k]["contract_names"] = array_unique(explode(',',$contract_name->contract_name));
                //passing comma separated string and returing encripted array
                // $result['data'][$k]["contract_id"] = $this->encriptCommaSeparatedValues($r["contract_id"]);
            }
            $result['data'][$k]["contract_name"] = array();
            if($r["contract_id"]>0){
                $is_workflow = $result['data'][$k]["is_workflow"];
                $result['data'][$k]["id_contract_workflow"]=array();
                $result['data'][$k]["contract_review_id"]=array();
                $result['data'][$k]["initiated"]=array();
                $result['data'][$k]["is_workflow"]=array();
                $contractIds  = explode(',',$r["contract_id"]);
                if($this->session_user_info->user_role_id==3){
                    $get_cont_ids= $this->Calender_model->filetercontractids(array('contract_owner_id'=>$this->session_user_info->id_user,'contract_ids'=>$contractIds));
                    $contractIds=array_column($get_cont_ids,'contract_id');
                 }
 
                 if($this->session_user_info->user_role_id==4){
                     $get_cont_ids= $this->Calender_model->filetercontractids(array('delegate_id'=>$this->session_user_info->id_user,'contract_ids'=>$contractIds));
                     $contractIds=array_column($get_cont_ids,'contract_id');
                 }
                 if($this->session_user_info->user_role_id==2){
                    $get_cont_ids= $this->Calender_model->filetercontractids(array('contract_ids'=>$contractIds));
                    $contractIds=array_column($get_cont_ids,'contract_id');
                }
                // print_r($contractIds);exit;
                unset($result['data'][$k]["contract_id"]);
                foreach($contractIds as $contractId){
                    if($r['is_workflow']>0){
                        // print_r($r);
                        // print_r($contractId);exit;
                        $get_workflow_info=$this->Calender_model->get_contract_workflow_info(array('contract_id'=>$contractId,'calender_id'=>$r['id_calender']));
                        // print_r($get_workflow_info);exit;
                        $result['data'][$k]["id_contract_workflow"][]=!empty($get_workflow_info[0]['id_contract_workflow'])?pk_encrypt($get_workflow_info[0]['id_contract_workflow']):'';
                        $result['data'][$k]["contract_review_id"][]=!empty($get_workflow_info[0]['id_contract_review'])?pk_encrypt($get_workflow_info[0]['id_contract_review']):'';
                        $result['data'][$k]["is_workflow"][]='1';
                        $result['data'][$k]["initiated"][]=$get_workflow_info[0]['workflow_status']=='workflow in progress'?true:false;
                        $get_contract_info=$this->User_model->check_record_selected('id_contract,contract_name','contract',array('id_contract'=>$contractId));
                        // print_r($get_contract_info);exit;
                        $result['data'][$k]["contract_name"][] = $get_contract_info[0]['contract_name'];
                        $result['data'][$k]["contract_id"][] = pk_encrypt($get_contract_info[0]['id_contract']);
                        
                    }
                    else{
                        $get_review_info=$this->Calender_model->get_review_info_of_contract(array('contract_id'=>$contractId));
                        $result['data'][$k]["id_contract_workflow"][]=!empty($get_review_info[0]['id_contract_workflow'])?pk_encrypt($get_review_info[0]['id_contract_workflow']):'';
                        $result['data'][$k]["contract_review_id"][]=!empty($get_review_info[0]['id_contract_review'])?pk_encrypt($get_review_info[0]['id_contract_review']):'';
                        $result['data'][$k]["is_workflow"][]='0';
                        $result['data'][$k]["initiated"][]=$get_review_info[0]['contract_review_status']=='review in progress'?true:false;
                        $get_contract_info=$this->User_model->check_record_selected('id_contract,contract_name','contract',array('id_contract'=>$contractId));
                        // print_r($get_contract_info);exit;
                        $result['data'][$k]["contract_name"][] = $get_contract_info[0]['contract_name'];
                        $result['data'][$k]["contract_id"][] = pk_encrypt($get_contract_info[0]['id_contract']);
                    }
                }



                // foreach($contractIds as $contractId)
                // {
                //     $contractDetails =[];
                //     $contractDetails = $this->User_model->check_record("contract",array("id_contract"=>$contractId));
                //     $result['data'][$k]["contract_name"][] = $contractDetails[0]['contract_name'];
                //     $result['data'][$k]["contract_id"][] = pk_encrypt($contractDetails[0]['id_contract']);
                //     if($r['is_workflow']!=1)
                //     {
                //         //for reviews
                //         $workflow = $is_workflow;
                //         $result['data'][$k]["id_contract_workflow"][]='';
                //         $result['data'][$k]["is_workflow"][]=$workflow;
                //         $contract_review_details = $this->User_model->check_record('contract_review',array('contract_id'=>$contractId,'is_workflow'=>'0','contract_review_status'=>'review in progress'));
                //         if(!empty($contract_review_details))
                //         {
                //             $result['data'][$k]["contract_review_id"][]=pk_encrypt($contract_review_details[0]['id_contract_review']);
                //             $result['data'][$k]["initiated"][]=true;
                //         }
                //         else
                //         {
                //             $result['data'][$k]["contract_review_id"][]='';
                //             $result['data'][$k]["initiated"][]=false;
                //         }
                //     }
                //     else
                //     { 
                //         //for taskes
                //         $contract_workflow = $this->User_model->check_record_selected('id_contract_workflow','contract_workflow',array("calender_id"=>$r["id_calender"],'contract_id'=>$contractId));
                //         if(!empty($contract_workflow[0]))
                //         {
                //             $contract_review = $this->User_model->check_record_selected('id_contract_review','contract_review',array("calender_id"=>$r["id_calender"],'contract_id'=>$contractId,'contract_workflow_id'=>$contract_workflow[0]['id_contract_workflow']));
                            
                //             if(!empty($contract_review))
                //             {
                //                 $contract_review_id = pk_encrypt($contract_review[0]['id_contract_review']);
                //                 $initiated = true;
                //             }
                //             else
                //             {
                //                 $contract_review_id ='';
                //                 $initiated = false;
                //             }
                //             $id_contract_workflow = pk_encrypt($contract_workflow[0]['id_contract_workflow']);
                //             $workflow = $is_workflow;
                //             $result['data'][$k]["id_contract_workflow"][]=$id_contract_workflow;
                //             $result['data'][$k]["contract_review_id"][]=$contract_review_id;
                //             $result['data'][$k]["is_workflow"][]=$workflow;
                //             $result['data'][$k]["initiated"][]=$initiated;
                //         } 
                //         else
                //         {
                //             $result['data'][$k]["id_contract_workflow"][]='';
                //             $workflow = $is_workflow;
                //             $result['data'][$k]["contract_review_id"][]='';
                //             $result['data'][$k]["is_workflow"][]=$workflow;
                //             $result['data'][$k]["initiated"][]=false;
                //         } 
                        
                //     }
                         
                // }
            }
            //$result[$k]["bu_name"] = array();
            if($r["bussiness_unit_id"]>0){
                // $bu_name = $this->Calender_model->getNames_by_ID("id_business_unit",$r["bussiness_unit_id"],"bu_name","business_unit");echo $this->db->last_query();exit;  
                $bu_name=$this->Calender_model->getbunameswithcountryname(array('id_business_unit'=>explode(',',$r["bussiness_unit_id"])));
                // echo $this->db->last_query();exit;
                $result['data'][$k]["bu_name"] = array_unique(explode(',',$bu_name[0]['bu_name']));
                //passing comma separated string and returing encripted array
                $result['data'][$k]["bussiness_unit_id"] = $this->encriptCommaSeparatedValues($r["bussiness_unit_id"]);
            }

            //$result[$k]["provider_name"] = array();
            if($r["provider_id"]>0){
                $provider_name = $this->Calender_model->getNames_by_ID("id_provider",$r["provider_id"],"provider_name","provider");
                $result['data'][$k]["provider_name"] = array_unique(explode(',',$provider_name->provider_name));
                //passing comma separated string and returing encripted array
                $result['data'][$k]["provider_id"] = $this->encriptCommaSeparatedValues($r["provider_id"]);
            }

            //$result[$k]["relationship_category_id"] = array();
            if($r["relationship_category_id"]>0){
                $relationship_category_name = $this->Relationship_category_model->RelationshipCategoryList(array('id_relationship_category_array'=>explode(",",$r['relationship_category_id'])));
                
                $result['data'][$k]["relationship_category_name"] = array_map(function($i){ return $i['relationship_category_name']; },$relationship_category_name["data"]);
                
                //passing comma separated string and returing encripted array
                $result['data'][$k]["relationship_category_id"] = $this->encriptCommaSeparatedValues($r["relationship_category_id"]);
            }
            if($r["provider_relationship_category_id"]>0){
                $provider_relationship_category_name = $this->Relationship_category_model->ProviderRelationshipCategoryList(array('id_provider_relationship_category_array'=>explode(",",$r['provider_relationship_category_id'])));
                
                $result['data'][$k]["provider_relationship_category_name"] = array_map(function($i){ return $i['relationship_category_name']; },$provider_relationship_category_name["data"]);
                
                //passing comma separated string and returing encripted array
                $result['data'][$k]["provider_relationship_category_id"] = $this->encriptCommaSeparatedValues($r["provider_relationship_category_id"]);
            }

            if(!empty($result['data'][$k]['date'])){
                if(date('Y-m-d')<= $result['data'][$k]['date']){
                    $result['data'][$k]['overdue']=0;
                }
                else{
                    $result['data'][$k]['overdue']=1;
                }
            }
            else{
                $result['data'][$k]['overdue']=0;
            }
            $result['data'][$k]["customer_id"] = pk_encrypt($result['data'][$k]["customer_id"]);
            $result['data'][$k]["id_calender"] = pk_encrypt($result['data'][$k]["id_calender"]);
            $result['data'][$k]["created_by"] = pk_encrypt($result['data'][$k]["created_by"]);
            $result['data'][$k]["workflow_id"] = pk_encrypt($result['data'][$k]["workflow_id"]);
            $result['data'][$k]["workflow_template_id"] = pk_encrypt($result['data'][$k]["workflow_template_id"]);
            // $result['data'][$k]["workflow_info"] =  $result['data'][$k]['is_workflow'];
            // $result['data'][$k]["contract_review_id"] = pk_encrypt($result['data'][$k]["contract_review_id"]);
            // $result['data'][$k]["id_contract_workflow"] = pk_encrypt($result['data'][$k]["id_contract_workflow"]);
            
        }
    

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    function encriptCommaSeparatedValues($commaSeparatedValue){
        $encripted_ids = "";
        foreach(explode(",",$commaSeparatedValue) as $v){
            $encripted_ids .= pk_encrypt($v).",";
        }
        return explode(",",rtrim($encripted_ids,","));
    }

    public function calendarevents_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));//check the customer id given or not
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));// treate this param as review_by
        $this->form_validator->add_rules('filterType', array('required'=>$this->lang->line('date_req')));// filterType req
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'data validataion');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //customer id validation
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'customer data not found1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data['id_user'] = $this->session_user_id;
        if($this->session_user_info->user_role_id==3){
            $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
        }
        else if($this->session_user_info->user_role_id==4) {
            $data['delegate_id'] = $data['id_user'];
        }
        //$data['user_id'] = $this->session_user_id;//get the login userid
        $data['business_unit_id']=implode('|',$this->session_user_business_units);//concatinate the all login user bussinessunits with '|' symbol
        $events = $this->Calender_model->getEvents($data);
      
        unset($data['business_unit_id']);//no need to pass this param to another module.


        //Calender events for Action items starts
        $data['date'] = substr($data['date'],0,10);
        $d = date_parse_from_format("Y-m-d", $data['date']);
        
        //echo '<pre>'.print_r($data);exit;

        $review_action_items = array();
        $filterType = $data["filterType"];
        if($this->session_user_info->user_role_id==2){

        }
        else if($this->session_user_info->user_role_id==3){
            $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
        }
        else if($this->session_user_info->user_role_id==4) {
            $data['delegate_id'] = $data['id_user'];
        }
        $data['contract_review_action_item_status'] = 'open';
        $data['item_status'] = '1';
        $data['calendar'] =1;
        $reslut_array = array();
        
        if($filterType=="year"){
            
            for($i = 0; $i < 12; $i++){
                $data['date'] = $d["year"].'-'.($i+1).'-1';
                $data["filterType"]="month";
                $action_items = $this->Contract_model->getActionItems($data);//echo '<pre>'.$this->db->last_query();
                $review_action_items[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.($i+1).'-1')).' GMT+0100';
                $review_action_items[$i]['count'] = count($action_items['data']);
                $obligations = $this->Project_model->getObligations($data);
                $obligations_items[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.($i+1).'-1')).' GMT+0100';
                $obligations_items[$i]['count'] = $obligations['total_records'];
            }
            $events['action_item'] = $review_action_items;
            $events['obligations_items'] = $obligations_items;
            
            //Parsing the year result into 12 arrays format.
            for($i = 0; $i < 12; $i++){
                $reslut_array[$i]['review'][]= $events['review'][$i];
                $reslut_array[$i]['workflow'][] = $events['workflow'][$i];
                $reslut_array[$i]['action_item'][] = $events['action_item'][$i];
                $reslut_array[$i]['obligations_item'][] = $events['obligations_items'][$i];
            }
        }
       // print_r($events);exit;
        if($filterType=="month"){
            $days = cal_days_in_month(CAL_GREGORIAN, $d["month"], $d["year"]);
            //echo '<pre>'.print_r($d);exit;
            for($i = 0; $i < $days; $i++){
                $data['date'] = $d["year"].'-'.$d["month"].'-'.($i+1);
                $data["filterType"]="date";
                $action_items = $this->Contract_model->getActionItems($data);//echo '<pre>'.$this->db->last_query();exit;
                $obligations_rights =$this->Project_model->getObligations($data);
                // if($i==26)
                //     echo '<pre>'.$this->db->last_query();
                $review_action_items[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.$d["month"].'-'.($i+1))).' GMT+0100';
                $obligations_month[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.$d["month"].'-'.($i+1))).' GMT+0100';
                $review_action_items[$i]['count'] = count($action_items['data']);
                $obligations_month[$i]['count'] = $obligations_rights['total_records'];  
            }
            $events['action_item'] =$review_action_items;
            $events['obligations_item'] = $obligations_month;
         
            $reslut_array = $events;
        }

        //Calender events for Action items ends
        //For Year view Old response $events , New response $reslut_array

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$reslut_array);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function deletecalender_get(){
        $data = $this->input->get();
        $this->form_validator->add_rules('id_calender', array('required'=>$this->lang->line('calender_id_req')));// filterType req
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'data validataion');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_calender'])) {
            $data['id_calender'] = pk_decrypt($data['id_calender']);
        }
        $calender = $this->User_model->check_record('calender',array('id_calender'=>$data['id_calender']));
        $get_workflowid=$this->User_model->check_record('contract_workflow',array('calender_id'=>$data['id_calender']));
        // print_r($get_workflowid[0]['id_contract_workflow']);exit;
        if($get_workflowid[0]['id_contract_workflow']>0){
            $this->User_model->update_data('contract_workflow',array('status'=>0),array('parent_id'=>$get_workflowid[0]['id_contract_workflow']));
        }
        if(isset($data['is_workflow']) && $data['is_workflow']==0){
            /* ** */
            //Un locking the contracts
            if($calender[0]['relationship_category_id'] != '')
                $this->db->where_in('relationship_category_id',explode(',',$calender[0]['relationship_category_id']));
            if($calender[0]['bussiness_unit_id'] != '')
                $this->db->where_in('business_unit_id',explode(',',$calender[0]['bussiness_unit_id']));
            if($calender[0]['provider_id'] != '')
                $this->db->where_in('provider_name',explode(',',$calender[0]['provider_id']));
            if($calender[0]['contract_id'] != '')
                $this->db->where_in('id_contract',explode(',',$calender[0]['contract_id']));
            if(!$this->db->update('contract',array('is_lock'=>0))){
                $result = array('status'=>FALSE, 'message' => $this->lang->line('contract_not_unlock'), 'data'=>[]);
                $this->response($result, REST_Controller::HTTP_OK);
            }    
            //echo '<pre>'.$this->db->last_query();exit;
            /* ** */
        }else{
            $this->db->where('calender_id',$data['id_calender'])->delete('contract_workflow');
        }

        if($calender[0]['parent_calender_id'] == null)
            $cal_id = $calender[0]['id_calender'];
        else
            $cal_id = $calender[0]['parent_calender_id'];

        $this->db->where(array('id_calender'=>$cal_id))->or_where(array('parent_calender_id'=>$cal_id))->delete('calender');
        
        //echo '<pre>'.$this->db->last_query();
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>[]);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    function CreateCalenderEntry($review_data){
        // print_r($rev);exit;
        $cont_ids=explode(',',$review_data['contract_id']);
        if(!empty($cont_ids)){
            $this->Calender_model->lockconts(array('contract_ids'=>$cont_ids));
        }
        $parent_review_id = $this->Calender_model->insert_review($review_data);
        if($parent_review_id){
            
            //writing recurrence of months. montly or quarterly or yearly
            $countOfMonths = 0;

            $recurrence = $review_data["recurrence"];

            //if recurrence value grater than zero then only 
            if($recurrence>0){
                switch($recurrence){
                    case '1':
                        $countOfMonths = 1;//montly
                        break;
                    case '2':
                        $countOfMonths = 3;//quarterly
                        break;
                    case '3':
                        $countOfMonths = 12;//yearly
                }
                // $begin = new DateTime($review_data["date"]);
                // $end = new DateTime(date('Y-m-d', strtotime($review_data["recurrence_till"] . ' +1 day')));

                // $interval = DateInterval::createFromDateString($countOfMonths.' month');
                // $period = new DatePeriod($begin, $interval, $end);

                /*Counting Dates Between two dates starts*/
                $date1 = $review_data["date"];
                $date2 = $review_data["recurrence_till"];                
                
                $year = (int)substr($date1,0,4);
                $month = (int)substr($date1, 5, 2);
                $date = substr($date1, 8, 2);                
                
                $new_dates = array();

                for($i = 0;$i>=0;$i++){
                    if($month<10)
                        $month = '0'.$month;
                    
                    $new_date = $year.'-'.$month.'-'.$date;
                    if(validateDate($new_date)>0){		
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }
                    else{
                        $date_test = new DateTime($year.'-'.$month.'-01');
                        $date_test->modify('last day of this month');
                        $new_date = $date_test->format('Y-m-d');
                        if(strtotime($new_date) < strtotime($date2))
                            $new_dates[$i] = $new_date;
                        else
                            break;
                    }	
                    //echo '<br>'.$new_date;
                    if($month >= 12){		
                        $month = 0;
                        $year++;
                    }	
                    $month += $countOfMonths;	
                    if($month >= 12){
                        $month = $month-12;
                        $year++;
                    }
                        
                }
                //echo '<pre>'.print_r($new_dates);exit;
                /*Counting Dates Between two dates ends*/
                // print_r($new_dates);exit;
                if(count($new_dates)>1){
                    $query = "insert into calender(date,
                                                    created_on,
                                                    recurrence_till,
                                                    recurrence,
                                                    parent_calender_id,
                                                    customer_id,
                                                    relationship_category_id,
                                                    provider_relationship_category_id,
                                                    created_by,
                                                    bussiness_unit_id,
                                                    contract_id ,
                                                    provider_id ,
                                                    plan_executed ,
                                                    auto_initiate ,
                                                    workflow_name,
                                                    initiate_date,
                                                    type,
                                                    task_type
                                                    ) values";
    
                    foreach ($new_dates as $dt=>$dv) {
                        if($dt == 0)
                            continue;
                        
                            $query .= "('".$dv."',
                                            '".$review_data["created_on"]."',
                                            '".$review_data["recurrence_till"]."',
                                            $recurrence,
                                            $parent_review_id,
                                            '".$review_data["customer_id"]."',
                                            '".$review_data["relationship_category_id"]."',
                                            '".$review_data["provider_relationship_category_id"]."',
                                            '".$review_data["created_by"]."',
                                            '".$review_data["bussiness_unit_id"]."',
                                            '".$review_data["contract_id"]."',
                                            '".$review_data["provider_id"]."',
                                            '".$review_data["plan_executed"]."',
                                            '".$review_data["auto_initiate"]."',
                                            '".$review_data["workflow_name"]."',
                                            '".$dv."',
                                            'contract',
                                            'main_task'
                                        ),";
                        
                        
                    }
    
                    $query = rtrim($query,",").";";
                    if($this->db->query($query)){
                    //locking the contract when contract added to calender
                        $update_contract_for_isLock = $this->Calender_model->lock_contracts($review_data);
                        if(!$update_contract_for_isLock){
                            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'contract not updated');
                            $this->response($result, REST_Controller::HTTP_OK);
                        }    
                        $result = array('status'=>TRUE,'message'=>$this->lang->line('review_added_to_calender'),'data'=>'Review Added to Calender');
                        $this->response($result, REST_Controller::HTTP_OK);
                    } else {
                        $this->db->where('id_calender',$parent_review_id)->delete('calender');
                        $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'multiple insert query error');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }

                }
                $result = array('status'=>TRUE,'message'=>$this->lang->line('review_added_to_calender'),'data'=>'Review Added to Calender');
                $this->response($result, REST_Controller::HTTP_OK);
            } else {
                //locking the contract when contract added to calender
                $update_contract_for_isLock = $this->Calender_model->lock_contracts($review_data);
                if(!$update_contract_for_isLock){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'contract not updated 2');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                $result = array('status'=>TRUE,'message'=>$this->lang->line('review_added_to_calender'),'data'=>'Review Added to Calender');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        } else {
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'Calender not inserted');
            $this->response($result, REST_Controller::HTTP_OK);
        }// end if parent_review_id 

    }


}