<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function getCountryList($data)
    {
        $this->db->select('*');
        $this->db->from('country');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getCurrencyList($data)
    {
        $this->db->select('*');
        $this->db->from('currency');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getUserRole($data)
    {
        $this->db->select('*');
        $this->db->from('user_role');
        if(isset($data['user_role_id']) && $data['user_role_id']>2)
            $this->db->where_not_in('id_user_role',array(1,2,6));
        else
            $this->db->where_not_in('id_user_role',array(1,2));
        if(isset($data['user_role_id']))
            $this->db->where('id_user_role >',$data['user_role_id']);
        $this->db->where('role_status',1);
        $this->db->where('id_user_role != 7');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProviderActionItems($data=null)
    {
        $this->db->select('*');
        $this->db->from('contract_review_action_item');
        if(isset($data['provider_id']))
        $this->db->where('provider_id',$data['provider_id']);
        if(isset($data['item_status']))
        $this->db->where('item_status',$data['item_status']);
        if(isset($data['status']))
        $this->db->where('status',$data['status']);
        if(isset($data['responsible_user_id']))
        $this->db->where('responsible_user_id',$data['responsible_user_id']);
        if(isset($data['user_role_id']))
        $this->db->where('responsible_user_id',$data['id_user']);
        if(isset($data['reference_type']))
        $this->db->where('reference_type',$data['reference_type']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getAvailableCurrencies($data=null){
        $this->db->select('*');
        $this->db->from('currency');
        $this->db->where('customer_id','0');
        $this->db->where('is_deleted',0);
        if(!empty($data['not_in_codes'])){
            $this->db->where_not_in('currency_name',$data['not_in_codes']);
        }
        $this->db->order_by('currency_full_name','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getadditionalcurs($data=null){
        // print_r($data);exit;
        $this->db->select('*');
        $this->db->from('currency');
        $this->db->where('customer_id',$data['customer_id']);
        $this->db->where('is_maincurrency',0);
        $this->db->where('is_deleted',0);
        if(isset($data['can_access']) && $data['can_access']==0){
            $this->db->where('status',0);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('currency_full_name', $data['search'], 'both');
            $this->db->or_like('currency_name', $data['search'], 'both');
            $this->db->or_like('euro_equivalent_value', $data['search'], 'both');
            $this->db->group_end(); 
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();
        // echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();


        if(isset($data['pagination']['number']) && $data['pagination']['number']!=''){
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        }
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else{
            $this->db->order_by('id_currency','DESC');
        }
       $result = $this->db->get()->result_array();
       return array('total_records' => $count_result,'data' => $result);
    }

    public function getFilters($data)
    {
        $this->db->select('uaf.*');
        $this->db->from('user_advanced_filters uaf');
        if(isset($data['user_id']))
        {
            $this->db->where('user_id',$data['user_id']);
        }
        if(isset($data['module']))
        {
            $this->db->where('module',$data['module']);
        }
        if(isset($data['id_master_filter']))
        {
            $this->db->where('id_master_filter',$data['id_master_filter']);
        }
        $this->db->where('status',1);
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getLanguages($data)
    {
        $this->db->select('l.*');
        $this->db->from('language l');
        if(isset($data['language_not_in']))
        {
            $this->db->where_not_in('l.id_language', $data['language_not_in']);
        }
        $this->db->where('status',1);
        $query = $this->db->get()->result_array();
        return $query;

    }
    public function getUserLanguages($data)
    {
        $this->db->select('l.*,cl.is_primary');
        $this->db->from('customer_languages cl');
        $this->db->join('language l','cl.language_id=l.id_language','left');  
        $this->db->where('l.status',1);
        $this->db->where('cl.customer_id',$data['customer_id']);
        $this->db->where('cl.status',1);
        $query = $this->db->get()->result_array();
        return $query;

    }
}