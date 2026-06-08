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

Chart.register(ChartDataLabels) ;


// --------------------------------------------------------------------------------------------
// GRAPHIQUE PIE : Points de recharge par département
// Affiche la répartition des points entre les 4 départements bretons sous forme de camembert
// --------------------------------------------------------------------------------------------
function renderChartDepartements(data) {
    const ctx = document.getElementById('chart-dep') ;
    if (!ctx) return ;

    const labels = data.map(d => `${d.nom_departement} (${d.code_dep})`) ;
    const values = data.map(d => parseInt(d.nb_points)) ;
    const colors = CHART_COLORS.slice(0, labels.length) ;

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
                tooltip: {
                    backgroundColor: '#1e1b2e',
                    borderColor: '#2e2a45',
                    borderWidth: 1,
                    titleColor: '#ede9ff',
                    bodyColor: '#a09bc0',
                    padding: 10,
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                            const pct   = ((ctx.parsed / total) * 100).toFixed(1) ;
                            return ` ${ctx.label} : ${ctx.parsed.toLocaleString('fr-FR')} (${pct}%)` ;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { size: 11, family: 'Inter', weight: '600' },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                        const pct = ((value / total) * 100).toFixed(1) ;
                        return pct > 5 ? `${pct}%` : '' ;
                    }
                }
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
    if (!ctx) return ;

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
                tooltip: {
                    backgroundColor: '#1e1b2e',
                    borderColor: '#2e2a45',
                    borderWidth: 1,
                    titleColor: '#ede9ff',
                    bodyColor: '#a09bc0',
                    padding: 10,
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                            const pct   = ((ctx.parsed / total) * 100).toFixed(1) ;
                            return ` ${ctx.label} : ${ctx.parsed.toLocaleString('fr-FR')} (${pct}%)` ;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { size: 11, family: 'Inter', weight: '600' },
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) ;
                        const pct = ((value / total) * 100).toFixed(1) ;
                        return pct > 5 ? `${pct}%` : '' ;
                    }
                }
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
    if (!ctx) return ;

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
                tooltip: {
                    backgroundColor: '#1e1b2e',
                    borderColor: '#2e2a45',
                    borderWidth: 1,
                    titleColor: '#ede9ff',
                    bodyColor: '#a09bc0',
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y.toLocaleString('fr-FR')} points`
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#a09bc0',
                    font: { size: 10, family: 'Inter', weight: '600' },
                    formatter: value => value.toLocaleString('fr-FR'),
                }
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

    const annees = [...new Set(data.map(d => d.annee))].sort() ;
    const deps   = [...new Set(data.map(d => d.code_dep))].sort() ;
    const depNoms = {} ;
    data.forEach(d => depNoms[d.code_dep] = d.nom_departement) ;

    const datasets = deps.map((dep, i) => ({
        label: `${depNoms[dep]} (${dep})`,
        data: annees.map(a => {
            const found = data.find(d => d.annee == a && d.code_dep == dep) ;
            return found ? parseInt(found.nb_points) : 0 ;
        }),
        backgroundColor: CHART_COLORS[i % CHART_COLORS.length],
        borderRadius: 4,
        borderWidth: 0,
    })) ;

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
                tooltip: {
                    backgroundColor: '#1e1b2e',
                    borderColor: '#2e2a45',
                    borderWidth: 1,
                    titleColor: '#ede9ff',
                    bodyColor: '#a09bc0',
                    padding: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label} : ${ctx.parsed.y.toLocaleString('fr-FR')} points`
                    }
                },
                datalabels: { display: false } 
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