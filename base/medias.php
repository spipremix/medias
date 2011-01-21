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
	$interfaces['exceptions_des_tables']['documents']['mime_type']=array('types_documents'
	, 'mime_type');
	$interfaces['table_titre']['documents']= "titre, fichier AS surnom, '' AS lang";
	$interfaces['table_date']['documents']='date';
	$interfaces['table_date']['types_documents']='date';

	// TODO : dynamiser en fonction de la configuration
	$interfaces['tables_jointures']['spip_articles'][]= 'documents_liens';
	$interfaces['tables_jointures']['spip_documents'][]= 'documents_liens';
	$interfaces['tables_jointures']['spip_documents'][]= 'types_documents';

	$interfaces['tables_jointures']['spip_rubriques'][]= 'documents_liens';

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

	$spip_documents = array(
			"id_document"	=> "bigint(21) NOT NULL",
			"id_vignette"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"extension"	=> "VARCHAR(10) DEFAULT '' NOT NULL",
			"titre"	=> "text DEFAULT '' NOT NULL",
			"date"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
			"descriptif"	=> "text DEFAULT '' NOT NULL",
			"fichier"	=> "varchar(255) DEFAULT '' NOT NULL",
			"taille"	=> "integer",
			"largeur"	=> "integer",
			"hauteur"	=> "integer",
			"mode"	=> "ENUM('vignette', 'image', 'document') DEFAULT 'document' NOT NULL",
			"distant"	=> "VARCHAR(3) DEFAULT 'non'",
			"maj"	=> "TIMESTAMP");

	$spip_documents_key = array(
			"PRIMARY KEY"	=> "id_document",
			"KEY id_vignette"	=> "id_vignette",
			"KEY mode"	=> "mode",
			"KEY extension"	=> "extension");
	$spip_documents_join = array(
			"id_document"=>"id_document",
			"extension"=>"extension");

	$spip_types_documents = array(
			"extension"	=> "varchar(10) DEFAULT '' NOT NULL",
			"titre"	=> "text DEFAULT '' NOT NULL",
			"descriptif"	=> "text DEFAULT '' NOT NULL",
			"mime_type"	=> "varchar(100) DEFAULT '' NOT NULL",
			"inclus"	=> "ENUM('non', 'image', 'embed') DEFAULT 'non'  NOT NULL",
			"upload"	=> "ENUM('oui', 'non') DEFAULT 'oui'  NOT NULL",
			"maj"	=> "TIMESTAMP");

	$spip_types_documents_key = array(
			"PRIMARY KEY"	=> "extension",
			"KEY inclus"	=> "inclus");

	$tables_principales['spip_documents'] =
		array('field' => &$spip_documents,  'key' => &$spip_documents_key, 'join' => &$spip_documents_join);
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
	$surnoms['doc'] = "documents";
	$surnoms['img'] = "documents";
	$surnoms['emb'] = "documents";
	$surnoms['type_document'] = "types_documents"; # hum
	$surnoms['extension'] = "types_documents"; # hum
	#$surnoms['type'] = "types_documents"; # a ajouter pour id_table_objet('type')=='extension' ?
	return $surnoms;
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
		// Init ou Re-init ==> replace pas insert
		sql_replace('spip_types_documents',
			array('mime_type' => $type_mime,
				'titre' => $titre,
				'inclus' => $inclus,
				'extension' => $extension,
				'upload' => 'oui'
			),
			'', $serveur);
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
			ecrire_meta($nom_meta_base_version,$current_version="0.1",'non');
		}
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
