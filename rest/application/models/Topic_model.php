<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Topic_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function TopicList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('t.*,l.*,count(DISTINCT q.id_question) as question_count');
        $this->db->from('topic t');
        $this->db->join('topic_language l','t.id_topic=l.topic_id','left');
        $this->db->join('question q','t.id_topic=q.topic_id','left');
        // $this->db->join('module m','m.id_module=t.module_id','left');
        // $this->db->where('m.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0);
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['module_id']))
            $this->db->where('t.module_id',$data['module_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.topic_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('t.topic_status',$data['status']);
        }
        /*if(isset($data['search']))
            $this->db->where('(l.topic_name like "%'.$data['search'].'%")');*/
        $this->db->group_by('t.id_topic');
        /* results count start */
        $query = $this->db->get();
        $all_clients_count = count($query->result_array());
        /* results count end */

        $this->db->select('t.*,l.*,count(DISTINCT q.id_question) as question_count');
        $this->db->from('topic t');
        $this->db->join('topic_language l','t.id_topic=l.topic_id','left');
        $this->db->join('question q','t.id_topic=q.topic_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['module_id']))
            $this->db->where('t.module_id',$data['module_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.topic_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('t.topic_status',$data['status']);
        }
        /*if(isset($data['search']))
            $this->db->where('(l.topic_name like "%'.$data['search'].'%")');*/
        $this->db->group_by('t.id_topic');
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.id_topic','ASC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function TopicListCount($data)
    {
        $this->db->select('*');
        $this->db->from('topic t');
        $num_results = $this->db->count_all_results();
        return $num_results;
    }

    public function getTopics($data)
    {
        $this->db->select('t.id_topic,l.topic_name');
        $this->db->from('topic t');
        $this->db->join('topic_language l','t.id_topic=l.topic_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['offset']) && $data['offset']!='' && isset($data['limit']) && $data['limit']!='')
            $this->db->limit($data['limit'],$data['offset']);
        $this->db->order_by('t.id_topic','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addTopic($data)
    {
        $this->db->insert('topic', $data);
        return $this->db->insert_id();
    }

    public function addTopicLanguage($data)
    {
        $this->db->insert('topic_language', $data);
        return $this->db->insert_id();
    }

    public function updateTopic($data)
    {
        $this->db->where('id_topic', $data['id_topic']);
        $this->db->update('topic', $data);
        return 1;
    }

    public function updateTopicLanguage($data)
    {
        $this->db->where('id_topic_language', $data['id_topic_language']);
        $this->db->update('topic_language', $data);
        return 1;
    }

    public function getTopicName($data){
        $this->db->select('topic_name');
        $this->db->from('topic_language tl');
        $this->db->where('tl.topic_id',$data['topic_id']);
        $result = $this->db->get();
        return $result->result_array();

    }
    
    public function getRelationquestions($data)
    {
        $this->db->select('*');
        $this->db->from('question q');
        $this->db->join('topic t','t.id_topic=q.topic_id','left');
        $this->db->join('module m','m.id_module=t.module_id','left');
        if(!(isset($data['is_workflow']) && ($data['is_workflow']==1)))
        {
            $reviewCondition = 'and rq.status =1';
        }
        else
        {
            $reviewCondition = '';
        }
        $this->db->join('relationship_category_question rq','rq.question_id = q.id_question '.$reviewCondition.' and rq.provider_visibility =1','inner');
        if(isset($data['module_id']))
        {
            $this->db->where('t.module_id',$data['module_id']);
        }
        if(isset($data['topic_id']))
        {
            $this->db->where('q.topic_id',$data['topic_id']);
        }
        $this->db->group_by('q.id_question');
        $result = $this->db->get();
        return $result->result_array();
    }
}