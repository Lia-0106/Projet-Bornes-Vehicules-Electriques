'use strict'

requestRecherche();

async function requestRecherche() {
    const response = await fetch('/back/php/API/request.php/recherche');

    if (response.ok) {
        const data = await response.json();
        displayRecherche(data);
    }
    else {
        console.error("Erreur lors de la récupération des formulaires de recherche :", error);
    }
}

function displayRecherche(data) {
    // FILTRE 1 : Par aménageur
    const selectAmenageur = document.getElementById('recherche-amenageur');
    selectAmenageur.innerHTML = `<option value ="">Tous</options>`;
    data.liste_amenageurs.forEach(amenageur => {
        selectAmenageur.innerHTML += `<option value="${amenageur.nom}">${amenageur.nom}</option>`
    })

    // FILTRE 2 : Par type de prise
    const selectTypePrise = document.getElementById('recherche-type-de-prise');
    selectTypePrise.innerHTML = `<option value ="">Tous</options>`;
    data.liste_types_prises.forEach(prise => {
        selectTypePrise.innerHTML += `<option value = "${prise.type_prise}">${prise.type_prise}</option>`
    })

    // FILTRE 3 : Par département
    const selectDep = document.getElementById('recherche-departement');
    selectDep.innerHTML = `<option value ="">Tous</options>`;
    data.liste_dep.forEach(dep => {
        selectDep.innerHTML += `<option value = "${dep.code_dep}">${dep.nom_departement} (${dep.code_dep})</option>`
    })
}