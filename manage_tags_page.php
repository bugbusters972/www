<?php

/**
 * @package UFM
*
 * @link 
 */
 /**
  * UFM Core API's
  */

require_once( 'core.php' );

/**
 * requires tag_api
 */
require_once( 'tag_api.php' );
require_once( 'user_pref_api.php' );
require_once( 'form_api.php' );

access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

compress_enable();

html_page_top( lang_get( 'manage_tags_link' ) );

print_manage_menu( 'manage_tags_page.php' );

$t_can_edit = access_has_global_level( config_get( 'tag_edit_threshold' ) );
$f_filter = utf8_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_tag_prefix' ) ) );
$f_page_number = gpc_get_int( 'page_number', 1 );
$t_tag_table = db_get_table( 'mantis_tag_table' );

# Start Index Menu
$t_prefix_array = array( 'ALL' );

for ( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[] = $i;
}

for ( $i =0; $i <= 9; $i++ ) {
	$t_prefix_array[] = "$i";
}

echo '<br /><table align="center" class="width75"><tr>';

foreach ( $t_prefix_array as $t_prefix ) {
	if ( $t_prefix === 'ALL' ) {
		$t_caption = lang_get( 'show_all_tags' );
	} else {
		$t_caption = $t_prefix;
	}

	if ( $t_prefix == $f_filter ) {
		$t_link = "<strong>$t_caption</strong>";
	} else {
		$t_link = '<a href="manage_tags_page.php?filter=' . $t_prefix .'">' . $t_caption . '</a>';
	}

	echo '<td>' . $t_link . '</td>';
}

echo '</tr></table>';

# Set the number of Tags per page.
$t_per_page = 20;
$t_offset = (( $f_page_number - 1 ) * $t_per_page );

# Determine number of tags in tag table
# Retrive Tags from tag table
if ( $f_filter === 'ALL' ) {
	$t_name_filter = '';
} else {
	$t_name_filter = $f_filter;
}

$t_total_tag_count = tag_count($t_name_filter);

#Number of pages from result
$t_page_count = ceil( $t_total_tag_count / $t_per_page );

if ( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if ( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if ( $f_page_number < 1 ) {
	$f_page_number = 1;
}

$t_tags = tag_get_all($t_name_filter, $t_per_page, $t_offset);
?>

<br />

<!--  Tag Table Start -->
<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="4">
			<?php
				echo lang_get( 'manage_tags_link' ) . ' [' . $t_total_tag_count . '] ';
				if ( $t_can_edit ) {
					print_link( '#tagcreate', lang_get( 'tag_create' ) );
				}
			?>
		</td>
	</tr>
	<tr class="row-category">
		<td width="25%"><?php echo lang_get( 'tag_name' ) ?></td>
		<td width="20%"><?php echo lang_get( 'tag_creator' ) ?></td>
		<td width="20%"><?php echo lang_get( 'tag_created' ) ?></td>
		<td width="20%"><?php echo lang_get( 'tag_updated' ) ?></td>
	</tr>
<?php
foreach ( $t_tags as $t_tag_row ) {
	$t_tag_name = string_display_line( $t_tag_row['name'] );
	$t_tag_description = string_display( $t_tag_row['description'] );
?>
	<tr <?php echo helper_alternate_class() ?>>
		<?php if ( $t_can_edit ) { ?>
		<td><a href="tag_view_page.php?tag_id=<?php echo $t_tag_row['id'] ?>" ><?php echo $t_tag_name ?></a></td>
		<?php } else { ?>
		<td><?php echo $t_tag_name ?></td>
		<?php } ?>
		<td><?php echo string_display_line( user_get_name( $t_tag_row['user_id'] ) ) ?></td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></td>
	</tr>
<?php } ?>

	<tr>
		<td class="right" colspan="8">
			<span class="small">
				<?php
					/* @todo hack - pass in the hide inactive filter via cheating the actual filter value */
					print_page_links( 'manage_tags_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter );
				?>
			</span>
		</td>
	</tr>
</table>

<?php if ( $t_can_edit ) { ?>

<br />
<a name="tagcreate">

<!-- Create Tag Form -->

<form method="post" action="tag_create.php">
<?php echo form_security_field( 'tag_create' ); ?>

<table align="center" class="width75" cellspacing="1">

	<!-- Title -->

	<tr>
		<td class="form-title" colspan="2">
			<?php echo lang_get( 'tag_create' ) ?>
		</td>
	</tr>
	<tr class="row-1">
		<td class="description">
		<span class="required">*</span>
			<?php echo lang_get( 'tag_name' ) ?>
		</td>
		<td>
			<input type="text" name="name" size="50" maxlength="100" />
			<?php echo sprintf( lang_get( 'tag_separate_by' ), config_get( 'tag_separator' ) ); ?>
		</td>
	</tr>
	<tr class="row-2">
		<td class="description">
			<?php echo lang_get( 'tag_description' ) ?>
		</td>
		<td><textarea name="description" cols="72" rows="6"></textarea>
		</td>
	</tr>
	<tr>
		<td class="left">
			<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
		</td>
		<td class="center" colspan="2">
		<input type="submit"  value="<?php echo lang_get( 'tag_create' ) ?>" />
		</td>
	</tr>

</table>
</form>

<?php
} #End can Edit

html_page_bottom();
