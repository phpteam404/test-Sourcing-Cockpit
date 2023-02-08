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
        
        //echo '<pre>'.$this->db->last_query();exit;
        $this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
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
        if($this->session_user_info->user_role_id!=7)
            $this->session_user_business_units=array_merge($this->session_user_business_units,$this->session_user_review_business_units);
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units));
        // $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        // $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        // $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        // $this->session_user_customer_all_users=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_master_countries=$this->Validation_model->getCountries();
        // $this->session_user_master_templates=$this->Validation_model->getTemplates();
        // $this->session_user_master_customers=$this->Validation_model->getCustomers();
        // $this->session_user_master_users=$this->Validation_model->getUsers();
        // $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();

        //echo '$this->session_user_id'.$this->session_user_id;
        //$this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
    }

    function add_post(){

        //get post values and storing into $data variable
        $data = $this->input->post();

        //checking loggedin user has permissions or not
        // print_r($this->session_user_info);exit;
        if(in_array($this->session_user_info->user_role_id,array("2,3,4"))){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'sddsds');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //validate post values
        $this->form_validator->add_rules('business_unit_id', array('required'=>$this->lang->line('business_unit_id_req')));
        $this->form_validator->add_rules('contract_id', array('required'=>$this->lang->line('contract_id_req')));
        $this->form_validator->add_rules('provider_id', array('required'=>$this->lang->line('provider_id_req')));
        $this->form_validator->add_rules('relationship_category_id', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));// treate this param as review_by
        $this->form_validator->add_rules('recurrence_till', array('required'=>$this->lang->line('recurrence_till_req')));
        $this->form_validator->add_rules('recurrence', array('required'=>$this->lang->line('recurrence_req')));//recurrence means montly, queterly, halferly, yearly
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
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
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
            $data['relationship_category_id']=implode(',',$relationship_category_id);
        }

        if(isset($data['contract_id']) && count(explode(',',$data['contract_id']))>0){
            $contract_id_exp=explode(',',$data['contract_id']);
            $contract_id=array();
            // print_r($this->session_user_contracts);exit;
            foreach($contract_id_exp as $k=>$v){
                    $contract_id_chk = pk_decrypt($v);
                    if(!in_array($contract_id_chk,$this->session_user_contracts)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $contract_id[]=pk_decrypt($v);
            }
            // print_r($contract_id);exit;
            $data['contract_id']=implode(',',$contract_id);
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
        $review_data["customer_id"] = $data["customer_id"];
        $review_data["bussiness_unit_id"] = $data["business_unit_id"];
        $review_data["contract_id"] = $data["contract_id"];
        $review_data["provider_id"] = $data["provider_id"];
        $review_data["relationship_category_id"] = $data["relationship_category_id"];
        $review_data["date"] = $data["date"];
        $review_data["recurrence_till"] = $data["recurrence_till"];
        $review_data["recurrence"] = $data["recurrence"];
        $review_data["created_by"] = $data["created_by"];

        //unset post data
        unset($data);
        // print_r($review_data);exit;
        // echo $update_contract_for_isLock = "update contract set is_lock=1 where id_contract in(".$review_data["contract_id"].")";
        // exit;
        $parent_review_id = $this->Calender_model->insert_review($review_data);
        // echo $this->db->last_query();exit;
        if($parent_review_id){
            
            //writing recurrence of months. montly or quarterly or yearly
            $countOfMonths = 0;

            $recurrence = $review_data["recurrence"];

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

            $begin = new DateTime($review_data["date"]);
            $end = new DateTime($review_data["recurrence_till"]);

            $interval = DateInterval::createFromDateString($countOfMonths.' month');
            $period = new DatePeriod($begin, $interval, $end);

            $i=0;
            $query = "insert into calender(date,
                                            recurrence_till,
                                            recurrence,
                                            parent_calender_id,
                                            customer_id,
                                            relationship_category_id,
                                            created_by,
                                            bussiness_unit_id,
                                            contract_id ,
                                            provider_id
                                            ) values";
            foreach ($period as $dt=>$k) {
                if($i>0)//this condition for to avoid 0 position, bcz in zero position srart date displaying
                    $query .= "('".$k->format("Y-m-d")."',
                                    '".$end."',
                                    $recurrence,
                                    $parent_review_id,
                                    '".$review_data["customer_id"]."',
                                    '".$review_data["relationship_category_id"]."',
                                    '".$review_data["created_by"]."',
                                    '".$review_data["bussiness_unit_id"]."',
                                    '".$review_data["contract_id"]."',
                                    '".$review_data["provider_id"]."'
                                ),";
                else 
                    $end = $k->format("Y-m-d");
                $i++;
            }

            $query = rtrim($query,",").";";

            // echo $query;exit;

            if($this->db->query($query)){

                //locking the contract when contract added to calender
                $update_contract_for_isLock = "update contract set is_lock=1 where id_contract in(".$review_data["contract_id"].")";
                if(!$this->db->query($update_contract_for_isLock)){
                    $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'contract not updated');
                    $this->response($result, REST_Controller::HTTP_OK);
                }

                $result = array('status'=>TRUE,'message'=>$this->lang->line('review_added_to_calender'),'data'=>'Review Added to Calender');
                $this->response($result, REST_Controller::HTTP_OK);
            } else {
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'multiple insert query error');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        } else {
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'Calender not inserted');
            $this->response($result, REST_Controller::HTTP_OK);
        }
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
                    $business_ids_chk = pk_decrypt($v);
                    if(!in_array($business_ids_chk,$this->session_user_business_units)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'Business id not found');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $business_ids[]=pk_decrypt($v);
            }
            $data['business_ids']=implode(',',$business_ids);
        }

        if(isset($data['provider_ids']) && count(explode(',',$data['provider_ids']))>0){
            $provider_ids_exp=explode(',',$data['provider_ids']);
            $provider_ids=array();
            foreach($provider_ids_exp as $k=>$v){
                $provider_ids[]=pk_decrypt($v);
            }
            $data['provider_ids']=implode(',',$provider_ids);
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
            $data['relationship_category_id']=implode(',',$relationship_category_id);
        }

        if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0){
            $contract_ids_exp=explode(',',$data['contract_ids']);
            $contract_ids=array();
            // print_r($this->session_user_contracts);exit;
            foreach($contract_ids_exp as $k=>$v){
                    $contract_ids_chk = pk_decrypt($v);
                    if(!in_array($contract_ids_chk,$this->session_user_contracts)){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                $contract_ids[]=pk_decrypt($v);
            }
            // print_r($contract_ids);exit;
            $data['contract_ids']=implode(',',$contract_ids);
        }
    

        // print_r($data);exit;
        //getting list of categories by customer_id
        $result =[];
        if(isset($data["customer_id"]) && $data["customer_id"]!=0){
            
            if(!isset($data['is_workflow']))
                $data['can_review'] = 1;

            $relationship_list = $this->Relationship_category_model->getRelationshipCategory($data);
            
            foreach($relationship_list as $k =>$v){
                $result[$k]["id_relationship_category"] = pk_encrypt($v["id_relationship_category"]);
                $result[$k]["id_relationship_category_id"] = $v["id_relationship_category"];
                $result[$k]["relationship_category_quadrant"] = $v["relationship_category_quadrant"];
                $result[$k]["relationship_category_name"] = $v["relationship_category_name"];
            }
        } else {
            
            //getting list of contracts based on relationship caregory id and if contract_id is exist then it will add to where condition
            // getting bussiness list
            $result["business_unit"] = $this->Calender_model->get_contractsByRelationshipCategoryID(
                array("relationship_category_id"=>$data["relationship_category_id"])
            );
            foreach($result["business_unit"] as $k=>$v){
                // print_r($v);exit;
                $result["business_unit"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
                $result["business_unit"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
                $result["business_unit"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
            }


            $result["provider"] = $this->Calender_model->get_contractsByRelationshipCategoryID(
                array("business_ids"=>$data["business_ids"],"relationship_category_id"=>$data["relationship_category_id"])
            );
            foreach($result["provider"] as $k=>$v){
                // print_r($v);exit;
                $result["provider"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
                $result["provider"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
                $result["provider"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
            }

            $result["contract"] = $this->Calender_model->get_contractsByRelationshipCategoryID(
                array("business_ids"=>$data["business_ids"],"relationship_category_id"=>$data["relationship_category_id"],"provider_ids"=>$data["provider_ids"])
            );
            foreach($result["contract"] as $k=>$v){
                // print_r($v);exit;
                $result["contract"][$k]['id_business_unit'] = pk_encrypt($v['id_business_unit']);
                $result["contract"][$k]['id_provider'] = pk_encrypt($v['id_provider']);
                $result["contract"][$k]['id_contract'] = pk_encrypt($v['id_contract']);
            }
        }
        
        // print_r($result);exit;
        $result = array('status'=>TRUE, 'message' =>array('message'=>$this->lang->line('list_of_categories')), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }

    function list_get(){
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
            
        //getting all review by of particular date
        $result=$this->Calender_model->getCalenderReview($data);
            // echo $this->db->last_query();

        foreach($result as $k=>$r){
            $contract_name = $this->Calender_model->getNames_by_ID("id_contract",$r["contract_id"],"contract_name","contract");
            $result[$k]["contract_name"] = array_unique(explode(',',$contract_name->contract_name));

            $bu_name = $this->Calender_model->getNames_by_ID("id_business_unit",$r["bussiness_unit_id"],"bu_name","business_unit");
            $result[$k]["bu_name"] = array_unique(explode(',',$bu_name->bu_name));

            $provider_name = $this->Calender_model->getNames_by_ID("id_provider",$r["provider_id"],"provider_name","provider");
            $result[$k]["provider_name"] = array_unique(explode(',',$provider_name->provider_name));
        }
        // print_r($result);exit;

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
}