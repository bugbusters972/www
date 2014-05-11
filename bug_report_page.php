<?php
	 $g_allow_browser_cache = 1;
	 /**
	  * UFM Core API's
	  */
	require_once( 'core.php' );
	require_once( 'ajax_api.php' );
	require_once( 'file_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'last_visited_api.php' );
	require_once( 'projax_api.php' );
	require_once( 'collapse_api.php' );

	$f_master_bug_id = gpc_get_int( 'm_id', 0 );
	date_default_timezone_set('America/Martinique');

	if ( $f_master_bug_id > 0 ) {
		# master bug exists...
		bug_ensure_exists( $f_master_bug_id );

		# master bug is not read-only...
		if ( bug_is_readonly( $f_master_bug_id ) ) {
			error_parameters( $f_master_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}

		$t_bug = bug_get( $f_master_bug_id, true );

		# the user can at least update the master bug (needed to add the relationship)...
		access_ensure_bug_level( config_get( 'update_bug_threshold', null, null, $t_bug->project_id ), $f_master_bug_id );

		#@@@ (thraxisp) Note that the master bug is cloned into the same project as the master, independent of
		#       what the current project is set to.
		if( $t_bug->project_id != helper_get_current_project() ) {
            # in case the current project is not the same project of the bug we are viewing...
            # ... override the current project. This to avoid problems with categories and handlers lists etc.
            $g_project_override = $t_bug->project_id;
            $t_changed_project = true;
        } else {
            $t_changed_project = false;
        }

	    access_ensure_project_level( config_get( 'report_bug_threshold' ) );

		$f_build				= $t_bug->build;
		$f_nom				= $t_bug->nom;
		$f_nomepouse				= $t_bug->nomepouse;
		$f_prenom					= $t_bug->prenom;
		$f_telephone				= $t_bug->telephone;
		$f_product_version		= $t_bug->version;
		$f_target_version		= $t_bug->target_version;
		$f_profile_id			=0;
		$f_handler_id			= $t_bug->handler_id;
		$f_accueillante_id			= $t_bug->accueillante_id;

		$f_category_id			= $t_bug->category_id;
		$f_eta					= $t_bug->eta;
		$f_sexe				= $t_bug->sexe;
		#$f_motif_contact				= $t_bug->motif_contact;
		$f_summary				= $t_bug->summary;
		$f_description			= $t_bug->description;
		$f_motif_nvvenue	= $t_bug->motif_nvvenue;
		$f_date_nvvenue		= $t_bug->date_nvvenue;
		$f_rapporteur		= $t_bug->rapporteur;
		$f_type_contact		= $t_bug->type_contact;
		$f_view_state			= $t_bug->view_state;
		$f_due_date				= $t_bug->due_date;
#nouveaux champs
		include ('nvxchamps-varf-if_inc.php');

		$t_project_id			= $t_bug->project_id;
	} else {
		# Get Project Id and set it as current
		$t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
		if( ( ALL_PROJECTS == $t_project_id || project_exists( $t_project_id ) )
		 && $t_project_id != helper_get_current_project()
		) {
			helper_set_current_project( $t_project_id );
			# Reloading the page is required so that the project browser
			# reflects the new current project
			print_header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
		}

		# New issues cannot be reported for the 'All Project' selection
		if ( ( ALL_PROJECTS == helper_get_current_project() ) ) {
			print_header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
		}

		access_ensure_project_level( config_get( 'report_bug_threshold' ) );

		$f_build				= gpc_get_string( 'build', '' );
		$f_nom				= gpc_get_string( 'nom', '' );
		$f_nomepouse				= gpc_get_string( 'nomepouse', '' );
		$f_prenom					= gpc_get_string( 'prenom', '' );
		$f_telephone				= gpc_get_string( 'telephone', '' );
		$f_product_version		= gpc_get_string( 'product_version', '' );
		$f_target_version		= gpc_get_string( 'target_version', '' );
		$f_profile_id			= gpc_get_int( 'profile_id', 0 );
		$f_handler_id			= gpc_get_int( 'handler_id', 0 );
		$f_accueillante_id			= gpc_get_int( 'accueillante_id', 0 );

		$f_category_id			= gpc_get_int( 'category_id', 0 );
		$f_eta					= gpc_get_int( 'eta', config_get( 'default_bug_eta' ) );
		$f_sexe				= gpc_get_int( 'sexe', config_get( 'default_bug_sexe' ) );
		#$f_motif_contact				= gpc_get_int( 'motif_contact', config_get( 'default_bug_motif_contact' ) );
		$f_summary				= gpc_get_string( 'summary', '' );
		$f_description			= gpc_get_string( 'description', '' );
		$f_motif_nvvenue	= gpc_get_int( 'motif_nvvenue','' );
		$f_date_nvvenue		= db_now();
		$f_rapporteur	= gpc_get_int( 'rapporteur','');
		$f_type_contact		= gpc_get_int( 'type_contact', config_get( 'default_bug_type_contact' ) );
		$f_view_state			= gpc_get_int( 'view_state', config_get( 'default_bug_view_status' ) );
		$f_due_date				= gpc_get_string( 'due_date', '');
#nouveaux champs
		include ('nvxchamps-varf-else_inc.php');

		if ( $f_due_date == '' ) {
			$f_due_date = date_get_null();
		}

		$t_changed_project		= false;
	}

	$f_report_stay			          = gpc_get_bool( 'report_stay', false );
	$f_copy_notes_from_parent         = gpc_get_bool( 'copy_notes_from_parent', false);
	$f_copy_attachments_from_parent   = gpc_get_bool( 'copy_attachments_from_parent', false);

	$t_fields = config_get( 'bug_report_page_fields' );
	$t_fields = columns_filter_disabled( $t_fields );

	$tpl_show_category = in_array( 'category_id', $t_fields );
	$tpl_show_type_contact = in_array( 'type_contact', $t_fields );
	$tpl_show_eta = in_array( 'eta', $t_fields );
	$tpl_show_sexe = in_array( 'sexe', $t_fields );
	#$tpl_show_motif_contact = in_array( 'motif_contact', $t_fields );
	$tpl_show_motif_nvvenue = in_array( 'motif_nvvenue', $t_fields );
	$tpl_show_handler = in_array( 'handler', $t_fields ) && access_has_project_level( config_get( 'update_bug_assign_threshold' ) );
	$tpl_show_profiles = config_get( 'enable_profiles' );
	$tpl_show_nom = $tpl_show_profiles && in_array( 'nom', $t_fields );
	$tpl_show_nomepouse = $tpl_show_profiles && in_array( 'nomepouse', $t_fields );
	$tpl_show_prenom = $tpl_show_profiles && in_array( 'prenom', $t_fields );
	$tpl_show_telephone = $tpl_show_profiles && in_array( 'telephone', $t_fields );
	$tpl_show_resolution = in_array('resolution', $t_fields);
	$tpl_show_status = in_array('status', $t_fields);

	$tpl_show_versions = version_should_show_product_version( $t_project_id );
	$tpl_show_product_version = $tpl_show_versions && in_array( 'product_version', $t_fields );
	$tpl_show_product_build = $tpl_show_versions && in_array( 'product_build', $t_fields ) && config_get( 'enable_product_build' ) == ON;
	$tpl_show_target_version = $tpl_show_versions && in_array( 'target_version', $t_fields ) && access_has_project_level( config_get( 'roadmap_update_threshold' ) );
	$tpl_show_date_nvvenue = in_array( 'date_nvvenue', $t_fields );
	$tpl_show_rapporteur = in_array( 'rapporteur', $t_fields );
	$tpl_show_due_date = in_array( 'due_date', $t_fields ) && access_has_project_level( config_get( 'due_date_update_threshold' ), helper_get_current_project(), auth_get_current_user_id() );
	$tpl_show_attachments = in_array( 'attachments', $t_fields ) && file_allow_bug_upload();
	$tpl_show_view_state = in_array( 'view_state', $t_fields ) && access_has_project_level( config_get( 'set_view_status_threshold' ) );

		require_once( 'nvxchamps_inc.php' );
	
	# don't index bug report page
	html_robots_noindex();

	html_page_top( lang_get( 'report_bug_link' ) );

	print_recently_visited();

	include('fariane_inc.php');
	include('salleattente_inc.php');
	?>
<form class="appnitro" name="report_bug_form" method="post" <?php if ( $tpl_show_attachments ) { echo 'enctype="multipart/form-data"'; } ?> action="bug_report.php">
<?php echo form_security_field( 'bug_report' ) ?>
<ul class="part0">
	<div class="form_description">
			<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
			<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
			<h2>Accueil</h2>
	</div>
<?php
	event_signal( 'EVENT_REPORT_BUG_FORM_TOP', array( $t_project_id ) );

	if ( $tpl_show_nomepouse ||$tpl_show_nom || $tpl_show_prenom || $tpl_show_telephone || $tpl_show_type_contact) {

echo '<fieldset ><legend>IDENTIT&Eacute;</legend><span class="inline33">';
#nom
Champ ('nom','input','');

#nomepouse
Champ ('nomepouse','input','');

#prenom
Champ ('prenom','input','');
echo '</span><span class="inline33">';
if ( $tpl_show_sexe ) {
#sexe
Champ ('sexe','select','');
}

#telephone
Champ ('telephone','input','');

echo '</fieldset></span>';
}

?>
<span class="inline33">
<fieldset ><legend>PRISE EN CHARGE</legend><span class="inline33">	
<li <?php echo helper_alternate_class() ?>>
	<label class="description">Accueillante</label>
	<p><?php echo string_html_specialchars( current_user_get_field( 'username' ) );?></p>
</li>
<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'nodossier' ) ?>
		</label>
		<p>Num&eacute;rotation automatique</p>
</li>
<li <?php echo helper_alternate_class() ?>>
	<label class="description"><?php echo lang_get( 'assign_to' ) ?></label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="handler_id">
				<option value="0" selected="selected"></option>
				<?php print_assign_to_option_list( $f_handler_id ) ?>
			</select>
		</div>
</li>
</fieldset></span>
<?php

#echo'</span>';


/*if ( $tpl_show_motif_contact ) {
	Champ ('motif_contact','select','');
	}


	if ( $tpl_show_due_date ) {
		$t_date_to_display = '';

		if ( !date_is_null( $f_due_date ) ) {
			$t_date_to_display = date( config_get( 'calendar_date_format' ), $f_due_date );
		}
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'due_date' ) ?>
		</label>
		<div>
		<?php
		    print "<input ".helper_get_tab_index()." type=\"text\" id=\"due_date\" name=\"due_date\" size=\"20\" maxlength=\"16\" value=\"".$t_date_to_display."\" />";
			date_print_calendar();
		?>
		</div>
	</li>
<?php }

	if ( $tpl_show_product_version ) {
		$t_product_version_released_mask = VERSION_RELEASED;

		if (access_has_project_level( config_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
			$t_product_version_released_mask = VERSION_ALL;
		}
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'product_version' ) ?>
		</label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="product_version">
				<?php print_version_option_list( $f_product_version, $t_project_id, $t_product_version_released_mask ) ?>
			</select>
		</div>
	</li>
<?php
	}
 if ( $tpl_show_product_build ) { ?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'product_build' ) ?>
		</label>
		<span>
			<input <?php echo helper_get_tab_index() ?> type="text" name="build" size="32" maxlength="32" value="<?php echo string_attribute( $f_build ) ?>" />
		</span>
	</li>
<?php }

 if ( $tpl_show_status ) { ?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'status' ) ?>
		</label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="status">
			<?php 
			$resolution_options = get_status_option_list(access_get_project_level( $t_project_id), 
					config_get('bug_submit_status'), true, 
					ON == config_get( 'allow_reporter_close' ), $t_project_id );
			foreach ( $resolution_options as $key => $value ) {
			?>
				<option value="<?php echo $key ?>" <?php check_selected($key, config_get('bug_submit_status')); ?> >
					<?php echo $value ?>
				</option>
			<?php } ?>
			</select>
		</div>
	</li>
<?php }

 if ( $tpl_show_resolution ) { ?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'resolution' ) ?>
		</label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="resolution">
				<?php 
				print_enum_string_option_list('resolution', config_get('default_bug_resolution'));
				?>
			</select>
		</div>
	</li>
<?php }

	if ( $tpl_show_target_version ) { ?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'target_version' ) ?>
		</label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="target_version">
				<?php print_version_option_list() ?>
			</select>
		</div>
	</li>
<?php }
if ( $tpl_show_resolution ) { ?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'summary' ) ?>
		</label>
		<div>
			<input <?php echo helper_get_tab_index() ?> type="text" name="summary" size="105" maxlength="128" value="<?php echo string_attribute( $f_summary ) ?>" />
		</div>
	</li>-->
<?php }*/
event_signal( 'EVENT_REPORT_BUG_FORM', array( $t_project_id ) );
 ?>
<fieldset ><legend>PREMIER CONTACT</legend>
<span class="inline33">
 <li <?php echo helper_alternate_class() ?>>
			<label class="description">
				Motif de contact
			</label>
			<div>
				<select name="motif_nvvenue"><?php print_enum_string_option_list( 'motif_nvvenue', $f_motif_nvvenue ); ?></select>
			</div>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'date_nvvenue' ) ?>
		</label>
		<div>
			<input type="hidden" name="date_nvvenue" value="<?php echo db_now()?>"/>
			<p><?php echo date(config_get('normal_date_format'), db_now()) ?></p>
		</div>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">Edit&eacute; par :</label>
		<input type="hidden" name="rapporteur" value="<?php echo current_user_get_field( 'id' )?>"/>
		<p><?php echo string_html_specialchars( current_user_get_field( 'username' ) );?></p>
	</li>
</span>
<?php
echo '<span class="inline7030">';
#notes
Champ ('description','textarea','');

#type_contact
Champ ('type_contact','select','');

echo '</span></fieldset>';
	/*$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			$t_custom_fields_found = true;
?>
	--><li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php if($t_def['require_report']) {?><span class="required">*</span><?php } echo string_display( lang_get_defaulted( $t_def['name'] ) ) ?>
		</label>
		<div>
			<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id ) ?>
		</div>
	</li>
<?php
		}
	} # foreach( $t_related_custom_field_ids as $t_id )*/
?>
<?php
	// File Upload (if enabled)
	if ( $tpl_show_attachments ) {
		$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
		$t_file_upload_max_num = max( 1, config_get( 'file_upload_max_num' ) );
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' ) ?>
			<?php echo ' - (' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)'?>
		</label>
		<div>
			<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
<?php
		// Display multiple file upload fields
		for( $i =0; $i < $t_file_upload_max_num; $i++ ) {
?>
			<input <?php echo helper_get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" size="50" />
<?php
			if( $t_file_upload_max_num > 1 ) {
				echo '<br />';
			}
		}
	echo '</div></li>';	
	}
?>

</ul>
<!--DOSSIER-->
<a name="partie2"></a>
<ul class="parta">
<div class="form_description"><h2>Dossier</h2></div>
<?php 
#attente
Champ ('attente','select','');

echo'<fieldset ><legend>&Eacute;TAT CIVIL</legend>';

Champ ('date_naissance','input','');

#lieu_naissance
Champ ('lieu_naissance','input','');

#lieu_naissance
Champ ('pays_naissance','input','');

#nationalite
Champ ('nationalite','select','');

#rue
Champ ('rue','input','');

#adrs_suite
Champ ('adrs_suite','input','');

#ville
Champ ('ville','input','');

#region
#Champ ('region','input','');

#code_postal
Champ ('code_postal','input','');

?>
<!-- pays-->
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'pays' ) ?>
		</label>
		<span>
			<select <?php echo helper_get_tab_index() ?> name="pays">
				<?php print_enum_string_option_list( 'nationalite', $f_nationalite ) ?>
			</select>
		</span>
	</li>

<?php 

#autre_hebergt
Champ ('autre_hebergt','textarea','');

echo'</fieldset>';
#email
Champ ('email','input','text');

#tel_domicile
Champ ('tel_domicile','input','text');

#tel_travail
Champ ('tel_travail','input','text');

#orig_orient
echo $f_orig_orient;
Champ ('orig_orient','input','checkbox');

#connu_assoc
Champ ('connu_assoc','input','radio');

echo '<fieldset class="group-logement-venue"><legend>LOGEMENT AU MOMENT DE LA VENUE (VIOLENCES AUTRES QUE TRAVAIL)</legend>';
#nom_logem
Champ ('nom_logem','input','radio');

#type_logem
Champ ('type_logem','select','');

#complem_info
Champ ('complem_info','textarea','');

echo '</fieldset><fieldset class="group-situation-matrimoniale"><legend>SITUATION MATRIMONIALE</legend>';

/* matrim_ant*/
Champ ('matrim_ant','select','');

/* matrim*/
Champ ('matrim','select','');

/* nb_enfant*/
Champ ('nb_enfant','input','');

/* nb_enf_charge*/
Champ ('nb_enf_charge','input','');

/* sxage_enf*/
?>
<li class="row-2">
<label class="description">Sexe et &acirc;ge des enfants</label>
<div id="sxage"></div>
<span><a onclick="Enf();">Ajouter enfant</a></span>
<input type="hidden" name="sxage_enf">
</li>
<?php
/* enf_pere*/
Champ ('enf_pere','input','radio');

/* enf_recon*/
Champ ('enf_recon','input','radio');

echo '</fieldset><fieldset class="group-situation-entree"><legend>SITUATION &Agrave; L&apos;ENTR&Eacute;E</legend>';

/* sit_prof*/
Champ ('sit_prof','input','radio');

/* niv_scolr*/
Champ ('niv_scolr','select','');

/* emploi*/
Champ ('emploi','input','');

/* entreprise*/
Champ ('entreprise','input','');

/* cddcdi*/
Champ ('cddcdi','input','radio');

/* typ_contrat*/
Champ ('typ_contrat','select','');

/* secteur_activ*/
Champ ('secteur_activ','select','');

/* type_ent*/
Champ ('type_ent','input','radio');

/* csp*/
Champ ('csp','select','');

/* ressources*/
Champ ('ressources','input','');

/* prestations*/
Champ ('prestations','input','checkbox');

echo '</fieldset><fieldset><legend>HISTOIRE DE LA VICTIME</legend>';

/* mere_victime*/
Champ ('mere_victime','input','radio');

/* plaintes*/
Champ ('plaintes','input','radio');

/* nb_plaintes*/
Champ ('nb_plaintes','input','');

/* nb_plaintesko*/
Champ ('nb_plaintesko','input','');

/* plaintes_gend*/
Champ ('plaintes_gend','input','radio');

/* nb_plaintesgend*/
Champ ('nb_plaintesgend','input','');

/* nb_plainteskogend*/
Champ ('nb_plainteskogend','input','');

/* maincour*/
Champ ('maincour','input','radio');

/* nb_maincour*/
Champ ('nb_maincour','input','');

/* suite_plaintes*/
Champ ('suite_plaintes','textarea','');

/* deja_vict*/
Champ ('deja_vict','input','radio');

/* vict_enfce*/
Champ ('vict_enfce','input','radio');

/* contacte*/
Champ ('contacte','input','radio');

/* note_general*/
Champ ('note_general','textarea','');
echo '</fieldset>';

	if ( $tpl_show_view_state ) {
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'view_status' ) ?>
		</label>
		<div>
			<label><input <?php echo helper_get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo lang_get( 'public' ) ?></label>
			<label><input <?php echo helper_get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo lang_get( 'private' ) ?></label>

		</div>
	</li>
<?php
		}
	if( $f_master_bug_id > 0 ) {
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'relationship_with_parent' ) ?>
		</label>
		<div>
			<?php relationship_list_box( config_get( 'default_bug_relationship_clone' ), "rel_type", false, true ) ?>
			<?php echo '<b>' . lang_get( 'bug' ) . ' ' . bug_format_id( $f_master_bug_id ) . '</b>' ?>
		</div>
	</li>

	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php echo lang_get( 'copy_from_parent' ) ?>
		</label>
		<div>
			<label><input <?php echo helper_get_tab_index() ?> type="checkbox" id="copy_notes_from_parent" name="copy_notes_from_parent" <?php check_checked( $f_copy_notes_from_parent ) ?> /> <?php echo lang_get( 'copy_notes_from_parent' ) ?></label>
			<label><input <?php echo helper_get_tab_index() ?> type="checkbox" id="copy_attachments_from_parent" name="copy_attachments_from_parent" <?php check_checked( $f_copy_attachments_from_parent ) ?> /> <?php echo lang_get( 'copy_attachments_from_parent' ) ?></label>
		</div>
	</li>
<?php
	}
?>
	<!--<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'report_stay' ) ?>
		</label>
		<td>
			<label><input <?php echo helper_get_tab_index() ?> type="checkbox" id="report_stay" name="report_stay" <?php check_checked( $f_report_stay ) ?> /> <?php echo lang_get( 'check_report_more_bugs' ) ?></label>
		</td>
	</li>-->

</ul>
<ul>
	<li class="buttons">
		<p class="asterisq">* <?php echo lang_get( 'required' ) ?></p>
		<input <?php echo helper_get_tab_index() ?> type="submit" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
	</li>
</ul>
</form>

<?php
if ( $tpl_show_due_date ) {
	date_finish_calendar( 'due_date', 'trigger' );
}

html_page_bottom();
