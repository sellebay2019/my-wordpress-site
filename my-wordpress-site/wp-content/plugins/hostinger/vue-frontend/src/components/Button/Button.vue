<script lang="ts" setup>
import { ref } from "vue";

import Icon from "@/components/Icon/Icon.vue";
import CircleLoader from "@/components/Loaders/CircleLoader.vue";
import { useButton } from "@/composables/useButton";
import type { IButtonProps } from "@/types";

type Emit = {
	click: [event: Event];
};

const props = withDefaults(defineProps<IButtonProps>(), {
	size: "medium",
	variant: "contain",
	color: "primary",
	isDisabled: null,
	isLoading: false
});
const emit = defineEmits<Emit>();
const buttonTextRef = ref<HTMLElement>();
const { style, tag } = useButton(props);

const handleClick = (event: Event) => {
	if (props.isDisabled) {
		event.preventDefault();
		event.stopPropagation();

		return;
	}
	emit("click", event);
};
</script>

<template>
  <Component
    :is="tag"
    :to="isDisabled ? undefined : to"
    :target="isDisabled ? undefined : target"
    :href="isDisabled ? undefined : to"
    class="button-v2"
    :class="{
      'button-v2--disabled': isDisabled,
      'button-v2--hovered': isHovered,
      'button-v2--loading': isLoading,
    }"
    :disabled="isDisabled || null"
    @click="handleClick"
  >
    <Icon
      v-if="iconPrepend && !isLoading"
      class="button-v2__icon"
      :name="iconPrepend"
      :color="isDisabled ? 'gray' : style.icon.color"
      :dimensions="style.icon.size"
    />

    <div class="button-v2__loader">
      <CircleLoader
        v-show="isLoading"
        :dimensions="style.loader.size"
        :border-color="style.loader.borderColor"
        :border-size="style.loader.border"
        :color="props.color"
      />
    </div>

    <span
      v-if="$slots.default"
      ref="buttonTextRef"
      class="button-v2__text"
    >
      <slot />
    </span>

    <Icon
      v-if="iconAppend && !isLoading"
      class="button-v2__icon"
      :name="iconAppend"
      :color="isDisabled ? 'gray' : style.icon.color"
      :dimensions="style.icon.size"
    />
  </Component>
</template>

<style lang="scss">
.button-v2 {
	$this: &;
	padding: v-bind("style.padding");
	color: v-bind("style.color");
	background-color: v-bind("style.backgroundColor");
	border: v-bind("style.border");
	border-radius: 8px;
	display: inline-flex;
	align-items: center;
	gap: 8px;
	position: relative;
	transition: background-color 0.1s ease-in-out;
	text-decoration: none;
	font-size: 12px;
	line-height: 24px;
	font-weight: 700;
	width: fit-content;
	flex-wrap: nowrap;
	justify-content: center;
	text-wrap: nowrap;

	&--disabled,
	&[disabled] {
		color: v-bind("style.colorDisabled") !important;
		background-color: v-bind("style.backgroundColorDisabled");
		font-size: 12px !important;
		pointer-events: none;
		cursor: not-allowed;
	}

	&--loading {
		pointer-events: none;

		#{$this}__text {
			opacity: 0;
		}

		#{$this}__loader {
			opacity: 1;
		}
	}

	&__text {
		opacity: 1;
		transition: opacity 0.2s ease-in-out;
	}

	&__loader {
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		pointer-events: none;
		opacity: 0;
		transition: opacity 0.2s ease-in-out;
	}

	&--hovered:not(&--disabled):not([disabled]) {
		background-color: v-bind("style.backgroundHoverColor");
	}

	&:hover:not(&--disabled):not([disabled]) {
		background-color: v-bind("style.backgroundHoverColor");
		cursor: pointer;
	}

	@media (max-width: 576px) {
		width: 100%;
	}
}
</style>
