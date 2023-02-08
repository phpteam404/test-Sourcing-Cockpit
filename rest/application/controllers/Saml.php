<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);

class Saml extends CI_Controller
{
    public function redirection()
    {
      $data = $this->input->get();
      $encryptedData = $data['etoken'];
      $decryptedData = pk_decrypt($encryptedData);

      if(!empty($decryptedData)){
        $UserDetails = json_decode($decryptedData) ;
        $email = $UserDetails->eid;
        $expiryTime =new DateTime($UserDetails->expiry);
        $currentDateTime =  new DateTime();
        if(($expiryTime > $currentDateTime))
        {
          $userDetails = $this->User_model->check_record('user',array('email' => $email));
          $token = uniqid().strtotime('now');
          $samlLog = array(
            'email' => $email,
            'uuid' => $token,
            'expire' => date('Y-m-d H:i:s',strtotime('+10 minutes',strtotime(date("Y-m-d H:i:s")))),
            'status' => 1,
            'user_id' => !empty($userDetails) ? $userDetails[0]['id_user'] : NULL,
          );
          $this->User_model->insert_data('saml_log',$samlLog);
          if(!empty($userDetails))
          {
            //user exist 
            redirect(WEB_BASE_URL.'#/saml?token='.$token, 'refresh');
          }
          else{
            //logged with saml but user does not exist
            redirect(WEB_BASE_URL.'#/saml?token='.$token, 'refresh');
          }
        }
        else
        {
          redirect(WEB_BASE_URL.'#/login', 'refresh');
        }
      }
      else
      {
          redirect(WEB_BASE_URL.'#/login', 'refresh');
      }
    }

}
