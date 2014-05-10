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

	require_once( 'compress_api.php' );
	require_once( 'filter_api.php' );
	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'string_api.php' );
	require_once( 'date_api.php' );
	require_once( 'rss_api.php' );

	auth_ensure_user_authenticated();

	$t_query_arr = filter_db_get_available_queries();

	# Special case: if we've deleted our last query, we have nothing to show here.
	if ( count( $t_query_arr ) < 1 ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

	compress_enable();

	html_page_top();

	$t_rss_enabled = config_get( 'rss_enabled' );
?>
<br />
<div align="center">
<table class="width75" cellspacing="0">
<?php
	$t_column_count = 0;
	$t_max_column_count = 2;

	foreach( $t_query_arr as $t_id => $t_name ) {
		if ( $t_column_count == 0 ) {
			print '<tr ' . helper_alternate_class() . '>';
		}

		print '<td>';

		if ( OFF != $t_rss_enabled ) {
			# Use the "new" RSS link style.
			print_rss( rss_get_issues_feed_url( null, null, $t_id ), lang_get( 'rss' ) );
			echo ' ';
		}

		$t_query_id = db_prepare_int( $t_id );
		print_link( "view_all_set.php?type=3&source_query_id=$t_query_id", $t_name );

		if ( filter_db_can_delete_filter( $t_id ) ) {
			echo ' ';
			print_button( "query_delete_page.php?source_query_id=$t_query_id", lang_get( 'delete_query' ) );
		}

		print '</td>';

		$t_column_count++;
		if ( $t_column_count == $t_max_column_count ) {
			print '</tr>';
			$t_column_count = 0;
		}
	}

	# Tidy up this row
	if ( ( $t_column_count > 0 ) && ( $t_column_count < $t_max_column_count ) ) {
		for ( $i = $t_column_count; $i < $t_max_column_count; $i++ ) {
			print '<td>&#160;</td>';
		}
		print '</tr>';
	}
?>
</table>
</div>
<?php
	html_page_bottom();
