<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(1);
class Contract_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }
    

    public function getContractList1($data)
    { 
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('c.*,bu.bu_name,p.provider_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1|| $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }
        
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        // if(isset($data['created_date']))//substr($data['created_date'],0,10);
        //     $this->db->where('DATE(c.created_on)',$data['created_date']);
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        else 
            $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $query = $this->db->get();
        // echo 
        $all_clients_count = $query->num_rows();

        /* results count end */

        $this->db->select('c.*,CONCAT(u1.first_name," ",u1.last_name) bu_owner,CONCAT(u2.first_name," ",u2.last_name) bu_delegate,c.provider_name provider_id,c.id_contract contract_id,bu.bu_name,p.provider_name,cu.currency_name,rc.classification_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');     
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }
        
        if(isset($data['business_unit_id'])  && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        // if(isset($data['created_date']))
        //     $this->db->where('DATE(c.created_on)',$data['created_date']);
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        else 
            $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('p.provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('p.provider_name,c.contract_name','asc');
        $query = $this->db->get();
        //echo 
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    function getContractList($data){
        // print_r($data);exit;
        /**
         * Start 
         * select query for contract and contract_reviews 
         */
        $this->db->select('0 as `is_workflow`,if(c.can_review=0,"0","1") type_sort_key, if(t.template_name is not null, "", "") as workflow_id, `t`.`template_name`, `t`.`id_template`, `c`.*, CONCAT(u1.first_name, " ", u1.last_name) bu_owner, CONCAT(u2.first_name, " ", u2.last_name) bu_delegate, `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`, `p`.`provider_name` as providerName, `cu`.`currency_name`, `rc`.`classification_name`, `rcl`.`relationship_category_name`, (select MAX(id_contract_review) from contract_review where contract_id = c.id_contract and is_workflow = 0) id_contract_review, IF(IFNULL(c.parent_contract_id, 0)>0, "sub_agreement", IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0, "parent_agreement", "agreement")) as agreement_type, "0" as id_contract_workflow,crv.contract_review_status,c.contract_value as Projected_value,crv2.validation_status,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,cw.parent_id as workflow_parent_id,"" as subTaskproviderName,(select count(*) from contract WHERE parent_contract_id=c.id_contract and is_deleted=0) as is_parent,if(c.parent_contract_id>0,1,0) as is_sub,"review" as typeOfActivity,(SELECT workflow_name  FROM calender cal WHERE FIND_IN_SET(c.business_unit_id,cal.bussiness_unit_id) AND ( FIND_IN_SET(c.relationship_category_id,cal.relationship_category_id) or cal.relationship_category_id="") AND ( FIND_IN_SET(c.provider_name,cal.provider_id) or cal.provider_id="") AND ( FIND_IN_SET(c.id_contract,cal.contract_id) or cal.contract_id="") AND CURDATE() <= cal.date AND cal.is_workflow =0 AND cal.status = 1 ORDER BY cal.id_calender ASC limit 1) as ActivityName ,(SELECT date FROM calender cal WHERE FIND_IN_SET(c.business_unit_id,cal.bussiness_unit_id) AND ( FIND_IN_SET(c.relationship_category_id,cal.relationship_category_id) or cal.relationship_category_id="") AND ( FIND_IN_SET(c.provider_name,cal.provider_id) or cal.provider_id="") AND ( FIND_IN_SET(c.id_contract,cal.contract_id) or cal.contract_id="") AND CURDATE() <= cal.date AND cal.is_workflow =0 AND cal.status = 1 ORDER BY cal.id_calender ASC limit 1) as completedBy,(SELECT GROUP_CONCAT(ml.module_name) FROM module m  LEFT JOIN module_language ml on m.id_module=ml.module_id WHERE m.contract_review_id=crv2.id_contract_review)as module_names');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6|| $this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(crv2.id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("`contract` `c`");
        $this->db->join("provider p","p.id_provider=c.provider_name","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join("`currency` `cu`","`c`.`currency_id`=`cu`.`id_currency`","left");
        $this->db->join("`relationship_category_language` `rcl`","`c`.`relationship_category_id`=`rcl`.`relationship_category_id` and `language_id`=1","left");
        $this->db->join("`contract_review` `crv`"," `crv`.`contract_id`=`c`.`id_contract` and crv.is_workflow=0","left");
        $this->db->join("`relationship_classification_language` `rc` "," `rc`.`relationship_classification_id`=`c`.`classification_id`","left");
        $this->db->join("`user` `u1` "," `c`.`contract_owner_id`=`u1`.`id_user`","left");
        $this->db->join("`user` `u2` "," `c`.`delegate_id`=`u2`.`id_user`","left");
        $this->db->join("`template` `t` "," `c`.`template_id`=`t`.`id_template`","left");
        $this->db->join("`contract_workflow` `cw`","`c`.`id_contract`=`cw`.`contract_id`","left");
        $this->db->join("contract_review crv2","crv2.id_contract_review=(select MAX(id_contract_review) from contract_review where contract_id = c.id_contract and is_workflow = 0)","left");
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) )
        {
            $this->db->join("document d","c.id_contract=d.reference_id AND d.reference_type = 'project' AND d.module_type = 'project' AND d.document_status = 1","left");
            $this->db->join("document dc","c.id_contract=dc.reference_id AND dc.reference_type = 'contract' AND dc.module_type = 'contract_review' AND dc.document_status = 1","left");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("CONCAT(IFNULL(GROUP_CONCAT(d.document_name),'') ,if(GROUP_CONCAT(d.document_name) IS NOT NULL And GROUP_CONCAT(dc.document_name) is NOT NULL , ',','') , IFNULL(GROUP_CONCAT(dc.document_name),'')) as document_names");
            
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("CONCAT(IFNULL(GROUP_CONCAT(d.document_source),'') ,if(GROUP_CONCAT(d.document_source) IS NOT NULL And GROUP_CONCAT(dc.document_source) is NOT NULL , ',','') , IFNULL(GROUP_CONCAT(dc.document_source),'')) as document_urls");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('expertContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(ue.id_user) from contract_user cuf LEFT JOIN user ue ON cuf.user_id=ue.id_user WHERE crv2.id_contract_review = cuf.contract_review_id and cuf.status=1  and ue.contribution_type=0 ) as expertContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('validatorContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(uv.id_user) from contract_user cuf LEFT JOIN user uv ON cuf.user_id=uv.id_user WHERE crv2.id_contract_review = cuf.contract_review_id and cuf.status=1  and uv.contribution_type=1 ) as validatorContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('relationContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(ur.id_user) from contract_user cuf LEFT JOIN user ur ON cuf.user_id=ur.id_user WHERE crv2.id_contract_review = cuf.contract_review_id and cuf.status=1  and ur.contribution_type=3 ) as relationContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('lastChange', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(SELECT IFNULL(max(conr.updated_on), max(conr.created_on)) FROM contract_review conr JOIN contract con ON conr.contract_id=con.id_contract WHERE conr.contract_id = c.id_contract  AND conr.contract_review_status = 'finished' AND conr.is_workflow = 0 AND con.is_deleted = '0' GROUP BY conr.id_contract_review ORDER BY conr.id_contract_review DESC limit 1) as lastChange");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('RelationId', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("c.provider_name as RelationId");
        }
        if(!empty($data['validation_filter_status'])){
            $this->db->join("contract_user cus","crv2.id_contract_review=cus.contract_review_id","left");
            $this->db->join("user u4","cus.user_id=u4.id_user","left");
        }
        if(!empty($data['can_review'])){
            $this->db->where('c.can_review',$data['can_review']);
        }
        $this->db->where('c.type','contract');
        //search option 
        // if($data['project_workflow_type']=='parent'){
        //     $this->db->where('cw.parent_id','0');
        // }
        if(!empty($data['validation_filter_status'])){
            if($this->session_user_info->contribution_type==1 || $data['validation_filter_contribution_type']==1)
            $this->db->where('u4.contribution_type',1);
            $this->db->where_in('crv2.validation_status',$data['validation_filter_status']);
            $this->db->where('cus.status',1);
            if(!empty($data['contributor_user_id'])){
                $this->db->where("cus.user_id",$data['contributor_user_id']);
            }
        }
        if($data['project_workflow_type']=='child'){
            $this->db->where('cw.parent_id',$data['parent_workflow_id']);
        }
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                // $this->db->group_start();
                // $this->db->like('c.contract_name', $data['search'], 'both');
                // $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                // $this->db->or_like('p.provider_name', $data['search'], 'both');
                // $this->db->or_like('bu.bu_name', $data['search'], 'both');
                // $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }//end if search

        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if($data['contribution_type']=='my_activities' && in_array($this->session_user_info->user_role_id,array(2,3,4))){
            $this->db->where_in('crv.contract_review_status',array('pending review', 'review in progress'));
        }
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['relationship_category_id']) && !is_array($data['relationship_category_id']))
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && $data['parent_contract_id']>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        // else if(isset($data['get_all_records'])){

        // }else
        //     $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        //ended conditions
        // advance filter start    
        foreach($data['adv_filters'] as $filter){
            if($filter['field_type']=='drop_down'){
                if($filter['database_field']=='activity_status')
                {
                    $this->db->where_in('c.contract_status',explode(',',$filter['value']));
                }
                elseif($filter['database_field']=='validation_status')
                {
                    continue;
                }
                else
                {
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
            }
            elseif($filter['field_type']=='date'){
                $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
            }
            elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
                if($filter['condition']=='like'){
                    $this->db->like($filter['database_field'],$filter['value'],'both');
                }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        // advance filter end  

        $this->db->group_by('c.id_contract');

        //first sub query
        $subQuery1 = $this->db->_compile_select();
        //end contract and contract_review 
        // print_r($subQuery1);exit;
        //restting select query for writing new query
        $this->db->_reset_select();
        $this->db->select("if(cw.workflow_name is null, 0, 1)  as is_workflow,'2' type_sort_key, `cw`.`workflow_id` as `workflow_id`, `cw`.`workflow_name` as `template_name`, `cw`.`workflow_id` as `id_template`, `c`.*, CONCAT(u1.first_name, ' ', u1.last_name) bu_owner, CONCAT(u2.first_name, ' ', u2.last_name) bu_delegate, `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`,  `p`.`provider_name` as providerName, `cu`.`currency_name`, `rc`.`classification_name`, `rcl`.`relationship_category_name`, (select MAX(id_contract_review) from contract_review where contract_workflow_id = cw.id_contract_workflow) id_contract_review, IF(IFNULL(c.parent_contract_id, 0)>0, 'sub_agreement', IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0, 'parent_agreement', 'agreement')) as agreement_type,cw.id_contract_workflow as id_contract_workflow,crv.contract_review_status,c.contract_value as Projected_value,crvw.validation_status,IF(ctry.country_name!='',CONCAT(bu.bu_name,' - ',ctry.country_name),bu.bu_name) as bu_name,cw.parent_id as workflow_parent_id,pr.provider_name as subTaskproviderName,IF(c.type='project',(SELECT COUNT(*) FROM contract_workflow WHERE parent_id=cw.id_contract_workflow and status=1),0) as is_parent,if(cw.parent_id>0,1,0) as is_sub,,'task' as typeOfActivity,workflow_name as ActivityName,cw.Execute_by as completedBy,(SELECT GROUP_CONCAT(ml.module_name) FROM module m  LEFT JOIN module_language ml on m.id_module=ml.module_id WHERE m.contract_review_id=crvw.id_contract_review)as module_names");
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6 ||$this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(crvw.id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("contract c");
        $this->db->join("provider p","`p`.`id_provider` = `c`.`provider_name`","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join("`currency` `cu`","`c`.`currency_id`=`cu`.`id_currency`","left");
        $this->db->join("`relationship_category_language` `rcl`","`c`.`relationship_category_id`=`rcl`.`relationship_category_id` and `language_id`=1","left");
        //$this->db->join("`contract_review` `crv`", "`crv`.`contract_id`=`c`.`id_contract`","left");
        $this->db->join("`relationship_classification_language` `rc`","`rc`.`relationship_classification_id`=`c`.`classification_id`","left");
        $this->db->join("`user` `u1`","`c`.`contract_owner_id`=`u1`.`id_user`","left");
        $this->db->join("`user` `u2`","`c`.`delegate_id`=`u2`.`id_user`","left");
        $this->db->join("`contract_workflow` `cw`","`c`.`id_contract`=`cw`.`contract_id`","left");
        $this->db->join("`contract_review` `crv`","`crv`.`contract_workflow_id`=`cw`.`id_contract_workflow`","left");
        $this->db->join("`contract_review` `crvw`","crvw.id_contract_review=(select MAX(id_contract_review) from contract_review where contract_workflow_id = cw.id_contract_workflow)","left");
        $this->db->join("user u3","u3.id_user=cw.provider_id","left");
        $this->db->join("provider pr","u3.provider=pr.id_provider","left");
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) )
        {
            $this->db->join("document d","c.id_contract=d.reference_id AND d.reference_type = 'project' AND d.module_type = 'project' AND d.document_status = 1","left");
            $this->db->join("document dc","c.id_contract=dc.reference_id AND dc.reference_type = 'contract' AND dc.module_type = 'contract_review' AND dc.document_status = 1","left");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("CONCAT(IFNULL(GROUP_CONCAT(d.document_name),'') ,if(GROUP_CONCAT(d.document_name) IS NOT NULL And GROUP_CONCAT(dc.document_name) is NOT NULL , ',','') , IFNULL(GROUP_CONCAT(dc.document_name),'')) as document_names");
            
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("CONCAT(IFNULL(GROUP_CONCAT(d.document_source),'') ,if(GROUP_CONCAT(d.document_source) IS NOT NULL And GROUP_CONCAT(dc.document_source) is NOT NULL , ',','') , IFNULL(GROUP_CONCAT(dc.document_source),'')) as document_urls");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('expertContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(ue.id_user) from contract_user cuf LEFT JOIN user ue ON cuf.user_id=ue.id_user WHERE crvw.id_contract_review = cuf.contract_review_id and cuf.status=1  and ue.contribution_type=0 ) as expertContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('validatorContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(uv.id_user) from contract_user cuf LEFT JOIN user uv ON cuf.user_id=uv.id_user WHERE crvw.id_contract_review = cuf.contract_review_id and cuf.status=1  and uv.contribution_type=1 ) as validatorContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('relationContributer', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(ur.id_user) from contract_user cuf LEFT JOIN user ur ON cuf.user_id=ur.id_user WHERE crvw.id_contract_review = cuf.contract_review_id and cuf.status=1  and ur.contribution_type=3 ) as relationContributer");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('lastChange', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("if(c.type='contract',(SELECT IFNULL(max(conr.updated_on), max(conr.created_on)) as review_on FROM contract_review conr JOIN contract con ON conr.contract_id=con.id_contract  WHERE conr.contract_id = con.id_contract AND conr.contract_workflow_id = id_contract_workflow AND conr.contract_review_status = 'finished' AND conr.is_workflow = '1' AND con.is_deleted = '0' GROUP BY conr.id_contract_review ORDER BY conr.id_contract_review DESC limit 1) ,NULL) as lastChange");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('RelationId', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("if(c.type='project',(select GROUP_CONCAT(pp.provider_id) from project_providers pp WHERE pp.project_id=c.id_contract and pp.is_linked=1),c.provider_name) as RelationId");
        }
        if(!empty($data['validation_filter_status'])){
            $this->db->join("contract_user cus","crvw.id_contract_review=cus.contract_review_id","left");
            $this->db->join("user u4","cus.user_id=u4.id_user","left");
        }
        if(!empty($data['can_review'])){
            $this->db->where_in('c.can_review',array('0','1'));
        }
        if(!empty($data['validation_filter_status'])){
            if($this->session_user_info->contribution_type==1 || $data['validation_filter_contribution_type']==1)
            $this->db->where('u4.contribution_type',1);
            $this->db->where('cus.status',1);
            // $this->db->where("crv.contract_review_status!='finished'");
            $this->db->where_in('crvw.validation_status',$data['validation_filter_status']);
            $this->db->where('crvw.contract_review_status!="finished"');
            if(!empty($data['contributor_user_id'])){
                $this->db->where("cus.user_id",$data['contributor_user_id']);
            }
            
        }
        // $this->db->where('cw.parent_id=0');
        if($data['project_workflow_type']=='parent'){
            // $this->db->where('cw.parent_id','0');
        }
        if($data['project_workflow_type']=='child'){
            $this->db->where('cw.parent_id',$data['parent_workflow_id']);
        }
        //search option 
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                // $this->db->group_start();
                // $this->db->like('c.contract_name', $data['search'], 'both');
                // $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                // $this->db->or_like('p.provider_name', $data['search'], 'both');
                // $this->db->or_like('bu.bu_name', $data['search'], 'both');
                // $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }//end if search

        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if($data['contribution_type']=='my_activities' && in_array($this->session_user_info->user_role_id,array(2,3,4))){
            $this->db->where_in('crv.contract_review_status',array('pending workflow', 'workflow in progress'));
        }
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('cw.workflow_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('cw.workflow_status',$data['contract_status']);
        if(isset($data['relationship_category_id']) && !is_array($data['relationship_category_id']))
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && $data['parent_contract_id']>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        // else if(isset($data['get_all_records'])){

        // }else            
        //     $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        //ended conditions
        //in second sub query, 
        if(isset($data['workflowName_Null']))
            $this->db->where("cw.workflow_name !=","");

        $this->db->where("cw.status","1");
        $this->db->group_by('cw.id_contract_workflow');    
         // advance filter start    
         foreach($data['adv_filters'] as $filter){
            if($filter['field_type']=='drop_down'){
                if($filter['database_field']=='activity_status')
                {
                    $this->db->where_in('cw.workflow_status',explode(',',$filter['value']));
                }
                elseif($filter['database_field']=='validation_status')
                {
                    continue;
                }
                else
                {
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
            }
            elseif($filter['field_type']=='date'){
                $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
            }
            elseif($filter['field_type']=='free_text'||$filter['field_type']=='numeric_text'){
                if($filter['condition']=='like'){
                    $this->db->like($filter['database_field'],$filter['value'],'both');
                }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        // advance filter end       

        //second sub query
        $subQuery2 = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select("*")->from("($subQuery1 UNION $subQuery2) as unionTable");

        //Activity filter filters reviews & workflows
        if(isset($data['activity_filter'])){
            if($data['activity_filter'] > 1)
                $this->db->where('type_sort_key',2);//Workflows
            else
                $this->db->where_in('type_sort_key',array(0,1));//Reviews
        } 
        if(isset($data['validation_filter_status'])){
            // $this->db->where_in('validation_status',$data['validation_filter_status']);
            // $this->db->where('contribution_type',1);
        }
        // if($data['parent_contract_id'] > 0)
        //Can_access filters the records user have access to
        if(isset($data['can_access']) && $data['can_access'] > 0 && $this->session_user_info->user_role_id != 8)
            $this->db->where('can_access',$data['can_access']);
        if($data['parent_contract_id']>0){
            $this->db->where('is_workflow',0);
        }
        if(isset($data['type']) && $data['type']=='action_items'){
            $this->db->group_by('contract_id');          
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='sub'){
            $this->db->where('is_sub>0');
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='parent'){
            $this->db->where('is_parent>0');
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='single'){
            $this->db->where('is_parent',0);
            $this->db->where('is_sub',0);
        }
        // advance union fiter start
        foreach($data['adv_union_filters'] as $Unionfilter){
            if($Unionfilter['field_type']=='drop_down'){
                if($Unionfilter['database_field']=='hierarchy')
                {
                    $this->db->group_start();
                    foreach(explode(',',$Unionfilter['value']) as $hirerarchy)
                    {
                        if($hirerarchy=='sub'){
                            $this->db->or_where('is_sub>0');
                        }
                        if($hirerarchy=='parent'){
                            $this->db->or_where('is_parent>0');
                        }
                        if($hirerarchy=='single'){
                            $this->db->or_group_start();
                            $this->db->where('is_parent',0);
                            $this->db->where('is_sub',0);
                            $this->db->group_end();
                        }
                    }
                    $this->db->group_end();
                }
                elseif($Unionfilter['database_field']=='expertContributer'||$Unionfilter['database_field']=='validatorContributer'||$Unionfilter['database_field']=='relationContributer'||$Unionfilter['database_field']=='RelationId')
                {
                    $databasefield = $Unionfilter['database_field'];
                    $this->db->group_start();
                    foreach(explode(',',$Unionfilter['value']) as $fieldValue)
                    {
                        $this->db->or_where("FIND_IN_SET($fieldValue,$databasefield) > 0");
                    }
                    $this->db->group_end();
                }
                else
                {
                    $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
                }
                
            }
            elseif($Unionfilter['field_type']=='date'){
                $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
            }
            elseif($Unionfilter['field_type']=='numeric_text' || $Unionfilter['field_type']=='free_text'){
                if($Unionfilter['condition']=='like'){
                    $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
                }
                elseif($Unionfilter['condition']=='<' || $Unionfilter['condition']=='>'|| $Unionfilter['condition']=='=' ){
                    $this->db->where($Unionfilter['database_field']." ".$Unionfilter['condition'],$Unionfilter['value']);
                }
            }
        }
        // advance union fiter end

        //search is moved from top to down because we are search in dynamic coloums also so we chabged here
        if(isset($data['search'])&&(!$data['advancedsearch_get']))
        {
            $this->db->group_start();
            $this->db->like('contract_name', $data['search'], 'both');
            $this->db->or_like('relationship_category_name', $data['search'], 'both');
            $this->db->or_like('provider_name', $data['search'], 'both');
            $this->db->or_like('bu_name', $data['search'], 'both');
            $this->db->or_like('type', $data['search'], 'both');
            $this->db->or_like('typeOfActivity', $data['search'], 'both');
            $this->db->group_end();
        }
        // print_r($data);exit
        //from current query 
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();
        // echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();

        //pagenation
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
            if(isset($data['type']) && $data['type']!=='action_items'){
                $this->db->group_by('id_contract');          
            }
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('providerName',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('providerName,contract_name','asc');


        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        // print_r($data);exit;
        // print_r($data["parent_contract_id"]);exit;
        // if($data["parent_contract_id"]!=0){
            // echo    
        // }

        return array('total_records' => $count_result,'data' => $query->result_array());

    }

    public function getMyReviewList_backup($data)
    { 
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('c.*,bu.bu_name,p.provider_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }
        
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $all_clients_count = $query->num_rows();

        /* results count end */

        $this->db->select('c.*,CONCAT(u1.first_name," ",u1.last_name) bu_owner,CONCAT(u2.first_name," ",u2.last_name) bu_delegate,c.provider_name provider_id,c.id_contract contract_id,bu.bu_name,p.provider_name,cu.currency_name,rc.classification_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');        
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }
        
        if(isset($data['business_unit_id'])  && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('p.provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('p.provider_name,c.contract_name','asc');
        $query = $this->db->get();
        //echo 
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getMyReviewList($data)
    { 
        
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        
            $this->db->select('0 as is_workflow,if(t.template_name is not null ,"","") as workflow_id,  t.template_name,t.id_template ,c.*,CONCAT(u1.first_name," ",u1.last_name) bu_owner,CONCAT(u2.first_name," ",u2.last_name) bu_delegate,c.provider_name provider_id,c.id_contract contract_id,p.provider_name as providerName,cu.currency_name,rc.classification_name,rcl.relationship_category_name,MAX(crv.id_contract_review) id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,"0" as id_contract_workflow,crv.validation_status,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,0 as parent_id,"" as subTaskproviderName');
            $this->db->from('contract c');
            $this->db->join('provider p','p.id_provider = c.provider_name','left');
            $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
            $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
            $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
            $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
            $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
            $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');     
            $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
            $this->db->join('user u2','c.delegate_id=u2.id_user','left');
            $this->db->join('template t','c.template_id=t.id_template','left');
    
            if(isset($data['search']))
            {
                if(!$data['advancedsearch_get'])
                {
                    $this->db->group_start();
                    $this->db->like('c.contract_name', $data['search'], 'both');
                    $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    $this->db->or_like('p.provider_name', $data['search'], 'both');
                    $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    $this->db->or_like('c.type', $data['search'], 'both');
                    $this->db->group_end();
                }
                else
                {   
                    if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                    {
                        $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                        $this->db->group_start();
                        if(isset($data['advancedsearch_get']->contract_name))
                            $this->db->like('c.contract_name', $data['search'], 'both');
                        if(isset($data['advancedsearch_get']->relationship_category_name))
                            $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                        if(isset($data['advancedsearch_get']->bu_name))
                            $this->db->or_like('bu.bu_name', $data['search'], 'both');
                        if(isset($data['advancedsearch_get']->provider_name_search))
                            $this->db->or_like('p.provider_name', $data['search'], 'both');
                        if(isset($data['advancedsearch_get']->contract_value))
                            $this->db->or_like('c.contract_value',$data['search'],'both');//description
                        if(isset($data['advancedsearch_get']->description))
                            $this->db->or_like('c.description',$data['search'],'both');
                        if(isset($data['advancedsearch_get']->tag_option_value))
                            $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                        if(isset($data['advancedsearch_get']->owner))
                            $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                        if(isset($data['advancedsearch_get']->delegate))
                            $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                        if(isset($data['advancedsearch_get']->automatic_prolongation)){
                            if(strtolower($data['search'])=='yes'){
                                $this->db->or_like('c.auto_renewal','1','both');
                            }else if(strtolower($data['search'])=='no'){
                                $this->db->or_like('c.auto_renewal','0','both');
                            }else{
                                $this->db->or_like('c.auto_renewal','1','both');
                                $this->db->or_like('c.auto_renewal','0','both');
                            }
                        }
                        if(isset($data['advancedsearch_get']->classification))
                            $this->db->or_like('rc.classification_name',$data['search'],'both');
                        
                        $this->db->group_end();
                
                    }
          
                }
            }
            
            if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
                $this->db->where('c.business_unit_id',$data['business_unit_id']);
            if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
                $this->db->where('c.business_unit_id',$data['id_business_unit']);
            if(isset($data['customer_id']))
                $this->db->where('bu.customer_id',$data['customer_id']);        
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);        
            if(isset($data['contract_owner_id']))
                $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
            if(isset($data['delegate_id']))
                $this->db->where('c.delegate_id',$data['delegate_id']);
            if(isset($data['contract_status']) && !is_array($data['contract_status']))
                $this->db->where('crv.contract_review_status',$data['contract_status']);
            if(isset($data['contract_status']) && is_array($data['contract_status']))
                $this->db->where_in('crv.contract_review_status',array('pending review','review in progress'));
            if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
                $this->db->where('p.provider_name',$data['provider_name']);
            if(isset($data['provider_id']) && $data['provider_id']>0)
                $this->db->where('c.provider_name',$data['provider_id']);
            $this->db->where('c.can_review',1);
            $this->db->where('crv.is_workflow',0);
            if(isset($data['deleted'])){
    
            }
            else
                $this->db->where('c.is_deleted','0');
            $this->db->group_by('c.id_contract');
    
                $query1 = $this->db->get_compiled_select();
                $this->db->_reset_select();
                $this->db->select('if(cw.workflow_name is null,0,1)  as is_workflow,cw.workflow_id as workflow_id, cw.workflow_name as template_name,cw.workflow_id as id_template ,c.*,CONCAT(u1.first_name," ",u1.last_name) bu_owner,CONCAT(u2.first_name," ",u2.last_name) bu_delegate,c.provider_name provider_id,c.id_contract contract_id,p.provider_name as providerName,cu.currency_name,rc.classification_name,rcl.relationship_category_name,crv.id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,cw.id_contract_workflow as id_contract_workflow,crv.validation_status,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,cw.parent_id as parent_id,
                pr.provider_name as subTaskproviderName');
                $this->db->from('contract c');
                $this->db->join('provider p','p.id_provider = c.provider_name','left');
                $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
                $this->db->join('country ctry','bu.country_id=ctry.id_country','left');

                $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
                $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
                // $this->db->join('contract_review crv','crv.contract_id=c.id_contract','right');
                $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');     
                $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
                $this->db->join('user u2','c.delegate_id=u2.id_user','left');
                $this->db->join('contract_workflow cw','c.id_contract=cw.contract_id','left');
                $this->db->join('contract_review crv','crv.contract_workflow_id=cw.id_contract_workflow','left');
                $this->db->join("user u3","u3.id_user=cw.provider_id","left");
                $this->db->join("provider pr","u3.provider=pr.id_provider","left");
                if(isset($data['search']))
                {
                    if(!$data['advancedsearch_get'])
                    {
                        $this->db->group_start();
                        $this->db->like('c.contract_name', $data['search'], 'both');
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                        $this->db->or_like('c.type', $data['search'], 'both');
                        $this->db->group_end();
                    }
                    else
                    {   
                        if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                        {
                            $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                            $this->db->group_start();
                            if(isset($data['advancedsearch_get']->contract_name))
                                $this->db->like('c.contract_name', $data['search'], 'both');
                            if(isset($data['advancedsearch_get']->relationship_category_name))
                                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                            if(isset($data['advancedsearch_get']->bu_name))
                                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                            if(isset($data['advancedsearch_get']->provider_name_search))
                                $this->db->or_like('p.provider_name', $data['search'], 'both');
                            if(isset($data['advancedsearch_get']->contract_value))
                                $this->db->or_like('c.contract_value',$data['search'],'both');//description
                            if(isset($data['advancedsearch_get']->description))
                                $this->db->or_like('c.description',$data['search'],'both');
                            if(isset($data['advancedsearch_get']->tag_option_value))
                                $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                            if(isset($data['advancedsearch_get']->owner))
                                $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                            if(isset($data['advancedsearch_get']->delegate))
                                $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                            if(isset($data['advancedsearch_get']->automatic_prolongation)){
                                if(strtolower($data['search'])=='yes'){
                                    $this->db->or_like('c.auto_renewal','1','both');
                                }else if(strtolower($data['search'])=='no'){
                                    $this->db->or_like('c.auto_renewal','0','both');
                                }else{
                                    $this->db->or_like('c.auto_renewal','1','both');
                                    $this->db->or_like('c.auto_renewal','0','both');
                                }
                            }
                            if(isset($data['advancedsearch_get']->classification))
                                $this->db->or_like('rc.classification_name',$data['search'],'both');
                            
                            $this->db->group_end();
                    
                        }
              
                    }
                }
                
                if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
                    $this->db->where('c.business_unit_id',$data['business_unit_id']);
                if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
                    $this->db->where('c.business_unit_id',$data['id_business_unit']);
                if(isset($data['customer_id']))
                    $this->db->where('bu.customer_id',$data['customer_id']);        
                if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                    $this->db->where_in('c.business_unit_id', $data['business_unit_id']);        
                if(isset($data['contract_owner_id']))
                    $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
                if(isset($data['delegate_id']))
                    $this->db->where('c.delegate_id',$data['delegate_id']);
                if(isset($data['contract_status']) && !is_array($data['contract_status']))
                    $this->db->where('crv.contract_review_status',$data['contract_status']);
                if(isset($data['contract_status']) && is_array($data['contract_status']))
                    $this->db->where_in('crv.contract_review_status',array('pending workflow','workflow in progress'));
                if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
                    $this->db->where('p.provider_name',$data['provider_name']);
                if(isset($data['provider_id']) && $data['provider_id']>0)
                    $this->db->where('c.provider_name',$data['provider_id']);
                //$this->db->where('c.can_review',1);
                $this->db->where('crv.is_workflow',1);
                //$this->db->where_in('crv.contract_review_status', array('pending workflow','workflow in progress'));
    
                if(isset($data['deleted'])){
        
                }
                else
                    $this->db->where('c.is_deleted','0');
                    $this->db->where("cw.status","1");
                    $this->db->group_by('cw.id_contract_workflow');
                    
                    $query2 = $this->db->get_compiled_select();
                    $this->db->_reset_select();
                    $this->db->select("*")->from("($query1 UNION $query2) as unionTable");//print_r($query2);exit;
                    $count_result_db = clone $this->db;
                    $count_result = $count_result_db->get();//echo '<pre>'.$count_result_db->last_query();exit;
                    $count_result = $count_result->num_rows();
                 
                    if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
                    $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
            //print_r($data);exit;
                    if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
                    if($data['sort']['predicate']=='provider_name')
                    $this->db->order_by('provider_name',$data['sort']['reverse']);
                    else if($data['sort']['predicate']=='last_review')
                    $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
                    else 
                    $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
            }        
            else
                $this->db->order_by('contract_name','asc');
                $query = $this->db->get();//echo 
            return array('total_records' => $count_result,'data' => $query->result_array());
    }

    public function getMyContributionList_backup($data)
    { 
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('c.*,p.provider_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');
        $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        $this->db->join('module m', 'm.id_module=cur.module_id', '');
        $this->db->where('cur.user_id',$data['customer_user']);
        $this->db->where('cur.status','1');
        $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');

        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }

        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $all_clients_count = $query->num_rows();

        /* results count end */

        $this->db->select('c.*,c.provider_name provider_id,c.id_contract contract_id,bu.bu_name,p.provider_name,cu.currency_name,rc.classification_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');   
        $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        $this->db->join('module m', 'm.id_module=cur.module_id', '');
        $this->db->where('cur.user_id',$data['customer_user']);
        $this->db->where('cur.status','1');
        $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');

        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        } 

        if(isset($data['business_unit_id'])  && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('p.provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('p.provider_name,c.contract_name','asc');
        $query = $this->db->get();
        //echo 
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getMyContributionList($data)
    { 
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select(' 0 as is_workflow, if(t.template_name is not null, "", "") as workflow_id,t.template_name,t.id_template,c.*,c.provider_name provider_id,c.id_contract contract_id,p.provider_name as providerName,cu.currency_name,rc.classification_name,rcl.relationship_category_name,`crv`.`id_contract_review` ,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,"0" as id_contract_workflow,cus.contract_review_id,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,1 as is_contribution,cw.parent_id,"review" as typeOfActivity,get_validation_incomplete_modules(crv.id_contract_review,cus.user_id) as val_incomplete');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','');
        $this->db->join('contract_user cus', 'crv.id_contract_review=cus.contract_review_id', '');
        // $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        // $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        // $this->db->join('module m', 'm.id_module=cur.module_id', '');
        $this->db->join('template t', 'c.template_id = t.id_template', '');
        $this->db->join('contract_workflow cw', 'c.id_contract=cw.contract_id', 'left');
        $this->db->where('crv.contract_review_status','review in progress');// add the review status from back end side(said by sai prasad)
        $this->db->where('cus.user_id',$data['customer_user']);
        $this->db->where('cus.status','1');
        // print_r($data);exit;
        if($data['mycontribution_filter']=='validator'){// get only validation on going reviews only
            $this->db->where('crv.validation_status',2);
        }
        else{
            $this->db->where_in('crv.validation_status',array(0,1));
        }
        // $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');
        
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                // $this->db->group_start();
                // $this->db->like('c.contract_name', $data['search'], 'both');
                // $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                // $this->db->or_like('p.provider_name', $data['search'], 'both');
                // $this->db->or_like('bu.bu_name', $data['search'], 'both');
                // $this->db->or_like('c.type', $data['search'], 'both');
                // $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }

        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        // if(isset($data['contract_status']) && !is_array($data['contract_status']))
        //     $this->db->where('crv.contract_review_status',$data['contract_status']);
        // if(isset($data['contract_status']) && is_array($data['contract_status']))
        //     $this->db->where_in('crv.contract_review_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        
        $query1 = $this->db->get_compiled_select();//echo 
        $this->db->_reset_select();

        /* results count end */

        $this->db->select('if(cw.workflow_name is null,0,1)  as is_workflow,cw.workflow_id as workflow_id, cw.workflow_name as template_name,cw.workflow_id as id_template ,c.*,c.provider_name provider_id,c.id_contract contract_id,p.provider_name as providerName,cu.currency_name,rc.classification_name,rcl.relationship_category_name,`crv`.`id_contract_review`,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,cw.id_contract_workflow as id_contract_workflow,cus.contract_review_id,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,(IF(c.type="contract","1",IF(cw.parent_id>0 && c.type="project", 1, 0))) as is_contribution,cw.parent_id,"task" as typeOfActivity,get_validation_incomplete_modules(crv.id_contract_review,cus.user_id) as val_incomplete');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');   
        $this->db->join('contract_user cus', 'crv.id_contract_review=cus.contract_review_id', '');
        // $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        // $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        // $this->db->join('module m', 'm.id_module=cur.module_id', '');
        // $this->db->join('contract_review crv', 'cur.contract_review_id=crv.id_contract_review', 'left');
        $this->db->join('contract_workflow cw', 'cw.id_contract_workflow=crv.contract_workflow_id', '');
        $this->db->where('crv.contract_review_status','workflow in progress');
        $this->db->where('cus.user_id',$data['customer_user']);
        $this->db->where('cus.status','1');
        if($data['mycontribution_filter']=='validator'){// get only validation on going work flows only
            $this->db->where('crv.validation_status',2);
        }
        else{
            $this->db->where_in('crv.validation_status',array(0,1));
        }
       // $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');
       
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                // $this->db->group_start();
                // $this->db->like('c.contract_name', $data['search'], 'both');
                // $this->db->or_like('rcl.relationship_category_name', $data['search'], ' both');
                // $this->db->or_like('p.provider_name', $data['search'], 'both');
                // $this->db->or_like('bu.bu_name', $data['search'], 'both');
                // $this->db->or_like('c.type', $data['search'], 'both');
                // $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        } 

        if(isset($data['business_unit_id'])  && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        // if(isset($data['contract_status']) && !is_array($data['contract_status']))
        //     $this->db->where('c.contract_status',$data['contract_status']);
        // if(isset($data['contract_status']) && is_array($data['contract_status']))
        //     $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        // if(isset($data['provider_id']) && $data['provider_id']>0)
        //     $this->db->where('c.provider_name',$data['provider_id']);
        //$this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $this->db->where("cw.status","1");
        $this->db->group_by('cw.id_contract_workflow');
       
               $query2 = $this->db->get_compiled_select();
              $this->db->_reset_select();
              $this->db->select("*")->from("($query1 UNION $query2) as unionTable");//
              if(!empty($data['is_contribution']) && $data['is_contribution']==1){
                  $this->db->where('is_contribution',1);
              }
              //search is moved from top to down because we are search in dynamic coloums also so we chabged here
            if(isset($data['search'])&&(!$data['advancedsearch_get']))
            {
                 $this->db->group_start();
                $this->db->like('contract_name', $data['search'], 'both');
                $this->db->or_like('relationship_category_name', $data['search'], ' both');
                $this->db->or_like('provider_name', $data['search'], 'both');
                $this->db->or_like('bu_name', $data['search'], 'both');
                $this->db->or_like('type', $data['search'], 'both');
                $this->db->or_like('typeOfActivity', $data['search'], 'both');
                $this->db->group_end();
            }
            // print_r($data);exit;
            if(!empty($data['mycontribution_filter']) && $data['mycontribution_filter']=='validator'){
                $this->db->where('val_incomplete>0');   
            }
              $count_result_db = clone $this->db;
              $count_result = $count_result_db->get();//echo $count_result_db->last_query();exit;
              $count_result = $count_result->num_rows();
            
              
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('provider_name,contract_name','asc');
        $query = $this->db->get();
        // echo 
        return array('total_records' => $count_result,'data' => $query->result_array());
    }
    //my contribustion list end

    public function getDeletedContractList($data)
    {
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('c.*,concat(u.first_name," ",u.last_name) deleted_by,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('user u','c.updated_by = u.id_user','left');
        if(isset($data['customer_user'])) {
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
            $this->db->join('module m', 'm.id_module=cur.module_id', '');
            $this->db->where('cur.user_id',$data['customer_user']);
            $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.contract_name', $data['search'], 'both');
            $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('c.provider_name', $data['search'], 'both');
            $this->db->or_like('c.type', $data['search'], 'both');
            $this->db->or_like('concat(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.contract_name like "%'.($data['search']).'%"
            or rcl.relationship_category_name like "%'.($data['search']).'%"
            or c.provider_name like "%'.($data['search']).'%")');*/
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('c.provider_name',$data['provider_name']);
        if(isset($data['parent_contract_id'])  && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        $this->db->where('c.is_deleted','1');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $query = $this->db->get();
        //echo 
        $all_clients_count = $query->num_rows();

        /* results count end */

        $this->db->select('c.*,IFNULL(`p`.`provider_name`,"---") provider_name,IFNULL(`p`.`provider_name`,"---") provider_sort,concat(u.first_name," ",u.last_name) deleted_by,cu.currency_name,rc.classification_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,(SELECT GROUP_CONCAT(p.provider_name) as provider_name FROM `project_providers` `pp` LEFT JOIN `provider` `p` ON `pp`.`provider_id`=`p`.`id_provider` WHERE `pp`.`project_id` = c.id_contract AND `pp`.`is_linked` = "1"
        AND `p`.`status` = "1")AS project_provider_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left');
        $this->db->join('user u','c.updated_by = u.id_user','left');
        if(isset($data['customer_user'])) {
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
            $this->db->join('module m', 'm.id_module=cur.module_id', '');
            $this->db->where('cur.user_id',$data['customer_user']);
            $this->db->where('m.contract_review_id=(select max(id_contract_review) from contract_review where contract_id=c.id_contract)');
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.contract_name', $data['search'], 'both');
            $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('c.provider_name', $data['search'], 'both');
            $this->db->or_like('c.type', $data['search'], 'both');
            $this->db->or_like('concat(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.contract_name like "%'.($data['search']).'%"
            or rcl.relationship_category_name like "%'.($data['search']).'%"
            or c.provider_name like "%'.($data['search']).'%")');*/
        if(isset($data['business_unit_id'])  && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('c.provider_name',$data['provider_name']);
        if(isset($data['parent_contract_id'])  && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        $this->db->where('c.is_deleted','1');
        $this->db->group_by('c.id_contract');

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('c.updated_on','desc');
        $query = $this->db->get();
        //echo 
        return array('total_records' => $all_clients_count,'data' => $query->result_array());
    }

    public function getProviders($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/

        $this->db->select('distinct(c.provider_name)');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract and crv.contract_review_status="initiated"','left');
        if(isset($data['customer_user'])) {
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id', '');
            $this->db->where('cur.user_id',$data['customer_user']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.contract_name', $data['search'], 'both');
            $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('c.provider_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.contract_name like "%'.$data['search'].'%" or rcl.relationship_category_name like "%'.$data['search'].'%" or c.provider_name like "%'.$data['search'].'%")');*/
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where('c.id_contract in (select contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id='.$data['session_user_id'].'  and cux.status=1)',null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.provider_name','asc');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getContracts($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/

        $this->db->select('c.contract_name,c.id_contract as contract_id');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract and crv.contract_review_status="initiated"','left');
        if(isset($data['customer_user'])) {
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id', '');
            $this->db->where('cur.user_id',$data['customer_user']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.contract_name', $data['search'], 'both');
            $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
            $this->db->or_like('c.provider_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(c.contract_name like "%'.$data['search'].'%" or rcl.relationship_category_name like "%'.$data['search'].'%" or c.provider_name like "%'.$data['search'].'%")');*/
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        /*if(isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id',$data['business_unit_id']);*/
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        /*if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);*/
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('c.provider_name',$data['provider_name']);
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name','asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getContractListCount($data)
    {
        if(isset($data['contract_status']))
            $data['contract_status']=explode(',',$data['contract_status']);
        $this->db->select('COUNT(DISTINCT c.id_contract) as total_records,MAX(id_contract_review) id_contract_review');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('contract_review cr ',' c.id_contract = cr.contract_id');
        $this->db->where('cr.is_workflow = 0');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']))
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            //$this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            {
                $this->db->group_start();
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
                $this->db->group_end();
            }  
            //$this->db->group_end();
            //$this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);  
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']) && count($data['business_unit_id'])>0)
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                //$this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL '.$data["end_date_lessthan_90"].' DAY)');
        // if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
        //     $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        // else
        //     $this->db->where('c.parent_contract_id',0);
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');

        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        
        $this->db->select("*")->from("($new_query) as newTable");
        //Can_access filters the records user have access to
        if(isset($data['can_access']) && $data['can_access'] > 0)
            $this->db->where('can_access',$data['can_access']);
        $query = $this->db->get();
        // echo $this->session_user_id;exit;
        // echo '<pre>'.$this->db->last_query();
        /*echo "<pre>";print_r($query->num_rows());echo "</pre>";exit;
        $all_clients_count = $query->result_array();*/
        return $query->num_rows();
    }

    public function getContractDetails($data)
    {
        if(!empty($data['id_contract_review']) && !empty($data['contract_review_id'])){
            if($data['id_contract_review'] > $data['contract_review_id'])
            unset($data['contract_review_id']);
            if($data['contract_review_id'] > $data['id_contract_review'])
            unset($data['id_contract_review']);
        }
        if(isset($data['contract_review_status']))
            $data['contract_review_status']=$this->db->escape($data['contract_review_status']);
        $this->db->select('c.*,t.template_name,bu.bu_name,rcl.relationship_category_name,rcll.classification_name,cr.currency_name,CONCAT_WS(\' \',u.first_name,u.last_name) as delegate_user_name,CONCAT_WS(\' \',u1.first_name,u1.last_name) as responsible_user_name,crv.id_contract_review,crv.contract_review_status,if(crv.contract_review_status="review in progress" OR crv.contract_review_status="workflow in progress","itako","annus") as reaaer,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,p.id_provider as provider_name,p.provider_name provider_name_show,u.email as delegate_email,u1.email as owner_email,c.delegate_id,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,u.first_name as delgate_first_name,u.last_name as delegate_last_name,u1.first_name as owner_first_name,u1.last_name as owner_last_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=c.relationship_category_id and rcl.language_id=1','LEFT');
        $this->db->join('relationship_classification_language rcll','rcll.relationship_classification_id=c.classification_id and rcll.language_id=1','LEFT');
        $this->db->join('currency cr','cr.id_currency=c.currency_id','LEFT');
        if(isset($data['is_workflow']) && $data['is_workflow'] == 1){
            $this->db->select('workflow_status as contract_status');
            $this->db->join('contract_workflow cw','cw.id_contract_workflow=c.id_contract','left');
            $this->db->join('template t','t.id_template=cw.workflow_id','left');//cw.workflow_id == to_avail_template of module table == template id
        }else{
            $this->db->join('template t','t.id_template=c.template_id','left');
        }
        $this->db->join('user u','u.id_user=c.delegate_id','left');
        $this->db->join('user u1','u1.id_user=c.contract_owner_id','left');
        if(isset($data['contract_review_status']))
            $this->db->join('contract_review crv','crv.contract_id=c.id_contract and crv.contract_review_status="'.$data["contract_review_status"].'"','left',false);
        else
            $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        if(isset($data['business_unit_id']))
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_contract']))
            $this->db->where('c.id_contract',$data['id_contract']);
        if(isset($data['contract_review_id']))
            $this->db->where('crv.id_contract_review',$data['contract_review_id']);
        if(isset($data['id_contract_review']))
            $this->db->where('crv.id_contract_review',$data['id_contract_review']);
        // if(isset($data['is_workflow']))
        //     $this->db->where('crv.is_workflow',$data['is_workflow']);
        $this->db->where('c.is_deleted','0');
        $this->db->order_by('crv.id_contract_review','DESC');
        $this->db->limit('1');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getContractCurrentDetails($data){
        $this->db->select('c.*,t.template_name,p.provider_name,rcl.relationship_category_name,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as business_unit,CONCAT_WS(\' \',u2.first_name,u2.last_name) as created_by,rcl.relationship_category_name,rcll.classification_name,cr.currency_name,CONCAT_WS(\' \',u.first_name,u.last_name) as delegate_user_name,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contract_owner_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('template t','t.id_template = c.template_id','left');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=c.relationship_category_id and rcl.language_id=1','LEFT');
        $this->db->join('relationship_classification_language rcll','rcll.relationship_classification_id=c.classification_id and rcll.language_id=1','LEFT');
        $this->db->join('currency cr','cr.id_currency=c.currency_id','LEFT');
        $this->db->join('user u','u.id_user=c.delegate_id','left');
        $this->db->join('user u1','u1.id_user=c.contract_owner_id','left');
        $this->db->join('user u2','u2.id_user=c.created_by','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->where('c.id_contract',$data['contract_id']);
        $this->db->where('c.is_deleted','0');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getContractLogId($data){
        $this->db->select('c.id_contract_log,c.created_on log_created_on,CONCAT( `u`.`first_name`,\' \', u.last_name) log_user_name,CONCAT(DATE_FORMAT(c.created_on,\'%d-%m-%Y\'),\' \', TIME(c.created_on),\' by \', CONCAT( `u`.`first_name`,\' \', u.last_name)) as log_by');
        $this->db->from('contract_log c');
        $this->db->join('user u','c.created_by = u.id_user','left');
        $this->db->where('contract_id',$data['contract_id']);
        $this->db->order_by('id_contract_log','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProviderLogId($data){
        $this->db->select('p.id_provider_log,p.created_on log_created_on,CONCAT( `u`.`first_name`,\' \', u.last_name) log_user_name,CONCAT(DATE_FORMAT(p.created_on,\'%d-%m-%Y\'),\' \', TIME(p.created_on),\' by \', CONCAT( `u`.`first_name`,\' \', u.last_name)) as log_by');
        $this->db->from('`provider_log` `p`');
        $this->db->join('user u','p.created_by = u.id_user','left');
        $this->db->where('provider_id',$data['id_provider']);
        $this->db->order_by('id_provider_log','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getContractLogDetails($data){//DATE_FORMAT(d.uploaded_on, "%Y-%m-%d")
        $this->db->select('c.*,t.template_name,p.provider_name,rcl.relationship_category_name,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as business_unit,CONCAT_WS(\' \',u2.first_name,u2.last_name) as created_by,rcl.relationship_category_name,rcll.classification_name,cr.currency_name,CONCAT_WS(\' \',u.first_name,u.last_name) as delegate_user_name,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contract_owner_name');
        $this->db->from('contract_log c');        
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('template t','t.id_template = c.template_id','left');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id=c.relationship_category_id and rcl.language_id=1','LEFT');
        $this->db->join('relationship_classification_language rcll','rcll.relationship_classification_id=c.classification_id and rcll.language_id=1','LEFT');
        $this->db->join('currency cr','cr.id_currency=c.currency_id','LEFT');
        $this->db->join('user u','u.id_user=c.delegate_id','left');
        $this->db->join('user u1','u1.id_user=c.contract_owner_id','left');
        $this->db->join('user u2','u2.id_user=c.created_by','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->where('c.id_contract_log',$data['contract_log_id']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addContract($data)
    {
        $this->db->insert('contract', $data);
        return $this->db->insert_id();
    }

    public function updateContract($data)
    {
        $this->db->where('id_contract', $data['id_contract']);
        $this->db->update('contract', $data);
        return 1;
    }

    public function getContractReviewActionItems($data)
    {
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('c.*,qu.question_text,concat(u.first_name," ",u.last_name) as user_name,ml.module_name,tl.topic_name,u1.user_role_id,concat(u1.first_name," ",u1.last_name) as created_by_name,IF(c.due_date<CURDATE() AND c.status = "open",1,0)as overdue,cr.type');
        $this->db->from('contract_review_action_item c');
        $this->db->join('contract_review crw','crw.id_contract_review =c.contract_review_id','left');
        $this->db->join('contract cr','c.contract_id = cr.id_contract','left');
        $this->db->join('user u','c.responsible_user_id=u.id_user','left');
        $this->db->join('question_language qu','c.question_id=qu.question_id','left');
        $this->db->join('module_language ml','ml.module_id=c.module_id and ml.language_id=1','left');
        $this->db->join('topic_language tl','tl.topic_id=c.topic_id and tl.language_id=1','left');
        $this->db->join('provider p','cr.provider_name=p.id_provider','left');
        $this->db->join('user u1','u1.id_user=c.created_by');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.action_item', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('c.description', $data['search'], 'both');
            $this->db->or_like('qu.question_text', $data['search'], 'both');
            $this->db->or_like('c.priority', $data['search'], 'both');
            $this->db->group_end();
        }

        // print_r($data);exit;
        /*if(isset($data['search']))
            $this->db->where('(c.action_item like "%'.$data['search'].'%" or u.first_name like "%'.$data['search'].'%" or u.last_name like "%'.$data['search'].'%" or c.description like "%'.$data['search'].'%")');*/
        if(isset($data['contract_id']) && $data['user_role_id']>0)
            $this->db->where('c.contract_id',$data['contract_id']);
        if(isset($data['is_workflow']) && $data['is_workflow'] == 1)
            $this->db->where('c.is_workflow',$data['is_workflow']);// is_workflow==1 meanse workflow action items
        if(isset($data['is_workflow']) && $data['is_workflow'] == 0 && isset($data['action_item_type']) && $data['action_item_type'] == 'inside'){
            $this->db->where('c.is_workflow',$data['is_workflow']);// is_workflow==0 AND action_item_type==inside meanse review action items
            $this->db->where('c.module_id > 0');
            // $this->db->where_in('c.reference_type',array('topic','question'));
        }
        if(isset($data['contract_workflow_id']))
            $this->db->where('crw.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['page_type']) && $data['page_type']='contract_review'){
            if(isset($data['module_id']) && !is_array($data['module_id']))
                $this->db->where('c.module_id IN (select m.id_module from contract_review cr JOIN module m on m.contract_review_id=cr.id_contract_review join module m2 on m2.parent_module_id=m.parent_module_id where cr.contract_id=c.contract_id and m2.id_module='.$data['module_id'].')');
            if(isset($data['topic_id']))
                $this->db->where('c.topic_id IN (select t.id_topic from contract_review cr LEFT JOIN module m on m.contract_review_id=cr.id_contract_review JOIN topic t on t.module_id=m.id_module JOIN topic t2 on t2.parent_topic_id=t.parent_topic_id where cr.contract_id=c.contract_id and t2.id_topic='.$data['topic_id'].')');
        }
        else{
            if(isset($data['contract_review_id']))
                $this->db->where('c.contract_review_id',$data['contract_review_id']);
            if(isset($data['id_contract_review']))
                $this->db->where('c.contract_review_id',$data['id_contract_review']);
            if(isset($data['module_id']) && !is_array($data['module_id']))
                $this->db->where('c.module_id',$data['module_id']);
            if(isset($data['topic_id']))
                $this->db->where('c.topic_id',$data['topic_id']);
        }
        if(isset($data['item_status']))
            $this->db->where('c.item_status',$data['item_status']);
        if(isset($data['id_contract_review_action_item']))
            $this->db->where('c.id_contract_review_action_item',$data['id_contract_review_action_item']);
        // if(isset($data['contract_workflow_id']))
        //     $this->db->where('c.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['id_user']) && isset($data['user_role_id'])){
            if($data['user_role_id']==3){ 
                // 18-09-2019 // For owner on changing new action item logic after sprint 5 on 
                $this->db->group_start();
                $this->db->where('c.created_by', $data['id_user']);
                $this->db->or_where('c.responsible_user_id', $data['id_user']);
                $this->db->or_where('cr.contract_owner_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('c.module_id',$data['module_id']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==4){
                // 18-09-2019 // For delegate on changing new action item logic after sprint 5 on 
                $this->db->group_start();
                $this->db->where('c.created_by', $data['id_user']);
                $this->db->or_where('c.responsible_user_id', $data['id_user']);
                $this->db->or_where('cr.delegate_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('c.module_id',$data['module_id']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==2 || $data['user_role_id']==1){
                // $this->db->group_start();
                // $this->db->where('c.created_by', $data['id_user']);
                // $this->db->or_where('u1.user_role_id>=', 2);
                // $this->db->or_where('c.responsible_user_id', $data['id_user']);
                // $this->db->group_end();
            }
            else if($data['user_role_id']==6 &&  $data['provider_id']==0){
                $this->db->group_start();
                $this->db->where('c.created_by', $data['id_user']);
                $this->db->or_where('u1.user_role_id>=', 2);
                $this->db->or_where('c.responsible_user_id', $data['id_user']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==7 &&  $data['provider_id']==0){
                $this->db->group_start();
                $this->db->where_in('c.responsible_user_id', $data['provider_colleuges']);
                $this->db->or_where('c.created_by', $data['id_user']);
                $this->db->group_end();
            }
        }
        if(!empty($data['status'])){
            $this->db->where('c.status',$data['status']);
        }
        if($data['action_item_type']=='outside' && empty($data['provider_id'])){
            // $this->db->where_in('c.reference_type',array('contract','question','topic'));
        }
        if(isset($data['provider_id'])){
            // $this->db->where('c.reference_type',array('Provider'));
            $this->db->where('`c`.`provider_id`',$data['provider_id']);

        }
        if(!empty($data['reference_type'])){
            $this->db->where_in('c.reference_type',$data['reference_type']);            
        }
        // if(!empty($data['reference_type'])){
        //     $this->db->where_in('c.reference_type',$data['reference_type']);            
        // }
        $this->db->group_by('c.id_contract_review_action_item');
        /* results count start */
        $all_records = clone $this->db;
        // $this->db->get();echo '<pre>'.
        $all_clients_db = $all_records->get()->num_rows();  
        //echo '$all_clients_count '.$all_clients_count.' ==';
        /* results count end */

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('c.id_contract_review_action_item','DESC');
        $query = $this->db->get();//echo 
        $result= $query->result_array();//echo '<pre>'.$this->db->last_query();
        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            $status_change_access='annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                $view_access = "itako";
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['created_by'] == $data['id_user']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user'] || $v['created_by'] == $data['id_user']) {
                        $view_access = "itako";
                    }
                } else if ($data['user_role_id'] == 8 || $data['user_role_id'] == 7 || $data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    $view_access = "itako";
                    if ($v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user'] || $v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = 'itako';
            }
            //$view_access="itako;
            if($view_access=="itako" && $v['status']!='completed')
                $status_change_access="itako";
            if($v['status']=='completed')
                $edit_access=$delete_access='annus';
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
            $result[$k]['scaacs']=$status_change_access;

            $this->db->select('c.*,concat(u.first_name," ",u.last_name) as user_name');
            $this->db->from('contract_review_action_item_log c');
            $this->db->join('user u','c.updated_by=u.id_user','left');
            $this->db->where('c.contract_review_action_item_id', $v['id_contract_review_action_item']);
            $this->db->where('c.updated_by is not null');
            $query_log = $this->db->get();//echo 
            $result[$k]['comments_log']= $query_log->result_array();
        }

        return array('total_records' => $all_clients_db,'data' => $result);
    }

    public function contractReviewActionItemLog($data)
    {
        $this->db->select('c.id_contract_review_action_item_log,c.contract_review_action_item_id,c.comments,c.updated_by,DATE_FORMAT(c.updated_on,"%Y-%m-%d") as updated_on,concat(u.first_name," ",u.last_name) as user_name');
        $this->db->from('contract_review_action_item_log c');
        $this->db->join('user u','c.updated_by=u.id_user','left');
        if(isset($data['id_contract_review_action_item']))
            $this->db->where('c.contract_review_action_item_id', $data['id_contract_review_action_item']);
        $this->db->where('c.updated_by is not null');
        $this->db->where('c.comments is not null');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addContractReviewActionItem($data)
    {
        $this->db->insert('contract_review_action_item', $data);
        return $this->db->insert_id();
    }

    public function getContractReview($data)
    {
        $this->db->select('cr.*,IFNULL(cr.updated_on,cr.created_on) as updated_date,CONCAT_WS(\' \',u.first_name,u.last_name) as updated_user_name,c.business_unit_id');
        $this->db->from('contract_review cr');
        $this->db->join('user u','u.id_user=cr.created_by','LEFT');
        $this->db->join('contract c','c.id_contract=cr.contract_id and c.is_deleted=0','LEFT');
        if(isset($data['contract_id']))
            $this->db->where('cr.contract_id',$data['contract_id']);
        if(isset($data['status']))
            $this->db->where('cr.contract_review_status',$data['status']);
        if(isset($data['id_contract_review']))
            $this->db->where('cr.id_contract_review',$data['id_contract_review']);
        if(isset($data['contract_workflow_id']))
            $this->db->where('cr.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['is_workflow']))
            $this->db->where('cr.is_workflow',$data['is_workflow']);
        if(isset($data['order']))
            $this->db->order_by('cr.id_contract_review',$data['order']);
        if(isset($data['offset']))
            $this->db->limit($data['limit'],$data['offset']);
        if(isset($data['condition']) && $data['condition']=='less_than_current_review')
            $this->db->where('cr.id_contract_review<',$data['contract_review_id']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function updateContractReviewActionItem($data)
    {
        //$this->addContractReviewActionItemLog($data);
        $this->db->where('id_contract_review_action_item', $data['id_contract_review_action_item']);
        $this->db->update('contract_review_action_item', $data);
        return 1;
    }

    /*public function addContractReviewActionItemLog($data){
        $this->db->select("id_contract_review_action_item,comments,CASE WHEN updated_on IS NOT NULL THEN updated_on ELSE created_on END AS created_on,CASE WHEN updated_by IS NOT NULL THEN updated_by ELSE created_by END AS created_by",FALSE);
        $this->db->from('contract_review_action_item crai');
        $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);
        $query = $this->db->get();
        $result=$query->result_array();
        foreach($result as $k=>$v){
            $insert_log_data=array();
            $insert_log_data['contract_review_action_item_id']=$v['id_contract_review_action_item'];
            $insert_log_data['comments']=$v['comments'];
            $insert_log_data['created_by']=$v['created_by'];
            $insert_log_data['created_on']=$v['created_on'];
            $this->db->insert('contract_review_action_item_log', $insert_log_data);
        }
        return 1;

    }*/

    public function getDelegates($data=array()){
        $this->db->select('u.id_user,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, (SELECT IF(ctry.country_name!="",CONCAT(bu1.bu_name," - ",ctry.country_name),bu1.bu_name) as bu_name 
        FROM `business_unit` `bu1`
        LEFT JOIN country ctry  ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=`bu`.`id_business_unit`)), ")")) as user_name,u.email');
        $this->db->from('business_unit_user buu');
        $this->db->join('user u','u.id_user=buu.user_id','left');
        $this->db->join('business_unit bu','bu.id_business_unit=buu.business_unit_id','left');
        $this->db->join('user_role ur','ur.id_user_role=u.user_role_id and ur.role_status=1','left');
        $this->db->where('ur.user_role_name','Delegate');
        $this->db->where('buu.status','1');
        $this->db->where('bu.status','1');
        $this->db->where('u.user_status','1');
        if(isset($data['id_business_unit']))
            $this->db->where('buu.business_unit_id',$data['id_business_unit']);
        // if(!empty($data['array_bu_ids'])){
        //     $this->db->where_in('buu.business_unit_id',$data['array_bu_ids']);
        // }
        if(isset($data['forDocumentIntelligence']) &&($data['forDocumentIntelligence']==1) && $this->session_user_info->user_role_id!=2)
        {
            $this->db->where_in('buu.business_unit_id', !empty($data['array_bu_ids'])?$data['array_bu_ids']:'');
        }
        elseif(!empty($data['array_bu_ids'])){
            $this->db->where_in('buu.business_unit_id',$data['array_bu_ids']);
        }
        if(!empty($data['not_user_ids'])){
            $this->db->where_not_in('u.id_user',$data['not_user_ids']);
        }
        if(!empty($data['user_id'])){
            $this->db->where('u.id_user',$data['user_id']);
        }
        $this->db->group_by('u.id_user');
        $query = $this->db->get();
        // echo $this->db->last_query(); die('test');
        return $query->result_array();
    }

    public function getBusinessUnitUsers($data)
    {
        $this->db->select('u.id_user,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as showname,CONCAT(u.first_name," ",u.last_name," (",u.email," | ",(SELECT IF(ctry.country_name!="",CONCAT(bu1.bu_name," - ",ctry.country_name),bu1.bu_name) as bu_name 
        FROM `business_unit` `bu1`
        LEFT JOIN country ctry  ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=`bu`.`id_business_unit`)," | ",ur.user_role_name,")") as name');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and bur.status=1','left');
        $this->db->join('business_unit bu','bur.business_unit_id=bu.id_business_unit and bu.status=1','left');

        // if(!empty($data['array_bu_ids'])){
        //     $this->db->where_in('bur.business_unit_id',$data['array_bu_ids']);
        // }
        if(isset($data['forDocumentIntelligence']) &&($data['forDocumentIntelligence']==1) && $this->session_user_info->user_role_id!=2)
        {
            $this->db->where_in('bur.business_unit_id', !empty($data['array_bu_ids'])?$data['array_bu_ids']:'');
        }
        elseif(!empty($data['array_bu_ids'])){
            $this->db->where_in('bur.business_unit_id',$data['array_bu_ids']);
        }
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        if($data['user_role_id']==3){
            $this->db->where_in('u.user_role_id',array(3,8));
        }
        else{
            $this->db->where('u.user_role_id',$data['user_role_id']);
        }
        if(isset($data['customer_id']))                             //added new by sp
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['user_id_not']))
            $this->db->where('ur.id_user_role not in (2,5,6)');
        if(!empty($data['not_user_ids'])){
            $this->db->where_not_in('u.id_user',$data['not_user_ids']);
        }
        if(!empty($data['user_id'])){
            $this->db->where('u.id_user',$data['user_id']);
        }
        $this->db->where('ur.role_status = 1');
        $this->db->group_by('u.id_user');
        $query = $this->db->get();
        //echo 
        return $query->result_array();
    }

    public function getCustomerUsers($data)
    {
        $this->db->select('u.id_user,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as name');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and status=1','left');
        if(isset($data['business_unit_id']))
            $this->db->where('bur.business_unit_id',$data['business_unit_id']);
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['type']) && $data['type']='contributor'){
            $this->db->where('u.id_user not in (select contract_owner_id from contract where id_contract='.$data['contract_id'].')');
            $this->db->where('u.id_user not in (select delegate_id from contract where id_contract='.$data['contract_id'].')');
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getCustomerUsers_add($data)
    {
        $this->db->select('u.id_user,bu.id_business_unit,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as showname,CONCAT(u.first_name," ",u.last_name," (",u.email," | ",ur.user_role_name," | ",IFNULL(GROUP_CONCAT((SELECT IF(ctry.country_name!="",CONCAT(bu1.bu_name," - ",ctry.country_name),bu1.bu_name) as bu_name 
        FROM `business_unit` `bu1`
        LEFT JOIN country ctry  ON bu1.country_id=ctry.id_country WHERE bu1.id_business_unit=`bu`.`id_business_unit`)),p.provider_name),")") as name,p.id_provider');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role','left');
        $this->db->join('business_unit_user bur','bur.user_id=u.id_user and bur.status=1','left');
        $this->db->join('business_unit bu','bur.business_unit_id=bu.id_business_unit and bu.status=1','left');
        $this->db->join('provider p','u.provider = p.id_provider','left');
        if(isset($data['business_unit_id']))
            $this->db->where('bu.id_business_unit',$data['business_unit_id']);
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['type']) && $data['type']='contributor'){
            //$this->db->where('u.id_user not in (select contract_owner_id from contract where id_contract='.$data['contract_id'].')');
            //$this->db->where('u.id_user not in (select delegate_id from contract where id_contract='.$data['contract_id'].')');
            if($data['contributor_type']=='expert')
                $this->db->where('u.contribution_type',0);
            if($data['contributor_type']=='validator')
                $this->db->where('u.contribution_type',1);
            if($data['contributor_type']=='provider'){                
                $this->db->where('u.contribution_type',3);
                if(isset($data['provider']))
                    $this->db->where_in('u.provider',$data['provider']);
                    $this->db->where('p.status = 1');

            }
        }
        $this->db->where('ur.role_status = 1');
        $this->db->where('u.user_status = 1');
        $this->db->where('u.is_deleted = 0');
        if(isset($data['user_not']))
            $this->db->where('u.id_user != ',$data['user_not']);
        $this->db->where('ur.id_user_role not in (2,5,6)');
        $this->db->group_by('u.id_user');
        $result = $this->db->get();//echo 
        //echo '<pre>'.print_r($result);exit;
        $result = $result->result_array();
        return $result;
    }

    public function getContractReviewUsers($data)
    {
        $this->db->select('u.id_user,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as name');
        $this->db->from('contract_user cu');
        $this->db->join('user u','u.id_user=cu.user_id','left');
        if(isset($data['contract_id']))
            $this->db->where('cu.contract_id',$data['contract_id']);
        if(isset($data['module_id']))
            $this->db->where('cu.module_id',$data['module_id']);
        if(isset($data['user_id']))
            $this->db->where('cu.user_id',$data['user_id']);
        if(isset($data['user_role_id']))
            $this->db->where('u.user_role_id',$data['user_role_id']);
        $this->db->where('cu.status',1);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addContractReview($data)
    {
        $this->db->insert('contract_review', $data);
        return $this->db->insert_id();
    }
    public function checkContributorForContractReview($data){
        $this->db->select('cu.*');
        $this->db->from('contract_user cu');
        $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        $this->db->where('cu.user_id',$data['id_user']);
        $this->db->where('cu.status',1);
        $result1 = $this->db->get()->result_array();
        if(count($result1)>0){
            return true;
        }
        else{
            return false;
        }
    }
    public function getContractReviewModule($data){
        // echo '<pre>'.print_r($data);exit;
        $this->db->select('cu.*');
        $this->db->from('contract_user cu');
        $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        $this->db->where('cu.user_id',$data['id_user']);
        $this->db->where('cu.status',1);
        $result1 = $this->db->get()->result_array();
        
        $this->db->select('m.id_module,m.is_workflow,m.module_status,ml.module_name,m.static,m.contract_review_id,count(crai.id_contract_review_action_item) as action_item_count,m.type');
        $this->db->from('module_language ml');
        $this->db->join('module m','m.id_module = ml.module_id','');
        $this->db->join('contract_review_action_item crai','m.id_module=crai.module_id and crai.item_status=1','left');
        $this->db->join('contract_review cr','cr.id_contract_review=m.contract_review_id');
        $this->db->join('user u','u.id_user=cr.created_by');
        $this->db->where('m.contract_review_id',$data['contract_review_id']);
        $this->db->where_in('m.module_status',array(1,2,3));
        if(!empty($data['id_module'])){
        $this->db->where('m.id_module',$data['id_module']);
        }
        $this->db->group_by('m.id_module');
        if(count($result1)>0){
            $this->db->join('contract_user cu','m.id_module=cu.module_id and cu.status=1','');
            $this->db->where('cu.user_id',$data['id_user']);
        }

        $this->db->order_by('m.module_order','asc');

        $result = $this->db->get()->result_array();//echo '<pre>'.$this->db->last_query();
        foreach($result as $k=>$v){
            $result[$k]['changes_count']=0;
            //$result[$k]['last_review']=date('Y-m-d');
            $result[$k]['score']='N/A';
        }
        return $result;
    }
    public function getTrendsModules($data){
        $this->db->select('m.id_module,m.is_workflow,m.module_status,ml.module_name,m.static,m.contract_review_id,m.type,m.parent_module_id');
        $this->db->from('module_language ml');
        $this->db->join('module m','m.id_module = ml.module_id','');
        $this->db->join('topic t','t.module_id = m.id_module','');
        $this->db->join('question q','t.id_topic = q.topic_id','');
        $this->db->where('m.contract_review_id',$data['contract_review_id']);
        if(isset($data['contributors_modules']) && count($data['contributors_modules']) > 0)
            $this->db->where_in('m.id_module',$data['contributors_modules']);
        if(isset($data['user_role_id']) && $data['user_role_id'] == 7)
            $this->db->where('q.provider_visibility = 1');
        $this->db->group_by('m.id_module');
        $this->db->order_by('m.module_order','asc');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getTrendsTopics($data){
        $this->db->select('t.id_topic,tl.topic_name,t.module_id,t.parent_topic_id,(SELECT count(qst.id_question) count FROM question qst WHERE qst.topic_id = t.id_topic  AND qst.provider_visibility=1) as provider_visibility_count,
        (SELECT count(qst1.id_question) count FROM question qst1 WHERE qst1.topic_id = t.id_topic  AND qst1.question_status=1) as question_visibility_count');
        $this->db->from('topic_language tl');
        $this->db->join('topic t','t.id_topic = tl.topic_id','');
        $this->db->join('question q','t.id_topic = q.topic_id','');
        $this->db->where('t.module_id',$data['module_id']);
        $this->db->where('t.topic_status',1);
        if($data['external_user']){
            $this->db->where('q.provider_visibility = 1');
        }
        $this->db->group_by('t.id_topic');
        $this->db->order_by('t.topic_order','asc');

        $subQuery1 = $this->db->_compile_select();
        $this->db->_reset_select();
        // print_r($data);exit;
        $this->db->select("*")->from("($subQuery1) as unionTable");
        $this->db->where('question_visibility_count>0');
        if($data['is_subtask']>0){
            $this->db->where('provider_visibility_count>0');
        }


        $result = $this->db->get();//echo '<pre>'.
        return $result->result_array();
    }

    public function getTrendsQuestions($data){
        $this->db->select('q.id_question,q.parent_question_id,ql.question_text,q.topic_id,q.question_type,q.provider_visibility');
        $this->db->from('question_language ql');
        $this->db->join('question q','ql.question_id = q.id_question','');
        $this->db->where('q.topic_id',$data['topic_id']);
        if($data['external_user']){
            $this->db->where('q.provider_visibility = 1');
        }
        $this->db->where('q.question_status = 1');
        $this->db->group_by('q.id_question');
        // $this->db->order_by('q.question_order','asc');
        $result = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $result->result_array();
    }

    public function getTrendsTopicScore($data){
        // echo '<pre>'.print_r($data);
        $this->db->select('t.id_topic,t.topic_score')->from('topic t');
        // $this->db->join('topic t','q.topic_id = t.id_topic','left');
        $this->db->where_in('t.module_id',$data['module_ids']);
        $this->db->where('t.parent_topic_id',$data['parent_topic_id']);
        $result = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $result->result_array();
    }

    public function getTrendsQuestionAnsweres($data){
        if($data['question_type'] == 'input' || $data['question_type'] == 'date'){
            //If question type is input or date then no need of joining the option tables
            if($data['is_validator']==1){
                $this->db->select('q.id_question, cqr.v_question_answer question_answere,provider_visibility');
            }
            else{
                $this->db->select('q.id_question, cqr.question_answer question_answere,provider_visibility');
            }
            $this->db->from('contract_question_review cqr');
            $this->db->join('question q','cqr.question_id = q.id_question','left');
        }else{
            //If question type is not an input and date then joining the option tables is nessesury 
            $this->db->select('q.id_question, qol.option_name question_answere,provider_visibility');
            $this->db->from('contract_question_review cqr');
            if($data['is_validator']==1){
                $this->db->join('question_option_language qol','cqr.v_question_option_id = qol.question_option_id','left');
            }
            else{
                $this->db->join('question_option_language qol','cqr.question_option_id = qol.question_option_id','left');
            }
            $this->db->join('question_option qo','qo.id_question_option = qol.question_option_id','left');
            $this->db->join('question q','qo.question_id = q.id_question','left');
        }
        $this->db->where_in('q.topic_id',$data['topic_ids']);
        if(isset($data['parent_question_id'])){
            $this->db->where('q.parent_question_id',$data['parent_question_id']);
        }
        $this->db->group_by('q.id_question');
        $this->db->order_by('q.question_order','asc');
        $result = $this->db->get();
        // if($data['question_type'] == 'input' || $data['question_type'] == 'date')
        // echo '<pre>'.
        return $result->result_array();
    }

    public function cloneModuleTopicQuestionForContract($data)
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('id_customer',$data['customer_id']);
        $query = $this->db->get();
        $customer = $query->result_array();

        $this->db->select('tm.*,m.*,group_concat(ml.module_name SEPARATOR "@@@") as module_name,group_concat(ml.language_id SEPARATOR "@@@") as language_id');
        $this->db->from('template_module tm');
        $this->db->join('module m','tm.module_id=m.id_module','left');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->where('tm.template_id',$customer[0]['template_id']);
        $this->db->where('tm.status',1);
        $this->db->group_by('tm.module_id');
        $this->db->order_by('tm.module_order','ASC');
        $query = $this->db->get();
        $template_module = $query->result_array();
        if(isset($data['created_by']))
            $data['created_by']=$this->db->escape($data['created_by']);
        if(isset($data['contract_review_id']))
            $data['contract_review_id']=$this->db->escape($data['contract_review_id']);
        for($s=0;$s<count($template_module);$s++)
        {
            $this->db->query('insert into module(module_order,created_by,created_on,module_status,contract_review_id,parent_module_id)
                              values("' . $template_module[$s]['module_order'] . '",
                                     "' . $this->db->escape($data['created_by']) . '",
                                     "' . currentDate() . '",
                                     "' . $template_module[$s]['module_status'] . '",
                                     "' . $data['contract_review_id'] . '",
                                     "' . $template_module[$s]['module_id'] . '"
                                     )');

            $module_id = $this->db->insert_id();
            $module_name = explode('@@@',$template_module[$s]['module_name']);
            $language_id = explode('@@@',$template_module[$s]['language_id']);
            for($t=0;$t<count($module_name);$t++)
            {
                $this->db->query('insert into module_language(module_id,module_name,language_id)
                              values("' . $module_id . '",
                                     "' . $module_name[$t] . '",
                                     "' . $language_id[$t] . '"
                                     )');
            }



            $this->db->select('tmt.*,t.*,group_concat(tl.topic_name SEPARATOR "@@@") as topic_name,group_concat(tl.language_id  SEPARATOR "@@@") as language_id');
            $this->db->from('template_module_topic tmt');
            $this->db->join('topic t','tmt.topic_id=t.id_topic','left');
            $this->db->join('topic_language tl','t.id_topic=tl.topic_id','left');
            $this->db->where('tmt.template_module_id',$template_module[$s]['id_template_module']);
            $this->db->where('tmt.status',1);
            $this->db->group_by('tmt.id_template_module_topic');
            $this->db->order_by('tmt.topic_order','ASC');
            $query = $this->db->get();
            $template_module_topics = $query->result_array();

            for($sr=0;$sr<count($template_module_topics);$sr++)
            {
                $this->db->query('insert into topic(module_id,topic_order,created_by,created_on,topic_status)
                              values("' . $module_id . '",
                                     "' . $template_module_topics[$sr]['topic_order'] . '",
                                     "' . $this->db->escape($data['created_by']) . '",
                                     "' . currentDate() . '",
                                     "' . $template_module_topics[$sr]['topic_status'] . '"
                                     )');
                $topic_id = $this->db->insert_id();
                $topic_name = explode('@@@',$template_module_topics[$sr]['topic_name']);
                $topic_language_id = explode('@@@',$template_module_topics[$sr]['language_id']);

                for($t=0;$t<count($topic_name);$t++)
                {
                    $this->db->query('insert into topic_language(topic_id,topic_name,language_id)
                              values("' . $topic_id . '",
                                     "' . $topic_name[$t] . '",
                                     "' . $topic_language_id[$t] . '"
                                     )');
                }

                $this->db->select('tmtq.*,q.*,group_concat(ql.question_text SEPARATOR "@@@") as question_text,group_concat(ql.request_for_proof SEPARATOR "@@@") as request_for_proof,group_concat(ql.language_id SEPARATOR "@@@") as language_id');
                $this->db->from('template_module_topic_question tmtq');
                $this->db->join('question q','tmtq.question_id=q.id_question','left');
                $this->db->join('question_language ql','q.id_question=ql.question_id','left');
                $this->db->where('tmtq.template_module_topic_id',$template_module_topics[$sr]['id_template_module_topic']);
                $this->db->where('tmtq.status',1);
                $this->db->group_by('tmtq.id_template_module_topic_question');
                $this->db->order_by('tmtq.question_order','ASC');
                $query = $this->db->get();
                $template_module_topic_questions = $query->result_array();
                for($st=0;$st<count($template_module_topic_questions);$st++)
                {
                    $this->db->query('insert into question(topic_id,question_order,question_required,question_type,created_by,created_on,question_status,parent_question_id)
                              values("' . $topic_id . '",
                                     "' . $template_module_topic_questions[$st]['question_order'] . '",
                                     "' . $template_module_topic_questions[$st]['question_required'] . '",
                                     "' . $template_module_topic_questions[$st]['question_type'] . '",
                                     "' . $this->db->escape($data['created_by']) . '",
                                     "' . currentDate() . '",
                                     "' . $template_module_topic_questions[$st]['question_status'] . '",
                                     "' . $template_module_topic_questions[$st]['id_question'] . '"
                                     )');
                    $question_id = $this->db->insert_id();
                    $question_name = explode('@@@',$template_module_topic_questions[$st]['question_text']);
                    $question_proof = explode('@@@',$template_module_topic_questions[$st]['request_for_proof']);
                    $question_language_id = explode(',',$template_module_topic_questions[$st]['language_id']);
                    for($t=0;$t<count($question_name);$t++)
                    {
                        $this->db->query('insert into question_language(question_id,question_text,request_for_proof,language_id)
                              values("' . $question_id . '",
                                     "' . $question_name[$t] . '",
                                     "' . $question_proof[$t] . '",
                                     "' . $question_language_id[$t] . '"
                                     )');
                    }

                    $this->db->select('qo.*,group_concat(qol.option_name SEPARATOR "@@@") as option_name,group_concat(qol.language_id SEPARATOR "@@@") as language_id,group_concat(qol.status SEPARATOR "@@@") as status');
                    $this->db->from('question_option qo');
                    $this->db->join('question_option_language qol','qo.id_question_option=qol.question_option_id','left');
                    $this->db->where('qo.question_id',$template_module_topic_questions[$st]['id_question']);
                    $this->db->where('qo.status',1);
                    $this->db->group_by('qo.id_question_option');
                    $query = $this->db->get();
                    $question_options = $query->result_array();

                    for($sth=0;$sth<count($question_options);$sth++)
                    {
                        $this->db->query('insert into question_option(question_id,option_value,status,created_by,created_on)
                              values("' . $question_id . '",
                                     "' . $question_options[$sth]['option_value'] . '",
                                     "' . $question_options[$sth]['status'] . '",
                                     "' . $this->db->escape($data['created_by']) . '",
                                     "' . currentDate() . '"
                                     )');
                        $question_option_id = $this->db->insert_id();
                        $option_name = explode('@@@',$question_options[$sth]['option_name']);
                        $option_language_id = explode('@@@',$question_options[$sth]['language_id']);
                        $status = explode('@@@',$question_options[$sth]['status']);
                        for($t=0;$t<count($option_name);$t++)
                        {
                            $this->db->query('insert into question_option_language(question_option_id,option_name,language_id,status)
                              values("' . $question_option_id . '",
                                     "' . $option_name[$t] . '",
                                     "' . $option_language_id[$t] . '",
                                     "' . $status[$t] . '"
                                     )');
                        }
                    }
                }
            }
        }
    }
    public function cloneModuleTopicQuestionForContractNew($data)
    {
        //echo '<pre>'.print_r($data);exit;
        // $this->db->select('*');
        // $this->db->from('customer');
        // $this->db->where('id_customer',$data['customer_id']);
        // $query = $this->db->get();
        // $customer = $query->result_array();
        $data['contract_review_id']=($data['contract_review_id']);
        $data['created_by']=($data['created_by']);
        $data['parent_relationship_category_id']=($data['parent_relationship_category_id']);
        if(isset($data['is_workflow']) && isset($data['id_contract_workflow'])){
            if($data['is_workflow'] == 1){
                $storeproc='CALL dumpModulesForContractWorkflow("'.$data['template_id'].'","'.$data['contract_review_id'].'","'.$data['created_by'].'","'.currentDate().'","'.$data['parent_relationship_category_id'].'")';
            }else{
                //$this->db->where(array('id_contract'=>$data['contract_id']))->update('contract',array('is_lock'=>1));
                $storeproc='CALL dumpModulesForContractReview("'.$data['template_id'].'","'.$data['contract_review_id'].'","'.$data['created_by'].'","'.currentDate().'","'.$data['parent_relationship_category_id'].'")';
            }
        }else{
            //$this->db->where(array('id_contract'=>$data['contract_id']))->update('contract',array('is_lock'=>1));
            $storeproc='CALL dumpModulesForContractReview("'.$data['template_id'].'","'.$data['contract_review_id'].'","'.$data['created_by'].'","'.currentDate().'","'.$data['parent_relationship_category_id'].'")';
        }
        $res_insert=$this->db->query($storeproc);
        // echo 

        if($res_insert){

            $this->db->select('*');
            $this->db->from('module');
            $this->db->where('contract_review_id',$data['contract_review_id']);
            $this->db->where('static',1);
            $query = $this->db->get();
            $modules = $query->result_array();
            //$contract_reviews = $this->db->get_where('contract_review',array('contract_id'=>$data['contract_id']))->result_array();

            //Geting the workflow contract review id if it is a workflow otherwise getting only contract review id
            $this->db->select('*')->from('contract_review')->where('contract_id',$data['contract_id']);
            if(isset($data['is_workflow']) && isset($data['id_contract_workflow']) && $data['is_workflow'] == 1 && $data['id_contract_workflow'] > 0)
                $this->db->where('contract_workflow_id',$data['id_contract_workflow']);
            $contract_reviews = $this->db->get();//echo '<pre>'.
            $contract_reviews = $contract_reviews->result_array();

            if(count($contract_reviews)>1){
                foreach($modules as $key=>$val){
                    $static_query="insert into contract_question_review (contract_review_id,question_id,question_answer,v_question_answer,question_feedback,v_question_feedback,updated_by,updated_on,parent_question_id,question_option_id,v_question_option_id) (
                                    select m.contract_review_id,q.id_question as question_id,cqr2.question_answer,cqr2.v_question_answer,cqr2.question_feedback,cqr2.v_question_feedback,? updated_by, now() updated_on,q.parent_question_id,qo.id_question_option,vqo.id_question_option from contract_question_review cqr2
                                    join question q2 on q2.id_question=cqr2.question_id
                                    join topic t2 on t2.id_topic=q2.topic_id
                                    join module m2 on m2.id_module=t2.module_id and m2.id_module=(select max(m2x.id_module) from module mx join contract_review crx on crx.id_contract_review=mx.contract_review_id,module m2x join contract_review cr2x on cr2x.id_contract_review=m2x.contract_review_id where crx.contract_id=cr2x.contract_id and mx.parent_module_id=m2x.parent_module_id and m2x.id_module<? and mx.id_module=?)
                                    join question q on q.parent_question_id=q2.parent_question_id
                                    left join question_option qo on q.id_question = qo.question_id AND cqr2.question_answer = qo.option_value
                                    left join question_option vqo on q.id_question = vqo.question_id AND cqr2.v_question_answer = vqo.option_value
                                    join topic t on t.id_topic=q.topic_id
                                    join module m on m.id_module=t.module_id and m.id_module=?
                                    where m.static=1  GROUP BY question_id)";
                    $this->db->query($static_query,array($data['created_by'],$val['id_module'],$val['id_module'],$val['id_module']));
                    // echo '<pre>'.$this->db->last_query();
                }
            }

        }
    }

    public function getContractReviewActionItemsList($data)
    {
        $this->db->select('crai.*,CONCAT_WS(\' \',u.first_name,u.last_name) as responsible_user_name,u1.user_role_id');
        $this->db->from('contract_review_action_item crai');
        $this->db->join('contract c','crai.contract_id = c.id_contract','left');
        $this->db->join('contract_review cr','cr.id_contract_review=crai.contract_review_id','LEFT');
        $this->db->join('user u','u.id_user=crai.responsible_user_id','LEFT');
        $this->db->join('user u1','u1.id_user=crai.created_by');
        if(isset($data['contract_workflow_id']))
            $this->db->where('crai.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['id_contract']))
            $this->db->where('crai.contract_id',$data['id_contract']);
        if(isset($data['page_type']) && $data['page_type']='contract_review'){
            if(isset($data['id_module']))
                $this->db->where('crai.module_id IN (select m.id_module from contract_review cr JOIN module m on m.contract_review_id=cr.id_contract_review join module m2 on m2.parent_module_id=m.parent_module_id where cr.contract_id=crai.contract_id and m2.id_module='.$this->db->escape($data['id_module']).')');
            if(isset($data['topic_id']))
                $this->db->where('crai.topic_id IN (select t.id_topic from contract_review cr LEFT JOIN module m on m.contract_review_id=cr.id_contract_review JOIN topic t on t.module_id=m.id_module JOIN topic t2 on t2.parent_topic_id=t.parent_topic_id where cr.contract_id=crai.contract_id and t2.id_topic='.$this->db->escape($data['topic_id']).')');
        }
        else {
            if (isset($data['id_module']))
                $this->db->where('crai.module_id', $data['id_module']);
            if (isset($data['id_contract_review']))
                $this->db->where('cr.id_contract_review', $data['id_contract_review']);
        }
        if(isset($data['item_status']))
            $this->db->where('crai.item_status',$data['item_status']);
        if(isset($data['status']))
            $this->db->where('crai.status',$data['status']);
        // if(isset($data['responsible_user_id']) && $data['user_role_id']!=2)
        //     $this->db->where('crai.responsible_user_id',$data['responsible_user_id']);
        if(isset($data['contract_id'])) {
           // $this->db->where('cr.contract_id', $data['contract_id']);
            $this->db->where('crai.contract_id', $data['contract_id']);
        }
        if(isset($data['id_user']) && isset($data['user_role_id'])){
            if($data['user_role_id']==3){
                // 18-09-2019 // For owner on changing new action item logic after sprint 5 on 
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.contract_owner_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==4){
                // 18-09-2019 // For delegate on changing new action item logic after sprint 5 on 
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.delegate_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==2 || $data['user_role_id']==1){
                // $this->db->group_start();
                // $this->db->where('crai.created_by', $data['id_user']);
                // $this->db->or_where('u1.user_role_id>=', 2);
                // $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                // $this->db->group_end();
            }
            else if($data['user_role_id']==6){
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('u1.user_role_id>=', 2);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==7){
                $this->db->group_start();
                $this->db->where_in('crai.responsible_user_id', $data['provider_colleuges']);
                $this->db->or_where('crai.created_by', $data['id_user']);
                $this->db->group_end();
            }
        }
        // if(isset($data['responsible_user_id']) && $data['user_role_id']!=2) {
        //     $this->db->where('crai.responsible_user_id', $data['responsible_user_id']);
        // }
        $query = $this->db->get();//echo '<pre>'.
        $result= $query->result_array();

        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            $status_change_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                $view_access = "itako";
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['created_by'] == $data['id_user']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user'] || $v['created_by'] == $data['id_user']) {
                        $view_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    $view_access = "itako";
                    if ($v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user']|| $v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = 'itako';
            }
            //$view_access="itako;
            if($view_access=="itako" && $v['status']!='completed')
                $status_change_access="itako";
            if($v['status']=='completed')
                $edit_access=$delete_access='annus';
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
            $result[$k]['scaacs']=$status_change_access;

            $this->db->select('c.*,concat(u.first_name," ",u.last_name) as user_name');
            $this->db->from('contract_review_action_item_log c');
            $this->db->join('user u','c.updated_by=u.id_user','left');
            $this->db->where('c.contract_review_action_item_id', $v['id_contract_review_action_item']);
            $query_log = $this->db->get();
            $result[$k]['comments_log']= $query_log->result_array();
        }

        return $result;
    }

    public function getContractReviewModuleData($data){
        // print_r($data);exit;
        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';

        $this->db->select('m.*,ml.module_name');
        $this->db->from('module_language ml');
        $this->db->join('module m','m.id_module = ml.module_id','');
        $this->db->where('m.contract_review_id',$data['contract_review_id']);
        $this->db->where('m.id_module',$data['module_id']);
        $this->db->order_by('m.module_order','ASC');
        $result = $this->db->get()->result_array();
// echo '<pre>'.print_r($data);exit;
        foreach($result as $key=>$val){
            $send['contract_review_id']=$data['contract_review_id'];
            $send['module_id']=$val['id_module'];
            $send['dynamic_column']=$answer_column;
            //Sprint 5
            if((int)$data['contribution_type'] == 3)
                $send['provider_visibility'] = array(1);

            $result[$key]['review_user_name'] = '---';
            $result[$key]['last_review'] = NULL;

            $latest = $this->getContractReviewModulelatestUpdate(array('module_id' => $val['id_module'],'contract_review_id' => $data['contract_review_id']));
            // echo '<pre>'.print_r($latest);exit;
            $progress = $this->progress($send);
            $result[$key]['progress']=$progress;
            if(!empty($latest)) {
                $result[$key]['review_user_name'] = $latest[0]->name;
                if($latest[0]->name)
                    $result[$key]['last_review']= date('Y-m-d',strtotime($latest[0]->date));
            }
            else{
                $latest_recent = $this->getContractReviewRecentModulelatestUpdate(array('module_id' => $val['id_module'],'contract_review_id' => $data['contract_review_id']));
                if(!empty($latest_recent) && $data['task_type']!='subtask') {
                    $result[$key]['review_user_name'] = $latest_recent[0]->name;
                    if($latest_recent[0]->name)
                        $result[$key]['last_review']= date('Y-m-d',strtotime($latest_recent[0]->date));
                }
            }
            // echo 
            //'id_contract_review'=>$data['contract_review_id'],
            //$result[$key]['action_items']=$this->getContractReviewActionItemsList(array('id_module'=>$val['id_module'],'contract_id'=>$data['contract_id']));
            $result[$key]['contributors']=$this->getContractContributors(array('module_id'=>$val['id_module'],'contract_id'=>$data['contract_id'],'contract_review_id'=>$data['contract_review_id']));
            //echo '<pre>'.
            $result[$key]['contract_details']=$this->getContractDetails(array('contract_review_id'=>$data['contract_review_id'],'id_contract'=>$data['contract_id']));
            /*$this->db->select('t.id_topic,tl.topic_name');
            $this->db->from('topic t');
            $this->db->join('topic_language tl','tl.topic_id=t.id_topic and tl.language_id=1','LEFT');
            $this->db->where('t.module_id',$val['id_module']);
            $this->db->where('t.topic_status','1');
            $this->db->order_by('t.topic_order','ASC');*/
            $this->db->select('t.id_topic,tl.topic_name,count(q.id_question) questions_cnt');
            $this->db->from('topic t');
            $this->db->join('topic_language tl','tl.topic_id=t.id_topic and tl.language_id=1','LEFT');
            $this->db->join('question q','q.topic_id=t.id_topic and q.question_status=1','LEFT');
            $this->db->where('t.module_id',$val['id_module']);
            $this->db->where('t.topic_status','1');
            if($data['contribution_type'] == 2 || $data['contribution_type'] == 3)
                $this->db->where('q.provider_visibility',1);
            $this->db->group_by('t.id_topic');
            $this->db->having('count(q.id_question)>0');
            $this->db->order_by('t.topic_order','ASC');
            $topics = $this->db->get()->result_array();//echo '<pre>'.$this->db->last_query();
            $inc=0;
            $topic_ids=array();
            $topic_names=array();
            foreach($topics as $kt1=>$vt1){
                $topic_ids[$inc]=$vt1['id_topic'];
                $topic_names[$vt1['id_topic']]=$vt1['topic_name'];
                $inc=$inc+1;
            }
            if(isset($data['id_topic']))
                $current_topic_index = array_search($data['id_topic'], $topic_ids);
            else
                $current_topic_index=0;
            $result[$key]['topic_pagination']['previous']=isset($topic_ids[$current_topic_index-1])?$topic_ids[$current_topic_index-1]:NULL;
            $result[$key]['topic_pagination']['previous_text']=isset($topic_names[$result[$key]['topic_pagination']['previous']])?$topic_names[$result[$key]['topic_pagination']['previous']]:NULL;
            $result[$key]['topic_pagination']['current']=isset($topic_ids[$current_topic_index])?$topic_ids[$current_topic_index]:NULL;
            $result[$key]['topic_pagination']['current_text']=isset($topic_names[$result[$key]['topic_pagination']['current']])?$topic_names[$result[$key]['topic_pagination']['current']]:NULL;
            $result[$key]['topic_pagination']['next']=isset($topic_ids[$current_topic_index+1])?$topic_ids[$current_topic_index+1]:NULL;
            $result[$key]['topic_pagination']['next_text']=isset($topic_names[$result[$key]['topic_pagination']['next']])?$topic_names[$result[$key]['topic_pagination']['next']]:NULL;
            $result[$key]['topic_pagination']['count']=count($topic_ids);
            $result[$key]['topic_pagination']['current_count']=$current_topic_index+1;

            $this->db->select('t.*,tl.topic_name');
            $this->db->from('topic t');
            $this->db->join('topic_language tl','tl.topic_id = t.id_topic and tl.language_id=1','LEFT');
            $this->db->where('t.module_id',$val['id_module']);
            $this->db->where('t.topic_status','1');
            if(isset($data['id_topic'])) {
                $this->db->where('t.id_topic', $data['id_topic']);
            }
            else{
                $this->db->where('t.id_topic', isset($topic_ids[$current_topic_index])?$topic_ids[$current_topic_index]:0);
            }
			$this->db->order_by('t.topic_order','ASC');
            $topics = $this->db->get()->result_array();
			//echo '<pre>'.
            $result[$key]['topics']=$topics;
            foreach($result[$key]['topics'] as $kt=>$vt){
                $this->db->select('q.*,ql.question_text,ql.request_for_proof,cqr.second_opinion,cqr.question_feedback,cqr.v_question_feedback,cqr.external_user_question_feedback, count(l.id_contract_question_review_log) as question_change');
                $this->db->from('question q');
                $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','LEFT');
                $this->db->join('contract_question_review cqr','cqr.question_id = q.id_question and cqr.contract_review_id='.$this->db->escape($data['contract_review_id']),'LEFT');
                if(isset($data['last_review_id'])){
                    $this->db->select('IF((SELECT count(*) FROM contract_question_review WHERE contract_review_id=cqr.contract_review_id AND cqr.updated_by IS NOT NULL)>0,cqr.question_answer,cqr1.question_answer) as question_answer,IFNULL(cqr.question_feedback,cqr1.question_feedback) as question_feedback,IFNULL(cqr.external_user_question_feedback,cqr1.external_user_question_feedback) as external_user_question_feedback,IFNULL(cqr.v_question_feedback,cqr1.v_question_feedback) as v_question_feedback,IF((SELECT COUNT(*) FROM contract_question_review cqrw WHERE cqrw.contract_review_id=cqr.contract_review_id AND cqrw.updated_by IS NOT NULL)>0,(select parent_question_option_id from question_option where id_question_option=cqr.question_option_id),(select parent_question_option_id from question_option where id_question_option=cqr1.question_option_id))as parent_question_answer,IFNULL((select parent_question_option_id from question_option where id_question_option=cqr.v_question_option_id), (select parent_question_option_id from question_option where id_question_option=cqr1.v_question_option_id)) v_parent_question_answer11,cqr.question_option_id,cqr.v_question_option_id,IF((SELECT count(*) FROM contract_question_review ctqr  LEFT JOIN user u ON ctqr.updated_by=u.id_user LEFT JOIN question qst on ctqr.question_id=qst.id_question WHERE ctqr.contract_review_id=cqr.contract_review_id AND qst.topic_id=q.topic_id AND u.contribution_type=1)=0,(select parent_question_option_id from question_option where id_question_option=cqr1.v_question_option_id),(select parent_question_option_id from question_option where id_question_option=cqr.v_question_option_id)) as v_parent_question_answer,IF((SELECT count(*) FROM contract_question_review ctqr  LEFT JOIN user u ON ctqr.updated_by=u.id_user LEFT JOIN question qst on ctqr.question_id=qst.id_question WHERE ctqr.contract_review_id=cqr.contract_review_id AND qst.topic_id=q.topic_id AND u.contribution_type=1)=0,cqr1.v_question_answer,cqr.v_question_answer) as v_question_answer');
                    $this->db->join('contract_question_review cqr1','cqr1.parent_question_id = q.parent_question_id and cqr1.contract_review_id='.$this->db->escape($data['last_review_id']),'LEFT');
                }
                
                else{
                    $this->db->select('cqr.question_answer,cqr.v_question_answer,cqr.question_feedback,cqr.external_user_question_feedback,cqr.v_question_feedback,(select parent_question_option_id from question_option where id_question_option=cqr.question_option_id) as parent_question_answer,(select parent_question_option_id from question_option where id_question_option=cqr.v_question_option_id) as v_parent_question_answer,cqr.question_option_id,cqr.v_question_option_id');
                }
                $this->db->join('contract_question_review_log l','cqr.id_contract_question_review=l.contract_question_review_id','left');
                $this->db->where('q.topic_id',$vt['id_topic']);
                $this->db->where('q.question_status','1');
                if($data['contribution_type'] == 2 || $data['contribution_type'] == 3)
                    $this->db->where('q.provider_visibility',1);
                $this->db->group_by('q.id_question');
                $this->db->order_by('q.question_order','ASC');
                $this->db->order_by('q.id_question','ASC');

                $questions = $this->db->get()->result_array();
				// print_r($questions);     
                // echo 
                $result[$key]['topics'][$kt]['questions']=$questions;
                foreach($result[$key]['topics'][$kt]['questions'] as $ktq=>$vtq){

                    $this->db->select('q.*,ql.option_name');
                    $this->db->from('question_option q');
                    $this->db->join('question_option_language ql','ql.question_option_id = q.id_question_option and ql.language_id=1','LEFT');
                    $this->db->where('q.question_id',$vtq['id_question']);
                    $this->db->where('q.status','1');
                    $question_options = $this->db->get()->result_array();
                    // echo '<pre>'.
                    $remarks = $this->getContractReviewDisucussionLogData(array('contract_review_id'=>$data['contract_review_id'],'question_id'=>$vtq['id_question']));
                    //echo '<pre>'.print_r($remarks);exit;
                    //echo count($remarks);exit;
                    if(count($remarks)==0){
                        $remarks = $this->getContractReviewDiscussionQuestinRemarks(array('contract_review_id'=>$data['contract_review_id'],'question_id'=>$vtq['id_question']));
                    }
                    //echo $remarks[0]['remarks'];    
                    //echo '<pre>'.print_r($remarks);exit;
                    //echo '<pre>'.$this->db->last_query();
                    $result[$key]['topics'][$kt]['questions'][$ktq]['remarks'] = isset($remarks[0]['remarks'])?$remarks[0]['remarks']:null;
                    $result[$key]['topics'][$kt]['questions'][$ktq]['options']=$question_options;
                    foreach($result[$key]['topics'][$kt]['questions'][$ktq]['options'] as $ktqo=>$vtqo){
                        if($vtq['parent_question_answer']==$vtqo['parent_question_option_id'])
                            $result[$key]['topics'][$kt]['questions'][$ktq]['parent_question_answer']=$vtqo['id_question_option'];
                        if($vtq['v_parent_question_answer']==$vtqo['parent_question_option_id'])
                            $result[$key]['topics'][$kt]['questions'][$ktq]['v_parent_question_answer']=$vtqo['id_question_option'];
                    }
                    
                    $this->db->select('d.*');
                    $this->db->from('document d');
                    //$this->db->where('d.reference_id',$vtq['id_question']);
                    $this->db->where('d.reference_id IN (select q_sub.id_question from question q_sub LEFT JOIN question q2_sub on q2_sub.parent_question_id=q_sub.parent_question_id LEFT JOIN topic t2_sub on t2_sub.id_topic=q2_sub.topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id
LEFT JOIN contract c2_sub on c2_sub.id_contract=cr2_sub.contract_id and c2_sub.is_deleted=0 LEFT JOIN topic t1_sub on t1_sub.id_topic=q_sub.topic_id LEFT JOIN module m1_sub on m1_sub.id_module=t1_sub.module_id LEFT JOIN contract_review cr1_sub on cr1_sub.id_contract_review=m1_sub.contract_review_id where q2_sub.id_question='.$vtq['id_question'].' and `cr1_sub`.`contract_id` = `cr2_sub`.`contract_id`)',false,false);
                    $this->db->where('d.reference_type','question');
                    $this->db->where('d.document_status',1);
                    if($data["is_workflow"] == 1)
                        // $this->db->where("m.contract_review_id",$data["module_id"]);
                        $this->db->where("d.contract_workflow_id",$data['contract_workflow_id']); 
                    
                    $query = $this->db->get();
                    $attachment=$query->result_array();

                    $attachment_count = 0;
                    $v_attachment_count = 0;
                    foreach($attachment as $k => $v){
                        if((int)$v['validator_record'])
                            $v_attachment_count++;
                        else
                            $attachment_count++;
                    }
                    $result[$key]['topics'][$kt]['questions'][$ktq]['attachment_count'] = $attachment_count;
                    $result[$key]['topics'][$kt]['questions'][$ktq]['v_attachment_count'] = $v_attachment_count;
                }
            }
        }

        return $result;
    }

    public  function progress($data){
        /*foreach($data as $k=>$v){
            $data[$k]=$this->db->escape($v);
        }*/
        if(!isset($data['provider_visibility']))
            $data['provider_visibility'] = array(0,1);

        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';

        $q="select *,IFNULL(ROUND((b.answer_questions*100)/a.total_questions),0) percentage from
                            (select COUNT(q.id_question) as total_questions from module m
                            LEFT JOIN topic t on m.id_module=t.module_id
                            LEFT JOIN question q on t.id_topic=q.topic_id
                            where m.id_module=?  and q.question_status=1 and t.topic_status and q.provider_visibility in ?) a,
                            (select count(DISTINCT cqr.question_id) as answer_questions from module m
                            LEFT JOIN topic t on m.id_module=t.module_id
                            LEFT JOIN question q on t.id_topic=q.topic_id
                            JOIN contract_question_review cqr on q.id_question=cqr.question_id
                            where m.id_module=? and  cqr.$answer_column!='' and q.provider_visibility in ? and cqr.contract_review_id=? and q.question_status=1 and t.topic_status) b";
        $query = $this->db->query($q,array($data["module_id"],$data['provider_visibility'],$data["module_id"],$data['provider_visibility'],$data["contract_review_id"]));
        //  echo "<pre>";echo $this->db->last_query($query);echo "</pre>";exit;
        $result = $query->result();

        return $result[0]->percentage;


    }
    public function getActionItemResponsibleUsers($data=array()){
        if(!is_array($data['contract_id'])){
            $data['contract_id']=array('contract_id'=>$data['contract_id']);
        }
        $q='SELECT * from (
SELECT u.id_user,u.user_role_id,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name FROM `contract` c left join business_unit_user buu on buu.business_unit_id=c.business_unit_id
LEFT JOIN user u on u.id_user=buu.user_id
LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role
LEFT JOIN business_unit_user as buusr ON u.id_user=buusr.user_id and buusr.status = 1
LEFT JOIN business_unit as bu ON bu.id_business_unit=buusr.business_unit_id
 where  c.id_contract in ? and u.user_status=1 and u.user_role_id not in (2,5,6) and u.customer_id = ? AND buu.status=1
union ALL
select u.id_user,u.user_role_id, CONCAT(CONCAT_WS(" ",u.first_name,u.last_name), CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name from user u
LEFT JOIN customer c on c.id_customer=u.customer_id
left join business_unit_user buu on buu.user_id=u.id_user  and buu.status = 1
left join business_unit bu on bu.id_business_unit=buu.business_unit_id
LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role
left join contract cn on cn.business_unit_id=bu.id_business_unit and cn.is_deleted=0
where cn.id_contract in ?   and u.user_status=1 and u.user_role_id not in (2,5,6) and u.customer_id = ?
UNION ALL
select u.id_user,u.user_role_id,CONCAT(CONCAT_WS(" ",u.first_name,u.last_name),
 CONCAT(" (", CONCAT_WS(" | ", u.email, ur.user_role_name, bu.bu_name), ")")) as name
from user u
LEFT JOIN contract_user cu on cu.user_id=u.id_user
LEFT JOIN user_role ur ON u.user_role_id=ur.id_user_role
LEFT JOIN customer c on c.id_customer=u.customer_id
left join business_unit_user buu on buu.user_id=u.id_user and buu.status = 1
left join business_unit bu on bu.id_business_unit=buu.business_unit_id
LEFT JOIN provider p ON u.provider=p.id_provider
where cu.contract_review_id=?  and u.user_status=1 and cu.`status`=1 AND p.`status`=1 and u.customer_id = ?
) z group by z.id_user order by z.id_user asc';
        $query = $this->db->query($q,array($data["contract_id"],$data['customer_id'],$data["contract_id"],$data['customer_id'],$data["contract_review_id"],$data['customer_id']));
        // echo  die('asd');
        $result = $query->result_array();
        return $result;
    }

    public function contract_progress($data){
        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';

        $q="select *,IFNULL(ROUND((b.answer_questions*100)/a.total_questions,2),0) percentage from
        (select COUNT(q.id_question) as total_questions from module m
            LEFT JOIN topic t on m.id_module=t.module_id
            LEFT JOIN question q on t.id_topic=q.topic_id
            where m.id_module IN
        (select m.id_module from module m join contract_review cr on m.contract_review_id = cr.id_contract_review JOIN contract c on c.id_contract = cr.contract_id and c.id_contract = ? where c.is_deleted=0 and cr.id_contract_review = ?  and m.module_status > 0)  and q.question_type!='input') a,(select count(cqr.id_contract_question_review) as answer_questions from module m
            LEFT JOIN topic t on m.id_module=t.module_id
            LEFT JOIN question q on t.id_topic=q.topic_id
            JOIN contract_question_review cqr on q.id_question=cqr.question_id
            where m.id_module IN
        (select m.id_module from module m join contract_review cr on m.contract_review_id = cr.id_contract_review JOIN contract c on c.id_contract = cr.contract_id and c.id_contract = ? where c.is_deleted = 0 and m.module_status > 0) and cqr.$answer_column!='' and cqr.contract_review_id=? and q.question_type!='input') b";
        $query = $this->db->query($q,array($data['contract_id'],$data['contract_review_id'],$data['contract_id'],$data['contract_review_id']));
        $result = $query->result();

        return $result[0]->percentage;

    }

    public function getContractReviewModulelatestUpdate($data){
        $q='select concat(u.first_name," ",u.last_name) as name,cqr.updated_on as date,cqr.question_answer,cqr.question_feedback FROM
                                module m
                                JOIN topic t on m.id_module=t.module_id
                                JOIN question q on t.id_topic=q.topic_id
                                JOIN contract_question_review cqr on q.id_question=cqr.question_id
                                LEFT JOIN user u on cqr.updated_by=u.id_user
                                where m.id_module =? order by cqr.updated_on desc limit 1';
        $query = $this->db->query($q,array($data['module_id']));
        //echo '<pre>'.
        return $query->result();
    }
    public function getContractReviewRecentModulelatestUpdate($data){
        $q='select concat(u.first_name," ",u.last_name) as name,cqr.updated_on as date,cqr.question_answer,cqr.question_feedback FROM
                                module m
                                JOIN topic t on m.id_module=t.module_id
                                JOIN question q on t.id_topic=q.topic_id
                                JOIN contract_question_review cqr on q.id_question=cqr.question_id
                                LEFT JOIN user u on cqr.updated_by=u.id_user
                                where m.id_module =(select max(m.id_module) from module m,module m1,contract_review cr,contract_review cr1 where m.parent_module_id=m1.parent_module_id and m1.id_module=? and m1.id_module!=m.id_module and cr.contract_id=cr1.contract_id and m.contract_review_id=cr.id_contract_review and m1.contract_review_id=cr1.id_contract_review) order by cqr.updated_on desc limit 1';
        $query = $this->db->query($q,array($data['module_id']));
        return $query->result();
    }

    public function getContributors($data=array()){
        $this->db->select('u.id_user,CONCAT_WS(\' \',u.first_name,u.last_name) as user_name,u.email');
        $this->db->from('business_unit_user buu');
        $this->db->join('user u','u.id_user=buu.user_id','left');
        $this->db->join('business_unit bu','bu.id_business_unit=buu.business_unit_id','left');
        $this->db->join('user_role ur','ur.id_user_role=u.user_role_id and ur.role_status=1','left');
        $this->db->where('ur.user_role_name','Contributor');
        $this->db->where('buu.status','1');
        $this->db->where('bu.status','1');
        $this->db->where('u.user_status','1');
        if(isset($data['id_business_unit']))
            $this->db->where('buu.business_unit_id',$data['id_business_unit']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addContractContributors($data=array()){

        $contributors_add=$data['contributors_add'];
        $contributors_added=array();
        $contributors_remove=$data['contributors_remove'];
        $contributors_removed=array();
        $total=array();
        $this->db->select('cu.user_id,cu.id_contract_user,cu.status');
        $this->db->from('contract_user cu');
        $this->db->where('cu.module_id',$data['module_id']);
        $this->db->where('cu.contract_id',$data['contract_id']);
        $query = $this->db->get();
        $result=$query->result_array();
        foreach($result as $key=>$val){
            if($val['status']==0)
                $contributors_removed[]=$val['user_id'];
            if($val['status']==1)
                $contributors_added[]=$val['user_id'];
            $total[]=$val['user_id'];
        }

        $update_to_active=array_intersect($contributors_add,$contributors_removed);
        $new_inserts=array_filter(array_diff($contributors_add,$total));
        
        $update_to_remove=array_intersect($contributors_remove,$contributors_added);
        /*print_r($total);
        print_r($update_to_remove);
        print_r($new_inserts);exit;*/
        foreach($new_inserts as $k=>$v){
            $inner_data=array();
            if(isset($v) && !empty($v)) {
                $inner_data['user_id'] = $v;
                $inner_data['module_id'] = $data['module_id'];
                $inner_data['contract_id'] = $data['contract_id'];
                $inner_data['status'] = 1;
                $inner_data['created_by'] = $data['created_by'];
                $inner_data['created_on'] = $data['created_on'];
                if(isset($data['contract_review_id']))
                    $inner_data['contract_review_id'] = $data['contract_review_id'];
                $this->addContractContributor($inner_data);
            }
        }
        foreach($update_to_active as $k=>$v){
            $inner_data=array();
            if(isset($v) && !empty($v)) {
                $inner_data['user_id'] = $v;
                $inner_data['module_id'] = $data['module_id'];
                $inner_data['contract_id'] = $data['contract_id'];
                $inner_data['status'] = 1;
                $inner_data['created_by'] = $data['created_by'];
                $inner_data['created_on'] = $data['created_on'];
                if(isset($data['contract_review_id']))
                    $inner_data['contract_review_id'] = $data['contract_review_id'];
                $this->updateContractContributor($inner_data);
            }
        }
        foreach($update_to_remove as $k=>$v){
            $inner_data=array();
            if(isset($v) && !empty($v)) {
                $inner_data['user_id'] = $v;
                $inner_data['module_id'] = $data['module_id'];
                $inner_data['contract_id'] = $data['contract_id'];
                $inner_data['status'] = 0;
                $inner_data['updated_by'] = $data['created_by'];
                $inner_data['updated_on'] = $data['created_on'];
                if(isset($data['contract_review_id']))
                    $inner_data['contract_review_id'] = $data['contract_review_id'];
                // $this->updateContractContributor($inner_data);
                $this->db->where(array('contract_review_id'=>$data['contract_review_id'],'module_id'=>$data['module_id'],'user_id'=>$v));
                $this->db->delete('contract_user');
                if(isset($data['contract_review_id']))
                {
                    $userDetails = $this->User_model->check_record("user",array("id_user"=>$v));
                    if((!empty($userDetails[0]))&&($userDetails[0]['contribution_type'] == 1))
                    {
                        //this block will execute when user is an validator 
                        $contractReviewDetails = $this->User_model->check_record("contract_review",array("id_contract_review"=>$data['contract_review_id']));
                        if(!empty($contractReviewDetails[0]))
                        {
                            //if(($contractReviewDetails[0]['is_workflow'] == 1)&&($contractReviewDetails[0]['validation_status']==2))
                            if(($contractReviewDetails[0]['is_workflow'] == 1)&&($contractReviewDetails[0]['validation_status']==2 || $contractReviewDetails[0]['validation_status']==3))
                            {
                                //task making validtion_status = 1 while removing a validator 
                                $this->User_model->update_data("contract_review",array('validation_status'=>1),array("id_contract_review"=>$data['contract_review_id']));
                            }
                            //elseif(($contractReviewDetails[0]['is_workflow'] == 0)&&($contractReviewDetails[0]['validation_status']==2)) {
                            elseif(($contractReviewDetails[0]['is_workflow'] == 0)&&($contractReviewDetails[0]['validation_status']==2 || $contractReviewDetails[0]['validation_status']==3)){
                                //review 
                                $validators = $this->getvalidatorContributors(array('contract_review_id'=>$data['contract_review_id']));
                                if(count($validators)==0)
                                {
                                    $this->User_model->update_data("contract_review",array('validation_status'=>1),array("id_contract_review"=>$data['contract_review_id']));
                                }
                                //added for partial validation if existing validator is removed 
                                //previous all modules status are 3 then we are making validation_status =3
                                if(count($validators)>0)
                                {
                                    $validatormodules = $this->getValidatormodules(array('contribution_type'=>1,'contract_review_id'=>$data['contract_review_id']));
                                    $ToUpdate =1;
                                    foreach($validatormodules as $validatormodule){
                                        if((int)$validatormodule['module_status'] != 3)
                                        {
                                            $ToUpdate = 0;
                                            break;
                                        }
                                    }
                                    if(isset($ToUpdate)&&($ToUpdate==1))
                                    {
                                        $this->User_model->update_data("contract_review",array('validation_status'=>3),array("id_contract_review"=>$data['contract_review_id']));
                                    }
                                }
                            }
                        }
    
                    }
                }
            }
        }
        return $new_inserts;
        return 1;
    }
    public function getvalidatorContributors($data)
    {
        $this->db->select('c.*,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contributor_user_name');
        $this->db->from('contract_user c');
        $this->db->join('user u1','u1.id_user=c.user_id','left');
        $this->db->where('c.status',1);
        if(isset($data['contract_review_id']))
            $this->db->where('c.contract_review_id',$data['contract_review_id']);
        $this->db->where('u1.user_status',1);
        $this->db->where('u1.contribution_type',1);//Inernal user with Validator check on
        $query = $this->db->get();
        return  $query->result_array();
    }
    public function addContractContributor($data)
    {
        $this->db->insert('contract_user', $data);
        return $this->db->insert_id();
    }

    public function updateContractContributor($data)
    {
        if(isset($data['user_id']))
            $this->db->where('user_id', $data['user_id']);
        if(isset($data['module_id']))
            $this->db->where('module_id', $data['module_id']);
        if(isset($data['contract_id']))
            $this->db->where('contract_id', $data['contract_id']);
        if(isset($data['contract_id']))
            $this->db->where('contract_id', $data['contract_id']);
        if(isset($data['id_contract_user']))
            $this->db->where('id_contract_user', $data['id_contract_user']);

        $this->db->update('contract_user', $data);
        return 1;
    }

    public function getReviewQuestionAnswer($data)
    {
        $this->db->select('*');
        $this->db->from('contract_question_review cr');
        if(isset($data['contract_review_id']))
            $this->db->where('cr.contract_review_id',$data['contract_review_id']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function addReviewQuestionAnswer_bulk($data)
    {
        $this->db->insert_batch('contract_question_review', $data);
        return 1;
    }

    public function updateReviewQuestionAnswer($data)
    {
        if(isset($data['contract_review_id']))
            $this->db->where('contract_review_id', $data['contract_review_id']);
        if(isset($data['question_id']))
            $this->db->where('question_id', $data['question_id']);
        $this->db->update('contract_question_review', $data);
        return 1;
    }

    public function getContractContributors($data)
    {
        $contribution_type = array('expert','validator','provider');
        $total_contribution_count=0;
        foreach($contribution_type as $v){
            if($v == 'expert'){
                $this->db->select('c.*,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contributor_user_name');
                $this->db->from('contract_user c');
                $this->db->join('user u1','u1.id_user=c.user_id','left');
                $this->db->where('c.status',1);
                if(isset($data['contract_id']))
                    $this->db->where('c.contract_id',$data['contract_id']);
                if(isset($data['module_id']))
                    $this->db->where('c.module_id',$data['module_id']);
                if(isset($data['user_id']))
                    $this->db->where('c.user_id',$data['user_id']);
                if(isset($data['contract_review_id']))
                    $this->db->where('c.contract_review_id',$data['contract_review_id']);
                $this->db->where('u1.user_status',1);
                $this->db->where('u1.contribution_type',0);//Internal user with Validator check off

                $query = $this->db->get();//echo '<pre>'.

                $contributors[$v]['data'] = $query->result_array();
            }
            if($v == 'validator'){
                $this->db->select('c.*,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contributor_user_name');
                $this->db->from('contract_user c');
                $this->db->join('user u1','u1.id_user=c.user_id','left');
                $this->db->where('c.status',1);
                if(isset($data['contract_id']))
                    $this->db->where('c.contract_id',$data['contract_id']);
                if(isset($data['module_id']))
                    $this->db->where('c.module_id',$data['module_id']);
                if(isset($data['user_id']))
                    $this->db->where('c.user_id',$data['user_id']);
                if(isset($data['contract_review_id']))
                    $this->db->where('c.contract_review_id',$data['contract_review_id']);
                $this->db->where('u1.user_status',1);
                $this->db->where('u1.contribution_type',1);//Inernal user with Validator check on

                $query = $this->db->get();//echo '<pre>'.

                $contributors[$v]['data'] = $query->result_array();
            }
            if($v == 'provider'){
                $this->db->select('c.*,CONCAT_WS(\' \',u1.first_name,u1.last_name) as contributor_user_name');
                $this->db->from('contract_user c');
                $this->db->join('user u1','u1.id_user=c.user_id','left');
                $this->db->where('c.status',1);
                if(isset($data['contract_id']))
                    $this->db->where('c.contract_id',$data['contract_id']);
                if(isset($data['module_id']))
                    $this->db->where('c.module_id',$data['module_id']);
                if(isset($data['user_id']))
                    $this->db->where('c.user_id',$data['user_id']);
                if(isset($data['contract_review_id']))
                    $this->db->where('c.contract_review_id',$data['contract_review_id']);
                $this->db->where('u1.user_status',1);
                $this->db->where('u1.contribution_type',3);//External user with provider check on

                $query = $this->db->get();//echo '<pre>'.

                $contributors[$v]['data'] = $query->result_array();
            }
        }
        // $total_contribution_count=count($contributors['expert']['data'])+count($contributors['validator']['data'])+count($contributors['provider']['data']);
        // return array('contributors'=>$contributors,'total_contribution_count'=>$total_contribution_count);
        return $contributors;
    }

    public function updateContractReview($data)
    {
        if(isset($data['id_contract_review']))
            $this->db->where('id_contract_review', $data['id_contract_review']);
        $this->db->update('contract_review', $data);
        return 1;
    }

    public function getContractModuleChanges($data)
    {
        $this->db->select('*');
        $this->db->from('module m');
        $this->db->join('topic t','m.id_module=t.module_id','');
        $this->db->join('question q','t.id_topic=q.topic_id','');
        $this->db->join('contract_question_review_log l','q.id_question=l.question_id','');
        if(isset($data['contract_review_id']))
            $this->db->where('l.contract_review_id',$data['contract_review_id']);
        if(isset($data['module_id']))
            $this->db->where('m.id_module',$data['module_id']);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getCurrentReview($data){
        $this->db->select('review_score,id_contract_review contract_review_id,contract_workflow_id,t.template_name,cr.created_on,cr.contract_review_status')->from('contract_review cr');
        $join_column_on = '';
        if(isset($data['is_workflow']) && $data['is_workflow'] > 0){
            $join_column_on = 't.id_template = cw.workflow_id';
            $this->db->join('contract_workflow cw','cr.contract_workflow_id = cw.id_contract_workflow','left');
        }
        else{
            $join_column_on = 't.id_template = c.template_id';
            $this->db->join('contract c','cr.contract_id = c.id_contract','left');
        }
        $this->db->join('template t ',$join_column_on,'');
        $this->db->where('cr.id_contract_review',$data['contract_review_id']);
        $this->db->where('cr.contract_workflow_id',$data['contract_workflow_id']);
        //$this->db->where('cr.contract_workflow_id',$data['contract_workflow_id']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getLastReviewByContractId($data)
    {
        $this->db->select('cr.id_contract_review,IFNULL(max(cr.updated_on), max(cr.created_on)) as review_on,IFNULL(concat(u.first_name," ",u.last_name),"---") as review_by,cr.validation_status,cr.contract_review_status');
        $this->db->from('contract_review cr');
        $this->db->join('contract c','cr.contract_id=c.id_contract','');
        $this->db->join('user u','cr.updated_by=u.id_user','left');
        $this->db->where('cr.contract_id',$data['contract_id']);
        if(isset($data['contract_workflow_id']))
            $this->db->where('cr.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['contract_review_status']))
            $this->db->where('cr.contract_review_status',$data['contract_review_status']);
        if(isset($data['is_workflow']))
            $this->db->where('cr.is_workflow',$data['is_workflow']);
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('cr.id_contract_review');
        if(isset($data['order']))
            $this->db->order_by('cr.id_contract_review',$data['order']);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result_array();
    }

    /*public function getContractDashboard($data)
    {
        $query = $this->db->query("call getContractReviewTopicScore(".$data['contract_review_id'].")");
        $result = $query->result_array();
        $this->Mcommon->clean_mysqli_connection($this->db->conn_id);
        return $result;
    }*/
    public function getContractDashboard1($data)
    {
        $this->db->flush_cache();
        $query="select
	module_id,module_name,topic_id,topic_name,
	(
    CASE
        WHEN topic_avg_weight_score = 'N/A' THEN 'N/A'
        WHEN topic_avg_weight_score >= 0.75 THEN 'Green'
        WHEN topic_avg_weight_score >= 0.50 THEN 'Amber'
        WHEN topic_avg_weight_score >= 0 THEN 'Red'
        WHEN topic_avg_weight_score < 0 THEN ''
        ELSE 'N/A'
    END) AS topic_score,topic_avg_weight_score,str,total_topic_progress

 from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.question_answer is null then -1 else cqr.question_answer END)))/(sum(q.question_weight)-sum(case when cqr.question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
ROUND((sum(case when cqr.question_answer is null then 0 else (case when cqr.question_answer = 'NA' then q.question_weight else cqr.question_answer END) END)/(sum(case when cqr.question_answer is null then 0 else 1 END))) *100) as total_topic_progress from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and t.type='general'
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (CASE
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('Yes-Yes-Yes','Yes-Yes-No','Yes-Yes-N/A','Yes-Yes-B','Yes-No-Yes','Yes-N/A-Yes','Yes-B-Yes','No-Yes-Yes','No-Yes-No','No-Yes-N/A','No-Yes-B','No-No-Yes') THEN '0.75'
		WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('Yes-No-No','Yes-No-N/A','Yes-No-B','Yes-N/A-No','Yes-B-No','Yes-N/A-N/A','Yes-N/A-B','Yes-B-N/A','Yes-B-B','No-N/A-Yes','No-B-Yes','No-N/A-N/A','No-B-N/A','No-N/A-B','No-B-B') THEN '0.50'
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('N/A-N/A-N/A') THEN 'N/A'
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('B-B-B') THEN -1
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('No-No-No','No-N/A-No','No-B-No','No-No-N/A','No-No-B') OR GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') like 'N/A-%' THEN '0'
		ELSE '-1'
    END) WHEN t.type='relationship' THEN (relationship_score_calculation(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) as topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,0 total_topic_progress
from question q
LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
left join question_option qo on q.id_question=qo.question_id and cqr.question_answer=qo.option_value
LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ?
and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A2 order by module_order asc,topic_order asc )temp";
        //$this->Mcommon->clean_mysqli_connection($this->db->conn_id);
        $query = $this->db->query($query,array(($data['contract_review_id']),($data['contract_review_id'])));
        $result =  $query->result_array();
        //$this->Mcommon->clean_mysqli_connection($this->db->conn_id);
        return $result;
    }
    public function getContractDashboard_old($data)
    {
        $this->db->flush_cache();
        /*$query="select
	module_id,module_name,topic_id,topic_name,simple_score_calculation AS topic_score,topic_avg_weight_score,str,total_topic_progress
  from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.question_answer is null then -1 else cqr.question_answer END)))/(sum(q.question_weight)-sum(case when cqr.question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,
m.module_order,t.topic_order,'' str,
ROUND((sum(case when cqr.question_answer is null then 0 else (case when cqr.question_answer = 'NA' then q.question_weight else cqr.question_answer END) END)/(sum(case when cqr.question_answer is null then 0 else 1 END))) *100) as total_topic_progress,count(q.id_question),GROUP_CONCAT((case when cqr.question_answer=0 then 'R' when cqr.question_answer=1 then 'G' when cqr.question_answer is null then 'E' when cqr.question_answer='NA' then 'N' else 'A' END) SEPARATOR ' '),
simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.question_answer is null then 'E' when cqr.question_answer='NA' then 'N' when cqr.question_answer=0 then 'R' when cqr.question_answer=1 then 'G' else 'A' END) SEPARATOR ' ')) as simple_score_calculation
 from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
where q.question_type != 'input' and m.contract_review_id = ?
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1)temp";*/
        $query="select
        module_id,module_name,static,module_order,module_status,is_workflow,topic_id,topic_name,final_score(topic_avg_weight_score) AS topic_score,topic_avg_weight_score,str,total_topic_progress

        from(
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.question_answer is null then -1 else cqr.question_answer END)))/(sum(q.question_weight)-sum(case when cqr.question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.question_answer is null then 0 else (case when cqr.question_answer = 'NA' then q.question_weight else cqr.question_answer END) END)/(sum(case when cqr.question_answer is null then 0 else 1 END))) *100) as total_topic_progress from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.question_answer != 'NA'
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='general' and q.provider_visibility in ?
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.question_answer is null then 'E' when cqr.question_answer='NA' then 'N' when cqr.question_answer=0 then 'R' when cqr.question_answer=1 then 'G' else 'A' END) SEPARATOR ' ')) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.question_answer is null then 0 else (case when cqr.question_answer = 'NA' then q.question_weight else cqr.question_answer END) END)/(sum(case when cqr.question_answer is null then 0 else 1 END))) *100) as total_topic_progress
        from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.question_answer != 'NA'
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='simple' and q.provider_visibility in ?
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (data_score_calculation(GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) WHEN t.type='relationship' THEN (relationship_score_calculation_new(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) as topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,0 total_topic_progress
        from question q
        LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.question_answer != 'NA'
        left join question_option qo on q.id_question=qo.question_id and cqr.question_answer=qo.option_value
        LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        where q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and q.provider_visibility in ?
        and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp";
		//echo $query;
        $query = $this->db->query($query,array($data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility']));
        //echo 
        $result =  $query->result_array();
        return $result;
    } 
	
	public function getContractDashboard($data)
    {
        // print_r($data);exit;
        $this->db->flush_cache();
        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';
        $query="select
        module_id,module_name,static,module_order,module_status,is_workflow,topic_id,topic_name,topic_avg_weight_score AS topic_score,topic_avg_weight_score,str,total_topic_progress

        from(
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,t.topic_score as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.$answer_column is null then 0 else (case when cqr.$answer_column = 'NA' then q.question_weight else cqr.$answer_column END) END)/(sum(case when cqr.$answer_column is null then 0 else 1 END))) *100) as total_topic_progress from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
        where #q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='general' and q.provider_visibility in ? and q.question_status = 1
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,t.topic_score as topic_avg_weight_score,m.module_order,t.topic_order,'' str,
        ROUND((sum(case when cqr.$answer_column is null then 0 else (case when cqr.$answer_column = 'NA' then q.question_weight else cqr.$answer_column END) END)/(sum(case when cqr.$answer_column is null then 0 else 1 END))) *100) as total_topic_progress
        from question q
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
        where #q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and t.type='simple' and q.provider_visibility in ? and q.question_status = 1
        GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
        UNION ALL
        select * from (
        select
        t.module_id,ml.module_name,m.module_status,m.static,m.is_workflow,q.topic_id,tl.topic_name,t.topic_score as topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,0 total_topic_progress
        from question q
        LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
        left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
        left join question_option qo on q.id_question=qo.question_id and cqr.$answer_column=qo.option_value
        LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
        join topic t on t.id_topic = q.topic_id
        join topic_language tl on tl.topic_id = t.id_topic
        join module m on m.id_module = t.module_id
        join module_language ml on ml.module_id = m.id_module
        where #q.question_type != 'input' and q.question_type != 'date' and
        m.contract_review_id = ? and q.provider_visibility in ? and q.question_status = 1
        and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp";
		//echo $query;
        $query = $this->db->query($query,array($data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility']));
        // echo 
        $result =  $query->result_array();
        return $result;
    }

    /*public function getContractReviewModuleScore($data)
    {
        $query = $this->db->query("call getContractModuleScore(".$data['contract_review_id'].")");
        $result =  $query->result_array();
        $this->Mcommon->clean_mysqli_connection($this->db->conn_id);
        return $result;
    }*/
    public function getContractReviewModuleScore1($data)
    {

        $query="select
	module_id,module_name,topic_id,topic_name,topic_avg_weight_score,
	COUNT(CASE WHEN topic_avg_weight_score is NULL OR topic_avg_weight_score='N/A'  THEN 1 END) AS na_total,
	COUNT(CASE WHEN topic_avg_weight_score >= 0.75 AND topic_avg_weight_score!='N/A' THEN 1 END) AS green_total,
	COUNT(CASE WHEN topic_avg_weight_score >= 0.50 and topic_avg_weight_score < 0.75 AND topic_avg_weight_score!='N/A' THEN 1 END) AS amber_total,
	COUNT(CASE WHEN topic_avg_weight_score >= 0 and topic_avg_weight_score < 0.50 AND topic_avg_weight_score!='N/A' THEN 1 END) AS red_total,
	COUNT(CASE WHEN topic_avg_weight_score < 0 AND topic_avg_weight_score!='N/A' THEN 1 END) AS no_answer_total,parent_module_id
	#(CASE WHEN topic_avg_weight_score >= 0.75 THEN count(topic_id) END) AS green_total,
	#(CASE WHEN topic_avg_weight_score >= 0.50 and topic_avg_weight_score < 0.75 THEN (topic_id) END) AS amber_total,
	#(CASE WHEN topic_avg_weight_score = 0 THEN (topic_id) END) AS red_total,
	#(CASE WHEN topic_avg_weight_score is NULL THEN (topic_id) END) AS na_total,


 from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.question_answer is null then -1 else cqr.question_answer END)))/(sum(q.question_weight)-sum(case when cqr.question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,m.parent_module_id from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and t.type='general'
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (CASE
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('Yes-Yes-Yes','Yes-Yes-No','Yes-Yes-N/A','Yes-Yes-B','Yes-No-Yes','Yes-N/A-Yes','Yes-B-Yes','No-Yes-Yes','No-Yes-No','No-Yes-N/A','No-Yes-B','No-No-Yes') THEN '0.75'
		WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('Yes-No-No','Yes-No-N/A','Yes-No-B','Yes-N/A-No','Yes-B-No','Yes-N/A-N/A','Yes-N/A-B','Yes-B-N/A','Yes-B-B','No-N/A-Yes','No-B-Yes','No-N/A-N/A','No-B-N/A','No-N/A-B','No-B-B') THEN '0.50'
		WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('N/A-N/A-N/A') THEN 'N/A'
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('B-B-B') THEN -1
        WHEN GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') IN ('No-No-No','No-N/A-No','No-B-No','No-No-N/A','No-No-B') OR GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') like 'N/A-%' THEN '0'
				ELSE '-1'
    END) WHEN t.type='relationship' THEN (relationship_score_calculation(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,m.parent_module_id
from question q
LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
left join question_option qo on q.id_question=qo.question_id and cqr.question_answer=qo.option_value
LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ?
and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A2 order by module_order asc,topic_order asc )temp
GROUP BY module_id";
        $query = $this->db->query($query,array($data['contract_review_id'],$data['contract_review_id']));
        $result =  $query->result_array();
        //echo "<pre>";print_r($result);echo "</pre>";exit;
        //echo $this->db->last_query();
        //$this->Mcommon->clean_mysqli_connection($this->db->conn_id);
        return $result;
    }
    public function getContractReviewModuleScore($data)
    {
        /*
            $query="select temp1.module_id,temp1.module_name,COUNT(CASE WHEN topic_score='Red'  THEN 1 END) AS red_total,
            COUNT(CASE WHEN topic_score='Amber'  THEN 1 END) AS amber_total,
            COUNT(CASE WHEN topic_score='Green'  THEN 1 END) AS green_total,
            COUNT(CASE WHEN topic_score='N/A'  THEN 1 END) AS na_total,
            COUNT(CASE WHEN topic_score=''  THEN 1 END) AS no_answer_total,parent_module_id from (
            select
                module_id,module_name,topic_id,topic_name,simple_score_calculation AS topic_score,topic_avg_weight_score,str,total_topic_progress,parent_module_id
            from(
            select * from (
            select
                t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.question_answer is null then -1 else cqr.question_answer END)))/(sum(q.question_weight)-sum(case when cqr.question_answer = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,
            m.module_order,t.topic_order,'' str,
            ROUND((sum(case when cqr.question_answer is null then 0 else (case when cqr.question_answer = 'NA' then q.question_weight else cqr.question_answer END) END)/(sum(case when cqr.question_answer is null then 0 else 1 END))) *100) as total_topic_progress,count(q.id_question),GROUP_CONCAT((case when cqr.question_answer=0 then 'R' when cqr.question_answer=1 then 'G' when cqr.question_answer is null then 'E' when cqr.question_answer='NA' then 'N' else 'A' END) SEPARATOR ' '),
            simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.question_answer is null then 'E' when cqr.question_answer='NA' then 'N' when cqr.question_answer=0 then 'R' when cqr.question_answer=1 then 'G' else 'A' END) SEPARATOR ' ')) as simple_score_calculation,m.parent_module_id
            from question q
            join topic t on t.id_topic = q.topic_id
            join topic_language tl on tl.topic_id = t.id_topic
            join module m on m.id_module = t.module_id
            join module_language ml on ml.module_id = m.id_module
            left join contract_question_review cqr on cqr.question_id = q.id_question #and cqr.question_answer != 'NA'
            where q.question_type != 'input' and m.contract_review_id = ?
            GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1)temp) temp1 GROUP BY temp1.module_id";
        */
        if(!isset($data['provider_visibility']))
        $data['provider_visibility'] = array(0,1);
        
        if(isset($data['dynamic_column']))
        $answer_column = $data['dynamic_column'];
        else
        $answer_column = 'question_answer'; 
        if(isset($data['is_subtask']) && $data['is_subtask']==1 && $this->session_user_info->contribution_type==3){
            $data['provider_visibility']=array(1);
        }
        $query="select
	module_id,module_name,topic_id,topic_name,topic_avg_weight_score,
	COUNT(CASE WHEN topic_avg_weight_score='' OR topic_avg_weight_score IS NULL   THEN 1 END) AS na_total,
	COUNT(CASE WHEN topic_avg_weight_score=1 THEN 1 END) AS green_total,
	COUNT(CASE WHEN topic_avg_weight_score>0 and topic_avg_weight_score < 1 THEN 1 END) AS amber_total,
	COUNT(CASE WHEN topic_avg_weight_score=0 THEN 1 END) AS red_total,
	COUNT(CASE WHEN topic_avg_weight_score<0 THEN 1 END) AS no_answer_total,parent_module_id



 from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.$answer_column is null then -1 else cqr.$answer_column END)))/(sum(q.question_weight)-sum(case when cqr.$answer_column = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,m.parent_module_id from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and t.type='general' and m.module_status in (1,2,3) and t.topic_status=1
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.$answer_column is null then 'E' when cqr.$answer_column='NA' then 'N' when cqr.$answer_column=0 then 'R' when cqr.$answer_column=1 then 'G' else 'A' END) SEPARATOR ' ')) as topic_avg_weight_score,
m.module_order,t.topic_order,'' str,m.parent_module_id
 from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and t.type='simple' and m.module_status in (1,2,3) and t.topic_status=1 GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (data_score_calculation(GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) WHEN t.type='relationship' THEN (relationship_score_calculation_new(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,m.parent_module_id
from question q
LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
left join question_option qo on q.id_question=qo.question_id and cqr.$answer_column=qo.option_value
LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and m.module_status in (1,2,3) and t.topic_status=1
and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp
GROUP BY module_id";
        $query = $this->db->query($query,array($data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility'],$data['contract_review_id'],$data['provider_visibility']));
         //echo 
        $result =  $query->result_array();//echo $this->db->last_query();exit;
        return $result;
    }

    public function getContributorContractReviewModuleScore($data)
    {
        if(!isset($data['provider_visibility']))
            $data['provider_visibility'] = array(0,1);

        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';    

        $query="select
	module_id,module_name,topic_id,topic_name,topic_avg_weight_score,
	COUNT(CASE WHEN topic_avg_weight_score='' OR topic_avg_weight_score IS NULL   THEN 1 END) AS na_total,
	COUNT(CASE WHEN topic_avg_weight_score=1 THEN 1 END) AS green_total,
	COUNT(CASE WHEN topic_avg_weight_score>0 and topic_avg_weight_score < 1 THEN 1 END) AS amber_total,
	COUNT(CASE WHEN topic_avg_weight_score=0 THEN 1 END) AS red_total,
	COUNT(CASE WHEN topic_avg_weight_score<0 THEN 1 END) AS no_answer_total,parent_module_id



 from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.$answer_column is null then -1 else cqr.$answer_column END)))/(sum(q.question_weight)-sum(case when cqr.$answer_column = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,m.parent_module_id from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and m.id_module in ? and t.type='general' and m.module_status in (1,2,3)
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.$answer_column is null then 'E' when cqr.$answer_column='NA' then 'N' when cqr.$answer_column=0 then 'R' when cqr.$answer_column=1 then 'G' else 'A' END) SEPARATOR ' ')) as topic_avg_weight_score,
m.module_order,t.topic_order,'' str,m.parent_module_id
 from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and m.id_module in ? and t.type='simple' and m.module_status in (1,2,3) GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (data_score_calculation(GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) WHEN t.type='relationship' THEN (relationship_score_calculation_new(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,m.parent_module_id
from question q
LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
left join question_option qo on q.id_question=qo.question_id and cqr.$answer_column=qo.option_value
LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
where q.question_type != 'input' and q.question_type != 'date' and m.contract_review_id = ? and q.provider_visibility in ? and m.id_module in ? and m.module_status in (1,2,3)
and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp
GROUP BY module_id";
        $query = $this->db->query($query,array($data['contract_review_id'],$data['provider_visibility'],$data['module_ids'],$data['contract_review_id'],$data['provider_visibility'],$data['module_ids'],$data['contract_review_id'],$data['provider_visibility'],$data['module_ids']));
        //echo 
        $result =  $query->result_array();
        return $result;
    }

    public function getContractReviewModuleScoreProgress($data)
    {
        if(isset($data['dynamic_column']))
            $answer_column = $data['dynamic_column'];
        else
            $answer_column = 'question_answer';

        $query="select
	module_id,module_name,topic_id,topic_name,topic_avg_weight_score,
	COUNT(CASE WHEN topic_avg_weight_score='' OR topic_avg_weight_score IS NULL   THEN 1 END) AS na_total,
	COUNT(CASE WHEN topic_avg_weight_score=1 THEN 1 END) AS green_total,
	COUNT(CASE WHEN topic_avg_weight_score>0 and topic_avg_weight_score < 1 THEN 1 END) AS amber_total,
	COUNT(CASE WHEN topic_avg_weight_score=0 THEN 1 END) AS red_total,
	COUNT(CASE WHEN topic_avg_weight_score<0 THEN 1 END) AS no_answer_total,parent_module_id



 from(
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(sum(q.question_weight*(case when cqr.$answer_column is null then -1 else cqr.$answer_column END)))/(sum(q.question_weight)-sum(case when cqr.$answer_column = 'NA' then q.question_weight else 0 END)) as topic_avg_weight_score,m.module_order,t.topic_order,'' str,m.parent_module_id from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where m.contract_review_id = ? and t.type='general' and m.module_status in (1,2,3)
GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A1
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,simple_score_calculation(count(q.id_question),GROUP_CONCAT((case when cqr.$answer_column is null then 'E' when cqr.$answer_column='NA' then 'N' when cqr.$answer_column=0 then 'R' when cqr.$answer_column=1 then 'G' else 'A' END) SEPARATOR ' ')) as topic_avg_weight_score,
m.module_order,t.topic_order,'' str,m.parent_module_id
 from question q
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
where  m.contract_review_id = ? and t.type='simple' and m.module_status in (1,2,3) GROUP BY t.id_topic  ORDER BY m.module_order asc,t.topic_order asc) A2
UNION ALL
select * from (
select
	t.module_id,ml.module_name,q.topic_id,tl.topic_name,(CASE WHEN t.type='data' THEN (data_score_calculation(GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) WHEN t.type='relationship' THEN (relationship_score_calculation_new(count(q.id_question),GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-'))) END) topic_avg_weight_score,m.module_order,t.topic_order,GROUP_CONCAT(IFNULL(qol.option_name,'B') ORDER BY q.id_question asc SEPARATOR '-') as str,m.parent_module_id
from question q
LEFT JOIN question_language ql on ql.question_id=q.id_question and ql.language_id=1
left join contract_question_review cqr on cqr.question_id = q.id_question and cqr.$answer_column != 'NA'
left join question_option qo on q.id_question=qo.question_id and cqr.$answer_column=qo.option_value
LEFT JOIN question_option_language qol on qol.question_option_id=qo.id_question_option and qol.language_id=1
join topic t on t.id_topic = q.topic_id
join topic_language tl on tl.topic_id = t.id_topic
join module m on m.id_module = t.module_id
join module_language ml on ml.module_id = m.id_module
where  m.contract_review_id = ? and m.module_status in (1,2,3)
and t.type in ('data','relationship') GROUP BY t.id_topic ORDER BY q.id_question asc) A3 order by module_order asc,topic_order asc )temp
GROUP BY module_id";
        $query = $this->db->query($query,array($data['contract_review_id'],$data['contract_review_id'],$data['contract_review_id']));
        // echo 
        $result =  $query->result_array();
        return $result;
    }

    public function getModuleDashboard($data){
        $this->db->select('m.id_module,ml.module_name');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module = ml.module_id','');
        $this->db->where('m.contract_review_id',$data);
        $result =  $this->db->get()->result_array();
        foreach($result as $key => $value){
            $result[$key]['topic'] = $this->getTopicDashboard($result[$key]['id_module']);
        }
        return $result;
    }

    public function getTopicDashboard($data){
        $this->db->select('t.id_topic,tl.topic_name');
        $this->db->from('topic t');
        $this->db->join('topic_language tl','t.id_topic = tl.topic_id','');
        $this->db->where('t.module_id',$data);
        return $this->db->get()->result_array();
    }

    public function addContractReviewActionItemLog($data)
    {
        $this->db->insert('contract_review_action_item_log', $data);
        return $this->db->insert_id();
    }

    public function getActionItemDetails($data)
    {
        $this->db->select("ml.module_name,tl.topic_name,concat(u.first_name,' ',u.last_name) as user_name,crai.*");
        $this->db->from('contract_review_action_item crai');
        $this->db->join('user u','u.id_user = crai.responsible_user_id','left');
        $this->db->join('module_language ml','ml.module_id = crai.module_id  and ml.language_id=1','left');
        $this->db->join('topic_language tl','tl.topic_id = crai.topic_id and tl.language_id=1','left');
        if(isset($data['id_contract_review_action_item']))
            $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);

        $result = $this->db->get();
        return $result->result_array();
    }

    // public function getActionItems($data){
        
    //     $this->db->select("ml.module_name,tl.topic_name,concat(u.first_name,' ',u.last_name) as user_name,crai.*,ql.question_text,concat(u1.first_name,' ',u1.last_name) as created_by_name,IF(crai.due_date<CURDATE() AND crai.status = 'open',1,0)as overdue");
    //     $this->db->from('contract_review_action_item crai');
    //     $this->db->join('user u','u.id_user = crai.responsible_user_id','left');
    //     $this->db->join('user u1','u1.id_user = crai.created_by','left');
    //     $this->db->join('module_language ml','ml.module_id = crai.module_id  and ml.language_id=1','left');
    //     $this->db->join('topic_language tl','tl.topic_id = crai.topic_id and tl.language_id=1','left');
    //     $this->db->join('question q','q.id_question = crai.question_id','left');
    //     $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','left');
    //     $this->db->join('contract c','c.id_contract = crai.contract_id and c.is_deleted=0','left');
    //     $this->db->join('relationship_category_language rcl','rcl.relationship_category_id = c.relationship_category_id','left');
    //     $this->db->join('provider p','c.provider_name = p.id_provider','left');
    //     $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
    //     if(isset($data['filterType']) && $data['filterType']=='date')
    //         $this->db->where("date(crai.due_date)=date('".$data['date']."')");
    //     if(isset($data['filterType']) && $data['filterType']=='month')
    //         $this->db->where("month(crai.due_date)=month('".$data['date']."') AND year(crai.due_date)=year('".$data['date']."')");
    //     if(isset($data['filterType']) && $data['filterType']=='year')
    //         $this->db->where("year(crai.due_date)=year('".$data['date']."')");
    //     $this->db->where('bu.customer_id',$data['customer_id']);
    //     if(isset($data['id_contract_review_action_item']))
    //         $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);
    //     if(isset($data['contract_id']) && strtolower($data['contract_id'])!='all')
    //         $this->db->where('contract_id',$data['contract_id']);
    //     if(isset($data['priority'])){
    //         if($data['priority'] == 'Not-classified')
    //             $this->db->where('(crai.priority = "" OR crai.priority IS NULL)');
    //         else
    //             $this->db->where('crai.priority',$data['priority']);
    //     }
    //     if(isset($data['search'])){
    //         $this->db->group_start();
    //         $this->db->like('ml.module_name', $data['search'], 'both');
    //         $this->db->or_like('crai.action_item', $data['search'], 'both');
    //         $this->db->or_like('u.first_name', $data['search'], 'both');
    //         $this->db->or_like('u.last_name', $data['search'], 'both');
    //         $this->db->or_like('crai.status', $data['search'], 'both');
    //         $this->db->or_like('crai.due_date', $data['search'], 'both');
    //         $this->db->or_like('crai.priority', $data['search'], 'both');
    //         $this->db->group_end();
    //     }
    //     if(isset($data['responsible_user_id'])){
    //         //If show my action items true
    //         $this->db->where_in('responsible_user_id',$data['responsible_user_id']);
    //     }else if($data['user_role_id']!=2 && $data['user_role_id'] !=6){
    //         if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
    //             $this->db->group_start();
    //         //If show my action items false
    //         if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0 && $data['user_role_id'] !=6){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //             $this->db->or_where('crai.responsible_user_id', $data['id_user']);
    //             $this->db->or_where('c.contract_owner_id',$data['id_user']);
    //             if(isset($data['module_id']) && is_array($data['module_id']))
    //                 $this->db->or_where_in('crai.module_id',$data['module_id']);
    //         }
    //         if(isset($data['delegate_id']) && $data['user_role_id'] !=6){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //             $this->db->or_where('crai.responsible_user_id', $data['id_user']);
    //             $this->db->or_where('c.delegate_id',$data['id_user']);
    //             if(isset($data['module_id']) && is_array($data['module_id']))
    //                 $this->db->or_where_in('crai.module_id',$data['module_id']);
    //         }
    //         if(isset($data['provider_colleuges']))
    //             $this->db->where_in('responsible_user_id',$data['provider_colleuges']);
    //         if(is_array($data['business_unit_id']) || isset($data['delegate_id']) || isset($data['provider_colleuges']))
    //             $this->db->group_end();
    //     }
    //     //This Block is for Reports Action items
    //     if(isset($data['contract_ids']) && count($data['contract_ids']) > 0){
    //         $this->db->where_in('crai.contract_id',$data['contract_ids']);
    //     }
    //     if(isset($data['is_workflow_array']))
    //         $this->db->where_in('crai.is_workflow',$data['is_workflow_array']);
    //     if(isset($data['start_date']))
    //         $this->db->where('due_date >=',$data['start_date']);
    //     if(isset($data['end_date']))
    //         $this->db->where('due_date <=',$data['end_date']);
    //     if(isset($data['contract_review_action_item_status']) && strtolower($data['contract_review_action_item_status'])!='all')
    //         $this->db->where('crai.status',$data['contract_review_action_item_status']);
    //     if(isset($data['item_status']))
    //         $this->db->where('crai.item_status',$data['item_status']);
    //     if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
    //         $this->db->where('p.provider_name',$data['provider_name']);
    //     if(isset($data['show_my_action_items']) && isset($data['id_user'])){
    //         if($data['show_my_action_items'] == 'assigned_to_me')
    //             $this->db->where('responsible_user_id',$data['id_user']);
    //         if($data['show_my_action_items'] == 'created_by_me'){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //         }
    //     }
    //     if($data['user_role_id']==6){
    //         $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
    //     }
        
    //     if(isset($data['overdue']) && $data['overdue'] == 'true' && isset($data['contract_review_action_item_status']) && $data['contract_review_action_item_status'] != 'completed'){
    //             $this->db->where('crai.due_date < CURDATE()');
    //     }
    //     $this->db->group_by('crai.id_contract_review_action_item');
    //     $this->db->where('c.is_deleted','0');
    //     $query = $this->db->get();
    //     //echo 
    //     $all_clients_count = count($query->result_array());

    //     $this->db->select("IF(crai.is_workflow=0,(SELECT contract_review_status from contract_review cr WHERE is_workflow = 0 AND cr.contract_id = crai.contract_id ORDER BY cr.id_contract_review DESC LIMIT 1),(SELECT contract_review_status from contract_review cr WHERE cr.contract_workflow_id = crai.contract_workflow_id ORDER BY cr.id_contract_review DESC LIMIT 1))review_status, (SELECT IF(cqr.question_option_id IS NULL,cqr.question_answer,qol.option_name) FROM contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE cqr.question_id = q.id_question GROUP BY cqr.question_id) question_answere,rcl.relationship_category_name category_name,ml.module_name,tl.topic_name,concat(u.first_name,' ',u.last_name) as user_name,crai.*, IFNULL(ql.question_text,'-') question_text,IF(crai.due_date<CURDATE() AND crai.status = 'open',1,0)as overdue,q.question_type");
    //     $this->db->from('contract_review_action_item crai');
    //     $this->db->join('user u','u.id_user = crai.responsible_user_id','left');
    //     $this->db->join('module_language ml','ml.module_id = crai.module_id  and ml.language_id=1','left');
    //     $this->db->join('topic_language tl','tl.topic_id = crai.topic_id and tl.language_id=1','left');
    //     $this->db->join('question q','q.id_question = crai.question_id','left');
    //     $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','left');
    //     $this->db->join('contract c','c.id_contract = crai.contract_id and c.is_deleted=0','left');
    //     $this->db->join('relationship_category_language rcl','rcl.relationship_category_id = c.relationship_category_id','left');
    //     $this->db->join('provider p','c.provider_name = p.id_provider','left');
    //     $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
    //     //$this->db->where('crai.provider_id>0');
    //     if(isset($data['filterType']) && $data['filterType']=='date')
    //         $this->db->where("date(crai.due_date)=date('".$data['date']."')");
    //     if(isset($data['filterType']) && $data['filterType']=='month')
    //         $this->db->where("month(crai.due_date)=month('".$data['date']."') AND year(crai.due_date)=year('".$data['date']."')");
    //     if(isset($data['filterType']) && $data['filterType']=='year')
    //         $this->db->where("year(crai.due_date)=year('".$data['date']."')");
    //     $this->db->where('bu.customer_id',$data['customer_id']);
    //     if(isset($data['id_contract_review_action_item']))
    //         $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);
    //     if(isset($data['contract_id']) && strtolower($data['contract_id'])!='all')
    //         $this->db->where('contract_id',$data['contract_id']);
    //     if(isset($data['priority'])){
    //         if($data['priority'] == 'Not-classified')
    //             $this->db->where('(crai.priority = "" OR crai.priority IS NULL)');
    //         else
    //             $this->db->where('crai.priority',$data['priority']);
    //     }
    //     if(isset($data['search'])){
    //         $this->db->group_start();
    //         $this->db->like('ml.module_name', $data['search'], 'both');
    //         $this->db->or_like('crai.action_item', $data['search'], 'both');
    //         $this->db->or_like('u.first_name', $data['search'], 'both');
    //         $this->db->or_like('u.last_name', $data['search'], 'both');
    //         $this->db->or_like('crai.status', $data['search'], 'both');
    //         $this->db->or_like('crai.due_date', $data['search'], 'both');
    //         $this->db->or_like('crai.priority', $data['search'], 'both');
    //         $this->db->group_end();
    //     }
    //     if(isset($data['responsible_user_id'])){
    //         //If show my action items true
    //         $this->db->where_in('responsible_user_id',$data['responsible_user_id']);
    //         //$data['pagination']['number'] = 0;
    //     }else if($data['user_role_id']!=2 && $data['user_role_id'] !=6){
    //         if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
    //             $this->db->group_start();
    //         //If show my action items false
    //         if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0 && $data['user_role_id'] !=6){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //             $this->db->or_where('crai.responsible_user_id', $data['id_user']);
    //             $this->db->or_where('c.contract_owner_id',$data['id_user']);
    //             if(isset($data['module_id']) && is_array($data['module_id']))
    //                 $this->db->or_where_in('crai.module_id',$data['module_id']);
    //         }
    //         if(isset($data['delegate_id'])){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //             $this->db->or_where('crai.responsible_user_id', $data['id_user']);
    //             $this->db->or_where('c.delegate_id',$data['id_user']);
    //             if(isset($data['module_id']) && is_array($data['module_id']))
    //                 $this->db->or_where_in('crai.module_id',$data['module_id']);
    //         }
    //         if(isset($data['provider_colleuges']))
    //             $this->db->where_in('responsible_user_id',$data['provider_colleuges']);
    //         if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
    //             $this->db->group_end();
    //     }
    //     //This Block is for Reports Action items
    //     if(isset($data['contract_ids']) && count($data['contract_ids']) > 0){
    //         $this->db->where_in('crai.contract_id',$data['contract_ids']);
    //     }
    //     if(isset($data['is_workflow_array']))
    //         $this->db->where_in('crai.is_workflow',$data['is_workflow_array']);
    //     if(isset($data['start_date']))
    //         $this->db->where('due_date >=',$data['start_date']);
    //     if(isset($data['end_date']))
    //         $this->db->where('due_date <=',$data['end_date']);
    //     if(isset($data['contract_review_action_item_status']) && strtolower($data['contract_review_action_item_status'])!='all')
    //         $this->db->where('crai.status',$data['contract_review_action_item_status']);
    //     if(isset($data['item_status']))
    //         $this->db->where('crai.item_status',$data['item_status']);
    //     if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
    //         $this->db->where('p.provider_name',$data['provider_name']);
    //     if(isset($data['show_my_action_items']) && isset($data['id_user'])){
    //         if($data['show_my_action_items'] == 'assigned_to_me')
    //             $this->db->where('responsible_user_id',$data['id_user']);
    //         if($data['show_my_action_items'] == 'created_by_me'){
    //             $this->db->where('crai.created_by', $data['id_user']);
    //         }
    //     }
    //     if(isset($data['overdue']) && $data['overdue'] == 'true' && isset($data['contract_review_action_item_status']) && $data['contract_review_action_item_status'] != 'completed'){
    //         $this->db->where('crai.due_date < CURDATE()');
    //     }
    //     if($data['user_role_id']==6){
    //         $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
    //     }
    //     $this->db->group_by('crai.id_contract_review_action_item');
    //     if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
    //         $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
    //     if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
    //         $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
    //     else
    //         $this->db->order_by('crai.due_date','ASC');
    //         // $this->db->order_by('p.provider_name,c.contract_name','ASC');
    //     $this->db->where('c.is_deleted','0');
    //     $result = $this->db->get()->result_array();
    //     // echo 
    //     return array('total_records' => $all_clients_count,'data' => $result);
    // }
    public function getActionItems($data){
        // print_r($data);exit;
        $this->db->select("IF(crai.is_workflow=0,(SELECT contract_review_status from contract_review cr WHERE is_workflow = 0 AND cr.contract_id = crai.contract_id ORDER BY cr.id_contract_review DESC LIMIT 1),(SELECT contract_review_status from contract_review cr WHERE cr.contract_workflow_id = crai.contract_workflow_id ORDER BY cr.id_contract_review DESC LIMIT 1))review_status, (SELECT IF(cqr.question_option_id IS NULL,cqr.question_answer,qol.option_name) FROM contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE cqr.question_id = q.id_question GROUP BY cqr.question_id) question_answere,rcl.relationship_category_name category_name,ml.module_name,tl.topic_name,concat(u.first_name,' ',u.last_name) as user_name,crai.*, IFNULL(ql.question_text,'-') question_text,IF(crai.due_date<CURDATE() AND crai.status = 'open',1,0)as overdue,q.question_type,`p`.`provider_name`,c.contract_name,u.first_name,c.type,CASE WHEN crai.reference_type='contract' THEN 'Contract' WHEN crai.reference_type='project' THEN 'Project' WHEN crai.reference_type='provider' THEN 'Relation' WHEN (crai.reference_type='topic' or crai.reference_type='question') and crai.is_workflow=1 THEN 'Task' WHEN (crai.reference_type='topic' or crai.reference_type='question') and crai.is_workflow=0 THEN 'Review' ELSE '' END as connected_to");
        $this->db->from('contract_review_action_item crai');
        $this->db->join('user u','u.id_user = crai.responsible_user_id','left');
        $this->db->join('module_language ml','ml.module_id = crai.module_id  and ml.language_id=1','left');
        $this->db->join('topic_language tl','tl.topic_id = crai.topic_id and tl.language_id=1','left');
        $this->db->join('question q','q.id_question = crai.question_id','left');
        $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','left');
        $this->db->join('contract c','c.id_contract = crai.contract_id and c.is_deleted=0','left');
        $this->db->join('relationship_category_language rcl','rcl.relationship_category_id = c.relationship_category_id','left');
        $this->db->join('provider p','c.provider_name = p.id_provider','left');
        $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
        $this->db->join('user u1','u1.id_user = crai.created_by','left');
        //$this->db->where('crai.provider_id>0');
        if(isset($data['filterType']) && $data['filterType']=='date')
            $this->db->where("date(crai.due_date)=date('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='month')
            $this->db->where("month(crai.due_date)=month('".$data['date']."') AND year(crai.due_date)=year('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='year')
            $this->db->where("year(crai.due_date)=year('".$data['date']."')");
        $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['id_contract_review_action_item']))
            $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);
        if(isset($data['contract_id']) && strtolower($data['contract_id'])!='all')
            $this->db->where('contract_id',$data['contract_id']);
        if(isset($data['priority'])){
            if($data['priority'] == 'Not-classified')
                $this->db->where('(crai.priority = "" OR crai.priority IS NULL)');
            else
                $this->db->where('crai.priority',$data['priority']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('crai.action_item', $data['search'], 'both',false);
            $this->db->or_like('CONCAT(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->or_like('crai.status', $data['search'], 'both');
            $this->db->or_like('crai.due_date', $data['search'], 'both');
            $this->db->or_like('crai.priority', $data['search'], 'both');
            // if it is a calander then search on above fields only
            if(! ($data['is_calendar'] == 1))
            {
                $this->db->or_like('ml.module_name', $data['search'], 'both');
                $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                $this->db->or_like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('tl.topic_name', $data['search'], 'both');
                $this->db->or_like('ql.question_text', $data['search'], 'both');
            }
            $this->db->group_end();
        }
        if(isset($data['responsible_user_id'])){
            //If show my action items true
            $this->db->group_start();
            $this->db->where_in('responsible_user_id',$data['responsible_user_id']);
            if($data['user_role_id']!=2)
            $this->db->or_where('crai.created_by',$data['id_user']);
            $this->db->group_end();
            //$data['pagination']['number'] = 0;
        }else if($data['user_role_id']!=2 && $data['user_role_id'] !=6){
            if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
                $this->db->group_start();
            //If show my action items false
            if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0 && $data['user_role_id'] !=6){
                // print_r($data);exit;
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.contract_owner_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                if($data['user_role_id']==8){
                    $this->db->or_where_in('c.business_unit_id',$data['business_unit_id']);
                }
            }
            if(isset($data['delegate_id'])){
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.delegate_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
            }
            // print_r($data);exit;
            if(isset($data['provider_colleuges'])){
                $this->db->where_in('responsible_user_id',$data['provider_colleuges']);
                $this->db->or_where('crai.created_by',$data['id_user']);

            }
            if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
                $this->db->group_end();
        }
        //This Block is for Reports Action items
        if(isset($data['contract_ids']) && count($data['contract_ids']) > 0){
            $this->db->where_in('crai.contract_id',$data['contract_ids']);
        }
        if(isset($data['is_workflow_array']))
            $this->db->where_in('crai.is_workflow',$data['is_workflow_array']);
        if(isset($data['start_date']))
            $this->db->where('due_date >=',$data['start_date']);
        if(isset($data['end_date']))
            $this->db->where('due_date <=',$data['end_date']);
        if(isset($data['contract_review_action_item_status']) && strtolower($data['contract_review_action_item_status'])!='all')
            $this->db->where('crai.status',$data['contract_review_action_item_status']);
        if(isset($data['item_status']))
            $this->db->where('crai.item_status',$data['item_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['show_my_action_items']) && isset($data['id_user'])){
            if($data['show_my_action_items'] == 'assigned_to_me')
                $this->db->where('responsible_user_id',$data['id_user']);
            if($data['show_my_action_items'] == 'created_by_me'){
                $this->db->where('crai.created_by', $data['id_user']);
            }
        }
        if(isset($data['overdue']) && $data['overdue'] == 'true' && isset($data['contract_review_action_item_status']) && $data['contract_review_action_item_status'] != 'completed'){
            $this->db->where('crai.due_date < CURDATE()');
        }
        if($data['user_role_id']==6){
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        $this->db->group_by('crai.id_contract_review_action_item');
          //////////advanced filters start ///////////////
        foreach($data['adv_filters'] as $filter){
            if($filter['field_type']=='drop_down'){
                if($filter['database_field']=='allocation')
                {
                    $allocationExplode = explode(',',$filter['value']);
                    if(!empty($allocationExplode)){$this->db->group_start();}
                    foreach($allocationExplode as $alloaction)
                    {   
                        if($alloaction == 'assigned_to_me')
                        {
                            $this->db->where('responsible_user_id',$data['id_user']);
                        }
                        if($alloaction == 'created_by_me')
                        {
                            $this->db->or_where('crai.created_by', $data['id_user']);
                        }
                    }
                    if(!empty($allocationExplode)){$this->db->group_end();}
                }
                else
                {
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
            }
            elseif($filter['field_type']=='date'){
                $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
            }
            elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
                if($filter['condition']=='like'){
                    $this->db->like($filter['database_field'],$filter['value'],'both');
                }
                // if($filter['condition']=='free_text' || $filter['condition']=='='){
                //     $this->db->where($filter['database_field'],$filter['value']);
                // }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        //////////advanced filters end ///////////////
        $subQuery1 = $this->db->_compile_select();  
        $this->db->_reset_select();
      
        $this->db->select("IF(crai.is_workflow=0,(SELECT contract_review_status from contract_review cr WHERE is_workflow = 0 AND cr.contract_id = crai.contract_id ORDER BY cr.id_contract_review DESC LIMIT 1),(SELECT contract_review_status from contract_review cr WHERE cr.contract_workflow_id = crai.contract_workflow_id ORDER BY cr.id_contract_review DESC LIMIT 1))review_status, (SELECT IF(cqr.question_option_id IS NULL,cqr.question_answer,qol.option_name) FROM contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE cqr.question_id = q.id_question GROUP BY cqr.question_id) question_answere,'--' as category_name,ml.module_name,tl.topic_name,concat(u.first_name,' ',u.last_name) as user_name,crai.*, IFNULL(ql.question_text,'-') question_text,IF(crai.due_date<CURDATE() AND crai.status = 'open',1,0)as overdue,q.question_type,,`p`.`provider_name`,'--' as contract_name,u.first_name,c.type,CASE WHEN crai.reference_type='contract' THEN 'Contract' WHEN crai.reference_type='project' THEN 'Project' WHEN crai.reference_type='provider' THEN 'Relation' WHEN (crai.reference_type='topic' or crai.reference_type='question') and crai.is_workflow=1 THEN 'Task' WHEN (crai.reference_type='topic' or crai.reference_type='question') and crai.is_workflow=0 THEN 'Review' ELSE '' END as connected_to");
        $this->db->from('contract_review_action_item crai');
        $this->db->join('user u','u.id_user = crai.responsible_user_id','left');
        $this->db->join('module_language ml','ml.module_id = crai.module_id  and ml.language_id=1','left');
        $this->db->join('topic_language tl','tl.topic_id = crai.topic_id and tl.language_id=1','left');
        $this->db->join('question q','q.id_question = crai.question_id','left');
        $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','left');
        // $this->db->join('relationship_category_language rcl','rcl.relationship_category_id = c.relationship_category_id','left');
        $this->db->join('provider p','crai.provider_id=p.id_provider','left');
        $this->db->join('contract c','p.id_provider = c.provider_name and c.is_deleted=0','left');
        $this->db->join('user u1','u1.id_user = crai.created_by','left');
        // $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
        //$this->db->where('crai.provider_id>0');
        if(isset($data['filterType']) && $data['filterType']=='date')
            $this->db->where("date(crai.due_date)=date('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='month')
            $this->db->where("month(crai.due_date)=month('".$data['date']."') AND year(crai.due_date)=year('".$data['date']."')");
        if(isset($data['filterType']) && $data['filterType']=='year')
            $this->db->where("year(crai.due_date)=year('".$data['date']."')");
        $this->db->where('p.customer_id',$data['customer_id']);
        if(isset($data['id_contract_review_action_item']))
            $this->db->where('crai.id_contract_review_action_item',$data['id_contract_review_action_item']);
        if(isset($data['contract_id']) && strtolower($data['contract_id'])!='all')
            $this->db->where('contract_id',$data['contract_id']);
        if(isset($data['priority'])){
            if($data['priority'] == 'Not-classified')
                $this->db->where('(crai.priority = "" OR crai.priority IS NULL)');
            else
                $this->db->where('crai.priority',$data['priority']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('crai.action_item', $data['search'], 'both');
            $this->db->or_like('CONCAT(u.first_name," ",u.last_name)',$data['search'],'both');
            $this->db->or_like('crai.status', $data['search'], 'both');
            $this->db->or_like('crai.due_date', $data['search'], 'both');
            $this->db->or_like('crai.priority', $data['search'], 'both');
            // if it is a calander then search on above fields only
            if(!($data['is_calendar'] == 1))
            {
                $this->db->or_like('ml.module_name', $data['search'], 'both');
                $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                //$this->db->or_like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('tl.topic_name', $data['search'], 'both');
                $this->db->or_like('ql.question_text', $data['search'], 'both');
            }
            $this->db->group_end();
        }
        if(isset($data['responsible_user_id'])){
            //If show my action items true
            $this->db->group_start();
            $this->db->where_in('responsible_user_id',$data['responsible_user_id']);
            if($data['user_role_id']!=2)
            $this->db->or_where('crai.created_by',$data['id_user']);
            $this->db->group_end();


            //$data['pagination']['number'] = 0;
        }else if($data['user_role_id']!=2 && $data['user_role_id'] !=6){
            if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
                $this->db->group_start();
            //If show my action items false
            if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0 && $data['user_role_id'] !=6){
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.contract_owner_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                if($data['user_role_id']==8){
                    $this->db->or_where_in('c.business_unit_id',$data['business_unit_id']);
                }
            }
            if(isset($data['delegate_id'])){
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.delegate_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
            }
            if(isset($data['provider_colleuges'])){
                $this->db->where_in('responsible_user_id',$data['provider_colleuges']);
                $this->db->or_where('crai.created_by',$data['id_user']);

            }
            if(is_array($data['business_unit_id']) || isset($data['delegate_id']) ||isset($data['provider_colleuges']))
                $this->db->group_end();
        }
        //This Block is for Reports Action items
        if(isset($data['contract_ids']) && count($data['contract_ids']) > 0){
            $this->db->where_in('crai.contract_id',$data['contract_ids']);
        }
        if(isset($data['is_workflow_array']))
            $this->db->where_in('crai.is_workflow',$data['is_workflow_array']);
        if(isset($data['start_date']))
            $this->db->where('due_date >=',$data['start_date']);
        if(isset($data['end_date']))
            $this->db->where('due_date <=',$data['end_date']);
        if(isset($data['contract_review_action_item_status']) && strtolower($data['contract_review_action_item_status'])!='all')
            $this->db->where('crai.status',$data['contract_review_action_item_status']);
        if(isset($data['item_status']))
            $this->db->where('crai.item_status',$data['item_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['show_my_action_items']) && isset($data['id_user'])){
            if($data['show_my_action_items'] == 'assigned_to_me')
                $this->db->where('responsible_user_id',$data['id_user']);
            if($data['show_my_action_items'] == 'created_by_me'){
                $this->db->where('crai.created_by', $data['id_user']);
            }
        }
        if(isset($data['overdue']) && $data['overdue'] == 'true' && isset($data['contract_review_action_item_status']) && $data['contract_review_action_item_status'] != 'completed'){
            $this->db->where('crai.due_date < CURDATE()');
        }
        if($data['user_role_id']==6){
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        $this->db->group_by('crai.id_contract_review_action_item');
        foreach($data['adv_filters'] as $filter){
            if($filter['field_type']=='drop_down'){
                if($filter['database_field']=='allocation')
                {
                    $allocationExplode = explode(',',$filter['value']);
                    if(!empty($allocationExplode)){$this->db->group_start();}
                    foreach($allocationExplode as $alloaction)
                    {   
                        if($alloaction == 'assigned_to_me')
                        {
                            $this->db->where('responsible_user_id',$data['id_user']);
                        }
                        if($alloaction == 'created_by_me')
                        {
                            $this->db->or_where('crai.created_by', $data['id_user']);
                        }
                    }
                    if(!empty($allocationExplode)){$this->db->group_end();}
                }
                else
                {
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
            }
            elseif($filter['field_type']=='date'){
                $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
            }
            elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
                if($filter['condition']=='like'){
                    $this->db->like($filter['database_field'],$filter['value'],'both');
                }
                // if($filter['condition']=='free_text' || $filter['condition']=='='){
                //     $this->db->where($filter['database_field'],$filter['value']);
                // }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        $subQuery2 = $this->db->_compile_select();
        $this->db->_reset_select();
         // print_r($subQuery2);exit;
        $this->db->select("*")->from("($subQuery1 UNION $subQuery2) as unionTable");
        $this->db->group_by('id_contract_review_action_item');
        foreach($data['adv_union_filters'] as $Unionfilter){
            if($Unionfilter['field_type']=='drop_down'){
                $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
            }
            if($Unionfilter['field_type']=='date'){
                $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
            }
            if($Unionfilter['field_type']=='numeric_text' || $Unionfilter['field_type']=='free_text'){
                if($Unionfilter['condition']=='like'){
                    $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
                }
                // if($Unionfilter['condition']=='free_text' || $filter['condition']=='='){
                //     if($Unionfilter['database_field']!='document_names' && $Unionfilter['database_field']!='document_urls'){
                //         $this->db->where($Unionfilter['database_field'],$Unionfilter['value']);
                //     }
                // }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            } 
        }
        $count_result_db = clone $this->db;
        
        $count_result = $count_result_db->get();
        //echo $this->db->last_query();exit;
        $count_result = $count_result->num_rows();
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else{
            $this->db->order_by('due_date','ASC');
            $this->db->order_by('provider_name,contract_name','ASC');
        }
        // $this->db->where('c.is_deleted','0');
        // $query = $this->db->get();echo '<pre>'.
        // return $query->result_array();
        $result = $this->db->get()->result_array();
        // echo 
        return array('total_records' => $count_result,'data' => $result);

    }

    public function getActionItemsCount($data)
    { 
        // print_r($data);exit;
        $this->db->select("crai.*");
        $this->db->from('contract_review_action_item crai');
        $this->db->join('contract c','c.id_contract = crai.contract_id and c.is_deleted=0','left');
        $this->db->join('business_unit bu','bu.id_business_unit = c.business_unit_id','left');
        $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_id']))
            $this->db->where('contract_id',$data['contract_id']);
        if(isset($data['responsible_user_id'])){
            //If show my action items true
            $this->db->where('responsible_user_id',$data['responsible_user_id']);
        }else if($data['user_role_id']!=2 && $data['user_role_id']!=7 && $data['user_role_id']!=6){
            //$this->db->group_start();
            //If show my action items false
            if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0){
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.contract_owner_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                if($data['user_role_id']==8){
                    $this->db->or_where_in('c.business_unit_id',$data['business_unit_id']);
                }
                $this->db->group_end();
            }
            if(isset($data['delegate_id'])){
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->or_where('c.delegate_id',$data['id_user']);
                if(isset($data['module_id']) && is_array($data['module_id']))
                    $this->db->or_where_in('crai.module_id',$data['module_id']);
                $this->db->group_end();
            }
            //$this->db->group_end();
        }

        if($data['user_role_id']==6){
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        $this->db->where('c.is_deleted','0');
        if(isset($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['item_status']))
            $this->db->where('crai.item_status',$data['item_status']);
        if(isset($data['contract_review_action_item_status']))
            $this->db->where('crai.status',$data['contract_review_action_item_status']);
        if(isset($data['priority']) && $data['priority']!='')
            $this->db->where('crai.priority',$data['priority']);
        if(isset($data['priority']) && $data['priority']=='')
            $this->db->where("(crai.priority is NULL OR priority = '' )");
        if(isset($data['type']) && $data['type']=='overdue' && $data['type']!='')
            $this->db->where('crai.due_date < CURDATE()');
        $this->db->group_by('crai.id_contract_review_action_item');

            $subQuery1 = $this->db->_compile_select();
            $this->db->_reset_select();


            $this->db->select("crai.*");
            $this->db->from('contract_review_action_item crai');
            $this->db->join('`provider` `p`','`crai`.`provider_id`=`p`.`id_provider`','left');
            $this->db->join('contract c','`p`.`id_provider` = `c`.`provider_name`','left');
            $this->db->where('p.customer_id',$data['customer_id']);
            if(isset($data['contract_id']))
                $this->db->where('contract_id',$data['contract_id']);
            if(isset($data['responsible_user_id'])){
                //If show my action items true
                $this->db->where('responsible_user_id',$data['responsible_user_id']);
            }else if($data['user_role_id']!=2 && $data['user_role_id']!=7 && $data['user_role_id']!=6){
                //$this->db->group_start();
                //If show my action items false
                if(isset($data['business_unit_id']) && count($data['business_unit_id'])>0){
                    $this->db->group_start();
                    $this->db->where('crai.created_by', $data['id_user']);
                    $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                    $this->db->or_where('c.contract_owner_id',$data['id_user']);
                    if(isset($data['module_id']) && is_array($data['module_id']))
                        $this->db->or_where_in('crai.module_id',$data['module_id']);
                    if($data['user_role_id']==8){
                        $this->db->or_where_in('c.business_unit_id',$data['business_unit_id']);
                    }
                    $this->db->group_end();
                }
                if(isset($data['delegate_id'])){
                    $this->db->group_start();
                    $this->db->where('crai.created_by', $data['id_user']);
                    $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                    $this->db->or_where('c.delegate_id',$data['id_user']);
                    if(isset($data['module_id']) && is_array($data['module_id']))
                        $this->db->or_where_in('crai.module_id',$data['module_id']);
                    $this->db->group_end();    
                }
                //$this->db->group_end();
            }
            $this->db->where('c.is_deleted','0');
            if(isset($data['contract_status']))
                $this->db->where('c.contract_status',$data['contract_status']);
            if(isset($data['item_status']))
                $this->db->where('crai.item_status',$data['item_status']);
            if(isset($data['contract_review_action_item_status']))
                $this->db->where('crai.status',$data['contract_review_action_item_status']);
            if(isset($data['priority']) && $data['priority']!='')
                $this->db->where('crai.priority',$data['priority']);
            if(isset($data['priority']) && $data['priority']=='')
                $this->db->where("(crai.priority is NULL OR priority = '' )");
            if(isset($data['type']) && $data['type']=='overdue' && $data['type']!='')
                $this->db->where('crai.due_date < CURDATE()');
            $this->db->group_by('crai.id_contract_review_action_item');
    
                $subQuery2 = $this->db->_compile_select();
                $this->db->_reset_select();

            $this->db->select("*")->from("($subQuery1 UNION $subQuery2) as unionTable");
            $this->db->group_by('id_contract_review_action_item');
        $result = $this->db->get()->result_array();//
        // print_r($result);exit;
        return count($result);
    }

    public function getProvidersList($data)
    {
        $this->db->select('c.id_contract as contract_id,c.provider_name,count(cri.id_contract_review_action_item) as action_items_count');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','c.business_unit_id=bu.id_business_unit','left');
        $this->db->join('contract_review_action_item cri','c.id_contract=cri.contract_id','left');
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('c.provider_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search'])) {
            $data['search']=$this->db->escape($data['search']);
            $this->db->where('(c.provider_name like "%' . $data['search'] . '%")');
        }*/
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        /*if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);*/
        if(isset($data['delegate_id'])) {
            if(isset($data['session_user_role'])){
                $this->db->group_start();
                $this->db->where('c.delegate_id', $data['delegate_id']);
                $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
                $this->db->group_end();
            }
            else
                $this->db->where('c.delegate_id', $data['delegate_id']);
        }
        //if(isset($data['responsible_user_id']))
            //$this->db->where('cri.responsible_user_id',$data['responsible_user_id']);

        //$this->db->or_where('cri.responsible_user_id',$data['id_user']);
        if(isset($data['responsible_user_id']) || isset($data['created_by'])){
            $this->db->group_start();
            if(isset($data['responsible_user_id']))
                $this->db->where('cri.responsible_user_id',$data['responsible_user_id']);
            if(isset($data['created_by']))
                $this->db->or_where('cri.created_by',$data['created_by']);
            $this->db->group_end();
        }
        if(isset($data['session_user_role']) && $data['session_user_role']==3){
            $this->db->group_start();
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->or_where("c.id_contract in (select cux.contract_id from contract_user cux where cux.contract_review_id in (select max(crx.id_contract_review) from contract_review crx where crx.contract_id=c.id_contract) and cux.user_id=".$data['session_user_id']." and cux.status=1)",null,false);
            $this->db->group_end();
        }
        else {
            if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
                $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        $this->db->where('c.is_deleted','0');


        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);

        $this->db->group_by('c.id_contract');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        return $query->result_array();
    }

    public function checkContractReviewSchedule_bkp($data)
    {
        $this->db->select('*');
        $this->db->from('calender c');
        $this->db->join('contract cr','c.relationship_category_id=cr.relationship_category_id and cr.is_deleted=0','left');
        $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
        $this->db->where('cr.id_contract',$data['contract_id']);
        $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
        $this->db->where('c.status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function checkContractReviewSchedule($data)
    {
        $this->db->select('*');
        $this->db->from('calender c');
        $this->db->join("business_unit bu","bu.id_business_unit=c.bussiness_unit_id");
        $this->db->join('contract cr','cr.business_unit_id=bu.id_business_unit and cr.is_deleted=0','left');
        $this->db->join('relationship_category_remainder rcr','cr.relationship_category_id=rcr.relationship_category_id','left'); 
        $this->db->where('CONCAT(",", `c`.`bussiness_unit_id`, ",") REGEXP ",('.$data["business_unit_id"].')," ',null,false);
        $this->db->where('( CONCAT(",", `c`.contract_id, ",") REGEXP ",('.$data["contract_id"].')," or CONCAT(",", `c`.contract_id, ",") REGEXP ",,")',null,false);
        $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
        $this->db->where('c.status',1);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function check_contract_in_calender($data)
    {
        $this->db->select('*');
        $this->db->from('calender c'); 
        $this->db->where('CONCAT(",", `c`.`bussiness_unit_id`, ",") REGEXP ",('.$data["business_unit_id"].')," ',null,false);
        if(isset($data['relationship_category_id']))//Using only for Overview List
            $this->db->where('( CONCAT(",", `c`.relationship_category_id, ",") REGEXP ",('.$data["relationship_category_id"].')," or CONCAT(",", `c`.relationship_category_id, ",") REGEXP ",,")',null,false);
        if(isset($data['provider_id']))//Using only for Overview List
            $this->db->where('( CONCAT(",", `c`.provider_id, ",") REGEXP ",('.$data["provider_id"].')," or CONCAT(",", `c`.provider_id, ",") REGEXP ",,")',null,false);
        if(isset($data['only_one_contract']))
            $this->db->where('( CONCAT(",", `c`.contract_id, ",") REGEXP ",('.$data["contract_id"].')," )',null,false);
        else
            $this->db->where('( CONCAT(",", `c`.contract_id, ",") REGEXP ",('.$data["contract_id"].')," or CONCAT(",", `c`.contract_id, ",") REGEXP ",,")',null,false);
        
        if(isset($data['days']))
            $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL '.$data['days'].' DAY) and c.date');
        else if(isset($data['past_records']))
            $this->db->where('CURDATE() > c.date');
        else
            $this->db->where('CURDATE() <= c.date');
        if(isset($data['is_workflow']))
            $this->db->where('c.is_workflow',$data['is_workflow']);
        if(isset($data['id_calender']))
            $this->db->where('c.id_calender',$data['id_calender']);
        $this->db->where('c.status',1);
        $query = $this->db->get();//echo '<pre>'.
        // echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }
    public function check_workflow_in_calender($data)
    {
        $this->db->select('*');
        $this->db->from('contract_workflow cw'); 
        $this->db->where('cw.id_contract_workflow',$data['id_contract_workflow']);
        if(isset($data['days']))
            $this->db->where('CURDATE() between DATE_SUB(cw.Execute_by,INTERVAL '.$data['days'].' DAY) and cw.Execute_by');
        else
            $this->db->where('CURDATE() < cw.Execute_by');
        $this->db->where('cw.status',1);
        $query = $this->db->get();//echo '<pre>'.
        return $query->result_array();
    }
    public function checkContractReviewCompletedSchedule_bkp($data)
    {
        $this->db->select('*');
        $this->db->from('contract_review cr');
        $this->db->where('cr.contract_id',$data['contract_id']);
        $this->db->where('cr.is_workflow',0);
        $this->db->where_in('cr.contract_review_status',array('review in progress'));
        $query = $this->db->get();
        //echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function checkContractReviewCompletedSchedule($data)
    {
        $this->db->select('*');
        $this->db->from('contract_review cr');
        $this->db->where('cr.contract_id',$data['contract_id']);
        if(isset($data["is_workflow"]) && $data["is_workflow"]==1){
            $this->db->where('cr.is_workflow',1);
            $where_review_progress = "workflow in progress";
        }else{ 
            $this->db->where('cr.is_workflow',0);
            $where_review_progress = "review in progress";
        }
        $this->db->where_in('cr.contract_review_status',array($where_review_progress));
        $query = $this->db->get();
        // echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }

    public function checkContractReviewCompletedScheduleForWorkflow($data)
    {
        $this->db->select('*');
        $this->db->from('contract_workflow cw');
        $this->db->where('cw.id_contract_workflow',$data['id_contract_workflow']);
        //$this->db->where('cr.is_workflow',1);
        $this->db->where_in('cw.workflow_status',array('workflow in progress'));
        //$this->db->where('CURDATE() between DATE_SUB("'.$data['next_recurrence'].'",INTERVAL '.$data['days'].' DAY) and "'.$data['next_recurrence'].'"');
        //$this->db->where('DATE(cr.contract_review_due_date) between DATE_SUB("'.$data['next_recurrence'].'",INTERVAL '.$data['days'].' DAY) and "'.$data['next_recurrence'].'"');
        $query = $this->db->get();
        //echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }

    public function getTopicData($data){


        $this->db->select('q.id_question,q.question_type,ql.question_text,ql.request_for_proof,cqr.question_answer,qo.option_value,cqr.v_question_answer,cqr.second_opinion,cqr.question_feedback,cqr.external_user_question_feedback,if(q.question_type="input",cqr.question_answer,qol.option_name) question_option_answer,if(q.question_type="input",cqr.v_question_answer,qoll.option_name) v_question_option_answer,qoo.option_value as v_option_value,cqr.v_question_feedback,q.provider_visibility');
        $this->db->from('question q');
        $this->db->join('question_language ql','ql.question_id = q.id_question and ql.language_id=1','LEFT');
        $this->db->join('contract_question_review cqr','cqr.question_id = q.id_question ','LEFT');
        $this->db->join('contract_question_review_log l','cqr.id_contract_question_review=l.contract_question_review_id','left');
        $this->db->join('question_option qo', 'qo.question_id=q.id_question and qo.option_value=cqr.question_answer', 'left');
        $this->db->join('question_option_language qol', 'qol.question_option_id=cqr.question_option_id and qol.language_id=1', 'left');
        $this->db->join('question_option qoo', 'qoo.question_id=q.id_question and qoo.option_value=cqr.v_question_answer', 'left');
        $this->db->join('question_option_language qoll', 'qoll.question_option_id=cqr.v_question_option_id and qoll.language_id=1', 'left');
        $this->db->where('q.topic_id',$data['id_topic']);
        if(isset($data['provider_visibility']))
            $this->db->where('q.provider_visibility',$data['provider_visibility']);
        $this->db->where('q.question_status','1');
        $this->db->group_by('q.id_question');
        $this->db->order_by('q.question_order','ASC');
        $this->db->order_by('q.id_question','ASC');
        $questions = $this->db->get()->result_array();
        // echo $this->db->last_query();
        $result['questions'] = $questions;

        foreach($result['questions'] as $k => $v){
            //echo $result['questions'][$k]['id_question'];
            $this->db->select('d.*,concat(u.first_name," ",u.last_name) as uploaded_by,ml.module_name');
            $this->db->from('module_language ml');
            $this->db->join('module m',' m.id_module =ml.module_id','left');
            $this->db->join('topic t','t.module_id=m.id_module','left');
            $this->db->join('question q',' q.topic_id = t.id_topic','left');
            $this->db->join('question_language ql','q.id_question = ql.question_id','left');
            $this->db->join('document d','d.reference_id = q.id_question and d.reference_type = "question"','left');
            $this->db->join('user u','u.id_user =d.uploaded_by','left');
            $this->db->where('d.reference_type','question');
            $this->db->where('d.document_status',1);
            if(isset($data['page_type']) && $data['page_type']='contract_review'){
                $this->db->where('d.reference_id IN (select q_sub.id_question from question q_sub LEFT JOIN question q2_sub on q2_sub.parent_question_id=q_sub.parent_question_id LEFT JOIN topic t2_sub on t2_sub.id_topic=q2_sub.topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id LEFT JOIN contract c2_sub on c2_sub.id_contract=cr2_sub.contract_id and c2_sub.is_deleted=0 LEFT JOIN topic t1_sub on t1_sub.id_topic=q_sub.topic_id LEFT JOIN module m1_sub on m1_sub.id_module=t1_sub.module_id LEFT JOIN contract_review cr1_sub on cr1_sub.id_contract_review=m1_sub.contract_review_id where q2_sub.id_question='.$result['questions'][$k]['id_question'].' and cr1_sub.contract_id=cr2_sub.contract_id)',false,false);
            }
            else {
                $this->db->where('d.reference_id',$result['questions'][$k]['id_question']);
            }
            $query = $this->db->get();
            $attachment=$query->result_array();

            $result['questions'][$k]['attachments'] = $attachment;
        }
        return $result;
    }
    function getContractDeadline($data=array()){
        $this->db->select('c.*');
        $this->db->from('calender c');
        $this->db->join('business_unit bu','bu.customer_id=c.customer_id','left');
        $this->db->join('contract co','co.business_unit_id=bu.id_business_unit and co.is_deleted=0','left');
        $this->db->where('c.date>=CURRENT_DATE()');
        $this->db->where('c.status',1);
        if(isset($data['relationship_category_id']))
            $this->db->where('CONCAT(",", c.relationship_category_id, ",") REGEXP ",'.$data['relationship_category_id'].',"');
        if(isset($data['bussiness_unit_id']))
            $this->db->where('CONCAT(",", c.bussiness_unit_id, ",") REGEXP ",'.$data['bussiness_unit_id'].',"');
        if(isset($data['id_contract']))
            $this->db->where('co.id_contract',$data['id_contract']);
        $this->db->order_by('c.date','asc');
        $this->db->limit('1');
        $query = $this->db->get();
        $result=$query->result_array();
        return isset($result[0]['date'])?$result[0]['date']:NULL;
    }

    public function getDownloadedFile($data){
        if(isset($data['id_document'])) {
            $this->db->select('cd.*');
            $this->db->from('document cd');
            $this->db->where('cd.id_document',$data['id_document']);
            $query = $this->db->get();
            return $query->result_array();
        }
    }

    public function getContractReviewChangeLog($data)
    {

        $modules=$topics=$questions=array();
        if(isset($data['contract_review_id'])) {
            $this->db->select('m.id_module,ml.module_name');
            $this->db->from('module m');
            $this->db->join('module_language ml', 'ml.module_id=m.id_module and ml.language_id=1', 'left');
            $this->db->join('contract_review cr', 'cr.id_contract_review=m.contract_review_id', 'left');
            if (isset($data['id_contract_review']))
                $this->db->where('cr.id_contract_review', $data['id_contract_review']);
            if (isset($data['contract_review_id']))
                $this->db->where('cr.id_contract_review', $data['contract_review_id']);
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id','');
                $this->db->where('cu.user_id',$data['contract_user']);
            }
            $this->db->group_by('m.id_module');
            $this->db->order_by('m.module_order','asc');
            $query = $this->db->get();
            $modules = $query->result_array();
        }

        if(isset($data['id_module'])) {
            $this->db->select('t.id_topic,tl.topic_name,m.id_module');
            $this->db->from('topic t');
            $this->db->join('topic_language tl', 'tl.topic_id=t.id_topic and tl.language_id=1', 'left');
            $this->db->join('module m','m.id_module=t.module_id');
            $this->db->join('contract_review cr', 'cr.id_contract_review=m.contract_review_id', 'left');
            if (isset($data['id_contract_review']))
                $this->db->where('cr.id_contract_review', $data['id_contract_review']);
            if (isset($data['contract_review_id']))
                $this->db->where('cr.id_contract_review', $data['contract_review_id']);
            if (isset($data['id_module']) && $data['id_module']!='all')
                $this->db->where('t.module_id', $data['id_module']);
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id','');
                $this->db->where('cu.user_id',$data['contract_user']);
            }
            $this->db->group_by('t.id_topic');
            $this->db->order_by('m.module_order','asc');
            $this->db->order_by('t.topic_order','asc');
            $query = $this->db->get();
            $topics = $query->result_array();
        }

        if(isset($data['id_module']) || isset($data['id_topic'])) {
            $this->db->select('m.id_module,t.id_topic,q.id_question,ql.question_text,ql.request_for_proof,tl.topic_name,ml.module_name,if(q.question_type="input" || q.question_type="date",cqrl.question_answer,qol.option_name) question_answer,q.question_type,CONCAT_WS(\' \',u.first_name,u.last_name) as answer_by_username,cqrl.updated_on,0 as is_current');
            $this->db->from('contract_question_review_log cqrl');
            $this->db->join('contract_review cr', 'cr.id_contract_review=cqrl.contract_review_id', 'left');
            $this->db->join('contract c', 'c.id_contract=cr.contract_id and c.is_deleted=0', 'left');
            $this->db->join('question q', 'q.id_question=cqrl.question_id', 'left');
            $this->db->join('question_language ql', 'ql.question_id=q.id_question and ql.language_id=1', 'left');
            $this->db->join('question_option qo', 'qo.question_id=q.id_question and qo.id_question_option=cqrl.question_option_id', 'left');
            $this->db->join('question_option_language qol', 'qol.question_option_id=qo.id_question_option and qol.language_id=1', 'left');
            $this->db->join('topic t', 't.id_topic=q.topic_id', 'left');
            $this->db->join('topic_language tl', 'tl.topic_id=t.id_topic and tl.language_id=1', 'left');
            $this->db->join('module m', 'm.id_module=t.module_id', 'left');
            $this->db->join('module_language ml', 'ml.module_id=m.id_module and ml.language_id=1', 'left');
            $this->db->join('user u', 'u.id_user=cqrl.updated_by', 'left');
            $this->db->where("cqrl.question_answer!=''");
            if (isset($data['id_contract_review']))
                $this->db->where('cr.id_contract_review', $data['id_contract_review']);
            if (isset($data['contract_review_id']))
                $this->db->where('cr.id_contract_review', $data['contract_review_id']);
            if (isset($data['id_contract']))
                $this->db->where('c.id_contract', $data['id_contract']);
            if (isset($data['id_module']) && $data['id_module']!='all')
                $this->db->where('m.id_module', $data['id_module']);
            if (isset($data['id_topic']) && $data['id_topic']!='all')
                $this->db->where('t.id_topic', $data['id_topic']);
            if (isset($data['id_question']))
                $this->db->where('q.id_question', $data['id_question']);
            if(isset($data['contract_user'])){
                $this->db->join('contract_user cu','m.id_module=cu.module_id','');
                $this->db->where('cu.user_id',$data['contract_user']);
                //$this->db->group_by('m.id_module');
            }

            //$this->db->order_by('cqrl.id_contract_question_review_log','desc');
            $this->db->order_by('m.module_order','asc');
            $this->db->order_by('t.topic_order','asc');
            $this->db->order_by('q.question_order','asc');
            $this->db->order_by('cqrl.updated_on','asc');
            $this->db->group_by('cqrl.id_contract_question_review_log');
            $query=$this->db->get();
            $questions_log=$query->result_array();
            $question_ids=array();
            foreach($questions_log as $k=>$v){
                $question_ids[]=$v['id_question'];

            }
            $question_ids=array_unique($question_ids);
            $questions_current=array();
            if(count($question_ids)>0) {
                $this->db->select('m.id_module,t.id_topic,q.id_question,ql.question_text,ql.request_for_proof,tl.topic_name,ml.module_name,if((q.question_type="input"||q.question_type="date"),cqrl.question_answer,qol.option_name) question_answer,q.question_type,CONCAT_WS(\' \',u.first_name,u.last_name) as answer_by_username,cqrl.updated_on,1 as is_current');
                $this->db->from('contract_question_review cqrl');
                $this->db->join('contract_review cr', 'cr.id_contract_review=cqrl.contract_review_id', 'left');
                $this->db->join('contract c', 'c.id_contract=cr.contract_id and c.is_deleted=0', 'left');
                $this->db->join('question q', 'q.id_question=cqrl.question_id', 'left');
                $this->db->join('question_language ql', 'ql.question_id=q.id_question and ql.language_id=1', 'left');
                $this->db->join('question_option qo', 'qo.question_id=q.id_question and qo.id_question_option=cqrl.question_option_id', 'left');
                $this->db->join('question_option_language qol', 'qol.question_option_id=qo.id_question_option and qol.language_id=1', 'left');
                $this->db->join('topic t', 't.id_topic=q.topic_id', 'left');
                $this->db->join('topic_language tl', 'tl.topic_id=t.id_topic and tl.language_id=1', 'left');
                $this->db->join('module m', 'm.id_module=t.module_id', 'left');
                $this->db->join('module_language ml', 'ml.module_id=m.id_module and ml.language_id=1', 'left');
                $this->db->join('user u', 'u.id_user=cqrl.updated_by', 'left');
                if (isset($data['id_contract_review']))
                    $this->db->where('cr.id_contract_review', $data['id_contract_review']);
                if (isset($data['contract_review_id']))
                    $this->db->where('cr.id_contract_review', $data['contract_review_id']);
                if (isset($data['id_contract']))
                    $this->db->where('c.id_contract', $data['id_contract']);
                if (isset($data['id_module']) && $data['id_module'] != 'all')
                    $this->db->where('m.id_module', $data['id_module']);
                if (isset($data['id_topic']) && $data['id_topic'] != 'all')
                    $this->db->where('t.id_topic', $data['id_topic']);
                if (isset($data['id_question']))
                    $this->db->where('q.id_question', $data['id_question']);
                if (isset($data['contract_user'])) {
                    $this->db->join('contract_user cu', 'm.id_module=cu.module_id', '');
                    $this->db->where('cu.user_id', $data['contract_user']);
                    //$this->db->group_by('m.id_module');
                }
                $this->db->where_in('q.id_question', $question_ids);
                $this->db->order_by('m.module_order', 'asc');
                $this->db->order_by('t.topic_order', 'asc');
                $this->db->order_by('q.question_order', 'asc');
                $this->db->group_by('q.id_question');
                $query = $this->db->get();
                $questions_current = $query->result_array();
            }
            /*echo "<pre>";print_r($questions_current);echo "</pre>";*/
            $questions=array_merge($questions_log,$questions_current);
            // print_r($questions_log);
            // print_r('$questions_current'.$questions_current);exit;
        }
        return array('modules'=>$modules,'topics'=>$topics,'questions'=>$questions);
    }
    public function getContractReviewDisucussionData($data){

        $this->db->select('q.id_question,ql.question_text,q.question_type,t.id_topic,tl.topic_name,m.id_module,ml.module_name,m.contract_review_id,crd.id_contract_review_discussion,crdq.id_contract_review_discussion_question,crdq.remarks,crdq.status,if(crdq.updated_on is not null,CONCAT_WS(\' \',u1.first_name,u1.last_name),CONCAT_WS(\' \',u.first_name,u.last_name)) as created_by,if(crdq.updated_on is not null,crdq.updated_on,crdq.created_on) as created_on,CONCAT_WS(\' \',u2.first_name,u2.last_name) discussion_created_by,crd.created_on as discussion_created_on,crd.discussion_status,CONCAT_WS(\' \',u3.first_name,u3.last_name) discussion_closed_by,crd.updated_on as discussion_closed_on,crd.is_auto_close');
        $this->db->from('question q');
        $this->db->join('question_language ql', 'ql.question_id=q.id_question and ql.language_id=1', 'left');
        $this->db->join('topic t', 't.id_topic=q.topic_id', 'left');
        $this->db->join('topic_language tl', 'tl.topic_id=t.id_topic and tl.language_id=1', 'left');
        $this->db->join('module m', 'm.id_module=t.module_id', 'left');
        $this->db->join('module_language ml', 'ml.module_id=m.id_module and ml.language_id=1', 'left');
        $this->db->join('contract_review_discussion crd', 'crd.module_id=m.id_module and m.contract_review_id=crd.contract_review_id', 'left');
        if(isset($data['contract_user'])) {
            /*$this->db->join('contract_review_discussion_question crdq', 'crdq.question_id=q.id_question and crdq.status=1', 'left');
            $this->db->join('contract_review_discussion crd', 'crd.id_contract_review_discussion=crdq.contract_review_discussion_id and m.contract_review_id=crd.contract_review_id and crd.discussion_status=1', 'left');*/
            $this->db->join('contract_review_discussion_question crdq', 'crdq.question_id=q.id_question and crdq.status=1 and crd.id_contract_review_discussion=crdq.contract_review_discussion_id', 'left');
            //$this->db->join('contract_review_discussion crd', 'crd.id_contract_review_discussion=crdq.contract_review_discussion_id and m.contract_review_id=crd.contract_review_id and crd.discussion_status=1', 'left');
        }
        else{
            //$this->db->join('contract_review_discussion_question crdq', 'crdq.question_id=q.id_question and crdq.status=1', '');
            //$this->db->join('contract_review_discussion crd', 'crd.id_contract_review_discussion=crdq.contract_review_discussion_id and m.contract_review_id=crd.contract_review_id and crd.discussion_status=1', '');
            $this->db->join('contract_review_discussion_question crdq', 'crdq.question_id=q.id_question and crdq.status=1 and crd.id_contract_review_discussion=crdq.contract_review_discussion_id', 'left');
            $this->db->where('crd.id_contract_review_discussion is not null');
            $this->db->where('crdq.id_contract_review_discussion_question is not null');
        }
        if (isset($data['id_contract_review']))
            $this->db->where('m.contract_review_id', $data['id_contract_review']);
        if (isset($data['contract_review_id']))
            $this->db->where('m.contract_review_id', $data['contract_review_id']);
        if(isset($data['contract_user']) && !isset($data['id_contract_review_discussion'])){
            $this->db->join('contract_user cu','m.id_module=cu.module_id','');
            $this->db->where('cu.user_id',$data['contract_user']);
            //$this->db->group_by('q.id_question');
        }
        if(isset($data['discussion_status'])){
            $this->db->where('crd.discussion_status',$data['discussion_status']);
        }
        if(isset($data['module_ids'])){
            $this->db->where_in('crd.module_id',$data['module_ids']);
        }
        if(isset($data['id_contract_review_discussion'])) {
            $this->db->where('crd.id_contract_review_discussion',$data['id_contract_review_discussion']);
        }
        if($data['contribution_type'] == 2 || $data['contribution_type'] == 3)
                $this->db->where('q.provider_visibility',1);
        $this->db->join('user u', 'u.id_user=crdq.created_by', 'left');
        $this->db->join('user u1', 'u1.id_user=crdq.updated_by', 'left');
        $this->db->join('user u2', 'u2.id_user=crd.created_by', 'left');
        $this->db->join('user u3', 'u3.id_user=crd.updated_by', 'left');
        $this->db->order_by('m.module_order','ASC');
        $this->db->order_by('t.topic_order','ASC');
        $this->db->order_by('q.question_order','ASC');
        $query = $this->db->get();//echo '<pre>'.

        $result=$query->result_array();
        foreach($result as $k=>$v){
            $result[$k]['change_log']=$this->getContractReviewDisucussionLogData(array('contract_review_discussion_question_id'=>($v['id_contract_review_discussion_question']==NULL?0:$v['id_contract_review_discussion_question'])));
            if(isset($v['discussion_status']) && $v['discussion_status']==2 && $v['id_contract_review_discussion_question']==NULL)
                unset($result[$k]);
            $this->db->select('q.*,ql.option_name');
            $this->db->from('question_option q');
            $this->db->join('question_option_language ql','ql.question_option_id = q.id_question_option and ql.language_id=1','LEFT');
            $this->db->where('q.question_id',$v['id_question']);
            $this->db->where('q.status','1');
            $question_options = $this->db->get();
            $question_options = $question_options->result_array();
            $result[$k]['options']=$question_options;
            //$this->db->select('')
            //$question_answere =             
            // foreach($result[$key]['topics'][$kt]['questions'][$ktq]['options'] as $ktqo=>$vtqo){
            //     if($vtq['parent_question_answer']==$vtqo['parent_question_option_id'])
            //         $result[$key]['topics'][$kt]['questions'][$ktq]['parent_question_answer']=$vtqo['id_question_option'];
            // }

        }
        //echo '<pre>'.print_r($result);exit;
        return $result;
    }
    public function getContractReviewDisucussionLogData($data){
        $this->db->select('crdql.*,CONCAT_WS(\' \',u.first_name,u.last_name) as created_by_name');
        $this->db->from('contract_review_discussion_question_log crdql');
        if(isset($data['contract_review_discussion_question_id'])) {
            $this->db->where('crdql.contract_review_discussion_question_id',$data['contract_review_discussion_question_id']);
        }
        if(isset($data['contract_review_id'])){
            $this->db->select('crdq.remarks question_remarks');
            $this->db->join('contract_review_discussion_question crdq', 'crdq.id_contract_review_discussion_question=crdql.contract_review_discussion_question_id');
            $this->db->join('contract_review_discussion crd', 'crd.id_contract_review_discussion=crdq.contract_review_discussion_id');
            $this->db->where('crd.contract_review_id',$data['contract_review_id']);
            $this->db->where('crdq.question_id',$data['question_id']);
        }
        $this->db->join('user u', 'u.id_user=crdql.created_by');
        $this->db->order_by('crdql.id_contract_review_discussion_question_log','desc');
        $query = $this->db->get();//echo '<pre>'.
        $result=$query->result_array();
        return $result;
    }

    public function getContractReviewDiscussionQuestinRemarks($data){
        $this->db->select('*')->from('contract_review_discussion_question crdq');
        $this->db->join('contract_review_discussion crd', 'crd.id_contract_review_discussion=crdq.contract_review_discussion_id');
        $this->db->where('crd.contract_review_id',$data['contract_review_id']);
        $this->db->where('crdq.question_id',$data['question_id']);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        $result=$query->result_array();
        return $result;
    }
    public function getContractReviewDiscussion($data=array()){
        $this->db->select('*');
        $this->db->from('contract_review_discussion crd');
        if (isset($data['id_contract_review']))
            $this->db->where('crd.contract_review_id', $data['id_contract_review']);
        if (isset($data['id_module']))
            $this->db->where('crd.module_id', $data['id_module']);
        $query = $this->db->get();
        $result=$query->result_array();
        return isset($result[0])?$result[0]:'';
    }
    public function getContractDiscussion($data=array()){
        $this->db->select('crd.*,CONCAT_WS(\' \',u2.first_name,u2.last_name) discussion_created_by,crd.created_on as discussion_created_on,CONCAT_WS(\' \',u3.first_name,u3.last_name) discussion_closed_by,crd.updated_on as discussion_closed_on,CONCAT_WS(\'/\',CONCAT_WS(\' \',u2.first_name,u2.last_name),crd.updated_on,ml.module_name) as option_name');
        $this->db->from('contract_review_discussion crd');
        $this->db->join('contract_review cr','cr.id_contract_review=crd.contract_review_id');
        $this->db->join('contract c','c.id_contract=cr.contract_id');
        $this->db->join('module m','m.id_module=crd.module_id');
        $this->db->join('module_language ml','ml.module_id=m.id_module and ml.language_id=1');
        $this->db->join('user u2', 'u2.id_user=crd.created_by', 'left');
        $this->db->join('user u3', 'u3.id_user=crd.updated_by', 'left');
        if (isset($data['id_contract_review']))
            $this->db->where('crd.contract_review_id', $data['id_contract_review']);
        if (isset($data['id_module']))
            $this->db->where('crd.module_id', $data['id_module']);
        if (isset($data['module_ids']))
            $this->db->where_in('crd.module_id', $data['module_ids']);
        if (isset($data['id_contract']))
            $this->db->where('cr.contract_id', $data['id_contract']);
        if (isset($data['is_workflow']))
            $this->db->where('cr.is_workflow', $data['is_workflow']);
        if (isset($data['contract_workflow_id']))
            $this->db->where('cr.contract_workflow_id', $data['contract_workflow_id']);
        if (isset($data['discussion_status']))
            $this->db->where('crd.discussion_status', $data['discussion_status']);
        if(isset($data['id_contract_review_discussion'])) {
            $this->db->where('crd.id_contract_review_discussion',$data['id_contract_review_discussion']);
        }
        if (isset($data['contract_review_status']))
            $this->db->where('cr.contract_review_status', $data['contract_review_status']);
        $this->db->where('c.is_deleted','0');
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        $result=$query->result_array();
        return $result;
    }
    public function getContractReviewDiscussionModuleCount($data=array()){
        // echo '<pre>'.print_r($data);exit;
        $this->db->select('*');
        $this->db->from('contract_review_discussion crd');
        if (isset($data['id_contract_review']))
            $this->db->where('crd.contract_review_id', $data['id_contract_review']);
        if (isset($data['module_id']))
            $this->db->where('crd.module_id', $data['module_id']);
        if (isset($data['module_ids']) && count($data['module_ids']) > 0)
            $this->db->where_in('crd.module_id', $data['module_ids']);
        if(isset($data['discussion_status'])){
            $this->db->where('crd.discussion_status',$data['discussion_status']);
        }
        $query = $this->db->get();
        $result=$query->result_array();
        // echo 
        return $result;
    }
    public function addContractReviewDiscussion($data=array()){

        $this->db->insert('contract_review_discussion', $data);
        return $this->db->insert_id();

    }
    public function updateContractReviewDiscussion($data=array()){
        $this->db->where('id_contract_review_discussion', $data['id_contract_review_discussion']);
        if(isset($data['discussion_status']))
            $this->db->where('discussion_status!=', $data['discussion_status']);
        $this->db->update('contract_review_discussion', $data);
    }
    public function addContractReviewDiscussionQuestion($data=array()){
        foreach($data as $k=>$v) {
            $this->db->insert('contract_review_discussion_question', $v);
            $data[$k]['id_contract_review_discussion_question']=$this->db->insert_id();
            $data[$k]['status']=1;
        }
        /*foreach($data as $k=>$v) {
            $log='';
            $log['contract_review_discussion_question_id']=$v['id_contract_review_discussion_question'];
            $log['remarks']=$v['remarks'];
            $log['status']=$v['status'];
            $log['created_by']=$v['created_by'];
            $log['created_on']=$v['created_on'];
            $this->db->insert('contract_review_discussion_question_log', $log);
        }*/
    }
    public function updateContractReviewDiscussionQuestion($data=array()){
        $data_new = $data;
        foreach($data_new as $k=>$v) {
            unset($v['remarks']);
            unset($v['updated_on']);
            $this->db->where('id_contract_review_discussion_question', $v['id_contract_review_discussion_question']);
            $this->db->update('contract_review_discussion_question', $v);
        }
        foreach($data as $k=>$v) {
            $log=array();
            $log['contract_review_discussion_question_id']=$v['id_contract_review_discussion_question'];
            $log['remarks']=$v['remarks'];
            $log['status']=$v['status'];
            $log['created_by']=$v['updated_by'];
            $log['created_on']=$v['updated_on'];
            $this->db->insert('contract_review_discussion_question_log', $log);
        }
    }
    /* public function getContractsToBeScheduled_bkp($data=array()){

        if(isset($data['reminder']) && $data['reminder']=='r2')
        {
            $this->db->select('*,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('calender c');
            $this->db->join('contract cr','c.relationship_category_id=cr.relationship_category_id and cr.is_deleted=0','left');
            $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
            $this->db->where_in('cr.contract_status',array('pending review'));
            $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
            $this->db->where('c.status',1);
            $this->db->where('cr.reminder_date2 IS NOT NULL');
            $this->db->where('CURDATE() = cr.reminder_date2');
        }else if(isset($data['reminder']) && $data['reminder']=='r3')
        {
            $this->db->select('*,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('calender c');
            $this->db->join('contract cr','c.relationship_category_id=cr.relationship_category_id and cr.is_deleted=0','left');
            $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
            $this->db->where_in('cr.contract_status',array('pending review'));
            $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
            $this->db->where('c.status',1);
            $this->db->where('cr.reminder_date3 IS NOT NULL');
            $this->db->where('CURDATE() = cr.reminder_date3');
        }
        else if(isset($data['reminder']) && $data['reminder']=='r1')
        {
            $this->db->select('*,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('calender c');
            $this->db->join('contract cr','c.relationship_category_id=cr.relationship_category_id and cr.is_deleted=0','left');
            $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
            $this->db->where_in('cr.contract_status',array('pending review'));
            $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
            $this->db->where('c.status',1);
            $this->db->where('cr.reminder_date1 IS NOT NULL');
            $this->db->where('CURDATE() = cr.reminder_date1');
        }else
        {
            $this->db->select('*,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('calender c');
            $this->db->join('contract cr','c.relationship_category_id=cr.relationship_category_id and cr.is_deleted=0','left');
            $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
            $this->db->where_in('cr.contract_status',array('new','review finalized'));
            $this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
            $this->db->where('c.status',1);
        }


        $query = $this->db->get();
        //echo 
        return $query->result_array();
    } */

        
    public function getContractsToBeScheduled($data=array())
    {
        $this->db->select('cr.id_calender,cr.bussiness_unit_id,cr.relationship_category_id,cr.provider_id,cr.contract_id,cr.date');
        $this->db->from('calender cr');
        $this->db->where('cr.recurrence_till >= CURDATE() + interval 30 day ');
        $this->db->where('cr.status',1);
        $query = $this->db->get();
        //echo $this->db->last_query().'<br>';
        $result= $query->result_array();
        $contract =array();
        foreach($result as $k=>$v)
        {
            //print_r($v); exit;
            //print_r($result); exit;
            if(isset($data['reminder']) && $data['reminder']=='r2')
            {
                $this->db->select('*,"'.$v['date'].'" as next_recurrence,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
                $this->db->from('contract c');
                $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
                $this->db->where_in('c.contract_status',array('pending review'));
                if(isset($v['bussiness_unit_id']))
                    $this->db->where_in('c.business_unit_id',explode(',',$v['bussiness_unit_id']));
                if(isset($v['relationship_category_id']) && $v['relationship_category_id'] != '')
                    $this->db->where_in('c.relationship_category_id',explode(',',$v['relationship_category_id']));
                if(isset($v['provider_id']) && $v['provider_id'] != '')
                    $this->db->where_in('c.provider_name',explode(',',$v['provider_id']));
                $this->db->where('CURDATE() between DATE_SUB("'.$v['date'].'",INTERVAL rcr.days DAY) and 
                "'.$v["date"].'"');
                $this->db->where('c.reminder_date2 IS NOT NULL');
                $this->db->where('CURDATE() = c.reminder_date2');
				$this->db->group_by('c.id_contract');
                $query = $this->db->get();
                //echo $this->db->last_query().'<br>';
                foreach($query->result_array() as $k1 =>$v1)
                    $contract[$k]= $v1;
                // return $query->result_array();
            }
            //return $contract[$k];
            else if(isset($data['reminder']) && $data['reminder']=='r3')
            {
                $this->db->select('*,"'.$v['date'].'" as next_recurrence,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
                $this->db->from('contract c');
                $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
                $this->db->where_in('c.contract_status',array('pending review'));
                if(isset($v['bussiness_unit_id']))
                    $this->db->where_in('c.business_unit_id',explode(',',$v['bussiness_unit_id']));
                if(isset($v['relationship_category_id']) && $v['relationship_category_id'] != '')
                    $this->db->where_in('c.relationship_category_id',explode(',',$v['relationship_category_id']));
                if(isset($v['provider_id']) && $v['provider_id'] != '')
                    $this->db->where_in('c.provider_name',explode(',',$v['provider_id']));
                $this->db->where('CURDATE() between DATE_SUB("'.$v['date'].'",INTERVAL rcr.days DAY) and 
                "'.$v["date"].'"');
                $this->db->where('c.reminder_date3 IS NOT NULL');
                $this->db->where('CURDATE() = c.reminder_date3');
				$this->db->group_by('c.id_contract');
                $query = $this->db->get();
            // echo $this->db->last_query().'<br>'; exit;
            foreach($query->result_array() as $k1 =>$v1)
                    $contract[$k]= $v1;
            //return $query->result_array();
            }
            //return $contract[$k];
            else if(isset($data['reminder']) && $data['reminder']=='r1')
            {
                $this->db->select('*,"'.$v['date'].'" as next_recurrence,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
                $this->db->from('contract c');
                $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
                $this->db->where_in('c.contract_status',array('pending review'));
                if(isset($v['bussiness_unit_id']))
                    $this->db->where_in('c.business_unit_id',explode(',',$v['bussiness_unit_id']));
                if(isset($v['relationship_category_id']) && $v['relationship_category_id'] != '')
                    $this->db->where_in('c.relationship_category_id',explode(',',$v['relationship_category_id']));
                if(isset($v['provider_id']) && $v['provider_id'] != '')
                    $this->db->where_in('c.provider_name',explode(',',$v['provider_id']));
                $this->db->where('CURDATE() between DATE_SUB("'.$v['date'].'",INTERVAL rcr.days DAY) and "'.$v["date"].'"');
                $this->db->where('c.reminder_date1 IS NOT NULL');
                $this->db->where('CURDATE() = c.reminder_date1');
				$this->db->group_by('c.id_contract');
                $query = $this->db->get();
                //echo $this->db->last_query().'<br>'; exit;
            foreach($query->result_array() as $k1 =>$v1)
                    $contract[$k]= $v1;
            //return $query->result_array();
            }
            //return $contract;
            else
            {

                $this->db->select('*,"'.$v['date'].'" as next_recurrence,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
                $this->db->from('contract c');
                $this->db->join('relationship_category_remainder rcr','c.relationship_category_id=rcr.relationship_category_id','left');
                $this->db->where_in('c.contract_status',array('new','review finalized'));
                if(isset($v['bussiness_unit_id']))
                    $this->db->where_in('c.business_unit_id',explode(',',$v['bussiness_unit_id']));
                if(isset($v['relationship_category_id']) && $v['relationship_category_id'] != '')
                    $this->db->where_in('c.relationship_category_id',explode(',',$v['relationship_category_id']));
                if(isset($v['provider_id']) && $v['provider_id'] != '')
                    $this->db->where_in('c.provider_name',explode(',',$v['provider_id']));
                $this->db->where('CURDATE() between DATE_SUB("'.$v['date'].'",INTERVAL rcr.days DAY)
                and "'.$v["date"].'"');
                $this->db->group_by('c.id_contract');
                //$this->db->where('CURDATE() between DATE_SUB(c.date,INTERVAL rcr.days DAY) and c.date');
                $query = $this->db->get();
                //if($k=7)
                // if($v['date'] == '2019-09-05'){
                //     echo $this->db->last_query().'<br>';  
                //     echo '<pre>'.print_r($v);exit;  
                // }
                //echo $this->db->last_query().'<br>';  
                foreach($query->result_array() as $k1 =>$v1) 
                    $contract[]= $v1;
                //return $query->result_array();

            }
        }
        //print_r($contract); exit;
        return $contract;
    }

    public function getContractsToBeScheduledForWorkflow($data=array())
    {
        //print_r($data);exit;
        if(isset($data['reminder']) && $data['reminder']=='r2')
        {
            //echo 'block r2';
            $this->db->select('*,c.contract_name,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('contract_workflow cw');
            $this->db->join('calender cr','cw.calender_id=cr.id_calender','left');
            $this->db->join('contract c','cw.contract_id=c.id_contract','');
            $this->db->join('relationship_category_remainder rcr','cr.customer_id=rcr.customer_id','left');
            $this->db->where_in('cw.workflow_status',array('pending workflow'));
            $this->db->where('rcr.relationship_category_id is null');
            $this->db->where('CURDATE() between DATE_SUB(cw.execute_by,INTERVAL rcr.days DAY) and cw.execute_by');
            $this->db->where('cw.reminder_date2 IS NOT NULL');
            $this->db->where('CURDATE() = cw.reminder_date2');
            $query = $this->db->get();
                //echo 'r2'. $this->db->last_query().'<br>'; 
            // foreach($query->result_array() as $k1 =>$v1)
            //         $contract[$k]= $v1;
            return $query->result_array();
        }
        else if(isset($data['reminder']) && $data['reminder']=='r3')
        {
            //echo 'block r3';
            $this->db->select('*,c.contract_name,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('contract_workflow cw');
            $this->db->join('calender cr','cw.calender_id=cr.id_calender','left');
            $this->db->join('contract c','cw.contract_id=c.id_contract','');
            $this->db->join('relationship_category_remainder rcr','cr.customer_id=rcr.customer_id','left');
            $this->db->where_in('cw.workflow_status',array('pending workflow'));
            $this->db->where('rcr.relationship_category_id is null');
            $this->db->where('CURDATE() between DATE_SUB(cw.execute_by,INTERVAL rcr.days DAY) and cw.execute_by');
            $this->db->where('cw.reminder_date3 IS NOT NULL');
            $this->db->where('CURDATE() = cw.reminder_date3');
            $query = $this->db->get();
                //echo 'r3'.$this->db->last_query().'<br>'; 
            // foreach($query->result_array() as $k1 =>$v1)
            //      $contract[$k]= $v1;
            return $query->result_array();
        }
        else if(isset($data['reminder']) && $data['reminder']=='r1')
        {
            //echo 'block r1';
            $this->db->select('*,c.contract_name,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('contract_workflow cw');
            $this->db->join('calender cr','cw.calender_id=cr.id_calender','left');
            $this->db->join('contract c','cw.contract_id=c.id_contract','');
            $this->db->join('relationship_category_remainder rcr','cr.customer_id=rcr.customer_id','left');
            $this->db->where_in('cw.workflow_status',array('pending workflow'));
            $this->db->where('rcr.relationship_category_id is null');
            $this->db->where('CURDATE() between DATE_SUB(cw.execute_by,INTERVAL rcr.days DAY) and cw.execute_by');
            $this->db->where('cw.reminder_date1 IS NOT NULL');
            $this->db->where('CURDATE() = cw.reminder_date1');
            $query = $this->db->get();
            //echo 'r1'.$this->db->last_query().'<br>'; exit;
            // foreach($query->result_array() as $k1 =>$v1)
            //         $contract[$k]= $v1;
            return $query->result_array();
        }
        //return $contract;
        else
        {
            //echo 'no type'; 
            $this->db->select('*, c.contract_name,CURDATE() as reminder_date_1,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL rcr.r2_days DAY) as reminder_date_2,DATE_ADD(DATE_FORMAT(CURDATE(),\'%Y-%m-%d\'),INTERVAL (rcr.r3_days+rcr.r2_days) DAY) as reminder_date_3');
            $this->db->from('contract_workflow cw');
            $this->db->join('calender cr','cw.calender_id=cr.id_calender','left');
            $this->db->join('contract c','cw.contract_id=c.id_contract','');
            $this->db->join('relationship_category_remainder rcr','cr.customer_id=rcr.customer_id','left');
            $this->db->where('rcr.relationship_category_id is null');
            $this->db->where_in('cw.workflow_status',array('new','workflow finalized'));
            $this->db->where('CURDATE() between DATE_SUB(cw.execute_by,INTERVAL rcr.days DAY)
                and cw.execute_by');
            $this->db->group_by('cw.id_contract_workflow');
            $query = $this->db->get();
            //echo 'else'.$this->db->last_query(); exit;
            //if($k=7)
            //echo $this->db->last_query().'<br>';  
            // foreach($query->result_array() as $k1 =>$v1) 
            //     $contract[$k]= $v1;
            return $query->result_array();

        }
           
    }
    public function updateContractWorkflow($data)
    {
        $this->db->where('id_contract_workflow',$data['id_contract_workflow']);
        $this->db->update('contract_workflow',$data);
        return 1;
    }

    public function getEmailTemplate($data){
        //echo '<pre>'.print_r($data);exit;
        /*if(isset($data['search'])) {
            $data['search']=$this->db->escape($data['search']);
        }*/
        $this->db->select('et.*,etl.*');
        $this->db->from('email_template et');
        if(isset($data['language_id']))
        {
            $languageId = $data['language_id'] ;
            $this->db->join('email_template_language etl','et.id_email_template = etl.email_template_id and etl.language_id='.$languageId,'');
        }
        else
        {
            $this->db->join('email_template_language etl','et.id_email_template = etl.email_template_id','');
        }
       
        if(isset($data['id_email_template']))
            $this->db->where('et.id_email_template',$data['id_email_template']);
        if(isset($data['customer_id']))
            $this->db->where('et.customer_id',$data['customer_id']);
        if(isset($data['module']))
            $this->db->where('et.module_name',$data['module']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('et.module_name', $data['search'], 'both');
            $this->db->or_like('etl.template_subject', $data['search'], 'both');
            $this->db->or_like('etl.template_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(et.module_name like "%'.$data['search'].'%"
            or etl.template_subject like "%'.$data['search'].'%"
            or etl.template_name like "%'.$data['search'].'%")');*/
        $count = $this->db->get();


        $this->db->select('et.*,etl.*');
        $this->db->from('email_template et');
        if(isset($data['language_id']))
        {
            $languageId = $data['language_id'] ;
            $this->db->join('email_template_language etl','et.id_email_template = etl.email_template_id and etl.language_id='.$languageId,'');
        }
        else
        {
            $this->db->join('email_template_language etl','et.id_email_template = etl.email_template_id','');
        }
       
        if(isset($data['id_email_template']))
            $this->db->where('et.id_email_template',$data['id_email_template']);
        if(isset($data['customer_id']))
            $this->db->where('et.customer_id',$data['customer_id']);
        if(isset($data['module']))
            $this->db->where('et.module_name',$data['module']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('et.module_name', $data['search'], 'both');
            $this->db->or_like('etl.template_subject', $data['search'], 'both');
            $this->db->or_like('etl.template_name', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(et.module_name like "%'.$data['search'].'%"
            or etl.template_subject like "%'.$data['search'].'%"
            or etl.template_name like "%'.$data['search'].'%")');*/
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('et.id_email_template','ASC');
        $result = $this->db->get();
        $final_result=$result->result_array();
        foreach($final_result as $k=>$v){
            $final_result[$k]['header']=EMAIL_HEADER_CONTENT;
            $final_result[$k]['footer']=EMAIL_FOOTER_CONTENT;
        }
        return array('total_records'=>$count->num_rows(),'data'=>$final_result);
    }

    public function updateEmailTemplate($data){
        if(isset($data['id_email_template'])){
            $this->db->where('id_email_template',$data['id_email_template']);
            $this->db->update('email_template',$data);
        }
        if(isset($data['id_email_template_language'])) {
            $this->db->where('id_email_template_language',$data['id_email_template_language']);
            $this->db->update('email_template_language', $data);
        }
    }

    public function insertEmailTemplate($data){
        if(isset($data['customer_id'])){
            $customer_id = $this->getUniqueCustomerId($data['email_template']['parent_email_template_id']);
            if (count($customer_id)>1){
                $data['email_template']['customer_id'] = $data['customer_id'];
                $this->db->insert('email_template', $data['email_template']);
                $data['email_template_language']['email_template_id'] = $this->db->insert_id();
                $this->db->insert('email_template_language', $data['email_template_language']);
            }


        }else {
            $customer_id = $this->getUniqueCustomerId($data['email_template']['parent_email_template_id']);
            foreach ($customer_id as $k => $v) {
                if ($v->customer_id == 0)
                    continue;
                $data['email_template']['customer_id'] = $v->customer_id;
                $this->db->insert('email_template', $data['email_template']);
                $data['email_template_language']['email_template_id'] = $this->db->insert_id();
                $this->db->insert('email_template_language', $data['email_template_language']);

            }
        }
        return true;
    }
    public function getUniqueCustomerId($data){
        $q='SELECT DISTINCT(A.customer_id)
                                    FROM `email_template` A
                                    WHERE A.`customer_id` not IN(SELECT DISTINCT(A.customer_id)
                                    FROM `email_template` A
                                    WHERE A.`parent_email_template_id` =?) and A.`customer_id`>0';
        $query = $this->db->query($q,array($data));
        $result = $query->result();
        return $result;
    }
    public function migrateContractUsersFromOldReview($data=array()){
        $query = $this->db->query('INSERT into contract_user (contract_id,module_id,user_id,status,created_by,created_on,contract_review_id)
select cr.contract_id,m2.id_module,cu.user_id,m2.module_status status,'.$this->db->escape($data['created_by']).' created_by,"'.currentDate().'" as created_on,m2.contract_review_id from contract_user cu join module m on m.id_module=cu.module_id join contract_review cr on cr.id_contract_review=m.contract_review_id
left join module m2 on m2.parent_module_id=m.parent_module_id and m2.contract_review_id='.$this->db->escape($data['new_contract_review_id']).'
where m.contract_review_id='.$this->db->escape($data['old_contract_review_id']));
        $q='select cr.contract_id,m2.id_module,cu.user_id,m2.module_status status,? created_by,"'.currentDate().'" as created_on,m2.contract_review_id from contract_user cu join module m on m.id_module=cu.module_id join contract_review cr on cr.id_contract_review=m.contract_review_id
left join module m2 on m2.parent_module_id=m.parent_module_id and m2.contract_review_id=?
where cu.`status`=1 and m.contract_review_id=?';
        $query = $this->db->query($q,array($data['created_by'],$data['new_contract_review_id'],$data['old_contract_review_id']));
        $result = $query->result_array();
        // echo '<pre>'.
        return $result;
        /*
         * INSERT into contract_user (contract_id,module_id,user_id,status,created_by,created_on)
select cr.contract_id,m2.id_module,cu.user_id,'1' status,'46' created_by,NOW() as created_on from contract_user cu join module m on m.id_module=cu.module_id join contract_review cr on cr.id_contract_review=m.contract_review_id
left join module m2 on m2.parent_module_id=m.parent_module_id and m2.contract_review_id=17
where cu.`status`=1 and m.contract_review_id=15;
         */
    }

    public function getDailyMailData($data){
        $date1=$data['date'];
        //echo $date1;exit;
        $date2=date('Y-m-d',strtotime('+1 day'. $data['date']));
        if(isset($data['run']) && $data['run']==1){
            $this->db->query('INSERT into daily_update_customer (customer_id,date,created_on) (
                            select DISTINCT(c.id_customer),"'.$date1.'",now() from customer c LEFT JOIN daily_update_customer dup on
                            dup.customer_id=c.id_customer and date="'.$date1.'" JOIN user u on c.id_customer=u.customer_id and u.user_role_id=2
                            and dup.date is null);');
            return 1;
        }

        if(isset($data['review_started']) && $data['review_started']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cr.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id','');
            $this->db->join('user u','u.id_user = cr.created_by','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role  and ur.role_status=1','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->where('cr.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');
        }
        if(isset($data['review_updated']) && $data['review_updated']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cr.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id','');
            $this->db->join('user u','u.id_user = cr.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role  and ur.role_status=1','');
            $this->db->where('cr.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['review_finalized']) && $data['review_finalized']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cr.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id','');
            $this->db->join('user u','u.id_user = cr.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('cr.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cr.contract_review_status','finished');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['contributor_add']) && $data['contributor_add']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cr.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_user cr','cl.id_contract=cr.contract_id','');
            $this->db->join('user u','u.id_user = cr.created_by','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->where('cr.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['contributor_remove']) && $data['contributor_remove']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cr.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_user cr','cl.id_contract=cr.contract_id','');
            $this->db->join('user u','u.id_user = cr.updated_by','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->where('cr.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cr.status',0);
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['discussion_started']) && $data['discussion_started']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_discussion crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['discussion_updated']) && $data['discussion_updated']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_discussion crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['discussion_closed']) && $data['discussion_closed']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_discussion crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('crd.discussion_status',2);
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['action_item_created']) && $data['action_item_created']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_action_item crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['action_item_updated']) && $data['action_item_updated']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_action_item crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['action_item_closed']) && $data['action_item_closed']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,crd.updated_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('contract_review cr','cl.id_contract=cr.contract_id');
            $this->db->join('contract_review_action_item crd','cr.id_contract_review=crd.contract_review_id','');
            $this->db->join('user u','u.id_user = crd.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('crd.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('crd.status','completed');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['report_created']) && $data['report_created']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,r.created_on as date,u.customer_id,ur.user_role_name');
            $this->db->from('report r');
            $this->db->join('user u','u.id_user = r.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('r.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
        }
        if(isset($data['report_edited']) && $data['report_edited']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,r.updated_on as date,u.customer_id,ur.user_role_name');
            $this->db->from('report r');
            $this->db->join('user u','u.id_user = r.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('r.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
        }
        if(isset($data['report_deleted']) && $data['report_deleted']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,r.updated_on as date,u.customer_id,ur.user_role_name');
            $this->db->from('report r');
            $this->db->join('user u','u.id_user = r.updated_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('r.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('r.report_status',2);
            $this->db->where('cu.id_customer',$data['customer_id']);
        }
        if(isset($data['changes_in_contract']) && $data['changes_in_contract']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cl.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract_log cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('user u','u.id_user = cl.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('cl.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
        }
        if(isset($data['changes_in_contract_status']) && $data['changes_in_contract_status']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cl.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name,cl.contract_status');
            $this->db->from('contract_log cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('user u','u.id_user = cl.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('cl.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cl.is_status_change',1);
            $this->db->where('cu.id_customer',$data['customer_id']);
        }
        if(isset($data['new_contract']) && $data['new_contract']==1){
            $this->db->select('cu.company_name,CONCAT(u.first_name," ",u.last_name) as name,cl.created_on as date,p.provider_name,cl.contract_name,bu.customer_id,bu.bu_name,ur.user_role_name');
            $this->db->from('contract cl');
            $this->db->join('provider p','p.id_provider = cl.provider_name','left');//p.provider_name
            $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
            $this->db->join('user u','u.id_user = cl.created_by','');
            $this->db->join('customer cu','u.customer_id = cu.id_customer','');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('cl.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('cu.id_customer',$data['customer_id']);
            $this->db->where('cl.is_deleted','0');

        }
        if(isset($data['user_create']) && $data['user_create']==1){
            $this->db->select('CONCAT(u.first_name," ",u.last_name) as name,c.company_name,GROUP_CONCAT(bu.bu_name) business_unit,ur.user_role_name,u.created_on,\'User Created\' action');
            $this->db->from('user u');
            $this->db->join('customer c','u.customer_id = c.id_customer','left');
            $this->db->join('business_unit_user buu','buu.user_id=u.id_user and buu.status=1','left');
            $this->db->join('business_unit bu','buu.business_unit_id = bu.id_business_unit','left');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('u.created_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('u.customer_id',$data['customer_id']);
            $this->db->group_by('u.id_user');
        }
        if(isset($data['user_update']) && $data['user_update']==1){
            $this->db->select('CONCAT(u.first_name," ",u.last_name) as name,c.company_name,GROUP_CONCAT(bu.bu_name) business_unit,ur.user_role_name,u.updated_on as created_on,\'User Updated\' action');
            $this->db->from('user u');
            $this->db->join('customer c','u.customer_id = c.id_customer','left');
            $this->db->join('business_unit_user buu','buu.user_id=u.id_user and buu.status=1','left');
            $this->db->join('business_unit bu','buu.business_unit_id = bu.id_business_unit','left');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('u.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('c.id_customer',$data['customer_id']);
            $this->db->group_by('u.id_user');
        }
        if(isset($data['user_delete']) && $data['user_delete']==1){
            $this->db->select('CONCAT(u.first_name," ",u.last_name) as name,c.company_name,GROUP_CONCAT(bu.bu_name) business_unit,ur.user_role_name,u.updated_on as created_on,\'User Deleted\' action');
            $this->db->from('user u');
            $this->db->join('customer c','u.customer_id = c.id_customer','left');
            $this->db->join('business_unit_user buu','buu.user_id=u.id_user and buu.status=1','left');
            $this->db->join('business_unit bu','buu.business_unit_id = bu.id_business_unit','left');
            $this->db->join('user_role ur','u.user_role_id = ur.id_user_role and ur.role_status=1','');
            $this->db->where('u.updated_on between "'.$date1.'" and "'.$date2.'"');
            $this->db->where('u.user_status',0);
            $this->db->where('c.id_customer',$data['customer_id']);
            $this->db->group_by('u.id_user');
        }

        $data_info=$this->db->get();
        //echo '<br>';echo $this->db->last_query();
        return $data_info->result_array();

        /*$this->db->select('bu.customer_id,bu.bu_name,ur.user_role_name');
        $this->db->join('business_unit bu','cl.business_unit_id = bu.id_business_unit','');
        $this->db->join('user u','u.id_user = cl.created_by','');
        $this->db->join('user_role ur','u.user_role_id = ur.id_user_role','');
        if(isset($data['status_change']) && $data['status_change']==1){
            $this->db->from('contract_log cl');
            $this->db->where('cl.is_status_change=1');
            $this->db->where('DATE_FORMAT(cl.created_on,\'%Y-%m-%d\')',$data['date']);
        }
        if(isset($data['changes_in_contract']) && $data['changes_in_contract']==1){
            $this->db->from('contract_log cl');
            $this->db->where('DATE_FORMAT(cl.created_on,\'%Y-%m-%d\')',$data['date']);
        }
        if(isset($data['new_contract']) && $data['new_contract']==1){
            $this->db->from('contract cl');
            $this->db->where('DATE_FORMAT(cl.created_on,\'%Y-%m-%d\')',$data['date']);
        }
        $this->db->group_by('bu.customer_id');

        $adminlist=$this->db->get();


        return array('data'=>$data_info->result_array(),'admin_list'=>$adminlist->result_array());*/

    }
    public function checkReviewUserAccess($data=array()){
        $this->db->select('cu.*');
        $this->db->from('contract_user cu');
        $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        $this->db->where('cu.user_id',$data['id_user']);
        if(isset($data['contract_id']))
            $this->db->where('cu.contract_id',$data['contract_id']);
        if(isset($data['module_id']))
            $this->db->where('cu.module_id',$data['module_id']);
        $this->db->where('cu.status',1);
        $result1 = $this->db->get()->result_array();
        if(isset($data['return_result']))
            return $result1;
        // echo '<pre>'.print_r($data);
        // echo '<pre>'.$this->db->last_query();
        return count($result1);
    }

    public function getPreviousReviewQuestions($prev_review_id){
        return $this->db->select('*')->from('contract_question_review cqr')->join('question q ',' cqr.question_id = q.id_question')->where('cqr.contract_review_id',$prev_review_id)->get()->result_array();
    }
    public function getCurrentReviewQuestions($review_id){
        return $this->db->select('question_id')->from('contract_question_review cqr')->where('cqr.contract_review_id',$review_id)->get()->result_array();
    }

    public function getCurrentReviewQuestion($cur_review_id,$parent_q_id,$q_o_value){
        $this->db->select('q.id_question,qo.id_question_option')->from('module m');
        $this->db->join('topic t ',' m.id_module = t.module_id');
        $this->db->join('question q ',' q.topic_id = t.id_topic');
        $this->db->join('question_option qo ',' qo.question_id = q.id_question');
        $this->db->where('m.contract_review_id',$cur_review_id)->where('q.parent_question_id',$parent_q_id)->where('qo.option_value',$q_o_value);
        $result1 = $this->db->get()->row_array();
        return $result1;
    }

    public function getCurrentReviewQuestion1($cur_review_id,$parent_q_id){
        $this->db->select('q.id_question')->from('module m');
        $this->db->join('topic t ',' m.id_module = t.module_id');
        $this->db->join('question q ',' q.topic_id = t.id_topic');
        $this->db->where('m.contract_review_id',$cur_review_id)->where('q.parent_question_id',$parent_q_id);
        $result1 = $this->db->get()->row_array();
        return $result1;
    }

    public function moduleQuestionCount($module_id,$contract_review_id,$contribution_type,$answer_column){
        if($contribution_type == 2 || $contribution_type == 3){
            $q='select concat(b.answer_questions,"/",a.total_questions) as count from
                                (select COUNT(q.id_question) as total_questions from module m
                                LEFT JOIN topic t on m.id_module=t.module_id
                                LEFT JOIN question q on t.id_topic=q.topic_id
                                where m.id_module=? and q.provider_visibility = 1  and q.question_status=1 and t.topic_status=1) a,
                                (select count(DISTINCT cqr.question_id) as answer_questions from module m
                                LEFT JOIN topic t on m.id_module=t.module_id
                                LEFT JOIN question q on t.id_topic=q.topic_id
                                JOIN contract_question_review cqr on q.id_question=cqr.question_id
                                where m.id_module=? and cqr.question_answer!="" and cqr.contract_review_id=?  and q.provider_visibility = 1 and q.question_status=1 and t.topic_status=1) b';
        }else{
            $q="select concat(b.answer_questions,'/',a.total_questions) as count from
                                (select COUNT(q.id_question) as total_questions from module m
                                LEFT JOIN topic t on m.id_module=t.module_id
                                LEFT JOIN question q on t.id_topic=q.topic_id
                                where m.id_module=? and q.question_status=1 and t.topic_status=1) a,
                                (select count(DISTINCT cqr.question_id) as answer_questions from module m
                                LEFT JOIN topic t on m.id_module=t.module_id
                                LEFT JOIN question q on t.id_topic=q.topic_id
                                JOIN contract_question_review cqr on q.id_question=cqr.question_id
                                where m.id_module=? and cqr.$answer_column!='' and cqr.contract_review_id=? and q.question_status=1 and t.topic_status=1) b";
        }     
        $query = $this->db->query($q,array($module_id,$module_id,$contract_review_id));
        // echo "<pre>";echo $this->db->last_query();echo "</pre>";exit;
        $result = $query->result();
        if(count($result>0)){
            return $result[0]->count;
        }else{
            return '';
        }
    }

    public function getContractName($id){
        $this->db->select('c.contract_name')->from('contract c')->where('c.id_contract',$id)->where('c.is_deleted=0');
        $result = $this->db->get()->result_array();
        if(count($result>0)){
            return $result[0];
        }else{
            return '';
        }

    }

    public function getContractReviewOpenActionItemsList($data)
    {
        $this->db->select('crai.*,CONCAT_WS(\' \',u.first_name,u.last_name) as responsible_user_name,u1.user_role_id');
        $this->db->from('contract_review_action_item crai');
        //$this->db->join('contract_review cr','cr.id_contract_review=crai.contract_review_id','LEFT');
        $this->db->join('contract_question_review cqr','cqr.question_id=crai.question_id','LEFT');
        $this->db->join('user u','u.id_user=crai.responsible_user_id','LEFT');
        $this->db->join('user u1','u1.id_user=crai.created_by');
        if(isset($data['contract_workflow_id']))
            $this->db->where('crai.contract_workflow_id',$data['contract_workflow_id']);
        if(isset($data['id_contract']))
            $this->db->where('crai.contract_id',$data['id_contract']);
        if(isset($data['page_type']) && $data['page_type']='contract_review'){
            if(isset($data['id_module']))
                $this->db->where('crai.module_id IN (select m.id_module from contract_review cr JOIN module m on m.contract_review_id=cr.id_contract_review join module m2 on m2.parent_module_id=m.parent_module_id where cr.contract_id=crai.contract_id and m2.id_module='.$this->db->escape($data['id_module']).')');
            if(isset($data['topic_id']))
                $this->db->where('crai.topic_id IN (select t.id_topic from contract_review cr LEFT JOIN module m on m.contract_review_id=cr.id_contract_review JOIN topic t on t.module_id=m.id_module JOIN topic t2 on t2.parent_topic_id=t.parent_topic_id where cr.contract_id=crai.contract_id and t2.id_topic='.$this->db->escape($data['topic_id']).')');
            if (isset($data['id_contract_review']))
                $this->db->where('cqr.contract_review_id', $data['id_contract_review']);
            $this->db->where('(cqr.question_answer IS NULL OR cqr.question_answer = "")');
        }
        else {
            if (isset($data['id_module']))
                $this->db->where('crai.module_id', $data['id_module']);
            if (isset($data['id_contract_review']))
                $this->db->where('cr.id_contract_review', $data['id_contract_review']);
        }
        if(isset($data['item_status']))
            $this->db->where('crai.item_status',$data['item_status']);
        if(isset($data['action_status']))
            $this->db->where('crai.status','open');
        if(isset($data['contract_id'])) {
            // $this->db->where('cr.contract_id', $data['contract_id']);
            $this->db->where('crai.contract_id', $data['contract_id']);
        }
        if(isset($data['id_user']) && isset($data['user_role_id'])){
            if($data['user_role_id']==5){
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->group_end();
            }
            else if($data['user_role_id']==4 || $data['user_role_id']==3 || $data['user_role_id']==2 || $data['user_role_id']==1 || $data['user_role_id']==6){
                $this->db->group_start();
                $this->db->where('crai.created_by', $data['id_user']);
                $this->db->or_where('u1.user_role_id>=', 2);
                $this->db->or_where('crai.responsible_user_id', $data['id_user']);
                $this->db->group_end();
            }
        }
        if(isset($data['responsible_user_id'])) {
            $this->db->where('crai.responsible_user_id', $data['responsible_user_id']);
        }
        $this->db->where('crai.question_id >0');
        $this->db->group_by('crai.question_id');
        $query = $this->db->get();//echo $this->db->last_query().'<br>';
        $result= $query->result_array();

        foreach ($result as $k => $v) {
            $view_access = 'annus';
            $edit_access = 'annus';
            $delete_access = 'annus';
            $status_change_access = 'annus';
            if(isset($data['id_user']) && isset($data['user_role_id'])) {
                $view_access = "itako";
                if ($data['user_role_id'] == 6 || $data['user_role_id'] == 5) {
                    if ($v['created_by'] == $data['id_user']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user'] || $v['created_by'] == $data['id_user']) {
                        $view_access = "itako";
                    }
                } else if ($data['user_role_id'] == 4 || $data['user_role_id'] == 3 || $data['user_role_id'] == 2 || $data['user_role_id'] == 1) {
                    $view_access = "itako";
                    if ($v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $edit_access = $delete_access = 'itako';
                    }
                    if ($v['responsible_user_id'] == $data['id_user']|| $v['created_by'] == $data['id_user'] || $v['user_role_id'] > $data['user_role_id']) {
                        $view_access = "itako";
                    }
                }
            }
            else{
                $view_access = $edit_access = $delete_access = 'itako';
            }
            //$view_access="itako;
            if($view_access=="itako" && $v['status']!='completed')
                $status_change_access="itako";
            if($v['status']=='completed')
                $edit_access=$delete_access='annus';
            $result[$k]['vaav']=$view_access;
            $result[$k]['eaae']=$edit_access;
            $result[$k]['daad']=$delete_access;
            $result[$k]['scaacs']=$status_change_access;

            $this->db->select('c.*,concat(u.first_name," ",u.last_name) as user_name');
            $this->db->from('contract_review_action_item_log c');
            $this->db->join('user u','c.updated_by=u.id_user','left');
            $this->db->where('c.contract_review_action_item_id', $v['id_contract_review_action_item']);
            $query_log = $this->db->get();
            $result[$k]['comments_log']= $query_log->result_array();
        }
        return $result;
    }

    public function getContractsToBeInitiatedInSixMonths(){
        // SELECT concat(u.first_name,' ',u.last_name) contract_owner_name,cu.company_name,c.* from contract c
        // JOIN user u on c.contract_owner_id = u.id_user
        // JOIN customer cu on cu.id_customer = u.customer_id
        // WHERE CURDATE() = DATE_SUB(c.contract_end_date, INTERVAL 180 day) AND DATEDIFF(c.contract_end_date,c.contract_start_date) >180;

        $this->db->select('concat(u.first_name," ",u.last_name) contract_owner_name,cu.company_name,c.*,cu.id_customer customer_id,DATE_FORMAT(contract_start_date, "%Y-%m-%d") contract_start_date,DATE_FORMAT(contract_end_date, "%Y-%m-%d") contract_end_date');
        $this->db->from('contract c');
        $this->db->join('user u','c.contract_owner_id = u.id_user');
        $this->db->join('customer cu',' cu.id_customer = u.customer_id');
        $this->db->where('CURDATE() = DATE_SUB(c.contract_end_date, INTERVAL 180 day)');
        //$this->db->where('CURDATE() = DATE_SUB(c.contract_end_date, INTERVAL 180 day) #AND DATEDIFF(c.contract_end_date,c.contract_start_date) >180');
        $query = $this->db->get();
        //echo $this->db->last_query().'<br>';
        
        return $query->result_array();
    }

    public function getSecondOpenion($id){
        $result = $this->db->select('*')->from('contract_question_review')->where('question_id',$id)->get();
        $result =  $result->row_array();
        return $result;
        //echo '<pre>'.print_r($result);exit;
    }

    public function getCurrentContractReviewId($where){
        $result = $this->db->select('id_contract_review,contract_review_status,is_workflow')->from('contract_review')->where($where)->order_by('id_contract_review','desc')->limit(1)->get();
        $result =  $result->result_array();
        return $result;
    }

    public function getContractReviewDiscussionByModule($data){
        return $this->db->select('*')->from('contract_review_discussion')->where('module_id',$data['module_id'])->order_by('id_contract_review_discussion','DESC')->get()->result_array();
    }

    public function getDelegateContributors($data){
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6){
            $q = 'SELECT cr.id_contract_review from contract c LEFT JOIN business_unit b ON c.business_unit_id = b.id_business_unit LEFT JOIN contract_review cr on c.id_contract = cr.contract_id AND cr.contract_review_status IN ("review in progress","workflow in progress") WHERE b.customer_id = ? AND cr.id_contract_review IS NOT NULL AND c.is_deleted=0 GROUP BY cr.id_contract_review';
            $query = $this->db->query($q,array($this->session_user_info->customer_id));
        }else{
            $q = 'SELECT cr.id_contract_review from contract c LEFT JOIN contract_review cr on c.id_contract = cr.contract_id AND cr.contract_review_status IN ("review in progress","workflow in progress")  WHERE (c.contract_owner_id = ? OR c.delegate_id = ?) AND cr.id_contract_review IS NOT NULL AND c.is_deleted=0 GROUP BY cr.id_contract_review UNION SELECT cr.id_contract_review from contract_user cu LEFT JOIN contract_review cr on cu.contract_review_id = cr.id_contract_review WHERE cr.contract_review_status IN ("review in progress","workflow in progress")  AND cu.user_id = ?  AND cr.id_contract_review IS NOT NULL';
            $query = $this->db->query($q,array($data["user_id"],$data["user_id"],$data["user_id"]));
        }
        $cr_result = $query->result();
        // echo '<pre>'.
        //echo '<pre>'.print_r($cr_result);exit;
        $contract_review_ids = array();
        foreach($cr_result as $v){
            if((int)$v > 0)
            $contract_review_ids[]=$v->id_contract_review;
        }
        
        $this->db->select('cu.contract_review_id,group_concat(cu.module_id) module_id,cu.user_id,u.provider,concat(u.first_name," ",u.last_name) name,u.contribution_type,u.email,group_concat(bu.bu_name) as bu_name,c.contract_name,c.id_contract,u.user_status,IF( ca.task_type = "main_task" AND u.contribution_type = 3 AND ca.is_workflow=1 AND ca.type="project", 1, 0 ) AS flag,cr.contract_workflow_id as id_contract_workflow,cr.is_workflow,c.type,c.business_unit_id,c.relationship_category_id,t.template_name')->from('contract_user cu');
        $this->db->join('contract c','cu.contract_id = c.id_contract','left');
        $this->db->join('contract_review cr ',' cu.contract_review_id = cr.id_contract_review','left');
        $this->db->join('calender ca ',' cr.calender_id = ca.id_calender','left');
        $this->db->join('business_unit bu','c.business_unit_id = bu.id_business_unit','left');
        $this->db->join('business_unit_user buu','buu.business_unit_id = bu.id_business_unit','left');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('user u','cu.user_id = u.id_user','left');
        $this->db->join('template t','c.template_id=t.id_template','left');
        $this->db->join('contract_workflow cw','cw.id_contract_workflow=cr.contract_workflow_id','left');
        //$this->db->join('user u1','cu.user_id = u1.id_user','left');
        $this->db->where('u.id_user !=',$data['user_id']);
        $this->db->where('c.is_deleted','0');
        // $this->db->where('u1.id_user !=',$data['user_id']);
        $this->db->where_in('cr.contract_review_status',array('review in progress','workflow in progress'));
        $this->db->where('cu.status',1);
        //$this->db->where('cw.status',1);

        if(isset($data['contribution_type']))
            $this->db->where('u.contribution_type',$data['contribution_type']);
        else
            $this->db->where_in('u.contribution_type',array(1,3,0));
        $this->db->where_in('cr.id_contract_review',count($contract_review_ids)>0?$contract_review_ids:array('0'));
        if($data['user_role_id']==6){
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->or_like('bu.bu_name', $data['search'], 'both');
            $this->db->or_like('p.provider_name', $data['search'], 'both');
            $this->db->or_like('u.first_name', $data['search'], 'both');
            $this->db->or_like('u.last_name', $data['search'], 'both');
            $this->db->or_like('u.email', $data['search'], 'both');
            $this->db->or_like('c.contract_name', $data['search'], 'both');
            if(strtolower($data['search']) == 'exp' || strtolower($data['search']) == 'ex' || strtolower($data['search']) == 'expe' || strtolower($data['search']) == 'expert' || strtolower($data['search']) == 'exper')
                $this->db->or_like('u.contribution_type', '0', 'both');
            if(strtolower($data['search']) == 'val' || strtolower($data['search']) == 'vali' || strtolower($data['search']) == 'va' || strtolower($data['search']) == 'validation' || strtolower($data['search']) == 'valid' || strtolower($data['search']) == 'valida' || strtolower($data['search']) == 'validat')
                $this->db->or_like('u.contribution_type', '1', 'both');
            if(strtolower($data['search']) == 're' || strtolower($data['search']) == 'rel' || strtolower($data['search']) == 'rela' || strtolower($data['search']) == 'relat' || strtolower($data['search']) == 'relati' || strtolower($data['search']) == 'relation')
                $this->db->or_like('u.contribution_type', '3', 'both');
            $this->db->group_end();
        }
        $this->db->group_by(array('cu.user_id','cr.id_contract_review'));
        $this->db->having('flag',0);

        $all_records = clone $this->db;
        $compiled_query = $this->db->_compile_select();
        $all_records_count = $all_records->get()->num_rows();

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('u.id_user','DESC');
        $result = $this->db->get();//echo '<pre>'.
        $result =  $result->result_array();

        //select ,user_id
        $this->db->select("count(user_id) contributions,name,contribution_type")->from("($compiled_query) as a");
        $this->db->group_by('user_id');
        $this->db->order_by('count(user_id)','DESC');
        $this->db->limit(3);
        $result1 = $this->db->get();//echo '<pre>'.
        $result1 =  $result1->result_array();
        return array('total_records'=>$all_records_count,'data'=>$result,'top_contributions'=>$result1);

    }

    public function getValidatormodules($data){
        
        //Getting the modules associated to the validators of a contract review.
        
        $this->db->select('cu.*,m.id_module,m.module_status')->from('contract_user cu');
        $this->db->join('user u','cu.user_id = u.id_user');
        $this->db->join('module m','cu.module_id = m.id_module');
        if(isset($data['contract_review_id']))
        $this->db->where('cu.contract_review_id',$data['contract_review_id']);
        if(isset($data['id_module']))
            $this->db->where('cu.module_id',$data['id_module']);
        if(isset($data['check_discussion'])){
            $this->db->join('contract_review_discussion crd','m.id_module = crd.module_id');
            $this->db->where('crd.discussion_status',$data['discussion_status']);
        }
        
        if(isset($data['contribution_type']))
            $this->db->where_in('u.contribution_type',$data['contribution_type']);
        if(isset($data['user_id']))
            $this->db->where('cu.user_id',$data['user_id']);
        if(isset($data['module_id']))
            $this->db->where('cu.module_id',$data['module_id']);
        $this->db->where_in('m.module_status',array(1,2,3));        
        $this->db->where('cu.status',1);
        $this->db->where('u.user_status',1);     
        $result = $this->db->get();//echo '<pre>'.$this->db->last_query();

        
        return  $result->result_array();
    }

    public function getUnAnsweredQuestions($data){
        if(isset($data['dynamic_column']) && $data['dynamic_column'] == 'v_question_answer'){
            $question_answer = 'v_question_answer';
            $question_feedback = 'v_question_feedback';
            $question_option_id = 'v_question_option_id';
        }
        else{
            $question_answer = 'question_answer';
            $question_feedback = 'question_feedback';
            $question_option_id = 'question_option_id';
        }

        // SELECT q.*,ql.* FROM question q
        // JOIN question_language ql on q.id_question = ql.question_id
        // JOIN topic t on q.topic_id = t.id_topic
        // JOIN module m on t.module_id = m.id_module
        // WHERE m.contract_review_id = 87 
        // AND q.id_question NOT IN(SELECT question_id from contract_question_review WHERE contract_review_id = 87 AND CHAR_LENGTH(question_answer)>0)
        $this->db->select('t.id_topic,tl.topic_name,t.topic_score')->from('topic t');
        $this->db->join('topic_language tl','t.id_topic = tl.topic_id','');
        //$this->db->join('module m','t.module_id = m.id_module','');
        $this->db->where('t.module_id',$data['module_id']);
        $this->db->group_by('t.id_topic');
        $this->db->order_by('t.topic_order');

        $topics = $this->db->get();
        // echo '<pre>'.
        $topics = $topics->result_array();
        // var_dump($data);exit;
        // echo '<pre>'.print_r($data);exit;
        $unanswred_questions = array();
        foreach($topics as $k=>$v){
            $unanswred_questions[$k]['id_topic'] = $v['id_topic'];
            $unanswred_questions[$k]['topic_name'] = $v['topic_name'];
            $unanswred_questions[$k]['topic_score'] = $v['topic_score'];

            //$this->db->select("topic_score,q.id_question,q.question_type,ql.question_text,m.contract_review_id,t.id_topic,t.module_id,cqr.second_opinion,(SELECT concat(if(cqr.".$question_option_id." is NULL,".$question_answer.",qol.option_name),'###',cqr.".$question_feedback.") question_answer from contract_question_review cqr LEFT JOIN question_option_language qol on cqr.".$question_option_id." = qol.question_option_id WHERE question_id=q.id_question GROUP BY question_id ) question_answer")->from('question q');
            $this->db->select("topic_score, q.id_question, q.question_type, ql.question_text, m.contract_review_id, t.id_topic, t.module_id, cqr.second_opinion,cqr.external_user_question_feedback, (SELECT concat(if(if(cqr.question_option_id is NULL, question_answer, qol.option_name) is null ,'',if(cqr.question_option_id is NULL, question_answer, qol.option_name)), '###', cqr.question_feedback) question_answer from contract_question_review cqr LEFT JOIN question_option_language qol on cqr.question_option_id = qol.question_option_id WHERE question_id=q.id_question GROUP BY question_id ) question_answer, (SELECT concat(if(if(cqr.v_question_option_id is NULL, v_question_answer, qol.option_name) is null ,'',if(cqr.v_question_option_id is NULL, v_question_answer, qol.option_name)), '###', cqr.v_question_feedback) question_answer from contract_question_review cqr LEFT JOIN question_option_language qol on cqr.v_question_option_id = qol.question_option_id WHERE question_id=q.id_question GROUP BY question_id ) v_question_answer, provider_visibility")->from('question q');
            $this->db->join('contract_question_review cqr','q.id_question = cqr.question_id','left');
            $this->db->join('question_language ql','q.id_question = ql.question_id','left');
            $this->db->join('topic t','q.topic_id = t.id_topic','left');
            $this->db->join('module m','t.module_id = m.id_module','left');
            $this->db->where('q.topic_id',$v['id_topic']);
            $this->db->where('q.question_status',1);
            $this->db->where('t.topic_status',1);
            if(isset($data['provider_questions']))
                $this->db->where('provider_visibility',1);
            if(!($data['all_questions'] == 'true'))
                $this->db->where("q.id_question NOT IN(SELECT question_id from contract_question_review WHERE contract_review_id = ".$data['contract_review_id']." AND CHAR_LENGTH(".$question_answer.")>0)");
            $this->db->group_by('q.id_question');
            $result = $this->db->get();
            //echo $this->db->last_query();
            //  echo '<pre>'.
            $unanswred_questions[$k]['questions'] = $result->result_array();
            //Milesone2 starts
            if($data['all_questions'] != 'false'){
                foreach($unanswred_questions[$k]['questions'] as $k1 => $v1){
                    // echo '<pre>'.print_r($v1);
                    $this->db->select('d.*,concat(u.first_name," ",u.last_name) as uploaded_by');
                    $this->db->from('document d');
                    $this->db->join('user u','u.id_user = d.uploaded_by','left');
                    $this->db->where('d.reference_id IN (select q_sub.id_question from question q_sub LEFT JOIN question q2_sub on q2_sub.parent_question_id=q_sub.parent_question_id LEFT JOIN topic t2_sub on t2_sub.id_topic=q2_sub.topic_id LEFT JOIN module m2_sub on m2_sub.id_module=t2_sub.module_id LEFT JOIN contract_review cr2_sub on cr2_sub.id_contract_review=m2_sub.contract_review_id
                    LEFT JOIN contract c2_sub on c2_sub.id_contract=cr2_sub.contract_id and c2_sub.is_deleted=0 LEFT JOIN topic t1_sub on t1_sub.id_topic=q_sub.topic_id LEFT JOIN module m1_sub on m1_sub.id_module=t1_sub.module_id LEFT JOIN contract_review cr1_sub on cr1_sub.id_contract_review=m1_sub.contract_review_id where q2_sub.id_question='.$v1['id_question'].' and `cr1_sub`.`contract_id` = `cr2_sub`.`contract_id`)',false,false);
                    $this->db->where('d.reference_type','question');
                    if(isset($data['contract_review_id'])){
                        $this->db->where('d.module_type','contract_review');
                        $this->db->where('d.module_id',$data['contract_review_id']);
                    }
                    $this->db->where('d.document_status',1);
                    $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
                    $attachment=$query->result_array();
        
                    $unanswred_questions[$k]['questions'][$k1]['attachments'] = $attachment;
                }
            }
            //Milesone2 ends
        }
// echo '<pre>'.print_r($unanswred_questions);exit;
        return $unanswred_questions;
        
    }

    public function getUserContractsByBusinessUnitArray($data){
        $this->db->select('c.*')->from('contract c');
        $this->db->where_in('business_unit_id',count($data['bu_array'])>0?$data['bu_array']:array(0));
        $this->db->where('is_deleted',0);
        $this->db->group_start();
        $this->db->where('contract_owner_id',$data['user_id']);
        $this->db->or_where('contract_owner_id',$data['user_id']);
        $this->db->group_end('delegate_id',$data['user_id']);
        $contract_result = $this->db->get();

        $this->db->select('c.*')->from('contract c');
        $this->db->join('contract_user cu','c.id_contract = cu.contract_id','');
        $this->db->where('cu.user_id',$data['user_id']);
        $this->db->where('cu.status',1);
        $this->db->where('c.is_deleted',0);
        $this->db->where_in('c.business_unit_id',count($data['bu_array'])>0?$data['bu_array']:array(0));
        $contributor_result = $this->db->get();//echo '<pre>'.

        
        return array_merge($contract_result->result_array(),$contributor_result->result_array());

    }

    public function spentcount($data)
    {
        $this->db->select('*');
        $this->db->select('COUNT(contract_id) AS count');
        $this->db->from('spent_lines'); 
        $this->db->where('status',$data['status']);
        $this->db->group_by('contract_id');
        $this->db->order_by('count', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }
    public function spentcount1($data)
    {
        $this->db->select('*'); 
        $this->db->from('spent_lines s'); 
        $this->db->where('s.contract_id',$data['contract_id']);
        $this->db->where('s.status',$data['status']); 
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result();
    }

    public function contributionsResult($data)
    {
        $this->db->select('p.provider_name ,rcl.relationship_category_name,c.id_contract,c.contract_name,c.contract_start_date,c.contract_end_date,
        rcl.relationship_category_name,bu.bu_name,GROUP_CONCAT(m.module_name) module_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
        $this->db->join('module_language m', 'm.module_id=cur.module_id', '');
        $this->db->where('cur.user_id',$data['id_user']);
        $this->db->where('cur.status','1');
        $this->db->where('c.contract_status','review in progress');
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name');
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        $all_clients_count = count($query->result_array());

        $this->db->select('p.provider_name ,rcl.relationship_category_name,c.id_contract,c.contract_name,c.contract_start_date,c.contract_end_date,
        rcl.relationship_category_name,bu.bu_name,GROUP_CONCAT(m.module_name) module_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
        $this->db->join('module_language m', 'm.module_id=cur.module_id', '');
        $this->db->where('cur.user_id',$data['id_user']);
        $this->db->where('cur.status','1');
        $this->db->where('c.contract_status','review in progress');
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        
        $this->db->where('c.can_review',1);
        if(isset($data['deleted'])){

        }
        else
        $this->db->where('c.is_deleted','0');
        $this->db->group_by('c.id_contract');
        $this->db->order_by('c.contract_name');
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('p.provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('p.provider_name,c.contract_name','asc');

        $query2 = $this->db->get();
        //echo '<pre>'.$this->db->last_query(); exit;
        //return  $result->result_array();
        return array('total_records' => $all_clients_count,'data' => $query2->result_array());

    }
    public function contractsResult($data)
    {

        $this->db->select('p.provider_name ,rcl.relationship_category_name,c.id_contract,c.contract_name,c.contract_start_date,c.contract_end_date,bu.bu_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        $this->db->where('c.is_deleted','0'); 
        $this->db->where('(contract_owner_id = '.$data['id_user'].' OR delegate_id = '.$data['id_user'].')'); 
        $this->db->group_by('c.id_contract');    
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        //count($query->result_array());
        
        $all_clients_count = count($query->result_array());
        //print_r($all_clients_count);
  

        $this->db->select('p.provider_name ,rcl.relationship_category_name,c.id_contract,c.contract_name,c.contract_start_date,c.contract_end_date,bu.bu_name');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        $this->db->where('c.is_deleted','0');
        $this->db->where('(contract_owner_id = '.$data['id_user'].' OR delegate_id = '.$data['id_user'].')'); 
        $this->db->group_by('c.id_contract');
       // print_r($data);
        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('p.provider_name',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('crv.updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }        
        else
            $this->db->order_by('p.provider_name,c.contract_name','asc');
            
        $query2 = $this->db->get();
        //echo '<pre>'.$this->db->last_query(); exit;
       //return  $query->result_array();
        return array('total_records' => $all_clients_count,'data' => $query2->result_array());

    }

    public function getStoredModules($data){
        // SELECT ml.module_name,CONCAT(u.first_name,' ',u.last_name) updated_by,sm.created_on,sm.id_stored_module
        // from module m
        // JOIN module_language ml on m.id_module = ml.module_id
        // JOIN user u ON u.id_user = m.updated_by
        // JOIN stored_modules sm on m.parent_module_id = sm.parent_module_id
        // WHERE contract_review_id in (SELECT id_contract_review from contract_review WHERE contract_id = 114) GROUP BY m.parent_module_id

        $this->db->select('ml.module_name,sm.next_plan date,sm.module_id id_module,CONCAT(u.first_name," ",u.last_name) updated_by,sm.created_on,sm.id_stored_module,sm.activate_in_next_review,sm.is_copied_from_project,sm.contract_workflow_id as storeModuleContractWorkflowID');
        if($data['is_workflow']>0)
        $this->db->select('cw.id_contract_workflow');
        $this->db->from('module m');
        $this->db->join('stored_modules sm','m.parent_module_id = sm.parent_module_id');
        $this->db->join('module_language ml','sm.module_id=ml.module_id','left');
        $this->db->join('user u ',' u.id_user = sm.updated_by');
        $this->db->join('contract_review cr','m.contract_review_id=cr.id_contract_review');
        if($data['is_workflow']>0)
        $this->db->join('contract_workflow cw','cr.contract_workflow_id=cw.id_contract_workflow');
        $this->db->where('sm.status',1);        
        $this->db->where('sm.is_workflow',$data['is_workflow']);
        $this->db->where('sm.contract_id',$data['contract_id']);
        $this->db->where('sm.module_id is NOT null',null,false);
        $this->db->group_by('id_stored_module');

        $result = $this->db->get();
        // echo '<pre>'.
        return $result->result_array();
    }
    public function exportList($data)
    { 
        $this->db->select('c.*,CONCAT(u1.first_name," ",u1.last_name) bu_owner,CONCAT(u2.first_name," ",u2.last_name) bu_delegate,c.provider_name provider_id,c.id_contract contract_id,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,p.provider_name,cu.currency_name,rc.classification_name,rcl.relationship_category_name,max(`crv`.`id_contract_review`) as id_contract_review,IF(IFNULL(c.parent_contract_id,0)>0,\'sub_agreement\',IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0,\'parent_agreement\',\'agreement\')) as agreement_type,t.template_name,"Review" as review_type');
        $this->db->from('contract c');
        $this->db->join('provider p','p.id_provider = c.provider_name','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('contract_review crv','crv.contract_id=c.id_contract','left');
        $this->db->join('relationship_classification_language rc','rc.relationship_classification_id=c.classification_id','left'); 
        $this->db->join('user u1','c.contract_owner_id=u1.id_user','left');
        $this->db->join('user u2','c.delegate_id=u2.id_user','left');
        $this->db->join('template t','c.template_id = t.id_template','left');

        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if(isset($data['type_of']))
            $this->db->where('c.type',$data['type_of']);    
        if(isset($data['is_read_only'])&& ($data['is_read_only'] == true))
        {
            $this->db->where_in('c.business_unit_id',$data['business_unit_id']);
        }      
        $this->db->where('c.is_deleted','0');
        // $this->db->where('c.can_review','1');
        $this->db->group_by('c.id_contract');

        $this->db->order_by('p.provider_name,c.contract_name','asc');
        $query = $this->db->get();
        //echo 
        return array('data' => $query->result_array());
    }

    public function getcontractworkflow($data){
        
        $this->db->select("1 as is_workflow,cw.workflow_status,`cw`.`workflow_name` as `review_name`, `c`.`id_contract`, cw.id_contract_workflow as id_contract_workflow,(select MAX(id_contract_review) from contract_review where contract_workflow_id = cw.id_contract_workflow) id_contract_review,crv.validation_status,c.business_unit_id");
        //,
        $this->db->from("contract c");
        $this->db->join("provider p","`p`.`id_provider` = `c`.`provider_name`","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join("`currency` `cu`","`c`.`currency_id`=`cu`.`id_currency`","left");
        $this->db->join("`relationship_category_language` `rcl`","`c`.`relationship_category_id`=`rcl`.`relationship_category_id` and `language_id`=1","left");
        //$this->db->join("`contract_review` `crv`", "`crv`.`contract_id`=`c`.`id_contract`","left");
        $this->db->join("`relationship_classification_language` `rc`","`rc`.`relationship_classification_id`=`c`.`classification_id`","left");
        $this->db->join("`user` `u1`","`c`.`contract_owner_id`=`u1`.`id_user`","left");
        $this->db->join("`user` `u2`","`c`.`delegate_id`=`u2`.`id_user`","left");
        $this->db->join("`contract_workflow` `cw`","`c`.`id_contract`=`cw`.`contract_id`","left");
        $this->db->join("`contract_review` `crv`","`crv`.`contract_workflow_id`=`cw`.`id_contract_workflow`","left");
        $this->db->where("c.id_contract",$data['contract_id']);
        if(isset($data['id_contract_workflow']) && count($data['id_contract_workflow']) > 0)
            $this->db->where_not_in("cw.id_contract_workflow",$data['id_contract_workflow']);
        if(isset($data['contract_review_status_not']) && $data['contract_review_status_not'] == '')
            $this->db->where("crv.contract_review_status != 'finished'");
        $this->db->where("cw.status","1");
        // if(isset($data['id_contract_workflow']))
                $this->db->group_by('cw.id_contract_workflow');  

        $query = $this->db->get();
        
        return $query->result_array();
    }

    public function getInfoContractTags($data){
        $this->db->select('ct.id_contract_tag,tl.tag_text,t.id_tag,t.tag_type,t.field_type,ct.tag_option,IF(ct.tag_option=0,ct.tag_option_value,ct.tag_option) tag_answer,t.tag_order');
        $this->db->from('contract_tags ct');
        $this->db->join('tag t','t.id_tag = ct.tag_id');
        $this->db->join('tag_language tl','t.id_tag = tl.tag_id');
        if(isset($data['contract_id']))
            $this->db->where('ct.contract_id',$data['contract_id']);
        $this->db->where('ct.status',1);
        $this->db->where('t.status',1);
        $this->db->group_by('t.id_tag');  
        // $this->db->order_by('t.tag_order');
        $this->db->order_by('t.id_tag','asc');
        $query = $this->db->get();
        return $query->result_array();
    }
    function getAllContractList($data){

        /**
         * Start 
         * select query for all contract 
         */
        $this->db->select('0 as is_workflow, (SELECT SUM(spent_amount) FROM spent_lines WHERE contract_id=c.id_contract AND status=1) spent_amount, c.contract_value as Projected_value,`c`.*, CONCAT(u1.first_name, " ", u1.last_name) bu_owner, CONCAT(u2.first_name, " ", u2.last_name) bu_delegate, `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`, `p`.`provider_name` as providerName, `cu`.`currency_name`, `rc`.`classification_name`, `rcl`.`relationship_category_name`, (select MAX(id_contract_review) from contract_review where contract_id = c.id_contract and is_workflow = 0) id_contract_review, IF(IFNULL(c.parent_contract_id, 0)>0, "sub_agreement", IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0, "parent_agreement", "agreement")) as agreement_type, "0" as id_contract_workflow,crv.contract_review_status,crv.updated_on as review_updated_on,crv.validation_status,TIMESTAMPDIFF(MONTH, `contract_start_date`, contract_end_date) months,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name,CASE WHEN c.parent_contract_id >0 THEN "sub" WHEN (select count(*)  from contract where parent_contract_id=c.id_contract AND is_deleted=0)>0 Then "parent" WHEN (select count(*)  from contract where parent_contract_id=c.id_contract AND is_deleted=0)=0 THEN "single" END AS hierarchy ,IF((c.parent_contract_id)>0,(select contract.contract_name  from contract where id_contract=c.parent_contract_id),"") as parent_contract_name,IFNULL((SELECT euro_equivalent_value  FROM currency  WHERE currency_name=cu.currency_name AND customer_id=bu.customer_id AND is_deleted=0),0) as euro_equivalent_value');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6 || $this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("`contract` `c`");
        $this->db->join("provider p","p.id_provider=c.provider_name","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join("`currency` `cu`","`c`.`currency_id`=`cu`.`id_currency`","left");
        $this->db->join("`relationship_category_language` `rcl`","`c`.`relationship_category_id`=`rcl`.`relationship_category_id` and `language_id`=1","left");
        $this->db->join("`contract_review` `crv`"," `crv`.`contract_id`=`c`.`id_contract`","left");
        $this->db->join("`relationship_classification_language` `rc` "," `rc`.`relationship_classification_id`=`c`.`classification_id`","left");
        $this->db->join("`user` `u1` "," `c`.`contract_owner_id`=`u1`.`id_user`","left");
        $this->db->join("`user` `u2` "," `c`.`delegate_id`=`u2`.`id_user`","left");
        $this->db->join("`template` `t` "," `c`.`template_id`=`t`.`id_template`","left");
        $this->db->join("spent_lines sl","c.id_contract=sl.contract_id and  sl.status=1","left");
        if($data['type'] == 'project')
        {
            $this->db->join("event_feeds ef","c.id_contract = ef.reference_id and ef.reference_type = 'project' and ef.status=1","left");
        }
        else
        {
            $this->db->join("event_feeds ef","c.id_contract = ef.reference_id and ef.reference_type = 'contract' and ef.status=1","left");
        }
        if(!empty($data['adv_filters']) && is_numeric(array_search('or', array_column($data['adv_filters'], 'table_alias'))))
        {
            $this->db->join("obligations_and_rights or","c.id_contract=or.contract_id and or.status =1","left");
            $this->db->where('or.parent_obligation_id',null); 
        }
        if(!empty($data['adv_filters']) && is_numeric(array_search('sc', array_column($data['adv_filters'], 'table_alias'))))
        {
            $this->db->join("service_catalogue sc","c.id_contract=sc.contract_id and sc.status =1","left");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) ){
            
            if($data['type'] == 'project')
            {
                $this->db->join("document d","c.id_contract=d.reference_id AND d.reference_type = 'project' AND d.module_type = 'project' AND d.document_status = 1","left");
            }
            else
            {
                $this->db->join("document d","c.id_contract=d.reference_id AND d.reference_type = 'contract' AND d.module_type = 'contract_review' AND d.document_status = 1","left");
            }
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("GROUP_CONCAT(d.document_name) as document_names");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("GROUP_CONCAT(d.document_source) as document_urls");
        }

        //event feed attachments
        $Ctype = $data['type'];
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('event_feed_document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(document.document_name) from document WHERE document.reference_type ='event_feed' and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = c.id_contract and event_feeds.reference_type = '$Ctype' and event_feeds.status =1)) as event_feed_document_names");
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('event_feed_document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(select GROUP_CONCAT(document.document_source) from document WHERE document.reference_type ='event_feed' and document.document_type = 1 and document.reference_id IN (select id_event_feed from event_feeds where event_feeds.reference_id = c.id_contract and event_feeds.reference_type = '$Ctype' and event_feeds.status =1)) as event_feed_document_urls");
        }

        //search option 
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('c.contract_unique_id', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                $this->db->or_like('c.contract_active_status',$data['search'],'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }//end if search
        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if(isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        // if(isset($data['contract_status']) && is_array($data['contract_status']))
        //     $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['relationship_category_id']) && $data['relationship_category_id']>0)
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['end_date_lessthan_180']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 180 DAY)');
        if(isset($data['created_this_month']))
            $this->db->where('MONTH(c.created_on) = MONTH(CURDATE()) AND YEAR(c.created_on) = YEAR(CURDATE())');
        if(isset($data['ending_this_month']))
            $this->db->where('MONTH(c.contract_end_date) = MONTH(CURDATE()) AND YEAR(c.contract_end_date) = YEAR(CURDATE())');
        if(isset($data['automatic_prolongation']))
            $this->db->where('c.auto_renewal','1');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        // if(isset($data['reviewable_contracts']))
        //         $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        else if(isset($data['get_all_records'])){

        } else
            $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        if(isset($data['contract_active_status']))
        {
            $this->db->where('c.contract_active_status',$data['contract_active_status']);
        }
        // ended conditions
        //////////advanced filters start ///////////////
        foreach($data['adv_filters'] as $filter){
            if( $filter['domain'] == "Contract Tags" )
            {
                $tagId = $filter['master_domain_field_id'];
                $condition = $filter['condition'];
                $value = $filter['value'];
                if($filter['field_type']=='drop_down')
                {
                    $this->db->group_start();
                    foreach(explode(",",$value) as $tagOptionValue)
                    {
                        $this->db->or_where("EXISTS(SELECT tag_id FROM  contract_tags WHERE contract_tags.contract_id = c.id_contract AND contract_tags.status=1 and contract_tags.tag_id =  $tagId  AND FIND_IN_SET($tagOptionValue, contract_tags.tag_option))");
                    }
                    $this->db->group_end();
                }
                elseif($filter['field_type']=='date')
                {
                    $this->db->where("EXISTS(SELECT tag_id FROM  contract_tags WHERE contract_tags.contract_id = c.id_contract AND contract_tags.status=1 and contract_tags.tag_id =  $tagId  AND DATE(contract_tags.tag_option_value) $condition  '$value')");
                }
                elseif(($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'))
                {
                   if($filter['condition'] == 'like')
                   {
                        $this->db->where("EXISTS(SELECT tag_id FROM  contract_tags WHERE contract_tags.contract_id = c.id_contract AND contract_tags.status=1 and contract_tags.tag_id =  $tagId  AND contract_tags.tag_option_value LIKE '%$value%' ESCAPE '!')");
                   }
                   else
                   {
                        $this->db->where("EXISTS(SELECT tag_id FROM  contract_tags WHERE contract_tags.contract_id = c.id_contract AND contract_tags.status=1 and contract_tags.tag_id =  $tagId  AND contract_tags.tag_option_value $condition  '$value')");
                   }   
                }
            }
            else
            {
                if($filter['field_type']=='drop_down'){
                    if($filter['database_field']=='sc.business_unit_id')
                    {
                        $databasefield = $filter['database_field'];
                        $this->db->group_start();
                        foreach(explode(',',$filter['value']) as $fieldValue)
                        {
                            $this->db->or_where("FIND_IN_SET($fieldValue,$databasefield) > 0");
                        }
                        $this->db->group_end();
                    }
                    else
                    {
                        $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                    }
                }
                elseif($filter['field_type']=='date'){
                    $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
                }
                // elseif($filter['field_type']=='free_text'){
                //     if($filter['condition']=='like'){
                //         $this->db->like($filter['database_field'],$filter['value'],'both');
                //     }
                //     if($filter['condition']=='free_text' || $filter['condition']=='='){
                //         $this->db->where($filter['database_field'],$filter['value']);
                //     }
                // }
                elseif($filter['field_type']=='numeric_text'||$filter['field_type']=='free_text'){
                    if($filter['condition']=='like'){
                        $this->db->like($filter['database_field'],$filter['value'],'both');
                    }
                    elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                        $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                    }
                }
            }
        }
        //////////advanced filters end ///////////////
        $this->db->group_by('c.id_contract');

        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        $this->db->select("*")->from("($new_query) as unionTable");
        if(isset($data['project_status']) && $data['type']=='project'){
            $this->db->where('project_status',$data['project_status']);
        }
        // print_r($data['user_role_id']);exit;
        //Can_access filters the records user have access to
        if(isset($data['can_access']) && $data['can_access'] > 0 &&  $data['user_role_id']!=8)
            $this->db->where('can_access',$data['can_access']);
        if(isset($data['type']) && $data['type'] !=''){
            $this->db->where('type',$data['type']);
        }
        else{
            $this->db->where('type','contract');
        }
        // if(isset($data['hierarchy']) && $data['hierarchy'] !=''){
        //     $this->db->where('hierarchy',$data['hierarchy']);
        // }
        // print_r($data['adv_union_filters']);exit;
        foreach($data['adv_union_filters'] as $Unionfilter){
            if($Unionfilter['field_type']=='drop_down'){
                $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
            }
            elseif($Unionfilter['field_type']=='date'){
                $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
            }
            // elseif($Unionfilter['field_type']=='free_text'){
            //     if($Unionfilter['condition']=='like'){
            //         $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
            //     }
            //     if($Unionfilter['condition']=='free_text' || $filter['condition']=='='){
            //         if($Unionfilter['database_field']!='document_names' && $Unionfilter['database_field']!='document_urls'){
            //             $this->db->where($Unionfilter['database_field'],$Unionfilter['value']);
            //         }
            //     }
            // } 
            elseif($Unionfilter['field_type']=='numeric_text'||$Unionfilter['field_type']=='free_text'){
                if($Unionfilter['condition']=='like'){
                    $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
                }
                elseif($Unionfilter['condition']=='<' || $Unionfilter['condition']=='>'|| $Unionfilter['condition']=='=' ){
                    $this->db->where($Unionfilter['database_field']." ".$Unionfilter['condition'],$Unionfilter['value']);
                }
            }
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();
        //  echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
        $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('providerName',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('review_updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else
            $this->db->order_by('providerName,contract_name','asc');
        
        $query = $this->db->get();
        //echo $count_result_db->last_query();exit; 
        return array('total_records' => $count_result,'data' => $query->result_array());
    }

    public function getArchive($data){
          //print_r($data);
          //$data['date_field'] = 'cr.updated_on';
          //print_r($data);
        $this->db->select('cr.contract_id,cr.id_contract_review,p.provider_name AS providerName,c.type,c.contract_name,rcl.relationship_category_name,cr.is_workflow,cr.updated_on ReviewDate,IF(cr.is_workflow=0,t1.template_name,t2.template_name) AS templateName,CONCAT(u1.first_name," ",u1.last_name) owner_name,CONCAT(u2.first_name," ",u2.last_name) delegate_name,cr.review_score,IF(cr.is_workflow=1,(SELECT id_module FROM module m WHERE m.contract_review_id=cr.id_contract_review LIMIT 1),0) module_id,IF(ctry.country_name!="",CONCAT(b.bu_name," - ",ctry.country_name),b.bu_name) as bu_name,IF(cw.parent_id>0 && c.type="project", 0, 1) as is_archive,cr.contract_workflow_id,,IF(cr.is_workflow=0,"review","task") AS typeOfActivity,CONCAT(u3.first_name, " ", u3.last_name) as submited_by,(SELECT GROUP_CONCAT(ml.module_name) FROM module m  LEFT JOIN module_language ml on m.id_module=ml.module_id WHERE m.contract_review_id=cr.id_contract_review)as module_names');
        $this->db->from('contract_review cr');
        $this->db->join('contract c', 'c.id_contract = cr.contract_id','left');
        $this->db->join('business_unit b', 'c.business_unit_id = b.id_business_unit','left');
        $this->db->join('country ctry','b.country_id=ctry.id_country','left');
        $this->db->join('relationship_category_language rcl','c.relationship_category_id=rcl.relationship_category_id and language_id=1','left');
        $this->db->join('provider p', 'p.id_provider = c.provider_name','left');
        $this->db->join('contract_workflow cw', 'cr.contract_workflow_id = cw.id_contract_workflow','left');
        $this->db->join('template t1', 'c.template_id = t1.id_template','left');
        $this->db->join('template t2', 'cw.workflow_id = t2.id_template','left');
        $this->db->join('user u1', 'u1.id_user = cr.contract_owner_id','left');
        $this->db->join('user u2', 'u2.id_user = cr.contract_delegate_id','left');
        $this->db->join('user u3', 'u3.id_user = cr.updated_by','left');
          // if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field'))) || is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field'))) )
        // {
        //     $this->db->join("document d","c.id_contract=d.reference_id AND d.reference_type = 'project' AND d.module_type = 'project' AND d.document_status = 1","left");
        //     $this->db->join("document dc","c.id_contract=dc.reference_id AND dc.reference_type = 'contract' AND dc.module_type = 'contract_review' AND dc.document_status = 1","left");
        // }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_names', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(SELECT GROUP_CONCAT(d.document_name)  from document d WHERE (`c`.`id_contract`=`d`.`reference_id` AND `d`.`reference_type` = 'project' AND `d`.`module_type` = 'project' AND `d`.`document_status` = 1) or (`c`.`id_contract`=`d`.`reference_id` AND `d`.`reference_type` = 'contract' AND `d`.`module_type` = 'contract_review' AND `d`.`document_status` = 1)) as document_names");
            
        }
        if(!empty($data['adv_union_filters']) && is_numeric(array_search('document_urls', array_column($data['adv_union_filters'], 'database_field')))){
            $this->db->select("(SELECT GROUP_CONCAT(d.document_source)  from document d WHERE (`c`.`id_contract`=`d`.`reference_id` AND `d`.`reference_type` = 'project' AND `d`.`module_type` = 'project' AND `d`.`document_status` = 1) or (`c`.`id_contract`=`d`.`reference_id` AND `d`.`reference_type` = 'contract' AND `d`.`module_type` = 'contract_review' AND `d`.`document_status` = 1)) as document_sources");
        }
        $this->db->where('cr.contract_review_status','finished');
        $this->db->where('c.is_deleted',0);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('b.customer_id',$data['customer_id']);
        if(isset($data['activity_type']) && $data['activity_type']=='1')
           $this->db->where('c.type','project');
        if(isset($data['activity_type']) && $data['activity_type']=='0')
             $this->db->where('c.type','contract');
        if(isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['provider_name']))
            $this->db->where('p.provider_name',$data['provider_name']);

            if(!in_array($this->session_user_info->user_role_id,array(2,6))){
                $this->db->group_start();
                if(!empty($data['contract_user'])){
                    $this->db->select('GROUP_CONCAT(module_id) modules');
                    $this->db->join('contract_user cu','cr.id_contract_review = cu.contract_review_id AND cu.status=1','left');
                    if(isset($data['contract_owner_id']))
                        $this->db->or_where('cr.contract_owner_id',$data['contract_owner_id']);
                    if(isset($data['delegate_id']))
                        $this->db->or_where('cr.contract_delegate_id',$data['delegate_id']);
                    $this->db->or_where('cu.user_id',$data['contract_user']);
                }
                else{
                    if(isset($data['contract_owner_id']))
                        $this->db->or_where('cr.contract_owner_id',$data['contract_owner_id']);
                    if(isset($data['delegate_id']))
                        $this->db->or_where('cr.contract_delegate_id',$data['delegate_id']);
                }
                if($this->session_user_info->user_role_id==8 && isset($data['managerBU']))
                {
                    $this->db->or_where_in('c.business_unit_id',$data['managerBU']);
                }
                $this->db->group_end();
            }
        // if(isset($data['contract_user'])){
        //     $this->db->select('GROUP_CONCAT(module_id) modules');
        //     $this->db->join('contract_user cu','cr.id_contract_review = cu.contract_review_id AND cu.status=1','left');
        //     $this->db->group_start();
        //     if(isset($data['contract_owner_id']))
        //         $this->db->or_where('cr.contract_owner_id',$data['contract_owner_id']);
        //     if(isset($data['delegate_id']))
        //         $this->db->or_where('cr.contract_delegate_id',$data['delegate_id']);
        //     $this->db->or_where('cu.user_id',$data['contract_user']);
        //     $this->db->group_end();
        // }else{
        //     if(isset($data['contract_owner_id']))
        //         $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        //     if(isset($data['delegate_id']))
        //         $this->db->where('c.delegate_id',$data['delegate_id']);
        // }
        if(isset($data['id_relationship_category']))
            $this->db->where('c.relationship_category_id',$data['id_relationship_category']);
        if(isset($data['relationship_category_id']))
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['activity_topic']))
            $this->db->where('cr.is_workflow',$data['activity_topic']);
        // if(isset($data['search'])){
        //     $this->db->group_start();
        //     $this->db->like('c.contract_name', $data['search'], 'both');
        //     $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
        //     $this->db->or_like('p.provider_name', $data['search'], 'both');
        //     $this->db->or_like('b.bu_name', $data['search'], 'both');
        //     $this->db->or_like('t1.template_name', $data['search'], 'both');
        //     $this->db->or_like('t2.template_name', $data['search'], 'both');
        //     $this->db->or_like('u1.first_name', $data['search'], 'both');
        //     $this->db->or_like('u2.first_name', $data['search'], 'both');
        //     $this->db->or_like('u1.last_name', $data['search'], 'both');
        //     $this->db->or_like('u2.last_name', $data['search'], 'both');
        //     $this->db->group_end();
        // }
        // echo 'DATE('.$data["date_field"].')'.$data["date_period"].'"'.$data["created_on"].'"';exit;
        if(isset($data['date_field']) && isset($data['updated_on']) && strtotime($data["updated_on"]) > 0)
        {
            $this->db->where('DATE('.$data["date_field"].')'.$data["date_period"].'"'.$data["updated_on"].'"');
        }
              //////////advanced filters start ///////////////
              foreach($data['adv_filters'] as $filter){
                if($filter['field_type']=='drop_down'){
                    if($filter['database_field']=='sc.business_unit_id')
                    {
                        $databasefield = $filter['database_field'];
                        $this->db->group_start();
                        foreach(explode(',',$filter['value']) as $fieldValue)
                        {
                            $this->db->or_where("FIND_IN_SET($fieldValue,$databasefield) > 0");
                        }
                        $this->db->group_end();
                    }
                    else
                    {
                        $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                    }
                }
                elseif($filter['field_type']=='date'){
                    $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
                }
                elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
                    if($filter['condition']=='like'){
                        $this->db->like($filter['database_field'],$filter['value'],'both');
                    }
                    elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                        $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                    }
                }
            }
            //////////advanced filters end ///////////////
        $this->db->group_by('id_contract_review');
            // $query = $this->db->get();
            // echo 
        $query2 = $this->db->get_compiled_select();
        $this->db->_reset_select();
        $this->db->select("*")->from("($query2) as unionTable");//
        $this->db->where('is_archive',1);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('contract_name', $data['search'], 'both');
            $this->db->or_like('relationship_category_name', $data['search'], 'both');
            $this->db->or_like('providerName', $data['search'], 'both');
            $this->db->or_like('bu_name', $data['search'], 'both');
            $this->db->or_like('templateName', $data['search'], 'both');
            $this->db->or_like('owner_name', $data['search'], 'both');
            $this->db->or_like('delegate_name', $data['search'], 'both');
            $this->db->or_like('typeOfActivity', $data['search'], 'both');
            $this->db->group_end();
        }
        foreach($data['adv_union_filters'] as $Unionfilter){
            if($Unionfilter['field_type']=='drop_down'){
                $this->db->where_in($Unionfilter['database_field'],explode(',',$Unionfilter['value']));
            }
            elseif($Unionfilter['field_type']=='date'){
                $this->db->where('DATE('.$Unionfilter['database_field'].')'.$Unionfilter['condition'],$Unionfilter['value']);
            }
            elseif($Unionfilter['field_type']=='numeric_text' || $Unionfilter['field_type']=='free_text'){
                if($Unionfilter['condition']=='like'){
                    $this->db->like($Unionfilter['database_field'],$Unionfilter['value'],'both');
                }
                elseif($Unionfilter['condition']=='<' || $Unionfilter['condition']=='>'|| $Unionfilter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();
        //  echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('providerName',$data['sort']['reverse']);
            else
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else
            $this->db->order_by('id_contract_review','DESC');
        
        $query = $this->db->get();
          //echo 
        return array('total_records' => $count_result,'data' => $query->result_array());
    }

    public function CheckContractHasAccess($data){
        $this->db->select('*')->from('contract c');
        $this->db->join('contract_review cr','cr.contract_id = c.id_contract','left');
        $this->db->join('contract_user cu','c.id_contract = cu.contract_id','left');
        $this->db->where('cr.id_contract_review',$data['contract_review_id']);
        $this->db->or_where('cu.contract_review_id',$data['contract_review_id']);
        $this->db->group_start();
        $this->db->where('c.contract_owner_id',$data['user_id']);
        $this->db->or_where('c.delegate_id',$data['user_id']);
        $this->db->or_where('cu.user_id',$data['user_id']);
        $this->db->group_end();
        $query = $this->db->get();
        return $query->result_array();
    }
    public function checkreviewandworkflowincalendar($data=null){
        $this->db->select('*');
        $this->db->from('calender c'); 
        if(isset($data['contract_id']))//Using only for Overview List
        $this->db->where('( CONCAT(",", `c`.contract_id, ",") REGEXP ",('.$data["contract_id"].')," or CONCAT(",", `c`.contract_id, ",") REGEXP ",,")',null,false);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProviderTotalspent($data=null){
        $this->db->select('SUM(cnt.contract_value) total_spent');
        $this->db->from('provider pr');
        $this->db->join('contract cnt','pr.id_provider=cnt.provider_name','left');
        $this->db->where('cnt.is_deleted','0');
        $this->db->where('cnt.can_review','1');
        $this->db->where('cnt.parent_contract_id','0');
        if(!empty($data['user_id'])){
            $this->db->where('cnt.contract_owner_id',$data['user_id']);
            $this->db->or_where('cnt.delegate_id',$data['user_id']);
        }
        if(!empty($data['provider_id'])){
            $this->db->where('pr.id_provider',$data['provider_id']);
        }
        $query = $this->db->get();
        return $query->result_array();
    }
    function getAllProviderContractList($data){

        /**
         * Start 
         * select query for all contract 
         */
        $this->db->select('0 as is_workflow, (SELECT SUM(spent_amount) FROM spent_lines WHERE contract_id=c.id_contract AND status=1) spent_amount, c.contract_value as Projected_value,`c`.*, CONCAT(u1.first_name, " ", u1.last_name) bu_owner, CONCAT(u2.first_name, " ", u2.last_name) bu_delegate, `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`, `p`.`provider_name` as providerName, `cu`.`currency_name`, `rc`.`classification_name`, `rcl`.`relationship_category_name`, (select MAX(id_contract_review) from contract_review where contract_id = c.id_contract and is_workflow = 0) id_contract_review, IF(IFNULL(c.parent_contract_id, 0)>0, "sub_agreement", IF((select count(cpa.id_contract) from contract cpa where cpa.parent_contract_id=c.id_contract)>0, "parent_agreement", "agreement")) as agreement_type, "0" as id_contract_workflow,crv.contract_review_status,crv.updated_on as review_updated_on,crv.validation_status,TIMESTAMPDIFF(MONTH, `contract_start_date`, contract_end_date) months,IF(ctry.country_name!="",CONCAT(bu.bu_name," - ",ctry.country_name),bu.bu_name) as bu_name');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("`contract` `c`");
        $this->db->join("provider p","p.id_provider=c.provider_name","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join('country ctry','bu.country_id=ctry.id_country','left');
        $this->db->join("`currency` `cu`","`c`.`currency_id`=`cu`.`id_currency`","left");
        $this->db->join("`relationship_category_language` `rcl`","`c`.`relationship_category_id`=`rcl`.`relationship_category_id` and `language_id`=1","left");
        $this->db->join("`contract_review` `crv`"," `crv`.`contract_id`=`c`.`id_contract`","left");
        $this->db->join("`relationship_classification_language` `rc` "," `rc`.`relationship_classification_id`=`c`.`classification_id`","left");
        $this->db->join("`user` `u1` "," `c`.`contract_owner_id`=`u1`.`id_user`","left");
        $this->db->join("`user` `u2` "," `c`.`delegate_id`=`u2`.`id_user`","left");
        $this->db->join("`template` `t` "," `c`.`template_id`=`t`.`id_template`","left");
        $this->db->join("spent_lines sl","c.id_contract=sl.contract_id","left");

        //search option 
        if(isset($data['search']))
        {
            if(!$data['advancedsearch_get'])
            {
                $this->db->group_start();
                $this->db->like('c.contract_name', $data['search'], 'both');
                $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                $this->db->or_like('p.provider_name', $data['search'], 'both');
                $this->db->or_like('bu.bu_name', $data['search'], 'both');
                $this->db->group_end();
            }
            else
            {   
                if($data['advancedsearch_get']->contract_name==1 || $data['advancedsearch_get']->relationship_category_name==1|| $data['advancedsearch_get']->bu_name==1 || $data['advancedsearch_get']->provider_name_search==1|| $data['advancedsearch_get']->contract_value==1|| $data['advancedsearch_get']->description==1|| $data['advancedsearch_get']->description==1||$data['advancedsearch_get']->tag_option_value==1 || $data['advancedsearch_get']->owner==1 || $data['advancedsearch_get']->delegate==1 || $data['advancedsearch_get']->automatic_prolongation==1 || $data['advancedsearch_get']->classification==1)
                {
                    $this->db->join('contract_tags ct ','c.id_contract=ct.contract_id','left');    
                    $this->db->group_start();
                    if(isset($data['advancedsearch_get']->contract_name))
                        $this->db->like('c.contract_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->relationship_category_name))
                        $this->db->or_like('rcl.relationship_category_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->bu_name))
                        $this->db->or_like('bu.bu_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->provider_name_search))
                        $this->db->or_like('p.provider_name', $data['search'], 'both');
                    if(isset($data['advancedsearch_get']->contract_value))
                        $this->db->or_like('c.contract_value',$data['search'],'both');//description
                    if(isset($data['advancedsearch_get']->description))
                        $this->db->or_like('c.description',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->tag_option_value))
                        $this->db->or_like('ct.tag_option_value',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->owner))
                        $this->db->or_like('CONCAT(u1.first_name," ",u1.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->delegate))
                        $this->db->or_like('CONCAT(u2.first_name," ",u2.last_name)',$data['search'],'both');
                    if(isset($data['advancedsearch_get']->automatic_prolongation)){
                        if(strtolower($data['search'])=='yes'){
                            $this->db->or_like('c.auto_renewal','1','both');
                        }else if(strtolower($data['search'])=='no'){
                            $this->db->or_like('c.auto_renewal','0','both');
                        }else{
                            $this->db->or_like('c.auto_renewal','1','both');
                            $this->db->or_like('c.auto_renewal','0','both');
                        }
                    }
                    if(isset($data['advancedsearch_get']->classification))
                        $this->db->or_like('rc.classification_name',$data['search'],'both');
                    
                    $this->db->group_end();
            
                }
      
            }
        }//end if search
        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if(isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['relationship_category_id']) && $data['relationship_category_id']>0)
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['created_this_month']))
            $this->db->where('MONTH(c.created_on) = MONTH(CURDATE()) AND YEAR(c.created_on) = YEAR(CURDATE())');
        if(isset($data['ending_this_month']))
            $this->db->where('MONTH(c.contract_end_date) = MONTH(CURDATE()) AND YEAR(c.contract_end_date) = YEAR(CURDATE())');
        if(isset($data['automatic_prolongation']))
            $this->db->where('c.auto_renewal','1');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        // if(isset($data['parent_contract_id']) && isset($data['parent_contract_id'])>0)
        //     $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        else if(isset($data['get_all_records'])){

        } 
        // $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        //ended conditions
        
        $this->db->group_by('c.id_contract');

        $new_query = $this->db->_compile_select();
        $this->db->_reset_select();
        
        $this->db->select("*")->from("($new_query) as unionTable");
        //Can_access filters the records user have access to
        if(isset($data['can_access']) && $data['can_access'] > 0)
            $this->db->where('can_access',$data['can_access']);

        $count_result_db = clone $this->db;
        $count_result = $count_result_db->get();
        //  echo $count_result_db->last_query();exit;
        $count_result = $count_result->num_rows();

        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
        $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse'])){
            if($data['sort']['predicate']=='provider_name')
                $this->db->order_by('providerName',$data['sort']['reverse']);
            else if($data['sort']['predicate']=='last_review')
                $this->db->order_by('review_updated_on',$data['sort']['reverse']);
            else 
                $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        }
        else
            $this->db->order_by('providerName,contract_name','asc');
        
        $query = $this->db->get();
        // echo 
        return array('total_records' => $count_result,'data' => $query->result_array());
    }

    public function getConnectedProjectsContracts($data){
        $this->db->select('c.*,concat(u.first_name," ",u.last_name) owner_name,concat(u1.first_name," ",u1.last_name) delegate_name');
        $this->db->from('contract c');
        if(isset($data['contract_id'])){
            $this->db->join('contract_projects cp','c.id_contract= cp.project_id','left');
            $this->db->join('user u','u.id_user=c.contract_owner_id','left');
            $this->db->join('user u1','u1.id_user=c.delegate_id','left');
            $this->db->where('cp.contract_id',$data['contract_id']);
        }

        if(isset($data['project_id'])){
            $this->db->join('contract_projects cp','c.id_contract= cp.contract_id','left');
            $this->db->join('user u','u.id_user=c.contract_owner_id','left');
            $this->db->join('user u1','u1.id_user=c.delegate_id','left');
            $this->db->where('cp.project_id',$data['project_id']); 
        }
        $this->db->where('cp.is_linked',1); 
        $query = $this->db->get();
        //echo ''.$this->db->last_query(); exit;
        return $query->result_array();
    }
    public function getServiceCatalogue($data){
        $this->db->select('sc.*,cu.id_currency,cu.currency_name,pp.payment_periodicity_name,cat.catalogue_unique_id,cat.catalogue_name');
        $this->db->from('service_catalogue sc');
        $this->db->join('contract c','c.id_contract= sc.contract_id','left');
        $this->db->join('payment_periodicity pp','sc.payment_periodicity_id=pp.id_payment_periodicity','left');
        $this->db->join('catalogue cat','sc.catalogue_id=cat.id_catalogue','left');
        $this->db->join('currency cu','cat.currency_id=cu.id_currency','left');
        if(isset($data['id_contract'])){
            $this->db->where('sc.contract_id',$data['id_contract']);  
        }
        if(isset($data['id_service_catalogue'])){
            $this->db->where('sc.id_service_catalogue',$data['id_service_catalogue']);
        }
        if(isset($data['search'])){
            $this->db->group_start();
            //$this->db->like('sc.catalogue_item_name', $data['search'], 'both');
            $this->db->like('cat.catalogue_name', $data['search'], 'both');
            $this->db->or_like('sc.unit_type', $data['search'], 'both');
            $this->db->or_like('pp.payment_periodicity_name', $data['search'], 'both');
            $this->db->or_like('sc.quantity', $data['search'], 'both');
            $this->db->group_end();
        }
        $this->db->where('sc.status',1); 
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
            $this->db->order_by('id_service_catalogue','asc');
        }
        $result = $this->db->get();//echo $this->db->last_query();
        return array('total_records'=>$count_result,'data'=>$result->result_array());
       
    }

    public function getServiceCatalogueForChart($data)
    {
        $this->db->select('sc.*,cu.id_currency,cu.currency_name,pp.payment_periodicity_name,cat.catalogue_unique_id,cat.catalogue_name');
        $this->db->from('service_catalogue sc');
        $this->db->join('contract c','c.id_contract= sc.contract_id','left');
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('payment_periodicity pp','sc.payment_periodicity_id=pp.id_payment_periodicity','left');
        $this->db->join('catalogue cat','sc.catalogue_id=cat.id_catalogue','left');
        if(isset($data['contract_id'])){
            $this->db->where('sc.contract_id',$data['contract_id']);  
        }
        $this->db->group_start();
            $this->db->or_where('sc.calculated_total_item_spend_add_to_chart', '1');
            $this->db->or_where('sc.manual_total_item_spend_add_to_chart', '1');
        $this->db->group_end();
        $this->db->where('sc.status',1); 
        $result = $this->db->get();
        return $result->result_array();
    }
    public function getprojectdashboardQuestionAnsweres($data){
        if($data['question_type'] == 'input' || $data['question_type'] == 'date'){
            //If question type is input or date then no need of joining the option tables
            $this->db->select('q.id_question, cqr.question_answer,cqr.v_question_answer,cqr.question_feedback,cqr.v_question_feedback');
            $this->db->from('contract_question_review cqr');
            $this->db->join('question q','cqr.question_id = q.id_question','left');
        }else{
            //If question type is not an input and date then joining the option tables is nessesury 
           // $this->db->select('q.id_question, qol.option_name question_answere');
            $this->db->select('q.id_question, qol.option_name as question_answer,qoll.option_name as v_question_answer,cqr.question_feedback,cqr.v_question_feedback');
            $this->db->from('contract_question_review cqr');
            $this->db->join('question_option_language qol','cqr.question_option_id = qol.question_option_id','left');
            $this->db->join('question_option qo','qo.id_question_option = qol.question_option_id','left');
            $this->db->join('question_option_language qoll','cqr.v_question_option_id = qoll.question_option_id','left');
            $this->db->join('question_option qoo','qoo.id_question_option = qoll.question_option_id','left');
            $this->db->join('question q','qo.question_id = q.id_question','left');
        }
        $this->db->where_in('q.topic_id',$data['topic_ids']);
        if(isset($data['parent_question_id'])){
            $this->db->where('q.parent_question_id',$data['parent_question_id']);
        }
        $this->db->group_by('q.id_question');
        $this->db->order_by('q.question_order','asc');
        $result = $this->db->get();
     //echo $this->db->last_query();
        return $result->result_array();
    }
    function dashboardActivityCount($data){
        /**
         * Start 
         * select query for contract and contract_reviews 
         */
        $this->db->select('0 as `is_workflow`,if(c.can_review=0,"0","1") type_sort_key, if(t.template_name is not null, "", "") as workflow_id, `t`.`template_name`, `t`.`id_template`, `c`.*,  `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`, `p`.`provider_name` as providerName,  "0" as id_contract_workflow,crv.contract_review_status,c.contract_value as Projected_value,crv2.validation_status,cw.parent_id as workflow_parent_id,"review" as typeOfActivity');
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6|| $this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(crv2.id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("`contract` `c`");
        $this->db->join("provider p","p.id_provider=c.provider_name","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join("`contract_review` `crv`"," `crv`.`contract_id`=`c`.`id_contract` and crv.is_workflow=0","left");
        $this->db->join("`template` `t` "," `c`.`template_id`=`t`.`id_template`","left");
        $this->db->join("`contract_workflow` `cw`","`c`.`id_contract`=`cw`.`contract_id`","left");
        $this->db->join("contract_review crv2","crv2.id_contract_review=(select MAX(id_contract_review) from contract_review where contract_id = c.id_contract and is_workflow = 0)","left");

        if(!empty($data['validation_filter_status'])){
            $this->db->join("contract_user cus","crv2.id_contract_review=cus.contract_review_id","left");
            $this->db->join("user u4","cus.user_id=u4.id_user","left");
        }
        if(!empty($data['can_review'])){
            $this->db->where('c.can_review',$data['can_review']);
        }
        $this->db->where('c.type','contract');
        //search option 
    
        if(!empty($data['validation_filter_status'])){
            if($this->session_user_info->contribution_type==1 || $data['validation_filter_contribution_type']==1)
            $this->db->where('u4.contribution_type',1);
            $this->db->where_in('crv2.validation_status',$data['validation_filter_status']);
            $this->db->where('cus.status',1);
            if(!empty($data['contributor_user_id'])){
                $this->db->where("cus.user_id",$data['contributor_user_id']);
            }
        }
        if($data['project_workflow_type']=='child'){
            $this->db->where('cw.parent_id',$data['parent_workflow_id']);
        }
       

        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if($data['contribution_type']=='my_activities' && in_array($this->session_user_info->user_role_id,array(2,3,4))){
            $this->db->where_in('crv.contract_review_status',array('pending review', 'review in progress'));
        }
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('c.contract_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('c.contract_status',$data['contract_status']);
        if(isset($data['relationship_category_id']) && !is_array($data['relationship_category_id']))
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && $data['parent_contract_id']>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        // else if(isset($data['get_all_records'])){

        // }else
        //     $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        //ended conditions
        // advance filter start    
        foreach($data['adv_filters'] as $filter){
            if($filter['field_type']=='drop_down'){
                if($filter['database_field']=='activity_status')
                {
                    $this->db->where_in('c.contract_status',explode(',',$filter['value']));
                }
                elseif($filter['database_field']=='validation_status')
                {
                    continue;
                }
                else
                {
                    $this->db->where_in($filter['database_field'],explode(',',$filter['value']));
                }
            }
            elseif($filter['field_type']=='date'){
                $this->db->where('DATE('.$filter['database_field'].')'.$filter['condition'],$filter['value']);
            }
            elseif($filter['field_type']=='numeric_text' || $filter['field_type']=='free_text'){
                if($filter['condition']=='like'){
                    $this->db->like($filter['database_field'],$filter['value'],'both');
                }
                elseif($filter['condition']=='<' || $filter['condition']=='>'|| $filter['condition']=='=' ){
                    $this->db->where($filter['database_field']." ".$filter['condition'],$filter['value']);
                }
            }
        }
        // advance filter end  

        $this->db->group_by('c.id_contract');

        //first sub query
        $subQuery1 = $this->db->_compile_select();
        //end contract and contract_review 
        //restting select query for writing new query
        $this->db->_reset_select();
        $this->db->select("if(cw.workflow_name is null, 0, 1)  as is_workflow,'2' type_sort_key, `cw`.`workflow_id` as `workflow_id`, `cw`.`workflow_name` as `template_name`, `cw`.`workflow_id` as `id_template`, `c`.*, `c`.`provider_name` as `provider_id`, `c`.`id_contract` `contract_id`,  `p`.`provider_name` as providerName, cw.id_contract_workflow as id_contract_workflow,crv.contract_review_status,c.contract_value as Projected_value,crvw.validation_status,cw.parent_id as workflow_parent_id,'task' as typeOfActivity");
        if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6 ||$this->session_user_info->user_role_id == 8)
            $this->db->select('1 as can_access');
        else if($this->session_user_info->user_role_id == 3)
            $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 4)
            $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
        else if($this->session_user_info->user_role_id == 7)
            $this->db->select('IF(get_contributor_contracts(crvw.id_contract_review,'.$this->session_user_id.')>0,1,0) as can_access');
        else
            $this->db->select('0 as can_access');
        $this->db->from("contract c");
        $this->db->join("provider p","`p`.`id_provider` = `c`.`provider_name`","left");
        $this->db->join("`business_unit` `bu`", "`bu`.`id_business_unit`=`c`.`business_unit_id`","left");
        $this->db->join("`contract_workflow` `cw`","`c`.`id_contract`=`cw`.`contract_id`","left");
        $this->db->join("`contract_review` `crv`","`crv`.`contract_workflow_id`=`cw`.`id_contract_workflow`","left");
        $this->db->join("`contract_review` `crvw`","crvw.id_contract_review=(select MAX(id_contract_review) from contract_review where contract_workflow_id = cw.id_contract_workflow)","left");
    
        
        if(!empty($data['validation_filter_status'])){
            $this->db->join("contract_user cus","crvw.id_contract_review=cus.contract_review_id","left");
            $this->db->join("user u4","cus.user_id=u4.id_user","left");
        }
        if(!empty($data['can_review'])){
            $this->db->where_in('c.can_review',array('0','1'));
        }
        if(!empty($data['validation_filter_status'])){
            if($this->session_user_info->contribution_type==1 || $data['validation_filter_contribution_type']==1)
            $this->db->where('u4.contribution_type',1);
            $this->db->where('cus.status',1);
            // $this->db->where("crv.contract_review_status!='finished'");
            $this->db->where_in('crvw.validation_status',$data['validation_filter_status']);
            $this->db->where('crvw.contract_review_status!="finished"');
            if(!empty($data['contributor_user_id'])){
                $this->db->where("cus.user_id",$data['contributor_user_id']);
            }
            
        }
        // $this->db->where('cw.parent_id=0');
        if($data['project_workflow_type']=='parent'){
            // $this->db->where('cw.parent_id','0');
        }
        if($data['project_workflow_type']=='child'){
            $this->db->where('cw.parent_id',$data['parent_workflow_id']);
        }
       

        //started conditions
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id']) && strtolower($data['business_unit_id'])!='all')
            $this->db->where('c.business_unit_id',$data['business_unit_id']);
        if(isset($data['id_business_unit']) && !is_array($data['id_business_unit']) && strtolower($data['id_business_unit'])!='all')
            $this->db->where('c.business_unit_id',$data['id_business_unit']);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);        
        if (isset($data['business_unit_id']) && is_array($data['business_unit_id']))
            $this->db->where_in('c.business_unit_id', count($data['business_unit_id'])>0?$data['business_unit_id']:array(0));        
        if(isset($data['contract_owner_id']))
            $this->db->where('c.contract_owner_id',$data['contract_owner_id']);
        if(isset($data['delegate_id']))
            $this->db->where('c.delegate_id',$data['delegate_id']);
        if($data['contribution_type']=='my_activities' && in_array($this->session_user_info->user_role_id,array(2,3,4))){
            $this->db->where_in('crv.contract_review_status',array('pending workflow', 'workflow in progress'));
        }
        if(isset($data['created_by']))
            $this->db->where('c.created_by',$data['created_by']);
        if(isset($data['contract_status']) && !is_array($data['contract_status']))
            $this->db->where('cw.workflow_status',$data['contract_status']);
        if(isset($data['contract_status']) && is_array($data['contract_status']))
            $this->db->where_in('cw.workflow_status',$data['contract_status']);
        if(isset($data['relationship_category_id']) && !is_array($data['relationship_category_id']))
            $this->db->where('c.relationship_category_id',$data['relationship_category_id']);
        if(isset($data['provider_name']) && strtolower($data['provider_name'])!='all')
            $this->db->where('p.provider_name',$data['provider_name']);
        if(isset($data['provider_id']) && $data['provider_id']>0)
            $this->db->where('c.provider_name',$data['provider_id']);
        if(isset($data['end_date_lessthan_90']))
            $this->db->where('DATE(c.contract_end_date) >= CURDATE() AND DATE(c.contract_end_date) <= DATE(NOW() + INTERVAL 90 DAY)');
        if(isset($data['date_field']) && isset($data['created_date']) && isset($data['date_period']) && $data['date_field']!='' && $data['created_date']!='' && $data['date_period']!='')
            $this->db->where('DATE(c.'.$data["date_field"].')'.$data["date_period"].'"'.$data["created_date"].'"');
        if(isset($data['reviewable_contracts']))
                $this->db->where('c.can_review',1);
        if(isset($data['parent_contract_id']) && $data['parent_contract_id']>0)
            $this->db->where('c.parent_contract_id',$data['parent_contract_id']);
        // else if(isset($data['get_all_records'])){

        // }else            
        //     $this->db->where('c.parent_contract_id',0);
        if(isset($data['deleted']))
            $this->db->where('c.is_deleted','1');
        else
            $this->db->where('c.is_deleted','0');
        //ended conditions
        //in second sub query, 
        if(isset($data['workflowName_Null']))
            $this->db->where("cw.workflow_name !=","");

        $this->db->where("cw.status","1");
        $this->db->group_by('cw.id_contract_workflow');        

        //second sub query
        $subQuery2 = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select("count(*) as dashboardActivityCount")->from("($subQuery1 UNION $subQuery2) as unionTable");

        //Activity filter filters reviews & workflows
        if(isset($data['activity_filter'])){
            if($data['activity_filter'] > 1)
                $this->db->where('type_sort_key',2);//Workflows
            else
                $this->db->where_in('type_sort_key',array(0,1));//Reviews
        } 
        if(isset($data['validation_filter_status'])){
            // $this->db->where_in('validation_status',$data['validation_filter_status']);
            // $this->db->where('contribution_type',1);
        }
        // if($data['parent_contract_id'] > 0)
        //Can_access filters the records user have access to
        if(isset($data['can_access']) && $data['can_access'] > 0 && $this->session_user_info->user_role_id != 8)
            $this->db->where('can_access',$data['can_access']);
        if($data['parent_contract_id']>0){
            $this->db->where('is_workflow',0);
        }
        if(isset($data['type']) && $data['type']=='action_items'){
            $this->db->group_by('contract_id');          
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='sub'){
            $this->db->where('is_sub>0');
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='parent'){
            $this->db->where('is_parent>0');
        }
        if(!empty($data['hierarchy']) && $data['hierarchy']=='single'){
            $this->db->where('is_parent',0);
            $this->db->where('is_sub',0);
        }


        //from current query 
        // $count_result_db = clone $this->db;
        // $count_result = $count_result_db->get();
        // //echo $this->db->last_query();exit;
        // $count_result = $count_result->num_rows();


        $this->db->order_by('providerName,contract_name','asc');


        $query = $this->db->get();
        

        // return array('total_records' => $count_result,'data' => $query->result_array());
        return $query->result_array();

    }

    function listing($data){
        //print_r($data);
        $this->db->select("id_contract,contract_name");
        if(isset($data['can_access']) && $data['can_access'] = 1)
        {
            if($this->session_user_info->user_role_id == 2 || $this->session_user_info->user_role_id == 6 ||$this->session_user_info->user_role_id == 8)
                $this->db->select('1 as can_access');
            else if($this->session_user_info->user_role_id == 3)
                $this->db->select('IF(get_owner_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
            else if($this->session_user_info->user_role_id == 4)
                $this->db->select('IF(get_delegate_contracts(c.id_contract,'.$this->session_user_id.')>0,1,0) as can_access');
            else
                $this->db->select('0 as can_access');
        }
        
        $this->db->from("contract c");
        $this->db->join("business_unit bu", "bu.id_business_unit=c.business_unit_id","left");
        if(isset($data['type']))
            $this->db->where('c.type',$data['type']);
        if(isset($data['customer_id']) && $data['customer_id']>0)
            $this->db->where('bu.customer_id',$data['customer_id']);

        $this->db->where('c.is_deleted',0);    
        if(isset($data['can_access']) && $data['can_access'] = 1)
        {  
            $this->db->having('can_access',$data['can_access']);
        }
        
        $this->db->order_by('c.contract_name','asc');    
        $query = $this->db->get();   
        return $query->result_array(); 
        
    }

    public function TagAnswer($data)
    {
        $this->db->select('GROUP_CONCAT(tag_option_name) as tag_option_values');
        $this->db->from('tag_option_language');
        $this->db->where_in('tag_option_id',$data['explodedData']);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
}

