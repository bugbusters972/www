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
	 * This is the first page a user sees when they login to the bugtracker
	 * News is displayed which can notify users of any important changes
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

	require_once( 'logging_api.php' );
	require_once( 'xmlhttprequest_api.php' );

	auth_ensure_user_authenticated();

	$f_entrypoint = gpc_get_string( 'entrypoint' );

	$t_function = 'xmlhttprequest_' . $f_entrypoint;
	if ( function_exists( $t_function ) ) {
		log_event( LOG_AJAX, "Calling {$t_function}..." );
		call_user_func( $t_function );
	} else {
		log_event( LOG_AJAX, "Unknown function for entry point = " . $t_function );
		echo 'unknown entry point';
	}
