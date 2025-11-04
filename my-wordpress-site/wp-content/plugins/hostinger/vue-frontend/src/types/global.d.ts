declare global {
	const hostinger_tools_data: {
		rest_base_url: string;
		nonce: string;
		translations: { [key: string]: string };
		hplatform: string;
		home_url: string;
		site_url: string;
		plugin_url: string;
		asset_url: string;
		edit_site_url: string;
		llmstxt_file_url: string;
		llmstxt_file_user_generated: boolean;
		mcp_choice: boolean;
		ai_plugin_compatibility: boolean;
		wp_version: string;
		php_version: string;
		recommended_php_version: string;
	};
}

export {};
