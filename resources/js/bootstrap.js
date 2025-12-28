import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configuration d'Echo (recommandée pour Laravel)
import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: true, // Toujours true en production
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
});

// Gestion sécurisée de l'ID utilisateur
function getUserId() {
    const meta = document.querySelector('meta[name="user-id"]');
    return meta ? meta.content : null;
}

const userId = getUserId();

// Écoute des canaux seulement si utilisateur connecté
if (userId) {
    // Canal privé (notez le préfixe 'private-' pour la cohérence)
    window.Echo.private(`private-user.${userId}`)
        .listen('.attribution.removed', (data) => {
            console.log('Attribution retirée:', data);
            // Vous pouvez appeler votre fonction showToast() ici
        })
        .listen('.panne.treated', (data) => {
            console.log('Panne traitée:', data);
            // Gestion des notifications pour les pannes traitées
        })
        .notification((notification) => {
            console.log('Nouvelle notification:', notification);
            // Gestion des notifications Laravel natives si vous les utilisez
        });

    // Pour le débogage en développement
    if (process.env.NODE_ENV === 'development') {
        window.Echo.private(`private-user.${userId}`).listenForWhisper('.event', (e) => {
            console.log('Whisper event:', e);
        });
    }
}

// Gestion des erreurs de connexion
window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('Erreur de connexion Pusher:', err);
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('Changement d\'état:', states);
});


window.Echo.private(`App.Models.User.${userId}`)
    .notification(() => {
        updateNotificationBadge();
    });
