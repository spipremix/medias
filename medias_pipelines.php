<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined('_ECRIRE_INC_VERSION')) return;

function medias_detecter_fond_par_defaut($fond){
	// traiter le cas pathologique d'un upload de document ayant echoue
	// car trop gros
	if (empty($_GET) AND empty($_POST) AND empty($_FILES)
	AND isset($_SERVER["CONTENT_LENGTH"])
	AND strstr($_SERVER["CONTENT_TYPE"], "multipart/form-data;")) {
		include_spip('inc/getdocument');
		erreur_upload_trop_gros();
	}
  return $fond;
}

function medias_post_insertion($flux){

	$objet = objet_type($flux['args']['table']);
	if (in_array($objet,array('article','rubrique'))
	  AND $id_auteur = intval($GLOBALS['visiteur_session']['id_auteur'])){

		# cf. GROS HACK ecrire/inc/getdocument
		# rattrapper les documents associes a cet objet nouveau
		# ils ont un id = 0-id_auteur
		$id_objet = $flux['args']['id_objet'];
		sql_updateq("spip_documents_liens", array("id_objet" => $id_objet), array("id_objet = ".(0-$id_auteur),"objet=".sql_quote($objet)));
	}

  return $flux;
}

function medias_configurer_liste_metas($config){
	$config['documents_article'] = 'non';
	$config['documents_rubrique'] = 'non';
	$config['documents_date'] = 'non';
	return $config;
}