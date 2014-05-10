<?php
require_once( 'core.php' );
html_robots_noindex();
html_page_top( 'planning' );

#un slot = une machine
class slot {

#elle a un id
public $id = 5;
#des intervalles de temps KO
public $kodeb = array();
public $kofin = array();

#connaitre dispo
public function is_free($du,$au){

#error mgmt
if ($du>$au) {
trigger_error ('La date de d&eacute;but ne peut pas &ecirc;tre sup&eacute;rieure &agrave; la date de fin',E_USER_ERROR);
}

for ($i=0;$i<count($this->kodeb); ++$i){
if (($du>$this->kodeb[$i]&&$du<$this->kofin[$i])||($au>$this->kodeb[$i]&&$au<$this->kofin[$i])){
echo 'date invalide du '.$this->kodeb[$i].' au '.$this->kofin[$i];
return false;
}
}
return true;
}


}

#elle contient plusieurs event= evenement
class event extends slot {
#rattachés par #for
public $for=null;
#ils ont un id et un nom
public $eventid = 1;
public $nom;

#...un intervalle de temps
public $debut;
public $fin;

}




$altra= new event;
$altra->id =1;
$altra->kodeb = array(100,500,900,2000);
$altra->kofin = array(200,600,1000,2010);

var_dump ($altra->is_free(700,750));

var_dump($altra);

$f = "06 91 ");

echo $f;
html_page_bottom();
?>