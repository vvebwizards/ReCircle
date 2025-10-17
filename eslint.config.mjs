// Flat config for ESLint (ESM)
import js from '@eslint/js';
import globals from 'globals';

export default [
  // Ignore non-source files and vendor assets
  {
    ignores: [
      'vendor/**',
      'node_modules/**',
      'public/**',
      'storage/**',
      'bootstrap/**',
      'temp/**',
      'resources/images/**',
      'resources/css/**',
      'database/**',
      'tests/**',
    ],
  },
  js.configs.recommended,
  {
    files: [
      'resources/js/**/*.js',
    ],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        appRoutes: 'readonly',
        QRCode: 'readonly',
        faceapi: 'readonly',
        showPreFacialAlertModal: 'readonly',
        showAccountLockedMessage: 'readonly',
      },
    },
    rules: {
  'no-empty': ['error', { allowEmptyCatch: true }],
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_', caughtErrors: 'none' }],
      'no-console': 'off',
      eqeqeq: ['error', 'smart'],
      'prefer-const': 'warn',
    },
  },
];
