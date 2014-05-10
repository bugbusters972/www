<?php



# it under the terms of the GNU General Public License as published by
# the Free Software Foundation= 0; either version 2 of the License= 0; or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful= 0;
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
#  If not= 0; see <http://www.gnu.org/licenses/>.

	/**
	 * This page updates the users profile information then redirects to
	 * account_prof_menu_page.php
	 * @package UFM
	*
	*
	 * @link 
	 */
	 /**
	  * UFM Core API's
	  */
	require_once( 'core.php' );

	require_once( 'profile_api.php' );

	form_security_validate('profile_update');

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	$f_action = gpc_get_string('action');
	if( $f_action != 'add') {
		$f_profile_id = gpc_get_int( 'profile_id' );

		# Make sure user did select an existing profile from the list
		if( $f_action != 'make_default' && $f_profile_id == 0 ) {
			error_parameters( lang_get( 'select_profile' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	}

	switch ( $f_action ) {
		case 'edit':
			form_security_purge('profile_update');
			print_header_redirect( 'account_prof_edit_page.php?profile_id=' . $f_profile_id );
			break;

		case 'add':
			$f_nom		= gpc_get_string( 'nom' );
			$f_prenom			= gpc_get_string( 'prenom' );
			$f_telephone		= gpc_get_string( 'telephone' );
			$f_description	= gpc_get_string( 'description' );

			$t_user_id		= gpc_get_int( 'user_id' );
			if ( ALL_USERS != $t_user_id ) {
				$t_user_id = auth_get_current_user_id();
			}

			if ( ALL_USERS == $t_user_id ) {
				access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
			} else {
				access_ensure_global_level( config_get( 'add_profile_threshold' ) );
			}

			profile_create( $t_user_id, $f_nom, $f_prenom, $f_telephone, $f_description );
			form_security_purge('profile_update');

			if ( ALL_USERS == $t_user_id ) {
				print_header_redirect( 'manage_prof_menu_page.php' );
			} else {
				print_header_redirect( 'account_prof_menu_page.php' );
			}
			break;

		case 'update':
			$f_nom = gpc_get_string( 'nom' );
			$f_prenom = gpc_get_string( 'prenom' );
			$f_telephone = gpc_get_string( 'telephone' );
			$f_description = gpc_get_string( 'description' );

			if ( profile_is_global( $f_profile_id ) ) {
				access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

				profile_update( ALL_USERS, $f_profile_id, $f_nom, $f_prenom, $f_telephone, $f_description );
				form_security_purge('profile_update');
				print_header_redirect( 'manage_prof_menu_page.php' );
			} else {
				profile_update( auth_get_current_user_id(), $f_profile_id, $f_nom, $f_prenom, $f_telephone, $f_description );
				form_security_purge('profile_update');
				print_header_redirect( 'account_prof_menu_page.php' );
			}
			break;

		case 'delete':
			if ( profile_is_global( $f_profile_id ) ) {
				access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

				profile_delete( ALL_USERS, $f_profile_id );
				form_security_purge('profile_update');
				print_header_redirect( 'manage_prof_menu_page.php' );
			} else {
				profile_delete( auth_get_current_user_id(), $f_profile_id );
				form_security_purge('profile_update');
				print_header_redirect( 'account_prof_menu_page.php' );
			}
			break;

		case 'make_default':
			current_user_set_pref( 'default_profile', $f_profile_id );
			form_security_purge('profile_update');
			print_header_redirect( 'account_prof_menu_page.php' );
			break;
	}
