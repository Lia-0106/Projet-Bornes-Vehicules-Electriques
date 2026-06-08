'use strict'

requestStats() ;


// -------------------------------------------------------
// RECUPERATION DES STATISTIQUES
// Appel API pour obtenir toutes les statistiques requises
// -------------------------------------------------------
async function requestStats() {
    const response = await fetch('/back/php/API/request.php/stats') ;
        
    if (response.ok) {
        const data = await response.json() ;  
        displayStats(data) ;
    }
    else {
        console.error("Erreur lors de la récupération des stats :", error) ;
    }  
}


// -------------------------------------------------------
// AFFICHAGE DES STATISTIQUES
// Remplit chaque bloc stat de la page d'accueil
// -------------------------------------------------------
function displayStats(data) {
    // STAT 1 : Nb total de points
    document.getElementById('stat-total-points').textContent = data.total_points ;

    // STAT 2 : Nb de points par années
    renderChartAnnee(data.points_annee) ;

    // STAT 3 : Nb de points par dep
    renderChartDepartements(data.points_par_dep) ;

    // STAT 4 : Nb de points par dep par année
    renderChartAnneeDepGrouped(data.points_par_dep_annee) ;

    // STAT 5 : Nb aménageurs
    document.getElementById('stat-amenageurs').textContent = data.nb_amenageurs ;

    // STAT 6 : Nb de prises par type
    renderChartPrises(data.prises_par_type) ;

    // STAT 7 : Nb de stations
    document.getElementById('stat-nb-stations').textContent = data.nb_stations ;
}