<template>
  <div class="login-form">
    <h2>Connexion</h2>
    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" required>
      <input v-model="password" type="password" placeholder="Mot de passe" required>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: ''
    };
  },
  methods: {
    async handleLogin() {
      try {
        const response = await axios.post('http://localhost:8000/api/login', {
          email: this.email,
          password: this.password
        });
        
        localStorage.setItem('authToken', response.data.token);
        this.$router.push('/');
      } catch (error) {
        alert('Erreur de connexion: ' + (error.response?.data?.message || error.message));
      }
    }
  }
};
</script>