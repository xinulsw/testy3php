<!--
var debug=true;

$(document).ready(function(){
	//---ogolne---//
	$('input[type=radio]').change(function(){
		$(this).blur();
	});
	//---logowanie---//
	$('#lnm').focus(function(){
		$(this).val('');
	});
	$('#lps').focus(function(){
		$(this).val('');
		$(this).prop('type','password');
	});
	//---testy---//
	$('#katt').change(function(){
		$('#czas').focus();
	});
	
	$('.modt').change(function(){
		if ($('#modt').val()) return; //wartość już ustawiona
		$('#modt').val($('#idt').val());
	});

	$('#ileplos').keyup(function(){
		var ilep=parseInt($.trim($(this).val()));
		if (ilep>0) {
			var ilept=parseInt($('#ilept').val());
			if (ilep<0 || ilep>ilept || ilep==ilept) {
				alert('Wprowadź liczbę większą od zera i mniejszą od wszystkich pytań w teście ('+ilept+').');
				$(this).val('');
				return;
			}
		}
		//if (ilep>0) $('div.losphide').toggle(false); else $('div.losphide').toggle(true);
	});
	//$('#skala').change(function(){
	//	if ($('#ttyp').is(':checked')) $(".error").html("W przypadku ankiet wybór skali nie ma znaczenia.	");
	//});
	$('#saddpk').change(function(){
		var loc='?acc='+$('#acc').val()+'&acc3=adp&idt='+$('#idt').val()+'&idk='+$('#saddpk').val();
		document.location=loc;
	});
	$('input.tbgropen').click(function(){
		if ($(this).is(':checked')) {
			$(this).next('input:checkbox').attr('checked','checked');
		}
	});
	$('input.tbgr').click(function(){
		if (!$(this).is(':checked')) {
			$(this).prev('input:checkbox').removeAttr('checked');
		}
	});
	$('#btnLosP').click(function(){
		var maxpyt=$('#maxpyt').val();
		if ($('#ilep').val()<2 || $('#ilep').val()>maxpyt) { alert('Podaj liczbę większą od 1 i mniejszą od '+maxpyt); return; }
		var loc='?acc='+$('#acc').val()+'&acc3=adp&idt='+$('#idt').val()+'&idk='+$('#saddpk').val()+'&ilep='+$('#ilep').val();
		document.location=loc;
	});
	$('#ttyp').click(function(){
		if ($(this).is(':checked')) {
			$('#skala').val(1);
			$('#skala').attr('disabled','disabled');
		} else {
			$('#skala').removeAttr('disabled');
		}
	});

	//---Ajax---//
	$('.footer').ajaxError(function(){
		$(this).html('Błąd wywołania!');
	});

	//przycisk punktacji w pytaniach otwartych
	$('input.ocen').click(function(){
		var ilepkt=$(this).prev('input').val();
		var winfo=$(this).next('input').val();
		if (typeof ilepkt !== 'undefined') {
			if ($(this).prev('input').prop('defaultValue')==ilepkt) {
				alert('Zmień najpierw ilość punktów!');
				return ;
			}
			var ilemax=$(this).attr('id').substring(4);
		
			if (isNaN(ilepkt) || parseInt(ilepkt)<0 || parseInt(ilepkt)>parseInt(ilemax)) {
				alert('Błędna ilość punktów! Przyznaj 0-'+ilemax+' punktów!');
				$(this).prev('input').addClass('bad');
				return;
			}
			$(this).prev('input').removeClass('bad');
			var winfo=winfo+'#'+ilepkt;
		}
		//alert (winfo); return;
		var dane={ acc: "24", acc2: "ocn", acc3: winfo };
		$('#load').show();
		$.get(
			'index.php',
			dane,
			function(data){
				$('#load').hide();
				$('#kom').html(data);
			}
		);
	});

//--- zaznacz wszystkie tdel
	$("input.seltdel").click(function(){
		if ($(this).prop("checked")) $("input.tdel").prop("checked","checked");
		else $("input.tdel").prop("checked","");
	});
//--- zaznacz wszystkie checkboksy
	$("input.selallchk").click(function(){
		//alert($(this).prop("checked"));
		if ($(this).prop("checked")) $("input:checkbox").prop("checked","checked");
		else $("input:checkbox").prop("checked","");
	});
	//sortowanie tabel
	//$('#tbsort').stupidtable();
	$(".tip").tooltip({track:true});
	//$(".dialog").dialog({ appendTo: tobind, autoOpen: false });
});

function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

$(document).on("mouseover","input.showdlg", function(){
	//$(this).prop('title',nl2br($(this).prop('title')));
	var id=$(this);
	var co=$(this).next('div').html();
	var isOpen = $('.dialog').dialog('isOpen');
	//alert(isOpen);
	if (isOpen) $('.dialog').dialog('destroy');
	return $('<div class="dialog" title="Dołączony materiał:"><p>' + nl2br(co) + '</p></div>').dialog({ closeText: '[x]', resizeable:false, draggable:true, position:{ my: "left top", at: "left bottom", of: id } });
});

$(document).on("click","input[type=button]", function(){
	$(this).blur();
});
$(document).on("click","input[type=checkbox]", function(){
	$(this).blur();
});
$(document).on("change","select", function(){
	$(this).blur();
});
//Ajax
$(document).on("change","select.ajax",function(){
	if ($(this).val() > 0) {
		$('#load').show();
		var id=$(this).attr('id');
		switch(id){
			case 'tgn':
				var dane={ acc: "24", acc2: "tgn", acc3: $(this).val() };
				$('#tbt').val(0);$('#tba').val(0);
			break;
			case 'uwn':
				var dane={ acc: "24", acc2: "uwn", acc3: $(this).val(), ids: $('#tgn').val() };
				$('#tgn').val(0);
			break;
			case 'tep':
				var dane={ acc: "24", acc2: "tep", acc3: $(this).val() };
				$('#tek').val(0);
			break;
			case 'tek':
				var dane={ acc: "24", acc2: "tek", acc3: $(this).val() };
				$('#tep').val(0);
			break;
			case 'anp':
				var dane={ acc: "24", acc2: "anp", acc3: $(this).val() };
			break;
			case 'ank':
				var dane={ acc: "24", acc2: "ank", acc3: $(this).val() };
			break;
			case 'tbt':
				var dane={ acc: "24", acc2: "tbt", acc3: $(this).val() };
				$('#tgn').val(0);$('#tba').val(0);
			break;
			case 'tba':
				var dane={ acc: "24", acc2: "tba", acc3: $(this).val() };
				$('#tgn').val(0);$('#tbt').val(0);
			break;
			case 'tbk'://lista testów z danej kategorii
				var dane={ acc: "24", acc2: "tbk", acc3: $(this).val() };
			break;
			default:
				$('#load').hide();
				return;
		}
			$.get(
			'index.php',
			dane,
			function(data){
				$('#load').hide();
				$('#response').html(data);
				if (id == 'tgn'|| id == 'uwn' || id == 'tpb' || id == 'tbt') {
					sortables_init();
				}
			}
		);
	}
});

function onlyNr(evt) {
	var e = evt;
	var charCode = 0;
	if(window.event){ // IE
		var charCode = e.keyCode;
	} else if (e.which) { // Safari, Firefox
		var charCode = e.which
	}
	if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;
	return true;
}

function onlyNriK(evt) {
	var e = evt;
	var charCode = 0;
	if(window.event){ // IE
		var charCode = e.keyCode;
	} else if (e.which) { // Safari, Firefox
		var charCode = e.which
	}
	if (charCode > 31 && (charCode < 46 || charCode > 57 || charcode < 65 || charcode > 90) || charCode == 47) return false;
	return true;
}

function getEl(id) {
	el=document.getElementById(id);
	if (typeof el == 'undefined') return 0;
	else return el;
}

function load(page) {
	this.location=page;
}

function errlen(id,nazwa,min,max) {
	el=getEl(id);
	errmsg='Bład w polu "'+nazwa+'": ';
	if (el && parseInt(el.value.length)<min) {
		alert (errmsg+'za mało znaków!');
		return true;
	}
	if (el && parseInt(el.value.length)>max) {
		alert (errmsg+'za dużo znaków!');
		return true;
	}
	return false;
}

function addCh(cel,co) {
	document.getElementById(cel).appendChild(co);
	return;
}

function addEl(typ,styp,nazwa,id,s,k,v) {//typ,sub typ,nazwa,id,size,klasa,wartość
	var newE=document.createElement(typ);
	if (styp.length>0) newE.setAttribute('type', styp);
	if (nazwa.length>0) newE.setAttribute('name', nazwa);
	if (id.length>0) newE.setAttribute('id', id);
	if (s && s>0) newE.setAttribute('size', s);
	if (k && k.length>0) newE.className=k;
	newE.value=v;
	return newE;
}

function setmod(id) {//		flaga modyfikacji
	//alert(id);
	wid=id.substr(3);
	el=getEl('mod'+wid);
	el.value=wid;
}
//-->
