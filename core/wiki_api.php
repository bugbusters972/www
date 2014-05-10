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
 * @subpackage WikiAPI
*
*
 * @link 
 */

/**
 * Returns whether wiki functionality is enabled
 * @return bool indicating whether wiki is enabled
 * @access public
 */
function wiki_enabled() {
	return( config_get_global( 'wiki_enable' ) == ON );
}

/**
 *
 * @return null
 * @access public
 */
function wiki_init() {
	if( wiki_enabled() ) {

		# handle legacy style wiki integration
		require_once( config_get_global( 'class_path' ) . 'MantisCoreWikiPlugin.class.php' );
		switch( config_get_global( 'wiki_engine' ) ) {
			case 'dokuwiki':
				plugin_child( 'MantisCoreDokuwiki' );
				break;
			case 'mediawiki':
				plugin_child( 'MantisCoreMediaWiki' );
				break;
			case 'twiki':
				plugin_child( 'MantisCoreTwiki' );
				break;
			case 'WikkaWiki':
				plugin_child( 'MantisCoreWikkaWiki' );
				break;
			case 'xwiki':
				plugin_child( 'MantisCoreXwiki' );
				break;
		}

		if( is_null( event_signal( 'EVENT_WIKI_INIT' ) ) ) {
			config_set_global( 'wiki_enable', OFF );
		}
	}
}

/**
 *
 * @param int $p_bug_id Bug ID
 * @return string url
 * @access public
 */
function wiki_link_bug( $p_bug_id ) {
	return event_signal( 'EVENT_WIKI_LINK_BUG', $p_bug_id );
}

/**
 *
 * @param int $p_project_id
 * @return string url
 * @access public
 */
function wiki_link_project( $p_project_id ) {
	return event_signal( 'EVENT_WIKI_LINK_PROJECT', $p_project_id );
}

