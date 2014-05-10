<?php
require_once( 'core.php' );
	
$du = gpc_get_int('datedep');
$au =  gpc_get_int('datefin','');

#appels téléphoniques
$appels = db_result(db_query_bound('SELECT COUNT(`id`) as appels FROM `mantis_bug_text_table` where `type_contact` =30 AND `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#venues sur place
$sur_place = db_result(db_query_bound('SELECT COUNT(`id`) as sur_place FROM `mantis_bug_text_table` where `type_contact` =10 AND `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) ));

#frequentation liées aux violences
$lievio = db_result(db_query_bound('SELECT COUNT(`id`) FROM `mantis_bug_text_table` where `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param().' AND `motif_nvvenue` =20 OR `motif_nvvenue`=80',array($du,$au) ));

#modalités de contact
$orig_orient = db_query_bound('SELECT `orig_orient` FROM `mantis_bug_table`
WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) );

#tranches dâge
$dn = db_query_bound('SELECT date_naissance, date_submitted FROM mantis_bug_table WHERE `last_updated` BETWEEN '.db_param().' AND  '.db_param(),array($du,$au) );

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

header("Content-type: application/vnd.ms-excel");
header("Content-disposition: attachment; filename=stats.csv");
echo "Statistiques du ".date(config_get('short_date_format'),$du)." au ".date(config_get('short_date_format'),$au).";\n";
echo"Donnée;Nbre de pers.\n
Nombre d'appels;".$appels."\n";
echo "Nombre de venues;".$sur_place."\n";
echo"Nombre de fréquentation liées aux violences;".$lievio."\n";
echo "Origine;\nFrance;".$francaise."\n";
echo "Etranger;".$etrangeres."\n
Répartition nationalités;\n";
Imprimstat('nationalite');
echo "Orientation;\n";
Impstatexp ('orig_orient');
echo"Situation matrimoniale;\n";
Imprimstat('matrim');
echo"Femme avec enfant;".$avecenf."\n";
echo"Femme sans enfant;".$sansenf."\n";
echo"Femme en couple;".$encouple."\n";
echo"Femme seule;".$seule."\n";
echo"Nombre d'enfants;\n";
while ($ligne = db_fetch_array( $nb_enfant )){
echo $ligne['nb_enfant'].' enfant(s),'.$ligne['nombre'].'\n';
}
echo "Situation professionnelle;\n";
Imprimstat('sit_prof');
echo"Nature des violences;\n";
Impstatexp('nat_vio_cpl');
echo"Ancienneté des violences;\n";
Imprimstat('dur_violence');
echo "Motif de contact;\n";
Imprimstat('motif_nvvenue');
echo"Besoins des femmes;\n";
Impstatexp('bes_vio');
echo"Répartition par âge;\n";
while ($i = db_fetch_array( $dn )){
	$f [] = date('Y',$i['date_submitted']) - date('Y',$i['date_naissance']);
	};
	if(is_array($f)){
	foreach(array_count_values($f) as $age=>$num){
	echo $age.' ans;'.$num."\n";}}