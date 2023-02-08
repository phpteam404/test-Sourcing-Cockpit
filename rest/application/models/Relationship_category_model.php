<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Relationship_category_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function RelationshipCategoryList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('relationship_category r');
        $this->db->join('relationship_category_language l','r.id_relationship_category=l.relationship_category_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['customer_id']))
            $this->db->where('r.customer_id',$data['customer_id']);
        if(isset($data['relationship_category_status']))
            $this->db->where('r.relationship_category_status',$data['relationship_category_status']);
        if(isset($data['id_relationship_category']))
            $this->db->where('r.id_relationship_category',$data['id_relationship_category']);
        if(isset($data['id_relationship_category_array']))
            $this->db->where_in('r.id_relationship_category',$data['id_relationship_category_array']);
        if(isset($data['can_review'])){
            $this->db->where('r.can_review',$data['can_review']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('r.relationship_category_quadrant', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.relationship_category_name like "%'.$data['search'].'%"
            or r.relationship_category_quadrant like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('r.id_relationship_category','DESC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function addRelationshipCategory($data)
    {
        $this->db->insert('relationship_category', $data);
        return $this->db->insert_id();
    }

    public function addRelationshipCategoryLanguage($data)
    {
        $this->db->insert('relationship_category_language', $data);
        return $this->db->insert_id();
    }
    public function addProviderRelationshipCategory($data)
    {
        $this->db->insert('provider_relationship_category', $data);
        return $this->db->insert_id();
    }

    public function addProviderRelationshipCategoryLanguage($data)
    {
        $this->db->insert('provider_relationship_category_language', $data);
        return $this->db->insert_id();
    }

    public function getRelationshipCategory($data)
    {
        $this->db->select('*');
        $this->db->from('relationship_category r');
        $this->db->join('relationship_category_language l','r.id_relationship_category=l.relationship_category_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['customer_id']))
            $this->db->where('r.customer_id',$data['customer_id']);
        if(isset($data['id_relationship_category']))
            $this->db->where('r.id_relationship_category',$data['id_relationship_category']);
        if(isset($data['id_relationship_category_not']))
            $this->db->where('r.id_relationship_category !=',$data['id_relationship_category_not']);
        if(isset($data['relationship_category_quadrant']))
            $this->db->where('r.relationship_category_quadrant',$data['relationship_category_quadrant']);
        if(isset($data['relationship_category_status']))
            $this->db->where('r.relationship_category_status',$data['relationship_category_status']);
        if(isset($data['can_review']))
            $this->db->where('r.can_review',$data['can_review']);
        if(isset($data['status']))
            $this->db->where('r.relationship_category_status',$data['status']);

        $this->db->order_by('l.relationship_category_name','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }


    public function getProviderRelationshipCategory($data){
        $this->db->select('*');
        $this->db->from('provider_relationship_category pr');
        $this->db->join('provider_relationship_category_language pl','pr.id_provider_relationship_category = pl.provider_relationship_category_id','left');
        if(isset($data['language_id']))
           $this->db->where('pl.language_id',$data['language_id']);
        if(isset($data['customer_id']))
           $this->db->where('pr.customer_id',$data['customer_id']);
        if(isset($data['id_provider_relationship_category']))
           $this->db->where('pr.id_relationship_category',$data['id_provider_relationship_category']);
        if(isset($data['id_provider_relationship_category_not']))
           $this->db->where('pr.id_provider_relationship_category !=',$data['id_provider_relationship_category_not']);
        if(isset($data['provider_relationship_category_quadrant']))
            $this->db->where('pr.provider_relationship_category_quadrant',$data['provider_relationship_category_quadrant']);
        if(isset($data['provider_relationship_category_status']))
            $this->db->where('pr.provider_relationship_category_status',$data['provider_relationship_category_status']);
        if(isset($data['can_review']))
            $this->db->where('pr.can_review',$data['can_review']);
        if(isset($data['status']))
            $this->db->where('pr.relationship_category_status',$data['status']);
        $this->db->order_by('pl.relationship_category_name','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function updateRelationshipCategory($data)
    {
        $this->db->where('id_relationship_category', $data['id_relationship_category']);
        $this->db->update('relationship_category', $data);
        return 1;
    }

    public function updateProviderReltionshipCategory($data){
        $this->db->where('id_provider_relationship_category',$data['id_provider_relationship_category']);
        $this->db->update('provider_relationship_category',$data);
    }

    public function updateRelationshipCategoryLanguage($data)
    {
        $this->db->where('id_relationship_category_language', $data['id_relationship_category_language']);
        $this->db->update('relationship_category_language', $data);
        return 1;
    }

    public function updateProviderRelationshipCategoryLanguage($data){
        $this->db->where('id_provider_relationship_category_language',$data['id_provider_relationship_category_language']);
        $this->db->update('provider_relationship_category_language',$data);
    }

    public function RelationshipClassificationList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('relationship_classification rc');
        $this->db->join('relationship_classification_language l','rc.id_relationship_classification=l.relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['classification_status']))
            $this->db->where('rc.classification_status',$data['classification_status']);
        if(isset($data['customer_id']))
            $this->db->where('rc.customer_id',$data['customer_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.classification_name like "%'.$data['search'].'%")');*/
        if(isset($data['parent_classification_id']))
            $this->db->where('rc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['parent_classification_id_not']))
            $this->db->where('rc.parent_classification_id !=',$data['parent_classification_id_not']);
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('rc.id_relationship_classification','DESC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getRelationshipClassification($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('relationship_classification rc');
        $this->db->join('relationship_classification_language l','rc.id_relationship_classification=l.relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.classification_name like "%'.$data['search'].'%")');*/
        if(isset($data['parent_classification_id']))
            $this->db->where('rc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['customer_id']))
            $this->db->where('rc.customer_id',$data['customer_id']);
        if(isset($data['classification_position']))
            $this->db->where('rc.classification_position',$data['classification_position']);
        if(isset($data['classification_status']))
            $this->db->where('rc.classification_status',$data['classification_status']);
        if(isset($data['id_relationship_classification_not']))
            $this->db->where('rc.id_relationship_classification !=',$data['id_relationship_classification_not']);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getProviderRelationshipClassification($data){
        $this->db->select('*');
        $this->db->from('provider_relationship_classification prc');
        $this->db->join('provider_relationship_classification_language pcl','prc.id_provider_relationship_classification =pcl.provider_relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('pcl.language_id',$data['language_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('pcl.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['parent_provider_relationship_classification_id']))
        $this->db->where('prc.parent_provider_relationship_classification_id',$data['parent_provider_relationship_classification_id']);
        if(isset($data['customer_id']))
           $this->db->where('prc.customer_id',$data['customer_id']);
        if(isset($data['classification_position']))
           $this->db->where('prc.classification_position',$data['classification_position']);
        if(isset($data['classification_status']))
           $this->db->where('prc.classification_status',$data['classification_status']);
        if(isset($data['id_provider_relationship_classification_not']))
           $this->db->where('prc.id_provider_relationship_classification !=',$data['id_provider_relationship_classification_not']);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function addRelationshipClassification($data)
    {
        $this->db->insert('relationship_classification', $data);
        return $this->db->insert_id();
    }


    public function addRelationshipClassificationLanguage($data)
    {
        $this->db->insert('relationship_classification_language', $data);
        return $this->db->insert_id();
    }
    public function addProviderRelationshipClassification($data)
    {
        $this->db->insert('provider_relationship_classification', $data);
        return $this->db->insert_id();
    }

    public function addProviderRelationshipClassificationLanguage($data)
    {
        $this->db->insert('provider_relationship_classification_language', $data);
        return $this->db->insert_id();
    }

    public function updateRelationshipClassification($data)
    {
        $this->db->where('id_relationship_classification', $data['id_relationship_classification']);
        $this->db->update('relationship_classification', $data);
        return 1;
    }

    public function updateProviderReltionshipclassification($data){
        $this->db->where('id_provider_relationship_classification',$data['id_provider_relationship_classification']);
        $this->db->update('provider-relationship_classifiction',$data);
        return 1;
    }

    public function updateProviderRelationshipClassifiction($data){
        $this->db->where('id_provider_relationship_classification',$data['id_provider_relationship_classification']);
        $this->db->update('provider_relationship_classification',$data);
        return 1;
    }

    public function updateRelationshipClassificationLanguage($data)
    {
        $this->db->where('id_relationship_classification_language', $data['id_relationship_classification_language']);
        $this->db->update('relationship_classification_language', $data);
        return 1;
    }

    public function updateProviderRelationshipClassificationLanguage($data){
        $this->db->where('id_provider_relationship_classification_language',$data['id_provider_relationship_classification_language']);
        $this->db->update('provider_relationship_classification_language',$data);
    }

    public function deleteClassificationLanguage($data)
    {
        $this->db->where('relationship_classification_id',$data['id_relationship_classification']);
        $this->db->delete('relationship_classification_language');
    }

    public function deleteClassification($data)
    {
        $this->db->where('id_relationship_classification',$data['id_relationship_classification']);
        $this->db->delete('relationship_classification');
    }

    public function getClassificationValue($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('relationship_classification rc');
        $this->db->join('relationship_classification_language l','rc.id_relationship_classification=l.relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.classification_name like "%'.$data['search'].'%")');*/
        if(isset($data['parent_classification_id']))
            $this->db->where('rc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['classification_position']))
            $this->db->where('rc.classification_position',$data['classification_position']);
        if(isset($data['classification_status']))
            $this->db->where('rc.classification_status',$data['classification_status']);
        if(isset($data['id_relationship_classification_not']))
            $this->db->where('rc.id_relationship_classification !=',$data['id_relationship_classification_not']);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getProviderClassificationValue($data){
        $this->db->select('*');
        $this->db->from('provider_relationship_classification prc');
        $this->db->join('provider_relationship_classification_language prcl','prc.id_provider_relationship_classification=prcl.provider_relationship_classification_id','left');
        if(isset($data['language_id']))
        $this->db->where('prcl.language_id',$data['language_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('prcl.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        if(isset($data['parent_classification_id']))
        $this->db->where('prc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['classification_position']))
            $this->db->where('prc.classification_position',$data['classification_position']);
        if(isset($data['classification_status']))
            $this->db->where('prc.classification_status',$data['classification_status']);
        if(isset($data['id_provider_relationship_classification_not']))
            $this->db->where('prc.id_provider_relationship_classification !=',$data['id_provider_relationship_classification_not']);
            
        $query = $this->db->get();
            return $query->result_array();
    }

    public function getRelationshipClassificationForContract($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('rc.*,l.*');
        $this->db->from('relationship_classification r');
        $this->db->join('relationship_classification rc','r.id_relationship_classification=rc.parent_classification_id','left');
        $this->db->join('relationship_classification_language l','rc.id_relationship_classification=l.relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('l.language_id',$data['language_id']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('l.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.classification_name like "%'.$data['search'].'%")');*/
        if(isset($data['parent_classification_id']))
            $this->db->where('rc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['customer_id']))
            $this->db->where('rc.customer_id',$data['customer_id']);
        if(isset($data['classification_status']))
            $this->db->where('rc.classification_status',$data['classification_status']);
        if(isset($data['id_relationship_classification_not']))
            $this->db->where('rc.id_relationship_classification !=',$data['id_relationship_classification_not']);
        if(isset($data['classification_position']))
            $this->db->where('r.classification_position',$data['classification_position']);

        $query = $this->db->get();
        return $query->result_array();
    }
    public function ProviderRelationshipCategoryList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('`provider_relationship_category` `pr`');
        $this->db->join('provider_relationship_category_language pl','pr.id_provider_relationship_category=pl.provider_relationship_category_id','left');
        if(isset($data['language_id']))
            $this->db->where('pl.language_id',$data['language_id']);
        if(isset($data['customer_id']))
            $this->db->where('pr.customer_id',$data['customer_id']);
        if(isset($data['provider_relationship_category_status']))
            $this->db->where('pr.provider_relationship_category_status',$data['provider_relationship_category_status']);
        if(isset($data['id_provider_relationship_category']))
            $this->db->where('pr.id_provider_relationship_category',$data['id_provider_relationship_category']);
        if(isset($data['id_provider_relationship_category_array']))
            $this->db->where_in('pr.id_provider_relationship_category',$data['id_provider_relationship_category_array']);
        if(isset($data['can_review'])){
            $this->db->where('pr.can_review',$data['can_review']);
        }
        if(!empty($data['status'])){
            $this->db->where('pr.provider_relationship_category_status',$data['status']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('pl.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('pr.provider_relationship_category_quadrant', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.relationship_category_name like "%'.$data['search'].'%"
            or r.relationship_category_quadrant like "%'.$data['search'].'%")');*/
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('pr.id_provider_relationship_category','DESC');
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }
    public function ProviderRelationshipClassificationList($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('*');
        $this->db->from('provider_relationship_classification prc');
        $this->db->join('provider_relationship_classification_language prcl','prc.id_provider_relationship_classification=prcl.provider_relationship_classification_id','left');
        if(isset($data['language_id']))
            $this->db->where('prcl.language_id',$data['language_id']);
        if(isset($data['classification_status']))
            $this->db->where('prc.classification_status',$data['classification_status']);
        if(isset($data['customer_id']))
            $this->db->where('prc.customer_id',$data['customer_id']);
        if(isset($data['classification_position'])){
            $this->db->where('prc.classification_position',$data['classification_position']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('prcl.classification_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(l.classification_name like "%'.$data['search'].'%")');*/
        if(isset($data['parent_classification_id']))
            $this->db->where('prc.parent_classification_id',$data['parent_classification_id']);
        if(isset($data['parent_classification_id_not']))
            $this->db->where('prc.parent_classification_id !=',$data['parent_classification_id_not']);
        /* results count start */
        $all_clients_db = clone $this->db;
        $all_clients_count = $all_clients_db->count_all_results();
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        elseif(isset($data['withOutOrder']) && ($data['withOutOrder']==true))
        { }
        else{
            $this->db->order_by('prc.id_provider_relationship_classification','DESC');
        }
        $query = $this->db->get();
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }
}