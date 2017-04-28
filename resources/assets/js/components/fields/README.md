# CMS docs

## Overview

### Sites
Sites are just pages marked as a site (`is_site`) in the DB, these are top-level pages containing more pages underneath them.

### Pages
Pages are stored as a title, slug, some meta information along with schemaless data stored as JSON in an `options` column. Not having a predefined schema makes evolving the system easy as we add new (or custom) options.

### Routes
Routes are used to store quick lookups for pages [TODO: explain more].

### Blocks
Blocks are discrete sections on a page. Pages are made up of a list of blocks. Blocks are stored in a separate table to pages, rather than being embedded in the page's JSON, so usage data can be collected and, in the future, we can create other block types for dynamic data.

Main table columns:

| Column | Description |
| --- | --- |
| `page_id` | The page this block is part of. |
| `type` | The block type. |
| `fields` | The field data. |
| `order` | The order this block appears on a page. |
| `parent_block` | The block this block is nested within. |
| `section` | If this block is nested, what part it is nested in. |

### Fields
Fields are inputs that allow end-users to modify blocks.  Blocks are made up of zero or more fields. Fields have their own definitions which are embedded in the block definition, which describe their validation rules etc. explained below.

### Block definition files
Block definitions contain the name, type and fields that belong to a block.
The type is a unique identifier.

| Attribute | Required? | Description |
| --- | --- | --- |
| `name` | yes | The unique name for this block, which needs to be unique per block. It must only contain alphanumeric characters, underscores, and dashes. |
| `type` | yes | The block type, which controls which block and fields to display. |
| `fields` | no | An array of field definitions (these appear in the order defined). |

#### Example:
```
{
	"name": "My Block Name",
	"type": "my-block-v1",
	"fields": [
		...fields
	]
}
```

### Fields types

#### Input fields
All fields share some attributes:

| Attribute | Required? | Description |
| --- | --- | --- |
| `type` | yes | The field type, which controls which field to display for the data. |
| `name` | yes | The unique name for this field, which needs to be unique per block. It must only contain alphanumeric characters, underscores, and dashes. |
| `label` | yes | A human-friendly label for this field. |
| `default` | no | A value the field can default to. |
| `info` | no | Additional information about the field, displayed as a tooltip. |
| `placeholder` | no | A value for the input's placeholder text. |

#### Input field definitions

##### Text / Textarea
```
{
	"type":        "text|textarea",
	"name":        "name",
	"label":       "Text Field",
	"default":     "value",
	"info":        "Info here",
	"placeholder": "Placeholder Value"
}
```
> Screenshots here

##### Richtext
```
{
	"type":        "richtext",
	"name":        "name",
	"label":       "Richtext Field",
	"default":     "<p>value</p>",
	"info":        "Info here",
	"placeholder": "Text"
}
```
> Screenshot here

##### Switch / Checkbox
```
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
```
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
```
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
> Screenshots here

##### Link
```
{
	"type":  "link",
	"name":  "name",
	"label": "Link Field",
	"info":  "Info here"
}
```
> Screenshot here

##### Image
```
{
	"type":     "image",
	"name":     "name",
	"label":    "Image Field",
	"settings": [

	],
	"info":     "Info here"
}
```
> Screenshot here

##### Video
```
{
	"type":     "video",
	"name":     "name",
	"label":    "Video Field",
	"settings": [

	],
	"info":     "Info here"
}
```
> Screenshot here

##### File
```
{
	"type":     "file",
	"name":     "name",
	"label":    "File Field",
	"settings": [

	],
	"info":     "Info here"
}
```
> Screenshot here

#### Display fields

Display fields give us the ability to visually separate fields without actually modifying the structure of the data underneath (these fields have no user inputs).

All fields share some attributes:

| Attribute | Required? | Description |
| --- | --- | --- |
| `type` | yes | The field type (header or paragraph). |
| `content` | yes | Textual content. |
| `info` | no | Additional information about the field, displayed as a tooltip. |

#### Display field definitions

##### Header / Paragraph
```
{
	"type":    "header|paragraph",
	"content": "content",
	"info":    "Info here"
}
```
> Screenshots here


#### Multiples of the same field

Sometimes we need a collection of a certain field type. As an example we may want several images for a slider. We could repeat the same field several times using a different name eg. slide1, slide2, slide3. This isn't the best solution though, as we might want to allow between 0 and 5 images, so some of those fields will appear and be left empty.

Instead we have an attribute that controls this for us and displays a simple UI to add/remove fields.

```
{
	"counts": {
		"initial": 1,
		"min": 0,
		"max": 0
	}
}
```
> Screenshot here

#### Dynamic fields

Dynamic fields pull their data from a specific source and display it in a user-friendly way.

```
{
	"type": "field-type",
	"dynamic": true
}
```
> Screenshot here

#### Grouping and nested fields

Grouping fields allow us to nest/scope fields. They can have a maximum depth of three levels.
> TODO: explain and implement grouping and nested fields

> Screenshot here

#### Controlling data output

```
{
	"query": {
		"page": 1,
		"limit": 15,
		"sort": "id desc"
	}
}
```

### Adding custom fields

Custom fields can be added by importing them...

----------
