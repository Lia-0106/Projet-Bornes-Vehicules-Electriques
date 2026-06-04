'use strict'

requestCarte() ;

async function requestCarte() {
    const response = await fetch('/back/php/API/request.php/carte') ;
        
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

/// VERSION ALTERNATIVE

// 'use strict';

// // Initialisation de la carte Leaflet centrée sur la Bretagne
// var map = L.map('map').setView([48.1, -2.9], 8) ;

// //fond de carte OpenStreetMap
// L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//     attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
// }).addTo(map) ;

// requestCarte() ;
// requestMarqueurs() ;

// // récupère les données pour remplir les selects
// async function requestCarte() {
//     const response = await fetch('/back/php/request.php/carte') ;
       
//     if (response.ok) {
//         const data = await response.json() ;  
//         displayRechercheCarte(data) ;
//     }
//     else {
//         console.error("Erreur lors de la récupération des formulaires de recherche de la carte") ;
//     }  
// }

// //rempli les select année et departement

// function displayRechercheCarte(data) {
//     // FILTRE 1 : Par année d'installation
//     const selectAnnee = document.getElementById('recherche-carte-annee') ;
//     selectAnnee.innerHTML = `<option value="">Tous</option>` ;
//     data.liste_annees.forEach(annee => {
//         selectAnnee.innerHTML += `<option value="${annee.annee}">${annee.annee}</option>` ;
//     }) ;

//     // FILTRE 2 : Par département
//     const selectDep = document.getElementById('recherche-carte-departement') ;
//     selectDep.innerHTML = `<option value="">Tous</option>` ;
//     data.liste_dep.forEach(dep => {
//         selectDep.innerHTML += `<option value="${dep.code_dep}">${dep.nom_departement} (${dep.code_dep})</option>` ;
//     }) ;
// }

// // récup les points de recharge et les affiches sur la carte
// async function requestMarqueurs(annee = '', dep = '') {
//     const url = '/back/php/request.php/marqueurs?annee=' + annee + '&dep=' + dep ;
//     const response = await fetch(url) ;

//     if (response.ok) {
//         const points = await response.json() ;
//         afficherMarqueurs(points) ;
//     }
//     else {
//         console.error("Erreur lors de la récupération des marqueurs") ;
//     }
// }


// var marqueurs = [] ; // stocke les marqueurs pour pouvoir les supprimer

// function afficherMarqueurs(points) {
//     //suppr anciens marqueurs
//     marqueurs.forEach(function(m) { map.removeLayer(m) ; }) ;
//     marqueurs = [] ;

//     // Ajoute nv marqueurs
//     points.forEach(function(point) {
//         var lat = parseFloat(point.latitude) ;
//         var lng = parseFloat(point.longitude) ;

//         if (isNaN(lat) || isNaN(lng)) return ;

//         var marqueur = L.marker([lat, lng]) ;

//         //bulles popup avec infos + lien vers détail
//         var popup =
//             '<strong>' + point.nom_station + '</strong><br>' +
//             point.nom_commune + ' — ' + point.nom_departement + '<br>' +
//             'Puissance : ' + point.puissance_nominale + ' kW<br>' +
//             '<a href="point-recharge.html?id=' + point.id + '">Voir le détail</a>' ;

//         marqueur.bindPopup(popup) ;
//         marqueur.addTo(map) ;
//         marqueurs.push(marqueur) ;
//     }) ;
// }


// document.querySelector('.btn-search').addEventListener('click', function() {
//     var annee = document.getElementById('recherche-carte-annee').value ;
//     var dep   = document.getElementById('recherche-carte-departement').value ;
//     requestMarqueurs(annee, dep) ;
// }) ;