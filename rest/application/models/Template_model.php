<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Template_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function TemplateList($data)
    {
        // $this->db->select('t.*');
        // $this->db->from('template t');
        // $this->db->join('customer_template ct ','ct.template_id = t.id_template','left');
        // $this->db->join('customer c','c.id_customer = ct.customer_id','left');
        // $this->db->where('t.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0);
        // if(isset($data['customer_id']) && $data['customer_id']!=0){
        //     $this->db->where('ct.customer_id',$data['customer_id']);
        //     //$this->db->where_in('ct.created_by',array(1,$this->session_user_id));
        //     //$this->db->where('t.parent_template_id is not  null');

        // }
        // else
        // {
        //         $this->db->where('t.parent_template_id is null');
        // }
            
        // if(isset($data['search'])){
        //     $this->db->group_start();
        //     $this->db->like('t.template_name', $data['search'], 'both');
        //     $this->db->group_end();
        // }
        // $this->db->group_by('t.id_template');
        // /* results count start */
        // $query = $this->db->get();
        // $all_clients_count = count($query->result_array());
        /* results count end */

        $this->db->select('t.*, group_concat(DISTINCT CONCAT(c.id_customer,"@",c.company_name)) as customer_id');
        $this->db->from('template t');
        $this->db->join('customer_template ct ','ct.template_id = t.id_template','left');
        $this->db->join('customer c','c.id_customer = ct.customer_id','left');
        $this->db->where('t.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0);
        if(isset($data['customer_id'])){
            $this->db->where('ct.customer_id',$data['customer_id']);
            //$this->db->where_in('ct.created_by',array(1,$this->session_user_id));
            //$this->db->where('t.parent_template_id is not  null');
        } else {
            $this->db->where('t.parent_template_id is null');
            // $this->db->where('t.id_template is not null');
            //$this->db->where('ct.created_by',$this->session_user_id);
        }
        
        if(isset($data['search']) && $data['customer_id']!=0 || $data['customer_id']==0){
            $this->db->group_start();
            $this->db->like('t.template_name', $data['search'], 'both');
            $this->db->group_end();
        }
        
        $this->db->group_by('t.id_template');

        $all_clients_count_db=clone $this->db;
        $all_clients_count = $all_clients_count_db->get()->num_rows();

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.id_template','DESC');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }


    public function getTemplates($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('t.*');
        $this->db->from('template t');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('t.template_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(t.template_name like "%'.$data['search'].'%")');*/
        if(isset($data['template_status']))
            $this->db->where('template_status',$data['template_status']);
        if(isset($data['is_workflow']))
            $this->db->where('is_workflow',$data['is_workflow']);
        //$this->db->where('t.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0);
        $this->db->where('t.parent_template_id is null');
        $this->db->order_by('t.id_template','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getTemplateModuleList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('t.*,ml.*,count(distinct tmt.id_template_module_topic) as topics_count,count(distinct tmtq.id_template_module_topic_question) as topics_questions_count');
        $this->db->from('template_module t');
        $this->db->join('module_language ml','t.module_id=ml.module_id','left');
        $this->db->join('template_module_topic tmt','tmt.template_module_id=t.id_template_module and tmt.status=1','left');
        $this->db->join('template_module_topic_question tmtq','tmtq.template_module_topic_id=tmt.id_template_module_topic and tmtq.status=1','left');
        if(isset($data['template_id']))
            $this->db->where('template_id',$data['template_id']);
        if(isset($data['status']))
            $this->db->where('t.status',$data['status']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('ml.module_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(ml.module_name like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.module_order','ASC');
        $this->db->group_by('t.module_id');
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getTemplateModuleTopicList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('t.*,tl.*,ml.module_name,count(distinct tmtq.id_template_module_topic_question) as topics_questions_count');
        $this->db->from('template_module_topic t');
        $this->db->join('topic_language tl','t.topic_id=tl.topic_id','left');
        $this->db->join('template_module tm','tm.id_template_module=t.template_module_id and tm.status=1','left');
        $this->db->join('module m','m.id_module=tm.module_id','left');
        $this->db->join('module_language ml','ml.module_id=m.id_module and ml.language_id=1','left');
        $this->db->join('template_module_topic_question tmtq','tmtq.template_module_topic_id=t.id_template_module_topic and tmtq.status=1','left');
        if(isset($data['template_module_id'])){
            if($data['template_module_id']!='all')
                $this->db->where('template_module_id',$data['template_module_id']);
        }
        if(isset($data['template_id'])){
            if($data['template_id']!='all')
                $this->db->where('tm.template_id',$data['template_id']);
        }
        if(isset($data['status']))
            $this->db->where('t.status',$data['status']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('tl.topic_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(tl.topic_name like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.topic_order','ASC');
        $this->db->group_by('t.id_template_module_topic');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getTemplateModuleTopicQuestionList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        // addOnQuery for checking question is related to relations . 
        $addOnQuery = '';
        if(isset($data['with_relation']) && $data['with_relation'] == 1)
        {
            if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
            {
                $addOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = ql.question_id  and provider_visibility =1) as relation_question';
            }
            else{
                $addOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = ql.question_id  and status =1 and provider_visibility =1) as relation_question';
            } 
        }
        $this->db->select('t.*,ql.*,ml.module_name,tl.topic_name'.$addOnQuery);
        $this->db->from('template_module_topic_question t');
        $this->db->join('question_language ql','t.question_id=ql.question_id','left');
        $this->db->join('template_module_topic tmt','tmt.id_template_module_topic=t.template_module_topic_id and tmt.status=1');
        $this->db->join('topic_language tl','tmt.topic_id=tl.topic_id','left');
        $this->db->join('template_module tm','tm.id_template_module=tmt.template_module_id and tm.status=1','left');
        $this->db->join('module m','m.id_module=tm.module_id','left');
        $this->db->join('module_language ml','ml.module_id=m.id_module and ml.language_id=1','left');
        if(isset($data['template_module_topic_id'])){
            if($data['template_module_topic_id']!='all')
                $this->db->where('template_module_topic_id',$data['template_module_topic_id']);
        }
        if(isset($data['template_id'])){
            if($data['template_id']!='all')
                $this->db->where('tm.template_id',$data['template_id']);
        }
        if(isset($data['status']))
            $this->db->where('t.status',$data['status']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('ql.question_text', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(ql.question_text like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.question_order','ASC');
        $this->db->group_by('t.id_template_module_topic_question');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getTemplate($data)
    {
        $this->db->select('*');
        $this->db->from('template t');
        $this->db->join('customer_template ct','t.id_template = ct.template_id','left');
        //$this->db->where('t.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0);
        if(isset($data['id_template']))
            $this->db->where('t.id_template',$data['id_template']);
        if(isset($data['id_template_not']))
            $this->db->where('t.id_template !=',$data['id_template_not']);
        if(isset($data['template_name']))
            $this->db->where('t.template_name',$data['template_name']);
        if(isset($data['customer_id']))
            $this->db->where('ct.customer_id',$data['customer_id']);
        if(isset($data['status']))
            $this->db->where('t.status',$data['status']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addTemplate($data)
    {
        $this->db->insert('template', $data);
        return $this->db->insert_id();
    }

    public function updateTemplate($data)
    {
        $this->db->where('id_template', $data['id_template']);
        $this->db->update('template', $data);
        return 1;
    }

    public function linkTemplateCustomer($data){
        //echo '<pre>'.print_r($data);exit;
         $storeproc='CALL dumpTemplate("'.$data['template_id'].'","'.$data['customer_id'].'","'.$data['created_by'].'","'.currentDate().'","'.$data['new_template_name'].'","'.$data['is_workflow'].'")';
        return $this->db->query($storeproc);
    }
    public function linkTemplateCustomerWorkflow($data){
        //echo '<pre>'.print_r($data);exit;
        $storeproc='CALL workflow_dumpTemplate("'.$data['template_id'].'","'.$data['customer_id'].'","'.$data['created_by'].'","'.currentDate().'","'.$data['new_template_name'].'","'.$data['is_workflow'].'")';
        return $this->db->query($storeproc);
    }
    public function getModules($data)
    {//echo "<pre>"; print_r($data); exit;
        $this->db->select('m.id_module,ml.module_name');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        if(isset($data['template_id_not'])) {
            $this->db->where('m.id_module not in (select module_id from template_module tm where tm.status=1 and tm.template_id = ' . $this->db->escape($data['template_id_not']) . ')');
        }
        if(isset($data['contract_review_id']))
            $this->db->where('m.contract_review_id',$data['contract_review_id']);
        if(isset($data['customer_id']) && $data['customer_id']>0)
            $this->db->where('m.customer_id',$data['customer_id']);
        else
            $this->db->where('m.customer_id is null');
        $this->db->where('m.module_status >',0);

        // $this->db->where('m.is_workflow',isset($data['is_workflow'])?1:0);
        // /* 
        //     this line add to know how any modules add to particular template.
        //     to_avial_template is the column name in module table
        //     this column will have 0 or any template id.
        //     0 means => current module will display to all templates and if insted of 0(zero) is there any other template id then current module will dipslay to particular template id.
        // */
        $this->db->where_in('m.to_avail_template',array('0',$data['template_id_not']));
        $this->db->group_by('m.id_module');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getTopics($data)
    {
        $this->db->select('t.id_topic,tl.topic_name');
        $this->db->from('topic t');
        $this->db->join('topic_language tl','t.id_topic=tl.topic_id','left');
        if(isset($data['template_id_not'])) {
            $this->db->where('t.id_topic not in (select tmc.topic_id FROM template_module tm
                                                LEFT JOIN template_module_topic tmc on tmc.template_module_id = tm.id_template_module
                                                WHERE tm.status=1 AND tmc.status=1 AND tm.template_id = ' . $this->db->escape($data['template_id_not']).')');
        }
        if(isset($data['module_id'])){
            $this->db->where('t.module_id',$data['module_id']);
        }
        $this->db->where('t.topic_status',1);
        $this->db->group_by('t.id_topic');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getQuestions($data)
    {
        // query add for checking question is related to relations . 
        $addOnQuery = '';
        if(isset($data['with_relation']) && $data['with_relation'] == 1)
        {
            if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
            {
                $addOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = q.id_question  and provider_visibility =1) as relation_question';
            }
            else{
                $addOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = q.id_question  and status =1 and provider_visibility =1) as relation_question';
            }
            
        }
        $this->db->select('q.id_question,ql.question_text'.$addOnQuery);
        $this->db->from('question q');
        $this->db->join('question_language ql','q.id_question=ql.question_id','left');
        if(isset($data['template_id_not'])) {
            $this->db->where('q.id_question not in ( select IFNULL(question_id,0) FROM template_module tm
                                                                        LEFT JOIN template_module_topic tmt on tm.id_template_module=tmt.template_module_id AND tmt.status=1
                                                                        LEFt JOIN template_module_topic_question tmtq on tmt.id_template_module_topic=tmtq.template_module_topic_id AND tmtq.status=1
                                                                        AND tm.status=1
                                                                        AND tm.template_id = ' . $this->db->escape($data['template_id_not']) . ' GROUP BY question_id) ');
        }
        if(isset($data['topic_id']))
            $this->db->where('q.topic_id',$data['topic_id']);
        $this->db->where('q.question_status',1);
        $this->db->group_by('q.id_question');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        return $query->result_array();
    }

    public function getTemplateModules($data)
    {
        $this->db->select('tm.*,ml.module_name');
        $this->db->from('template_module tm');
        $this->db->join('module_language ml','tm.module_id=ml.module_id','left');
        if(isset($data['template_id']))
            $this->db->where('tm.template_id',$data['template_id']);
        if(isset($data['status']))
            $this->db->where('tm.status',$data['status']);
        if(isset($data['id_template_module']))
            $this->db->where('tm.id_template_module',$data['id_template_module']);
        $this->db->group_by('tm.id_template_module');
        $this->db->order_by('tm.module_order','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getTemplateModuleTopics($data)
    {
        $this->db->select('tmt.*,tl.topic_name');
        $this->db->from('template_module_topic tmt');
        $this->db->join('topic_language tl','tmt.topic_id=tl.topic_id','left');
        $this->db->join('template_module tm','tmt.template_module_id=tm.id_template_module','left');
        if(isset($data['template_module_id']))
            $this->db->where('tmt.template_module_id',$data['template_module_id']);
        if(isset($data['id_template_module_topic']))
            $this->db->where('tmt.id_template_module_topic',$data['id_template_module_topic']);
        if(isset($data['template_id']))
            $this->db->where('tm.template_id',$data['template_id']);
        if(isset($data['status']))
            $this->db->where('tm.status',$data['status']);
        if(isset($data['status']))
            $this->db->where('tmt.status',$data['status']);
        $this->db->order_by('tm.module_order','ASC');
        $this->db->order_by('tmt.topic_order','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getTemplateModuleTopicQuestions($data)
    {
        $this->db->select('*');
        $this->db->from('template_module_topic_question q');
        if(isset($data['template_module_topic_id']))
            $this->db->where('q.template_module_topic_id',$data['template_module_topic_id']);
        if(isset($data['status']))
            $this->db->where('q.status',$data['status']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addTemplateModule($data)
    {
        $this->db->insert('template_module', $data);
        return $this->db->insert_id();
    }

    public function updateTemplateModule($data)
    {
        if(isset($data['template_id']))
            $this->db->where('template_id', $data['template_id']);
        if(isset($data['module_id']))
            $this->db->where('module_id', $data['module_id']);
        if(isset($data['id_template_module']))
            $this->db->where('id_template_module', $data['id_template_module']);
        $this->db->update('template_module', $data);
        return 1;
    }

    public function updateTemplateModuleBatch($data)
    {
        $this->db->update_batch('template_module',$data, 'id_template_module');
        return 1;
    }

    public function updateTemplateModuleTopicBatch($data)
    {
        $this->db->update_batch('template_module_topic',$data, 'id_template_module_topic');
        return 1;
    }

    public function updateTemplateModuleTopicQuestionBatch($data)
    {
        $this->db->update_batch('template_module_topic_question',$data, 'id_template_module_topic_question');
        return 1;
    }

    public function addTemplateModuleTopic($data)
    {
        $this->db->insert('template_module_topic', $data);
        return $this->db->insert_id();
    }

    public function updateTemplateModuleTopic($data)
    {
        if(isset($data['template_module_id']))
            $this->db->where('template_module_id', $data['template_module_id']);
        if(isset($data['topic_id']))
            $this->db->where('topic_id', $data['topic_id']);
        if(isset($data['id_template_module_topic']))
            $this->db->where('id_template_module_topic', $data['id_template_module_topic']);
        $this->db->update('template_module_topic', $data);
        return 1;
    }

    public function addTemplateModuleTopicQuestion($data)
    {
        $this->db->insert('template_module_topic_question', $data);
        return $this->db->insert_id();
    }

    public function updateTemplateModuleTopicQuestion($data)
    {
        if(isset($data['template_module_topic_id']))
            $this->db->where('template_module_topic_id', $data['template_module_topic_id']);
        if(isset($data['question_id']))
            $this->db->where('question_id', $data['question_id']);
        if(isset($data['id_template_module_topic_question']))
            $this->db->where('id_template_module_topic_question', $data['id_template_module_topic_question']);
        $this->db->update('template_module_topic_question', $data);
        return 1;
    }

    public function getModuleTopicQuestionCount($data)
    {
        $this->db->select('count(DISTINCT tm.id_template_module) as module_count,count(DISTINCT tmt.id_template_module_topic) as topic_count,count(DISTINCT tmtq.id_template_module_topic_question) as question_count');
        $this->db->from('template_module tm');
        $this->db->join('template_module_topic tmt','tmt.template_module_id=tm.id_template_module and tmt.status=1','left');
        $this->db->join('template_module_topic_question tmtq','tmt.id_template_module_topic=tmtq.template_module_topic_id and tmtq.status=1','left');
        if(isset($data['template_id']))
            $this->db->where('tm.template_id',$data['template_id']);
        if(isset($data['status']))
            $this->db->where('tm.status',$data['status']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function cloneTemplate($data)
    {
        $this->db->query('insert into template (template_name,template_status,created_on)
                          select "'.$this->db->escape($data["template_name"]).'",template_status,"'.currentDate().'" from template where id_template ='.$this->db->escape($data['clone_template_id']));
        $insert_id = $this->db->insert_id();

        /* getting template modules*/
        $this->db->select('*');
        $this->db->from('template_module');
        $this->db->where('template_id',$data['clone_template_id']);
        $query = $this->db->get();
        $template_modules = $query->result_array();

        for($s=0;$s<count($template_modules);$s++)
        {
            //adding template modules
            $this->db->query('insert into template_module (template_id,module_id,module_order,status)
                              values('.$insert_id.',"'.$template_modules[$s]['module_id'].'","'.$template_modules[$s]['module_order'].'","'.$template_modules[$s]['status'].'")');
            $new_template_module_id = $this->db->insert_id();

            /*getting template module topic*/
            $this->db->select('*');
            $this->db->from('template_module_topic');
            $this->db->where('template_module_id',$template_modules[$s]['id_template_module']);
            $query = $this->db->get();
            $template_module_topics = $query->result_array();
            for($sr=0;$sr<count($template_module_topics);$sr++)
            {
                //adding template module topic
                $this->db->query('insert into template_module_topic (template_module_id,topic_id,topic_order,status)
                              values('.$new_template_module_id.',"'.$template_module_topics[$sr]['topic_id'].'","'.$template_module_topics[$sr]['topic_order'].'","'.$template_module_topics[$sr]['status'].'")');
                $new_template_module_topic_id = $this->db->insert_id();

                /*getting template module topic question*/
                $this->db->select('*');
                $this->db->from('template_module_topic_question');
                $this->db->where('template_module_topic_id',$template_module_topics[$sr]['id_template_module_topic']);
                $query = $this->db->get();
                $template_module_topic_questions = $query->result_array();

                for($st=0;$st<count($template_module_topic_questions);$st++)
                {
                    //adding templte module topic question
                    $this->db->query('insert into template_module_topic_question (template_module_topic_id,question_id,question_order,status)
                              values('.$new_template_module_topic_id.',"'.$template_module_topic_questions[$st]['question_id'].'","'.$template_module_topic_questions[$st]['question_order'].'","'.$template_module_topic_questions[$st]['status'].'")');

                }
            }
        }

        return 1;
    }

    public function getTemplatePreview($data){
        $template_module['modules'] = $this->getTemplateModules(array('template_id'=>$data['template_id'],'status'=>1));
        //relation question visibility 
        if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
        {
            $questionAddOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = qt.id_question  and provider_visibility =1) as relation_question';
        }
        else{
            $questionAddOnQuery = ',(select if(count(*)>0 , 1,0)  from relationship_category_question WHERE question_id = qt.id_question  and status =1 and provider_visibility =1) as relation_question';
        } 
        foreach($template_module['modules'] as $k => $v){
            $this->db->select('t.id_template_module_topic,t.topic_id,tl.topic_name,t.topic_order');
            $this->db->from('template_module_topic t');
            $this->db->join('topic_language tl','t.topic_id=tl.topic_id and tl.language_id=1','left');
            $this->db->where('t.template_module_id',$v['id_template_module']);
            $this->db->where('t.status',1);
            $this->db->order_by('t.topic_order');
            $topics = $this->db->get();
            $template_module['modules'][$k]['topics']=$topics->result_array();
            foreach($template_module['modules'][$k]['topics'] as $k1 => $v1){
                $this->db->select('q.question_id,ql.question_text,ql.request_for_proof,q.question_order,qt.question_type'.$questionAddOnQuery);
                $this->db->from('template_module_topic_question q');
                $this->db->join('question_language ql','q.question_id=ql.question_id and ql.language_id=1','left');
                $this->db->join('question qt','q.question_id=qt.id_question','left');
                $this->db->where('q.template_module_topic_id',$v1['id_template_module_topic']);
                $this->db->where('q.status',1);
                $this->db->order_by('q.question_order');
                $question = $this->db->get();
                $template_module['modules'][$k]['topics'][$k1]['questions']=$question->result_array();
                foreach($template_module['modules'][$k]['topics'][$k1]['questions'] as $k2 => $v2){
                    $this->db->select('qo.id_question_option,qo.question_id,qol.option_name');
                    $this->db->from('question_option qo');
                    $this->db->join('question_option_language qol','qo.id_question_option = qol.question_option_id and qol.language_id=1','left');
                    $this->db->where('qo.question_id',$v2['question_id']);
                    $this->db->where('qo.status',1);
                    $option = $this->db->get()->result_array();
                    $template_module['modules'][$k]['topics'][$k1]['questions'][$k2]['options']=$option;
                    /*foreach($option as $k3 => $v3){

                    }*/
                }
            }

        }

       return $template_module;
    }

    public function getImportTemplateModuleTopicQuestionCount($data)
    {
        $this->db->select('t.id_template,t.template_name,count(distinct tm.module_id) as module_count,
        count(distinct tmt.id_template_module_topic) as topics_count,count(distinct tmtq.id_template_module_topic_question)
        as topics_questions_count');
        $this->db->from('template t');
        $this->db->join('template_module tm','tm.template_id = t.id_template and tm.status = 1','left');
        $this->db->join('template_module_topic tmt','tm.id_template_module = tmt.template_module_id and tmt.status = 1','left');
        $this->db->join('template_module_topic_question tmtq','tmt.id_template_module_topic = tmtq.template_module_topic_id and tmtq.status = 1','left');
        $this->db->where('t.is_workflow',(isset($data['is_workflow']) && $data['is_workflow']=='true')?1:0);
        if(isset($data['import_status']))
            $this->db->where('t.import_status',$data['import_status']);
        if(isset($data['template_status']))
            $this->db->where('t.template_status',$data['template_status']);
            $this->db->where('t.id_template is not null');
        if(isset($data['is_workflow']) && $data['is_workflow']=='true')
        {
            $is_workflow = 1;
        } else {
            $is_workflow = 0;
        }

        $this->db->join('module m','tm.module_id=m.id_module','left');
       // $this->db->join('module_lamnguage ml','m.id_module=ml.module_id','left');
        $this->db->where('m.is_workflow',$is_workflow);

        $this->db->group_by('t.template_name');
        //this line getting count of all records
        $all_clients_db=clone $this->db;
        $all_clients_count=$all_clients_db->get()->num_rows();
        //echo '<pre>'.$this->db->last_query();exit;
        // print_r(json_decode($data['pagination'],true)); exit;
        //pagination condition
        $data['pagination']=json_decode($data['pagination'],true);
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.id_template');
        
        $query = $this->db->get();
            //echo ''.$this->db->last_query(); exit;
        $query_result = $query->result_array();
        return $data = array("total_records"=>$all_clients_count,"data"=>$query_result);
    }

    public function checkCustomerTemplateName($data){
        //$check_query = 'SELECT * from template t LEFT JOIN customer_template ct on t.id_template = ct.template_id WHERE ct.customer_id = '.$data['customer_id'].' AND t.template_name = "'.$data['new_template_name'].'"';
        return $this->db->select('*')->from('template t')->join('customer_template ct','t.id_template = ct.template_id','left')->where('ct.customer_id',$data['customer_id'])->where('t.template_name',$data['new_template_name'])->get()->result_array();
        //->where('t.is_workflow',isset($data['is_workflow'])?$data['is_workflow']:0)
    }

}