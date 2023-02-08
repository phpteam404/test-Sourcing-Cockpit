<?php
/*
 * English language
 */
$lang['text_rest_invalid_api_key'] = 'Invalid API key %s'; // %s is the REST API key
// $lang['text_rest_invalid_credentials'] = 'Wrong login information. Please check your login details';
$lang['text_rest_ip_denied'] = 'IP denied';
$lang['text_rest_ip_unauthorized'] = 'IP unauthorized';
$lang['text_rest_unauthorized'] = 'Unauthorized';
$lang['text_rest_ajax_only'] = 'Only AJAX requests are allowed';
$lang['text_rest_api_key_unauthorized'] = 'This API key does not have access to the requested controller';
$lang['text_rest_api_key_permissions'] = 'This API key does not have enough permissions';
$lang['text_rest_api_key_time_limit'] = 'This API key has reached the time limit for this method';
$lang['text_rest_unknown_method'] = 'Unknown method';
$lang['text_rest_unsupported'] = 'Unsupported protocol';
$lang['account_block_error'] = 'Account not available. Contact your administrator';
$lang['invaid_user'] = 'User or Email not exist';
$lang['two_more_attempts'] = 'Invalid Password, You have 2 more attempts remained for the day.';
$lang['one_more_attempts'] = 'Wrong login information. Please check your login details.';//, You have %s more attempts remaining.
$lang['ldap_not_available'] = 'LDAP Settings not found';
$lang['m_order_success'] = 'Module Order Updated Successfully';
$lang['t_order_success'] = 'Topic Order Updated Successfully';
$lang['q_order_success'] = 'Question Order Updated Successfully';
$lang['unable_download_file']='Unable to Download file';

/*General Messages*/
$lang['document_error'] = 'Please upload valid documents';
$lang['allowed_formats']= ' txt, jpeg, jpg, png, gif, bmp, pdf, doc, docx, rtf, xls, xlsx, ppt, pptx';
$lang['max_upload_size'] = 'Uploaded document size must be < %s';
$lang['invalid_format'] = 'Invalid format';
$lang['invalid_data'] = 'Invalid data, Please Check again.';
$lang['success'] = 'Success';
$lang['info_save'] = 'Information saved successfully';
$lang['date_req'] = 'Date required';
$lang['days_req'] = 'Days required';
$lang['month_req'] = 'Month required';
$lang['year_req'] = 'Year required';
$lang['filterType_req'] = 'filterType required';





/* Access Logs */
$lang['action_name_req'] = 'Action name required';
$lang['action_url_req'] = 'Action url required';
$lang['action_description_req'] = 'Action description required';
$lang['access_token_req'] = 'Action token required';

/*User Module*/
$lang['user_id_req'] = 'User id required';
$lang['user_role_id_req'] = 'User role required';
$lang['first_name_req'] = 'Invalid First Name';
$lang['first_name_len'] = 'First Name should be below 40 characters';
$lang['last_name_req'] = 'Invalid Last Name';
$lang['last_name_len'] = 'Last Name should be below 40 characters';
$lang['email_req'] = 'Email is required';
$lang['email_invalid'] = 'Enter valid email';
$lang['email_wrong'] = 'Email does not exists';
$lang['email_duplicate'] = 'Email already exists';
$lang['email_not_exists'] = 'Email does not exist';
$lang['password_req'] = 'Password is required';
$lang['password_num_min_len'] = 'Password must be minimum 8 characters';
$lang['password_num_max_len'] = 'Password must be maximum 12 characters';
$lang['confirm_password_req'] = 'Confirm Password is required';
$lang['old_new_password_same'] = 'Old Password and New Password cannot be same';
$lang['old_password_not_match'] = 'Incorrect old password';
$lang['new_password_confirm_password_notmatch'] = "New Password and Confirm Password didn't match.";
$lang['password_changed'] = 'Password updated successfully.';
$lang['password_match'] = 'Password did not match';
$lang['login_error'] = 'Incorrect login details';
$lang['login_inactive_error'] = 'Your account is disabled. Please contact your administrator.';
$lang['user_update'] = 'User updated successfully.';
$lang['user_add'] = 'User created successfully.';
$lang['phone_num_req'] = 'Phone Number is required';
$lang['phone_num_req'] = 'Phone Number is required';
$lang['phone_num_num'] = 'Phone Number should be numeric';
$lang['phone_num_min_len'] = 'Phone Number must be minimum 7 digits';
$lang['phone_num_max_len'] = 'Phone Number must be maximum 15 digits';
$lang['phone_num_max_len_20'] = 'Phone Number must be maximum 15 digits';
$lang['customer_admin_inactive'] = 'Admin inactive successfully';
$lang['customer_user_inactive'] = 'User inactive successfully';
$lang['status'] = 'Status required';
$lang['is_manual_password_req'] = 'Manual Password field is required';
$lang['new_password'] = 'New password is sent to your mail';
$lang['ldap_failed'] = 'LDAP Authentication Failed';
$lang['downgrade_not_possible'] = 'Downgrade is applicable to only Manger to Owner';
$lang['upgrade_not_possible'] = 'Upgrade is applicable to only Owner to Manager';


/* Customer */
$lang['from_date_req'] = 'From date required';
$lang['to_date_req'] = 'To date required';
$lang['customer_id_req'] = 'Customer id required';
$lang['company_name_req'] = 'Company name required';
$lang['company_address_req'] = 'Company address required';
$lang['postal_code_req'] = 'Postal code required';
$lang['postal_code_num'] = 'Postal code must be number';
$lang['vat_number_req'] = 'Vat number required';
$lang['country_id_req'] = 'Country id required';
$lang['created_by_req'] = 'Created by required';
$lang['updated_by_req'] = 'Updated by required';
$lang['company_status_req'] = 'status is required';
$lang['customer_add'] = 'Customer added successfully';
$lang['customer_update'] = 'Customer updated successfully';
$lang['customer_inactive'] = 'Customer inactive successfully';
$lang['customer_admin_add'] = 'Customer admin created successfully';
$lang['customer_admin_update'] = 'Customer admin updated successfully';
$lang['customer_template_failed'] = 'Customer template not linked';
$lang['dump_template_failed'] = 'Unable to execute procedure';
$lang['provider_id_req'] = 'Relation id required';
$lang['provider_name_req'] = 'Relation name required';
$lang['address_req'] = 'Address required';
$lang['contact_no_req'] = 'Contact no. required';
$lang['description_req'] = 'Description required';
$lang['provider_update'] = 'Relation updated successfully';
$lang['provider_add'] = 'Relation added successfully';
$lang['provider_exists'] = 'Relation name already exists';
$lang['provider_failed'] = 'Relation not created';
$lang['user_type_req'] = 'User type required';
$lang['invalid_provider'] = 'Invalid Relation';
$lang['provider_deleted'] = 'Relation Deleted successfully';
$lang['provider_having_subtask'] = 'Relation already mapped to the Subtask';

/* project */
$lang['project_update'] = 'Project Information updated successfully';
$lang['project_id_req'] = 'Project id required';
$lang['project_add'] = 'Project created successfully';
$lang['Project_name_req'] = 'Project name required';
$lang['Project_uniqid_req'] = 'Project ID required';
$lang['project_unique_id_exists'] = 'Project ID Already exists.';
$lang['contract_alrady_link'] = 'Contract already linked to this project';
$lang['provider_alrady_link'] = 'Relation already linked to this project';
/* Document */
//$lang['document_add'] = 'Document(s) added successfully';
$lang['document_add'] = 'Added successfully';
$lang['reference_id_req'] = 'Reference id required';
$lang['reference_type_req'] = 'Reference type required';
$lang['contract_linked'] = 'Contract linked successfully';
$lang['provider_linked'] = 'Relation linked successfully';
$lang['contract_start_data_is_less'] = 'Start date should be less than end date';


/* Module */
$lang['module_id_req'] = 'Module id required';
$lang['module_selection_id_req'] = 'Invalid selection';
$lang['module_name_req'] = 'Module name required';
$lang['module_order_req'] = 'Module order required';
$lang['module_language_id_req'] = 'Module language id required';
$lang['module_add'] = 'Module added successfully';
$lang['module_not_added'] = 'Module Not added';
$lang['module_update'] = 'Module updated successfully';
$lang['module_inactive'] = 'Module inactive successfully';
$lang['module_status_req'] = 'Module status required';
$lang['module_type_req'] = 'Module type required';
$lang['module_to_avail_template_req'] = 'Available template id required';

/* Topic */
$lang['topic_id_req'] = 'Topic id required';
$lang['topic_id_selection_req'] = 'Invalid selection';
$lang['topic_name_req'] = 'Topic name required';
$lang['topic_type_req'] = 'Topic type required';
$lang['topic_order_req'] = 'Topic order required';
$lang['topic_language_id_req'] = 'Topic language id required';
$lang['topic_add'] = 'Topic added successfully';
$lang['topic_update'] = 'Topic updated successfully';
$lang['topic_inactive'] = 'Topic inactive successfully';
$lang['topic_status_req'] = 'Topic status required';

/* Question */
$lang['question_text_req'] = 'Question text required';
$lang['question_order_req'] = 'Question order required';
$lang['question_type_req'] = 'Question type required';
$lang['option_name_req'] = 'Question options required required';
$lang['question_add'] = 'Question added successfully';
$lang['question_update'] = 'Question updated successfully';
$lang['question_id_req'] = 'Question id required';
$lang['question_answer_req'] = 'Question answer required';
$lang['question_feedback_req'] = 'Question feedback required';
$lang['question_id_select_req'] = 'Invalid selection';
$lang['question_language_id_req'] = 'Question language id required';
$lang['question_status_req'] = 'Question status required';
$lang['id_relationship_category_question_req'] = 'Category Question id required';
$lang['id_relationship_category_req'] = 'Category required';
$lang['updateRelationshipCategories_status_req'] = 'Status required';
$lang['question_data_req'] = 'Question data required';
$lang['enable_category'] = 'You have to Enable atleast one Category';

/* Relationship category */
$lang['relationship_category_cannot_downgrade'] = 'Relationship category Cannot be downgraded';
$lang['relationship_category_id_req'] = 'Relationship category id required';
$lang['relationship_category_language_id_req'] = 'Relationship category language id required';
$lang['relationship_category_name_req'] = 'Relationship category name required';
$lang['relationship_category_quadrant_req'] = 'Relationship category quadrant required';
$lang['relationship_category_quadrant_duplicate'] = 'Relationship category quadrant already exists';
$lang['relationship_category_add'] = 'Relationship category added successfully';
$lang['relationship_category_update'] = 'Relationship category updated successfully';
$lang['relationship_category_delete'] = 'Relationship category deleted successfully';
$lang['relationship_category_id_rey'] = 'Relationship category id required';
$lang['relationship_category_status_req'] = 'Relationship category status required';
$lang['classification_name_req'] = 'Relationship classification is required';
$lang['classification_position_req'] = 'Relationship classification position is required';
$lang['is_visible_req'] = 'visible option required';
$lang['classification_position_duplicate'] = 'Relationship classification position already exists';
$lang['classification_status_req'] = 'Relationship classification status required';
$lang['relationship_classification_add'] = 'Relationship classification added successfully';
$lang['relationship_classification_update'] = 'Relationship classification updated successfully';
$lang['relationship_classification_id_req'] = 'Relationship classification id required';
$lang['relationship_classification_language_id_req'] = 'Relationship classification language id required';
$lang['relationship_classification_delete'] = 'Relationship classification deleted';
$lang['parent_classification_id_req'] = 'Parent classification id required';
/* Provider relation ship categeoreis */
$lang['provider_relationship_classification_add'] = 'Relation relationship classification added successfully';
$lang['provider_relationship_category_add'] = 'Relation Relationship category added successfully';
$lang['provider_relationship_category_id_req'] = 'Relation Relationship category id required';
$lang['Provider_relationship_category_language_id_req'] = 'Relation Relationship category language id required';
$lang['provider_relationship_category_status_req'] = 'Relation Relationship category status required';
$lang['provider_relationship_category_update'] = 'Relation Relationship category updated successfully';
$lang['provider_classification_id_req'] ='Relation Classification id required';
$lang['provider_classification_language_id_req'] ='Relation Classfication Language id required';
$lang['provider_classification_position_duplicate'] ='Relation Classification position already exists';
$lang['provider_relationship_category_update'] ='Relation Classification updated Succesfully';
// $lang['provider_relationship_category_add'] ='Relation Classification added Succesfully';
$lang['provider_relationship_classification_add'] ='Relation Classification added Succesfully';
$lang['unique_id_exists'] ='ID already exists';
$lang['update_provider'] ='Relation Information updated successfully';
$lang['provider_tags_updated'] ='Relation tags updated successfully';
/* Templates */
$lang['template_id_req'] = 'Template id required';
$lang['template_name_req'] = 'Template name required';
$lang['template_name_duplicate'] = 'Template name already exists';
$lang['template_add'] = 'Template added successfully';
$lang['template_update'] = 'Template updated successfully';
$lang['template_module_save'] = 'Template module saved successfully';
$lang['template_module_topic_save'] = 'Template module topic saved successfully';
$lang['template_module_topic_question_save'] = 'Template module topic question saved successfully';
$lang['template_module_id_req'] = 'Template module id required';
$lang['template_module_topic_id_req'] = 'Template module topic id required';
$lang['template_module_delete'] = 'Module removed successfully';
$lang['template_module_topic_id_req'] = 'Template module topic id required';
$lang['template_module_topic_question_id_req'] = 'Template module topic question id required';
$lang['template_module_topic_delete'] = 'Topic removed successfully.';
$lang['template_module_topic_question_delete'] = 'Question removed successfully';
$lang['template_clone'] = 'Template cloned successfully';

/* Settings update */
$lang['settings_update'] = 'Settings updated successfully.';

/* Business Unit */
$lang['bu_name_req'] = 'Business name required';
$lang['bu_responsibility_req'] = 'Business responsibility required';
$lang['business_unit_id_req'] = 'Business unit id required';
$lang['business_unit_create'] = 'Business unit created successfully';
$lang['business_unit_update'] = 'Business unit updated successfully';

/* Contract */
$lang['contract_name_req'] = 'Contract name required';
$lang['contract_id_req'] = 'Contract id required';
$lang['contract_review_id_req'] = 'Contract review id required';
$lang['contract_owner_id_req'] = 'Contract owner required';
$lang['contract_start_date_req'] = 'Contract start date required';
$lang['contract_end_date_req'] = 'Contract end date required';
$lang['contract_value_req'] = 'Contract value required';
$lang['currency_id_req'] = 'Currency id required';
$lang['id_contract_req'] = 'Contract id required';
$lang['contract_add'] = 'Contract added successfully';
$lang['contract_update'] = 'Contract Information updated successfully';
$lang['contract_tags_update'] = 'Contract tags updated successfully';
$lang['stakeholder_update'] = 'Stakeholder updated successfully';
$lang['stakeholder_not_update'] = 'Stakeholder not updated';
$lang['contract_start_date_invalid'] = 'Contract start date is invalid';
$lang['contract_end_date_invalid'] = 'Contract end date is invalid';
$lang['contract_delegate_id_req'] = 'Contract delegate required';
$lang['contract_description_req'] = 'Contract description required';
$lang['workflow_initiate'] = 'Task initiated successfully.';
$lang['review_initiate'] = 'Review initiated successfully.';
$lang['review_finalize'] = 'Review finalized successfully.';
$lang['workflow_finalize'] = 'Task finalized successfully.';
$lang['contract_delete'] = 'Contract Deleted successfully.';
$lang['contract_undo'] = 'Contract Restored successfully.';
$lang['validate_initiate'] = 'Validation initiated successfully.';
$lang['validate_completed'] = 'Validation completed successfully.';
$lang['export_type_req'] = 'Export type Required.';
$lang['workflow_added'] = 'Task Added to Calender.';
$lang['contract_unique_id_alredy_ext'] = 'Contract ID already exists. Please try again ';
$lang['template_should_be_locked_by_customer_admin_only'] = 'Template should be locked by customer Admin only';
$lang['template_should_be_locked_or_unlocked_by_customer_admin_only'] = 'Template should be locked or unlocked by customer Admin only';
$lang['contract__active_status_required'] = 'status Required';
/*Contract review items*/
$lang['action_item_req'] = 'Action item required';
$lang['responsible_user_id_req'] = 'Responsible user required';
$lang['due_date_req'] = 'Due date required';
$lang['contract_review_action_item_add'] = 'Action item added successfully';
$lang['contract_review_action_item_update'] = 'Action item updated successfully';
$lang['contract_review_action_item_delete'] = 'Action item deleted successfully';
$lang['contract_review_id'] = 'Contract Review Id Required';
$lang['id_contract_review_action_item_req'] = 'Action Item Id Required';
$lang['no_review_access'] = 'You Don\'t have access to this review please move further';

/*Document */
$lang['document_id_req'] = 'Document id required';
$lang['document_delete'] = 'Document deleted successfully';
$lang['document_unlocked'] = 'Document unlocked successfully';
$lang['document_locked'] = 'Document locked successfully';
$lang['no_link_added'] = 'No link added';

/*Calender*/
$lang['calender_id_req'] = 'Calender id required';
$lang['provider_id_req'] = 'Relation id required.';
$lang['recurrence_till_req'] = 'Recurrence till Date required.';
$lang['recurrence_req'] = 'Recurrence required.';
$lang['review_added_to_calender'] = 'Review Added to Calender.';
$lang['contract_not_unlock'] = 'Contracts are not un-locked';
$lang['workflow_name_req'] = 'Workflow name required';
$lang['workflow_not_created'] = 'Workflow not created';
$lang['workflow_not_updated'] = 'Workflow not updated';
$lang['calender_not_created'] = 'Calender not created';


/* Mails */
$lang['mail_footer'] = '<p style="color:#8e8e8e;font-size:10px">If you are not sure what this is about, you can disregard this message. Have questions? Need help?
                <a href="mailto:support@with-services.com" style="color:#74a6f9; text-decoration:none">Contact our support team </a> and we’ll get back to you in just a few minutes - promise</p>';
$lang['id_email_template_req'] = 'Email template id required.';
$lang['invalid_id_email_template'] = 'Email template id is not valid.';
$lang['email_status_req'] = 'Email template Status is required.';
$lang['id_email_template_language_req'] = 'Email template language is required.';
$lang['email_template_name_req'] = 'Email template name is required.';
$lang['email_template_subject_req'] = 'Email template subject is required';
$lang['email_template_content_req'] = 'Email template content is required';

//forget password
$lang['forget_password_subject'] = 'Password Recovery';
$lang['forget_password_mail'] = '<p>Dear <i>{first_name} {last_name}</i>, Your newly generated password is <b>{password}</b><br><b>Note: It is recommended to update your Password next time when you login.</b></p>';
$lang['host_req'] = 'Host required';
$lang['dc_req'] = 'dc required';
$lang['port_req'] = 'Port required';
$lang['not_updated'] = 'Update failed';
$lang['not_inserted'] = 'Insert failed';
$lang['inserted'] = 'LDAP Details Added Successfully';
$lang['updated'] = 'LDAP Details Updated Successfully';
$lang['status_req'] = 'LDAP status required Successfully';

/* for customer admin */
$lang['customer_admin_create_subject'] = 'Account created';
$lang['customer_admin_create_message'] = '<p>Dear <i>{first_name} {last_name}</i>, Your {role} account for {customer_name} created successfully. your login details are <br><b>Email : </b>{email} <br><b>Password : </b>{password}<br><b>Note: It is recommended to update your Password next time when you login.</b></p>';

/* for customer admin */
$lang['customer_user_create_subject'] = 'Account created';
$lang['customer_user_create_message'] = '<p>Dear <i>{first_name} {last_name}</i>, Your {role} account for {customer_name} created successfully. your login details are <br><b>Email : </b>{email} <br><b>Password : </b>{password}<br><b>Note: It is recommended to update your Password next time when you login.</b></p>';

//forget password
$lang['reset_password_subject'] = 'Password Changed';
$lang['reset_password_mail'] = '<p>Dear <i>{first_name} {last_name}</i>, Your password has been changed by admin. Your newly generated password is <b>{password}</b><br><b>Note: It is recommended to update your Password next time when you login.</b></p>';
$lang['id_user_req'] = 'User id required';
$lang['user_validations_will_be_gone'] = 'User has been removed as a validator.!';


$lang['review_discussion_initiate_success'] = 'Review discussion has been initiated.';
$lang['review_discussion_save_success'] = 'Review discussion has been saved.';
$lang['review_discussion_close_success'] = 'Review discussion has been closed.';
$lang['question_id_req'] = 'Question id required.';
$lang['comments_required'] = 'Comments required.';
$lang['module_id_req'] = 'Module id required.';
$lang['second_opinion_req'] = 'Second opinion required.';
$lang['save_question_answere'] = 'Please save the Answers first.';



//report
$lang['report_delete']='Report deleted successfully.';
$lang['report_save']='Report has been savd successfully.';
$lang['id_report_req']='Report id required.';
$lang['report_save_type']='Report save type required.';
$lang['report_name_req']='Report name required.';
$lang['report_contracts']='Report contracts required.';
$lang['report_classification_id_req'] = 'Relationship classification id required.';
$lang['latest_review_from_date_req'] = 'From date required.';
$lang['latest_review_to_date_req'] = 'To date required.';

$lang['from_date_req'] = 'From date required.';
$lang['to_date_req'] = 'To date required.';
$lang['type_req'] = 'Type Required.';

$lang['permission_not_allowed'] = 'Unauthorized access.';
$lang['user_already_assigned_with'] = 'Sorry this user is already assigned to contract(s).';
$lang['module_url_req'] = 'Module URL required.';

//tags
$lang['tag_add'] = 'Tag Added Successfully.';
$lang['tag_text_req'] = 'Tag Text Requiered.';
$lang['can_update_fixed_tag_only'] = 'Can Update fixed tag only';
$lang['tag_type_req'] = 'Tag Type Requiered.';
$lang['tag_error'] = 'You cannot create more than 72 Active Tags.';
$lang['tag_update'] = 'Tag updated successfully.';
$lang['tag_req'] = 'Tag data Required.';

//spent mngmt
$lang['spent_line_add_success'] = 'Spent Line added successfully.';
$lang['spent_line_update_success'] = 'Spent Line updated successfully.';
$lang['spent_info_add_success'] = 'Spent Information added successfully.';
$lang['spent_info_update_success'] = 'Spent Information updated successfully.';
$lang['contract_value_period_req'] = 'Contract value period required.';
$lang['additional_recurring_fees_req'] = 'Additional recurring fees required.';
$lang['additional_recurring_fees_period_req'] = 'Additional recurring fees period required.';
$lang['additonal_one_off_fees_req'] = 'Additonal one off fees required.';
$lang['spent_period_req'] = 'Spent period required.';
$lang['spent_amount_req'] = 'Spent amount required.';
$lang['contract_value_req'] = 'Projected value required.';
$lang['spent_line_id_req'] = 'Spent Line Id required';
$lang['same_not_possible'] = 'Sub Argument mapping on self contract is not possible';


//Missileneous
$lang['id_stored_module_req'] = 'Store module id required.';
$lang['activate_in_next_review_req'] = 'Activation status is required.';
$lang['from_date_should_be_less_than_to_date'] = 'From date should be less than to date';
$lang['tabs_order_changed_successfully'] = 'Tabs Order changed successfully';

//service Catalogue
$lang['catalogue_item_req'] = 'Catalogue item  required.';
$lang['service_catalogue_add_success'] = 'Service Catalogue added successfully.';
$lang['service_catalogue_update_success'] = 'Service Catalogue updated successfully.';
$lang['service_catalogue_deleted_successfully'] = 'Service Catalogue Deleted successfully.';
$lang['service_catalogue_id_req'] = 'Service Catalogue id required.';
$lang['period_start_date_should_be_less_than_period_end_date'] = 'Period start date should be less than Period end date';

//Obligations and rights
$lang['no_of_days_req'] = 'No of days required.';
$lang['logic_req'] = 'Logic required.';
$lang['notification_message_req'] = 'Notification Message required.';
$lang['resend_recurrence_req'] = 'Resend recurrence required.';
$lang['email_send_last_date_req'] = 'Email resend end date required';
$lang['start_date_be_less_than_end_date'] = 'Email Notification start day should be less than email notification end data';
$lang['recurrence_start_date_should_be_empty'] = 'Recurrence start date should be empty';
$lang['recurrence_end_date_should_be_empty'] = 'Recurrence end date should be empty';
$lang['calender_should_be_off'] = 'Calender should be off';
$lang['recurrence_start_date_should_not_be_empty'] = 'Recurrence start date should not be empty';
$lang['recurrence_end_date_should_not_be_empty'] = 'Recurrence end date should not be empty';
$lang['recurrence_start_date_should_be_less_than_recurrence_end_date'] = 'Recurrence start date should be less than recurrence end date';
$lang['email_send_start_date_should_be_less_than_email_send_last_date'] = 'Date should be less than Email resend end date';
$lang['obligation_id_req'] = 'Obligations Id required';
$lang['deleted_successfully'] = 'Deleted successfully';
$lang['right_add_success'] = 'Right Added successfully';
$lang['obligation_add_success'] = 'Obligation Added successfully';
$lang['right_updated_success'] = 'Right updated successfully';
$lang['obligation_updated_success'] = 'Obligation updated successfully';
$lang['obligation_and_right_updated_success'] = 'Obligation and Right updated successfully';
$lang['obligation_and_right_added_success'] = 'Obligation and Right added successfully';
$lang['deleted_sucessfully'] = 'Obligation and Right deleted successfully';
//////////////////currency//////////////////
$lang['currency_name_req'] = 'Currency name required';
$lang['currency_code_req'] = 'Currency code required';
$lang['currency_added'] = 'Additional Currency Created successfully';
$lang['main_currency_updated'] = 'Main Currency Updated successfully';
$lang['additional_currency_updated'] = 'additional Currency Updated successfully';
$lang['exchange_rate_is_numaric'] = 'Exchange rate must be only numbers';
////////////////////////////////////////////document intelligence////////////
$lang['int_temp_name_req'] = 'Template name required';
$lang['customer_req'] = 'customer is  required';
$lang['template_add'] = 'Template Created successfully';
$lang['intelligence_template_id_req'] = 'Intelligence Template id required';
$lang['template_update'] = 'Template Updated successfully';
$lang['field_name_req'] = 'Field name is required';
$lang['field_type_req'] = 'Field type is required';
$lang['question_req'] = 'Field name is required';
$lang['template_question_add'] = 'Template question created successfully';
$lang['intelligence_template_field_id_req'] = 'Template question id required';
$lang['question_deleted'] = 'Template question deleted successfully';
$lang['template_question_update'] = 'Template question updated successfully';
$lang['file_size_execed_one_mb'] = 'Upload File size should be not more then one megabyte';
$lang['only_pdf_files'] = 'Upload only pdf files';
$lang['owner_req'] = 'Owner id required';
$lang['delegate_req'] = 'Delegate id required';
$lang['document_inteligence_create'] = 'Document Inteligence created successfully';
$lang['document_inteligence_update'] = 'Document Inteligence updated successfully';
$lang['document_inteligence_delete'] = 'Document Inteligence deleted successfully';
$lang['document_inteligence_id_req'] = 'Document Inteligence id required.';
$lang['unable_delete_doc_int'] = 'Unable to delete document intelligence until contract creation completed';
$lang['parent_contract_id_req'] = 'Parent Contrct id required';
$lang['child_contract_id_req'] = 'Child Contract id required';
$lang['sub_arg_mapped'] = 'Sub Agreements mapped successfully';
$lang['sub_arg_un_mapped'] = 'Sub Agreements un mapped successfully';
$lang['signle_contracts_only_mpped'] = 'Only linking single contracts as Sub Agreements ';
$lang['validate_answers_req'] = 'Validation Answers are required';
$lang['validation_saved_successfully'] = 'Validation Saved successfully';
$lang['attachment_path_required'] = 'Attachment path required';
$lang['path_does_not_exist'] = 'Document Path Does not Exist';
$lang['moved_successfully'] = 'Document Moved Successfully';
$lang['something_went_wrong'] = 'Something Went Wrong';
$lang['cant_submit_validation'] = 'Cannot Submit Validation';
$lang['validation_submitted_successfully'] = 'Validation submitted successfully';
$lang['process_completed_successfuly'] = 'Process completed successfully';
$lang['first_submit_validation'] = 'first submit validation';
$lang['document_name_required'] = 'Document name required';
$lang['please_select_file'] = 'Please Select file';
///////////////Advanced filters///////////////////
$lang['domain_module_req'] = 'Domain module required';
$lang['id_master_domain_req'] = 'Master Domain id required';
$lang['condition_req'] = 'Condition required';
$lang['value_req'] = 'Value required';
$lang['master_domain_field_id_req'] = 'Master domain field id required'; 
$lang['filter_updated_successfully'] = 'Filter Updated successfully'; 
$lang['filter_added_successfully'] = 'Filter added successfully';
$lang['id_master_filter_req'] = 'Filter Id required';
$lang['filter_deleted_successfully'] = 'Filter deleted successfully';
$lang['contribution_type_req'] = 'contribution type required';

$lang['contract_workflow_id_req'] = 'contract workflow Id required';
$lang['sub_task_does_not_exist'] = 'Sub task does not exist';
$lang['sub_task_linked_to_contract'] = 'Sub task linked to contarct';
$lang['sub_task_mapped'] = 'Subtask Mapped Successfully';
$lang['sso_check_req'] = 'URL SSO check required';
$lang['issuer_url_req'] = 'Issuer URL / Entity Id required';
$lang['certificate_req'] = 'X.509 certificate  required';
$lang['is_email_verification_active_req'] = 'Email verification is required';
$lang['is_mfa_active_req'] = 'MFA is required';
$lang['email_verification_should_active'] = 'Email Verification should active';
$lang['saml_inserted'] = 'SAML Details Added successfully';
$lang['saml_updated'] = 'SAML Details Updated successfully';
$lang['mfa_updated'] = 'MFA Details Updated successfully';
$lang['verification_code_sent_successfully'] = 'Verification code sent successfully';
$lang['verification_code_req'] = 'Verification code required';
$lang['verification_method_req'] = 'Verification method required';
$lang['invalid_verification_code'] = 'Invalid Verification code';
$lang['verification_code_expired'] = 'Verification code expired';


//satic messages
$lang['no_reviews_found'] = 'No reviews found';
$lang['project_deleted_successfully'] = 'Project Deleted successfully.';
$lang['contract_deleted_successfully'] = 'Contract Deleted successfully.';
$lang['operation_failed'] = 'Operation Failed';
$lang['has_a_conflict'] = 'has a conflict';
$lang['file_is_not_in_pdf_format'] = 'file is not in pdf format';
$lang['upload_limited_to_max_20_files_at_once'] = 'Upload limited to max. 20 files at once';
$lang['upload_at_least_one_file'] = 'Upload at least one file';
$lang['not_allowed'] = 'Not Allowed';
$lang['not_allowed_to_access'] = 'Not allowed to access.';
$lang['file_not_found'] = 'File Not Found';
$lang['the_file_you_requested_are_not_found'] = 'The file you requested are not found.';
$lang['subtask_already_mapped_to_contract'] = 'Subtask Already Mapped To Contract';
$lang['you_dont_have_permissions_to_this_module'] = "You don't have permissions to this module";
$lang['contributor_updated_successfully'] = 'Contributor Updated Successfully.';



//event Feeds
$lang['reference_type_req'] = 'Reference type required';
$lang['reference_id_req'] = 'Reference id required';
$lang['subject_req'] = 'Subject required';
$lang['event_feed_added_successfully'] = 'Event feed added successfully';
$lang['event_feed_updated_successfully'] = 'Event feed updated successfully';
$lang['event_feed_id_req'] = 'Event feed id required';
$lang['event_feed_deleted_sucessfully'] = 'Event feed deleted successfully';


$lang['verification_code_expired'] = 'Verification code expired';
$lang['language_id_req'] = 'language id required';
$lang['contract_information'] = 'Contract Information';
$lang['contract_tags'] = 'Contract Tags';
$lang['action_items'] = 'Action Items';
$lang['obligations_rights'] = 'Obligations & Rights';
$lang['service_catalogue'] = 'Service Catalogue';
$lang['contract_event_feed'] = 'Contract Event Feed';
$lang['sub_agreements'] = 'Sub Agreements';
$lang['tabs_order_changed_sucessfully'] = 'Tabs order changed successfully';
$lang['contract_value'] = 'Contract Value';
$lang['invoices'] = 'Invoices';
$lang['catalogue_value'] = 'Catalogue Value';

$lang['urgent'] = 'Urgent';
$lang['medium'] = 'Medium';
$lang['low'] = 'Low';
$lang['not_classified'] = 'Not Classified';
$lang['expert'] = 'Expert';
$lang['validator'] = 'Validator';
$lang['relation'] = 'Relation';
$lang['green'] = 'Green';
$lang['red'] = 'Red';
$lang['amber'] = 'Amber';
$lang['n_a'] = 'N/A';
$lang['contract_not_found'] = 'Contract Not found';

//contract builder
$lang['key_required'] = 'Key required';
$lang['method_required'] = 'Method required';

//catalogue
$lang['catalogue_name_req'] = 'Catalogue name required';
$lang['status_required'] = 'Catalogue status required';
$lang['catalogue_description_req'] = 'Catalogue description required';
$lang['catalogue_unique_id_alredy_ext'] = 'Catalogue ID already exists. Please try again ';
$lang['catalogue_add'] = 'Catalogue added successfully';
$lang['id_catalogue_req'] = 'Catalogue Id required';
$lang['catalogue_tags_update'] = 'Catalogue Tags updated successfully';
$lang['catalogue_update'] = 'Catalogue updated successfully';
$lang['catalogue_deleted'] = 'Catalogue deleted successfully';

// Tags
$lang['multi_select_not_allowed'] = 'Multi Select is not allowed for this type';
$lang['selected_field_is_req'] = 'Select field is required';


//login errors
$lang['please_login_with_ldap'] = 'Please login with LDAP';
$lang['please_login_with_sso'] = 'Please login with SSO';
$lang['please_login_with_mfa'] = 'Please login with MFA';
$lang['domain_or_field_is_invalid'] = 'selected Domain or Field is Invalid';
$lang['text_rest_invalid_credentials'] = 'Incorrect email address/password combination';
$lang['reg_exp_not_match'] = 'You should enter combination of uppercase and lowercase alphabets, numbers, and special characters($ @ $ ! % * # ? & ( ) . - _ = +) of minimum length 8';

$lang['contract_not_found'] = 'Contract not found';
$lang['base_currency_code_req'] = 'Base currency code required';
$lang['convertable_currency_code_req'] = 'convertable currency code required';
$lang['currencys_not_found'] = 'Currencys Not found';
$lang['service_catalogue_deleted_sucessfully'] = 'Service Catalogue deleted sucessfully';
$lang['value_management_information_update_success'] = 'Value management Information updated successfully.';

$lang['user_deleted_successfully'] = 'User deleted Successfully';
$lang['inactive_user_first'] = 'InActivate User First';





