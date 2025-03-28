<template>
  <div class="register-container">
    <h2>Créer un compte</h2>
    <form @submit.prevent="handleRegister" class="register-form">
      <div class="form-group">
        <label for="name">Nom complet</label>
        <input
          v-model="form.name"
          type="text"
          id="name"
          required
          placeholder="Votre nom"
        />
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input
          v-model="form.email"
          type="email"
          id="email"
          required
          placeholder="email@exemple.com"
        />
      </div>

      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input
          v-model="form.password"
          type="password"
          id="password"
          required
          placeholder="••••••••"
          minlength="8"
        />
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          id="password_confirmation"
          required
          placeholder="••••••••"
          minlength="8"
        />
      </div>

      <button type="submit" :disabled="loading" class="submit-btn">
        <span v-if="loading">Création en cours...</span>
        <span v-else>S'inscrire</span>
      </button>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div class="login-link">
        Déjà un compte ? 
        <a href="#" @click.prevent="goToLogin">Se connecter</a>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'RegisterView',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      loading: false,
      error: null,
      success: false
    };
  },
  methods: {
    async handleRegister() {
      // Validation simple
      if (this.form.password !== this.form.password_confirmation) {
        this.error = "Les mots de passe ne correspondent pas";
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('http://localhost:8000/api/register', this.form);
        
        // Si l'API retourne directement le token
        if (response.data.token) {
          localStorage.setItem('authToken', response.data.token);
          this.$emit('registered'); // Pour une redirection parente
          this.success = true;
        } else {
          // Rediriger vers le login après inscription
          this.goToLogin();
        }
      } catch (error) {
        this.handleRegistrationError(error);
      } finally {
        this.loading = false;
      }
    },

    handleRegistrationError(error) {
      if (error.response) {
        // Erreurs de validation Laravel
        if (error.response.data.errors) {
          const errors = error.response.data.errors;
          this.error = Object.values(errors).flat().join(', ');
        } else {
          this.error = error.response.data.message || "Erreur lors de l'inscription";
        }
      } else {
        this.error = "Problème de connexion au serveur";
      }
    },

    goToLogin() {
      this.$emit('show-login');
    }
  }
};
</script>

<style scoped>
.register-container {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: 500;
  color: #2c3e50;
}

input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #42b983;
}

.submit-btn {
  padding: 0.75rem;
  background-color: #42b983;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s;
}

.submit-btn:hover {
  background-color: #3aa876;
}

.submit-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: #e74c3c;
  padding: 0.75rem;
  background-color: #fdecea;
  border-radius: 4px;
  margin-top: 1rem;
  text-align: center;
}

.login-link {
  text-align: center;
  margin-top: 1rem;
  color: #7f8c8d;
}

.login-link a {
  color: #42b983;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>