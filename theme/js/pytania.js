<!--
var kom = new Array (
	'Dla pytań tego typu operacja niedostępna.'
);
//typy pytań 0=>text, 1=>radio, 2=>checkbox, 3=>user short text, 4=>textarea, 5=>select
var typy=new Array('text','radio','checkbox','open text','open textarea','select/input');
var drgOptions = {
	helper: function () {
		var nrpyt=$(this).find('.pnrtxt').text()+$(this).find('.pskr').text();
		return $('<div id="drag_helper">'+nrpyt+'</div>')[0];
	},
	revert: "invalid"
};
var drpOptions = {
	activeClass: "ui-state-hover",
	hoverClass: "ui-state-active",
	drop: function (event, ui) {
		var nrpyt = ui.draggable.attr('id').substr(3);
		var nrpnew = $(this).attr('id').substr(3);
		mvUD(nrpyt,nrpnew);
	}
};

$(document).ready(function(){

	$('.pytdiv').draggable(drgOptions);
	$('.pytdiv').droppable(drpOptions);
	$('.divh').css('display','none');

	$('#kallchk').click(function(){
		tlst();
	});

	$('#katall').change(function(){
		tlst();
	});

	if ($('#katt').length) $('#katall').attr('disabled','disabled');

	$('#ileodp').click(function(){
		$(this).val('');
	});

	$('#pedd').submit(function(e){
		if ($('#idt').val()==0 && $('#pytin .pytdiv').length < 1) {
			alert('Nie dodałeś żadnego pytania');
			return false;
		}
		var error = false;
		$('.pyttxt').each(function(){
			nrpyt=$(this).parents('.pytdiv').attr('id').substr(3);
			if ($(this).val().length == 0) {
				alert('Brak treści pytania '+nrpyt+'!');
				var error=true;
				e.preventDefault();
			}
		});
		if (error) return false;
		$('#tmp').remove();
		return true;
	});

	$('#btnAdd').click(function(){
		addPyt();
	});
	
	$('#btnCopy').click(function(){
		var nrpyt=$('#cPyt').val();
		var from=$('#div'+nrpyt);
		if (!from.length) {
			alert('Wprowadź numer pytania!');
			return;
		}
		var nrpnew=$('#pytin .pytdiv').length+1;
		from.clone(true).appendTo($('#pytin'));
		$('.pytdiv:last').attr('id','div'+nrpnew);
		$('#pytin').append('<hr />');
		chName(nrpnew);
		$('#div'+nrpnew+' .pnrtxt').html('Pytanie '+nrpnew+': ');
		$('#div'+nrpnew+' .pdel').val(0);
		$('#div'+nrpnew+' .ppub').val(0);
		$('#div'+nrpnew+' .pid').val(0);
		$('#div'+nrpnew+' .mod').val(0);
		$(this).blur();
	});
	
	$('#shall').change(function(){
		if ($(this).prop('checked')){
			$('.divh').show();
			$('.chkHide').attr('checked','checked');
		} else {
			$('.divh').hide();
			$('.chkHide').removeAttr('checked');
		}
	});

	$('#typpyt').change(function(){
		var kom=$('#typpyt option:selected').prop('title');
		$('#pytkom').html(kom);
	});

});

$(document).on("click","input.chkHide",function(){
		var divp=$(this).parents('.pytdiv').attr('id');
		//var nrpyt=$(this).parent().attr('id').substr(4);
		$('#'+divp+' div.divh').toggle();
	});

$(document).on("change",".modpt",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		setmodp(id);
	});

$(document).on("click","input.btnMup",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		var nrpyt=id.substr(3);
		var nrpnew=parseInt(nrpyt)-1;
		mvUD(nrpyt,nrpnew);
		$(this).blur();
	});

$(document).on("click","input.btnMdo",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		var nrpyt=id.substr(3);
		var nrpnew=parseInt(nrpyt)+1;
		mvUD(nrpyt,nrpnew);
		$(this).blur();
	});

$(document).on("click","input.btnShow",function(){
		show($(this).parents('.pytdiv').attr('id'));
	});

$(document).on("click","input.btnRemP",function(){
		var nrpyt = $(this).parents('.pytdiv').attr('id').substr(3);
		var r = confirm('Usunąć pytanie '+nrpyt+'?');
		if (r != true) return;
		$(this).parents('.pytdiv').next().remove();
		$(this).parents('.pytdiv').remove();
		$('div.pytdiv').each(function(index){
			$(this).attr('id','div'+(index+1));
			$(this).children('.pnrtxt').html('Pytanie '+(index+1)+': ');
			chName((index+1));
		});
	});

$(document).on("click","input.btnAddI",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		addImg(id.substr(3));
		setmodp(id);
	});
$(document).on("click","input.btnAddIP",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		addImgP(id.substr(3));
		setmodp(id);
	});
$(document).on("click","input.btnAddT",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		addTxt(id.substr(3));
		setmodp(id);
	});

$(document).on("click","input.btnChT",function(){
		var id=$(this).parents('.pytdiv').attr('id');
		var typ=$('#'+id+' input.typ').val();
		if (typ === '5') {
			alert('Nie można zmienić tego typu odpowiedzi!');
			return;
		}
		chType(id);
	});

$(document).on("click","input.btnRemO",function(){
		var id= $(this).parents('.pytdiv').attr('id');
		var typ=$('#'+id+' input.typ').val();
		if (typ === '5') { alert('Nie można usunąć tego typu odpowiedzi!'); return; }
		var ileo=$('#'+id+' input.ileo').val();
		
		if (parseInt(ileo)<2) { alert ('Została tylko jedna odpowiedź!'); return; }
		$('#'+id+' div.odiv:last').remove();
		$('#'+id+' input.ileo').val($('#'+id+' input.ileo').val()-1);
		if (typ==='0' || typ==='3') $('#'+id+' input.mpkt').val($('#'+id+' input.ileo').val());
		setmodp(id);
	});

$(document).on("click","input.btnAddO",function(){
		var id= $(this).parents('.pytdiv').attr('id');
		var nrpyt=id.substr(3);
		var typ=$('#'+id+' input.typ').val();
		if (typ === '5') {
			alert('Nie można dodać tego typu odpowiedzi!');
			return;
		}
		var ileo=$('#'+id+' div.odiv').length;
		addInputN(nrpyt,typ,ileo);
		$('#'+id+' input.ileo').val(ileo+1);
		if (typ==='0' || typ==='3') $('#'+id+' input.mpkt').val(ileo+1);
		setmodp(id);
	});
	
$(document).on("click","input.btnRemF",function () {
		$(this).prev('input').remove();
		$(this).remove();
	});

$(document).on("click","#pytin .pyttxt", function(){
		if ($(this).val()==' wpisz treść pytania') $(this).val('');
	});

$(document).on("change","#pytin .pyttxt", function(){
		var pskr=$(this).val().substr(0,100);
		var id= $(this).parents('.pytdiv').attr('id');
		$('#'+id+' .pskr').text(pskr);
	});

$(document).on("click","#pytin .otxt", function(){
		var id=($(this).parents('.pytdiv').attr('id'));
		var typ=$('#'+id+' .typ').val();
		//if ( typ > 2 && typ < 4 ) { alert (kom[0]); return; }
		if ($(this).val().indexOf(' odpowiedź')>-1) $(this).val('');
	});

//$(document).on("click","#pytin .totxt", function(){
//		var id=($(this).parents('.pytdiv').attr('id'));
//		var typ=$('#'+id+' .typ').val();
//		if ( typ > 2 && typ < 4 ) { alert (kom[0]); return; }
//		if ($(this).val().indexOf(' wpisz')>-1 || $(this).val().indexOf(' pytanie')>-1) $(this).val('');
//	});

//$(document).on("click","#pytin .todp", function(){
//		var id=($(this).parents('.pytdiv').attr('id'));
//		var typ=$('#'+id+' .typ').val();
//		if ( typ > 2 && typ < 4 ) { alert (kom[0]); return; }
//		if ($(this).val().indexOf(' wpisz')>-1 || $(this).val().indexOf(' odpowiedź')>-1) $(this).val('');
//	});
$(document).on("click","#pytin .odp", function(){
		var id=($(this).parents('.pytdiv').attr('id'));
		var typ=$('#'+id+' .typ').val();
		//if ( typ < 1 ) return; //nie trzeba nic spraw
		//if ($(this).val().indexOf(' wpisz')>-1 || $(this).val().indexOf(' odpowiedź')>-1) $(this).val('');
		if ($(this).next('input.ifile').val().length == 0) {
			if (typ < 1) {//text
				alert('Dodaj obrazek, później wpisz odpowiedź!');
			} else {
				alert ('Nie dodano obrazka i nie można zaznaczyć tej odpowiedzi!');
				$(this).prop('checked',false);
				$(this).blur();
			}
		}
	});
//
//addEl(typ,styp,nazwa,id,s,k,v)
function addPyt() {//dodawanie pytań - funkcja główna, katid oznacza kategorię pytania
	var katid=1;
	if (!($('#katall').attr('disabled'))) katid=$('#katall').val();
	else if ($('#katt').length) katid=$('#katt').val();
	var ile=$('#ileodp').val();
	if (ile<1) { alert('Nie podałeś ilości odpowiedzi!'); return; }
	var cel='pytin';
	//var typ=$('input[name=typp]:checked').val();
	var typ=$('#typpyt').val();
	var isimg=$('#addimg').attr('checked');
	var nrpyt = $('#pytin div.pytdiv').length+1;
	var mpkt = 1;//maksymalna ilość punktów
	if (typ==='0' || typ==='3' || typ==='4') mpkt=ile;//text
	if (typ==='5') ile=1;

	$('#pytin').append('<div id="div'+nrpyt+'" class="pytdiv">'+
		'<span class="pnrtxt">Pytanie '+nrpyt+': </span>'+
		'<span class="pskr"></span>'+
		'<div class="cbut">'+
			'<input class="chkHide" type="checkbox" checked="checked" /><label>Pokaż</label>&nbsp;&nbsp;'+
			'<input class="pdel" type="checkbox" value="0" name="pdel[]" /><label>Usuń z bazy</label>&nbsp;&nbsp;'+
			'<input class="ppub modpt" type="checkbox" value="'+nrpyt+'" name="ppub[]" /><label>Publiczne</label>&nbsp;&nbsp;'+
			'&middot;<label>Max. punktów:</label> <input class="mpkt modpt" type="text" name="mpkt[]" value="'+mpkt+'" size="1" '+(typ==='3'||typ==='4'?'':'readonly="readonly" ')+'/>'+
		'</div>'+
		'<div class="divh">'+
			'<textarea class="pyttxt modpt" rows="2" cols="50" name="pyt[]" title="Treść pytania/polecenia"> wpisz treść pytania</textarea>'+
			'<input class="pid" type="hidden" value="0" name="tk[]" />'+
			'<input class="typ" type="hidden" value="'+typ+'" name="typ[]" />'+
			'<input class="ileo" type="hidden" value="'+ile+'" name="ileo[]" />'+
			'<input class="mod" type="hidden" name="mod[]" />'+
			'<div class="idiv cb"></div>'+
			'<div class="tdiv cb"></div>'+
			'<div class="sdiv cb"></div>'+
			'<div class="bdiv cb"></div>'+
		'</div></div><hr />');
	for (i=0; i<ile; i++) addInputN(nrpyt,typ,i);
	if (isimg) addImg(nrpyt);
	$('#katall').clone().appendTo($('#div'+nrpyt+' div.bdiv'));
	$('#div'+nrpyt+' div.bdiv select').removeAttr('id');
	$('#div'+nrpyt+' div.bdiv select').attr('class','katp modpt');
	$('#div'+nrpyt+' div.bdiv select').attr('name','kat[]');
	$('#div'+nrpyt+' select.katp').removeAttr('onchange');
	$('#div'+nrpyt+' select.katp').val(katid);
	if ($('#kallchk').is(":checked")) {
		$('#div'+nrpyt+' select.katp').attr('disabled','disabled');
	} else {
		$('#div'+nrpyt+' select.katp').removeAttr('disabled');
	}
	
	$('#div'+nrpyt+' div.bdiv').append(''+
		'<input class="btnMup but flr" type="button" value="/\\" title="Przesuń w górę" />'+
		'<input class="btnMdo but flr" type="button" value="\\/" title="Przesuń w dół" />'+
		'<input class="btnShow but flr" type="button" value="Sh" title="Pokaż dane" />'+
		'<input class="btnRemP but flr" type="button" value="Pyt[-]" title="Usuń pytanie" />'+
		'<input class="btnAddT but flr" type="button" value="Txt[+]" title="Dodaj tekst do analizy" />'+
		'<input class="btnAddI but flr" type="button" value="Obr[+]" title="Dodaj obrazek dla całego pytania" />'+
		'<input class="btnAddIP but flr" type="button" value="ObrP[+]" title="Dodaj obrazki dla podpunktów" />'+
		'<input class="btnChT but flr" type="button" value="Typ[<>]" title="Zmień typ pytania" />'+
		'<input class="btnRemO but flr" type="button" value="Odp[-]" title="Usuń odpowiedź" />'+
		'<input class="btnAddO but flr" type="button" value="Odp[+]" title="Dodaj odpowiedź" />');
	$('#ilep').val(nrpyt);
	$('#div'+nrpyt).draggable(drgOptions);
	$('#div'+nrpyt).droppable(drpOptions);
}

function addImg(nrpyt) {//	dodawanie obrazka
	var typ = $('#div'+nrpyt+' input.typ').val();
	var ilef = $('#div'+nrpyt+' input.ifile').length+1;
	$('#div'+nrpyt+' div.idiv').append('<input class="ifile" type="file" name="file'+nrpyt+'[]" size="80" /><input class="btnRemF" type="button" value="x" title="Usuń zasób!" />');
}
function addImgP(nrpyt) {//dodawanie obrazków do podpunktów
	var typ = $('#div'+nrpyt+' input.typ').val();
	//alert(typ);
	var n='t';
	switch (parseInt(typ)) {
		case 0:
			$('#div'+nrpyt+' input.otxt').each(function(index){
				$(this).prop('type','file');
				$(this).prop('name',n+nrpyt+'[]');
				$(this).addClass('ifile');
				$(this).removeAttr('value');
			});
		break;
		case 1:
		case 2:
			$('#div'+nrpyt+' input.otxt').each(function(index){
				$(this).prop('type','file');
				$(this).prop('name',n+nrpyt+'[]');
				$(this).addClass('ifile');
				$(this).removeAttr('value');
			});
			$('#div'+nrpyt+' input.odp').each(function(index){
				$(this).prop('checked',false);
			});
		break;
		case 3:
		case 4:
			$('#div'+nrpyt+' .odp').each(function(){
				$(this).removeClass('inpw95').addClass('inpw49');
				$(this).prev('input.otxt').prop('type','file');
			});
		break;
		default:
			alert('Nieobsługiwany typ pytania!');
			return;
	}
}
function addTxt(nrpyt) {//dodawanie tekstu do analizy
	//alert('Tu');
	if ($('#div'+nrpyt+' textarea.txt').length) {
		alert('Pole tekstu analizy już zostało dodane!'); return;
	}
	$('#div'+nrpyt+' div.tdiv').append('<textarea class="txt inpw95" name="txt'+nrpyt+'" rows="3" title="Wpisz lub wklej tekst do analizy...">Wpisz lub wklej tekst do analizy...</textarea>');
}
function getType(nrpyt) {//	jeżeli typ jest większy niż 2 zwróć typ zapamiętany
		var typ=0;
		if ($('#div'+nrpyt+' input.kodp').is(':checked')) typ=$('#div'+nrpyt+' input.kodp').val();
		if ($('#div'+nrpyt+' input.dodp').is(':checked')) typ=$('#div'+nrpyt+' input.dodp').val();
		return typ;
}

function addInputN(nrpyt,typ,i) {
	var otyp=typ;
	switch (parseInt(typ)) {
		case 0://text
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="otxt modpt inpw49" type="text" value=" pytanie" name="t'+nrpyt+'[]" />'+
			'<input class="odp modpt inpw49" type="text" value=" odpowiedź" size="60" name="o'+nrpyt+'[]" title="Poprawna odpowiedź" /></div>');
		break;
		case 1://radio
		case 2://checkbox
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="odp modpt" type="'+typy[typ]+'" value="'+i+'" name="o'+nrpyt+'[]" />'+
			'<input class="otxt modpt inpw95" type="text" value=" odpowiedź" size="60" name="t'+nrpyt+'[]" title="Poprawna odpowiedź" /></div>');		
		break;
		case 3://short text
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="otxt modpt ifile inpw49" type="hidden" name="t'+nrpyt+'[]" />'+
			'<input class="odp modpt inpw95" type="text" value=" odpowiedź" name="o'+nrpyt+'[]" title="Odpowiedź" /></div>');
		break;
		case 4://long text
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="otxt modpt ifile inpw49" type="hidden" name="t'+nrpyt+'[]" />'+
			'<textarea class="odp modpt inpw95" rows="3" name="o'+nrpyt+'[]"> odpowiedź</textarea></div>');		
		break;
		case 5://select
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="odp modpt" type="hidden" value="0" name="o'+nrpyt+'[]" />'+
			'<textarea class="otxt modpt inpw95" rows="10" cols="50" name="t'+nrpyt+
			'[]">Wpisz(wklej) treść pytania. Sprawdzane pojęcia/wyrazy wprowadź w postaci [odp dobra#odp zła#odp zła...], np. [góra#gura] lub [pamięć ROM#pamięć RAM#pamięć DRAM].</textarea></div>');
		break;
		case 6:
		$('#div'+nrpyt+' div.sdiv').append('<div class="odiv cb">'+
			'<input class="otxt modpt ifile fll" type="file" name="t'+nrpyt+'_'+i+'" />'+
			'<input class="odp modpt inpw49" type="text" value=" odpowiedź" size="60" name="o'+nrpyt+'[]" title="Poprawna odpowiedź" /></div>');
		break;
	}
}

function chType(id,ntyp){
	//0 = text, 1 = radio, 2 = checkbox, 3 = textarea, 4 = user text, 5 = select
	nrpyt=id.substr(3);
	typ=$('#'+id+' input.typ').val(); //typ pytania
	if (typ === '5' || typ === '6') { alert(kom[0]); return; } //niezmieniamy typu dla pytań select lub obrazek - pytanie
	if (typeof(ntyp) == 'undefined') ntyp=parseInt(typ)+1;
	if (typ==2) ntyp=0; //można zmienić w obrębie 0,1,2
	if (typ==4) ntyp=3; //można zmieniać 3 na 4 i 4 na 3
	//alert (typ + ' ' + ntyp);
	var isFile=($('#'+id+' div.sdiv input[type=file]').length > 0) ? true : false; //czy są obrazki?
	if (typ == 0 && ntyp == 1) { //z text na radio
		if (isFile) {//dodano obrazki do podpunktów
			//alert('text na radio');
			$('#'+id+' input.odp').each(function(index){
				$(this).prop('type',typy[ntyp]);
				$(this).val(index).removeClass('inpw49');
				$(this).insertBefore($(this).prev('input.ifile'));
			});			
			$('#'+id+' input.ifile').each(function(index){
				$(this).removeClass('inpw49').addClass('inpw95');
			});		
		} else {
			$('#'+id+' input.otxt').each(function(index){
				$(this).prop('type',typy[ntyp]);
				$(this).attr('name','o'+nrpyt+'[]');
				$(this).attr('class','odp modpt');
				$(this).val(index);
			});
			$('#'+id+' div.sdiv input[type=text]').each(function(index){
				$(this).attr('name','t'+nrpyt+'[]');
				$(this).attr('class','otxt modpt inpw95');
			});
		}
		$('#'+id+' input.mpkt').val('1');
	} else if (typ == 1 && ntyp == 2) {//z radio na checkbox
		$('#'+id+' input.odp[type=radio]').each(function(){
			$(this).prop('type',typy[ntyp]);
		});
	} else if (typ == 2 && ntyp == 0) {//z checkbox na text
		var ile=$('#'+id+' input.otxt').length;
		if (isFile) {//dodano obrazki do podpunktów
			$('#'+id+' input.ifile').each(function(index){
				$(this).removeClass('inpw95').addClass('inpw49');//.addClass('fll');
				$(this).insertBefore($(this).prev('input.odp'));
			});
			$('#'+id+' input.odp').each(function(){//pola checkbox na text
				$(this).prop('type',typy[ntyp]);
				$(this).val(' odpowiedź').addClass('inpw49');
			});
		} else {
			$('#'+id+' input.otxt[type=text]').each(function(index){
				$(this).attr('name','o'+nrpyt+'[]');
				$(this).attr('class','odp modpt inpw49');
				$(this).prop('type',typy[ntyp]);
				//$(this).val(' odpowiedź');
			});
			$('#'+id+' input.odp[type=checkbox]').each(function(index){//pola checkbox na text
				$(this).show();
				$(this).prop('type',typy[0]);
				$(this).attr('name','t'+nrpyt+'[]');
				$(this).attr('class','otxt modpt inpw49');
				$(this).val(' pytanie');
			});
		}
		$('#'+id+' input.mpkt').val(ile);
	} else if (typ == 2 && ntyp == 1) {
		$('#'+id+' input.odp[type=checkbox]').each(function(){
			$(this).prop('type',typy[ntyp]);
		});
	} else if (typ == 3 && ntyp == 4) {//z open text na open textarea
		$('#'+id+' input.odp').each(function(index){
			var w=$(this).val();
			if (isFile) $(this).replaceWith($('<textarea class="odp modpt inpw49" rows="3" name="o'+nrpyt+'[]">'+w+'</textarea>'));
			else $(this).replaceWith($('<textarea class="odp modpt inpw95" rows="3" name="o'+nrpyt+'[]">'+w+'</textarea>'));	
		});
	} else if (typ == 4 && ntyp == 3) {//z open textarea na open text
		$('#'+id+' textarea.odp').each(function(index){
			var w=$(this).val();
			if (isFile) $(this).replaceWith($('<input class="odp modpt inpw49" type="text" value="'+w+'" name="o'+nrpyt+'[]" />'));
			else $(this).replaceWith($('<input class="odp modpt inpw95" type="text" value="'+w+'" name="o'+nrpyt+'[]" />'));	
		});
	}
	$('#'+id+' input.typ').val(ntyp);
	setmodp(id);
}

function chOrder(nrpyt) {
	$('#div'+nrpyt+' input.odp').prepand($(this).next('input.otxt[type=file]'));
}

function show(id) {//funkcja informacyjna
	//alert(id);
	nrpyt=id.substr(4);
	dimg=getEl('dimg'+nrpyt);
	ptxt=$('#'+id+' .pyttxt').val(); //treść pytania
	pdel=$('#'+id+' .pdel').prop('checked'); //czy usunąć pyt z bazy
	ppub=$('#'+id+' .ppub').prop('checked'); //czy publiczne
	pid=$('#'+id+' .pid').val(); //pid pytania
	typ=$('#'+id+' .typ').val(); //typ pytania
	ileo=$('#'+id+' .odiv').length;	//ile odp
	mod=$('#'+id+' .mod').val(); //f.mod
	kom='Pytanie '+nrpyt+': "'+ptxt+'"\nPid: '+pid+'\nPytań/odpowiedzi: '+ileo+'\n';
	kom+='\nUsunąć: '+pdel+'\n';
	kom+='Typ: '+typy[parseInt(typ)]+'\nZmienione: '+mod+'\n';
	kom+='Publiczne: '+ppub+'\n';
	odpt='';
	for (i=0;i<ileo.value;i++) {
		obj=getEl('t'+nrpyt+i);
		obj2=getEl('o'+nrpyt+i);
		if (typ.value==0) kom=kom+(i+1)+') '+obj.value+' - '+obj2.value;
		else kom=kom+(i+1)+') '+obj2.value+' - '+obj.value;
		kom=kom+'\n';
	}
	kat=$('#'+id+' .katp').val();
	kom=kom+'Kategoria: '+kat;
	file=getEl('file'+nrpyt);
	if (file) kom=kom+'\nPlik: '+file.value;
	if (dimg) {
		ileobr=dimg.getElementsByTagName('input');
		for (i=0; i<ileobr.length; i++) {
			idel=getEl('idel'+nrpyt+i);
			kom=kom+'\nObrazek '+i+' '+idel.checked;
		}
	}
	alert(kom);
}

function mvUD(nrpyt,nrpnew) {
	//nrpyt=id.substr(3);
	//if (up) var nrpnew=parseInt(nrpyt)-1; else var nrpnew=parseInt(nrpyt)+1;
	var cel=$('#div'+nrpnew);
	if (!(cel.length)) {
		alert('Nie można przenieść pytania!');
		return;
	}
	var from=$('#div'+nrpyt);
	$('#pytin').append('<div id="tmp" style="display: none;"></div>');
	var obj= new Array(' span.pskr',' div.cbut',' div.divh');
	for (i=0; i<3; i++) {
		$('#tmp').append($('#div'+nrpnew+obj[i]));
		cel.append($('#div'+nrpyt+obj[i]));
		from.append($('#tmp'+obj[i]));
	}
	$('#tmp').remove();
//--zmiana nazw tablic
	chName(nrpyt);
	chName(nrpnew);
}

function chName(nrpyt) {
	$('#div'+nrpyt+' div.idiv input.ifile').each(function(){
		$(this).attr('name','file'+nrpyt+'[]');
	});
	//var typ=$('#div'+nrpyt+' input.typ').val();
	$('#div'+nrpyt+' .odp').each(function(){
		$(this).attr('name','o'+nrpyt+'[]');
	});
	$('#div'+nrpyt+' .otxt').each(function(){
		$(this).attr('name','t'+nrpyt+'[]');
	});
	$('#div'+nrpyt+' .txt').each(function(){
		$(this).attr('name','txt'+nrpyt);
	});
//	$('#div'+nrpyt+' .todp').each(function(){
//		$(this).attr('name','o'+nrpyt+'[]');
//	});
//	$('#div'+nrpyt+' .totxt').each(function(){
//		$(this).attr('name','t'+nrpyt+'[]');
//	});
}

function tlst() {//uaktywnia listy wyboru przedmiotu, kategorii
/*
katt - ustawienie kategorii testu
przt - ustawienie przedmiotu testu
pallchk - checkbox przedmiotu dla wszystkich pytań
przall - ustawienie przedmiotu dla wszystkich pytań
kallchk - checkbox kategorii dla wszystkich pytań
katall - ustawienie kategorii dla wszystkich pytań
*/
		var kallchk=$('#kallchk').prop('checked');
		var katall=$('#katall');
		//var katt=$('#katt');
		if (kallchk) katall.removeAttr('disabled'); else katall.attr('disabled','disabled');
		//alert(katall.attr('disabled'));
		//if (katt.length) katall.val(katt.val);
		$('#pytin .katp').each(function(){
			if (kallchk) {
				$(this).attr('disabled','disabled');
				var id=$(this).parents('.pytdiv').attr('id');
				setmodp(id);
			} else {
				$(this).removeAttr('disabled');
			}
		});
}

function setmodp(id) {
	$('#'+id+' input.mod').val($('#'+id+' input.pid').val());
}
//-->