<script lang="ts" setup>
import { computed, defineAsyncComponent } from "vue";

import type { Color } from "@/types";
import type { IconUnion } from "@/types/enums/iconModels";
import { toTitleCase, wrapInCssVar } from "@/utils/helpers";
import { kebabToCamel } from "@/utils/services/snakeCamelService";

interface Props {
	dimensions?: number;
	color?: Color;
	name: IconUnion;
}

const props = withDefaults(defineProps<Props>(), {
	dimensions: 24,
	color: "white"
});

const iconColor = computed(() => {
	if (!props.color) return "";

	return wrapInCssVar(props.color);
});

const selectedIcon = computed(() =>
	defineAsyncComponent(
		() =>
			import(
				`@/components/Icon/Icons/${kebabToCamel(toTitleCase(props.name))}.vue`
			)
	)
);
</script>

<template>
  <svg
    class="icon"
    aria-hidden="true"
  >
    <g>
      <Component :is="selectedIcon" />
    </g>
  </svg>
</template>

<style lang="scss" scoped>
.icon {
	transition: 0.3s ease transform;
	fill: currentColor;
	color: v-bind(iconColor);
	width: v-bind("dimensions + 'px'");
	height: v-bind("dimensions + 'px'");
	min-width: v-bind("dimensions + 'px'");
}
</style>
