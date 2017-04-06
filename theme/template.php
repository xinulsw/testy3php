<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); } ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<title>E-Testy Edukacyjne</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Language" content="pl" />
<meta name="author" content="wdesign" />
<meta name="Description" content="Platforma E-Testy Edukacyjne umożliwia tworzenie testów edukacyjnych oraz sprawdzanie wiedzy przy ich wykorzystaniu. Załóż konto, twórz sprawdziany, dodawaj materiały edukacyjne." />
<meta name="Keywords" content="e-testy,e-testy free,testy www,testy edukacyjne,testy free,testy język polski,testy informatyka" />
<meta name="Robots" content="all" />
	<link href="<?php get_theme_path(); ?>css/reset.css" rel="stylesheet" type="text/css" />
	<link href="<?php get_theme_path(); ?>css/style.css" rel="stylesheet" type="text/css" />
	<link href="<?php get_theme_path(); ?>css/testy.css" rel="stylesheet" type="text/css" />
	<!--[if lt IE 7 ]>
    <script src="<?php get_theme_path(); ?>js/dd_belatedpng.js"></script>
    <script> DD_belatedPNG.fix('img, .png_bg'); //fix any <img> or .png_bg background-images </script>
  <![endif]-->
<?php CHtml::getHeader('styles'); ?>
</head>
<body>
<!--[if lte IE 8]>
	<div>
		<p>Używasz przeglądarki Internet Explorer 6 lub 7, które ze względu na podatność na ataki złośliwego oprogramowania zostały wycofane.<br />
		Zainstaluj IE w wersji 8 lub najlepiej <a rel="nofollow" href="http://windows.microsoft.com/ie9">9</a>, albo wypróbuj lepsze programy:
		<a rel="nofollow" href="http://www.firefox.pl/">Mozilla Firefox</a>,
		<a rel="nofollow" href="http://www.opera.com/">Opera</a>,
		<a rel="nofollow" href="http://www.google.com/chrome/">Google Chrome</a>.
		</p>
	</div>
<![endif]-->
	<!-- site header -->
	<div id="header">
		<div class="header">
			<div class="wrapper">
				<div id="motto">
					<div class="fll">Czego dowiesz się sam, tego się nauczysz!</div>
					<div id="login" class="flr">
			<?php if ($user->uid) echo 'Zalogowano: '.$user->login.'('.$user->kto.') &bull; <a href="?acc=1&amp;ul=1">Wyloguj</a>&nbsp;'; else {?>
				<form name="flog" id="flog" action="." method="post">
				<input name="lnm" id="lnm" type="text" size="15" value="Login..." />
				<input name="lps" id="lps" type="text" size="20" value="Hasło..." />
				<input type="submit" value="Zaloguj" class="but" />
				</form>
			<?php	} ?>
					</div>
				</div>
				<!-- logo/sitename -->
				<a id="logo" href="http://ecg.vot.pl">eCG</a>
				<!-- main navigation -->
				<div id="nav">
						<?php CMenu::getMenu(); ?>
				</div>
			</div>
		</div>
		
		<div id="breadcrumbs" >
			<div class="wrapper">
				<div class="left"><a href="http://ecg.vot.pl">eCG</a> &raquo; <a href=".">Testy</a></div>
				<div class="right">
					<?php GetDzis(); ?>
				</div>
			</div>
		</div>
		
  </div> <!-- koniec header -->
	<div class="wrapper clearfix">
		<!-- page content -->
		<div id="article">
			<div class="section">
				
				<!-- title and content -->
				<noscript><p>!!! Twoja przeglądarka nie obsługuje języka JavaScript! Aby korzystać z wszystkich możliwości serwisu, uaktywnij obsługę JavaScript.</p></noscript>
				<h1><?php echo CMenu::getPage($user->kto,CValid::$acc,2); ?></h1>
					<?php
						CMsg::getKom(1);//komunikaty ontop
						if (file_exists(CT_LIB . CMenu::$plik . '.php'))
							include(CT_LIB . CMenu::$plik . '.php');
						else {
							echo '<p>Brak zasobu!</p>';
						}
						CMsg::getKom(0);//komunikaty onend
					?>
				<!-- page footer -->
				<div class="footer cb">
					<div id="error"></div>
					<div id="kom"></div>
					<?php CMsg::pall(true); ?>
				</div>
			</div>
		</div>
		
		<!-- include the sidebar template -->
		<!-- //include('sidebar.php'); -->
	</div>

	<!-- site footer -->
	<div id="footer" class="clearfix" >
		
	 	<div class="wrapper">
			<div class="left" style="width: 40%;">&nbsp;&copy; 2011-2014 wDesign&nbsp;v. 1.2.0</div>
			<?php echo '<div class="left">'.get_execution_time().'</div>'; ?>
			<div class="right">&middot; Theme by <a href="http://www.cagintranet.com" >Cagintranet</a> highly modified by wDesign</div>
		</div>
	</div>
	<div id="tip"><div id="tiph">Testy</div><div id="tipc"></div></div>
<script type="text/javascript" src="<?php get_theme_path(); ?>js/jquery-1.11.1.min.js"><!-- //--></script>
<script type="text/javascript" src="<?php get_theme_path(); ?>js/jquery-ui-1.10.4.min.js"><!-- //--></script>
<script type="text/javascript" src="<?php get_theme_path(); ?>js/testy.js"><!-- //--></script>
<?php CHtml::getHeader('scripts'); CHtml::getJS(); ?>
</body>
</html>
