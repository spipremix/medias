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
 * Interfaces des tables breves pour le compilateur
 *
 * @param array $interfaces
 * @return array
 */
function medias_declarer_tables_interfaces($interfaces) {
	$interfaces['table_des_tables']['documents']='documents';
	$interfaces['table_des_tables']['types_documents']='types_documents';

	$interfaces['exceptions_des_tables']['documents']['type_document']=array('types_documents'
	, 'titre');
	$interfaces['exceptions_des_tables']['documents']['extension_document']=array('types_documents', 'extension');
	$interfaces['exceptions_des_tables']['documents']['mime_type']=array('types_documents', 'mime_type');
	$interfaces['exceptions_des_tables']['documents']['media']=array('types_documents', 'media');
	
	$interfaces['table_date']['types_documents']='date';

	$interfaces['table_des_traitements']['FICHIER']['documents']= 'get_spip_doc(%s)';

	return $interfaces;
}


/**
 * Table principale spip_documents et spip_types_documents
 *
 * @param array $tables_principales
 * @return array
 */
function medias_declarer_tables_principales($tables_principales) {

	$spip_types_documents = array(
			"extension"	=> "varchar(10) DEFAULT '' NOT NULL",
			"titre"	=> "text DEFAULT '' NOT NULL",
			"descriptif"	=> "text DEFAULT '' NOT NULL",
			"mime_type"	=> "varchar(100) DEFAULT '' NOT NULL",
			"inclus"	=> "ENUM('non', 'image', 'embed') DEFAULT 'non'  NOT NULL",
			"upload"	=> "ENUM('oui', 'non') DEFAULT 'oui'  NOT NULL",
			"media" => "varchar(10) DEFAULT 'file' NOT NULL",
			"maj"	=> "TIMESTAMP");

	$spip_types_documents_key = array(
			"PRIMARY KEY"	=> "extension",
			"KEY inclus"	=> "inclus");

	$tables_principales['spip_types_documents']	=
		array('field' => &$spip_types_documents, 'key' => &$spip_types_documents_key);

	return $tables_principales;
}

/**
 * Table des liens documents-objets spip_documents_liens
 * @param  $tables_auxiliaires
 * @return
 */
function medias_declarer_tables_auxiliaires($tables_auxiliaires) {

	$spip_documents_liens = array(
			"id_document"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"id_objet"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"objet"	=> "VARCHAR (25) DEFAULT '' NOT NULL",
			"vu"	=> "ENUM('non', 'oui') DEFAULT 'non' NOT NULL");

	$spip_documents_liens_key = array(
			"PRIMARY KEY"		=> "id_document,id_objet,objet",
			"KEY id_document"	=> "id_document");

	$tables_auxiliaires['spip_documents_liens'] = array(
		'field' => &$spip_documents_liens,
		'key' => &$spip_documents_liens_key);

	return $tables_auxiliaires;
}

/**
 * Declarer le surnom des breves
 *
 * @param array $surnoms
 * @return array
 */
function medias_declarer_tables_objets_surnoms($surnoms) {
	$surnoms['type_document'] = "types_documents"; # hum
	$surnoms['extension'] = "types_documents"; # hum
	#$surnoms['type'] = "types_documents"; # a ajouter pour id_table_objet('type')=='extension' ?
	return $surnoms;
}

function medias_declarer_tables_objets_sql($tables){
	$tables['spip_articles']['champs_versionnes'][] = 'jointure_documents';
	$tables['spip_documents'] = array(
		'table_objet_surnoms'=>array('doc','img','emb'),
	  'type_surnoms' => array(),
		'url_voir' => 'document_edit',
		'url_edit' => 'document_edit',
		'page'=>'',
		'texte_retour' => 'icone_retour',
		'texte_objets' => 'medias:objet_documents',
		'texte_objet' => 'medias:objet_document',
		'texte_modifier' => 'medias:info_modifier_document',
		'info_aucun_objet'=> 'medias:aucun_document',
		'info_1_objet' => 'medias:un_document',
		'info_nb_objets' => 'medias:des_documents',
		'titre' => "CASE WHEN length(titre)>0 THEN titre ELSE fichier END as titre, '' AS lang",
		'date' => 'date',
		'principale'=>'oui',
		'field' => array(
			"id_document"	=> "bigint(21) NOT NULL",
			"id_vignette"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"extension"	=> "VARCHAR(10) DEFAULT '' NOT NULL",
			"titre"	=> "text DEFAULT '' NOT NULL",
			"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
			"descriptif"	=> "text DEFAULT '' NOT NULL",
			"fichier"	=> "text NOT NULL DEFAULT ''",
			"taille"	=> "integer",
			"largeur"	=> "integer",
			"hauteur"	=> "integer",
			"mode"	=> "varchar(10) DEFAULT 'document' NOT NULL",
			"distant"	=> "VARCHAR(3) DEFAULT 'non'",
			"statut" => "varchar(10) DEFAULT '0' NOT NULL",
			"credits" => "varchar(255) DEFAULT '' NOT NULL",
			"date_publication" => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
			"brise" => "tinyint DEFAULT 0",
			"maj"	=> "TIMESTAMP"
		),
		'key' => array(
			"PRIMARY KEY"	=> "id_document",
			"KEY id_vignette"	=> "id_vignette",
			"KEY mode"	=> "mode",
			"KEY extension"	=> "extension"
		),
		'join' => array(
			"id_document"=>"id_document",
			"extension"=>"extension"
		),
		'tables_jointures' => array('types_documents'),
		'rechercher_champs' => array(
			'titre' => 3, 'descriptif' => 1, 'fichier' => 1
		),
		'champs_versionnes' => array('id_vignette', 'titre', 'descriptif', 'hauteur', 'largeur', 'mode','distant'),
	);

	// jointures sur les forum pour tous les objets
	$tables[]['tables_jointures'][]= 'documents_liens';

	// recherche jointe sur les documents pour les articles et rubriques
	$tables['spip_articles']['rechercher_jointures']['document'] = array('titre' => 2, 'descriptif' => 1);
	$tables['spip_rubriques']['rechercher_jointures']['document'] = array('titre' => 2, 'descriptif' => 1);
	return $tables;
}


/**
 * Creer la table des types de document
 *
 * http://doc.spip.org/@creer_base_types_doc
 *
 * @param string $serveur
 * @return void
 */
function creer_base_types_doc($serveur='') {
	global $tables_images, $tables_sequences, $tables_documents, $tables_mime;
	include_spip('base/typedoc');
	include_spip('base/abstract_sql');

	foreach ($tables_mime as $extension => $type_mime) {
		if (isset($tables_images[$extension])) {
			$titre = $tables_images[$extension];
			$inclus='image';
		}
		else if (isset($tables_sequences[$extension])) {
			$titre = $tables_sequences[$extension];
			$inclus='embed';
		}
		else {
			$inclus='non';
			if (isset($tables_documents[$extension]))
				$titre = $tables_documents[$extension];
			else
				$titre = '';
		}

		// type de media
	  $media = "file";
	  if (preg_match(",^image/,",$type_mime) OR in_array($type_mime,array('application/illustrator')))
		  $media = "image";
	  elseif (preg_match(",^audio/,",$type_mime))
		  $media = "audio";
	  elseif (preg_match(",^video/,",$type_mime) OR in_array($type_mime,array('application/ogg','application/x-shockwave-flash','application/mp4')))
		  $media = "video";
	  
		// Init ou Re-init ==> replace pas insert
		sql_replace('spip_types_documents',
			array('mime_type' => $type_mime,
				'titre' => $titre,
				'inclus' => $inclus,
				'extension' => $extension,
				'media' => $media,
				'upload' => 'oui'
			),
			'', $serveur);
	}
}


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

function medias_upgrade($nom_meta_base_version,$version_cible){
	$current_version = 0.0;
	if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
		if (spip_version_compare($current_version,'0.1.0','<')){
			include_spip('base/create');
			maj_tables(array('spip_documents','spip_documents_liens','spip_types_documents'));
			creer_base_types_doc();
			ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
		}
		if (spip_version_compare($current_version,'0.2.0','<')){
			include_spip('base/abstract_sql');
			sql_alter("TABLE spip_documents ADD statut varchar(10) DEFAULT '0' NOT NULL");
			ecrire_meta($nom_meta_base_version,$current_version="0.2",'non');
		}
		if (spip_version_compare($current_version,'0.3.0','<')){
			include_spip('base/abstract_sql');
			// ajouter un champ
			sql_alter("TABLE spip_documents ADD date_publication datetime DEFAULT '0000-00-00 00:00:00' NOT NULL");
			// vider le cache des descriptions de tables
			$trouver_table = charger_fonction('trouver_table','base');
			$trouver_table(false);
			// ecrire la version pour ne plus passer la
			ecrire_meta($nom_meta_base_version,$current_version="0.3.0",'non');
		}
		if (spip_version_compare($current_version,'0.4.0','<')){
			// recalculer tous les statuts en tenant compte de la date de publi des articles...
			echo "Mise a jour des statuts de documents...";
			medias_check_statuts(true);
			ecrire_meta($nom_meta_base_version,$current_version="0.4.0",'non');
		}
		if (spip_version_compare($current_version,'0.5.0','<')){
			include_spip('base/abstract_sql');
			// ajouter un champ
			sql_alter("TABLE spip_documents ADD brise tinyint DEFAULT 0");
			// vider le cache des descriptions de tables
			$trouver_table = charger_fonction('trouver_table','base');
			$trouver_table(false);
			ecrire_meta($nom_meta_base_version,$current_version="0.5.0",'non');
		}
		if (spip_version_compare($current_version,'0.6.0','<')){
			include_spip('base/abstract_sql');
			sql_alter("TABLE spip_types_documents ADD media varchar(10) DEFAULT 'file' NOT NULL");
			creer_base_types_doc();
			ecrire_meta($nom_meta_base_version,$current_version="0.6.0",'non');
		}
		if (spip_version_compare($current_version,'0.7.0','<')){
			include_spip('base/abstract_sql');
			sql_alter("TABLE spip_documents ADD credits varchar(255) DEFAULT '' NOT NULL");
			ecrire_meta($nom_meta_base_version,$current_version="0.7.0",'non');
		}
		if (spip_version_compare($current_version,'0.10.0','<')){
			// Augmentation de la taille du champ fichier pour permettre les URL longues
			include_spip('base/abstract_sql');
			sql_alter("TABLE spip_documents CHANGE fichier fichier TEXT NOT NULL DEFAULT ''");
			ecrire_meta($nom_meta_base_version,$current_version="0.10.0",'non');
		}
		if (version_compare($current_version,'0.11.0','<')){
			// Passage du mode en varchar
			include_spip('base/abstract_sql');
			sql_alter("TABLE spip_documents CHANGE mode mode varchar(10) DEFAULT 'document' NOT NULL");
			ecrire_meta($nom_meta_base_version,$current_version="0.11.0",'non');
		}
		if (version_compare($current_version,'0.12.0','<')){
			// generalisation des metas documents_article et documents_rubriques
			$config = array();
			if (isset($GLOBALS['meta']['documents_article']) AND $GLOBALS['meta']['documents_article']!=='non')
				$config[] = 'spip_articles';
			if (isset($GLOBALS['meta']['documents_rubrique']) AND $GLOBALS['meta']['documents_rubrique']!=='non')
				$config[] = 'spip_rubriques';
			ecrire_meta('documents_objets',implode(',',$config));
			ecrire_meta($nom_meta_base_version,$current_version="0.12.0",'non');
		}
	}
	medias_check_statuts();
}

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


function medias_optimiser_base_disparus($flux){
	//
	// Documents
	//

	include_spip('action/editer_liens');
	// optimiser les liens de tous les documents vers des objets effaces
	$flux['data'] += objet_optimiser_liens(array('document'=>'*'),'*');
	// on ne nettoie volontairement pas automatiquement les documents orphelins
	
	return $flux;
  
}
?>
