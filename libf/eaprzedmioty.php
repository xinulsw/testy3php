<?php if(!defined('IN_CT')){ die('You cannot load this page directly.'); }
	CHtml::addRes('scripts','tbdelrow.js');
	include_cl(array('przedmioty'));
	$przedmioty=new przedmioty(null,$user->uid);
	$przedmioty->getTb2();
	CValid::$acc3='psv';
	if (isset($_POST['tadd0'])) sav_tab($przedmioty,$user,$kom);
	CHtml::sh_tab($przedmioty,$tbop=array('kom'=>'Lista przedmiotów','bDel'=>0,'lDel'=>1,'bAdd'=>1));
?>