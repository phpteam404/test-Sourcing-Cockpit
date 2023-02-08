<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{

    public function __construct(){
        parent::__construct();
    }


    public $key = '#@WITH-BRO-TOOL$#';
    public function createOauthCredentials($user_id,$first_name,$last_name)
    {
        $query = $this->db->get_where('oauth_clients',array('user_id' => $user_id));
        $result = $query->result_array();
        $key = bin2hex(openssl_random_pseudo_bytes(10));
        if(empty($result))
        {
            $data = array(
                'user_id' => $user_id,
                'secret' => $key,
                'name' => $first_name.' '.$last_name,
                'created_at' => currentDate()
            );
            $this->db->insert('oauth_clients', $data);
            $client_id = $this->db->insert_id();
            return array('client_id' => $client_id, 'client_secret' => $key);
        }
        else
        {
            return array('client_id' => $result[0]['id'], 'client_secret' => $result[0]['secret']);
        }
    }

    public function getTokenDetails($access_token,$user_id)
    {
        /*$query = $this->db->query('select * from oauth_access_tokens oct
                                            left join oauth_sessions os on oct.session_id=os.id
                                            left join oauth_clients oc on oc.id=os.client_id
                                            where oct.access_token="'.$access_token.'" and oc.user_id="'.$user_id.'"');*/
        $this->db->select('*');
        $this->db->from('oauth_access_tokens oct');
        $this->db->join('oauth_sessions os','oct.session_id=os.id','left');
        $this->db->join('oauth_clients oc','oc.id=os.client_id','left');
        $this->db->where('oct.access_token',$access_token);
        $this->db->where('oc.user_id',$user_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getSession($data)
    {
        $this->db->select('oc.name,os.*');
        $this->db->from('oauth_sessions os');
        $this->db->join('oauth_clients oc','oc.id=os.client_id','left');
        $this->db->where('oc.user_id',$data['user_id']);
        if(isset($data['offset']) && $data['offset']!='' && isset($data['limit']) && $data['limit']!='')
            $this->db->limit($data['limit'],$data['offset']);
        $this->db->order_by('os.id','DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getTotalSession($data)
    {
        $this->db->select('*');
        $this->db->from('oauth_sessions os');
        $this->db->join('oauth_clients oc','oc.id=os.client_id','left');
        $this->db->where('oc.user_id',$data['user_id']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function encode($value)
    {
        return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->key), $value, MCRYPT_MODE_CBC, md5(md5($this->key)))),'+/=', '-_,');
    }
    public function decode($value)
    {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->key), base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_CBC, md5(md5($this->key))), "\0");
    }


    public function activeAccount($code)
    {
        $query = $this->db->get_where('user',array('id_user' => $this->decode($code)));
        $data = $query->row();
        if(empty($data)){ return 0; }
        else{
            $update = array('user_status' => '1');
            $this->db->where('id_user', $this->decode($code));
            $this->db->update('user', $update);
            return 1;
        }
    }

    public function login($data)
    {
        $this->db->select('ur.user_role_name,p.provider_name provider_name,u.contribution_type,bu.bu_name,u.customer_id,u.user_role_id,u.id_user,u.profile_image,u.first_name,u.last_name,u.email,u.user_status,u.is_blocked,date_format(u.last_password_attempt_date,"%Y-%m-%d") as last_password_attempt_date,ur.access,u.display_rec_count,l.language_iso_code,u.language_id');
        $this->db->from('user u');        
        $this->db->join('provider p','u.provider = p.id_provider','left');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');        
        $this->db->join('business_unit_user buu','u.id_user = buu.user_id','left');
        $this->db->join('business_unit bu','bu.id_business_unit = buu.business_unit_id','left');
        $this->db->join('language l','l.id_language = u.language_id','left');
        $this->db->where(array('u.email' => $data['email_id'], 'u.password' => md5($data['password'])));
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        //'u.user_status' => 1
        return $query->row();
    }
    public function ldap_login($data)
    {
        $this->db->select('ur.user_role_name,u.customer_id,u.user_role_id,u.id_user,u.profile_image,u.first_name,u.last_name,u.email,u.user_status,u.is_blocked,date_format(u.last_password_attempt_date,"%Y-%m-%d") as last_password_attempt_date,ur.access,l.language_iso_code,u.language_id');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('language l','l.id_language = u.language_id','left');
        $this->db->where(array('u.email' => $data['email_id']));
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        //'u.user_status' => 1
        return $query->row();
    }

    public function saml_login($data)
    {
        $this->db->select('ur.user_role_name,u.customer_id,u.user_role_id,u.id_user,u.profile_image,u.first_name,u.last_name,u.email,u.user_status,u.is_blocked,date_format(u.last_password_attempt_date,"%Y-%m-%d") as last_password_attempt_date,ur.access,l.language_iso_code,u.language_id');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('language l','l.id_language = u.language_id','left');
        $this->db->where(array('u.email' => $data['email_id']));
        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        //'u.user_status' => 1
        return $query->row();
    }

    public function updateUser($data,$id)
    {
        $this->db->where('id_user', $id);
        $this->db->update('user', $data);
        return 1;
    }

    public function passwordExist($data)
    {
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('id_user',$data['user_id']);
        $this->db->where('password',md5($data['oldpassword']));
        $query = $this->db->get();
        return $query->row();
    }

    public function check_email($data)
    {
        $this->db->select('u.*,date_format(u.last_password_attempt_date,"%Y-%m-%d") as last_password_attempt_date');
        $this->db->from('user u');
        if(isset($data['id']) && $data['id']!=0 && $data['id']!='')
            $this->db->where('u.id_user!=',$data['id']);
        if(isset($data['contribution_type_not_equal_to']) && $data['contribution_type_not_equal_to']!='')
            $this->db->where('u.contribution_type!=',$data['contribution_type_not_equal_to']);    
        $this->db->where('u.email',addslashes($data['email']));
        $this->db->where('u.is_deleted',0);
        $query = $this->db->get();
        return $query->row();
    }

    public function changePassword($data)
    {
        $update = array('password' => md5($data['password']));
        $this->db->where('id_user', $data['user_id']);
        $this->db->update('user', $update);
        return 1;
    }

    public function updatePassword($password,$id)
    {
        $update = array('password' => md5($password));
        $this->db->where('id_user', $id);
        $this->db->update('user', $update);
        return 1;
    }

    public function getUsersList($data)
    {
        $query = $this->db->get_where('user',array('user_role_id'=>$data['type']));
        return $query->result_array();
    }

    public function getUserRole($data)
    {
        $query = $this->db->get_where('user_role',array('id_user_role'=>$data['user_role_id']));
        return $query->result_array();
    }

    public function getUserInfo($data)
    {
        $this->db->select('u.id_user,p.provider_name provider_name,u.provider,u.contribution_type,bu.bu_name,ur.user_role_name,u.user_role_id,u.first_name,u.last_name,u.profile_image,u.email,u.gender,u.user_status,u.customer_id,ur.access,u.is_allow_all_bu,u.display_rec_count,u.office_phone,u.secondary_phone,u.fax_number,u.address,u.postal_code,u.city,u.country_id,c.country_name,u.other_gender_value,u.content_administator_relation,u.content_administator_review_templates,u.content_administator_task_templates,u.content_administator_currencies,u.legal_and_content_administator,u.content_administator_catalogue,l.language_iso_code,u.language_id,u.link,u.notes,u.function');
        $this->db->from('user u');
        $this->db->join('user_role ur','u.user_role_id=ur.id_user_role and ur.role_status=1','left');
        $this->db->join('business_unit_user buu','u.id_user = buu.user_id','left');
        $this->db->join('provider p','u.provider = p.id_provider','left');
        $this->db->join('business_unit bu','bu.id_business_unit = buu.business_unit_id','left');
        $this->db->join('country c','c.id_country = u.country_id','left');
        $this->db->join('language l','l.id_language = u.language_id','left');
        if(isset($data['user_role_id']))
            $this->db->where('u.user_role_id',$data['user_role_id']);
        if(isset($data['customer_id']))
            $this->db->where('u.customer_id',$data['customer_id']);
        if(isset($data['user_role_id_not']))
            $this->db->where_not_in('u.user_role_id',$data['user_role_id_not']);
        if(isset($data['user_id']))
            $this->db->where('u.id_user',$data['user_id']);
        if(isset($data['user_status']))
            $this->db->where('u.user_status',$data['user_status']);
        $query = $this->db->get();
        return $query->row();
    }

    public function createUser($data)
    {
        $this->db->insert('user', $data);
        return $this->db->insert_id();
    }

    public function addLoginAttempts($data)
    {
        $this->db->insert('invalid_login_attempts', $data);
        return $this->db->insert_id();
    }

    public function updateOauthAccessToken($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('oauth_access_tokens', $data);
        $query = $this->db->get_where('oauth_access_tokens', array('id' => $data['id']));
        $accesstoken_details = $query->row();

        $data=array('updated_at'=>currentDate());
        $this->db->where('id', $accesstoken_details->session_id);
        $this->db->update('oauth_sessions', $data);
        return 1;
    }

    public function updateAccessToken($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('oauth_access_tokens', $data);
        return 1;
    }

    public function check_record($table,$where){
        $this->db->select('*');
        $this->db->from($table);
        if(isset($where))
            $this->db->where($where);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }

    public function custom_query($q){
        $query = $this->db->query($q);//echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    }
    public function custom_query_new($q){
        $query = $this->db->query($q);
    }
    public function custom_update_query($q){
        $query = $this->db->query($q);//echo '<pre>'.$this->db->last_query();
        //return $query->result_array();
    }
    public function batch_update($table,$data,$key){
        $this->db->update_batch($table, $data, $key);
        return 1;
    }
    
    public function check_record_selected($selected,$table,$where){
        $this->db->select($selected!=''?$selected:'*');
        $this->db->from($table);
        if(isset($where))
            $this->db->where($where);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    }

    public function check_record_adv($table,$where,$where_not){
        $where_not_sql = '';
        $this->db->select('*');
        $this->db->from($table);
        if(isset($where))
            $this->db->where($where);
        if(isset($where_not)){
            foreach($where_not as $k => $v)
                $where_not_sql .= $k.' != '.$v.' AND ';
            $this->db->where(substr($where_not_sql,0,-4));
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insert_data($table,$data){
        $this->db->insert($table, $data);//echo $this->db->last_query();exit;
        return $this->db->insert_id();
    }

    public function batch_insert($table,$data){
        $this->db->insert_batch($table,$data);
        return 1;
    }

    public function update_data($table,$data,$where){
        $this->db->where($where);
        $this->db->update($table, $data);
        return 1;
    }

    public function menu($data)
    {
        $modulename = 'm.module_name';
        if(isset($data['language_iso_code']) && !empty($data['language_iso_code'])){
            switch ($data['language_iso_code']) {
                case 'en':
                    $modulename = 'm.module_name';
                    break;
                case 'nl':
                    $modulename = 'm.module_name_nl';
                    break;
                case 'es':
                    $modulename = 'm.module_name_es';
                    break;    
                case 'de':
                    $modulename = 'm.module_name_de';
                    break;
                default:
                    $modulename = 'm.module_name';
                    break;
            }
        }
        $select_fields = "m.id_app_module,$modulename as module_name,m.module_icon,m.module_url,m.module_order";

        if($data['user_role_id'] == 2)//only customer admin needs key is_admin_menu
            $select_fields = "m.id_app_module,m.is_admin_menu,$modulename as module_name,m.module_icon,m.module_url,m.module_order";
        
        $menu = $this->getMenu(array('select'=>$select_fields,'user_role_id' => $data['user_role_id'],'menu_type'=>1,'parent_module_id'=>0));
        
        foreach($menu as $k => $v){
            $menu[$k]['sub_menu'] = $this->getMenu(array('select'=>$select_fields,'user_role_id' => $data['user_role_id'],'menu_type'=>2,'parent_module_id'=>$v['id_app_module']));
        }

        //dynamic user access for owner and delegate roles (from sprint 8.6)
        //print_r($data);
        if(isset($data['user_id']) && (isset($data['user_role_id']) && ($data['user_role_id'] == 3 || $data['user_role_id'] == 4)))
        {
            $moduleKey = array();
            $userDetails = $this->check_record('user',array('id_user' => $data['user_id']));
            if($userDetails[0]['content_administator_review_templates'] == 1)
            {
                array_push($moduleKey , 'manage_review');
            }
            if($userDetails[0]['content_administator_task_templates'] == 1)
            {
                array_push($moduleKey , 'manage-workflows');
            }
            if($userDetails[0]['content_administator_currencies'] == 1)
            {
                array_push($moduleKey , 'currency');
            }
            if($userDetails[0]['legal_and_content_administator'] == 1)
            {
                array_push($moduleKey , 'customer-contract-builder');
            }
            // if($userDetails[0]['content_administator_catalogue'] == 1)
            // {
            //     array_push($moduleKey , 'catalogue');
            // }
            if(!empty($moduleKey))
            {
                $this->db->select($select_fields);
                $this->db->from('app_module m');
                $this->db->where_in('module_key',$moduleKey);
                $this->db->where('m.parent_module_id',0);
                $query = $this->db->get();
                $dynamicMenu = $query->result_array();
                if(!empty($dynamicMenu))
                {
                    foreach($dynamicMenu as $dynamicMenuDetails)
                    {
                        $dynamicMenuDetails['sub_menu'] = $this->check_record_selected($select_fields,'app_module m',array('m.parent_module_id'=>$dynamicMenuDetails['id_app_module']));
                        array_push($menu,$dynamicMenuDetails);
                    }
                }

            }
           
        }
       //echo $this->db->last_query();
        $menu_array = array();

        /*for ($s = 0; $s < count($menu); $s++) {
            if ($menu[$s]['sub_module'] == 1) {
                if (!isset($menu_array[$menu[$s]['id_app_module']]))
                    $menu_array[$menu[$s]['id_app_module']] = array(
                        'module_name' => $menu[$s]['module_name'],
                        'module_icon' => $menu[$s]['module_icon'],
                        'module_url' => $menu[$s]['module_url']
                    );
                $menu_array[$menu[$s]['id_app_module']]['childs'][] = array(
                    'child_name' => $menu[$s]['child_label'],
                    'child_icon' => $menu[$s]['child_icon'],
                    'url' => $menu[$s]['child_module_url']
                );
            }
            else
            {
                $menu_array[$menu[$s]['id_app_module']] = array(
                    'module_name' => $menu[$s]['module_name'],
                    'module_icon' => $menu[$s]['module_icon'],
                    'module_url' => $menu[$s]['module_url'],
                    'childs' => array()
                );
            }
        }*/
        //echo "<pre>"; print_r($menu_array); exit;
        //$menu = array_values($menu_array);
        // $menu = array_values($menu);
        // print_r($menu);exit;
        if(!empty($menu))
        {
            $keys = array_column($menu, 'module_order');
            array_multisort($keys, SORT_ASC, $menu);
        }
       return $menu;
    }

    public function getModules($data)
    {
        $this->db->select('am.*,ama.*,amac.user_role_id,amac.app_module_access_status');
        $this->db->from('app_module am');
        $this->db->join('app_module_action ama','am.id_app_module=ama.app_module_id','left');
        $this->db->join('app_module_access amac','ama.id_app_module_action=amac.app_module_action_id and amac.user_role_id = '.$this->db->escape($data["user_role_id"]).'','left');
        if(isset($data['module_url']))
            $this->db->where('am.module_url',$data['module_url']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getMenu($data)
    {
        /*$query = $this->db->query('select m.*,m1.module_name as child_label,m1.module_icon as child_icon, m1.module_name as child_module_name,m1.module_key as child_module_key,m1.parent_module_id as child_parent_module_id,m1.module_url as child_module_url
                          from `app_module` m
                          LEFT JOIN `app_module` m1 on m.id_app_module=m1.parent_module_id
                          where m.is_menu=1 and (m.id_app_module or m1.id_app_module in
	                       (select DISTINCT(ma.app_module_id) from app_module_action ma
		                                        LEFT JOIN app_module_access mc on ma.id_app_module_action=mc.app_module_action_id
		                                        where mc.user_role_id='.$data['user_role_id'].' and mc.app_module_access_status=1))

		                  GROUP BY m.id_app_module ORDER BY m.module_order ASC,m1.module_order ASC',FALSE);*/
        $query = $this->db->query('SELECT '.$data['select'].' FROM `app_module` m
	                               LEFT JOIN app_module_action mc on m.id_app_module=mc.app_module_id
                                   LEFT JOIN app_module_access mac on mc.id_app_module_action=mac.app_module_action_id
                                   WHERE is_menu='.$data['menu_type'].' and mac.user_role_id='.$this->db->escape($data['user_role_id']).' and parent_module_id = '.$data['parent_module_id'].'
                                   GROUP BY m.id_app_module ORDER BY m.module_order');//echo $this->db->last_query();exit;
        return $query->result_array();

    }

    public function addUserLog($data)
    {
        $this->db->insert('user_log', $data);
        return 1;
    }

    public function addAccessLog($data)
    {
        $this->db->insert('access_log', $data);
        return 1;
    }

    public function getUserCount($data){
        $this->db->select('count(*) as count');
        $this->db->from('user');
        $this->db->where('customer_id',$data['customer_id']);
        if(isset($data['role']) && $data['role']==3)
            $this->db->where('user_role_id',$data['role']);
        elseif(isset($data['role']) && $data['role']==4)
            $this->db->where('user_role_id',$data['role']);
        elseif(isset($data['role']) && $data['role']==5)
            $this->db->where('user_role_id',$data['role']);
        elseif(isset($data['role']) && $data['role']==6)
            $this->db->where('user_role_id',$data['role']);
        $result = $this->db->get();
        return $result->row_array();

    }

    public function getActionList($data){
        /*if(isset($data['search']))
            $data['search']=$this->db->escape($data['search']);*/
        $this->db->select('al.*,CONCAT(u.first_name," ",u.last_name) user_name,CONCAT(u1.first_name," ",u1.last_name) acting_user_name');
        $this->db->from('access_log al');
        $this->db->join('user u','u.id_user = al.user_id','left');
        $this->db->join('user u1','u1.id_user = al.acting_user_id','left');
        $this->db->where('al.access_token',$data['access_token']);
        if(isset($data['search'])){
            $this->db->group_start();
            $this->db->like('al.name', $data['search'], 'both');
            $this->db->or_like('al.module_type', $data['search'], 'both');
            $this->db->or_like('al.action_name', $data['search'], 'both');
            $this->db->or_like('al.action_description', $data['search'], 'both');
            $this->db->or_like('al.action_url', $data['search'], 'both');
            $this->db->group_end();
        }
        /*if(isset($data['search']))
            $this->db->where('(al.name like "%'.$data['search'].'%"
            or al.module_type like "%'.$data['search'].'%"
            or al.action_name like "%'.$data['search'].'%"
            or al.action_description like "%'.$data['search'].'%"
            or al.action_url like "%'.$data['search'].'%")');*/
        $all_records_db = clone $this->db;
        $all_records_count = $all_records_db->get()->num_rows();


        if(isset($data['pagination']['number']) && $data['pagination']['number']!='')
            $this->db->limit($data['pagination']['number'],$data['pagination']['start']);
        if(isset($data['sort']['predicate']) && $data['sort']['predicate']!='' && isset($data['sort']['reverse']))
            $this->db->order_by($data['sort']['predicate'],$data['sort']['reverse']);
        else
            $this->db->order_by('al.id_access_log','DESC');
        $result = $this->db->get();

        return array('total_records'=>$all_records_count,'data'=>$result->result_array());
    }
    public function getLoggedUserId()
    {
        $this->db->select('IF(child_user_id IS NULL,parent_user_id,child_user_id) as id,child_user_id,parent_user_id');
        $this->db->from('user_login u');
        $this->db->where('access_token', str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION']));
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getUserLogin($data)
    {
        $this->db->select('*');
        $this->db->from('user_login');
        $this->db->where('access_token', $data['access_token']);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function addUserLogin($data)
    {
        $this->db->insert('user_login', $data);
        return 1;
    }
    public function updateUserLogin($data)
    {
        $this->db->where('access_token', $data['access_token']);
        $this->db->update('user_login', $data);
        return 1;
    }
    public function getPreviousUserSessions($data)
    {
        $this->db->select('*,oat.id as access_token_id');
        $this->db->from('oauth_clients oc');
        $this->db->join('oauth_sessions os','oc.id=os.client_id','left');
        $this->db->join('oauth_access_tokens oat','os.id=session_id','left');
        $this->db->where('oc.user_id',$data['user_id']);
        if(isset($data['timestamp']))
            $this->db->where('oat.expire_time>',$data['timestamp']);
        if(isset($data['access_token']))
            $this->db->where('oat.access_token',$data['access_token']);
        if(isset($data['user_id']))
            $this->db->where('oc.user_id',$data['user_id']);
        if(isset($data['expired_null']))
            $this->db->where('expired_date_time',null);

        $query=$this->db->get();
        return $query->result_array();
    }
    public function custom_query_insert_update($q){
        $query = $this->db->query($q);
        return array('affected_rows'=>$this->db->affected_rows(),'last_inserted_id'=>$this->db->insert_id());
    }

    public function getcontractsBybuid($data=null){
        $this->db->select('c.id_contract,c.contract_name,bu.customer_id');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','c.business_unit_id=bu.id_business_unit','left');
        if(!empty($data['customer_id'])){
            $this->db->where('bu.customer_id',$data['customer_id']);
        }
        if(!empty($data['contract_unique_id'])){
            $this->db->where('c.contract_unique_id',$data['contract_unique_id']);
        }
        $this->db->where('c.type','contract');

        $this->db->order_by('id_contract','asc');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getProjectsBybuid($data=null){
        $this->db->select('c.id_contract,c.contract_name,bu.customer_id');
        $this->db->from('contract c');
        $this->db->join('business_unit bu','c.business_unit_id=bu.id_business_unit','left');
        if(!empty($data['customer_id'])){
            $this->db->where('bu.customer_id',$data['customer_id']);
        }
        if(!empty($data['project_unique_id'])){
            $this->db->where('c.contract_unique_id',$data['project_unique_id']);
        }
        $this->db->where('c.type','project');

        $this->db->order_by('id_contract','asc');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function check_record_order($table,$where,$coloum,$order){
        $this->db->select('*');
        $this->db->from($table);
        if(isset($where))
            $this->db->where($where);
        if(isset($coloum)&&isset($order))
            $this->db->order_by($coloum,$order);    
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();
        return $query->result_array();
    }
    public function getCurrencyDetails($data=null){
        $this->db->select('IFNULL((SELECT euro_equivalent_value  FROM currency  WHERE currency_name=cu.currency_name AND customer_id=bu.customer_id AND  is_deleted=0), 0) as euro_equivalent_value,cu.currency_name,IFNULL((SELECT id_currency  FROM currency  WHERE currency_name=cu.currency_name AND customer_id=bu.customer_id AND is_deleted=0), NULL) as currency_id');
        $this->db->from("contract c"); 
        $this->db->join('currency cu','c.currency_id=cu.id_currency','left');
        $this->db->join('business_unit bu','bu.id_business_unit=c.business_unit_id','left');
        $this->db->where('id_contract', $data["contract_id"]);    
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    } 
    public function getRoles($data=null){
        $this->db->select('*');
        $this->db->from('user_role');
        $this->db->where_in('id_user_role',$data['user_role_ids']);
        $this->db->where('role_status',1);
        $query = $this->db->get();    
        return $query->result_array();
    }
    public function check_record_whereIn($table,$wherecoloum,$whereData,$selected)
    {
      //  echo "sdff";exit;
        $this->db->select($selected!=''?$selected:'*');
        $this->db->from($table);
        if(isset($wherecoloum))
        {
            $this->db->where_in($wherecoloum,$whereData);
        }
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();
    }
    public function delete($table,$where)
    {
        $this->db->where($where);
        $query =$this->db->delete($table);
        return $query;
    }
    public function getFilter($data)
    {
        $this->db->select("CONCAT( IFNULL(uaf.table_alias, ''), if(ISNULL(uaf.table_alias),'','.'), uaf.database_field) as database_field,uaf.condition,uaf.value,uaf.field_type,uaf.table_alias,md.domain,uaf.master_domain_field_id");
        $this->db->from('user_advanced_filters uaf');
        $this->db->join('master_domain md','md.id_master_domain=uaf.master_domain_id','left');
        if(isset($data['status']))
        {
            $this->db->where('uaf.status',$data['status']);
        }
        if(isset($data['user_id']))
        {
            $this->db->where('uaf.user_id',$data['user_id']);
        }
        if(isset($data['module']))
        {
            $this->db->where('uaf.module',$data['module']);
        }
        if(isset($data['is_union_table']))
        {
            $this->db->where('uaf.is_union_table',$data['is_union_table']);
        }
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getCustomerLanguage($data)
    {
        $this->db->select("l.language_iso_code , l.id_language ,l.language_name");
        $this->db->from('customer_languages cl');
        $this->db->join('language l','l.id_language=cl.language_id','left');
        $this->db->where('cl.customer_id',$data['customer_id']);
        if(isset($data['is_primary']))
        {
            $this->db->where('cl.is_primary',$data['is_primary']); 
        }
        if(isset($data['status']))
        {
            $this->db->where('cl.status',$data['status']);  
        }
        else
        {
            $this->db->where('cl.status',1); 
        }
        $query = $this->db->get();
        return $query->result_array();


    }

    public function samlLogVerify($data)
    {
        $currentDateTime = currentDate();
        $this->db->select("sl.*");
        $this->db->from('saml_log sl');
        $this->db->where('sl.email',$data['email_id']);
        $this->db->where('sl.status',1);
        $this->db->where('sl.expire >',$currentDateTime);
        $this->db->where('sl.uuid',$data['token']);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function check_record_selected_join($selected,$table,$where,$join,$joinCondition)
    {
        $this->db->select($selected!=''?$selected:'*');
        $this->db->from($table);
        $this->db->join($join,$joinCondition,'left');
        if(isset($where))
            $this->db->where($where);
        $query = $this->db->get();//echo '<pre>'.$this->db->last_query();exit;
        return $query->result_array();

    }

}
