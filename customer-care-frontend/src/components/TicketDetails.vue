<template>
  <div class="ticket-details-view">
    <div v-if="ticket" class="ticket-container">
      <h1>Détails du Ticket #{{ ticket.id }}</h1>
      <div class="ticket-info">
        <p><strong>Titre:</strong> {{ ticket.title }}</p>
        <p><strong>Description:</strong> {{ ticket.description }}</p>
        <p><strong>Statut:</strong> <span :class="'status-' + ticket.status">{{ ticketStatusLabels[ticket.status] }}</span></p>
        <p><strong>Créé le:</strong> {{ formatDate(ticket.created_at) }}</p>
        <p><strong>Auteur (Client):</strong> {{ ticket.user.name }} ({{ ticket.user.email }})</p> 
        <!-- Afficher le nom et email de l'utilisateur associé au ticket -->
      </div>

      <h2>Réponses</h2>
      <!-- Composant ResponseList (pas encore créé) - pour afficher les réponses associées au ticket -->
      <!-- <response-list :ticket-id="ticket.id" /> --> 

      <h3>Ajouter une Réponse</h3>
      <!-- Composant ResponseForm (pas encore créé) - pour ajouter une nouvelle réponse au ticket -->
      <!-- <response-form :ticket-id="ticket.id" /> --> 
    </div>
    <div v-else-if="loading" class="loading-message">
      <p>Chargement des détails du ticket...</p> <!-- Message de chargement -->
    </div>
    <div v-else class="not-found-message">
      <p>Ticket non trouvé.</p> <!-- Message si ticket non trouvé -->
    </div>
    <div v-if="error" class="error-message">
      <p>Erreur lors de la récupération des détails du ticket : {{ error }}</p> <!-- Message d'erreur si erreur -->
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { format } from 'date-fns'; // Importer date-fns pour formater les dates

export default {
  name: 'TicketDetailsView', // Nom du composant
  props: {
    ticketId: {
      type: Number,
      required: true, // Le prop ticketId est obligatoire
    },
  },
  data() {
    return {
      ticket: null, // Objet pour stocker les détails du ticket
      loading: false, // Booléen pour indiquer l'état de chargement (initialement false)
      error: null,   // Pour stocker les messages d'erreur
      ticketStatusLabels: { // Labels pour le statut en français
        open: 'Ouvert',
        pending: 'En Attente',
        closed: 'Fermé',
      },
    };
  },
  mounted() {
    this.fetchTicketDetails(); // Appeler fetchTicketDetails au montage
  },
  methods: {
    fetchTicketDetails() {
      this.loading = true; // Indiquer que le chargement est en cours
      this.error = null; // Réinitialiser l'erreur

      axios.get(`/api/tickets/${this.ticketId}`) // URL API pour récupérer les détails d'un ticket par son ID
        .then(response => {
          this.ticket = response.data; // Stocker les détails du ticket dans la data
          this.loading = false; // Indiquer que le chargement est terminé
        })
        .catch(error => {
          console.error('Erreur lors de la récupération des détails du ticket:', error);
          this.ticket = null; // Réinitialiser le ticket en cas d'erreur
          this.loading = false; // Chargement terminé (même en cas d'erreur)
          this.error = error.message || 'Erreur inconnue'; // Stocker le message d'erreur
        });
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return format(date, 'dd/MM/yyyy HH:mm'); // Formater la date avec date-fns (ex: 28/03/2025 14:30)
    },
  },
};
</script>

<style scoped>
.ticket-details-view {
  font-family: 'Arial', sans-serif;
  color: #333;
  padding: 20px;
}

.ticket-container {
  background-color: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

h1 {
  font-size: 2.8em;
  margin-bottom: 25px;
  text-align: center;
  color: #3498db; /* Bleu */
}

.ticket-info {
  margin-bottom: 30px;
  line-height: 1.8;
}

.ticket-info strong {
  font-weight: bold;
  margin-right: 5px;
}

.ticket-status {
  display: inline-block;
  padding: 10px 15px;
  border-radius: 25px;
  font-weight: bold;
  font-size: 1em;
  color: white;
  margin-left: 10px;
}

.status-open {
  background-color: #28a745; /* Vert */
}

.status-pending {
  background-color: #ffc107; /* Jaune */
}

.status-closed {
  background-color: #6c757d; /* Gris */
}


h2, h3 {
  margin-top: 40px;
  margin-bottom: 20px;
  color: #3498db; /* Bleu */
}

.loading-message, .not-found-message, .error-message {
  text-align: center;
  margin-top: 50px;
  padding: 20px;
  border-radius: 8px;
}

.loading-message {
  background-color: #e3f2fd; /* Bleu très clair */
  color: #1e88e5; /* Bleu */
}

.not-found-message {
  background-color: #ffebee; /* Rouge très clair */
  color: #d32f2f; /* Rouge foncé */
}

.error-message {
  background-color: #ffebee; /* Rouge très clair */
  color: #d32f2f; /* Rouge foncé */
  border: 1px solid #ef9a9a; /* Bordure rouge clair */
}
</style>