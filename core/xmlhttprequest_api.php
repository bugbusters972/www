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
 * @package CoreAPI
 * @subpackage XMLHttpRequestAPI
*
*
 * @link 
 */

/**
 * requires bug_api
 */
require_once( 'bug_api.php' );
/**
 * requires profile_api
 */
require_once( 'profile_api.php' );
/**
 * requires logging_api
 */
require_once( 'logging_api.php' );
/**
 * requires projax_api
 */
require_once( 'projax_api.php' );

/**
 *
 * @return null
 * @access public
 */
function xmlhttprequest_issue_reporter_combobox() {
	$f_bug_id = gpc_get_int( 'issue_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	$t_reporter_id = bug_get_field( $f_bug_id, 'reporter_id' );
	$t_project_id = bug_get_field( $f_bug_id, 'project_id' );

	echo '<select name="reporter_id">';
	print_reporter_option_list( $t_reporter_id, $t_project_id );
	echo '</select>';
}

/**
 * Print a generic combobox with a list of users above a given access level.
 */
function xmlhttprequest_user_combobox() {
	$f_user_id = gpc_get_int( 'user_id' );
	$f_user_access = gpc_get_int( 'access_level' );

	echo '<select name="user_id">';
	print_user_option_list( $f_user_id, ALL_PROJECTS, $f_user_access );
	echo '</select>';
}

/**
 * Echos a serialized list of noms starting with the prefix specified in the $_POST
 * @return null
 * @access public
 */
function xmlhttprequest_nomepouse_get_with_prefix() {
	$f_nomepouse = gpc_get_string( 'nomepouse' );

	$t_unique_entries = profile_get_field_all_for_user( 'nomepouse' );
	$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_nomepouse );

	echo projax_array_serialize_for_autocomplete( $t_matching_entries );
}

function xmlhttprequest_nom_get_with_prefix() {
	$f_nom = gpc_get_string( 'nom' );

	$t_unique_entries = profile_get_field_all_for_user( 'nom' );
	$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_nom );

	echo projax_array_serialize_for_autocomplete( $t_matching_entries );
}

/**
 * Echos a serialized list of OSes starting with the prefix specified in the $_POST
 * @return null
 * @access public
 */
 function xmlhttprequest_prenom_get_with_prefix() {
	$f_prenom = gpc_get_string( 'prenom' );

	$t_unique_entries = profile_get_field_all_for_user( 'prenom' );
	$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_prenom );

	echo projax_array_serialize_for_autocomplete( $t_matching_entries );
}

/**
 * Echos a serialized list of OS Versions starting with the prefix specified in the $_POST
 * @return null
 * @access public
 */
function xmlhttprequest_telephone_get_with_prefix() {
	$f_telephone = gpc_get_string( 'telephone' );

	$t_unique_entries = profile_get_field_all_for_user( 'telephone' );
	$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_telephone );

	echo projax_array_serialize_for_autocomplete( $t_matching_entries );
}
