<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }
    public function getProjectinfo($data=null){
        $this->db->select('c.*,CONCAT(u.first_name," ",u.last_name) as bu_owner,CONCAT(u1.first_name," ",u1.last_name) as bu_delegate,cr.validation_status,cur.currency_name');
        $this->db->from('contract c');
        $this->db->join('`user` u','c.contract_owner_id=u.id_user','left');
        $this->db->join('`user` u1','c.delegate_id=u1.id_user','left');
        $this->db->join('contract_review cr','c.id_contract=cr.contract_id','left');
        $this->db->join('currency cur','c.currency_id=cur.id_currency','left');
        if(isset($data['project_id']))
        $this->db->where('id_contract',$data['project_id']);
        // $this->db->where("cr.contract_review_status!='finished'");
        // $this->db->group_by('c.id_contract');
        $this->db->order_by('cr.id_contract_review','desc');
        $this->db->limit(1);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }

    public function updateProject($data)
    {
        $this->db->where('id_contract', $data['id_contract']);
        $this->db->update('contract', $data);
        return 1;
    }
    public function getactiveprojectProvider($data=null){
        $this->db->select('*');
        $this->db->from('`project_providers` pp');
        $this->db->join('provider p','pp.provider_id=p.id_provider','left');
        if(!empty($data['project_id'])){
            $this->db->where('pp.`project_id`',$data['project_id']);
        }
        $this->db->where('pp.`is_linked`','1');
        $this->db->where('p.`status`','1');
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }

    // public function getProjectWorkflow($data=null){
    //     $this->db->select('*');
    //     $this->db->from('contract_workflow cw');
    //     $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
    //     if(!empty($data['contract_id'])){
    //         $this->db->where('cw.contract_id',$data['contract_id']);
    //     }
    //     if(!empty($data['id_contract_workflow'])){
    //         $this->db->where('cw.id_contract_workflow',$data['id_contract_workflow']);
    //     }
    //     // $this->db->where("cr.contract_review_status!='finished'");
    //     if(!empty($data['contract_review_status_not']))
    //     $this->db->where("cw.workflow_status != 'workflow finlized'");
    //     $this->db->order_by('cr.id_contract_review','desc');
    //     $this->db->limit(1);
    //     $query = $this->db->get();//echo $this->db->last_query();exit;
    //     return $query->result_array();
    // }
    public function getProjectWorkflow($data=null){
        $this->db->select("cw.*,(SELECT id_contract_review FROM contract_review WHERE contract_workflow_id=cw.id_contract_workflow AND contract_review_status!='finished')as id_contract_review,(SELECT validation_status FROM contract_review WHERE contract_workflow_id=cw.id_contract_workflow AND contract_review_status!='finished')as validation_status");
        $this->db->from('contract_workflow cw');
        if(isset($data['contract_id'])){
            $this->db->where('cw.contract_id',$data['contract_id']);
        }
        if(isset($data['id_contract_workflow'])){
            $this->db->where('cw.id_contract_workflow',$data['id_contract_workflow']);
        }
        if(!empty($data['not_contract_workflow_id'])){
            $this->db->where('cw.id_contract_workflow!=',$data['not_contract_workflow_id']);
        }
        if(isset($data['parent_id'])){
            $this->db->where('cw.parent_id',$data['parent_id']);
        }
        $this->db->where('cw.status',1);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
        
    }
    public function getlastReview($data){
        $this->db->select('*');
        $this->db->from('contract_review');
        if(isset($data['contract_workflow_id']))
            $this->db->where('contract_workflow_id',$data['contract_workflow_id']);
        $this->db->order_by('id_contract_review','desc');
        $this->db->limit(1);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getmaintaskinfo($data=null){
        $this->db->select('*');
        $this->db->from('contract_workflow cw');
        $this->db->join('contract_review cr','cr.contract_workflow_id=cw.id_contract_workflow','left');
        $this->db->where('cr.id_contract_review',$data['contract_review_id']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getModuleUpdateData($data=null){
        $this->db->select('CONCAT(u.first_name,"",u.last_name) user_name,DATE_FORMAT(cqr.updated_on,"%Y-%m-%d") as date');
        $this->db->from('contract_question_review cqr');
        $this->db->join('`user` u','cqr.updated_by=u.id_user','left');
        $this->db->where('contract_review_id',$data['contract_review_id']);
        $this->db->where('cqr.updated_by!=""');
        $this->db->order_by('cqr.updated_on','desc');
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getModuleUpdatenameanddate($data=null){
        $this->db->select('CONCAT(u.first_name," ",u.last_name) user_name,DATE_FORMAT(cqr.updated_on,"%Y-%m-%d") as date');
        $this->db->from('module m');
        $this->db->join('topic t','m.id_module=t.module_id','left');
        $this->db->join('question q','t.id_topic=q.topic_id','left');
        $this->db->join('contract_question_review cqr','q.id_question=cqr.question_id','left');
        $this->db->join('`user` u','cqr.updated_by=u.id_user','left');
        $this->db->where('m.id_module',$data['module_id']);
        $this->db->where('cqr.updated_by!=""');
        $this->db->order_by('cqr.updated_on','desc');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getsubtask($data=null){
        $this->db->select('*');
        $this->db->from('calender c');
        $this->db->join('contract_workflow cw','c.id_calender=cw.calender_id','left');
        $this->db->where('c.date=CURDATE()');
        $this->db->where('c.task_type','sub_task');
        $this->db->where('c.plan_executed','1');
        $this->db->where('c.auto_initiate','1');
        $this->db->where('cw.parent_id>0');
        $this->db->where('cw.workflow_status','new');
        $this->db->limit(5);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getsubtaskstofinalize($data=null){
        $this->db->select('cw.contract_id,cw.id_contract_workflow,cw.created_by,cw.calender_id,cr.id_contract_review,cw.workflow_status,cw.workflow_name');
        $this->db->from('contract_workflow cw');
        $this->db->join('calender c','cw.calender_id=c.id_calender','left');
        $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
        $this->db->where('cw.parent_id',$data['parent_id']);
        $query = $this->db->get();
        return $query->result_array();   
    }
    public function getBuname($data=null){
        $this->db->select('IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,c.type');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','c.business_unit_id=bu.id_business_unit','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->where('c.id_contract',$data['contract_id']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_subtaskList($data=null){
        // print_r($data);exit;
        $this->db->select('`cw`.`id_contract_workflow`,`cr`.`id_contract_review`, `m`.`id_module` as module_id, `ml`.`module_name`,
        `p`.`provider_name`, `cw`.`workflow_name` as `template_name`, `cw`.`contract_id`, `cw`.`parent_id`,1 as is_workflow,IF(cw.parent_id>0,1,0) as is_subtask');
        $this->db->from('contract_workflow cw');
        $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
        $this->db->join('module m','cr.id_contract_review=m.contract_review_id','left');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->join('`user` u','cw.provider_id=u.id_user','left');
        $this->db->join('provider p','u.provider=p.id_provider','left');
        if(!empty($data['parent_id'])){
            $this->db->where('cw.parent_id',$data['parent_id']);
        }
        if(!empty($data['project_id'])){
            $this->db->where('cw.contract_id',$data['project_id']);
        }
        if(!empty($data['provider_id'])){
            $this->db->where('cw.provider_id',$data['provider_id']);
        }
        if(!empty($data['workflow_id'])){
            $this->db->where('cw.id_contract_workflow',$data['workflow_id']);
        }
        if(!empty($data['review_ids'])){
            $this->db->where_in('cr.id_contract_review',$data['review_ids']);
        }
        if($data['type']=='archive'){
        }
        else{
            $this->db->where('cw.status','1');
        }
        $this->db->where('cr.id_contract_review IS NOT NULL');
        $this->db->order_by('cw.id_contract_workflow','asc');
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function get_template_name($data=null){
        $this->db->select('ml.module_name,m.id_module');
        $this->db->from('`module` m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->where('m.contract_review_id',$data['contract_review_id']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProjectLogId($data){
        $this->db->select('c.id_project_log,c.created_on log_created_on,CONCAT( `u`.`first_name`,\' \', u.last_name) log_user_name,CONCAT(DATE_FORMAT(c.created_on,\'%d-%m-%Y\'),\' \', TIME(c.created_on),\' by \', CONCAT( `u`.`first_name`,\' \', u.last_name)) as log_by');
        $this->db->from('project_log c');
        $this->db->join('user u','c.created_by = u.id_user','left');
        $this->db->where('project_id',$data['project_id']);
        $this->db->order_by('id_project_log','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProjectLogDetails($data){
        $this->db->select('c.project_id as id_contract,c.project_name as contract_name,c.project_startdate as contract_start_date,c.project_enddate as contract_end_date,c.budget_spend as contract_value,c.business_unit as business_unit_id,c.owner_id as contract_owner_id,c.delegate_id as delegate_id,c.project_description as description,c.currency as currency_id,c.id_project_log as id_project_log,c.is_status_change as project_status,c.created_by,c.created_on,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as business_unit,CONCAT_WS(\' \',u2.first_name,u2.last_name) as created_by,cr.currency_name,CONCAT_WS(\' \',u.first_name,u.last_name) as delegate_user_name,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contract_owner_name');
        $this->db->from('project_log c');        
        $this->db->join('currency cr','cr.id_currency=c.currency','LEFT');
        $this->db->join('user u','u.id_user=c.delegate_id','left');
        $this->db->join('user u1','u1.id_user=c.owner_id','left');
        $this->db->join('user u2','u2.id_user=c.created_by','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->where('c.id_project_log',$data['project_log_id']);
        $query = $this->db->get();
        return $query->result_array();

        
    }
    public function getContractDashboard_old($data)
    {
        $this->db->flush_cache();
        $query="select
        module_id,module_name,static,module_order,module_status,is_workflow,topic_id,topic_name,final_score(topic_avg_weight_score) AS topic_score,topic_avg_weight_score,str,total_topic_progress

        from(
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.v_question_answer is null then -1 else cqr.v_question_answer END)))/(sum(q.question_weight)-sum(case when cqr.v_question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.v_question_answer is null then 0 else (case when cqr.v_question_answer = 'NA' then q.question_weight else cqr.v_question_answer END) END)/(sum(case when cqr.v_question_answer is null then 0 else 1 END))) *100) as total_topic_progress from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.v_question_answer != 'NA'
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='general' and q.provider_visibility in ? and t.id_topic = ?
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.v_question_answer is null then 'E' when cqr.v_question_answer='NA' then 'N' when cqr.v_question_answer=0 then 'R' when cqr.v_question_answer=1 then 'G' else 'A' END) SEPARATOR ' ')) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.v_question_answer is null then 0 else (case when cqr.v_question_answer = 'NA' then q.question_weight else cqr.v_question_answer END) END)/(sum(case when cqr.v_question_answer is null then 0 else 1 END))) *100) as total_topic_progress
        from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.v_question_answer != 'NA'
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='simple' and q.provider_visibility in ? and t.id_topic = ?
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (data_score_calculation(GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) WHEN t.type='relationship' THEN (relationship_score_calculation_new(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) as topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,0 total_topic_progress
        from question q
        LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.v_question_answer != 'NA'
        left join question_option qo on q.id_question=qo.question_id and cqr.v_question_answer=qo.option_value
        LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and q.provider_visibility in ? and t.id_topic = ?
        and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp";
		//echo $query;
        $query = $this->db->query($query,array($data['contract_review_id'],$data['provider_visibility'],$data['topic_id'],$data['contract_review_id'],$data['provider_visibility'],$data['topic_id'],$data['contract_review_id'],$data['provider_visibility'],$data['topic_id']));
        //echo $this->db->last_query();exit;
        $result =  $query->result_array();
        return $result;
    }
    public function delete($table,$where)
    {
        $this->db->where($where);
        $this->db->delete($table);
        return 1;
    }
    Public function getConnectedSubtasks($data)
    {
        $this->db->select('*');
        $this->db->from('contract_workflow cw');
        if(isset($data['contract_id']))
        {
            $this->db->where('cw.contract_id',$data['contract_id']);
        }
        if(isset($data['provider_id']))
        {
            $this->db->where_in('cw.provider_id',$data['provider_id']);
        }
        $this->db->where("cw.status",1);
        $this->db->where_in("cw.workflow_status",array("new","workflow in progress"));
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getcontractUsers($data=null){
        $this->db->select('cu.user_id');
        $this->db->from('contract_review cr');
        $this->db->join('contract_user cu','cr.id_contract_review=cu.contract_review_id','left');
        $this->db->where('cr.contract_id',$data['contract_id']);
        $this->db->where('cr.contract_review_status!=','finished');
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getmoduleAndreview($data=null){
        $this->db->select('m.id_module,cw.id_contract_workflow,cr.id_contract_review as contract_review_id');
        $this->db->from('module m');
        $this->db->join('`contract_review` `cr`','`m`.`contract_review_id`=`cr`.`id_contract_review`','left');
        $this->db->join('`contract_workflow` `cw`','`cr`.`contract_workflow_id`=`cw`.`id_contract_workflow`','left');
        $this->db->where('m.id_module',$data['id_module']);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getvalidatormodule($data=null){
        $this->db->select('m.module_status,m.id_module');
        $this->db->from('contract_review cr');
        $this->db->join('contract_user cu','cr.id_contract_review=cu.contract_review_id ','left');
        $this->db->join('user u','cu.user_id=u.id_user','left');
        $this->db->join('module m','cr.id_contract_review=m.contract_review_id','left');
        $this->db->where('u.contribution_type',1);
        $this->db->where('cr.id_contract_review',$data['contract_review_id']);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getObligations($data=null){
        $this->db->select('or.*,c.contract_name,c.contract_owner_id,c.delegate_id,pp.payment_periodicity_name as recurrence,pp1.payment_periodicity_name as resend_recurrence, IF(ISNULL(`or`.`parent_obligation_id`),recurrence_start_date,(SELECT recurrence_start_date from obligations_and_rights WHERE id_obligation = `or`.`parent_obligation_id`)) as parent_recurrence_start_date');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from('obligations_and_rights or');
        $this->db->join('contract c','c.id_contract= or.contract_id','left');
        $this->db->join('payment_periodicity pp','or.recurrence_id=pp.id_payment_periodicity','left');
        $this->db->join('payment_periodicity pp1','or.resend_recurrence_id=pp1.id_payment_periodicity','left');
        $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
        if(isset($data['id_contract'])){
            $this->db->where('or.contract_id',$data['id_contract']);  
        }
        if(isset($data['id_obligation'])){
            $this->db->where('or.id_obligation',$data['id_obligation']);
        }
        if(!empty($data['business_units'])){
            $this->db->where_in('c.business_unit_id',$data['business_units']);
        }
        $this->db->where('or.status',1);
        $this->db->where('c.is_deleted',0);
        //for calender
        if(isset($data['filterType']) && $data['filterType']=='date')
            $this->db->where("date(or.recurrence_start_date)=date('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='month')
            $this->db->where("month(or.recurrence_start_date)=month('".$data['date']."') AND year(or.recurrence_start_date)=year('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='year')
            $this->db->where("year(or.recurrence_start_date)=year('".$data['date']."')");
        if(isset($data['customer_id']))
        {
            $this->db->where('bu.customer_id',$data['customer_id']);
        }
        if(isset($data['calendar']))
        {
            $this->db->where('or.calendar',$data['calendar']);
           
            $this->db->where('or.recurrence_id>0');
        }
        //end for calender

        if((isset($data['get_parent']))&&($data['get_parent']==true))
        {
            $this->db->where('or.parent_obligation_id',null);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('or.description', $data['search'], 'both');
            $this->db->or_like('pp.payment_periodicity_name', $data['search'], 'both');
            $this->db->or_like('or.type_name', $data['search'], 'both');
            if(isset($data['calendar']))
            {
                $this->db->or_like('c.contract_name', $data['search'], 'both');
            }
            $this->db->or_like('or.applicable_to_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['calendar']))
        {
            $this->db->having('can_access',1);
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();//echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
        {
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        }
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
        $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else{
            $this->db->order_by('description','asc');
        }
        $result = $this->db->get();
        return array('total_records'=>$count_result,'data'=>$result->result_array());
    }
    public function getobligationsmails($data=null)
    {
        $this->db->select('orm.*,or.notification_message,or.applicable_to_name,or.type_name,or.description,c.contract_name,u2.email as delegate_email,u1.email as owner_email,c.delegate_id,c.contract_owner_id,u2.first_name as delgate_first_name,u2.last_name as delegate_last_name,u1.first_name as owner_first_name,u1.last_name as owner_last_name,bu.customer_id,orm.id as oblogationsMailId');
        $this->db->from('obligations_and_rights_mail orm');
        $this->db->join('contract c','orm.contract_id=c.id_contract ','left');
        $this->db->join('obligations_and_rights or','or.id_obligation=orm.obligation_id ','left');
        $this->db->join("user u1","c.contract_owner_id=u1.id_user","left");
        $this->db->join("user u2","c.delegate_id=u2.id_user","left");
        $this->db->join("business_unit bu", "bu.id_business_unit=c.business_unit_id","left");
        $this->db->where('orm.status',1);
        $this->db->where('c.is_deleted',0);
        $this->db->where('orm.mail_status',0);
        $this->db->where('or.status',1);
        $this->db->where('or.email_notification',1);
        $this->db->where('orm.date',$data['date']);
        $query = $this->db->get();//echo $this->db->last_query();exit;
        return $query->result_array();

    }
    public function get_Record_order($table,$where,$coloum,$order="DESC")
    {
        $this->db->select('*');
        $this->db->from($table);
        if(isset($where))
        {
            $this->db->where($where);
        }
        if(isset($coloum)&&isset($order))
        {
            $this->db->order_by($coloum,$order);
        }
      
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }
    public function getspent($data){
        $this->db->select('COUNT(contract_id) AS count,sl.*');
        $this->db->from('spent_lines sl');
        $this->db->join("contract c","sl.contract_id=c.id_contract","left");
        $this->db->join("business_unit bu","c.business_unit_id=bu.id_business_unit","left");
        $this->db->where('sl.status','1');
        $this->db->where('bu.customer_id',$data['customer_id']);
        $this->db->group_by('sl.contract_id');
        $this->db->order_by('count','DESC');
        $this->db->limit(1);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }
    public function getProjectTaskUsers($data)
    {
        $this->db->select('u.id_user,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as name');
        $this->db->from('contract_user cu');
        $this->db->join('user u','u.id_user=cu.user_id','left');
        if(isset($data['contract_id']))
            $this->db->where('cu.contract_id',$data['contract_id']);
        if(isset($data['contract_review_id']))
            $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        if(isset($data['contribution_type']))
        {
            if($data['contribution_type'] == 'expert')
            {
                $this->db->where('u.contribution_type',0);
            }else{
                $this->db->where('u.contribution_type',$data['contribution_type']);
            }
        }
        if(isset($data['user_id']))
            $this->db->where('u.id_user',$data['user_id']);
        $this->db->where('cu.status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getprojectSubtasks($data=null)
    {
        $this->db->select('cr.id_contract_review,cw.id_contract_workflow,cw.contract_id,cw.workflow_name,cw.workflow_status,cw.parent_id,cw.status,u.provider as provider_id');
        $this->db->from('contract_workflow cw');
        $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
        $this->db->join('user u','u.id_user=cw.provider_id','left');
        $this->db->where('cw.contract_id',$data['contract_id']);
        $this->db->where('cw.parent_id ',$data['contract_workflow_id']);
        $this->db->where('cw.workflow_status','workflow finlized');
        $query=$this->db->get();
        return $query->result_array();
    }
    public function gettaskDetails($data=null)
    {
        $this->db->select('c.*,cw.id_contract_workflow,cr.id_contract_review');
        $this->db->from('contract_workflow cw');
        $this->db->join('contract_review cr','cw.id_contract_workflow=cr.contract_workflow_id','left');
        $this->db->join('calender c','c.id_calender=cw.calender_id','left');
        if(isset($data['id_contract_workflow']))
        {
            $this->db->where('cw.id_contract_workflow',$data['id_contract_workflow']);
        }
        $query=$this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function dumpProjectTaskAnswers($data)
    {
        $query="insert into contract_question_review (contract_review_id,question_id,question_answer,v_question_answer,question_feedback,v_question_feedback,updated_by,updated_on,parent_question_id,question_option_id,v_question_option_id) (
            select ? contract_review_id,qn.id_question,cqr.question_answer,cqr.v_question_answer,cqr.question_feedback,cqr.v_question_feedback,cqr.updated_by,cqr.updated_on,cqr.parent_question_id,qon.id_question_option,cqr.v_question_option_id
        from contract_question_review cqr
        LEFT JOIN question q on q.id_question =  cqr.question_id 
        LEFT JOIN topic t on t.id_topic =q.topic_id
        left join module m on m.id_module = t.module_id
        left join module mn on mn.parent_module_id = m.parent_module_id and  mn.contract_review_id =?
        left join topic tn on tn.parent_topic_id = t.parent_topic_id and mn.id_module= tn.module_id
        left join question qn on qn.parent_question_id = q.parent_question_id and qn.topic_id= tn.id_topic
        LEFT JOIN question_option qo on cqr.question_option_id=qo.id_question_option 
        LEFT JOIN question_option qon on qo.parent_question_option_id=qon.parent_question_option_id and qon.question_id=qn.id_question
        WHERE cqr.contract_review_id=?)";
        return $this->db->query($query,array($data['new_contract_review'],$data['new_contract_review'],$data['old_contract_review']));
    }
    public function getSubTaskMappedContracts($data=null){
        $this->db->select('smc.id_subtask_mapped_contracts,smc.contract_id,smc.contract_workflow_id,c.contract_name,0 as is_workflow');
        $this->db->from('subtask_mapped_contracts smc');
        $this->db->join('contract c','smc.contract_id=c.id_contract','left');
        $this->db->where('smc.contract_workflow_id',$data['contract_workflow_id']);
        $query=$this->db->get();
        return $query->result_array();
    }
    public function getProjecttaskAttachemnts($data)
    {
        $this->db->select('d.*');
        $this->db->from('document d');
        $this->db->where('d.module_id',$data['old_contract_review']);
        $this->db->where('d.module_type','contract_review');
        $this->db->where('d.contract_workflow_id',$data['contract_workflow_id']);
        $this->db->where_in('d.reference_id',$data['Oldquestions']);
        $this->db->where('d.reference_type','question');
        $this->db->where('d.document_status',1);
        $query=$this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getModuleDetails($data){
        $newContractReview = $data['new_contract_review'];
        $this->db->select('m.*,mn.id_module as new_module_id');
        $this->db->from('module m');
        $this->db->join('module mn','mn.parent_module_id = m.parent_module_id and mn.contract_review_id ='.$newContractReview,'left');
        $this->db->where('m.contract_review_id',$data['old_contract_review']);
        $query=$this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();
    }
    public function getTopicDetails($data)
    {
        $newContractReview = $data['new_contract_review'];
        $this->db->select('t.*,tn.id_topic as new_topic_id');
        $this->db->from('topic t');
        $this->db->join('module m','m.id_module = t.module_id','left');
        $this->db->join('module mn','mn.parent_module_id = m.parent_module_id and  mn.contract_review_id ='.$newContractReview,'left');
        $this->db->join('topic tn','tn.parent_topic_id = t.parent_topic_id and mn.id_module= tn.module_id','left');
        $this->db->where('m.contract_review_id',$data['old_contract_review']);
        $query=$this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();

    }
    public function getEventFeeds($data=null){
        // $this->db->select('or.*,c.contract_name,c.contract_owner_id,c.delegate_id,pp.payment_periodicity_name as recurrence,pp1.payment_periodicity_name as resend_recurrence, IF(ISNULL(`or`.`parent_obligation_id`),recurrence_start_date,(SELECT recurrence_start_date from obligations_and_rights WHERE id_obligation = `or`.`parent_obligation_id`)) as parent_recurrence_start_date');
        // if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 8)
        //     $this->db->select('1 as can_access');
        // else if($this->session_user_info->user_role_id == 3)
        //     $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        // else if($this->session_user_info->user_role_id == 4)
        //     $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        // else
        //     $this->db->select('0 as can_access');

        $this->db->select('ef.*,c.contract_name,CONCAT(u.first_name," ",u.last_name) as responsible_user_name,r.provider_name as relation_name,p.contract_name');
        $this->db->from('event_feeds ef');
        $this->db->join('contract c','c.id_contract = ef.reference_id and  ef.reference_type = "contract"','left');
        $this->db->join('provider r','r.id_provider = ef.reference_id and ef.reference_type = "relation"','left');
        $this->db->join('contract p','p.id_contract = ef.reference_id and ef.reference_type = "relation"','left');
        $this->db->join('user u','u.id_user = ef.responsible_user_id','left');

        $this->db->where('ef.status',1);  

        if(isset($data['reference_type']))
        {
            $this->db->where('ef.reference_type',$data['reference_type']);  
        }
        if(isset($data['reference_id']))
        {
            $this->db->where('ef.reference_id',$data['reference_id']);  
        }
        if(isset($data['id_event_feed']))
        {
            $this->db->where('ef.id_event_feed',$data['id_event_feed']);  
        }

     
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('ef.subject', $data['search'], 'both');
            $this->db->or_like('CONCAT(u.first_name," ",u.last_name)', $data['search'], 'both');
            $this->db->or_like('p.provider_name', $data['search'], 'both');
            $this->db->or_like('ef.stakeholders', $data['search'], 'both');
            $this->db->or_like('ef.type', $data['search'], 'both');
            $this->db->group_end();
        }
        // if(isset($data['calendar']))
        // {
        //     $this->db->having('can_access',1);
        // }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();//echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
        {
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        }
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
        $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else{
            $this->db->order_by('id_event_feed','desc');
        }
        $result = $this->db->get();
        return array('total_records'=>$count_result,'data'=>$result->result_array());
    }
}
