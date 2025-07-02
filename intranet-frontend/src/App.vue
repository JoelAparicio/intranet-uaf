<template>
    <router-view />
</template>

<script>

export default {
    name: 'App',
}

</script>

<style>
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

#app {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.content {
    flex: 1;
}

.footer {
    position: relative;
    bottom: 0;
    width: 100%;
}
</style>
