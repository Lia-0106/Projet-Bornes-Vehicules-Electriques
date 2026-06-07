<?php

// -------------------------------------------------------
// FONCTIONS UTILITAIRES
// -------------------------------------------------------


// -------------------------------------------------------
// INLIST
// Vérifie si une valeur est présente dans une chaine
// -------------------------------------------------------
function inList($chaine, $valeur) {
    $liste = explode(',', $chaine) ;
    return in_array($valeur, $liste) ;
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
