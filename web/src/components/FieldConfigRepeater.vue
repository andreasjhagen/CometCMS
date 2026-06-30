<template>
  <div class="space-y-2">
    <label class="text-xs text-slate-500 block mb-1">Sub-fields</label>
    <div
      v-for="(sub, si) in field.subfields ?? []"
      :key="sub._id"
      class="rounded-lg border border-slate-200 bg-slate-50 p-2.5"
    >
      <div class="flex items-center gap-2">
        <span
          class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-slate-500"
        >
          <Icon :icon="fieldTypeIcon(sub.type)" class="h-3.5 w-3.5" />
        </span>
        <div class="flex-1 grid grid-cols-3 gap-2">
          <div>
            <label class="text-xs text-slate-400 block mb-0.5">Key</label>
            <input
              v-model="sub.key"
              type="text"
              placeholder="field_name"
              class="form-input w-full rounded-lg border-slate-300 text-sm"
              @blur="normalizeSubFieldKey(sub, si)"
            />
          </div>
          <div>
            <label class="text-xs text-slate-400 block mb-0.5">Type</label>
            <SearchableSelect
              :model-value="sub.type"
              :options="subFieldTypeOptions"
              :clearable="false"
              :searchable="false"
              @update:model-value="setSubFieldType(sub, $event)"
            />
          </div>
          <div class="flex flex-col justify-end">
            <label class="text-xs text-slate-400 block mb-0.5">&nbsp;</label>
            <label class="inline-flex items-center gap-2 h-[38px]">
              <input
                type="checkbox"
                v-model="sub.required"
                class="form-checkbox rounded border-slate-300 text-theme-600"
              />
              <span class="text-sm text-slate-600">Required</span>
            </label>
          </div>
        </div>
        <button
          type="button"
          @click="removeSubField(si)"
          class="shrink-0 text-slate-400 hover:text-red-500 transition-colors p-1 rounded"
          title="Remove sub-field"
        >
          <Icon icon="mdi:close" class="w-4 h-4" />
        </button>
      </div>

      <FieldConfigSelect
        v-if="sub.type === 'select'"
        :field="sub"
        class="mt-2 pl-9"
      />
      <FieldConfigRelation
        v-if="sub.type === 'relation'"
        :field="sub"
        :content-types="contentTypes"
        class="mt-2 pl-9"
      />
      <FieldConfigRange
        v-if="sub.type === 'range'"
        :field="sub"
        class="mt-2 pl-9"
      />
      <FieldConfigMedia
        v-if="sub.type === 'media'"
        :field="sub"
        class="mt-2 pl-9"
      />
    </div>
    <button
      type="button"
      @click="addSubField"
      class="w-full py-1.5 rounded-lg border border-dashed border-slate-300 text-xs text-slate-500 hover:border-theme-400 hover:text-theme-600 transition-colors"
    >
      + Add sub-field
    </button>
  </div>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import SearchableSelect from "./SearchableSelect.vue";
import FieldConfigSelect from "./FieldConfigSelect.vue";
import FieldConfigRelation from "./FieldConfigRelation.vue";
import FieldConfigRange from "./FieldConfigRange.vue";
import FieldConfigMedia from "./FieldConfigMedia.vue";
import { useFieldTypeMeta } from "../composables/useFieldTypeMeta.js";
import {
  normalizeKey,
  optionsToText,
  parseSelectOptions,
  serializeSelectOptions,
  rangeDefaults,
  uniqueKey,
} from "../composables/fieldBuilderUtils.js";

const props = defineProps({
  field: { type: Object, required: true },
  contentTypes: { type: Array, default: () => [] },
});

const { fieldTypeIcon } = useFieldTypeMeta();

const SUB_FIELD_TYPES = [
  "text",
  "textarea",
  "number",
  "boolean",
  "select",
  "date",
  "datetime",
  "media",
  "relation",
  "slug",
  "markdown",
  "html",
  "json",
  "color",
  "range",
];

const subFieldTypeOptions = SUB_FIELD_TYPES.map((type) => ({
  value: type,
  label: type,
  icon: fieldTypeIcon(type),
}));

let uid = 0;

function addSubField() {
  if (!Array.isArray(props.field.subfields)) props.field.subfields = [];
  props.field.subfields.push({
    _id: `sf_${uid++}`,
    key: "",
    type: "text",
    required: false,
    _optionsText: "",
    target: "",
  });
}

function removeSubField(index) {
  props.field.subfields.splice(index, 1);
}

function normalizeSubFieldKey(sub, index) {
  const base = normalizeKey(sub.key);

  if (base === "") {
    sub.key = "";
    return;
  }

  const used = new Set(
    (props.field.subfields ?? [])
      .filter((_, i) => i !== index)
      .map((item) => String(item.key ?? ""))
      .filter(Boolean),
  );

  sub.key = uniqueKey(base, used);
}

function setSubFieldType(sub, type) {
  sub.type = type;

  if (type === "select") {
    sub._optionsText = optionsToText(sub.options) || (sub._optionsText ?? "");
    sub.options = serializeSelectOptions(parseSelectOptions(sub._optionsText));
  } else {
    delete sub.options;
    delete sub._optionsText;
  }

  if (type === "relation") {
    sub.target = sub.target ?? sub.collection ?? "";
  } else {
    delete sub.target;
    delete sub.collection;
  }

  if (type === "range") {
    Object.assign(sub, rangeDefaults(sub));
  } else {
    delete sub.min;
    delete sub.step;
    delete sub.max;
    delete sub.display_decimals;
  }

  if (!["media", "relation", "select"].includes(type)) {
    delete sub.multiple;
  }
}
</script>
