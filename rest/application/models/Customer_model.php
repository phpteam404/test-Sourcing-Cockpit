<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function customerList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('c.*,co.country_name,count(con.id_contract) as contracts_count');
        $this->db->from('customer c');
        $this->db->join('country co','c.country_id=co.id_country','left');
        $this->db->join('business_unit b','c.id_customer=b.customer_id','left');
        $this->db->join('contract con','b.id_business_unit=con.business_unit_id and con.is_deleted=0','left');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.company_name', $data['search'], 'both');
            $this->db->or_like('c.company_address', $data['search'], 'both');
            $this->db->or_like('c.city', $data['search'], 'both');
            $this->db->or_like('co.country_name', $data['search'], 'both');
            $this->db->group_end();
        }
            //$this->db->where('(c.company_name like "%'.$data['search'].'%" or c.company_address like "%'.$data['search'].'%" or c.city like "%'.$data['search'].'%" or co.country_name like "%'.$data['search'].'%")');
        $this->db->group_by('c.id_customer');
        if(!empty($data['status']) && $data['status']==1){
            $this->db->where('c.company_status',1);
        }
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->get()->num_rows();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('c.id_customer','DESC');

        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getCustomer($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('c.*,co.country_name');
        $this->db->from('customer c');
        $this->db->join('country co','c.country_id=co.id_country','left');
        if(isset($data['id_customer']))
            $this->db->where('c.id_customer',$data['id_customer']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.company_name', $data['search'], 'both');
            $this->db->or_like('c.company_address', $data['search'], 'both');
            $this->db->or_like('c.city', $data['search'], 'both');
            $this->db->or_like('co.country_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.company_name like "%'.$data['search'].'%"
            or c.company_address like "%'.$data['search'].'%"
            or c.city like "%'.$data['search'].'%"
            or co.country_name like "%'.$data['search'].'%")');*/
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addCustomer($data)
    {
        $this->db->insert('customer', $data);
        return $this->db->insert_id();
    }

    public function updateCustomer($data)
    {
        $this->db->where('id_customer', $data['id_customer']);
        $this->db->update('customer', $data);
        return 1;
    }

    public function getCustomerAdminList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('u.id_user,ur.user_role_name,u.first_name,u.last_name,CONCAT(u.first_name," ",u.last_name) as name,u.email,u.gender,u.user_status,u.last_logged_on,u.first_name,u.last_name,u.is_blocked');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            // $this->db->like('u.first_name', $data['search'], 'both');
            // $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('CONCAT(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->or_like('u.email', $data['search'], 'both');
            $this->db->or_like('u.gender', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['user_status']))
            $this->db->where('u.user_status',$data['user_status']);
        /*if(isset($data['search']))
            $this->db->where('(u.first_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.email like "%'.$data['search'].'%"
            or u.gender like "%'.$data['search'].'%")');*/
        $this->db->where('u.user_role_id',2);
        $this->db->where('u.is_deleted', 0);
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('u.id_user','DESC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getCustomerUserList($data)
    {
        // print_r($data);exit;
        /*user role not in (1-with admin),(2-customer admin)*/
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        // $this->db->where_not_in('u.user_role_id',array(1,2));
        // if(!empty($data['Is_external_provider'])&& $data['Is_external_provider']==1){
        //     $this->db->select('`u`.`id_user`,ur.id_user_role,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name');
        // }
        // else{
        //     $this->db->select('u.id_user,u.user_role_id,p.provider_name,p.id_provider provider,u.contribution_type,ur.user_role_name,CONCAT(u.first_name," ",u.last_name) as name,u.email,u.gender,u.user_status,u.last_logged_on,u.is_blocked');
        // }

        // $this->db->from('user u');
        // $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        // $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        // $this->db->join('business_unit bu','bu.id_business_unit=bur.business_unit_id','left');
        // $this->db->join('provider p','u.provider = p.id_provider','left');
        // if(isset($data['user_contracts'])){
        //     $this->db->join('contract_user cu','u.id_user = cu.user_id');
        //     $this->db->where_in('cu.contract_id',count($data['user_contracts'])>0?$data['user_contracts']:array('0'));
        // }
        // if(isset($data['customer_id']))
        //     $this->db->where('u.customer_id',$data['customer_id']);
        // if(isset($data['user_type']) && $data['user_type']=='external')//Here Contribution type 2=> External user 3=> Ext Usr & Provider Contributor
        //     $this->db->where_in('u.contribution_type',array(3,2));
        // else if(isset($data['user_type']) && $data['user_type']=='internal')//Here Contribution type 0=> Internal user 1=> Intrnl Usr & Validator Contributor
        //     $this->db->where_in('u.contribution_type',array(0,1));
        // if(isset($data['business_unit_id']))
        //     $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        // if(!empty($data['type']) && $data['type']=='project'){
        //     $this->db->where_in('p.id_provider',$data['id_provider']);
        // }
        // if(!empty($data['id_provider']) && $data['type']!='project'){
        //     $this->db->where('p.id_provider',$data['id_provider']);
        // }
        // if(!empty($data['buids'])){
        //     $this->db->where_in('bur.business_unit_id',$data['buids']);
        // }    
        // if(isset($data['search'])){
        //     $this->db->group_start();
        //     $this->db->like('bu.bu_name', $data['search'], 'both');
        //     $this->db->or_like('u.first_name', $data['search'], 'both');
        //     $this->db->or_like('u.last_name', $data['search'], 'both');
        //     $this->db->or_like('u.email', $data['search'], 'both');
        //     $this->db->or_like('u.gender', $data['search'], 'both');
        //     if(strtolower($data['search']) == 'exp' || strtolower($data['search']) == 'ex' || strtolower($data['search']) == 'expe' || strtolower($data['search']) == 'expert' || strtolower($data['search']) == 'exper')
        //         $this->db->or_like('u.contribution_type', '0', 'both');
        //     if(strtolower($data['search']) == 'val' || strtolower($data['search']) == 'vali' || strtolower($data['search']) == 'va' || strtolower($data['search']) == 'validation' || strtolower($data['search']) == 'valid' || strtolower($data['search']) == 'valida' || strtolower($data['search']) == 'validat')
        //         $this->db->or_like('u.contribution_type', '1', 'both');
        //     if(strtolower($data['search']) == 'pro' || strtolower($data['search']) == 'pr' || strtolower($data['search']) == 'prov' || strtolower($data['search']) == 'provider' || strtolower($data['search']) == 'provi' || strtolower($data['search']) == 'provid')
        //         $this->db->or_like('u.contribution_type', '3', 'both');
        //     $this->db->or_like('ur.user_role_name', $data['search'], 'both');
        //     $this->db->group_end();
        // }
        // if(isset($data['status']) && $data['status'] == 1){
        //     $this->db->where('u.user_status',$data['status']);
        // }
        // /*if(isset($data['search']))
        //     $this->db->where('(bu.bu_name like "%'.$data['search'].'%"
        //     or u.first_name like "%'.$data['search'].'%"
        //     or u.last_name like "%'.$data['search'].'%"
        //     or u.email like "%'.$data['search'].'%"
        //     or u.gender like "%'.$data['search'].'%")');*/
        // $this->db->group_by('u.id_user');

        // if(isset($data['current_user_not']))
        //     $this->db->where('u.id_user !=',$data['current_user_not']);
        // if(isset($data['business_unit_array']) && count($data['business_unit_array'])>0) {
        //     //$this->db->where_in('bur.business_unit_id', $data['business_unit_array']);
        //     $this->db->where('(CASE WHEN  u.user_role_id in (5,6) THEN 1 WHEN u.user_role_id not in (5,6) AND bur.business_unit_id in ('.implode(',',$data['business_unit_array']).') THEN 1 END)=1');
        // }

        // if(isset($data['business_units_array']) && count($data['business_units_array'])>0) {
        //     $this->db->where_in('bur.business_unit_id', $data['business_units_array']);
        // }
        // if(isset($data['user_role_not']))
        //     $this->db->where_not_in('u.user_role_id',$data['user_role_not']);

        // /* results count start */
        // $all_clients_db = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        // $all_clients_count = count($all_clients_db->result_array());
        // /* results count end */
        if(isset($data['contractOwner']) && $data['contractOwner'] == 1)
        {
            $this->db->where_not_in('u.user_role_id',array(1));
        }
        else
        {
            $this->db->where_not_in('u.user_role_id',array(1,2));
        }
        

        if(!empty($data['Is_external_provider'])&& $data['Is_external_provider']==1){
            $this->db->select('`u`.`id_user`,ur.id_user_role,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name,u.link,u.function,u.notes');
        }
        else{
            $this->db->select('u.id_user,u.user_role_id,p.provider_name,p.id_provider provider,u.contribution_type,ur.user_role_name,CONCAT(u.first_name," ",u.last_name) as name,u.email,u.gender,u.user_status,u.last_logged_on,group_concat(bu.id_business_unit) as business_unit_id,u.is_blocked,GROUP_CONCAT(IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name)) as bu_name,u.link,u.function,u.notes');
        }
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=bur.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('provider p','u.provider = p.id_provider','left');
        $this->db->where('u.is_deleted', 0);
        if(isset($data['user_contracts'])){
            $this->db->join('contract_user cu','u.id_user = cu.user_id');
            $this->db->where_in('cu.contract_id',count($data['user_contracts'])>0?$data['user_contracts']:array('0'));
        }
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['user_type']) && $data['user_type']=='external')//Here Contribution type 2=> External user 3=> Ext Usr & Provider Contributor
            $this->db->where_in('u.contribution_type',array(3,2));
        else if(isset($data['user_type']) && $data['user_type']=='internal')//Here Contribution type 0=> Internal user 1=> Intrnl Usr & Validator Contributor
            $this->db->where_in('u.contribution_type',array(0,1));
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);            
        if(!empty($data['type']) && $data['type']=='project'){
            $this->db->where_in('p.id_provider',$data['id_provider']);
        }
        if(!empty($data['id_provider']) && $data['type']!='project'){
            $this->db->where('p.id_provider',$data['id_provider']);
        }
        if(!empty($data['buids'])){
            $this->db->where_in('bur.business_unit_id',$data['buids']);
        }   
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('bu.bu_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('u.email', $data['search'], 'both');
            $this->db->or_like('u.gender', $data['search'], 'both');
            if(strtolower($data['search']) == 'exp' || strtolower($data['search']) == 'ex' || strtolower($data['search']) == 'expe' || strtolower($data['search']) == 'expert' || strtolower($data['search']) == 'exper')
                $this->db->or_like('u.contribution_type', '0', 'both');
            if(strtolower($data['search']) == 'val' || strtolower($data['search']) == 'vali' || strtolower($data['search']) == 'va' || strtolower($data['search']) == 'validation' || strtolower($data['search']) == 'valid' || strtolower($data['search']) == 'valida' || strtolower($data['search']) == 'validat')
                $this->db->or_like('u.contribution_type', '1', 'both');
            if(strtolower($data['search']) == 'pro' || strtolower($data['search']) == 'pr' || strtolower($data['search']) == 'prov' || strtolower($data['search']) == 'provider' || strtolower($data['search']) == 'provi' || strtolower($data['search']) == 'provid')
                $this->db->or_like('u.contribution_type', '3', 'both');
            $this->db->or_like('ur.user_role_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('u.user_status',$data['status']);
        }
        /*if(isset($data['search']))
            $this->db->where('(bu.bu_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%"
            or u.email like "%'.$data['search'].'%"
            or u.gender like "%'.$data['search'].'%")');*/
        $this->db->group_by('u.id_user');

        if(isset($data['business_unit_array']) && count($data['business_unit_array'])>0) {
            $this->db->where_in('bur.business_unit_id', $data['business_unit_array']);
            // $this->db->where('(CASE WHEN  u.user_role_id in (5,6) THEN 1 WHEN u.user_role_id not in (5,6) AND bur.business_unit_id in ('.implode(',',$data['business_unit_array']).') THEN 1 END)=1');
        }
        if(isset($data['business_units_array']) && count($data['business_units_array'])>0 && $data['user_type']=='internal') {
            $this->db->where_in('bur.business_unit_id', $data['business_units_array']);
        }
        // if(isset($data['user_role_not']))
        //             $this->db->where_not_in('u.user_role_id',$data['user_role_not']);


        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();//echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate'] == 'contribution')
                $this->db->order_by('u.contribution_type',$data['sort']['reverse']);
            else
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);

        }
        else
            $this->db->order_by('u.id_user','DESC');
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return array('total_records' => $count_result,'data' => $query->result_array());
    }

    public function addCalender($data)
    {
        $this->db->insert_batch('calender', $data);
        return $this->db->insert_id();
    }

    public function updateCalenderByCategory($data)
    {
        $this->db->where('relationship_category_id',$data['relationship_category_id']);
        $this->db->where('date',$data['date']);
        $this->db->update('calender',$data);
    }

    public function getCalender($data){
        $this->db->select('c.*,rc.relationship_category_name');
        $this->db->from('calender c');
        $this->db->join('relationship_category_language rc','c.relationship_category_id = rc.relationship_category_id','');
        if(isset($data['customer_id']))
            $this->db->where('c.customer_id',$data['customer_id']);
        if(isset($data['month']))
            $this->db->where('month(c.date)',$data['month']);
        if(isset($data['date']))
            $this->db->where('date',$data['date']);
        if(isset($data['status']))
                    $this->db->where('status',$data['status']);

        return $this->db->get()->result_array();
    }

    public function getYearCalender($data){
        //previous: $this->db->select('distinct(rc.relationship_category_name),DATE_FORMAT(c.date,"%M") as month,DATE_FORMAT(c.date,"%m") as month_id');
        $this->db->select('rc.relationship_category_id,rc.relationship_category_name,DATE_FORMAT(c.date,"%M") as month,IF(DATE_FORMAT(c.date, "%m")<10,REPLACE(DATE_FORMAT(c.date, "%m"),0,\'\'),DATE_FORMAT(c.date, "%m")) as month_id,count(c.id_calender) as relationship_count, r.relationship_category_quadrant');
        $this->db->from('calender c');
        $this->db->join('relationship_category r','r.id_relationship_category=c.relationship_category_id','');
        $this->db->join('relationship_category_language rc','c.relationship_category_id = rc.relationship_category_id','');
        if(isset($data['customer_id']))
            $this->db->where('c.customer_id',$data['customer_id']);
        if(isset($data['year']))
            $this->db->where('year(c.date)',$data['year']);
        if(isset($data['status']))
            $this->db->where('status',$data['status']);
        $this->db->group_by('month_id,c.relationship_category_id');
        $result = $this->db->get();
        //echo $this->db->last_query();exit;
        return $result->result_array();
    }

    public function checkAlreadyExist($data){
        /*
         * SELECT c.*,rcr.days,DATE_SUB(c.date,INTERVAL rcr.days DAY),rcl.relationship_category_name FROM calender c join relationship_category_remainder rcr on rcr.relationship_category_id=c.relationship_category_id join relationship_category_language rcl on rcl.relationship_category_id=c.relationship_category_id and rcl.language_id=1 where c.relationship_category_id=110 and '2017-04-04' BETWEEN DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date and c.status=1;
         */
        $this->db->select('c.*,rcr.days,DATE_SUB(c.date,INTERVAL rcr.days DAY),rcl.relationship_category_name');
        $this->db->from('calender c');
        $this->db->join('relationship_category_remainder rcr','rcr.relationship_category_id=c.relationship_category_id','');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=c.relationship_category_id and rcl.language_id=1','');
        $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        $this->db->group_start();
        $this->db->where('"'.$data['date'].'" between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date and c.status=1');
        $this->db->or_where('"'.$data['date'].'" between c.date and DATE_ADD(c.date,INTERVAL rcr.days DAY) and c.status=1');
        $this->db->group_end();
        $query=$this->db->get();
        /*echo $this->db->last_query(); exit;*/
        return $query->result_array();
    }

    public function updateCalender($data)
    {
        if(isset($data['id_calender']))
            $this->db->where('id_calender', $data['id_calender']);
        if(isset($data['date']))
            $this->db->where('date', $data['date']);
        $this->db->update('calender', $data);
        return 1;
    }

    public function addRelationshipRemainder($data)
    {
        $this->db->insert_batch('relationship_category_remainder', $data);
        return 1;
    }

    public function updateRelationshipRemainder($data)
    {
        $this->db->update_batch('relationship_category_remainder', $data, 'id_relationship_category_remainder');
        return 1;

    }

    public function getRelationshipCategoryRemainder($data)
    {
        $this->db->select('r.id_relationship_category,rl.relationship_category_name,rr.*');
        $this->db->from('relationship_category r');
        $this->db->join('relationship_category_language rl','r.id_relationship_category=rl.relationship_category_id and language_id=1','');
        $this->db->join('relationship_category_remainder rr','rr.relationship_category_id = r.id_relationship_category and rr.customer_id='.$this->db->escape($data['customer_id']),'');
        if(isset($data['customer_id']))
            $this->db->where('r.customer_id',$data['customer_id']);
        return $this->db->get()->result_array();
    }

    public function getUserCount($data)
    {
        $this->db->select('count(distinct u.id_user) as total_records');
        $this->db->from('user u');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=bur.business_unit_id','left');
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['user_role_id_not']) & count($data['user_role_id_not'])>0)
            $this->db->where_not_in('u.user_role_id', $data['user_role_id_not']);
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        if(isset($data['business_unit_array']) && count($data['business_unit_array'])>0)
            $this->db->where_in('bur.business_unit_id',$data['business_unit_array']);
        $this->db->where_not_in('u.user_role_id', array(5,6));
        $result1 = $this->db->get()->result_array();//echo '<pre>'.this->db->last_query();

        $this->db->select('count(distinct u.id_user) as total_records');
        $this->db->from('user u');
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['user_role_id_not']) & count($data['user_role_id_not'])>0)
            $this->db->where_not_in('u.user_role_id', $data['user_role_id_not']);
        $this->db->where_in('u.user_role_id', array(5,6));
        $result2 = $this->db->get()->result_array();//echo '<pre>'.this->db->last_query();exit;

        return $result1[0]['total_records']+$result2[0]['total_records'];
    }

    public function getDelegateContributorsCount($data){
        $this->db->select('count(distinct u.id_user) as total_records');
        $this->db->from('user u');
        $this->db->join('contract_user cu','u.id_user = cu.user_id');
        $this->db->where_in('cu.contract_id',count($data['user_contracts'])>0?$data['user_contracts']:array('0'));
        $this->db->where('u.id_user !=',$data['user_id']);
        $this->db->where('cu.status',1);
        $this->db->where_in('u.contribution_type',array(1,3,0));
        $result = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $result->result_array();
        if(count($result)>0)
            return $result[0]['total_records'];
        else
            return 0;
    }

    public function EmailTemplateList($data)
    {
        if(isset($data['customer_id']))
        {
            $this->db->select("l.language_iso_code , l.id_language ,l.language_name");
            $this->db->from('customer_languages cl');
            $this->db->join('language l','l.id_language=cl.language_id','left');
            $this->db->where('cl.customer_id',$data['customer_id']);
            $this->db->where('cl.is_primary',1); 
            $this->db->where('cl.status',1); 
            $query = $this->db->get();
            $lang =  $query->result_array();
            $data['language_id'] = $lang[0]['id_language'];
        }
        
        $this->db->select('*');
        $this->db->from('email_template e');
        $this->db->join('email_template_language el','e.id_email_template=el.email_template_id','left');
        if(isset($data['language_id']))
            $this->db->where('el.language_id',$data['language_id']);
        if(isset($data['customer_id']))
            $this->db->where('e.customer_id',$data['customer_id']);
        if(isset($data['module_key']))
            $this->db->where('e.module_key',$data['module_key']);
        if(isset($data['parent_email_template_id']))
            $this->db->where('e.parent_email_template_id',$data['parent_email_template_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('r.relationship_category_quadrant', $data['search'], 'both');
            $this->db->group_end();
        }
        if(!empty($data['module_name'])){
            $this->db->where('e.module_name',$data['module_name']);
        }
        /*if(isset($data['search']))
            $this->db->where('(l.relationship_category_name like "%'.$data['search'].'%"
        or r.relationship_category_quadrant like "%'.$data['search'].'%")');*/
        if(isset($data['status']))
            $this->db->where_in('e.status',explode(',',$data['status']));
        else
            $this->db->where('e.status',1);
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('e.id_email_template','ASC');
        $query = $this->db->get();
        // if(isset($data['customer_id']) && $data['customer_id']>0 && isset($data['module_key']) && $data['module_key']!='' && $all_clients_count<=0){
        //     $data['customer_id']=0;
        //     return $this->EmailTemplateList($data);
        // }
        $final_result=$query->result_array();
        /*foreach($final_result as $k=>$v){
            $final_result[$k]['template_content']=EMAIL_HEADER_CONTENT.$v['template_content'].EMAIL_FOOTER_CONTENT;
        }*/
        return array('total_records' => $all_clients_count,'data' => $final_result);
    }
    public function addEmailTemplate($data)
    {
        $this->db->insert('email_template', $data);
        return $this->db->insert_id();
    }

    public function addEmailTemplateLanguage($data)
    {
        $this->db->insert('email_template_language', $data);
        return $this->db->insert_id();
    }

    public function addMailer($data)
    {
        $this->db->insert('mailer', $data);
        return $this->db->insert_id();
    }

    public function getMailer($data=array())
    {
        $this->db->select('*');
        $this->db->from('mailer m');
        $this->db->where('m.is_cron',1);
        if(isset($data['limit']))
            $this->db->limit($data['limit']);
        $this->db->where('m.cron_status',0);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function updateMailer($data)
    {
        if(isset($data['mailer_id'])) {
            $this->db->where('mailer_id', $data['mailer_id']);
            $this->db->update('mailer', $data);
            return 1;
        }
    }

    public function getDailyUpdatesData($data){
        $this->db->select('id_daily_update_customer,customer_id');
        $this->db->from('daily_update_customer');
        $this->db->where('date',$data['date']);
        $this->db->where('status',$data['status']);

        $result = $this->db->get();
        return $result->result_array();
        //print_r($result->result_array());exit;
        //echo $this->db->last_query();
    }

    public function getDailyUpdates($data){
        $this->db->select('*');
        $this->db->from('daily_update_customer');
        $this->db->where('customer_id',$data['customer_id']);
        $this->db->where('DATE(created_on)',$data['date']);
        //$this->db->where('date <=',$data['to_date']);


        $result = $this->db->get();
        return $result->result_array();
        //print_r($result->result_array());
        //echo $this->db->last_query();exit;
    }

    public function addDailyUpdatesData($data){
        $this->db->insert('daily_update_customer',$data);
        return 1;
    }
    public function updateDailyMail($data,$condition){
        $this->db->where('id_daily_update_customer',$condition['id_daily_update_customer']);
        $this->db->update('daily_update_customer',$data);
        //echo $this->db->last_query();
        return 1;
    }
    public function getCustomerUserListHistory($data)
    {
        /*user role not in (1-with admin),(2-customer admin)*/
        //$this->db->where_not_in('u.user_role_id',array(1,2));
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('u.id_user,ur.user_role_name,CONCAT(u.first_name," ",u.last_name) as name,u.email,u.gender,u.user_status,u.last_logged_on');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=bur.business_unit_id','left');
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('bu.bu_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('u.email', $data['search'], 'both');
            $this->db->or_like('u.gender', $data['search'], 'both');
            $this->db->or_like('ur.user_role_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(bu.bu_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%"
            or u.email like "%'.$data['search'].'%"
            or u.gender like "%'.$data['search'].'%"
            or ur.user_role_name like "%'.$data['search'].'%")');*/
        $this->db->group_by('u.id_user');

        if(isset($data['current_user_not']))
            $this->db->where('u.id_user !=',$data['current_user_not']);
        if(isset($data['business_unit_array']) && count($data['business_unit_array'])>0) {
            //$this->db->where_in('bur.business_unit_id', $data['business_unit_array']);
            $this->db->where('(CASE WHEN  u.user_role_id in (5,6) THEN 1 WHEN u.user_role_id not in (5,6) AND bur.business_unit_id in ('.implode(',',$data['business_unit_array']).') THEN 1 END)=1');
        }
        if(isset($data['user_role_not']))
            $this->db->where_not_in('u.user_role_id',$data['user_role_not']);

        /* results count start */
        $all_clients_db = $this->db->get();
        $all_clients_count = count($all_clients_db->result_array());
        /* results count end */

        //$this->db->where_not_in('u.user_role_id',array(1,2));

        $this->db->select('u.id_user,ur.user_role_name,CONCAT(u.first_name," ",u.last_name) as name,u.email,u.gender,u.user_status,u.last_logged_on,group_concat(bu.id_business_unit) as business_unit_id,group_concat(bu.bu_name) as bu_name');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=bur.business_unit_id','left');
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('bu.bu_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('u.email', $data['search'], 'both');
            $this->db->or_like('u.gender', $data['search'], 'both');
            $this->db->or_like('ur.user_role_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(bu.bu_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%" or
             u.email like "%'.$data['search'].'%" or
             u.gender like "%'.$data['search'].'%" or
             ur.user_role_name like "%'.$data['search'].'%")');*/
        $this->db->group_by('u.id_user');

        if(isset($data['business_unit_array']) && count($data['business_unit_array'])>0) {
            //$this->db->where_in('bur.business_unit_id', $data['business_unit_array']);
            $this->db->where('(CASE WHEN  u.user_role_id in (5,6) THEN 1 WHEN u.user_role_id not in (5,6) AND bur.business_unit_id in ('.implode(',',$data['business_unit_array']).') THEN 1 END)=1');
        }
        if(isset($data['user_role_not']))
            $this->db->where_not_in('u.user_role_id',$data['user_role_not']);

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('u.id_user','ASC');
        $query = $this->db->get();
        $final_result=$query->result_array();
        foreach($final_result as $k=>$v){
            $last_login=$this->getUserLastLogin(array('id_user'=>$v['id_user']));
            $final_result[$k]['last_logged_on']=isset($last_login[0]['created_at'])?$last_login[0]['created_at']:NULL;
        }
        //echo $this->db->last_query(); exit;
        return array('total_records' => $all_clients_count,'data' => $final_result);
    }
    public function getUserLoginHistory($data){
        /*select u.id_user,CONCAT(u.first_name,' ',u.last_name) user_name,(oat.access_token),SEC_TO_TIME((TIME_TO_SEC(TIMEDIFF(oat.updated_at,oat.created_at)))) as time_spent,os.client_browser,os.client_remote_address,oat.created_at as login_date,oat.updated_at as logout_date,(select count(id_access_log) from access_log where access_token=oat.access_token) as actions
FROM oauth_access_tokens oat
LEFT JOIN oauth_sessions os on oat.session_id = os.id
LEFT JOIN  oauth_clients oc on os.client_id = oc.id
LEFT JOIN  user u on oc.user_id = u.id_user
where  oat.created_at BETWEEN '2017-05-12' AND '2017-05-16' and u.id_user=69 ORDER BY oat.id asc;*/

        if($data['type']=='detail') {
            $data['to_date']=date('Y-m-d',strtotime($data['to_date'] .' +1 day'));
            $this->db->select('u.id_user,CONCAT(u.first_name,\' \',u.last_name) user_name,(oat.access_token),SEC_TO_TIME((TIME_TO_SEC(TIMEDIFF(oat.updated_at,oat.created_at)))) as time_spent,os.client_browser,os.client_remote_address,oat.created_at as login_date,oat.updated_at as logout_date,(select count(id_access_log) from access_log where access_token=oat.access_token) as actions_count');
            $this->db->from('oauth_access_tokens oat');
            $this->db->join('oauth_sessions os', 'oat.session_id = os.id', 'LEFT');
            $this->db->join('oauth_clients oc', 'os.client_id = oc.id', 'LEFT');
            $this->db->join('user u', 'oc.user_id = u.id_user', 'LEFT');
            $this->db->where('oat.created_at BETWEEN \'' . $data['from_date'] . '\' AND \'' . $data['to_date'] . '\'');
            $this->db->where('u.id_user', $data['id_user']);
            //$this->db->order_by('oat.id', 'asc');
            $all_clients_db = clone $this->db;
            $all_clients_count = $all_clients_db->count_all_results();
            /* results count end */

            if (isset($data['pagination']['number']) && $data['pagination']['number'] != '')
                $this->db->limit($data['pagination']['number'], $data['pagination']['start']);
            if (isset($data['sort']['predicate']) && $data['sort']['predicate'] != '' && isset($data['sort']['reverse']))
                $this->db->order_by($data['sort']['predicate'], $data['sort']['reverse']);
            else
                $this->db->order_by('oat.id', 'DESC');
        }
        else{
            //SUM((select count(id_access_log) from access_log where access_token=oat.access_token)) as actions_count,
            $this->db->select('u.id_user,CONCAT(u.first_name,\' \',u.last_name) user_name,count(oat.access_token) logins_count,SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(oat.updated_at,oat.created_at)))) as time_spent,ur.user_role_name,u.email');
            $this->db->from('oauth_access_tokens oat');
            $this->db->join('oauth_sessions os', 'oat.session_id = os.id', 'LEFT');
            $this->db->join('oauth_clients oc', 'os.client_id = oc.id', 'LEFT');
            $this->db->join('user u', 'oc.user_id = u.id_user', 'LEFT');
            $this->db->join('user_role ur', 'ur.id_user_role=u.user_role_id', 'LEFT');
            $this->db->where('u.id_user', $data['id_user']);
            //$this->db->order_by('oat.id', 'asc');
            $all_clients_db = clone $this->db;
            $all_clients_count = 1;
            /* results count end */

            if (isset($data['pagination']['number']) && $data['pagination']['number'] != '')
                $this->db->limit($data['pagination']['number'], $data['pagination']['start']);
            if (isset($data['sort']['predicate']) && $data['sort']['predicate'] != '' && isset($data['sort']['reverse']))
                $this->db->order_by($data['sort']['predicate'], $data['sort']['reverse']);
            else
                $this->db->order_by('oat.id', 'ASC');
        }
        $query = $this->db->get();
        $final_result=$query->result_array();
        foreach($final_result as $k=>$v){
            $last_login=$this->getUserLastLogin($data);
            $final_result[$k]['last_logged_on']=isset($last_login[0]['created_at'])?$last_login[0]['created_at']:NULL;
            //$final_result[$k]['last_logged_on']=currentDate();
        }
        /*foreach($final_result as $k=>$v){
            $final_result[$k]['template_content']=EMAIL_HEADER_CONTENT.$v['template_content'].EMAIL_FOOTER_CONTENT;
        }*/
        return array('total_records' => $all_clients_count,'data' => $final_result);

    }
    public function getUserLastLogin($data){
        /*select oat.created_at
FROM oauth_access_tokens oat
LEFT JOIN oauth_sessions os on oat.session_id = os.id
LEFT JOIN  oauth_clients oc on os.client_id = oc.id
LEFT JOIN  user u on oc.user_id = u.id_user
where  u.id_user=69 ORDER BY oat.id desc limit 1;*/

        $this->db->select('oat.created_at');
        $this->db->from('oauth_access_tokens oat');
        $this->db->join('oauth_sessions os', 'oat.session_id = os.id', 'LEFT');
        $this->db->join('oauth_clients oc', 'os.client_id = oc.id', 'LEFT');
        $this->db->join('user u', 'oc.user_id = u.id_user', 'LEFT');
        $this->db->where('u.id_user', $data['id_user']);
        $this->db->order_by('oat.id', 'desc');
        $this->db->limit('1');
        $query = $this->db->get();
        $final_result=$query->result_array();
        return $final_result;


    }

//     public function getproviderlist($data=null){
//         // print_r($data);exit;
//         // $data['risk_profile']='A';
//         // // $data['approval_status']='A';
//         /*if(isset($data['search']))
//             $data['search']=$this->db->escape($data['search']);*/
//         // $this->db->select('select * from(');
// //         $this->db->select('prc.can_review,p.*,prcl.relationship_category_name as category_name,c.country_name,(SELECT GROUP_CONCAT(cnt.id_contract) FROM provider pr LEFT JOIN contract cnt ON pr.id_provider=cnt.provider_name WHERE cnt.is_deleted=0
// //         AND pr.id_provider=p.id_provider  
// //         GROUP BY pr.id_provider) contract_ids,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
// //         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
// //         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `tl`.`tag_text` = "Approval Status" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as approval_status,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
// //         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
// //         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `tl`.`tag_text` = "Risk Profile" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as risk_profile,(SELECT cry.currency_name FROM contract ctr  LEFT JOIN provider pr ON ctr.provider_name=pr.id_provider  LEFT JOIN currency cry on	 ctr.currency_id=cry.id_currency WHERE pr.id_provider=p.id_provider
// //         GROUP BY pr.id_provider) as currency_name_old,(SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
// //         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
// //         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `tl`.`tag_text` = "Risk Profile" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as risk_profile_comments,
// // (SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
// //         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
// //         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `tl`.`tag_text` = "Approval Status" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as approval_status_comments,(SELECT COUNT(*) as action_items_count FROM contract_review_action_item WHERE item_status=1 AND status="open" AND `contract_id` IS NULL AND provider_id=p.id_provider)as action_items_count,(SELECT currency_name FROM currency WHERE customer_id=p.customer_id and is_maincurrency=1 and status=1) currency_name
// // ');
//         $this->db->select('prc.can_review,p.*,prcl.relationship_category_name as category_name,c.country_name,(SELECT GROUP_CONCAT(cnt.id_contract) FROM provider pr LEFT JOIN contract cnt ON pr.id_provider=cnt.provider_name WHERE cnt.is_deleted=0
//         AND pr.id_provider=p.id_provider  
//         GROUP BY pr.id_provider) contract_ids,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
//         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
//         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_2" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as approval_status,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
//         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
//         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND 
//         `t`.`label` = "label_1" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as risk_profile,(SELECT `pt`.`tag_option_value` `finacial_health_value` FROM `tag` `t` LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_3" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as finacial_health,(SELECT cry.currency_name FROM contract ctr  LEFT JOIN provider pr ON ctr.provider_name=pr.id_provider  LEFT JOIN currency cry on	 ctr.currency_id=cry.id_currency WHERE pr.id_provider=p.id_provider
//         GROUP BY pr.id_provider) as currency_name_old,(SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
//         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
//         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_1" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as risk_profile_comments,
// (SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
//         LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
//         LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_2" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as approval_status_comments,(SELECT `pt`.`comments` `finacial_health_value` FROM `tag` `t` LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id` LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_3" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as finacial_health_comments,(SELECT COUNT(*) as action_items_count FROM contract_review_action_item WHERE item_status=1 AND status="open" AND `contract_id` IS NULL AND provider_id=p.id_provider)as action_items_count,(SELECT currency_name FROM currency WHERE customer_id=p.customer_id and is_maincurrency=1 and status=1) currency_name
// ');
//         $this->db->from('provider p');
//         if(isset($data['only_user_connected_providers'])){
//             $this->db->join('contract c','c.provider_name = id_provider');
//             $this->db->group_by('p.id_provider');
//         }
//         $this->db->join('provider_relationship_category prc','prc.id_provider_relationship_category=p.category_id','left');
//         $this->db->join('provider_relationship_category_language prcl','prc.id_provider_relationship_category = prcl.provider_relationship_category_id','left');
//         $this->db->join('country c','p.country=c.id_country','left');
//         //event feed realtions

//         $this->db->join("event_feeds ef","p.id_provider = ef.reference_id and ef.reference_type = 'provider' and ef.status=1","left");

//         if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) ){
//             $this->db->join("document d","p.id_provider=d.reference_id AND d.reference_type = 'provider' AND d.module_type = 'provider' AND d.document_status = 1","left");
//         }
//         if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
//             $this->db->select("GROUP_CONCAT(d.document_name) as document_names");
//         }
//         if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
//             $this->db->select("GROUP_CONCAT(d.document_source) as document_urls");
//         }

        
//         if(!empty($data['adv_union_filters']) && is_numeric(array_search('event_feed_document_names', array_column($data['adv_union_filters'], 'database_field')))){
//             $this->db->select("(select GROUP_CONCAT(document.document_name) from document WHERE document.reference_type ='event_feed' and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1  )) as event_feed_document_names");
//         }
//         if(!empty($data['adv_union_filters']) && is_numeric(array_search('event_feed_document_urls', array_column($data['adv_union_filters'], 'database_field')))){
//             $this->db->select("(select GROUP_CONCAT(document.document_source) from document WHERE document.reference_type ='event_feed' and document.document_type = 1 and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1)) as event_feed_document_urls");
//         }

//         if(isset($data['type']) && $data['type']=='project'){
//             $this->db->join('project_providers pps','pps.provider_id=p.id_provider','left');
//             $this->db->where('pps.project_id',$data['project_id']);
//             $this->db->where('pps.is_linked',1);
//         }
//         if(isset($data['customer_id'])){
//             $this->db->where('p.customer_id',$data['customer_id']);
//         }
//         if(isset($data['id_provider']))
//             $this->db->where('p.id_provider',$data['id_provider']);
//         if(isset($data['provider_array']))
//             $this->db->where_in('p.id_provider',$data['provider_array']);
//         // if(isset($data['status']))
//         //     $this->db->where('p.status',$data['status']);
//         // else
//         //     $this->db->where('status !=',2);
//         if(isset($data['search'])){
//             $this->db->group_start();
//             $this->db->like('p.provider_name', $data['search'], 'both');
//             $this->db->or_like('p.unique_id', $data['search'], 'both');
//             $this->db->or_like('c.country_name', $data['search'], 'both');
//             $this->db->or_like('prcl.relationship_category_name', $data['search'], 'both');
//             $this->db->or_like('p.vat', $data['search'], 'both');
//             // $this->db->or_like('address', $data['search'], 'both');
//             // $this->db->or_like('contact_no', $data['search'], 'both');
//             $this->db->group_end();
//         }
//           //////////advanced filters start ///////////////
//           foreach($data['adv_filters'] as $filter){
//             //  print_r($filter);
//             if( $filter['domain'] == "Relation Tags" )
//             {
//                 $tagId = $filter['master_domain_field_id'];
//                 $condition = $filter['condition'];
//                 $value = $filter['value'];
//                 if($filter['field_type']=='drop_down')
//                 {
//                     $tagOptionValue = "'" . str_replace(",", "','", $value) . "'";
//                     $this->db->where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider AND provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value in ($tagOptionValue))");
//                 }
//                 elseif($filter['field_type']=='date')
//                 {
//                     $this->db->where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND DATE(provider_tags.tag_option_value) $condition  '$value')");
//                 }
//                 elseif(($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'))
//                 {
//                    if($filter['condition'] == 'like')
//                    {
//                         $this->db->where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value LIKE '%$value%' ESCAPE '!')");
//                    }
//                    else
//                    {
//                         $this->db->where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value $condition  '$value')");
//                    }   
//                 }
//             }
//             else
//             {
//                 if($filter['field_type']=='drop_down'){
//                     $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
//                 }
//                 elseif($filter['field_type']=='date'){
//                     $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
//                 }
//                 elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
//                     $filter['value'] = str_replace(',','',$filter['value']);
//                     if($filter['condition']=='like'){
//                         $this->db->like($filter['database_field'],$filter['value'],'both');
//                     }
//                     elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
//                         $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
//                     }
//                 }
//             }
           
            
//         }
//         //exit;
//         //////////advanced filters end ///////////////
//         if(!empty($data['status'])){
//             $this->db->where('p.status',$data['status']);
//         }else
//             $this->db->where('p.status !=',2);
//         if(isset($data['country_id'])){
//             $this->db->where('p.country',$data['country_id']);
//         }
//         if(isset($data['relationship_category_id'])){
//             $this->db->where('p.category_id',$data['relationship_category_id']);
//         }
//         if(isset($data['can_access']) && empty($data['type'])){
//             $this->db->where('p.status',$data['can_access']);
//         }
//         // if(isset($data['chart_type']) && $data['chart_type']=='allproviders'){
//         //     // $this->db->where('prc.can_review',);
//         // }

//         /*if(isset($data['search']))
//             $this->db->where('(provider_name like "%'.$data['search'].'%"
//             or email like "%'.$data['search'].'%"
//             or address like"%'.$data['search'].'%"
//             or contact_no like "%'.$data['search'].'%")');*/
//             // $count = clone $this->db;
//             // $count =$count->get();
//             // print_r($count->result_id->num_rows);exit;
//             //echo 'first <pre>'.$count->result_id->num_rows;
//             //echo '$count->get()->num_rows '.$count->get()->result_id->num_rows;exit;
//             // if($count->result_id->num_rows > 1)
//             //     $rec_count = $count->result_id->num_rows;
//             // else
//             //     $rec_count = 0;

//             // if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
//             //     $order_by=$data['sort']['predicate'].' '.$data['sort']['reverse'].')as temp';
//             //     if(!empty($data['risk_profile']) && empty($data['approval_status'])){
//             //         // $this->db->where('risk_profile',$data['risk_profile']);
//             //         $risk_profile=$data['risk_profile'];
//             //         $order_by=$order_by.' where risk_profile='."'$risk_profile'";
//             //     }
//             //     if(!empty($data['approval_status']) && empty($data['risk_profile'])){
//             //         $approval_status=$data['approval_status'];
//             //         $order_by=$order_by.' where approval_status='."'$approval_status'";
//             //     }
//             //     if(!empty($data['approval_status']) && !empty($data['risk_profile'])){
//             //         $risk_profile=$data['risk_profile'];
//             //         $approval_status=$data['approval_status'];
//             //         $order_by=$order_by.' where approval_status='."'$approval_status' and risk_profile='$risk_profile'";
//             //     }
//             //     $this->db->order_by($order_by);
//             // }
//             // else{
//             //     $order_by='p.provider_name asc ) as temp';
//             //     if(!empty($data['risk_profile']) && empty($data['approval_status'])){
//             //         // $this->db->where('risk_profile',$data['risk_profile']);
//             //         $risk_profile=$data['risk_profile'];
//             //         $order_by=$order_by.' where risk_profile='."'$risk_profile'";
//             //     }
//             //     if(!empty($data['approval_status']) && empty($data['risk_profile'])){
//             //         $approval_status=$data['approval_status'];
//             //         $order_by=$order_by.' where approval_status='."'$approval_status'";
//             //     }
//             //     if(!empty($data['approval_status']) && !empty($data['risk_profile'])){
//             //         $risk_profile=$data['risk_profile'];
//             //         $approval_status=$data['approval_status'];
//             //         $order_by=$order_by.' where approval_status='."'$approval_status' and risk_profile='$risk_profile'";
//             //     }
//             //     $this->db->order_by($order_by);
//             // }
//             $this->db->group_by('p.id_provider');
//            $subquery= $this->db->get_compiled_select();
//             // $result = $this->db->get();
//             $this->db->_reset_select();
//             $this->db->select('*');
//             $this->db->from("($subquery)as unionTable");
//             if(!empty($data['approval_status'])){
//                 $this->db->where('approval_status',$data['approval_status']);
//             }
//             if(!empty($data['risk_profile'])){
//                 $this->db->where('risk_profile',$data['risk_profile']);
//             }
//             foreach($data['adv_union_filters'] as $Unionfilter){
//                     if($Unionfilter['field_type']=='drop_down'){
//                         $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
//                     }
//                     elseif($Unionfilter['field_type']=='date'){
//                         $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
//                     }
//                     // elseif($Unionfilter['field_type']=='free_text'){
//                     //     if($Unionfilter['condition']=='like'){
//                     //         $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
//                     //     }
//                     //     if($Unionfilter['condition']=='free_text' || $filter['condition']=='='){
//                     //         if($Unionfilter['database_field']!='document_names' && $Unionfilter['database_field']!='document_urls'){
//                     //             $this->db->where($Unionfilter['database_field'],$Unionfilter['value']);
//                     //         }
//                     //     }
//                     // }
//                     elseif($Unionfilter['field_type']=='free_text'||$Unionfilter['field_type']=='numeric_text'){
//                         if($Unionfilter['condition']=='like'){
//                             $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
//                         }
//                         elseif($Unionfilter['condition']=='<' || $Unionfilter['condition']=='>'|| $Unionfilter['condition']=='=' ){
//                             $this->db->where($Unionfilter['database_field']." ".$Unionfilter['condition'],$Unionfilter['value']);
//                         }
//                     }
//             }
//             $count_result_db = clone $this->db;
//             $count_result = $count_result_db->get();//echo $count_result_db->last_query();exit;
//             $count_result = $count_result->num_rows();
//             if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
//                 $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
//             if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
//                 $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
//             }
//             else{
//                 $this->db->order_by('provider_name','asc');
//             }
//             $result = $this->db->get();//echo $this->db->last_query();exit;
//             return array('total_records'=>$count_result,'data'=>$result->result_array());
//             // if($rec_count > 0 || count($result->result_array())>0)
//             // else
//             //     return array('total_count'=>(int)$rec_count,'data'=>[]);

//     }

    public function getproviderlist($data = null)
    {
        $this->db->select('prc.can_review,p.*,prcl.relationship_category_name as category_name,c.country_name,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
        LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
        LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_2" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as approval_status,(SELECT `pt`.`tag_option_value` `risk_profile_value` FROM `tag` `t`
        LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
        LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND 
        `t`.`label` = "label_1" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as risk_profile,(SELECT `pt`.`tag_option_value` `finacial_health_value` FROM `tag` `t` LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_3" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id` ) as finacial_health,
        (SELECT currency_name FROM currency WHERE customer_id=p.customer_id and is_maincurrency=1 and status=1) currency_name');
        $userId = $this->session_user_info->id_user;
        if(!(isset($data['id_provider']) && $data['id_provider'] > 0))
        {
            if(in_array($this->session_user_info->user_role_id,array(3,4,6,7))){
                $this->db->select('(SELECT COUNT(*) FROM `contract_review_action_item` WHERE `provider_id` = p.id_provider AND `item_status` = 1 AND `status` = "open" AND `responsible_user_id` = '.$userId.' AND `reference_type` = "provider") as action_items_count');
            }
            else
            {
                $this->db->select('(SELECT COUNT(*) FROM `contract_review_action_item` WHERE `provider_id` = p.id_provider AND `item_status` = 1 AND `status` = "open"  AND `reference_type` = "provider") as action_items_count');
            }
        }
        if (isset($data['id_provider']) && $data['id_provider'] > 0) {
            $this->db->select('(SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
            LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
            LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_1" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as risk_profile_comments,
            (SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
            LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
            LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_2" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as approval_status_comments,
            (SELECT `pt`.`comments` `finacial_health_value` FROM `tag` `t` LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id` LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_3" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as finacial_health_comments,
            (SELECT GROUP_CONCAT(cnt.id_contract) FROM provider pr LEFT JOIN contract cnt ON pr.id_provider=cnt.provider_name WHERE cnt.is_deleted=0
            AND pr.id_provider=p.id_provider  
            GROUP BY pr.id_provider) contract_ids,
            (SELECT cry.currency_name FROM contract ctr  LEFT JOIN provider pr ON ctr.provider_name=pr.id_provider  LEFT JOIN currency cry on	 ctr.currency_id=cry.id_currency WHERE pr.id_provider=p.id_provider
            GROUP BY pr.id_provider) as currency_name_old,(SELECT COUNT(*) as action_items_count FROM contract_review_action_item WHERE item_status=1 AND status="open" AND `contract_id` IS NULL AND provider_id=p.id_provider)as action_items_count');
        }
        $this->db->from('provider p');
        if (isset($data['only_user_connected_providers'])) {
            $this->db->join('contract c', 'c.provider_name = id_provider');
            $this->db->group_by('p.id_provider');
        }
        $this->db->join(
            'provider_relationship_category prc',
            'prc.id_provider_relationship_category=p.category_id',
            'left'
        );
        $this->db->join(
            'provider_relationship_category_language prcl',
            'prc.id_provider_relationship_category = prcl.provider_relationship_category_id',
            'left'
        );
        $this->db->join('country c', 'p.country=c.id_country', 'left');
        //event feed realtions

        $this->db->join(
            'event_feeds ef',
            "p.id_provider = ef.reference_id and ef.reference_type = 'provider' and ef.status=1",
            'left'
        );

        if (
            (!empty($data['adv_union_filters']) &&
                is_numeric(
                    array_search(
                        'document_urls',
                        array_column(
                            $data['adv_union_filters'],
                            'database_field'
                        )
                    )
                )) ||
            is_numeric(
                array_search(
                    'document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->join(
                'document d',
                "p.id_provider=d.reference_id AND d.reference_type = 'provider' AND d.module_type = 'provider' AND d.document_status = 1",
                'left'
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                'GROUP_CONCAT(d.document_name) as document_names'
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'document_urls',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                'GROUP_CONCAT(d.document_source) as document_urls'
            );
        }

        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'event_feed_document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                "(select GROUP_CONCAT(document.document_name) from document WHERE document.reference_type ='event_feed' and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1  )) as event_feed_document_names"
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'event_feed_document_urls',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                "(select GROUP_CONCAT(document.document_source) from document WHERE document.reference_type ='event_feed' and document.document_type = 1 and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1)) as event_feed_document_urls"
            );
        }

        if (isset($data['type']) && $data['type'] == 'project') {
            $this->db->join(
                'project_providers pps',
                'pps.provider_id=p.id_provider',
                'left'
            );
            $this->db->where('pps.project_id', $data['project_id']);
            $this->db->where('pps.is_linked', 1);
        }
        if (isset($data['customer_id'])) {
            $this->db->where('p.customer_id', $data['customer_id']);
        }
        if (isset($data['id_provider'])) {
            $this->db->where('p.id_provider', $data['id_provider']);
        }
        if (isset($data['provider_array'])) {
            $this->db->where_in('p.id_provider', $data['provider_array']);
        }
        // if(isset($data['status']))
        //     $this->db->where('p.status',$data['status']);
        // else
        //     $this->db->where('status !=',2);
        if (isset($data['search'])) {
            $this->db->group_start();
            $this->db->like('p.provider_name', $data['search'], 'both');
            $this->db->or_like('p.unique_id', $data['search'], 'both');
            $this->db->or_like('c.country_name', $data['search'], 'both');
            $this->db->or_like(
                'prcl.relationship_category_name',
                $data['search'],
                'both'
            );
            $this->db->or_like('p.vat', $data['search'], 'both');
            // $this->db->or_like('address', $data['search'], 'both');
            // $this->db->or_like('contact_no', $data['search'], 'both');
            $this->db->group_end();
        }
        //////////advanced filters start ///////////////
        foreach ($data['adv_filters'] as $filter) {
            //  print_r($filter);
            if ($filter['domain'] == 'Relation Tags') {
                $tagId = $filter['master_domain_field_id'];
                $condition = $filter['condition'];
                $value = $filter['value'];
                // if ($filter['field_type'] == 'drop_down') {
                //     $tagOptionValue =
                //         "'" . str_replace(',', "','", $value) . "'";
                //     $this->db->where(
                //         "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider AND provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value in ($tagOptionValue))"
                //     );
                // }
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider AND provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND FIND_IN_SET($tagOptionValue, provider_tags.tag_option))");
                    }
                    $this->db->group_end();
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND DATE(provider_tags.tag_option_value) $condition  '$value')"
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    if ($filter['condition'] == 'like') {
                        $this->db->where(
                            "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value LIKE '%$value%' ESCAPE '!')"
                        );
                    } else {
                        $this->db->where(
                            "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value $condition  '$value')"
                        );
                    }
                }
            }
            elseif($filter['domain'] == 'Relation Contact')
            {
                $condition = $filter['condition'];
                $value = $filter['value'];
                $dbField = $filter['database_field'];
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND $dbField $condition  '$tagOptionValue')");
                        
                    }
                    $this->db->group_end();
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND  $dbField $condition  '$value')"
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    if ($filter['condition'] == 'like') {
                        $this->db->where(
                            "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND   $dbField LIKE '%$value%' ESCAPE '!')"
                        );
                       
                    } else {
                        $this->db->where(
                            "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND  $dbField $condition  '$value')"
                        );
                    }
                }

            }
            else {
                if ($filter['field_type'] == 'drop_down') {
                    $this->db->where_in(
                        $filter['database_field'],
                        explode(',', $filter['value'])
                    );
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        'DATE(' .
                            $filter['database_field'] .
                            ')' .
                            $filter['condition'],
                        $filter['value']
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    $filter['value'] = str_replace(',', '', $filter['value']);
                    if ($filter['condition'] == 'like') {
                        $this->db->like(
                            $filter['database_field'],
                            $filter['value'],
                            'both'
                        );
                    } elseif (
                        $filter['condition'] == '<' ||
                        $filter['condition'] == '>' ||
                        $filter['condition'] == '='
                    ) {
                        $this->db->where(
                            $filter['database_field'] .
                                ' ' .
                                $filter['condition'],
                            $filter['value']
                        );
                    }
                }
            }
        }
        //exit;
        //////////advanced filters end ///////////////
        if (!empty($data['status'])) {
            $this->db->where('p.status', $data['status']);
        } else {
            $this->db->where('p.status !=', 2);
        }
        if (isset($data['country_id'])) {
            $this->db->where('p.country', $data['country_id']);
        }
        if (isset($data['relationship_category_id'])) {
            $this->db->where(
                'p.category_id',
                $data['relationship_category_id']
            );
        }
        if (isset($data['can_access']) && empty($data['type'])) {
            $this->db->where('p.status', $data['can_access']);
        }
        $this->db->group_by('p.id_provider');
        $subquery = $this->db->get_compiled_select();
        // $result = $this->db->get();
        $this->db->_reset_select();
        $this->db->select('*');
        $this->db->from("($subquery)as unionTable");
        if (!empty($data['approval_status'])) {
            $this->db->where('approval_status', $data['approval_status']);
        }
        if (!empty($data['risk_profile'])) {
            $this->db->where('risk_profile', $data['risk_profile']);
        }
        foreach ($data['adv_union_filters'] as $Unionfilter) {
            if ($Unionfilter['field_type'] == 'drop_down') {
                $this->db->where_in(
                    $Unionfilter['database_field'],
                    explode(',', $Unionfilter['value'])
                );
            } elseif ($Unionfilter['field_type'] == 'date') {
                $this->db->where(
                    'DATE(' .
                        $Unionfilter['database_field'] .
                        ')' .
                        $Unionfilter['condition'],
                    $Unionfilter['value']
                );
            }
            // elseif($Unionfilter['field_type']=='free_text'){
            //     if($Unionfilter['condition']=='like'){
            //         $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
            //     }
            //     if($Unionfilter['condition']=='free_text' || $filter['condition']=='='){
            //         if($Unionfilter['database_field']!='document_names' && $Unionfilter['database_field']!='document_urls'){
            //             $this->db->where($Unionfilter['database_field'],$Unionfilter['value']);
            //         }
            //     }
            // }
            elseif (
                $Unionfilter['field_type'] == 'free_text' ||
                $Unionfilter['field_type'] == 'numeric_text'
            ) {
                if ($Unionfilter['condition'] == 'like') {
                    $this->db->like(
                        $Unionfilter['database_field'],
                        $Unionfilter['value'],
                        'both'
                    );
                } elseif (
                    $Unionfilter['condition'] == '<' ||
                    $Unionfilter['condition'] == '>' ||
                    $Unionfilter['condition'] == '='
                ) {
                    $this->db->where(
                        $Unionfilter['database_field'] .
                            ' ' .
                            $Unionfilter['condition'],
                        $Unionfilter['value']
                    );
                }
            }
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get(); //echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();
        if (
            isset($data['pagination']['number']) &&
            $data['pagination']['number'] != ''
        ) {
            $this->db->limit(
                $data['pagination']['number'],
                $data['pagination']['start']
            );
        }
        if (
            isset($data['sort']['predicate']) &&
            $data['sort']['predicate'] != '' &&
            isset($data['sort']['reverse'])
        ) {
            $this->db->order_by(
                $data['sort']['predicate'],
                $data['sort']['reverse']
            );
        } else {
            $this->db->order_by('provider_name', 'asc');
        }
        $result = $this->db->get(); //echo $this->db->last_query();exit;
        return [
            'total_records' => $count_result,
            'data' => $result->result_array(),
        ];
        // if($rec_count > 0 || count($result->result_array())>0)
        // else
        //     return array('total_count'=>(int)$rec_count,'data'=>[]);
    }

    public function optProviderList($data = null)
    {
        $this->db->select('prc.can_review,p.*,prcl.relationship_category_name as category_name,c.country_name,(SELECT currency_name FROM currency WHERE customer_id=p.customer_id and is_maincurrency=1 and status=1) currency_name');
        $userId = $this->session_user_info->id_user;
        if(!(isset($data['id_provider']) && $data['id_provider'] > 0))
        {
            if(in_array($this->session_user_info->user_role_id,array(3,4,6,7))){
                $this->db->select('(SELECT COUNT(*) FROM `contract_review_action_item` WHERE `provider_id` = p.id_provider AND `item_status` = 1 AND `status` = "open" AND `responsible_user_id` = '.$userId.' AND `reference_type` = "provider") as action_items_count');
            }
            else
            {
                $this->db->select('(SELECT COUNT(*) FROM `contract_review_action_item` WHERE `provider_id` = p.id_provider AND `item_status` = 1 AND `status` = "open"  AND `reference_type` = "provider") as action_items_count');
            }
        }
        if (isset($data['id_provider']) && $data['id_provider'] > 0) {
            $this->db->select('(SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
            LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
            LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_1" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as risk_profile_comments,
            (SELECT `pt`.`comments` `risk_profile_value` FROM `tag` `t`
            LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id`
            LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_2" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as approval_status_comments,
            (SELECT `pt`.`comments` `finacial_health_value` FROM `tag` `t` LEFT JOIN `tag_language` `tl` ON `t`.`id_tag`=`tl`.`tag_id` LEFT JOIN `provider_tags` `pt` ON `t`.`id_tag`=`pt`.`tag_id` WHERE `t`.`status` = 1 AND `t`.`is_fixed` = 1 AND `t`.`type` = "provider_tags" AND `t`.`label` = "label_3" AND `pt`.`provider_id` = p.id_provider GROUP BY `pt`.`provider_id`) as finacial_health_comments,
            (SELECT GROUP_CONCAT(cnt.id_contract) FROM provider pr LEFT JOIN contract cnt ON pr.id_provider=cnt.provider_name WHERE cnt.is_deleted=0
            AND pr.id_provider=p.id_provider  
            GROUP BY pr.id_provider) contract_ids,
            (SELECT cry.currency_name FROM contract ctr  LEFT JOIN provider pr ON ctr.provider_name=pr.id_provider  LEFT JOIN currency cry on	 ctr.currency_id=cry.id_currency WHERE pr.id_provider=p.id_provider
            GROUP BY pr.id_provider) as currency_name_old,(SELECT COUNT(*) as action_items_count FROM contract_review_action_item WHERE item_status=1 AND status="open" AND `contract_id` IS NULL AND provider_id=p.id_provider)as action_items_count');
        }
        $this->db->from('provider p');
        if (isset($data['only_user_connected_providers'])) {
            $this->db->join('contract c', 'c.provider_name = id_provider');
            $this->db->group_by('p.id_provider');
        }
        $this->db->join(
            'provider_relationship_category prc',
            'prc.id_provider_relationship_category=p.category_id',
            'left'
        );
        $this->db->join(
            'provider_relationship_category_language prcl',
            'prc.id_provider_relationship_category = prcl.provider_relationship_category_id',
            'left'
        );
        $this->db->join('country c', 'p.country=c.id_country', 'left');
        //event feed realtions

        $this->db->join(
            'event_feeds ef',
            "p.id_provider = ef.reference_id and ef.reference_type = 'provider' and ef.status=1",
            'left'
        );

        if (
            (!empty($data['adv_union_filters']) &&
                is_numeric(
                    array_search(
                        'document_urls',
                        array_column(
                            $data['adv_union_filters'],
                            'database_field'
                        )
                    )
                )) ||
            is_numeric(
                array_search(
                    'document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->join(
                'document d',
                "p.id_provider=d.reference_id AND d.reference_type = 'provider' AND d.module_type = 'provider' AND d.document_status = 1",
                'left'
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                'GROUP_CONCAT(d.document_name) as document_names'
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'document_urls',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                'GROUP_CONCAT(d.document_source) as document_urls'
            );
        }

        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'event_feed_document_names',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                "(select GROUP_CONCAT(document.document_name) from document WHERE document.reference_type ='event_feed' and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1  )) as event_feed_document_names"
            );
        }
        if (
            !empty($data['adv_union_filters']) &&
            is_numeric(
                array_search(
                    'event_feed_document_urls',
                    array_column($data['adv_union_filters'], 'database_field')
                )
            )
        ) {
            $this->db->select(
                "(select GROUP_CONCAT(document.document_source) from document WHERE document.reference_type ='event_feed' and document.document_type = 1 and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = p.id_provider and event_feeds.reference_type = 'provider' and event_feeds.status =1)) as event_feed_document_urls"
            );
        }

        if (isset($data['type']) && $data['type'] == 'project') {
            $this->db->join(
                'project_providers pps',
                'pps.provider_id=p.id_provider',
                'left'
            );
            $this->db->where('pps.project_id', $data['project_id']);
            $this->db->where('pps.is_linked', 1);
        }
        if (isset($data['customer_id'])) {
            $this->db->where('p.customer_id', $data['customer_id']);
        }
        if (isset($data['id_provider'])) {
            $this->db->where('p.id_provider', $data['id_provider']);
        }
        if (isset($data['provider_array'])) {
            $this->db->where_in('p.id_provider', $data['provider_array']);
        }
        // if(isset($data['status']))
        //     $this->db->where('p.status',$data['status']);
        // else
        //     $this->db->where('status !=',2);
        if (isset($data['search'])) {
            $this->db->group_start();
            $this->db->like('p.provider_name', $data['search'], 'both');
            $this->db->or_like('p.unique_id', $data['search'], 'both');
            $this->db->or_like('c.country_name', $data['search'], 'both');
            $this->db->or_like(
                'prcl.relationship_category_name',
                $data['search'],
                'both'
            );
            $this->db->or_like('p.vat', $data['search'], 'both');
            // $this->db->or_like('address', $data['search'], 'both');
            // $this->db->or_like('contact_no', $data['search'], 'both');
            $this->db->group_end();
        }
        //////////advanced filters start ///////////////
        foreach ($data['adv_filters'] as $filter) {
            if ($filter['domain'] == 'Relation Tags') {
                $tagId = $filter['master_domain_field_id'];
                $condition = $filter['condition'];
                $value = $filter['value'];
                // if ($filter['field_type'] == 'drop_down') {
                //     $tagOptionValue =
                //         "'" . str_replace(',', "','", $value) . "'";
                //     $this->db->where(
                //         "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider AND provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value in ($tagOptionValue))"
                //     );
                // }
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider AND provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND FIND_IN_SET($tagOptionValue, provider_tags.tag_option))");
                    }
                    $this->db->group_end();
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND DATE(provider_tags.tag_option_value) $condition  '$value')"
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    if ($filter['condition'] == 'like') {
                        $this->db->where(
                            "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value LIKE '%$value%' ESCAPE '!')"
                        );
                    } else {
                        $this->db->where(
                            "EXISTS(SELECT tag_id FROM  provider_tags WHERE provider_tags.provider_id = p.id_provider And provider_tags.status=1 and provider_tags.tag_id =  $tagId  AND provider_tags.tag_option_value $condition  '$value')"
                        );
                    }
                }
            } 
            elseif($filter['domain'] == 'Relation Contact')
            {
                $condition = $filter['condition'];
                $value = $filter['value'];
                $dbField = $filter['database_field'];
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND $dbField $condition  '$tagOptionValue')");
                        
                    }
                    $this->db->group_end();
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND  $dbField $condition  '$value')"
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    if ($filter['condition'] == 'like') {
                        $this->db->where(
                            "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND   $dbField LIKE '%$value%' ESCAPE '!')"
                        );
                       
                    } else {
                        $this->db->where(
                            "EXISTS(SELECT id_user FROM  user u WHERE u.provider = p.id_provider AND u.user_status=1 and u.contribution_type in(2,3) AND email!='' AND  $dbField $condition  '$value')"
                        );
                    }
                }

            }
            else {
                if ($filter['field_type'] == 'drop_down') {
                    $this->db->where_in(
                        $filter['database_field'],
                        explode(',', $filter['value'])
                    );
                } elseif ($filter['field_type'] == 'date') {
                    $this->db->where(
                        'DATE(' .
                            $filter['database_field'] .
                            ')' .
                            $filter['condition'],
                        $filter['value']
                    );
                } elseif (
                    $filter['field_type'] == 'numeric_text' ||
                    $filter['field_type'] == 'free_text'
                ) {
                    $filter['value'] = str_replace(',', '', $filter['value']);
                    if ($filter['condition'] == 'like') {
                        $this->db->like(
                            $filter['database_field'],
                            $filter['value'],
                            'both'
                        );
                    } elseif (
                        $filter['condition'] == '<' ||
                        $filter['condition'] == '>' ||
                        $filter['condition'] == '='
                    ) {
                        $this->db->where(
                            $filter['database_field'] .
                                ' ' .
                                $filter['condition'],
                            $filter['value']
                        );
                    }
                }
            }
        }
        //exit;
        //////////advanced filters end ///////////////
        if (!empty($data['status'])) {
            $this->db->where('p.status', $data['status']);
        } else {
            $this->db->where('p.status !=', 2);
        }
        if (isset($data['country_id'])) {
            $this->db->where('p.country', $data['country_id']);
        }
        if (isset($data['relationship_category_id'])) {
            $this->db->where(
                'p.category_id',
                $data['relationship_category_id']
            );
        }
        if (isset($data['can_access']) && empty($data['type'])) {
            $this->db->where('p.status', $data['can_access']);
        }
        $this->db->group_by('p.id_provider');
        $subquery = $this->db->get_compiled_select();
        // $result = $this->db->get();
        $this->db->_reset_select();
        $this->db->select('*');
        $this->db->from("($subquery)as unionTable");
        if (!empty($data['approval_status'])) {
            $this->db->where('approval_status', $data['approval_status']);
        }
        if (!empty($data['risk_profile'])) {
            $this->db->where('risk_profile', $data['risk_profile']);
        }
        foreach ($data['adv_union_filters'] as $Unionfilter) {
            if ($Unionfilter['field_type'] == 'drop_down') {
                $this->db->where_in(
                    $Unionfilter['database_field'],
                    explode(',', $Unionfilter['value'])
                );
            } elseif ($Unionfilter['field_type'] == 'date') {
                $this->db->where(
                    'DATE(' .
                        $Unionfilter['database_field'] .
                        ')' .
                        $Unionfilter['condition'],
                    $Unionfilter['value']
                );
            }
            // elseif($Unionfilter['field_type']=='free_text'){
            //     if($Unionfilter['condition']=='like'){
            //         $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
            //     }
            //     if($Unionfilter['condition']=='free_text' || $filter['condition']=='='){
            //         if($Unionfilter['database_field']!='document_names' && $Unionfilter['database_field']!='document_urls'){
            //             $this->db->where($Unionfilter['database_field'],$Unionfilter['value']);
            //         }
            //     }
            // }
            elseif (
                $Unionfilter['field_type'] == 'free_text' ||
                $Unionfilter['field_type'] == 'numeric_text'
            ) {
                if ($Unionfilter['condition'] == 'like') {
                    $this->db->like(
                        $Unionfilter['database_field'],
                        $Unionfilter['value'],
                        'both'
                    );
                } elseif (
                    $Unionfilter['condition'] == '<' ||
                    $Unionfilter['condition'] == '>' ||
                    $Unionfilter['condition'] == '='
                ) {
                    $this->db->where(
                        $Unionfilter['database_field'] .
                            ' ' .
                            $Unionfilter['condition'],
                        $Unionfilter['value']
                    );
                }
            }
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get(); //echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();
        if (
            isset($data['pagination']['number']) &&
            $data['pagination']['number'] != ''
        ) {
            $this->db->limit(
                $data['pagination']['number'],
                $data['pagination']['start']
            );
        }
        if (
            isset($data['sort']['predicate']) &&
            $data['sort']['predicate'] != '' &&
            isset($data['sort']['reverse'])
        ) {
            $this->db->order_by(
                $data['sort']['predicate'],
                $data['sort']['reverse']
            );
        } else {
            $this->db->order_by('provider_name', 'asc');
        }
        $result = $this->db->get(); //echo $this->db->last_query();exit;
        return [
            'total_records' => $count_result,
            'data' => $result->result_array(),
        ];
        // if($rec_count > 0 || count($result->result_array())>0)
        // else
        //     return array('total_count'=>(int)$rec_count,'data'=>[]);
    }
    public function getproviderfilterlist($data){
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('c.provider_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        if(isset($data['customer_user'])) {
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
            $this->db->join('module m', 'm.id_module=cur.module_id', '');
            $this->db->where('cur.user_id',$data['customer_user']);
            $this->db->where('cur.status','1');
            $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');
        }
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        else if(isset($data['graph'])){

        }else
            $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('p.id_provider');
        $query = $this->db->get();     
        return $query->result_array(); 
    }

    public function addprovider($data){
        $this->db->insert('provider',$data);
        return $this->db->insert_id();
    }
    public function updateprovider($data,$id){
        $this->db->where('id_provider',$id);
        $this->db->update('provider',$data);
        return 1;
    }
    public function dailynotificationcount($data){
        $this->db->select('m.*');
        $this->db->from('mailer m');
        $this->db->join('email_template t', 'm.email_template_id=t.id_email_template', 'LEFT');
        $this->db->where('t.module_key','CONTRACT_DAILY_UPDATE');
        $this->db->where('m.mail_to_user_id',$data['id_user']);
        if(isset($data['is_opened'])){
            if($data['is_opened']==1)
                $this->db->where('m.is_notification_opened',1); //new
            else if($data['is_opened']==0)
                $this->db->where('m.is_notification_opened',0); //old
        }
        /*$this->db->where('m.is_notification_opened',0);*/
        $result = $this->db->get()->num_rows();
        return $result;
    }
    /*Sam*/
    public function dailyNotificationList($data){
        $this->db->select('m.*,d.content, d.`date`');
        $this->db->from('mailer m');
        $this->db->join('email_template t', 'm.email_template_id=t.id_email_template', 'LEFT');
        $this->db->join('user u', 'm.mail_to_user_id=u.id_user', 'LEFT');
        $this->db->join('daily_update_customer d', "u.customer_id=d.customer_id", 'LEFT');
        $this->db->where('DATE(`d`.`created_on`)=DATE(`m`.`send_date`)');
        $this->db->where('t.module_key','CONTRACT_DAILY_UPDATE');
        $this->db->where('m.mail_to_user_id',$data['id_user']);
        if(isset($data['is_opened'])){
            if($data['is_opened']==1)
                $this->db->where('m.is_notification_opened',1);
            else if($data['is_opened']==0)
                $this->db->where('m.is_notification_opened',0);
        }
        /*$this->db->where('m.is_notification_opened',0);*/

        /* results count start */
        $all_notification_db = clone $this->db;
        $all_notification_count = $all_notification_db->get()->num_rows();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('m.send_date','DESC');

        $query = $this->db->get();
        return array('total_records' => $all_notification_count,'data' => $query->result_array());

        /*$result = $this->db->get()->result_array();
        return $result;*/
    }
    public function updatedailynotificationcount($data){

        $query="UPDATE mailer m, email_template t SET m.is_notification_opened = 1
                WHERE t.module_key = 'CONTRACT_DAILY_UPDATE'
                AND m.mail_to_user_id = ?
                AND m.is_notification_opened =0
                AND DATE(m.send_date) =?
                AND m.email_template_id = t.id_email_template";
        $result = $this->db->query($query,array($data['id_user'],$data['date']));
        return 1;
    }

    public function getValidationContributors($data){
        
        $this->db->select('u.*')->from('user u');
        $this->db->join('contract_user cu ',' u.id_user = cu.user_id','left');
        $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        if(isset($data['module_id']))
            $this->db->where('cu.module_id',$data['module_id']);
        $this->db->where('cu.status',1);
        $this->db->where('u.contribution_type',1);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getUserModules($data){
        $this->db->select('module_name')->from('module_language ml');
        if(isset($data['module_id']))
            if(is_array($data['module_id']))
                $this->db->where_in('ml.module_id',$data['module_id']);

        $query = $this->db->get();
        return $query->result_array();
    }
    public function getriskandapproval($data=null){
        $this->db->select('pt.tag_option_value risk_profile_value');
        $this->db->from('tag t');
        $this->db->join('tag_language tl','t.id_tag=tl.tag_id','left');
        $this->db->join('provider_tags pt','t.id_tag=pt.tag_id','left');
        if(!empty($data['customer_id'])){
            $this->db->where('t.customer_id',$data['provider_tags']);
        }
        $this->db->where('t.status',1);
        $this->db->where('t.is_fixed',1);
        $this->db->where('t.type','provider_tags');
        
        if(!empty($data['tag_text'])){
            $this->db->where('tl.tag_text',$data['tag_text']);
        }
        if(!empty($data['provider_id'])){
            $this->db->where('pt.provider_id',$data['provider_id']);
        }
                $query = $this->db->get();
        return $query->result_array();

    }

    public function getInfoProviderTags($data){
        $this->db->select('pt.id_provider_tag,tl.tag_text,t.id_tag,t.tag_type,t.field_type,pt.tag_option,IF(pt.tag_option=0,pt.tag_option_value,pt.tag_option) tag_answer,pt.comments,t.selected_field,t.multi_select,t.business_unit_id,pt.tag_option_value as tag_option_values');
        $this->db->from('provider_tags pt');
        $this->db->join('tag t','t.id_tag = pt.tag_id');
        $this->db->join('tag_language tl','t.id_tag = tl.tag_id');
        if(isset($data['provider_id']))
            $this->db->where('pt.provider_id',$data['provider_id']);
        $this->db->where('pt.status',1);
        $this->db->where('t.status',1);
        $this->db->group_by('t.id_tag');  
        // $this->db->order_by('t.tag_order');
        if(isset($data['orderBy']) && ($data['orderBy'] == 'forExport'))
        {
            $this->db->order_by('t.is_fixed','desc');
        }
        else
        {
            $this->db->order_by('t.id_tag','asc');
        }
        // $this->db->order_by('t.id_tag','asc');
        $query = $this->db->get();
        //echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function checkuniqueidexitst($data=null){
        $this->db->select('*');
        $this->db->from('provider');
        if(isset($data['id_provider'])){
            $this->db->where('id_provider!=',$data['id_provider']);
        }
        if(isset($data['unique_id'])){
            $this->db->where('unique_id',$data['unique_id']);
        }
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProviderloginfo($data=null){
        $this->db->select('p.*,prcl.relationship_category_name as category_name,c.country_name');
        $this->db->from('provider_log p');
        $this->db->join('provider_relationship_category prc','p.category_id=prc.id_provider_relationship_category','left');
        $this->db->join('country c','p.country=c.id_country','left');
        $this->db->join('provider_relationship_category_language prcl','prc.id_provider_relationship_category=prcl.provider_relationship_category_id','left');
        $this->db->where('p.id_provider_log',$data['provider_log_id']);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getOptionvalue($data=null){
        $this->db->select('tag_option_name as tag_answer');
        $this->db->from('tag_option ton');
        $this->db->join('tag_option_language tol','ton.id_tag_option=tol.tag_option_id','left');
        $this->db->where('ton.id_tag_option',$data['id_tag_option']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProviderTotalSpent($data)
    {
        $this->db->select('c.id_contract, c.currency_id, c.contract_value, c.contract_value_period, c.po_number, c.additional_recurring_fees, c.additional_recurring_fees_period, c.additonal_one_off_fees, c.contract_start_date, c.contract_end_date, TIMESTAMPDIFF(MONTH, c.contract_start_date, c.contract_end_date) months, IF((c.contract_value_period="total"OR (ISNULL(c.contract_value_period))), ROUND(c.contract_value), ROUND(c.contract_value*(TIMESTAMPDIFF(MONTH, c.contract_start_date, c.contract_end_date)/12))) as ProjectedValue, CASE WHEN (c.additional_recurring_fees_period is NULL or c.additional_recurring_fees_period = "" ) THEN ROUND(c.additional_recurring_fees) 
        WHEN c.additional_recurring_fees_period ="month" Then ROUND(c.additional_recurring_fees*(TIMESTAMPDIFF(MONTH, c.contract_start_date, c.contract_end_date)))
        WHEN c.additional_recurring_fees_period ="quarter" THEN ROUND((c.additional_recurring_fees/3)*(TIMESTAMPDIFF(MONTH, c.contract_start_date, c.contract_end_date)))
        ELSE ROUND((c.additional_recurring_fees)*(TIMESTAMPDIFF(MONTH, c.contract_start_date, c.contract_end_date)/12))
        END AS Additional_Reccuring_fees_value,cu.currency_name,IFNULL((SELECT euro_equivalent_value  FROM currency  WHERE currency_name=cu.currency_name AND customer_id=bu.customer_id AND is_deleted =0), 0) as euro_equivalent_value'); 
        $this->db->from("contract c"); 
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->where_in('id_contract', $data["contract_ids"]);
        $new_query= $this->db->get_compiled_select();
        $this->db->_reset_select();
        // $this->db->select("SUM(Additional_Reccuring_fees_value)+SUM(ProjectedValue)+SUM(additonal_one_off_fees) as spent")->from("($new_query) as unionTable");
        $this->db->select("*")->from("($new_query) as unionTable");
            $result = $this->db->get()->result_array();
        return $result;
    }
    public function getProviderContracts($data)
    {
     
        $this->db->select('c.id_contract');
        $this->db->from("contract c"); 
        $this->db->join('provider p','p.id_provider=c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->where('p`.id_provider', $data["provider_id"]);
        $this->db->where('bu.customer_id', $data["customer_id"]);
        $this->db->where('c.is_deleted', 0);
        $this->db->group_by('c.id_contract');
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function providerList($data)
    {
        $this->db->select("id_provider,provider_name");
        $this->db->from("provider p");
        if(isset($data['customer_id']) && $data['customer_id']>0)
        {
            $this->db->where('p.customer_id',$data['customer_id']);
        }
        $this->db->where('p.status',1);
        $query = $this->db->get();  
        $this->db->order_by('p.provider_name','asc');  
        //echo $this->db->last_query();exit;
        return $query->result_array(); 
           

    }

}