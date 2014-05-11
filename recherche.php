<?php
require_once( 'core.php' );
html_robots_noindex();
html_page_top( lang_get( 'report_bug_link' ) );

include('salleattente_inc.php');
$init = gpc_get_int( 'init','');
$id = gpc_get_int( 'id','');
$reporter = gpc_get_int( 'reporter_id','');
$handler = gpc_get_int( 'handler_id','');
$motif_nvvenue = gpc_get_int( 'motif_nvvenue','');
$du = strlen(gpc_get_string('datedep',''))>2?encDt(gpc_get_string('datedep')):"";
$au =  strlen(gpc_get_string('datefin',''))>2?encDt(gpc_get_string('datefin')):db_now();
$nom = gpc_get_string( 'nom','');
$prenom = gpc_get_string( 'prenom','');
$nomepouse = gpc_get_string( 'nomepouse','');
#var_dump($init);
?>
<form class="appnitro" name="search_form" method="post" action="recherche.php">

<div class="form_description">
<h2>Crit&egrave;res de recherche</h2>
<input type="hidden" name="init" value="1"/>
</div>

<ul>
<span class="inline33">
	<li>
		<label class="description"><?php echo lang_get( 'reporter' ) ?></label>
		<select name="reporter_id">
		<option value="0" selected="selected"></option>
		<?php print_user_option_list( $reporter ) ?>
		</select>
	</li>
	<li>
		<label class="description"><?php echo lang_get( 'handler' ) ?></label>
		<select name="handler_id">
		<option value="0" selected="selected"></option>
			<?php print_assign_to_option_list( $handler ) ?>
		</select>
	</li>
	<li>
		<label class="description"><?php echo lang_get( 'nodossier' ) ?></label>
		<input name="id"/>
	</li>
</span>

<span class="inline33">
<li>
	<label class="description">Du (jj/mm/aaaa)</label>
	<input name="datedep" value="<?php $du?>"/>
</li>
<li>
	<label class="description">..au (jj/mm/aaaa)</label>
	<input name="datefin" value="<?php $au?>"/>
</li>
<li>
	<label class="description"><?php echo lang_get( 'motif_nvvenue' ) ?></label>
	<select name="motif_nvvenue">
	<option value="0" selected="selected"></option>
	<?php print_enum_string_option_list( 'motif_nvvenue', $motif_nvvenue ); ?>
	</select>
</li>
</span>

<span class="inline33">
<li>
	<label class="description"><?php echo lang_get( 'nom' ) ?></label>
	<input name="nom" value="<?php $nom?>"/>
</li>
<li>
	<label class="description"><?php echo lang_get( 'nomepouse' ) ?></label>
	<input name="nomepouse" value="<?php $nomepouse?>"/>
</li>
<li>
	<label class="description"><?php echo lang_get( 'prenom' ) ?></label>
	<input name="prenom" <?php $prenom?>/>
</li>
</span>

<li class="buttons">
		<input <?php echo helper_get_tab_index() ?> type="submit" value="Rechercher" />
</li>

</ul>
</form>
<div class="appnitro">
<div class="result_rech"><h2>R&eacute;sultats</h2></div>
<table id="buglist" width="780" cellspacing="1">
<tbody><tr>
	<td class="form-title" colspan="7">
		<span class="floatleft">
		Liste des dossiers trouv&eacute;s</span>

		<span class="floatleft small"> &nbsp;&nbsp;&nbsp; </span>

		<span class="floatright small"> </span>
	</td>
</tr>
<tr class="row-category">
<td> &nbsp; </td><td>N&ordm; dossier</td><td>Nom de naissance</td><td>Pr&eacute;nom</td><td>Sexe</td><td>Mise &agrave; jour</td></tr>

<?php
$querie = "SELECT `mantis_bug_table`.`id`, `mantis_bug_table`.`nom`,`mantis_bug_table`.`prenom`, `mantis_bug_table`.`sexe`, `mantis_bug_table`.`last_updated` FROM `mantis_bug_table` LEFT JOIN `mantis_bug_text_table` ON `mantis_bug_text_table`.`dossier` =  `mantis_bug_table`.`id` WHERE";
$querie.=($handler>0)?" `handler_id` = ".$handler." AND":"";
$querie.=($reporter>0)?" `reporter_id` = ".$reporter." AND":"";
$querie.=($motif_nvvenue>0)?" `motif_nvvenue` = ".$motif_nvvenue." AND":"";
$querie.=($id>0)?" `mantis_bug_table`.`id` = '".$id."' AND":"";
$querie.=(strlen($nom)>2)?" `nom` like '%".strtoupper($nom)."%' AND":"";
$querie.=(strlen($nomepouse)>2)?" `nomepouse` like '%".$nomepouse."%' AND":"";
$querie.=(strlen($prenom)>2)?" `prenom` like '%".$prenom."%' AND":"";
$querie.=(($du * $au)>2)?" `date_submitted` BETWEEN ".$du." AND ".$au:"";
$querie.=($handler+$reporter+$motif_nvvenue+$du*$au+strlen($nom)+strlen($nomepouse)+strlen($prenom))<=1?" 1":"";
$querie.=" GROUP BY `mantis_bug_table`.`id` LIMIT 0,5000";
$querie = str_replace("AND GROUP", "GROUP", $querie);
#var_dump ($querie);

if($init == 1){
$voila = db_query_bound($querie);
while ($i = db_fetch_array( $voila )){
echo(
'<tr><td><a href="bug_update_page.php?bug_id='.$i['id'].'"><img border="0" width="16" height="16" src="./images/update.png" alt="Modifier le dossier" title="Modifier le dossier"></a></td>'.
'<td><a href="view.php?id='.$i['id'].'">'.$i['id'].'</a></td>'.
'<td><a href="view.php?id='.$i['id'].'">'.$i['nom'].'</a></td>'.
'<td><a href="view.php?id='.$i['id'].'">'.$i['prenom'].'</a></td>'.
'<td>'.get_enum_element('sexe',$i['sexe']).'</td>'.
'<td>'.date(config_get("normal_date_format"),$i['last_updated']).'</td></tr>'
);
}
}
echo '</tbody></table></div>';
html_page_bottom();
#var_dump($querie);