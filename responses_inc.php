<?php
function get_all_responses( $Bn ) {
	global $g_cache_responses, $g_cache_response;

	if( !isset( $g_cache_responses ) ) {
		$g_cache_responses = array();
	}

	if( !isset( $g_cache_response ) ) {
		$g_cache_response = array();
	}
	# the cache should be aware of the sorting order
	if( !isset( $g_cache_responses[(int)$Bn] ) ) {
		#$t_bug_table = db_get_table( 'mantis_bug_table' );
		$t_reponse_table = db_get_table( 'bugnote_reponse_table' );

		# sort by bugnote id which should be more accurate than submit date, since two bugnotes
		# may be submitted at the same time if submitted using a script (eg: MantisConnect).
		$t_querie = "SELECT `id`,`bugnote_id`,`date_rep`,`handler_id`,`reporter_id`, `type_contact_rep`, `accompagn`, `aide_soc_fin`, `aide_alim`, `demarch`, `sante`, `assis_jur`, `heberg`, `aide_enf`, `aide_prof`, `autre_demarch`, `temps`, `rep_comm`
		FROM $t_reponse_table 
		WHERE `bugnote_id` = " . db_param().'ORDER BY "id" ASC';
		$t_responses = array();
		# BUILD array
		$r_result = db_query_bound(  $t_querie,  array($Bn) );
	

		#foreach($ligne as $col => $value){echo $col.$value;
		while ($ligne = db_fetch_array( $r_result )){
			$t_response = new RespData;
			$t_response->reid = $ligne['id'];
			$t_response->bugnote_id = $ligne['bugnote_id'];
			$t_response->date_rep = $ligne['date_rep'];
			$t_response->handler_id = $ligne['handler_id'];
			$t_response->reporter_id = $ligne['reporter_id'];
			$t_response->type_contact_rep = $ligne['type_contact_rep'];
			$t_response->accompagn = $ligne['accompagn'];
			$t_response->aide_soc_fin = $ligne['aide_soc_fin'];
			$t_response->aide_alim = $ligne['aide_alim'];
			$t_response->demarch = $ligne['demarch'];
			$t_response->sante = $ligne['sante'];
			$t_response->assis_jur = $ligne['assis_jur'];
			$t_response->heberg = $ligne['heberg'];
			$t_response->aide_enf = $ligne['aide_enf'];
			$t_response->aide_prof = $ligne['aide_prof'];
			$t_response->autre_demarch = $ligne['autre_demarch'];
			$t_response->temps = $ligne['temps'];
			$t_response->rep_comm = $ligne['rep_comm'];

			$tpl_aide_soc_fin = $t_response->aide_soc_fin;

			$t_responses[] = $t_response;
			$g_cache_response[(int)$t_response->reid] = $t_response;
			
echo '<ul class="partc"><div class="form_description"><h3>R&eacute;ponse du '.date ( config_get('short_date_format'),$t_response->date_rep).'</h3></div>';
?>
<table width="100%" cellspacing="1"><tbody>
	<tr>
		<td colspan="4" class="form-title">R&eacute;sum&eacute; de la r&eacute;ponse</td>
	</tr>
<tr>
<th>Date de la r&eacute;ponse</th><th>Temps pass&eacute;</th><th>Edit&eacute; par</th>
</tr>
<tr class="bugnote">
<td><?php echo date ( config_get('short_date_format'),$t_response->date_rep);?></td>
<td><?php echo get_enum_element('temps',$t_response->temps);?></td>
<td><?php echo user_get_name($t_response->handler_id);?></td>
</tr>
<tr>
<td>
<form action="reponse_edit_page.php?reponse_id=<?php echo $t_response->reid.'&bugnote_id='.$t_response->bugnote_id; ?>" method="post">
<?php echo form_security_field( 'reponse_update' ) ?>
<input type="submit" value="Modifier" class="button-small"></form>
</td>
<td>
<form action="reponse_delete.php?reponse_id=<?php echo $t_response->reid.'&bugnote_id='.$t_response->bugnote_id; ?>" method="post">
<?php echo form_security_field( 'reponse_delete' ) ?>
<input type="submit" value="Supprimer" class="button-small"></form>
</td>
<td></td>		
</tr></tbody></table>
<?php
echo '<fieldset><legend>PRISE EN CHARGE</legend><span class="inline33"><li><label class="description">'.lang_get('date_rep').'</label><p>'.date ( config_get('short_date_format'),$t_response->date_rep).'</p></li>';

echo '<li><label class="description">'.lang_get('reporter_id').'</label><p>'.user_get_name( $t_response->reporter_id ).'</p></li>';

echo '<li><label class="description">'.lang_get('handler_id').'</label><p>'.user_get_name($t_response->handler_id).'</p></li></span><span class="inline33">';

echo '<li><label class="description">'.lang_get('type_contact').'</label><p>'.get_enum_element('type_contact',$t_response->type_contact_rep).'</p></li>';

echo '<li><label class="description">'.lang_get('accompagn').'</label><p>'.get_enum_element('accompagn',$t_response->accompagn).'</p></li></span></fieldset><fieldset><legend>AIDE SOCIALE ET FINANCI&Egrave;RE</legend><span class="inline33">';

Respexp('aide_soc_fin',$t_response->aide_soc_fin);
Respexp('aide_alim',$t_response->aide_alim);
Respexp('demarch',$t_response->demarch);

echo'</span></fieldset><span class="inline33">';

Respexp('sante',$t_response->sante);
Respexp('assis_jur',$t_response->assis_jur);
Respexp('heberg',$t_response->heberg);

echo'</span><span class="inline33">';

Respexp('aide_enf',$t_response->aide_enf);
Respexp('aide_prof',$t_response->aide_prof);

echo'</span>';

echo'<li><label class="description">'.lang_get('autre_demarch').'</label>';
$demz = explode("@@", $t_response->autre_demarch);
foreach ($demz as $dem){
if (strlen($dem)>3){
echo '<p>D&eacute;marche : '.$dem.'</p>';
}}
echo '</li>';

echo'<span class="inline33"><li><label class="description">'.lang_get('temps').'</label><p>'.get_enum_element('temps',$t_response->temps).'</p></li>';

echo'<li><label class="description">'.lang_get('rep_comm').'</label><p>'.string_html_specialchars($t_response->rep_comm).'</p></li></span></ul>';

		}

		$g_cache_responses[(int)$Bn] = $t_responses;
		#var_dump($t_responses);
	}

	return $g_cache_responses[(int)$Bn];
}