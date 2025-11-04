<script setup lang="ts">
import { computed } from "vue";
import { RouterView, useRoute } from "vue-router";

import Modals from "@/components/Modals/Base/Modals.vue";
import Wrapper from "@/layouts/Wrapper.vue";
import { EditSiteButton, HeaderButton, PreviewSiteButton } from "@/types";

const route = useRoute();

const headerTitle = computed(() => route.meta.title as string);

const headerButton = computed(() => route.meta.headerButton as HeaderButton);

const previewSiteButton = computed(
	() => route.meta.previewSiteButton as PreviewSiteButton
);

const editSiteButton = computed(
	() => route.meta.editSiteButton as EditSiteButton
);
</script>

<template>
  <div>
    <div id="overhead-button" />
    <Wrapper
      :title="headerTitle"
      :header-button="headerButton"
      :preview-site-button="previewSiteButton"
      :edit-site-button="editSiteButton"
    >
      <RouterView v-slot="{ Component }">
        <Component :is="Component" />
      </RouterView>
    </Wrapper>
    <Modals />
  </div>
</template>

<style lang="scss" scoped>
:deep(.h-button-v2) {
	&:hover {
		cursor: pointer;
	}
}
#overhead-button {
	position: absolute;
	right: 0;
	padding: 40px;
	z-index: 2;

	@media (max-width: 576px) {
		padding: 16px;
	}
}
</style>
