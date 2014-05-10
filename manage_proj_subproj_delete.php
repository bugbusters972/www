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

	form_security_validate( 'manage_proj_subproj_delete' );

	auth_reauthenticate();

	$f_project_id    = gpc_get_int( 'project_id' );
	$f_subproject_id = gpc_get_int( 'subproject_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	project_hierarchy_remove( $f_subproject_id, $f_project_id );

	form_security_purge( 'manage_proj_subproj_delete' );

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

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
