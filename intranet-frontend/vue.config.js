const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
    transpileDependencies: true,
    // Configuración del título de la aplicación
    chainWebpack: config => {
        config
            .plugin('html')
            .tap(args => {
                args[0].title = 'UAF Intranet'
                return args
            })
    },
    // Configuración del servidor de desarrollo
    devServer: {
        host: '0.0.0.0',
        port: 3000,
        client: {
            webSocketURL: 'ws://localhost:3000/ws'
        }
    }
})