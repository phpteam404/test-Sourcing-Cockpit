<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Document_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function addBulkDocuments($data)
    {
        $this->db->insert_batch('document',$data);
        return 1;
    }

    public function getDocList($data){
         //print_r($data);exit;
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('m.id_module,m.contract_review_id,u.first_name,concat(u.first_name," ",u.last_name) updated_name,concat(u1.first_name," ",u1.last_name) updated_by_name,ml.module_name,ql.question_text,ql.question_id,tl.topic_name,d.*,uploaded_on,concat(u.first_name," ",u.last_name) as uploaded_user_name,c.contract_owner_id,c.delegate_id');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module = ml.module_id','');
        $this->db->join('contract_review crv','crv.id_contract_review = m.contract_review_id','LEFT');
        $this->db->join('contract c','c.id_contract = crv.contract_id and c.is_deleted=0','LEFT');
        if(isset($data['module_ids']) && count($data['module_ids']) > 0){
            $this->db->where_in('m.id_module',$data['module_ids']);
        }
        if(isset($data['reference_type']) &&  $data['reference_type']== 'question' ){
            $this->db->join('topic t','t.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            if(isset($data['page_type']) && $data['page_type']='contract_review'){
                $this->db->where('d.reference_id IN (select q_sub.id_question from question q_sub LEFT JOIN question q2_sub on q2_sub.parent_question_id=q_sub.parent_question_id LEFT JOIN topic t2_sub on t2_sub.id_topic=q2_sub.topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id LEFT JOIN contract c2_sub on c2_sub.id_contract=cr2_sub.contract_id and c2_sub.is_deleted=0 LEFT JOIN topic t1_sub on t1_sub.id_topic=q_sub.topic_id LEFT JOIN module m1_sub on m1_sub.id_module=t1_sub.module_id LEFT JOIN contract_review cr1_sub on cr1_sub.id_contract_review=m1_sub.contract_review_id where q2_sub.id_question='.$this->db->escape($data['reference_id']).' and cr1_sub.contract_id=cr2_sub.contract_id)',false,false);
            }
            else {
                $this->db->where('d.reference_id', $data['reference_id']);
            }
            if($data["is_workflow"] == 1)
                // $this->db->where("m.contract_review_id",$data["module_id"]);
                $this->db->where("d.contract_workflow_id",$data['contract_workflow_id']); 
            else
                $this->db->where("crv.is_workflow","0");
        }
        else  if(isset($data['reference_type']) && $data['reference_type'] == 'topic') {
            $this->db->join('topic t', 't.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q', 'q.topic_id = t.id_topic', '');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d', 'd.reference_id = q.id_question and d.reference_type = "question"', '');
            $this->db->join('user u', 'u.id_user = d.uploaded_by', '');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['page_type']) && $data['page_type']='contract_review'){
                $this->db->where('t.id_topic IN (select t_sub.id_topic from contract_review cr_sub  JOIN module m_sub on m_sub.contract_review_id=cr_sub.id_contract_review JOIN topic t_sub on t_sub.module_id=m_sub.id_module JOIN topic t2_sub on t2_sub.parent_topic_id=t_sub.parent_topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id where cr_sub.contract_id=cr2_sub.contract_id and t2_sub.id_topic='.$this->db->escape($data['reference_id']).')');
            }else {
                $this->db->where('t.id_topic', $data['reference_id']);
            }
            if($data["is_workflow"] == 1)
                // $this->db->where("m.contract_review_id",$data["module_id"]);
                $this->db->where("d.contract_workflow_id",$data['contract_workflow_id']); 
            else
                $this->db->where("crv.is_workflow","0");
        }
        else if(isset($data['reference_type']) && $data['reference_type'] == 'module' || isset($data['module_id'])){
            $this->db->join('topic t','m.id_module = t.module_id','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id and cu.status=1','');
                $this->db->where('cu.user_id',$data['contract_user']);
            }
            if(isset($data['page_type']) && $data['page_type']='contract_overview' && isset($data['contract_id'])){
                $this->db->where('c.id_contract', $data['contract_id']);
                
                if($data["is_workflow"] == 1)
                    // $this->db->where("m.contract_review_id",$data["module_id"]);
                    $this->db->where("d.contract_workflow_id",$data['contract_workflow_id']); 
                else
                    $this->db->where("crv.is_workflow","0"); 

            }
            else {
                if (isset($data['module_id']))
                    $this->db->where('m.contract_review_id', $data['module_id']);
                else
                    $this->db->where('d.module_id', $data['reference_type']);
            }
        }

        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
        if(isset($data['updated_by']))
            $this->db->where('d.updated_by>0');
        if(isset($data['document_type']))
            $this->db->where('d.document_type',$data['document_type']);
        $this->db->group_by('d.id_document');
        /* results count start */
        $all_records = clone $this->db;
        $all_records_count = $all_records->get()->num_rows();
        /* results count end */
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('d.document_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('d.uploaded_on', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(d.document_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%"
            or d.uploaded_on like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('d.id_document','DESC');
        $this->db->group_by('d.id_document');
        $query = $this->db->get();
    //    echo $this->db->last_query();exit;
        $result = $query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    $delete_access = "annus";
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = 'itako';
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = 'itako';
                    }
                }
                if($data['id_user']==$v['contract_owner_id'] || $data['id_user']==$v['delegate_id']){
                    $delete_access = "itako";
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako";
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }
        return array('total_records' => $all_records_count,'data' => $result);

    }
    public function getDocLogList($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('m.id_module,m.contract_review_id,u.first_name,concat(u.first_name," ",u.last_name) updated_name,concat(u1.first_name," ",u1.last_name) updated_by_name,ml.module_name,ql.question_text,ql.question_id,tl.topic_name,d.*,uploaded_on,concat(u.first_name," ",u.last_name) as uploaded_user_name,c.contract_owner_id,c.delegate_id');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module = ml.module_id','');
        $this->db->join('contract_review crv','crv.id_contract_review = m.contract_review_id','LEFT');
        $this->db->join('contract c','c.id_contract = crv.contract_id and c.is_deleted=0','LEFT');
        if(isset($data['module_ids']) && count($data['module_ids']) > 0){
            $this->db->where_in('m.id_module',$data['module_ids']);
        }
        if(isset($data['reference_type']) &&  $data['reference_type']== 'question' ){

            $this->db->join('topic t','t.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            if(isset($data['page_type']) && $data['page_type']='contract_review'){
                $this->db->where('d.reference_id IN (select q_sub.id_question from question q_sub LEFT JOIN question q2_sub on q2_sub.parent_question_id=q_sub.parent_question_id LEFT JOIN topic t2_sub on t2_sub.id_topic=q2_sub.topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id
                LEFT JOIN contract c2_sub on c2_sub.id_contract=cr2_sub.contract_id and c2_sub.is_deleted=0 LEFT JOIN topic t1_sub on t1_sub.id_topic=q_sub.topic_id LEFT JOIN module m1_sub on m1_sub.id_module=t1_sub.module_id LEFT JOIN contract_review cr1_sub on cr1_sub.id_contract_review=m1_sub.contract_review_id where q2_sub.id_question='.$this->db->escape($data['reference_id']).' and cr1_sub.contract_id=cr2_sub.contract_id)',false,false);
            }
            else {
                $this->db->where('d.reference_id', $data['reference_id']);
            }
        }
        else  if(isset($data['reference_type']) && $data['reference_type'] == 'topic') {
            $this->db->join('topic t', 't.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q', 'q.topic_id = t.id_topic', '');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d', 'd.reference_id = q.id_question and d.reference_type = "question"', '');
            $this->db->join('user u', 'u.id_user = d.uploaded_by', '');
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            if(isset($data['page_type']) && $data['page_type']='contract_review'){
                $this->db->where('t.id_topic IN (select t_sub.id_topic from contract_review cr_sub  JOIN module m_sub on m_sub.contract_review_id=cr_sub.id_contract_review JOIN topic t_sub on t_sub.module_id=m_sub.id_module JOIN topic t2_sub on t2_sub.parent_topic_id=t_sub.parent_topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id where cr_sub.contract_id=cr2_sub.contract_id and t2_sub.id_topic='.$this->db->escape($data['reference_id']).')');
            }else {
                $this->db->where('t.id_topic', $data['reference_id']);
            }
        }
        else if(isset($data['reference_type']) && $data['reference_type'] == 'module' || isset($data['module_id'])){
            $this->db->join('topic t','m.id_module = t.module_id','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
            if(isset($data['external_user'])){
                $this->db->where('q.provider_visibility = 1');
            }
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id and cu.status=1','');
                $this->db->where('cu.user_id',$data['contract_user']);
            }
            if(isset($data['page_type']) && $data['page_type']='contract_overview' && isset($data['contract_id'])){
                $this->db->where('c.id_contract', $data['contract_id']);
                if($data["is_workflow"] == 1)
                    $this->db->where("d.contract_workflow_id",$data['contract_workflow_id']); 
                else
                    $this->db->where("crv.is_workflow","0"); 
            }
            else {
                if (isset($data['module_id']))
                    $this->db->where('m.contract_review_id', $data['module_id']);
                else
                    $this->db->where('d.module_id', $data['reference_type']);
            }
        }
        if(isset($data['is_workflow']))
            $this->db->where('m.is_workflow',$data['is_workflow']);
        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
        if(isset($data['updated_by']))
            $this->db->where('d.updated_by>0');
        if(isset($data['document_type']))
            $this->db->where('d.document_type',$data['document_type']);
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('d.document_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('d.uploaded_on', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(d.document_name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%"
            or d.uploaded_on like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        $this->db->order_by('d.updated_on DESC');
        $this->db->group_by('d.id_document');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    $delete_access = "annus";
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = 'itako';
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = 'itako';
                    }
                }
                if($data['id_user']==$v['contract_owner_id'] || $data['id_user']==$v['delegate_id']){
                    $delete_access = "itako";
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako";
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }
        return array('total_records' => $all_clients_count,'data' => $result);

    }

    /*public function getAllDoccumentList($data)
    {        ///// Brings documents from all reviews and sends to contract review page.
        $this->db->select('m.id_module,ml.module_name,d.*,date_format(d.uploaded_on,\'%Y-%m-%d\') as uploaded_on,concat(u.first_name," ",u.last_name) as uploaded_user_name,u.user_role_id,c.contract_owner_id,c.delegate_id');
        $this->db->from('document d');
        $this->db->join('module m','m.id_module = d.module_id','');
        $this->db->join('module_language ml','m.id_module = ml.module_id','');
        $this->db->join('contract_review crv','crv.id_contract_review = m.contract_review_id','LEFT');
        $this->db->join('contract c','c.id_contract = crv.contract_id','LEFT');
        $this->db->join('user u','u.id_user=d.uploaded_by','LEFT');
        $this->db->where('d.module_id IN (select id_contract_review from contract_review where contract_id='.$data['id_contract'].')');
        $query = $this->db->get();
        $result = $query->result_array();

        foreach ($result as $k => $v) {
            $view_access = 'annus;
            $edit_access = 'annus;
            $delete_access = 'annus;
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    $delete_access = "itako;
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = "itako;
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = "itako;
                    }
                }
                if($data['id_user']==$v['contract_owner_id'] || $data['id_user']==$v['delegate_id']){
                    $delete_access = "itako;
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako;
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }

        return $result;


    }*/

    public function getDocumentsList($data)
    {
        $this->db->select('d.*,concat(u.first_name," ",u.last_name) updated_name,concat(u1.first_name," ",u1.last_name) updated_by_name,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as uploaded_user,DATE_FORMAT(d.uploaded_on,"%Y-%m-%d") as updated_date');
        $this->db->from('document d');
        $this->db->join('user u','u.id_user=d.uploaded_by','LEFT');
        $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
        if(isset($data['module_id']))
            $this->db->where('d.module_id',$data['module_id']);
        if(isset($data['module_type']))
            $this->db->where('d.module_type',$data['module_type']);
        if(isset($data['reference_id']))
            $this->db->where('d.reference_id',$data['reference_id']);
        if(isset($data['reference_type']))
            $this->db->where('d.reference_type',$data['reference_type']);
        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
        if(isset($data['id_document']))
            $this->db->where('d.id_document',$data['id_document']);
        if(isset($data['document_type']))
            if(is_array($data['document_type']))
            {
                $this->db->where_in('d.document_type',$data['document_type']);
            }    
            else
            {
                $this->db->where('d.document_type',$data['document_type']);
            }
                
        else
            $this->db->where('d.document_type',0);
        // if(isset($data['updated_by']))
        //     $this->db->where('d.updated_by>0');
        $query = $this->db->get();
        $result=$query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako";
            }
            if($v['reference_type']=='contract'){
                if(isset($data['id_user']) && isset($data['user_role_id'])) {
                    if($data['user_role_id']==2){

                    }
                    else {
                        if ((isset($data['contract_owner_id']) && $data['id_user'] == $data['contract_owner_id']) || (isset($data['delegate_id']) && $data['id_user'] == $data['delegate_id'])) {
                            $delete_access = "itako";
                        }
                    }
                }
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }

        return $result;
    }

    public function getContractDocumentsList($data)
    {
        $this->db->select('d.*,u.first_name,concat(u.first_name," ",u.last_name) updated_name,concat(u1.first_name," ",u1.last_name) updated_by_name,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as uploaded_user,DATE_FORMAT(d.uploaded_on,"%Y-%m-%d") as updated_date');
        $this->db->from('document d');
        $this->db->join('user u','u.id_user=d.uploaded_by','LEFT');
        $this->db->join('user u1','u1.id_user=d.updated_by','LEFT');
        if(isset($data['module_id']))
            $this->db->where('d.module_id',$data['module_id']);
        if(isset($data['module_type']))
            $this->db->where('d.module_type',$data['module_type']);
        if(isset($data['reference_id']))
            $this->db->where('d.reference_id',$data['reference_id']);
        if(isset($data['reference_type']))
            $this->db->where('d.reference_type',$data['reference_type']);
        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
        if(isset($data['id_document']))
            $this->db->where('d.id_document',$data['id_document']);
        if(isset($data['updated_by']))
            $this->db->where('d.updated_by>0');
        if(isset($data['document_type']))
            $this->db->where('d.document_type',$data['document_type']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('d.document_name', $data['search'], 'both');
            $this->db->group_end();
        }
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        $this->db->order_by('d.id_document','DESC');
        $query = $this->db->get();
        $result=$query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako";
            }
            if($v['reference_type']=='contract'){
                if(isset($data['id_user']) && isset($data['user_role_id'])) {
                    if($data['user_role_id']==2){

                    }
                    else {
                        if ((isset($data['contract_owner_id']) && $data['id_user'] == $data['contract_owner_id']) || (isset($data['delegate_id']) && $data['id_user'] == $data['delegate_id'])) {
                            $delete_access = "itako";
                        }
                    }
                }
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }

        return array('total_records' => $all_clients_count,'data' => $result);
    }

    public function getDocumentsListPagination($data)
    {
        $this->db->select('d.*,CONCAT_WS(\' \',u.first_name,u.last_name) as uploaded_user_name,u.user_role_id');
        $this->db->from('document d');
        $this->db->join('user u','u.id_user=d.uploaded_by','LEFT');
        if(isset($data['module_id']))
            $this->db->where('d.module_id',$data['module_id']);
        if(isset($data['module_type']))
            $this->db->where('d.module_type',$data['module_type']);
        if(isset($data['reference_id']))
            $this->db->where('d.reference_id',$data['reference_id']);
        if(isset($data['reference_type']))
            $this->db->where('d.reference_type',$data['reference_type']);
        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
        if(isset($data['id_document']))
            $this->db->where('d.id_document',$data['id_document']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('d.document_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(d.document_name like "%'.$data['search'].'%")');*/

        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('d.id_document','DESC');
        $query = $this->db->get();
        $result=$query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = "itako";
            }
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
        }
        return array('total_records' => $all_clients_count,'data' => $result);
    }
    public function addDocument($data)
    {
        $this->db->insert_batch('document', $data);
        return 1;
    }
    public function updateDocument($data)
    {
        $this->db->where('id_document', $data['id_document']);
        $this->db->update('document', $data);
        return 1;
    }

    public function deleteDocument($user,$data,$source)
    {
        $this->db->where('id_document',$data['id_document']);
        /*$this->db->delete('document');*/
        $this->db->update('document', array('document_status'=>0,'updated_by'=>$user,'updated_on'=>currentDate(),'document_source'=>str_replace('/', '/deleted/', $source)));
        return 1;
    }
    public function getDocument($data)
    {
        $this->db->select('*');
        $this->db->from('document');
        $this->db->where('id_document',$data['id_document']);
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;
    }

    public function getArchiveDocuments($data){
        $this->db->select('ml.module_name,tl.topic_name,ql.question_text,d.id_document,concat(u.first_name," ",u.last_name) created_by,d.uploaded_on as created_on,d.document_name,d.document_type,d.document_source');
        $this->db->from('module m');
        $this->db->join('module_language ml', 'm.id_module = ml.module_id','left');
        $this->db->join('topic t', 't.module_id = ml.module_id','left');
        $this->db->join('topic_language tl', 'tl.topic_id = t.id_topic','left');
        $this->db->join('question q', 'q.topic_id = t.id_topic','left');
        $this->db->join('question_language ql', 'ql.question_id = q.id_question','left');
        $this->db->join('document d', 'd.reference_id = q.id_question ','left');
        $this->db->join('user u', 'd.uploaded_by = u.id_user','left');
        //WHERE m.contract_review_id = 802 AND d.reference_type = 'question' AND m.id_module IN (3057)
        $this->db->where('m.contract_review_id',$data['id_contract_review']);
        $this->db->where('d.reference_type','question');
        $this->db->where('d.document_status','1');
        if(isset($data['module_ids']))
            $this->db->where_in('m.id_module',$data['module_ids']);
        $this->db->group_by('d.id_document');
        $this->db->order_by('d.id_document','desc');
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;  
    }
    public function intelligenceTemplateList($data=null){
        $this->db->select('it.id_intelligence_template,it.template_name,it.available_for_all_customers,it.`status`,GROUP_CONCAT(c.company_name) as customers,(SELECT COUNT(*) FROM intelligence_template_fields WHERE intelligence_template_id=it.id_intelligence_template AND is_deleted=0) as no_of_fields');
        $this->db->from('intelligence_template it');
        $this->db->join('inteligence_template_customers itc','it.id_intelligence_template=itc.template_id','left');
        $this->db->join('customer c','itc.customer_id=c.id_customer','left');
        if(!empty($data['id_intelligence_template'])){
            $this->db->where('it.id_intelligence_template',$data['id_intelligence_template']);
        }
        if(isset($data['status'])){
            $this->db->where('it.status',$data['status']);
        }
        else{
            $this->db->where('it.status',1);
        }
        $this->db->group_by('it.id_intelligence_template');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('it.template_name', $data['search'], 'both');
            $this->db->or_like('c.company_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /* results count start */
        $all_records = clone $this->db;
        // $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $all_records_count = $all_records->get()->num_rows();
        /* results count end */
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('it.id_intelligence_template','asc');
        // $this->db->group_by('it.id_intelligence_template');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        return array('total_records' => $all_records_count,'data' => $query->result_array());

    }
    public function  get_customer_ids_names($data=null){
        $this->db->select('c.company_name,c.id_customer');
        $this->db->from('inteligence_template_customers itc');
        $this->db->join('customer c','itc.customer_id=c.id_customer','left');
        $this->db->where('itc.template_id',$data['template_id']);
        $query = $this->db->get();
        return $query->result_array();
        
    }
    public function getQuestionList($data=null){
        $this->db->select('*');
        $this->db->from('intelligence_template_fields itf');
        if(!empty($data['id_intelligence_template_fields'])){
            $this->db->where('itf.id_intelligence_template_fields',$data['id_intelligence_template_fields']);
        }
        if(!empty($data['id_intelligence_template'])){
            $this->db->where('itf.intelligence_template_id',$data['id_intelligence_template']);
        }
        $this->db->where('is_deleted',0);
        if(!empty($data['filter_field_type'])){
            $this->db->where('itf.field_type',$data['filter_field_type']);
        }
        // $this->db->group_by('it.id_intelligence_template');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('itf.field_name', $data['search'], 'both');
            $this->db->or_like('itf.field_type', $data['search'], 'both');
            $this->db->or_like('itf.question', $data['search'], 'both');
            $this->db->group_end();
        }
        /* results count start */
        $all_records = clone $this->db;
        // $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $all_records_count = $all_records->get()->num_rows();
        /* results count end */
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('itf.id_intelligence_template_fields','asc');
        // $this->db->group_by('it.id_intelligence_template');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        return array('total_records' => $all_records_count,'data' => $query->result_array());
    }
    public function get_cust_templates($data=null){
        $this->db->select('it.template_name,it.id_intelligence_template,it.available_for_all_customers');
        $this->db->from('`intelligence_template` it');
        $this->db->join('inteligence_template_customers itc','it.id_intelligence_template=itc.template_id','left');
        $this->db->where('it.`status`',1);
        $this->db->group_start();
        $this->db->where('itc.customer_id',$data['customer_id']);
        $this->db->or_where('it.available_for_all_customers',1);
        $this->db->group_end();
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();  
    }
    public function getDocumentIntList($data=null){
        $this->db->select('di.*,CONCAT(u.first_name," ",u.last_name) owner_name,CONCAT(u1.first_name," ",u1.last_name) as delegate_name,it.template_name,CONCAT(u2.first_name," ",u2.last_name) as uploaded_by');
        $this->db->from('`document_intelligence` di');
        $this->db->join('`user` u','di.owner_id=u.id_user','left');
        $this->db->join('`user` u1','di.delegate_id=u1.id_user','left');
        $this->db->join('`user` u2','di.created_by=u2.id_user','left');
        $this->db->join('intelligence_template it','di.intelligence_template_id=it.id_intelligence_template','left');
        // if($this->session_user_info->user_role_id == 2)
        // {
        //     $this->db->where('di.customer_id',$data['customer_id']);
        // }
        // else 
        if($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 8)
        {
            $this->db->where('di.owner_id',$this->session_user_id);
        }
        else if($this->session_user_info->user_role_id == 4)
        {
            $this->db->where('di.delegate_id',$this->session_user_id);
        }
        if(!empty($data['customer_id'])){
            $this->db->where('di.customer_id',$data['customer_id']);
        }
        $this->db->where('di.is_deleted',0);
        if(!empty($data['ocr_status'])){
            if($data['ocr_status']=='not_started'){
                $this->db->where('di.ocr_status',null);
            }
            else{
                $this->db->where('di.ocr_status',$data['ocr_status']);
            }
        }
        if(!empty($data['id_document_intelligence'])){
            $this->db->where('di.id_document_intelligence',$data['id_document_intelligence']);
        }
        if(!empty($data['analysis_status'])){
            if($data['analysis_status']=='not_started'){
                $this->db->where('di.analysis_status',null);
            }
            elseif($data['analysis_status']=='P')
            {
                $this->db->group_start();
                $this->db->where('di.analysis_status','P1');
                $this->db->or_where('di.analysis_status','P2');
                $this->db->or_where('di.analysis_status','P3');
                $this->db->or_where('di.analysis_status','P4');
                $this->db->group_end();
            }
            else{
                $this->db->where('di.analysis_status',$data['analysis_status']);
            }
        }
        if(!empty($data['validate_status'])){
            if($data['validate_status']=='not_started'){
                $this->db->where('di.validate_status',null);
            }
            else{
                $this->db->where('di.validate_status',$data['validate_status']);
            }
        }
        // $this->db->group_by('it.id_intelligence_template');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('di.original_document_name', $data['search'], 'both');
            $this->db->or_like('di.original_document_path', $data['search'], 'both');
            $this->db->or_like('di.ocr_document_name', $data['search'], 'both');
            $this->db->or_like('di.ocr_document_path', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('u1.first_name', $data['search'], 'both');
            $this->db->or_like('u1.last_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /* results count start */
        $all_records = clone $this->db;
        // $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $all_records_count = $all_records->get()->num_rows();
        /* results count end */
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('di.id_document_intelligence','desc');
        // $this->db->group_by('it.id_intelligence_template');
        $query = $this->db->get();
         //echo $this->db->last_query();exit;
        return array('total_records' => $all_records_count,'data' => $query->result_array());
    }
    public function getDoumentQuestionsAnswers($data=null)
    {
        $this->db->select('df.*,di.contract_id');
        $this->db->from('document_fields df');
        if(!empty($data['document_intelligence_id'])){
            $this->db->where('df.document_intelligence_id',$data['document_intelligence_id']);
        }
        $this->db->join('document_intelligence di','di.id_document_intelligence=df.document_intelligence_id','left');
        if(isset($data['is_moved'])){
            $this->db->where('df.is_moved',$data['is_moved']);
        }
        if(!empty($data['field_type'][0])){
            $this->db->where_in('df.field_type',$data['field_type']);
        }
        if(!empty($data['field_status'][0])){
            $this->db->group_start();
            foreach($data['field_status'] as $status)
            {
                $this->db->or_like('df.field_status', $status, 'both');
            }
            $this->db->group_end();
        }
        if(!empty($data['field_status_not_in'][0])){
            $this->db->group_start();
            foreach($data['field_status_not_in'] as $status)
            {
                $this->db->or_not_like('df.field_status', $status, 'both');
            }
            $this->db->group_end();
        }
     
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('df.field_name', $data['search'], 'both');
            $this->db->or_like('df.field_type', $data['search'], 'both');
            $this->db->group_end();
        }
        
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();  
        
    }
}