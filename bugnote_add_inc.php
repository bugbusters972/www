<?php
if ( ( !bug_is_readonly( $f_bug_id ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<a name="addbugnote"></a> <br />

<form name="bugnoteadd" method="post" action="bugnote_add.php">
<?php echo form_security_field( 'bugnote_add' ) ?>
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
<ul class="partb">
<div class="form_description">
	<h6><?php echo lang_get( 'add_bugnote_title' ) ?></h6>
	</div>
<?php
#Champ ('note','input','');
echo '<fieldset><legend>DEPUIS QUAND DURENT LES VIOLENCES?</legend>';
Champ ('dur_violence','input','radio');
Champ ('lieu_violence','input','checkbox');
echo '</fieldset><fieldset><legend>NATURE DES VIOLENCES DANS LE COUPLE</legend>';
Champ ('nat_vio_cpl','input','checkbox');
Champ ('enf_temoin_cpl','input','radio');
Champ ('enf_vio_cpl','input','radio');
Champ ('comm_cpl','textarea','');
echo '</fieldset><fieldset><legend>NATURE DES VIOLENCES AU TRAVAIL</legend>';
Champ ('cond_trav','input','checkbox');
Champ ('isol_trav','input','checkbox');
Champ ('dign_trav','input','checkbox');
Champ ('vio_trav','input','checkbox');
Champ ('juri_trav','input','checkbox');
echo '</fieldset><fieldset><legend>CARACTERISTIQUES DU PROBL&Egrave;ME</legend>';
Champ ('domaine','input','radio');
echo '</fieldset><fieldset><legend>CARACT&Eacute;RISTIQUES DE L&apos;AUTEUR DES VIOLENCES</legend>';
Champ ('nom_vio','input','');
Champ ('prenom_vio','input','');
Champ ('age_vio','input','');
Champ ('sexe_vio','input','radio');
Champ ('adresse_vio','input','radio');
Champ ('emploi_vio','input','');
Champ ('ressources_vio','input','');
Champ ('secteur_vio','select','');
Champ ('csp_vio','select','');
Champ ('lien_vict_vio','select','');
Champ ('vict_enf_vio','input','radio');
Champ ('mere_vict_vio','input','radio');
Champ ('pb_vio','input','checkbox');
Champ ('comm_vio','textarea','');
echo '</fieldset><fieldset><legend>CONS&Eacute;QUENCES</legend>';
Champ ('cons_phys','input','checkbox');
Champ ('cons_psy','input','checkbox');
Champ ('cons_trav','input','checkbox');
Champ ('cons_soc','input','checkbox');
Champ ('cons_comm','textarea','');
echo '</fieldset><fieldset><legend>BESOINS</legend>';
Champ ('bes_vio','input','checkbox');
Champ ('bes_soc','input','checkbox');
Champ ('bes_com','textarea','');
echo '</fieldset>';
?>
<!--<tr class="row-2">
	<td class="description" width="25%">
		<?php echo lang_get( 'bugnote' ) ?>
	</td>
	<td width="75%">
		<textarea name="note" cols="72" rows="10"></textarea>
	</td>
</tr>

<tr class="row-2">
	<td class="description" width="25%">
		duree des violences
	</td>
	<td width="75%">
		<input name="dur_violence"></input>
	</td>
</tr>

<tr class="row-2">
	<td class="description" width="25%">
		lieu des violences
	</td>
	<td width="75%">
		<input name="lieu_violence"></input>
	</td>
</tr>-->

<?php /*if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<li class="row-1">
	<p class="description">
		<?php echo lang_get( 'view_status' ) ?>
	</p>
	<p>
<?php
		$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
		if ( access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<input type="checkbox" name="private" <?php check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
<?php
			echo lang_get( 'private' );
		} else {
			echo get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
	</p>
</li>
<?php } */?>

<?php if ( config_get('time_tracking_enabled') ) { ?>
<?php if ( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $f_bug_id ) ) { ?>
<li <?php echo helper_alternate_class() ?>>
	<p class="description">
		<?php echo lang_get( 'time_tracking' ) ?> (HH:MM)
	</p>
	<p>
		<?php if ( config_get( 'time_tracking_stopwatch' ) && config_get( 'use_javascript' ) ) { ?>
		<script language="javascript">
			var time_tracking_stopwatch_lang_start = "<?php echo lang_get( 'time_tracking_stopwatch_start' ) ?>";
			var time_tracking_stopwatch_lang_stop = "<?php echo lang_get( 'time_tracking_stopwatch_stop' ) ?>";
		</script>
		<?php
			html_javascript_link( 'time_tracking_stopwatch.js' );
		?>
		<input type="text" name="time_tracking" size="5" value="00:00" />
		<input type="button" name="time_tracking_ssbutton" value="<?php echo lang_get( 'time_tracking_stopwatch_start' ) ?>" onclick="time_tracking_swstartstop()" />
		<input type="button" name="time_tracking_reset" value="<?php echo lang_get( 'time_tracking_stopwatch_reset' ) ?>" onclick="time_tracking_swreset()" />
		<?php } else { ?>
		<input type="text" name="time_tracking" size="5" value="00:00" />
		<?php } ?>
	</p>
</li>
<?php }} 

event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) ); ?>
<li class="buttons">
	<p class="asterisq">* <?php echo lang_get( 'required' ) ?></p>
		<input type="submit" value="<?php echo lang_get( 'add_bugnote_button' ) ?>"  onclick="this.disabled=1;document.bugnoteadd.submit();" />
</li>
</ul>
</form>

<?php 
}
