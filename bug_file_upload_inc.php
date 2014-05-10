<?php
require_once( 'file_api.php' );

	# check if we can allow the upload... bail out if we can't
	if ( !file_allow_bug_upload( $f_bug_id ) ) {
		return false;
	}

	$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
?>

<?php
	collapse_open( 'upload_form' );
	$t_file_upload_max_num = max( 1, config_get( 'file_upload_max_num' ) );
?>
<form method="post" enctype="multipart/form-data" action="bug_file_add.php">
<?php echo form_security_field( 'bug_file_add' ) ?>

<table width=780" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
<?php
		#collapse_icon( 'upload_form' );
		echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' );
?>
	</td>
</tr>
<tr class="row-1">
	<td class="description" width="30%">
		<?php echo lang_get( $t_file_upload_max_num == 1 ? 'select_file' : 'select_files' ) ?><br />
		<?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)</span>'?>
	</td>
	<td width="60%">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
<?php
	// Display multiple file upload fields
	for( $i =0; $i < $t_file_upload_max_num; $i++ ) {
?>
		<input id="ufile[]" name="ufile[]" type="file" size="50" />
<?php
		if( $t_file_upload_max_num > 1 ) {
			echo '<br />';
		}
	}
?>
		<input type="submit"
			value="<?php echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file_button' : 'upload_files_button' ) ?>"
		/>
	</td>
</tr>
</table>
</form>
<?php
	collapse_closed( 'upload_form' );
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php
			#collapse_icon( 'upload_form' );
			echo lang_get( 'upload_file' ) ?>
	</td>
</tr>
</table>

<?php
	collapse_end( 'upload_form' );
