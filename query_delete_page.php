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

	auth_ensure_user_authenticated();
	compress_enable();

	$f_query_id = gpc_get_int( 'source_query_id' );
	$t_redirect_url = 'query_view_page.php';
	$t_delete_url = 'query_delete.php';

	if ( !filter_db_can_delete_filter( $f_query_id ) ) {
		print_header_redirect( $t_redirect_url );
	}

	html_page_top();
?>
	<br />
	<div align="center">
	<center><b><?php print string_display( filter_db_get_name( $f_query_id ) ); ?></b></center>
	<?php echo lang_get( 'query_delete_msg' ); ?>

	<form method="post" action="<?php print $t_delete_url; ?>">
	<?php echo form_security_field( 'query_delete' ) ?>
	<br /><br />
	<input type="hidden" name="source_query_id" value="<?php print $f_query_id; ?>"/>
	<input type="submit"  value="<?php print lang_get( 'delete_query' ); ?>"/>
	</form>

	<form method="post" action="<?php print $t_redirect_url; ?>">
	<?php # CSRF protection not required here - form does not result in modifications ?>
	<input type="submit"  value="<?php print lang_get( 'go_back' ); ?>"/>
	</form>

<?php
	print '</div>';
	html_page_bottom();
