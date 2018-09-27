<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// On déclare ici la config du core
function medias_ieconfig_metas($table) {
	$table['medias_meta']['titre'] = _T('medias:titre_documents_joints');
	$table['medias_meta']['icone'] = 'document-16.png';
	$table['medias_meta']['metas_brutes'] = 'documents_objets,documents_date';

	return $table;
}
