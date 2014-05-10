<?php
date_default_timezone_set('America/Martinique');


function get_all_contacts( $p_bug_id ) {
	global $g_cache_contacts, $g_cache_contact;

	if( !isset( $g_cache_contacts ) ) {
		$g_cache_contacts = array();
	}

	if( !isset( $g_cache_contact ) ) {
		$g_cache_contact = array();
	}

	# the cache should be aware of the sorting order
	if( !isset( $g_cache_contacts[(int)$p_bug_id] ) ) {
		
		$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );

		# sort by bugnote id which should be more accurate than submit date, since two bugnotes
		# may be submitted at the same time if submitted using a script (eg: MantisConnect).
		$t_query = "SELECT `type_contact`,`description`,`rapporteur`,`date_nvvenue`,`motif_nvvenue` 
		FROM `mantis_bug_text_table` 
		WHERE dossier =" . db_param() . '
						ORDER BY `date_nvvenue` DESC';
		$t_contacts = array();

		# BUILD array
		$t_result = db_query_bound( $t_query, array( $p_bug_id ) );
		#var_dump($t_result); echo $t_result->_numOfRows;
		while( $row = db_fetch_array( $t_result ) ) {
			$t_contact = new BugData;

			$t_contact->id = $row['id'];
			$t_contact->description = (strlen($row['description'])>2)?$row['description']:'Aucune note';
			$t_contact->motif_nvvenue = $row['motif_nvvenue'];
			$t_contact->date_nvvenue = $row['date_nvvenue'];
			$t_contact->rapporteur = $row['rapporteur'];
			$t_contact->type_contact = $row['type_contact'];

			$t_contacts[] = $t_contact;
			$g_cache_contact[(int)$t_contact->id] = $t_contact;
			
echo '<fieldset><legend id="contacts">Contact du '.date ( config_get('short_date_format'),$t_contact->date_nvvenue).'</legend><span class="inline33">';	

echo '<li><label class="description">'.lang_get('motif_nvvenue').'</label><p>'.get_enum_element('motif_nvvenue',$t_contact->motif_nvvenue).'</p></li>';

echo '<li><label class="description">'.lang_get('date_nvvenue').'</label><p>'.date ( config_get('normal_date_format'),$t_contact->date_nvvenue).'</p></li>';

echo '<li><label class="description">'.lang_get('rapporteur').'</label><p>';
print_user_with_subject( $t_contact->rapporteur, $p_bug_id );
echo '</p></li>';
			
echo '<li><label class="description">'.lang_get('description').'</label><p>'.$t_contact->description.'</p></li>';

echo '<li><label class="description">'.lang_get('type_contact').'</label><p>'.get_enum_element('type_contact',$t_contact->type_contact).'</p></li>';			
		
echo '</span></fieldset>';
		}


		$g_cache_contacts[(int)$p_bug_id] = $t_contacts;
		
	}

	return $g_cache_contacts[(int)$p_bug_id];
}

function imprim_all_contacts( $p_bug_id ) {
	global $g_cache_contacts, $g_cache_contact;

	if( !isset( $g_cache_contacts ) ) {
		$g_cache_contacts = array();
	}

	if( !isset( $g_cache_contact ) ) {
		$g_cache_contact = array();
	}

	# the cache should be aware of the sorting order
	if( !isset( $g_cache_contacts[(int)$p_bug_id] ) ) {
		
		$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );

		# sort by bugnote id which should be more accurate than submit date, since two bugnotes
		# may be submitted at the same time if submitted using a script (eg: MantisConnect).
		$t_query = "SELECT `type_contact`,`description`,`rapporteur`,`date_nvvenue`,`motif_nvvenue` 
		FROM `mantis_bug_text_table` 
		WHERE dossier =" . db_param() . '
						ORDER BY `date_nvvenue` DESC';
		$t_contacts = array();

		# BUILD array
		$t_result = db_query_bound( $t_query, array( $p_bug_id ) );
		#var_dump($t_result); echo $t_result->_numOfRows;
		while( $row = db_fetch_array( $t_result ) ) {
			$t_contact = new BugData;

			$t_contact->id = $row['id'];
			$t_contact->description = (strlen($row['description'])>2)?$row['description']:'Aucune note';
			$t_contact->motif_nvvenue = $row['motif_nvvenue'];
			$t_contact->date_nvvenue = $row['date_nvvenue'];
			$t_contact->rapporteur = $row['rapporteur'];
			$t_contact->type_contact = $row['type_contact'];

			$t_contacts[] = $t_contact;
			$g_cache_contact[(int)$t_contact->id] = $t_contact;
			
echo '<table width="780"><tr colspan="5"><h3>Contact du '.date ( config_get('short_date_format'),$t_contact->date_nvvenue).'</h3></tr>';	

echo '<tr><td class="print-category">'.lang_get('motif_nvvenue').'</td><td>'.get_enum_element('motif_nvvenue',$t_contact->motif_nvvenue).'</td></tr>';

echo '<tr><td class="print-category">'.lang_get('date_nvvenue').'</td><td>'.date ( config_get('normal_date_format'),$t_contact->date_nvvenue).'</td></tr>';

echo '<tr><td class="print-category">'.lang_get('rapporteur').'</td><td>';
print_user_with_subject( $t_contact->rapporteur, $p_bug_id );
echo '</td></tr>';
			
echo '<tr><td class="print-category">'.lang_get('description').'</td><td>'.$t_contact->description.'</td></tr>';

echo '<tr><td class="print-category">'.lang_get('type_contact').'</td><td>'.get_enum_element('type_contact',$t_contact->type_contact).'</td></tr></table>';			
		
		}


		$g_cache_contacts[(int)$p_bug_id] = $t_contacts;
		
	}

	return $g_cache_contacts[(int)$p_bug_id];
}