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
	 * @package UFM
	*
	*
	 * @link 
	 */
	 /**
	  * UFM Core API's
	  */
	require_once( 'core.php' );

	require_once( 'version_api.php' );

	form_security_validate( 'manage_proj_ver_delete' );

	auth_reauthenticate();

	$f_version_id = gpc_get_int( 'version_id' );

	$t_version_info = version_get( $f_version_id );
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_version_info->project_id;

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version_info->project_id );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'version_delete_sure' ) .
		'<br />' . lang_get( 'version' ) . ': ' . string_display_line( $t_version_info->version ),
		lang_get( 'delete_version_button' ) );

	version_remove( $f_version_id );

	form_security_purge( 'manage_proj_ver_delete' );

	html_page_top( null, $t_redirect_url );
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
