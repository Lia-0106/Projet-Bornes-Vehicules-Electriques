'use strict';

// Initialisation de la carte Leaflet centrée sur la Bretagne
var map = L.map('map').setView([48.1, -2.9], 8) ;

// Fond de carte OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map) ;

requestCarte() ;
requestMarqueurs() ;

async function requestCarte() {
    const response = await fetch('/back/php/API/request.php/carte') ;
       
    if (response.ok) {
        const data = await response.json() ;  
        displayRechercheCarte(data) ;
    }
    else {
        console.error("Erreur lors de la récupération des formulaires de recherche de la carte") ;
    }  
}

function displayRechercheCarte(data) {
    // FILTRE 1 : Par année d'installation
    const selectAnnee = document.getElementById('recherche-carte-annee') ;
    selectAnnee.innerHTML = `<option value="">Tous</option>` ;
    data.liste_annees.forEach(annee => {
        selectAnnee.innerHTML += `<option value="${annee.annee}">${annee.annee}</option>` ;
    }) ;

    // FILTRE 2 : Par département
    const selectDep = document.getElementById('recherche-carte-departement') ;
    selectDep.innerHTML = `<option value="">Tous</option>` ;
    data.liste_dep.forEach(dep => {
        selectDep.innerHTML += `<option value="${dep.code_dep}">${dep.nom_departement} (${dep.code_dep})</option>`;
    }) ;
}

async function requestMarqueurs(annee, dep) {
    const url = '/back/php/API/request.php/marqueurs?annee=' + annee + '&dep=' + dep ;
    const response = await fetch(url) ;

    if (response.ok) {
        const points = await response.json() ;
        afficherMarqueurs(points) ;
    } else {
        console.error("Erreur lors de la récupération des marqueurs") ;
    }
}

let marqueurs = [] ;

function afficherMarqueurs(points) {
    marqueurs.forEach(function(m) {
        map.removeLayer(m) ;
    }) ;

    marqueurs = [] ;

    points.forEach(function(point) {
        const marqueur = L.marker([point.latitude, point.longitude]) ;

        const popup =
            '<strong>' + point.nom_station + '</strong><br>' +
            point.nom_commune + ' — ' + point.nom_departement + '<br>' +
            'Puissance : ' + point.puissance_nominale + ' kW<br>' +
            '<a href="point-recharge.html?id=' + point.id + '">Voir le détail</a>' ;

        marqueur.bindPopup(popup) ; // Attache le pop-up au marqueur
        marqueur.addTo(map) ; // Affiche le marqueur
        marqueurs.push(marqueur) ;
    }) ;
}

document.querySelector('.btn-search').addEventListener('click', function() {
    const annee = document.getElementById('recherche-carte-annee').value ;
    const dep   = document.getElementById('recherche-carte-departement').value ;
    requestMarqueurs(annee, dep) ;
}) ;
