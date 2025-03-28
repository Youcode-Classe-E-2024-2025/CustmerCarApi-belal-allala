<template>
  <div class="ticket-create-view">
    <h1>Créer un Nouveau Ticket</h1>
    <form @submit.prevent="createTicket" class="ticket-form">
      <div class="form-group">
        <label for="title">Titre du Ticket:</label>
        <input type="text" id="title" v-model="ticketForm.title" class="form-control" required>
        <div v-if="validationErrors.title" class="error text-danger">{{ validationErrors.title[0] }}</div> <!-- Afficher erreur de validation pour 'title' -->
      </div>
      <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" v-model="ticketForm.description" class="form-control" rows="5" required></textarea>
        <div v-if="validationErrors.description" class="error text-danger">{{ validationErrors.description[0] }}</div> <!-- Afficher erreur de validation pour 'description' -->
      </div>
      <button type="submit" class="btn btn-primary">Créer le Ticket</button>
      <div v-if="successMessage" class="success-message">
        {{ successMessage }} <router-link to="/tickets">Voir la liste des tickets</router-link> <!-- Lien vers la liste des tickets après succès -->
      </div>
      <div v-if="error" class="error-message">
        Erreur lors de la création du ticket : {{ error }} <!-- Message d'erreur général -->
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TicketCreateView', // Nom du composant
  data() {
    return {
      ticketForm: { // Objet pour stocker les données du formulaire de ticket
        title: '',
        description: '',
      },
      validationErrors: {}, // Objet pour stocker les erreurs de validation (initialement vide)
      successMessage: null, // Pour afficher un message de succès (initialement null)
      error: null,         // Pour afficher les messages d'erreur générales
    };
  },
  methods: {
    createTicket() {
      this.validationErrors = {}; // Réinitialiser les erreurs de validation
      this.error = null; // Réinitialiser l'erreur générale
      this.successMessage = null; // Réinitialiser le message de succès

      axios.post('/api/tickets', this.ticketForm) // Requête POST vers l'API Laravel pour créer un ticket
        .then(response => {
          console.log('Ticket créé avec succès:', response.data);
          this.successMessage = 'Ticket créé avec succès !'; // Afficher message de succès
          this.ticketForm = { title: '', description: '' }; // Réinitialiser le formulaire
          // Rediriger vers la vue Détails du Ticket (si vous avez configuré Vue Router)
          // this.$router.push(`/tickets/${response.data.id}`); 
        })
        .catch(error => {
          console.error('Erreur lors de la création du ticket:', error);
          if (error.response && error.response.status === 422) {
            this.validationErrors = error.response.data.errors; // Récupérer les erreurs de validation depuis la réponse 422
          } else {
            this.error = error.message || 'Erreur inconnue lors de la création du ticket.'; // Message d'erreur général
          }
        });
    },
  },
};
</script>

<style scoped>
.ticket-create-view {
  font-family: 'Arial', sans-serif;
  color: #333;
  padding: 20px;
}

h1 {
  font-size: 2.5em;
  margin-bottom: 30px;
  text-align: center;
  color: #3498db; /* Bleu */
}

.ticket-form {
  max-width: 600px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: bold;
  color: #555;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
  margin-top: 5px;
}

textarea.form-control {
  resize: vertical; /* Permettre de redimensionner verticalement la textarea */
}

.btn-primary {
  background-color: #3498db; /* Bleu */
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  font-size: 1.1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #2980b9; /* Bleu plus foncé au hover */
}

.error-message, .success-message {
  margin-top: 20px;
  padding: 15px;
  border-radius: 5px;
  text-align: center;
}

.error-message {
  background-color: #ffebee; /* Rouge très clair */
  color: #d32f2f; /* Rouge foncé */
  border: 1px solid #ef9a9a; /* Bordure rouge clair */
}

.success-message {
  background-color: #e8f5e9; /* Vert très clair */
  color: #388e3c; /* Vert foncé */
  border: 1px solid #a5d6a7; /* Bordure verte clair */
}

.text-danger {
  color: #d32f2f; /* Rouge foncé pour les erreurs de validation inline */
  font-size: 0.9em;
  margin-top: 5px;
}
</style>
