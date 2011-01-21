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

// inclure les fonctions bases du core
include_once _DIR_RESTREINT . "inc/documents.php";

include_spip('inc/minipres');


// Erreurs d'upload
// renvoie false si pas d'erreur
// et true si erreur = pas de fichier
// pour les autres erreurs affiche le message d'erreur et meurt
// http://doc.spip.org/@check_upload_error
function check_upload_error($error, $msg='') {
	global $spip_lang_right;

	if (!$error) return false;

	spip_log("Erreur upload $error -- cf. http://php.net/manual/fr/features.file-upload.errors.php");

	switch ($error) {
			
		case 4: /* UPLOAD_ERR_NO_FILE */
			return true;

		# on peut affiner les differents messages d'erreur
		case 1: /* UPLOAD_ERR_INI_SIZE */
			$msg = _T('upload_limit',
			array('max' => ini_get('upload_max_filesize')));
			break;
		case 2: /* UPLOAD_ERR_FORM_SIZE */
			$msg = _T('upload_limit',
			array('max' => ini_get('upload_max_filesize')));
			break;
		case 3: /* UPLOAD_ERR_PARTIAL  */
			$msg = _T('upload_limit',
			array('max' => ini_get('upload_max_filesize')));
			break;
		
		default: /* autre */
			if (!$msg)
			$msg = _T('pass_erreur').' '. $error
			. '<br />' . propre("[->http://php.net/manual/fr/features.file-upload.errors.php]");
			break;
	}

	spip_log ("erreur upload $error");

  	if(_request("iframe")=="iframe") {
	  echo "<div class='upload_answer upload_error'>$msg</div>";
	  exit;
	}
  
	echo minipres($msg,
		      "<div style='text-align: $spip_lang_right'><a href='"  . rawurldecode($GLOBALS['redirect']) . "'><button type='button'>" . _T('ecrire:bouton_suivant') . "</button></a></div>");
	exit;
}

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
function accepte_fichier_upload ($f) {
	if (!preg_match(",.*__MACOSX/,", $f)
	AND !preg_match(",^\.,", basename($f))) {
		$ext = corriger_extension((strtolower(substr(strrchr($f, "."), 1))));
		return sql_countsel('spip_types_documents', "extension=" . sql_quote($ext) . " AND upload='oui'");
	}
}

# callback pour le deballage d'un zip telecharge
# http://www.phpconcept.net/pclzip/man/en/?options-pclzip_cb_pre_extractfunction
// http://doc.spip.org/@callback_deballe_fichier
function callback_deballe_fichier($p_event, &$p_header) {
	if (accepte_fichier_upload($p_header['filename'])) {
		$p_header['filename'] = _tmp_dir . basename($p_header['filename']);
		return 1;
	} else {
		return 0;
	}
}

?>
