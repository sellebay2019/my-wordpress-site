export interface ModalContent {
	title?: string;
	subtitle?: string;
	data?: Record<string, unknown>;
	onSuccess?: () => void;
	onClose?: () => void;
	[key: string]: unknown;
}

export interface ModalSettings {
	isXL?: boolean;
	isXXL?: boolean;
	isLG?: boolean;
	hasCloseButton?: boolean;
	noContentPadding?: boolean;
	noBorder?: boolean;
}
