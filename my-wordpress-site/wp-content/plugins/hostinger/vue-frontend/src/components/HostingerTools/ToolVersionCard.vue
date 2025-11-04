<script lang="ts" setup>
import Button from "@/components/Button/Button.vue";
import Card from "@/components/Card.vue";
import SkeletonLoader from "@/components/Loaders/SkeletonLoader.vue";
import { translate } from "@/utils/helpers";

type Props = {
	title: string;
	toolImageSrc: string;
	version?: string;
	actionButton?: {
		text: string;
		onClick?: () => void;
	};
	isLoading?: boolean;
};

defineProps<Props>();
</script>

<template>
  <Card v-if="isLoading">
    <template #header>
      <SkeletonLoader
        width="50%"
        :height="24"
        rounded
      />
    </template>
    <SkeletonLoader
      width="100%"
      :height="24"
      rounded
    />
  </Card>
  <Card
    v-else
    class="tool-version-card"
  >
    <template #header>
      <div class="d-flex justify-content-between w-100">
        <div class="d-flex">
          <img
            class="h-mr-8"
            height="24"
            width="24"
            :src="toolImageSrc"
            alt="Tool icon"
          >
          <div>
            <h3 class="h-m-0">
              {{ title }}
            </h3>
            <p class="text-body-2">
              {{ version }}
            </p>
          </div>
        </div>
      </div>
    </template>

    <Button
      v-if="actionButton"
      @click="actionButton?.onClick"
    >
      {{
        translate("hostinger_tools_update")
      }}
    </Button>
  </Card>
</template>

<style lang="scss" scoped>
.tool-version-card {
	gap: 0;
	display: flex;
	flex-direction: row;

	@media (max-width: 768px) {
		flex-direction: column;
		gap: 16px;
	}

	::v-deep(.card__body) {
		flex: 1;
	}
}
</style>
