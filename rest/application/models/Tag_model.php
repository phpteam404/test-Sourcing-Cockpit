<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tag_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function ModuleTopicTagList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('count(*) as total_records');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->join('topic t','m.id_module=t.module_id','');
        $this->db->join('topic_language tl','tl.topic_id=t.id_topic','left');
        if(isset($data['topic_status']))
            $this->db->where('t.topic_status',$data['topic_status']);
        if(isset($data['contract_review_id']))
            $this->db->where('m.contract_review_id',$data['contract_review_id']);
        if(isset($data['customer_id'])){
            $this->db->where('m.customer_id',$data['customer_id']);
        }else{
            $this->db->where('m.customer_id is null');
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('ml.module_name', $data['search'], 'both');
            $this->db->or_like('tl.topic_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(tl.topic_name like "%'.$data['search'].'%" or ml.module_name like "%'.$data['search'].'%")');*/

        /* results count start */
        $query = $this->db->get();
        $all_clients_count = $query->result_array();
        //echo "<pre>"; print_r($all_clients_count); exit;
        $all_clients_count = $all_clients_count[0]['total_records'];
        /* results count end */

        $this->db->select('m.id_module,ml.module_name,t.id_topic,tl.topic_name,COUNT(t.id_tag) as tag_count');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->join('topic t','m.id_module=t.module_id','');
        $this->db->join('topic_language tl','t.id_topic=tl.topic_id','left');
        $this->db->join('tag t','t.id_topic=t.topic_id','left');
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
        /*if(isset($data['search']))
            $this->db->where('(tl.topic_name like "%'.$data['search'].'%" or ml.module_name like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('t.tag_order','ASC');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function TagList($data)
    {
        $this->db->select("t.*,l.*,IF(c.country_name!='',CONCAT(bu.bu_name,' - ',c.country_name),bu.bu_name) as bu_name ,bu.status as business_unit_status");
        $this->db->from('tag t');
        $this->db->join('tag_language l','t.id_tag=l.tag_id','left');
        $this->db->join('business_unit bu','t.business_unit_id=bu.id_business_unit','left');
        $this->db->join('country c','bu.country_id=c.id_country','left');
        /*$this->db->join('relationship_category_tag rct','rct.tag_id=t.id_tag and rct.status=1','left');*/
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        $this->db->where('t.customer_id',$data['customer_id']);
        // if(isset($data['status']))
        //     $this->db->where('t.status',$data['status']);
        if(isset($data['status']) && $data['status'] == 1){
            $this->db->where('t.status',$data['status']);
        }
        if(!empty($data['tag_type'])){
            $this->db->where('t.type',$data['tag_type']);
        }


        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        elseif(isset($data['orderBy']) && ($data['orderBy'] == 'forExport'))
        {
            $this->db->order_by("t.is_fixed desc,t.tag_order asc");
        }
        elseif(isset($data['orderBy']) && ($data['orderBy'] == 'tag_order'))
        {
            $this->db->order_by('t.tag_order','asc');
        }
        else{
            $this->db->order_by('t.id_tag','asc');
            // $this->db->order_by('t.status','DESC');
            // $this->db->order_by('t.tag_order','ASC');
        }
        /*$this->db->group_by('t.id_tag');*/
        $query = $this->db->get();
        // echo $this->db->last_query(); exit;
        $result=$query->result_array();
        //return array('total_records' => $all_clients_count,'data' => $query->result_array());
        return $result;
    }

    public function getTagInfo($data)
    {
        $this->db->select('t.*,l.*,group_concat(ol.id_tag_option_language) as id_tag_option_language,group_concat(ol.tag_option_name) as option_name');
        $this->db->from('tag t');
        $this->db->join('tag_language l','t.id_tag=l.tag_id','left');
        $this->db->join('tag_option o','t.id_tag=o.tag_id  and o.status=1','left');
        $this->db->join('tag_option_language ol','o.id_tag_option=ol.tag_option_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['id_tag']))
            $this->db->where('t.id_tag',$data['id_tag']);
        if(isset($data['tag_id']))
            $this->db->where('t.id_tag',$data['tag_id']);
        $query = $this->db->get();
        $result=$query->result_array(); //echo '<pre>'.$this->db->last_query();exit;
        foreach($result as $kr=>$vr){
            $inner_data['tag_id']=$vr['id_tag'];
            $this->db->select('o.*,ol.*');
            $this->db->from('tag_option o');
            $this->db->join('tag_option_language ol','o.id_tag_option=ol.tag_option_id','left');
            $this->db->where('o.tag_id',$vr['id_tag']);
            $this->db->where('o.status','1');
            $sub_query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
            $result[$kr]['option_names'] = $sub_query->result_array();
        }
        //echo '<pre>'.print_r($result);exit;
        //echo $this->db->last_query(); exit;
        return $result;
    }

    public function addTag($data)
    {
        $this->db->insert('tag', $data);
        return $this->db->insert_id();
    }

    public function addTagLanguage($data)
    {
        $this->db->insert('tag_language', $data);
        return $this->db->insert_id();
    }

    public function addTagOption($data)
    {
        $this->db->insert('tag_option', $data);
        return $this->db->insert_id();
    }

    public function addTagOptionLanguage($data)
    {
        $this->db->insert('tag_option_language', $data);
        return $this->db->insert_id();
    }

    public function updateTag($data)
    {
        $this->db->where('id_tag', $data['id_tag']);
        $this->db->update('tag', $data);
        return 1;
    }

    public function updateTagBacth($data)
    {
        $this->db->update_batch('tag',$data, 'id_tag');
    }

    public function updateTagLanguage($data)
    {
        $this->db->where('id_tag_language', $data['id_tag_language']);
        $this->db->update('tag_language', $data);
        return 1;
    }

    public function updateTagOption($data)
    {
        $this->db->where('id_tag_option', $data['id_tag_option']);
        $this->db->update('tag_option', $data);
        return 1;
    }

    public function updateTagOptionLanguage($data)
    {
        $this->db->where('id_tag_option_language', $data['id_tag_option_language']);
        $this->db->update('tag_option_language', $data);
        return 1;
    }

    public function getExistingOptions($data)
    {
        $this->db->select('*');
        $this->db->from('tag_option_language');
        if(isset($data['id_tag_option']))
            $this->db->where('id_tag_option',$data['id_tag_option']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addRelationshipCategoryTag($data)
    {
        $this->db->insert('relationship_category_tag',$data);
        return $this->db->insert_id();
    }

    public function updateRelationshipCategoryTag($data)
    {
        $this->db->where('id_relationship_category_tag', $data['id_relationship_category_tag']);
        $this->db->update('relationship_category_tag', $data);
        return 1;
    }

    public function getTagRelationshipCategory($data)
    {
        $this->db->select('rc.*,rcl.relationship_category_name,rct.id_relationship_category_tag,IFNULL(rct.status,0) as status');
        $this->db->from('relationship_category rc');
        $this->db->join('relationship_category_language rcl','rc.id_relationship_category=rcl.relationship_category_id','left');
        if(isset($data['tag_id'])) {
            $this->db->join('relationship_category_tag rct', 'rc.id_relationship_category=rct.relationship_category_id and rct.tag_id=' . $this->db->escape($data["tag_id"]), 'left');
            //$this->db->where('rct.tag_id',$data['tag_id']);
        }
        else {
            $this->db->join('relationship_category_tag rct', 'rc.id_relationship_category=rct.relationship_category_id', 'left');
            $this->db->where('id_relationship_category_tag is null');
        }
        if(isset($data['status']))
            $this->db->where('relationship_category_status',$data['status']);
        if(isset($data['customer_id']))
            $this->db->where('rc.customer_id',$data['customer_id']);
        $this->db->where('rc.can_review',1);
        $this->db->order_by('rc.id_relationship_category','desc');
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
    public function getTagMasterOptions($data=array())
    {
        $this->db->select('*');
        $this->db->from('tag_type_option tto');
        if(isset($data['tag_type']))
            $this->db->where('tto.tag_type',$data['tag_type']);
        $this->db->where('status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getTag($data)
    {
        $this->db->select('t.*');
        $this->db->from('tag t');
        if(isset($data['id_tag']))
            $this->db->where('t.id_tag',$data['id_tag']);
        if(isset($data['tag_id']))
            $this->db->where('t.id_tag',$data['tag_id']);
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;
    }
    public function getTagOption($data)
    {
        $this->db->select('t.*');
        $this->db->from('tag_option t');
        if(isset($data['id_tag_option']))
            $this->db->where('t.id_tag_option',$data['id_tag_option']);
        $query = $this->db->get();
        $result=$query->result_array();
        return $result;
    }
    public function getTagOptions($data)
    {
        $this->db->select('to.*,tl.*');
        $this->db->from('tag_option to');
        //$this->db->join('tag_option to','t.id_tag = to.tag_id');
        $this->db->join('tag_option_language tl','to.id_tag_option = tl.tag_option_id');
        if(isset($data['tag_id']))
            $this->db->where('to.tag_id',$data['tag_id']);
        if(isset($data['status']))
            $this->db->where('to.status',$data['status']);

        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result=$query->result_array();
        foreach($result as $k => $v){
            $result[$k]['id_tag_option'] = pk_encrypt($v['id_tag_option']);
            $result[$k]['tag_option_id'] = pk_encrypt($v['tag_option_id']);
            $result[$k]['id_tag_option_language'] = pk_encrypt($v['id_tag_option_language']);
            $result[$k]['updated_by'] = pk_encrypt($v['updated_by']);
            $result[$k]['created_by'] = pk_encrypt($v['created_by']);
            $result[$k]['tag_id'] = pk_encrypt($v['tag_id']);
        }
        return $result;
    }

    public function getCustomerTags($data){
        $this->db->select('tl.tag_text,t.id_tag,t.tag_type,t.field_type')->from('tag t');
        $this->db->join('tag_language tl ',' t.id_tag = tl.tag_id');
        $this->db->where('t.customer_id',$data['customer_id']);
        $this->db->where('t.status',$data['status']);
        $this->db->order_by('t.tag_order');
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }
    public function getContractTagoptions($data){
        $this->db->select('top.id_tag_option,tol.tag_option_name')->from('tag_option top');
        $this->db->join('tag_option_language tol','top.id_tag_option = tol.tag_option_id');
        if(isset($data['tag_id']))
            $this->db->where('top.tag_id',$data['tag_id']);
            if(isset($data['tag_option_id']))
            $this->db->where('top.id_tag_option',$data['tag_option_id']);
            $this->db->where('top.status',1);
            $query = $this->db->get();
        return $query->result_array();
    }

    public function getContractTags($data){
        $this->db->select('*')->from('contract_tags ct');
        $this->db->join('tag t','ct.tag_id = t.id_tag');
        if(isset($data['name']) && $data['name'])
            $this->db->join('tag_language tl','t.id_tag = tl.tag_id');
        $this->db->where('ct.contract_id',$data['contract_id']);
        $this->db->where('ct.status',$data['status']);
        $this->db->group_by('ct.tag_id');
        if(isset($data['orderBy']) && $data['orderBy'] == 'export')
        {
            $this->db->order_by('t.id_tag');
        }
        else{
            $this->db->order_by('t.tag_order');
        }
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }
    public function getCntTags($data=null){
        // print_r($data);exit;
        $contract_id=$data['contract_id'];
        $this->db->select("`t`.*, `l`.*,(SELECT id_contract_tag FROM contract_tags  WHERE  tag_id=t.id_tag  and  contract_id = $contract_id  GROUP BY tag_id) id_contract_tag,(SELECT IF(ct.tag_option=0, `ct`.`tag_option_value`, ct.tag_option) tag_answer FROM contract_tags ct  WHERE  ct.tag_id=t.id_tag  and  ct.contract_id = $contract_id GROUP BY ct.tag_id) tag_answer,
        (SELECT tag_option FROM contract_tags  WHERE  tag_id=t.id_tag  and  contract_id = $contract_id GROUP BY tag_id) tag_option ,(SELECT comments FROM contract_tags  WHERE  tag_id=t.id_tag  and  contract_id = $contract_id GROUP BY tag_id) comments");
        $this->db->from('tag t');
        $this->db->join('tag_language l','t.id_tag=l.tag_id','left');
        // $this->db->where('l.language_id',$data['language_id']);
        $this->db->where('t.customer_id',$this->session_user_info->customer_id);
        $this->db->where('t.status',1);
        $this->db->where('t.type','contract_tags');
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }

    public function getNames($data)
    {
        if($data['module'] == 'contract' || $data['module'] == 'project')
        {
            $fieldName = "contract_name";
            $table = "contract";
            $coloumnName = "id_contract";
        }
        elseif($data['module'] == 'relation')
        {
            $fieldName = "provider_name";
            $table = "provider";
            $coloumnName = "id_provider";
        }
        elseif($data['module'] == 'catalogue')
        {
            $fieldName = "catalogue_name";
            $table = "catalogue";
            $coloumnName = "id_catalogue";
        }
        $this->db->select('GROUP_CONCAT('.$fieldName.') as tag_option_value');
        $this->db->from($table);
        $this->db->where_in($coloumnName,$data['ids']);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        $result = $query->result_array();
        return $result;
    }

    public function getSelectName($data)
    {
        if($data['module'] == 'contract' || $data['module'] == 'project')
        {
            $fieldName = "contract_name";
            $table = "contract";
            $coloumnName = "id_contract";
        }
        elseif($data['module'] == 'relation')
        {
            $fieldName = "provider_name";
            $table = "provider";
            $coloumnName = "id_provider";
        }
        elseif($data['module'] == 'catalogue')
        {
            $fieldName = "catalogue_name";
            $table = "catalogue";
            $coloumnName = "id_catalogue";
        }
        if( isset($data['clickable']) && $data['clickable'] == true)
        {
            if($data['module'] == 'contract' || $data['module'] == 'project')
            {
                if($data['userroleId'] == 2)
                {
                    $clickCondition = "'1' as can_access";
                }
                elseif($data['userroleId'] == 3)
                {
                    $clickCondition = "if(contract.contract_owner_id = ".$data['userId'].",1,0) as can_access";
                }
                elseif($data['userroleId'] == 4 )
                {
                    $clickCondition = "if(contract.delegate_id = ".$data['userId'].",1,0) as can_access";
                }
                elseif($data['userroleId'] == 6)
                {
                    $userId =  $data['userId'] ;
                    $clickCondition = "if(FIND_IN_SET(contract.business_unit_id , (select GROUP_CONCAT(business_unit_id) from business_unit_user where user_id = $userId and status =1) ),1,0) as can_access";
                }
                else{
                    $clickCondition = "'0' as can_access";
                }
            }
            elseif($data['module'] == 'relation' || $data['module'] == 'catalogue')
            {
                if($data['userroleId'] == 2 || $data['userroleId'] == 3 || $data['userroleId'] == 4 ||$data['userroleId'] == 6)
                {
                    $clickCondition = "'1' as can_access";
                }
            }  
        }
        else
        {
            $clickCondition = '';
        }
        $this->db->select($coloumnName.' as id,'.$fieldName.' as name ,'.$clickCondition);
        $this->db->from($table);
        $this->db->where_in($coloumnName,$data['ids']);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        $result = $query->result_array();
        return $result;

    }

    public function emptyingTagData($data)
    {
        $this->db->where('tag_id', $data['tag_id']);
        $this->db->update($data['table'], $data['updateData']);
        return 1;
    }

    public function getMultiSelectedDropDown($data)
    {
        $this->db->select("GROUP_CONCAT(tol.tag_option_name) as options");
        $this->db->from("tag_option_language tol");
        $this->db->where_in("tag_option_id",$data['options']);
        $query =$this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function getCatalogueTags($data=null){
        $catalogue_id=$data['catalogue_id'];
        $this->db->select("`t`.*, `l`.*,(SELECT id_catalogue_tag FROM catalogue_tags  WHERE  tag_id=t.id_tag  and  catalogue_id = $catalogue_id  GROUP BY tag_id) id_catalogue_tag,(SELECT IF(ct.tag_option=0, `ct`.`tag_option_value`, ct.tag_option) tag_answer FROM catalogue_tags ct  WHERE  ct.tag_id=t.id_tag  and  ct.catalogue_id = $catalogue_id GROUP BY ct.tag_id) tag_answer,
        (SELECT tag_option FROM catalogue_tags  WHERE  tag_id=t.id_tag  and  catalogue_id = $catalogue_id GROUP BY tag_id) tag_option ,(SELECT comments FROM catalogue_tags  WHERE  tag_id=t.id_tag  and  catalogue_id = $catalogue_id GROUP BY tag_id) comments");
        $this->db->from('tag t');
        $this->db->join('tag_language l','t.id_tag=l.tag_id','left');
        $this->db->where('t.customer_id',$this->session_user_info->customer_id);
        $this->db->where('t.status',1);
        $this->db->where('t.type','catalogue_tags');
        $query = $this->db->get(); //echo $this->db->last_query();
        $result = $query->result_array();
        return $result;
    }

    public function getCatalogeTagsdata($data){
        $this->db->select('*')->from('catalogue_tags ct');
        $this->db->join('tag t','ct.tag_id = t.id_tag');
        if(isset($data['name']) && $data['name'])
            $this->db->join('tag_language tl','t.id_tag = tl.tag_id');
        $this->db->where('ct.catalogue_id',$data['catalogue_id']);
        $this->db->where('ct.status',$data['status']);
        $this->db->group_by('ct.tag_id');
        if(isset($data['orderBy']) && $data['orderBy'] == 'export')
        {
            $this->db->order_by('t.id_tag');
        }
        else{
            $this->db->order_by('t.tag_order');
        }
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        $result = $query->result_array();
        return $result;
    }
}