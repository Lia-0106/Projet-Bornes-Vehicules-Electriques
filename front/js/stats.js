'use strict'

requestStats() ;

async function requestStats() {
    const response = await fetch('/back/php/request.php/stats') ;
        
    if (response.ok) {
        const data = await response.json() ;  
        displayStats(data) ;
    }
    else {
        console.error("Erreur lors de la récupération des stats :", error) ;
    }  
}

function displayStats(data) {
    // Affichage STAT 1
    document.getElementById('stat-total-points').textContent = data.total_points ;

    // Affichage STAT 2 : liste des points par année
    const listePointsAnnee = document.getElementById('stat-annee-list') ;
    listePointsAnnee.innerHTML = '' ;
    data.points_annee.forEach(annee => {
    listePointsAnnee.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${annee.annee}</span>
        <span class="fw-bold text-primary">${annee.nb_points}</span>
        </div>` ;
    }) ;

    // Affichage STAT 3
    const listePointsDep = document.getElementById('stat-dep-list') ;
    listePointsDep.innerHTML = '' ;
    data.points_par_dep.forEach(dep => {
    listePointsDep.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${dep.nom_departement} (${dep.code_dep})</span>
        <span class="fw-bold text-primary">${dep.nb_points}</span>
        </div>` ;
    });

    // Affichage STAT 4
    const listePointsDepAnnee = document.getElementById('stat-year-dep-list') ;
    listePointsDepAnnee.innerHTML = '' ;
    data.points_par_dep_annee.forEach(depAnnee => {
    listePointsDepAnnee.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>${depAnnee.annee} — ${depAnnee.nom_departement}</span>
        <span class="fw-bold text-primary">${depAnnee.nb_points}</span>
        </div>            `
    })

    // Affichage STAT 5
    document.getElementById('stat-amenageurs').textContent = data.nb_amenageurs ;

    // Affichage STAT 6
    const listePrises = document.getElementById('stat-prises-list') ;
    listePrises.innerHTML = '';
    data.prises_par_type.forEach(prise => {
    listePrises.innerHTML += `<div class="d-flex justify-content-between border-bottom pb-1">
        <span>Prise ${prise.type_prise}</span>
        <span class="fw-bold text-success">${prise.nb_prises}</span>
        </div>`
    })
}