//On fait un select de toutes les villes
//l'idVille en paramètre est initialisé à 0, ça sera le cas une seul fois lors du chargement de la page
//l'idVille envoyer en paramètre servira à modifier uniquement le code postal correspondant à la ville selectionnée
function initListeVille($idVille = 0) {
    fetch('http://localhost/projetSortir/public/api/ville', {
        method: "GET",
        headers: {'Accept': 'application/json'}
    })
        .then(response => response.json())
        .then(response => {
            if ($idVille === 0) {
                //lors du chargement de la page, on affiche toutes les options du select
                let options = '';
                let i = 0;
                let cP = '';
                response.map(ville => {
                    //Pour la première ville affichée dans le select on affiche son code postal
                    //Et on appelle la fonction qui recherchera les lieux correspondants à cette ville
                    if (i === 0) {
                        initListeLieux(ville.id);
                        cP = `${ville.codePostal}`
                        i = 1;
                    }

                    options += `<option value="${ville.id}">${ville.nom}</option>`
                })

                document.querySelector('#ville').innerHTML = options;
                document.querySelector('#codePostal').innerHTML = cP;
            } else {
                //On change uniquement le code postal qui correspond à la ville selectionnée
                //sans recharger la liste des options du select de la ville
                //et on appelle la fonction qui recherche les lieux correspondant
                let cP = '';
                response.map(ville => {
                    if (ville.id == $idVille) {
                        cP = `${ville.codePostal}`
                        initListeLieux(ville.id);
                    }
                })
                document.querySelector('#codePostal').innerHTML = cP;
                //On appelle la fonction qui affichera la ville selectionnée dans le modal du lieu
                selectVille($idVille);
            }

        })
        .catch(e = () => {
            alert('Erreur')
        })

}

//On fait un select des lieux en fonction de l'id de la ville envoyé en paramètre
function initListeLieux(idVille) {
    fetch('http://localhost/projetSortir/public/api/lieux/' + idVille, {
        method: "GET",
        headers: {'Accept': 'application/json'}
    })
        .then(response => response.json())
        .then(response => {
            //On initialise les variables nécessaires à l'affichage du détail du premier lieux
            //mais aussi les différentes options du select correspondant au lieu
            let options = '';
            let i = 1;
            let rue = '';
            let latitude = '';
            let longitude = '';
            response.map(lieux => {
                //on rempli le select du lieu
                options += `<option value="${lieux.id}">${lieux.nom}</option>`

                //on recupère le détail du premier lieu (celui qui sera afficher en premier dans le select)
                if (i === 1) {
                    rue = `${lieux.rue}`
                    latitude = `${lieux.latitude}`
                    longitude = `${lieux.longitude}`
                    i = 0;
                }
            })
            document.querySelector('#sortie_lieu').innerHTML = options;
            document.querySelector('#rue').innerHTML = rue;
            document.querySelector('#latitude').innerHTML = latitude;
            document.querySelector('#longitude').innerHTML = longitude;

        })
        .catch(e = () => {
            alert('Erreur')
        })
}

//On fait un select by id du lieu envoyé en paramètre
function initDetails(id) {
    fetch('http://localhost/projetSortir/public/api/lieux/details/' + id, {
        method: "GET",
        headers: {'Accept': 'application/json'}
    })
        .then(response => response.json())
        .then(response => {
            //on initialise les variables à injecter
            let rue = '';
            let latitude = '';
            let longitude = '';
            response.map(lieux => {
                rue = `${lieux.rue}`
                latitude = `${lieux.latitude}`
                longitude = `${lieux.longitude}`
            })
            //On envoie les valeurs récupérées dans les champs correspondants
            document.querySelector('#rue').innerHTML = rue;
            document.querySelector('#latitude').innerHTML = latitude;
            document.querySelector('#longitude').innerHTML = longitude;

        })
        .catch(e = () => {
            alert('Erreur')
        })
}

//On garde les variables en mémoire, cette fonction est appelée dans l'attribut onclick
// des boutons submit du lieu et de la ville

function enregistrerVariables() {
    let nom = document.querySelector('#sortie_nom').value;
    sessionStorage.setItem('nom', nom);

    let dateDebut = document.querySelector('#sortie_dateHeureDebut').value;
    sessionStorage.setItem('dateDebut', dateDebut);

    let dateFinInsc = document.querySelector('#sortie_dateLimiteInscription').value;
    sessionStorage.setItem('dateFinInsc', dateFinInsc);

    let nbInscMax = document.querySelector('#sortie_nbInscriptionsMax').value;
    sessionStorage.setItem('nbInscMax', nbInscMax);

    let duree = document.querySelector('#sortie_duree').value;
    sessionStorage.setItem('duree', duree);

    let infos = document.querySelector('#sortie_infosSortie').value;
    sessionStorage.setItem('infos', infos);

    let ville = document.querySelector('#ville').value;
    sessionStorage.setItem('ville', ville);

}

//On pré-remplie les champs du formulaire si les valeurs ont été enregistrer
function chargementVariables() {
    if (sessionStorage.getItem('nom')) {
        document.querySelector('#sortie_nom').value = sessionStorage.getItem('nom');
    }
    if (sessionStorage.getItem('dateDebut')) {
        document.querySelector('#sortie_dateHeureDebut').value = sessionStorage.getItem('dateDebut');
    }
    if (sessionStorage.getItem('dateFinInsc')) {
        document.querySelector('#sortie_dateLimiteInscription').value = sessionStorage.getItem('dateFinInsc');
    }
    if (sessionStorage.getItem('nbInscMax')) {
        document.querySelector('#sortie_nbInscriptionsMax').value = sessionStorage.getItem('nbInscMax');
    }
    if (sessionStorage.getItem('duree')) {
        document.querySelector('#sortie_duree').value = sessionStorage.getItem('duree');
    }
    if (sessionStorage.getItem('infos')) {
        document.querySelector('#sortie_infosSortie').value = sessionStorage.getItem('infos');
    }
}

//
function selectVille(idVille) {
    let selectModal = document.getElementById('ville');
    let option;

    for (let i = 0; i < selectModal.options.length; i++) {
        option = selectModal.options[i];
        if (option.value == idVille) {
            option.setAttribute('selected', true);
        } else {
            option.removeAttribute('selected');
        }
    }
}


//On vide le sessionStorage en quittant la page (retour, enregistrer, publier)
function suppSS() {
    sessionStorage.clear();
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//au chargement de la page on appelle la fonction qui va ajouter les options au select de la ville
//et on pré-rempli les champs du formulaire si ils avaient été enregistrés dans la SessionStorage
window.onload = () => {
    initListeVille();
    chargementVariables();
    //Si on vient d'ajouter un lieu, on affiche la liste des lieux et le code postal correspondant
    //à l'ajout qu'on vient de faire
    if (sessionStorage.getItem('ville')) {
        let idVille = sessionStorage.getItem('ville');
        initListeVille(idVille);
    }
}

//Une fois que le document est chargé on écoute les changements sur les select du lieu et de la ville
window.addEventListener("DOMContentLoaded", (event) => {
    const selectVille = document.querySelector('#ville');
    //a chaque changement dans le select de la ville on appelle la fonction qui va changé le code postal
    //et rechargé la liste des lieux correspondants
    selectVille.addEventListener('change', function () {
        initListeVille(this.value);

        //On change la ville pré-selectionné dans le select du modal Lieu
        var selectModal = document.getElementById('lieu_ville');
        var option;

        for (var i = 0; i < selectModal.options.length; i++) {
            option = selectModal.options[i];

            if (option.value == this.value) {
                option.setAttribute('selected', true);
            } else {
                option.removeAttribute('selected');
            }
        }
    })


    //a chaque changement du lieu dans le select on appelle la fonction qui affiche le détail correspondant
    const selectLieu = document.querySelector('#sortie_lieu');
    selectLieu.addEventListener('change', function () {
        initDetails(this.value);
    })
});