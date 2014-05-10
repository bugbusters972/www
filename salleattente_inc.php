<div id="salle" title="Personnes re&ccedil;ues ce-jour, dont le dossier n'est pas encore pris en charge (voir champ &apos;Attente&apos;).">
<ul>
<h5>SALLE D&apos;ATTENTE</h5>
<?php
date_default_timezone_set('America/Martinique');
$hier = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
$demain = mktime(0, 0, 0, date('m'), date('d')+1, date('Y'));
/*var_dump(date(config_get('normal_date_format'),$hier));
var_dump($demain);*/

$enattente = db_query_bound('SELECT `mantis_bug_table`.`id`,`mantis_bug_table`.`nom`,`mantis_bug_text_table`.`date_nvvenue` FROM `mantis_bug_table` INNER JOIN `mantis_bug_text_table` ON mantis_bug_table.id = mantis_bug_text_table.dossier WHERE `attente`=1 AND `date_nvvenue` BETWEEN '.db_param().' AND  '.db_param(), array($hier,$demain));

while ($i = db_fetch_array($enattente)){
echo '<li><a href="view.php?id='.$i['id'].'">'.$i['nom'].',<br/> le '.date(config_get('normal_date_format'),$i['date_nvvenue']).'</a></li><hr/>';
}
?>
</ul>
</div>