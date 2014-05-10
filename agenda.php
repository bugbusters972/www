<?php
	require_once( 'core.php' );
	auth_ensure_user_authenticated();
	html_robots_noindex();

	html_page_top();
	include('salleattente_inc.php');
$t_user_id = auth_get_current_user_id();
$ggurl = array(
8 => "https://www.google.com/calendar/embed?src=ufm.nramael%40gmail.com&ctz=America/Martinique",
7 => "https://www.google.com/calendar/embed?src=ufm.banel%40gmail.com&ctz=America/Martinique",
6 => "https://www.google.com/calendar/embed?src=ufm.cgermany%40gmail.com&ctz=America/Martinique",
10 => "https://www.google.com/calendar/embed?src=ufm.rbonheur%40gmail.com&ctz=America/Martinique ",
9=> "https://www.google.com/calendar/embed?src=ufm.ecelestin%40gmail.com&ctz=America/Martinique",
11=>"https://www.google.com/calendar/embed?src=ufm.mracon%40gmail.com&ctz=America/Martinique",
12=>"https://www.google.com/calendar/embed?src=ufm.odurocher%40gmail.com&ctz=America/Martinique",
13=>"https://www.google.com/calendar/embed?src=ufm.mmarron%40gmail.com&ctz=America/Martinique",
14=>"https://www.google.com/calendar/embed?src=ufm.ctamarin%40gmail.com&ctz=America/Martinique",
15=>"https://www.google.com/calendar/embed?src=ufm.ybollin%40gmail.com&ctz=America/Martinique",
20=>"https://www.google.com/calendar/embed?src=ufm.atheophile%40gmail.com&ctz=America/Martinique",
21=>"https://www.google.com/calendar/embed?src=ufm.gsebastien%40gmail.com&ctz=America/Martinique",	
22=>"https://www.google.com/calendar/embed?src=ufm.gderigent%40gmail.com&ctz=America/Martinique",
23=>"https://www.google.com/calendar/embed?src=ufm.cjeanvion%40gmail.com&ctz=America/Martinique",
);


foreach ( $ggurl as $id=>$url){
if ($id == $t_user_id){
echo "<div class='appnitro'>
<iframe src='".$url."' style='border-width:0' width='780' height='600' frameborder='0' scrolling='no'></iframe>
</div>";
return true;
}
}
html_page_bottom();
