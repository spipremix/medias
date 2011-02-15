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
#
# Fichier obsolete, a supprimer
#
#
#
#
#

if (!defined('_ECRIRE_INC_VERSION')) return;

// inclure les fonctions bases du core
include_once _DIR_RESTREINT . "inc/documents.php";

include_spip('inc/minipres');

// Erreur appelee depuis public.php (la precedente ne fonctionne plus
// depuis qu'on est sortis de spip_image.php, apparemment).
// http://doc.spip.org/@erreur_upload_trop_gros
function erreur_upload_trop_gros() {
	include_spip('inc/filtres');
	
	$msg = 		"<p>"
		.taille_en_octets($_SERVER["CONTENT_LENGTH"])
		.'<br />'
		._T('upload_limit',
		array('max' => ini_get('upload_max_filesize')))
		."</p>";
	
  echo minipres(_T('pass_erreur'),"<div class='upload_answer upload_error'>".$msg."</div>");
	exit;
}

//
// Gestion des fichiers ZIP
//
// http://doc.spip.org/@accepte_fichier_upload
/*
function accepte_fichier_upload ($f) {
	if (!preg_match(",.*__MACOSX/,", $f)
	AND !preg_match(",^\.,", basename($f))) {
		$ext = corriger_extension((strtolower(substr(strrchr($f, "."), 1))));
		return sql_countsel('spip_types_documents', "extension=" . sql_quote($ext) . " AND upload='oui'");
	}
}
*/
# callback pour le deballage d'un zip telecharge
# http://www.phpconcept.net/pclzip/man/en/?options-pclzip_cb_pre_extractfunction
// http://doc.spip.org/@callback_deballe_fichier
/*
function callback_deballe_fichier($p_event, &$p_header) {
	if (accepte_fichier_upload($p_header['filename'])) {
		$p_header['filename'] = _tmp_dir . basename($p_header['filename']);
		return 1;
	} else {
		return 0;
	}
}
*/
?>
