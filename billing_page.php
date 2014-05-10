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

	access_ensure_global_level( config_get( 'time_tracking_reporting_threshold' ) );

	html_page_top( lang_get( 'time_tracking_billing_link' )  );
?>

<br />

<?php
	$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
?>
	<!-- Jump to Bugnote add form -->
<?php
	# Work break-down
	include( $t_mantis_dir . 'billing_inc.php' );

	html_page_bottom();

