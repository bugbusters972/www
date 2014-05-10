<?php
date_default_timezone_set('America/Martinique');
?>
<div id="shadow"></div>
<div id="lightbox">
<img id="top" src="images/top.png" alt="">

<div id="form_container">

<div class="appnitro">
<ul class="part0">
<form name="add_contact_form" method="post" action="add_contact.php">
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>"/>

<?php echo form_security_field( 'add_contact_form' );?>
<fieldset ><legend>NOUVEAU CONTACT</legend>
<span class="inline33">
 <li <?php echo helper_alternate_class() ?>>
			<label class="description">
				<?php print_documentation_link( 'motif_nvvenue' ) ?>
			</label>
			<div>
				<select name="motif_nvvenue"><?php print_enum_string_option_list( 'motif_nvvenue', $f_motif_nvvenue ); ?></select>
			</div>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'date_nvvenue' ) ?>
		</label>
		<div>
			<input type="hidden" name="date_nvvenue" value="<?php echo db_now()?>"/>
			<p><?php echo date(config_get('normal_date_format'), db_now())?></p>
		</div>
	</li>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">Edit&eacute; par :</label>
		<input type="hidden" name="rapporteur" value="<?php echo current_user_get_field( 'id' )?>"/>
		<p><?php echo string_html_specialchars( current_user_get_field( 'username' ) );?></p>
	</li>
</span>
<?php
echo '<span class="inline7030">';

#type_contact
Champ ('type_contact','select','');

#notes
Champ ('description','textarea','');


echo'</span>
<input type="submit" value="Enregistrer Nouveau contact"/>
</fieldset>
</form>
</ul>
</div>
</div>
</div>';
