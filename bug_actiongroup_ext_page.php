<?php
	require_once( 'core.php' );
	require_once( 'bug_group_action_api.php' );

	$t_external_action = utf8_strtolower( utf8_substr( $f_action, utf8_strlen( $t_external_action_prefix ) ) );
	$t_form_name = 'bug_actiongroup_' . $t_external_action;

	bug_group_action_init( $t_external_action );

	bug_group_action_print_top();
?>

	<br />

	<div align="center">
	<form method="post" action="bug_actiongroup_ext.php">
<?php echo form_security_field( $t_form_name ); ?>
		<input type="hidden" name="action" value="<?php echo string_attribute( $t_external_action ) ?>" />
<table class="width75" cellspacing="1">
	<?php
		bug_group_action_print_title( $t_external_action );
		bug_group_action_print_hidden_fields( $f_bug_arr );
		bug_group_action_print_action_fields( $t_external_action );
	?>
</table>
	</form>
	</div>

	<br />

<?php
	bug_group_action_print_bug_list( $f_bug_arr );
	bug_group_action_print_bottom();
