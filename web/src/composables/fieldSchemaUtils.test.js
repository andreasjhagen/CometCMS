import { describe, expect, it } from "vitest";
import {
  formatJsonDefaultText,
  normalizeExternalDefault,
  normalizeExternalLayout,
  normalizeFieldDefault,
  normalizeLayoutWidth,
  selectDefaultSingleValue,
  selectDefaultValues,
  setFieldLayoutWidth,
  setJsonFieldDefault,
  validColorDefault,
} from "./fieldSchemaUtils.js";

describe("fieldSchemaUtils", () => {
  it("normalizes layout widths for storage", () => {
    expect(normalizeLayoutWidth("1/2")).toBe("1/2");
    expect(normalizeLayoutWidth("weird")).toBe("full");

    const field = { layout: { width: "1/2", align: "start" } };
    normalizeExternalLayout(field);
    expect(field.layout).toEqual({ width: "1/2", align: "start" });

    setFieldLayoutWidth(field, "full");
    expect(field.layout).toEqual({ align: "start" });
  });

  it("normalizes editable defaults", () => {
    const range = { type: "range", min: 4, max: 2, default: 99, step: -1 };
    normalizeFieldDefault(range);
    expect(range.default).toBe(4);
    expect(range.max).toBe(4);

    const multipleSelect = { type: "select", multiple: true, default: "draft" };
    normalizeFieldDefault(multipleSelect);
    expect(multipleSelect.default).toEqual(["draft"]);
  });

  it("normalizes external defaults before schema save", () => {
    const json = { type: "json", default: "", _defaultJsonText: "" };
    normalizeExternalDefault(json, '{"hero":true}');
    expect(json.default).toEqual({ hero: true });

    const media = { type: "media", default: ["hero.jpg"] };
    normalizeExternalDefault(media);
    expect(media.default).toBeUndefined();
  });

  it("handles default editor helpers", () => {
    const field = { type: "json" };
    setJsonFieldDefault(field, '{"count":2}');
    expect(field.default).toEqual({ count: 2 });
    expect(formatJsonDefaultText(field)).toBe('{\n  "count": 2\n}');
    expect(selectDefaultValues({ default: "draft" })).toEqual(["draft"]);
    expect(selectDefaultSingleValue({ default: ["draft"] })).toBe("draft");
    expect(validColorDefault("red")).toBe("#000000");
  });
});
