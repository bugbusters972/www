<?php
#fonction Champ créé les champs
function Champ ($name, $tag, $type){
	$x = '$f_'.$name;
	echo '<li '.helper_alternate_class().'><label class="description">';
	echo print_documentation_link( $name ).'</label>';
	
	if ($tag == 'select') {
	echo '<div><'.$tag.' name="'.$name.'">';
	print_enum_string_option_list( $name, $x );
	echo '</'.$tag.'></div>';
	}
	
	else if ( $type=='checkbox' || $type=='radio'){
	echo '<span>';
	print_enum_string_radio_list( $name, $x,$tag, $type);
	echo '</span>';
	}
	else if($tag == 'input' || $tag == 'textarea'){
	echo '<div><'.$tag.' name="'.$name.'"></'.$tag.'></div>';
	}
	
	echo '</li>';
}

function Affiche ($jeff,$css) {
echo '<li class="'.$css.'">', lang_get( $jeff ), '</li>';
echo '<p>'.$GLOBALS['tpl_'.$jeff].'</p>';
}
