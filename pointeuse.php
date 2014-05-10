<?php
require_once( 'fiche_presence.php' );

#recupere params
$qui = gpc_get_int('qui');
$heure = gpc_get_int('hh');
$min = gpc_get_int('mi');
$jour = gpc_get_int('jj');
$mois = gpc_get_int('mm');
$annee = gpc_get_int('aaaa');
$semaine = gpc_get_int('semn');
$Hpse = gpc_get_string('Hpse',null);

#calcul à l'insert
$aaaammjj = $annee.$mois.$jour;
$maintenant = $heure.":".$min;
$nomJ = toJr($jour,$mois);
$horaire = Hora($nomJ);


#arrive ou part?
$dejala = db_result(db_query_bound(
				"SELECT `arrive` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
				array($aaaammjj,$qui)
				));

#commence ou finit pause?
$enpause = db_result(db_query_bound(
		"SELECT `debut_pause` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
		array($aaaammjj,$qui)
));

#pause consommée aujourd'hui
$pse = db_result(db_query_bound(
		"SELECT `pse` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
		array($aaaammjj,$qui)
));


#si pas arrivé=> insertion, si deja arrive=>update. selon 2 criteres aaaammjj + qui.
if(!$dejala){

			#insertion
			db_query_bound(
			"INSERT INTO pointeuse ( aaaammjj,jour,mois,annee,qui,arrive,semaine,nom_j,horaire_j ) VALUES ( "
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().','
			.db_param().')',
			array( $aaaammjj,$jour,$mois,$annee,$qui,$maintenant,$semaine,$nomJ,$horaire )
			);
			html_meta_redirect( 'bonjour.php' );

#gérer pause ?
} else if ($Hpse && !$enpause) {

				#debut pause
				#champ debut_pause  = temps actuel
					db_query_bound("UPDATE pointeuse SET `debut_pause`=".db_param()." WHERE `aaaammjj`=".db_param()." AND `qui`=".db_param(),
							array($Hpse,$aaaammjj,$qui));
					

} else if ($Hpse && $enpause) {
	

								
				#pse = pse + (temps actuel- debut pause)
						db_query_bound("UPDATE pointeuse SET `pse`=`pse`+(".db_param()."-`debut_pause`) WHERE `aaaammjj`=".db_param()." AND `qui`=".db_param(),
								array( toMin($maintenant), $aaaammjj, $qui) );
						
						db_query_bound("UPDATE pointeuse SET `debut_pause`=".db_param()." WHERE `aaaammjj`=".db_param()." AND `qui`=".db_param(),
							array(0,$aaaammjj,$qui));
						html_meta_redirect( 'bonjour.php' );
						

} else {
				
				#calcul à l'update
					$arrive = db_result(db_query_bound(
							"SELECT `arrive` FROM `pointeuse` WHERE `aaaammjj`=".db_param()." AND `qui`=".db_param(),
							array($aaaammjj,$qui)
					));
					
					$pse = calcpse($pse);
					$total = ($pse>30)?toMin( $maintenant )-toMin( $arrive )-$pse-30:toMin( $maintenant )-toMin( $arrive );
					$hsup = ($total>$horaire)?($total-$horaire):0;
					$hrec = ($total<$horaire)?($horaire-$total):0;
	
					
					db_query_bound(
					"UPDATE pointeuse SET `part`= ".db_param().", `pse`= ".db_param().", `total_j`= ".db_param().", `hsup_j`= ".db_param().", `hrec_j`= ".db_param()."  WHERE `aaaammjj`=".db_param()." AND `qui`=".db_param(),
					array( $maintenant, $pse, $total, $hsup, $hrec, $aaaammjj, $qui )
					);
						echo "Mouvement enregistr&eacute. Vous pouvez &eacute;teindre l&apos;ordinateur.";
}
