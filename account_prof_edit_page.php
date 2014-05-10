<?php



# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#

# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
#  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * This page allows the user to edit his/her profile
	 * Changes get POSTed to account_prof_update.php
	 *
	 * @package UFM
	*
	*
	 * @link 
	 */
	/**
	 * UFM Core API's
	 */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'profile_api.php' );

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	$f_profile_id	= gpc_get_int( 'profile_id' );

	if ( profile_is_global( $f_profile_id ) ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

		$row = profile_get_row( ALL_USERS, $f_profile_id );
	} else {
		$row = profile_get_row( auth_get_current_user_id(), $f_profile_id );
	}

   	extract( $row, EXTR_PREFIX_ALL, 'v' );

	html_page_top();

	if ( profile_is_global( $f_profile_id ) ) {
		print_manage_menu();
	}
?>

<?php # Edit Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prof_update.php">
<?php  echo form_security_field( 'profile_update' )?>
<input type="hidden" name="action" value="update" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />
		<?php echo lang_get( 'edit_profile_title' ) ?>
	</td>
	<td class="right">
		<?php
			if ( !profile_is_global( $f_profile_id ) ) {
				print_account_menu();
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="description" width="25%">
		<span class="required">*</span><?php echo lang_get( 'nom' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="nom" size="32" maxlength="32" value="<?php echo string_attribute( $v_nom ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="description">
		<span class="required">*</span><?php echo lang_get( 'prenom' ) ?>
	</td>
	<td>
		<input type="text" name="prenom" size="32" maxlength="32" value="<?php echo string_attribute( $v_prenom ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="description">
		<span class="required">*</span><?php echo lang_get( 'telephone' ) ?>
	</td>
	<td>
		<input type="text" name="telephone" size="16" maxlength="16" value="<?php echo string_attribute( $v_telephone ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="description">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8"><?php echo string_textarea( $v_description ) ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'update_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php
html_page_bottom();
