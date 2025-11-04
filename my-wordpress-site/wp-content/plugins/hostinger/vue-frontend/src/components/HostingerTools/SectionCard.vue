<script lang="ts" setup>
import Button from "@/components/Button/Button.vue";
import Card from "@/components/Card.vue";
import CopyField from "@/components/CopyField.vue";
import Icon from "@/components/Icon/Icon.vue";
import SkeletonLoader from "@/components/Loaders/SkeletonLoader.vue";
import Toggle from "@/components/Toggle.vue";
import { SectionHeaderButton, SectionItem } from "@/types";
import { translate } from "@/utils/helpers";

type SectionHeaderToggle = {
	value: boolean;
	onToggle?: (value: boolean) => void;
};

type Props = {
	title: string;
	isLoading?: boolean;
	sectionItems: SectionItem[];
	headerButtons?: SectionHeaderButton[];
	headerToggle?: SectionHeaderToggle;
	warning?: string;
	isDisabled?: boolean;
};

type Emits = {
	"save-section": [value: boolean, item: SectionItem];
};

defineProps<Props>();

const emit = defineEmits<Emits>();
</script>

<template>
  <div class="home-section">
    <Card v-if="isLoading">
      <SkeletonLoader
        class="h-mb-24"
        width="50%"
        :height="24"
        rounded
      />
      <SkeletonLoader
        v-for="(item, index) in sectionItems"
        :key="`skeleton-${index}`"
        :class="{ 'h-mb-24': index !== sectionItems.length - 1 }"
        width="100%"
        :height="item.copyLink ? 48 : 24"
        rounded
      />
    </Card>
    <Card v-else>
      <template #header>
        <div class="w-100">
          <slot name="snackbar" />
          <div
            class="d-flex align-items-center justify-content-between w-100"
          >
            <div class="d-flex align-items-center">
              <Toggle
                v-if="headerToggle"
                :model-value="headerToggle.value"
                :bind="false"
                :is-disabled="isDisabled"
                class="h-mr-16"
                @click="
                  headerToggle.onToggle &&
                    headerToggle.onToggle(!headerToggle.value)
                "
              />
              <h2 class="h-m-0">
                {{ title }}
              </h2>
            </div>
            <div
              v-if="headerButtons?.length"
              class="d-flex align-items-center"
            >
              <Button
                v-for="button in headerButtons"
                :key="button.id"
                size="small"
                :to="button.to"
                :is-disabled="isDisabled"
                :variant="button.variant"
                :target="button.to ? '_blank' : undefined"
                :icon-append="
                  button.to ? 'icon-launch' : undefined
                "
              >
                {{ button.text }}
              </Button>
            </div>
          </div>
          <div
            v-if="warning"
            class="hostinger-notice d-flex align-items-center w-100 mt-3"
          >
            <Icon
              name="icon-info"
              color="gray-dark"
            />
            <p class="text-body-3">
              {{ warning }}
            </p>
          </div>
        </div>
      </template>
      <div
        v-for="item in sectionItems"
        :key="item.title"
        class="home-section__section-item"
        :class="{
          'home-section__section-item--disabled':
            headerToggle && !headerToggle.value,
        }"
      >
        <div class="d-flex flex-direction-column">
          <div class="d-flex align-items-center w-100">
            <Toggle
              v-if="item.toggleValue !== undefined"
              class="h-mr-16"
              :model-value="Boolean(item.toggleValue)"
              :bind="false"
              :is-disabled="
                (headerToggle && !headerToggle.value) ||
                  isDisabled
              "
              @click="
                emit(
                  'save-section',
                  Boolean(!item.toggleValue),
                  item,
                )
              "
            />
            <div class="d-flex flex-column flex-grow-1">
              <h3 class="h-m-0">
                {{ item.title }}
              </h3>
              <p class="h-m-0 text-body-2">
                {{ item.description }}
                <template v-if="item.learnMoreLink">
                  <a
                    :href="item.learnMoreLink"
                    target="_blank"
                    rel="noopener"
                    class="text-link-2 additional-link"
                  >
                    {{
                      translate(
                        "hostinger_tools_llms_txt_learn_more",
                      )
                    }}
                  </a>
                </template>
              </p>
            </div>
            <div
              v-if="
                item.sideButtons?.length ||
                  item.sideButton?.text
              "
              class="d-flex align-items-center h-ml-16 section-buttons"
            >
              <Button
                v-for="(button, index) in item.sideButtons"
                :key="`${item.title}-${button.id || index}`"
                size="small"
                :variant="button.variant || 'text'"
                :to="button.to"
                :target="button.to ? '_blank' : undefined"
                :icon-append="button.icon || 'icon-launch'"
                :is-disabled="button.isDisabled || isDisabled"
                color="primary"
                @click="button.onClick"
              >
                {{ button.text }}
              </Button>

              <Button
                v-if="
                  item.sideButton?.text &&
                    !item.sideButtons?.length
                "
                size="small"
                variant="text"
                :is-disabled="isDisabled"
                @click="item.sideButton?.onClick"
              >
                {{ item.sideButton?.text }}
              </Button>
            </div>
          </div>
        </div>
        <CopyField
          v-if="item.copyLink"
          class="h-mt-16"
          :link="item.copyLink"
        />
      </div>
    </Card>
  </div>
</template>

<style lang="scss">
.home-section {
	&__section-item {
		display: flex;
		flex-direction: column;
		margin-top: 16px;
		padding-bottom: 16px;
		border-bottom: 1px solid var(--gray-border);

		&:last-child {
			margin-bottom: 0;
			padding-bottom: 0;
			border-bottom: none;
		}

		&--disabled {
			opacity: 0.5;
			pointer-events: none;
		}

		.additional-link {
			text-decoration: none !important;
		}
	}

	.section-buttons {
		gap: 8px;
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
