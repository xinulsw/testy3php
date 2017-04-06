<?php
if ($user->n) {
	echo '
	<p>Szybko do celu:</p>
	<ol>
		<li>Dodaj <a href="?acc=10&acc3=gsh">grupę(y)</a>.</li>
		<li>Dodaj <a href="?acc=8">kategorię(e)</a>.</li>
		<li>Dodaj <a href="?acc=8">pytania</a></li>
		<li>Zdefiniuj <a href="?acc=9">test(y)</a> i przypisz go grupie(om).</li>
		<li>Przekaż "token" przypisany danej grupie wszystkim zainteresowanym, aby mogli się do niej dopisać w dziale <code>Profil/Wybierz grupę</code>.</li>
		<li>Testy/ankiety publiczne są dostępne dla wszystkich, testy przypisane &#8211; dostępne są tylko wskazanym grupom.</li>
		<li>Przeglądaj, oceniaj, usuwaj <a href="?acc=10">wyniki</a>.</li>
	</ol>
	<p>Ad. 1.: Możesz przeglądać, usuwać, resetować hasła użytkowników, którzy dopisali się do grupy, klikając numer grupy na liście grup.
	<p>Ad. 4.: Możesz dodać pytania i później przypisać je do testu, albo dodać test i edytować (dodawać) dla niego pytania.</p>
	';
} else {
	echo '
<ul>
	<li>Z modułu "Testy" można korzystać anonimowo, można też założyć konto.</li>
	<li>Dostęp anonimowy pozwala na wyszukiwanie i rozwiązywanie testów/ankiet publicznych, wyniki nie są zapisywane.</li>
	<li>Aby rozwiązywać testy zamknięte, należy utworzyć konto, a następnie przypisać je do grup(y), podając hasło otrzymane od założyciela grupy.</li>
	<li>Aby dodawać pytania, testy i grupy należy utworzyć konto w serwisie i przejść do działu Profil/Dodaj testy/grupy.</li>
	<li>"Grupa" w kontekście serwisu zawiera użytkowników, którzy chcą rozwiązywać testy, albo wypełniać ankiety dodane przez założyciela grupy.</li>
	<li><a href="?acc=12">Założenie konta</a> umożliwia: zapisywanie/przeglądanie/usuwanie wyników testów publicznych oraz otwartych, a w przypadku kont typu "nauczyciel": dodawanie grup, kategorii, pytań, testów i ankiet.</li>
</ul>
';
}
CMsg::kadd('Kontakt z administratorem: <a href="javascript:void(location.href=\'mailto:\'+String.fromCharCode(97,100,109,105,110,64,99,101,110,116,114,117,109,46,118,111,116,46,112,108))">admin</a>.');
//print_r($user->tb); echo '<br />';
?>