<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
require APPPATH . '/libraries/REST_Controller.php';
 

class Catalogue extends REST_Controller
{
    public $session_user_id=NULL;
    public $session_user_info=NULL;
    public $session_user_master_currency=NULL;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('Catalogue_model');
        $this->load->model('Download_model');
        
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
        $this->session_user_master_currency=$this->Validation_model->getCurrency(array('customer'));
    }

    public function GenerateCataloguerId_get(){
        // $catalogue_count=$this->User_model->check_record_selected('count(*) as count','catalogue',array('customer_id'=>$this->session_user_info->customer_id));
        // $unique_id='C'.str_pad($catalogue_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
        $unique_id = uniqueId(array('module' => 'catalogue' , 'customer_id' => $this->session_user_info->customer_id));
        $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => array('catalogue_unique_id'=>$unique_id));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function add_post()
    {
        $data = $this->input->post();

        if(isset($data['catalogue'])){
            $data = $data['catalogue'];
        }
        if(isset($_FILES['file']))
            $totalFilesCount = count($_FILES['file']['name']);
        else
            $totalFilesCount=0;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
 
        $this->form_validator->add_rules('catalogue_name', array('required'=>$this->lang->line('catalogue_name_req')));
       
        $this->form_validator->add_rules('description', array('required'=>$this->lang->line('catalogue_description_req')));

        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status_required')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($data['customer_id']!=$this->session_user_info->customer_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        
      
        if(isset($data['currency_id'])) {
            $data['currency_id'] = pk_decrypt($data['currency_id']);
            if(!in_array($data['currency_id'],$this->session_user_master_currency)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'6');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
      

        if($this->session_user_info->user_role_id>2){

            if((($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_catalogue == 0)) || ($this->session_user_info->user_role_id > 4))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
           
        }
     
        // $check_catalogue_unique_id_exitst=$this->Catalogue_model->getcatalogueBybuid(array('catalogue_unique_id'=>$data['catalogue_unique_id'],'customer_id'=>$data['customer_id']));
        // if(!empty($check_catalogue_unique_id_exitst)){
        //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('catalogue_unique_id_alredy_ext')), 'data'=>'');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        
        //generating contract Unique id

        // $catalogue_count=$this->User_model->check_record_selected('count(*) as count','catalogue',array('customer_id'=>$this->session_user_info->customer_id));
        // $unique_id='C'.str_pad($catalogue_count[0]['count']+1, 7, '0', STR_PAD_LEFT);

        $unique_id = uniqueId(array('module' => 'catalogue' , 'customer_id' => $this->session_user_info->customer_id));

        $data['catalogue_unique_id'] = $unique_id;
        
        $add = array(
            'catalogue_name' => $data['catalogue_name'],
            'catalogue_unique_id' => $data['catalogue_unique_id'],
            'currency_id' => $data['currency_id'],
            'status' => (isset($data['status']) && $data['status'] == "Active") ? 1 : 0,
            'description' => isset($data['description'])?$data['description']:'',
            'created_by' => $this->session_user_id,
            'created_on' => currentDate(), 
            'customer_id' => $data['customer_id']
        );
     

        $id_catalogue=$this->User_model->insert_data('catalogue',$add);

        //////////CATALOGUE TAGS//////////////

        if(isset($data['grouped_tags']) && count($data['grouped_tags'])>0){
            
            //print_r($data['grouped_tags']);
            foreach($data['grouped_tags'] as $GK => $GV)
            {
                $data['catalogue_tags'] = $GV['tag_details'];
                $tag_data = array();
                foreach($data['catalogue_tags'] as $k => $v){                    
                    $tag_data[$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
                    $tag_data[$k]['catalogue_id'] = (int)$id_catalogue;
                    $tag_data[$k]['created_by'] = $this->session_user_id;
                    $tag_data[$k]['created_on'] = currentDate();
                    if($v['tag_type']=='input' || $v['tag_type']=='date')
                    {
                        $tag_data[$k]['tag_option_value'] = $v['tag_option'];
                    } 
                    elseif($v['tag_type']=='radio' || $v['tag_type']=='rag' || ($v['tag_type']=='dropdown' && ($v['multi_select'] == 0))){
                        $tag_data[$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                        $tag_data[$k]['comments'] = isset($v['comments']) ? $v['comments'] : NULL ;
                        $tag_option_value = $this->User_model->check_record('tag_option_language',array('tag_option_id'=>$tag_data[$k]['tag_option']));
                        if(isset($tag_option_value[0]) || isset($v['tag_option_name']))
                            $tag_data[$k]['tag_option_value'] = isset($v['tag_option_name'])?$v['tag_option_name']:$tag_option_value[0]['tag_option_name'];
                    }
                    elseif($v['tag_type'] == 'dropdown' && ($v['multi_select'] == 1))
                    {
                        $tagAnswers = [];
                        $tagOptionValue = [];
                        $CreatedTagOption = $this->User_model->check_record("tag_option" , array("tag_id" => $tag_data[$k]['tag_id'] ,"status" => 1));
                        foreach($v['tag_option'] as $multiDropKey => $multiDropValue)
                        {
                            foreach($CreatedTagOption as $option)
                            {
                                if($option['id_tag_option'] == (int)pk_decrypt($multiDropValue))
                                {
                                    $tagOptionValue[] = $option['tag_option_name'];
                                }
                            }

                            $tagAnswers[] = (int)pk_decrypt($multiDropValue);
                        }
                        $commaSepTagAnswers = "";
                        $commaSepTagAnswersValue = "";
                        if(count($tagAnswers) > 0)
                        {
                            $commaSepTagAnswers = implode("," , $tagAnswers);
                        }
                        if(count($tagOptionValue) > 0)
                        {
                            $commaSepTagAnswersValue = implode("," , $tagOptionValue);
                        }
                        $tag_data[$k]['tag_option'] = $commaSepTagAnswers;
                        $tag_data[$k]['tag_option_value'] = $commaSepTagAnswersValue;
                        
                    }
                    elseif($v['tag_type'] == 'selected')
                    {
                        $v['tag_option'] = ((int)$v['multi_select'] == 0) ? array($v['tag_option']) : $v['tag_option'];
                        $tagAnswers = [];
                        foreach($v['tag_option'] as $multiKey => $multiValue)
                        {
                            $tagAnswers[] = (int)pk_decrypt($multiValue);
                        }
                        $commaSepTagAnswers ="";
                        if(count($tagAnswers) > 0)
                        {
                            $commaSepTagAnswers = implode("," , $tagAnswers);
                        }
                        $tag_data[$k]['tag_option'] = $commaSepTagAnswers;

                        $modalData = [
                            'module' => $v['selected_field'],
                            'ids' => $tagAnswers
                        ];
                        if(count($tagAnswers) > 0)
                        {
                            $tagOptionValue = $this->Tag_model->getNames($modalData);
                            $tag_data[$k]['tag_option_value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                        }
                        else
                        {
                            $tag_data[$k]['tag_option_value'] = '';
                        }
                    }
                    //print_r($tag_data[$k]);
                    $this->User_model->insert_data('catalogue_tags',$tag_data[$k]);
                    //echo $this->db->last_query();
                }
            }
        }
        //////////CATALOGUE TAGS//////////////

        $customer_id=$data['customer_id'];
        $path=FILE_SYSTEM_PATH.'uploads/';
        $catalogue_documents=array();
        if(!is_dir($path.$customer_id)){ mkdir($path.$customer_id); }
        if(isset($_FILES) && $totalFilesCount>0)
        {
            $i_attachment=0;
            for($i_attachment=0; $i_attachment<$totalFilesCount; $i_attachment++) {
                $imageName = doUpload(array(
                    'temp_name' => $_FILES['file']['tmp_name'][$i_attachment],
                    'image' => $_FILES['file']['name'][$i_attachment],
                    'upload_path' => $path,
                    'folder' => $customer_id));
                $catalogue_documents[$i_attachment]['module_id'] = $id_catalogue;
                $catalogue_documents[$i_attachment]['module_type']='catalogue';
                $catalogue_documents[$i_attachment]['reference_id']=$id_catalogue;
                $catalogue_documents[$i_attachment]['reference_type']='catalogue';
                $catalogue_documents[$i_attachment]['document_name']=$_FILES['file']['name'][$i_attachment];
                $catalogue_documents[$i_attachment]['document_type'] = 0;
                $catalogue_documents[$i_attachment]['document_source']=$imageName;
                $catalogue_documents[$i_attachment]['document_mime_type']=$_FILES['file']['type'][$i_attachment];
                $catalogue_documents[$i_attachment]['document_status']=1;
                $catalogue_documents[$i_attachment]['uploaded_by']=$this->session_user_id;
                $catalogue_documents[$i_attachment]['uploaded_on']=currentDate();
            }
        }
        //print_r($catalogue_documents);
        if(count($catalogue_documents)>0){
            $this->Document_model->addBulkDocuments($catalogue_documents);
        }
        $catalogue_documents = array();
        if(isset($data['links']))
            foreach($data['links'] as $k => $v){
                $catalogue_documents[$k]['module_id'] =$id_catalogue;
                $catalogue_documents[$k]['module_type'] = 'catalogue';
                $catalogue_documents[$k]['reference_id'] = $id_catalogue;
                $catalogue_documents[$k]['reference_type'] = 'catalogue';
                $catalogue_documents[$k]['document_name'] = $v['title'];
                $catalogue_documents[$k]['document_type'] = 1;
                $catalogue_documents[$k]['document_source'] = $v['url'];
                $catalogue_documents[$k]['document_mime_type'] = 'URL';
                $catalogue_documents[$k]['uploaded_by'] = $this->session_user_id;
                $catalogue_documents[$k]['uploaded_on'] = currentDate();
                $catalogue_documents[$k]['updated_on'] = currentDate();
            }
        if(count($catalogue_documents)>0){
            $this->Document_model->addBulkDocuments($catalogue_documents);
        }
        
        $result = array('status'=>TRUE, 'message' => $this->lang->line('catalogue_add'), 'data'=>'','catalogue_id'=>pk_encrypt($id_catalogue));
        
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(isset($data['catalogue'])){
            $data = $data['catalogue'];
        }
   
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));

        $this->form_validator->add_rules('currency_id', array('required'=>$this->lang->line('currency_id_req')));
 
        $this->form_validator->add_rules('catalogue_name', array('required'=>$this->lang->line('catalogue_name_req')));
       
        $this->form_validator->add_rules('description', array('required'=>$this->lang->line('catalogue_description_req')));
      
        //$this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));

        $this->form_validator->add_rules('status', array('required'=>$this->lang->line('status_required')));

        $this->form_validator->add_rules('id_catalogue', array('required'=>$this->lang->line('id_catalogue_req')));
        

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    
        if(isset($data['id_catalogue'])) {
            $data['id_catalogue'] = pk_decrypt($data['id_catalogue']);
        }
      
        
        
       
        if(!empty($data['currency_id'])) {
            $data['currency_id'] = pk_decrypt($data['currency_id']);
            if(!in_array($data['currency_id'],$this->session_user_master_currency)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'7');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        // if(isset($data['updated_by'])) {
        //     $data['updated_by'] = pk_decrypt($data['updated_by']);
        //     if($data['updated_by']!=$this->session_user_id){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'9');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }

        if($this->session_user_info->user_role_id>2){
            if((($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_catalogue == 0)) || ($this->session_user_info->user_role_id > 4))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
           
        }
      
        
        $update = array(
            'catalogue_name' => $data['catalogue_name'],
            'status' => (isset($data['status']) && $data['status'] == "Active") ? 1 : 0,
            'description' => isset($data['description'])?$data['description']:'',
            'currency_id' => isset($data['currency_id'])?$data['currency_id']:'',
            'updated_by' => $this->session_user_id,
            'updated_on' => currentDate(),
        );
    
        $this->User_model->update_data('catalogue',$update,array('id_catalogue' => $data['id_catalogue']));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('catalogue_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function catalogueTags_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_catalogue', array('required'=>$this->lang->line('id_catalogue_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(count($this->session_user_info)==0)
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        //user access to catalogue
        // if($this->session_user_info->user_role_id>2){
        //     if((($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_catalogue == 0)) || ($this->session_user_info->user_role_id > 4))
        //     {
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        $data['id_catalogue'] = pk_decrypt($data['id_catalogue']);
        $master_tags = $this->Tag_model->TagList(array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'tag_type'=>'catalogue_tags'));
        $tag_data = $this->Tag_model->getCatalogueTags(array('catalogue_id'=>$data['id_catalogue']));
        $is_rag_exists = false;
        
        foreach($master_tags as $k => $v){
            $tag_result[$k]['tag_text'] = $v['tag_text'];
            $tag_result[$k]['tag_type'] = $v['tag_type'];
            $tag_result[$k]['field_type'] = $v['field_type'];
            $tag_result[$k]['tag_option'] = 0;
            $tag_result[$k]['tag_answer'] = '';
            $tag_result[$k]['id_catalogue_tag'] = 0;
            $tag_result[$k]['business_unit_id'] = pk_encrypt($v['business_unit_id']);
            $tag_result[$k]['business_unit_status'] = $v['business_unit_status'];
            $tag_result[$k]['bu_name'] = $v['bu_name'];
            $tag_result[$k]['selected_field'] = $v['selected_field'];
            $tag_result[$k]['multi_select'] = $v['multi_select'];
            $tag_result[$k]['options'] = $this->Tag_model->getContractTagoptions(array('tag_id'=>$v['id_tag'])); //getting catalogue tag options

            if($v['tag_type'] == 'rag')
            {
                $is_rag_exists = true;
            }
            if(isset($tag_data[$k]) && $tag_data[$k]['id_tag'] == $v['id_tag']){
                //If catalogue tag exists
                $tag_result[$k]['id_catalogue_tag'] = pk_encrypt($tag_data[$k]['id_catalogue_tag']);
                if((int)$v['multi_select'] == 1)
                {
                    if((int)$tag_data[$k]['tag_option'] == 0){
                        //If catalogue tag is of date or input type
                        $tag_result[$k]['tag_option'] = $tag_data[$k]['tag_option'];
                        $tag_result[$k]['tag_answer'] = !is_null($tag_data[$k]['tag_answer'])?$tag_data[$k]['tag_answer']:'';
                    }
                    else{
                        $tag_option = [];
                        $tag_option = explode(",",$tag_data[$k]['tag_option']) ; 
                        $tag_result[$k]['tag_option'] = [];
                        $tag_result[$k]['tag_answer'] = [];
                        $tag_result[$k]['selectedOption'] = [];
                        if($v['tag_type'] == "selected")
                        {
                            $SelectTag =array('module' => $v['selected_field'] , 'ids' =>$tag_option , 'clickable' => True ,'userroleId' => $this->session_user_info->user_role_id , 'userId' => $this->session_user_id);
                            $selectedOption = $this->Tag_model->getSelectName($SelectTag);
                            foreach($selectedOption as $selectedOptionkey =>$selectedOptionValue)
                            {
                                $selectedOption[$selectedOptionkey]['id'] = pk_encrypt($selectedOption[$selectedOptionkey]['id']);
                            }
                            $tag_result[$k]['selectedOption'] = $selectedOption;
                        }
                        $tagAnswerDisplayarray = [];
                        $tagAnswerDisplay = '';
                        foreach($tag_option as $optionDetails)
                        {
                            if($v['tag_type'] == "dropdown")
                            {
                                $key = array_search($optionDetails, array_column($tag_result[$k]['options'], 'id_tag_option'));
                                $tagAnswerDisplayarray[] = $tag_result[$k]['options'][$key]['tag_option_name'];
                            }
                            $tag_result[$k]['tag_option'][] = pk_encrypt($optionDetails);
                            $tag_result[$k]['tag_answer'][] = pk_encrypt($optionDetails);
                        }    
                        $tagAnswerDisplay = implode(",",$tagAnswerDisplayarray);  
                        $tag_result[$k]['tagAnswerDisplay'] = $tagAnswerDisplay;       
                    }
                }
                else
                {
                    if((int)$tag_data[$k]['tag_option'] == 0){
                        //If catalogue tag is of date or input type
                        $tag_result[$k]['tag_option'] = $tag_data[$k]['tag_option'];
                        //$tag_result[$k]['tag_answer'] = !empty($tag_data[$k]['tag_answer'])?$tag_data[$k]['tag_answer']:'';
                        $tag_result[$k]['tag_answer'] = !is_null($tag_data[$k]['tag_answer'])?$tag_data[$k]['tag_answer']:'';
                        $tag_result[$k]['tagAnswerDisplay'] = !is_null($tag_data[$k]['tag_answer'])?$tag_data[$k]['tag_answer']:'';
                    }
                    else{
                        $tag_result[$k]['tag_option'] = pk_encrypt($tag_data[$k]['tag_option']);
                        $tag_result[$k]['tag_answer'] = pk_encrypt($tag_data[$k]['tag_answer']);
                        if($v['tag_type'] == "dropdown" || $v['tag_type'] == "radio" ||$v['tag_type'] == "rag")
                        {
                            $key = array_search($tag_data[$k]['tag_option'], array_column($tag_result[$k]['options'], 'id_tag_option'));
                            $tag_result[$k]['tagAnswerDisplay'] = $tag_result[$k]['options'][$key]['tag_option_name'];
                            
                        }
                        elseif($v['tag_type'] == "input" || $v['tag_type'] == "date")
                        {
                            $tag_result[$k]['tagAnswerDisplay'] = !is_null($tag_data[$k]['tag_answer'])?$tag_data[$k]['tag_answer']:'';  
                        }
                        elseif($v['tag_type'] == "selected")
                        {
                            $SelectTag =array('module' => $v['selected_field'] , 'ids' =>explode(",",$tag_data[$k]['tag_option']) , 'clickable' => True ,'userroleId' => $this->session_user_info->user_role_id , 'userId' => $this->session_user_id);
                            
                            $selectedOption = $this->Tag_model->getSelectName($SelectTag);
                            foreach($selectedOption as $selectedOptionkey =>$selectedOptionValue)
                            {
                                $selectedOption[$selectedOptionkey]['id'] = pk_encrypt($selectedOption[$selectedOptionkey]['id']);
                            }
                            $tag_result[$k]['selectedOption'] = $selectedOption;
                        }
                    }
                }
                if($v['tag_type'] == "rag")
                {

                    $tag_result[$k]['comments'] = $tag_data[$k]['comments'];
                }
               
            }
            foreach($tag_result[$k]['options'] as $k1 => $v1)
            {
                $tag_result[$k]['options'][$k1]['id_tag_option'] = pk_encrypt($v1['id_tag_option']);
            }
            
            $tag_result[$k]['tag_id'] = pk_encrypt($v['id_tag']);
            $tag_result[$k]['tag_order'] =$v['tag_order'];
        }
        $new_result = array();
        usort($tag_result, function ($item1, $item2) {
            return $item1['tag_order'] <=> $item2['tag_order'];
        });

        //grouping tags with business unit

        $groupTag = [];
        $businessUnitArray = array_unique(array_column($tag_result, 'business_unit_id'));

        $i=0;
        foreach($businessUnitArray as $buK=>$buV)
        {
            $groupTag[$i] = array(
                'business_unit_id' => $buV,
                'tag_details' =>[],
                'count' => 0,
                'count_without_rag' => 0
            );
            $count_without_rag = 0;
            foreach($tag_result as $tagK => $tagV)
            {
                if($tagV['business_unit_id'] == $buV)
                {
                    $groupTag[$i]['tag_details'][]=$tag_result[$tagK];
                    $groupTag[$i]['bu_name']=$tag_result[$tagK]['bu_name'];
                    $groupTag[$i]['status']=$tag_result[$tagK]['business_unit_status'];

                    if($tag_result[$tagK]['tag_type'] != 'rag')
                    {
                        $count_without_rag++;
                    }
                }
            }

            $groupTag[$i]['count'] = count($groupTag[$i]['tag_details']);
            $groupTag[$i]['count_without_rag'] = $count_without_rag;
            $i++;
        }
      
        $columns = array_column($groupTag, 'bu_name');
        array_multisort($columns, SORT_ASC, $groupTag);
        $result = array('status'=>TRUE, 'message'=>$this->lang->line('success'), 'data'=>$groupTag,'is_rag_exists'=>$is_rag_exists);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function catalogueTagsUpdate_post(){
        $data = $this->input->post();
   
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_catalogue', array('required'=>$this->lang->line('id_catalogue_req')));
        $this->form_validator->add_rules('catalogue_tags', array('required'=>$this->lang->line('tag_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_catalogue'])) {
             $data['id_catalogue'] = pk_decrypt($data['id_catalogue']);
        }

        $data['grouped_tags']=$data['catalogue_tags'];
        $data['updated_by']=$this->session_user_id;

        if(isset($data['grouped_tags']) && count($data['grouped_tags'])>0)
        {
            foreach($data['grouped_tags'] as $groupKey => $groupValue)
            {
                $data['catalogue_tags'] = $groupValue['tag_details'] ; 
                if(isset($data['catalogue_tags']) && count($data['catalogue_tags'])>0){
                    $tag_data = array();
                    foreach($data['catalogue_tags'] as $k => $v){
                        foreach($v['options'] as $k1 => $v1)
                        {
                            $data['catalogue_tags'][$k]['options'][$k1]['id_tag_option'] = pk_decrypt($v1['id_tag_option']);
                        } 
                        $data['catalogue_tags'][$k]['id_catalogue_tag'] = (int)pk_decrypt($v['id_catalogue_tag']);
                        $data['catalogue_tags'][$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
                        if($v['tag_type'] != 'selected')
                        {
                            if($v['tag_type'] == 'dropdown' && (int)$v['multi_select'] == 1 && (!empty($v['tag_answer'])))
                            {
                                if(!empty($v['tag_answer']))
                                {
                                    $tagAnswers = [];
                                    $tagOptionValue = [];
                                    foreach($data['catalogue_tags'][$k]['tag_answer'] as $multiDropKey => $multiDropValue)
                                    {
                                        foreach($data['catalogue_tags'][$k]['options'] as $option)
                                        {
                                            if($option['id_tag_option'] == (int)pk_decrypt($multiDropValue))
                                            {
                                                $tagOptionValue[] = $option['tag_option_name'];
                                            }
                                        }
                                        $tagAnswers[] = (int)pk_decrypt($multiDropValue);
                                    }
                                    $commaSepTagAnswers = "";
                                    $commaSepTagAnswersValue = "";
                                    if(count($tagAnswers) > 0)
                                    {
                                        $commaSepTagAnswers = implode("," , $tagAnswers);
                                    }
                                    if(count($tagOptionValue) > 0)
                                    {
                                        $commaSepTagAnswersValue = implode("," , $tagOptionValue);
                                    }
                                    $data['catalogue_tags'][$k]['tag_option'] = $commaSepTagAnswers;
                                    $data['catalogue_tags'][$k]['tag_option_value'] = $commaSepTagAnswersValue;

                                }
                                else
                                {
                                    $data['catalogue_tags'][$k]['tag_option'] = 0 ;
                                    $data['catalogue_tags'][$k]['tag_option_value'] = Null;
                                }
                            }
                            else
                            {
                                if((int)pk_decrypt($v['tag_option']) > 0){
                                    $data['catalogue_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                                    if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
                                        $data['catalogue_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
                                }else{
                                    $data['catalogue_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                                    if(!(int)pk_decrypt($v['id_catalogue_tag']) > 0){
                                        if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
                                            $data['catalogue_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
                                    }
                                }
                                    
                                if($v['tag_type'] == 'input' || $v['tag_type'] == 'date'){
                                    $data['catalogue_tags'][$k]['tag_option_value'] = $data['catalogue_tags'][$k]['tag_answer'];
                                }else{
                
                                    foreach($data['catalogue_tags'][$k]['options'] as $k2 => $v2){
                                        $data['catalogue_tags'][$k]['tag_option'] = null;
                                        $data['catalogue_tags'][$k]['tag_option_value'] = null;
                                        if(pk_decrypt($v['tag_answer']) == $v2['id_tag_option']){
                                            $data['catalogue_tags'][$k]['tag_option'] = $v2['id_tag_option'];
                                            $data['catalogue_tags'][$k]['tag_option_value'] = $v2['tag_option_name'];
                                            break;
                                        }
                                    }
                                }  
                            }
                        }
                        elseif($v['tag_type'] == 'selected')
                        {
                            if(!empty($v['tag_answer']))
                            {
                                $v['tag_answer'] = ((int)$v['multi_select'] == 0) ? array($v['tag_answer']) : $v['tag_answer'];
                    
                                $tagAnswers = [];

                                foreach($v['tag_answer'] as $multiKey => $multiValue)
                                {
                                    $tagAnswers[] = (int)pk_decrypt($multiValue);
                                }
                                $commaSepTagAnswers ="";
                                if(count($tagAnswers) > 0)
                                {
                                    $commaSepTagAnswers = implode("," , $tagAnswers);
                                }
                                else
                                {
                                    $commaSepTagAnswers = 0;
                                }
                                $data['catalogue_tags'][$k]['tag_option'] = $commaSepTagAnswers;

                                $modalData = [
                                    'module' => $v['selected_field'],
                                    'ids' => $tagAnswers
                                ];
                                $tagOptionValue = $this->Tag_model->getNames($modalData);
                                $data['catalogue_tags'][$k]['tag_option_value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                            }
                            else
                            {
                                $data['catalogue_tags'][$k]['tag_option'] = 0 ;
                                $data['catalogue_tags'][$k]['tag_option_value'] = Null;
                            }
                        }
                        $tag_data = array(
                            'tag_option' => $data['catalogue_tags'][$k]['tag_option'],
                            'tag_option_value' => $data['catalogue_tags'][$k]['tag_option_value'],
                            'catalogue_id' => $data['id_catalogue'],
                            'tag_id' => $data['catalogue_tags'][$k]['tag_id'],
                            'comments' => $data['catalogue_tags'][$k]['comments']
                        );
                        if(isset($v['id_catalogue_tag']) && (int)pk_decrypt($v['id_catalogue_tag']) > 0){
                            //Update
                            $tag_data['updated_on'] = currentDate();
                            $tag_data['updated_by'] = $this->session_user_id;
                            $this->User_model->update_data('catalogue_tags',$tag_data,array('id_catalogue_tag'=>$data['catalogue_tags'][$k]['id_catalogue_tag']));
                        }else{
                            //Insert
                            $tag_data['created_on'] = currentDate();
                            $tag_data['created_by'] = $this->session_user_id;
                            $this->User_model->insert_data('catalogue_tags',$tag_data);
                        }
                    }
                        
                }
            }
            $result = array('status'=>TRUE, 'message' => $this->lang->line('catalogue_tags_update'), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);   
        }
        else{
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
             
    }

    public function catalogue_delete()
    {
        $data = $this->input->get();
   
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_catalogue', array('required'=>$this->lang->line('id_catalogue_req')));
  
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if($this->session_user_info->user_role_id>2){

            if((($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && ($this->session_user_info->content_administator_catalogue == 0)) || ($this->session_user_info->user_role_id > 4))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
           
        }

        if(isset($data['id_catalogue'])) {
            $data['id_catalogue'] = pk_decrypt($data['id_catalogue']);
        }

        $update = array(
            'is_deleted' => 1,
            'updated_by' => $data['updated_by'],
            'updated_on' => currentDate(),
        );
    
        //echo  $data['id_catalogue'];
        $this->User_model->update_data('catalogue',$update,array('id_catalogue' => $data['id_catalogue']));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('catalogue_deleted'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function list_get()
    {
        $data = $this->input->get();
        // if(empty($data)){
        //     $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
        //     $this->response($result, REST_Controller::HTTP_OK);
        // }
        $data['customer_id'] = $this->session_user_info->customer_id;
        if(isset($data['id_catalogue']))
        {
            $data['id_catalogue'] = pk_decrypt($data['id_catalogue']);
        }
        $data = tableOptions($data);
        /////////////////// advanced filters start//////////////////
        if(isset($data['is_advance_filter']) && $data['is_advance_filter'] == 1)
        {
            $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_catalogue_list','is_union_table'=>0));
            $data['adv_filters']=$get_filters;
            $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_catalogue_list','is_union_table'=>1));
            // echo $this->db->last_query();exit;
            $data['adv_union_filters']=$get_union_filters;
        }
        /////////////////// advanced filters end//////////////////
        $catalogueData = $this->Catalogue_model->list($data);
        $result  = $catalogueData['data'];
        $count =$catalogueData['total_records'];
        foreach($result as $k=>$val)
        {
            //for time opimization  we are providing attachments only for view catalogue only
            if(isset($data['id_catalogue']))
            {
                $inner_data=array();
                $inner_data['reference_id']=$val['id_catalogue'];
                $inner_data['reference_type']='catalogue';
                $inner_data['module_type']='catalogue';
                $inner_data['module_id']=$val['id_catalogue'];
                $inner_data['document_status']=1;
                
                $result[$k]['attachment']['documents'] = $this->Document_model->getDocumentsList($inner_data);
                $inner_data['document_type'] = array(0,1);
                $result[$k]['attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
                $inner_data['document_type'] = 1;
                $result[$k]['attachment']['links'] = $this->Document_model->getDocumentsList($inner_data);

                foreach($result[$k]['attachment']['all_records'] as $ka=>$va){
                    $result[$k]['attachment']['all_records'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$k]['attachment']['all_records'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result[$k]['attachment']['all_records'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['id_document']);
                    $result[$k]['attachment']['all_records'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['module_id']);
                    $result[$k]['attachment']['all_records'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['reference_id']);
                    $result[$k]['attachment']['all_records'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['uploaded_by']);
                    $result[$k]['attachment']['all_records'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['all_records'][$ka]['user_role_id']);
                }
                $result[$k]['attachment']['all_records']= array_values($result[$k]['attachment']['all_records']);
                foreach($result[$k]['attachment']['documents'] as $ka=>$va){
                    $result[$k]['attachment']['documents'][$ka]['show_icon']=false;
                    $result[$k]['attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$k]['attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result[$k]['attachment']['documents'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['id_document']);
                    $result[$k]['attachment']['documents'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['module_id']);
                    $result[$k]['attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['reference_id']);
                    $result[$k]['attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['uploaded_by']);
                    $result[$k]['attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['documents'][$ka]['user_role_id']);
                }
                $result[$k]['attachment']['documents']= array_values($result[$k]['attachment']['documents']);
                foreach($result[$k]['attachment']['links'] as $ka=>$va){
                    $result[$k]['attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$k]['attachment']['links'][$ka]['id_document']=pk_encrypt($result[$k]['attachment']['links'][$ka]['id_document']);
                    $result[$k]['attachment']['links'][$ka]['module_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['module_id']);
                    $result[$k]['attachment']['links'][$ka]['reference_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['reference_id']);
                    $result[$k]['attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[$k]['attachment']['links'][$ka]['uploaded_by']);
                    $result[$k]['attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[$k]['attachment']['links'][$ka]['user_role_id']);
                }
                $result[$k]['attachment']['links']= array_values($result[$k]['attachment']['links']);

                $CatalogueInfoColarray =array('catalogue_unique_id','catalogue_name','description','status','currency_id');

                $CatalogueinfoFilledFields =0;
                foreach ($CatalogueInfoColarray as $key => $v) {
                    if(!empty($result[$k][$v]) || $v=='status'){$CatalogueinfoFilledFields++;}
                }
                $catalogue_information =$CatalogueinfoFilledFields."/5";
                $catalogueTagesFilled =0;
                $master_tags = $this->Tag_model->TagList(array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'tag_type'=>'catalogue_tags'));
                $tag_data = $this->Tag_model->getCatalogueTags(array('catalogue_id' =>$result[$k]['id_catalogue']));
                $tag_result = array();
                if(empty($tag_data))
                {
                    $catalogue_tags ="0/".count($master_tags);
                }
                else{
                    $catalogueTagesFilled = 0;
                    foreach ($tag_data as $tagkey => $va) {
                        if(($tag_data[$tagkey]['tag_answer']!="")&&($tag_data[$tagkey]['tag_answer']!=NULL))
                        {
                            $catalogueTagesFilled++;
                        }
                    }
                    $catalogue_tags  =$catalogueTagesFilled."/".count($master_tags);
                }
                $result[$k]['catalogue_information'] = $catalogue_information;
                $result[$k]['catalogue_tags'] = $catalogue_tags;
                $result[$k]['catalogue_attachments_count'] = count($result[$k]['attachment']['all_records']);
            }
            $result[$k]['currency_id'] = pk_encrypt($result[$k]['currency_id']);
            $result[$k]['status'] = (int)$result[$k]['status'];
            $result[$k]['id_catalogue'] = pk_encrypt($result[$k]['id_catalogue']);
            //connected contracts
            $SelectTag =array('module' => 'contract' , 'ids' =>explode(",",$result[$k]['contract_ids']) , 'clickable' => True ,'userroleId' => $this->session_user_info->user_role_id , 'userId' => $this->session_user_id);
            $selectedOption = $this->Tag_model->getSelectName($SelectTag);
            foreach($selectedOption as $selectedOptionkey =>$selectedOptionValue)
            {
                $selectedOption[$selectedOptionkey]['id'] = pk_encrypt($selectedOption[$selectedOptionkey]['id']);
            }
            $result[$k]['connected_to'] = $selectedOption;
            $result[$k]['status'] = ($result[$k]['status'] == 1) ? 'Active' : 'Closed';

        }
        $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>$result,'total_records'=>$count), REST_Controller::HTTP_OK);
    }

    public function catalogueExport_get(){
        $data = $this->input->get();
        $data['customer_id'] = $this->session_user_info->customer_id;
        $data['user_role_id'] = $this->session_user_info->user_role_id;
        $data['id_user'] = $this->session_user_id;

        /////////////////// advanced filters start//////////////////
        $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_catalogue_list','is_union_table'=>0));
        $data['adv_filters']=$get_filters;
        $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_catalogue_list','is_union_table'=>1));
        // echo $this->db->last_query();exit;
        $data['adv_union_filters']=$get_union_filters;
        /////////////////// advanced filters end//////////////////
      
        $result = $this->Catalogue_model->list($data);

        //preparing headers
        
        //Geting Active tags
        $active_tags = $this->Tag_model->TagList(array('customer_id'=>$data['customer_id'],'status'=>1,'tag_type'=>'catalogue_tags'));
        $tags = array();
        for($i=0; $i<NO_OF_TAGS ;$i++){
            $tags[$i]['text']=isset($active_tags[$i])?$active_tags[$i]['tag_text']:'';
            $tags[$i]['field_type']=isset($active_tags[$i])?$active_tags[$i]['field_type']:'';
            $tags[$i]['id']=isset($active_tags[$i])?$active_tags[$i]['id_tag']:'';
        }
        $headers=array('Catalogue ID','Catalogue Name','Currency','Catalogue Description','Connected To','Status',$tags);
        
        if(isset($result['data']))
            $result = $result['data'];

        $this->load->library('excel');
        //activate worksheet number 1
        $excelRowstartsfrom=1;
        $excelColumnstartsFrom=0;
        $columnBegin =$excelColumnstartsFrom;
        $excelstartsfrom=$excelRowstartsfrom;
        //writing headers
        foreach($headers as $k=>$v){
            if(is_array($v)){ 
                foreach($v as $k1 => $v1){
                    $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v1['text']);
                    if($v1['field_type'] == 'currency')
                        $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . ($excelstartsfrom+1).':'.$this->getkey($columnBegin) . ($excelstartsfrom+1000))->getNumberFormat()->setFormatCode('_("€"* #,##0.00_);_("€"* \(#,##0.00\);_("€"* "-"??_);_(@_)');
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                        array('borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('bold'  => true,'size'=>12)));
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin))->setAutoSize(true);
                    $columnBegin++;
                }                
            }else{
                $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v);
                $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                    array('borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('bold'  => true,'size'=>12)));

                $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin))->setAutoSize(true);
                $columnBegin++;
            }
        }
        $excelstartsfrom++;

        $excel_data=array();
        //arranging data in required format
        $masterTags = $tags;
        foreach($result as $k => $v){ 
            $catalogue_tags = $this->Tag_model->getCatalogeTagsdata(array('catalogue_id'=>$v['id_catalogue'],'status'=>1,'orderBy'=>'export'));
            $tags =array();
            for($t=0;$t<NO_OF_TAGS;$t++)
            {
                $tags[] = array('id'=>'','value'=>'');
            }
            
            foreach($masterTags as $mk=>$mv)
            {
                $arrayIndex = array_search($mv['id'], array_column($catalogue_tags, 'tag_id'));
                if(gettype($arrayIndex)!="boolean")
                {
                    $tags[$mk]['id'] = $catalogue_tags[$arrayIndex]['id_tag'];
                    $tags[$mk]['field_type'] = $catalogue_tags[$arrayIndex]['tag_type'];

                    if($catalogue_tags[$arrayIndex]['tag_type'] == "input" ||$catalogue_tags[$arrayIndex]['tag_type'] == "rag" || $catalogue_tags[$arrayIndex]['tag_type'] == "radio" || ($catalogue_tags[$arrayIndex]['tag_type'] == "dropdown" && $catalogue_tags[$arrayIndex]['multi_select'] == 0)){

                        $tags[$mk]['value'] = $catalogue_tags[$arrayIndex]['tag_option_value'];
                    }
                    elseif($catalogue_tags[$arrayIndex]['tag_type'] == "date" && !empty($catalogue_tags[$arrayIndex]['tag_option_value'])){
                        $tags[$mk]['value']=date_format(date_create($catalogue_tags[$arrayIndex]['tag_option_value']),"M d,Y");
                        $tags[$mk]['field_type'] = 'date';
                    }
                    elseif($catalogue_tags[$arrayIndex]['tag_type'] == "dropdown" && $catalogue_tags[$arrayIndex]['multi_select'] == 1)
                    {
                        $explodedData = [];
                        if(!empty($catalogue_tags[$arrayIndex]['tag_option']))
                        {
                            $explodedData = explode(",",$catalogue_tags[$arrayIndex]['tag_option']);
                            $tagAnswers = $this->Contract_model->TagAnswer(array('id_contract_tag' => $catalogue_tags[$arrayIndex]['id_contract_tag'] , 'explodedData' => $explodedData));
                            $tags[$mk]['value'] = !empty($tagAnswers) ? $tagAnswers[0]['tag_option_values'] : '';
                        }
                        else
                        {
                            $tags[$mk]['value'] = '';
                        }
                    }
                    elseif($catalogue_tags[$arrayIndex]['tag_type'] == "selected")
                    {
                        $tagAnswers = explode(",",$catalogue_tags[$arrayIndex]['tag_option']);
                        $modalData = [
                            'module' => $catalogue_tags[$arrayIndex]['selected_field'],
                            'ids' => $tagAnswers
                        ];
                        $tagOptionValue = $this->Tag_model->getNames($modalData);
                        $tags[$mk]['value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                    }
                }
                else
                {
                    $tags[$mk]['id'] = '';
                    $tags[$mk]['field_type'] = '';
                    $tags[$mk]['value'] = '';
                }
            }
               
            $excel_data[$k]['catalogue_unique_id']=$v['catalogue_unique_id'];
            $excel_data[$k]['catalogue_name']=$v['catalogue_name'];
            $excel_data[$k]['currency_name']=$v['currency_name'];
            $excel_data[$k]['description']=$v['description'];
            $excel_data[$k]['connected_contracts']=str_replace(",",";",$v['contract_names']);
            $excel_data[$k]['status']=($v['status'] == 1) ? 'Active' : 'Closed';
            $excel_data[$k]['tags']=$tags;
        }
        ///writing data row by row
        foreach($excel_data as $k => $v){
            $columnBegin =$excelColumnstartsFrom;
            $catalogueCurrencyCode = "";
            $catalogueCurrencyCode =  $v['currency_name'];
            // unset($v['currency_name']);
            foreach($v as $key => $v1){ 
                if(is_array($v1)){
                    foreach($v1 as $v2){    
                        if($v2['field_type'] == 'number'){
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,number_format($v2['value'],0));
                            $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                                array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),'font'  => array('size'=>12)));
                        }
                        elseif($v2['field_type'] == 'date'){
                            $format = 'mmm d,YYYY';

                            if(!empty($v2['value']))
                            {
                                $date = new DateTime($v2['value']);
                                $dateVal = PHPExcel_Shared_Date::PHPToExcel($date);
                            }
                            else{
                                $date = '';
                                $dateVal = '';
                            }
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$dateVal);
                            $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->getNumberFormat()->setFormatCode($format);
                         }
                        elseif($v2['field_type'] == 'currency'){
                           $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v2['value']);
                           $symbol =CurrencySymbol($catalogueCurrencyCode);
                           $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->getNumberFormat()->setFormatCode('_("'.$catalogueCurrencyCode.' '.'"* #,##0.00_);_("'.$catalogueCurrencyCode.' '.'"* (#,##0.00);_("'.$catalogueCurrencyCode.' '.'"* "-"??_);_(@_)');
                            $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                                array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('size'=>12)));
                        }else{
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,str_replace(",",";",$v2['value']));
                            $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                                array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('size'=>12)));
                        }
                        $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin))->setAutoSize(true);
                        $columnBegin++;
                    }
                }else{
                    $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v1);
                    $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                        array('borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('size'=>12)));
                    $this->excel->getActiveSheet()->getColumnDimension($this->getkey($columnBegin))->setAutoSize(true);
                    $columnBegin++;
                }
            } 
            $excelstartsfrom++;
        }
        $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom).$excelRowstartsfrom.':'.$this->getkey($columnBegin).$excelstartsfrom)->getAlignment()->setWrapText(true);



        $this->excel->getActiveSheet()->setSelectedCells('A0');
        //activate worksheet number 1
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('All Catalogue List');
        $filename = 'all_catalogue_'.date("d-m-Y",strtotime(currentDate())).'.xls';
        // echo $filename;exit;//save our workbook as this file name
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $file_path = FILE_SYSTEM_PATH.'downloads/' . $filename;
        $objWriter->save($file_path);
        $view_path='downloads/' . $filename;
        $file_path = REST_API_URL.$view_path;
        $file_path = str_replace('::1','localhost',$file_path);
        $insert_id = $this->Download_model->addDownload(array('path'=>$view_path,'filename'=>$filename,'user_id'=>$this->session_user_info->id_user,'access_token'=>substr($_SERVER['HTTP_AUTHORIZATION'],7),'status'=>0,'created_date_time'=>currentDate()));
        $response = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>pk_encrypt($insert_id));
        $this->response($response, REST_Controller::HTTP_OK);

    }

    function getkey($pos){
        //this function used to return ascii value based on position used int export_get function
        $numeric = $pos % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($pos / 26);
        if ($num2 > 0) {
            return $this->getkey($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }


    public function exchangeRate_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('base_currency_code', array('required'=>$this->lang->line('base_currency_code_req')));
        $this->form_validator->add_rules('convertable_currency_code', array('required'=>$this->lang->line('convertable_currency_code_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $customer_id = $this->session_user_info->customer_id;

        $maincurrency = $this->User_model->check_record('currency',array('customer_id' => $customer_id ,'is_maincurrency' => 1 , 'is_deleted' => 0));

        $basecurrency = $this->User_model->check_record('currency',array('customer_id' => $customer_id ,'currency_name' => $data['base_currency_code'] , 'is_deleted' => 0));

        $convertablecurrency = $this->User_model->check_record('currency',array('customer_id' => $customer_id ,'currency_name' => $data['convertable_currency_code'] , 'is_deleted' => 0));
        if(!empty($basecurrency) && !empty($convertablecurrency))
        {
            //converting basecurrency to convertablecurrency
            if($convertablecurrency[0]['currency_name'] == $basecurrency[0]['currency_name'])
            {
                $result = array(
                    "base_currency" => $basecurrency[0]['currency_name'],
                    "converable_currency" =>$convertablecurrency[0]['currency_name'],
                    "exchange_rate" => 1
                );
            }
            elseif($convertablecurrency[0]['is_maincurrency'] == 1)
            {
                $result = array(
                    "base_currency" => $basecurrency[0]['currency_name'],
                    "converable_currency" =>$convertablecurrency[0]['currency_name'],
                    "exchange_rate" => (float)number_format((float)str_replace(",",".",$basecurrency[0]['euro_equivalent_value']), 4, '.', '')
                );
            }
            else
            {
                //in database euro_equivalent_value is 0 for mainCurrency so changeing it to 1
                $convertablecurrency[0]['euro_equivalent_value'] = ($convertablecurrency[0]['is_maincurrency'] == 1) ? 1 : $convertablecurrency[0]['euro_equivalent_value'];
                $basecurrency[0]['euro_equivalent_value'] = ($basecurrency[0]['is_maincurrency'] == 1) ? 1 : $basecurrency[0]['euro_equivalent_value'];
                
                $exchangeRate = (float)str_replace(",",".",$basecurrency[0]['euro_equivalent_value']) /(float)str_replace(",",".",$convertablecurrency[0]['euro_equivalent_value']) ;

                $result = array(
                    "base_currency" => $basecurrency[0]['currency_name'],
                    "converable_currency" =>$convertablecurrency[0]['currency_name'],
                    "exchange_rate" => (float)number_format((float)$exchangeRate, 4, '.', '')
                );

            }
            

            $this->response(array('status'=>TRUE,'message'=>$this->lang->line('success'),'data' =>  $result), REST_Controller::HTTP_OK);

        }
        else
        {
            $this->response(array('status'=>FALSE,'message'=>$this->lang->line('currencys_not_found')), REST_Controller::HTTP_OK);
        }

        






    }
        
    
}