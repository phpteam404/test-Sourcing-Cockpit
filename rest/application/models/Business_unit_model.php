<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Business_unit_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function getBusinessUnitList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/

        $this->db->select("IF(c.country_name!='',CONCAT(bu.bu_name,' - ',c.country_name),bu.bu_name) as bu_name,c.*,bu.customer_id,bu.bu_responsibility,bu.company_address,bu.postal_code,bu.city,bu.country_id,bu.status,bu.created_by,bu.updated_by,bu.created_on,bu.updated_on, 0 as no_of_contracts,bu.id_business_unit");
        $this->db->from('business_unit bu');
        $this->db->join('country c','bu.country_id=c.id_country','left');
        //if(isset($data['status']))
            //$this->db->where('bu.status',$data['status']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('bu.bu_name', $data['search'], 'both');
            $this->db->or_like('bu.bu_responsibility', $data['search'], 'both');
            $this->db->or_like('bu.city', $data['search'], 'both');
            $this->db->or_like('bu.postal_code', $data['search'], 'both');
            $this->db->or_like('c.country_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(bu.bu_name like "%'.$data['search'].'%"
            or bu.bu_responsibility like "%'.$data['search'].'%"
            or bu.city like "%'.$data['search'].'%"
            or bu.postal_code like "%'.$data['search'].'%"
            or c.country_name like "%'.$data['search'].'%" )');*/
        $this->db->group_by('bu.id_business_unit');

        if(!empty($data['business_unit_array'])){
            $this->db->where_in('bu.id_business_unit',$data['business_unit_array']);
        }
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('bu.status',$data['status']);
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
            $this->db->order_by('bu.bu_name','ASC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getProviderUserBusinessUnits($data)
    {
        $this->db->select('bu.id_business_unit,bu.customer_id,bu.bu_responsibility,bu.company_address,bu.postal_code,bu.city,bu.country_id,bu.status,bu.created_by,bu.updated_by,bu.created_on,bu.updated_on,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name, 0 as no_of_contracts')->from('business_unit bu');
        $this->db->join('business_unit_user buu ',' bu.id_business_unit = buu.business_unit_id','left');
        $this->db->join('user u ',' buu.user_id = u.id_user','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        $this->db->where('u.contribution_type',1);
        $this->db->group_by('bu.id_business_unit');
        $this->db->order_by('bu.bu_name','ASC');

        $query = $this->db->get();
        return $query->result_array();

    }

    public function getBusinessUnitDetails($data)
    {
        $this->db->select('*');
        $this->db->from('business_unit bu');
        $this->db->join('country c','bu.country_id=c.id_country','left');
        if(isset($data['id_business_unit']))
            $this->db->where('id_business_unit', $data['id_business_unit']);
        if(isset($data['status']))
            $this->db->where('status', $data['status']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addBusinessUnit($data)
    {
        $this->db->insert('business_unit', $data);
        return $this->db->insert_id();
    }

    public function addBusinessUnitUser($data){
                        //adding into BUU on assigninng to contributor
        foreach($data['users'] as $k => $v) {
            $existing_buu = $this->getBusiness_Unit_User(array('business_unit_id'=>$data["business_unit_id"],'user_id'=>$v));
            if(!empty($existing_buu)){
                    if($existing_buu[0]['status']==0) {
                        $this->db->where('id_business_unit_user', $existing_buu[0]['id_business_unit_user']);
                        $this->db->update('business_unit_user', array('status' => 1));
                    }
            }else {
                    $this->db->insert('business_unit_user', array('business_unit_id' => $data["business_unit_id"], 'user_id' => $v, 'status' => 1, 'created_on' => currentDate(), 'created_by' => $data['created_by']));
            }

        }


    }

    public function getBusiness_Unit_User($data){
        $this->db->select('*');
        $this->db->from('business_unit_user b');
        if(isset($data['business_unit_id']))
            $this->db->where('b.business_unit_id', $data['business_unit_id']);
        if(isset($data['user_id']))
            $this->db->where('b.user_id', $data['user_id']);
        if(isset($data['status']))
            $this->db->where('b.status', $data['status']);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function updateBusinessUnit($data)
    {
        $this->db->where('id_business_unit', $data['id_business_unit']);
        $this->db->update('business_unit', $data);
        return 1;
    }

    public function getBusinessUnitUser($data)
    {
        $this->db->select('IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,bu.id_business_unit business_unit_id,`bu`.`id_business_unit` as `id_business_unit`');
        $this->db->from('business_unit_user b');
        $this->db->join('business_unit bu','b.business_unit_id=bu.id_business_unit');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        if(isset($data['business_unit_id']))
            $this->db->where('b.business_unit_id', $data['business_unit_id']);
        if(isset($data['user_id']))
            $this->db->where('user_id', $data['user_id']);
        if(isset($data['status']))
            $this->db->where('b.status', $data['status']);
        if(isset($data['status']))
            $this->db->where('bu.status', $data['status']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id', $data['customer_id']);
        $this->db->group_by('bu.id_business_unit');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function mapBusinessUnitUser($data)
    {
        $this->db->insert_batch('business_unit_user', $data);
        return 1;
    }

    public function updateBusinessUnitUser($data)
    {
        if(isset($data['user_id']))
            $this->db->where('user_id',$data['user_id']);
        if(isset($data['business_unit_id']))
            $this->db->where('business_unit_id',$data['business_unit_id']);
        $this->db->update('business_unit_user',$data);
    }

    public function getProviderBusinessUnits($data)
    {
        $this->db->select('distinct(bu.id_business_unit) bulist')->from('contract_user cu');
        $this->db->join('contract c','cu.contract_id = c.id_contract');
        $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id');
        $this->db->where('cu.user_id',$data['user_id']);
        $this->db->where('cu.status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
}