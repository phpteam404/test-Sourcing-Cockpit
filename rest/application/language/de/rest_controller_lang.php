<?php
/*
 * German language
 */
$lang['text_rest_invalid_api_key'] ='Ungültiger API-Schlüssel %s';// %s is the REST API key
$lang['text_rest_invalid_credentials'] ='Falsche Anmeldedaten. Bitte überprüfen Sie Ihre Anmeldedaten';
$lang['text_rest_ip_denied'] ='IP denied';
$lang['text_rest_ip_unauthorized'] ='IP nicht authorisiert';
$lang['text_rest_unauthorized'] ='nicht authorisiert';
$lang['text_rest_ajax_only'] ='Nur AJAX-Anfragen sind erlaubt';
$lang['text_rest_api_key_unauthorized'] ='Dieser API-Schlüssel hat keinen Zugriff auf den angeforderten Controller';
$lang['text_rest_api_key_permissions'] ='Dieser API-Schlüssel hat nicht genügend Berechtigungen';
$lang['text_rest_api_key_time_limit'] ='Dieser API-Schlüssel hat das Zeitlimit für diese Methode erreicht';
$lang['text_rest_unknown_method'] ='Unbekannte Methode';
$lang['text_rest_unsupported'] ='Nicht unterstütztes Protokoll';
$lang['account_block_error'] ='Konto nicht verfügbar. Kontaktieren Sie Ihren Administrator';
$lang['invaid_user'] ='Benutzer oder E-Mail nicht vorhanden';
$lang['two_more_attempts'] ='Ungültiges Passwort, Sie haben heute noch 2 Versuche übrig.';
$lang['one_more_attempts'] ='Falsche Anmeldedaten. Bitte überprüfen Sie Ihre Anmeldedaten.';//, Sie haben %s verbliebene Versuche.
$lang['ldap_not_available'] ='LDAP-Einstellungen nicht gefunden';
$lang['m_order_success'] ='Modulauftrag erfolgreich aktualisiert';
$lang['t_order_success'] ='Themenauftrag erfolgreich aktualisiert';
$lang['q_order_success'] ='Frage Bestellung erfolgreich aktualisiert';
$lang['unable_download_file']='Download nicht möglich';

/*General Messages*/
$lang['document_error'] ='Bitte laden Sie gültige Dokumente hoch';
$lang['allowed_formats']='"txt, jpeg, jpg, png, gif, bmp, pdf, doc, docx, rtf, xls, xlsx, ppt, pptx";';
$lang['max_upload_size'] ='Die Größe des hochgeladenen Dokuments muss < %s sein';
$lang['invalid_format'] ='Ungültiges Format';
$lang['invalid_data'] ='Ungültige Daten, bitte nochmals prüfen.';
$lang['success'] ='Erfolg';
$lang['info_save'] ='Informationen erfolgreich gespeichert';
$lang['date_req'] ='Erforderliches Datum';
$lang['days_req'] ='Erforderliche Tage';
$lang['month_req'] ='Monat erforderlich';
$lang['year_req'] ='Erforderliches Jahr';
$lang['filterType_req'] ='Filter-Type erforderlich';





/* Access Logs */
$lang['action_name_req'] ='Name erforderlich';
$lang['action_url_req'] ='url erforderlich';
$lang['action_description_req'] ='Beschreibung der Maßnahme erforderlich';
$lang['access_token_req'] ='Aktions-Token erforderlich';

/*User Module*/
$lang['user_id_req'] ='Benutzerkennung erforderlich';
$lang['user_role_id_req'] ='Benutzerrolle erforderlich';
$lang['first_name_req'] ='Ungültiger Vorname';
$lang['first_name_len'] ='Der Vorname sollte weniger als 40 Zeichen umfassen.';
$lang['last_name_req'] ='Ungültiger Nachname';
$lang['last_name_len'] ='Der Nachname sollte weniger als 40 Zeichen umfassen';
$lang['email_req'] ='E-Mail ist erforderlich';
$lang['email_invalid'] ='Gültige E-Mail eingeben';
$lang['email_wrong'] ='E-Mail existiert nicht';
$lang['email_duplicate'] ='E-Mail existiert bereits';
$lang['email_not_exists'] ='E-Mail existiert nicht';
$lang['password_req'] ='Passwort ist erforderlich';
$lang['password_num_min_len'] ='Das Passwort muss mindestens 8 Zeichen lang sein.';
$lang['password_num_max_len'] ='Das Passwort darf maximal 12 Zeichen lang sein.';
$lang['confirm_password_req'] ='Passwortbestätigung ist erforderlich';
$lang['old_new_password_same'] ='"Altes Passwort und neues Passwort können nicht identisch sein";';
$lang["old_password_not_match"] ='"Falsches altes Passwort";';
$lang["new_password_confirm_password_notmatch"] ='"Neues Passwort und Bestätigungspasswort stimmen nicht überein.";';
$lang['password_changed'] ='Passwort erfolgreich aktualisiert.';
$lang['password_match'] ='Passwort stimmt nicht überein';
$lang['login_error'] ='Falsche Anmeldedaten';
$lang['login_inactive_error'] ='Ihr Konto ist deaktiviert. Bitte kontaktieren Sie Ihren Administrator.';
$lang['user_update'] ='Benutzer erfolgreich aktualisiert.';
$lang['user_add'] ='Benutzer erfolgreich angelegt.';
$lang['phone_num_req'] ='Telefonnummer ist erforderlich';
$lang['phone_num_req'] ='Telefonnummer ist erforderlich';
$lang['phone_num_num'] ='Telefonnummer sollte numerisch sein';
$lang['phone_num_min_len'] ='Die Telefonnummer muss mindestens 7 Ziffern haben.';
$lang['phone_num_max_len'] ='Die Telefonnummer darf maximal 15 Ziffern haben.';
$lang['phone_num_max_len_20'] ='Die Telefonnummer darf maximal 15 Ziffern haben.';
$lang['customer_admin_inactive'] ='Admin erfolgreich inaktiv';
$lang['customer_user_inactive'] ='Benutzer erfolgreich inaktiviert';
$lang['status'] ='Status erforderlich';
$lang['is_manual_password_req'] ='Das Feld Manuelles Passwort ist erforderlich';
$lang['new_password'] ='Das neue Passwort wird an Ihre E-Mail gesendet';
$lang['ldap_failed'] ='LDAP-Authentifizierung fehlgeschlagen';
$lang['downgrade_not_possible'] ='Herabstufung gilt nur für Manager zu Owner';
$lang['upgrade_not_possible'] ='Das Upgrade gilt nur für Owner zu Manager';


/* Customer */
$lang['from_date_req'] ='Von-Datum erforderlich';
$lang['to_date_req'] ='Bis heute erforderlich';
$lang['customer_id_req'] ='Kunden-ID erforderlich';
$lang['company_name_req'] ='Firmenname erforderlich';
$lang['company_address_req'] ='Adresse des Unternehmens erforderlich';
$lang['postal_code_req'] ='Postleitzahl erforderlich';
$lang['postal_code_num'] ='Postleitzahl muss Nummer sein';
$lang['vat_number_req'] ='Umsatzsteuer-Identifikationsnummer erforderlich';
$lang['country_id_req'] ='Länderkennung erforderlich';
$lang['created_by_req'] ='Erstellt von erforderlich';
$lang['updated_by_req'] ='Aktualisiert nach Bedarf';
$lang['company_status_req'] ='Status ist erforderlich';
$lang['customer_add'] ='Kunde erfolgreich hinzugefügt';
$lang['customer_update'] ='Kunde erfolgreich aktualisiert';
$lang['customer_inactive'] ='Kunde erfolgreich inaktiv';
$lang['customer_admin_add'] ='Kundenverwaltung erfolgreich erstellt';
$lang['customer_admin_update'] ='Kundenverwaltung erfolgreich aktualisiert';
$lang['customer_template_failed'] ='Kundenvorlage nicht verknüpft';
$lang['dump_template_failed'] ='Prozedur kann nicht ausgeführt werden';
$lang['provider_id_req'] ='Vertragspartner ID erforderlich';
$lang['provider_name_req'] ='Vertragspartnername erforderlich';
$lang['address_req'] ='Adresse erforderlich';
$lang['contact_no_req'] ='Kontakt-Nr. erforderlich';
$lang['description_req'] ='Beschreibung erforderlich';
$lang['provider_update'] ='Vertragspartner erfolgreich aktualisiert';
$lang['provider_add'] ='Vertragspartner erfolgreich hinzugefügt';
$lang['provider_exists'] ='Vertragspartnername existiert bereits';
$lang['provider_failed'] ='Vertragspartner nicht erstellt';
$lang['user_type_req'] ='Benutzertyp erforderlich';
$lang['invalid_provider'] ='Ungültiger Vertragspartner';
$lang['provider_deleted'] ='Vertragspartner erfolgreich gelöscht';
$lang['provider_having_subtask'] ='Vertragspartner ist bereits der Unteraufgabe zugeordnet';

/* project */
$lang['project_update'] ='Projektinformationen erfolgreich aktualisiert';
$lang['project_id_req'] ='Projekt-ID erforderlich';
$lang['project_add'] ='Projekt erfolgreich erstellt';
$lang['Project_name_req'] ='Projektname erforderlich';
$lang['Project_uniqid_req'] ='Projekt-ID erforderlich';
$lang['project_unique_id_exists'] ='Die Projekt-ID existiert bereits.';
$lang['contract_alrady_link'] ='Bereits mit diesem Projekt verbundener Vertrag';
$lang['provider_alrady_link'] ='Bereits mit diesem Projekt verknüpfter Vertragspartner';
/* Document */
//$lang['document_add'] ='Dokument(e) erfolgreich hinzugefügt';
$lang['document_add'] ='Erfolgreich hinzugefügt';
$lang['reference_id_req'] ='Referenz-ID erforderlich';
$lang['reference_type_req'] ='Referenztyp erforderlich';
$lang['contract_linked'] ='Vertrag erfolgreich verknüpft';
$lang['provider_linked'] ='Vertragspartner erfolgreich verknüpft';
$lang['contract_start_data_is_less'] ='Das Anfangsdatum sollte kleiner als das Enddatum sein';


/* Module */
$lang['module_id_req'] ='Modul-ID erforderlich';
$lang['module_selection_id_req'] ='Ungültige Auswahl';
$lang['module_name_req'] ='Modulname erforderlich';
$lang['module_order_req'] ='Modulbestellung erforderlich';
$lang['module_language_id_req'] ='Modul Sprachkennung erforderlich';
$lang['module_add'] ='Modul erfolgreich hinzugefügt';
$lang['module_not_added'] ='Modul Nicht hinzugefügt';
$lang['module_update'] ='Modul erfolgreich aktualisiert';
$lang['module_inactive'] ='Modul erfolgreich inaktiv';
$lang['module_status_req'] ='Modulstatus erforderlich';
$lang['module_type_req'] ='Erforderlicher Modultyp';
$lang['module_to_avail_template_req'] ='Verfügbare Vorlagen-ID erforderlich';

/* Topic */
$lang['topic_id_req'] ='Themen-ID erforderlich';
$lang['topic_id_selection_req'] ='Ungültige Auswahl';
$lang['topic_name_req'] ='Themenname erforderlich';
$lang['topic_type_req'] ='Themenart erforderlich';
$lang['topic_order_req'] ='Themenauftrag erforderlich';
$lang['topic_language_id_req'] ='Thema Sprachkennung erforderlich';
$lang['topic_add'] ='Thema erfolgreich hinzugefügt';
$lang['topic_update'] ='Thema erfolgreich aktualisiert';
$lang['topic_inactive'] ='Thema erfolgreich inaktiv';
$lang['topic_status_req'] ='Themenstatus erforderlich';

/* Question */
$lang['question_text_req'] ='Fragetext erforderlich';
$lang['question_order_req'] ='Frage Bestellung erforderlich';
$lang['question_type_req'] ='Fragetyp erforderlich';
$lang['option_name_req'] ='Frageoptionen erforderlich erforderlich';
$lang['question_add'] ='Frage erfolgreich hinzugefügt';
$lang['question_update'] ='Frage erfolgreich aktualisiert';
$lang['question_id_req'] ='Frage-ID erforderlich';
$lang['question_answer_req'] ='Frage Antwort erforderlich';
$lang['question_feedback_req'] ='Rückmeldung zur Frage erforderlich';
$lang['question_id_select_req'] ='Ungültige Auswahl';
$lang['question_language_id_req'] ='Frage Sprach-ID erforderlich';
$lang['question_status_req'] ='Frage Status erforderlich';
$lang['id_relationship_category_question_req'] ='Kategorie Frage-ID erforderlich';
$lang['id_relationship_category_req'] ='Kategorie erforderlich';
$lang['updateRelationshipCategories_status_req'] ='Status erforderlich';
$lang['question_data_req'] ='Frage Daten erforderlich';
$lang['enable_category'] ='Sie müssen mindestens eine Kategorie freischalten';

/* Relationship category */
$lang['relationship_category_cannot_downgrade'] ='Vertragspartnerkategorie kann nicht zurückgestuft werden';
$lang['relationship_category_id_req'] ='Vertragspartnerkategorie ID erforderlich';
$lang['relationship_category_language_id_req'] ='Vertragspartnerkategorie Sprach-ID erforderlich';
$lang['relationship_category_name_req'] ='Name der Vertragspartnerkategorie erforderlich';
$lang['relationship_category_quadrant_req'] ='Vertragspartnerkategorie Quadrant erforderlich';
$lang['relationship_category_quadrant_duplicate'] ='Vertragspartnerkategorie Quadrant existiert bereits';
$lang['relationship_category_add'] ='Vertragspartnerkategorie erfolgreich hinzugefügt';
$lang['relationship_category_update'] ='Vertragspartnerkategorie erfolgreich aktualisiert';
$lang['relationship_category_delete'] ='Vertragspartnerkategorie erfolgreich gelöscht';
$lang['relationship_category_id_rey'] ='Vertragspartnerkategorie ID erforderlich';
$lang['relationship_category_status_req'] ='Status der Vertragspartnerkategorie erforderlich';
$lang['classification_name_req'] ='Klassifizierung der Vertragspartner ist erforderlich';
$lang['classification_position_req'] ='Eine Position zur Klassifizierung von Vertragspartner ist erforderlich.';
$lang['is_visible_req'] ='sichtbare Option erforderlich';
$lang['classification_position_duplicate'] ='Es gibt bereits eine Position zur Klassifizierung von Vertragspartnern';
$lang['classification_status_req'] ='Status der Vertragspartnerklassifizierung erforderlich';
$lang['relationship_classification_add'] ='Vertragspartnerklassifizierung erfolgreich hinzugefügt';
$lang['relationship_classification_update'] ='Vertragspartnerklassifizierung erfolgreich aktualisiert';
$lang['relationship_classification_id_req'] ='Vertragspartnerklassifizierung ID erforderlich';
$lang['relationship_classification_language_id_req'] ='Vertragspartner Klassifizierung Sprach-ID erforderlich';
$lang['relationship_classification_delete'] ='Vertragspartnerklassifizierung gelöscht';
$lang['parent_classification_id_req'] ='Klassifizierungs-ID erforderlich';
/* Provider relation ship categeoreis */
$lang['provider_relationship_classification_add'] ='Klassifizierung der Vertragspartnerbeziehungen erfolgreich hinzugefügt';
$lang['provider_relationship_category_add'] ='Kategorie "Vertragspartnerbeziehung" erfolgreich hinzugefügt';
$lang['provider_relationship_category_id_req'] ='Beziehung Vertragspartnerkategorie ID erforderlich';
$lang['Provider_relationship_category_language_id_req'] ='Beziehung Vertragspartnerkategorie Sprach-ID erforderlich';
$lang['provider_relationship_category_status_req'] ='Beziehung Vertragspartnertypenstatus erforderlich';
$lang['provider_relationship_category_update'] ='Relation Vertragspartnerkategorie erfolgreich aktualisiert';
$lang['provider_classification_id_req'] ='Vertragspartner Klassifizierungs-ID erforderlich';
$lang['provider_classification_language_id_req'] ='Vertragspartner Klassifizierungs Sprach-ID erforderlich';
$lang['provider_classification_position_duplicate'] ='Vertragspartner Klassifizierungsposition existiert bereits';
$lang['provider_relationship_category_update'] ='Vertragspartnerklassifizierung erfolgreich aktualisiert';
// $lang['provider_relationship_category_add'] ='Vertragspartner Klassifikation erfolgreich hinzugefügt';
$lang['provider_relationship_classification_add'] ='Vertragspartner Klassifikation erfolgreich hinzugefügt';
$lang['unique_id_exists'] ='ID existiert bereits';
$lang['update_provider'] ='Vertragspartnerinformationen erfolgreich aktualisiert';
$lang['provider_tags_updated'] ='Vertragspartner-Tags erfolgreich aktualisiert';
/* Templates */
$lang['template_id_req'] ='Vorlagen-ID erforderlich';
$lang['template_name_req'] ='Name der Vorlage erforderlich';
$lang['template_name_duplicate'] ='Name der Vorlage existiert bereits';
$lang['template_add'] ='Vorlage erfolgreich hinzugefügt';
$lang['template_update'] ='Vorlage erfolgreich aktualisiert';
$lang['template_module_save'] ='Vorlagenmodul erfolgreich gespeichert';
$lang['template_module_topic_save'] ='Thema des Vorlagenmoduls erfolgreich gespeichert';
$lang['template_module_topic_question_save'] ='Vorlagenmodul Themenfrage erfolgreich gespeichert';
$lang['template_module_id_req'] ='Vorlagenmodul-ID erforderlich';
$lang['template_module_topic_id_req'] ='Themen-ID des Vorlagenmoduls erforderlich';
$lang['template_module_delete'] ='Modul erfolgreich entfernt';
$lang['template_module_topic_id_req'] ='Themen-ID des Vorlagenmoduls erforderlich';
$lang['template_module_topic_question_id_req'] ='Vorlagenmodul Themenfrage-ID erforderlich';
$lang['template_module_topic_delete'] ='Thema erfolgreich entfernt.';
$lang['template_module_topic_question_delete'] ='Frage erfolgreich entfernt';
$lang['template_clone'] ='Vorlage erfolgreich geklont';

/* Settings update */
$lang['settings_update'] ='Einstellungen erfolgreich aktualisiert.';

/* Business Unit */
$lang['bu_name_req'] ='Firmenname erforderlich';
$lang['bu_responsibility_req'] ='Geschäftliche Verantwortung erforderlich';
$lang['business_unit_id_req'] ='ID der Geschäftseinheit erforderlich';
$lang['business_unit_create'] ='Geschäftseinheit erfolgreich erstellt';
$lang['business_unit_update'] ='Geschäftseinheit erfolgreich aktualisiert';

/* Contract */
$lang['contract_name_req'] ='Name des Vertrags erforderlich';
$lang['contract_id_req'] ='Vertragskennung erforderlich';
$lang['contract_review_id_req'] ='Vertragsprüfung erforderlich';
$lang['contract_owner_id_req'] ='Vertrags-Owner erforderlich';
$lang['contract_start_date_req'] ='Erforderliches Datum für den Vertragsbeginn';
$lang['contract_end_date_req'] ='Datum des Vertragsendes erforderlich';
$lang['contract_value_req'] ='Erforderlicher Auftragswert';
$lang['currency_id_req'] ='Währungskennung erforderlich';
$lang['id_contract_req'] ='Vertragskennung erforderlich';
$lang['contract_add'] ='Vertrag erfolgreich hinzugefügt';
$lang['contract_update'] ='Vertragsinformationen erfolgreich aktualisiert';
$lang['contract_tags_update'] ='Vertrags-Tags erfolgreich aktualisiert';
$lang['stakeholder_update'] ='Stakeholder erfolgreich aktualisiert';
$lang['stakeholder_not_update'] ='Stakeholder nicht aktualisiert';
$lang['contract_start_date_invalid'] ='Das Datum des Vertragsbeginns ist ungültig';
$lang['contract_end_date_invalid'] ='Vertragsenddatum ist ungültig';
$lang['contract_delegate_id_req'] ='Vertragsbevollmächtigter erforderlich';
$lang['contract_description_req'] ='Beschreibung des Vertrags erforderlich';
$lang['workflow_initiate'] ='Task erfolgreich gestartet.';
$lang['review_initiate'] ='Review erfolgreich eingeleitet.';
$lang['review_finalize'] ='Das Review wurde erfolgreich abgeschlossen.';
$lang['workflow_finalize'] ='Die Task wurde erfolgreich abgeschlossen.';
$lang['contract_delete'] ='Der Vertrag wurde erfolgreich gelöscht.';
$lang['contract_undo'] ='Vertrag erfolgreich wiederhergestellt.';
$lang['validate_initiate'] ='Die Validierung wurde erfolgreich eingeleitet.';
$lang['validate_completed'] ='Validierung erfolgreich abgeschlossen.';
$lang['export_type_req'] ='Exporttyp erforderlich.';
$lang['workflow_added'] ='Task zum Kalender hinzugefügt.';
$lang['contract_unique_id_alredy_ext'] ='Die Vertrags-ID existiert bereits. Bitte versuchen Sie es erneut';
$lang['template_should_be_locked_by_customer_admin_only'] ='Vorlage sollte nur vom Kunden-Admin gesperrt werden';
$lang['template_should_be_locked_or_unlocked_by_customer_admin_only'] ='Vorlage sollte nur vom Kundenadministrator gesperrt oder entsperrt werden';
$lang['contract__active_status_required'] ='Status erforderlich';
/*Contract review items*/
$lang['action_item_req'] ='Erforderliche Maßnahme';
$lang['responsible_user_id_req'] ='Verantwortlicher Benutzer erforderlich';
$lang['due_date_req'] ='Erforderliches Fälligkeitsdatum';
$lang['contract_review_action_item_add'] ='Ad-hoc Aufgabe erfolgreich hinzugefügt';
$lang['contract_review_action_item_update'] ='Ad-hoc Aufgabe erfolgreich aktualisiert';
$lang['contract_review_action_item_delete'] ='Ad-hoc Aufgabe erfolgreich gelöscht';
$lang['contract_review_id'] ='ID für Vertragsprüfung erforderlich';
$lang['id_contract_review_action_item_req'] ='Ad-hoc Aufgaben-ID erforderlich';
$lang['no_review_access'] ='Sie haben keinen Zugang zu diesem Beitrag, bitte gehen Sie weiter';

/*Document */
$lang['document_id_req'] ='Dokumenten-ID erforderlich';
$lang['document_delete'] ='Dokument erfolgreich gelöscht';
$lang['document_unlocked'] ='Dokument erfolgreich entriegelt';
$lang['document_locked'] ='Dokument erfolgreich gesperrt';
$lang['no_link_added'] ='Kein Link hinzugefügt';

/*Calender*/
$lang['calender_id_req'] ='Kalender-ID erforderlich';
$lang['provider_id_req'] ='"Vertragspartner-ID erforderlich.";';
$lang['recurrence_till_req'] ='"Wiederholung bis Datum erforderlich";';
$lang['recurrence_req'] ='"Wiederholung erforderlich.";';
$lang['review_added_to_calender'] ='"Review zum Kalender hinzugefügt";';
$lang['contract_not_unlock'] ='Verträge werden nicht entsperrt';
$lang['workflow_name_req'] ='Task-Name erforderlich';
$lang['workflow_not_created'] ='Task nicht erstellt';
$lang['workflow_not_updated'] ='Task nicht aktualisiert';
$lang['calender_not_created'] ='Kalender nicht erstellt';


/* Mails */
$lang['mail_footer'] ='<p style="color:#8e8e8e;font-size:10px">Wenn Sie sich nicht sicher sind, worum es hier geht, können Sie diese Nachricht ignorieren. Haben Sie Fragen? Brauchen Sie Hilfe?
                <a href="mailto:support@with-services.com" style="color:#74a6f9; text-decoration:none">Contact our support team </a> and we’ll get back to you in just a few minutes - promise</p>';
$lang['id_email_template_req'] ='E-Mail-Vorlagen-ID erforderlich.';
$lang['invalid_id_email_template'] ='Die ID der E-Mail-Vorlage ist nicht gültig.';
$lang['email_status_req'] ='E-Mail-Vorlagen-Status ist erforderlich.';
$lang['id_email_template_language_req'] ='Die Sprache der E-Mail-Vorlage ist erforderlich.';
$lang['email_template_name_req'] ='Der Name der E-Mail-Vorlage ist erforderlich.';
$lang['email_template_subject_req'] ='Der Betreff der E-Mail-Vorlage ist erforderlich';
$lang['email_template_content_req'] ='Der Inhalt der E-Mail-Vorlage ist erforderlich';

//forget password
$lang['forget_password_subject'] ='Passwort-Wiederherstellung';
$lang['forget_password_mail'] ='<p>Liebe<i>{first_name} {last_name}</i>Ihr neu generiertes Passwort lautet<b>{password}</b><br><b>Hinweis: Es wird empfohlen, Ihr Passwort beim nächsten Mal zu aktualisieren, wenn Sie sich anmelden.</b></p>';
$lang['host_req'] ='Host erforderlich';
$lang['dc_req'] ='DC erforderlich';
$lang['port_req'] ='Port erforderlich';
$lang['not_updated'] ='Aktualisierung fehlgeschlagen';
$lang['not_inserted'] ='Einfügen fehlgeschlagen';
$lang['inserted'] ='LDAP-Details hinzugefügt';
$lang['updated'] ='LDAP-Details aktualisiert';
$lang['status_req'] ='LDAP-Status erforderlich';

/* for customer admin */
$lang['customer_admin_create_subject'] ='Konto erstellt';
$lang['customer_admin_create_message'] ='<p>Liebe<i>{first_name} {last_name}</i>, Ihr{role} ausmachen{customer_name} erfolgreich erstellt. Ihre Anmeldedaten sind<br><b>E-Mail:</b>{email} <br><b>Kennwort:</b>{password}<br><b>Hinweis: Es wird empfohlen, Ihr Passwort beim nächsten Mal zu aktualisieren, wenn Sie sich anmelden.</b></p>';

/* for customer admin */
$lang['customer_user_create_subject'] ='Konto erstellt';
$lang['customer_user_create_message'] ='<p>Liebe<i>{first_name} {last_name}</i>, Ihr{role} ausmachen{customer_name} erfolgreich erstellt. Ihre Anmeldedaten sind<br><b>E-Mail:</b>{email} <br><b>Kennwort:</b>{password}<br><b>Hinweis: Es wird empfohlen, Ihr Passwort beim nächsten Mal zu aktualisieren, wenn Sie sich anmelden.</b></p>';

//forget password
$lang['reset_password_subject'] ='Geändertes Passwort';
$lang['reset_password_mail'] ='<p>Liebe<i>{first_name} {last_name}</i>Ihr Passwort wurde vom Admin geändert. Ihr neu generiertes Passwort lautet<b>{password}</b><br><b>Hinweis: Es wird empfohlen, Ihr Passwort beim nächsten Mal zu aktualisieren, wenn Sie sich anmelden.</b></p>';
$lang['id_user_req'] ='Benutzerkennung erforderlich';
$lang['user_validations_will_be_gone'] ='Der Benutzer wurde als Prüfer entfernt!';


$lang['review_discussion_initiate_success'] ='Die Diskussion des Reviews wurde eingeleitet.';
$lang['review_discussion_save_success'] ='Die Diskussion des Reviews wurde gespeichert.';
$lang['review_discussion_close_success'] ='Die Diskussion des Reviews wurde abgeschlossen.';
$lang['question_id_req'] ='Fragen-ID erforderlich.';
$lang['comments_required'] ='Kommentare erforderlich.';
$lang['module_id_req'] ='Modul-ID erforderlich.';
$lang['second_opinion_req'] ='Zweitmeinung erforderlich.';
$lang['save_question_answere'] ='Bitte speichern Sie zuerst die Antworten.';



//report
$lang['report_delete']='Bericht erfolgreich gelöscht.';
$lang['report_save']='Der Bericht wurde erfolgreich gespeichert.';
$lang['id_report_req']='Bericht-ID erforderlich.';
$lang['report_save_type']='Berichtsspeichertyp erforderlich.';
$lang['report_name_req']='Name des Berichts erforderlich.';
$lang['report_contracts']='Bericht Verträge erforderlich.';
$lang['report_classification_id_req'] ='Vertragspartnerklassifizierung erforderlich.';
$lang['latest_review_from_date_req'] ='Von-Datum erforderlich.';
$lang['latest_review_to_date_req'] ='Bis-Datum erforderlich.';

$lang['from_date_req'] ='Von-Datum erforderlich.';
$lang['to_date_req'] ='Bis-Datum erforderlich.';
$lang['type_req'] ='Typ erforderlich.';

$lang['permission_not_allowed'] ='"Unbefugter Zugriff";';
$lang['user_already_assigned_with'] ='"Dieser Benutzer ist bereits einem oder mehreren Verträgen zugeordnet";';
$lang['module_url_req'] ='"Modul-URL erforderlich.";';

//tags
$lang['tag_add'] ='"Tag erfolgreich hinzugefügt.";';
$lang['tag_text_req'] ='"Tag Text benötigt";';
$lang['can_update_fixed_tag_only'] ='"Can Update fixed tag only";';
$lang['tag_type_req'] ='"Tag Type benötigt.";';
$lang['tag_error'] ='"Sie können nicht mehr als 72 aktive Tags erstellen";';
$lang['tag_update'] ='"Tag erfolgreich aktualisiert.";';
$lang['tag_req'] ='"Tag-Daten erforderlich.";';

//spent mngmt
$lang['spent_line_add_success'] ='"Ausgabenlinie erfolgreich hinzugefügt.";';
$lang['spent_line_update_success'] ='"Ausgabenlinie erfolgreich aktualisiert.";';
$lang['spent_info_add_success'] ='"Ausgabeninformation erfolgreich hinzugefügt.";';
$lang['spent_info_update_success'] ='"Ausgabeninformationen erfolgreich aktualisiert";';
$lang['contract_value_period_req'] ='"Erforderlicher Zeitraum für den Auftragswert";';
$lang['additional_recurring_fees_req'] ='"Zusätzliche wiederkehrende Gebühren erforderlich.";';
$lang['additional_recurring_fees_period_req'] ='"Zusätzlicher Zeitraum für wiederkehrende Gebühren erforderlich.";';
$lang['additonal_one_off_fees_req'] ='"Zusätzliche einmalige Gebühren erforderlich.";';
$lang['spent_period_req'] ='"Verwendeter Zeitraum erforderlich";';
$lang['spent_amount_req'] ='"Ausgegebener Betrag erforderlich";';
$lang['contract_value_req'] ='"Voraussichtlicher Wert erforderlich.";';
$lang['spent_line_id_req'] ='"Ausgabenlinien-ID erforderlich";';
$lang['same_not_possible'] ='"Zuordnung von Unterargumenten zum eigenen Vertrag ist nicht möglich";';


//Missileneous
$lang['id_stored_module_req'] ='"Modul-ID speichern erforderlich";';
$lang['activate_in_next_review_req'] ='"Aktivierungsstatus ist erforderlich.";';
$lang['from_date_should_be_less_than_to_date'] ='Das "Von-Datum" sollte kleiner sein als das "Bis-Datum";';
$lang['tabs_order_changed_successfully'] ='"Registerkartenreihenfolge erfolgreich geändert";';

//service Catalogue
$lang['catalogue_item_req'] ='"Katalogeintrag erforderlich.";';
$lang['service_catalogue_add_success'] ='"Dienstkatalog erfolgreich hinzugefügt";';
$lang['service_catalogue_update_success'] ='"Dienstkatalog erfolgreich aktualisiert";';
$lang['service_catalogue_deleted_successfully'] ='"Dienstkatalog erfolgreich gelöscht";';
$lang['service_catalogue_id_req'] ='"Dienstkatalog-ID erforderlich";';
$lang['period_start_date_should_be_less_than_period_end_date'] ='"Das Anfangsdatum der Periode sollte kleiner sein als das Enddatum der Periode";';

//Obligations and rights
$lang['no_of_days_req'] ='"Anzahl der benötigten Tage";';
$lang['logic_req'] ='"Logik erforderlich.";';
$lang['notification_message_req'] ='"Benachrichtigungsmeldung erforderlich.";';
$lang['resend_recurrence_req'] ='"Wiederholung erforderlich.";';
$lang['email_send_last_date_req'] ='"Enddatum für die erneute Versendung von E-Mails erforderlich";';
$lang['start_date_be_less_than_end_date'] ='"Der Starttag der E-Mail-Benachrichtigung sollte kleiner sein als das Enddatum der E-Mail-Benachrichtigung";';
$lang['recurrence_start_date_should_be_empty'] ='"Das Datum des Beginns der Wiederholung sollte leer sein";';
$lang['recurrence_end_date_should_be_empty'] ='"Das Enddatum der Wiederholung sollte leer sein";';
$lang['calender_should_be_off'] ='"Der Kalender sollte inaktiv sein";';
$lang['recurrence_start_date_should_not_be_empty'] ='"Das Datum des Beginns der Wiederholung sollte nicht leer sein";';
$lang['recurrence_end_date_should_not_be_empty'] ='"Das Enddatum der Wiederholung sollte nicht leer sein";';
$lang['recurrence_start_date_should_be_less_than_recurrence_end_date'] ='"Das Anfangsdatum der Wiederholung sollte kleiner sein als das Enddatum der Wiederholung";';
$lang['email_send_start_date_should_be_less_than_email_send_last_date'] ='"Das Datum sollte kleiner sein als das Enddatum für die erneute Versendung von E-Mails;';
$lang['obligation_id_req'] ='"Vertragsverpflichtungs-ID benötigt";';
$lang['deleted_successfully'] ='"Erfolgreich gelöscht";';
$lang['right_add_success'] ='"Vertragsrecht erfolgreich hinzugefügt";';
$lang['obligation_add_success'] ='"Vertragsverpflichtung erfolgreich hinzugefügt";';
$lang['right_updated_success'] ='"Vertragsrecht erfolgreich aktualisiert";';
$lang['obligation_updated_success'] ='"Vertragsverpflichtung erfolgreich aktualisiert";';
$lang['obligation_and_right_updated_success'] ='"Pflichten und Rechte erfolgreich aktualisiert";';
$lang['obligation_and_right_added_success'] ='"Pflichten und Rechte erfolgreich hinzugefügt";';
$lang['deleted_sucessfully'] ='"Pflichten und Recht erfolgreich gelöscht";';
//////////////////currency//////////////////
$lang['currency_name_req'] ='"Währungsname erforderlich";';
$lang['currency_code_req'] ='"Währungscode erforderlich";';
$lang['currency_added'] ='"Zusätzliche Währung erfolgreich erstellt";';
$lang['main_currency_updated'] ='"Hauptwährung erfolgreich aktualisiert";';
$lang['additional_currency_updated'] ='"zusätzliche Währung erfolgreich aktualisiert";';
$lang['exchange_rate_is_numaric'] ='"Der Wechselkurs darf nur eine Zahl sein";';
////////////////////////////////////////////document intelligence////////////
$lang['int_temp_name_req'] ='"Name der Vorlage erforderlich";';
$lang['customer_req'] ='"Kunde ist erforderlich";';
$lang['template_add'] ='"Vorlage erfolgreich erstellt";';
$lang['intelligence_template_id_req'] ='"Intelligenzvorlagen-ID erforderlich";';
$lang['template_update'] ='"Vorlage erfolgreich aktualisiert";';
$lang['field_name_req'] ='"Feldname ist erforderlich";';
$lang['field_type_req'] ='"Feldtyp ist erforderlich";';
$lang['question_req'] ='"Feldname ist erforderlich";';
$lang['template_question_add'] ='"Vorlagenfrage erfolgreich erstellt";';
$lang['intelligence_template_field_id_req'] ='"Vorlagenfrage-ID erforderlich";';
$lang['question_deleted'] ='"Vorlagenfrage erfolgreich gelöscht";';
$lang['template_question_update'] ='"Vorlagenfrage erfolgreich aktualisiert";';
$lang['file_size_execed_one_mb'] ='"Die Größe der hochgeladenen Datei sollte nicht mehr als ein Megabyte betragen";';
$lang['only_pdf_files'] ='"Nur PDF-Dateien hochladen";';
$lang['owner_req'] ='"Eigentümer-ID erforderlich";';
$lang['delegate_req'] ='"Stellvertreter-ID erforderlich";';
$lang['document_inteligence_create'] ='"Dokumenten-Intelligenz erfolgreich erstellt";';
$lang['document_inteligence_update'] ='"Dokumenten-Intelligenz erfolgreich aktualisiert";';
$lang['document_inteligence_delete'] ='"Dokumenten-Intelligenz erfolgreich gelöscht";';
$lang['document_inteligence_id_req'] ='"Dokumenten-Intelligenz-ID erforderlich";';
$lang['unable_delete_doc_int'] ='Das Dokument "Intelligenz" kann nicht gelöscht werden, bis die Vertragserstellung abgeschlossen ist;';
$lang['parent_contract_id_req'] ='"Parent-ID erforderlich";';
$lang['child_contract_id_req'] ='"Child Contract id required";';
$lang['sub_arg_mapped'] ='"Unterabkommen erfolgreich abgebildet";';
$lang['sub_arg_un_mapped'] ='"Untervereinbarungen nicht erfolgreich zugeordnet";';
$lang['signle_contracts_only_mpped'] ='"Nur die Verknüpfung einzelner Verträge als Unterverträge";';
$lang['validate_answers_req'] ='"Validierung der Antworten ist erforderlich";';
$lang['validation_saved_successfully'] ='"Validierung erfolgreich gespeichert";';
$lang['attachment_path_required'] ='"Dokumentenpfad der Anhänge erforderlich";';
$lang['path_does_not_exist'] ='"Dokumentenpfad existiert nicht";';
$lang['moved_successfully'] ='"Dokument erfolgreich verschoben";';
$lang['something_went_wrong'] ='"Etwas ist schief gelaufen";';
$lang['cant_submit_validation'] ='"Validierung kann nicht eingereicht werden";';
$lang['validation_submitted_successfully'] ='"Validierung erfolgreich durchgeführt";';
$lang['process_completed_successfuly'] ='"Prozess erfolgreich abgeschlossen";';
$lang['first_submit_validation'] ='"Validierung der ersten Übermittlung";';
$lang['document_name_required'] ='"Dokumentenname erforderlich";';
$lang['please_select_file'] ='"Bitte Datei auswählen";';
///////////////Advanced filters///////////////////
$lang['domain_module_req'] ='"Domänenmodul erforderlich";';
$lang['id_master_domain_req'] ='"Master-Domain-ID erforderlich";';
$lang['condition_req'] ='"Bedingung erforderlich";';
$lang['value_req'] ='"Wert erforderlich";';
$lang['master_domain_field_id_req'] ='"Master-Domänenfeld-ID erforderlich";';
$lang['filter_updated_successfully'] ='"Filter erfolgreich aktualisiert";';
$lang['filter_added_successfully'] ='"Filter erfolgreich hinzugefügt";';
$lang['id_master_filter_req'] ='"Filter-ID erforderlich";';
$lang['filter_deleted_successfully'] ='"Filter erfolgreich gelöscht";';
$lang['contribution_type_req'] ='"Beitragsart erforderlich";';

$lang['contract_workflow_id_req'] ='"Vertrags-Workflow-Kennung erforderlich";';
$lang['sub_task_does_not_exist'] ='"Unteraufgabe existiert nicht";';
$lang['sub_task_linked_to_contract'] ='"Teilaufgabe in Verbindung mit Verträgen";';
$lang['sub_task_mapped'] ='"Teilaufgabe erfolgreich zugeordnet";';
$lang['sso_check_req'] ='"URL SSO-Prüfung erforderlich";';
$lang['issuer_url_req'] ='"Emittenten-URL / Entity ID erforderlich";';
$lang['certificate_req'] ='"X.509-Zertifikat erforderlich";';
$lang['is_email_verification_active_req'] ='"E-Mail-Überprüfung ist erforderlich";';
$lang['is_mfa_active_req'] ='"MFA ist erforderlich";';
$lang['email_verification_should_active'] ='"E-Mail-Überprüfung sollte aktiv sein";';
$lang['saml_inserted'] ='SAML-Details hinzugefügt';
$lang['saml_updated'] ='SAML-Details aktualisiert';
$lang['mfa_updated'] ='MFA-Details aktualisiert';
$lang['verification_code_sent_successfully'] ='Überprüfungscode erfolgreich gesendet';
$lang['verification_code_req'] ='Verifizierungscode erforderlich';
$lang['verification_method_req'] ='Erforderliche Überprüfungsmethode';
$lang['invalid_verification_code'] ='Ungültiger Verifizierungscode';
$lang['verification_code_expired'] ='Verifizierungscode ist abgelaufen';


//satic messages
$lang['no_reviews_found'] ='Keine Bewertungen gefunden';
$lang['project_deleted_successfully'] ='Das Projekt wurde erfolgreich gelöscht.';
$lang['contract_deleted_successfully'] ='Der Vertrag wurde erfolgreich gelöscht.';
$lang['operation_failed'] ='Operation fehlgeschlagen';
$lang['has_a_conflict'] ='hat einen Konflikt';
$lang['file_is_not_in_pdf_format'] ='Datei ist nicht im pdf-Format';
$lang['upload_limited_to_max_20_files_at_once'] ='Upload begrenzt auf max. 20 Dateien auf einmal';
$lang['upload_at_least_one_file'] ='Mindestens eine Datei hochladen';
$lang['not_allowed'] ='Nicht erlaubt';
$lang['not_allowed_to_access'] ='Der Zugriff ist nicht erlaubt.';
$lang['file_not_found'] ='Datei nicht gefunden';
$lang['the_file_you_requested_are_not_found'] ='Die von Ihnen angeforderte Datei wurde nicht gefunden.';
$lang['subtask_already_mapped_to_contract'] ='Teilaufgabe bereits dem Vertrag zugeordnet';
$lang["you_dont_have_permissions_to_this_module"] ='"Sie haben keine Berechtigung für dieses Modul";';
$lang['contributor_updated_successfully'] ='Mitwirkende erfolgreich aktualisiert.';



//event Feeds
$lang['reference_type_req'] ='Referenztyp erforderlich';
$lang['reference_id_req'] ='Referenz-ID erforderlich';
$lang['subject_req'] ='Thema erforderlich';
$lang['event_feed_added_successfully'] ='Ereignis-Feed erfolgreich hinzugefügt';
$lang['event_feed_updated_successfully'] ='Ereignis-Feed erfolgreich aktualisiert';
$lang['event_feed_id_req'] ='Ereignis-Feed-ID erforderlich';
$lang['event_feed_deleted_sucessfully'] ='Ereignis-Feed erfolgreich gelöscht';


$lang['verification_code_expired'] = 'Verifizierungscode verfallen';
$lang['language_id_req'] = 'Sprache ID erforderlich';
$lang['contract_information'] = 'Vertragsinformationen';
$lang['contract_tags'] = 'Vertrags-Tags';
$lang['action_items'] = 'Ad-hoc Aufgaben';
$lang['obligations_rights'] = 'Pflichten und Rechte';
$lang['service_catalogue'] = 'Servicekatalog';
$lang['contract_event_feed'] = 'Ereignisfeed';
$lang['sub_agreements'] = 'Unterverträge';
$lang['tabs_order_changed_sucessfully'] = 'Reihenfolge der Registerkarten erfolgreich geändert';
$lang['contract_value'] = 'Vertragswert';
$lang['invoices'] = 'Rechnungen';
$lang['catalogue_value'] = 'Katalogwert';

$lang['urgent'] = 'Dringend';
$lang['medium'] = 'Mittel';
$lang['low'] = 'Niedrig';
$lang['not_classified'] = 'Nicht klassifiziert';
$lang['expert'] = 'Experte';
$lang['validator'] = 'Prüfer';
$lang['relation'] = 'Vertragspartner';
$lang['green'] = 'Grün';
$lang['red'] = 'Rot';
$lang['amber'] = 'Gelb';
$lang['n_a'] = 'k. A.';
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

