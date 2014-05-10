<?php
if ( access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $f_bug_id ) ) {
	
	$t_users = bug_get_monitors( $f_bug_id );
	$num_users = sizeof ( $t_users );

	echo '<a name="monitors" id="monitors" /><br />';

?>
<table width="780" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
<tr class="row-1">
	<td class="description" width="33%">
		<?php if($num_users){echo $num_users.' demande(s) de rappel(s) pour';}else{
		echo "Attribuer une demande de rappel &agrave;...";} ?>
	</td>
	<td width="33%">
<?php
		if ( 0 == $num_users ) {
			#echo lang_get( 'no_users_monitoring_bug' );
			echo "&nbsp;";
		} else {
			$t_can_delete_others = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $f_bug_id ); 
	 		for ( $i =0; $i < $num_users; $i++ ) {
				echo ($i > 0) ? ', ' : '';
				echo print_user( $t_users[$i] );
				if ( $t_can_delete_others ) {
					echo ' [<a class="small" href="' . helper_mantis_url( 'bug_monitor_delete.php' ) . '?bug_id=' . $f_bug_id . '&user_id=' . $t_users[$i] . form_security_param( 'bug_monitor_delete' ) . '">' . lang_get( 'delete_link' ) . '</a>]';
				}
	 		}
 		}

		if ( access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $f_bug_id ) ) {
			echo '<br /><br />';
?></td>
		<td><form method="get" action="bug_monitor_add.php">
		<?php echo form_security_field( 'bug_monitor_add' ) ?>
			<input type="hidden" name="bug_id" value="<?php echo (integer)$f_bug_id; ?>" />
			<label for="username">Entrez le nom de l&apos;utilisatrice</label>
			<input type="text" name="username" />
			<input type="submit" value="<?php echo lang_get( 'add_user_to_monitor' ) ?>" />
		</form>
		<?php } ?>
	</td>
</tr>
</table>


<?php 
} # show monitor list
?>
