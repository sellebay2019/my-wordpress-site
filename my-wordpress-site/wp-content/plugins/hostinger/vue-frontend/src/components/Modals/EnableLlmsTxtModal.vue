<script lang="ts" setup>
import Button from "@/components/Button/Button.vue";
import BaseModal from "@/components/Modals/Base/BaseModal.vue";
import { useModal } from "@/composables";
import { translate } from "@/utils/helpers";

interface Props {
	data: {
		onConfirm: () => void;
	};
}

const props = defineProps<Props>();

const { closeModal } = useModal();

const onProceed = () => {
	if (props.data.onConfirm) props.data.onConfirm();

	closeModal();
};
</script>

<template>
  <BaseModal
    :title-icon="{
      name: 'icon-info',
      color: 'danger',
    }"
    :title="translate('hostinger_tools_llms_txt_modal_title')"
  >
    <p class="h-mb-24 text-gray">
      {{ translate("hostinger_tools_llms_txt_modal_description") }}
    </p>
    <div class="d-flex justify-content-end">
      <Button
        color="danger"
        variant="text"
        class="h-mr-16"
        @click="closeModal"
      >
        {{ translate("hostinger_tools_llms_txt_modal_cancel") }}
      </Button>
      <Button
        color="danger"
        @click="onProceed"
      >
        {{ translate("hostinger_tools_llms_txt_modal_create_file") }}
      </Button>
    </div>
  </BaseModal>
</template>
