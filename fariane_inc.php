<div id="fariane">
<?php
#nom, prenom
if ($tpl_bug){
echo '<h5> Fiche de : '.string_display_line( $tpl_bug->prenom ).'&nbsp;'.string_display_line( $tpl_bug->nom ).'</h5><hr/>';
}
?>
<ul id="step">
</ul>
<ul id="action_left">
<?php
#ajoute probleme
#ajoute réponse		
if ($f_bug_id){
echo html_buttons_view_bug_page( $tpl_bug_id );
}

echo '<li><hr/><h5>CHANGER DE DOSSIER</h5>

<form method="post" action="' . helper_mantis_url( 'jump_to_bug.php">' );

if( ON == config_get( 'use_javascript' ) ) {
$t_bug_label = lang_get( 'issue_id' );
	echo "<input type=\"text\" name=\"bug_id\" size=\"10\" class=\"small\" value=\"$t_bug_label\" onfocus=\"if (this.value == '$t_bug_label') this.value = ''\" onblur=\"if (this.value == '') this.value = '$t_bug_label'\" />&#160;";
} else {
	echo "<input type=\"text\" name=\"bug_id\" size=\"10\" class=\"small\" />&#160;";
}

echo '<input type="submit" class="button-small" value="' . lang_get( 'jump' ) . '" />&#160;';
echo '</form></li>';
?>
<li class="enrdecoy">
<hr/>
<input type="submit" onclick="$('.appnitro').submit();" value="Enregistrer tout"/>
</li>
</ul>
</div>