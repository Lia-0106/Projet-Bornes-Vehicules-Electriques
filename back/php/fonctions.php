<?php

// -------------------------------------------------------
// FONCTIONS UTILITAIRES
// -------------------------------------------------------


// -------------------------------------------------------
// INLIST
// Vérifie si une valeur est présente dans une chaine
// -------------------------------------------------------
function inList($data, $valeur) {
    if (!is_array($data)) {
        $data = explode(',', $data) ;
        foreach ($data as $i => $elem) {
            $data[$i] = trim($elem) ;
        }
    }
    return in_array($valeur, $data) ;
}

// -------------------------------------------------------
// FORMAT DATE
// Formate la date : jour/mois/année
// -------------------------------------------------------
function formatDate($date) {
    if (empty($date) || substr($date, 0, 4) === '0000') {
        return '—' ;
    }
    $parties = explode('-', $date) ;
    return $parties[2] . '/' . $parties[1] . '/' . $parties[0] ;
}
