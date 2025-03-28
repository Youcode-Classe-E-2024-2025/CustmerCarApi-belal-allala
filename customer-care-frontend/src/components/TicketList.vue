<template>
  <div class="ticket-list">
    <h1>Liste des Tickets</h1>
    
    <!-- Afficher le composant de login si non authentifié -->
    <div v-if="showLoginForm" class="login-container">
      <h2>Connexion requise</h2>
      <input v-model="email" type="email" placeholder="Email">
      <input v-model="password" type="password" placeholder="Mot de passe">
      <button @click="handleLogin">Se connecter</button>
      <p v-if="loginError" class="error">{{ loginError }}</p>
    </div>
    
    <!-- Afficher le contenu normal si authentifié -->
    <template v-else>
      <div v-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchTickets">Réessayer</button>
      </div>
      
      <div v-else-if="loading" class="loading">
        <p>Chargement...</p>
      </div>
      
      <ul v-else-if="tickets.length > 0">
        <li v-for="ticket in tickets" :key="ticket.id">
          {{ ticket.title }} - {{ ticket.status }}
        </li>
      </ul>
      
      <div v-else class="empty">
        <p>Aucun ticket disponible</p>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TicketList',
  data() {
    return {
      tickets: [],
      loading: false,
      error: null,
      showLoginForm: false,
      email: '',
      password: '',
      loginError: null
    }
  },
  mounted() {
    this.checkAuth();
  },
  methods: {
    checkAuth() {
      const token = localStorage.getItem('authToken');
      if (!token) {
        this.showLoginForm = true;
      } else {
        this.fetchTickets();
      }
    },
    
    async handleLogin() {
      try {
        const response = await axios.post('http://localhost:8000/api/login', {
          email: this.email,
          password: this.password
        });
        
        localStorage.setItem('authToken', response.data.token);
        this.showLoginForm = false;
        this.loginError = null;
        this.fetchTickets();
      } catch (error) {
        this.loginError = error.response?.data?.message || 'Échec de la connexion';
      }
    },
    
    async fetchTickets() {
      this.loading = true;
      this.error = null;
      
      try {
        const token = localStorage.getItem('authToken');
        const response = await axios.get('http://localhost:8000/api/tickets', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        
        this.tickets = response.data.data || [];
      } catch (error) {
        if (error.response?.status === 401) {
          localStorage.removeItem('authToken');
          this.showLoginForm = true;
          this.error = 'Session expirée, veuillez vous reconnecter';
        } else {
          this.error = error.message;
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  max-width: 300px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

input {
  display: block;
  width: 100%;
  margin: 10px 0;
  padding: 8px;
}

button {
  padding: 8px 16px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.error {
  color: red;
  margin-top: 10px;
}
</style>