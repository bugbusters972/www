<?php
/**
 * Requires bugnote API
 */
require_once( 'current_user_api.php' );
require_once( 'responses_inc.php' );

# grab the user id currently logged in
$t_user_id = auth_get_current_user_id();

#precache access levels
if ( isset( $g_project_override ) ) {
	access_cache_matrix_project( $g_project_override );
} else {
	access_cache_matrix_project( helper_get_current_project() );
}

# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$t_bugnotes = bugnote_get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );

#precache users
$t_bugnote_users = array();
foreach($t_bugnotes as $t_bugnote) {
	$t_bugnote_users[] = $t_bugnote->reporter_id;
}
user_cache_array_rows( $t_bugnote_users );

$num_notes = count( $t_bugnotes );
?>

<?php # Bugnotes BEGIN ?>
<a name="bugnotes" id="bugnotes"><br /></a>

<?php 
	event_signal( 'EVENT_VIEW_BUGNOTES_START', array( $f_bug_id, $t_bugnotes ) );

	$t_normal_date_format = config_get( 'normal_date_format' );
	$t_short_date_format = config_get( 'short_date_format' );
	$t_total_time =0;

	for ( $i=0; $i < $num_notes; $i++ ) {
		$t_bugnote = $t_bugnotes[$i];

		if ( $t_bugnote->date_submitted != $t_bugnote->last_modified )
			$t_bugnote_modified = true;
		else
			$t_bugnote_modified = false;

		$t_bugnote_id_formatted = bugnote_format_id( $t_bugnote->bugnote_text_id );
		$Bn = $t_bugnote->bugnote_text_id;
		

		if ( 0 != $t_bugnote->time_tracking ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_bugnote->time_tracking );
			$t_bugnote->note_type = TIME_TRACKING;   // for older entries that didn't set the type @@@PLR FIXME
			$t_total_time += $t_bugnote->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}

		if ( VS_PRIVATE == $t_bugnote->view_state ) {
			$t_bugnote_css		= 'bugnote-private';
			$t_bugnote_note_css	= 'bugnote-note-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
			$t_bugnote_note_css	= 'bugnote-note-public';
		}
		#string f.r.a.v
		if (bugnote_recup_field( $Bn, 'nat_vio_cpl')>0){ $frav= 'dans le couple';}else if(bugnote_recup_field( $Bn, 'cond_trav')>0){$frav= 'au travail';};
?>
<a name="partie3"></a>
<ul class="partb">
<div class="form_description">
<h2><?php echo 'Violences '.$frav.'&nbsp;'. date( $t_short_date_format, $t_bugnote->date_submitted );?></h2>

</div>

<table width="100%" cellspacing="1">
<tbody>
	<tr>
		<td class="form-title" colspan="4">
<?php if ( 0 == $num_notes ) { echo lang_get( 'no_bugnotes_msg' ); }else { echo 'R&eacute;sum&eacute; du probl&egrave;me';}?>
		</td>
	</tr>
<tr>
<th>Date de la venue</th><th>Nature des violences</th><th>Faits relatifs aux violences</th><th>Mise &agrave; jour</th>
</tr>
<tr class="bugnote" id="c<?php echo $Bn ?>">
    <td>
	<?php echo date( $t_normal_date_format, $t_bugnote->date_submitted ); ?>
	</td>
	<td>
	<?php echo '<a href="'.string_get_bugnote_view_url($t_bugnote->bug_id, $Bn).'">'.Bndecode('nat_vio_cpl',$Bn).'</a>';?>
	</td>
	<td>
	<?php echo '<a href="'.string_get_bugnote_view_url($t_bugnote->bug_id, $Bn).'">'.$frav.'</a>';?>
	</td>
	<td>
	<?php if ( $t_bugnote_modified ) {
			echo date( $t_normal_date_format, $t_bugnote->last_modified );}?>
	</td>
		<?php /*print_avatar( $t_bugnote->reporter_id ); ?>
		<?php echo string_get_bugnote_view_url($t_bugnote->bug_id, $Bn); ?>
		<?php echo lang_get( 'bugnote_link_title' ); ?>
		<?php echo $t_bugnote_id_formatted ; ?>
		<?php echo print_user( $t_bugnote->reporter_id ); ?>
		<?php if ( user_exists( $t_bugnote->reporter_id ) ) {
				$t_access_level = access_get_project_level( null, (int)$t_bugnote->reporter_id );
				// Only display access level when higher than 0 (ANYBODY)
				if( $t_access_level > ANYBODY ) {
					echo '(', get_enum_element( 'access_levels', $t_access_level ), ')';
					}};?>
		<?php if ( VS_PRIVATE == $t_bugnote->view_state ) {echo lang_get( 'private' );} */?>
	
		<?php
			# bug must be open to be editable
			if ( !bug_is_readonly( $f_bug_id ) ) {
				$t_can_edit_note = false;
				$t_can_delete_note = false;

				# admins and the bugnote creator can edit/delete this bugnote
				if ( ( access_has_bug_level( config_get( 'manage_project_threshold' ), $f_bug_id ) ) ||
					( ( $t_bugnote->reporter_id == $t_user_id ) && ( ON == config_get( 'bugnote_allow_user_edit_delete' ) ) ) ) {
					$t_can_edit_note = true;
					$t_can_delete_note = true;
				}

				# users above update_bugnote_threshold should be able to edit this bugnote
				if ( $t_can_edit_note || access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id ) ) {
					echo '<tr><td>';
					print_button( 'bugnote_edit_page.php?bugnote_id='.$Bn, lang_get( 'bugnote_edit_link' ) );
					echo '</td>';
				}

				# users above delete_bugnote_threshold should be able to delete this bugnote
				if ( $t_can_delete_note || access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $f_bug_id ) ) {
					echo "<td>";
					print_button( 'bugnote_delete.php?bugnote_id='.$Bn, lang_get( 'delete_link' ) );
					echo'</td>';
				}

				# users with access to both update and change view status (or the bugnote author) can change public/private status
				/*if ( $t_can_edit_note || ( access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id ) &&
					access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id ) ) ) {
					if ( VS_PRIVATE == $t_bugnote->view_state ) {
						echo " ";
						print_button( 'bugnote_set_view_state.php?private=0&bugnote_id=' . $Bn, lang_get( 'make_public' ) );
					} else {
						echo " ";
						print_button( 'bugnote_set_view_state.php?private=1&bugnote_id=' . $Bn, lang_get( 'make_private' ) );
					}
				}*/
			}
		?>
		</div>
	</td>
	<td class="<?php echo $t_bugnote_note_css ?>">
		<?php
			switch ( $t_bugnote->note_type ) {
				case REMINDER:
					echo '<em>';

					# List of recipients; remove surrounding delimiters
					$t_recipients = trim( $t_bugnote->note_attr, '|' );

					if( empty( $t_recipients ) ) {
						echo lang_get( 'reminder_sent_none' );
					} else {
						# If recipients list's last char is not a delimiter, it was truncated
						$t_truncated = ( '|' != utf8_substr( $t_bugnote->note_attr, utf8_strlen( $t_bugnote->note_attr ) - 1 ) );

						# Build recipients list for display
						$t_to = array();
						foreach ( explode( '|', $t_recipients ) as $t_recipient ) {
							$t_to[] = prepare_user_name( $t_recipient );
						}

						echo lang_get( 'reminder_sent_to' ) . ': '
							. implode( ', ', $t_to )
							. ( $t_truncated ? ' (' . lang_get( 'reminder_list_truncated' ) . ')' : '' );
					}

					echo '</em><br /><br />';
					break;

				case TIME_TRACKING:
					if ( access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
						echo '<b>', lang_get( 'time_tracking_time_spent' ) . ' ' . $t_time_tracking_hhmm, '</b><br /><br />';
					}
					break;
			}

			echo string_display_links( $t_bugnote->note );;
		?>
	</td>
</tr></table>
<?php event_signal( 'EVENT_VIEW_BUGNOTE', array( $f_bug_id, $Bn, VS_PRIVATE == $t_bugnote->view_state ) ); 
echo '<fieldset><legend>DEPUIS QUAND DURENT LES VIOLENCES?</legend>';
Bnexp ('dur_violence',$Bn);
Bnexp ('lieu_violence',$Bn);
echo '</fieldset><fieldset><legend>NATURE DES VIOLENCES DANS LE COUPLE</legend>';
Bnexp ('nat_vio_cpl',$Bn);
Bnexp ('enf_temoin_cpl',$Bn);
Bnexp ('enf_vio_cpl',$Bn);
Bnaffiche_str ('comm_cpl',$Bn);
echo '</fieldset><fieldset><legend>NATURE DES VIOLENCES AU TRAVAIL</legend>';
Bnexp ('cond_trav',$Bn);
Bnexp ('isol_trav',$Bn);
Bnexp ('dign_trav',$Bn);
Bnexp ('vio_trav',$Bn);
Bnexp ('juri_trav',$Bn);
echo '</fieldset><fieldset><legend>CARACTERISTIQUES DU PROBL&Egrave;ME</legend>';
Bnexp ('domaine',$Bn);
echo '</fieldset><fieldset><legend>CARACT&Eacute;RISTIQUES DE L&apos;AUTEUR DES VIOLENCES</legend>';
Bnaffiche_str ('nom_vio',$Bn);
Bnaffiche_str ('prenom_vio',$Bn);
Bnaffiche_int ('age_vio',$Bn);
Bnexp ('sexe_vio',$Bn,'1');
Bnexp ('adresse_vio',$Bn);
Bnaffiche_str ('emploi_vio',$Bn);
Bnaffiche_int ('ressources_vio',$Bn);
Bnexp ('secteur_vio',$Bn);
Bnexp ('csp_vio',$Bn);
Bnexp ('lien_vict_vio',$Bn);
Bnexp ('vict_enf_vio',$Bn);
Bnexp ('mere_vict_vio',$Bn);
Bnexp ('pb_vio',$Bn);
Bnaffiche_str ('comm_vio',$Bn);
echo '</fieldset><fieldset><legend>CONS&Eacute;QUENCES</legend>';
Bnexp ('cons_phys',$Bn);
Bnexp ('cons_psy',$Bn);
Bnexp ('cons_trav',$Bn);
Bnexp ('cons_soc',$Bn);
Bnaffiche_str ('cons_comm',$Bn);
echo '</fieldset><fieldset><legend>BESOINS</legend>';;
Bnexp ('bes_vio',$Bn);
Bnexp ('bes_soc',$Bn);
Bnaffiche_str ('bes_com',$Bn);
echo '</fieldset></ul><a name="#partie3"></a>';
get_all_responses ($Bn);
?>
<form name="reponse_form" method="post" action="reponse.php">
<?php echo form_security_field( 'reponse_form' ) ?>
<input type="hidden" name="bugnote_id" value="<?php echo $Bn ?>"/>
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>"/>
<div class="form_description"><h4><?php echo lang_get('new_reponse') ?></h4></div>
<fieldset><legend>PRISE EN CHARGE</legend>
<span class="inline33">
	<li <?php echo helper_alternate_class() ?>>
			<label class="description">
				<?php print_documentation_link( 'date_rep' ) ?>
			</label>
			<input type="hidden" name="date_rep" value="<?php echo db_now(); ?>"/>
			<p><?php echo date(config_get('normal_date_format'), db_now()); ?></p>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'reporter_id' ) ?>
		</label>
		<div>
			<input type="hidden" name="reporter_id" value="<?php echo current_user_get_field( 'id' ); ?>"/>
			<p><?php echo string_html_specialchars( current_user_get_field( 'username' ) );?><p>
		</div>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description"><?php print_documentation_link( 'assign_to' ) ?></label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="handler_id">
				<option value="0" selected="selected"></option>
				<?php print_assign_to_option_list( $f_handler_id ) ?>
			</select>
		</div>
	</li>
	</span><span class="inline33">
	 <li <?php echo helper_alternate_class() ?>>
			<label class="description"><?php print_documentation_link( 'type_contact' ) ?>
			</label>
			<div>
				<select name="type_contact_rep"><?php print_enum_string_option_list( 'type_contact', '' ); ?></select>
			</div>
	</li>
	
<?php
Champ ('accompagn','select','');

echo '</span></fieldset><fieldset><legend>AIDE SOCIALE ET FINANCI&Egrave;RE</legend><span class="inline33">';

Champ ('aide_soc_fin', 'input', 'checkbox');
Champ ('aide_alim', 'input', 'checkbox');
Champ ('demarch', 'input', 'checkbox');

echo '</span></fieldset><span class="inline33">';
Champ ('sante', 'input', 'checkbox');
Champ ('assis_jur', 'input', 'checkbox');
Champ ('heberg', 'input', 'checkbox');

echo '</span><span class="inline33">';

Champ ('aide_enf', 'input', 'checkbox');
Champ ('aide_prof', 'input', 'checkbox');
echo '</span>';
#autres démarches
?>
<li <?php echo helper_alternate_class() ?>>
	<label class="description"><?php print_documentation_link( 'autre_demarch' ) ?></label>
		<div id="autdemarch">
			<input type="text" id="demarche"/>
		</div>
		<a onclick="newdemarch();">Ajouter un champ</a>
	<input type="hidden" name="autre_demarch"/>
</li>
<?php
Champ ('temps', 'select', '');
Champ ('rep_comm', 'textarea', '');

echo '<li class="buttons"><p class="asterisq">*Champ obligatoire</p><input type="submit" value="Enregistrer"/></li></form>';

	} # end for loop

	if ( $t_total_time > 0 && access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
		echo '<tr><td colspan="2">', sprintf ( lang_get( 'total_time_for_issue' ), db_minutes_to_hhmm( $t_total_time ) ), '</td></tr>';
	}

	event_signal( 'EVENT_VIEW_BUGNOTES_END', $f_bug_id );
?>
