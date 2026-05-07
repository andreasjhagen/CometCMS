<template>
  <div class="space-y-2">
    <label class="text-xs text-slate-500 block mb-1">
      Options
      <span class="text-slate-400"
        >(one per line, optionally <code>key:Label</code>)</span
      >
    </label>
    <textarea
      v-model="field._optionsText"
      rows="4"
      placeholder="draft:Draft&#10;published:Published&#10;archived"
      class="form-textarea w-full rounded-lg border-slate-300 text-sm font-mono"
      @input="syncOptions"
    />
    <label class="inline-flex items-center gap-2">
      <input
        type="checkbox"
        v-model="field.multiple"
        class="form-checkbox rounded border-slate-300 text-theme-600"
      />
      <span class="text-sm text-slate-600">Allow multiple</span>
    </label>
  </div>
</template>

<script setup>
import {
  parseSelectOptions,
  serializeSelectOptions,
} from "../composables/fieldBuilderUtils.js";

const props = defineProps({
  field: { type: Object, required: true },
});

function syncOptions() {
  props.field.options = serializeSelectOptions(
    parseSelectOptions(props.field._optionsText),
  );
}
</script>
