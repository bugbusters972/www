<?php
	require_once( 'core.php' );
	auth_ensure_user_authenticated();
$init = gpc_get_int( 'init',0);

$usr = current_user_get_field( 'id' );

	html_robots_noindex();

	html_page_top1();
	html_page_top2a();

	if($init == 1){
	form_security_validate( 'reset_mdp' );
		$psswd= gpc_get_string( 'password');
		$query = "UPDATE `mantis_user_table` SET `password`=md5(". db_param().") WHERE `id` =". db_param();
		$up = db_query_bound( $query, Array( $psswd, $usr ) );
		echo "<div><p>Mot de passe modifi&eacute; avec succ&egrave;s.</p>
		<p><a href='my_view_page.php'>Cliquez ici pour retourner &agrave; l&apos;accueil</a></p>
		</div>";
	} else {
?>

<div id="form_container">
<h1><a></a></h1>
<form class="appnitro" name="reset_form" method="post" action="reset_mdp.php">
<?php echo form_security_field( 'reset_mdp' ) ?>
<div class="form_description">
	<h2>Modifier mot de passe</h2>
</div>
<ul>
<li class="row-1">
	<label class="description">Entrez votre nouveau mot de passe</label>
	<span>
	<label>	<?php echo lang_get( 'password' ) ?></label>
	<input type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
	<input type="hidden" name="init" value="1"/>
	</span>
</li>
<li>
	<div class="center" colspan="2">
		<input type="submit" value="Enregistrer" />
	</div>
</li>
</ul>
</form>
<?php
}
	html_page_bottom1a( __FILE__ );
