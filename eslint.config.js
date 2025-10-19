export default [
  {
    files: ["**/*.js", "**/*.mjs"],
    ignores: [
      "ml_service/venv/**",
      "ml_service/**/site-packages/**",
      "ml_service/**/Lib/**",
      "ml_service/**/*.js",
      "node_modules/**",
      "public/build/**",
      "vendor/**"
    ],
    languageOptions: {
      ecmaVersion: 2023,
      sourceType: "module"
    },
    rules: {
      // project default rules are fine; rely on existing .eslintrc or package.json config
    }
  }
];
