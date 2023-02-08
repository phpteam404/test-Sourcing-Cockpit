<?php
class Calender_model extends CI_Model{
    
    function insert_review($data){
        $this->db->insert("calender",$data);
        return $this->db->insert_id();
    }
    function addContract_workflow($data)
    {
        // print_r($data);
         $this->db->insert_batch("contract_workflow",$data);
        return 1;
        // echo $this->db->last_query();
    }
    /*function get_contractsByRelationshipCategoryID($data){

        if(isset($data["business_ids"]) && $data["business_ids"]!=""){
            $this->db->where_in("id_business_unit", $data["business_ids"]);
            if(!isset($data["provider_ids"])){
                $this->db->group_by("p.id_provider");
            } 
        }

        if(isset($data["relationship_category_id"]) && $data["relationship_category_id"]){
            $this->db->where_in("c.relationship_category_id", $data["relationship_category_id"]);
            if(!isset($data["business_ids"])){
                $this->db->group_by("bu.id_business_unit");
            }

        }

        $this->db->select("bu.id_business_unit,bu.bu_name,p.id_provider ,p.provider_name ,c.id_contract,c.contract_name");
        $this->db->join("business_unit bu","bu.id_business_unit = c.business_unit_id");
        $this->db->join("provider p","p.id_provider = c.provider_name");
        
        if(isset($data['is_workflow']) && $data['is_workflow']==1){
            $workflow = 1;
        } else {
            $workflow = 0;
        }

        if(!isset($data['is_workflow']))
            $this->db->where("c.is_lock","0");

        if(isset($data["contract_ids"]) && $data["contract_ids"]!="")
            $this->db->where_in("id_contract", $data["contract_ids"]);

        if(isset($data["provider_ids"]) && $data["provider_ids"]!=""){
            $this->db->where_in("id_provider", $data["provider_ids"]);
            $this->db->group_by("c.id_contract");
        }

        $query = $this->db->order_by("c.contract_name")->get("contract c");
        // echo $this->db->last_query();exit;
        return $query->result_array();

    }*/

    function get_contractsByRelationshipCategoryID($data){
        //print_r($data);exit;
        if(isset($data["user_id"]) && $data["user_id"]!=""){
            $this->db->where_in("id_business_unit", $data["user_id"]);
            $this->db->group_by("bu.id_business_unit");
        }

        $this->db->select("bu.id_business_unit,bu.bu_name,p.id_provider ,p.provider_name ,c.id_contract,c.contract_name");
        $this->db->join("relationship_category rc","rc.id_relationship_category=relationship_category_id");
        $this->db->join("business_unit bu","bu.id_business_unit = c.business_unit_id");
        $this->db->join("provider p","p.id_provider = c.provider_name");
        
        // if(isset($data['is_workflow']) && $data['is_workflow']==1){
        //     $workflow = 1;
        // } else {
        //     $workflow = 0;
        // }

        // $this->db->where("c.is_workflow",$workflow);

        if(!isset($data['is_workflow']))
            $this->db->where("c.is_lock","0");


        $query = $this->db->order_by("c.contract_name")->get("contract c");
        //echo $this->db->last_query();exit;
        return $query->result_array();

    }

    function getBusinessUnits($data){        
    // echo '<pre>'.print_r($data);exit;
        $this->db->select('bu.id_business_unit,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name')->from('business_unit bu');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('contract c','bu.id_business_unit = c.business_unit_id','right');
        $this->db->where('bu.status',1);

        if(!isset($data['is_workflow'])){
            if(isset($data['calender_contracts']) && count($data['calender_contracts']) > 0 ){
                //$this->db->group_start();
                $this->db->where_in('c.id_contract',$data['calender_contracts']);
                $this->db->or_where("c.is_lock","0");
                //$this->db->group_end();
            }else{
               $this->db->where("c.is_lock","0");
            }
        }

        //validating customer_id
        if(isset($data['customer_id'])){
            $this->db->where('bu.customer_id',$data['customer_id']);
        }

        //validating user_id
        if(isset($data['user_id'])){
            $this->db->where('(c.contract_owner_id = '.$data['user_id'].' OR c.delegate_id = '.$data['user_id'].')');
        }

        //validating is_workflow
        if(!isset($data['is_workflow'])){
            $this->db->where("c.is_lock","0");
        }

        $this->db->where('c.is_deleted = 0');
        $this->db->group_by('bu.id_business_unit');
        $this->db->order_by('bu.bu_name','asc');
        $result = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $result->result_array();
    }

    function getContractRelationshipCategory($data){
        // print_r($data);
        $this->db->select('rcl.relationship_category_name,rc.id_relationship_category')->from('relationship_category_language rcl');
        $this->db->join('relationship_category rc ',' rcl.relationship_category_id = rc.id_relationship_category','left');
        $this->db->join('contract c ',' rcl.relationship_category_id = c.relationship_category_id','left');
        $this->db->where('rc.relationship_category_status',1);
        $this->db->join('business_unit bu ',' c.business_unit_id = bu.id_business_unit','left');
        if(isset($data['business_ids'])){
            $this->db->where_in('c.business_unit_id',count($data['business_ids'])>0?$data['business_ids']:array(0));
        }

        if(!isset($data['is_workflow'])){
            if(isset($data['calender_contracts']) && count($data['calender_contracts']) > 0 ){
                $this->db->group_start();
                $this->db->where_in('c.id_contract',$data['calender_contracts']);
                $this->db->or_where("c.is_lock","0");
                $this->db->group_end();
            }else{
               $this->db->where("c.is_lock","0");
            }
        }

        if(isset($data['customer_id'])){
            $this->db->where('rc.customer_id',$data['customer_id']);
        }
        if(isset($data['can_review'])){
            $this->db->where('rc.can_review',$data['can_review']);
        }
        if(isset($data['user_id'])){
            $this->db->where('(c.contract_owner_id = '.$data['user_id'].' OR c.delegate_id = '.$data['user_id'].')');
        }
        // if(!isset($data['is_workflow'])){
        //     $this->db->where("c.is_lock","0");
        // }
        $this->db->where('c.is_deleted = 0');
        if(isset($data['type']) && $data['type']=='project'){
            $this->db->where('c.type','project');
        }
        else{
            $this->db->where('c.type','contract');
        }
        $this->db->group_by('rc.id_relationship_category');
        $this->db->order_by('rcl.relationship_category_name','asc');
        $result = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $result->result_array();
    }
    
    // function getProviderRelationshipCategory($data){
    //     $this->db->select('prcl.relationship_category_name,pr.id_provider_relationship_category')->from('provider_relationship_category pr');
    //     $this->db->join('provider_relationship_category_language prcl','pr.id_provider_relationship_category=prcl.provider_relationship_category_id','left');
    //     $this->db->join('provider p ',' p.category_id = pr.id_provider_relationship_category','left');
    //     $this->db->join('contract c ',' p.id_provider = c.provider_name','left');
    //     $this->db->where('pr.provider_relationship_category_status',1);
    //     $this->db->join('business_unit bu ',' c.business_unit_id = bu.id_business_unit','left');
    //     if(isset($data['business_ids'])){
    //         $this->db->where_in('c.business_unit_id',count($data['business_ids'])>0?$data['business_ids']:array(0));
    //     }

    //     if(!isset($data['is_workflow'])){
    //         if(isset($data['calender_contracts']) && count($data['calender_contracts']) > 0 ){
    //             $this->db->group_start();
    //             $this->db->where_in('c.id_contract',$data['calender_contracts']);
    //             $this->db->or_where("c.is_lock","0");
    //             $this->db->group_end();
    //         }else{
    //            $this->db->where("c.is_lock","0");
    //         }
    //     }

    //     if(isset($data['customer_id'])){
    //         $this->db->where('pr.customer_id',$data['customer_id']);
    //     }
    //     if(isset($data['can_review'])){
    //         $this->db->where('pr.can_review',$data['can_review']);
    //     }
    //     if(isset($data['user_id'])){
    //         $this->db->where('(c.contract_owner_id = '.$data['user_id'].' OR c.delegate_id = '.$data['user_id'].')');
    //     }
    //     // if(!isset($data['is_workflow'])){
    //     //     $this->db->where("c.is_lock","0");
    //     // }
    //     $this->db->where('c.is_deleted = 0');
    //     if(isset($data['type']) && $data['type']=='project'){
    //         $this->db->where('c.type','project');
    //     }
    //     else{
    //         $this->db->where('c.type','contract');
    //     }
    //     $this->db->group_by('pr.id_provider_relationship_category');
    //     $this->db->order_by('prcl.relationship_category_name','asc');
    //     $result = $this->db->get();
    //     // echo '<pre>'.$this->db->last_query();exit;
    //     return $result->result_array();
    // }

    function getProviderRelationshipCategory($data){     
        $this->db->select('prcl.relationship_category_name,pr.id_provider_relationship_category');
        $this->db->from('provider_relationship_category pr');
        $this->db->join('provider_relationship_category_language prcl','pr.id_provider_relationship_category=prcl.provider_relationship_category_id','left');
        if(isset($data['customer_id']))
            $this->db->where('pr.customer_id',$data['customer_id']);
        if(isset($data['can_review'])){
            $this->db->where('pr.can_review',$data['can_review']);
        }
        if(!empty($data['status'])){
            $this->db->where('pr.provider_relationship_category_status',$data['status']);
        }
        $this->db->group_by('pr.id_provider_relationship_category');
        $this->db->order_by('prcl.relationship_category_name','asc');
        $result = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $result->result_array();
    }

    function getProvider($data){
        //echo '<pre>'.print_r($data);exit;
        $this->db->select('p.provider_name,p.id_provider')->from('contract c');
        $this->db->join('provider p','c.provider_name = p.id_provider','left');
        $this->db->join('business_unit bu','c.business_unit_id = bu.id_business_unit','left');
        $this->db->join('provider_relationship_category prc','p.category_id = prc.id_provider_relationship_category','left');
        if($this->session_user_info->user_role_id == 3)
            $this->db->where('c.contract_owner_id',$this->session_user_id);
        if($this->session_user_info->user_role_id == 4)
            $this->db->where('c.delegate_id',$this->session_user_id);
        if(!isset($data['is_workflow'])){
            if(isset($data['calender_contracts']) && count($data['calender_contracts']) > 0 ){
                $this->db->group_start();
                $this->db->where_in('c.id_contract',$data['calender_contracts']);
                $this->db->or_where("c.is_lock","0");
                $this->db->group_end();
            }else{
               $this->db->where("c.is_lock","0");
            }
        }

        if(!empty($data['customer_id'])){
            $this->db->where('bu.customer_id',$data['customer_id']);
        }
        if(!empty($data['business_ids'])){
            $this->db->where_in("c.business_unit_id",$data['business_ids']);
        }
        if(!empty($data['relationship_category_id'])){
            $this->db->where_in("c.relationship_category_id",$data['relationship_category_id']);
        }
        if(!empty($data['provider_relationship_category_id'])){
            $this->db->where_in("p.category_id",$data['provider_relationship_category_id']);
        }
        // if(!isset($data['is_workflow'])){
        //     $this->db->where("c.is_lock","0");
        // }
        if(isset($data['type']) && $data['type']=='project'){
            $this->db->where('c.type','project');
        }
        else{
            $this->db->where('c.type','contract');
        }
        $this->db->where('c.is_deleted = 0');
        $this->db->group_by('p.id_provider');
        $this->db->order_by('p.provider_name','asc');
        $result = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $result->result_array();
    }

    function getContracts($data){
      
        $this->db->select('c.id_contract,c.contract_name')->from('contract c');
        $this->db->join('business_unit bu','c.business_unit_id = bu.id_business_unit','left');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('provider_relationship_category prc','prc.id_provider_relationship_category = p.category_id','left');
        
        //validating customer_id
        if(isset($data['customer_id'])){
            $this->db->where('bu.customer_id',$data['customer_id']);
        }

        //validating user_id
        if(isset($data['user_id'])){
            $this->db->where('(c.contract_owner_id = '.$data['user_id'].' OR c.delegate_id = '.$data['user_id'].')');
        }

        //validating is_workflow
        if(!isset($data['is_workflow'])){
            if(isset($data['calender_contracts']) && count($data['calender_contracts']) > 0 ){
                $this->db->group_start();
                $this->db->where_in('c.id_contract',$data['calender_contracts']);
                $this->db->or_where("c.is_lock","0");
                $this->db->group_end();
            }else{
               $this->db->where("c.is_lock","0");
            }
            $this->db->where('c.can_review',1);
        } 

        //validating business_ids
        if(!empty($data['business_ids'])){
            $this->db->where_in("c.business_unit_id",$data['business_ids']);
        }
        if(!empty($data['business_unit_id']) && $data['business_unit_id']!=""){
            $this->db->where_in("c.business_unit_id",$data['business_unit_id']);
        }

        //validating relationship_category_id
        if(!empty($data['relationship_category_id']) && $data["relationship_category_id"]!=""){
            $this->db->where_in("c.relationship_category_id",$data['relationship_category_id']);
        }

        //validating provider ids 
        if(!empty($data['provider_ids']) && $data["provider_ids"]!=""){
            $this->db->where_in("c.provider_name",$data['provider_ids']);
        }
        if(!empty($data['provider_id']) && $data["provider_id"]!=""){
            $this->db->where_in("c.provider_name",$data['provider_id']);
        }
        //validating provider relationship category id
        if(!empty($data['provider_relationship_category_id']) && $data["provider_relationship_category_id"]!=""){
            $this->db->where_in("prc.id_provider_relationship_category",$data['provider_relationship_category_id']);
        }

        //validating contract_id
        if(!empty($data['contract_id']) && $data['contract_id']!=""){
            $this->db->where_in("c.id_contract",$data['contract_id']);
        }
        if(isset($data['type']) && $data['type']=='project'){
            $this->db->where('c.type','project');
            $this->db->where('c.project_status','1');
        }
        else{
            $this->db->where('c.type','contract');
        }
        $this->db->where('c.is_deleted = 0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $result = $this->db->get();
        // echo '<pre>'.$this->db->last_query();
        return $result->result_array();
    }

    function getCompletedContracts($data){
      
        $this->db->select('c.id_contract,c.contract_name')->from('contract c');
        if(isset($data['completed_contracts']) && $data['completed_contracts']!=""){
            $this->db->where_in("c.id_contract",$data['completed_contracts']);
        }
        $result = $this->db->get();
        //echo '<pre>'.$this->db->last_query();
        return $result->result_array();
    }


    public function getCalenderReview($data)
    {
        // print_r($data);exit;
        $where = [];
        if(isset($data['customer_id'])){
            $where['c.customer_id'] = $data['customer_id'];
            $where['c.status'] = 1;
            $where['c.task_type'] = 'main_task';

        }
        // if($this->session_user_info->user_role_id !=2){
        //     $where['c.created_by'] = $this->session_user_id;//get the login user role id :: as we are not allowing to create other bu plannnig
        // }
        if(isset($data["date"])){
            //extract the month and year form given date
            $date = $data['date'];
            $d = date_parse_from_format("Y-m-d", $date);

            if($data["filterType"]=="date")
            {
                $where['MONTH(c.date)']=$d["month"];
                $where['YEAR(c.date)']=$d["year"];
                $where['day(c.date)']=$d["day"];
            }

            if($data["filterType"]=="month")
            {
                $where['MONTH(c.date)']=$d["month"];
                $where['YEAR(c.date)']=$d["year"];
            }
            
            if($data["filterType"]=="year"){
                $where['YEAR(c.date)']=$d["year"];
            }

            if(isset($data['planType'])){
                if(strtolower($data['planType']) == 'review')
                    $where['c.is_workflow'] = 0;
                else
                    $where['c.is_workflow'] = 1;
            }
        }

        $this->db->select('CONCAT(u.first_name," ",u.last_name) as username,`rrm`.`recurrence_name`, 
                        c.bussiness_unit_id,c.completed_contract_id,c.contract_id,c.customer_id,c.provider_id,c.id_calender,c.relationship_category_id,c.provider_relationship_category_id,c.`date`,c.recurrence,c.recurrence_till,`c`.`parent_calender_id`,c.is_workflow,c.created_by,c.workflow_id , c.workflow_id workflow_template_id,c.workflow_name,c.auto_initiate,c.type as activity_type,c.type,c.is_workflow as workflow_info,IF(`c`.`is_workflow`=1, "task", "review") as taskOrreview');
        $this->db->from("calender c");
        $this->db->join('review_recurrence_master rrm','c.recurrence=rrm.id_review_recurrence','left');
        $this->db->join('user u','c.created_by=u.id_user','left');
        // if(isset($data['user_role_id']) && $data['user_role_id']!=2)//check the login user admin or not 
        // {
        //     $this->db->where('CONCAT(",", `bussiness_unit_id`, ",") REGEXP ",'.$data['business_unit_id'].',"', NULL, FALSE);//pass the all business unit of login user
        // }
        $this->db->where($where);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
                
        $this->db->group_by("c.id_calender");
                        // ->get();
        //added for search                
        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        $this->db->select("*")->from("($new_query) as unionTable");
        if(isset($data['user_role_id']) && $data['user_role_id']!=2)//check the login user admin or not 
        {
            $this->db->where('CONCAT(",", `bussiness_unit_id`, ",") REGEXP ",'.$data['business_unit_id'].',"', NULL, FALSE);//pass the all business unit of login user
        }
        if(isset($data['search']))
        {
            $this->db->group_start();
            $this->db->like('username', $data['search'], 'both');
            $this->db->or_like('taskOrreview', $data['search'], 'both');
            $this->db->or_like('workflow_name', $data['search'], 'both');
            $this->db->group_end();
        }
        //ends here
        $clone_db = clone $this->db;
        $count_query = $clone_db->get();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
        $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        $result_query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result_query = $result_query->result_array();

        return array('total_count'=>$count_query->num_rows(),'data'=>$result_query);
        

    }

    function getNames_by_ID($where_key,$where_val,$column_name,$table){
        return $this->db->select("group_concat($column_name) as $column_name")
                        ->where_in($where_key,$where_val,false)
                        ->get($table)
                        ->row();
    }

    function getEvents($data){
        $date = $data['date'];
        $review = array();
        $workflow = array();
        $d = date_parse_from_format("Y-m-d", $date);
        $custom_where='0=0'; //the customer_where defined for pass where condition for pass login user bussiness unit
        // if($this->session_user_info->user_role_id !=2)//check the login user is customer admin or not
        // {
        //     $custom_where='CONCAT(",", `bussiness_unit_id`, ",") REGEXP ",'.$data['business_unit_id'].',"';
        // }

        //echo '==='.date('D M d Y H:i:s',strtotime($data['date'])).' GMT+0100';exit;
        // if($this->session_user_info->user_role_id !=2){
        //     $where['c.created_by'] = $this->session_user_id;//get the login user role id :: as we are not allowing to create other bu plannnig
        // }
        $where['c.status'] = 1;
        $where['c.task_type'] = 'main_task'; //for getting main tasks only
        if($data["filterType"]=="year"){
            $where['YEAR(c.date)']=$d["year"];
            $where['c.customer_id']=$data['customer_id'];
            for($i = 0; $i < 12; $i++){
                $where['MONTH(c.date)']=$i+1;//Month

                $where['c.is_workflow'] = 0;
                //$result = $this->db->select('*')->from('calender c')->where($custom_where,NULL,FALSE)->where($where)->get()->result_array();
                $result = $this->gettingCalenderCount($custom_where,$where,$data);
                $review[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.($i+1).'-1')).' GMT+0100';
                $review[$i]['count'] = count($result);

                $where['c.is_workflow'] = 1;
                //$result = $this->db->select('*')->from('calender c')->where($custom_where,NULL,FALSE)->where($where)->get()->result_array();
                $result = $this->gettingCalenderCount($custom_where,$where,$data);
                $workflow[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.($i+1).'-1')).' GMT+0100';
                $workflow[$i]['count'] = count($result);
            }
        }
        if($data["filterType"]=="month"){
            $where['MONTH(c.date)']=$d["month"];
            $where['YEAR(c.date)']=$d["year"];
            $where['c.customer_id']=$data['customer_id'];
            $days = cal_days_in_month(CAL_GREGORIAN, $d["month"], $d["year"]);
            for($i = 0; $i < $days; $i++){
                $where['day(c.date)']=$i+1;//Date;

                $where['c.is_workflow'] = 0;
                // $result = $this->db->select('*')->from('calender c')->where($custom_where,NULL,FALSE)->where($where)->get()->result_array();
                $result = $this->gettingCalenderCount($custom_where,$where,$data);
                $review[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.$d["month"].'-'.($i+1))).' GMT+0100';
                $review[$i]['count'] = count($result);
                $where['c.is_workflow'] = 1;
                $result = $this->gettingCalenderCount($custom_where,$where,$data);
                //$result = $this->db->select('*')->from('calender c')->where($custom_where,NULL,FALSE)->where($where)->get()->result_array();
                $workflow[$i]['date'] = date('D M d Y H:i:s',strtotime($d["year"].'-'.$d["month"].'-'.($i+1))).' GMT+0100';
                $workflow[$i]['count'] = count($result);
            }
        }
            
        return array('review'=>$review,'workflow'=>$workflow);
        
    }

    function lock_contracts($data){
        // //checking business_unit_id is existed or not, if exists then based on the business_unit_it locking the contract 
        // if(isset($data["bussiness_unit_id"]) && $data["bussiness_unit_id"] !="")
        //     $this->db->where_in("business_unit_id",explode(",",$data["bussiness_unit_id"]));
        // //checking relationship_category_id is existed or not, if exists then based on the relationship_category_id locking the contract 
        // if(isset($data["relationship_category_id"]) && $data["relationship_category_id"]!="")
        //     $this->db->where_in("relationship_category_id",explode(",",$data["relationship_category_id"]));
        // //checking provider_id is existed or not, if exists then based on the provider_id locking the contract 
        // if(isset($data["provider_id"]) && $data["provider_id"]!="")
        //     $this->db->where_in("provider_name",explode(",",$data["provider_id"]));
        //checking contract_id is existed or not, if exists then based on the contract_id locking the contract 
        if(isset($data["contract_id"]) && $data["contract_id"]!="")
            $this->db->where_in("id_contract",explode(",",$data["contract_id"]));
            

        return $this->db->update("contract c",array("is_lock"=>1));

        // echo $this->db->last_query();
    }

    function check_contract_in($data){

         $this->db->select("id_contract");
        $this->db->from("contract");
        $this->db->where("is_deleted",0);

        if(isset($data["is_lock"]))    
            $this->db->where("is_lock",$data["is_lock"]);
        
        if(isset($data["calender_bus"]))    
            $this->db->where_in("business_unit_id",$data["calender_bus"]);
        
        if(isset($data["calender_cat_ids"]))    
            $this->db->where_in("relationship_category_id",$data["calender_cat_ids"]);

        if(isset($data["calender_providers"]))    
            $this->db->where_in("provider_name",$data["calender_providers"]);

        return $this->db->get()->result_array();  
                
    }

    function delete_workflow($data){
        //getting calender_id from contract_workflow by id_contract_workflow
        $calender_data = $this->db->select("calender_id,contract_id")->where("id_contract_workflow",$data["id_contract_workflow"])->get("contract_workflow")->row();
        // echo "1=>".$this->db->last_query();
        //getting contract_ids from calender table by calender_id
        $contracts_data = $this->db->select("contract_id")->where("id_calender",$calender_data->calender_id)->get("calender")->row();
        // echo "2=>".$this->db->last_query();

        //delerte workflow using id_contract_workflow
        if($this->db->where("id_contract_workflow",$data["id_contract_workflow"])->delete("contract_workflow")){
            // echo "3=>".$this->db->last_query();
            //checking contrats data is avaialbe or not
            if($contracts_data->contract_id!=""){
                $contract_ids = $contracts_data->contract_id;
                $contractIdRemove = $calender_data->contract_id;

                $contract_ids_array = explode(',', $contract_ids);
                $findSkill = array_search($contractIdRemove, $contract_ids_array);
                if ($findSkill !== false)
                    unset($contract_ids_array[$findSkill]);
                $contract_ids = implode(',', $contract_ids_array);
                
                //checking after remove needle from an array
                if($contract_ids){
                    if(!$this->db->where("id_calender",$calender_data->calender_id)->update("calender",array("contract_id"=>$contract_ids))){
                        return false;
                    }
                    // echo "4=>".$this->db->last_query();
                }else{
                    if(!$this->db->where("id_calender",$calender_data->calender_id)->delete("calender")){
                        return false;
                    }
                    // echo "4=>".$this->db->last_query();
                }

                return true;
            } //if condition end
        }//workflow delete if end 

        return false;
    }
    public function getbunameswithcountryname($data=null){
        $this->db->select('group_concat(IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name)) as bu_name');
        $this->db->from('`business_unit` bu');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        if(!empty($data['id_business_unit'])){
            $this->db->where_in('bu.id_business_unit',$data['id_business_unit']);
        }
        if(!empty($data['customer_id'])){
            $this->db->where_in('bu.customer_id',$data['customer_id']);
        }
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }
    public function filetercontractids($data=null){
        $this->db->select('id_contract as contract_id');
        $this->db->from('contract');
        if(!empty($data['contract_owner_id'])){
            $this->db->where('contract_owner_id',$data['contract_owner_id']);
        }
        if(!empty($data['delegate_id'])){
            $this->db->where('delegate_id',$data['delegate_id']);
        }
        $this->db->where_in('id_contract',$data['contract_ids']);
        $this->db->where('is_deleted',0);
        $query = $this->db->get();
        // echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function lockconts($data){
        $this->db->where_in("id_contract",$data['contract_ids']);
        $this->db->update("contract",array("is_lock"=>1));
        return 1;
    }
    public  function get_review_info_of_contract($data=null){
        $this->db->select('`id_contract_review`,contract_workflow_id,contract_review_status');
        $this->db->from('contract_review');
        $this->db->where('contract_id',$data['contract_id']);
        $this->db->where('is_workflow',0);
        $this->db->order_by('id_contract_review','desc');
        $this->db->limit('1');
        $query = $this->db->get();
        // echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function get_contract_workflow_info($data=null){
        $this->db->select('cw.workflow_status,cw.id_contract_workflow,cr.id_contract_review');
        $this->db->from('contract_workflow cw');
        $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
        $this->db->where('cw.contract_id',$data['contract_id']);
        $this->db->where('cw.calender_id',$data['calender_id']);
        $query = $this->db->get();
        // echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function gettingCalenderCount($custom_where,$where,$data=null)
    {
        $this->db->select('c.*,CONCAT(u.first_name," ",u.last_name) as username,IF(`c`.`is_workflow`=1, "task", "review") as taskOrreview');
        $this->db->from("calender c");
        $this->db->join('user u','c.created_by=u.id_user','left');
        $this->db->where($custom_where,NULL,FALSE);
        $this->db->where($where);    
        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        $this->db->select("*")->from("($new_query) as unionTable");
        // print_r($data);exit;
        $this->db->where('CONCAT(",", `bussiness_unit_id`, ",") REGEXP ",'.$data['business_unit_id'].',"', NULL, FALSE);//pass the all business unit of login user
        if(isset($data['search']))
        {
            $this->db->group_start();
            $this->db->like('username', $data['search'], 'both');
            $this->db->or_like('taskOrreview', $data['search'], 'both');
            $this->db->or_like('workflow_name', $data['search'], 'both');
            $this->db->group_end();
        }   
        $result_query = $this->db->get();
        $result = $result_query->result_array();
        return $result;
    }
}
?>