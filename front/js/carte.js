'use strict' ;

// -------------------------------------------------------
// INITIALISATION DE LA CARTE
// Carte Leaflet centrée sur la Bretagne
// Tableau marqueurs : stocke les marqueurs actifs
// -------------------------------------------------------
var map      = L.map('map').setView([48.1, -2.9], 8) ;
var marqueurs = [] ;

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map) ;

requestCarte() ;


// -------------------------------------------------------
// FILTRES DE RECHERCHE
// Récupère les listes depuis l'API pour remplir les selects
// -------------------------------------------------------
async function requestCarte() {
    const response = await fetch('/back/php/API/request.php/carte') ;

    if (response.ok) {
        const data = await response.json() ;
        displayRechercheCarte(data) ;
    }
    else {
        console.error("Erreur lors de la récupération des filtres de la carte") ;
    }
}


// -------------------------------------------------------
// AFFICHAGE DES FILTRES
// Remplit les selects année et département
// -------------------------------------------------------
function displayRechercheCarte(data) {
    // FILTRE 1 : Par année d'installation
    const selectAnnee = document.getElementById('recherche-carte-annee') ;
    selectAnnee.innerHTML = `<option value="">-- Tous --</option>` ;
    data.liste_annees.forEach(annee => {
        selectAnnee.innerHTML += `<option value="${annee.annee}">${annee.annee}</option>` ;
    }) ;

    // FILTRE 2 : Par département
    const selectDep = document.getElementById('recherche-carte-departement') ;
    selectDep.innerHTML = `<option value="">-- Tous --</option>` ;
    data.liste_dep.forEach(dep => {
        selectDep.innerHTML += `<option value="${dep.code_dep}">${dep.nom_departement} (${dep.code_dep})</option>` ;
    }) ;
}


// -------------------------------------------------------
// RECUPERATION DES MARQUEURS
// Appel API avec les filtres sélectionnés
// Paramètres optionnels : annee et dep (vides = tous)
// -------------------------------------------------------
async function requestMarqueurs(annee = '', dep = '') {
    const url = '/back/php/API/request.php/marqueurs?annee=' + annee + '&dep=' + dep ;
    const response = await fetch(url) ;

    if (response.ok) {
        const points = await response.json() ;
        afficherMarqueurs(points) ;
    }
    else {
        console.error("Erreur lors de la récupération des marqueurs") ;
    }
}


// -------------------------------------------------------
// AFFICHAGE DES MARQUEURS
// Supprime les anciens marqueurs + place les nouveaux avec leur pop-up
// -------------------------------------------------------
function afficherMarqueurs(points) {
    // Supprime les anciens marqueurs avant d'afficher les nouveaux
    marqueurs.forEach(function(m) { map.removeLayer(m) ; }) ;
    marqueurs = [] ;

    points.forEach(function(point) {
        var lat = parseFloat(point.latitude) ;
        var lng = parseFloat(point.longitude) ;

        if (isNaN(lat) || isNaN(lng)) return ;

        var marqueur = L.marker([lat, lng]) ;

        // Popup : infos de la station + lien vers la page détail
        var popup =
            '<strong>' + point.nom_station + '</strong><br>' +
            point.nom_commune + ' — ' + point.nom_departement + '<br>' +
            'Puissance : ' + point.puissance_nominale + ' kW<br>' +
            '<a href="point-recharge.html?id=' + point.id + '">Voir le détail</a>' ;

        marqueur.bindPopup(popup) ;
        marqueur.addTo(map) ;
        marqueurs.push(marqueur) ;
    }) ;
}


// -------------------------------------------------------
// DECLENCHEMENT DE LA RECHERCHE
// Récupère les valeurs des filtres au clic sur le bouton
// -------------------------------------------------------
document.querySelector('.filter-btn').addEventListener('click', function() {
    var annee = document.getElementById('recherche-carte-annee').value ;
    var dep   = document.getElementById('recherche-carte-departement').value ;
    requestMarqueurs(annee, dep) ;
}) ;
