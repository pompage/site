<?php

/* change all characters in URL to lowercase
***********************************************************/
define ('_url_minuscules',1);

/* modifies the way URL are created
***********************************************************/
$GLOBALS['url_arbo_types'] = array(
  'rubrique'=>'',
  'article'=>'',
  'mot'=>'tag'
);

/* always strip numeral prefix in titles
***********************************************************/
$table_des_traitements['TITRE'][]= 'typo(supprimer_numero(%s))';

/* for 'arbo' URL pattern
***********************************************************/
#$GLOBALS['url_arbo_parents']=array('article'=>array('id_rubrique','racine'));

$GLOBALS['debut_intertitre'] = '<h2>';
$GLOBALS['fin_intertitre']   = '</h2>';
?>
