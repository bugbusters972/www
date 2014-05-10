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

	form_security_validate( 'manage_proj_update' );

	auth_reauthenticate();

	$f_project_id 	= gpc_get_int( 'project_id' );
	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_status 		= gpc_get_int( 'status' );
	$f_view_state 	= gpc_get_int( 'view_state' );
	$f_file_path 	= gpc_get_string( 'file_path', '' );
	$f_enabled	 	= gpc_get_bool( 'enabled' );
	$f_inherit_global = gpc_get_bool( 'inherit_global', 0 );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	project_update( $f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled, $f_inherit_global );
	event_signal( 'EVENT_MANAGE_PROJECT_UPDATE', array( $f_project_id ) );

	form_security_purge( 'manage_proj_update' );

	print_header_redirect( 'manage_proj_page.php' );
