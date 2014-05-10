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
	 * CALLERS
	 * This page is called from:
	 * - print_account_menu()
	 * - header redirects from account_*.php
	 *
	 * EXPECTED BEHAVIOUR
	 * - Display the user's current preferences
	 * - Allow the user to edit the preferences
	 * - Provide the option of saving changes or resetting to default values
	 *
	 * CALLS
	 * This page calls the following pages:
	 * - acount_prefs_update.php  (to save changes)
	 * - account_prefs_reset.php  (to reset preferences to default values)
	 *
	 * RESTRICTIONS & PERMISSIONS
	 * - User must be authenticated
	 * - The user's account must not be protected
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

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	include( 'account_prefs_inc.php' );

	html_page_top( lang_get( 'change_preferences_link' ) );

	edit_account_prefs();

	html_page_bottom();
