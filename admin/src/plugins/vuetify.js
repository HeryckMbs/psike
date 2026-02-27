import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { mdi } from 'vuetify/iconsets/mdi'

export default createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
    sets: {
      mdi,
    },
  },
  theme: {
    defaultTheme: 'dark',
    themes: {
      dark: {
        dark: true,
        colors: {
          // Cores do tema do site
          primary: '#00ff88', // Verde neon (accent primary)
          secondary: '#00cc6a', // Verde neon secundário
          accent: '#00ff88', // Verde neon
          error: '#FF5252',
          info: '#2196F3',
          success: '#00ff88', // Verde neon para sucesso
          warning: '#FFC107',
          // Backgrounds pretos do site
          background: '#0f0f0f', // Preto principal
          surface: '#1a1a1a', // Cinza escuro
          'surface-variant': '#252525', // Cinza médio
          // Textos
          'on-background': '#ffffff', // Texto primário
          'on-surface': '#ffffff', // Texto em superfícies
          'on-primary': '#0f0f0f', // Texto sobre verde neon
          'on-secondary': '#0f0f0f',
          'on-accent': '#0f0f0f',
        },
      },
      light: {
        dark: false,
        colors: {
          primary: '#1976d2',
          secondary: '#424242',
          accent: '#82B1FF',
          error: '#FF5252',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FFC107',
          background: '#FFFFFF',
          surface: '#FFFFFF',
        },
      },
    },
  },
})
