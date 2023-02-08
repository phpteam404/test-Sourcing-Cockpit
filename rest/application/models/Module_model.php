<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Module_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function moduleList($data)
    {
        //print_r($data); exit;
        $this->db->select('m.*,l.*,count(DISTINCT t.id_topic) as topic_count,count(DISTINCT q.id_question) as question_count');
        $this->db->from('module m');
        $this->db->join('module_language l','m.id_module=l.module_id','left');
        $this->db->join('topic t','m.id_module=t.module_id','left');
        $this->db->join('question q','t.id_topic=q.topic_id','left');
        if(!isset($data['is_workflow'])){
            $this->db->where('m.is_workflow',0);
        } else {
            $this->db->select('tp.import_status,m.to_avail_template as workflow_template_id');
            $this->db->join('template tp','m.to_avail_template=tp.id_template','left');
            $this->db->where('m.is_workflow',1);
        }
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['contract_review_id']))
            $this->db->where('m.contract_review_id',$data['contract_review_id']);
        if(isset($data['customer_id'])){
            $this->db->where('m.customer_id',$data['customer_id']);
        }else{
            $this->db->where('m.customer_id is null');
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.module_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('m.module_status',$data['status']);
        }
        /*if(isset($data['search']))
            $this->db->where('(l.module_name like "%'.$data['search'].'%")');*/
        $this->db->group_by('m.id_module');
        $all_clients_count_db = clone $this->db;
       // echo ''.$this->db->last_query(); exit;
        $all_clients_count = $all_clients_count_db->get()->num_rows();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('m.id_module','ASC');
        $query = $this->db->get();
         //echo ''.$this->db->last_query(); exit;
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function addModule($data)
    {
        $this->db->insert('module', $data);
        return $this->db->insert_id();
    }

    public function getStorableModules($data){
        $this->db->select('*')->from('module');
        $this->db->where('contract_review_id',$data['contract_review_id']);
        $this->db->where_in('module_status',$data['module_status']);
        if(isset($data['static']))
            $this->db->where('(static = 1 or is_workflow = 1)');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addModuleLanguage($data)
    {
        $this->db->insert('module_language', $data);
        return $this->db->insert_id();
    }

    public function updateModule($data)
    {   unset($data['customer_id']);
        $this->db->where('id_module', $data['id_module']);
        $this->db->update('module', $data);
        return 1;
    }

    public function updateModuleLanguage($data)
    {
        // if(isset($data['is_workflow']) && $data['is_workflow']==TRUE)
        //     $this->db->where('id_module_language', $data['id_module_language']);
        //     $this->db->update('template',$data);
        $this->db->where('id_module_language', $data['id_module_language']);
        $this->db->update('module_language', $data);
        return 1;
    }

    public function getModuleName($data)
    {
        $this->db->select('m.id_module,l.id_module_language,l.module_name,m.type,m.module_status,m.is_workflow');
        $this->db->from('module m');
        $this->db->join('module_language l','m.id_module=l.module_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['module_id']))
            $this->db->where('m.id_module',$data['module_id']);

        $query = $this->db->get();
        return $query->result_array();
    }
}