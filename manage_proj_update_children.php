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

	require_once( 'project_hierarchy_api.php' );

	form_security_validate( 'manage_proj_update_children' );

	auth_reauthenticate();

	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$t_subproject_ids = current_user_get_accessible_subprojects( $f_project_id, true );
	foreach ( $t_subproject_ids as $t_subproject_id ) {
		$f_inherit_child = gpc_get_bool( 'inherit_child_' . $t_subproject_id, false );
		project_hierarchy_update( $t_subproject_id, $f_project_id, $f_inherit_child );
	}

	form_security_purge( 'manage_proj_update_children' );

	print_successful_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
