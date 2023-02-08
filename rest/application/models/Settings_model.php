<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function getSettings($data)
    {
       /* if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('app_config a');
        if(isset($data['customer_id']))
            $this->db->where('a.customer_id',$data['customer_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('a.name', $data['search'], 'both');
            $this->db->or_like('a.key', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(a.name like "%'.$data['search'].'%" or a.key like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('a.id_app_config','DESC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function updateSettings($data)
    {
        $this->db->where('id_app_config',$data['id_app_config']);
        $this->db->update('app_config', $data);
        return 1;
    }



}