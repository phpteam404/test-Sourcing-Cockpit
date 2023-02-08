<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Dashboard extends REST_Controller
{
    public $user_id = 0;
    public $session_user_id = null;
    public $session_user_info = null;
    public $session_user_business_units = null;
    public $session_user_business_units_user = null;
    public $session_user_contracts = null;
    public $session_user_contract_reviews = null;
    public $session_user_contract_documents = null;
    public $session_user_contract_action_items = null;
    public $session_user_delegates = null;
    public $session_user_contributors = null;
    public $session_user_reporting_owners = null;
    public $session_user_bu_owners = null;
    public $session_user_customer_admins = null;
    public $session_user_customer_all_users = null;
    public $session_user_customer_relationship_categories = null;
    public $session_user_customer_relationship_classifications = null;
    public $session_user_customer_calenders = null;
    public $session_user_master_currency = null;
    public $session_user_master_language = null;
    public $session_user_master_countries = null;
    public $session_user_master_templates = null;
    public $session_user_master_customers = null;
    public $session_user_master_users = null;
    public $session_user_master_user_roles = null;
    public $session_user_contract_review_modules = null;
    public $session_user_contract_review_topics = null;
    public $session_user_contract_review_questions = null;
    public $session_user_contract_review_question_options = null;
    public $session_user_wadmin_relationship_categories = null;
    public $session_user_wadmin_relationship_classifications = null;
    public $session_user_wadmin_email_templates = null;
    public $session_user_customer_email_templates = null;
    public $session_user_customer_providers = null;
    public $session_user_own_business_units = null;
    public $session_user_review_business_units = null;
    public function __construct()
    {
        parent::__construct();
        if (isset($_SERVER['HTTP_USER'])) {
            $this->user_id = pk_decrypt($_SERVER['HTTP_USER']);
        }
        $this->load->model('Validation_model');
        $this->load->model('Download_model');
        $this->load->model('Project_model');
        $getLoggedUserId = $this->User_model->getLoggedUserId();
        //$this->User_model->check_record('calender',array('is_workflow'=>0,'auto_initiate'=>1,'month(date)'=>date('m'),'year(date)'=>date('Y')));
        //echo '<pre>'.
        //$this->session_user_id=!empty($this->session->userdata('session_user_id_acting'))?($this->session->userdata('session_user_id_acting')):($this->session->userdata('session_user_id'));
        $_SERVER['HTTP_LOGGEDIN_USER'] = $this->session_user_id =
            $getLoggedUserId[0]['id'];
        $this->session_user_info = $this->User_model->getUserInfo([
            'user_id' => $this->session_user_id,
        ]);
        if (
            $this->session_user_info->user_role_id < 3 ||
            $this->session_user_info->user_role_id == 5
        ) {
            $this->session_user_business_units = $this->Validation_model->getBusinessUnitList(
                ['customer_id' => $this->session_user_info->customer_id]
            );
        } elseif (
            $this->session_user_info->user_role_id == 3 ||
            $this->session_user_info->user_role_id == 4 ||
            $this->session_user_info->user_role_id == 8
        ) {
            $this->session_user_business_units = $this->Validation_model->getBusinessUnitListByUser(
                ['user_id' => $this->session_user_info->id_user]
            );
        } elseif ($this->session_user_info->user_role_id == 6) {
            if ($this->session_user_info->is_allow_all_bu == 1) {
                $this->session_user_business_units = $this->Validation_model->getBusinessUnitList(
                    ['customer_id' => $this->session_user_info->customer_id]
                );
            } else {
                $this->session_user_business_units = $this->Validation_model->getBusinessUnitListByUser(
                    ['user_id' => $this->session_user_info->id_user]
                );
            }
        }
        $this->session_user_own_business_units =
            $this->session_user_business_units;
        $this->session_user_review_business_units = $this->Validation_model->getReviewBusinessUnits(
            ['id_user' => $this->session_user_id]
        );
        if ($this->session_user_info->user_role_id != 7) {
            $this->session_user_business_units = array_merge(
                $this->session_user_business_units,
                $this->session_user_review_business_units
            );
        }
        if ($this->session_user_info->user_role_id == 5) {
            $this->session_user_contracts = $this->Validation_model->getContributorContract(
                [
                    'business_unit_id' => $this->session_user_business_units,
                    'customer_user' => $this->session_user_info->id_user,
                ]
            );
        } else {
            $this->session_user_contracts = $this->Validation_model->getContracts(
                ['business_unit_id' => $this->session_user_business_units]
            );
        }
        //$this->session_user_contracts=$this->Validation_model->getContracts(array('business_unit_id'=>$this->session_user_business_units_user));
        // $this->session_user_delegates=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>4));
        // $this->session_user_contributors=$this->Validation_model->getCustomerUsers(array('customer_id'=>array($this->session_user_info->customer_id),'user_role_id'=>5));
        $this->session_user_customer_all_users = $this->Validation_model->getCustomerUsers(
            ['customer_id' => [$this->session_user_info->customer_id]]
        );
        // $this->session_user_customer_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_customer_calenders=$this->Validation_model->getCustomerCalenders(array('customer_id'=>array($this->session_user_info->customer_id)));
        // $this->session_user_master_countries=$this->Validation_model->getCountries();
        // $this->session_user_master_templates=$this->Validation_model->getTemplates();
        $this->session_user_master_customers = $this->Validation_model->getCustomers();
        $this->session_user_master_users = $this->Validation_model->getUsers();
        // $this->session_user_master_user_roles=$this->Validation_model->getUserRoles();

        //echo '$this->session_user_id'.$this->session_user_id;
        // $this->session_user_wadmin_relationship_categories=$this->Validation_model->getCustomerRelationshipCategories(array('customer_id'=>array(0)));
    }
    /* dashboard tabs counts data api start */
    public function counts_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '1',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '2',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '3',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        //   print_r($data);exit;
        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array($data['delegate_id'], $this->session_user_delegates)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }

        $result_array = [];
        $data['can_access'] = 1;
        $data['get_all_records'] = true;
        $data['can_review'] = 1;
        $all_activities = $this->Contract_model->dashboardActivityCount($data);
        unset($data['can_review']);
        $result_array['all_activities_count'] =
            $all_activities[0]['dashboardActivityCount'];
        $provider_query =
            'SELECT count(*)  count FROM provider p WHERE p.customer_id = ' .
            $data['customer_id'] .
            ' AND p.status != 2 AND p.status = 1';
        [$provider_count] = $this->User_model->custom_query($provider_query);
        $result_array['all_relations_count'] = $provider_count['count'];
        if (isset($data['user_role_id']) && isset($data['id_user'])) {
            if ($data['user_role_id'] == 2) {
            } elseif (
                $data['user_role_id'] == 3 ||
                $data['user_role_id'] == 8
            ) {
                $data['business_unit_id'] = $this->User_model->check_record(
                    'business_unit_user',
                    [
                        'user_id' => $this->session_user_id,
                        'status' => 1,
                    ]
                );
                $data['business_unit_id'] = array_map(function ($i) {
                    return $i['business_unit_id'];
                }, $data['business_unit_id']);
                $contributor_modules = $this->User_model->check_record(
                    'contract_user',
                    ['status' => 1, 'user_id' => $this->session_user_id]
                );
                // echo
                if (count($contributor_modules) > 0) {
                    $data['module_id'] = array_filter(
                        array_map(function ($i) {
                            return $i['module_id'];
                        }, $contributor_modules)
                    );
                }
            } elseif ($data['user_role_id'] == 4) {
                $data['delegate_id'] = $data['id_user'];
                $contributor_modules = $this->User_model->check_record(
                    'contract_user',
                    ['status' => 1, 'user_id' => $this->session_user_id]
                );
                if (count($contributor_modules) > 0) {
                    $data['module_id'] = array_filter(
                        array_map(function ($i) {
                            return $i['module_id'];
                        }, $contributor_modules)
                    );
                }
            } elseif ($data['user_role_id'] == 6) {
                $data['business_unit_id'] = $this->User_model->check_record(
                    'business_unit_user',
                    [
                        'user_id' => $this->session_user_id,
                        'status' => 1,
                    ]
                );
                $data['business_unit_id'] = array_map(function ($i) {
                    return $i['business_unit_id'];
                }, $data['business_unit_id']);
                if ($this->session_user_info->is_allow_all_bu == 1) {
                    $bu_ids = $this->User_model->check_record_selected(
                        'GROUP_CONCAT(id_business_unit) as bu_ids',
                        'business_unit',
                        [
                            'status' => 1,
                            'customer_id' =>
                                $this->session_user_info->customer_id,
                        ]
                    );
                    $data['business_unit_id'] = explode(
                        ',',
                        $bu_ids[0]['bu_ids']
                    );
                }
            }
        }
        $data['contract_review_action_item_status'] = 'open';
        $data['item_status'] = 1;
        $result_array[
            'action_items_count'
        ] = (int) $this->Contract_model->getActionItemsCount($data);
        $data['type'] = 'project';
        $data['project_status'] = 1;
        $projects = $this->Contract_model->getAllContractList($data);
        $result_array['all_projects_count'] = $projects['total_records'];

        unset($data['can_review']);
        $data['contract_active_status'] = 'Active';
        unset($data['type']);
        $all_contracts = $this->Contract_model->getAllContractList($data);
        $result_array['all_contracts_count'] = $all_contracts['total_records'];

        $data['user_role_not'] = [];
        if ($data['user_role_id'] == 1) {
            $data['user_role_not'] = [1];
        }
        if ($data['user_role_id'] == 2) {
            $data['user_role_not'] = [1, 2];
        }
        if ($data['user_role_id'] == 3) {
            $data['user_role_not'] = [1, 2, 3, 6];
        }
        if ($data['user_role_id'] == 4) {
            $data['user_role_not'] = [1, 2, 6];
        }
        if ($data['user_role_id'] == 5) {
            $data['user_role_not'] = [1, 2, 3, 4, 5, 6];
        }
        if ($data['user_role_id'] == 6) {
            $data['user_role_not'] = [1, 2];
        }
        if ($data['user_role_id'] == 7) {
            $data['user_role_not'] = [1, 2, 3, 4, 5, 6];
        }
        $user_list_array = [
            'user_role_not' => $data['user_role_not'],
            'business_unit_array' => $business_unit_array,
            'customer_id' => $data['customer_id'],
        ];
        $data['user_type'] == 'internal';
        if ($data['user_role_id'] == 6) {
            $user_list_array['buids'] = $data['business_unit_id'];
        }
        $user_list_result = $this->Customer_model->getCustomerUserList(
            $user_list_array
        );
        $result_array['co_workers_count'] = $user_list_result['total_records'];

        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }
    /* dashboard tabs counts data api end */

    /* dashboard all activities tab graph data api start */
    public function allactivitiesGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '1',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '2',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '3',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '4',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '5',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '6',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '9',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }

        $result_array = [];
        if (true) {
            //Pie Chart for All-Activites
            if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
                $business_unit = $this->Business_unit_model->getBusinessUnitUser(
                    ['user_id' => $this->session_user_id, 'status' => '1']
                );
                // echo
                $business_unit_id = array_map(function ($i) {
                    return $i['id_business_unit'];
                }, $business_unit);
            }
            if ($this->session_user_info->user_role_id == 6) {
                $data['business_unit_id'] = $this->session_user_business_units;
                if (
                    count($data['business_unit_id']) == 0 &&
                    $this->session_user_info->is_allow_all_bu == 0
                ) {
                    $data['business_unit_id'] = [0];
                }
            }
            $pie_input = [
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=1&status=pending review',
                    'label' => 'Reviews to Initiate',
                    'color' => 'e78200',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 1,
                        'contract_status' => 'pending review',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=1&status=review in progress',
                    'label' => 'Reviews in Progress',
                    'color' => 'e78200',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 1,
                        'contract_status' => 'review in progress',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=1&status=review finalized',
                    'label' => 'Reviews Finalized',
                    'color' => 'e78200',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 1,
                        'contract_status' => 'review finalized',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'parent_contract_id' => 0,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=1&status=new',
                    'label' => 'New Reviews',
                    'color' => 'e78200',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 1,
                        'contract_status' => 'new',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=2&status=new',
                    'label' => 'New Task',
                    'color' => '5bb166',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 2,
                        'contract_status' => 'new',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=2&status=pending workflow',
                    'label' => 'Tasks to Initiate',
                    'color' => '5bb166',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 2,
                        'contract_status' => 'pending workflow',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=2&status=workflow in progress',
                    'label' => 'Tasks in Progress',
                    'color' => '5bb166',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 2,
                        'contract_status' => 'workflow in progress',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
                [
                    'link' =>
                        WEB_BASE_URL .
                        '#/all-activities?activity_filter=2&status=workflow finalized',
                    'label' => 'Tasks Finalized',
                    'color' => '5bb166',
                    'filter' => [
                        'customer_id' => $this->session_user_info->customer_id,
                        'activity_filter' => 2,
                        'contract_status' => 'workflow finalized',
                        'business_unit_id' => $business_unit_id,
                        'get_all_records' => 1,
                        'can_access' => 1,
                    ],
                ],
            ];
            $all_reviews_count = $all_workflows_count = 0;
            foreach ($pie_input as $pk => $pv) {
                // print_r($pv['filter']['can_review']);exit;
                $pv['filter']['can_review'] = 1;
                $result_array['all_activity']['graph'][$pk]['label'] =
                    $pv['label'];
                $result_array['all_activity']['graph'][$pk]['color'] =
                    $pv['color'];
                //$query_result = $this->Contract_model->getContractList($pv['filter']);
                $query_result = $this->Contract_model->dashboardActivityCount(
                    $pv['filter']
                );
                // print_r($pv['filter']);
                // echo PHP_EOL.
                $count = $query_result[0]['dashboardActivityCount'];
                // $result_array['all_activity']['graph'][$pk]['value'] = $query_result['total_records'];
                $result_array['all_activity']['graph'][$pk]['value'] = $count;
                //counting revies, workflows
                if ($pv['filter']['activity_filter'] == 2) {
                    // $result_array['all_activity']['graph'][$pk]['name'] = "workflow";
                    //$all_workflows_count += $query_result['total_records'];
                    $all_workflows_count += $count;
                } else {
                    // $result_array['all_activity']['graph'][$pk]['name'] = "review";
                    //$all_reviews_count += $query_result['total_records'];
                    $all_reviews_count += $count;
                }
            }
            $result_array['all_activity']['counts'][
                'all_reviews_count'
            ] = $all_reviews_count;
            $result_array['all_activity']['counts'][
                'all_workflows_count'
            ] = $all_workflows_count;
            $result = [
                'status' => true,
                'message' => $this->lang->line('success'),
                'data' => $result_array,
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }
    }
    /* dashboard all activities tab graph data api end */

    /* dashboard all relations tab graph data api start */
    public function allrelationsGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '1',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '2',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '3',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '4',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '5',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '6',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '9',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }
        $main_currency = $this->User_model->check_record('currency', [
            'customer_id' => $data['customer_id'],
            'is_maincurrency' => 1,
        ]);
        $result_array = [];
        $providersdetails = $this->Customer_model->getproviderlist([
            'customer_id' => $data['customer_id'],
            'can_access' => 1,
        ]);
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
        $result_array['providers']['count']['total_spent'] = 0;
        $labels = $this->User_model->custom_query(
            'select tag_text from tag t LEFT JOIN tag_language tl on tl.tag_id = t.id_tag WHERE t.type="provider_tags" and t.is_fixed=1 and customer_id=' .
                $this->session_user_info->customer_id .
                ' ORDER BY label asc'
        );
        if (!empty($labels)) {
            $providerLables = array_column($labels, 'tag_text');
        } else {
            $providerLables = [
                'Risk Profile',
                'Approval Status',
                'Finacial Health',
            ];
        }

        foreach ($providers as $provider) {
            if ($provider['approval_status'] == 'G') {
                $approval_status_green++;
            }
            if ($provider['approval_status'] == 'R') {
                $approval_status_red++;
            }
            if ($provider['approval_status'] == 'A') {
                $approval_status_amber++;
            }
            if ($provider['approval_status'] == 'N/A') {
                $approval_status_na++;
            }
            if ($provider['risk_profile'] == 'G') {
                $risk_profile_green++;
            }
            if ($provider['risk_profile'] == 'R') {
                $risk_profile_red++;
            }
            if ($provider['risk_profile'] == 'A') {
                $risk_profile_amber++;
            }
            if ($provider['risk_profile'] == 'N/A') {
                $risk_profile_na++;
            }
            $total_amount_pr = 0;
            if (!empty($provider['contract_ids'])) {
                $contracts_ids = $this->Customer_model->getProviderContracts([
                    'customer_id' => $this->session_user_info->customer_id,
                    'provider_id' => $provider['id_provider'],
                ]);
                $contrat_ids = array_map(function ($i) {
                    return (int) $i['id_contract'];
                }, $contracts_ids);
                //$contrat_ids=explode(',',$provider['contract_ids']);
                if (!empty($contrat_ids)) {
                    $amount = $this->Customer_model->getProviderTotalSpent([
                        'contract_ids' => $contrat_ids,
                        'customer_id' => $data['customer_id'],
                    ]);
                    $exg_rate_pr = 1;
                    foreach ($amount as $at => $av) {
                        $exg_rate_pr = str_replace(
                            ',',
                            '.',
                            $av['euro_equivalent_value']
                        );
                        if (
                            $main_currency[0]['currency_name'] ==
                                $av['currency_name'] ||
                            $exg_rate_pr == 0
                        ) {
                            $exg_rate_pr = 1;
                        }
                        // else{
                        //     $exg_rate_pr=str_replace(',','.',$av['euro_equivalent_value']);
                        // }
                        $total_amount_pr +=
                            ($av['Additional_Reccuring_fees_value'] +
                                $av['ProjectedValue'] +
                                $av['additonal_one_off_fees']) *
                            $exg_rate_pr;
                    }
                }
            }
            //echo $total_amount_pr;echo "<br>";
            $result_array['providers']['count'][
                'total_spent'
            ] += $total_amount_pr;
        }
        $provider_approval_status_graph[0]['value'] = $approval_status_green;
        $provider_approval_status_graph[0]['label'] = $this->lang->line(
            'green'
        );
        $provider_approval_status_graph[0]['color'] = '36a921';
        $provider_approval_status_graph[0]['link'] =
            WEB_BASE_URL . '#/provider?approval_status=G';
        $provider_approval_status_graph[1]['value'] = $approval_status_amber;
        $provider_approval_status_graph[1]['label'] = $this->lang->line(
            'amber'
        );
        $provider_approval_status_graph[1]['color'] = 'ff9900';
        $provider_approval_status_graph[1]['link'] =
            WEB_BASE_URL . '#/provider?approval_status=A';
        $provider_approval_status_graph[2]['value'] = $approval_status_red;
        $provider_approval_status_graph[2]['label'] = $this->lang->line('red');
        $provider_approval_status_graph[2]['color'] = 'f20505';
        $provider_approval_status_graph[2]['link'] =
            WEB_BASE_URL . '#/provider?approval_status=R';
        $provider_approval_status_graph[3]['value'] = $approval_status_na;
        $provider_approval_status_graph[3]['label'] = $this->lang->line('n_a');
        $provider_approval_status_graph[3]['color'] = 'cccccc';
        $provider_approval_status_graph[3]['link'] =
            WEB_BASE_URL . '#/provider?approval_status=N/A';
        // $result_array['action_item']['graph'] =$provider_approval_status_graph;
        $result_array['providers'][
            'provider_approval_status_graph'
        ] = $provider_approval_status_graph;
        $provider_risk_profile_graph[0]['value'] = $risk_profile_green;
        $provider_risk_profile_graph[0]['label'] = $this->lang->line('green');
        $provider_risk_profile_graph[0]['color'] = '36a921';
        $provider_risk_profile_graph[0]['link'] =
            WEB_BASE_URL . '#/provider?risk_profile=G';
        $provider_risk_profile_graph[1]['value'] = $risk_profile_amber;
        $provider_risk_profile_graph[1]['label'] = $this->lang->line('amber');
        $provider_risk_profile_graph[1]['color'] = 'ff9900';
        $provider_risk_profile_graph[1]['link'] =
            WEB_BASE_URL . '#/provider?risk_profile=A';
        $provider_risk_profile_graph[2]['value'] = $risk_profile_red;
        $provider_risk_profile_graph[2]['label'] = $this->lang->line('red');
        $provider_risk_profile_graph[2]['color'] = 'f20505';
        $provider_risk_profile_graph[2]['link'] =
            WEB_BASE_URL . '#/provider?risk_profile=R';
        $provider_risk_profile_graph[3]['value'] = $risk_profile_na;
        $provider_risk_profile_graph[3]['label'] = $this->lang->line('n_a');
        $provider_risk_profile_graph[3]['color'] = 'cccccc';
        $provider_risk_profile_graph[3]['link'] =
            WEB_BASE_URL . '#/provider?risk_profile=N/A';
        $result_array['providers'][
            'provider_risk_profile_graph'
        ] = $provider_risk_profile_graph;

        $provider_finacial_health_graph[0]['value'] = $finacial_health_green;
        $provider_finacial_health_graph[0]['label'] = $this->lang->line(
            'green'
        );
        $provider_finacial_health_graph[0]['color'] = '36a921';
        $provider_finacial_health_graph[0]['link'] =
            WEB_BASE_URL . '#/provider?finacial_health=G';
        $provider_finacial_health_graph[1]['value'] = $finacial_health_amber;
        $provider_finacial_health_graph[1]['label'] = $this->lang->line(
            'amber'
        );
        $provider_finacial_health_graph[1]['color'] = 'ff9900';
        $provider_finacial_health_graph[1]['link'] =
            WEB_BASE_URL . '#/provider?finacial_health=A';
        $provider_finacial_health_graph[2]['value'] = $finacial_health_red;
        $provider_finacial_health_graph[2]['label'] = $this->lang->line('red');
        $provider_finacial_health_graph[2]['color'] = 'f20505';
        $provider_finacial_health_graph[2]['link'] =
            WEB_BASE_URL . '#/provider?finacial_health=R';
        $provider_finacial_health_graph[3]['value'] = $finacial_health_na;
        $provider_finacial_health_graph[3]['label'] = $this->lang->line('n_a');
        $provider_finacial_health_graph[3]['color'] = 'cccccc';
        $provider_finacial_health_graph[3]['link'] =
            WEB_BASE_URL . '#/provider?finacial_health=N/A';
        $result_array['providers'][
            'provider_finacial_health_graph'
        ] = $provider_finacial_health_graph;
        $result_array['provider_lables'] = $providerLables;

        // $result_array['action_item']['graph2'] =$provider_risk_profile_graph;
        // $result_array['action_item']['graph'] =$provider_risk_profile_graph;
        $result_array['providers_count'] = $providersdetails['total_records'];
        $result_array['providers']['count']['providers_count'] =
            $providersdetails['total_records'];
        $result_array['main_currency_name'] = $main_currency[0]['currency_name'];
        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }
    /* dashboard all relations tab graph data api end */

    /* dashboard all action items tab graph data api start */
    public function allactionItemsGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '1',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '2',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '3',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '4',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '5',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '6',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '9',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }
        $data['contract_review_action_item_status'] = 'open';
        $data['item_status'] = 1;
        //unset($data['responsible_user_id']);
        //$data['created_by'] = $loggedinuser;
        $result_array['action_item'] = [];
        if (true) {
            //Bar Graph Dashboard Start
            // print_r($data);exit;
            $data['priority'] = 'Urgent';
            // print_r($data);exit;
            $urgent_count = (int) $this->Contract_model->getActionItemsCount(
                $data
            ); //echo
            $data['priority'] = 'Medium';
            $medium_count = (int) $this->Contract_model->getActionItemsCount(
                $data
            );
            $data['priority'] = 'Low';
            $low_count = (int) $this->Contract_model->getActionItemsCount(
                $data
            );
            $data['priority'] = '';
            $NotClassified = (int) $this->Contract_model->getActionItemsCount(
                $data
            ); //echo '<pre>'.
            unset($data['priority']);
            $data['type'] = 'overdue';
            $overdue_count = (int) $this->Contract_model->getActionItemsCount(
                $data
            );
            unset($data['type']);
            $priority_count[0]['value'] = $low_count;
            $priority_count[0]['label'] = $this->lang->line('low');
            $priority_count[0]['color'] = '36a921';
            $priority_count[0]['link'] =
                WEB_BASE_URL . '#/action-items?priority=Low';
            $priority_count[1]['value'] = $medium_count;
            $priority_count[1]['label'] = $this->lang->line('medium');
            $priority_count[1]['color'] = 'ff9900';
            $priority_count[1]['link'] =
                WEB_BASE_URL . '#/action-items?priority=Medium';
            $priority_count[2]['value'] = $urgent_count;
            $priority_count[2]['label'] = $this->lang->line('urgent');
            $priority_count[2]['color'] = 'f20505';
            $priority_count[2]['link'] =
                WEB_BASE_URL . '#/action-items?priority=Urgent';
            $priority_count[3]['value'] = $NotClassified;
            $priority_count[3]['label'] = $this->lang->line('not_classified');
            $priority_count[3]['color'] = 'cccccc';
            $priority_count[3]['link'] =
                WEB_BASE_URL . '#/action-items?priority=Not-classified';

            $result_array['action_item']['counts'][
                'ovredue_count'
            ] = $overdue_count;
            //Bar Graph Dashboard End
            $result_array['action_items_count'] = $result_array['action_item']['counts']['action_items_count'] = (int)$this->Contract_model->getActionItemsCount($data);
            $result_array['action_item']['graph'] = $priority_count;
        }
        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }
    /* dashboard all action items tab graph data api end */

    /* dashboard all co-workers tab graph data api start */
    public function allcoworkersGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '1',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '2',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '3',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '4',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '5',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '6',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '7',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '9',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }

        $data['user_role_not'] = [];
        if ($data['user_role_id'] == 1) {
            $data['user_role_not'] = [1];
        }
        if ($data['user_role_id'] == 2) {
            $data['user_role_not'] = [1, 2];
        }
        if ($data['user_role_id'] == 3) {
            $data['user_role_not'] = [1, 2, 3, 6];
        }
        if ($data['user_role_id'] == 4) {
            $data['user_role_not'] = [1, 2, 6];
            //$data['user_contracts']=$this->session_user_contracts;
        }
        if ($data['user_role_id'] == 5) {
            $data['user_role_not'] = [1, 2, 3, 4, 5, 6];
        }
        if ($data['user_role_id'] == 6) {
            $data['user_role_not'] = [1, 2];
        }
        if ($data['user_role_id'] == 7) {
            $data['user_role_not'] = [1, 2, 3, 4, 5, 6];
        }
        // print_r($data);exit;
        $user_list_array = [
            'user_role_not' => $data['user_role_not'],
            // 'user_type' => 'internal',
            'business_unit_array' => $business_unit_array,
            'customer_id' => $data['customer_id'],
        ];
        $data['user_type'] == 'internal';
        if ($data['user_role_id'] == 6) {
            // print_r($data['business_unit_id']);exit;
            $user_list_array['buids'] = $data['business_unit_id'];
            // $user_list_array['user_type']='external';
        }
        $user_list_result = $this->Customer_model->getCustomerUserList(
            $user_list_array
        ); //echo
        // echo '<pre>'.print_r($user_list_result);exit;
        // $result_array['co_workers'] = $this->Customer_model->getUserCount(array('customer_id' => $data['customer_id'],'user_role_id_not' => $user_role_id_not,'business_unit_array'=>$data['business_unit_id'],'user_role_id'=>$data['user_role_id'],'user_contracts'=>$this->session_user_contracts));
        $result_array['co_workers'] = $user_list_result['total_records'];
        // if($data['user_role_id']==4){
        //    $result_array['co_workers'] = $this->Customer_model->getDelegateCoworkers(array('user_contracts'=>$this->session_user_contracts,'business_unit_array'=>$this->session_user_business_units));
        // }
        //echo '<pre>'.print_r($this->session_user_contracts);exit;
        if ($this->session_user_info->user_role_id == 3) {
            $session_user_contracts = $this->User_model->check_record(
                'contract_user',
                ['user_id' => $this->session_user_id, 'status' => 1]
            );
            foreach ($session_user_contracts as $v) {
                $data['contracts_array'][] = $v['contract_id'];
            }
        } elseif ($this->session_user_info->user_role_id == 4) {
            $session_user_contracts = $this->User_model->check_record(
                'contract_user',
                ['user_id' => $this->session_user_id, 'status' => 1]
            );
            foreach ($session_user_contracts as $v) {
                $data['contracts_array'][] = $v['contract_id'];
            }
            $delegate_contracts = $this->User_model->check_record('contract', [
                'delegate_id' => $this->session_user_id,
                'is_deleted' => 0,
            ]);
            foreach ($delegate_contracts as $v) {
                $data['contracts_array'][] = $v['id_contract'];
            }
        } else {
            $data['contracts_array'] = $this->session_user_contracts;
        }

        //$contributor_count = $this->Customer_model->getDelegateContributorsCount(array('user_contracts'=>$data['contracts_array'],'user_id'=>$this->session_user_id));
        //echo '<pre>'.
        //Contributor counts for delegate user
        if ($this->session_user_info->user_role_id == 3) {
            $session_user_contributing_contracts = $this->User_model->check_record(
                'contract_user',
                ['user_id' => $this->session_user_id, 'status' => 1]
            );
            $owner_contracts = $this->User_model->check_record('contract', [
                'contract_owner_id' => $this->session_user_id,
                'is_deleted' => 0,
            ]);
            foreach ($session_user_contributing_contracts as $v) {
                $data['contracts_array'][] = $v['contract_id'];
            }
            foreach ($owner_contracts as $v) {
                $data['contracts_array'][] = $v['id_contract'];
            }
        } elseif ($this->session_user_info->user_role_id == 4) {
            $session_user_contributing_contracts = $this->User_model->check_record(
                'contract_user',
                ['user_id' => $this->session_user_id, 'status' => 1]
            );
            $delegate_contracts = $this->User_model->check_record('contract', [
                'delegate_id' => $this->session_user_id,
                'is_deleted' => 0,
            ]);
            foreach ($session_user_contributing_contracts as $v) {
                $data['contracts_array'][] = $v['contract_id'];
            }
            foreach ($delegate_contracts as $v) {
                $data['contracts_array'][] = $v['id_contract'];
            }
        } else {
            $data['contracts_array'] = $this->session_user_contracts;
        }
        $data['user_id'] = $this->session_user_id;
        // print_r($data);exit;
        $contributors = $this->Contract_model->getDelegateContributors($data);
        //  echo '<pre>'.
        $result_array['contributors'] = (int) $contributors['total_records'];

        // echo '<pre>'.print_r($contributors);exit;
        if (true) {
            $result_array['co_workers_obj']['counts']['all_co_workers'] =
                $result_array['co_workers'];
            $result_array['co_workers_obj']['counts']['all_contributors'] =
                (int) $contributors['total_records'];
            $result_array['co_workers_obj']['top_contributions'] =
                $contributors['top_contributions'];
            $experts = $validators = $providers = 0;
            foreach ($contributors['data'] as $ck => $cv) {
                if ((int) $cv['contribution_type'] == 0) {
                    $experts++;
                }
                if ((int) $cv['contribution_type'] == 1) {
                    $validators++;
                }
                if ((int) $cv['contribution_type'] == 3) {
                    $providers++;
                }
            }
            // print_r($contributors['data']);exit;
            $result_array['co_workers_obj']['graph'][0][
                'label'
            ] = $this->lang->line('expert');
            $result_array['co_workers_obj']['graph'][0]['value'] = $experts;
            $result_array['co_workers_obj']['graph'][0]['link'] =
                $this->session_user_info->user_role_id == 6
                    ? ''
                    : WEB_BASE_URL . '#/contributors?contribution_type=0';
            $result_array['co_workers_obj']['graph'][0]['color'] = '4472c4';
            $result_array['co_workers_obj']['graph'][1][
                'label'
            ] = $this->lang->line('validator');
            $result_array['co_workers_obj']['graph'][1]['value'] = $validators;
            $result_array['co_workers_obj']['graph'][1]['link'] =
                $this->session_user_info->user_role_id == 6
                    ? ''
                    : WEB_BASE_URL . '#/contributors?contribution_type=1';
            $result_array['co_workers_obj']['graph'][1]['color'] = '4472c4';
            $result_array['co_workers_obj']['graph'][2][
                'label'
            ] = $this->lang->line('relation');
            $result_array['co_workers_obj']['graph'][2]['value'] = $providers;
            $result_array['co_workers_obj']['graph'][2]['link'] =
                $this->session_user_info->user_role_id == 6
                    ? ''
                    : WEB_BASE_URL . '#/contributors?contribution_type=3';
            $result_array['co_workers_obj']['graph'][2]['color'] = '4472c4';
        }
        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }
    /* dashboard all co-workers tab graph data api end */

    /* dashboard all contacts tab graph data api start */
    public function allcontractsGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }

        $result_array = [];

        $data['can_access'] = 1;
        $data['get_all_records'] = true;
        $result_array['end_date'] = [];
        $main_currency = $this->User_model->check_record('currency', [
            'customer_id' => $data['customer_id'],
            'is_maincurrency' => 1,
        ]);
        $data['end_date_lessthan_90'] = 90;
        $data['contract_active_status'] = 'Active';
        $result_array[
            'end_date_lessthan_90'
        ] = $this->Contract_model->getAllContractList($data)['total_records'];
        $result_array['end_date'] = [];
        if (true) {
            //End date Graph / Widget
            $result_array['end_date']['ending_in_90_days'] =
                $result_array['end_date_lessthan_90'];
            unset($data['end_date_lessthan_90']);
            $all_contracts = $this->Contract_model->getAllContractList($data); // echo '<pre>'.$this->db->last_query();exit;
            // $result_array['end_date']['contracts'] = $all_contracts['data'];
            $result_array['end_date']['all_contracts'] =
                $all_contracts['total_records'];
            $result_array['end_date']['created_this_month'] = 0;
            $result_array['end_date']['ending_this_month'] = 0;
            $result_array['end_date']['automatic_prolongation'] = 0;
            $result_array['end_date']['total_projected_spend'] = 0;
            foreach ($all_contracts['data'] as $ak => $av) {
                // echo $av['created_on'].'=='.date('my',strtotime($av['created_on'])).' == '.date('my').PHP_EOL;
                // echo 'AR='.$av['auto_renewal'].PHP_EOL;
                if (date('my', strtotime($av['created_on'])) == date('my')) {
                    $result_array['end_date']['created_this_month']++;
                }
                if (
                    date('my', strtotime($av['contract_end_date'])) ==
                    date('my')
                ) {
                    $result_array['end_date']['ending_this_month']++;
                }
                if ((int) $av['auto_renewal']) {
                    $result_array['end_date']['automatic_prolongation']++;
                }

                //Adding sum up
                $graph = $this->spent_mngment_graph(
                    'spent_line',
                    'Actual Spent',
                    $av
                );
                $Projected_value = 0;
                $Projected_value = array_sum(
                    array_map(function ($i) {
                        return (int) $i->data[0]->value;
                    }, $graph->dataset)
                );
                $exg_rate = 1;
                $exg_rate = str_replace(',', '.', $av['euro_equivalent_value']);
                if (
                    $av['currency_name'] ==
                        $main_currency[0]['currency_name'] ||
                    $exg_rate == 0
                ) {
                    $exg_rate = 1;
                }
                $result_array['end_date']['total_projected_spend'] +=
                    $Projected_value * $exg_rate;
                // $result_array['end_date']['total_projected_spend'] += $Projected_value;
            }
            $result_array['main_currency_name'] = $main_currency[0]['currency_name'];
        }
        $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);
    }

    /* dashboard all contacts tab graph data api end */

    /* dashboard all projects tab graph data api start */
    public function allprojectsGraph_get()
    {
        $data = $this->input->get();
        if (empty($data)) {
            $result = [
                'status' => false,
                'error' => $this->lang->line('invalid_data'),
                'data' => '',
            ];
            $this->response($result, REST_Controller::HTTP_OK);
        }

        $this->form_validator->add_rules('customer_id', [
            'required' => $this->lang->line('customer_id_req'),
        ]);
        $validated = $this->form_validator->validate($data);
        if ($validated != 1) {
            $result = ['status' => false, 'error' => $validated, 'data' => ''];
            $this->response($result, REST_Controller::HTTP_OK);
        }
        if (isset($data['customer_id'])) {
            $data['customer_id'] = pk_decrypt($data['customer_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                $this->session_user_info->customer_id != $data['customer_id']
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
            if (
                $this->session_user_info->user_role_id == 1 &&
                $data['customer_id'] != '' &&
                $data['customer_id'] > 0 &&
                !in_array(
                    $data['customer_id'],
                    $this->session_user_master_customers
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['user_role_id'])) {
            $data['user_role_id'] = pk_decrypt($data['user_role_id']);
            if (
                $data['user_role_id'] != $this->session_user_info->user_role_id
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['id_user'])) {
            $data['id_user'] = pk_decrypt($data['id_user']);
            if ($data['id_user'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }

        if (isset($data['delegate_id'])) {
            $data['delegate_id'] = pk_decrypt($data['delegate_id']);
            // if($this->session_user_info->user_role_id!=1 && !in_array($data['delegate_id'],$this->session_user_delegates)){
            //     $result = array('status'=>FALSE, 'error' =>array('message'=>$this->lang->line('permission_not_allowed')), 'data'=>'');
            //     $this->response($result, REST_Controller::HTTP_OK);
            // }
        }
        if (isset($data['contract_owner_id'])) {
            $data['contract_owner_id'] = pk_decrypt($data['contract_owner_id']);
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['contract_owner_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['contract_id'])) {
            $data['contract_id'] = pk_decrypt($data['id_contract']);
            if (
                !in_array($data['contract_id'], $this->session_user_contracts)
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['responsible_user_id'])) {
            $data['responsible_user_id'] = pk_decrypt(
                $data['responsible_user_id']
            );
            if (
                $this->session_user_info->user_role_id != 1 &&
                !in_array(
                    $data['responsible_user_id'],
                    $this->session_user_customer_all_users
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (isset($data['created_by'])) {
            $data['created_by'] = pk_decrypt($data['created_by']);
            if ($data['created_by'] != $this->session_user_id) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        if (
            isset($data['business_unit_id']) &&
            !is_array($data['business_unit_id'])
        ) {
            $data['business_unit_id'] = pk_decrypt($data['business_unit_id']);
            if (
                !in_array(
                    $data['business_unit_id'],
                    $this->session_user_business_units
                )
            ) {
                $result = [
                    'status' => false,
                    'error' => [
                        'message' => $this->lang->line(
                            'permission_not_allowed'
                        ),
                    ],
                    'data' => '',
                ];
                $this->response($result, REST_Controller::HTTP_OK);
            }
        }
        $business_unit_array = $data['business_unit_id'] = [];
        if (in_array($this->session_user_info->user_role_id, [3, 4, 8])) {
            $business_unit = $this->Business_unit_model->getBusinessUnitUser([
                'user_id' => $data['id_user'],
                'status' => '1',
            ]);
            $business_unit_array = $data['business_unit_id'] = array_map(
                function ($i) {
                    return $i['id_business_unit'];
                },
                $business_unit
            );
            $data['session_user_role'] = $this->session_user_info->user_role_id;
            $data['session_user_id'] = $this->session_user_id;
        }
        if ($this->session_user_info->user_role_id == 6) {
            $data['business_unit_id'] = $this->session_user_business_units;
        }
        if ($this->session_user_info->user_role_id == 7) {
            $data['provider_id'] = $this->session_user_info->provider;
        }
        if (count($data['business_unit_id']) == 0) {
            unset($data['business_unit_id']);
        }
        $data['can_access'] = 1;
        $data['get_all_records'] = true;
        $data['end_date_lessthan_90'] = 90;
        $data['type'] = 'project';
        $data['project_status'] = 1;
        $main_currency = $this->User_model->check_record('currency', [
            'customer_id' => $data['customer_id'],
            'is_maincurrency' => 1,
        ]);
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
         $result_array['main_currency_name'] = $main_currency[0]['currency_name'];
         $result = [
            'status' => true,
            'message' => $this->lang->line('success'),
            'data' => $result_array,
        ];
        $this->response($result, REST_Controller::HTTP_OK);  
    }

    /* dashboard all projects tab graph data api end */

    function spent_mngment_graph($graphtype, $graph_title, $data)
    {
        //echo '<pre>'.print_r($data);exit;
        $currency = $this->User_model->check_record('currency', [
            'id_currency' => $data['currency_id'],
        ]);
        $graph = '';

        $chart->showSum = '1';
        $chart->decimalSeparator = ',';
        $chart->thousandSeparator = '.';
        $chart->canvasTopMargin = '0';
        //$chart->yAxisMaxValue= '9,147,483,647';
        $chart->caption = '';
        $chart->subCaption = '';
        $chart->xAxisname = '';
        $chart->yAxisName = '';
        $chart->numberPrefix = $currency[0]['currency_name'] . ' ';
        $chart->animation = '0';
        $chart->showBorder = '0';
        $chart->bgColor = '#ffffff';
        $chart->showLabels = '1';
        $chart->adjustDiv = '1';
        $chart->showValues = '0';
        $chart->showLimits = '0';
        $chart->showDivLineValues = '0';
        $chart->showShadow = '0';
        $chart->showLegend = '0';
        $chart->showcanvasborder = '0';
        $chart->canvasBgAlpha = '0';
        $chart->divLineAlpha = '0';
        $chart->legendBorderAlpha = '0';
        $chart->showAlternateHGridColor = '0';
        $chart->useEllipsesWhenOverflow = '1';
        $chart->palette = '3';
        $chart->theme = 'fusion';
        $chart->plottooltext = "\$seriesName : <b>\$dataValue</b>";
        $chart->formatNumberScale = '0';
        $chart->usePlotGradientColor = '0';
        $chart->theme = 'fusion';
        $chart->use3DLighting = '1';
        $chart->creditLabel = '0';
        $chart->key =
            'yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==';

        $categories[0]->category[0]->label = 'Projected Spend';
        $categories[0]->category[1]->label = 'Actual Spend';

        $dataset = [];
        $spent_line_info = $this->User_model->check_record('spent_lines', [
            'contract_id' => $data['id_contract'],
            'status' => 1,
        ]);
        //echo '<pre>'.print_r($data);exit;
        foreach ($spent_line_info as $k => $v) {
            $spent_line_info[$k]['id'] = pk_encrypt($v['id']);
            $spent_line_info[$k]['contract_id'] = pk_encrypt($v['contract_id']);
            $spent_line_info[$k]['created_by'] = pk_encrypt($v['created_by']);
            $spent_line_info[$k]['updated_by'] = pk_encrypt($v['updated_by']);
        }

        $dataset[0]->seriesname = 'Projected Value';
        if (
            $data['contract_value_period'] == 'total' ||
            $data['contract_value_period'] == null
        ) {
            $dataset[0]->data[0]->value = round((int) $data['contract_value']);
            $dataset[0]->data[1]->value = 0;
            //$dataset[0]->data[1]->toolText = 'Spend Management';
        } else {
            $dataset[0]->data[0]->value = round(
                $data['contract_value'] * ((int) $data['months'] / 12)
            );
            $dataset[0]->data[1]->value = 0;
            //$dataset[0]->data[1]->toolText = 'Spend Management';
        }
        $dataset[1]->seriesname = 'Additional Reccuring fees';
        if ($data['additional_recurring_fees_period'] == null) {
            $dataset[1]->data[0]->value = round(
                $data['additional_recurring_fees']
            );
            $dataset[1]->data[1]->value = 0;
            //$dataset[1]->data[1]->toolText = 'Spend Management';
        } elseif ($data['additional_recurring_fees_period'] == 'month') {
            $dataset[1]->data[0]->value = round(
                $data['additional_recurring_fees'] * (int) $data['months']
            );
            $dataset[1]->data[1]->value = 0;
            //$dataset[1]->data[1]->toolText = 'Spend Management';
        } elseif ($data['additional_recurring_fees_period'] == 'quarter') {
            $dataset[1]->data[0]->value = round(
                ($data['additional_recurring_fees'] / 3) * (int) $data['months']
            );
            $dataset[1]->data[1]->value = 0;
            //$dataset[1]->data[1]->toolText = 'Spend Management';
        } else {
            $dataset[1]->data[0]->value = round(
                $data['additional_recurring_fees'] *
                    ((int) $data['months'] / 12)
            );
            $dataset[1]->data[1]->value = 0;
            //$dataset[1]->data[1]->toolText = 'Spend Management';
        }
        $dataset[2]->seriesname = 'Additional One-off fees';
        $dataset[2]->data[0]->value = round(
            (int) $data['additonal_one_off_fees']
        );
        $dataset[2]->data[1]->value = 0;
        $dataset[2]->data[1]->toolText = 'Actual spend';
        $i = $index = 3;
        // for($i = 3; $i<count($spent_line_info); $i++){
        //     $dataset[$i]->seriesname = 'Spent Line '.($i+1);
        //     $dataset[$i]->data[0]->value = 0;
        //     $dataset[$i]->data[0]->value = isset($data['spentline_info'][$i])?$data['spentline_info'][$i]['spent_amount']:0;
        // }
        foreach ($spent_line_info as $k => $v) {
            $dataset[$i]->seriesname = 'Spend Line ' . ($k + 1);
            $dataset[$i]->data[0]->value = 0;
            $dataset[$i]->data[0]->toolText = 'Projected Spend';
            $dataset[$i]->data[1]->value = $v['spent_amount'];
            $i++;
        }

        $graph->chart = $chart;
        $graph->categories = $categories;
        $graph->dataset = $dataset;
        // print_r($graph);exit;
        return $graph;
    }
}
