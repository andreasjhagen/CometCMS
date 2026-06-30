<template>
  <span class="inline-flex items-center align-baseline">
    <select
      v-if="editing"
      ref="selectEl"
      :value="modelValue"
      :aria-label="ariaLabel"
      class="form-select inline-block h-8 w-20 rounded-md border-slate-300 py-1 pl-2 pr-7 text-sm"
      @change="onChange"
      @blur="close"
      @keydown.escape.prevent.stop="close"
    >
      <option v-for="option in options" :key="option" :value="option">
        {{ option }}
      </option>
    </select>
    <button
      v-else
      type="button"
      class="inline rounded px-0.5 font-medium text-slate-700 underline decoration-slate-300 underline-offset-2 transition-colors hover:text-theme-600 hover:decoration-theme-400 focus:outline-none focus:ring-2 focus:ring-theme-500 focus:ring-offset-2"
      :aria-label="ariaLabel"
      @click="open"
    >
      {{ displayValue ?? modelValue }}
    </button>
  </span>
</template>

<script setup>
import { nextTick, ref } from "vue";

defineProps({
  modelValue: {
    type: Number,
    required: true,
  },
  options: {
    type: Array,
    required: true,
  },
  displayValue: {
    type: Number,
    default: null,
  },
  ariaLabel: {
    type: String,
    default: "Rows per page",
  },
});

const emit = defineEmits(["update:modelValue", "change"]);

const editing = ref(false);
const selectEl = ref(null);

function open() {
  editing.value = true;
  nextTick(() => {
    selectEl.value?.focus();
  });
}

function close() {
  editing.value = false;
}

function onChange(event) {
  const value = Number(event.target.value);
  emit("update:modelValue", value);
  emit("change", value);
  close();
}
</script>
