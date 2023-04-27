function confirmer(url, action) {
    let message;
    if (action === 'suspendre') {
        message = "Êtes-vous sûr de vouloir suspendre ce compte?";
    } else if (action === 'supprimer') {
        message = "Êtes-vous sûr de vouloir supprimer ?";
    } else if (action === 'annuler') {
    message = "Êtes-vous sûr de vouloir annuler cette sortie ?";
}

    let res = confirm(message);
    if (res) {
        window.location.href = url;
    }
}
