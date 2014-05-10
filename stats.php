<?php
	require_once( 'core.php' );
	require_once( 'summary_api.php' );
	include('salleattente_inc.php');
	
$du = strlen(gpc_get_string('datedep',''))>2?encDt(gpc_get_string('datedep')):0;
$au =  strlen(gpc_get_string('datefin',''))>2?encDt(gpc_get_string('datefin')):db_now();

#appels téléphoniques
$appels = db_result(db_query_bound('SELECT COUNT(`id`) as appels FROM `mantis_bug_text_table` where `type_contact` =30 AND `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#venues sur place
$sur_place = db_result(db_query_bound('SELECT COUNT(`id`) as sur_place FROM `mantis_bug_text_table` where `type_contact` =10 AND `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#frequentation liées aux violences
$lievio = db_result(db_query_bound('SELECT COUNT(`id`) FROM `mantis_bug_text_table` where `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param().' AND `motif_nvvenue` =20 OR `motif_nvvenue`=80',array($du,$au) ));

#modalités de contact
$orig_orient = db_query_bound('SELECT `orig_orient` FROM `mantis_bug_table`
WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) );;

#tranches dâge
$dn = db_query_bound('SELECT date_naissance, date_submitted FROM mantis_bug_table WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) );;

#SITUATION MATRIM
$matrim = db_query_bound('SELECT matrim, COUNT(*) AS nombre FROM mantis_bug_table WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY matrim',array($du,$au));

#avec enfant
$avecenf = db_result(db_query_bound('SELECT COUNT(`id`) as `avec_enf` FROM `mantis_bug_table` where `nb_enfant`>0 AND `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#sans enfant
$sansenf = db_result(db_query_bound('SELECT COUNT(`id`) as `sans_enf` FROM `mantis_bug_table` where `nb_enfant`=0 AND `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#en couple
$encouple = db_result(db_query_bound('SELECT COUNT(`id`) as `en_couple` FROM `mantis_bug_table` WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param().' AND (`matrim`=1 OR `matrim`=4 OR `matrim`=5)', array($du,$au) ));

#seule
$seule = db_result(db_query_bound('SELECT COUNT(`id`) as `seule` FROM `mantis_bug_table` where `last_updated` BETWEEN '.db_param().' AND  '.db_param().' AND (`matrim`=8 OR `matrim`=2 OR `matrim`=3 OR `matrim`=6 OR `matrim`=7)', array($du,$au)));

#repart_nb_enfant
$nb_enfant = db_query_bound('SELECT nb_enfant, COUNT(*) AS nombre FROM mantis_bug_table where `last_updated` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY nb_enfant', array($du,$au));

#orig_geo
$francaise = db_result(db_query_bound('SELECT COUNT(`id`) as `francaise` FROM `mantis_bug_table` where `nationalite`=0 AND `last_updated` BETWEEN '.db_param().' AND  '.db_param(), array($du,$au)));

$etrangeres = db_result(db_query_bound('SELECT COUNT(`id`) as `etrangere` FROM `mantis_bug_table` where `nationalite`>0 AND `last_updated` BETWEEN '.db_param().' AND  '.db_param(), array($du,$au)));

#PAR ORIGINE ?
$nationalite = db_query_bound('SELECT nationalite, COUNT(*) AS nombre FROM mantis_bug_table where `last_updated` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY nationalite', array($du,$au));

#situation pro
$sit_prof = db_query_bound('SELECT sit_prof, COUNT(*) AS nombre FROM mantis_bug_table where `last_updated` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY sit_prof', array($du,$au));

#nature des violences
$nat_vio_cpl = db_query_bound('SELECT `nat_vio_cpl` FROM `mantis_bugnote_text_table` INNER JOIN `mantis_bugnote_table` ON mantis_bugnote_text_table.id = mantis_bugnote_table.bugnote_text_id WHERE `last_modified` BETWEEN '.db_param().' AND  '.db_param(), array($du,$au));

#ancienneté des violences
$dur_violence = db_query_bound('SELECT `dur_violence`, COUNT(*) AS `nombre` FROM `mantis_bugnote_text_table` INNER JOIN `mantis_bugnote_table` ON mantis_bugnote_text_table.id = mantis_bugnote_table.bugnote_text_id WHERE `last_modified` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY dur_violence', array($du,$au));

#demandes des femmes
$motif_nvvenue = db_query_bound('SELECT motif_nvvenue, COUNT(*) AS nombre FROM mantis_bug_text_table where `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param().' GROUP BY motif_nvvenue', array($du,$au));

#besoins des femmes
$bes_vio = db_query_bound('SELECT `bes_vio`,`bes_soc` FROM `mantis_bugnote_text_table` INNER JOIN `mantis_bugnote_table` ON mantis_bugnote_text_table.id = mantis_bugnote_table.bugnote_text_id WHERE `last_modified` BETWEEN '.db_param().' AND  '.db_param(), array($du,$au));

	html_page_top( );
?>

<div class="appnitro">
<ul>
<li  <?php echo helper_alternate_class() ?>>
	<div class="form_description">
	<h2>Statistiques du <?php echo date(config_get('short_date_format'),$du) ?> au <?php echo date(config_get('short_date_format'),$au)?>
	</h2>
	<a href="stats-csv.php?datedep=<?php echo $du;?>&datefin=<?php echo $au;?>">Export CSV</a></div>
</li>
<li>
<table class="statable">
<tbody>
<th>Donn&eacute;e</th><th>Nbre de pers.</th>
<tr>
	<td>Nombre d&apos;appels</td>
	<td><?php echo $appels?></td>
</tr>
<tr>
	<td>Nombre de venues</td>
	<td><?php echo $sur_place?></td>
</tr>
<tr>
	<td>Nombre de fr&eacute;quentation li&eacute;es aux violences</td>
	<td><?php echo $lievio?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Origine</td>
</tr>
<tr>
	<td>France</td>
	<td><?php echo $francaise;?></td>
</tr>
<tr>
	<td>Etranger</td>
	<td><?php echo $etrangeres;?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">R&eacute;partition nationalit&eacute;s</td>
</tr>
<tr>
<?php TRstat('nationalite');?>
</tr>
<tr>
	<td class="soustitre" colspan="2">Orientation</td>
</tr>
<tr>
<?php Statexp ('orig_orient'); ?>
</tr>
<tr>
	<td class="soustitre" colspan="2">Situation matrimoniale</td>	
</tr>
<tr>
<?php TRstat('matrim');?>
</tr>
<tr>
	<td class="soustitre" colspan="2">Femmes avec enfants</td>	
</tr>
<tr>
	<td><?php echo $avecenf;?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Femmes sans enfant</td>	
</tr>
<tr>
	<td><?php echo $sansenf;?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Femmes en couple</td>	
</tr>
<tr>
	<td><?php echo $encouple;?></td>
</tr>
</tbody>
</table>
<table class="statable">
<tbody>
<th>Donn&eacute;e</th><th>Nbre de pers.</th>
<tr>
	<td class="soustitre" colspan="2">Femmes seules</td>	
</tr>
<tr>
	<td><?php echo $seule;?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Nombre d&apos;enfants</td>	
</tr>
<tr>
	<?php while ($ligne = db_fetch_array( $nb_enfant )){
echo '<tr><td>'.$ligne['nb_enfant'].' enfant(s)</td><td>'.$ligne['nombre'].'</td></tr>';
}?>
</tr>
<tr>
	<td class="soustitre" colspan="2">Situation professionnelle</td>	
</tr>
<tr>
	<?php TRstat('sit_prof');?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Nature des violences</td>	
</tr>
<tr>
	<?php Statexp('nat_vio_cpl');?></td>
</tr>
<tr>

	<td class="soustitre" colspan="2">Anciennet&eacute; des violences</td>	
</tr>
<tr>
	<?php TRstat('dur_violence');?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Motif de contact</td>	
</tr>
<tr>
	<?php TRstat('motif_nvvenue');?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">Besoins des femmes</td>	
</tr>
<tr>
	<?php Statexp('bes_vio');?></td>
</tr>
<tr>
	<td class="soustitre" colspan="2">R&eacute;partition par &acirc;ge</td>	
</tr>
<tr>
<?php while ($i = db_fetch_array( $dn )){
	$f [] = date('Y',$i['date_submitted']) - date('Y',$i['date_naissance']);
	};
	if(is_array($f)){
	foreach(array_count_values($f) as $age=>$num){
	echo '<tr><td>'.$age.' ans</td><td>'.$num.'</td></tr>';}}
?>
</tr>
</tbody>
</table>
</li>
</ul>
</div>