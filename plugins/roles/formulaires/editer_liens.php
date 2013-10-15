<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2012                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

/**
 * Gestion du formulaire d'édition de liens 
 *
 * @package SPIP\Formulaires
**/
if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Retrouve la source et l'objet de la liaison
 *
 * À partir des 3 premiers paramètres transmis au formulaire,
 * la fonction retrouve :
 * - l'objet dont on utilise sa table de liaison (table_source)
 * - l'objet et id_objet sur qui on lie des éléments (objet, id_objet)
 * - l'objet que l'on veut lier dessus (objet_lien)
 * 
 * @param string $a
 * @param string|int $b
 * @param int|string $c
 * @return array
 *   ($table_source,$objet,$id_objet,$objet_lien)
 */
function determine_source_lien_objet($a,$b,$c){
	$table_source = $objet_lien = $objet = $id_objet = null;
	// auteurs, article, 23 :
	// associer des auteurs à l'article 23, sur la table pivot spip_auteurs_liens
	if (is_numeric($c) AND !is_numeric($b)){
		$table_source = table_objet($a);
		$objet_lien = objet_type($a);
		$objet = objet_type($b);
		$id_objet = $c;
	}
	// article, 23, auteurs
	// associer des auteurs à l'article 23, sur la table pivot spip_articles_liens
	if (is_numeric($b) AND !is_numeric($c)){
		$table_source = table_objet($c);
		$objet_lien = objet_type($a);
		$objet = objet_type($a);
		$id_objet = $b;
	}

	return array($table_source,$objet,$id_objet,$objet_lien);
}

/**
 * Chargement du formulaire d'édition de liens
 *
 * #FORMULAIRE_EDITER_LIENS{auteurs,article,23}
 *   pour associer des auteurs à l'article 23, sur la table pivot spip_auteurs_liens
 * #FORMULAIRE_EDITER_LIENS{article,23,auteurs}
 *   pour associer des auteurs à l'article 23, sur la table pivot spip_articles_liens
 * #FORMULAIRE_EDITER_LIENS{articles,auteur,12}
 *   pour associer des articles à l'auteur 12, sur la table pivot spip_articles_liens
 * #FORMULAIRE_EDITER_LIENS{auteur,12,articles}
 *   pour associer des articles à l'auteur 12, sur la table pivot spip_auteurs_liens
 *
 * @param string $a
 * @param string|int $b
 * @param int|string $c
 * @param bool $editable
 * @return array
 */
function formulaires_editer_liens_charger_dist($a,$b,$c,$editable=true){

	list($table_source,$objet,$id_objet,$objet_lien) = determine_source_lien_objet($a,$b,$c);
	if (!$table_source OR !$objet OR !$objet_lien OR !$id_objet)
		return false;

	$objet_source = objet_type($table_source);
	$table_sql_source = table_objet_sql($objet_source);

	// verifier existence de la table xxx_liens
	include_spip('action/editer_liens');
	if (!objet_associable($objet_lien))
		return false;
	
	// L'éditabilité :) est définie par un test permanent (par exemple "associermots") ET le 4ème argument
	$editable = ($editable and autoriser('associer'.$table_source, $objet, $id_objet));
	
	if (!$editable AND !count(objet_trouver_liens(array($objet_lien=>'*'),array(($objet_lien==$objet_source?$objet:$objet_source)=>'*'))))
		return false;

	// squelettes de vue et de d'association
	// ils sont différents si des rôles sont définis.
	$skel_vue   = $table_source."_lies";
	$skel_ajout = $table_source."_associer";

	// description des roles
	include_spip('inc/roles');
	if ($roles = roles_presents($objet_source, $objet)) {
		// on demande de nouveaux squelettes en conséquence
		$skel_vue   = $table_source."_roles_lies";
		$skel_ajout = $table_source."_roles_associer";
	}

	$valeurs = array(
		'id'=>"$table_source-$objet-$id_objet-$objet_lien", // identifiant unique pour les id du form
		'_vue_liee' => $skel_vue,
		'_vue_ajout' => $skel_ajout,
		'_objet_lien' => $objet_lien,
		'id_lien_ajoute'=>_request('id_lien_ajoute'),
		'objet'=>$objet,
		'id_objet'=>$id_objet,
		'objet_source'=>$objet_source,
		'table_source' => $table_source,
		'recherche'=>'',
		'roles' => $roles, # description des roles
		'visible'=>0,
		'ajouter_lien'=>'',
		'supprimer_lien'=>'',
		'definir_roles'=>'',
		'_oups' => _request('_oups'),
		'editable' => $editable,
	);

	return $valeurs;
}

/**
 * Traiter le post des informations d'édition de liens
 * 
 * Les formulaires peuvent poster dans quatre variables
 * - ajouter_lien et supprimer_lien
 * - remplacer_lien
 * - definir_roles
 *
 * Les deux premières peuvent être de trois formes différentes :
 * ajouter_lien[]="objet1-id1-objet2-id2"
 * ajouter_lien[objet1-id1-objet2-id2]="nimportequoi"
 * ajouter_lien['clenonnumerique']="objet1-id1-objet2-id2"
 * Dans ce dernier cas, la valeur ne sera prise en compte
 * que si _request('clenonnumerique') est vrai (submit associé a l'input)
 *
 * remplacer_lien doit être de la forme
 * remplacer_lien[objet1-id1-objet2-id2]="objet3-id3-objet2-id2"
 * ou objet1-id1 est celui qu'on enleve et objet3-id3 celui qu'on ajoute
 *
 * definir_roles doit être de la forme, et sert en complément de ajouter_lien
 * definir_roles[objet1-id1-objet2-id2] = array("role", "autre_role")
 * 
 * @param string $a
 * @param string|int $b
 * @param int|string $c
 * @param bool $editable
 * @return array
 */
function formulaires_editer_liens_traiter_dist($a,$b,$c,$editable=true){
	$res = array('editable'=>$editable?true:false);
	list($table_source,$objet,$id_objet,$objet_lien) = determine_source_lien_objet($a,$b,$c);
	if (!$table_source OR !$objet OR !$objet_lien)
		return $res;


	if (_request('tout_voir'))
		set_request('recherche','');


	if (autoriser('modifier',$objet,$id_objet)) {
		// annuler les suppressions du coup d'avant !
		if (_request('annuler_oups')
			AND $oups = _request('_oups')
			AND $oups = unserialize($oups)){
			$objet_source = objet_type($table_source);
			include_spip('action/editer_liens');
			foreach($oups as $oup) {
				if ($objet_lien==$objet_source)
					objet_associer(array($objet_source=>$oup[$objet_source]), array($objet=>$oup[$objet]),$oup);
				else
					objet_associer(array($objet=>$oup[$objet]), array($objet_source=>$oup[$objet_source]),$oup);
			}
			# oups ne persiste que pour la derniere action, si suppression
			set_request('_oups');
		}

		$supprimer = _request('supprimer_lien');
		$ajouter = _request('ajouter_lien');

		// il est possible de preciser dans une seule variable un remplacement :
		// remplacer_lien[old][new]
		if ($remplacer = _request('remplacer_lien')){
			foreach($remplacer as $k=>$v){
				if ($old = lien_verifier_action($k,'')){
					foreach(is_array($v)?$v:array($v) as $kn=>$vn)
						if ($new = lien_verifier_action($kn,$vn)){
							$supprimer[$old] = 'x';
							$ajouter[$new] = '+';
						}
				}
			}
		}

		if ($supprimer){
			include_spip('action/editer_liens');
			$oups = array();
			foreach($supprimer as $k=>$v) {
				if ($lien = lien_verifier_action($k,$v)){
					$lien = explode("-", $lien);
					list($objet_source,$ids,$objet_lie,$idl,$role) = $lien;
					// appliquer une condition sur le rôle si défini ('*' pour tous les roles)
					$cond = $role ? array('role' => $role) : array();
					if ($objet_lien==$objet_source){
						$oups = array_merge($oups,  objet_trouver_liens(array($objet_source=>$ids), array($objet_lie=>$idl), $cond));
						objet_dissocier(array($objet_source=>$ids), array($objet_lie=>$idl), $cond);
					}
					else{
						$oups = array_merge($oups,  objet_trouver_liens(array($objet_lie=>$idl), array($objet_source=>$ids), $cond));
						objet_dissocier(array($objet_lie=>$idl), array($objet_source=>$ids), $cond);
					}
				}
			}
			set_request('_oups',$oups?serialize($oups):null);
		}
		
		if ($ajouter){
			$ajout_ok = false;
			include_spip('action/editer_liens');
			foreach($ajouter as $k=>$v){
				if ($lien = lien_verifier_action($k,$v)) {
					$ajout_ok = true;
					list($objet1,$ids,$objet2,$idl) = explode("-",$lien);
					$roles = lien_retrouver_roles_postes($lien);
					if ($objet_lien==$objet1) {
						lien_ajouter_liaison($objet1, $ids, $objet2, $idl, $roles);
					} else {
						lien_ajouter_liaison($objet2, $idl, $objet1, $ids, $roles);
					}
					set_request('id_lien_ajoute',$ids);
				}
			}
			# oups ne persiste que pour la derniere action, si suppression
			# une suppression suivie d'un ajout dans le meme hit est un remplacement
			# non annulable !
			if ($ajout_ok)
				set_request('_oups');
		}
	}

	
	return $res;
}

/**
 * Retrouver l'action de liaision demandée
 * 
 * Les formulaires envoient une action dans un tableau ajouter_lien
 * ou supprimer_lien
 * 
 * L'action est de la forme : objet1-id1-objet2-id2
 * ou de la forme : objet1-id1-objet2-id2-role
 *
 * L'action peut-être indiquée dans la clé ou dans la valeur.
 * Si elle est indiquee dans la valeur et que la clé est non numérique,
 * on ne la prend en compte que si un submit avec la clé a été envoyé
 *
 * @internal
 * @param string $k Clé du tableau
 * @param string $v Valeur du tableau
 * @return string Action demandée si trouvée, sinon ''
 */
function lien_verifier_action($k,$v) {
	$action = '';
	if (preg_match(",^\w+-[\w*]+-[\w*]+-[\w*]+(-[\w*])?,",$k))
		$action = $k;
	if (preg_match(",^\w+-[\w*]+-[\w*]+-[\w*]+(-[\w*])?,",$v)){
		if (is_numeric($k))
			$action = $v;
		if (_request($k))
			$action = $v;
	}
	// ajout un role null fictif (plus pratique) si pas défini
	if ($action and count(explode("-", $action)) == 4) {
		$action .= '-';
	}
	return $action;
}


/**
 * Retrouve le ou les roles postés avec une liaison demandée
 *
 * @internal
 * @param string $lien    Action du lien
 * @return array          Liste des rôles. Tableau vide s'il n'y en a pas.
**/
function lien_retrouver_roles_postes($lien) {
	// un role est défini dans la liaison
	$defs = explode('-', $lien);
	list(,,,,$role) = $defs;
	if ($role) return array($role);

	// retrouver les rôles postés pour cette liaison, s'il y en a.
	$roles = _request('definir_roles');
	if (!$roles OR !is_array($roles)) {
		return array();
	}

	// pas avec l'action complete (incluant le role)
	if (!isset($roles[$lien]) OR !$roles = $roles[$lien]) {
		// on tente avec l'action sans le role
		array_pop($defs);
		$lien = implode('-', $defs);
		if (!isset($roles[$lien]) OR !$roles = $roles[$lien]) {
			$roles = array();
		}
	}

	// pas de rôle vide
	return array_filter($roles);
}

/**
 * Ajoute les liens demandés en prenant éventuellement en compte le rôle
 *
 * Appelle la fonction objet_associer. L'appelle autant de fois qu'il y
 * a de rôles demandés pour cette liaison.
 * 
 * @internal
 * @param string $objet_source   Objet source de la liaison (qui a la table de liaison)
 * @param array|string $ids      Identifiants pour l'objet source
 * @param string $objet_lien     Objet à lier
 * @param array|string $idl      Identifiants pour l'objet lié
 * @return void
**/
function lien_ajouter_liaison($objet_source, $ids, $objet_lien, $idl, $roles) {

	// retrouver la colonne de roles s'il y en a a lier
	if ($roles and $colonne_role = roles_colonne($objet_source, $objet_lien)) {
		foreach ($roles as $role) {
			objet_associer(array($objet_source=>$ids), array($objet_lien=>$idl), array($colonne_role => $role));
		}
	} else {
		objet_associer(array($objet_source=>$ids), array($objet_lien=>$idl));
	}
}

?>
