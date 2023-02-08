<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Customer extends REST_Controller
{
    public $user_id = 0 ;
    public $session_user_id=NULL;
    public $session_user_info=NULL;
    public $session_user_business_units=NULL;
    public $session_user_business_units_user=NULL;
    public $session_user_contracts=NULL;
    public $session_user_contract_reviews=NULL;
    public $session_user_contract_documents=NULL;
    public $session_user_contract_action_items=NULL;
    public $session_user_delegates=NULL;
    public $session_user_contributors=NULL;
    public $session_user_reporting_owners=NULL;
    public $session_user_bu_owners=NULL;
    public $session_user_customer_admins=NULL;
    public $session_user_customer_all_users=NULL;
    public $session_user_customer_relationship_categories=NULL;
    public $session_user_customer_relationship_classifications=NULL;
    public $session_user_customer_calenders=NULL;
    public $session_user_master_currency=NULL;
    public $session_user_master_language=NULL;
    public $session_user_master_countries=NULL;
    public $session_user_master_templates=NULL;
    public $session_user_master_customers=NULL;
    public $session_user_master_users=NULL;
    public $session_user_master_user_roles=NULL;
    public $session_user_contract_review_modules=NULL;
    public $session_user_contract_review_topics=NULL;
    public $session_user_contract_review_questions=NULL;
    public $session_user_contract_review_question_options=NULL;
    public $session_user_wadmin_relationship_categories=NULL;
    public $session_user_wadmin_relationship_classifications=NULL;
    public $session_user_wadmin_email_templates=NULL;
    public $session_user_customer_email_templates=NULL;
    public $session_user_customer_providers=NULL;
    public $session_user_own_business_units=NULL;
    public $session_user_review_business_units=NULL;
    public function __construct()
    {
        parent::__construct();
        if(isset($_SERVER['HTTP_USER'])){
            $this->user_id = pk_decrypt($_SERVER['HTTP_USER']);
        }
        $this->load->model('Validation_model');
        $this->load->model('Download_model');
        $this->load->model('Project_model');
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        //$this->User_model->check_record('calender',array('is_workflow'=>0,'auto_initiate'=>1,'month(date)'=>date('m'),'year(date)'=>date('Y')));
        //echo '<pre>'.
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));

        //api access check 
        if($this->session_user_info->user_role_id == 7)
        {
            $apiaccess = Apiaccess($this->session_user_info->user_role_id , $_SERVER['PATH_INFO']);
            if(!$apiaccess)
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')));
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }


        if($this->session_user_info->user_role_id<3 || $this->session_user_info->user_role_id==5)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
        else if($this->session_user_info->user_role_id==3 || $this->session_user_info->user_role_id==4 || $this->session_user_info->user_role_id==8)
            $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        else if($this->session_user_info->user_role_id==6){
            if($this->session_user_info->is_allow_all_bu==1)
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitList(array('customer_id'=>$this->session_user_info->customer_id));
            else
                $this->session_user_business_units=$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$this->session_user_info->id_user));
        }
        $this->session_user_own_business_units=$this->session_user_business_units;
        $this->session_user_review_business_units=$this->Validation_model->getReviewBusinessUnits(array('id_user'=>$this->session_user_id));
        if($this->session_user_info->user_role_id!=7)
            $this->session_user_business_units=array_merge($this->session_user_business_units,$this->session_user_review_business_units);
        if($this->session_user_info->user_role_id==5)
            $this->session_user_contracts=$this->Validation_model->getContributorContract(array('business_unit_id'=>$this->session_user_business_units,'customer_user'=>$this->session_user_info->id_user));
        else
            $this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units));
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        // $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        // $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        $this->session_user_customer_all_users=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_master_countries=$this->Validation_model->getCountries();
        // $this->session_user_master_templates=$this->Validation_model->getTemplates();
        $this->session_user_master_customers=$this->Validation_model->getCustomers();
        $this->session_user_master_users=$this->Validation_model->getUsers();
        // $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();

        //echo '$this->session_user_id'.$this->session_user_id;
        // $this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
    }

    public function list_get()
    {
        $data = $this->input->get();
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $result = $this->Customer_model->customerList($data);
        for($s=0;$s<count($result['data']);$s++)
        {
            $ldap = $this->User_model->check_record('customer_ldap',array('customer_id'=>$result['data'][$s]['id_customer'],'status'=>1));
            $sso = $this->User_model->check_record('customer_saml',array('customer_id'=>$result['data'][$s]['id_customer'],'status'=>1));
            $result['data'][$s]['is_ldap_or_saml_active'] = (!empty($ldap) || !empty($sso))?"1":"0";
            /*getImageUrl helper function for getting image usrl*/
            $result['data'][$s]['company_logo'] = getImageUrl($result['data'][$s]['company_logo'],'company',SMALL_IMAGE);
            $result['data'][$s]['country_id'] = pk_encrypt($result['data'][$s]['country_id']);
            $result['data'][$s]['created_by'] = pk_encrypt($result['data'][$s]['created_by']);
            $result['data'][$s]['id_customer'] = pk_encrypt($result['data'][$s]['id_customer']);
            $result['data'][$s]['template_id'] = pk_encrypt($result['data'][$s]['template_id']);
            $result['data'][$s]['updated_by'] = pk_encrypt($result['data'][$s]['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function info_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_customer', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_customer'])) {
            $data['id_customer'] = pk_decrypt($data['id_customer']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['id_customer']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Customer_model->getCustomer($data);
        for($s=0;$s<count($result);$s++)
        {
            $result[$s]['company_logo_medium'] = getImageUrl($result[$s]['company_logo'],'company',MEDIUM_IMAGE);
            $result[$s]['company_logo_small'] = getImageUrl($result[$s]['company_logo'],'company',SMALL_IMAGE);
            $result[$s]['company_logo'] = getImageUrl($result[$s]['company_logo'],'company','');
            $result[$s]['country_id'] = pk_encrypt($result[$s]['country_id']);
            $result[$s]['created_by'] = pk_encrypt($result[$s]['created_by']);
            $result[$s]['id_customer'] = pk_encrypt($result[$s]['id_customer']);
            $result[$s]['template_id'] = pk_encrypt($result[$s]['template_id']);
            $result[$s]['updated_by'] = pk_encrypt($result[$s]['updated_by']);
            $result[$s]['import_subscription'] = (int)$result[$s]['import_subscription'];
            $result[$s]['primary_language_id']  = null ;
            $result[$s]['primary_language']  = null ;
            $result[$s]['secondary_languages_id']  = null ;
            $result[$s]['secondary_languages']  = null ;
            $secondaryLanguage = null ;
            $secondaryLanguageCode = null ;
            $languages  = $this->Master_model->getUserLanguages(array('customer_id' => pk_decrypt($result[$s]['id_customer'])));
            foreach($languages as $language)
            {
               
                if($language['is_primary'] == 1)
                {
                    $result[$s]['primary_language_id']  = pk_encrypt($language['id_language']) ;
                    $result[$s]['primary_language']  = $language['language_iso_code'] ;
                }
                else
                {
                    $secondaryLanguageid[] = pk_encrypt($language['id_language']); 
                    $secondaryLanguageCode[] = ($language['language_iso_code']); 
                }
                

            }
            $result[$s]['secondary_languages_id'] = $secondaryLanguageid;
            $result[$s]['secondary_languages'] =$secondaryLanguageCode;
            
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function details_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_customer', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_customer'])) {
            $data['id_customer'] = pk_decrypt($data['id_customer']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['id_customer']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Customer_model->getCustomer($data);
        $result_array = array();
        for($s=0;$s<count($result);$s++)
        {
            $result[$s]['company_logo_medium'] = getImageUrl($result[$s]['company_logo'],'company',MEDIUM_IMAGE);
            $result[$s]['company_logo_small'] = getImageUrl($result[$s]['company_logo'],'company',SMALL_IMAGE);
            $result[$s]['company_logo'] = getImageUrl($result[$s]['company_logo'],'company','');
        }
        if(!empty($result)) {
            if (isset($result[0])) {
                $result = $result[0];
            }
            $result_array = array(
                'company_name' => $result['company_name'],
                'company_address' => $result['company_address'],
                'postal_code' => $result['postal_code'],
                'city' => $result['city'],
                'vat_number' => $result['vat_number'],
                'template_id' => pk_encrypt($result['template_id']),
                'country_id' => pk_encrypt($result['country_id']),
                'company_logo_medium' => $result['company_logo_medium'],
                'company_logo_small' => $result['company_logo_small'],
                'company_logo' => $result['company_logo'],
            );
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result_array);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function add_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(isset($data['customer'])){ $data = $data['customer']; }

        $this->form_validator->add_rules('company_name', array('required'=>$this->lang->line('company_name_req')));
        $this->form_validator->add_rules('postal_code', array('required'=>$this->lang->line('postal_code_req')));
        //$this->form_validator->add_rules('vat_number', array('required'=>$this->lang->line('vat_number_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        //$this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['template_id'])) {
            $data['template_id'] = pk_decrypt($data['template_id']);
            // if(!in_array($data['template_id'],$this->session_user_master_templates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['country_id'])) {
            $data['country_id'] = pk_decrypt($data['country_id']);
            // if(!in_array($data['country_id'],$this->session_user_master_countries)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['primary_language_id'])) {
            $data['primary_language_id'] = pk_decrypt($data['primary_language_id']);
        }
        $customer_data = array(
            'company_name' => $data['company_name'],
            'company_address' => isset($data['company_address'])?$data['company_address']:'',
            'postal_code' => $data['postal_code'],
            'import_subscription'=>$data['import_subscription'],
            'city' => isset($data['city'])?$data['city']:'',
            'vat_number' => isset($data['vat_number'])?$data['vat_number']:'',
            'country_id' => isset($data['country_id'])?$data['country_id']:'',
            'created_by' => $data['created_by'],
            'created_on' => currentDate()
        );

        $path='uploads/';
        if(isset($_FILES) && !empty($_FILES['customer']['name']['company_logo']))
        {
            $imageName = doUpload(array(
                'temp_name' => $_FILES['customer']['tmp_name']['company_logo'],
                'image' => $_FILES['customer']['name']['company_logo'],
                'upload_path' => $path,
                'folder' => ''));
            $customer_data['company_logo'] = $imageName;
        }
        else{
            unset($customer_data['company_logo']);
        }

        $customer_id = $this->Customer_model->addCustomer($customer_data);
        $this->User_model->insert_data('currency',array('is_maincurrency'=>1,'customer_id'=>$customer_id,'currency_name'=>'EUR','currency_full_name'=>'Euro','status'=>1));
        if(isset($imageName)){
            if(!is_dir($path.$customer_id)){ mkdir($path.$customer_id); }
            rename($path.$imageName, $path.$customer_id.'/'.$imageName);
            imageResize($path.$customer_id.'/'.$imageName);
            $this->Customer_model->updateCustomer(array('id_customer' => $customer_id,'company_logo' => $customer_id.'/'.$imageName));
        }

        /* updating relationship category */
        $relationship_category = $this->Relationship_category_model->RelationshipCategoryList(array('customer_id' => 0,'relationship_category_status' =>1));
        $provider_relationship_category = $this->Relationship_category_model->ProviderRelationshipCategoryList(array('customer_id' => 0,'provider_relationship_category_status' =>1));
        $relationship_category = $relationship_category['data'];
        $provider_relationship_category = $provider_relationship_category['data'];
        // print_r($provider_relationship_category);exit;

        for($s=0;$s<count($relationship_category);$s++)
        {
            $inserted_id = $this->Relationship_category_model->addRelationshipCategory(array(
                'relationship_category_quadrant' => $relationship_category[$s]['relationship_category_quadrant'],
                'relationship_category_status' => 1,
                'parent_relationship_category_id' => $relationship_category[$s]['id_relationship_category'],
                'customer_id' => $customer_id,
                'created_by' => $data['created_by'],
                'created_on' => currentDate()
            ));

            $this->Relationship_category_model->addRelationshipCategoryLanguage(array(
                'relationship_category_id' => $inserted_id,
                'relationship_category_name' => $relationship_category[$s]['relationship_category_name'],
                'language_id' => $relationship_category[$s]['language_id']
            ));

            //Adding Remainder days
            $this->User_model->insert_data('relationship_category_remainder',array('relationship_category_id'=>$inserted_id,'customer_id'=>$customer_id));
        }
        // updating provider relaionship category


        for($j=0;$j<count($provider_relationship_category);$j++)
        {
            $provider_inserted_id = $this->Relationship_category_model->addProviderRelationshipCategory(array(
                'provider_relationship_category_quadrant' => $provider_relationship_category[$j]['provider_relationship_category_quadrant'],
                'provider_relationship_category_status' => 1,
                'parent_provider_relationship_category_id' => $provider_relationship_category[$j]['id_provider_relationship_category'],
                'customer_id' => $customer_id,
                'created_by' => $data['created_by'],
                'created_on' => currentDate(),
                'can_review'=>1
            ));
            $this->Relationship_category_model->addProviderRelationshipCategoryLanguage(array(
                'provider_relationship_category_id' => $provider_inserted_id,
                'relationship_category_name' => $provider_relationship_category[$j]['relationship_category_name'],
                'language_id' => $provider_relationship_category[$j]['language_id']
            ));

            //Adding provider Remainder days for calender
            // $this->User_model->insert_data('relationship_category_remainder',array('relationship_category_id'=>$inserted_id,'customer_id'=>$customer_id));
        }

 



        /* updating relationship classification */
        $relationship_classification = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => 0,'classification_status' =>1));
        $relationship_classification = $relationship_classification['data'];
        /*$relationship_classification1 = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id_not' => 0,'classification_status' =>1));
        $relationship_classification1 = $relationship_classification1['data'];
        $relationship_classification = array_merge($relationship_classification,$relationship_classification1);*/
        for($s=0;$s<count($relationship_classification);$s++)
        {
            $parent_inserted_id = $this->Relationship_category_model->addRelationshipClassification(array(
                'classification_key' => $relationship_classification[$s]['classification_key'],
                'classification_position' => $relationship_classification[$s]['classification_position'],
                'parent_classification_id' => $relationship_classification[$s]['parent_classification_id'],
                'parent_relationship_classification_id' => $relationship_classification[$s]['id_relationship_classification'],
                'customer_id' => $customer_id,
                'is_visible' => $relationship_classification[$s]['is_visible'],
                'created_by' => $data['created_by'],
                'created_on' => currentDate()
            ));
            $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
                'relationship_classification_id' => $parent_inserted_id,
                'classification_name' => $relationship_classification[$s]['classification_name'],
                'language_id' => $relationship_classification[$s]['language_id']
            ));

            $relationship_classification1 = $this->Relationship_category_model->RelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => $relationship_classification[$s]['id_relationship_classification'],'classification_status' =>1));
            $relationship_classification1 = $relationship_classification1['data'];
            for($sr=0;$sr<count($relationship_classification1);$sr++)
            {
                $inserted_id = $this->Relationship_category_model->addRelationshipClassification(array(
                    'classification_key' => $relationship_classification1[$sr]['classification_key'],
                    'classification_position' => $relationship_classification1[$sr]['classification_position'],
                    'parent_classification_id' => $parent_inserted_id,
                    'parent_relationship_classification_id' => $relationship_classification[$s]['id_relationship_classification'],
                    'customer_id' => $customer_id,
                    'is_visible' => $relationship_classification1[$sr]['is_visible'],
                    'created_by' => $data['created_by'],
                    'created_on' => currentDate()
                ));
                $this->Relationship_category_model->addRelationshipClassificationLanguage(array(
                    'relationship_classification_id' => $inserted_id,
                    'classification_name' => $relationship_classification1[$sr]['classification_name'],
                    'language_id' => $relationship_classification1[$sr]['language_id']
                ));
            }


        }
        /* updating provider relationship classification */
        $provider_relationship_classification = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => 0,'classification_status' =>1,'withOutOrder'=>true));//echo 
        $provider_relationship_classification = $provider_relationship_classification['data'];
        for($p=0;$p<count($provider_relationship_classification);$p++)
        {
            $provider_parent_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                'classification_key' => $provider_relationship_classification[$p]['classification_key'],
                'classification_position' => $provider_relationship_classification[$p]['classification_position'],
                'parent_classification_id' => $provider_relationship_classification[$p]['parent_classification_id'],
                'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                'customer_id' => $customer_id,
                'is_visible' => $provider_relationship_classification[$p]['is_visible'],
                'created_by' => $data['created_by'],
                'created_on' => currentDate()
            ));
            $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                'provider_relationship_classification_id' => $provider_parent_inserted_id,
                'classification_name' => $provider_relationship_classification[$p]['classification_name'],
                'language_id' => $provider_relationship_classification[$p]['language_id']));
            $provider_relationship_classification1 = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => 0,'parent_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],'classification_status' =>1,'withOutOrder'=>true));
            $provider_relationship_classification1 = $provider_relationship_classification1['data'];
            // print_r($provider_relationship_classification1);exit;
             for($pr=0;$pr<count($provider_relationship_classification1);$pr++)
             {
                 $provider_class_inserted_id = $this->Relationship_category_model->addProviderRelationshipClassification(array(
                     'classification_key' => $provider_relationship_classification1[$pr]['classification_key'],
                     'classification_position' => $provider_relationship_classification1[$pr]['classification_position'],
                     'parent_classification_id' => $provider_parent_inserted_id,
                     'parent_provider_relationship_classification_id' => $provider_relationship_classification[$p]['id_provider_relationship_classification'],
                     'customer_id' => $customer_id,
                     'is_visible' => $provider_relationship_classification1[$pr]['is_visible'],
                     'created_by' => $data['created_by'],
                     'created_on' => currentDate()
                 ));
                 $this->Relationship_category_model->addProviderRelationshipClassificationLanguage(array(
                     'provider_relationship_classification_id' => $provider_class_inserted_id,
                     'classification_name' => $provider_relationship_classification1[$pr]['classification_name'],
                     'language_id' => $provider_relationship_classification1[$pr]['language_id']
                 ));
             }


        }

        /* updating email templates */
        $email_template = $this->Customer_model->EmailTemplateList(array('customer_id' => 0,'language_id' =>$data['primary_language_id'],'status'=>'0,1'));
        $email_template = $email_template['data'];

        for($s=0;$s<count($email_template);$s++)
        {
            $inserted_id = $this->Customer_model->addEmailTemplate(array(
                'module_name' => $email_template[$s]['module_name'],
                'module_key' => $email_template[$s]['module_key'],
                'wildcards' => $email_template[$s]['wildcards'],
                'email_from_name' => $email_template[$s]['email_from_name'],
                'email_from' => $email_template[$s]['email_from'],
                'status' => $email_template[$s]['status'],
                'parent_email_template_id' => $email_template[$s]['id_email_template'],
                'customer_id' => $customer_id,
                'created_by' => $data['created_by'],
                'recipients' => $email_template[$s]['recipients'],
                'created_on' => currentDate()
            ));

            $this->Customer_model->addEmailTemplateLanguage(array(
                'email_template_id' => $inserted_id,
                'template_name' => $email_template[$s]['template_name'],
                'template_subject' => $email_template[$s]['template_subject'],
                'template_content' => $email_template[$s]['template_content'],
                'language_id' => $email_template[$s]['language_id']
            ));
        }

        $this->User_model->insert_data('customer_languages',array('customer_id'=> $customer_id ,'language_id' =>  $data['primary_language_id'] , 'is_primary' => 1 , 'status' => 1 , 'created_on' => currentDate())); 
       
        if(isset($data['secondary_languages_id'])){
            $secondaryLanguagesids = $data['secondary_languages_id'];
            if(count($secondaryLanguagesids) > 0)
            {
                foreach($secondaryLanguagesids as $enclangId)
                {
                    $langId = pk_decrypt($enclangId);
                    if($langId == $data['primary_language_id'])
                    {
                        continue;
                    }
                    else
                    {
                        $this->User_model->insert_data('customer_languages',array('customer_id'=> $customer_id ,'language_id' =>  $langId , 'is_primary' => 0 , 'status' => 1 , 'created_on' => currentDate())); 
                    }
                }
            }  
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('customer_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer'])){ $data = $data['customer']; }

        $this->form_validator->add_rules('id_customer', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('company_name', array('required'=>$this->lang->line('company_name_req')));
        $this->form_validator->add_rules('postal_code', array('required'=>$this->lang->line('postal_code_req')));
        //$this->form_validator->add_rules('vat_number', array('required'=>$this->lang->line('vat_number_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        //$this->form_validator->add_rules('template_id', array('required'=>$this->lang->line('template_id_req')));
        //$this->form_validator->add_rules('company_status', array('required'=>$this->lang->line('company_status_req')));
        $validated = $this->form_validator->validate($data);
        $error = '';
        if($validated != 1)
        {
            if($error!=''){ $validated = array_merge($error,$validated); }
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_customer'])) {
            $data['id_customer'] = pk_decrypt($data['id_customer']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['id_customer']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['country_id'])) {
            $data['country_id'] = pk_decrypt($data['country_id']);
            // if(!in_array($data['country_id'],$this->session_user_master_countries)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        $update_data = array(
            'id_customer' => $data['id_customer'],
            'company_name' => $data['company_name'],
            'company_address' => isset($data['company_address'])?$data['company_address']:'',
            'postal_code' => $data['postal_code'],
            'import_subscription'=>$data['import_subscription'],
            'city' => isset($data['city'])?$data['city']:'',
            'vat_number' => isset($data['vat_number'])?$data['vat_number']:'',
            'country_id' => isset($data['country_id'])?$data['country_id']:'',
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate(),
            'company_status' => ''
        );
        if(isset($data['company_status'])){ $update_data['company_status'] = $data['company_status']; }
        else{ unset($update_data['company_status']); }

        $customer_id = $data['id_customer'];
        $path='uploads/';
        if(isset($_FILES) && !empty($_FILES['customer']['name']['company_logo']))
        {
            $imageName = doUpload(array(
                'temp_name' => $_FILES['customer']['tmp_name']['company_logo'],
                'image' => $_FILES['customer']['name']['company_logo'],
                'upload_path' => $path,
                'folder' => $data['id_customer']));
            $update_data['company_logo'] = $imageName;

            imageResize($path.$imageName);
            /* getting previous image to delete*/
            $customer_data = $this->Customer_model->getCustomer(array('id_customer' => $data['id_customer']));
            if(!empty($customer_data)){
                deleteImage($customer_data[0]['company_logo']);
            }
        }
        else{
            unset($update_data['company_logo']);
        }
        if(isset($data['is_delete_logo']) && $data['is_delete_logo'] == 1){
            $update_data['company_logo'] = null;
        }

        $this->Customer_model->updateCustomer($update_data);

        if(isset($data['secondary_languages_id']) && !empty($data['secondary_languages_id']))
        {
           
            $secLanguages = $data['secondary_languages_id'];
            $this->User_model->update_data('customer_languages' , array('status' => 0 ,'updated_on' => currentDate()) , array('customer_id' => $customer_id , 'is_primary' => 0 ));
            foreach($secLanguages as $secLanguage)
            {
                $languageDetails = $this->User_model->check_record('customer_languages',array('language_id' => pk_decrypt($secLanguage) , 'customer_id' => $customer_id , 'is_primary' => 0));
                if(!empty($languageDetails) && count($languageDetails) > 0)
                {
                    $this->User_model->update_data('customer_languages' , array('status' => 1 , 'updated_on' => currentDate()) , array('id_customer_language' => $languageDetails[0]['id_customer_language']  ));
                }
                else
                {
                    $this->User_model->insert_data('customer_languages',array('customer_id'=> $customer_id ,'language_id' =>  pk_decrypt($secLanguage) ,  'status' => 1 , 'created_on' => currentDate())); 
                }

            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('customer_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function adminList_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data = tableOptions($data);
        $result = $this->Customer_model->getCustomerAdminList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['id_user']=pk_encrypt($v['id_user']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function admin_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //validating data

        $this->form_validator->add_rules('user_id', array('required'=> $this->lang->line('user_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=> $this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_id'])) $data['user_id']=pk_decrypt($data['user_id']);
        if(isset($data['customer_id'])) $data['customer_id']=pk_decrypt($data['customer_id']);
        if(isset($data['user_role_id'])) $data['user_role_id']=pk_decrypt($data['user_role_id']);
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data['user_role_id'] = 2;
        $result = $this->User_model->getUserInfo($data);
        if(isset($result->id_user))
            $result->id_user=pk_encrypt($result->id_user);
        if(isset($result->customer_id))
            $result->customer_id=pk_encrypt($result->customer_id);
        if(isset($result->user_role_id))
            $result->user_role_id=pk_encrypt($result->user_role_id);
        $result->language_id=pk_encrypt($result->language_id);    
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function admin_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $firstNameRules               = array(
            'required'=> $this->lang->line('first_name_req'),
            'max_len-100' => $this->lang->line('first_name_len'),
        );
        $lastNameRules               = array(
            'required'=> $this->lang->line('last_name_req'),
            'max_len-100' => $this->lang->line('last_name_len'),
        );
        $emailRules = array(
            'required'=> $this->lang->line('email_req'),
            'valid_email' => $this->lang->line('email_invalid')
        );
        $is_manual_passwordRules = array(
            'required'=> $this->lang->line('is_manual_password_req')
        );
        $passwordRules               = array(
            'required'=> $this->lang->line('password_req'),
            'min_len-8' => $this->lang->line('password_num_min_len'),
            'max_len-20' => $this->lang->line('password_num_max_len'),
        );
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('created_by', array('required' => $this->lang->line('created_by_req')));
        $this->form_validator->add_rules('first_name', $firstNameRules);
        $this->form_validator->add_rules('last_name', $lastNameRules);
        $this->form_validator->add_rules('email', $emailRules);
        $this->form_validator->add_rules('language_id', array('required'=> $this->lang->line('language_id_req')));
        if(!isset($data['id_user'])) {
            $this->form_validator->add_rules('is_manual_password', $is_manual_passwordRules);
            if(isset($data['is_manual_password']) && $data['is_manual_password']==1){
                $this->form_validator->add_rules('password', $passwordRules);
            }
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
            if(!in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if(!in_array($data['id_user'],$this->session_user_master_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['language_id'])) $data['language_id']=pk_decrypt($data['language_id']);
        $user_id=0;
        if(isset($data['id_user'])){ $user_id = $data['id_user']; }
        /*checking for email uniqueness*/
        $email_check = $this->User_model->check_email(array('email' => $data['email'],'id' => $user_id));
        $result = $email_check;

        if(!empty($email_check)){
            $result = array('status'=>FALSE,'error'=>array('email' => $this->lang->line('email_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(!isset($data['id_user'])) {
            if($data['is_manual_password']==1){
                $password = $data['password'];
            }
            else if($data['is_manual_password']==0){
                $password = generatePassword(8);//helper function for generating password
            }

            $user_data = array(
                'user_role_id' => 2,
                'customer_id' => $data['customer_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => md5($password),
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'language_id' => isset($data['language_id']) ? $data['language_id'] : 1,
                'created_by' => $data['created_by'],
                'created_on' => currentDate(),
                'user_status' => $data['user_status'],
                'other_gender_value' => (isset($data['other_gender_value']) && !empty($data['other_gender_value']) && $data['gender'] == 'other') ? $data['other_gender_value'] : null,
            );

            $this->User_model->createUser($user_data);
            /*getting data for mail --start */
            $customer_name = $user_role_name = '';
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $data['customer_id']));
            $user_role_details = $this->User_model->getUserRole(array('user_role_id' => 2));

            if(!empty($user_role_details)){ $user_role_name = $user_role_details[0]['user_role_name']; }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }

            /*sending mail for newly created admin*/
            /*$message = str_replace(array('{first_name}','{last_name}','{role}','{customer_name}','{email}','{password}'),array($data['first_name'],$data['last_name'],$user_role_name,$customer_name,$data['email'],$password),$this->lang->line('customer_admin_create_message'));
            $template_data = array(
                'web_base_url' => WEB_BASE_URL,
                'message' => $message,
                'mail_footer' => $this->lang->line('mail_footer')
            );
            $subject = $this->lang->line('customer_admin_create_subject');
            $template_data = $this->parser->parse('templates/notification.html',$template_data);
            sendmail($data['email'],$subject,$template_data);

            $msg = $this->lang->line('customer_admin_add');*/
            $user_info = $this->User_model->check_email(array('email' => $data['email']));
            $msg = $this->lang->line('customer_admin_add');
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $user_info->customer_id));
            /*$cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
            $cust_admin = $cust_admin['data'][0];*/
            $cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
            $cust_admin = $cust_admin['data'][0];
            //echo 'cust_detail'.'<pre>';print_r($customer_details);exit;
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'company');
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'company');*/
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'profile');*/
            }

            $user_info = $this->User_model->getUserInfo(array('user_id' => $user_info->id_user));
            $user_role_name = $this->User_model->getUserRole(array('user_role_id' => $user_info->user_role_id));
            // E-Mail Sending from With Admin's template.
            $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $data['customer_id'],'module_key'=>'USER_CREATION'));
            //echo 'cust_detail'.'<pre>';print_r($user_info);exit;
            if($template_configurations['total_records']>0){
                $template_configurations=$template_configurations['data'][0];
                $wildcards=$template_configurations['wildcards'];
                $wildcards_replaces=array();
                $wildcards_replaces['first_name']=$user_info->first_name;
                $wildcards_replaces['last_name']=$user_info->last_name;
                $wildcards_replaces['customer_name']=$customer_details[0]['company_name'];
                $wildcards_replaces['logo']=$customer_logo;
                $wildcards_replaces['email']=$user_info->email;
                $wildcards_replaces['role']=$user_role_name[0]['user_role_name'];
                $wildcards_replaces['year'] = date("Y");
                $wildcards_replaces['url']=WEB_BASE_URL.'html';
                $wildcards_replaces['password']=$password;
                $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                /*$from_name=SEND_GRID_FROM_NAME;
                $from=SEND_GRID_FROM_EMAIL;
                $from_name=$cust_admin['name'];
                $from=$cust_admin['email'];*/
                $from_name=$template_configurations['email_from_name'];
                $from=$template_configurations['email_from'];
                $to=$user_info->email;
                $to_name=$user_info->first_name.' '.$user_info->last_name;
                $mailer_data['mail_from_name']=$from_name;
                $mailer_data['mail_to_name']=$to_name;
                $mailer_data['mail_to_user_id']=$user_info->id_user;
                $mailer_data['mail_from']=$from;
                $mailer_data['mail_to']=$to;
                $mailer_data['mail_subject']=$subject;
                $mailer_data['mail_message']=$body;
                $mailer_data['status']=0;
                $mailer_data['send_date']=currentDate();
                $mailer_data['is_cron']=0;
                $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                $mailer_id=$this->Customer_model->addMailer($mailer_data);
                if($mailer_data['is_cron']==0) {
                    //$mail_sent_status=sendmail($to, $subject, $body, $from);
                    $this->load->library('sendgridlibrary');
                    $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                    if($mail_sent_status==1)
                        $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                }
            }
        }
        else{
            $user_data = array(
                'customer_id' => $data['customer_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'language_id' => isset($data['language_id']) ? $data['language_id'] : 1,
                'updated_by' => $data['created_by'],
                'updated_on' => currentDate(),
                'user_status' => $data['user_status'],
                'other_gender_value' => (isset($data['other_gender_value']) && !empty($data['other_gender_value']) && $data['gender'] == 'other') ? $data['other_gender_value'] : null,
            );

            $this->User_model->updateUser($user_data,$data['id_user']);
            $msg = $this->lang->line('customer_admin_update');
        }

        $result = array('status'=>TRUE, 'message' => $msg, 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function admin_delete()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if(!in_array($data['id_user'],$this->session_user_master_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->User_model->updateUser(array('user_status' => 0),$data['id_user']);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('customer_admin_inactive'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function userList_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('user_role_id', array('required' => $this->lang->line('user_role_id_req')));
        //$this->form_validator->add_rules('user_type', array('required' => $this->lang->line('user_type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->user_role_id!=$data['user_role_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if(!in_array($data['business_unit_id'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->id_user!=$data['id_user']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider'])) {
            $data['id_provider'] = pk_decrypt($data['id_provider']);
        }
        if(isset($data['current_user_not']))
            $data['current_user_not']=pk_decrypt($data['current_user_not']);
        if(isset($data['user_role_id']) && in_array($data['user_role_id'],array(3,4,8))){
            //3 means bu owner, can able to get own business unit users
            $user_id=isset($data['id_user'])?$data['id_user']:$this->user_id;
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $user_id,'status'=>1));
            //echo ''.$this->sb->last_query(); exit;
            $business_unit_id = array_map(function($i){ return $i['business_unit_id']; },$business_unit);
            $data['business_unit_array'] = $business_unit_id;
            $data['user_role_not'] = array(3);
        }
        $data = tableOptions($data);//helper function ordering smart table grid option
        $data['user_role_not']=array();
        if($data['user_role_id']==1){
            $data['user_role_not']=array(1);
        }
        if($data['user_role_id']==2){
            $data['user_role_not']=array(1,2);
        }
        if($data['user_role_id']==3 || $data['user_role_id']==8){
            $data['user_role_not']=array(1,2,3);
        }
        if($data['user_role_id']==4){
            $data['user_role_not']=array(1,2);
            //$data['user_contracts']=$this->session_user_contracts;
        }
        if($data['user_role_id']==5){
            $data['user_role_not']=array(1,2,3,4,5);
        }
        if($data['user_role_id']==6){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        if($data['user_role_id']==7){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        if(isset($data['contractOwner']) && $data['contractOwner'] == 1)
        {
            $data['user_role_not']=array(1);
            unset($data['business_unit_array']);
        }
        if(isset($data['user_type']) && $data['user_type']=='external')
             unset($data['business_unit_array']);
            //  for project providerusers tab   write below this if condition 
            if(!empty($data['project_id'])){
                $data['project_id']=pk_decrypt($data['project_id']);
                $data['type']='project';
                $get_project_providers=$this->Project_model->getactiveprojectProvider(array('project_id'=>$data['project_id']));
                $data['id_provider']=array_column($get_project_providers,'provider_id');
                if(empty($data['id_provider'])){
                    $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array());
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
        if(!empty($data['contract_id'])){
            $data['contract_id']=pk_decrypt($data['contract_id']);
            if($data['user_type']=='external'){
             if(empty($data['id_provider'])){
                $get_provider_id=$this->User_model->check_record('contract',array('id_contract'=>$data['contract_id']));
                if(!empty($get_provider_id[0]['provider_name'])){
                    $data['id_provider']=$get_provider_id[0]['provider_name'];
                }
             }   
            }
        }
        // print_r($data);exit;
        $result = $this->Customer_model->getCustomerUserList($data);//echo $this->db->last_query();
        for($s=0;$s<count($result['data']);$s++)
        {
            if($result['data'][$s]['user_role_id'] == 6){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$result['data'][$s]['id_user']));
                if($user_info[0]['is_allow_all_bu']==1){
                    // $bu_names = $this->User_model->check_record_selected('GROUP_CONCAT(bu_name) as bu_name','business_unit',array('status'=>1,'customer_id'=>$user_info[0]['customer_id']));
                    $bu_names = $this->User_model->Calender_model->getbunameswithcountryname(array('customer_id'=>$user_info[0]['customer_id']));//echo 
                    $bu_ids = $this->User_model->check_record_selected('GROUP_CONCAT(id_business_unit) as bu_ids','business_unit',array('status'=>1,'customer_id'=>$user_info[0]['customer_id']));
                    $result['data'][$s]['bu_name'] = $bu_names[0]['bu_name'];
                    $result['data'][$s]['business_unit_id'] = $bu_names[0]['bu_ids'];
                }
            }
            $result['data'][$s]['id_user']=pk_encrypt($result['data'][$s]['id_user']);
            $result['data'][$s]['provider']=pk_encrypt($result['data'][$s]['provider']);
            if($result['data'][$s]['bu_name']!='')
                $result['data'][$s]['bu_name'] = array_unique(explode(',',$result['data'][$s]['bu_name']));
            if($result['data'][$s]['business_unit_id']!='')
                $result['data'][$s]['business_unit_id'] = array_unique(explode(',',$result['data'][$s]['business_unit_id']));
            for($si=0;$si<count($result['data'][$s]['business_unit_id']);$si++){
                $result['data'][$s]['business_unit_id'][$si]=pk_encrypt($result['data'][$s]['business_unit_id'][$si]);
            }
            if(($result['data'][$s]['contribution_type']=='1' || $result['data'][$s]['contribution_type']=='0')){
                //Removed from If condition  "&& (int)$result['data'][$s]['user_role_id'] != 6"
                //Here Contribution type 0=> Internal user 1=> Intrnl Usr & Validator Contributor (IN DB) 
                if($result['data'][$s]['contribution_type']=='1')
                    $result['data'][$s]['contribution'] = 'Validation';
                if($result['data'][$s]['contribution_type']=='0')
                    $result['data'][$s]['contribution'] = 'Expert';

                $result['data'][$s]['user_type'] = "internal";
            }
            else{
                //Here Contribution type 2=> External user 3=> Ext Usr & Provider Contributor (IN DB)
                if($result['data'][$s]['contribution_type']=='3')
                    $result['data'][$s]['contribution'] = 'External User';
                if($result['data'][$s]['contribution_type']=='2')
                    $result['data'][$s]['contribution'] = 'Relation Contact';
                
                $result['data'][$s]['user_type'] = "external";
            }
            $result['data'][$s]['user_role_id']=pk_encrypt($result['data'][$s]['user_role_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function user_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //validating data

        $this->form_validator->add_rules('user_id', array('required'=> $this->lang->line('user_id_req')));
        $this->form_validator->add_rules('customer_id', array('required'=> $this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_id'])) {
            $data['user_id'] = pk_decrypt($data['user_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['user_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id']))
            $data['user_role_id']=pk_decrypt($data['user_role_id']);
        $data['user_role_id_not'] = array(1,2);
        $result = $this->User_model->getUserInfo($data);
        $result->business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $result->id_user,'status' =>1));
        if(isset($result->id_user))
            $result->id_user=pk_encrypt($result->id_user);
        if(isset($result->customer_id))
            $result->customer_id=pk_encrypt($result->customer_id);
        if(isset($result->user_role_id))
            $result->user_role_id=pk_encrypt($result->user_role_id);
        if(isset($result->provider_name))
            $result->provider_name=pk_encrypt($result->provider);
        if($result->contribution_type=='1' || $result->contribution_type=='0'){
            if($result->contribution_type=='1')
                $result->contribution_type = 1;//Assigning boolean for checkboxes // Provider Contributor
            else
                $result->contribution_type = 0;//Assigning boolean for checkboxes // Expert Contributor
            $result->user_type = "internal";
        }
        else{            
            if($result->contribution_type=='3')
                $result->contribution_type = 1;//Assigning boolean for checkboxes // Provider Contributor
            else
                $result->contribution_type = 0;//Assigning boolean for checkboxes // External user with no contribution
            $result->user_type = "external";
        }
        if(isset($result->country_id))
            $result->country_id=pk_encrypt($result->country_id);
        foreach($result->business_unit as $k=>$v){
            $result->business_unit[$k]['business_unit_id']=pk_encrypt($result->business_unit[$k]['business_unit_id']);
            $result->business_unit[$k]['country_id']=pk_encrypt($result->business_unit[$k]['country_id']);
            $result->business_unit[$k]['created_by']=pk_encrypt($result->business_unit[$k]['created_by']);
            $result->business_unit[$k]['customer_id']=pk_encrypt($result->business_unit[$k]['customer_id']);
            $result->business_unit[$k]['id_business_unit']=pk_encrypt($result->business_unit[$k]['id_business_unit']);
            $result->business_unit[$k]['id_business_unit_user']=pk_encrypt($result->business_unit[$k]['id_business_unit_user']);
            $result->business_unit[$k]['updated_by']=pk_encrypt($result->business_unit[$k]['updated_by']);
            $result->business_unit[$k]['user_id']=pk_encrypt($result->business_unit[$k]['user_id']);
        }
        $result->content_administator_relation = (int)$result->content_administator_relation;
        $result->content_administator_review_templates = (int)$result->content_administator_review_templates;
        $result->content_administator_task_templates = (int)$result->content_administator_task_templates;
        $result->content_administator_currencies = (int)$result->content_administator_currencies;
        $result->legal_and_content_administator = (int)$result->legal_and_content_administator;
        $result->content_administator_catalogue = (int)$result->content_administator_catalogue;
        $result->language_id = pk_encrypt($result->language_id);
      
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function user_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $firstNameRules               = array(
            'required'=> $this->lang->line('first_name_req'),
            'max_len-100' => $this->lang->line('first_name_len'),
        );
        $lastNameRules               = array(
            'required'=> $this->lang->line('last_name_req'),
            'max_len-100' => $this->lang->line('last_name_len'),
        );
        $emailRules = array(
            'required'=> $this->lang->line('email_req'),
            'valid_email' => $this->lang->line('email_invalid')
        );
        $is_manual_passwordRules = array(
            'required'=> $this->lang->line('is_manual_password_req')
        );
        $passwordRules               = array(
            'required'=> $this->lang->line('password_req'),
            'min_len-8' => $this->lang->line('password_num_min_len'),
            'max_len-20' => $this->lang->line('password_num_max_len'),
        );

        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('created_by', array('required' => $this->lang->line('created_by_req')));
        $this->form_validator->add_rules('first_name', $firstNameRules);
        $this->form_validator->add_rules('last_name', $lastNameRules);
        $this->form_validator->add_rules('email', $emailRules);
        $this->form_validator->add_rules('user_type', array('required' => $this->lang->line('user_type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if($data['user_type']=='internal')
            $this->form_validator->add_rules('user_role_id', array('required' => $this->lang->line('user_role_id_req')));
        if(!isset($data['id_user'])) {
            if(isset($data['is_manual']) && $data['is_manual']==1){
                $this->form_validator->add_rules('is_manual', $is_manual_passwordRules);
                $this->form_validator->add_rules('password', $passwordRules);
            }
        }
        if(isset($data['user_role_id']) && pk_decrypt($data['user_role_id']) != 1)
        {
            $this->form_validator->add_rules('language_id',  array('required' => $this->lang->line('language_id_req')));
        }
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])){
            $data['customer_id']=pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['provider_name']))
            $data['provider_name'] = pk_decrypt($data['provider_name']);
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_user'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            $apiAccess = $this->checkUserCreateUpdateAccess(array('loginUserDetails' => $this->session_user_info , 'updateableUserId' => $data['id_user']));
            if($apiAccess==false)
            {
                //access check with other roles
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            // if(!in_array($data['user_role_id'],$this->session_user_master_user_roles)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        // if(isset($data['business_unit'])) {
        //     foreach($data['business_unit'] as $k=>$v){
        //         $data['business_unit'][$k]=pk_decrypt($v);
        //         if($this->session_user_info->user_role_id!=1 && !in_array($data['business_unit'][$k],$this->session_user_business_units)){
        //             $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //             $this->response($result, REST_Controller::HTTP_OK);
        //         }
        //     }
        // }
        if(isset($data['language_id'])) {
            $data['language_id'] = pk_decrypt($data['language_id']);
        }
        if(isset($data['business_unit']) && count($data['business_unit'])>0){
            $bu_array = array();            
            foreach($data['business_unit'] as $k=>$v){
                // if(!in_array(pk_decrypt($v),$this->session_user_business_units)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
                $bu_array[$k]=pk_decrypt($v);
            }
            $data['business_unit'] = $bu_array;
        }
        if(isset($data['country_id']) ){
            $data['country_id']=pk_decrypt($data['country_id']);
        }
 
        $user_id = 0;
        if(isset($data['id_user'])){ $user_id = $data['id_user']; }
        $email_check = $this->User_model->check_email(array('email' => $data['email'],'id' => $user_id));
        if(!empty($email_check)){
            $result = array('status'=>FALSE,'error'=>array('email' => $this->lang->line('email_duplicate')),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_role_id']) && $data['user_role_id']!=6)
            $data['is_allow_all_bu']=0;

        if(!isset($data['id_user'])) {
            if($data['is_manual']==1){
                $password = $data['password'];
            }
            else if($data['is_manual']==0){
                $password = generatePassword(8);//helper function for generating password
            }
            $user_data = array(
                'user_role_id' => isset($data['user_role_id'])?$data['user_role_id']:7,
                'customer_id' => $data['customer_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => md5($password),
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'language_id' => isset($data['language_id']) ? $data['language_id'] : 1,
                'created_by' => $data['created_by'],
                'created_on' => currentDate(),
                'user_status' =>  $data['user_status'] ,
                'provider' => (isset($data['provider_name'])&&$data['provider_name']>0)?$data['provider_name']:0,
                'is_allow_all_bu' => isset($data['is_allow_all_bu'])?$data['is_allow_all_bu']:0,
                'office_phone' => (isset($data['office_phone'])&&!empty($data['office_phone']))?$data['office_phone']:null,
                'secondary_phone' => (isset($data['secondary_phone'])&&!empty($data['secondary_phone']))?$data['secondary_phone']:null,
                'fax_number' => (isset($data['fax_number'])&&!empty($data['fax_number']))?$data['fax_number']:null,
                'address' => (isset($data['address'])&&!empty($data['address']))?$data['address']:null,
                'postal_code' => (isset($data['postal_code'])&&!empty($data['postal_code']))?$data['postal_code']:null,
                'city' => (isset($data['city'])&&!empty($data['city']))?$data['city']:null,
                'country_id' => (isset($data['country_id'])&&!empty($data['country_id']))?$data['country_id']:null,
                'other_gender_value' => (isset($data['other_gender_value']) && !empty($data['other_gender_value']) && $data['gender'] == 'other') ? $data['other_gender_value'] : null,
                'content_administator_relation' => (isset($data['content_administator_relation']) && !empty($data['content_administator_relation'])) ? $data['content_administator_relation'] : 0 ,
                'content_administator_review_templates' => (isset($data['content_administator_review_templates']) && !empty($data['content_administator_review_templates'])) ? $data['content_administator_review_templates'] : 0 ,
                'content_administator_task_templates' => (isset($data['content_administator_task_templates']) && !empty($data['content_administator_task_templates'])) ? $data['content_administator_task_templates'] : 0 ,
                'content_administator_currencies' => (isset($data['content_administator_currencies']) && !empty($data['content_administator_currencies'])) ? $data['content_administator_currencies'] : 0 ,
                'legal_and_content_administator' => (isset($data['legal_and_content_administator']) && !empty($data['legal_and_content_administator'])) ? $data['legal_and_content_administator'] : 0,
                'content_administator_catalogue' => (isset($data['content_administator_catalogue']) && !empty($data['content_administator_catalogue'])) ? $data['content_administator_catalogue'] : 0,
                'function' => ($data['user_type'] == 'external' && isset($data['function']) && !empty($data['function'])) ? $data['function'] : null,
                'notes' => ($data['user_type'] == 'external' && isset($data['notes']) && !empty($data['notes'])) ? $data['notes'] : null,
                'link' => ($data['user_type'] == 'external' && isset($data['link']) && !empty($data['link'])) ? $data['link'] : null,
            );
            if($data['user_type']=='internal'){
                //Here Contribution type 0=> Internal user, 1=> Intrnl Usr & Validator Contributor (IN DB) 
                if(isset($data['contribution_type']) && $data['contribution_type']==1)
                    $user_data['contribution_type'] = 1; // Validation Contributor
                else
                    $user_data['contribution_type'] = 0; // Expert Contributor
            }else if($data['user_type']=='external'){
                $user_data['user_role_id'] = 7; // 7 meanse external users              
                //Here Contribution type 2=> External user, 3=> Ext Usr & Provider Contributor (IN DB)
                if(isset($data['contribution_type']) && $data['contribution_type']==1)
                    $user_data['contribution_type'] = 3; // Provider Contributor
                else
                    $user_data['contribution_type'] = 2; // External user with no contribution
            }
            $user_id = $this->User_model->createUser($user_data);

            $customer_name = $user_role_name = '';
            $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $data['customer_id']));
            /*$cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
            $cust_admin = $cust_admin['data'][0];*/
            if($customer_details[0]['company_logo']=='') {
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'company');
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'company');*/
            }
            else{
                $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'profile');*/
            }
            $user_role_details = $this->User_model->getUserRole(array('user_role_id' => 2));
            $user_role_details_user = $this->User_model->getUserRole(array('user_role_id' => $data['user_role_id']));
            if(!empty($user_role_details)){ $user_role_name = $user_role_details[0]['user_role_name']; }
            if(!empty($user_role_details_user)){ $user_role_name_user = $user_role_details_user[0]['user_role_name']; }
            if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }

            /*sending mail for newly created customer user*/
            /*$message = str_replace(array('{first_name}','{last_name}','{role}','{customer_name}','{email}','{password}'),array($data['first_name'],$data['last_name'],$user_role_name,$customer_name,$data['email'],$password),$this->lang->line('customer_user_create_message'));
            $template_data = array(
                'web_base_url' => WEB_BASE_URL,
                'message' => $message,
                'mail_footer' => $this->lang->line('mail_footer')
            );
            $subject = $this->lang->line('customer_user_create_subject');
            $template_data = $this->parser->parse('templates/notification.html',$template_data);
            sendmail($data['email'],$subject,$template_data);*/

            if($user_data['contribution_type'] != 2){
                $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $data['customer_id'],'module_key'=>'USER_CREATION'));
                if($template_configurations['total_records']>0){
                    $template_configurations=$template_configurations['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$data['first_name'];
                    $wildcards_replaces['last_name']=$data['last_name'];
                    $wildcards_replaces['customer_name']=$customer_name;
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['email']=$data['email'];
                    $wildcards_replaces['role']=$user_role_name_user;
                    $wildcards_replaces['password']=$password;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                    /*$from_name=SEND_GRID_FROM_NAME;
                    $from=SEND_GRID_FROM_EMAIL;
                    $from_name=$cust_admin['name'];
                    $from=$cust_admin['email'];*/
                    $from_name=$template_configurations['email_from_name'];
                    $from=$template_configurations['email_from'];
                    $to=$data['email'];
                    $to_name=$data['first_name'].' '.$data['last_name'];
                    $mailer_data['mail_from_name']=$from_name;
                    $mailer_data['mail_to_name']=$to_name;
                    $mailer_data['mail_to_user_id']=$user_id;
                    $mailer_data['mail_from']=$from;
                    $mailer_data['mail_to']=$to;
                    $mailer_data['mail_subject']=$subject;
                    $mailer_data['mail_message']=$body;
                    $mailer_data['status']=0;
                    $mailer_data['send_date']=currentDate();
                    $mailer_data['is_cron']=0;//0-immediate mail,1-through cron job
                    $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                    //echo '<pre>';print_r($customer_logo);exit;
                    $mailer_id=$this->Customer_model->addMailer($mailer_data);
                    if($mailer_data['is_cron']==0) {
                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                        $this->load->library('sendgridlibrary');
                        $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                        if($mail_sent_status==1)
                            $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                    }
                }
            }
          
            $msg = $this->lang->line('user_add');
        }
        else{
            $delete = array(0);
            if(isset($data['business_unit'])) {
                $previous_data = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $user_id,'status'=>1));
                $previous_data = array_map(function ($i) {
                    return $i['business_unit_id'];
                }, $previous_data);
                
                $delete = array_values(array_diff($previous_data, $data['business_unit']));
            }
            $session_user_contracts_by_deleted_bu = $this->Contract_model->getUserContractsByBusinessUnitArray(array('user_id' => $user_id, 'bu_array'=>$delete));//echo 
            //echo '<pre>'.print_r($session_user_contracts_by_deleted_bu);exit;
            $user_old_data = $this->User_model->check_record('user',array('id_user'=>$data['id_user']));
            $is_downgrade_upgrade=0;
            if(in_array($user_old_data[0]['user_role_id'],array(3,8)) && in_array($data['user_role_id'],array(3,8))){
                $is_downgrade_upgrade=1;
            }
            // print_r($data['user_role_id']);
            // print_r($user_old_data[0]['user_role_id']);exit;
            if($user_old_data[0]['user_role_id']!=$data['user_role_id']){
                if($data['user_role_id']!=3 && $user_old_data[0]['user_role_id']==8){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('downgrade_not_possible')), 'data'=>'1');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
                if($data['user_role_id']==8 && $user_old_data[0]['user_role_id']!=3){
                    $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('upgrade_not_possible')), 'data'=>'1');
                    $this->response($result, REST_Controller::HTTP_OK);
                }
            }
            if(count($user_old_data)>0 && $is_downgrade_upgrade==0){
                $user_owner_contracts = $this->User_model->check_record('contract',array('contract_owner_id'=>$data['id_user'],'is_deleted'=>0));
                $user_delegate_contracts = $this->User_model->check_record('contract',array('delegate_id'=>$data['id_user'],'is_deleted'=>0));
                //$user_contributor_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$data['id_user'],'status'=>1));
                if(count($user_owner_contracts)>0 || count($user_delegate_contracts)>0 || count($session_user_contracts_by_deleted_bu)>0){
                    if($user_old_data[0]['user_role_id'] != $data['user_role_id'] || count($session_user_contracts_by_deleted_bu)>0){
                        $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('user_already_assigned_with')), 'data'=>'1');
                        $this->response($result, REST_Controller::HTTP_OK);
                    }
                }
                if((int)$user_old_data[0]['contribution_type'] == 1 && $data['contribution_type']==0){
                    $success_on_failure = true;
                }
            }
            $userOldInfo = $this->User_model->check_record('user',array('id_user' => $data['id_user']));
            
            $user_data = array(
                'user_role_id' => $data['user_role_id'],
                'customer_id' => $data['customer_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'language_id' => isset($data['language_id']) ? $data['language_id'] : 1,
                'updated_by' => $data['created_by'],
                'updated_on' => currentDate(),
                'user_status' => $data['user_status'],
                'provider' => (isset($data['provider_name'])&&$data['provider_name']>0)?$data['provider_name']:0,
                'is_allow_all_bu' => isset($data['is_allow_all_bu'])?$data['is_allow_all_bu']:0,
                'office_phone' => (isset($data['office_phone'])&&!empty($data['office_phone']))?$data['office_phone']:null,
                'secondary_phone' => (isset($data['secondary_phone'])&&!empty($data['secondary_phone']))?$data['secondary_phone']:null,
                'fax_number' => (isset($data['fax_number'])&&!empty($data['fax_number']))?$data['fax_number']:null,
                'address' => (isset($data['address'])&&!empty($data['address']))?$data['address']:null,
                'postal_code' => (isset($data['postal_code'])&&!empty($data['postal_code']))?$data['postal_code']:null,
                'city' => (isset($data['city'])&&!empty($data['city']))?$data['city']:null,
                'country_id' => (isset($data['country_id'])&&!empty($data['country_id']))?$data['country_id']:null,
                'other_gender_value' => (isset($data['other_gender_value']) && !empty($data['other_gender_value']) && $data['gender'] == 'other') ? $data['other_gender_value'] : null,
                'content_administator_relation' => (isset($data['content_administator_relation']) && !empty($data['content_administator_relation'])) ? $data['content_administator_relation'] : 0 ,
                'content_administator_review_templates' => (isset($data['content_administator_review_templates']) && !empty($data['content_administator_review_templates'])) ? $data['content_administator_review_templates'] : 0 ,
                'content_administator_task_templates' => (isset($data['content_administator_task_templates']) && !empty($data['content_administator_task_templates'])) ? $data['content_administator_task_templates'] : 0 ,
                'content_administator_currencies' => (isset($data['content_administator_currencies']) && !empty($data['content_administator_currencies'])) ? $data['content_administator_currencies'] : 0 ,
                'legal_and_content_administator' => (isset($data['legal_and_content_administator']) && !empty($data['legal_and_content_administator'])) ? $data['legal_and_content_administator'] : 0 ,
                'content_administator_catalogue' => (isset($data['content_administator_catalogue']) && !empty($data['content_administator_catalogue'])) ? $data['content_administator_catalogue'] : 0 ,
                'function' => ($data['user_type'] == 'external' && isset($data['function']) && !empty($data['function'])) ? $data['function'] : null,
                'notes' => ($data['user_type'] == 'external' && isset($data['notes']) && !empty($data['notes'])) ? $data['notes'] : null,
                'link' => ($data['user_type'] == 'external' && isset($data['link']) && !empty($data['link'])) ? $data['link'] : null,
            );
            if($data['user_type']=='internal'){
                //Here Contribution type 0=> Internal user, 1=> Intrnl Usr & Validator Contributor (IN DB) 
                if(isset($data['contribution_type']) && $data['contribution_type']==1)
                    $user_data['contribution_type'] = 1;
                else
                    $user_data['contribution_type'] = 0;
            }else if($data['user_type']=='external'){
                //Here Contribution type 2=> External user, 3=> Ext Usr & Provider Contributor (IN DB)
                if(isset($data['contribution_type']) && $data['contribution_type']==1)
                    $user_data['contribution_type'] = 3;
                else
                    $user_data['contribution_type'] = 2;
            }
            $user_id = $data['id_user'];
            if($userOldInfo[0]['contribution_type'] == 2 &&  $user_data['contribution_type'] == 3)
            {
                if($data['is_manual']==1){
                    $password = $data['password'];
                }
                else if($data['is_manual']==0){
                    $password = generatePassword(8);//helper function for generating password
                }
                $user_data['password'] = md5($password);
            }
            $this->User_model->updateUser($user_data,$data['id_user']);

            if($userOldInfo[0]['contribution_type'] == 2 &&  $user_data['contribution_type'] == 3)
            {
                //updating user from relation contract to externaluser sending mail to user with User activation
                $customer_name = $user_role_name = '';
                $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $data['customer_id']));

                if($customer_details[0]['company_logo']=='') {
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
                }
                else{
                    $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
                }
                $user_role_details = $this->User_model->getUserRole(array('user_role_id' => 2));
                $user_role_details_user = $this->User_model->getUserRole(array('user_role_id' => $data['user_role_id']));
                if(!empty($user_role_details)){ $user_role_name = $user_role_details[0]['user_role_name']; }
                if(!empty($user_role_details_user)){ $user_role_name_user = $user_role_details_user[0]['user_role_name']; }
                if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }


                $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $data['customer_id'],'module_key'=>'USER_CREATION'));

                if($template_configurations['total_records']>0){
                    $template_configurations=$template_configurations['data'][0];
                    $wildcards=$template_configurations['wildcards'];
                    $wildcards_replaces=array();
                    $wildcards_replaces['first_name']=$data['first_name'];
                    $wildcards_replaces['last_name']=$data['last_name'];
                    $wildcards_replaces['customer_name']=$customer_name;
                    $wildcards_replaces['logo']=$customer_logo;
                    $wildcards_replaces['email']=$data['email'];
                    $wildcards_replaces['role']=$user_role_name_user;
                    $wildcards_replaces['password']=$password;
                    $wildcards_replaces['year'] = date("Y");
                    $wildcards_replaces['url']=WEB_BASE_URL.'html';
                    $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
                    $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
                    /*$from_name=SEND_GRID_FROM_NAME;
                    $from=SEND_GRID_FROM_EMAIL;
                    $from_name=$cust_admin['name'];
                    $from=$cust_admin['email'];*/
                    $from_name=$template_configurations['email_from_name'];
                    $from=$template_configurations['email_from'];
                    $to=$data['email'];
                    $to_name=$data['first_name'].' '.$data['last_name'];
                    $mailer_data['mail_from_name']=$from_name;
                    $mailer_data['mail_to_name']=$to_name;
                    $mailer_data['mail_to_user_id']=$user_id;
                    $mailer_data['mail_from']=$from;
                    $mailer_data['mail_to']=$to;
                    $mailer_data['mail_subject']=$subject;
                    $mailer_data['mail_message']=$body;
                    $mailer_data['status']=0;
                    $mailer_data['send_date']=currentDate();
                    $mailer_data['is_cron']=0;//0-immediate mail,1-through cron job
                    $mailer_data['email_template_id']=$template_configurations['id_email_template'];
                    //echo '<pre>';print_r($customer_logo);exit;
                    $mailer_id=$this->Customer_model->addMailer($mailer_data);
                    if($mailer_data['is_cron']==0) {
                        //$mail_sent_status=sendmail($to, $subject, $body, $from);
                        $this->load->library('sendgridlibrary');
                        $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                        if($mail_sent_status==1)
                            $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
                    }
                }
            }
            $msg = $this->lang->line('user_update');
        }

        //mapping user to business unit
        if(isset($data['business_unit'])) {
            $previous_data = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $user_id));
            $previous_data = array_map(function ($i) {
                return $i['business_unit_id'];
            }, $previous_data);

            $add = array_values(array_diff($data['business_unit'], $previous_data));
            $delete = array_values(array_diff($previous_data, $data['business_unit']));

            if (!empty($add)) {
                for ($s = 0; $s < count($add); $s++) {
                    if($add[$s]!='') {
                        $business_unit_user[] = array(
                            'business_unit_id' => $add[$s],
                            'user_id' => $user_id,
                            'created_by' => $data['created_by'],
                            'created_on' => currentDate(),
                        );
                    }
                }

                if (!empty($business_unit_user)) {
                    $this->Business_unit_model->mapBusinessUnitUser($business_unit_user);
                }
            }
            $this->Business_unit_model->updateBusinessUnitUser(array(
                'user_id' => $user_id,
                'status' => 1
            ));
            if (!empty($delete)) {
                for ($s = 0; $s < count($delete); $s++) {
                    if($delete[$s]!='') {
                        $this->Business_unit_model->updateBusinessUnitUser(array(
                            'business_unit_id' => $delete[$s],
                            'user_id' => $user_id,
                            'status' => 0
                        ));
                    }
                }
            }
        }

        if($success_on_failure)
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('user_validations_will_be_gone')) , 'data'=>'' ,'success'=>true);
        else
            $result = array('status'=>TRUE, 'message' => $msg, 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function user_delete()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_user'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->User_model->updateUser(array('user_status' => 0),$data['id_user']);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('customer_user_inactive'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function resetPassword_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $passwordRules               = array(
            'required'=> $this->lang->line('password_req'),
            'min_len-8' => $this->lang->line('password_num_min_len'),
            'max_len-12' => $this->lang->line('password_num_max_len'),
        );
        $confirmPasswordRules        = array(
            'required'=>$this->lang->line('confirm_password_req'),
            'match_field-password'=>$this->lang->line('password_match')
        );

        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('user_id', array('required' => $this->lang->line('user_id_req')));
        $this->form_validator->add_rules('password', $passwordRules);
        $this->form_validator->add_rules('cpassword', $confirmPasswordRules);
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(!validatePassword($data['password'])){
            $result = array('status'=>FALSE,'error'=>array('password'=>$this->lang->line("reg_exp_not_match")),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['user_id'])) {
            $data['user_id'] = pk_decrypt($data['user_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['user_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $apiAccess = $this->checkUserCreateUpdateAccess(array('loginUserDetails' => $this->session_user_info , 'updateableUserId' => $data['user_id']));
        if($apiAccess==false)
        {
            //access check with other roles
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $customer_details = $this->Customer_model->getCustomer(array('id_customer' => $data['customer_id']));
        $cust_admin = $this->Customer_model->getCustomerAdminList(array('customer_id' => $customer_details[0]['id_customer']));
        $cust_admin = $cust_admin['data'][0];
        if($customer_details[0]['company_logo']=='') {
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'company');
            /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'company');
            $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'company');*/
        }
        else{
            $customer_logo = getImageUrlSendEmail($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
            /*$result->customer_logo_small = getImageUrl($customer_details[0]['company_logo'], 'profile', SMALL_IMAGE);
            $result->customer_logo = getImageUrl($customer_details[0]['company_logo'], 'profile');*/
        }
        if(!empty($customer_details)){ $customer_name = $customer_details[0]['company_name']; }
        $this->User_model->changePassword(array('user_id' => $data['user_id'],'password' => $data['password']));

        /*mail content --start */
        $user_info = $this->User_model->getUserInfo(array('user_id' => $data['user_id']));
        /*$message = str_replace(array('{first_name}','{last_name}','{password}'),array($user_info->first_name,$user_info->last_name,$data['password']),$this->lang->line('reset_password_mail'));
        $template_data = array(
            'web_base_url' => WEB_BASE_URL,
            'message' => $message,
            'mail_footer' => $this->lang->line('mail_footer')
        );
        $subject = $this->lang->line('reset_password_subject');
        $template_data = $this->parser->parse('templates/notification.html',$template_data);
        sendmail($user_info->email,$subject,$template_data);*/
        $template_configurations=$this->Customer_model->EmailTemplateList(array('customer_id' => $data['customer_id'],'module_key'=>'RESET_PASSWORD'));
        if($template_configurations['total_records']>0){
            $template_configurations=$template_configurations['data'][0];
            $wildcards=$template_configurations['wildcards'];
            $wildcards_replaces=array();
            $wildcards_replaces['first_name']=$user_info->first_name;
            $wildcards_replaces['last_name']=$user_info->last_name;
            $wildcards_replaces['customer_name']=$customer_name;
            $wildcards_replaces['logo']=$customer_logo;
            $wildcards_replaces['email']=$user_info->email;
            $wildcards_replaces['role']=$user_info->user_role_name;
            $wildcards_replaces['password']=$data['password'];
            $wildcards_replaces['year'] = date("Y");
            $wildcards_replaces['url']=WEB_BASE_URL.'html';
            $body = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_content']);
            $subject = wildcardreplace($wildcards,$wildcards_replaces,$template_configurations['template_subject']);
            /*$from_name=SEND_GRID_FROM_NAME;
            $from=SEND_GRID_FROM_EMAIL;
            $from_name=$cust_admin['name'];
            $from=$cust_admin['email'];*/
            $from_name=$template_configurations['email_from_name'];
            $from=$template_configurations['email_from'];
            $to=$user_info->email;
            $to_name=$user_info->first_name.' '.$user_info->last_name;
            $mailer_data['mail_from_name']=$from_name;
            $mailer_data['mail_to_name']=$to_name;
            $mailer_data['mail_to_user_id']=$user_info->id_user;
            $mailer_data['mail_from']=$from;
            $mailer_data['mail_to']=$to;
            $mailer_data['mail_subject']=$subject;
            $mailer_data['mail_message']=$body;
            $mailer_data['status']=0;
            $mailer_data['send_date']=currentDate();
            $mailer_data['is_cron']=0;
            $mailer_data['email_template_id']=$template_configurations['id_email_template'];
            $mailer_id=$this->Customer_model->addMailer($mailer_data);
            if($mailer_data['is_cron']==0) {
                //$mail_sent_status=sendmail($to, $subject, $body, $from);
                $this->load->library('sendgridlibrary');
                $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
                if($mail_sent_status==1)
                    $this->Customer_model->updateMailer(array('status'=>1,'mailer_id'=>$mailer_id));
            }
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('password_changed'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function delete_delete()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_customer', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_customer'])) {
            $data['id_customer'] = pk_decrypt($data['id_customer']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['id_customer']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['id_customer'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $this->Customer_model->updateCustomer(array('id_customer' => $data['id_customer'],'company_status' => 0));
        $result = array('status'=>TRUE, 'message' => $this->lang->line('customer_inactive'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function calender_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('month', array('required'=>$this->lang->line('month_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        unset($data['date']);
        $data['status'] = 1;
        $result = $this->Customer_model->getCalender($data);
        foreach($result as $k=>$v){
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_calender']=pk_encrypt($v['id_calender']);
            $result[$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result[$k]['created_by']=pk_encrypt($v['created_by']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function calenderYearView_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('year', array('required'=>$this->lang->line('year_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $data['status'] = 1;
        $result = $this->Customer_model->getYearCalender($data);
        $relationships = $this->Relationship_category_model->getRelationshipCategory(array('customer_id'=>$data['customer_id']));
        $output = array();
        foreach($result as $arg)
        {
            $output[$arg['month_id']][] = $arg;
            $rel_cat[$arg['relationship_category_id']] = $arg['relationship_category_id'];
        }

        foreach($output as $k=>$v){//changing 0,1 indexes to month and relationship id's
            foreach ($v as $k1=>$v1) {
                $output[$k][$v1['relationship_category_id']]=$v1;

                unset($output[$k][$k1]);
            }
        }
        $final_result=array();
        foreach($output as $k=>$v){
            foreach($v as $k1=>$v1){

                foreach($relationships as $r=>$s){

                    if($k1==$s['relationship_category_id']){

                        $final_result[$k][pk_encrypt($s['relationship_category_id'])]=$v1;
                        $final_result[$k][pk_encrypt($s['relationship_category_id'])]['month_id']=pk_encrypt($v1['month_id']);
                        $final_result[$k][pk_encrypt($s['relationship_category_id'])]['relationship_category_id']=pk_encrypt($v1['relationship_category_id']);
                    }
                    else{
                        if(!isset($final_result[$k][pk_encrypt($s['relationship_category_id'])]))
                        {
                            $final_result[$k][pk_encrypt($s['relationship_category_id'])]=array('relationship_category_id'=>pk_encrypt($s['relationship_category_id']),
                                'relationship_category_name'=>$s['relationship_category_name'],
                                'month'=>date("F", mktime(0, 0, 0, $k, 10)),
                                'month_id'=>pk_encrypt($k),
                                'relationship_category_quadrant'=>'',
                                'relationship_count'=>0);
                        }
                    }
                }
            }
        }


        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$final_result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function calender_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));
        $this->form_validator->add_rules('relationship_category_id', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['relationship_category_id']) && is_array($data['relationship_category_id'])) {
            foreach($data['relationship_category_id'] as $k=>$v){
                $data['relationship_category_id'][$k]=pk_decrypt($v);
                // if($this->session_user_info->user_role_id!=1 && !in_array($data['relationship_category_id'][$k],$this->session_user_customer_relationship_categories)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
                // if($this->session_user_info->user_role_id==1 && !in_array($data['relationship_category_id'][$k],$this->session_user_wadmin_relationship_categories)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
        }
        $conflicts=array();
        if(isset($data['relationship_category_id']) && is_array($data['relationship_category_id'])) {
            $existing_records = $this->Customer_model->getCalender(array('customer_id' => $data['customer_id'], 'date' => $data['date']));
            $existing_records_inactive = $this->Customer_model->getCalender(array('customer_id' => $data['customer_id'], 'date' => $data['date'],'status'=>0));
            $existing_records_relationship_id = array_map(function ($i) {
                return $i['relationship_category_id'];
            }, $existing_records);
            $existing_records_relationship_inactive_id = array_map(function ($i) {
                return $i['relationship_category_id'];
            }, $existing_records_inactive);
            $data['new_relationship_category_id'] = array_values(array_diff($data['relationship_category_id'], $existing_records_relationship_id));
            $data['new_relationship_category_id_tobeactive'] = array_values(array_intersect($data['relationship_category_id'], $existing_records_relationship_inactive_id));
            $data['old_relationship_category_id'] = array_values(array_diff($existing_records_relationship_id, $data['relationship_category_id']));
            $add = array();
            $update = array();

            //$this->Customer_model->updateCalender(array('date' => $data['date'], 'status' => 1));

            for ($s = 0; $s < count($data['new_relationship_category_id']); $s++) {
                $alreadyExist=$this->Customer_model->checkAlreadyExist(array('relationship_category_id'=>$data['new_relationship_category_id'][$s],'date'=>$data['date']));
                if(count($alreadyExist)==0) {
                    $add[] = array(
                        'customer_id' => $data['customer_id'],
                        'date' => $data['date'],
                        'relationship_category_id' => $data['new_relationship_category_id'][$s],
                        'created_by' => $data['created_by'],
                        'created_on' => currentDate()
                    );
                }
                else{
                    foreach($alreadyExist as $k=>$v){
                        //$conflicts[]=array('category'=>$v['relationship_category_name'],'date'=>$v['date']);
                        $conflicts[]=$v['relationship_category_name'];
                    }

                }
            }
            for ($s = 0; $s < count($data['new_relationship_category_id_tobeactive']); $s++) {
                $alreadyExist=$this->Customer_model->checkAlreadyExist(array('relationship_category_id'=>$data['new_relationship_category_id_tobeactive'][$s],'date'=>$data['date']));
                if(count($alreadyExist)==0) {
                    /*$this->Customer_model->updateCalenderByCategory(array(
                        'customer_id' => $data['customer_id'],
                        'date' => $data['date'],
                        'relationship_category_id' => $data['new_relationship_category_id_tobeactive'][$s],
                        'status' => 1,
                        'updated_by' => $data['created_by'],
                        'updated_on' => currentDate()
                    ));*/
                    $update[]=array(
                        'customer_id' => $data['customer_id'],
                        'date' => $data['date'],
                        'relationship_category_id' => $data['new_relationship_category_id_tobeactive'][$s],
                        'status' => 1,
                        'updated_by' => $data['created_by'],
                        'updated_on' => currentDate()
                    );
                }
                else{
                    foreach($alreadyExist as $k=>$v){
                        //$conflicts[]=array('category'=>$v['relationship_category_name'],'date'=>$v['date']);
                        $conflicts[]=$v['relationship_category_name'];
                    }

                }
            }
            for ($s = 0; $s < count($data['old_relationship_category_id']); $s++) {
                /*$this->Customer_model->updateCalenderByCategory(array(
                    'customer_id' => $data['customer_id'],
                    'date' => $data['date'],
                    'relationship_category_id' => $data['old_relationship_category_id'][$s],
                    'status' => 0,
                    'updated_by' => $data['created_by'],
                    'updated_on' => currentDate()
                ));*/
                $update[]=array(
                    'customer_id' => $data['customer_id'],
                    'date' => $data['date'],
                    'relationship_category_id' => $data['old_relationship_category_id'][$s],
                    'status' => 0,
                    'updated_by' => $data['created_by'],
                    'updated_on' => currentDate()
                );
            }
            if(count($conflicts)==0) {
                if (!empty($add))
                    $this->Customer_model->addCalender($add);
                if (!empty($update)){
                    foreach($update as $ku=>$vu){
                        $this->Customer_model->updateCalenderByCategory($vu);
                    }
                }
            }
            else{
                $result = array('status'=>FALSE,'message'=>implode(',',array_unique($conflicts)).' '.$this->lang->line('has_a_conflict'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        else{
            $this->Customer_model->updateCalender(array('date' => $data['date'], 'status' => 0));
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('info_save'), 'data'=>array('warning'=>implode(',',$conflicts)));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function calender_delete()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_calender', array('required'=>$this->lang->line('calender_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_calender'])) {
            $data['id_calender'] = pk_decrypt($data['id_calender']);
            // if(!in_array($data['id_calender'],$this->session_user_customer_calenders)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        $this->Customer_model->updateCalender(array('id_calender' => $data['id_calender'],'status' =>1));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('relationship_category_delete'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function relationshipCategoryRemainder_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Customer_model->getRelationshipCategoryRemainder($data);
        // echo '<pre>'.
        foreach($result as $k=>$v){
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_category']=pk_encrypt($v['id_relationship_category']);
            $result[$k]['id_relationship_category_remainder']=pk_encrypt($v['id_relationship_category_remainder']);
            $result[$k]['relationship_category_id']=pk_encrypt($v['relationship_category_id']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function relationshipCategoryRemainder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        //$this->form_validator->add_rules('days', array('required'=>$this->lang->line('days_req')));
        $this->form_validator->add_rules('relationship_category_id', array('required'=>$this->lang->line('relationship_category_id_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }


        if(isset($data['relationship_category_id']) && is_array($data['relationship_category_id'])) {
            foreach($data['relationship_category_id'] as $k=>$v){
                $data['relationship_category_id'][$k]['id']=pk_decrypt($v['id']);
                // if($this->session_user_info->user_role_id!=1 && !in_array($data['relationship_category_id'][$k]['id'],$this->session_user_customer_relationship_categories)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
                // if($this->session_user_info->user_role_id==1 && !in_array($data['relationship_category_id'][$k]['id'],$this->session_user_wadmin_relationship_categories)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
        }
        $existing_categories = $this->Customer_model->getRelationshipCategoryRemainder(array('customer_id' => $data['customer_id']));
        $add = $update = array();
        for($s=0;$s<count($data['relationship_category_id']);$s++)
        {
            for($sr=0;$sr<count($existing_categories);$sr++)
            {
                if($data['relationship_category_id'][$s]['id']==$existing_categories[$sr]['id_relationship_category'] && $existing_categories[$sr]['id_relationship_category_remainder']!=''){
                    $update[] = array(
                        'id_relationship_category_remainder' => $existing_categories[$sr]['id_relationship_category_remainder'],
                        'days' => $data['relationship_category_id'][$s]['days'],
                        'r2_days' => $data['relationship_category_id'][$s]['r2_days'],
                        'r3_days' => $data['relationship_category_id'][$s]['r3_days'],
                        'updated_by' => $data['updated_by'],
                        'updated_on' => currentDate()
                    );
                }
                else if($data['relationship_category_id'][$s]['id']==$existing_categories[$sr]['id_relationship_category']){
                    $add[] = array(
                        'customer_id' => $data['customer_id'],
                        'days' => $data['relationship_category_id'][$s]['days'],
                        'r2_days' => $data['relationship_category_id'][$s]['r2_days'],
                        'r3_days' => $data['relationship_category_id'][$s]['r3_days'],
                        'relationship_category_id' => $data['relationship_category_id'][$s]['id'],
                        'updated_by' => $data['updated_by'],
                        'updated_on' => currentDate()
                    );
                }
            }
        }

        if(!empty($add))
            $this->Customer_model->addRelationshipRemainder($add);

        if(!empty($update))
            $this->Customer_model->updateRelationshipRemainder($update);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('info_save'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function workflowRemainder_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->User_model->check_record('relationship_category_remainder',array('customer_id'=>$data['customer_id'],'relationship_category_id'=>null));
        //echo '<pre>'.
        foreach($result as $k=>$v){
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$k]['id_relationship_category_remainder']=pk_encrypt($v['id_relationship_category_remainder']);
            $result[$k]['updated_by']=pk_encrypt($v['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function workflowRemainder_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1){
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
            if($data['updated_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $add_update = array(
            'customer_id' => $data['customer_id'],
            'days' => $data['days'],
            'r2_days' => $data['r2_days'],
            'r3_days' => $data['r3_days'],
            'updated_by' => $data['updated_by'],
            'updated_on' => currentDate()
        );
        if(isset($data['id_relationship_category_remainder'])){
            $data['id_relationship_category_remainder'] = pk_decrypt($data['id_relationship_category_remainder']);
            $this->User_model->update_data('relationship_category_remainder',$add_update,array('id_relationship_category_remainder'=>$data['id_relationship_category_remainder']));
        }else{
            $this->User_model->insert_data('relationship_category_remainder',$add_update);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('info_save'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function dashboard_get()
    {
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if(isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if(isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['contract_owner_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if(!in_array($data['contract_id'],$this->session_user_contracts)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt($data['responsible_user_id']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['responsible_user_id'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['business_unit_id']) && !is_array($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if(!in_array($data['business_unit_id'],$this->session_user_business_units)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id']=array();
        if(in_array($this->session_user_info->user_role_id,array(3,4,8))){
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
            $business_unit_array = $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            $data['session_user_role']=$this->session_user_info->user_role_id;
            $data['session_user_id']=$this->session_user_id;
        }
        if($this->session_user_info->user_role_id==6){
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if($this->session_user_info->user_role_id == 7){
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if(count($data['business_unit_id'])==0)
            unset($data['business_unit_id']);

        $result_array = array();

        $data['can_access'] = 1;
        $data['get_all_records'] = true;

        /*     
            //Counting only reviews
            $activities_count = 0;
            foreach($this->Contract_model->getContractList($data)['data'] as $vd){
                if($vd['can_review'] == 1)
                    $activities_count++;
            }
        */
        $data['can_review']=1;
		//echo '<pre>ddd';exit;
        // $all_activities = $this->Contract_model->getContractList($data);//echo 
        $all_activities = $this->Contract_model->dashboardActivityCount($data);//echo 
        
        unset($data['can_review']);
        //$result_array['contracts_count'] = $all_activities['total_records'];
        $result_array['contracts_count'] = $all_activities[0]['dashboardActivityCount'];
        
        $main_currency=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));
        $data['end_date_lessthan_90'] = 90;
        $data['contract_active_status'] = 'Active';
        $result_array['end_date_lessthan_90'] = $this->Contract_model->getAllContractList($data)['total_records'];
        $result_array['end_date'] = array();
        if(true){
            //End date Graph / Widget
            $result_array['end_date']['ending_in_90_days'] = $result_array['end_date_lessthan_90'];
            unset($data['end_date_lessthan_90']);
            $all_contracts = $this->Contract_model->getAllContractList($data);//echo '<pre>'.$this->db->last_query();
            // $result_array['end_date']['contracts'] = $all_contracts['data'];
            $result_array['end_date']['all_contracts'] = $all_contracts['total_records'];
            $result_array['end_date']['created_this_month'] = 0;
            $result_array['end_date']['ending_this_month'] = 0;
            $result_array['end_date']['automatic_prolongation'] = 0;
            $result_array['end_date']['total_projected_spend'] = 0;
            foreach($all_contracts['data'] as $ak => $av){
                // echo $av['created_on'].'=='.date('my',strtotime($av['created_on'])).' == '.date('my').PHP_EOL;
                // echo 'AR='.$av['auto_renewal'].PHP_EOL;
                if(date('my',strtotime($av['created_on'])) == date('my'))
                    $result_array['end_date']['created_this_month']++;
                if(date('my',strtotime($av['contract_end_date'])) == date('my'))
                    $result_array['end_date']['ending_this_month']++;
                if((int)$av['auto_renewal'])
                    $result_array['end_date']['automatic_prolongation']++;

                //Adding sum up
                $graph = $this->spent_mngment_graph('spent_line','Actual Spent',$av);
                $Projected_value= 0;
                $Projected_value = array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset));
                $exg_rate=1;
                $exg_rate=str_replace(',','.',$av['euro_equivalent_value']);
                if($av['currency_name']==$main_currency[0]['currency_name']|| $exg_rate==0){
                    $exg_rate=1;
                }
                $result_array['end_date']['total_projected_spend'] += $Projected_value * $exg_rate;
                // $result_array['end_date']['total_projected_spend'] += $Projected_value;
            }            
        }
        // echo '<pre>'.
        unset($data['end_date_lessthan_90']);
        unset($data['contract_active_status']);
        // all project begin
        $data['end_date_lessthan_90'] = 90;
        $data['type'] = 'project';
        $data['project_status'] = 1;
        $project_end_date_lessthan_90 = $this->Contract_model->getAllContractList($data)['total_records'];
        $result_array['projects']['ending_in_90_days'] = $project_end_date_lessthan_90;
        unset($data['end_date_lessthan_90']);
        $data['end_date_lessthan_180'] = 180;
        $end_date_lessthan_180 = $this->Contract_model->getAllContractList($data)['total_records'];
        $result_array['projects']['ending_in_180_days'] = $end_date_lessthan_180;
        unset($data['end_date_lessthan_180']);
        $projects = $this->Contract_model->getAllContractList($data);//echo '<pre>'.$this->db->last_query();
        // $result_array['end_date']['contracts'] = $all_projects['data'];
        $result_array['projects_count'] = $projects['total_records'];
        $result_array['projects']['projects'] = $projects['total_records'];
        $result_array['projects']['created_this_month'] = 0;
        $result_array['projects']['ending_this_month'] = 0;
        $result_array['projects']['total_projected_spend'] = 0;
        foreach($projects['data'] as $ak => $av){
            //print_r($av);exit;
            if(date('my',strtotime($av['created_on'])) == date('my'))
            {
                $result_array['projects']['created_this_month']++;
            }
            if(date('my',strtotime($av['contract_end_date'])) == date('my'))
            {
                $result_array['projects']['ending_this_month']++;
            }
            $exg_rate=1;
            $exg_rate=str_replace(',','.',$av['euro_equivalent_value']);
            if($av['currency_name']==$main_currency[0]['currency_name']|| $exg_rate==0){
                $exg_rate=1;
            }     
            //Adding sum up
             $result_array['projects']['total_projected_spend'] += $av['Projected_value'] * $exg_rate;
        }  
        unset($data['type']); 
        unset($data['project_status']); 
        //all project end
        $data['contract_status'] = 'pending review,review in progress';
        /*$pending_reviews = $this->Contract_model->getContractList($data);
        $result_array['contracts'] = $pending_reviews;*/
        //$result_array['to_be_reviewed'] = $this->Contract_model->getContractListCount($data);
        $data['contract_status'] = 'pending review';
        $result_array['pending_reviews'] = $this->Contract_model->getContractListCount($data);

        unset($data['business_unit_id']);
        unset($data['delegate_id']);
        unset($data['responsible_user_id']);
        unset($data['customer_user']);
        $data['business_unit_id']=array();
        if(isset($data['user_role_id']) && isset($data['id_user'])){
            if($data['user_role_id']==2){

            }
            else if($data['user_role_id']==3 || $data['user_role_id']==8){
                $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
                $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
                $contributor_modules = $this->User_model->check_record('contract_user',array('status'=>1,'user_id'=>$this->session_user_id));
                // echo 
                if(count($contributor_modules) > 0)
                    $data['module_id'] = array_filter(array_map(function($i){ return $i['module_id']; },$contributor_modules));
            }
            else if($data['user_role_id']==4) {
                $data['delegate_id'] = $data['id_user'];
                $contributor_modules = $this->User_model->check_record('contract_user',array('status'=>1,'user_id'=>$this->session_user_id));
                if(count($contributor_modules) > 0)
                    $data['module_id'] = array_filter(array_map(function($i){ return $i['module_id']; },$contributor_modules));
            }
            else if($data['user_role_id']==6){
                $data['business_unit_id'] = $this->User_model->check_record('business_unit_user',array('user_id'=>$this->session_user_id,'status'=>1));
                $data['business_unit_id'] = array_map(function($i){ return $i['business_unit_id']; },$data['business_unit_id']);
                if($this->session_user_info->is_allow_all_bu==1){
                    $bu_ids = $this->User_model->check_record_selected('GROUP_CONCAT(id_business_unit) as bu_ids','business_unit',array('status'=>1,'customer_id'=>$this->session_user_info->customer_id));
                    $data['business_unit_id'] = explode(',',$bu_ids[0]['bu_ids']);
                }
            }
        }
        /*$action_items = $this->Contract_model->getActionItems($data);
        $result_array['action_items'] = $action_items;*/
        //print_r($data['id_user']); exit;
        
        unset($data['contract_status']);
        $data['contract_review_action_item_status']='open';
        $data['item_status']=1;
        //unset($data['responsible_user_id']);
        //$data['created_by'] = $loggedinuser;
        $result_array['action_item'] = array();
        if(true){
            //Bar Graph Dashboard Start
            // print_r($data);exit;
            $data['priority']='Urgent';
            // print_r($data);exit;
            $urgent_count = (int)$this->Contract_model->getActionItemsCount($data);//echo 
            $data['priority']='Medium';
            $medium_count = (int)$this->Contract_model->getActionItemsCount($data);
            $data['priority']='Low';
            $low_count = (int)$this->Contract_model->getActionItemsCount($data); 
            $data['priority']='';
            $NotClassified = (int)$this->Contract_model->getActionItemsCount($data);//echo '<pre>'.
            unset($data['priority']);
            $data['type']='overdue';
            $overdue_count = (int)$this->Contract_model->getActionItemsCount($data);
            unset($data['type']);
            $priority_count[0]['value']=$low_count;
            $priority_count[0]['label']= $this->lang->line('low');
            $priority_count[0]['color']="36a921";
            $priority_count[0]['link']=WEB_BASE_URL."#/action-items?priority=Low";
            $priority_count[1]['value']=$medium_count;
            $priority_count[1]['label']= $this->lang->line('medium');
            $priority_count[1]['color']="ff9900";
            $priority_count[1]['link']=WEB_BASE_URL."#/action-items?priority=Medium";
            $priority_count[2]['value']=$urgent_count;
            $priority_count[2]['label']= $this->lang->line('urgent');
            $priority_count[2]['color']="f20505";
            $priority_count[2]['link']=WEB_BASE_URL."#/action-items?priority=Urgent";
            $priority_count[3]['value']=$NotClassified;
            $priority_count[3]['label']= $this->lang->line('not_classified');
            $priority_count[3]['color']="cccccc";
            $priority_count[3]['link']=WEB_BASE_URL."#/action-items?priority=Not-classified";

            $result_array['action_item']['counts']['ovredue_count']=$overdue_count;
            //Bar Graph Dashboard End
            $result_array['action_items_count'] = $result_array['action_item']['counts']['action_items_count'] = (int)$this->Contract_model->getActionItemsCount($data);
            //echo '<pre>'.
            $result_array['action_item']['graph']=$priority_count;
        }
        $result_array['all_activity'] = array();
        if(true){
            //Pie Chart for All-Activites
            if(in_array($this->session_user_info->user_role_id,array(3,4,8))){
                $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $this->session_user_id,'status' => '1'));
                // echo 
                $business_unit_id = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            }
            if($this->session_user_info->user_role_id==6){
                $data['business_unit_id'] = $this->session_user_business_units;
                if(count($data['business_unit_id'])==0 && $this->session_user_info->is_allow_all_bu==0)
                {
                    $data['business_unit_id']=array(0);
                }
            }
            $pie_input = array(
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=1&status=pending review','label' => 'Reviews to Initiate','color' => 'e78200','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>1,'contract_status'=>'pending review','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=1&status=review in progress','label' => 'Reviews in Progress','color' => 'e78200','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>1,'contract_status'=>'review in progress','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=1&status=review finalized','label' => 'Reviews Finalized','color' => 'e78200','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>1,'contract_status'=>'review finalized','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'parent_contract_id' => 0,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=1&status=new','label' => 'New Reviews','color' => 'e78200','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>1,'contract_status'=>'new','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=2&status=new','label' => 'New Task','color' => '5bb166','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>2,'contract_status'=>'new','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=2&status=pending workflow','label' => 'Tasks to Initiate','color' => '5bb166','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>2,'contract_status'=>'pending workflow','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=2&status=workflow in progress','label' => 'Tasks in Progress','color' => '5bb166','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>2,'contract_status'=>'workflow in progress','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1)),
                array('link'=>WEB_BASE_URL.'#/all-activities?activity_filter=2&status=workflow finalized','label' => 'Tasks Finalized','color' => '5bb166','filter'=>array('customer_id'=>$this->session_user_info->customer_id,'activity_filter'=>2,'contract_status'=>'workflow finalized','business_unit_id'=>$business_unit_id,'get_all_records'=>1,'can_access'=>1))
            );
            $all_reviews_count = $all_workflows_count = 0;
            foreach($pie_input as $pk => $pv){
                // print_r($pv['filter']['can_review']);exit;
                $pv['filter']['can_review']=1;
                $result_array['all_activity']['graph'][$pk]['label'] = $pv['label'];
                $result_array['all_activity']['graph'][$pk]['color'] = $pv['color'];
                //$query_result = $this->Contract_model->getContractList($pv['filter']);
                $query_result = $this->Contract_model->dashboardActivityCount($pv['filter']);
                
                // print_r($pv['filter']);
                // echo PHP_EOL.
                $count = $query_result[0]['dashboardActivityCount'];
                // $result_array['all_activity']['graph'][$pk]['value'] = $query_result['total_records'];
                $result_array['all_activity']['graph'][$pk]['value'] = $count;
                //counting revies, workflows
                if($pv['filter']['activity_filter']==2){
                    // $result_array['all_activity']['graph'][$pk]['name'] = "workflow";
                    //$all_workflows_count += $query_result['total_records'];
                    $all_workflows_count += $count;
                }
                else{
                    // $result_array['all_activity']['graph'][$pk]['name'] = "review";
                    //$all_reviews_count += $query_result['total_records'];
                    $all_reviews_count += $count;
                }
            }
            $result_array['all_activity']['counts']['all_reviews_count'] = $all_reviews_count;
            $result_array['all_activity']['counts']['all_workflows_count'] = $all_workflows_count;
        }
        $new_graph_obj = array();
        foreach($result_array['all_activity']['graph'] as $gk => $gv){
            if($gv['value'] > 0){
                $new_graph_obj[] = $gv;
            }
        }
        $result_array['all_activity']['graph'] = $new_graph_obj;
        
        unset($data['created_by']); // this is only for counting Action items of a user.
        //echo '<pre>'.
        $data['user_role_not']=array();
        if($data['user_role_id']==1){
            $data['user_role_not']=array(1);
        }
        if($data['user_role_id']==2){
            $data['user_role_not']=array(1,2);
        }
        if($data['user_role_id']==3){
            $data['user_role_not']=array(1,2,3,6);
        }
        if($data['user_role_id']==4){
            $data['user_role_not']=array(1,2,6);
            //$data['user_contracts']=$this->session_user_contracts;
        }
        if($data['user_role_id']==5){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        if($data['user_role_id']==6){
            $data['user_role_not']=array(1,2);
        }
        if($data['user_role_id']==7){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        // print_r($data);exit;
        $user_list_array=array(
            'user_role_not' => $data['user_role_not'],
            // 'user_type' => 'internal',
            'business_unit_array' => $business_unit_array,
            'customer_id' => $data['customer_id']
        );
        $data['user_type']=='internal';
        if($data['user_role_id']==6){
            // print_r($data['business_unit_id']);exit;
            $user_list_array['buids']=$data['business_unit_id'];
            // $user_list_array['user_type']='external';

        }
        $user_list_result = $this->Customer_model->getCustomerUserList($user_list_array);//echo 
        // echo '<pre>'.print_r($user_list_result);exit;
        // $result_array['co_workers'] = $this->Customer_model->getUserCount(array('customer_id' => $data['customer_id'],'user_role_id_not' => $user_role_id_not,'business_unit_array'=>$data['business_unit_id'],'user_role_id'=>$data['user_role_id'],'user_contracts'=>$this->session_user_contracts));
        $result_array['co_workers'] = $user_list_result['total_records'];
        // if($data['user_role_id']==4){
        //    $result_array['co_workers'] = $this->Customer_model->getDelegateCoworkers(array('user_contracts'=>$this->session_user_contracts,'business_unit_array'=>$this->session_user_business_units));
        // }
        //echo '<pre>'.print_r($this->session_user_contracts);exit;
        if($this->session_user_info->user_role_id == 3){ 
            $session_user_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            foreach($session_user_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
        }else if($this->session_user_info->user_role_id == 4){ 
            $session_user_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            foreach($session_user_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
            $delegate_contracts = $this->User_model->check_record('contract',array('delegate_id'=>$this->session_user_id,'is_deleted'=>0));
            foreach($delegate_contracts as $v)
                $data['contracts_array'][] = $v['id_contract'];
        }else{
            $data['contracts_array'] = $this->session_user_contracts;
        }

        //$contributor_count = $this->Customer_model->getDelegateContributorsCount(array('user_contracts'=>$data['contracts_array'],'user_id'=>$this->session_user_id));
        //echo '<pre>'.
        //Contributor counts for delegate user
        if($this->session_user_info->user_role_id == 3){ 
            $session_user_contributing_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $owner_contracts = $this->User_model->check_record('contract',array('contract_owner_id'=>$this->session_user_id,'is_deleted'=>0));
            foreach($session_user_contributing_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
            foreach($owner_contracts as $v)
                $data['contracts_array'][] = $v['id_contract'];
        }else if($this->session_user_info->user_role_id == 4){ 
            $session_user_contributing_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $delegate_contracts = $this->User_model->check_record('contract',array('delegate_id'=>$this->session_user_id,'is_deleted'=>0));
            foreach($session_user_contributing_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
            foreach($delegate_contracts as $v)
                $data['contracts_array'][] = $v['id_contract'];
        }else{
            $data['contracts_array'] = $this->session_user_contracts;
        }
        $data['user_id'] = $this->session_user_id;
        // print_r($data);exit;
        $contributors = $this->Contract_model->getDelegateContributors($data);
        //  echo '<pre>'.
        $result_array['contributors'] = (int)$contributors['total_records'];
        
        // echo '<pre>'.print_r($contributors);exit;
        if(true){
            $result_array['co_workers_obj']['counts']['all_co_workers'] = $result_array['co_workers'];
            $result_array['co_workers_obj']['counts']['all_contributors'] = (int)$contributors['total_records'];
            $result_array['co_workers_obj']['top_contributions'] = $contributors['top_contributions'];
            $experts = $validators = $providers = 0;
            foreach($contributors['data'] as $ck => $cv){
                if((int)$cv['contribution_type'] == 0)
                    $experts++;
                if((int)$cv['contribution_type'] == 1)
                    $validators++;
                if((int)$cv['contribution_type'] == 3)
                    $providers++;
            }
            // print_r($contributors['data']);exit;
            $result_array['co_workers_obj']['graph'][0]['label'] = $this->lang->line('expert');
            $result_array['co_workers_obj']['graph'][0]['value'] = $experts;
            $result_array['co_workers_obj']['graph'][0]['link'] = $this->session_user_info->user_role_id == 6?'':WEB_BASE_URL.'#/contributors?contribution_type=0';
            $result_array['co_workers_obj']['graph'][0]['color'] = '4472c4';
            $result_array['co_workers_obj']['graph'][1]['label'] = $this->lang->line('validator');
            $result_array['co_workers_obj']['graph'][1]['value'] = $validators;
            $result_array['co_workers_obj']['graph'][1]['link'] = $this->session_user_info->user_role_id == 6?'':WEB_BASE_URL.'#/contributors?contribution_type=1';
            $result_array['co_workers_obj']['graph'][1]['color'] = '4472c4';
            $result_array['co_workers_obj']['graph'][2]['label'] = $this->lang->line('relation');
            $result_array['co_workers_obj']['graph'][2]['value'] = $providers;
            $result_array['co_workers_obj']['graph'][2]['link'] = $this->session_user_info->user_role_id == 6?'':WEB_BASE_URL.'#/contributors?contribution_type=3';
            $result_array['co_workers_obj']['graph'][2]['color'] = '4472c4';
        }
        
        
        $providersdetails = $this->Customer_model->getproviderlist(array("customer_id"=>$data['customer_id'],"can_access"=>1));
        $providers = $providersdetails['data'];
        $risk_profile_green = 0;
        $risk_profile_red = 0;
        $risk_profile_amber = 0;
        $risk_profile_na = 0;
        $approval_status_green = 0;
        $approval_status_red = 0;
        $approval_status_amber = 0;
        $approval_status_na = 0;
        $finacial_health_green = 0;
        $finacial_health_red = 0;
        $finacial_health_amber = 0;
        $finacial_health_na = 0;
        $result_array['providers']['count']['total_spent']=0;
        $labels = $this->User_model->custom_query('select tag_text from tag t LEFT JOIN tag_language tl on tl.tag_id = t.id_tag WHERE t.type="provider_tags" and t.is_fixed=1 and customer_id='.$this->session_user_info->customer_id.' ORDER BY label asc');
        if(!empty($labels))
        {
            $providerLables=array_column($labels, 'tag_text');
        }
        else{
            $providerLables = array('Risk Profile','Approval Status','Finacial Health');
        }

        foreach ($providers as $provider) {
           if($provider['approval_status'] == "G") { $approval_status_green++;}
           if($provider['approval_status'] == "R") { $approval_status_red++;}
           if($provider['approval_status'] == "A") { $approval_status_amber++;}
           if($provider['approval_status'] == "N/A") { $approval_status_na++;}
           if($provider['risk_profile'] == "G") { $risk_profile_green++;}
           if($provider['risk_profile'] == "R") { $risk_profile_red++;}
           if($provider['risk_profile'] == "A") { $risk_profile_amber++;}
           if($provider['risk_profile'] == "N/A") { $risk_profile_na++;}
           $total_amount_pr = 0;
           if(!empty($provider['contract_ids'])){
                $contracts_ids = $this->Customer_model->getProviderContracts(array("customer_id"=>$this->session_user_info->customer_id,'provider_id'=>$provider['id_provider']));
                $contrat_ids=array_map(function($i){ return (int)$i['id_contract'];},$contracts_ids);
                //$contrat_ids=explode(',',$provider['contract_ids']);
                if(!empty($contrat_ids))
                {
                    $amount = $this->Customer_model->getProviderTotalSpent(array("contract_ids"=>$contrat_ids,'customer_id'=>$data['customer_id']));
                     $exg_rate_pr=1;
                     foreach ($amount as $at=>$av){
                         $exg_rate_pr=str_replace(',','.',$av['euro_equivalent_value']);
                         if($main_currency[0]['currency_name']==$av['currency_name'] || $exg_rate_pr==0){
                             $exg_rate_pr=1;
                         }
                         // else{
                         //     $exg_rate_pr=str_replace(',','.',$av['euro_equivalent_value']);
                         // }
                         $total_amount_pr +=($av['Additional_Reccuring_fees_value']+$av['ProjectedValue']+$av['additonal_one_off_fees'])*$exg_rate_pr;
                     }
                }
               
                }
            //echo $total_amount_pr;echo "<br>";
         $result_array['providers']['count']['total_spent']+= $total_amount_pr;
        }
        $provider_approval_status_graph[0]['value']=$approval_status_green;
        $provider_approval_status_graph[0]['label']= $this->lang->line('green');
        $provider_approval_status_graph[0]['color']="36a921";
        $provider_approval_status_graph[0]['link']=WEB_BASE_URL."#/provider?approval_status=G";
        $provider_approval_status_graph[1]['value']=$approval_status_amber;
        $provider_approval_status_graph[1]['label']= $this->lang->line('amber');
        $provider_approval_status_graph[1]['color']="ff9900";
        $provider_approval_status_graph[1]['link']=WEB_BASE_URL."#/provider?approval_status=A";
        $provider_approval_status_graph[2]['value']=$approval_status_red;
        $provider_approval_status_graph[2]['label']= $this->lang->line('red');
        $provider_approval_status_graph[2]['color']="f20505";
        $provider_approval_status_graph[2]['link']=WEB_BASE_URL."#/provider?approval_status=R";
        $provider_approval_status_graph[3]['value']=$approval_status_na;
        $provider_approval_status_graph[3]['label']= $this->lang->line('n_a');
        $provider_approval_status_graph[3]['color']="cccccc";
        $provider_approval_status_graph[3]['link']=WEB_BASE_URL."#/provider?approval_status=N/A";
       // $result_array['action_item']['graph'] =$provider_approval_status_graph;
        $result_array['providers']['provider_approval_status_graph']=$provider_approval_status_graph;
        $provider_risk_profile_graph[0]['value']=$risk_profile_green;
        $provider_risk_profile_graph[0]['label']= $this->lang->line('green');;
        $provider_risk_profile_graph[0]['color']="36a921";
        $provider_risk_profile_graph[0]['link']=WEB_BASE_URL."#/provider?risk_profile=G";
        $provider_risk_profile_graph[1]['value']=$risk_profile_amber;
        $provider_risk_profile_graph[1]['label']= $this->lang->line('amber');
        $provider_risk_profile_graph[1]['color']="ff9900";
        $provider_risk_profile_graph[1]['link']=WEB_BASE_URL."#/provider?risk_profile=A";
        $provider_risk_profile_graph[2]['value']=$risk_profile_red;
        $provider_risk_profile_graph[2]['label']= $this->lang->line('red');
        $provider_risk_profile_graph[2]['color']="f20505";
        $provider_risk_profile_graph[2]['link']=WEB_BASE_URL."#/provider?risk_profile=R";
        $provider_risk_profile_graph[3]['value']=$risk_profile_na;
        $provider_risk_profile_graph[3]['label']= $this->lang->line('n_a');
        $provider_risk_profile_graph[3]['color']="cccccc";
        $provider_risk_profile_graph[3]['link']=WEB_BASE_URL."#/provider?risk_profile=N/A";
        $result_array['providers']['provider_risk_profile_graph']=$provider_risk_profile_graph;

        $provider_finacial_health_graph[0]['value']=$finacial_health_green;
        $provider_finacial_health_graph[0]['label']= $this->lang->line('green');;
        $provider_finacial_health_graph[0]['color']="36a921";
        $provider_finacial_health_graph[0]['link']=WEB_BASE_URL."#/provider?finacial_health=G";
        $provider_finacial_health_graph[1]['value']=$finacial_health_amber;
        $provider_finacial_health_graph[1]['label']= $this->lang->line('amber');
        $provider_finacial_health_graph[1]['color']="ff9900";
        $provider_finacial_health_graph[1]['link']=WEB_BASE_URL."#/provider?finacial_health=A";
        $provider_finacial_health_graph[2]['value']=$finacial_health_red;
        $provider_finacial_health_graph[2]['label']= $this->lang->line('red');
        $provider_finacial_health_graph[2]['color']="f20505";
        $provider_finacial_health_graph[2]['link']=WEB_BASE_URL."#/provider?finacial_health=R";
        $provider_finacial_health_graph[3]['value']=$finacial_health_na;
        $provider_finacial_health_graph[3]['label']= $this->lang->line('n_a');
        $provider_finacial_health_graph[3]['color']="cccccc";
        $provider_finacial_health_graph[3]['link']=WEB_BASE_URL."#/provider?finacial_health=N/A";
        $result_array['providers']['provider_finacial_health_graph']=$provider_finacial_health_graph;
        $result_array['provider_lables'] = $providerLables;

       // $result_array['action_item']['graph2'] =$provider_risk_profile_graph;
        // $result_array['action_item']['graph'] =$provider_risk_profile_graph;
        $result_array['providers_count'] = $providersdetails['total_records'];
        $result_array['providers']['count']['providers_count'] =$providersdetails['total_records'];
        $result_array['main_currency_name'] = $main_currency[0]['currency_name'];
        //echo '<pre>'.print_r($this->session_user_contracts);exit;
        //echo '<pre>'.
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result_array);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function testemailtemplate_post(){

        //type = preview,testmail
        $data = $this->input->post();
        //echo '<pre>';print_r($data);exit;
        //$wildcards_replaces = json_decode($data['wildcards'],true);
        //$wildcards=json_encode(array_keys($wildcards_replaces));
        $wildcards_replaces=$wildcards=array();
        /*echo '<pre>';print_r($wildcards_replaces);
        echo '<pre>';print_r($wildcards);*/
        //$data['content']=EMAIL_HEADER_CONTENT.$data['content'].EMAIL_FOOTER_CONTENT;
        //$body = wildcardreplace($wildcards, $wildcards_replaces, $data['content']);
        //$subject = wildcardreplace($wildcards, $wildcards_replaces, $data['subject']);
        $body=$data['content'];
        $subject=$data['subject'];
        $from_name = SEND_GRID_FROM_NAME;
        $from = SEND_GRID_FROM_EMAIL;
        $to = $data['to_email'];
        $to_name = $data['to_name'];

        if($data['type']=='testmail'){

            $this->load->library('sendgridlibrary');
            $this->sendgridlibrary->sendemail($from_name, $from, $subject, $body, $to_name, $to, array());
        }

        $result_array = array('body'=>$body,'subject'=>$subject,'from_name'=>$from_name,'from'=>$from,'to'=>$to,'to_name'=>$to_name);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result_array);
        $this->response($result, REST_Controller::HTTP_OK);

    }

    function getDirectorySize($path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if(is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    $nextpath = $path . '/' . $file;
                    if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                        if (is_dir($nextpath)) {
                            $dircount++;
                            $result = $this->getDirectorySize($nextpath);
                            $totalsize += $result;

                        } elseif (is_file($nextpath)) {
                            $totalsize += filesize($nextpath);
                            $totalcount++;
                        }
                    }
                }
                closedir($handle);
            }

        }
        //$total['size'] =     $this->sizeFormat($totalsize);

        return $totalsize;
    }

    function sizeFormat($size)
    {
        if($size<1024)
        {
            return $size." bytes";
        }
        else if($size<(1024*1024))
        {
            $size=round($size/1024,1);
            return $size." KB";
        }
        else if($size<(1024*1024*1024))
        {
            $size=round($size/(1024*1024),1);
            return $size." MB";
        }
        else
        {
            $size=round($size/(1024*1024*1024),1);
            return $size." GB";
        }

    }
    public function userListHistory_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('user_role_id', array('required' => $this->lang->line('user_role_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if($data['user_role_id']!=$this->session_user_info->user_role_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['current_user_not'])) $data['current_user_not']=pk_decrypt($data['current_user_not']);
        if(isset($data['user_role_id']) && in_array($data['user_role_id'],array(3))){
            //3 means bu owner, can able to get own business unit users
            $user_id=isset($data['id_user'])?$data['id_user']:$this->user_id;
            $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $user_id,'status'=>1));
            $business_unit_id = array_map(function($i){ return $i['business_unit_id']; },$business_unit);
            $data['business_unit_array'] = $business_unit_id;
            $data['user_role_not'] = array(3);
        }

        $data = tableOptions($data);//helper function ordering smart table grid option
        $data['user_role_not']=array();
        if($data['user_role_id']==1){
            $data['user_role_not']=array(1);
        }
        if($data['user_role_id']==2){
            $data['user_role_not']=array(1);
        }
        if($data['user_role_id']==3){
            $data['user_role_not']=array(1,2,3,6);
        }
        if($data['user_role_id']==4){
            $data['user_role_not']=array(1,2,3,4,6);
        }
        if($data['user_role_id']==5){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        if($data['user_role_id']==6){
            $data['user_role_not']=array(1,2,3,4,5,6);
        }
        $result = $this->Customer_model->getCustomerUserListHistory($data);
        for($s=0;$s<count($result['data']);$s++)
        {
            if($result['data'][$s]['bu_name']!='')
                $result['data'][$s]['bu_name'] = explode(',',$result['data'][$s]['bu_name']);
            if($result['data'][$s]['business_unit_id']!='') {
                $result['data'][$s]['business_unit_id'] = explode(',', $result['data'][$s]['business_unit_id']);
                foreach($result['data'][$s]['business_unit_id'] as $k=>$v){
                    $result['data'][$s]['business_unit_id'][$k]=pk_encrypt($v);
                }
            }

                $result['data'][$s]['id_user'] = pk_encrypt($result['data'][$s]['id_user']);

        }
        //echo $this->db->last_query(); exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function userHistory_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_user', array('required' => $this->lang->line('id_user_req')));
        $this->form_validator->add_rules('type', array('required' => $this->lang->line('id_user_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])){
            $data['id_user']=pk_decrypt($data['id_user']);
            if($this->session_user_info->user_role_id!=1 && !in_array($data['id_user'],$this->session_user_customer_all_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && !in_array($data['id_user'],$this->session_user_master_users)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if($data['type']=='detail'){
            $this->form_validator->add_rules('from_date', array('required' => $this->lang->line('from_date_req')));
            $this->form_validator->add_rules('to_date', array('required' => $this->lang->line('to_date_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
            $data = tableOptions($data);//helper function ordering smart table grid option
            $result['history'] = $this->Customer_model->getUserLoginHistory($data);
            foreach($result['history']['data'] as $k=>$v){
                $result['history']['data'][$k]['id_user']=pk_encrypt($result['history']['data'][$k]['id_user']);
            }
            $result['user_info'] = $this->User_model->getUserInfo(array('user_id'=>$data['id_user']));
            $result['user_info']->customer_id=pk_encrypt($result['user_info']->customer_id);
            $result['user_info']->id_user=pk_encrypt($result['user_info']->id_user);
            $result['user_info']->user_role_id=pk_encrypt($result['user_info']->user_role_id);


        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function listCustomers_get(){
        $data = $this->input->get();
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $result = $this->Customer_model->customerList($data);
        for($s=0;$s<count($result['data']);$s++)
        {
            /*getImageUrl helper function for getting image usrl*/
            $result['data'][$s]['company_logo'] = getImageUrl($result['data'][$s]['company_logo'],'company',SMALL_IMAGE);
            $result['data'][$s]['users_count']=$this->User_model->getUserCount(array('customer_id'=>$result['data'][$s]['id_customer']));
            $result['data'][$s]['bu_owners_count']=$this->User_model->getUserCount(array('customer_id'=>$result['data'][$s]['id_customer'],'role'=>3));
            $result['data'][$s]['bu_delegates_count']=$this->User_model->getUserCount(array('customer_id'=>$result['data'][$s]['id_customer'],'role'=>4));
            $result['data'][$s]['contributors_count']=$this->User_model->getUserCount(array('customer_id'=>$result['data'][$s]['id_customer'],'role'=>5));
            $result['data'][$s]['reporting_owners_count']=$this->User_model->getUserCount(array('customer_id'=>$result['data'][$s]['id_customer'],'role'=>6));
            $business_unit = $this->Business_unit_model->getBusinessUnitList(array('customer_id'=>$result['data'][$s]['id_customer']));
            $result['data'][$s]['business_unit_count']=$business_unit['total_records'];
            $path = FCPATH.'uploads/'.$result['data'][$s]['id_customer'];
            $path_storage=$this->getDirectorySize($path);
            $path_new = FILE_SYSTEM_PATH.'uploads/'.$result['data'][$s]['id_customer'];
            $path_new_storage=$this->getDirectorySize($path_new);
            $result['data'][$s]['storage']['size'] = $this->sizeFormat($path_storage+$path_new_storage);
            $result['data'][$s]['country_id']=pk_encrypt($result['data'][$s]['country_id']);
            $result['data'][$s]['created_by']=pk_encrypt($result['data'][$s]['created_by']);
            $result['data'][$s]['id_customer']=pk_encrypt($result['data'][$s]['id_customer']);
            $result['data'][$s]['template_id']=pk_encrypt($result['data'][$s]['template_id']);
            $result['data'][$s]['updated_by']=pk_encrypt($result['data'][$s]['updated_by']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function actionList_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('access_token', array('required'=>$this->lang->line('access_token_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $data = tableOptions($data);
        $result = $this->User_model->getActionList($data);
        foreach($result['data'] as $k=>$v){
            $result['data'][$k]['acting_user_id']=pk_encrypt($v['acting_user_id']);
            $result['data'][$k]['id']=pk_encrypt($v['id']);
            $result['data'][$k]['id_access_log']=pk_encrypt($v['id_access_log']);
            $result['data'][$k]['user_id']=pk_encrypt($v['user_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function insertEmailTemplatesManually_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $this->form_validator->add_rules('id_customer', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) $data['created_by']=pk_decrypt($data['created_by']);
        if(isset($data['id_customer']) && $data['id_customer']!='all') $data['id_customer']=pk_decrypt($data['id_customer']);
        $email_template = $this->Customer_model->EmailTemplateList(array('customer_id' => 0,'language_id' =>1,'status'=>'0,1'));
        $email_template = $email_template['data'];
        if($data['id_customer']=='all') {
            $customers = $this->Customer_model->customerList(array());
            $customers=$customers['data'];
        }
        else{
            $customers[0]['id_customer']=$data['id_customer'];

        }
        for($s=0;$s<count($email_template);$s++)
        {
            foreach($customers as $kc=>$vc) {
                $check_email_template_already_exist = $this->Customer_model->EmailTemplateList(array('customer_id' => $vc['id_customer'], 'language_id' => 1, 'status' => '0,1', 'parent_email_template_id' => $email_template[$s]['id_email_template']));
                $check_email_template_already_exist = $check_email_template_already_exist['total_records'];
                if ($check_email_template_already_exist == 0) {
                    $inserted_id = $this->Customer_model->addEmailTemplate(array(
                        'module_name' => $email_template[$s]['module_name'],
                        'module_key' => $email_template[$s]['module_key'],
                        'wildcards' => $email_template[$s]['wildcards'],
                        'email_from_name' => $email_template[$s]['email_from_name'],
                        'email_from' => $email_template[$s]['email_from'],
                        'status' => $email_template[$s]['status'],
                        'parent_email_template_id' => $email_template[$s]['id_email_template'],
                        'customer_id' => $vc['id_customer'],
                        'created_by' => $data['created_by'],
                        'created_on' => currentDate()
                    ));

                    $this->Customer_model->addEmailTemplateLanguage(array(
                        'email_template_id' => $inserted_id,
                        'template_name' => $email_template[$s]['template_name'],
                        'template_subject' => $email_template[$s]['template_subject'],
                        'template_content' => $email_template[$s]['template_content'],
                        'language_id' => $email_template[$s]['language_id']
                    ));
                }
            }
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function dailyupdates_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        //$this->form_validator->add_rules('to_date', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $result = $this->Customer_model->getDailyUpdates($data);
        foreach($result as $k=>$v){
            $result[$k]['id_daily_update_customer']=pk_encrypt($v['id_daily_update_customer']);
            $result[$k]['customer_id']=pk_encrypt($v['customer_id']);
        }
        $this->Customer_model->updatedailynotificationcount($data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }
    //*** comment for new service 6.4 sprint  ***/
    // public function addprovider_post(){
    //     $data = $this->input->post();
    //     if(empty($data)){
    //         $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }
    //     $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
    //     $this->form_validator->add_rules('provider_name', array('required'=>$this->lang->line('provider_name_req')));
    //     $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));

    //     $validated = $this->form_validator->validate($data);
    //     if($validated != 1)
    //     {
    //         $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }
    //     if(isset($data['customer_id'])) {
    //         $data['customer_id'] = pk_decrypt($data['customer_id']);
    //         if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
    //             $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
    //             $this->response($result, REST_Controller::HTTP_OK);
    //         }
    //         if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
    //             $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
    //             $this->response($result, REST_Controller::HTTP_OK);
    //         }
    //     }

    //     if(isset($data['country_id'])) {
    //         $data['country_id'] = pk_decrypt($data['country_id']);
    //         // if(!in_array($data['country_id'],$this->session_user_master_countries)){
    //         //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
    //         //     $this->response($result, REST_Controller::HTTP_OK);
    //         // }
    //     }
    //     if(isset($data['created_by'])) {
    //         $data['created_by'] = pk_decrypt($data['created_by']);
    //     }
    //     if(isset($data['country'])) {
    //         $data['country'] = pk_decrypt($data['country']);
    //     }

    //     $name_exists = $this->User_model->check_record('provider',array('provider_name'=>$data['provider_name'],'customer_id'=>$data['customer_id']));
 
    //     if(!empty($name_exists)){
    //         $result = array('status'=>FALSE, 'error' =>array('message' => $this->lang->line('provider_exists')), 'data'=>'6');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }

    //     $add_data = array(
    //         'provider_name' => $data['provider_name'],
    //         'description' => isset($data['description'])?$data['description']:NULL,
    //         'company_address' => isset($data['company_address'])?$data['company_address']:NULL,
    //         'city' => isset($data['city'])?$data['city']:NULL,
    //         'country' => isset($data['country'])?$data['country']:NULL,
    //         'postal_code' => isset($data['postal_code'])?$data['postal_code']:NULL,
    //         'customer_id' => $data['customer_id'],
    //         'status' => 1,
    //         'created_on' => currentDate(),
    //         'created_by' => $data['created_by']
    //     );

    //     $insert_id = $this->Customer_model->addprovider($add_data);
    //     if($insert_id){
    //         $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_add'), 'data'=>'4');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }else{
    //         $result = array('status'=>FALSE, 'error' =>array('message' => $this->lang->line('provider_failed')), 'data'=>'5');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }
    // }


    //* new service for 6.4 sprint*//

    public function addprovider_post()
    {
        $data = $this->input->post();
        if(isset($data['provider'])){
            $data = $data['provider'];
        }
        //print_r($data); exit;
        if(isset($_FILES['file']))
            $totalFilesCount = count($_FILES['file']['name']);
        else
            $totalFilesCount=0;
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('provider_name', array('required'=>$this->lang->line('provider_name_req')));
        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        //print_r($data['grouped_tags']);exit;
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
         }
         if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
         }

         if(isset($data['country'])) {
             $data['country'] = pk_decrypt($data['country']);
         }
        if(isset($data['category_id'])){
            $data['category_id']=pk_decrypt($data['category_id']);
        }
        // if(!empty($data['unique_id'])){
        //     $check_unique_id_exist=$this->User_model->Check_record('provider',array('unique_id'=>$data['unique_id'],'customer_id'=>$data['customer_id']));
        //     if(!empty($check_unique_id_exist)){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('unique_id_exists')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK); 
        //     }
        // }
        // $providers_count=$this->User_model->check_record_selected('count(*) as count','provider',array('customer_id'=>$this->session_user_info->customer_id));
        // $data['unique_id'] ='PR'.str_pad($providers_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
        $data['unique_id'] = uniqueId(array('module' => 'provider' , 'customer_id' => $this->session_user_info->customer_id));
        // print_r($data);exit;
         $add = array(
            'provider_name' => $data['provider_name'],
            'unique_id' => isset($data['unique_id'])?$data['unique_id']:'',
            'company_address'=>$data['company_address'],
            'vat'=>$data['vat'],
            'description'=>$data['description'],
            'city'=>$data['city'],
            'country' => $data['country'],
            'postal_code' => $data['postal_code'],
            'category_id'=>$data['category_id'],
            'customer_id'=>$data['customer_id'],
            'created_on'=>currentDate(),
            'created_by'=>$this->session_user_id,
        );
        $insert_id = $this->Customer_model->addprovider($add);
        if(!empty($data['project_id'])){
            $data['project_id']=pk_decrypt($data['project_id']);
            $this->User_model->insert_data('project_providers',array('project_id'=>$data['project_id'],'provider_id'=>$insert_id,'is_linked'=>1));
        }
        // if(empty($data['unique_id'])){
        //     $unique_id='PR'.str_pad($insert_id, 7, '0', STR_PAD_LEFT);
        //     $this->User_model->update_data('provider',array('unique_id'=>$unique_id),array('id_provider'=>$insert_id));
        // }

        //Inserting Default Stakeholder lables
        $stake_holder_lables = array('provider_id'=>$insert_id,'lable1'=>'Procurement and Sales Managers','lable2'=>'Relationship and Account Managers','lable3'=>'Executive Sponsors','created_by'=>$data['created_by'],'created_on' => currentDate(),'contract_id'=>0);
        $this->User_model->insert_data('contract_stakeholder_lables',$stake_holder_lables);
        // if(isset($data['provider_tags']) && count($data['provider_tags'])>0){
        //     $tag_data = array();
        //     foreach($data['provider_tags'] as $k => $v){
        //         $tag_data[$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
        //         $tag_data[$k]['provider_id'] = (int)$insert_id;
        //         $tag_data[$k]['created_by'] = $data['created_by'];
        //         $tag_data[$k]['created_on'] = currentDate();
        //         $tag_data[$k]['comments'] = $v['comments'];
        //         if($v['tag_type']=='input' || $v['tag_type']=='date')
        //             $tag_data[$k]['tag_option_value'] = $v['tag_option'];
                    
        //         else{
        //             $tag_data[$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
        //             $tag_option_value = $this->User_model->check_record('tag_option_language',array('tag_option_id'=>$tag_data[$k]['tag_option']));
        //             if(isset($tag_option_value[0]) || isset($v['tag_option_name']))
        //                 $tag_data[$k]['tag_option_value'] = isset($v['tag_option_name'])?$v['tag_option_name']:$tag_option_value[0]['tag_option_name'];
        //         }
        //         $this->User_model->insert_data('provider_tags',$tag_data[$k]);
               
        //     }
        // }


         //////////Provider TAGS//////////////

         if(isset($data['grouped_tags']) && count($data['grouped_tags'])>0){
            foreach($data['grouped_tags'] as $GK => $GV)
            {
                $data['provider_tags'] = $GV['tag_details'] ; 
                $tag_data = array();
                foreach($data['provider_tags'] as $k => $v){
                    $tag_data[$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
                    $tag_data[$k]['provider_id'] = (int)$insert_id;
                    $tag_data[$k]['created_by'] = $this->session_user_id;
                    $tag_data[$k]['created_on'] = currentDate();
                    $tag_data[$k]['comments'] = $v['comments'];
                    if($v['tag_type']=='input' || $v['tag_type']=='date')
                    {
                        $tag_data[$k]['tag_option_value'] = $v['tag_option'];
                    } 
                    elseif($v['tag_type']=='radio' || $v['tag_type']=='rag' || ($v['tag_type']=='dropdown' && ($v['multi_select'] == 0))){
                        $tag_data[$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                        $tag_option_value = $this->User_model->check_record('tag_option_language',array('tag_option_id'=>$tag_data[$k]['tag_option']));
                        if(isset($tag_option_value[0]) || isset($v['tag_option_name']))
                            $tag_data[$k]['tag_option_value'] = isset($v['tag_option_name'])?$v['tag_option_name']:$tag_option_value[0]['tag_option_name'];
                    }
                    elseif($v['tag_type'] == 'dropdown' && ($v['multi_select'] == 1))
                    {
                        $tagAnswers = [];
                        $tagOptionValue = [];
                        //$CreatedTagOption = $this->User_model->check_record("tag_option" , array("tag_id" => $tag_data[$k]['tag_id'] ,"status" => 1));
                        $CreatedTagOption = $this->Tag_model->getContractTagoptions(array('tag_id' => $tag_data[$k]['tag_id']));
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
                    $this->User_model->insert_data('provider_tags',$tag_data[$k]);
                }
            }
        }
        //////////Provider TAGS//////////////
        $customer_id=$data['customer_id'];
        $path=FILE_SYSTEM_PATH.'uploads/';
        $provider_documents=array();
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
                $provider_documents[$i_attachment]['module_id']=$insert_id;
                $provider_documents[$i_attachment]['module_type']='provider';
                $provider_documents[$i_attachment]['reference_id']=$insert_id;
                $provider_documents[$i_attachment]['reference_type']='provider';
                $provider_documents[$i_attachment]['document_name']=$_FILES['file']['name'][$i_attachment];
                $provider_documents[$i_attachment]['document_type'] = 0;
                $provider_documents[$i_attachment]['document_source']=$imageName;
                $provider_documents[$i_attachment]['document_mime_type']=$_FILES['file']['type'][$i_attachment];
                $provider_documents[$i_attachment]['document_status']=1;
                $provider_documents[$i_attachment]['uploaded_by']=$this->session_user_id;
                $provider_documents[$i_attachment]['uploaded_on']=currentDate();
            }
        }
        // print_r($provider_documents);exit;
        if(count($provider_documents)>0){
            $this->Document_model->addBulkDocuments($provider_documents);
        }
        $provider_links = array();
        if(isset($data['links']))
            foreach($data['links'] as $k => $v){
                $provider_links[$k]['module_id'] = $insert_id;
                $provider_links[$k]['module_type'] = 'provider';
                $provider_links[$k]['reference_id'] = $insert_id;
                $provider_links[$k]['reference_type'] = 'provider';
                $provider_links[$k]['document_name'] = $v['title'];
                $provider_links[$k]['document_type'] = 1;
                $provider_links[$k]['document_source'] = $v['url'];
                $provider_links[$k]['document_mime_type'] = 'URL';
                $provider_links[$k]['uploaded_by'] = $this->session_user_id;
                $provider_links[$k]['uploaded_on'] = currentDate();
                $provider_links[$k]['updated_on'] = currentDate();
            }
        if(count($provider_links)>0){
            $this->Document_model->addBulkDocuments($provider_links);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_add'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function updateprovider_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('provider_name', array('required'=>$this->lang->line('provider_name_req')));
        $this->form_validator->add_rules('id_provider', array('required'=>$this->lang->line('provider_id_req')));
        $this->form_validator->add_rules('updated_by', array('required'=>$this->lang->line('updated_by_req')));

        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>' ');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider'])) {
            $data['id_provider'] = pk_decrypt($data['id_provider']);
        }
        
        if(isset($data['country'])) {
            $data['country'] = pk_decrypt($data['country']);
            
        }

        if(isset($data['updated_by'])) {
            $data['updated_by'] = pk_decrypt($data['updated_by']);
        }

        $name_exists = $this->User_model->check_record('provider',array('provider_name'=>$data['provider_name'],"id_provider !="=>$data['id_provider']));
          //echo '<pre>'.
        if(!empty($name_exists)){
            $result = array('status'=>FALSE, 'error' =>array('message' => $this->lang->line('provider_exists')), 'data'=>'6');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $update_data = array(
            'provider_name' => $data['provider_name'],
            'company_address' => isset($data['company_address'])?$data['company_address']:NULL,
            'city' => isset($data['city'])?$data['city']:NULL,
            'postal_code' => isset($data['postal_code'])?$data['postal_code']:NULL,
            'country' => isset($data['country'])?$data['country']:NULL,
            'customer_id' => $data['customer_id'],
            'description' => isset($data['description'])?$data['description']:NULL,
            'status' => $data['status'],
            'updated_on' => currentDate(),
            'updated_by' => $this->session_user_id
        );

        $update = $this->Customer_model->updateprovider($update_data,$data['id_provider']);

        if($update){
            $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_update'), 'data'=>'5');
            $this->response($result, REST_Controller::HTTP_OK);
        }else{
            $result = array('status'=>FALSE, 'message' => '', 'data'=>'6');
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    public function provider_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider'])) {
            $data['id_provider'] = pk_decrypt($data['id_provider']);
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
        }
        if(isset($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
        }
        if(isset($data['country_id'])){
            $data['country_id']=pk_decrypt($data['country_id']);
        }

        if(isset($data['relationship_category_id'])){
            $data['relationship_category_id'] =pk_decrypt($data['relationship_category_id']);
        }
        if(isset($data['user_role_id']) && isset($data['id_user'])){
            if(in_array($data['user_role_id'],array(3))){
                $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
                $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
                $data['session_user_role']=$this->session_user_info->user_role_id;
                $data['session_user_id']=$this->session_user_id;
            }
            /*if($data['user_role_id']==3){
                $data['contract_owner_id'] = $data['id_user'];
            }*/
            if($data['user_role_id']==4){
                $data['delegate_id'] = $data['id_user'];
                $data['session_user_role']=$this->session_user_info->user_role_id;
                $data['session_user_id']=$this->session_user_id;
                // if(!in_array($data['delegate_id'],$this->session_user_delegates)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if($data['user_role_id']==5){
                $data['customer_user'] = $data['id_user'];
                // if(!in_array($data['customer_user'],$this->session_user_contributors)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if($data['user_role_id']==6){
                $data['business_unit_id'] = $this->session_user_business_units;
            }
            if(isset($data['business_unit_id']) && count($data['business_unit_id'])==0)
                unset($data['business_unit_id']);

            if(isset($data['business_unit_id']) && $data['business_unit_id']>0){
                
            }else{
                if($data['user_role_id']==2)
                    $data['business_unit_id'] = false;
            } 
            if(!empty($data['project_id'])){
                $data['project_id']=pk_decrypt($data['project_id']);
            }
           

            $result = $this->Customer_model->getproviderfilterlist($data);//echo '<pre>'.
            //echo $this->db->last_query();
            if(empty($result) && $data['business_unit_id']){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('total_count'=>0,'data'=>[]));
                $this->response($result, REST_Controller::HTTP_OK);
            }
            foreach($result as $v)
                $data['provider_array'][] = $v['provider_name'];
            if(isset($data['provider_array']) && count($data['provider_array'])==0)
                unset($data['provider_array']);
            if($this->session_user_info->contribution_type == 2 || $this->session_user_info->contribution_type == 3)
                $data['id_provider'] = $this->session_user_info->provider;
        }
        // if(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && !isset($data['all_providers']))
        //     $data['only_user_connected_providers'] = true;
        $data = tableOptions($data);
        if(empty($data['all_providers']) || isset($data['overview']) =='true'){
            unset($data['status']);
            // unset($data['can_access']);
        }
        // print_r($this->session_user_info);exit;
        if(isset($data['project_id'])){
            $data['project_id']=pk_decrypt($data['project_id']);
            $data['type']='project';
        }
        if(isset($data['is_advance_filter']) && $data['is_advance_filter'] == 1)
        {
            $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>0));
            $data['adv_filters']=$get_filters;
            foreach($data['adv_filters'] as $key=>$value)
            {
                if($value['domain'] == "Relation Tags"){
                    $tagData = $this->User_model->check_record('tag',array("id_tag"=>$value['master_domain_field_id']));
                    $data['adv_filters'][$key]['relation_tag_type'] =$tagData[0]['tag_type'];
                }else
                {
                    $data['adv_filters'][$key]['relation_tag_type'] =NULL; 
                }
            }
            $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>1));
            $data['adv_union_filters']=$get_union_filters; 
        }
        $result = $this->Customer_model->optProviderList($data);
        // echo $this->db->last_query();exit;
        unset($data['adv_union_filters']);
        unset($data['get_all_records']); 
        $total_records=$result['total_records'];
        $result=$result['data'];
        $mail_currecy=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));

        $labels = $this->User_model->custom_query('select tag_text,id_tag,label from tag t LEFT JOIN tag_language tl on tl.tag_id = t.id_tag WHERE t.type="provider_tags" and t.is_fixed=1 and customer_id='.$this->session_user_info->customer_id.' ORDER BY label asc');
        if(!empty($labels))
        {
            $result1['labels']=array_column($labels, 'tag_text');
        }
        else{
            $result1['labels'] = array('Risk Profile','Approval Status','Finacial Health');
        }

        foreach($result as $s=>$v){
            //print_r($result);
            // if(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4)){
            //     $total_spent=$this->Contract_model->getProviderTotalspent(array('user_id'=>$this->session_user_info->id_user,'provider_id'=>$v['id_provider']));//echo 
            // }
            // else{
            //         $total_spent=$this->Contract_model->getProviderTotalspent(array('provider_id'=>$v['id_provider']));
            // }
            // $result[$s]['total_spent']=!empty($total_spent[0]['$total_spent'])?$total_spent[0]['$total_spent']:'';
        // $action_item_array['provider_id']=$v['id_provider'];
        // $action_item_array['item_status']=1;
        // $action_item_array['status']='open';
        // $action_item_array['reference_type']='provider';
        // if(in_array($this->session_user_info->user_role_id,array(3,4,6,7))){
        //     $action_item_array['user_role_id']=$this->session_user_info->user_role_id;
        //     $action_item_array['id_user']=$this->session_user_info->id_user;
        // }
        
        // $get_action_item = $this->Master_model->getProviderActionItems($action_item_array);
 
        // $result[$s]['action_items_count']=!empty($get_action_item)?count($get_action_item):0;
        $result[$s]['action_items_count']=$v['action_items_count'];
            $Projected_value=0;
            // print_r($data);exit;
            if(!empty($v['contract_ids']) && !empty($data['overview']) || !empty($data['id_provider'])){
                $contracts_ids = $this->Customer_model->getProviderContracts(array("customer_id"=>$this->session_user_info->customer_id,'provider_id'=>$v['id_provider']));
                if(!empty($contracts_ids))
                {
                    foreach($contracts_ids as $ci=> $cid){
                        $contract_info = $this->User_model->check_record_selected('id_contract,contract_name,currency_id,contract_value,contract_value_period,po_number,additional_recurring_fees,additional_recurring_fees_period,additonal_one_off_fees,contract_start_date,contract_end_date,TIMESTAMPDIFF(MONTH,contract_start_date,contract_end_date) months','contract',array('id_contract'=>$cid['id_contract']));
                        $graph = $this->spent_mngment_graph('spent_line','Actual Spent',$contract_info[0]);
                        $get_exg_rate=$this->User_model->getCurrencyDetails(array('contract_id'=>$contract_info[0]['id_contract']));
                        $exhange_value=1;
                        $exhange_value=str_replace(',','.',$get_exg_rate[0]['euro_equivalent_value']);
                        if($exhange_value == 0 || $get_exg_rate[0]['currency_name']==$mail_currecy[0]['currency_name']){
                            $exhange_value=1;
                        }
                        $Projected_value += ($exhange_value) *(array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset)));
  
                    }
                }
            }
            $result[$s]['total_spent']=$Projected_value>0?$Projected_value:null;
            $result[$s]['currency_name']=$mail_currecy[0]['currency_name'];
            $result[$s]['customer_id']=pk_encrypt($v['customer_id']);
            $result[$s]['country']=pk_encrypt($v['country']);
            $result[$s]['category_id']=pk_encrypt($v['category_id']);
            $result[$s]['created_by']=pk_encrypt($v['created_by']);
            $result[$s]['updated_by']=pk_encrypt($v['updated_by']);
            $result[$s]['vat']=$v['vat']=='null'?'':$v['vat'];
            $result[$s]['description']=$v['description']=='null'?'':$v['description'];
            $result[$s]['updated_by']=pk_encrypt($v['updated_by']);
            if(!empty($data['id_provider'])){
                $providerInfoarray =array('unique_id','provider_name','company_address','city','country','category_id','postal_code','vat','status','description');
                $providerinfoFilledFields =0;
                foreach ($providerInfoarray as $k => $va) {
                    if($va!='category_id')
                    {
                        if(($v[$va]!="")||($v[$va]!=NULL))
                        {
                            $providerinfoFilledFields++;
                        }
                    }
                    else{
                        if((($v[$va]!="")||($v[$va]!=NULL))&&($v[$va]!="0"))
                        {
                            $providerinfoFilledFields++;
                        }
                    }
                }
                $result[$s]['provider_information'] = $providerinfoFilledFields."/10";
                $master_tags = $this->Tag_model->TagList(array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'tag_type'=>'provider_tags'));
                $tag_data = $this->Customer_model->getInfoProviderTags(array('provider_id'=>$result[$s]['id_provider']));
                $tag_result = array();
                if(empty($tag_data))
                {
                    $result[$s]['provider_tags']  ="0/".count($master_tags);
                }
                else{
                    $providerTagesFilled = 0;
                    foreach ($tag_data as $k => $va) {
                        if(($tag_data[$k]['tag_answer']!="")&&($tag_data[$k]['tag_answer']!=NULL))
                        {
                            $providerTagesFilled++;
                        }
                    }
                    $result[$s]['provider_tags']  =$providerTagesFilled."/".count($master_tags);
                }
                $providerStakeholdersarray =array('internal_contract_sponsor','provider_contract_sponsor','internal_partner_relationship_manager','provider_partner_relationship_manager','provider_contract_responsible','internal_contract_responsible');
                $providerStakeholdersFilledFields =0;
                foreach ($providerStakeholdersarray as $k => $va) {
                        if(($v[$va]!="")||($v[$va]!=NULL)){
                            $providerStakeholdersFilledFields++;
                    }
                }
                $result[$s]['provider_stakeholders'] = $providerStakeholdersFilledFields."/6";
              
                $inner_data=array();
                $inner_data['reference_id']=$result[$s]['id_provider'];
                $inner_data['reference_type']='provider';
                $inner_data['module_type']='provider';
                $inner_data['document_status']=1;
                $inner_data['document_type'] = 0;
                $result[$s]['attachment']['documents'] = $result[$s]['unique_attachment']['documents'] = $this->Document_model->getDocumentsList($inner_data);
                $inner_data['document_type'] = array(0,1);
                $result[$s]['unique_attachment']['all_records'] = $this->Document_model->getDocumentsList($inner_data);
                $inner_data['document_type'] = 1;
                $result[$s]['attachment']['links'] = $result[$s]['unique_attachment']['links'] = $this->Document_model->getDocumentsList($inner_data);
                foreach($result[$s]['attachment']['documents'] as $ka=>$va){
                    $result[$s]['attachment']['documents'][$ka]['updated_by']=0;
                }
                foreach($result[$s]['attachment']['links'] as $ka=>$va){
                    $result[$s]['attachment']['links'][$ka]['updated_by']=0;
                }
                $inner_data['updated_by']=isset($data['updated_by'])?$data['updated_by']:1;
                $result[$s]['attachment']['links'] = array_merge($this->Document_model->getDocumentsList($inner_data),$result[$s]['attachment']['links']);
                // echo 
                unset($inner_data['document_type']);
                $result[$s]['attachment']['documents'] = array_merge($this->Document_model->getDocumentsList($inner_data),$result[$s]['attachment']['documents']);
                foreach($result[$s]['attachment']['documents'] as $ka=>$va){
                    $result[$s]['attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$s]['attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result[$s]['attachment']['documents'][$ka]['id_document']=pk_encrypt($result[$s]['attachment']['documents'][$ka]['id_document']);
                    $result[$s]['attachment']['documents'][$ka]['module_id']=pk_encrypt($result[$s]['attachment']['documents'][$ka]['module_id']);
                    $result[$s]['attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[$s]['attachment']['documents'][$ka]['reference_id']);
                    $result[$s]['attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[$s]['attachment']['documents'][$ka]['uploaded_by']);
                    $result[$s]['attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[$s]['attachment']['documents'][$ka]['user_role_id']);
                }
                foreach($result[$s]['attachment']['links'] as $ka=>$va){
                    $result[$s]['attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$s]['attachment']['links'][$ka]['id_document']=pk_encrypt($result[$s]['attachment']['links'][$ka]['id_document']);
                    $result[$s]['attachment']['links'][$ka]['module_id']=pk_encrypt($result[$s]['attachment']['links'][$ka]['module_id']);
                    $result[$s]['attachment']['links'][$ka]['reference_id']=pk_encrypt($result[$s]['attachment']['links'][$ka]['reference_id']);
                    $result[$s]['attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[$s]['attachment']['links'][$ka]['uploaded_by']);
                    $result[$s]['attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[$s]['attachment']['links'][$ka]['user_role_id']);
                }
                foreach($result[$s]['unique_attachment']['all_records'] as $ka=>$va){
                    $result[$s]['unique_attachment']['all_records'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['id_document']=pk_encrypt($result[$s]['unique_attachment']['all_records'][$ka]['id_document']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['module_id']=pk_encrypt($result[$s]['unique_attachment']['all_records'][$ka]['module_id']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['reference_id']=pk_encrypt($result[$s]['unique_attachment']['all_records'][$ka]['reference_id']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['uploaded_by']=pk_encrypt($result[$s]['unique_attachment']['all_records'][$ka]['uploaded_by']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['user_role_id']=pk_encrypt($result[$s]['unique_attachment']['all_records'][$ka]['user_role_id']);
                    $result[$s]['unique_attachment']['all_records'][$ka]['action']=0;
                    if(in_array($this->session_user_info->user_role_id,array(2)))
                    {
                        $result[$s]['unique_attachment']['all_records'][$ka]['action']=1;
                    }
                    if(($result[$s]['unique_attachment']['all_records'][$ka]['is_lock']==1) && (!((in_array($this->session_user_info->user_role_id,array(2))) || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id ==4) && ($this->session_user_info->content_administator_relation == 1)  ))))
                    {  
                        unset($result[$s]['unique_attachment']['all_records'][$ka]);
                    }
                }
                $result[$s]['unique_attachment']['all_records']= array_values($result[$s]['unique_attachment']['all_records']);
                foreach($result[$s]['unique_attachment']['documents'] as $ka=>$va){
                    $result[$s]['unique_attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$s]['unique_attachment']['documents'][$ka]['encryptedPath']=pk_encrypt($va['document_source']);
                    $result[$s]['unique_attachment']['documents'][$ka]['id_document']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['id_document']);
                    $result[$s]['unique_attachment']['documents'][$ka]['module_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['module_id']);
                    $result[$s]['unique_attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['reference_id']);
                    $result[$s]['unique_attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['uploaded_by']);
                    $result[$s]['unique_attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['user_role_id']);
                    $result[$s]['unique_attachment']['documents'][$ka]['action']=0;
                    if(in_array($this->session_user_info->user_role_id,array(2)))
                    {
                        $result[$s]['unique_attachment']['documents'][$ka]['action']=1;
                    }
                    if(($result[$s]['unique_attachment']['all_records'][$ka]['is_lock']==1) && (!((in_array($this->session_user_info->user_role_id,array(2))) || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id ==4) && ($this->session_user_info->content_administator_relation == 1)  ))))
                    {  
                        unset($result[$s]['unique_attachment']['all_records'][$ka]);
                    }
                }
                $result[$s]['unique_attachment']['documents']= array_values($result[$s]['unique_attachment']['documents']);
                foreach($result[$s]['unique_attachment']['links'] as $ka=>$va){
                    $result[$s]['unique_attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                    $result[$s]['unique_attachment']['links'][$ka]['id_document']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['id_document']);
                    $result[$s]['unique_attachment']['links'][$ka]['module_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['module_id']);
                    $result[$s]['unique_attachment']['links'][$ka]['reference_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['reference_id']);
                    $result[$s]['unique_attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['uploaded_by']);
                    $result[$s]['unique_attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['user_role_id']);
                    $result[$s]['unique_attachment']['links'][$ka]['action']=0;
                    if(in_array($this->session_user_info->user_role_id,array(2)))
                    {
                        $result[$s]['unique_attachment']['links'][$ka]['action']=1;
                    }
                    if(($result[$s]['unique_attachment']['all_records'][$ka]['is_lock']==1) && (!((in_array($this->session_user_info->user_role_id,array(2))) || (($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id ==4) && ($this->session_user_info->content_administator_relation == 1)  ))))
                    {  
                        unset($result[$s]['unique_attachment']['all_records'][$ka]);
                    }
                }
                $result[$s]['unique_attachment']['links']= array_values($result[$s]['unique_attachment']['links']);
                $result[$s]['provider_attachments'] = count($result[$s]['unique_attachment']['all_records']);
                //exit;
                // foreach($result[$s]['unique_attachment']['documents'] as $ka=>$va){
                //     $result[$s]['unique_attachment']['documents'][$ka]['document_source_exactpath']=($va['document_source']);
                //     $result[$s]['unique_attachment']['documents'][$ka]['id_document']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['id_document']);
                //     $result[$s]['unique_attachment']['documents'][$ka]['module_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['module_id']);
                //     $result[$s]['unique_attachment']['documents'][$ka]['reference_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['reference_id']);
                //     $result[$s]['unique_attachment']['documents'][$ka]['uploaded_by']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['uploaded_by']);
                //     $result[$s]['unique_attachment']['documents'][$ka]['user_role_id']=pk_encrypt($result[$s]['unique_attachment']['documents'][$ka]['user_role_id']);
                // }
                // foreach($result[$s]['unique_attachment']['links'] as $ka=>$va){
                //     $result[$s]['unique_attachment']['links'][$ka]['document_source_exactpath']=($va['document_source']);
                //     $result[$s]['unique_attachment']['links'][$ka]['id_document']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['id_document']);
                //     $result[$s]['unique_attachment']['links'][$ka]['module_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['module_id']);
                //     $result[$s]['unique_attachment']['links'][$ka]['reference_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['reference_id']);
                //     $result[$s]['unique_attachment']['links'][$ka]['uploaded_by']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['uploaded_by']);
                //     $result[$s]['unique_attachment']['links'][$ka]['user_role_id']=pk_encrypt($result[$s]['unique_attachment']['links'][$ka]['user_role_id']);
                // }
            }
            $id_provider = $v['id_provider'];
            $label_1_id = null;
            $label_2_id = null;
            $label_3_id = null;
            // print_r($labels);
            if(!empty($labels))
            {
                foreach($labels as $lab)
                {
                    if($lab['label'] == 'label_1')
                    {
                        $label_1_id = $lab['id_tag'];
                    }
                    elseif($lab['label'] == 'label_2')
                    {
                        $label_2_id = $lab['id_tag'];
                    }
                    elseif($lab['label'] == 'label_3')
                    {
                        $label_3_id = $lab['id_tag'];
                    }
                }
                $query = "select  * FROM (";
                if(!empty($label_1_id))
                {
                    $query .="(select pt.tag_option_value risk_profile from provider_tags pt WHERE pt.provider_id = $id_provider and pt.tag_id = $label_1_id   GROUP BY pt.provider_id) risk_profile,";
                }
                if(!empty($label_2_id))
                {
                    $query .=" (select pt.tag_option_value approval_status from provider_tags pt WHERE pt.provider_id = $id_provider and pt.tag_id = $label_2_id GROUP BY pt.provider_id) approval_status,";
                }
                if($label_3_id)
                {
                    $query .="(select pt.tag_option_value finacial_health from provider_tags pt WHERE pt.provider_id = $id_provider and pt.tag_id = $label_3_id GROUP BY pt.provider_id) finacial_health";
                }
                $query .= ")";
                $tagdata = $this->User_model->custom_query($query);
            }
      
            $result[$s]['id_provider']=pk_encrypt($v['id_provider']);
            $result[$s]['risk_profile'] = $result[$s]['label_1'] = isset($tagdata[0]['risk_profile']) ? $tagdata[0]['risk_profile'] : null ;
            $result[$s]['approval_status'] = $result[$s]['label_2'] = isset($tagdata[0]['approval_status']) ? $tagdata[0]['approval_status'] : null ;
            $result[$s]['finacial_health'] = $result[$s]['label_3'] = isset($tagdata[0]['finacial_health']) ? $tagdata[0]['finacial_health'] : null ;
        }
        $result1['data']=$result;
        $result1['total_records']=$total_records;
     

        // print_r($data['id_provider']);
        // if($data['id_provider']>0){
        //     $mail_currecy=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));
        //     $result1['data'][0]['currency_name']=$mail_currecy[0]['currency_name'];
        // }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result1);
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function dailynotificationcount_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $data = tableOptions($data);
        $result = $this->Customer_model->dailynotificationcount($data);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function notification_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $data = tableOptions($data);
        $result = $this->Customer_model->dailyNotificationList($data);
        foreach($result['data'] as $k=>$v){
            $content = $v['content'];
            $content = json_decode($content, true);
            $result['data'][$k]['content'] = $content;
            $result['data'][$k]['mailer_id'] = pk_encrypt($v['mailer_id']);
            $result['data'][$k]['mail_to_user_id'] = pk_encrypt($v['mail_to_user_id']);
            $result['data'][$k]['email_template_id'] = pk_encrypt($v['email_template_id']);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function dailynotificationcount_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $this->form_validator->add_rules('date', array('required'=>$this->lang->line('date_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if($data['id_user']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        $data = tableOptions($data);
        $result = $this->Customer_model->updatedailynotificationcount($data);

        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function checkAD_post(){
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('email_id', array('required'=> $this->lang->line('email_req')));
        $this->form_validator->add_rules('password', array('required'=> $this->lang->line('password_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $params=array('host'=>$data['host'],'port'=>$data['port'],'dc'=>$data['dc']);
        $this->load->library('LdapAuthentication',$params);
        $is_login=$this->ldapauthentication->login($data['email_id'],$data['password']);
        if($is_login['status']===true){
            $result = array('status'=>TRUE,'message'=>$this->lang->line('success'),'data'=>'');
        }
        else{
            $result = array('status'=>FALSE,'message'=>$is_login['message'],'data'=>'');
        }
        $this->response($result, REST_Controller::HTTP_OK);

    }

    public function DelegateContributors_get(){
        $data = $this->input->get();

        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $data = tableOptions($data);
        if($this->session_user_info->user_role_id == 3){ 
            $session_user_contributing_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $owner_contracts = $this->User_model->check_record('contract',array('contract_owner_id'=>$this->session_user_id,'is_deleted'=>0));
            foreach($session_user_contributing_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
            foreach($owner_contracts as $v)
                $data['contracts_array'][] = $v['id_contract'];
        }else if($this->session_user_info->user_role_id == 4){ 
            $session_user_contributing_contracts = $this->User_model->check_record('contract_user',array('user_id'=>$this->session_user_id,'status'=>1));
            $delegate_contracts = $this->User_model->check_record('contract',array('delegate_id'=>$this->session_user_id,'is_deleted'=>0));
            foreach($session_user_contributing_contracts as $v)
                $data['contracts_array'][] = $v['contract_id'];
            foreach($delegate_contracts as $v)
                $data['contracts_array'][] = $v['id_contract'];
        }else{
            $data['contracts_array'] = $this->session_user_contracts;
        } 
        $data['user_id'] = $this->session_user_id;
        // echo '<pre>--=='.print_r($data);exit;
        if(isset($data['contribution_type']) && $data['contribution_type'] == '')
            unset($data['contribution_type']);
        $contributors = $this->Contract_model->getDelegateContributors($data);//echo '<pre>'.
        //echo '<pre>'.print_r($contributors);exit;
        foreach($contributors['data'] as $k => $v){
            //if($v['provider']>0)
            //    $contributors[$k]['provider_name'] = $this->User_model->check_record_selected('provider_name','provider',array('id_provider'=>$v['provider']))[0]['provider_name'];
            unset($contributors['data'][$k]['flag']);
            $user_info = $this->User_model->getUserInfo(array('user_id'=>$v['user_id']));
            $contributors['data'][$k]['provider_name'] = $user_info->provider_name;
            $bu_name = null;
            if((int)$v['contribution_type'] == 0 || (int)$v['contribution_type'] == 1){
                //Geting the Business units of User which he belongs to
                $bu_names = $this->Business_unit_model->getBusinessUnitList(array('status'=>1,'business_unit_array'=>$this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$v['user_id']))));
                //echo '<pre>'.print_r($bu_names);
                foreach($bu_names['data'] as $v1){
                    $bu_name[] = $v1['bu_name'];
                }
            }
            if(empty($bu_name))
            {
                $bu_name = array($contributors['data'][$k]['provider_name']);
            }
            $user_modules = $this->User_model->check_record_selected('module_id','contract_user',array('contract_review_id'=>$v['contract_review_id'],'user_id'=>$v['user_id'],'status'=>1));
            //echo '<pre>'.$this->db->last_query();//
            //echo '<pre>'.print_r($user_modules);
            $module_id = array();
            foreach($user_modules as $v2){
                $module_id[] = (int)$v2['module_id']>0?$v2['module_id']:0;
            }//echo '<pre>'.print_r($module_id);
            $module_list = $this->Customer_model->getUserModules(array('module_id'=>$module_id));
            $module_name = array();
            foreach($module_list as $v3){
                $module_name[] = $v3['module_name'];
            }            
            unset($contributors['data'][$k]['module_id']);
            //print_r($contributors);exit;
            if($contributors['data'][$k]['is_workflow'] == 0){
                
                $check_contract_in_calender_sql = $this->Contract_model->check_contract_in_calender(
                                                                        array(
                                                                            'contract_id' => $contributors['data'][$k]['id_contract'],
                                                                            'is_workflow' => $contributors['data'][$k]['is_workflow'],
                                                                            'business_unit_id' => $contributors['data'][$k]['business_unit_id'],
                                                                            'relationship_category_id' => $contributors['data'][$k]['relationship_category_id'],
                                                                            //'provider_id' => $contributors['data'][$k]['provider']
                                                                        ));

                                                                      
                if(count($check_contract_in_calender_sql)>0){
                    //Meanse there are contracts planned by selecting them
                    $contributors['data'][$k]['review_name'] = $check_contract_in_calender_sql[0]['workflow_name'];
                }else{
                    //Meanse ther is not plan available 
                    $contributors['data'][$k]['review_name'] = null;
                }
                //echo '<pre>'.print_r($check_contract_in_calender_sql);exit;
            }else{
                if($contributors['data'][$k]['id_contract_workflow'] > 0){
                    
                    $check_contract_in_calender_sql = $this->User_model->check_record('contract_workflow',array('id_contract_workflow'=>$contributors['data'][$k]['id_contract_workflow'],'status'=>1));
                    $contributors['data'][$k]['review_name'] = $check_contract_in_calender_sql[0]['workflow_name'];
                }else{
                    //Meanse ther is not plan available 
                    $contributors['data'][$k]['review_name'] = null;
                }
                $contributors['data'][$k]['template_name'] = $contributors['data'][$k]['review_name'];
            }
            unset($contributors['data'][$k]['business_unit_id']);
            unset($contributors['data'][$k]['relationship_category_id']);
            $contributors['data'][$k]['contract_review_id'] = pk_encrypt($contributors['data'][$k]['contract_review_id']);
            $contributors['data'][$k]['id_contract_workflow'] = pk_encrypt($contributors['data'][$k]['id_contract_workflow']);
            $contributors['data'][$k]['is_workflow'] = $contributors['data'][$k]['is_workflow'];
            $contributors['data'][$k]['initiated'] = true;
            $contributors['data'][$k]['user_id'] = pk_encrypt($contributors['data'][$k]['user_id']);
            $contributors['data'][$k]['id_contract'] = pk_encrypt($contributors['data'][$k]['id_contract']);

            $contributors['data'][$k]['module_name'] = array_unique($module_name);
            $contributors['data'][$k]['bu_name'] = $bu_name;
            if((int)$v['contribution_type'] == 0)
                $contributors['data'][$k]['contribution_type'] = 'Expert';
            else if((int)$v['contribution_type'] == 1)
                $contributors['data'][$k]['contribution_type'] = 'Validator';
            else if((int)$v['contribution_type'] == 3)
                $contributors['data'][$k]['contribution_type'] = 'Relation';            
        }

        $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => $contributors);
        $this->response($result, REST_Controller::HTTP_OK);
        
    }

    function spent_mngment_graph($graphtype,$graph_title,$data){
        //echo '<pre>'.print_r($data);exit;
        $currency = $this->User_model->check_record('currency',array('id_currency'=>$data['currency_id']));
        $graph = '';

        $chart->showSum= "1";
        $chart->decimalSeparator= ',';
        $chart->thousandSeparator= '.';
        $chart->canvasTopMargin= '0';
        //$chart->yAxisMaxValue= '9,147,483,647';
        $chart->caption= "";
        $chart->subCaption= "";
        $chart->xAxisname= "";
        $chart->yAxisName= "";
        $chart->numberPrefix= $currency[0]['currency_name'].' ';
        $chart->animation= "0";
        $chart->showBorder= "0";
        $chart->bgColor= "#ffffff";
        $chart->showLabels= "1";
        $chart->adjustDiv= "1";
        $chart->showValues= "0";
        $chart->showLimits= "0";
        $chart->showDivLineValues= "0";
        $chart->showShadow= "0";
        $chart->showLegend= "0";
        $chart->showcanvasborder= "0";
        $chart->canvasBgAlpha= "0";
        $chart->divLineAlpha= "0";
        $chart->legendBorderAlpha= "0";
        $chart->showAlternateHGridColor= "0";
        $chart->useEllipsesWhenOverflow= "1";
        $chart->palette= "3";
        $chart->theme= "fusion";
        $chart->plottooltext= "\$seriesName : <b>\$dataValue</b>";
        $chart->formatNumberScale= "0";
        $chart->usePlotGradientColor= "0";
        $chart->theme= "fusion";
        $chart->use3DLighting= "1";
        $chart->creditLabel= "0";
        $chart->key="yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==";

        $categories[0]->category[0]->label = 'Projected Spend';
        $categories[0]->category[1]->label = 'Actual Spend';

        $dataset = array();
        $spent_line_info = $this->User_model->check_record('spent_lines',array('contract_id'=>$data['id_contract'],'status'=>1));
        //echo '<pre>'.print_r($data);exit;
        foreach($spent_line_info as $k => $v){ 
            $spent_line_info[$k]['id'] = pk_encrypt($v['id']);
            $spent_line_info[$k]['contract_id'] = pk_encrypt($v['contract_id']);
            $spent_line_info[$k]['created_by'] = pk_encrypt($v['created_by']);
            $spent_line_info[$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }

        
            $dataset[0]->seriesname = 'Projected Value';
            if($data['contract_value_period'] == 'total' || $data['contract_value_period'] == null){
                $dataset[0]->data[0]->value = round((int)$data['contract_value']);
                $dataset[0]->data[1]->value = 0;
                //$dataset[0]->data[1]->toolText = 'Spend Management';
            }
            else{ 
                $dataset[0]->data[0]->value = round($data['contract_value']*((int)$data['months']/12));
                $dataset[0]->data[1]->value = 0;
                //$dataset[0]->data[1]->toolText = 'Spend Management';
            }
            $dataset[1]->seriesname = 'Additional Reccuring fees';
            if($data['additional_recurring_fees_period'] == null){
                $dataset[1]->data[0]->value = round($data['additional_recurring_fees']);
                $dataset[1]->data[1]->value = 0;
                //$dataset[1]->data[1]->toolText = 'Spend Management';
            }else if($data['additional_recurring_fees_period'] == 'month'){
                $dataset[1]->data[0]->value = round($data['additional_recurring_fees']*(int)$data['months']);
                $dataset[1]->data[1]->value = 0;
                //$dataset[1]->data[1]->toolText = 'Spend Management';
            }
            else if($data['additional_recurring_fees_period'] == 'quarter'){
                $dataset[1]->data[0]->value = round($data['additional_recurring_fees']/3*(int)$data['months']);
                $dataset[1]->data[1]->value = 0;
                //$dataset[1]->data[1]->toolText = 'Spend Management';
            }else{
                $dataset[1]->data[0]->value = round($data['additional_recurring_fees']*((int)$data['months']/12));
                $dataset[1]->data[1]->value = 0;
                //$dataset[1]->data[1]->toolText = 'Spend Management';
            }
            $dataset[2]->seriesname = 'Additional One-off fees';
            $dataset[2]->data[0]->value = round((int)$data['additonal_one_off_fees']);            
            $dataset[2]->data[1]->value = 0;            
            $dataset[2]->data[1]->toolText = 'Actual spend';         
            $i = $index = 3;
            // for($i = 3; $i<count($spent_line_info); $i++){
            //     $dataset[$i]->seriesname = 'Spent Line '.($i+1);
            //     $dataset[$i]->data[0]->value = 0;
            //     $dataset[$i]->data[0]->value = isset($data['spentline_info'][$i])?$data['spentline_info'][$i]['spent_amount']:0;
            // }
            foreach($spent_line_info as $k => $v){
                $dataset[$i]->seriesname = 'Spend Line '.($k+1);
                $dataset[$i]->data[0]->value = 0;
                $dataset[$i]->data[0]->toolText = 'Projected Spend';
                $dataset[$i]->data[1]->value = $v['spent_amount'];
                $i++;
            }

        $graph->chart = $chart;
        $graph->categories = $categories;
        $graph->dataset = $dataset;

        return $graph;      
    }
    public function GenerateProviderId_get(){
        // $get_id= $this->User_model->custom_query('SELECT id_provider FROM provider ORDER BY id_provider DESC LIMIT 1');
        // $providers_count=$this->User_model->check_record_selected('count(*) as count','provider',array('customer_id'=>$this->session_user_info->customer_id));
        // $unique_id='PR'.str_pad($providers_count[0]['count']+1, 7, '0', STR_PAD_LEFT);
        $unique_id=uniqueId(array('module' => 'provider' , 'customer_id' => $this->session_user_info->customer_id));
        $result = array('status' => TRUE, 'message' => $this->lang->line('success'), 'data' => array('unique_id'=>$unique_id));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function providerTags_get(){
        $data = $this->input->get();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('id_provider', array('required'=>$this->lang->line('provider_id_req')));
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

        if(isset($data['id_provider'])) {
            $provider_id = $data['id_provider'] = pk_decrypt($data['id_provider']);
            // if($this->session_user_info->user_role_id!=7)
            // if(!in_array($data['id_provider'],$this->session_user_contracts)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        // if(isset($data['provider_id'])) {
        //     $provider_id = $data['provider_id'] = pk_decrypt($data['provider_id']);
        //     if($this->session_user_info->user_role_id != 7)
        //     if(!in_array($data['provider_id'],$this->session_user_contracts)){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        //$active
        $master_tags = $this->Tag_model->TagList(array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'tag_type'=>$data['tag_type']));
        
        $tag_data = $this->Customer_model->getInfoProviderTags(array('provider_id'=>$provider_id));
        $tag_result = array();
        
        foreach($master_tags as $k => $v){
            $tag_result[$k]['tag_text'] = $v['tag_text'];
            $tag_result[$k]['tag_type'] = $v['tag_type'];
            $tag_result[$k]['field_type'] = $v['field_type'];
            $tag_result[$k]['tag_option'] = 0;
            $tag_result[$k]['tag_answer'] = '';
            $tag_result[$k]['id_provider_tag'] = 0;
            $tag_result[$k]['business_unit_id'] = pk_encrypt($v['business_unit_id']);
            $tag_result[$k]['bu_name'] = $v['bu_name'];
            $tag_result[$k]['selected_field'] = $v['selected_field'];
            $tag_result[$k]['multi_select'] = $v['multi_select'];
            $tag_result[$k]['business_unit_status'] = $v['business_unit_status'];
            $tag_result[$k]['options'] = $this->Tag_model->getContractTagoptions(array('tag_id'=>$v['id_tag']));

            //echo $tag_data[$k]['id_tag'].' == '.$v['id_tag'].'<br>';
            // if(isset($tag_data[$k]) && $tag_data[$k]['id_tag'] == $v['id_tag']){
            //     //If contract tag exists
            //     $tag_result[$k]['id_provider_tag'] = pk_encrypt($tag_data[$k]['id_provider_tag']);
            //     if((int)$tag_data[$k]['tag_option'] == 0){
            //         //If contract tag is of date or input type
            //         $tag_result[$k]['tag_option'] = $tag_data[$k]['tag_option'];
            //         $tag_result[$k]['tag_answer'] = $tag_data[$k]['tag_answer'];
            //     }
            //     else{
            //         $tag_result[$k]['tag_option'] = pk_encrypt($tag_data[$k]['tag_option']);
            //         $tag_result[$k]['tag_answer'] = pk_encrypt($tag_data[$k]['tag_answer']);
            //     }
            //     $tag_result[$k]['comments'] = $tag_data[$k]['comments']; 
            // }
            
            $tag_dataKey =null;
            $tag_dataKey = array_search($v['id_tag'], array_column($tag_data, 'id_tag'));
            if(is_numeric($tag_dataKey))
            {
              //If contract tag exists
            //   $tag_result[$k]['id_provider_tag'] = pk_encrypt($tag_data[$tag_dataKey]['id_provider_tag']);
            //   if((int)$tag_data[$tag_dataKey]['tag_option'] == 0){
            //       //If contract tag is of date or input type
            //       $tag_result[$k]['tag_option'] = $tag_data[$tag_dataKey]['tag_option'];
            //       $tag_result[$k]['tag_answer'] = $tag_data[$tag_dataKey]['tag_answer'];
            //   }
            //   else{
            //       $tag_result[$k]['tag_option'] = pk_encrypt($tag_data[$tag_dataKey]['tag_option']);
            //       $tag_result[$k]['tag_answer'] = pk_encrypt($tag_data[$tag_dataKey]['tag_answer']);
            //   }
            //   $tag_result[$k]['comments'] = $tag_data[$tag_dataKey]['comments'];


              $tag_result[$k]['id_provider_tag'] = pk_encrypt($tag_data[$tag_dataKey]['id_provider_tag']);
                if((int)$v['multi_select'] == 1)
                {
                    if($tag_data[$tag_dataKey]['tag_option'] == 0){
                        //If contract tag is of date or input type
                        $tag_result[$k]['tag_option'] = $tag_data[$tag_dataKey]['tag_option'];
                        $tag_result[$k]['tag_answer'] = $tag_data[$tag_dataKey]['tag_answer'];
                    }
                    else{
                        $tag_option = [];
                        $tag_option = explode(",",$tag_data[$tag_dataKey]['tag_option']) ; 
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
                    if((int)$tag_data[$tag_dataKey]['tag_option'] == 0){
                        //If contract tag is of date or input type
                        $tag_result[$k]['tag_option'] = $tag_data[$tag_dataKey]['tag_option'];
                        //$tag_result[$k]['tag_answer'] = !empty($tag_data[$tag_dataKey]['tag_answer'])?$tag_data[$tag_dataKey]['tag_answer']:'';
                        $tag_result[$k]['tag_answer'] = !is_null($tag_data[$tag_dataKey]['tag_answer'])?$tag_data[$tag_dataKey]['tag_answer']:'';
                        $tag_result[$k]['tagAnswerDisplay'] = !is_null($tag_data[$tag_dataKey]['tag_answer'])?$tag_data[$tag_dataKey]['tag_answer']:'';
                    }
                    else{
                        $tag_result[$k]['tag_option'] = pk_encrypt($tag_data[$tag_dataKey]['tag_option']);
                        $tag_result[$k]['tag_answer'] = pk_encrypt($tag_data[$tag_dataKey]['tag_answer']);
                        if($v['tag_type'] == "dropdown" || $v['tag_type'] == "radio" ||$v['tag_type'] == "rag")
                        {
                            $key = array_search($tag_data[$tag_dataKey]['tag_option'], array_column($tag_result[$k]['options'], 'id_tag_option'));
                            $tag_result[$k]['tagAnswerDisplay'] = $tag_result[$k]['options'][$key]['tag_option_name'];
                            
                        }
                        elseif($v['tag_type'] == "input" || $v['tag_type'] == "date")
                        {
                            $tag_result[$k]['tagAnswerDisplay'] = !is_null($tag_data[$tag_dataKey]['tag_answer'])?$tag_data[$tag_dataKey]['tag_answer']:'';  
                        }
                        elseif($v['tag_type'] == "selected")
                        {
                            $SelectTag =array('module' => $v['selected_field'] , 'ids' =>explode(",",$tag_data[$tag_dataKey]['tag_option']) , 'clickable' => True ,'userroleId' => $this->session_user_info->user_role_id , 'userId' => $this->session_user_id);
                            $selectedOption = $this->Tag_model->getSelectName($SelectTag);
                            foreach($selectedOption as $selectedOptionkey =>$selectedOptionValue)
                            {
                                $selectedOption[$selectedOptionkey]['id'] = pk_encrypt($selectedOption[$selectedOptionkey]['id']);
                            }
                            $tag_result[$k]['selectedOption'] = $selectedOption;
                        }
                    }
                }
                $tag_result[$k]['comments'] = $tag_data[$tag_dataKey]['comments'];




            }
            if(($tag_result[$k]['tag_type']=='date') && empty($tag_result[$k]['tag_answer']) )
            {
                $tag_result[$k]['tag_answer'] ='0000-00-00';
            }
            $tag_result[$k]['tag_id'] = pk_encrypt($v['id_tag']);
            $tag_result[$k]['tag_order'] =$v['tag_order'];

            foreach($tag_result[$k]['options'] as $k1 => $v1)
            {
                $tag_result[$k]['options'][$k1]['id_tag_option'] = pk_encrypt($v1['id_tag_option']);
            }
            

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


        $result = array('status'=>TRUE, 'message'=>$this->lang->line('success'), 'data'=>$groupTag,'test'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

     public function provideerTagsUpdate_post(){
            $data = $this->input->post();
            if(empty($data)){
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            $this->form_validator->add_rules('id_provider', array('required'=>$this->lang->line('provider_id_req')));
            $this->form_validator->add_rules('grouped_tags', array('required'=>$this->lang->line('tag_req')));
            $validated = $this->form_validator->validate($data);
            if($validated != 1)
            {
                $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if(isset($data['id_provider'])) {
                $data['id_provider'] = pk_decrypt($data['id_provider']);
            }
        
            $data['provider_tags']=$data['grouped_tags'];
            $data['updated_by']=$this->session_user_id;
            $this->provider_change_log($data['provider_tags'],$data['id_provider']);
            // if(isset($data['provider_tags']) && count($data['provider_tags'])>0){
            //     $tag_data = array();
            //     foreach($data['provider_tags'] as $k => $v){
            //         foreach($v['options'] as $k1 => $v1)
            //             $data['provider_tags'][$k]['options'][$k1]['id_tag_option'] = pk_decrypt($v1['id_tag_option']);
            //         $data['provider_tags'][$k]['id_provider_tag'] = (int)pk_decrypt($v['id_provider_tag']);
            //         $data['provider_tags'][$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
            //         //echo 'Index: '.$k;print_r($data['contract_tags'][$k]);
            //         if((int)pk_decrypt($v['tag_option']) > 0){
            //             $data['provider_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
            //             if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
            //                 $data['provider_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
            //         }else{
            //             $data['provider_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
            //             if(!(int)pk_decrypt($v['id_provider_tag']) > 0){
            //                 if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
            //                     $data['provider_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
            //             }
            //         }//print_r($data['contract_tags'][$k]);
            //         if($v['tag_type'] == 'input' || $v['tag_type'] == 'date'){
            //             $data['provider_tags'][$k]['tag_option_value'] = $data['provider_tags'][$k]['tag_answer'];
            //         }else{
            //             foreach($data['provider_tags'][$k]['options'] as $k2 => $v2){
            //                 $data['provider_tags'][$k]['tag_option'] = null;
            //                 $data['provider_tags'][$k]['tag_option_value'] = null;
            //                 if(pk_decrypt($v['tag_answer']) == $v2['id_tag_option']){
            //                     $data['provider_tags'][$k]['tag_option'] = $v2['id_tag_option'];
            //                     $data['provider_tags'][$k]['tag_option_value'] = $v2['tag_option_name'];
            //                     break;
            //                 }
            //             }
            //         }                
            //         $tag_data = array(
            //             'tag_option' => $data['provider_tags'][$k]['tag_option'],
            //             'tag_option_value' => $data['provider_tags'][$k]['tag_option_value'],
            //             'provider_id' => $data['id_provider'],
            //             'tag_id' => $data['provider_tags'][$k]['tag_id'],
            //             'comments' => $data['provider_tags'][$k]['comments']
            //         );
            //         if(isset($v['id_provider_tag']) && (int)pk_decrypt($v['id_provider_tag']) > 0){
            //             //Update
            //             $tag_data['updated_on'] = currentDate();
            //             $tag_data['updated_by'] = $this->session_user_id;
            //             $this->User_model->update_data('provider_tags',$tag_data,array('id_provider_tag'=>$data['provider_tags'][$k]['id_provider_tag']));
            //         }else{
            //             //Insert
            //             $tag_data['created_on'] = currentDate();
            //             $tag_data['created_by'] = $this->session_user_id;
            //             $this->User_model->insert_data('provider_tags',$tag_data);
            //             //echo '<pre>'.$this->db->last_query();
            //         }
            //     }
            //     // echo 
            //     //echo '<pre>'.print_r($data);exit;
            //     $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_tags_updated'), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);    
            // }
            if(isset($data['grouped_tags']) && count($data['grouped_tags'])>0)
            {
                foreach($data['grouped_tags'] as $groupKey => $groupValue)
                {
                    $data['provider_tags'] = $groupValue['tag_details'] ; 
                    if(isset($data['provider_tags']) && count($data['provider_tags'])>0){
                        $tag_data = array();
                        foreach($data['provider_tags'] as $k => $v){
                            foreach($v['options'] as $k1 => $v1)
                            {
                                $data['provider_tags'][$k]['options'][$k1]['id_tag_option'] = pk_decrypt($v1['id_tag_option']);
                            } 
                            $data['provider_tags'][$k]['id_provider_tag'] = (int)pk_decrypt($v['id_provider_tag']);
                            $data['provider_tags'][$k]['tag_id'] = (int)pk_decrypt($v['tag_id']);
                            if($v['tag_type'] != 'selected')
                            {
                                if($v['tag_type'] == 'dropdown' && (int)$v['multi_select'] == 1 )
                                {
                                    if(!empty($v['tag_answer']))
                                    {
                                        $tagAnswers = [];
                                        $tagOptionValue = [];
                                        foreach($data['provider_tags'][$k]['tag_answer'] as $multiDropKey => $multiDropValue)
                                        {
                                            foreach($data['provider_tags'][$k]['options'] as $option)
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
                                        $data['provider_tags'][$k]['tag_option'] = $commaSepTagAnswers;
                                        $data['provider_tags'][$k]['tag_option_value'] = $commaSepTagAnswersValue;

                                    }
                                    else
                                    {
                                        $data['provider_tags'][$k]['tag_option'] = 0 ;
                                        $data['provider_tags'][$k]['tag_option_value'] = Null;

                                    }
                                    
                                }
                                else
                                {
                                    if((int)pk_decrypt($v['tag_option']) > 0){
                                        $data['provider_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                                        if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
                                            $data['provider_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
                                    }else{
                                        $data['provider_tags'][$k]['tag_option'] = (int)pk_decrypt($v['tag_option']);
                                        if(!(int)pk_decrypt($v['id_provider_tag']) > 0){
                                            if($v['tag_type'] != 'input' && $v['tag_type'] != 'date')
                                                $data['provider_tags'][$k]['tag_answer'] = pk_decrypt($v['tag_answer']);
                                        }
                                    }//print_r($data['contract_tags'][$k]);
                                    if($v['tag_type'] == 'input' || $v['tag_type'] == 'date'){
                                        $data['provider_tags'][$k]['tag_option_value'] = $data['provider_tags'][$k]['tag_answer'];
                                    }else{
                                        foreach($data['provider_tags'][$k]['options'] as $k2 => $v2){
                                            $data['provider_tags'][$k]['tag_option'] = null;
                                            $data['provider_tags'][$k]['tag_option_value'] = null;
                                            if(pk_decrypt($v['tag_answer']) == $v2['id_tag_option']){
                                                $data['provider_tags'][$k]['tag_option'] = $v2['id_tag_option'];
                                                $data['provider_tags'][$k]['tag_option_value'] = $v2['tag_option_name'];
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
                                    $data['provider_tags'][$k]['tag_option'] = $commaSepTagAnswers;

                                    $modalData = [
                                        'module' => $v['selected_field'],
                                        'ids' => $tagAnswers
                                    ];
                                  
                                    $tagOptionValue = $this->Tag_model->getNames($modalData);
                                   
                                    $data['provider_tags'][$k]['tag_option_value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                                }
                                else
                                {
                                    $data['provider_tags'][$k]['tag_option'] = 0 ;
                                    $data['provider_tags'][$k]['tag_option_value'] = Null;
                                }
                            }
                            $tag_data = array(
                                'tag_option' => $data['provider_tags'][$k]['tag_option'],
                                'tag_option_value' => $data['provider_tags'][$k]['tag_option_value'],
                                'provider_id' => $data['id_provider'],
                                'tag_id' => $data['provider_tags'][$k]['tag_id'],
                                'comments' => $data['provider_tags'][$k]['comments']
                            );
                            if(isset($v['id_provider_tag']) && (int)pk_decrypt($v['id_provider_tag']) > 0){
                                //Update
                                $tag_data['updated_on'] = currentDate();
                                $tag_data['updated_by'] = $this->session_user_id;
                                $this->User_model->update_data('provider_tags',$tag_data,array('id_provider_tag'=>$data['provider_tags'][$k]['id_provider_tag']));
                            }else{
                                //Insert
                                $tag_data['created_on'] = currentDate();
                                $tag_data['created_by'] = $this->session_user_id;
                                $this->User_model->insert_data('provider_tags',$tag_data);
                                //echo '<pre>'.$this->db->last_query();
                            }
                        }
                        
                    }
                }
                $result = array('status'=>TRUE, 'message' => $this->lang->line('provider_tags_updated'), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);   
            }
            else{
                $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
                
        }
    public function updateProviderData_post(){
        $data = $this->input->post();
        if(!empty($data['provider']))
        $data=$data['provider'];
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('id_provider', array('required'=>$this->lang->line('provider_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_provider'])) {
             $data['id_provider'] = pk_decrypt($data['id_provider']);
        }
      
        // $check_unique_id_exist=$this->Customer_model->checkuniqueidexitst(array('unique_id'=>$data['unique_id'],'id_provider'=>$data['id_provider']));
        // if(count($check_unique_id_exist)>0){
        //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('unique_id_exists')), 'data'=>'');
        //     $this->response($result, REST_Controller::HTTP_OK); 
        // }
        $update_array=array(
            // 'unique_id'=>isset($data['unique_id'])?$data['unique_id']:'',
            'provider_name'=>isset($data['provider_name'])?$data['provider_name']:'',
            'description'=>isset($data['description']) && $data['description']!='null'?$data['description']:'',
            'company_address'=>isset($data['company_address']) && $data['company_address']!='null'?$data['company_address']:'',
            'city'=>isset($data['city']) && $data['city']!='null'?$data['city']:'',
            'country'=>isset($data['country'])?pk_decrypt($data['country']):'',
            'postal_code'=>isset($data['postal_code']) && $data['postal_code']!='null'?$data['postal_code']:'',
            'category_id'=>isset($data['category_id'])?pk_decrypt($data['category_id']):'',
            'status'=>isset($data['status'])?$data['status']:'0',
            'vat'=>isset($data['vat'])?$data['vat']:'0',
            'updated_by' => $this->session_user_id,
            'updated_on' => currentDate()

        );
        $this->provider_change_log($update_array,$data['id_provider']);
        $this->User_model->update_data('provider',$update_array,array('id_provider'=>$data['id_provider']));//echo 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('update_provider'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);   
    }
    public function providerListGraph_get(){
        $data = $this->input->get();
        if(isset($data['user_role_id'])){
            unset($data['user_role_id']);
        }
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if($this->session_user_info->user_role_id==1 && $data['customer_id']!='' && $data['customer_id']>0 && !in_array($data['customer_id'],$this->session_user_master_customers)){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if(isset($data['id_provider'])) {
            $data['id_provider'] = pk_decrypt($data['id_provider']);
        }
        if(isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
        }
        if(isset($data['business_unit_id'])) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
        }
        if(isset($data['country_id'])){
            $data['country_id']=pk_decrypt($data['country_id']);
        }

        if(isset($data['relationship_category_id'])){
            $data['relationship_category_id'] =pk_decrypt($data['relationship_category_id']);
        }
        if(isset($data['user_role_id']) && isset($data['id_user'])){
            // if(in_array($data['user_role_id'],array(3))){
            //     $business_unit = $this->Business_unit_model->getBusinessUnitUser(array('user_id' => $data['id_user'],'status' => '1'));
            //     $data['business_unit_id'] = array_map(function($i){ return $i['id_business_unit']; },$business_unit);
            //     $data['session_user_role']=$this->session_user_info->user_role_id;
            //     $data['session_user_id']=$this->session_user_id;
            // }
            /*if($data['user_role_id']==3){
                $data['contract_owner_id'] = $data['id_user'];
            }*/
            if($data['user_role_id']==4){
                $data['delegate_id'] = $data['id_user'];
                $data['session_user_role']=$this->session_user_info->user_role_id;
                $data['session_user_id']=$this->session_user_id;
                // if(!in_array($data['delegate_id'],$this->session_user_delegates)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if($data['user_role_id']==5){
                $data['customer_user'] = $data['id_user'];
                // if(!in_array($data['customer_user'],$this->session_user_contributors)){
                //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
                //     $this->response($result, REST_Controller::HTTP_OK);
                // }
            }
            if($data['user_role_id']==6){
                $data['business_unit_id'] = $this->session_user_business_units;
            }
            if(isset($data['business_unit_id']) && count($data['business_unit_id'])==0)
                unset($data['business_unit_id']);

            if(isset($data['business_unit_id']) && $data['business_unit_id']>0){
                
            }else{
                if($data['user_role_id']==2)
                    $data['business_unit_id'] = false;
            } 
            $result = $this->Customer_model->getproviderfilterlist($data);//echo '<pre>'.
            if(empty($result) && $data['business_unit_id']){
                $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('total_count'=>0,'data'=>[]));
                $this->response($result, REST_Controller::HTTP_OK);
            }
            foreach($result as $v)
                $data['provider_array'][] = $v['provider_name'];
            if(isset($data['provider_array']) && count($data['provider_array'])==0)
                unset($data['provider_array']);
            if($this->session_user_info->contribution_type == 2 || $this->session_user_info->contribution_type == 3)
                $data['id_provider'] = $this->session_user_info->provider;
        }
        // if(($this->session_user_info->user_role_id == 3 || $this->session_user_info->user_role_id == 4) && !isset($data['all_providers']))
        //     $data['only_user_connected_providers'] = true;
        unset($data['business_unit_id']);
        $data = tableOptions($data);
        $data['status']=1;
        $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>0));
        $data['adv_filters']=$get_filters;
        foreach($data['adv_filters'] as $key=>$value)
        {
            if($value['domain'] == "Relation Tags"){
                $tagData = $this->User_model->check_record('tag',array("id_tag"=>$value['master_domain_field_id']));
                $data['adv_filters'][$key]['relation_tag_type'] =$tagData[0]['tag_type'];
            }else
            {
                $data['adv_filters'][$key]['relation_tag_type'] =NULL; 
            }
        }
        $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>1));
        $data['adv_union_filters']=$get_union_filters;  
        $result1 = $this->Customer_model->getproviderlist($data);//echo 
       // echo $this->db->last_query();exit;
       unset($data['adv_filters']);
       unset($data['adv_union_filters']);
        $result=$result1['data'];
        $coordinateVal = 50;
        $coordinateMaxVal = 100;
        $coordinateMinVal = 0;
        $coordinateAddVal = 25;

        $currency = $this->Master_model->getCurrencyList(array());
        /*if($data['user_role_id']==2){*/
        $quadrantLabelTR = $quadrantLabelTL = $quadrantLabelBL = $quadrantLabelBR = '';
        $quadrants = $this->Relationship_category_model->ProviderRelationshipCategoryList(array('customer_id' => $data['customer_id'],"can_review"=>1));
        if(isset($quadrants['data']) && count($quadrants['data']>0)){
            $quadrants = $quadrants['data'];
            foreach($quadrants as $k=>$v){
                if($v['provider_relationship_category_quadrant'] == 'Q1'){
                    $quadrantLabelTR = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q2'){
                    $quadrantLabelTL = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q3'){
                    $quadrantLabelBL = $v['relationship_category_name'];
                }
                if($v['provider_relationship_category_quadrant'] == 'Q4'){
                    $quadrantLabelBR = $v['relationship_category_name'];
                }
            }
        }
        // print_r($quadrantLabelBL);exit;
        $xaxis = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' =>$data['customer_id']));
        // print_r($data['customer_id']);exit;
        $xaxis=$xaxis['data'];
        if($xaxis && is_array($xaxis) && isset($xaxis[0]['classification_position'])){
            foreach($xaxis as $k=>$v){
                if($v['classification_position'] == 'x'){
                    $xaxis = $v['is_visible'];
                }
                if($v['classification_position'] == 'y'){
                    $yaxis = $v['is_visible'];
                }
            }
        }
        if($xaxis == '0'){
            $left = '';
            $right = '';
        } else {
            $left = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $data['customer_id'], 'classification_position' => 'left','classification_status'=>1));
            $left=$left['data'];
            if($left && is_array($left) && isset($left[0]['classification_name'])){
                $left = $left[0]['classification_name'];
            }
            $right = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $data['customer_id'], 'classification_position' => 'right','classification_status'=>1));
            $right=$right['data'];
            if($right && is_array($right) && isset($right[0]['classification_name'])){
                $right = $right[0]['classification_name'];
            }
        }
        
        if($yaxis == '0'){
            $high = '';
            $low = '';
        } else {
            $low = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $data['customer_id'], 'classification_position' => 'low','classification_status'=>1,''));
            $low=$low['data'];
            if($low && is_array($low) && isset($low[0]['classification_name'])){
                $low = $low[0]['classification_name'];
            }
            $high = $this->Relationship_category_model->ProviderRelationshipClassificationList(array('customer_id' => $data['customer_id'], 'classification_position' => 'high','classification_status'=>1));
            $high=$high['data'];
            if($high && is_array($high) && isset($high[0]['classification_name'])){
                $high = $high[0]['classification_name'];
            }
        }
        $quadrantMaxVal = $data['customer_id'];
        $firstQuadrant = $secondQuadrant = $thirdQuadrant = $fourthQuadrant = [];
        $data = array();
        $color = array('r' => '#ff0000','a' =>  '#ff9900','g' =>  '#5bb166','b' =>  '#0c7cd5','na'=>'#ccc');
        $currentcyLabel = '&euro;';
        foreach($result as $k=>$v){
            if($v['category_name'] == $quadrantLabelTR){
                $firstQuadrant[$k] = $v;
            }
            if($v['category_name'] == $quadrantLabelTL){
                $secondQuadrant[$k] = $v;
            }
            if($v['category_name'] == $quadrantLabelBL){
                $thirdQuadrant[$k] = $v;
            }
            if($v['category_name'] == $quadrantLabelBR){
                $fourthQuadrant[$k] = $v;
            }
            if(!empty($v['contract_ids'])){
                $contrat_ids=explode(',',$v['contract_ids']);
                // print_r($result);exit;
                $mail_currecy=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));
                $Projected_value_1=0;
                foreach($contrat_ids as $ci=> $cid){
                    $contract_info = $this->User_model->check_record_selected('id_contract,currency_id,contract_value,contract_value_period,po_number,additional_recurring_fees,additional_recurring_fees_period,additonal_one_off_fees,contract_start_date,contract_end_date,TIMESTAMPDIFF(MONTH,contract_start_date,contract_end_date) months','contract',array('id_contract'=>$cid));
                    // print_r($contract_info);exit;
                    $graph = $this->spent_mngment_graph('spent_line','Actual Spent',$contract_info[0]);
                    $get_exg_rate=$this->User_model->getCurrencyDetails(array('contract_id'=>$contract_info[0]['id_contract']));
                    // echo 
                    $exhange_value=1;
                    $exhange_value=str_replace(',','.',$get_exg_rate[0]['euro_equivalent_value']);
                    if($exhange_value == 0 || $get_exg_rate[0]['currency_name']==$mail_currecy[0]['currency_name']){
                        $exhange_value=1;
                    }
                    $Projected_value_1 += ($exhange_value) *(array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset)));
                    // $Projected_value_1 += array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset));
                }
                // print_r($Projected_value);exit;
            }
            $quadrantMaxVal += $Projected_value_1;
        }
        $firstIndx = $secondIndx = $thirdIndx = $fourthIndx = 1;
        $firstQuadrantCount = $secondQuadrantCount = $thirdQuadrantCount = $fourthQuadrantCount = 1;
        $firstQuadrantCount=$firstQuadrantCount+count($firstQuadrant);
        $secondQuadrantCount=$secondQuadrantCount+count($secondQuadrant);
        $thirdQuadrantCount=$thirdQuadrantCount+count($thirdQuadrant);
        $fourthQuadrantCount=$fourthQuadrantCount+count($fourthQuadrant);

        foreach($result as $k=>$v){

            $Projected_value =0;
            if(!empty($v['contract_ids'])){
                $contrat_ids=explode(',',$v['contract_ids']);
                // print_r($result);exit;
                foreach($contrat_ids as $ci=> $cid){
                    $contract_info = $this->User_model->check_record_selected('id_contract,currency_id,contract_value,contract_value_period,po_number,additional_recurring_fees,additional_recurring_fees_period,additonal_one_off_fees,contract_start_date,contract_end_date,TIMESTAMPDIFF(MONTH,contract_start_date,contract_end_date) months','contract',array('id_contract'=>$cid));
                    // print_r($contract_info);exit;
                    $graph = $this->spent_mngment_graph('spent_line','Actual Spent',$contract_info[0]);
                    $get_exg_rate=$this->User_model->getCurrencyDetails(array('contract_id'=>$contract_info[0]['id_contract']));
                    // echo 
                    $exhange_value=1;
                    $exhange_value=str_replace(',','.',$get_exg_rate[0]['euro_equivalent_value']);
                    if($exhange_value == 0 || $get_exg_rate[0]['currency_name']==$mail_currecy[0]['currency_name']){
                        $exhange_value=1;
                    }
                    $Projected_value += ($exhange_value) *(array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset)));
                    // $Projected_value += array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset));
                }
                // print_r($Projected_value);exit;
            }
            // print_r($contrat_ids);exit;
            // print_r($Projected_value);exit;
            if(!empty($v['category_name']) && $Projected_value>0 && $v['can_review']==1){
                $provider_total_spent=0;
                $provider_total_spent=!empty($Projected_value)?$Projected_value:0;
                // print_r($provider_total_spent);exit;
                if($v['category_name'] == $quadrantLabelTR){
                    $x = $coordinateVal+(($coordinateVal/$firstQuadrantCount)*$firstIndx);
                    $y = $coordinateVal+(($coordinateVal/$firstQuadrantCount)*$firstIndx);
                    if($firstIndx%3==0) {
                        //$y = $y + 10;//random
                    }
                    else if($firstIndx%3==1) {
                        $y = $y + 5;//random
                    }
                    else {
                        $x = $x + 5;//random
                    }
                    /*$x=rand($coordinateVal+5,$coordinateMaxVal-5);
                    $y=rand($coordinateVal+5,$coordinateMaxVal-5);*/
                    $z = ($provider_total_spent/$quadrantMaxVal)*100;

                    $coordinate = 1;
                    $firstIndx++;
                }
                if($v['category_name'] == $quadrantLabelTL){
                    $x = $coordinateVal-(($coordinateVal/$secondQuadrantCount)*$secondIndx);
                    $y = $coordinateVal+(($coordinateVal/$secondQuadrantCount)*$secondIndx);
                    if($secondIndx%3==0) {
                        //$y = $y + 10;//random
                    }
                    else if($secondIndx%3==1) {
                        $y = $y + 5;//random
                    }
                    else {
                        $x = $x - 5;//random
                    }
                    $z = ($provider_total_spent/$quadrantMaxVal)*100;
                    /*$x=rand($coordinateMinVal+5,$coordinateVal-5);
                    $y=rand($coordinateVal+5,$coordinateMaxVal-5);*/
                    $coordinate = 2;
                    $secondIndx++;
                }
                if($v['category_name'] == $quadrantLabelBL){
                    $x = $coordinateVal-(($coordinateVal/$thirdQuadrantCount)*$thirdIndx);
                    $y = $coordinateVal-(($coordinateVal/$thirdQuadrantCount)*$thirdIndx);
                    if($thirdIndx%3==0) {
                        //$y = $y - 10;//random
                    }
                    else if($thirdIndx%3==1) {
                        $y = $y - 5;//random
                    }
                    else {
                        $x = $x - 5;//random
                    }
                    /*$x=rand($coordinateMinVal+5,$coordinateVal-5);
                    $y=rand($coordinateMinVal+5,$coordinateVal-5);*/
                    $z = ($provider_total_spent/$quadrantMaxVal)*100;
                    $coordinate = 3;
                    $thirdIndx++;
                }
                if($v['category_name'] == $quadrantLabelBR){
                    $x = $coordinateVal+(($coordinateVal/$fourthQuadrantCount)*$fourthIndx);
                    $y = $coordinateVal-(($coordinateVal/$fourthQuadrantCount)*$fourthIndx);
                    if($fourthIndx%3==0) {
                        //$y = $y - 10;//random
                    }
                    else if($fourthIndx%3==1) {
                        $y = $y - 5;//random
                    }
                    else {
                    $x = $x + 5;//random
                    }
                    /*$x=rand($coordinateVal+5,$coordinateMaxVal-5);
                    $y=rand($coordinateMinVal+5,$coordinateVal-5);*/
                    $z = ($provider_total_spent/$quadrantMaxVal)*100;
                    $coordinate = 4;
                    $fourthIndx++;
                }

                if($z<1){
                    $z = 1;
                }
                foreach($currency as $k1=>$v1){
                    if($v['currency_id'] == $v1['id_currency']){
                        $z = $z*$v1['euro_equivalent_value'];
                        $currencyName = $v1['currency_name'];
                    }
                }
                $labels = $this->User_model->custom_query('select tag_text from tag t LEFT JOIN tag_language tl on tl.tag_id = t.id_tag WHERE t.type="provider_tags" and t.is_fixed=1 and customer_id='.$this->session_user_info->customer_id.' ORDER BY label asc');
                if(!empty($labels))
                {
                    // foreach($labels as $label)
                    // {
                    //     $result1['labels'][] = $label['tag_text'];
                    // }
                    $graph_lables=array_column($labels, 'tag_text');
                }
                else{
                    $graph_lables = array('Risk Profile','Approval Status','Finacial Health');
                }
                // print_r($graph_lables[0]);exit;
                $provider_total_spent = $this->a_number_format($provider_total_spent, 0, '.',",",3);
                $status = '';
                $score = "<tr><td class='labelDiv' align='right'>$graph_lables[0]</td><td class=''> </td><td class='allpadding'><span class='status-widget font-weight-bold' >";
                if($v['risk_profile']=='' || $v['risk_profile']=='NA'){
                    $score .= "<span class=''></span><span class=''></span><span class=''></span>";
                    $scoreText = '';
                } else {
                    $scoreText = strtolower($v['risk_profile']);
                    if($scoreText == 'r'){
                        $risk_color_code='red';
                        $score .= "<span class='".strtolower($risk_color_code)."-active'></span><span class=''></span><span class=''></span>";
                    }
                    else if($scoreText == 'a'){
                        $risk_color_code='amber';
                        $score .= "<span class=''></span><span class='".strtolower($risk_color_code)."-active'></span><span class=''></span>";
                    }
                    else if($scoreText == 'g'){
                        $risk_color_code='green';
                        $score .= "<span class=''></span><span class=''></span><span class='".strtolower($risk_color_code)."-active'></span>";
                    }
                    else{
                        $score .= "<span class=''></span><span class=''></span><span class=''></span>";
                    }
                }
                $score .= "</span></td></tr>";
                $approval_status = "<tr><td class='labelDiv' align='right'>$graph_lables[1]</td><td class=''> </td><td class='allpadding'><span class='status-widget font-weight-bold' >";
                if($v['approval_status']=='' || $v['approval_status']=='NA'){
                    $approval_status .= "<span class=''></span><span class=''></span><span class=''></span>";
                    $approval_status_text = '';
                } else {
                    $approval_status_text = strtolower($v['approval_status']);
                    if($approval_status_text == 'r'){
                        $color_code='red';
                        $approval_status .= "<span class='".strtolower($color_code)."-active'></span><span class=''></span><span class=''></span>";
                    }
                    else if($approval_status_text == 'a'){
                        $color_code='amber';
                        $approval_status .= "<span class=''></span><span class='".strtolower($color_code)."-active'></span><span class=''></span>";
                    }
                    else if($approval_status_text == 'g'){
                        $color_code='green';
                        $approval_status .= "<span class=''></span><span class=''></span><span class='".strtolower($color_code)."-active'></span>";
                    }
                    else{
                        $approval_status .= "<span class=''></span><span class=''></span><span class=''></span>";
                    }
                }
                $approval_status .= "</span></td></tr>";
                if($v['risk_profile']=='' || empty($scoreText)){
                    $bubble_color=$color['b'];
                }
                else{
                    $bubble_color=$color[$scoreText];
                }
                // if(isset($v['risk_profile'])){
                //     $status = "<tr><td class='labelDiv' align='right'>Status</td><td class=''> </td><td class='allpadding'>".ucfirst($v['contract_status'])."</td></tr>";
                // }

                // $last_review = "<tr><td class='labelDiv' align='right'>Last Review</td><td class=''> </td><td class='allpadding'>";
                // if($v['last_review'] != '---'){
                //     $last_review .= date('M d,Y',strtotime($v['last_review']));
                // }else{
                //     $last_review .= " -- ";
                // }
                // $last_review .= "</td></tr>";
                if(!empty($v['created_on'])){
                    $created_on = date('M d,Y',strtotime($v['created_on']));
                }
                else{
                    $created_on='--';
                }
                $contract_value=currencyFormat($provider_total_spent,'EUR');
                // print_r($contract_value);exit;
                $data[] = array(
                    'x' => $x,
                    'y' => $y,
                    'z' => $z,
                    'name' => $v['provider_name'],
                    'color' => $bubble_color,
                    'tooltext' => "<div class='color_change'>
                    <table width='220'>
                       <tr>
                          <td class='labelDiv' align='right'>Provider</td>
                          <td class=''> </td>
                          <td class='allpadding'>{$v['provider_name']}</td>
                       </tr>
                       <tr>
                          <td class='labelDiv' align='right'>Country</td>
                          <td class=''> </td>
                          <td class='allpadding'>{$v['country_name']}</td>
                       </tr>
                       <!--  <tr>
                         <td class='labelDiv' align='right'>Total Spend</td>
                         <td class=''> </td>
                          <td class='allpadding'>{$currentcyLabel} {$contract_value} </td>
                      </tr> -->
                       <tr>
                          <td class='labelDiv' align='right'>Created Date</td>
                          <td class=''> </td>
                          <td class='allpadding'>{$created_on}</td>
                       </tr>
                       {$score}{$approval_status}
                    </table>
                 </div>",
                    'coordinate' => $coordinate,
                    'score' => $scoreText,
                    'approva_status' => $approval_status_text
            ); 
            }             
        }
        
        $final_result = array(
            'chart' =>  array(
                "xAxisMinValue" =>  "0",
                "xAxisMaxValue" =>  ($coordinateVal*2),
                "yAxisMinValue" =>  0,
                "yAxisMaxValue" =>  ($coordinateVal*2),
                "plotFillAlpha" => 60,
                "showYAxisvalue" => "0",
                "numDivlines" =>  "0",
                "showValues" => "0",
                "showTrendlineLabels" =>  "0",
                "quadrantLabelTL" =>  $quadrantLabelTL,
                "quadrantLabelTR" =>  $quadrantLabelTR,
                "quadrantLabelBL" =>  $quadrantLabelBL,
                "quadrantLabelBR" =>  $quadrantLabelBR,
                "quadrantLabelFontBold" => "1",
                "quadrantLabelFontSize" => "11",
                "quadrantLabelFont" => "verdana",
                "toolTipBgColor" =>  "#ECECEC",
                "toolTipBorderColor" =>  "#000",
                "drawQuadrant"  =>  "1",
                "quadrantXVal" =>  "50",
                "quadrantYVal" =>  "50",
                "quadrantLineAlpha"  =>  "50",
                "quadrantLineThickness"  =>  "1",
                "theme" =>  "fint",
                "showHoverEffect" => '0',
                'maxLabelWidthPercent ' => '70',
                'use3DLighting ' => '1',
                "numVDivLines"=> "0",
                "numDivLines"=> "0",
                "quadrantLabelFontAlpha"=> "100",
                "showXAxisValues"=> "0"
                /*"yAxisName" => "test&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;test2",
                "rotateYAxisName" => "1"*/
            ),
            "categories" => array(
                "category" => array(
                    array(
                        "label" =>  ' ',
                        "x" =>  "0"
                    ),
                    array(
                        "label" =>  ' ',
                        "x" =>  "100"
                    )
                )
            ),
            "dataset" => array(
                array(
                    "color" => "#00aee4",
                    "data" =>  $data
                )
            ),
            "classficationRelation" => array(
                    'left' => $left,
                    'right' => $right,
                    'low' => $low,
                    'high' => $high,
            )
            
        );
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$final_result);
        // print_r($result);exit;
        $this->response($result, REST_Controller::HTTP_OK);
    }
    private function a_number_format($number_in_iso_format, $no_of_decimals=3, $decimals_separator='.', $thousands_separator='', $digits_grouping=3){
        // Check input variables
        if (!is_numeric($number_in_iso_format)){
            error_log("Warning! Wrong parameter type supplied in my_number_format() function. Parameter \$number_in_iso_format is not a number.");
            return false;
        }
        if (!is_numeric($no_of_decimals)){
            error_log("Warning! Wrong parameter type supplied in my_number_format() function. Parameter \$no_of_decimals is not a number.");
            return false;
        }
        if (!is_numeric($digits_grouping)){
            error_log("Warning! Wrong parameter type supplied in my_number_format() function. Parameter \$digits_grouping is not a number.");
            return false;
        }


        // Prepare variables
        $no_of_decimals = $no_of_decimals * 1;


        // Explode the string received after DOT sign (this is the ISO separator of decimals)
        $aux = explode(".", $number_in_iso_format);
        // Extract decimal and integer parts
        $integer_part = $aux[0];
        $decimal_part = isset($aux[1]) ? $aux[1] : '';

        // Adjust decimal part (increase it, or minimize it)
        if ($no_of_decimals > 0){
            // Check actual size of decimal_part
            // If its length is smaller than number of decimals, add trailing zeros, otherwise round it
            if (strlen($decimal_part) < $no_of_decimals){
                $decimal_part = str_pad($decimal_part, $no_of_decimals, "0");
            } else {
                $decimal_part = substr($decimal_part, 0, $no_of_decimals);
            }
        } else {
            // Completely eliminate the decimals, if there $no_of_decimals is a negative number
            $decimals_separator = '';
            $decimal_part       = '';
        }

        // Format the integer part (digits grouping)
        if ($digits_grouping > 0){
            $aux = strrev($integer_part);
            $integer_part = '';
            for ($i=strlen($aux)-1; $i >= 0 ; $i--){
                if ( $i % $digits_grouping == 0 && $i != 0){
                    $integer_part .= "{$aux[$i]}{$thousands_separator}";
                } else {
                    $integer_part .= $aux[$i];
                }
            }
        }

        $processed_number = "{$integer_part}{$decimals_separator}{$decimal_part}";
        return $processed_number;
    }
    public function providersListExport_get(){
        //this function generates a report in excel.
        $data = $this->input->get();
        // print_r($data);exit;
        //$this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
        $this->form_validator->add_rules('export_type', array('required'=>$this->lang->line('export_type_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        // if(isset($data['customer_id'])) {
        //     $data['customer_id'] = pk_decrypt($data['customer_id']);
        //     if($this->session_user_info->customer_id!=$data['customer_id']){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }

        // }
        // if(isset($data['user_role_id'])) {
        //     $data['user_role_id'] = pk_decrypt($data['user_role_id']);
        //     if($data['user_role_id']!=$this->session_user_info->user_role_id){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        // if(isset($data['id_user'])) {
        //     $data['id_user'] = pk_decrypt($data['id_user']);
        //     if($data['id_user']!=$this->session_user_id){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        // }
        $data['customer_id'] = $this->session_user_info->customer_id;
        $data['user_role_id'] = $this->session_user_info->user_role_id;
        $data['id_user'] = $this->session_user_id;
        if(strtolower($data['export_type']) == 'all_providers'){
            // $result = $this->Customer_model->exportList($data);
            $get_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>0));
            $data['adv_filters']=$get_filters;
            foreach($data['adv_filters'] as $key=>$value)
            {
                if($value['domain'] == "Relation Tags"){
                    $tagData = $this->User_model->check_record('tag',array("id_tag"=>$value['master_domain_field_id']));
                    $data['adv_filters'][$key]['relation_tag_type'] =$tagData[0]['tag_type'];
                }else
                {
                    $data['adv_filters'][$key]['relation_tag_type'] =NULL; 
                }
            }
            $get_union_filters=$this->User_model->getFilter(array('status'=>1,'user_id'=>$this->session_user_info->id_user,'module'=>'all_relations_list','is_union_table'=>1));
            $data['adv_union_filters']=$get_union_filters;  
        $result = $this->Customer_model->getproviderlist($data);
        unset($data['adv_union_filters']);
        unset($data['adv_filters']);
        // echo 
        }
        for($s=0;$s<count($result['data']);$s++)
        {
             if(strlen($result['data'][$s]['relationship_category_name'])>2){
                  preg_match_all('/[A-Z]/', ucwords(strtolower($result['data'][$s]['relationship_category_name'])), $matches);
                  $result['data'][$s]['relationship_category_short_name'] = implode('',$matches[0]);
             }else{
                $result['data'][$s]['relationship_category_short_name'] = $result['data'][$s]['relationship_category_name'];
            }

        }
        //preparing headers
        if(strtolower($data['export_type']) == 'all_providers'){
            //Geting Active tags
            $active_tags = $this->Tag_model->TagList(array('customer_id'=>$data['customer_id'],'status'=>1,'tag_type'=>'provider_tags','orderBy'=>'forExport'));
            // echo '<pre>'.
            //echo '<pre>'.print_r($active_tags);exit;
            $tags = array();
            for($i=0; $i<NO_OF_TAGS ;$i++){
                $tags[$i]['text']=isset($active_tags[$i])?$active_tags[$i]['tag_text']:'';
                $tags[$i]['field_type']=isset($active_tags[$i])?$active_tags[$i]['field_type']:'';
                $tags[$i]['id']=isset($active_tags[$i])?$active_tags[$i]['id_tag']:'';
            }
           $headers=array('Id','Relation Name','Description','Company Address','City','Country','Postal Code','Vat','Category','Total Spend',$tags);
        }
        if(isset($result['data']))
            $result = $result['data'];

        $this->load->library('excel');
        //activate worksheet number 1
        $excelRowstartsfrom=1;
        $excelColumnstartsFrom=0;
        $columnBegin =$excelColumnstartsFrom;
        $excelstartsfrom=$excelRowstartsfrom;
        //echo '<pre>'.print_r($headers);exit;
        //writing headers
        foreach($headers as $k=>$v){
            if(is_array($v)){
                foreach($v as $k1 => $v1){
                    $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v1['text']);
                   
                    if($v1['field_type'] == 'currency')
                        $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . ($excelstartsfrom+1).':'.$this->getkey($columnBegin) . ($excelstartsfrom+1000))->getNumberFormat()->setFormatCode('_("€"* #,##0.00_);_("€"* \(#,##0.00\);_("€"* "-"??_);_(@_)');
                    // if($v1['field_type'] == 'number')
                    //     $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . ($excelstartsfrom+1).':'.$this->getkey($columnBegin) . ($excelstartsfrom+1000))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
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
        // print_r($tags);exit;
        foreach($result as $k => $v){
            $provider_tags = $this->Customer_model->getInfoProviderTags(array('provider_id'=>$v['id_provider'],'status'=>1,'orderBy'=>'forExport'));
            $tags =[];
            for($t=0;$t<NO_OF_TAGS;$t++)
            {
                $tags[] = array('id'=>'','value'=>'');
            }
            // $tags = array(array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''),array('id'=>'','value'=>''));
            // foreach($provider_tags as $k1 => $v1){
            //     $tags[$k1]['id'] = $v1['id_tag'];
            //     $tags[$k1]['field_type'] = $v1['field_type'];
            //     if($v1['tag_type']!='input' || $v1['tag_type']!='date'){
            //         $get_answer= $this->Tag_model->getContractTagoptions(array('tag_id'=>$v1['id_tag'],'tag_option_id'=>$v1['tag_option']));
            //         if($get_answer[0]['tag_option_name']=='R'){
            //            $get_answer[0]['tag_option_name']='Red';
            //         }
            //         if($get_answer[0]['tag_option_name']=='G'){
            //            $get_answer[0]['tag_option_name']='Green';
            //         }
            //         if($get_answer[0]['tag_option_name']=='A'){
            //            $get_answer[0]['tag_option_name']='Amber';
            //         }
            //        $tags[$k1]['value']=$get_answer[0]['tag_option_name'];
            //    }
            //     if($v1['tag_type']=='input' || $v1['tag_type']=='date'){
            //        $tags[$k1]['value']=$v1['tag_answer'];
            //    }
            // }
            // echo "provider tags";
            // print_r($provider_tags);exit;
            // echo "active_tags";
            // print_r($active_tags);
            foreach($active_tags as $k1 =>$v1)
            {
                $providerTagKey=null;
                $providerTagKey = array_search($v1['id_tag'], array_column($provider_tags, 'id_tag'));
                if(is_numeric($providerTagKey))
                {
                    $tags[$k1]['id'] = $provider_tags[$providerTagKey]['id_tag'];
                    $tags[$k1]['field_type'] = $provider_tags[$providerTagKey]['tag_type'];

                    if($provider_tags[$providerTagKey]['tag_type']=='input' || $provider_tags[$providerTagKey]['tag_type']=='date'){
                        $tags[$k1]['value']=$provider_tags[$providerTagKey]['tag_answer'];
                    }
                    elseif($provider_tags[$providerTagKey]['tag_type'] == "rag" || $provider_tags[$providerTagKey]['tag_type'] == "radio" || ($provider_tags[$providerTagKey]['tag_type'] == "dropdown" && $provider_tags[$providerTagKey]['multi_select'] == 0))
                    {
                        if(!empty($provider_tags[$providerTagKey]['tag_option']))
                        {
                            $get_answer= $this->Tag_model->getContractTagoptions(array('tag_id'=>$provider_tags[$providerTagKey]['id_tag'],'tag_option_id'=>$provider_tags[$providerTagKey]['tag_option']));
                      
                            if($get_answer[0]['tag_option_name']=='R'){
                                $get_answer[0]['tag_option_name']='Red';
                            }
                            if($get_answer[0]['tag_option_name']=='G'){
                                $get_answer[0]['tag_option_name']='Green';
                            }
                            if($get_answer[0]['tag_option_name']=='A'){
                                $get_answer[0]['tag_option_name']='Amber';
                            }
                            $tags[$k1]['value']=$get_answer[0]['tag_option_name'];

                        }
                        else
                        {
                            $tags[$k1]['value']='';
                        }
                     
                    }
                    elseif($provider_tags[$providerTagKey]['tag_type'] == "dropdown" && $provider_tags[$providerTagKey]['multi_select'] == 1)
                    {
                        $explodedData = [];
                        if(!empty($provider_tags[$providerTagKey]['tag_option']))
                        {
                            $explodedData = explode(",",$provider_tags[$providerTagKey]['tag_option']);
                            $tagAnswers = $this->Contract_model->TagAnswer(array('id_contract_tag' => $provider_tags[$providerTagKey]['id_contract_tag'] , 'explodedData' => $explodedData));
                            $tags[$k1]['value'] = !empty($tagAnswers) ? $tagAnswers[0]['tag_option_values'] : '';
                        }
                        else
                        {
                            $tags[$k1]['value'] = '';
                        }
                    }
                    elseif($provider_tags[$providerTagKey]['tag_type'] == "selected")
                    {
                        $tagAnswers = explode(",",$provider_tags[$providerTagKey]['tag_option']);
                        $modalData = [
                            'module' => $provider_tags[$providerTagKey]['selected_field'],
                            'ids' => $tagAnswers
                        ];
                        $tagOptionValue = $this->Tag_model->getNames($modalData);
                        $tags[$k1]['value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                    }



                //     if($provider_tags[$providerTagKey]['tag_type']!='input' || $provider_tags[$providerTagKey]['tag_type']!='date'){
                //         if(!empty($provider_tags[$providerTagKey]['tag_option']) || $provider_tags[$providerTagKey]['tag_option'] !=0 || $provider_tags[$providerTagKey]['tag_option'] !=NULL  || $provider_tags[$providerTagKey]['tag_option'] !='')
                //         {
                //             $get_answer= $this->Tag_model->getContractTagoptions(array('tag_id'=>$provider_tags[$providerTagKey]['id_tag'],'tag_option_id'=>$provider_tags[$providerTagKey]['tag_option']));
                //             if($get_answer[0]['tag_option_name']=='R'){
                //                $get_answer[0]['tag_option_name']='Red';
                //             }
                //             if($get_answer[0]['tag_option_name']=='G'){
                //                $get_answer[0]['tag_option_name']='Green';
                //             }
                //             if($get_answer[0]['tag_option_name']=='A'){
                //                $get_answer[0]['tag_option_name']='Amber';
                //             }
                //            $tags[$k1]['value']=$get_answer[0]['tag_option_name'];
                //         }
                //         else
                //         {
                //             $tags[$k1]['value']='';
                //         }
                //    }
                //     if($provider_tags[$providerTagKey]['tag_type']=='input' || $provider_tags[$providerTagKey]['tag_type']=='date'){
                //        $tags[$k1]['value']=$provider_tags[$providerTagKey]['tag_answer'];
                //    }

                   

                }
                else
                {
                    $tags[$k1]['id'] = '';
                    $tags[$k1]['field_type'] = '';
                    $tags[$k1]['value']='';
                }
            }
            $excel_data[$k]['Id']=$v['unique_id'];
            $excel_data[$k]['ProviderName']=$v['provider_name'];
            $excel_data[$k]['Description']=($v['description']=='null'?"":$v['description']);
            $excel_data[$k]['CompanyAddress']= $v['company_address'];
            $excel_data[$k]['City']= $v['city'];
            $excel_data[$k]['Country']= $v['country_name'];
            $excel_data[$k]['Postalcode']= $v['postal_code'];
            $excel_data[$k]['Vat']=($v['vat']=='null'?"":$v['vat']);
            $excel_data[$k]['Category']= $v['category_name'];
            // $excel_data[$k]['Riskprofile']= $v['risk_profile'];
            // $excel_data[$k]['ApprovalStatus']= $v['approval_status'];
            $excel_data[$k]['TotalSpend']= $v['total_spent'];
            // $excel_data[$k]['TotalSpend']= $v['total_spent'];
            $excel_data[$k]['tags']=$tags;
            // print_r($v);exit;
            $Projected_value=0;
            $mail_currecy=$this->User_model->check_record('currency',array('customer_id'=>$data['customer_id'],'is_maincurrency'=>1));
            if(!empty($v['contract_ids'])){
                $contracts_ids = $this->Customer_model->getProviderContracts(array("customer_id"=>$data['customer_id'],'provider_id'=>$v['id_provider']));
                if(!empty($contracts_ids))
                {
                    foreach($contracts_ids as $ci=> $cid){
                        $contract_info = $this->User_model->check_record_selected('id_contract,contract_name,currency_id,contract_value,contract_value_period,po_number,additional_recurring_fees,additional_recurring_fees_period,additonal_one_off_fees,contract_start_date,contract_end_date,TIMESTAMPDIFF(MONTH,contract_start_date,contract_end_date) months','contract',array('id_contract'=>$cid['id_contract']));
                        $graph = $this->spent_mngment_graph('spent_line','Actual Spent',$contract_info[0]);
                        $get_exg_rate=$this->User_model->getCurrencyDetails(array('contract_id'=>$contract_info[0]['id_contract']));
                        $exhange_value=1;
                        $exhange_value=str_replace(',','.',$get_exg_rate[0]['euro_equivalent_value']);
                        if($exhange_value == 0 || $get_exg_rate[0]['currency_name']==$mail_currecy[0]['currency_name']){
                            $exhange_value=1;
                        }
                        $Projected_value += ($exhange_value) *(array_sum(array_map(function($i){ return (int)$i->data[0]->value;},$graph->dataset)));
  
                    }
                }
            }
            $excel_data[$k]['TotalSpend']= $Projected_value>0?$mail_currecy[0]['currency_name'].' '.$Projected_value:null;
            // print_r($excel_data[$k]['TotalSpend']);
            // print_r('<br>');
            // $result[$s]['total_spent']=$Projected_value>0?$Projected_value:null;
           
        }
        ///writing data row by row
        //echo '<pre>'.print_r($excel_data);exit;
        foreach($excel_data as $k => $v){
            $columnBegin =$excelColumnstartsFrom;
            foreach($v as $v1){
                if(is_array($v1)){
                    foreach($v1 as $v2){    
                        // if($v2['field_type'] == 'number'){
                        //     $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,number_format($v2['value'],0));
                        //     $this->excel->getActiveSheet()->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                        //         array('borders' => array(
                        //             'allborders' => array(
                        //                 'style' => PHPExcel_Style_Border::BORDER_THIN
                        //             )
                        //         ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),'font'  => array('size'=>12)));
                        if($v2['field_type'] == 'date'){
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
                            $this->excel->setActiveSheetIndex(0)->getStyle($this->getkey($columnBegin) . $excelstartsfrom)->applyFromArray(
                                array('borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                ),'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),'font'  => array('size'=>12)))->getNumberFormat()->setFormatCode($format);
                         
                    }else{
                            $this->excel->setActiveSheetIndex(0)->setCellValue($this->getkey($columnBegin) . $excelstartsfrom, str_replace(",",";",$v2['value']));
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
                    $this->excel->setActiveSheetIndex(0)
                    ->setCellValue($this->getkey($columnBegin) . $excelstartsfrom,$v1);
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
        $this->excel->getActiveSheet()->getStyle($this->getkey($excelColumnstartsFrom).$excelRowstartsfrom.':'.$this->getkey($columnBegin).$excelstartsfrom)
            ->getAlignment()->setWrapText(true);



        $this->excel->getActiveSheet()->setSelectedCells('A0');
        //activate worksheet number 1
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Blad1');
        if($data['export_type']=='All_Providers'){
            $data['export_type']='All_Relations';
        }
        $filename = $data['export_type'].'_'.date("d-m-Y",strtotime(currentDate())).'.xls';
        // echo $filename;exit;//save our workbook as this file name
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $file_path = FILE_SYSTEM_PATH.'downloads/' . $filename;
        $objWriter->save($file_path);
        $view_path='downloads/' . $filename;
        $file_path = REST_API_URL.$view_path;
        $file_path = str_replace('::1','localhost',$file_path);

        $insert_id = $this->Download_model->addDownload(array('path'=>$view_path,'filename'=>$filename,'user_id'=>$this->session_user_info->id_user,'access_token'=>substr($_SERVER['HTTP_AUTHORIZATION'],7),'status'=>0,'created_date_time'=>currentDate()));
        //echo ''.$this->db->last_query(); exit;

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
    // public function dailynotificationcount_get(){
    //     $data = $this->input->get();
    //     if(empty($data)){
    //         $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }
    //     $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
    //     $validated = $this->form_validator->validate($data);
    //     if($validated != 1)
    //     {
    //         $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
    //         $this->response($result, REST_Controller::HTTP_OK);
    //     }
    //     if(isset($data['id_user'])) {
    //         $data['id_user'] = pk_decrypt($data['id_user']);
    //         if($data['id_user']!=$this->session_user_id){
    //             $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
    //             $this->response($result, REST_Controller::HTTP_OK);
    //         }
    //     }

    //     $data = tableOptions($data);
    //     $result = $this->Customer_model->dailynotificationcount($data);

    //     $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
    //     $this->response($result, REST_Controller::HTTP_OK);
    // }
    function provider_change_log($data,$provider_id){
        // print_r($provider_id);
        // print_r($data);exit;
        $provider_changed = 0;
        $status_chage = 0;
        // $provider_curent_tags = $this->Tag_model->getContractTags(array('contract_id'=>$data['id_contract'],'status'=>1,'name'=>true));
        $provider_curent_tags = $this->Customer_model->getInfoProviderTags(array('provider_id'=>$provider_id));
        // echo '<pre>'.print_r($provider_curent_tags);exit;
        $tag_log_json = '[';
        foreach($provider_curent_tags as $kt => $vt){



            // if($vt['tag_type'] == 'input' || $vt['tag_type'] == 'date'){
            //     $savedanswer=$vt['tag_answer'];
            //     $savinganswer=$data[$kt]['tag_answer'];
            // }
            // else{
            //     $get_saved_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>$vt['tag_option']));
            //     $savedanswer=isset($get_saved_answer[0]['tag_answer'])?$get_saved_answer[0]['tag_answer']:'';
            //     $get_saving_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>pk_decrypt($data[$kt]['tag_option'])));
            //     $savinganswer=isset($get_saving_answer[0]['tag_answer'])?$get_saving_answer[0]['tag_answer']:'';
            // }
            // $tag_json = '{"tag_text":"'.$vt['tag_text'].'","tag_value":"'.$savedanswer.'","tag_type":"'.$vt['tag_type'].'","tag_comments":"'.$data[$kt]['comments'].'","tag_id":"'.pk_encrypt($vt['id_tag']).'"},';

            
            
            // if($vt['tag_type'] == 'input' || $vt['tag_type'] == 'date'){
            //     $savedanswer=$vt['tag_answer'];
            //     $savinganswer=$data[$kt]['tag_answer'];
            // }
            // elseif($vt['tag_type'] == 'rag' || $vt['tag_type'] == 'radio' || ($vt['tag_type'] == 'dropdown' && (int)$vt['multi_select'] == 0))
            // {
            //     $get_saved_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>$vt['tag_option']));
            //     $savedanswer=isset($get_saved_answer[0]['tag_answer'])?$get_saved_answer[0]['tag_answer']:'';
            //     $get_saving_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>pk_decrypt($data[$kt]['tag_option'])));
            //     $savinganswer=isset($get_saving_answer[0]['tag_answer'])?$get_saving_answer[0]['tag_answer']:'';
            // }
            // elseif(($vt['tag_type'] == 'selected') || ($vt['tag_type'] == 'dropdown' && (int)$vt['multi_select'] == 1)){
            //     if($vt['tag_type'] == 'dropdown'){
            //         $get_saved_answer=$this->Tag_model->getMultiSelectedDropDown(array('options'=>explode(",",$vt['tag_option'])));
            //         $savedanswer=isset($get_saved_answer[0]['options'])?$get_saved_answer[0]['options']:'';
            //         $get_saving_answer=$this->Tag_model->getMultiSelectedDropDown(array('options'=>pk_decrypt($data[$kt]['tag_option'])));
            //         $savinganswer=isset($get_saving_answer[0]['tag_answer'])?$get_saving_answer[0]['tag_answer']:'';
            //     }
            //     else
            //     {
            //         $get_saved_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>$vt['tag_option']));
            //         $savedanswer=isset($get_saved_answer[0]['tag_answer'])?$get_saved_answer[0]['tag_answer']:'';
            //         $get_saving_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>pk_decrypt($data[$kt]['tag_option'])));
            //         $savinganswer=isset($get_saving_answer[0]['tag_answer'])?$get_saving_answer[0]['tag_answer']:'';

            //     }
            // }
            // $tag_json = '{"tag_text":"'.$vt['tag_text'].'","tag_value":"'.$savedanswer.'","tag_type":"'.$vt['tag_type'].'","tag_comments":"'.$data[$kt]['comments'].'","tag_id":"'.pk_encrypt($vt['id_tag']).'"},';
            // $data[$kt]['tag_id'] = pk_decrypt($data[$kt]['tag_id']);
            // if($savedanswer != $savinganswer){
            //     $provider_changed = 1;
            // }
            // $tag_log_json .= $tag_json;


            $tag_json = '{"tag_text":"'.$vt['tag_text'].'","tag_value":"'.$vt['tag_option_values'].'","tag_type":"'.$vt['tag_type'].'","tag_comments":"'.$vt['comments'].'","tag_id":"'.pk_encrypt($vt['id_tag']).'"},';

            if(empty($vt['business_unit_id']))
            {
                $groupedKey = 0 ;
            }
            else{
                $groupedKey = array_search(pk_encrypt($vt['business_unit_id']), array_column($data, 'business_unit_id'));
            }
            $tagKey = array_search(pk_encrypt($vt['id_tag']), array_column($data[$groupedKey]['tag_details'], 'tag_id'));
            if(is_numeric($groupedKey) && is_numeric($tagKey))
            {
                $Tagdata =$data[$groupedKey]['tag_details'][$tagKey];
                if(($Tagdata['tag_type'] == 'input' && $vt['tag_type'] == 'input' ) || ($Tagdata['tag_type'] == "date")){
                    if($Tagdata['tag_option'] != $vt['tag_option_values']){ $provider_changed = 1; }
                }
                elseif($Tagdata['tag_type'] == "rag" || $Tagdata['tag_type'] == "radio" || ($Tagdata['tag_type'] == "dropdown" && $Tagdata['multi_select'] == 0))
                {
                    if(pk_decrypt($Tagdata['tag_option']) != $vt['tag_option']){ $provider_changed = 1;}
                }
                elseif($Tagdata['tag_type'] == "dropdown" && $Tagdata['multi_select'] == 1)
                {
                    $updatedTagOptionarray = [];
                    foreach($Tagdata['tag_answer'] as $updatedTagOption)
                    {
                        $updatedTagOptionarray[] = pk_decrypt($updatedTagOption);
                    }
                    $updatedTagOptionImpData = '';
                    $updatedTagOptionImpData = implode("," , $updatedTagOptionarray);
                    if(($updatedTagOptionImpData != $vt['tag_option'] ) && !empty($vt['tag_option']) && !empty($updatedTagOptionImpData))
                    {
                        $provider_changed = 1;
                    }
                }
                elseif($Tagdata['tag_type'] == "selected"){
                    $Tagdata['tag_answer'] = ($Tagdata['multi_select'] == 1) ? $Tagdata['tag_answer'] :array($Tagdata['tag_answer']);
                    $updatedTagOptionarray = [];
                    foreach($Tagdata['tag_answer'] as $updatedTagOption)
                    {
                        $updatedTagOptionarray[] = pk_decrypt($updatedTagOption);
                    }
                    $updatedTagOptionImpData = '';
                    $updatedTagOptionImpData = implode("," , $updatedTagOptionarray);
                    $tagAnswers = explode(",",$Tagdata['tag_answer']);
                    $modalData = [
                        'module' => $Tagdata['selected_field'],
                        'ids' => $tagAnswers
                    ];
                    $tagOptionValue = $this->Tag_model->getNames($modalData);
                    $Tagdata['tag_option_value'] = !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                    if($updatedTagOptionImpData != $vt['tag_option']&& !empty($vt['tag_option']) && !empty($updatedTagOptionImpData))
                    {
                        $provider_changed = 1;
                    }
                }
                $tag_log_json .= $tag_json;

            }
        }
        $tag_log_json = rtrim($tag_log_json,",").']'; // Removing last comma and appendint ']'
        $provider_curent_info = $this->User_model->check_record('provider',array('id_provider'=>$provider_id));
        if(isset($provider_curent_info[0])){
            if($provider_curent_info[0]['unique_id'] != $data['unique_id']) $provider_changed = 1;
            if($provider_curent_info[0]['provider_name'] != $data['provider_name']) $provider_changed = 1;
            if($provider_curent_info[0]['description'] != $data['description']) $provider_changed = 1;
            if($provider_curent_info[0]['company_address'] != $data['company_address']) $provider_changed = 1;
            if($provider_curent_info[0]['city'] != $data['city']) $provider_changed = 1;
            if($provider_curent_info[0]['postal_code'] != $data['postal_code']) $provider_changed = 1;
            if($provider_curent_info[0]['country'] != $data['country']) $provider_changed = 1;
            if($provider_curent_info[0]['category_id'] != $data['category_id']) $provider_changed = 1;
            // if($provider_curent_info[0]['category_id'] != $data['category_id']) $provider_changed = 1;
            if($provider_curent_info[0]['vat'] != $data['vat']) $provider_changed = 1;
            


            
            if(isset($data['internal_contract_responsible']))                if($provider_curent_info[0]['internal_contract_responsible'] != $data['internal_contract_responsible']) $provider_changed = 1;
            if(isset($data['internal_contract_sponsor']))                if($provider_curent_info[0]['internal_contract_sponsor'] != $data['internal_contract_sponsor']) $provider_changed = 1;
            if(isset($data['internal_partner_relationship_manager']))                if($provider_curent_info[0]['internal_partner_relationship_manager'] != $data['internal_partner_relationship_manager']) $provider_changed = 1;
            if(isset($data['provider_contract_responsible']))                if($provider_curent_info[0]['provider_contract_responsible'] != $data['provider_contract_responsible']) $provider_changed = 1;
            if(isset($data['provider_contract_sponsor']))                if($provider_curent_info[0]['provider_contract_sponsor'] != $data['provider_contract_sponsor']) $provider_changed = 1;

        if($provider_changed == 1){ 
            $provider_log_data=array(
                'provider_id'=>$provider_id,
                'provider_name'=>$provider_curent_info[0]['provider_name'],
                'description'=>$provider_curent_info[0]['description'],
                'company_address'=>$provider_curent_info[0]['company_address'],
                'city'=>$provider_curent_info[0]['city'],
                'country'=>$provider_curent_info[0]['country'],
                'postal_code'=>$provider_curent_info[0]['postal_code'],
                'customer_id'=>$provider_curent_info[0]['customer_id'],
                'status'=>$provider_curent_info[0]['status'],
                'unique_id'=>$provider_curent_info[0]['unique_id'],
                'vat'=>$provider_curent_info[0]['vat'],
                'created_on'=>currentDate(),
                'created_by' => $this->session_user_id,
                'category_id'=>$provider_curent_info[0]['category_id'],
                'internal_contract_sponsor'=>$provider_curent_info[0]['internal_contract_sponsor'],
                'provider_contract_sponsor'=>$provider_curent_info[0]['provider_contract_sponsor'],
                'internal_partner_relationship_manager'=>$provider_curent_info[0]['internal_partner_relationship_manager'],
                'provider_partner_relationship_manager'=>$provider_curent_info[0]['provider_partner_relationship_manager'],
                'provider_contract_responsible'=>$provider_curent_info[0]['provider_contract_responsible'],
                'internal_contract_responsible'=>$provider_curent_info[0]['internal_contract_responsible'],
                'provider_tags_data'=>$tag_log_json
            );
            $this->User_model->insert_data('provider_log',$provider_log_data);
            // echo '<pre>'.
        }
        }
    }
    public function providerlog_get(){
        $data = $this->input->get();
        if (empty($data)) {
            $result = array('status' => FALSE, 'error' => $this->lang->line('invalid_data'), 'data' => '');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_provider'])) {
            $data['id_provider'] = pk_decrypt($data['id_provider']);
        //     if($this->session_user_info->user_role_id!=7)
        //     if(!in_array($data['contract_id'],$this->session_user_contracts)){
        //         $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
        //         $this->response($result, REST_Controller::HTTP_OK);
        //     }
        }
        if(isset($data['provider_log_id'])) $data['provider_log_id']=pk_decrypt($data['provider_log_id']);
        $current_Provider_detailis=array();
        $provider_log_options=array();
        if(isset($data['id_provider'])){
            // $current_Provider_detailis = $this->Contract_model->getContractCurrentDetails($data);//echo 
            $current_Provider_detailis = $this->Customer_model->getproviderlist(array('id_provider'=>$data['id_provider']));//echo $this->db->last_query();exit
            $current_Provider_detailis=$current_Provider_detailis['data'];
            // print_r($current_Provider_detailis);exit;
            $current_Provider_detailis[0]['provider_tags'] = [];
            // $tag_data = $this->Tag_model->getContractTags(array('contract_id'=>$data['contract_id'],'status'=>1,'name'=>true));
            $tag_data = $this->Customer_model->getInfoProviderTags(array('provider_id'=>$data['id_provider']));
            // $tag_data = $this->Tag_model->getContractTags(array('contract_id'=>$data['contract_id'],'status'=>1,'name'=>true));
            // print_r($tag_data);exit;
            if(count($tag_data)>0)
                foreach($tag_data as $k => $v){
                    if($v['tag_option'] == 0 || $v['tag_option'] == '0'){
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_text'] = $v['tag_text'];
                        // $current_Provider_detailis[0]['contract_tags'][$k]['tag_value'] = is_null($current_Provider_detailis[0]['contract_tags'][$k]['tag_value']) ? '' : $current_Provider_detailis[0]['contract_tags'][$k]['tag_value'];
                        //$current_Provider_detailis[0]['provider_tags'][$k]['tag_value'] = isset($v['tag_answer'])?$v['tag_answer']:'';
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_value'] = !empty($v['tag_answer'])?$v['tag_answer']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_id'] = pk_encrypt($v['id_tag']);
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_comments'] = !empty($v['comments'])?$v['comments']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_type'] = $v['tag_type'];
                        
                    }
                    else{
                        if($v['tag_type']=='date' || $v['tag_type']=='input'){
                            $tag_answer=isset($v['tag_answer'])?$v['tag_answer']:'';
                        }
                        elseif($v['tag_type']=='radio' || $v['tag_type']=='rag' || ($v['tag_type']=='dropdown' && ($v['multi_select'] == 0)))
                        {
                            $get_saved_answer=$this->Customer_model->getOptionvalue(array('id_tag_option'=>$v['tag_option']));
                            $tag_answer=isset($get_saved_answer[0]['tag_answer'])?$get_saved_answer[0]['tag_answer']:'';

                        }
                        elseif($v['tag_type'] == 'dropdown' && ($v['multi_select'] == 1))
                        {
                            $get_saved_answer = $this->Tag_model->getMultiSelectedDropDown(array("options" => explode(",",$v['tag_option'])));
                            $tag_answer=isset($get_saved_answer[0]['options'])?$get_saved_answer[0]['options']:'';
                        }
                        elseif($v['tag_type'] == 'selected'){
                            $modeldata = array(
                                'module' => $v['selected_field'],
                                'ids' => explode(",",$v['tag_option'])
                            );
                            $tagOptionValue = $this->Tag_model->getNames($modeldata);
                            $tag_answer =  !empty($tagOptionValue) ? $tagOptionValue[0]['tag_option_value'] : '';
                        }

                        
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_text'] = $v['tag_text'];
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_value'] = !empty($tag_answer)?$tag_answer:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_id'] = pk_encrypt($v['id_tag']);
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_comments'] = !empty($v['comments'])?$v['comments']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_type'] = $v['tag_type'];
                    }
                }
            else{
                $tag_data = $this->Tag_model->TagList(array('customer_id'=>$this->session_user_info->customer_id,'status'=>1,'tag_type'=>'provider_tags'));
                //echo '<pre>'.print_r($this->session_user_info);exit;
                foreach($tag_data as $k => $v){
                    if($v['tag_option'] == 0 || $v['tag_option'] == '0'){
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_text'] = $v['tag_text'];
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_value'] = !empty($v['tag_option_value'])?$v['tag_option_value']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_id'] = pk_encrypt($v['id_tag']);
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_comments'] = !empty($v['comments'])?$v['comments']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_type'] = $v['tag_type'];


                        // print_r($v);exit;
                    }
                    else{
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_text'] = $v['tag_text'];
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_value'] = !empty($v['tag_option_value'])?$v['tag_option_value']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_id'] = pk_encrypt($v['id_tag']);
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_comments'] = !empty($v['comments'])?$v['comments']:null;
                        $current_Provider_detailis[0]['provider_tags'][$k]['tag_type'] = $v['tag_type'];


                    }
                }
            }
            $provider_log_options = $this->Contract_model->getProviderLogId($data);

            /*foreach($provider_log_options as $k => $v){
                $provider_log_options[$k]['log_option'] = $v['created_on'].' by '.$v['created_by'];
            }*/
        }

        $contract_log_details=array();
        if(isset($data['provider_log_id'])){
            $contract_log_details = $this->Customer_model->getProviderloginfo($data);
            if(isset($contract_log_details[0]))
            {
                $tagDetails = json_decode($contract_log_details[0]['provider_tags_data']);
                foreach($tagDetails as $k=>$v)
                {
                    $tagDetails[$k]->tag_comments = !empty($tagDetails[$k]->tag_comments)?$tagDetails[$k]->tag_comments:null;
                    $tagDetails[$k]->tag_value = !empty($tagDetails[$k]->tag_value)?$tagDetails[$k]->tag_value:null;
                }
                $contract_log_details[0]['provider_tags'] = $tagDetails;
            }
            
        }
        unset($contract_log_details[0]['provider_tags_data']);
        // print_r($contract_log_details);exit;
        //echo '<pre>'.print_r($contract_log_details);exit;
        foreach($provider_log_options as $k=>$v){
            $provider_log_options[$k]['id_provider_log']=pk_encrypt($provider_log_options[$k]['id_provider_log']);
        }
        foreach($current_Provider_detailis as $k=>$v){
            // print_r($v);exit;
            $current_Provider_detailis[$k]['vat']=$v['vat']=='null'?'':$v['vat'];
            $current_Provider_detailis[$k]['description']=$v['description']=='null'?'':$v['description'];
            if($current_Provider_detailis[$k]['provider_contract_sponsor']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['provider_contract_sponsor']));
                $current_Provider_detailis[$k]['provider_contract_sponsor'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($current_Provider_detailis[$k]['provider_contract_responsible']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['provider_contract_responsible']));
                $current_Provider_detailis[$k]['provider_contract_responsible'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($current_Provider_detailis[$k]['internal_contract_sponsor']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['internal_contract_sponsor']));
                $current_Provider_detailis[$k]['internal_contract_sponsor'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($current_Provider_detailis[$k]['internal_partner_relationship_manager']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['internal_partner_relationship_manager']));
                $current_Provider_detailis[$k]['internal_partner_relationship_manager'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($current_Provider_detailis[$k]['provider_partner_relationship_manager']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['provider_partner_relationship_manager']));
                $current_Provider_detailis[$k]['provider_partner_relationship_manager'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($current_Provider_detailis[$k]['internal_contract_responsible']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$current_Provider_detailis[$k]['internal_contract_responsible']));
                $current_Provider_detailis[$k]['internal_contract_responsible'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            $current_Provider_detailis[$k]['id_provider']=pk_encrypt($current_Provider_detailis[$k]['id_provider']);
            $current_Provider_detailis[$k]['customer_id']=pk_encrypt($current_Provider_detailis[$k]['customer_id']);
            $current_Provider_detailis[$k]['category_id']=pk_encrypt($current_Provider_detailis[$k]['category_id']);
            
        }
        foreach($contract_log_details as $k=>$v){
            $contract_log_details[$k]['vat']=$v['vat']=='null'?'':$v['vat'];
            $contract_log_details[$k]['description']=$v['description']=='null'?'':$v['description'];
            if($contract_log_details[$k]['provider_contract_sponsor']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['provider_contract_sponsor']));
                $contract_log_details[$k]['provider_contract_sponsor'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($contract_log_details[$k]['provider_contract_responsible']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['provider_contract_responsible']));
                $contract_log_details[$k]['provider_contract_responsible'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($contract_log_details[$k]['internal_contract_sponsor']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['internal_contract_sponsor']));
                $contract_log_details[$k]['internal_contract_sponsor'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($contract_log_details[$k]['internal_partner_relationship_manager']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['internal_partner_relationship_manager']));
                $contract_log_details[$k]['internal_partner_relationship_manager'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($contract_log_details[$k]['provider_partner_relationship_manager']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['provider_partner_relationship_manager']));
                $contract_log_details[$k]['provider_partner_relationship_manager'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }
            if($contract_log_details[$k]['internal_contract_responsible']!=''){
                $user_info = $this->User_model->check_record('user',array('id_user'=>$contract_log_details[$k]['internal_contract_responsible']));
                $contract_log_details[$k]['internal_contract_responsible'] = $user_info[0]['first_name'].' '.$user_info[0]['last_name'];
            }

            $contract_log_details[$k]['id_provider_log']=pk_encrypt($contract_log_details[$k]['id_provider_log']);
            $contract_log_details[$k]['provider_id']=pk_encrypt($contract_log_details[$k]['provider_id']);
            $contract_log_details[$k]['customer_id']=pk_encrypt($contract_log_details[$k]['customer_id']);
            $contract_log_details[$k]['category_id']=pk_encrypt($contract_log_details[$k]['category_id']);
            
        }
        $result =array('current_Provider_detailis'=>$current_Provider_detailis,'provider_log_options'=>$provider_log_options,'contract_log_details'=>$contract_log_details);
        // echo '<pre>';print_r(json_encode($result));exit;
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function dashboardInfoTabs_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])){
            $data['id_user']=pk_decrypt($data['id_user']);
        }
         $default_tab_order='[{"tabID":1,"tabName":"ALL ACTIVITIES","content":"all-activities-graph.html"},{"tabID":2,"tabName":"ALL CONTRACTS","content":"all-contracts-graph.html"},{"tabID":3,"tabName":"ALL ACTION ITEMS","content":"all-action-items.html"},{"tabID":4,"tabName":"CO-WORKERS","content":"all-coworkers-graph.html"},{"tabID":5,"tabName":"ALL PROJECTS","content":"all-projects-graph.html"},{"tabID":6,"tabName":"ALL PROVIDERS","content":"all-providers-graph.html"}]';
        // $default_tab_order='[{"tabID":1,"tabName":"ALL ACTIVITIES","content":"all-activities-graph.html"},{"tabID":2,"tabName":"ALL CONTRACTS","content":"all-contracts-graph.html"},{"tabID":3,"tabName":"ALL ACTION ITEMS","content":"all-action-items.html"},{"tabID":4,"tabName":"CO-WORKERS","content":"all-coworkers-graph.html"}]';
        $tabs_info=$this->User_model->check_record_selected('dashboard_tabs_order','user',array('id_user'=>$data['id_user']));
        $tabs=!empty($tabs_info[0]['dashboard_tabs_order'])?$tabs_info[0]['dashboard_tabs_order']:$default_tab_order;
        $tas_responce=json_decode($tabs);
        if($this->session_user_info->user_role_id==7){
            $tas_responce=array();
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$tas_responce);
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function addDashboard_post()
    {
        $data = $this->input->post();
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])){
            $data['id_user']=pk_decrypt($data['id_user']);
        }
        // print_r(json_encode($data['data']));exit;

        $this->User_model->update_data('user',array('dashboard_tabs_order'=>json_encode($data['data'])),array('id_user'=>$data['id_user']));
        // echo 
        $result = array('status'=>TRUE, 'message' => $this->lang->line('tabs_order_changed_sucessfully'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);

    }
    public function customersList_get()
    {
        $data = $this->input->get();
        if($this->session_user_info->user_role_id!=1){
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            $this->response($result, REST_Controller::HTTP_FORBIDDEN);
        }
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $data['status'] = 1;
        $companies = $this->Customer_model->customerList($data);
        for($s=0;$s<count($companies['data']);$s++)
        {
            $companyDetails[] = array(
                'id_customer' => pk_encrypt($companies['data'][$s]['id_customer']),
                'company_name' => $companies['data'][$s]['company_name'],
                'company_status' => $companies['data'][$s]['company_status'],
            );
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('total_records' => $companies['total_records'] , 'companies' => $companyDetails));
        $this->response($result, REST_Controller::HTTP_OK);
    }
    public function customerUsers_get()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required' => $this->lang->line('customer_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }
        if(isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if($this->session_user_info->user_role_id!=1 && $this->session_user_info->customer_id!=$data['customer_id']){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_FORBIDDEN);
            }
        }
        $data = tableOptions($data);//helper function ordering smart table grid option

        $data['user_role_not']=array(1,2);
        $data['status'] = 1;
        $users = $this->Customer_model->getCustomerUserList($data);//echo $this->db->last_query();exit;
        for($s=0;$s<count($users['data']);$s++)
        {
            
            $userDetails[] =array(
                'id_user' => pk_encrypt($users['data'][$s]['id_user']),
                'name' => $users['data'][$s]['name'],
                'email' => $users['data'][$s]['email'],
                'user_status' => $users['data'][$s]['user_status'],
                'customer_id' => pk_encrypt($data['customer_id'])
            );
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('total_records' => $users['total_records'] , 'users' => $userDetails));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function checkUserCreateUpdateAccess($data)
    {
        if(!empty($data['updateableUserId']))
        {
            $updateableUserDetails = $this->User_model->getUserInfo(array('user_id'=>$data['updateableUserId']));
            if($data['loginUserDetails']->user_role_id == 1) {
                return true;
            }
            elseif($data['loginUserDetails']->user_role_id == 2 && ($updateableUserDetails->customer_id ==  $data['loginUserDetails']->customer_id))
            {
                return true;
            }
            elseif($data['loginUserDetails']->user_role_id == 3)
            {
                $BuDetails = $this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$data['updateableUserId']));
                $LoginUserBuDetails = $this->Validation_model->getBusinessUnitListByUser(array('user_id'=>$data['loginUserDetails']->id_user));
                if($updateableUserDetails->user_role_id == 7)
                {
                    return true;
                }
                foreach($BuDetails as $updateableUserBu)
                {
                    if(in_array($updateableUserBu ,array_values($LoginUserBuDetails)))
                    {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    public function userDelete_delete()
    {
        $data = $this->input->get();
        $this->form_validator->add_rules('id_user', array('required'=>$this->lang->line('user_id_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
        }
        $deleteUserInfo = $this->User_model->check_record('user',array('id_user' => $data['id_user']));

        if(empty($deleteUserInfo))
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'3');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        if($deleteUserInfo[0]['user_status'] != 0)
        {
            if(!($deleteUserInfo[0]['user_role_id'] == 7 && $deleteUserInfo[0]['contribution_type'] == 2))
            {
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('inactive_user_first')), 'data'=>'1');
                $this->response($result, REST_Controller::HTTP_OK);
            }
           
        }

        if($deleteUserInfo[0]['customer_id']  == 0  && $deleteUserInfo[0]['user_role_id'] == 1)
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'2');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        
        $sessionUserInfo = $this->session_user_info;


        if($sessionUserInfo->customer_id != $deleteUserInfo[0]['customer_id'] && $sessionUserInfo->customer_id!=0)
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'4');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $canDelete = [];

        if($sessionUserInfo->user_role_id == 1 && $sessionUserInfo->customer_id == 0)
        {
            //can delete customer admin ,owner ,delegate , readonly user ,external user and  manager roles
            $canDelete = [2,3,4,5,6,7,8];
        }
        elseif($sessionUserInfo->user_role_id == 2)
        {
            //can delete owner ,delegate , readonly user ,external user and  manager roles
            $canDelete = [3,4,5,6,7,8];
        }
        elseif($sessionUserInfo->user_role_id == 3)
        {
            //can delete owner ,delegate , readonly user ,external user and  manager roles
            $canDelete = [3,4,5,6,7,8];
        }
        if(count($canDelete) > 0 && in_array($deleteUserInfo[0]['user_role_id'],$canDelete))
        {
            //can Delete user
            $this->User_model->updateUser(array('is_deleted' => 1 , 'email' => null),$data['id_user']);
        }
        else
        {
            $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'5');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $result = array('status'=>TRUE, 'message' => $this->lang->line('user_deleted_successfully'), 'data'=>'6');
        $this->response($result, REST_Controller::HTTP_OK);

    }
}