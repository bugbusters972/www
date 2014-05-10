<?php
	require_once( 'core.php' );

	if ( isset( $g_global_profiles ) ) {
		$g_global_profiles = true;
	} else {
		$g_global_profiles = false;
	}

	require_once( 'current_user_api.php' );

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	if ( $g_global_profiles ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
	} else {
		access_ensure_global_level( config_get( 'add_profile_threshold' ) );
	}

	html_page_top( lang_get( 'manage_profiles_link' ) );

	if ( $g_global_profiles ) {
		print_manage_menu( 'manage_prof_menu_page.php' );
	}

	if ( $g_global_profiles ) {
		$t_user_id = ALL_USERS;
	} else {
		$t_user_id = auth_get_current_user_id();
	}

	# Add Profile Form BEGIN
?>
<br />
<div align="center">
<form method="post" action="account_prof_update.php">
<?php  echo form_security_field( 'profile_update' )?>
<input type="hidden" name="action" value="add" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
		<?php echo lang_get( 'add_profile_title' ) ?>
	</td>
	<td class="right">
	<?php
		if ( !$g_global_profiles ) {
			print_account_menu( 'account_prof_menu_page.php' );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="description" width="25%">
		<span class="required">*</span><?php echo lang_get( 'nom' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="nom" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="description">
		<span class="required">*</span><?php echo lang_get( 'prenom' ) ?>
	</td>
	<td>
		<input type="text" name="prenom" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="description">
		<span class="required">*</span><?php echo lang_get( 'telephone' ) ?>
	</td>
	<td>
		<input type="text" name="telephone" size="16" maxlength="16" />
	</td>
</tr>
<tr class="row-2">
	<td class="description">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8"></textarea>
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input type="submit" value="<?php echo lang_get( 'add_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php 
	# Add Profile Form END
	# Edit or Delete Profile Form BEGIN

	$t_profiles = profile_get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>
<br />
<div align="center">
<form method="post" action="account_prof_update.php">
<?php  echo form_security_field( 'profile_update' )?>
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_or_delete_profiles_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<input type="radio" name="action" value="edit" checked="checked" /> <?php echo lang_get( 'edit_profile' ) ?>
<?php
	if ( !$g_global_profiles ) {
?>
		<input type="radio" name="action" value="make_default" /> <?php echo lang_get( 'make_default' ) ?>
<?php
	}
?>
		<input type="radio" name="action" value="delete" /> <?php echo lang_get( 'delete_profile' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="description" width="25%">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td width="75%">
		<select name="profile_id">
			<?php print_profile_option_list( $t_user_id, '', $t_profiles ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'submit_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php 
} # Edit or Delete Profile Form END

html_page_bottom();
