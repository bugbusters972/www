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
	 * @package UFM
	*
	*
	 * @link 
	 */
	 /**
	  * UFM Core API's
	  */
	require_once( 'core.php' );

	require_once( 'authentication_api.php' );
	require_once( 'columns_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'helper_api.php' );

	html_page_top( lang_get( 'manage_columns_config' ) );

	# Define constant that will be checked by the include page.
	define ( 'ACCOUNT_COLUMNS', '' );

	current_user_ensure_unprotected();

	include ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'manage_columns_inc.php' );

	html_page_bottom();
