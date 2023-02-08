<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Validation_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function getBusinessUnitList($data)
    {
        $this->db->select('bu.id_business_unit');
        $this->db->from('business_unit bu');
        //$this->db->where('bu.status',1);
        if(isset($data['customer_id']))
            $this->db->where('bu.customer_id',$data['customer_id']);
        $query = $this->db->get();
        $business_unit=$query->result_array();
        $business_unit_id = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
        return $business_unit_id;
    }
    public function getBusinessUnitListByUser($data){
        $this->db->select('b.business_unit_id');
        $this->db->from('business_unit_user b');
        $this->db->where('b.status', 1);
        if(isset($data['user_id']))
            $this->db->where('b.user_id', $data['user_id']);

        $query = $this->db->get();
        $business_unit=$query->result_array();
        $business_unit_id = array_map(function($i){ return $i['business_unit_id']; },$business_unit);
        //$business_unit_id_assigned = $this->getReviewBusinessUnits(array('id_user'=>$data['user_id']));
        //$business_unit_id=array_merge($business_unit_id,$business_unit_id_assigned);
        return $business_unit_id;
    }
    public function getContracts($data){
        $business_unit_id=array();
        if(count($data['business_unit_id'])>0) {
            $this->db->select('c.id_contract');
            $this->db->from('contract c');
            $this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            if(isset($data['deleted'])){

            }
            else
                $this->db->where('c.is_deleted','0');
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $business_unit_id = array_map(function ($i) {
                return $i['id_contract'];
            }, $business_unit);
        }
        return $business_unit_id;
    }
    public function getContributorContract($data){
        $business_unit_id=array();
        //if(count($data['business_unit_id'])>0) {
            $this->db->select('c.id_contract');
            $this->db->from('contract c');
            $this->db->join('contract_user cur', 'c.id_contract=cur.contract_id and cur.status=1', '');
            //$this->db->where_in('c.business_unit_id', $data['business_unit_id']);
            $this->db->where('cur.user_id',$data['customer_user']);
            if(isset($data['deleted'])){

            }
            else
                $this->db->where('c.is_deleted','0');
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $business_unit_id = array_map(function ($i) {
                return $i['id_contract'];
            }, $business_unit);
        //}
        return $business_unit_id;
    }
    public function getContractReviews($data){
        $contract_review_id=array();
        if(count($data['contract_id'])>0) {
            $this->db->select('c.id_contract_review');
            $this->db->from('contract_review c');
            $this->db->where_in('c.contract_id', $data['contract_id']);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $contract_review_id = array_map(function ($i) {
                return $i['id_contract_review'];
            }, $business_unit);
        }
        return $contract_review_id;
    }
    public function getContractDocuments($data){
        $document_id=array();
        if(count($data['contract_id'])>0) {
            $this->db->select('d.id_document');
            $this->db->from('document d');
            $this->db->where_in('d.reference_id', $data['contract_id']);
            $this->db->where('d.reference_type', 'contract');
            //$this->db->where('d.document_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $document_id = array_map(function ($i) {
                return $i['id_document'];
            }, $business_unit);
        }
        return $document_id;

    }
    public function getContractReviewDocuments($data){
        $document_id=array();
        if(count($data['contract_review_id'])>0) {
            $this->db->select('d.id_document');
            $this->db->from('document d');
            $this->db->where_in('d.module_id', $data['contract_review_id']);
            $this->db->where('d.module_type', 'contract_review');
            //$this->db->where('d.document_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $document_id = array_map(function ($i) {
                return $i['id_document'];
            }, $business_unit);
        }
        return $document_id;
    }
    public function getDocumentsList($data)
    {
        $this->db->select('d.*,u.user_role_id,CONCAT(u.first_name," ",u.last_name) as uploaded_user,DATE_FORMAT(d.uploaded_on,"%Y-%m-%d") as updated_date');
        $this->db->from('document d');
        $this->db->join('user u','u.id_user=d.uploaded_by','LEFT');

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
    public function getContractActionItems($data){
        $contract_review_action_item_id=array();
        if(count($data['contract_id'])>0) {
            $this->db->select('crai.id_contract_review_action_item');
            $this->db->from('contract_review_action_item crai');
            $this->db->where_in('crai.contract_id', $data['contract_id']);
            //$this->db->where('crai.item_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $contract_review_action_item_id = array_map(function ($i) {
                return $i['id_contract_review_action_item'];
            }, $business_unit);
        }
        return $contract_review_action_item_id;
    }
    public function getContractActionItemsByUser($data){
        $contract_review_action_item_id=array();
        if(count($data['user_id'])>0) {
            $this->db->select('crai.id_contract_review_action_item');
            $this->db->from('contract_review_action_item crai');
            $this->db->where('crai.responsible_user_id', $data['user_id']);
            //$this->db->where('crai.item_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $contract_review_action_item_id = array_map(function ($i) {
                return $i['id_contract_review_action_item'];
            }, $business_unit);
        }
        return $contract_review_action_item_id;
    }
    public function getCustomerUsers($data){
        $user_id=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('u.id_user');
            $this->db->from('user u');
            $this->db->where_in('u.customer_id', $data['customer_id']);
            //$this->db->where('u.user_status', 1);
            if(isset($data['user_role_id']))
                $this->db->where('u.user_role_id', $data['user_role_id']);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $user_id = array_map(function ($i) {
                return $i['id_user'];
            }, $business_unit);
        }
        return $user_id;
    }
    public function getCustomerRelationshipCategories($data){
        $id_relationship_category=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('rc.id_relationship_category');
            $this->db->from('relationship_category rc');
            $this->db->where_in('rc.customer_id', $data['customer_id']);
            //$this->db->where('rc.relationship_category_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_relationship_category = array_map(function ($i) {
                return $i['id_relationship_category'];
            }, $business_unit);
        }
        return $id_relationship_category;
    }
    public function getCustomerProviderRelationshipCategories($data){
        $id_relationship_category=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('prc.id_provider_relationship_category');
            $this->db->from('provider_relationship_category prc');
            $this->db->where_in('prc.customer_id', $data['customer_id']);
            //$this->db->where('rc.relationship_category_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_relationship_category = array_map(function ($i) {
                return $i['id_provider_relationship_category'];
            }, $business_unit);
        }
        return $id_relationship_category;
    }
    public function getCustomerRelationshipClassifications($data){
        $id_relationship_classification=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('rc.id_relationship_classification');
            $this->db->from('relationship_classification rc');
            $this->db->where_in('rc.customer_id', $data['customer_id']);
            //$this->db->where('rc.classification_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_relationship_classification = array_map(function ($i) {
                return $i['id_relationship_classification'];
            }, $business_unit);
        }
        return $id_relationship_classification;
    }

    public function getCustomerProviderRelationshipClassifications($data){
        $id_provider_relationship_classification= array();
        if(count($data['customer_id'])>0){
            $this->db->select('prc.id_provider_relationship_classification');
            $this->db->from('provider_relationship_classification prc');
            $this->db->where_in('prc.customer_id',$data['customer_id']);
            $query = $this->db->get();
            //echo ''.$this->db->last_query(); exit;
            $business_unit = $query->result_array();
            $id_provider_relationship_classification = array_map(function ($i){
                return $i['id_provider_relationship_classification'];
            },$business_unit);
        }
        return $id_provider_relationship_classification;
    }
    public function getCurrency(){
        $id_currency=array();

            $this->db->select('c.id_currency');
            $this->db->from('currency c');
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_currency = array_map(function ($i) {
                return $i['id_currency'];
            }, $business_unit);

        return $id_currency;
    }
    public function getMasterContractReviewModules(){
        $module_id=array();
        $module_lang=array();
        $lang=array();
        $this->db->select('m.id_module,ml.id_module_language,ml.language_id');
        $this->db->from('module m');
        $this->db->join('module_language ml','m.id_module=ml.module_id','left');
        $this->db->where('m.contract_review_id','0');
        $query = $this->db->get();
        $module_id_res = $query->result_array();
        $module_id = array_map(function ($i) {
            return $i['id_module'];
        }, $module_id_res);
        $module_lang = array_map(function ($i) {
            return $i['id_module_language'];
        }, $module_id_res);
        $lang = array_map(function ($i) {
            return $i['language_id'];
        }, $module_id_res);

        return array('module_id'=>$module_id,'module_lang'=>$module_lang,'language'=>$lang);
    }
    public function getMasterContractReviewTopics(){
        $topic_id=array();
        $topic_lang=array();
        $lang=array();
        $this->db->select('t.id_topic,tl.id_topic_language,tl.language_id');
        $this->db->from('topic t');
        $this->db->join('topic_language tl','t.id_topic=tl.topic_id','left');
        $this->db->where('t.parent_topic_id is null');
        $query = $this->db->get();
        $topic_id_res = $query->result_array();
        $topic_id = array_map(function ($i) {
            return $i['id_topic'];
        }, $topic_id_res);
        $topic_lang = array_map(function ($i) {
            return $i['id_topic_language'];
        }, $topic_id_res);
        $lang = array_map(function ($i) {
            return $i['language_id'];
        }, $topic_id_res);

        return array('topic'=>$topic_id,'topic_lang'=>$topic_lang,'language'=>$lang);
    }
    public function getLanguage(){
        $id_language=array();

        $this->db->select('l.id_language');
        $this->db->from('language l');
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_language = array_map(function ($i) {
            return $i['id_language'];
        }, $business_unit);

        return $id_language;
    }
    public function getContractReviewModules($data){
        $id_module=array();
        if(count($data['contract_review_id'])>0) {
            $this->db->select('m.id_module');
            $this->db->from('module m');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['contract_review_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('m.contract_review_id', $sale_ids);
            }
            $this->db->group_end();
            //$this->db->where('m.module_status >', 0);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_module = array_map(function ($i) {
                return $i['id_module'];
            }, $business_unit);
        }
        return $id_module;
    }
    public function getContractReviewTopics($data){
        $id_topic=array();
        if(count($data['module_id'])>0) {
            $this->db->select('t.id_topic');
            $this->db->from('topic t');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['module_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('t.module_id', $sale_ids);
            }
            $this->db->group_end();
            //$this->db->where('t.topic_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_topic = array_map(function ($i) {
                return $i['id_topic'];
            }, $business_unit);
        }
        return $id_topic;
    }
    public function getContractReviewQuestions($data){
        $id_question=array();
        if(count($data['topic_id'])>0) {
            $this->db->select('q.id_question');
            $this->db->from('question q');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['topic_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('q.topic_id', $sale_ids);
            }
            $this->db->group_end();
            //$this->db->where('q.question_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_question = array_map(function ($i) {
                return $i['id_question'];
            }, $business_unit);
        }
        return $id_question;
    }
    public function getContractReviewQuestionOptions($data){
        $id_question_option=array();
        if(count($data['question_id'])>0) {
            $this->db->select('q.id_question_option');
            $this->db->from('question_option q');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['question_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('q.question_id', $sale_ids);
            }
            $this->db->group_end();
            //$this->db->where('q.status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_question_option = array_map(function ($i) {
                return $i['id_question_option'];
            }, $business_unit);
        }
        return $id_question_option;
    }
    public function getCountries(){
        $id_country=array();
        $this->db->select('id_country');
        $this->db->from('country');
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_country = array_map(function ($i) {
            return $i['id_country'];
        }, $business_unit);
        return $id_country;
    }
    public function getTemplates(){
        $id_template=array();
        $this->db->select('id_template');
        $this->db->from('template');
        //$this->db->where('template_status',1);
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_template = array_map(function ($i) {
            return $i['id_template'];
        }, $business_unit);
        return $id_template;
    }
    public function getCustomers(){
        $id_customer=array();
        $this->db->select('id_customer');
        $this->db->from('customer');
        //$this->db->where('company_status',1);
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_customer = array_map(function ($i) {
            return $i['id_customer'];
        }, $business_unit);
        return $id_customer;
    }
    public function getUsers(){
        $user_id=array();

            $this->db->select('u.id_user');
            $this->db->from('user u');
            //$this->db->where('u.user_status', 1);
            $this->db->where('u.user_role_id!=', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $user_id = array_map(function ($i) {
                return $i['id_user'];
            }, $business_unit);

        return $user_id;
    }
    public function getUserRoles(){
        $id_user_role=array();

        $this->db->select('u.id_user_role');
        $this->db->from('user_role u');
        $this->db->where('u.id_user_role!=', 1);
        $this->db->where('u.role_status', 1);
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_user_role = array_map(function ($i) {
            return $i['id_user_role'];
        }, $business_unit);

        return $id_user_role;
    }
    public function getCustomerCalenders($data){
        $id_calender=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('c.id_calender');
            $this->db->from('calender c');
            $this->db->where_in('c.customer_id', $data['customer_id']);
            //$this->db->where('c.status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_calender = array_map(function ($i) {
                return $i['id_calender'];
            }, $business_unit);
        }
        return $id_calender;
    }
    public function getContractReviewMasterQuestions(){
        $id_question=array();

        $this->db->select('q.id_question');
        $this->db->from('question q');
        //$this->db->where('q.question_status', 1);
        $this->db->where('q.parent_question_id IS NULL');
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_question = array_map(function ($i) {
            return $i['id_question'];
        }, $business_unit);

        return $id_question;
    }
    public function getContractReviewMasterQuestionOptions(){
        $id_question_option=array();

        $this->db->select('q.id_question_option');
        $this->db->from('question_option q');
        //$this->db->where('q.status', 1);
        $this->db->where('q.parent_question_option_id IS NULL');
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_question_option = array_map(function ($i) {
            return $i['id_question_option'];
        }, $business_unit);

        return $id_question_option;
    }
    public function getTemplateModules(){
        $id_template_module=array();

            $this->db->select('m.id_template_module');
            $this->db->from('template_module m');

            //$this->db->where('m.status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_template_module = array_map(function ($i) {
                return $i['id_template_module'];
            }, $business_unit);

        return $id_template_module;
    }
    public function getTemplateModuleTopics(){
        $id_template_module_topic=array();

        $this->db->select('t.id_template_module_topic');
        $this->db->from('template_module_topic t');
        //$this->db->where('t.status', 1);
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_template_module_topic = array_map(function ($i) {
            return $i['id_template_module_topic'];
        }, $business_unit);

        return $id_template_module_topic;
    }
    public function getTemplateModuleTopicQuestions(){
        $id_template_module_topic_question=array();

        $this->db->select('t.id_template_module_topic_question');
        $this->db->from('template_module_topic_question t');
        //$this->db->where('t.status', 1);
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_template_module_topic_question = array_map(function ($i) {
            return $i['id_template_module_topic_question'];
        }, $business_unit);

        return $id_template_module_topic_question;
    }
    public function getReportIds($data){
        $report_id=array();

        $this->db->select('r.id_report');
        $this->db->from('report r');
        $this->db->join('user u','u.id_user=r.created_by','left');
        $this->db->where('u.customer_id',$data['customer_id']);
        $query = $this->db->get();
        $result = $query->result_array();
        $report_id=array_map(function ($i){
            return $i['id_report'];
        },$result);

        return $report_id;
    }
    public function getReportContractIds($data){
        $report_contract_id=array();

        $this->db->select('rc.id_report_contract');
        $this->db->from('report_contract rc');
        if(count($data['report_id'])>0)
            $this->db->where_in('rc.report_id',$data['report_id']);
        else
            $this->db->where(0);
        $query = $this->db->get();
        $result = $query->result_array();
        $report_contract_id=array_map(function ($i){
            return $i['id_report_contract'];
        },$result);

        return $report_contract_id;

    }
    public function getReportContractModuleIds($data){
        $report_contract_module_id=array();

        $this->db->select('rcm.id_report_contract_module');
        $this->db->from('report_contract_module rcm');
        if(count($data['report_contract_id'])>0)
            $this->db->where_in('rcm.report_contract_id',$data['report_contract_id']);
        else
            $this->db->where(0);
        $query = $this->db->get();
        $result = $query->result_array();
        $report_contract_module_id=array_map(function ($i){
            return $i['id_report_contract_module'];
        },$result);

        return $report_contract_module_id;

    }
    public function getContractReviewDiscussions($data){
        $id_module=array();
        if(count($data['contract_review_id'])>0) {
            $this->db->select('m.id_contract_review_discussion');
            $this->db->from('contract_review_discussion m');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['contract_review_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('m.contract_review_id', $sale_ids);
            }
            $this->db->group_end();
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_module = array_map(function ($i) {
                return $i['id_contract_review_discussion'];
            }, $business_unit);
        }
        return $id_module;
    }
    public function getContractReviewDiscussionQuestions($data){
        $id_module=array();
        if(count($data['contract_review_discussion_id'])>0) {
            $this->db->select('m.id_contract_review_discussion_question');
            $this->db->from('contract_review_discussion_question m');
            $this->db->group_start();
            $sale_ids_chunk = array_chunk($data['contract_review_discussion_id'],25);
            foreach($sale_ids_chunk as $sale_ids)
            {
                $this->db->or_where_in('m.contract_review_discussion_id', $sale_ids);
            }
            $this->db->group_end();
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_module = array_map(function ($i) {
                return $i['id_contract_review_discussion_question'];
            }, $business_unit);
        }
        return $id_module;
    }
    public function getCustomerEmailTemplates($data){
        $id_relationship_category=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('rc.id_email_template');
            $this->db->from('email_template rc');
            $this->db->where_in('rc.customer_id', $data['customer_id']);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            $business_unit = $query->result_array();
            $id_relationship_category = array_map(function ($i) {
                return $i['id_email_template'];
            }, $business_unit);
        }
        return $id_relationship_category;
    }
    public function getCustomerProviders($data){
        $id_relationship_category=array();
        if(count($data['customer_id'])>0) {
            $this->db->select('rc.id_provider');
            $this->db->from('provider rc');
            $this->db->where_in('rc.customer_id', $data['customer_id']);
            //$this->db->where('rc.relationship_category_status', 1);
            $query = $this->db->get();
            $business_unit = $query->result_array();
            $id_relationship_category = array_map(function ($i) {
                return $i['id_provider'];
            }, $business_unit);
        }
        return $id_relationship_category;
    }
    public function getReviewBusinessUnits($data){
        $id_relationship_category=array();
        $this->db->select('c.business_unit_id');
        $this->db->from('contract_user cu');
        $this->db->join('contract c','c.id_contract=cu.contract_id');
        $this->db->where('cu.user_id', $data['id_user']);
        $this->db->where('cu.status', 1);
        $this->db->where('cu.contract_review_id=(select max(id_contract_review) from contract_review cr where cr.contract_id=cu.contract_id)',null,false);
        if(isset($data['deleted'])){

        }
        else
            $this->db->where('c.is_deleted','0');
        $query = $this->db->get();
        $business_unit = $query->result_array();
        $id_relationship_category = array_map(function ($i) {
            return $i['business_unit_id'];
        }, $business_unit);
        return $id_relationship_category;
    }

//----------------//

}