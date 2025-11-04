<script lang="ts" setup>
import Button from "@/components/Button/Button.vue";
import { useGeneralStoreData } from "@/stores";
import { EditSiteButton, HeaderButton, PreviewSiteButton } from "@/types";

const props = defineProps<Props>();

const { siteUrl, editSiteUrl } = useGeneralStoreData();

type Props = {
	title?: string;
	headerButton?: HeaderButton;
	previewSiteButton?: PreviewSiteButton;
	editSiteButton?: EditSiteButton;
};
</script>

<template>
  <div class="wrapper">
    <div class="wrapper__content">
      <div class="wrapper__header">
        <h1
          v-if="props.title"
          class="text-heading-1"
        >
          {{ props.title }}
        </h1>
        <div class="wrapper__buttons-wrapper">
          <Button
            v-if="headerButton"
            class="wrapper__button"
            :to="headerButton?.href"
            size="small"
            variant="outline"
            :target="headerButton.href ? '_blank' : undefined"
            icon-append="icon-launch"
            @click="headerButton?.onClick"
          >
            {{ headerButton.text }}
          </Button>
          <Button
            v-if="previewSiteButton && siteUrl"
            class="wrapper__button"
            :to="siteUrl"
            size="small"
            variant="outline"
            :target="siteUrl ? '_blank' : undefined"
            icon-prepend="icon-visibility"
            @click="previewSiteButton?.onClick"
          >
            {{ previewSiteButton.text }}
          </Button>
          <Button
            v-if="editSiteButton && editSiteUrl"
            class="wrapper__button"
            :to="editSiteUrl"
            size="small"
            variant="outline"
            :target="editSiteUrl ? '_blank' : undefined"
            icon-prepend="icon-launch"
            @click="editSiteButton?.onClick"
          >
            {{ editSiteButton.text }}
          </Button>
        </div>
      </div>
      <slot />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.wrapper {
	padding: 48px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: left;
	min-height: calc(100vh - var(--header-height));

	@media (max-width: 768px) {
		padding-right: 10px;
		padding-left: 0px;
	}

	&__buttons-wrapper {
		display: flex;

		@media (max-width: 500px) {
			width: 100%;
			flex-wrap: wrap;
		}
	}

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-wrap: wrap;
	}

	&__button {
		background-color: var(--white);
		margin-left: 10px;
		display: flex;
		flex-wrap: nowrap;

		@media (max-width: 500px) {
			margin: 5px 0;
		}
	}

	&__content {
		max-width: 740px;
		width: 100%;
	}
}
</style>
