<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Download_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mcommon');
    }

    public function addDownload($data){
        $this->db->insert('download', $data);
        return $this->db->insert_id();
    }

    public function updateDownload($data){
        $this->db->where('id_download',$data['id_download']);
        $this->db->update('download',$data);
    }

    public function checkDownload($data){

        $this->db->select('d.*');
        $this->db->from('download d');
        $this->db->where('d.id_download',$data['id_download']);
        $this->db->where('d.access_token',$data['access_token']);
        $this->db->where('d.user_id',$data['user_id']);
        $this->db->where('d.status',$data['status']);
        $query = $this->db->get();
        $result=$query->result_array();
        if(count($result)>0){
            return $result[0];
        }
        else{
            return false;
        }

    }


}