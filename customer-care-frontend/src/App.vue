<template>
  <div id="app">
    <img alt="Vue logo" src="./assets/logo.png" />
    <ticket-list />  <!-- Balise TicketList en kebab-case (ticket-list) - CORRECT -->
  </div>
</template>

<script>
import TicketList from './components/TicketList.vue'; // Importez TicketList (en CamelCase dans le script)

export default {
  name: 'App',
  components: {
    TicketList, // Déclarez TicketList (en CamelCase) dans les components
  },
};
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3d4d;
  margin-top: 60px;
}
</style>