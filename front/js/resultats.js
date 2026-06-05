'use strict'

// Récupère depuis l'API les résultats filtrés
async function requestResultats() {
    const amenageur = document.getElementById('recherche-amenageur').value ;
    const type_prise = document.getElementById('recherche-type-de-prise').value ;
    const code_dep   = document.getElementById('recherche-departement').value ;

    const response = await fetch(`/back/php/API/request.php/resultats?amenageur=${amenageur}&type_prise=${type_prise}&code_dep=${code_dep}`) ;

    if (response.ok) {
        const data = await response.json() ;
        displayResultats(data) ;
    }
    else {
        console.error("Erreur lors de l'affichage des points de recharge :", response.status) ;
    }
}


// Affiche les résultats dans le tableau + le badge avec le nb de résultats
function displayResultats(data) {
    const tbody = document.querySelector('.table tbody') ;
    const badge = document.querySelector('.table-badge') ;

    badge.textContent = `${data.length} point(s) de recharge` ;
    tbody.innerHTML = ''

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Aucun point de recharge ne correspond à vos critères.</td></tr>`
    }

    data.forEach(item => {
        const row = document.createElement('tr') ;

        const cellDate = document.createElement('td') ;
        let dateFormatee = '—' ;
        if(item.date_mise_en_service && item.date_mise_en_service.substring(0, 4) !== '0000') {
            const annee = item.date_mise_en_service.substring(0,4) ;
            const mois = item.date_mise_en_service.substring(5, 7) ;
            
            dateFormatee = `${mois}/${annee}` ;
        }
        cellDate.textContent = dateFormatee ;
        row.appendChild(cellDate) ;

        const cellPrise = document.createElement('td') ;
        cellPrise.textContent = item.types_prises || '—' ;
        row.appendChild(cellPrise) ;

        const cellPuissance = document.createElement('td') ;
        cellPuissance.textContent = `${item.puissance} kW` || '—' ;
        row.appendChild(cellPuissance) ;

        const cellLocalisation = document.createElement('td') ;
        cellLocalisation.textContent = `${item.adresse_station}, ${item.code_postal} ${item.nom_commune}` ;
        row.appendChild(cellLocalisation) ;

        const cellDetails = document.createElement('td') ;
        const link = document.createElement('a') ;
        link.href = `point-recharge.html?id=${item.point_id}` ;
        link.className = 'cell-link' ;
        link.innerHTML = 'Voir →' ;
        cellDetails.appendChild(link) ;
        row.appendChild(cellDetails)

        tbody.appendChild(row) ;
        
    }) ;
}

document.querySelector('.filter-btn').addEventListener('click', requestResultats) ;
requestResultats() ;