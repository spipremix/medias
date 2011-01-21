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

function medias_autoriser() {}

// On ne peut joindre un document qu'a un article qu'on a le droit d'editer
// mais il faut prevoir le cas d'une *creation* par un redacteur, qui correspond
// au hack id_article = 0-id_auteur
// http://doc.spip.org/@autoriser_joindredocument_dist
function autoriser_joindredocument_dist($faire, $type, $id, $qui, $opt){
	return
		autoriser('modifier', $type, $id, $qui, $opt)
		OR (
			$type == 'article'
			AND $id<0
			AND abs($id) == $qui['id_auteur']
			AND autoriser('ecrire', $type, $id, $qui, $opt)
		);
}

// On ne peut modifier un document que s'il est lie a un objet qu'on a le droit
// d'editer *et* qu'il n'est pas lie a un objet qu'on n'a pas le droit d'editer
// http://doc.spip.org/@autoriser_document_modifier_dist
function autoriser_document_modifier_dist($faire, $type, $id, $qui, $opt){
	static $m = array();

	if ($qui['statut'] == '0minirezo'
	AND !$qui['restreint'])
		return true;

	if (!isset($m[$id])) {
		$vu = false;
		$interdit = false;

		$s = sql_select("id_objet,objet", "spip_documents_liens", "id_document=".sql_quote($id));
		while ($t = sql_fetch($s)) {
			if (autoriser('modifier', $t['objet'], $t['id_objet'], $qui, $opt)) {
				$vu = true;
			}
			else {
				$interdit = true;
				break;
			}
		}
		$m[$id] = ($vu && !$interdit);
	}

	return $m[$id];
}


// On ne peut supprimer un document que s'il n'est lie a aucun objet
// c'est autorise pour tout auteur ayant acces a ecrire
// http://doc.spip.org/@autoriser_document_modifier_dist
function autoriser_document_supprimer_dist($faire, $type, $id, $qui, $opt){
	if (!intval($id)
		OR !$qui['id_auteur']
		OR !autoriser('ecrire','','',$qui))
		return false;
	if (sql_countsel('spip_documents_liens', 'id_document='.intval($id)))
		return false;

	return true;
}


//
// Peut-on voir un document dans _DIR_IMG ?
// Tout le monde (y compris les visiteurs non enregistres), puisque par
// defaut ce repertoire n'est pas protege ; si une extension comme
// acces_restreint a positionne creer_htaccess, on regarde
// si le document est lie a un element publie
// (TODO: a revoir car c'est dommage de sortir de l'API true/false)
//
// http://doc.spip.org/@autoriser_document_voir_dist
function autoriser_document_voir_dist($faire, $type, $id, $qui, $opt) {

	if (!isset($GLOBALS['meta']["creer_htaccess"])
	OR $GLOBALS['meta']["creer_htaccess"] != 'oui')
		return true;

	if ((!is_numeric($id)) OR $id < 0) return false;

	if (in_array($qui['statut'], array('0minirezo', '1comite')))
		return 'htaccess';

	if ($liens = sql_allfetsel('objet,id_objet', 'spip_documents_liens', 'id_document='.intval($id)))
	foreach ($liens as $l) {
		$table_sql = table_objet_sql($l['objet']);
		$id_table = id_table_objet($l['objet']);
		if (sql_countsel($table_sql, "$id_table = ". intval($l['id_objet'])
		. (in_array($l['objet'], array('article', 'rubrique', 'breve'))
			? " AND statut = 'publie'"
			: '')
		) > 0)
			return 'htaccess';
	}
	return false;
}


/**
 * Auto-association de documents a du contenu editorial qui le reference
 * par defaut true pour tous les objets
 */
function autoriser_autoassocierdocument_dist($faire, $type, $id, $qui, $opts) {
	return true;
}
