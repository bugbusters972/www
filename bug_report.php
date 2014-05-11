<?php
	require_once( 'core.php' );

	require_once( 'string_api.php' );
	require_once( 'file_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );

	form_security_validate( 'bug_report' );

	$t_project_id = null;
	$f_master_bug_id = gpc_get_int( 'm_id', 0 );
	if ( $f_master_bug_id > 0 ) {
		bug_ensure_exists( $f_master_bug_id );
		if ( bug_is_readonly( $f_master_bug_id ) ) {
			error_parameters( $f_master_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}
		$t_master_bug = bug_get( $f_master_bug_id, true );
		project_ensure_exists( $t_master_bug->project_id );
		access_ensure_bug_level( config_get( 'update_bug_threshold', null, null, $t_master_bug->project_id ), $f_master_bug_id );
		$t_project_id = $t_master_bug->project_id;
	} else {
		$f_project_id = gpc_get_int( 'project_id' );
		project_ensure_exists( $f_project_id );
		$t_project_id = $f_project_id;
	}
	if ( $t_project_id != helper_get_current_project() ) {
		$g_project_override = $t_project_id;
	}

	access_ensure_project_level( config_get('report_bug_threshold' ) );

	$t_bug_data = new BugData;
	$t_bug_data->project_id             = $t_project_id;
	$t_bug_data->reporter_id            = auth_get_current_user_id();
	$t_bug_data->build                  = gpc_get_string( 'build', '' );
	$t_bug_data->nom               = strtoupper (gpc_get_string( 'nom', '' ));
	$t_bug_data->nomepouse               = strtoupper (gpc_get_string( 'nomepouse', '' ));
	$t_bug_data->prenom                     = gpc_get_string( 'prenom', '' );
	$t_bug_data->telephone               = preg_replace('/\s+/', '',gpc_get_string( 'telephone', '' ));
	$t_bug_data->version                = gpc_get_string( 'product_version', '' );
	$t_bug_data->profile_id             = gpc_get_int( 'profile_id', 0 );
	$t_bug_data->handler_id             = gpc_get_int( 'handler_id', 0 );
	$t_bug_data->view_state             = gpc_get_int( 'view_state', config_get( 'default_bug_view_status' ) );
	#$t_bug_data->category_id            = gpc_get_int( 'category_id', 0 );
	$t_bug_data->category_id            = 2;
	$t_bug_data->sexe               = gpc_get_int( 'sexe', config_get( 'default_bug_sexe' ) );
	#$t_bug_data->motif_contact               = gpc_get_int( 'motif_contact', config_get( 'default_bug_motif_contact' ) );
	$t_bug_data->projection             = gpc_get_int( 'projection', config_get( 'default_bug_projection' ) );
	$t_bug_data->eta                    = gpc_get_int( 'eta', config_get( 'default_bug_eta' ) );
	$t_bug_data->resolution             = gpc_get_string('resolution', config_get( 'default_bug_resolution' ) );
	$t_bug_data->status                 = gpc_get_string( 'status', config_get( 'bug_submit_status' ) );
	$t_bug_data->summary                = 'null';
	$t_bug_data->description            = gpc_get_string( 'description','pas de commentaire.' );
	$t_bug_data->motif_nvvenue     = gpc_get_int( 'motif_nvvenue', '' );
	$t_bug_data->date_nvvenue = gpc_get_int( 'date_nvvenue', db_now());
	$t_bug_data->rapporteur = auth_get_current_user_id();
	$t_bug_data->type_contact        = gpc_get_int( 'type_contact', config_get( 'default_bug_type_contact' ) );
	$t_bug_data->due_date               = gpc_get_string( 'due_date', '');
	#nouveaux champs
	#$t_bug_data->demande_rappel = gpc_get_int( 'demande_rappel', config_get( 'default_bug_demande_rappel'));
$t_bug_data->attente = gpc_get_int( 'attente', config_get( 'default_bug_attente'));
$t_bug_data->date_naissance = encDt( gpc_get_string( 'date_naissance', ''));
$t_bug_data->lieu_naissance = gpc_get_string( 'lieu_naissance', config_get( 'default_bug_lieu_naissance'));
$t_bug_data->pays_naissance = gpc_get_string( 'pays_naissance', config_get( 'default_bug_lieu_naissance'));
$t_bug_data->nationalite = gpc_get_int( 'nationalite', config_get( 'default_bug_nationalite'));
$t_bug_data->rue = gpc_get_string( 'rue', config_get( 'default_bug_rue'));
$t_bug_data->adrs_suite = gpc_get_string( 'adrs_suite', config_get( 'default_bug_adrs_suite'));
$t_bug_data->ville = gpc_get_string( 'ville', config_get( 'default_bug_ville'));
#$t_bug_data->region = gpc_get_string( 'region', config_get( 'default_bug_region'));
$t_bug_data->code_postal = gpc_get_int( 'code_postal', config_get( 'default_bug_code_postal'));
$t_bug_data->pays = gpc_get_int( 'pays', config_get( 'default_bug_pays'));
$t_bug_data->autre_hebergt = gpc_get_string( 'autre_hebergt', config_get( 'default_bug_autre_hebergt'));
$t_bug_data->email = gpc_get_string( 'email', config_get( 'default_bug_email'));
$t_bug_data->tel_domicile = gpc_get_int( 'tel_domicile', config_get( 'default_bug_tel_domicile'));
$t_bug_data->tel_travail = gpc_get_int( 'tel_travail', config_get( 'default_bug_tel_travail'));
$t_bug_data->orig_orient = gpc_get_string( 'orig_orient', config_get( 'default_bug_orig_orient'));
$t_bug_data->connu_assoc = gpc_get_int( 'connu_assoc', config_get( 'default_bug_connu_assoc'));
$t_bug_data->nom_logem = gpc_get_int( 'nom_logem', config_get( 'default_bug_nom_logem'));
$t_bug_data->type_logem = gpc_get_int( 'type_logem', config_get( 'default_bug_type_logem'));
$t_bug_data->complem_info = gpc_get_string( 'complem_info', config_get( 'default_bug_complem_info'));
$t_bug_data->matrim_ant = gpc_get_int( 'matrim_ant', config_get( 'default_bug_matrim_ant'));
$t_bug_data->matrim = gpc_get_int( 'matrim', config_get( 'default_bug_matrim'));
$t_bug_data->nb_enfant = gpc_get_int( 'nb_enfant', config_get( 'default_bug_nb_enfant'));
$t_bug_data->nb_enf_charge = gpc_get_int( 'nb_enf_charge', config_get( 'default_bug_nb_enf_charge'));
$t_bug_data->sxage_enf = gpc_get_string( 'sxage_enf', config_get( 'default_bug_sxage_enf'));
$t_bug_data->enf_pere = gpc_get_int( 'enf_pere', config_get( 'default_bug_enf_pere'));
$t_bug_data->enf_recon = gpc_get_int( 'enf_recon', config_get( 'default_bug_enf_recon'));
$t_bug_data->sit_prof = gpc_get_int( 'sit_prof', config_get( 'default_bug_sit_prof'));
$t_bug_data->niv_scolr = gpc_get_int( 'niv_scolr', config_get( 'default_bug_niv_scolr'));
$t_bug_data->emploi = gpc_get_string( 'emploi', config_get( 'default_bug_emploi'));
$t_bug_data->entreprise = gpc_get_string( 'entreprise', config_get( 'default_bug_entreprise'));
$t_bug_data->cddcdi = gpc_get_int( 'cddcdi', config_get( 'default_bug_cddcdi'));
$t_bug_data->typ_contrat = gpc_get_int( 'typ_contrat', config_get( 'default_bug_typ_contrat'));
$t_bug_data->secteur_activ = gpc_get_int( 'secteur_activ', config_get( 'default_bug_secteur_activ'));
$t_bug_data->type_ent = gpc_get_int( 'type_ent', config_get( 'default_bug_type_ent'));
$t_bug_data->csp = gpc_get_int( 'csp', config_get( 'default_bug_csp'));
$t_bug_data->ressources = gpc_get_int( 'ressources', config_get( 'default_bug_ressources'));
$t_bug_data->prestations = gpc_get_string( 'prestations', config_get( 'default_bug_prestations'));
$t_bug_data->mere_victime = gpc_get_int( 'mere_victime', config_get( 'default_bug_mere_victime'));
$t_bug_data->plaintes = gpc_get_int( 'plaintes', config_get( 'default_bug_plaintes'));
$t_bug_data->nb_plaintes = gpc_get_int( 'nb_plaintes', config_get( 'default_bug_nb_plaintes'));
$t_bug_data->nb_plaintesko = gpc_get_int( 'nb_plaintesko', config_get( 'default_bug_nb_plaintesko'));
$t_bug_data->plaintes_gend = gpc_get_int( 'plaintes_gend', config_get( 'default_bug_plaintes_gend'));
$t_bug_data->nb_plaintesgend = gpc_get_int( 'nb_plaintesgend', config_get( 'default_bug_nb_plaintesgend'));
$t_bug_data->nb_plainteskogend = gpc_get_int( 'nb_plainteskogend', config_get( 'default_bug_nb_plainteskogend'));
$t_bug_data->maincour = gpc_get_int( 'maincour', config_get( 'default_bug_maincour'));
$t_bug_data->nb_maincour = gpc_get_int( 'nb_maincour', config_get( 'default_bug_nb_maincour'));
$t_bug_data->suite_plaintes = gpc_get_string( 'suite_plaintes', config_get( 'default_bug_suite_plaintes'));
$t_bug_data->deja_vict = gpc_get_int( 'deja_vict', config_get( 'default_bug_deja_vict'));
$t_bug_data->vict_enfce = gpc_get_int( 'vict_enfce', config_get( 'default_bug_vict_enfce'));
$t_bug_data->contacte = gpc_get_int( 'contacte', config_get( 'default_bug_contacte'));
$t_bug_data->note_general = gpc_get_string( 'note_general', config_get( 'default_bug_note_general'));
#fin nouveaux champs
	
	if ( is_blank ( $t_bug_data->due_date ) ) {
		$t_bug_data->due_date = date_get_null();
	}

	$f_files                            = gpc_get_file( 'ufile', null ); /** @todo (thraxisp) Note that this always returns a structure */
	$f_report_stay                      = gpc_get_bool( 'report_stay', false );
	$f_copy_notes_from_parent           = gpc_get_bool( 'copy_notes_from_parent', false);
	$f_copy_attachments_from_parent     = gpc_get_bool( 'copy_attachments_from_parent', false);

	if ( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id ) ) {
		$t_bug_data->target_version = gpc_get_string( 'target_version', '' );
	}

	# if a profile was selected then let's use that information
	if ( 0 != $t_bug_data->profile_id ) {
		if ( profile_is_global( $t_bug_data->profile_id ) ) {
			$row = user_get_profile_row( ALL_USERS, $t_bug_data->profile_id );
		} else {
			$row = user_get_profile_row( $t_bug_data->reporter_id, $t_bug_data->profile_id );
		}

		if ( is_blank( $t_bug_data->nom ) ) {
			$t_bug_data->nom = $row['nom'];
		}
		if ( is_blank( $t_bug_data->prenom ) ) {
			$t_bug_data->prenom = $row['prenom'];
		}
		if ( is_blank( $t_bug_data->telephone ) ) {
			$t_bug_data->telephone = $row['telephone'];
		}
	}
	helper_call_custom_function( 'issue_create_validate', array( $t_bug_data ) );

	# Validate the custom fields before adding the bug.
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );

		# Produce an error if the field is required but wasn't posted
		if ( !gpc_isset_custom_field( $t_id, $t_def['type'] ) &&
			( $t_def['require_report'] ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if ( !custom_field_validate( $t_id, gpc_get_custom_field( "custom_field_$t_id", $t_def['type'], NULL ) ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	# Allow plugins to pre-process bug data
	$t_bug_data = event_signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );

	# Ensure that resolved bugs have a handler
	if ( $t_bug_data->handler_id == NO_USER && $t_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) {
		$t_bug_data->handler_id = auth_get_current_user_id();
	}

	# Create the bug
	$t_bug_id = $t_bug_data->create();

	# Mark the added issue as visited so that it appears on the last visited list.
	last_visited_issue( $t_bug_id );

	# Handle the file upload
	$t_files = helper_array_transpose( $f_files );
	foreach( $t_files as $t_file ) {
		if( !empty( $t_file['name'] ) ) {
			file_add( $t_bug_id, $t_file, 'bug' );
		}
	}

	# Handle custom field submission
	/*foreach( $t_related_custom_field_ids as $t_id ) {
		# Do not set custom field value if user has no write access
		if( !custom_field_has_write_access( $t_id, $t_bug_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		if( !custom_field_set_value( $t_id, $t_bug_id, gpc_get_custom_field( "custom_field_$t_id", $t_def['type'], $t_def['default_value'] ), false ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}*/

	$f_master_bug_id = gpc_get_int( 'm_id', 0 );
	$f_rel_type = gpc_get_int( 'rel_type', -1 );

	/*if ( $f_master_bug_id > 0 ) {
		# it's a child generation... let's create the relationship and add some lines in the history

		# update master bug last updated
		bug_update_date( $f_master_bug_id );

		# Add log line to record the cloning action
		history_log_event_special( $t_bug_id, BUG_CREATED_FROM, '', $f_master_bug_id );
		history_log_event_special( $f_master_bug_id, BUG_CLONED_TO, '', $t_bug_id );

		if ( $f_rel_type >= 0 ) {
			# Add the relationship
			relationship_add( $t_bug_id, $f_master_bug_id, $f_rel_type );

			# Add log line to the history (both issues)
			history_log_event_special( $f_master_bug_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $f_rel_type ), $t_bug_id );
			history_log_event_special( $t_bug_id, BUG_ADD_RELATIONSHIP, $f_rel_type, $f_master_bug_id );

			# update relationship target bug last updated
			bug_update_date( $t_bug_id );

			# Send the email notification
			email_relationship_added( $f_master_bug_id, $t_bug_id, relationship_get_complementary_type( $f_rel_type ) );
		}

		# copy notes from parent
		if ( $f_copy_notes_from_parent ) {

		    $t_parent_bugnotes = bugnote_get_all_bugnotes( $f_master_bug_id );

		    foreach ( $t_parent_bugnotes as $t_parent_bugnote ) {

		        $t_private = $t_parent_bugnote->view_state == VS_PRIVATE;

		        bugnote_add( $t_bug_id, $t_parent_bugnote->note, $t_parent_bugnote->time_tracking,
		            $t_private, $t_parent_bugnote->note_type, $t_parent_bugnote->note_attr,
		            $t_parent_bugnote->reporter_id, 
					#send_email
					FALSE , 
					#log history
					FALSE);
		    }
		}

		# copy attachments from parent
		if ( $f_copy_attachments_from_parent ) {
            file_copy_attachments( $f_master_bug_id, $t_bug_id );
		}
	}

	helper_call_custom_function( 'issue_create_notify', array( $t_bug_id ) );

	# Allow plugins to post-process bug data with the new bug ID
	event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );

	email_new_bug( $t_bug_id );

	// log status and resolution changes if they differ from the default
	if ( $t_bug_data->status != config_get('bug_submit_status') )
		history_log_event($t_bug_id, 'status', config_get('bug_submit_status') );

	if ( $t_bug_data->resolution != config_get('default_bug_resolution') )
		history_log_event($t_bug_id, 'resolution', config_get('default_bug_resolution') );*/

	form_security_purge( 'bug_report' );

	html_page_top1();

	if ( !$f_report_stay ) {
		html_meta_redirect( 'my_view_page.php' );
	}

	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo 'Enregistrement r&eacute;ussi.<br />';
	print_bracket_link( string_get_bug_view_url( $t_bug_id ), sprintf( lang_get( 'view_submitted_bug_link' ), $t_bug_id ) );
	print_bracket_link( 'my_view_page.php', lang_get( 'view_bugs_link' ) );

	if ( $f_report_stay ) {
?>
	<p>
	<form method="post" action="<?php echo string_get_bug_report_url() ?>">
	<?php # CSRF protection not required here - form does not result in modifications ?>
		
		<input type="hidden" name="category_id" value="<?php #echo string_attribute( $t_bug_data->category_id ) ?>2" />
		<input type="hidden" name="category_id" value="<?php echo string_attribute( $t_bug_data->category_id ) ?>" />
		<input type="hidden" name="sexe" value="<?php echo string_attribute( $t_bug_data->sexe ) ?>" />
		<input type="hidden" name="type_contact" value="<?php echo string_attribute( $t_bug_data->type_contact ) ?>" />
		<input type="hidden" name="profile_id" value="<?php echo string_attribute( $t_bug_data->profile_id ) ?>" />
		<input type="hidden" name="nom" value="<?php echo string_attribute( $t_bug_data->nom ) ?>" />
		<input type="hidden" name="nomepouse" value="<?php echo string_attribute( $t_bug_data->nomepouse ) ?>" />
		<input type="hidden" name="prenom" value="<?php echo string_attribute( $t_bug_data->prenom ) ?>" />
		<input type="hidden" name="telephone" value="<?php echo string_attribute( $t_bug_data->telephone ) ?>" />
		<input type="hidden" name="product_version" value="<?php echo string_attribute( $t_bug_data->version ) ?>" />
		<input type="hidden" name="target_version" value="<?php echo string_attribute( $t_bug_data->target_version ) ?>" />
		<input type="hidden" name="build" value="<?php echo string_attribute( $t_bug_data->build ) ?>" />
		<input type="hidden" name="report_stay" value="1" />
		<input type="hidden" name="view_state" value="<?php echo string_attribute( $t_bug_data->view_state ) ?>" />
		<input type="hidden" name="due_date" value="<?php echo string_attribute( $t_bug_data->due_date ) ?>" />
		<input type="submit"  value="<?php echo lang_get( 'report_more_bugs' ) ?>" />
	</form>
	</p>
<?php
	}
?>
</div>

<?php
	html_page_bottom();
