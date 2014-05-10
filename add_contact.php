<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );

	form_security_validate( 'add_contact_form' );
	
	$t_user = auth_get_current_user_id();

	$f_dossier 			= 	gpc_get_int( 'bug_id' );
	$f_motif_nvvenue	= 	gpc_get_int( 'motif_nvvenue', 0 );
	$f_date_nvvenue		= 	gpc_get_int('date_nvvenue', db_now() );
	$f_rapporteur		=	gpc_get_int( 'rapporteur', $t_user );
	$f_description		=	gpc_get_string( 'description', 'aucune note' );
	$f_type_contact		=	gpc_get_int( 'type_contact' );

	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );	

	$query = "INSERT INTO $t_bug_text_table
	(description, motif_nvvenue, date_nvvenue, rapporteur, type_contact, dossier)
	VALUES (" . db_param() .  ',' . db_param() .',' . db_param().',' . db_param().',' . db_param().',' . db_param().")";
	db_query_bound( $query, Array($f_description, $f_motif_nvvenue, $f_date_nvvenue, $f_rapporteur, $f_type_contact, $f_dossier));

	form_security_purge( 'add_contact_form' );
	
	echo '<div class="appnitro">
	<p>Enregistr&eacute;.</p>
	<p>Retour au dossier '.$f_dossier.'...</p>
	</div>';
	
	html_meta_redirect( 'view.php?id='.$f_dossier );
	
