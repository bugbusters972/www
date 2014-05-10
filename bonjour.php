<?php
require_once( 'core.php' );
require_once( 'fiche_presence.php' );

estconnecte();
html_robots_noindex();

$qui = auth_get_current_user_id();
$hh = date( 'H',db_now() );
$mi = date( 'i',db_now() );

$jj = date( 'd',db_now() );
$mm = date( 'm',db_now() );
$aaaa = date( 'Y',db_now() );

$jj = intval($jj);
$mm = intval($mm);
$aaaa= intval($aaaa);

$semn = date( 'W', db_now() );


$dejala = db_result(db_query_bound(
		"SELECT `arrive` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
		array($aaaa.$mm.$jj,$qui)
));

$pse_on = db_result(db_query_bound(
		"SELECT `debut_pause` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
		array($aaaa.$mm.$jj,$qui)
));

#pause consommée aujourd'hui
$pse = db_result(db_query_bound(
		"SELECT `pse` FROM `pointeuse` WHERE `aaaammjj` =".db_param()." AND `qui` =".db_param(),
		array($aaaa.$mm.$jj,$qui)
));

$t_access_level = get_enum_element( 'access_levels', current_user_get_access_level() );

?>

<html>
<head>
<link href="./css/bonjour.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="javascript/min/jquery-1.9.1.js"></script>
<script type="text/javascript">
function Onoff(){
	$('.menupause').toggle('fast'); 
}

function Pointe(qui,aaaa,mm,jj,hpse,hh,mi,semn){
	var url = 'pointeuse.php?qui='+qui+'&aaaa='+aaaa+'&mm='+mm+'&jj='+jj+'&Hpse='+hpse+'&hh='+hh+'&mi='+mi+'&semn='+semn;
	$.get( url, function(){$('.btnpause h3').append(' - OK');})
}


$(document).ready(function(){

	$('.btnpause').hover(
		function(){
		$(this).toggleClass('btnpause-hover');
		});

	<?php 
			if($pse_on){
				echo "Onoff();";
			}
			?>

$('.btnpause h6').css('width','<?php echo 100-$pse*100/30;?>%');
$('.refresh').fadeOut( 3 ).delay( 30000 ).fadeIn( 400 );
});
</script>
<?php
if ($dejala && $hh>15){
	echo "<style> body { background-color: black; } .pointeuse .btn { background-color: rgb(230,76,101); }</style>";
} 
?>

</head>

<body>

<!-- Fenêtre refresh -->
<div class="refresh">

<p><a href="javascript:location.reload();">
CLIQUEZ<br/>
ICI POUR<br/>
RAFRAICHIR
</a></p>

</div>

<!-- Menu pause -->
<div class="menupause">
	<h3>TEMPS DE PAUSE CONSOMM&Eacute; :<br/> <?php echo toH($pse);?> (sur 30min)</h3>
	<div class="btnpause">	
	<?php 
	echo "<a href='javascript:Pointe(".$qui.",".$aaaa.",".$mm.",".$jj.",".toMin($hh.":".$mi).",".$hh.",".$mi.",".$semn.");'>";
	if (!$pse_on){
		
		echo "<h3>DEMARRER PAUSE</h3>";
		
	} else {
		
		echo "<h3>TERMINER PAUSE</h3>";
		
	}
	?>
	</a>
	</div>
	<div class="annul">
	<a onclick="javascript:Onoff();">Retour</a>
	</div>
</div>

<div class="calendrier">
	<h1><?php echo strtoupper( date( 'l',db_now()) ); ?></h1>
	<h2><?php echo $jj; ?></h2>
	<h3><?php echo date( 'H:i',db_now()); ?></h3>
</div>

<?php 

#bouton pause
if( $dejala ){
	echo "<div class='btnpause' onclick='javascript:Onoff();'>
			<h3>PAUSE</h3>
			<span><h6></h6></span>
		</div>";	
}
?>

<div class="pointeuse">

	<h3>
	<?php 
	if(!$dejala){
		echo "BONJOUR, ".strtoupper(user_get_name($qui));
	} else {
		echo "AU REVOIR, ".strtoupper(user_get_name($qui));
	}
	?>
	</h3>
	
	<div class="vousetes">
		<p><?php echo user_get_name($qui)." - ".$t_access_level; ?></p>
	</div>
	
	<div class="btn">
		<a href="
			<?php 
			echo "pointeuse.php?qui=".$qui
								."&jj=".$jj
								."&mm=".$mm
								."&aaaa=".$aaaa
								."&hh=".$hh
								."&mi=".$mi
								."&semn=".$semn
			?>
			">
			<?php
			if(!$dejala){ echo "J&apos;ARRIVE"; } else { echo "JE PARS"; } 
			?>
		</a>
	</div>
</div>

</body>
</html>