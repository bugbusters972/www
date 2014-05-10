<?php
	require_once( 'core.php' );
	html_robots_noindex();

	html_page_top( lang_get( 'report_bug_link' ) );
	include('salleattente_inc.php');
?>
<form class="appnitro" name="stat_form" method="post" action="stats.php">
	<div class="form_description">
			<h2>S&eacute;lectionner une p&eacute;riode</h2>
	</div>
	<ul class="part0">
	<span class="inline33">
	<li  <?php echo helper_alternate_class() ?>>
	<label class="description">Du</label>
	<input name="datedep" value="jj/mm/aaaa"/>
	</li>
	<li  <?php echo helper_alternate_class() ?>>
	<label class="description">..au</label>
	<input name="datefin" value="jj/mm/aaaa"/>
	</li>
	</span>
	<li class="buttons">
		<input <?php echo helper_get_tab_index() ?> type="submit" value="Valider" />
	</li>
	</ul>
</form>
<?php
html_page_bottom();
