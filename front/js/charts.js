'use strict'

// --------------------------------------------
// GESTION DES GRAPHIQUES DE L'ACCUEIL DU SITE
// DOCUMENTATION : https://www.chartjs.org/
// --------------------------------------------


// --------------------------------------------
// PALETTE DE COULEURS POUR LES GRAPHIQUES
// Avoir un style cohérent
// --------------------------------------------
const CHART_COLORS = [
    '#7c5af5', '#9474ff', '#4f86f7', '#f76f4f', '#dbdbdb'
] ;

Chart.register(ChartDataLabels) ; // Plugin qui permet d'afficher les valeurs directement sur les graphiques


// --------------------------------------------------------------------------------------------
// GRAPHIQUE PIE : Points de recharge par département
// Affiche la répartition des points entre les 4 départements bretons sous forme de camembert
// --------------------------------------------------------------------------------------------
function renderChartDepartements(data) {
    const ctx = document.getElementById('chart-dep') ;

    const labels = data.map(d => `${d.nom_departement} (${d.code_dep})`) ;
    const values = data.map(d => parseInt(d.nb_points)) ;
    const colors = CHART_COLORS.slice(0, labels.length) ; // Prend autant de couleurs qu'il y a de dep

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { size: 11, family: 'Inter' },
                        color: '#a09bc0',
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { size: 11, family: 'Inter', weight: '600' },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                        const pourcentage = ((value / total) * 100).toFixed(1) ;
                        return pourcentage > 5 ? `${pourcentage}%` : '' ;
                    }
                },
                tooltip: { enabled: false },
            }
        }
    }) ;
}


// -----------------------------------------------------------------
// GRAPHIQUE DOUGHNUT : Types de prises
// Affiche la répartition des types de prises sous forme d'un donut
// -----------------------------------------------------------------
function renderChartPrises(data) {
    const ctx = document.getElementById('chart-prises') ;

    const labels = data.map(p => `Prise ${p.type_prise}`) ;
    const values = data.map(p => parseInt(p.nb_prises)) ;
    const colors = CHART_COLORS.slice(0, labels.length) ;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { size: 11, family: 'Inter' },
                        color: '#a09bc0',
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { size: 11, family: 'Inter', weight: '600' },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                        const pourcentage = ((value / total) * 100).toFixed(1) ;
                        return pourcentage > 5 ? `${pourcentage}%` : '' ;
                    }
                },
                tooltip: { enabled: false }
            }
        }
    }) ;
}


// --------------------------------------------------------------------------------------------
// GRAPHIQUE BAR : Points de recharge par année
// Affiche l'évolution du nb de points installés chaque année sous forme de barres verticales
// --------------------------------------------------------------------------------------------
function renderChartAnnee(data) {
    const ctx = document.getElementById('chart-annee') ;

    // .reverse() car données envoyées par ordre décroissant
    const labels = data.map(d => d.annee).reverse() ;
    const values = data.map(d => parseInt(d.nb_points)).reverse() ;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Points installés',
                data: values,
                backgroundColor: '#7c5af5',
                borderColor: '#9474ff',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#a09bc0',
                    font: { size: 10, family: 'Inter', weight: '600' },
                },
                tooltip: { enabled: false }
            },
            scales: {
                x: {
                    ticks: { color: '#7b7599', font: { size: 11 } },
                    grid:  { color: '#2e2a45' },
                },
                y: {
                    ticks: { color: '#7b7599', font: { size: 11 } },
                    grid:  { color: '#2e2a45' },
                    beginAtZero: true,
                }
            }
        }
    }) ;
}


// ---------------------------------------------------------------------
// GRAPHIQUE BAR GROUPE : Points par année et par département
// Affiche pour chaque année le nb de points installés dans chaque dep
// ---------------------------------------------------------------------
function renderChartAnneeDepGrouped(data) {
    const ctx = document.getElementById('chart-annee-dep') ;
    if (!ctx) return ;

    // Années uniques
    const annees = [] ;
    for (const d of data) {
        if (!annees.includes(d.annee)) {
            annees.push(d.annee) ;
        }
    }
    annees.sort() ;

    // Départements uniques
    const deps = [] ;
    for (const d of data) {
        if (!deps.includes(d.code_dep)) {
            deps.push(d.code_dep) ;
        }
    }
    deps.sort() ;

    // Dictionnaire nom/code
    const depNoms = {} ;
    for (const d of data) {
        depNoms[d.code_dep] = d.nom_departement ;
    }

    // Datasets
    const datasets = deps.map((dep, i) => {
        const valeurs = annees.map(annee => {
            const ligne = data.find(d => d.annee == annee && d.code_dep == dep) ;
            return ligne ? parseInt(ligne.nb_points) : 0 ; // Retourne 0 si pas de borne à "cette année dans ce dep"
        }) ;

        return {
            label: `${depNoms[dep]} (${dep})`,
            data: valeurs,
            backgroundColor: CHART_COLORS[i],
            borderRadius: 4,
            borderWidth: 0,
        } ;
    }) ;

    new Chart(ctx, {
        type: 'bar',
        data: { labels: annees, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { size: 11, family: 'Inter' },
                        color: '#a09bc0',
                    }
                },
                datalabels: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: {
                    ticks: { color: '#7b7599', font: { size: 11 } },
                    grid:  { color: '#2e2a45' },
                },
                y: {
                    ticks: { color: '#7b7599', font: { size: 11 } },
                    grid:  { color: '#2e2a45' },
                    beginAtZero: true,
                }
            }
        }
    }) ;
}