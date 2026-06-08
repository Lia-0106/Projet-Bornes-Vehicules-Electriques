'use strict'

requestPointRecharge() ;


// -------------------------------------------------------
// RECUPERATION DU POINT DE RECHARGE
// Lit l'id dans l'URL puis appelle l'API pour les détails
// -------------------------------------------------------
async function requestPointRecharge() {
    const params = new URLSearchParams(window.location.search) ;
    const id = params.get('id') ;

    const response = await fetch('/back/php/API/request.php/point-recharge?id=' + id);

    if (response.ok) {
        const data = await response.json();
        displayPointRecharge(data);
    }
    else {
        console.error("Erreur lors de la récupération des informations du point de recharge :", response.status);
    }
}


// -------------------------------------------------------
// AFFICHAGE DU POINT DE RECHARGE
// Remplit les 4 blocs : identification, localisation,
// caractéristiques, exploitation
// -------------------------------------------------------
function displayPointRecharge(data) {
    document.getElementById('id-station-title').textContent = data.id_station_itinerance ;

    // IDENTIFICATION
    document.getElementById('id-station').textContent = data.id_station_itinerance ;
    document.getElementById('amenageur').textContent = data.nom_amenageur ;
    document.getElementById('siren-amenageur').textContent = data.siren_amenageur || '—' ;
    document.getElementById('contact-amenageur').textContent = data.contact_amenageur ;

    // LOCALISATION
    document.getElementById('adresse').textContent = data.adresse_station ;
    document.getElementById('commune').textContent = data.nom_commune ;
    document.getElementById('departement').textContent = data.nom_departement ;
    document.getElementById('coordonnees').textContent = data.consolidated_longitude + ', ' + data.consolidated_latitude ;
    document.getElementById('implantation').textContent = data.implantation_station || '—' ;

    // CARACTERISTIQUES
    document.getElementById('horaires').textContent = data.horaires || '—' ;
    document.getElementById('condition-acces').textContent = data.condition_acces || '—' ;
    document.getElementById('puissance').textContent = data.puissance_nominale + ' kW' ;
    document.getElementById('types-prises').textContent = data.types_prises ;
    document.getElementById('gratuit').textContent = (data.gratuit == 1) ? 'Oui' : 'Non' || '—' ;
    if (data.gratuit == 1) {
        document.getElementById('row-tarification').classList.add('d-none') ;
        document.getElementById('row-paiement').classList.add('d-none') ;
    } else {
        document.getElementById('tarification').textContent = data.tarification || "—" ;
        document.getElementById('types-paiement').textContent = data.types_paiement || "—" ;
    }
    document.getElementById('cable-t2').textContent = (data.cable_t2_attache == 1) ? 'Oui' : 'Non' ;

    // EXPLOITATION
    document.getElementById('enseigne').textContent = data.nom_enseigne || '—' ;
    document.getElementById('operateur').textContent = data.nom_operateur || '—' ;
    document.getElementById('contact-operateur').textContent = data.contact_operateur || '—' ;
    document.getElementById('tel-operateur').textContent = data.telephone_operateur || '—' ;
    let dateFormatee = 'Inconnue' ;
        if(data.date_mise_en_service && data.date_mise_en_service.substring(0, 4) !== '0000') {
            const annee = data.date_mise_en_service.substring(0,4) ;
            const mois = data.date_mise_en_service.substring(5, 7) ;
            const jour = data.date_mise_en_service.substring(8, 10) ;
            
            dateFormatee = `${jour}/${mois}/${annee}` ;
        }
    document.getElementById('mise-en-service').textContent = dateFormatee ;

}