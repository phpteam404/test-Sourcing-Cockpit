<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(1);
class Catalogue_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }
    public function getcustomerCurrency($data)
    {
        $id_currency=array();

        $this->db->select('c.id_currency');
        $this->db->from('currency c');
        if(isset($data['customer_id']))
        {
            $this->db->where('c.customer_id',$data['customer_id']);
        }
        $query = $this->db->get();
        $currency = $query->result_array();
        $id_currency = array_map(function ($i) {
            return $i['id_currency'];
        }, $currency);

        return $id_currency;

    }

    public function getcatalogueBybuid($data=null){
        $this->db->select('c.id_catalogue,c.catalogue_name');
        $this->db->from('catalogue c');
        $this->db->where('c.customer_id',$data['customer_id']);
        if(!empty($data['catalogue_unique_id'])){
            $this->db->where('c.catalogue_unique_id',$data['catalogue_unique_id']);
        }
        $this->db->order_by('id_catalogue','asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function simpleCatalogueList($data)
    {
        $this->db->select("id_catalogue,catalogue_name");
        $this->db->from("catalogue c");
        if(isset($data['customer_id']) && $data['customer_id']>0)
        {
            $this->db->where('c.customer_id',$data['customer_id']);
        }
        //$this->db->where('c.status',1);
        $this->db->where('c.is_deleted',0);
        $query = $this->db->get();  
        $this->db->order_by('c.catalogue_name','asc');  
        return $query->result_array(); 
    }

    public function list($data)
    {
        $this->db->select("c.*,cu.currency_name,(select GROUP_CONCAT(DISTINCT contract_id)  from service_catalogue where service_catalogue.catalogue_id = c.id_catalogue and service_catalogue.status =1) as contract_ids ,(select GROUP_CONCAT(DISTINCT contract_name)   from service_catalogue LEFT JOIN contract on contract.id_contract = service_catalogue.contract_id where service_catalogue.catalogue_id = c.id_catalogue and service_catalogue.status =1) as contract_names");
        // if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) ){
        //     $this->db->select("");
        // }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(document_name) FROM document d WHERE d.reference_type='catalogue' AND d.reference_id=c.id_catalogue AND d.module_id=c.id_catalogue AND d.document_status=1) as document_names");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(document_source) FROM document d WHERE d.reference_type='catalogue' AND d.reference_id=c.id_catalogue AND d.module_id=c.id_catalogue AND d.document_status=1) as document_urls");
        }
        $this->db->from("catalogue c");
        // if(isset($data['customer_id']) && $data['customer_id']>0)
        // {
            $this->db->where('c.customer_id',$data['customer_id']);
        // }
        $this->db->join("currency cu","c.currency_id=cu.id_currency","left");
        $this->db->where('c.is_deleted',0);
        if(isset($data['id_catalogue']))
        {
            $this->db->where('c.id_catalogue',$data['id_catalogue']);
        }
        if(isset($data['status']))
        {
            $this->db->where('c.status',$data['status']);
        }
        // if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) ){
        //     $this->db->join("document d","c.id_catalogue=d.reference_id AND d.reference_type = 'catalogue' AND d.module_type = 'catalogue' AND d.module_id = c.id_catalogue AND d.document_status = 1","left");
        // }
        // if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
        //     $this->db->select("GROUP_CONCAT(d.document_name) as document_names");
        // }
        // if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
        //     $this->db->select("GROUP_CONCAT(d.document_source) as document_urls");
        // }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.catalogue_name', $data['search'], 'both');
            $this->db->or_like('c.catalogue_unique_id', $data['search'], 'both');
            $this->db->or_like('cu.currency_name', $data['search'], 'both');
            $this->db->group_end();
        }
        //$this->db->order_by('c.catalogue_name','asc'); 
        //////////advanced filters start ///////////////
        foreach($data['adv_filters'] as $filter){
            if( $filter['domain'] == "Catalogue Tags" )
            {
                $tagId = $filter['master_domain_field_id'];
                $condition = $filter['condition'];
                $value = $filter['value'];
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT tag_id FROM  catalogue_tags WHERE catalogue_tags.catalogue_id = c.id_catalogue AND catalogue_tags.status=1 and catalogue_tags.tag_id =  $tagId  AND FIND_IN_SET($tagOptionValue, catalogue_tags.tag_option))");
                    }
                    $this->db->group_end();
                }
                elseif($filter['field_type']=='date')
                {
                    $this->db->where("EXISTS(SELECT tag_id FROM  catalogue_tags WHERE catalogue_tags.catalogue_id = c.id_catalogue AND catalogue_tags.status=1 and catalogue_tags.tag_id =  $tagId  AND DATE(catalogue_tags.tag_option_value) $condition  '$value')");
                }
                elseif(($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'))
                {
                   if($filter['condition'] == 'like')
                   {
                        $this->db->where("EXISTS(SELECT tag_id FROM  catalogue_tags WHERE catalogue_tags.catalogue_id = c.id_catalogue AND catalogue_tags.status=1 and catalogue_tags.tag_id =  $tagId  AND catalogue_tags.tag_option_value LIKE '%$value%' ESCAPE '!')");
                   }
                   else
                   {
                        $this->db->where("EXISTS(SELECT tag_id FROM  catalogue_tags WHERE catalogue_tags.catalogue_id = c.id_catalogue AND catalogue_tags.status=1 and catalogue_tags.tag_id =  $tagId  AND catalogue_tags.tag_option_value $condition  $value)");
                   }   
                }
            }
            else
            {
                if($filter['field_type']=='drop_down'){
                    if($filter['database_field'] == 'c.status')
                    {
                        $filterDetails = explode(',',$filter['value']);
                        $statusArray = [];
                        foreach($filterDetails as $key){
                            if($key == 'Closed')
                            {
                                $statusArray[] = 0;
                            }
                            elseif($key == 'Active')
                            {
                                $statusArray[] = 1;
                            }
                        }
                        $filter['value'] = implode(",",$statusArray);
                    }
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
                elseif($filter['field_type']=='date'){
                    $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
                }
                elseif($filter['field_type']=='numeric_text'||$filter['field_type']=='free_text'){
                    if($filter['condition']=='like'){
                        $this->db->like($filter['database_field'],$filter['value'],'both');
                    }
                    elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                        $this->db->where($filter['database_field']." ".$filter['condition'],(int)$filter['value']);
                    }
                }
            }
        }
        //////////advanced filters end ///////////////
        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        $this->db->select("*")->from("($new_query) as unionTable");
        foreach($data['adv_union_filters'] as $Unionfilter){
            if($Unionfilter['field_type']=='drop_down'){
                $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
            }
            elseif($Unionfilter['field_type']=='date'){
                $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
            }
            elseif($Unionfilter['field_type']=='numeric_text'||$Unionfilter['field_type']=='free_text'){
                if($Unionfilter['condition']=='like'){
                    $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
                }
                elseif($Unionfilter['condition']=='<' || $Unionfilter['condition']=='>'|| $Unionfilter['condition']=='=' ){
                    $this->db->where($Unionfilter['database_field']." ".$Unionfilter['condition'],(int)$Unionfilter['value']);
                }
            }
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
      
        $result = $this->db->get();
        // return $query->result_array(); 
        return array('total_records'=>$count_result,'data'=>$result->result_array());

    }
}
