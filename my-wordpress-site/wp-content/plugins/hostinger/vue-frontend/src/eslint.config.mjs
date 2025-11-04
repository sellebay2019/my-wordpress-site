import js from "@eslint/js";
import typescript from "@typescript-eslint/eslint-plugin";
import typescriptParser from "@typescript-eslint/parser";
import vueTypeScript from "@vue/eslint-config-typescript";
import importPlugin from "eslint-plugin-import";
import modulesNewlines from "eslint-plugin-modules-newlines";
import simpleImportSort from "eslint-plugin-simple-import-sort";
import unusedImports from "eslint-plugin-unused-imports";
import vue from "eslint-plugin-vue";
import vueParser from "vue-eslint-parser";

export default [
	js.configs.recommended,
	...vue.configs["flat/recommended"],
	...vueTypeScript(),
	{
		files: ["webpack.config.js"],
		languageOptions: {
			globals: {
				require: "readonly",
				module: "readonly",
				__dirname: "readonly",
				process: "readonly"
			}
		},
		rules: {
			"@typescript-eslint/no-require-imports": "off",
			"no-undef": "off"
		}
	},
	{
		files: ["src/**/*.{js,jsx,ts,tsx,vue}"],
		ignores: ["types/**/*", "src/styles/**/*"],
		languageOptions: {
			parser: vueParser,
			parserOptions: {
				parser: typescriptParser,
				ecmaVersion: "latest",
				sourceType: "module",
			},
			globals: {
				browser: true,
				node: true,
				es6: true,
			},
		},
		plugins: {
			"@typescript-eslint": typescript,
			vue,
			import: importPlugin,
			"modules-newlines": modulesNewlines,
			"unused-imports": unusedImports,
			"simple-import-sort": simpleImportSort,
		},
		rules: {
			"no-console": "off",
			"no-debugger": "off",
			"comma-dangle": ["error", "never"],
			"vue/no-multiple-template-root": "off",
			"vue/require-default-prop": "off",
			"vue/no-deprecated-slot-attribute": "off",
			"vue/no-v-html": "off",
			"vue/require-explicit-emits": "error",
			"vue/prop-name-casing": "off",
			"vue/component-definition-name-casing": ["error", "PascalCase"],
			"vue/max-attributes-per-line": [
				"error",
				{
					singleline: {
						max: 7,
					},
					multiline: {
						max: 1,
					},
				},
			],
			"vue/html-self-closing": [
				"error",
				{
					html: { normal: "always", void: "always", component: "always" },
				},
			],
			"vue/component-name-in-template-casing": [
				"error",
				"PascalCase",
				{
					ignores: ["/^hp-/"],
					registeredComponentsOnly: true,
					globals: [],
				},
			],
			"vue/html-indent": "off",
			"vue/singleline-html-element-content-newline": "off",
			"vue/valid-v-for": "error",

			"vue/component-options-name-casing": ["error", "PascalCase"],
			"vue/custom-event-name-casing": ["error", "kebab-case"],
			"vue/define-macros-order": ["error", {
				order: ['defineProps', 'defineEmits'],
			}],
			"vue/html-comment-content-spacing": ["error", "always"],
			"vue/no-unused-refs": "error",
			"vue/padding-line-between-blocks": ["error", "always"],
			"vue/prefer-separate-static-class": "error",
			"arrow-parens": ["error", "always"],
			"no-nested-ternary": "error",
			"vue/attribute-hyphenation": [
				"off",
				"error",
				"never",
				{
					ignore: ["custom-prop"],
				},
			],
			"no-multiple-empty-lines": ["error", { max: 1, maxEOF: 1 }],
			"no-trailing-spaces": ["error"],
			"quote-props": ["error", "as-needed"],
			semi: ["error", "always"],
			"prefer-const": "error",
			"no-const-assign": "error",
			"no-array-constructor": "error",
			"no-new-object": "error",
			"newline-before-return": ["error"],
			"simple-import-sort/imports": [
				"error",
				{
					groups: [
						[
							"^(assert|buffer|child_process|cluster|console|constants|crypto|dgram|dns|domain|events|fs|http|https|module|net|os|path|punycode|querystring|readline|repl|stream|string_decoder|sys|timers|tls|tty|url|util|vm|zlib|freelist|v8|process|async_hooks|http2|perf_hooks)(/.*|$)",
						],
						["^@?\\w"],
						["^(@|@company|@ui|components|utils|config|vendored-lib)(/.*|$)"],
						["^\\u0000"],
						["^\\.\\.(?!/?$)", "^\\.\\./?$"],
						["^\\./(?=.*/)(?!/?$)", "^\\.(?!/?$)", "^\\./?$"],
					],
				},
			],
			"simple-import-sort/exports": "error",
			"import/extensions": [
				"error",
				"ignorePackages",
				{
					"": "never",
					js: "never",
					ts: "never",
					vue: "ignorePackages",
				},
			],
			"@typescript-eslint/no-unused-vars": "off",
			"no-unused-vars": "off",
			"unused-imports/no-unused-imports": "error",
			"unused-imports/no-unused-vars": [
				"error",
				{
					vars: "all",
					varsIgnorePattern: "^_",
					args: "after-used",
					argsIgnorePattern: "^_",
				},
			],
			"@typescript-eslint/no-explicit-any": "warn",
			"camelcase": ["error", {
				properties: 'always',
				allow: ['^[A-Z][a-zA-Z0-9]*$', '^[A-Z_][A-Z0-9_]*$', '^hostinger_tools_data$', '^hst_affiliate_data$'],
				ignoreDestructuring: false,
				ignoreImports: true,
				ignoreGlobals: true
			}],
			"func-style": "error",
			"wrap-iife": "error",
			"no-loop-func": "error",
			"prefer-rest-params": "error",
			"no-new-func": "error",
			"no-duplicate-imports": "error",
			"prefer-promise-reject-errors": "error",
			"no-param-reassign": [
				"error",
				{
					props: false,
				},
			],
			"prefer-spread": "error",
			"vue/order-in-components": "off",
			"vue/multi-word-component-names": "off",
			"arrow-spacing": "error",
			"prefer-arrow-callback": "error",
			"arrow-body-style": ["error", "as-needed"],
			"lines-around-comment": [
				"error",
				{
					beforeBlockComment: true,
					allowBlockStart: true,
					allowClassStart: true,
					allowObjectStart: true,
					allowArrayStart: true,
				},
			],
			"padding-line-between-statements": [
				"error",
				{ blankLine: "always", prev: "import", next: "*" },
				{ blankLine: "any", prev: "import", next: "import" },
				{ blankLine: "always", prev: "*", next: ["const", "let", "var"] },
				{
					blankLine: "any",
					prev: ["const", "let", "var"],
					next: ["const", "let", "var"],
				},
			],
		},
	},
	{
		files: ["**/*.vue"],
		rules: {
			"vue/multi-word-component-names": "off"
		}
	}
];
