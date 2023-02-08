<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Question_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function ModuleTopicQuestionList($data)
    {

        $this->db->select('m.id_module,ml.module_name,t.id_topic,tl.topic_name,COUNT(q.id_question) as question_count');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->join('topic t','m.id_module=t.module_id','');
        $this->db->join('topic_language tl','t.id_topic=tl.topic_id','left');
        $this->db->join('question q','t.id_topic=q.topic_id','left');
        if(!isset($data['is_workflow'])){
            $this->db->where('m.is_workflow',0);
        }
        else
        {
            $this->db->where('m.is_workflow',1);
        }
        if(isset($data['topic_status']))
            $this->db->where('t.topic_status',$data['topic_status']);
        if(isset($data['contract_review_id']))
            $this->db->where('m.contract_review_id',$data['contract_review_id']);
        if(isset($data['customer_id'])){
            $this->db->where('m.customer_id',$data['customer_id']);
        }else{
            $this->db->where('m.customer_id is null');
        }

        $this->db->group_by('t.id_topic');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('ml.module_name', $data['search'], 'both');
            $this->db->or_like('tl.topic_name', $data['search'], 'both');
            $this->db->group_end();
        }

        $all_clients_count_db = clone $this->db;
        $all_clients_count = $all_clients_count_db->get()->num_rows();
        // print_r($all_clients_count);exit;
        // $all_clients_count = $all_clients_count->
        /*if(isset($data['search']))
            $this->db->where('(tl.topic_name like "%'.$data['search'].'%" or ml.module_name like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('q.question_order','ASC');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function QuestionList($data)
    {
        $this->db->select('q.*,l.*');
        $this->db->from('question q');
        $this->db->join('question_language l','q.id_question=l.question_id','left');
        /*$this->db->join('relationship_category_question rcq','rcq.question_id=q.id_question and rcq.status=1','left');*/
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['id_topic']))
            $this->db->where('q.topic_id',$data['id_topic']);
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('q.question_status',$data['status']);
        }
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('q.question_order','ASC');
        $this->db->group_by('q.id_question');
        $query = $this->db->get();
        //echo '<pre>'.$this->db->last_query();exit;
        $inner_data['status'] = 1;
        $inner_data['customer_id'] = $data['customer_id'];//echo '<pre>'.$this->db->last_query();exit;
        $result=$query->result_array();
        foreach($result as $kr=>$vr){
            $inner_data['question_id']=$vr['id_question'];
            $result[$kr]['relationship_categories'] = $this->getQuestionRelationshipCategory($inner_data);
        }
        //echo $this->db->last_query(); exit;
        //return array('total_records' => $all_clients_count,'data' => $query->result_array());
        return $result;
    }

    public function getQuestionInfo($data)
    {
        $this->db->select('q.*,l.*,o.*,group_concat(ol.id_question_option_language) as id_question_option_language,group_concat(ol.option_name) as option_name');
        $this->db->from('question q');
        $this->db->join('question_language l','q.id_question=l.question_id','left');
        $this->db->join('question_option o','q.id_question=o.question_id  and o.status=1','left');
        $this->db->join('question_option_language ol','o.id_question_option=ol.question_option_id and ol.status=1','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['id_question']))
            $this->db->where('q.id_question',$data['id_question']);
        if(isset($data['question_id']))
            $this->db->where('q.id_question',$data['question_id']);
        $query = $this->db->get();
        $result=$query->result_array();
        foreach($result as $kr=>$vr){
            $inner_data['question_id']=$vr['id_question'];
            $this->db->select('o.*,ol.*');
            $this->db->from('question_option o');
            $this->db->join('question_option_language ol','o.id_question_option=ol.question_option_id and ol.status=1','left');
            $this->db->where('o.question_id',$vr['id_question']);
            $this->db->where('ol.id_question_option_language is not null');
            $this->db->where('o.status','1');
            $sub_query = $this->db->get();
            $result[$kr]['option_names'] = $sub_query->result_array();
        }
        //$this->db->group_by('q.id_question');
        //echo $this->db->last_query(); exit;
        return $result;
    }

    public function addQuestion($data)
    {
        $this->db->insert('question', $data);
        return $this->db->insert_id();
    }

    public function addQuestionLanguage($data)
    {
        $this->db->insert('question_language', $data);
        return $this->db->insert_id();
    }

    public function addQuestionOption($data)
    {
        $this->db->insert('question_option', $data);
        return $this->db->insert_id();
    }

    public function addQuestionOptionLanguage($data)
    {
        $this->db->insert('question_option_language', $data);
        return $this->db->insert_id();
    }

    public function updateQuestion($data)
    {
        $this->db->where('id_question', $data['id_question']);
        $this->db->update('question', $data);
        return 1;
    }

    public function updateQuestionBacth($data)
    {
        $this->db->update_batch('question',$data, 'id_question');
    }

    public function updateQuestionLanguage($data)
    {
        $this->db->where('id_question_language', $data['id_question_language']);
        $this->db->update('question_language', $data);
        return 1;
    }

    public function updateQuestionOption($data)
    {
        $this->db->where('id_question_option', $data['id_question_option']);
        $this->db->update('question_option', $data);
        return 1;
    }

    public function updateQuestionOptionLanguage($data)
    {
        $this->db->where('id_question_option_language', $data['id_question_option_language']);
        $this->db->update('question_option_language', $data);
        return 1;
    }

    public function getExistingOptions($data)
    {
        $this->db->select('*');
        $this->db->from('question_option_language');
        if(isset($data['id_question_option']))
            $this->db->where('id_question_option',$data['id_question_option']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addRelationshipCategoryQuestion($data)
    {
        $this->db->insert('relationship_category_question',$data);
        return $this->db->insert_id();
    }

    public function updateRelationshipCategoryQuestion($data)
    {
        $this->db->where('id_relationship_category_question', $data['id_relationship_category_question']);
        $this->db->update('relationship_category_question', $data);
        return 1;
    }

    public function getQuestionRelationshipCategory($data)
    {
        $this->db->select('rc.*,rcl.relationship_category_name,rcq.id_relationship_category_question,IFNULL(rcq.status,0) as status,rcq.provider_visibility');
        $this->db->from('relationship_category rc');
        $this->db->join('relationship_category_language rcl','rc.id_relationship_category=rcl.relationship_category_id','left');
        if(isset($data['question_id'])) {
            $this->db->join('relationship_category_question rcq', 'rc.id_relationship_category=rcq.relationship_category_id and rcq.question_id=' . $this->db->escape($data["question_id"]), 'left');
            //$this->db->where('rcq.question_id',$data['question_id']);
        }
        else {
            $this->db->join('relationship_category_question rcq', 'rc.id_relationship_category=rcq.relationship_category_id', 'left');
            $this->db->where('id_relationship_category_question is null');
        }
        if(isset($data['status']))
            $this->db->where('relationship_category_status',$data['status']);
        if(isset($data['customer_id']))
            $this->db->where('rc.customer_id',$data['customer_id']);
        $this->db->where('rc.can_review',1);
        $this->db->order_by('rc.id_relationship_category','desc');
        $this->db->group_by('rc.id_relationship_category');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        $result=$query->result_array();
        foreach($result as $kr=>$vr){
            $matches=array();
            if(strlen($vr['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($vr['relationship_category_name'])), $matches);
                $result[$kr]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result[$kr]['relationship_category_short_name'] = $vr['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($vr['relationship_category_name'])), $matches);
            // $result[$kr]['relationship_category_short_name'] = implode('',$matches[0]);
        }
        return $result;
    }
    public function getQuestionMasterOptions($data=array())
    {
        $this->db->select('*');
        $this->db->from('question_type_option qto');
        if(isset($data['question_type']))
            $this->db->where('qto.question_type',$data['question_type']);
        $this->db->where('status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getQuestion($data)
    {
        $this->db->select('q.*');
        $this->db->from('question q');
        if(isset($data['id_question']))
            $this->db->where('q.id_question',$data['id_question']);
        if(isset($data['question_id']))
            $this->db->where('q.id_question',$data['question_id']);
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;
    }
    public function getQuestionOption($data)
    {
        $this->db->select('q.*');
        $this->db->from('question_option q');
        if(isset($data['id_question_option']))
            $this->db->where('q.id_question_option',$data['id_question_option']);
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;
    }
    public function getQuestionModuleTemplate($data)
    {
        $this->db->select('m.id_module,ml.module_name, if(m.to_avail_template,(select tp.template_name from template tp where tp.id_template = m.to_avail_template),"All") as template_name');
        $this->db->from('module m');
        $this->db->join('topic t','t.module_id = m.id_module','');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        if(isset($data['id_topic']))
                $this->db->where('t.id_topic',$data['id_topic']);
        $query = $this->db->get();
        //echo ''.$this->db->last_query(); exit;
        return $query->result_array();
        
    }

}