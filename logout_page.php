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

	auth_logout();

	print_header_redirect( config_get( 'logout_redirect_page' ), /* die */ true, /* sanitize */ false );
