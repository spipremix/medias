<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

/**
 * Analyse des textes pour trouver et marquer comme vu les documents utilisés dedans
 *
 * @package SPIP\Medias\Fonctions
**/

if (!defined("_ECRIRE_INC_VERSION")) return;

// la dist ne regarde que chapo et texte, on laisse comme ca,
// mais ca permet d etendre a descriptif ou toto depuis d autres plugins
$GLOBALS['medias_liste_champs'][] = 'texte';
$GLOBALS['medias_liste_champs'][] = 'chapo';
 
/**
 * Trouver les documents utilisés dans le texte d'un objet et enregistrer cette liaison comme vue.
 *
 * La liste des champs susceptibles de contenir des documents ou images est indiquée
 * par la globale `medias_liste_champs` (un tableau).
 * 
 * Le contenu de ces champs (du moins ceux qui existent pour l'objet demandé) est récupéré et analysé.
 * La présence d'un modèle de document dans ces contenus, tel que imgXX, docXX ou embXX
 * indique que le document est utilisé et doit être lié à l'objet, avec le champ `vu=oui`
 *
 * S'il y avait des anciens liens avec vu=oui qui n'ont plus lieu d'être, ils passent à non.
 *
 * @note
 *     La fonction pourrait avoir bien moins d'arguments : seuls $champs, $id, $type ou $objet, $desc, $serveur
 *     sont nécessaires. On calcule $desc s'il est absent, et il contient toutes les infos…
 * 
 * @param array $champs
 *     Couples [champ => valeur] connus de l'objet
 * @param int $id
 *     Identifiant de l'objet
 * @param string $type
 *     Type d'objet éditorial (ex: article)
 * @param string $id_table_objet
 *     Nom de la clé primaire sur la table sql de l'objet
 * @param string $table_objet
 *     Nom de l'objet éditorial (ex: articles)
 * @param string $spip_table_objet
 *     Nom de la table sql de l'objet
 * @param array $desc
 *     Description de l'objet, si déjà calculé
 * @param string $serveur
 *     Serveur sql utilisé.
 * @return void|null
**/
function inc_marquer_doublons_doc_dist($champs, $id, $type, $id_table_objet, $table_objet, $spip_table_objet, $desc=array(), $serveur=''){

	// On conserve uniquement les champs qui modifient le calcul des doublons de documents
	// S'il n'il en a aucun, les doublons ne sont pas impactés, donc rien à faire d'autre..
	if (!$champs = array_intersect_key($champs, array_flip($GLOBALS['medias_liste_champs']))) {
		return;
	}

	if (!$desc){
		$trouver_table = charger_fonction('trouver_table', 'base');
		$desc = $trouver_table($table_objet, $serveur);
	}

	// Il faut récupérer toutes les données qui impactent les liens de documents vus
	// afin de savoir lesquels sont présents dans les textes, et pouvoir actualiser avec
	// les liens actuellement enregistrés.
	$absents = array();

	// Récupérer chaque champ impactant qui existe dans la table de l'objet et qui nous manque
	foreach ($GLOBALS['medias_liste_champs'] as $champ) {
		if (isset($desc['field'][$champ]) and !isset($champs[$champ])) {
			$absents[] = $champ;
		}
	}

	// Retrouver les textes des champs manquants
	if ($absents) {
		$row = sql_fetsel($absents, $spip_table_objet, "$id_table_objet=".sql_quote($id));
		if ($row) {
			$champs = array_merge($row, $champs);
		}
	}

	include_spip('inc/texte');
	include_spip('base/abstract_sql');
	include_spip('action/editer_liens');
	include_spip('base/objets');

	// récupérer la liste des modèles qui considèrent un document comme vu s'ils sont utilisés dans un texte
	$modeles = lister_tables_objets_sql('spip_documents');
	$modeles = $modeles['modeles'];

	// liste d'id_documents trouvés dans les textes
	$GLOBALS['doublons_documents_inclus'] = array();

	// detecter les doublons dans ces textes
	traiter_modeles(implode(" ", $champs), array('documents' => $modeles), '', '', null, array(
		'objet'         => $type,
		'id_objet'      => $id,
		$id_table_objet => $id
	));

	// tous les documents liés à l'article sont considérés non vus
	objet_qualifier_liens(array('document'=>'*'), array($type=>$id), array('vu'=>'non'));

	// ceux présents sont considérés comme vus
	if (count($GLOBALS['doublons_documents_inclus'])){
		// on repasse par une requete sur spip_documents pour verifier que les documents existent bien !
		$in_liste = sql_in('id_document', $GLOBALS['doublons_documents_inclus']);
		$res = sql_allfetsel("id_document", "spip_documents", $in_liste);
		$res = array_map('reset', $res);
		// Creer le lien s'il n'existe pas deja
		objet_associer(array('document'=>$res),array($type=>$id),array('vu'=>'oui'));
		objet_qualifier_liens(array('document'=>$res),array($type=>$id),array('vu'=>'oui'));
	}
}

