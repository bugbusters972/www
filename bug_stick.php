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
	 * This file sticks or unsticks a bug to the top of the view page
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

	require_once( 'bug_api.php' );

	form_security_validate( 'bug_stick' );

	$f_bug_id = gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id, true );
	$f_action = gpc_get_string( 'action' );

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	access_ensure_bug_level( config_get( 'set_bug_sticky_threshold' ), $f_bug_id );

	bug_set_field( $f_bug_id, 'sticky', 'stick' == $f_action );

	form_security_purge( 'bug_stick' );

	print_successful_redirect_to_bug( $f_bug_id );
?>
