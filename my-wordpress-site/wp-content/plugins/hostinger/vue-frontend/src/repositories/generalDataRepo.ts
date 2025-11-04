import { Header, SettingsData } from "@/types";
import http from "@/utils/services/httpService";

const URL = `${hostinger_tools_data.rest_base_url}hostinger-tools-plugin/v1`;

export const generalDataRepo = {
	getSettings: () =>
		http.get<{ data: SettingsData }>(`${URL}/get-settings`, {
			headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce }
		}),
	postSettings: (data: SettingsData) =>
		http.post<{ data: SettingsData }>(`${URL}/update-settings`, data, {
			headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce }
		}),

	getRegenerateByPassCode: () =>
		http.get<{ data: SettingsData }>(`${URL}/regenerate-bypass-code`, {
			headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce }
		})
};
