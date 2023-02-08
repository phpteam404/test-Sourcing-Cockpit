<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Contract_builder extends REST_Controller
{
    public $session_user_id=NULL;
    public $session_user_info=NULL;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Validation_model');
        $this->load->model('Download_model');
        
        $getLoggedUserId=$this->User_model->getLoggedUserId();
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id=$getLoggedUserId[0]['id'];
        $this->session_user_info=$this->User_model->getUserInfo(array('user_id'=>$this->session_user_id));
    }
    public function curl($url,$method,$pagination,$parameters,$urlKey,$frontEndData)
    {
        // echo $url;exit;
        $ch = \curl_init($url);
        // Set HTTP Header for request 
        $headers = array(
            'Connection: Keep-Alive',
            'X-AUTH-TOKEN: '.CONTRACT_BUILDER_API_AUTH_TOKEN,
            'Authorization: Basic '. base64_encode("congen:q7RQzZVgnr") 
        );

        if(isset($parameters) && isset($parameters['page']))
        {
            $headers[] = 'Accept: application/hal+json';
        }
        else{
            $headers[] = 'Accept: application/json';
        }
        if($urlKey == "linkSCPcontractToBuild")
        {
            $contractBuildId = $parameters['contractBuildId'];
            $structure_id = $parameters['structureId'];
            $pdfUrl = CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/$contractBuildId/$structure_id/pdf";
            $internalCurl = $this->curl($pdfUrl,'GET' , false , [] , 'contractBuildLink', array(
                'version_number' => $frontEndData['version_number'],
                'contract_builder_name' =>$frontEndData['contract_builder_name'],
                'contract_id' => pk_decrypt($parameters['scpContractId']),
                'contract_build_id' => $parameters['contractBuildId'],
                'build_status' => $frontEndData['build_status']
            ));
            if(!$internalCurl)
            {
                $documentId = null;
                $result = array('status'=>FALSE,'error'=>"Linking Failed",'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $documentId = $internalCurl;
            }
             
        }
      
        // if($pagination){
        //     $headers[] = 'Accept: application/hal+json';
        // }
        // else{
        //     $headers[] = 'Accept: application/json';
        // }
        if($method == 'PATCH')
        {
            $headers[] = 'Content-Type: application/merge-patch+json';
        }
        else{
            $headers[] = 'Content-Type: application/json';
        }

        \curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
       
        if($method == 'POST')
        {
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode( $parameters, JSON_NUMERIC_CHECK ));
        }
        elseif($method == 'PATCH')
        {
            \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $parameters, JSON_NUMERIC_CHECK ));
        }
        elseif($method == 'DELETE')
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
       

        $response = \curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = \curl_error($ch);
        $errno    = \curl_errno($ch);
        
        if (\is_resource($ch)) {
            \curl_close($ch);
        }

        if (0 !== $errno) {
            throw new \RuntimeException($error, $errno);
        }
        if(isset($urlKey) && ($urlKey == "contractBuildPdf" || $urlKey == "contractBuilderDocx" || $urlKey == "contractBuildLink"))
        {
            $dotExtension = '.pdf';
            if($urlKey == "contractBuilderDocx")
            {
                $dotExtension = '.docx';
            }

            $pdf_data = $response;
            $document_name = $frontEndData['contract_builder_name'].'_v'.$frontEndData['version_number']. $dotExtension;
            $document_source = $frontEndData['contract_builder_name'].'_v'.$frontEndData['version_number']. '_' . time().'.pdf';

            $file_path = FILE_SYSTEM_PATH.'downloads/' . $document_name;

            file_put_contents( $file_path, $pdf_data);
            if($urlKey == "contractBuildLink")
            {
                $document_data['module_type'] = 'contract_builder';
                $document_data['module_id'] = $frontEndData['contract_build_id'];
                $document_data['reference_type'] = 'contract';
                $document_data['reference_id'] = $frontEndData['contract_id'];
                $document_data['document_name'] = $document_name;
                $document_data['document_type'] = 0;
                $document_data['document_source'] = $document_source;
                $document_data['document_mime_type'] = 'application/pdf';
                $document_data['validator_record'] = 0;
                $document_data['uploaded_by'] = $this->session_user_info->id_user;
                $document_data['uploaded_on'] = currentDate();
                $document_data['updated_on'] = currentDate();
                $document_data['version_number'] = $frontEndData['version_number'];
                $document_data['build_status'] = $frontEndData['build_status'];
                return ($this->User_model->insert_data('document',$document_data));
            }
            $view_path='downloads/' . $document_name;
            $file_path = REST_API_URL.$view_path;
            $file_path = str_replace('::1','localhost',$file_path);
            $insert_id = $this->Download_model->addDownload(array('path'=>$view_path,'filename'=>$document_name,'user_id'=>$this->session_user_info->id_user,'access_token'=>substr($_SERVER['HTTP_AUTHORIZATION'],7),'status'=>0,'created_date_time'=>currentDate()));
            $response = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>pk_encrypt($insert_id));
            $this->response($response, REST_Controller::HTTP_OK);
        }
        if(isset($urlKey) && ($urlKey == "linkSCPcontractToBuild") && !empty($documentId))
        {
            $resp = json_decode($response);
            $this->User_model->update_data('document', array('linked_id' => $resp->id) ,array('id_document' => $documentId));
        }
        
        return (array('responseCode' => $httpcode , 'response' =>json_decode($response, true)));
            
    }
    public function url_post(){

        $data = $this->input->post();
        $this->form_validator->add_rules('key', array('required'=>$this->lang->line('key_required')));
        $this->form_validator->add_rules('method', array('required'=>$this->lang->line('method_required')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        $urls_array = array(
            'masterTemplateList' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_structures",
            'customerContractBuild' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts",
            'customerContractBuildDetails' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}",
            'getStructureVariable' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_structures/{id}/variables",
            'createContractBuild' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts",
            'updateContractBuild' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}",
            'createStructure' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_structures",
            'deleteContractBuild' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}",
            'contractPreview' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}/{structure_id}/preview", // GET id->contractbuilsid, structure_id->structureId
            'contractBuilderDocx' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}/{structure_id}/download", //GET id->contractbuilsid, structure_id->structureId
            'linkSCPcontractToBuild' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contract_links",
            'contractBuildPdf' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}/{structure_id}/pdf",//GET id->contractbuilsid,structure_id->structureId
            'structureDiff' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_structures/{id}/{child_id}/diff",//GET id->structure_id,child_id->child_structure_id
            'contractVariablesdata' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contracts/{id}/variables",//GET id->contractbuilsid
            'unlinkingBuildFromContract' => CONTRACT_BUILDER_API_BASE_URL."sourcing_cockpit_contract_links/{id}",

        );
        $url = $urls_array[$data['key']];
        $frontEndData = array();
        if(($data['key'] == 'getStructureVariable' || $data['key'] == 'updateContractBuild' || $data['key'] == 'deleteContractBuild' || $data['key'] == 'contractPreview' || $data['key'] == 'contractBuilderDocx' || $data['key'] == 'customerContractBuildDetails' || $data['key'] == 'contractBuildPdf' || $data['key'] == 'structureDiff' || $data['key'] == 'contarctVariablesdata' || $data['key'] == 'contractVariablesdata' ) && isset($data['id']))
        {
            $url = str_replace('{id}', $data['id'], $url);
        }
        if(($data['key'] == 'contractPreview' || $data['key'] == 'contractBuilderDocx' || $data['key'] == 'contractBuildPdf') && isset($data['structure_id']))
        {
            $url = str_replace('{structure_id}', $data['structure_id'], $url);
        }
        if(($data['key'] == 'structureDiff' ))
        {
            $url = str_replace('{child_id}', $data['child_id'], $url);
        }
        $parameters = $data['parameters'];
        if(isset($data['method']) && !empty($data['method']))
        {
            $method = $data['method'];
        }
        else{
            $method = "GET";
        }
        if($data['key'] == 'customerContractBuild')
        {
            if(isset($data['parameters']))
            {
                $data['parameters']['customerId'] = pk_encrypt($this->session_user_info->customer_id);
            }
            else{
                $data['parameters']['customerId'] = pk_encrypt($this->session_user_info->customer_id);
            }
        }
        elseif($data['key'] == 'contractBuildPdf' || $data['key'] == 'contractBuildDocx' || $data['key'] == 'linkSCPcontractToBuild')
        {
            $frontEndData = array(
                'contract_builder_name' => $data['contract_builder_name'],
                'version_number' => $data['version_number'],
                'build_status' => $data['build_status'],
                // 'status' => $data['status'],
                // 'key' => $data['key'],
                // 'contract_builder_id' => $data['id'],
                // 'structure_id' => $data['structure_id'],
            );
        }
        if($method == "GET" && isset($data['parameters']) && !empty($data['parameters']))
        {
            if(isset($data['parameters']['page']))
            {
                $userData = $this->User_model->check_record('user' , array('id_user' => $this->session_user_id));
                $data['parameters']['itemsPerPage'] = isset($userData[0]['display_rec_count']) ? (int)$userData[0]['display_rec_count'] : 10;
            }
            $queryString =  http_build_query($data['parameters']);
            $url =  $url.'?'.$queryString;
        }
        if(isset($data['pagination']) && $data['pagination'] == true)
        {
            $pagination = true;
        }
        else
        {
            $pagination = false;
        }
        $externalApiData = $this->curl($url,$method,$pagination,$parameters,$data['key'],$frontEndData);
        $result = array('status'=>($externalApiData['responseCode']>= 200 && $externalApiData['responseCode'] < 300) ? TRUE : false, 'data'=>$externalApiData['response']);
        $responseCode = (($externalApiData['responseCode']>= 200) && ($externalApiData['responseCode'] < 300)) ? $externalApiData['responseCode'] : 200;
        $this->response($result, $responseCode);
       
    }

    public function searchCustomer_get()
    {
        //getting customer and businessUnit by customer id
        $data = $this->input->get();
        $this->form_validator->add_rules('customer_id', array('required'=>$this->lang->line('customer_id_req')));
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
        $customerData = $this->User_model->check_record_selected_join('id_customer,company_name as name,company_address as address,city,postal_code,vat_number,customer.country_id,country.country_name ,"customer" as type ,IF(country.country_name!="",CONCAT(customer.company_name," - ",country.country_name),customer.company_name) as display_name','customer',array('id_customer' => $data['customer_id']),'country','country.id_country=customer.country_id');
        $businessUnitData = $this->User_model->check_record_selected_join('id_business_unit,bu_name as name,company_address as address,city,postal_code,vat_number,business_unit.country_id,country.country_name ,"business_unit" as type ,IF(country.country_name!="",CONCAT(business_unit.bu_name," - ",country.country_name),business_unit.bu_name) as display_name','business_unit',array('customer_id' => $data['customer_id'] , 'status' => 1),'country','country.id_country=business_unit.country_id');
        $result = array_merge($customerData,$businessUnitData);
        foreach($result as $k=>$value)
        {
            if(isset($result[$k]['id_customer']))
            {
                $result[$k]['id_customer'] = pk_encrypt($result[$k]['id_customer']);
            }
            else
            {
                $result[$k]['id_business_unit'] = pk_encrypt($result[$k]['id_business_unit']);
            }
            $result[$k]['country_id'] = pk_encrypt($result[$k]['country_id']);
        }
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>$result);
        $this->response($result, REST_Controller::HTTP_OK);

    }

    

}