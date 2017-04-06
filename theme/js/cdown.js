<!--
$(document).ready(function(){

	function shkom(kom,kl,add) {
		kom = '<span class="'+kl+'">'+kom+'</span>';
		if (add) $('#kom').html($('#kom').html()+'<br />'+kom);
		else $('#kom').html(kom);
	}
	
	$('.pwybor').change(function(){
		var cask=$('#cask').val();
		$('#pmod'+cask).val(1);
	});

	var fid;//identyfikator licznika czasu
	
	$('#zapisz').click(function(){
		if ($('#czas').length) clearTimeout(fid); //zatrzymaj odliczanie
		var cask=$('#cask').val();
		$(this).blur();
		$(this).attr('disabled','disabled');
		var nrpyt=$('#test .pyt').attr('id').substr(3);
		var typ=$('#t'+nrpyt).val();
		var error=false;
		switch (parseInt(typ)) {
			case 0:
			case 6:
				$('#test div.pytodp input:text').each(function(){
					if ($(this).val().length < 1) error = true;
				});
			break;
			case 1:
				if ($('#test div.pytodp input:checked').length < 1) {
					error = true;
				}
			case 2:
				if ($('#test div.pytodp input:checked').length < 1) {
					error = true;
				}
			break;
			case 3:
				//var foundone = false;
				$('#test div.pytodp input:text').each(function(){
					if ($(this).val().length < 1 || $(this).val().indexOf('...')==0) error = true;
					//if ($(this).val().indexOf('...')==0) error = true;
				});
			break;
			case 4:
				//var foundone = false;
				$('#test div.pytodp textarea').each(function(){
					if ($(this).val().length < 1 || $(this).val().indexOf('...')==0) error = true;
					//if ($(this).val().indexOf('...')==0) error = true;
					//if ($(this).val()===$(this).prop('defaultValue')) error = true;
				});
				//if (foundone) error = false; //wystarczy odpowiedź w 1 polu tekstowym
			break;
			case 5:
				$('#test div.pytodp select').each(function(){
					if($(this).val()==='Wybierz odpowiedź...') error=true;
				});
				$('#test div.pytodp input:text').each(function(){
					if ($(this).val().length < 1 || $(this).val().indexOf('...')==0) error = true;
				});
			break;
			default:
				alert('Nieobsługiwany typ.');	
		}
		var dane=$('#test').serialize();

		$('#load').show();
		$.post(
			'index.php',
			dane,
			function(ret) {
				//alert(ret);
				//if (parseInt(ret)<1) shkom('Nie zapisano pustej odpowiedzi!','info');
				$('#load').hide();
				var answer=$('#answered').html();
				if (answer.indexOf(cask)<0) {
					$('#answered').html(answer+' [<button id="btnodp'+cask+'" class="nextp but">'+cask+'</button>]');
				}
				$('#kom span.error').each(function(){
					if ($(this).text().indexOf(cask+':')>0) {
						$(this).remove();
					}	
				});

				if (error) {
					$('#btnodp'+cask).addClass('errinfo');
					shkom('Pytanie '+cask+': Nie udzieliłeś (pełnej) odpowiedzi!','error',0);
				} else {
					$('#btnodp'+cask).removeClass('errinfo');
					//shkom('Pytanie '+cask+': Zapisano odpowiedź!','info');
				}
				$('#pmod'+cask).val(0);
				$('input:button').removeAttr('disabled');
				nextP(parseInt(cask)+1);
				if ($('#czas').length) timer(); //wznów odliczanie
			},
			'html'
		);
	});

	if ($('#typ').val() == 1) $('form input[type=submit]').val('Wyślij ankietę');

	$('#test').submit(function(){
		//$('.lpytan').each(function(){//czy udzielono odpowiedzi na wszystkie pytania?
		//	alert($(this).find('input[type=radio:checked]'));
		//});
		if ($('#sure').prop('checked')) {
			$('#op').remove();
			$('#load').show();
			$('#pytania div.pyt').css('visibility','hidden');
			$('#pytania div.pyt').each(function(){
				$('#ask').append($(this));
			});
		} else {
			alert('Proszę zaznaczyć pole "Skończyłem"!');
			$(this).blur();
			return false;
		}
		return true;
	});

	$('#tend').click(function(event){
		if (!$('#sure').prop('checked')) {
			event.preventDefault();
			alert('Proszę zaznaczyć pole "Skończyłem"!');
		}
	});

	$('.popen').click(function(){
		var str=$(this).val();
		if (str.indexOf('...')==0) $(this).val(str.substr(3));
	});

	$(document).on("click",".nextp",function(){
		var cask=$('#cask').val();
		var isMod=$('#pmod'+cask).val();
		if (isMod == 1) {
			shkom('Pytanie '+cask+': Zapisz zmienioną odpowiedź!','error',0);
			return;
		}
		$(this).blur();
		nextP($(this).html());
	});

	function nextP(cask){
		var id=$('#ask div:first').attr('id');
		if (id) $('#pytania').append($('#ask div:first'));
		ilep = $('#ilep').val();
		if (parseInt(cask) <= parseInt(ilep)) {
			$('#ask').append($('#pyt'+cask));
			$('#cask').val(cask);
		} else if (parseInt(cask) > parseInt(ilep)) {
			$('#ask').append($('#pyt'+ilep));
			var answer=$('#answered').html();
			if (answer.indexOf('już')<0) $('#answered').html(answer+'<br />Przejrzałeś już wszystkie pytania! Aby zakończyć, zaznacz pole "Skończyła(e)m" i naciśnij przycisk "Oceń test/Zapisz ankietę".');	
		}
	}

	function timer(){
		czas=$('#czas').val().split(":");
		m=parseInt(czas[0]);
		if (czas[1].charAt(0)=='0') s=parseInt(czas[1].charAt(1));
		else s=parseInt(czas[1]);
		if (m==3 && s==0) {
			$('#czaskom').html('Pozostały 3 minuty. Sprawdź odpowiedzi.');
		} else if (m==2 && s==0) {
			$('#czaskom').html('Pozostały 2 minuty.');
		} else if (m==1 && s==0) {
			$('#czaskom').html('Pozostała 1 minuta.');
		}

		if (m==0 && s==0) {
			alert("Czas rozwiązywania testu dobiegł końca.\nTest zostanie oceniony.");
			clearTimeout(fid);
			$('#sure').attr('checked','checked');
			$('#test').submit();
		}

		if (s>0) --s; else { --m; s=59; }
		czas=m+':';
		if (s<10) czas+='0'+s; else czas+=s;
		$('#czas').val(czas);
		fid=setTimeout(function(){timer()},1000);
	}

	nextP(1);
	if ($('#czas').length) timer();
});
//-->