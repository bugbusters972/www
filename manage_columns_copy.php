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

	require_once( 'columns_api.php' );
	require_once( 'gpc_api.php' );
	require_once( 'helper_api.php' );

	form_security_validate( 'manage_columns_copy' );

	auth_reauthenticate();

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );
	$f_manage_page		= gpc_get_bool( 'manage_page' );

	if ( $f_copy_from ) {
		$t_src_project_id = $f_other_project_id;
		$t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
		$t_src_project_id = $f_project_id;
		$t_dst_project_id = $f_other_project_id;
	} else {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	# only admins can set global defaults.for ALL_PROJECT
	if ( $f_manage_page && $t_dst_project_id == ALL_PROJECTS && !current_user_is_administrator() ) {
		access_denied();
	}

	# only MANAGERS can set global defaults.for a project
	if ( $f_manage_page && $t_dst_project_id != ALL_PROJECTS ) {
		access_ensure_project_level( MANAGER, $t_dst_project_id );
	}

	# user should only be able to set columns for a project that is accessible.
	if ( $t_dst_project_id != ALL_PROJECTS ) {
		access_ensure_project_level( VIEWER, $t_dst_project_id );
	}

	# Calculate the user id to set the configuration for.
	if ( $f_manage_page ) {
		$t_user_id = NO_USER;
	} else {
		$t_user_id = auth_get_current_user_id();
	}

	$t_all_columns = columns_get_all();
	$t_default = null;

	$t_view_issues_page_columns = config_get( 'view_issues_page_columns', $t_default, $t_user_id, $t_src_project_id );
	$t_view_issues_page_columns = columns_remove_invalid( $t_view_issues_page_columns, $t_all_columns );

	$t_print_issues_page_columns = config_get( 'print_issues_page_columns', $t_default, $t_user_id, $t_src_project_id );
	$t_print_issues_page_columns = columns_remove_invalid( $t_print_issues_page_columns, $t_all_columns );

	$t_csv_columns = config_get( 'csv_columns', $t_default, $t_user_id, $t_src_project_id );
	$t_csv_columns = columns_remove_invalid( $t_csv_columns, $t_all_columns );

	$t_excel_columns = config_get( 'excel_columns', $t_default, $t_user_id, $t_src_project_id );
	$t_excel_columns = columns_remove_invalid( $t_excel_columns, $t_all_columns );

	config_set( 'view_issues_page_columns', $t_view_issues_page_columns, $t_user_id, $t_dst_project_id );
	config_set( 'print_issues_page_columns', $t_print_issues_page_columns, $t_user_id, $t_dst_project_id );
	config_set( 'csv_columns', $t_csv_columns, $t_user_id, $t_dst_project_id );
	config_set( 'excel_columns', $t_excel_columns, $t_user_id, $t_dst_project_id );

	form_security_purge( 'manage_columns_copy' );

	$t_redirect_url = $f_manage_page ? 'manage_config_columns_page.php' : 'account_manage_columns_page.php';
	print_header_redirect( $t_redirect_url );
