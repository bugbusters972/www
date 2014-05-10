Demarche = [];

n = 0;
function Enf(){
n = n+1;
$('#sxage').append('<span id="enf"><p> AJOUTER ENFANT '+n+'</p><label for="age">Date de naissance</label><input id="age"/><label for="sexe">sexe</label><select id="sexe"><option value="Femme">Femme</option><option value="Homme">Gar&ccedil;on</option></select></span>');
};

function newdemarch() {
$('* #autdemarch').append('<input type="text" value="" id="demarche"/>');
};


$(document).ready(function(){

$('select[name$=pays] option').filter(function(){
return $(this).attr('value') == '117';
}).attr('selected','selected');

if ($(".form_description").length>1){
$(".form_description").nextAll().hide();
$(".form_description :first").nextAll().show();
}

$('* #contacts').nextAll().hide();

$('label+p').filter(
function(index){
		return $(this).text() =='' | $(this).text() =='0';
		}).prev().css('color','#D3D3D3');

$('label').filter(function(){return $(this).attr('for') == 'nom';
}).append(' *');
$('label').filter(function(){return $(this).attr('for') == 'prenom';}).append(' *');
$('label').filter(function(){return $(this).attr('for') == 'telephone';}).append(' *');
$('label').filter(function(){return $(this).attr('for') == 'date_naissance';}).append(' *');
$('label').filter(function(){return $(this).attr('for') == 'nationalite';}).append(' *');
$('label').filter(function(){return $(this).attr('for') == 'temps';}).append(' *');


function Coblig(x){
$('* #alert').empty();
if ($(x).val() == ""){
$(x).addClass('c-oblig');
/*$('#action_left').append('<li id="alert">Veuillez remplir le champ '+$(x).attr('name')+' </li>');*/
} else{
$(x).removeClass('c-oblig');
}
};



$('input').focusin(function(){
Coblig($('input[name="telephone"]'));
Coblig($('input[name="prenom"]'));
Coblig($('input[name$="nom"]'));


/*if ($('.form_description').eq('1').next().css('display') == 'block'){
};*/
});



/*if (
$('input[name="temps"]').val(),
$('input[name="nationalite"]'),
$('input[name="date_naissance"]'),*/



$(".form_description").click(function(){
	$(this).nextAll().toggle("slow");
	});

$("legend").click(function(){
	$(this).nextAll().toggle("slow");
	});
	
$("input, select, textarea").click(function(){
$("form li").removeClass('highlighted');
$(this).parents("form li").addClass('highlighted');
});



$(".form_description").each(function(index){
if ($(this).children('h2').text() && index =='0'){
$('#step').append('<li><a href="#"><strong>&gt; '+$(this).children('h2').text()+'</a></strong></li>');}

if ($(this).children('h2').text() && index =='1'){
$('#step').append('<li><a href="#partie2"><strong>&gt; '+$(this).children('h2').text()+'</a></strong></li>');}



if ($(this).children('h3').text() != ''){
/*alert(index);*/
$('#step').append('<li><a href="#partie3"> - '+$(this).children('h3').text()+'</a></li>');}


if ($(this).children('h4').text() != ''){
$('#step').append('<li class="btnadd"><a  href="#partie3"> '+$(this).children('h4').text()+'</a></li>');}

if ($(this).children('h6').text() != ''){
$('#step').append('<li class="btnadd"><a href="#bottom"> '+$(this).children('h6').text()+'</a></li>');}


});

Enf();

$('form').submit(function(){

$( "input:checkbox:checked" ).each(function(){
var id = $(this).attr('id'); var n = $( this ).val();
$($( 'input[name$="'+id+'"]' )).append(n+'+');
$($( 'input[name$="'+id+'"]' )).val($($( 'input[name$="'+id+'"]' )).text());
});

$('* #enf').each(function(index){
$('input[name$="sxage_enf"]').append(index+'+');

var inp = $(this).children('input').val();
var sel = $(this).children('select').val();
$('input[name$="sxage_enf"]').append(inp+'+'+sel+',');
$('input[name$="sxage_enf"]').val($('input[name$="sxage_enf"]').text());
});

$('* #demarche').each(function(){
var dem = $(this).val();
if (dem.length > 2){
Demarche += dem+'@@';
$('input[name$="autre_demarch"]').val(Demarche);
}
});


});

$('.nouvcontact, #shadow').click(function(){
$('#shadow, #lightbox').toggle('fast');
});

});