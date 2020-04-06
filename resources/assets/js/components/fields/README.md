## Overview

### Pages
Pages are made up of different regions that each contain sections, we refer to these sections as blocks. Every page has a layout, which essentially represents a theme.

Schema:

| Column | Description |
| --- | --- |
| `title` | The page title. |
| `layout_name` | The layout definition this page is using. |
| `layout_version` | The version of this layout in use. |
| `is_published` | Whether this page has been published or not. |
| `options` | Misc options for this page, metadata etc. (JSON). |

### Layouts
Layouts are part of a theme and are a combination of specific markup, CSS and JavaScript. Layouts are used by pages and are divided into one or more regions, described below.

### Regions
Regions are subdivisions of a page/layout (eg. header, main, sidebar and footer), each of which allows different blocks within them.

### Blocks
Blocks are discrete sections within a region. Blocks are stored in a separate table, rather than being embedded in the page's JSON, this is so usage data can be collected and, in the future, we can create more complex block types easier.

Schema:

| Column | Description |
| --- | --- |
| `page_id` | The page this block is part of. |
| `order` | The order this block appears on a page. |
| `definition_name` | The definition this block is using (block type). |
| `definition_version` | The version of this block we are using. |
| `region_name` | What region this block appears in (they don't have a version so they can be ported to different themes). |
| `fields` | The field data (JSON). |


### Fields
Fields are inputs that allow end-users to modify blocks on a page.  Blocks are made up of zero or more fields.

## Definition files

A block's structure is represented via definition files. These also contain the embedded field definitions which define what field types are contained within these blocks and any validation rules. The definition files are stored as JSON and are simple enough to put together manually quickly.


### Block definitions
Block definitions contain the label, name, version and fields that belong to a block.

| Attribute | Required? | Description |
| --- | --- | --- |
| `label` | yes | A human-friendly label for this block. |
| `name` | yes | The unique name for this block. It must only contain alphanumeric characters, underscores, and dashes. |
| `version` | yes | The block version. When a backwards incompatible change happens we bump this version. |
| `fields` | no | An array of field definitions. These are displayed in the order defined. |

#### Example:
```json
{
	"label": "My Block Name",
	"name": "my-block",
	"version": 1,
	"fields": [
		...fields
	]
}
```


### Fields types

#### Input fields
Input fields share some attributes:

| Attribute | Required? | Description |
| --- | --- | --- |
| `type` | yes | The field type, which controls which field to display for the data. |
| `name` | yes | The field name, which must be unique. It can only contain alphanumeric characters, underscores, and dashes. |
| `label` | yes | A human-friendly label for this field. |
| `default` | no | A default value for this field. |
| `info` | no | Additional information about the field, displayed as help text below the field name. You can include external links to further help or information in the info text using the format `<a href='' target='_blank' rel='external'>Link</a>` |
| `placeholder` | no | A value for the input's placeholder text. |

#### Input field definitions

##### Text / Textarea
```json
{
	"type":        "text|textarea",
	"name":        "name",
	"label":       "Text Field",
	"default":     "value",
	"info":        "Info here",
	"placeholder": "Placeholder here"
}
```
> Screenshots here

##### Richtext
```json
{
	"type":        "richtext",
	"name":        "name",
	"label":       "Richtext Field",
	"default":     "<p>value</p>",
	"info":        "Info here",
	"placeholder": "Placeholder here"
}
```
> Screenshot here

##### Switch / Checkbox
```json
{
	"type":    "switch|checkbox",
	"name":    "name",
	"label":   "Switch/Checkbox Field",
	"default": false,
	"info":    "Info here"
}
```
> Screenshots here

##### Select / Multi-select
```json
{
	"type":    "select|multiselect",
	"name":    "name",
	"label":   "Select Field",
	"options": [
		{
			"group": "Group 1",
			"value": "value",
			"label": "Text 1"
		},
		{
			"group": "Group 2",
			"value": "value2",
			"label": "Text 2"
		}
	],
	"default": "value",
	"info":    "Info here"
}

```
> Screenshots here

##### Radio / Button group
```json
{
	"type":    "radio|buttongroup",
	"name":    "name",
	"label":   "Radio Field",
	"options": [
		{
			"value": "value",
			"label": "Text 1"
		},
		{
			"value": "value2",
			"label": "Text 2"
		}
	],
	"default": "value",
	"info":    "Info here"
}
```
> Screenshot here

##### Media
```json
{
	"type":     "media",
	"name":     "name",
	"label":    "Media Field",
	"settings": {
		"media_type": "image|document|video|audio",
		...etc
	},
	"info":     "Info here"
}
```
> Screenshot here

##### Date
```json
{
	"type":    "date",
	"name":    "name",
	"label":   "Date Field",
	"info":    "Info here"
}
```
> Screenshot here

##### DateTime
```json
{
	"type":    "datetime",
	"name":    "name",
	"label":   "DateTime Field",
	"info":    "Info here"
}
```
> Screenshot here

##### Time
```json
{
	"type":    "time",
	"name":    "name",
	"label":   "Time Field",
	"info":    "Info here"
}
```
> Screenshot here

##### Nested
```json
{
	"type":    "nested",
	"name":    "name",
	"label":   "Nested Field",
	"info":    "Info here"
}
```
> Screenshot here


### Validation rules

Each field can have a set of validation rules.
If none are supplied, only the type of a field is validated.

#### Field types

| Field | Type |
| --- | --- |
| text | `string` |
| textarea | `string` |
| richtext | `string` |
| switch | `boolean` |
| checkbox | `array` |
| select | `*` |
| multiselect | `array` |
| radio | `*` |
| buttongroup | `*` |
| link | `string` |
| image | `object` |
| number | `number` |
| slider | `integer` |
| date | `date` |
| time | `*` |
| datetime | `*` |
| nested | `array` |
| collection | `array` |
| group | `object` |

#### Rules

| Rule | Argument(s) | Format | Description |
| --- | --- | --- | --- |
| required | none | required | Whether this field is required. |
| string | none | string | Only length. |
| integer | none | integer | Only length. |
| in | `list` | in:`list` | An `enum` type. The argument is a comma separated list that we search for our value in. |
| min_value | `len` | min_value:`len` | Minimum integer value. |
| max_value | `len` | max_value:`len` | Maximum integer value. |
| min_length | `len` | min_length:`len` | Minimum string length. |
| max_length | `len` | max_length:`len` | Maximum string length. |
| max_length_without_html | `len` | max_length_without_html:`len` | Maximum string length once all HTML is removed. |
| min | `len` | min:`len` | Minimum array length. |
| max | `len` | max:`len` | Maximum array length. |
| length | `len` | length:`len` | Ensure item is this length. |
| regex | `regex` | regex:`regex` | Validate this field based on a regular expression. |
| slug | none | slug | Ensures this field matches `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`. |

Validation rules are defined as an array like so:

```json
{
	"type":    "text",
	"name":    "name",
	"label":   "A field",
	"info":    "Info here",
	"validation": [
		"required",
		"in:list,of,words"
	]
}
```

In this instance, if the field was left empty or wasn't one of the predefined options (list, of or word) then a validation  would be shown to the user.


### Special field types

These fields differ slightly from the others as they contain nested data or fields that don't store any data at all.

#### Group/nested

The group field type allows us to nest/scope fields. They are only ever one level deep.
This visually groups a set of fields to appear like a field made up of other fields.

```json
{
	"name": "link",
	"label": "Link",
	"type": "group",

	"fields": [
		{
			"name": "url",
			"label": "URL",
			"type": "text"
		},
		{
			"name": "text",
			"label": "Link Text",
			"type": "text"
		}
	]
}
```

> Screenshot here

#### Collection

Sometimes we need a collection of a certain field type. As an example we may want several images for a slider. We could repeat the same field several times using a different name eg. slide1, slide2, slide3. This isn't the best solution though, as it's repetitive and we might want to set a maximum/minimum amounts of images without having a bunch of empty fields.

We use validation rules to help us display a simple UI to add/remove fields (referred to as "items" on the client-side).

```json
{
	"name": "slides",
	"label": "Slider",
	"type": "collection",

	"fields": [
		{
			"name": "image",
			"label": "Image",
			"type": "image",
			"default": {
				"url": "/img/placeholder.jpg",
				"name": "placeholder.jpg"
			}
		},
		{
			"name": "text",
			"label": "Text",
			"type": "text"
		}
	],

	"validation": [
		"required",
		"min:2",
		"max:4"
	]
}
```
If there were no validation rules set then it would simply mean they can add as many or as few items as they like (probably a bad idea).

> Screenshot here

#### Display fields

Display fields give us the ability to visually separate fields without actually modifying the structure of the data underneath (these fields have no user inputs).

All display fields share some attributes:

| Attribute | Required? | Description |
| --- | --- | --- |
| `type` | yes | The field type (header or paragraph). |
| `content` | yes | Textual content. |

#### Display field definitions

##### Header / Paragraph
```json
{
	"type":    "header|paragraph",
	"content": "content"
}
```
> Screenshots here

#### Dynamic fields

Dynamic fields pull their data from a specific source and display it in a user-friendly way.

> TODO: Implement dynamic fields

```json
{
	"type": "field-type",
	"dynamic": true
}
```
> Screenshot here


### Adding custom fields

> TODO: Implement ability to add custom fields
