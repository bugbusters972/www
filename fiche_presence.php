<?php
require_once( 'core.php' );

$mois = gpc_get_int('mois',0);

function nom_mois($m){
	$nom_mois = array ( 1=>'Janvier', 2=>'Fevrier', 3=>'Mars', 4=>'Avril', 5=>'Mai', 6=>'Juin',	7=>'Juillet',
		8=>'Aout', 9=>'Septembre', 10=>'Octobre', 11=>'Novembre', 12=>'Decembre' );
	return $nom_mois[$m];
}

function toMin($hhmin){
	list($hh, $min) = split(':',$hhmin);
	$xmin = intval($hh)*60+intval($min);
	return $xmin;
}

function toH($min){
	$hh = floor( $min/60);
	$min= $min % 60;
	return $hh.':'.$min;
}

function toJr($jj,$mm){
	$aaaa = ($mm>date('n'))?date('Y')-1:date('Y');
	$nom_jour = date("D", mktime(0, 0, 0, $mm, $jj, $aaaa));
	return $nom_jour;
}

function toSm($jj,$mm){
	$aaaa = ($mm>date('n'))?date('Y')-1:date('Y');
	$n_semn = date("W", mktime(0, 0, 0, $mm, $jj, $aaaa));
	return $n_semn;
}

function Hora($jour){
	switch ($jour){
		case "Mon":
			return 510;
			break;
			
		case "Tue":
			return 270;
			break;
			
		case "Wed":
			return 510;
			break;
			
		case "Thu":
			return 510;
			break;
			
		case "Fri":
			return 300;
			break;
	}
}

function calcpse($pse){
	$flo = $pse/30;
	$int = intval($flo);
	
	return $pse=($flo>$int)?30*($int+1):$pse;

	
}

function Report($mois){
	if($mois>0) {
	header("Content-type: application/vnd.ms-excel");
	header("Content-disposition: attachment; filename=fiche_presence.csv");

#lister les user qui ont pointé le mois dernier
	$q_users = db_query_bound("SELECT `qui` FROM `pointeuse` WHERE `mois`=".db_param()." GROUP BY `qui`",$mois);
	$users = array();
	while($ligne = db_fetch_array( $q_users )){
		$users[] = $ligne['qui'];
	}

foreach($users as $user){

	echo "\n\nFICHE DE PRESENCE DE ".strtoupper(user_get_name($user)).";;;;;;;;;;;;\n";
	echo "Mois de ".nom_mois($mois).";;;;;;;;;;;Horaire: 35H Hebdo;\n";
	echo "Jour;DATE;H arr;H dep;PSE;TOTAL;horaire;h sup;h recup;solde;Cong annuel;Cong mat;Motif;\n";

#par jour
	$q_usrday = db_query_bound("SELECT * FROM `pointeuse` WHERE `mois`=".db_param()." AND `qui`=".db_param()." ORDER BY `aaaammjj`",array($mois,$user));
	while($ligne = db_fetch_array( $q_usrday )){

		echo $ligne['nom_j'].';'
			.$ligne['jour'].';'
			.$ligne['arrive'].';'
			.$ligne['part'].";"
			.toH($ligne['pse']).";"
			.toH($ligne['total_j']).";"
			.toH($ligne['horaire_j']).";"
			.toH($ligne['hsup_j']).";"
			.toH($ligne['hrec_j']).";;;;\n";
	}
	
#par semaine
	$q_usrweek = db_query_bound("SELECT `semaine`, SUM(`total_j`),SUM(`horaire_j`),SUM(`hsup_j`),SUM(`hrec_j`) FROM `pointeuse`
			 WHERE `qui`=".db_param()." AND `semaine` BETWEEN ( WEEKOFYEAR(NOW())-5 ) AND WEEKOFYEAR(NOW()) GROUP BY `semaine`",$user);
	while($ligne = db_fetch_array( $q_usrweek )){
		echo 	"Total semaine ".$ligne['semaine'].";;;;;"
				.toH($ligne['SUM(`total_j`)']).";"
				.toH($ligne['SUM(`horaire_j`)']).";"
				.toH($ligne['SUM(`hsup_j`)']).";"
				.toH($ligne['SUM(`hrec_j`)']).";;;;;\n";
	}
	
	}
	
#par mois
	$q_totalmois = db_query_bound( "SELECT `mois`, SUM(`total_j`),SUM(`horaire_j`),SUM(`hsup_j`),SUM(`hrec_j`) FROM `pointeuse` WHERE `mois`=".db_param(),$mois	);
	while ($ligne = db_fetch_array( $q_totalmois )){
	
	echo "\n\n;;;;;Heures travail;Horaire initial;h sup(+);h recup (-);;Cong.ann.;Cong.mat;\n";
	
	echo 	"\nTotal du mois de "
			.nom_mois($ligne['mois']).";;;;;"
			.toH($ligne['SUM(`total_j`)']).";"
			.toH($ligne['SUM(`horaire_j`)']).";"
			.toH($ligne['SUM(`hsup_j`)']).";"
			.toH($ligne['SUM(`hrec_j`)']).";;;;;\n";
	
	echo	"\nSolde mensuel;;;;;"
			.toH($ligne['SUM(`hsup_j`)']-$ligne['SUM(`hrec_j`)']).";;;;";
		}

}}

Report($mois);

?>