'use strict'

requestCarte() ;

async function requestCarte() {
    const response = await fetch('/back/php/request.php/carte') ;
        
    if (response.ok) {
        const data = await response.json() ;  
        displayRechercheCarte(data) ;
    }
    else {
        console.error("Erreur lors de la récupération des formulaires de recherche de la carte :", error) ;
    }  
}

function displayRechercheCarte(data) {
    // FILTRE 1 : Par année d'installation
    const selectAnnee = document.getElementById('recherche-carte-annee') ;
    selectAnnee.innerHTML = `<option value ="">Tous</options>` ;
    data.liste_annees.forEach(annee => {
        selectAnnee.innerHTML += `<option value="${annee.annee}">${annee.annee}</option>`
    })

    // FILTRE 2 : Par département
    const selectDep = document.getElementById('recherche-carte-departement') ;
    selectDep.innerHTML = `<option value ="">Tous</options>` ;
    data.liste_dep.forEach(dep => {
        selectDep.innerHTML += `<option value = "${dep.code_dep}">${dep.nom_departement} (${dep.code_dep})</option>`
    })
}