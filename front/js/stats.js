'use strict'

requestStats() ;


// -------------------------------------------------------
// RÉCUPÉRATION DES STATISTIQUES
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
    const listePointsAnnee = document.getElementById('stat-annee-list') ;
    listePointsAnnee.innerHTML = '' ;
    data.points_annee.forEach(annee => {
    listePointsAnnee.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${annee.annee}</span>
        <span class="fw-bold text-primary">${annee.nb_points}</span>
        </div>` ;
    }) ;

    // STAT 3 : Nb de points par dep
    const listePointsDep = document.getElementById('stat-dep-list') ;
    listePointsDep.innerHTML = '' ;
    data.points_par_dep.forEach(dep => {
    listePointsDep.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${dep.nom_departement} (${dep.code_dep})</span>
        <span class="fw-bold text-primary">${dep.nb_points}</span>
        </div>` ;
    }) ;

    // STAT 4 : Nb de points par dep par année
    const listePointsDepAnnee = document.getElementById('stat-year-dep-list') ;
    listePointsDepAnnee.innerHTML = '' ;
    data.points_par_dep_annee.forEach(depAnnee => {
    listePointsDepAnnee.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${depAnnee.annee} — ${depAnnee.nom_departement}</span>
        <span class="fw-bold text-primary">${depAnnee.nb_points}</span>
        </div>            `
    }) ;

    // STAT 5 : Nb aménageurs
    document.getElementById('stat-amenageurs').textContent = data.nb_amenageurs ;

    // STAT 6 : Nb de prises par type
    const listePrises = document.getElementById('stat-prises-list') ;
    listePrises.innerHTML = '';
    data.prises_par_type.forEach(prise => {
    listePrises.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>Prise ${prise.type_prise}</span>
        <span class="fw-bold text-success">${prise.nb_prises}</span>
        </div>`
    }) ;

    // STAT 7 : Nb de stations
    document.getElementById('stat-nb-stations').textContent = data.nb_stations ;
}