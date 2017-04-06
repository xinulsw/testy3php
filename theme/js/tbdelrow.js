<!--
$(document).ready(function(){
	$('.tbaddrow select').change(function(){
		var id=$(this).parent().siblings('.nob').find('.mod').attr('id');
		$('#'+id).val(id.substr(3));
	});
	$('.itext').change(function(){
		var id=$(this).parent().siblings('.nob').find('.mod').attr('id');
		$('#'+id).val(id.substr(3));
	});
	$('#btnAddRow').click(function(){
		//var id = $(this).prev('form .tbaddrow').attr('id');
		addRowToTable();
	});
	$('form').submit(function(){
		var ret=true;
		$(".error").html("");
		$(".itext").each(function(){
			var max=1;
			if ($(this).val().length<1) {
				//alert($(this).val());
				$(".error").html("Uzupełnij wszystkie pola!");
				ret=false;
			}
		});
		return ret;
	});
});
$(document).on("change",".itext", function(){
	var id=$(this).parent().siblings('.nob').find('.mod').attr('id');
	$('#'+id).val(id.substr(3));
});
$(document).on("click",".btnDel",function(){
		var tb_id=$('.tbaddrow').attr('id');
		$(this).parent().parent().remove();
		reorder();
	});


function errl(val,min,max) {//funkcja pomocnicza
	var ln=val.length;
	alert(val+" "+ln+" "+min+" "+max);
	if (ln<1) return true;
	else if (ln < min) return true;
	else if (ln > max) return true;
	return false;
}

function getNrTr(){
	var tbody = $('.tbaddrow tbody').length;
	if (tbody) return $('.tbaddrow tbody tr').length;
	else return $('.tbaddrow tr').length;
}

function addRowToTable() {
	var tb_id=$('.tbaddrow').attr('id');
	var row_base = 1; //pierwszy numer do wyświetlenia
	var nextRow = getNrTr();
	var numRow = nextRow + row_base;
	var tdt=new Array();
	cl='tbrow' + (numRow % 2);
	kod='<tr class="'+cl+'"><td class="tc">'+numRow+'</td>';
	if (tb_id == 'nAdd') {
		tdt[1]=addEl('input','text','tadd0[]','','15','itext','');
		tdt[2]=addEl('input','text','tadd2[]','','20','itext','');		
	} else if (tb_id == 'nEdit') {
		tdt[1]=addEl('input','text','tadd0[]','','15','itext','');
		tdt[2]=addEl('input','text','tadd2[]','','20','itext','');
		tdt[3]=addEl('input','text','tadd3[]','','15','itext','');
		tdt[4]=addEl('input','text','tadd4[]','','15','itext','');		
	} else if (tb_id == 'grEd') {
		kod+='<td><input class="itext" name="tadd0[]" size="10" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd1[]" size="40" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd2[]" size="10" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd3[]" size="3" type="text" /></td>';
		//kod+='<td>'+$('#rszk').html()+'</td>';
	} else if (tb_id == 'szkEdit') {
		kod+='<td><input class="itext" name="tadd0[]" size="50" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd1[]" size="20" type="text" /></td>';
	} else if (tb_id == 'katEdit') {
		kod+='<td>'+$('#przid').html()+'</td>';
		kod+='<td><input class="itext" name="tadd1[]" size="50" type="text" /></td>';
	} else if (tb_id == 'gpEdit') {
		kod+='<td>'+$('#gid').html()+'</td>';
		kod+='<td>'+$('#przid').html()+'</td>';
	} else if (tb_id == 'tEdit') {
		kod+='<td>'+$('#kid').html()+'</td>';
		kod+='<td><input class="itext" name="tadd1[]" size="3" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd2[]" size="35" type="text" /></td>';
		kod+='<td><input class="itext" name="tadd3[]" size="3" type="text" /></td>';
		kod+='<td><input class="icheck" name="tadd3[]" size="3" type="checkbox" /></td>';
		kod+='<td><a class="but" href="">Edytuj</a></td>';
	} else {
		tdt[1]=addEl('input','text','tadd0[]','','20','itext','');
	}
	kod+='<td><input class="btnDel but" type="button" value="Usuń" /></td>';
	kod+='</tr>';
	$('.tbaddrow > tbody:last').append(kod);
}

function reorder(){
	$('.tbaddrow tbody tr').each(function(){
		var rIndex=$(this)[0].sectionRowIndex;
		var td=$(this).find('td:first');
		if (td.html().indexOf('input')> -1) {
			var html=td.html();
			if (html.indexOf('href') > -1)
				td.find('a').text(rIndex+1);
			else {
				var pos=html.indexOf('<input');
				var html=html.substr(pos);
				td.html((rIndex+1)+html);
			}
		} else td.text(rIndex+1);
	});
	$('.tbaddrow tr:odd').attr('class','tbrow1');
	$('.tbaddrow tr:even').attr('class','tbrow0');
}
//-->