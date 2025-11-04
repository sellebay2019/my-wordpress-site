import { defineStore } from "pinia";
import { ref } from "vue";
import { toast } from "vue3-toastify";

import { generalDataRepo } from "@/repositories";
import { STORE_PERSISTENT_KEYS } from "@/types/enums";
import { SettingsData } from "@/types/models";
import { translate } from "@/utils/helpers";

export const useSettingsStore = defineStore(
	"settingsStore",
	() => {
		const settingsData = ref<SettingsData | null>(null);

		const fetchSettingsData = async () => {
			const [{ data }, err] = await generalDataRepo.getSettings();

			if (err) return;

			settingsData.value = data;
		};

		const regenerateByPassCode = async () => {
			const [{ data }, err] =
				await generalDataRepo.getRegenerateByPassCode();

			if (err) return;

			toast.success(translate("bypass_link_reset_success"));

			const tempSettingsData = settingsData.value;

			settingsData.value = {
				...data,
				currentWpVersion: tempSettingsData?.currentWpVersion || "",
				phpVersion: tempSettingsData?.phpVersion || "",
				newestWpVersion: tempSettingsData?.newestWpVersion || "",
				isEligibleWwwRedirect:
					tempSettingsData?.isEligibleWwwRedirect || false
			};
		};

		const updateSettingsData = async (
			settings: SettingsData
		): Promise<boolean> => {
			const [{ data }, err] =
				await generalDataRepo.postSettings(settings);

			if (err) {
				toast.error(translate("hostinger_tools_settings_error"));

				return false;
			}

			const tempSettingsData = settingsData.value;

			settingsData.value = {
				...data,
				currentWpVersion: tempSettingsData?.currentWpVersion || "",
				phpVersion: tempSettingsData?.phpVersion || "",
				newestWpVersion: tempSettingsData?.newestWpVersion || "",
				isEligibleWwwRedirect:
					tempSettingsData?.isEligibleWwwRedirect || false
			};

			toast.success(translate("hostinger_tools_settings_updated"));

			return true;
		};

		return {
			fetchSettingsData,
			updateSettingsData,
			regenerateByPassCode,
			settingsData
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.SETTINGS_STORE }
	}
);
