'use strict'

async function requestResultats() {
    const amenageur = document.getElementById('recherche-amenageur').value ;
    const type_prise = document.getElementById('recherche-type-de-prise').value ;
    const code_dep   = document.getElementById('recherche-departement').value ;

    const response = await fetch(`/back/php/API/request.php/resultats?amenageur=${amenageur}&type_prise=${type_prise}&code_dep=${code_dep}`) ;

    if (response.ok) {
        const data = await response.json();
        displayResultats(data);
    }
    else {
        console.error("Erreur lors de l'affichage des points de recharge :", error);
    }
}

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
        let dateFormatee = 'Inconnue' ;
        if(item.date_mise_en_service && item.date_mise_en_service.substring(0, 4) !== '0000') {
            const annee = item.date_mise_en_service.substring(0,4) ;
            const mois = item.date_mise_en_service.substring(5, 7) ;
            const jour = item.date_mise_en_service.substring(8, 10) ;
            
            dateFormatee = `${jour}/${mois}/${annee}` ;
        }
        cellDate.textContent = dateFormatee ;
        row.appendChild(cellDate) ;

        const cellPrise = document.createElement('td') ;
        cellPrise.textContent = item.types_prises || 'Inconnu' ;
        row.appendChild(cellPrise) ;

        const cellPuissance = document.createElement('td') ;
        cellPuissance.textContent = `${item.puissance} kW` || 'Inconnue' ;
        row.appendChild(cellPuissance) ;

        const cellLocalisation = document.createElement('td') ;
        cellLocalisation.textContent = `${item.adresse_station}, ${item.code_postal} ${item.nom_commune}` ;
        row.appendChild(cellLocalisation) ;

        const cellDetails = document.createElement('td') ;
        const link = document.createElement('a') ;
        link.href = `point-recharge.html?id=${item.point_id}` ;
        link.className = 'btn btn-sm btn-purple-custom' ;
        link.innerHTML = '<i class="fa fa-eye"></i>'
        cellDetails.appendChild(link) ;
        row.appendChild(cellDetails)

        tbody.appendChild(row) ;
        
    });
}

document.querySelector('.btn-search').addEventListener('click', requestResultats) ;