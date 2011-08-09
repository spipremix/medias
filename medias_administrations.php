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

/**
 * verifier et maj le statut des documents
 * @param bool $affiche
 * @return
 */
function medias_check_statuts($affiche = false){
	$trouver_table = charger_fonction('trouver_table','base');
	$desc = $trouver_table('documents');
	# securite, si jamais on arrive ici avant un upgrade de base
	if (!isset($desc['field']['statut']))
		return;

	// utiliser sql_allfetsel pour clore la requete avant la mise a jour en base sur chaque doc (sqlite)
	// iterer par groupe de 100 pour ne pas exploser sur les grosses bases
	$docs = array_map('reset',sql_allfetsel('id_document','spip_documents',"statut='0'",'','',"0,100"));
	while (count($docs)){
		include_spip('action/editer_document');
		foreach($docs as $id_document)
			// mettre a jour le statut si necessaire
			instituer_document($id_document);
		if ($affiche) echo " .";
	  $docs = array_map('reset',sql_allfetsel('id_document','spip_documents',"statut='0'",'','',"0,100"));
	}
}

/**
 * Mise a jour de la BDD
 * @param string $nom_meta_base_version
 * @param string $version_cible
 */
function medias_upgrade($nom_meta_base_version,$version_cible){
	if (!isset($GLOBALS['meta'][$nom_meta_base_version])){
		$trouver_table = charger_fonction('trouver_table','base');
		if ($desc = $trouver_table('spip_documents')
		  AND !isset($desc['field']['statut']))
			ecrire_meta($nom_meta_base_version,'0.1.0');
	}

	$maj = array();
	$maj['create'] = array(
		array('maj_tables',array('spip_documents','spip_documents_liens','spip_types_documents')),
		array('creer_base_types_doc')
	);
	$maj['0.2.0'] = array(
		array('sql_alter',"TABLE spip_documents ADD statut varchar(10) DEFAULT '0' NOT NULL"),
	);
	$maj['0.3.0'] = array(
		array('sql_alter',"TABLE spip_documents ADD date_publication datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"),
	);
	$maj['0.4.0'] = array(
		// recalculer tous les statuts en tenant compte de la date de publi des articles...
		array('medias_check_statuts',true),
	);
	$maj['0.5.0'] = array(
		array('sql_alter',"TABLE spip_documents ADD brise tinyint DEFAULT 0"),
	);
	$maj['0.6.0'] = array(
		array('sql_alter',"TABLE spip_types_documents ADD media varchar(10) DEFAULT 'file' NOT NULL"),
		array('creer_base_types_doc'),
	);
	$maj['0.7.0'] = array(
		array('sql_alter',"TABLE spip_documents ADD credits varchar(255) DEFAULT '' NOT NULL"),
	);
	$maj['0.10.0'] = array(
		array('sql_alter',"TABLE spip_documents CHANGE fichier fichier TEXT NOT NULL DEFAULT ''"),
	);
	$maj['0.11.0'] = array(
		array('sql_alter',"TABLE spip_documents CHANGE mode mode varchar(10) DEFAULT 'document' NOT NULL"),
	);
	$maj['0.11.0'] = array(
		array('sql_alter',"TABLE spip_documents CHANGE mode mode varchar(10) DEFAULT 'document' NOT NULL"),
	);
	$maj['0.12.0'] = array(
		array('medias_maj_meta_documents'),
	);
	$maj['0.14.0'] = array(
		array('creer_base_types_doc'),
	);
	$maj['0.15.0'] = array(
		array('creer_base_types_doc'),
	);
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);

	medias_check_statuts();
}

/**
 * Maj des meta documents
 */
function medias_maj_meta_documents(){
	$config = array();
	if (isset($GLOBALS['meta']['documents_article']) AND $GLOBALS['meta']['documents_article']!=='non')
		$config[] = 'spip_articles';
	if (isset($GLOBALS['meta']['documents_rubrique']) AND $GLOBALS['meta']['documents_rubrique']!=='non')
		$config[] = 'spip_rubriques';
	ecrire_meta('documents_objets',implode(',',$config));
}

/*
function medias_install($action,$prefix,$version_cible){
	$version_base = $GLOBALS[$prefix."_base_version"];
	switch ($action){
		case 'test':
			# plus necessaire si pas de bug :p
			# medias_check_statuts();
			return (isset($GLOBALS['meta'][$prefix."_base_version"])
				AND version_compare($GLOBALS['meta'][$prefix."_base_version"],$version_cible,">="));
			break;
		case 'install':
			medias_upgrade('medias_base_version',$version_cible);
			break;
		case 'uninstall':
			# pas de deinstallation sur les documents pour le moment, trop dangereux
			# medias_vider_tables();
			break;
	}
}
*/