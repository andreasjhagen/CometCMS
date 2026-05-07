import js from '@eslint/js'
import { defineConfig, globalIgnores } from 'eslint/config'
import globals from 'globals'
import vue from 'eslint-plugin-vue'

export default defineConfig([
  globalIgnores([
    'dist/**',
    'coverage/**',
  ]),

  js.configs.recommended,
  ...vue.configs['flat/essential'],

  {
    files: ['src/**/*.{js,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: globals.browser,
    },
    rules: {
      'no-empty': 'warn',
      'no-unused-vars': ['warn', {
        argsIgnorePattern: '^_',
        caughtErrorsIgnorePattern: '^_',
        ignoreRestSiblings: true,
        varsIgnorePattern: '^_',
      }],
      'no-useless-assignment': 'warn',
      'vue/no-mutating-props': ['error', {
        shallowOnly: true,
      }],
      'vue/require-toggle-inside-transition': 'off',
    },
  },
  {
    files: ['*.config.js', 'vite.config.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: globals.node,
    },
  },
])
