import { defineStore } from "pinia";
import { ref } from "vue";

import { HostingerToolsData, STORE_PERSISTENT_KEYS } from "@/types";
import { snakeToCamelObj } from "@/utils/services";

export const useGeneralStoreData = defineStore(
	"generalStoreData",
	() => {
		const toolsData = ref<HostingerToolsData>(
			// @ts-expect-error - hostinger_tools_data is a global variable
			snakeToCamelObj(hostinger_tools_data)
		);

		return {
			...toolsData.value
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.TOOLS_GENERAL_DATA_STORE }
	}
);
