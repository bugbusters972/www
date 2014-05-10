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
	 * This page allows the user to set the fields of the bugs he wants to print
	 * Update is POSTed to acount_prefs_update.php
	 * Reset is POSTed to acount_prefs_reset.php
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

	require( 'print_all_bug_options_inc.php' );

	auth_ensure_user_authenticated();

	html_page_top();
	edit_printing_prefs();
	html_page_bottom();
