<script lang="ts" setup>
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";

import Button from "@/components/Button/Button.vue";
import SectionCard from "@/components/HostingerTools/SectionCard.vue";
import ToolVersionCard from "@/components/HostingerTools/ToolVersionCard.vue";
import Icon from "@/components/Icon/Icon.vue";
import { useModal } from "@/composables";
import { useGeneralStoreData, useSettingsStore } from "@/stores";
import {
	Header,
	ModalName,
	SectionItem,
	SettingsData,
	ToggleableSettingsData
} from "@/types";
import { SECTION_ID } from "@/types/models/components/sectionCardModels";
import {
	getAssetSource,
	getBaseUrl,
	isNewerVerison,
	kebabToCamel,
	translate
} from "@/utils/helpers";
import http from "@/utils/services/httpService";
import {toast} from "vue3-toastify";

const { fetchSettingsData, updateSettingsData, regenerateByPassCode } =
	useSettingsStore();

const { settingsData } = storeToRefs(useSettingsStore());
const {
	siteUrl,
	llmstxtFileUrl,
	llmstxtFileUserGenerated,
	mcpChoice,
	aiPluginCompatibility,
	nonce,
	restBaseUrl
} = useGeneralStoreData();

const WORDPRESS_UPDATE_LINK = getBaseUrl(location.href) + "update-core.php";

const isPageLoading = ref(false);

const HOSTINGER_FREE_DOMAINS = /hostingersite\.com|hostinger\.dev/;

const initialMcpChoice = ref(false);

const llmMasterToggle = ref(false);

const isLLMSSectionDisabled = computed(
	() => isFreeDomain.value || !isHostingerPlatform.value
);

const maintenanceSection = computed(() => [
	{
		id: SECTION_ID.MAINTENANCE_MODE,
		title: translate("hostinger_tools_maintenance_mode"),
		description: translate("hostinger_tools_disable_public_access"),
		isVisible: true,
		toggleValue: settingsData.value?.maintenanceMode
	},
	{
		id: SECTION_ID.BYPASS_LINK,
		title: translate("hostinger_tools_bypass_link"),
		description: translate("hostinger_tools_skip_link_maintenance_mode"),
		isVisible: true,
		sideButton: {
			text: translate("hostinger_tools_reset_link"),
			onClick: () => {
				openModal(
					ModalName.ByPassLinkResetModal,
					{
						data: {
							onConfirm: () => regenerateByPassCode()
						}
					},
					{ isLG: true }
				);
			}
		},
		copyLink: settingsData.value?.bypassCode
			? `${siteUrl}/?bypass_code=${settingsData.value.bypassCode}`
			: undefined
	}
]);

const securitySection = computed(() =>
	[
		{
			id: SECTION_ID.DISABLE_XML_RPC,
			title: translate("hostinger_tools_disable_xml_rpc"),
			description: translate("hostinger_tools_xml_rpc_description"),
			isVisible: true,
			toggleValue: settingsData.value?.disableXmlRpc
		},
		{
			id: SECTION_ID.DISABLE_AUTHENTICATION_PASSWORD,
			title: translate("hostinger_tools_disable_authentication_password"),
			description: translate(
				"hostinger_tools_authentication_password_description"
			),
			isVisible: true,
			toggleValue: settingsData.value?.disableAuthenticationPassword
		}
	].filter((item) => item.isVisible)
);

const redirectsSection = computed(() => {
	const allItems = [
		{
			id: SECTION_ID.FORCE_HTTPS,
			title: translate("hostinger_tools_force_https"),
			description: translate("hostinger_tools_force_https_description"),
			isVisible: true,
			toggleValue: settingsData.value?.forceHttps
		},
		{
			id: SECTION_ID.FORCE_WWW,
			title: translate("hostinger_tools_force_www"),
			description: !settingsData.value?.isEligibleWwwRedirect
				? translate(
						"hostinger_tools_force_www_description_not_available"
					)
				: translate("hostinger_tools_force_www_description"),
			isVisible: !!settingsData.value?.isEligibleWwwRedirect,
			toggleValue: settingsData.value?.forceWww
		}
	];

	return allItems.filter((item) => item.isVisible);
});

const llmsSection = computed(() => {
	const allItems = [
		{
			id: SECTION_ID.ENABLE_LLMS_TXT,
			title: translate("hostinger_tools_enable_llms_txt"),
			description: translate("hostinger_tools_llms_txt_description"),
			isVisible: true,
			toggleValue: settingsData.value?.enableLlmsTxt,
			learnMoreLink: "https://www.hostinger.com/support/how-to-enable-llms-txt-on-your-website/",
			sideButtons: [
				{
					id: "hostinger_tools_llms_txt_llmstxt",
					text: translate("hostinger_tools_llms_txt_llmstxt"),
					isDisabled: !settingsData.value?.enableLlmsTxt,
					to:
						settingsData.value?.enableLlmsTxt &&
						llmMasterToggle.value
							? llmstxtFileUrl
							: undefined,
					variant: "outline" as const
				},
				{
					id: "hostinger_tools_llms_txt_check_validity",
					text: translate("hostinger_tools_llms_txt_check_validity"),
					isDisabled: !settingsData.value?.enableLlmsTxt,
					to:
						settingsData.value?.enableLlmsTxt &&
						llmMasterToggle.value
							? `https://llmstxtvalidator.org/?url=${llmstxtFileUrl}`
							: undefined,
					variant: "outline" as const
				}
			]
		},
		{
			id: SECTION_ID.OPTIN_MCP,
			title: translate("hostinger_tools_optin_mcp"),
			description: translate("hostinger_tools_optin_mcp_description"),
			isVisible: true,
			toggleValue: settingsData.value?.optinMcp,
			learnMoreLink:
				"https://support.hostinger.com/en/articles/11729400-ai-agent-access-smart-ai-discovery"
		}
	];

	return allItems.filter((item) => item.isVisible);
});

const aiSection = computed(() => [
	{
		id: SECTION_ID.SWITCH_MCP_CHOICE,
		title: translate("hostinger_tools_mcp_choice"),
		description: translate("hostinger_tools_mcp_description"),
		isVisible: true,
		toggleValue: initialMcpChoice.value
	}
]);

const llmsSectionHeaderToggle = computed(() => {
	const visibleItems = llmsSection.value.filter((item) => item.isVisible);
	if (visibleItems.length <= 1) return undefined;

	return {
		value: llmMasterToggle.value,
		onToggle: async (value: boolean) => {
			llmMasterToggle.value = value;

			if (!settingsData.value) return;

			const updatedSettings = {
				...settingsData.value,
				enableLlmsTxt: value,
				optinMcp: value
			};

			const success = await updateSettingsData(updatedSettings);

			if (success && settingsData.value) {
				settingsData.value.enableLlmsTxt = value;
				settingsData.value.optinMcp = value;
			}
		}
	};
});

const { openModal } = useModal();

const isWordPressUpdateDisplayed = computed(() => {
	if (!settingsData.value) {
		return false;
	}

	return isNewerVerison({
		currentVersion: settingsData.value.currentWpVersion,
		newVersion: settingsData.value.newestWpVersion
	});
});

const isPhpUpdateDisplayed = computed(() => {
	if (!settingsData.value) {
		return false;
	}

	return isNewerVerison({
		currentVersion: settingsData.value.phpVersion,
		newVersion: "8.2" // Hardcoded for now
	});
});

const isHostingerPlatform = computed(
	() => parseInt(hostinger_tools_data.hplatform) > 0
);

const isFreeDomain = computed(() =>
	HOSTINGER_FREE_DOMAINS.test(String(siteUrl))
);

const createUpdateButton = (onClick: () => void) => ({
	text: translate("hostinger_tools_update"),
	onClick
});

const resellerLocale = computed(() => {
	const { pluginUrl } = useGeneralStoreData();

	return pluginUrl.match(/^[^/]+/)?.[0] || "hostinger.com";
});

const connectDomainUrl = computed(() => {
	if (!isHostingerPlatform.value) return undefined;

	const domain = location.host;

	return `https://auth.${resellerLocale.value}/login?section=website-dashboard&domain=${domain}`;
});

const phpVersionCard = computed(() => ({
	title: translate("hostinger_tools_php_version"),
	toolImageSrc: getAssetSource("images/icons/icon-php.svg"),
	version: settingsData.value?.phpVersion,
	actionButton:
		isHostingerPlatform.value && isPhpUpdateDisplayed.value
			? createUpdateButton(() => {
					window.open(
						`https://auth.${resellerLocale.value}/login?r=/section/php-configuration/domain/${location.host}`,
						"_blank"
					);
				})
			: undefined
}));

const wordPressVersionCard = computed(() => ({
	title: translate("hostinger_tools_wordpress_version"),
	toolImageSrc: getAssetSource("images/icons/icon-wordpress-light.svg"),
	version: settingsData.value?.currentWpVersion,
	actionButton: isWordPressUpdateDisplayed.value
		? createUpdateButton(() => {
				window.location.href = WORDPRESS_UPDATE_LINK;
			})
		: undefined
}));

const onSaveSection = (value: boolean, item: SectionItem) => {
	const isTurnedOn = value === false;

	if (item.id === SECTION_ID.DISABLE_XML_RPC && isTurnedOn) {
		openModal(
			ModalName.XmlSecurityModal,
			{
				data: {
					onConfirm: () => {
						onUpdateSettings(value, item);
					}
				}
			},
			{ isLG: true }
		);

		return;
	}

	onUpdateSettings(value, item);
};

const onSaveLLmsSection = async (isEnabled: boolean, item: SectionItem) => {
	const updateSetting = async () => {
		await onUpdateSettings(isEnabled, item);

		// Update the master toggle state based on current settings
		llmMasterToggle.value = !!(
			settingsData.value?.enableLlmsTxt || settingsData.value?.optinMcp
		);
	};

	if (
		llmstxtFileUserGenerated &&
		isEnabled &&
		item.id === SECTION_ID.ENABLE_LLMS_TXT
	) {
		openModal(
			ModalName.EnableLlmsTxtModal,
			{
				data: {
					onConfirm: () => {
						updateSetting();
					}
				}
			},
			{ isLG: true }
		);

		return;
	}

	await updateSetting();
};

const onSaveAiSection = async (isEnabled: boolean) => {
	try {
		const response = await http.post<SettingsData>(
			`${restBaseUrl}hostinger-ai-assistant/v1/toggle-mcp-plugin`,
			{ action: isEnabled ? "setup" : "deny" },
			{
				headers: { [Header.WP_NONCE]: nonce }
			}
		);

		const error = response[1];

		if (error) {
			toast.error(translate("hostinger_tools_settings_error"));
			return false;
		}

		initialMcpChoice.value = isEnabled;

		window.dispatchEvent(
			new CustomEvent("mcp-choice-changed", {
				detail: {
					choice: initialMcpChoice.value
				}
			})
		);

		toast.success(translate("hostinger_tools_settings_updated"));

	} catch (error) {
		console.error("Failed to save MCP choice: ", error);
		toast.error(translate("hostinger_tools_settings_error"));
	}
};

const onUpdateSettings = async (value: boolean, item: SectionItem) => {
	if (!settingsData.value) return;

	const id = kebabToCamel(item.id) as keyof ToggleableSettingsData;

	const updatedSettings = {
		...settingsData.value,
		[id]: value
	};

	const success = await updateSettingsData(updatedSettings);

	if (success && settingsData.value) {
		settingsData.value[id] = value;
	}
};

(async () => {
	isPageLoading.value = true;
	await fetchSettingsData();
	isPageLoading.value = false;

	if (parseInt(String(mcpChoice)) === 1) {
		initialMcpChoice.value = true;
	}

	llmMasterToggle.value = !!(
		settingsData.value?.enableLlmsTxt || settingsData.value?.optinMcp
	);
})();
</script>

<template>
  <div v-if="settingsData">
    <div class="hostinger-tools__tool-version-cards">
      <ToolVersionCard
        :is-loading="isPageLoading"
        v-bind="wordPressVersionCard"
        class="h-mr-16"
      />
      <ToolVersionCard
        :is-loading="isPageLoading"
        v-bind="phpVersionCard"
      />
    </div>
    <div>
      <SectionCard
        :is-loading="isPageLoading"
        :title="translate('hostinger_tools_llms')"
        :section-items="llmsSection"
        :is-disabled="isLLMSSectionDisabled"
        :header-toggle="llmsSectionHeaderToggle"
        :warning="
          llmstxtFileUserGenerated
            ? translate(
              'hostinger_tools_llms_txt_external_file_found',
            )
            : ''
        "
        @save-section="onSaveLLmsSection"
      >
        <template
          v-if="isLLMSSectionDisabled"
          #snackbar
        >
          <div
            class="hostinger-notice d-flex align-items-center w-100 h-mb-16"
          >
            <Icon
              name="icon-info"
              color="gray-dark"
            />
            <p class="text-body-3">
              {{
                translate(
                  "hostinger_tools_free_domain_llm_unavailable",
                )
              }}
            </p>
            <Button
              v-if="connectDomainUrl"
              size="small"
              variant="text"
              color="primary"
              class="h-ml-8"
              :to="connectDomainUrl"
              target="_blank"
            >
              {{
                translate("hostinger_tools_connect_domain_cta")
              }}
            </Button>
          </div>
        </template>
      </SectionCard>

      <SectionCard
        v-if="aiPluginCompatibility"
        :is-loading="isPageLoading"
        :title="translate('hostinger_tools_ai')"
        :section-items="aiSection"
        @save-section="onSaveAiSection"
      />

      <SectionCard
        :is-loading="isPageLoading"
        :title="translate('hostinger_tools_maintenance')"
        :section-items="maintenanceSection"
        @save-section="onSaveSection"
      />

      <SectionCard
        :is-loading="isPageLoading"
        :title="translate('hostinger_tools_security')"
        :section-items="securitySection"
        @save-section="onSaveSection"
      />

      <SectionCard
        :is-loading="isPageLoading"
        :title="translate('hostinger_tools_redirects')"
        :section-items="redirectsSection"
        @save-section="onSaveSection"
      />
    </div>
  </div>
</template>

<style lang="scss">
.hostinger-tools {
	&__tool-version-cards {
		display: flex;
		width: 100%;

		@media (max-width: 590px) {
			flex-direction: column;
		}
	}
}

.hostinger-notice {
	background: var(--gray-light);
	color: var(--gray-dark);
	border: 1px solid var(--gray-border);
	border-radius: 12px;
	padding: 12px 16px;
	font-size: var(--font-size-sm);
	gap: 1em;
}
</style>
