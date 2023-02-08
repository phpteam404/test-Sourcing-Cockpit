<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function getDocList($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('m.id_module,ml.module_name,ql.question_text,tl.topic_name,d.*,date_format(d.uploaded_on,\'%Y-%m-%d\') as uploaded_on,concat(u.first_name," ",u.last_name) as uploaded_user_name,u.user_role_id,c.contract_owner_id,c.delegate_id');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module = ml.module_id','');
        $this->db->join('contract_review crv','crv.id_contract_review = m.contract_review_id','LEFT');
        $this->db->join('contract c','c.id_contract = crv.contract_id and c.is_deleted=0','LEFT');

        if(isset($data['reference_type']) &&  $data['reference_type']== 'question' ){

            $this->db->join('topic t','t.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            $this->db->where('d.reference_id',$data['reference_id']);
        }
        else  if(isset($data['reference_type']) && $data['reference_type'] == 'topic') {
            $this->db->join('topic t', 't.module_id = m.id_module','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q', 'q.topic_id = t.id_topic', '');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d', 'd.reference_id = q.id_question and d.reference_type = "question"', '');
            $this->db->join('user u', 'u.id_user = d.uploaded_by', '');
            $this->db->where('t.id_topic',$data['reference_id']);
        }
        else if(isset($data['reference_type']) && $data['reference_type'] == 'module' || isset($data['module_id'])){
            $this->db->join('topic t','m.id_module = t.module_id','');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic','');
            $this->db->join('question q','q.topic_id = t.id_topic','');
            $this->db->join('question_language ql','q.id_question = ql.question_id','');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','');
            $this->db->join('user u','u.id_user = d.uploaded_by','');
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id and cu.status=1','');
                $this->db->where('cu.user_id',$data['contract_user']);
            }
            if(isset($data['module_id']))
                $this->db->where('m.contract_review_id',$data['module_id']);
            else
                $this->db->where('d.module_id',$data['reference_type']);
        }

        if(isset($data['document_status']))
            $this->db->where('d.document_status',$data['document_status']);
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
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('d.id_document','DESC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $result = $query->result_array();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    $delete_access = "itako";
                    if ($v['uploaded_by'] == $data['id_user']) {
                        $view_access = $edit_access = $delete_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    if ($v['uploaded_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = $edit_access = $delete_access = "itako";
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
    public function getReportList($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('r.*,GROUP_CONCAT(distinct (SELECT IF(ctry.country_name!="",CONCAT(bu1.bu_name," - ",ctry.country_name),bu1.bu_name) as bu_name 
        FROM `business_unit` `bu1`
        LEFT JOIN country ctry  ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=`bu`.`id_business_unit`)) as business_units,GROUP_CONCAT(distinct rcl.relationship_category_name) as classifications,COUNT(distinct repc.id_report_contract) as no_of_contracts,concat(u.first_name," ",u.last_name) as report_user_name');
        $this->db->from('report r');
        $this->db->join('business_unit bu','FIND_IN_SET(bu.id_business_unit, r.business_unit_ids) > 0','LEFT');
        $this->db->join('relationship_category rc','FIND_IN_SET(rc.id_relationship_category, r.classification_ids) > 0','LEFT');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=rc.id_relationship_category and rcl.language_id=1','LEFT');
        $this->db->join('report_contract repc','repc.report_id=r.id_report','LEFT');
        $this->db->join('user u','u.id_user=r.created_by','LEFT');
        $this->db->where('r.report_status',1);
        if(isset($data['id_report'])){
            $this->db->where('r.id_report',$data['id_report']);
        }
        if(isset($data['user_role_id']) && $data['user_role_id']==2){
            /*$this->db->where('r.created_by',$data['id_user']);*/
        }
        else{
            if(isset($data['id_user']))
                $this->db->where('r.created_by',$data['id_user']);
        }
        if(isset($data['customer_id'])){
            $this->db->where('u.customer_id',$data['customer_id']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('r.name', $data['search'], 'both');
            // $this->db->or_like('u.first_name', $data['search'], 'both');
            // $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('CONCAT(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->or_like('r.report_status', $data['search'], 'both');
            $this->db->or_like('r.created_on', $data['search'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('r.id_report');
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->get();
        $all_clients_count = $all_clients_count->num_rows();
        /* results count end */

        /*if(isset($data['search']))
            $this->db->where('(r.name like "%'.$data['search'].'%"
            or u.first_name like "%'.$data['search'].'%"
            or u.last_name like "%'.$data['search'].'%"
            or r.report_status like "%'.$data['search'].'%"
            or r.created_on like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('r.id_report','DESC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $result = $query->result_array();
        foreach($result as $k=>$v){
            $result[$k]['vaav']=$result[$k]['exaaxe']=$result[$k]['eaae']=$result[$k]['daad']='annus';
            $result[$k]['vaav']=$result[$k]['exaaxe']="itako";
            $result[$k]['eaae']=$result[$k]['daad']="itako";
            if(isset($data['id_user'])){
                if($v['created_by']==$data['id_user']){
                    $result[$k]['eaae']=$result[$k]['daad']="itako";
                }
            }
        }
        return array('total_records' => $all_clients_count,'data' => $result);
    }
    public function getReport($data){
        $global_modules=array();
        $this->db->select('r.*,GROUP_CONCAT(distinct bu.bu_name) as business_units,GROUP_CONCAT(distinct rcl.relationship_category_name) as classifications,COUNT(distinct repc.id_report_contract) as no_of_contracts,concat(u.first_name," ",u.last_name) as report_user_name');
        $this->db->from('report r');
        $this->db->join('business_unit bu','FIND_IN_SET(bu.id_business_unit, r.business_unit_ids) > 0','LEFT');
        $this->db->join('relationship_category rc','FIND_IN_SET(rc.id_relationship_category, r.classification_ids) > 0','LEFT');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=rc.id_relationship_category and rcl.language_id=1','LEFT');
        $this->db->join('report_contract repc','repc.report_id=r.id_report','LEFT');
        $this->db->join('user u','u.id_user=r.created_by','LEFT');
        $this->db->where('r.report_status',1);
        if(isset($data['id_report'])){
            $this->db->where('r.id_report',$data['id_report']);
            $this->db->group_by('r.id_report');
        }
        $query = $this->db->get();
        $result = $query->result_array();
        foreach($result as $k=>$v){
            $this->db->select('rc.*,cr.is_workflow,IF((rc.static_business_unit IS NOT NULL AND rc.static_business_unit!=""),rc.static_business_unit,(SELECT IF(ctry.country_name!="",CONCAT(bu1.bu_name," - ",ctry.country_name),bu1.bu_name) as bu_name 
            FROM `business_unit` `bu1`
            LEFT JOIN country ctry  ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=`bu`.`id_business_unit`)) as bu_name,IF((rc.static_contract_name IS NOT NULL AND rc.static_contract_name!=""),rc.static_contract_name,c.contract_name) as contract_name,IF((rc.static_provider_name IS NOT NULL AND rc.static_provider_name!=""),rc.static_provider_name,c.provider_name) as provider_name,IF((rc.static_relationship_category_name IS NOT NULL AND rc.static_relationship_category_name!=""),rc.static_relationship_category_name,rcl.relationship_category_name) as relationship_category_name,IF((rc.static_contract_status IS NOT NULL AND rc.static_contract_status!=""),rc.static_contract_status,c.contract_status) as contract_status,cr.contract_review_status,c.contract_unique_id');
            $this->db->from('report_contract rc');
            $this->db->join('business_unit bu','bu.id_business_unit=rc.business_unit_id','LEFT');
            $this->db->join('`country` `ctry`','`bu`.`country_id`=`ctry`.`id_country`','LEFT');
            $this->db->join('contract c','c.id_contract=rc.contract_id and c.is_deleted=0','LEFT');
            $this->db->join('contract_review cr','cr.id_contract_review=rc.contract_review_id','LEFT');
            $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=rc.relationship_category_id and rcl.language_id=1','LEFT');
            $this->db->where('rc.report_id',$v['id_report']);
            if(isset($data['id_report_contract']) && count(explode(',',$data['id_report_contract']))>0){
                $this->db->where_in('rc.id_report_contract',explode(',',$data['id_report_contract']));
            }
            $this->db->order_by('rc.order','ASC');
            $query1 = $this->db->get();//echo $this->db->last_query();exit;
            $result1 = $query1->result_array();
            $result[$k]['report_contracts']=$result1;
            foreach($result[$k]['report_contracts'] as $kr=>$vr){
                if(strlen($vr['relationship_category_name'])>2){
                    preg_match_all('/[A-Z]/', ucwords(strtolower($vr['relationship_category_name'])), $matches);
                    $result[$k]['report_contracts'][$kr]['relationship_category_short_name'] = implode('',$matches[0]);
                }else{
                    $result[$k]['report_contracts'][$kr]['relationship_category_short_name'] = $vr['relationship_category_name'];
                }
                // preg_match_all('/[A-Z]/', ucwords(strtolower($vr['relationship_category_name'])), $matches);
                // $result[$k]['report_contracts'][$kr]['relationship_category_short_name'] = implode('',$matches[0]);
                $this->db->select('rcm.*,ml.module_name,m.parent_module_id');
                $this->db->from('report_contract_module rcm');
                $this->db->join('module m','m.id_module=rcm.module_id','LEFT');
                $this->db->join('module_language ml','ml.module_id=rcm.module_id and ml.language_id=1','LEFT');
                $this->db->where('rcm.report_contract_id',$vr['id_report_contract']);
                $query2 = $this->db->get();
                $result2 = $query2->result_array();
                $result[$k]['report_contracts'][$kr]['modules']=$result2;
                $result[$k]['report_contracts'][$kr]['id_contract']=$vr['contract_id'];
                foreach($result[$k]['report_contracts'][$kr]['modules'] as $krm=>$vrm){
                    // $global_modules[$vrm['parent_module_id']]=array('parent_module_id'=>$vrm['parent_module_id'],'module_name'=>$vrm['module_name']);
                }

            }
        }
        $this->db->select('m.id_module as parent_module_id,ml.module_name');
        $this->db->from('module m');
        $this->db->join('module_language ml','ml.module_id=m.id_module and ml.language_id=1');
        $this->db->join('template_module t','t.module_id=m.id_module');
        // $this->db->join('customer c','c.template_id=t.template_id');
        // $this->db->where_in('m.id_module',array_keys($global_modules));
        $this->db->join('customer_template ct','ct.template_id=t.template_id');
        $this->db->where('ct.customer_id',$data['customer_id']);
        $this->db->where('m.is_workflow','0');
        $this->db->group_by('ml.module_name');
        $this->db->order_by('t.module_order','ASC');
        $query = $this->db->get();
        // $global_modules = $query->result_array();
        foreach($global_modules as $kg=>$vg){
            $matches='';
            preg_match_all('/[A-Z]/', ucwords(strtolower($vg['module_name'])), $matches);
            $global_modules[$kg]['module_short_name'] = implode('',$matches[0]);
        }

        return array('result'=>$result,'global_modules'=>array_values($global_modules));
    }
    public function search($data=array()){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $individual_contracts=0;
        if(isset($data['individual_contracts']) && $data['individual_contracts']==1){
            $individual_contracts=1;
        }
        $global_modules=array();
        $this->db->select('c.*,cr.is_workflow,p.provider_name,max(cr.id_contract_review) as id_contract_review,max(cr.id_contract_review) as contract_review_id,c.contract_name,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,rcl.relationship_category_name,cr.updated_on,cr.contract_review_status');
        $this->db->from('contract_review cr');
        $this->db->join('calender ca','ca.id_calender=cr.calender_id','left');
        $this->db->join('contract c','c.id_contract=cr.contract_id');
        $this->db->join('provider p','p.id_provider = c.provider_name');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=c.relationship_category_id');
        //$this->db->where('cr.updated_on BETWEEN "'. $data['latest_review_from_date']. '" and "'. $data['latest_review_to_date'].'"');
        //$this->db->where('cr.updated_on IS NOT NULL');
        $this->db->where('bu.customer_id',$data['customer_id']);
        // $this->db->where('cr.is_workflow',0);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        if(isset($data['review_statuses'])){
            //$data['review_statuses'] = '"' . implode('","', explode(',',$data['review_statuses'])) . '"';
        }
        if(isset($data['user_role_id']) && ($data['user_role_id']==3 || $data['user_role_id']==6)){
            if($individual_contracts==0 && isset($data['business_unit_ids']) && count(explode(',',$data['business_unit_ids']))>0)
                $this->db->where_in('c.business_unit_id', explode(',', $data['business_unit_ids']));
            if($individual_contracts==1 && isset($data['session_user_business_units']) && count(explode(',',$data['session_user_business_units']))>0)
                $this->db->where_in('c.business_unit_id', explode(',', $data['session_user_business_units']));
        }
        if($individual_contracts==0) {
            if(isset($data['business_unit_ids']) && count(explode(',',$data['business_unit_ids']))>0)
                $this->db->where_in('c.business_unit_id', explode(',', $data['business_unit_ids']));
            if(isset($data['classification_ids']) && count(explode(',',$data['classification_ids']))>0)
                $this->db->where_in('c.relationship_category_id', explode(',', $data['classification_ids']));
            if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0)
                $this->db->where_in('c.id_contract',explode(',',$data['contract_ids']));
            if(isset($data['provider_ids']) && count(explode(',',$data['provider_ids']))>0)
                $this->db->where_in('p.id_provider',explode(',',$data['provider_ids']));
            if(isset($data['calender_ids']) && count(explode(',',$data['calender_ids']))>0){
                $this->db->group_start();
                $this->db->where_in('ca.id_calender',explode(',',$data['calender_ids']));
                $this->db->or_where_in('ca.parent_calender_id',explode(',',$data['calender_ids']));
                $this->db->group_end();
            }
            if(isset($data['review_statuses']) && count(explode(',',$data['review_statuses']))>0){
                // $statuses = explode(',',$data['review_statuses']);asort($statuses);
                $where_statuse = $this->getStatusArray($data['review_statuses']);
                $this->db->where($where_statuse);
            }
            else
                $this->db->where_in('cr.contract_review_status',array('review in progress','finished','workflow in progress'));

        }
        if($individual_contracts==1) {
            /*$this->db->where_not_in('c.business_unit_id', explode(',', $data['business_unit_ids']));
            $this->db->where_not_in('c.relationship_category_id', explode(',', $data['classification_ids']));
            if(isset($data['contract_ids']) && count(explode(',',$data['contract_ids']))>0)
                $this->db->where_not_in('c.id_contract',explode(',',$data['contract_ids']));*/
            if(isset($data['id_report']) && $data['id_report']!=NULL)
                $this->db->where('c.id_contract not in (select contract_id from report_contract where report_id='.$this->db->escape($data['id_report']).')');
            /*if(isset($data['review_statuses']) && count(explode(',',$data['review_statuses']))>0)
                $this->db->where_in('c.contract_status',explode(',',$data['review_statuses']));*/
            $this->db->where_in('cr.contract_review_status',array('review in progress','review finalized'));
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.contract_name', $data['search'], 'both');
            $this->db->or_like('p.provider_name', $data['search'], 'both');
            $this->db->or_like('c.contract_status', $data['search'], 'both');
            $this->db->or_like('c.contract_last_reviewed_on', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.contract_name like "%'.$data['search'].'%"
            or c.provider_name like "%'.$data['search'].'%"
            or c.contract_status like "%'.$data['search'].'%"
            or c.contract_last_reviewed_on like "%'.$data['search'].'%")');*/
        $this->db->group_by('cr.id_contract_review');
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('c.business_unit_id asc, c.provider_name asc, c.contract_name');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result_array();
        foreach($result as $k=>$v){
            $matches='';
            if(strlen($v['relationship_category_name'])>2){
                preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
                $result[$k]['relationship_category_short_name'] = implode('',$matches[0]);
            }else{
                $result[$k]['relationship_category_short_name'] = $v['relationship_category_name'];
            }
            // preg_match_all('/[A-Z]/', ucwords(strtolower($v['relationship_category_name'])), $matches);
            // $result[$k]['relationship_category_short_name'] = implode('',$matches[0]);
            $module_score = $this->Contract_model->getContractReviewModuleScore(array('contract_review_id' => $v['id_contract_review']));


            $red_count=$amber_count=$green_count=0;
            for($sr=0;$sr<count($module_score);$sr++)
            {
                $module_score[$sr]['org_score']=$module_score[$sr]['score'] = getScoreByCount($module_score[$sr]);
                $module_score[$sr]['id_report_contract_module'] = NULL;
                $red_count=$red_count+$module_score[$sr]['red_total'];
                $amber_count=$amber_count+$module_score[$sr]['amber_total'];
                $green_count=$green_count+$module_score[$sr]['green_total'];
                // $global_modules[$module_score[$sr]['parent_module_id']]=array('parent_module_id'=>$module_score[$sr]['parent_module_id'],'module_name'=>$module_score[$sr]['module_name']);
                /*  The Above line is commentd in Sprint 6.1 as the client does not sant parant modules to be shown in screen
                 Plese Uncomment when they needs. */

            }
            $result[$k]['modules']=$module_score;
            $result[$k]['topic_red_count']=$red_count;
            $result[$k]['topic_amber_count']=$amber_count;
            $result[$k]['topic_green_count']=$green_count;
            $actionitems=$this->Contract_model->getActionItems(array('contract_id'=>$v['id_contract'],'customer_id'=>$data['customer_id'],'item_status'=>1));
            $result[$k]['action_items_count']=$actionitems['total_records'];
            $result[$k]['latest_review_date']=$v['updated_on'];
            $result[$k]['decision_required']=NULL;
            $result[$k]['comments']=NULL;
            $result[$k]['id_report_contract']=NULL;
            $result[$k]['order']=0;
        }
        $final_result['report_contracts']=$result;
        $final_result['id_report']=NULL;
        $final_result['save_type']=NULL;

        if(count($global_modules)>0) {
            // $this->db->select('m.id_module as parent_module_id,ml.module_name');
            // $this->db->from('module m');
            // $this->db->join('module_language ml', 'ml.module_id=m.id_module and ml.language_id=1');
            // $this->db->join('template_module t', 't.module_id=m.id_module');
            // $this->db->join('customer c', 'c.template_id=t.template_id');
            // $this->db->where_in('m.id_module', array_keys($global_modules));
            // $this->db->where('c.id_customer', $data['customer_id']);
            // $this->db->order_by('t.module_order', 'ASC');
            $this->db->select('m.id_module as parent_module_id,ml.module_name');
            $this->db->from('module m');
            $this->db->join('module_language ml','ml.module_id=m.id_module and ml.language_id=1');
            $this->db->join('template_module t','t.module_id=m.id_module');
            $this->db->join('customer_template ct','ct.template_id=t.template_id');
            $this->db->where('ct.customer_id',$data['customer_id']);
            $this->db->where('m.is_workflow','0');
            $this->db->group_by('ml.module_name');
            $this->db->order_by('t.module_order','ASC');

            $query = $this->db->get();
            $global_modules = $query->result_array();
            foreach ($global_modules as $kg => $vg) {
                $matches = '';
                preg_match_all('/[A-Z]/', ucwords(strtolower($vg['module_name'])), $matches);
                $global_modules[$kg]['module_short_name'] = implode('', $matches[0]);
            }
        }




        return array('data'=>$final_result,'parent_modules'=>array_values($global_modules),'parent_modules_k'=>array_keys($global_modules));
    }

    public function addReport($data)
    {
        $this->db->insert('report', $data);
        return $this->db->insert_id();
    }
    public function updateReport($data)
    {
        $this->db->where('id_report', $data['id_report']);
        $this->db->update('report', $data);
        return 1;
    }
    public function addReportContract($data)
    {
        $this->db->insert('report_contract', $data);
        return $this->db->insert_id();
    }
    public function updateReportContract($data)
    {
        $this->db->where('id_report_contract', $data['id_report_contract']);
        $this->db->update('report_contract', $data);
        return 1;
    }
    public function addReportContractModule($data)
    {
        $this->db->insert('report_contract_module', $data);
        return $this->db->insert_id();
    }
    public function updateReportContractModule($data)
    {
        $this->db->where('id_report_contract_module', $data['id_report_contract_module']);
        $this->db->update('report_contract_module', $data);
        return 1;
    }
    public function deleteReport($data)
    {
        $this->db->where('id_report',$data['id_report']);
        $this->db->update('report', array('report_status'=>2));
        return 1;
    }
    public function getReportEntry($data){
        $this->db->select('r.*');
        $this->db->from('report r');
        $this->db->where('id_report',$data['id_report']);
        $query = $this->db->get();
        $global_modules = $query->result_array();
        return $global_modules;
    }
    public function review_module_topic_qurestions($data){
        $this->db->select('c.id_contract,q.id_question,q.question_type,cr.contract_review_status,rcl.relationship_category_name,DATE_FORMAT(IFNULL(cr.updated_on,cr.created_on), "%Y-%m-%d") as review_date,ql.question_text,ql.request_for_proof,cqr.question_answer,cqr.v_question_answer,cqr.second_opinion,cqr.question_feedback,cqr.external_user_question_feedback,if(q.question_type="input",cqr.question_answer,qol.option_name) question_option_answer,qo.option_value,if(q.question_type="input",cqr.v_question_answer,vqol.option_name) v_question_option_answer,vqo.option_value as v_option_value,tl.topic_id,tl.topic_name,ml.module_id,ml.module_name,c.contract_name,p.provider_name,cqr.v_question_feedback,c.contract_unique_id,q.provider_visibility');
        $this->db->from('question q');
        $this->db->join('question_language ql' ,' ql.question_id = q.id_question and ql.language_id=1','LEFT');
        $this->db->join('contract_question_review cqr' ,' cqr.question_id = q.id_question','LEFT');
        $this->db->join('contract_question_review_log l' ,' cqr.id_contract_question_review = l.contract_question_review_id','LEFT');
        $this->db->join('question_option qo' ,' qo.question_id = q.id_question and qo.option_value=cqr.question_answer','LEFT');
        $this->db->join('question_option_language qol' ,' qol.question_option_id = cqr.question_option_id and qol.language_id=1','LEFT');
        $this->db->join('question_option vqo' ,' vqo.question_id = q.id_question and vqo.option_value=cqr.v_question_answer','LEFT');
        $this->db->join('question_option_language vqol' ,' vqol.question_option_id = cqr.v_question_option_id and vqol.language_id=1','LEFT');
        $this->db->join('topic t' ,' t.id_topic = q.topic_id','LEFT');
        $this->db->join('topic_language tl' ,' t.id_topic = tl.topic_id AND tl.language_id=1','LEFT');
        $this->db->join('module m' ,' m.id_module = t.module_id','LEFT');
        $this->db->join('module_language ml' ,' m.id_module = ml.module_id and ml.language_id=1','LEFT');
        $this->db->join('contract_review cr' ,' m.contract_review_id = cr.id_contract_review','LEFT');
        $this->db->join('contract c' ,' c.id_contract = cr.contract_id','LEFT');
        $this->db->join(' relationship_category_language rcl ' ,' c.relationship_category_id = rcl.relationship_category_id','LEFT');
        $this->db->join('provider p' ,' c.provider_name = p.id_provider','LEFT');
        if(is_array($data['contract_review_ids']) && count($data['contract_review_ids'])>0)
            $this->db->where_in('cr.id_contract_review',$data['contract_review_ids']);
        $this->db->group_by('`q`.`id_question`');
        $this->db->order_by('cr.id_contract_review','ASC');
        $this->db->order_by('m.module_order','ASC');
        $this->db->order_by('t.topic_order','ASC');
        $this->db->order_by('q.question_order','ASC');
        $this->db->order_by('q.question_order','ASC');

        $query = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    }

    function getAttachments($data){
//         SELECT p.provider_name,c.contract_name,rcl.relationship_category_name category_name,IF(cr.contract_review_status='finished',IF(cr.is_workflow=0,'Review finalized','Workflow finalized'),CONCAT(UCASE(LEFT(cr.contract_review_status, 1)), SUBSTRING(cr.contract_review_status, 2))) review_status,IFNULL(cr.updated_on,cr.created_on) completed_date,ml.module_name,tl.topic_name, ql.question_text,(SELECT IF(cqr.question_option_id IS NULL,cqr.question_answer,qol.option_name) FROM contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE cqr.question_id = q.id_question GROUP BY cqr.question_id) question_answere,COUNT(d.id_document) file_count,GROUP_CONCAT(d.document_name) document_name FROM question_language ql
// LEFT JOIN question q ON q.id_question = ql.question_id
// LEFT JOIN document d ON q.id_question = d.reference_id AND d.reference_type = 'question'
// LEFT JOIN topic t ON t.id_topic = q.topic_id
// LEFT JOIN topic_language tl ON t.id_topic = tl.topic_id
// LEFT JOIN module m ON m.id_module = t.module_id
// LEFT JOIN module_language ml ON m.id_module = ml.module_id
// LEFT JOIN contract_review cr ON cr.id_contract_review = m.contract_review_id
// LEFT JOIN contract c ON c.id_contract = cr.contract_id
// LEFT JOIN provider p ON p.id_provider = c.provider_name
// LEFT JOIN relationship_category_language rcl ON c.relationship_category_id = rcl.relationship_category_id
// WHERE m.contract_review_id IN (343)
// GROUP BY q.id_question
        $this->db->select("p.provider_name,c.contract_name,rcl.relationship_category_name category_name,IF(cr.contract_review_status='finished',IF(cr.is_workflow=0,'Review finalized','Workflow finalized'),CONCAT(UCASE(LEFT(cr.contract_review_status, 1)), SUBSTRING(cr.contract_review_status, 2))) review_status,IFNULL(cr.updated_on,cr.created_on) completed_date,ml.module_name,tl.topic_name, ql.question_text,(SELECT IF(cqr.question_option_id IS NULL,cqr.question_answer,qol.option_name) FROM contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE cqr.question_id = q.id_question GROUP BY cqr.question_id) question_answere,COUNT(d.id_document) file_count,GROUP_CONCAT(d.document_name) document_name,d.uploaded_on,q.question_type,c.contract_unique_id");
        $this->db->from('question_language ql');
        $this->db->join('question q','q.id_question = ql.question_id','left');
        $this->db->join('document d','q.id_question = d.reference_id AND d.reference_type = "question"','left');
        $this->db->join('topic t','t.id_topic = q.topic_id','left');
        $this->db->join('topic_language tl','t.id_topic = tl.topic_id','left');
        $this->db->join('module m','m.id_module = t.module_id','left');
        $this->db->join('module_language ml','m.id_module = ml.module_id','left');
        $this->db->join('contract_review cr','cr.id_contract_review = m.contract_review_id','left');
        $this->db->join('contract c','c.id_contract = cr.contract_id','left');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id = rcl.relationship_category_id','left');
        if(isset($data['contract_review_ids']) && count($data['contract_review_ids']) > 0)
            $this->db->where_in('m.contract_review_id',$data['contract_review_ids']);
        if(isset($data['module_ids']) && count($data['module_ids']) > 0)
            $this->db->where_in('m.id_module',$data['module_ids']);
        $this->db->where('d.id_document IS NOT NULL AND d.document_status = 1');
        $this->db->group_by('q.id_question');
        $query = $this->db->get();
        // echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    }
    

    function getReviewIds($data){
        $this->db->select('contract_review_id')->from('report_contract');
        $this->db->where_in('id_report_contract',$data['id_report_contract']);
        // $this->db->group_by('contract_workflow_id');

        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }
    function getStatusArray($statuses){
        $statuses = explode(',',$statuses);
        asort($statuses);
        $statuses = implode(',',$statuses);
        /*  rf,rip,wf,wip
            cr.contract_review_status
            cr.is_workflow 
        */
        switch($statuses){
            case 'rf':
                return '(cr.is_workflow = 0 AND cr.contract_review_status IN ("finished"))';
                break;
            case 'rf,rip':
                return '(cr.is_workflow = 0 AND cr.contract_review_status IN ("finished","review in progress"))';
                break;
            case 'rf,rip,wf':
                return '(cr.contract_review_status IN ("finished","review in progress"))';
                break;
            case 'rip':
                return '(cr.contract_review_status IN ("review in progress"))';
                break;
            case 'rip,wf':
                return '(cr.contract_review_status IN ("review in progress") OR (cr.is_workflow = 1 AND cr.contract_review_status IN ("finished")))';
                break;
            case 'rip,wf,wip':
                return '(cr.contract_review_status IN ("review in progress") OR (cr.is_workflow = 1 AND cr.contract_review_status IN ("finished","workflow in progress")))';
                break;
            case 'wf':
                return '(cr.is_workflow = 1 AND cr.contract_review_status IN ("finished"))';
                break;
            case 'rf,wf':
                return '(cr.contract_review_status IN ("finished"))';
                break;
            case 'rf,wf,wip':
                return '(cr.contract_review_status IN ("finished") OR cr.contract_review_status IN ("workflow in progress"))';
                break;
            case 'wf,wip':
                return '(cr.is_workflow = 1 AND cr.contract_review_status IN ("finished","workflow in progress"))';
                break;
            case 'wip':
                return '(cr.is_workflow = 1 AND cr.contract_review_status IN ("workflow in progress"))';
                break;
            case 'rf,wip':
                return '((cr.is_workflow = 0 AND cr.contract_review_status IN ("finished")) OR cr.contract_review_status IN ("workflow in progress"))';
                break;
            case 'rf,rip,wip':
                return '(cr.is_workflow = 0 AND cr.contract_review_status IN ("finished","review in progress")) OR cr.contract_review_status IN ("workflow in progress"))';
                break;
            case 'rip,wip':
                return '(cr.contract_review_status IN ("review in progress") OR cr.contract_review_status IN ("workflow in progress"))';
                break;
            default : //rf,rip,wf,wip
                return '(cr.contract_review_status IN ("finished","review in progress","workflow in progress"))';
            
        }
    }
    public function getactivityNames($data=null){
        $this->db->select('c.id_calender,c.workflow_name activity_name');
        $this->db->from('calender c');
        $this->db->where('c.customer_id',$data['customer_id']);
        $this->db->where('c.parent_calender_id is NULL');
        $this->db->where('c.task_type','main_task');
        $this->db->where('c.status','1');
        if(!empty($data['business_unit_id'])){
            $this->db->where('CONCAT(",", `bussiness_unit_id`, ",") REGEXP ",'.$data['business_unit_id'].',"', NULL, FALSE);
        }
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();

    }
}