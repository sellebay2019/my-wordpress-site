const path = require("path");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const { VueLoaderPlugin } = require("vue-loader");
const webpack = require("webpack");

module.exports = {
	entry: `./vue-frontend/src/main.ts`,
	output: {
		path: path.resolve(__dirname, "./vue-frontend/dist/"),
		filename: "[name].js",
	},
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: "vue-loader",
				options: {
					compilerOptions: {
						isCustomElement: (tag) => tag.startsWith("hp-"),
					},
				},
			},
			{
				test: /\.ts$/,
				loader: "ts-loader",
				options: {
					appendTsSuffixTo: [/\.vue$/],
					transpileOnly: true,
					configFile: path.resolve(__dirname, 'vue-frontend/tsconfig.json')
				},
				exclude: /node_modules/,
			},
			{
				test: /\.s?[c]ss$/i,
				use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
			},
			{
				test: /\.(jpg|jpeg|png|gif|woff|woff2|eot|ttf|svg)$/i,
				use: "url-loader?limit=2048",
			},
		],
	},
	optimization: {
		minimizer: [
			new CssMinimizerPlugin(),
			new TerserPlugin({
				terserOptions: {
					compress: {
						drop_console: true,
					},
				},
			}),
		],
	},
	plugins: [
		new VueLoaderPlugin(),
		new MiniCssExtractPlugin({
			filename: "[name].css",
		}),
		// new webpack.HotModuleReplacementPlugin(),
		new webpack.DefinePlugin({
			__VUE_OPTIONS_API__: true,
			__VUE_PROD_DEVTOOLS__: false,
			__VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
		}),
	],
	resolve: {
		extensions: [".ts", ".tsx", ".js", ".vue"],
		alias: {
			"@": path.resolve(__dirname, "vue-frontend/src/"),
			"@vue-frontend": path.resolve(__dirname, "vue-frontend/"),
		},
	},
};
