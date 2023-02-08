<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Settings extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function info_get()
    {
        $data = $this->input->get();
        /*helper function for ordering smart table grid options*/
        $data = tableOptions($data);
        $result = $this->Settings_model->getSettings($data);
        $result = array('status'=>TRUE, 'message' => $this->lang->line('success'), 'data'=>array('data' =>$result['data'],'total_records' => $result['total_records']));
        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function update_post()
    {
        $data = $this->input->post();
        if(empty($data)){
            $result = array('status'=>FALSE,'error'=>$this->lang->line('invalid_data'),'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('created_by', array('required'=>$this->lang->line('created_by_req')));
        $validated = $this->form_validator->validate($data);
        if($validated != 1)
        {
            $result = array('status'=>FALSE,'error'=>$validated,'data'=>'');
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if(isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if($data['created_by']!=$this->session_user_id){
                $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //$created_by = $data['created_by'];
        //$data = array_values($data['data']);
        /*$update_data = array();
        for($s=0;$s<count($data);$s++)
        {
            $update_data[] = array(
                'id_app_config' => $data['id_app_config'],
                'value' => $data['value'],
                'updated_by' => $created_by,
                'updated_on' => currentDate()
            );
        }*/
        $this->Settings_model->updateSettings(array(
            'id_app_config' => $data['id_app_config'],
            'value' => $data['value'],
            'updated_by' => $data['created_by'],
            'updated_on' => currentDate()));

        $result = array('status'=>TRUE, 'message' => $this->lang->line('settings_update'), 'data'=>'');
        $this->response($result, REST_Controller::HTTP_OK);
    }

}