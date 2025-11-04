import { IconUnion } from "@/types/enums/iconModels";

export type SectionItem = {
	id: string;
	title: string;
	description: string;
	isVisible?: boolean;
	toggleValue?: boolean;
	sideButton?: {
		text: string;
		onClick: () => void;
	};
	sideButtons?: Array<{
		id: string;
		text: string;
		to?: string;
		isDisabled?: boolean;
		variant?: "text" | "outline" | "contain";
		icon?: IconUnion;
		onClick?: () => void;
	}>;
	copyLink?: string;
	learnMoreLink?: string;
};

export type SectionHeaderToggle = {
	value: boolean;
	onToggle: (value: boolean) => void;
};

export const SECTION_ID = {
	MAINTENANCE_MODE: "maintenance-mode",
	BYPASS_LINK: "bypass-link",
	DISABLE_XML_RPC: "disable-xml-rpc",
	DISABLE_AUTHENTICATION_PASSWORD: "disable-authentication-password",
	FORCE_HTTPS: "force-https",
	FORCE_WWW: "force-www",
	ENABLE_LLMS_TXT: "enable-llms-txt",
	OPTIN_MCP: "optin-mcp",
	SWITCH_MCP_CHOICE: "switch-mcp-choice"
} as const;
