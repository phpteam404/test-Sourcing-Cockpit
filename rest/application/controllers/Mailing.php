<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/third_party/mailer/mailer.php';

class Mailing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function cron()
    {
        $mailer_data = $this->Customer_model->getMailer();
        echo '<pre>';print_r($mailer_data);exit;
        $from_name=$mailer_data['mail_from_name'];
        $from=$mailer_data['mail_from'];
        $subject=$mailer_data['mail_subject'];
        $body=$mailer_data['mail_message'];
        $to_name=$mailer_data['mail_to_name'];
        $to=$mailer_data['mail_to'];
        $mailer_id=$mailer_data['mailer_id'];
        $this->load->library('sendgridlibrary');
        $this->Customer_model->updateMailer(array('cron_status'=>1,'mailer_id'=>$mailer_id));
        $mail_sent_status=$this->sendgridlibrary->sendemail($from_name,$from,$subject,$body,$to_name,$to,array(),$mailer_id);
        if($mail_sent_status==1) {
            $this->Customer_model->updateMailer(array('status' => 1, 'mailer_id' => $mailer_id));
            $this->Customer_model->updateMailer(array('cron_status'=>2,'mailer_id'=>$mailer_id));
        }
        else{
            $this->Customer_model->updateMailer(array('cron_status'=>3,'mailer_id'=>$mailer_id));
        }


    }


}