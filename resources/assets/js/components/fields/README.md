# Docs

## Fields

All fields share some attributes:

- type
- name
- label
- info (optional)

### Text / Textarea

```
{
	"type":        "text|textarea",
	"name":        "name",
	"label":       "Text",
	"default":     "value",
	"info":        "Text",
	"placeholder": "Text"
}
```

### Richtext

```
{
	"type":        "richtext",
	"name":        "name",
	"label":       "Text",
	"default":     "<p>value</p>",
	"info":        "Text",
	"placeholder": "Text"
}
```

### Switch / Checkbox

```
{
	"type":    "switch|checkbox",
	"name":    "name",
	"label":   "Text",
	"default": false,
	"info":    "Text"
}
```

### Select / Multi-select

```
{
	"type":    "select|multiselect",
	"name":    "name",
	"label":   "Text",
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
	"info":    "Text"
}

```

### Radio / Button group

```
{
	"type":    "radio|buttongroup",
	"name":    "name",
	"label":   "Text",
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
	"info":    "Text"
}
```

### Link

```
{
	"type":  "link",
	"name":  "name",
	"label": "Text",
	"info":  "Text"
}
```

### Image

```
{
	"type":     "image",
	"name":     "name",
	"label":    "Text",
	"settings": [

	],
	"info":     "Text"
}
```

### Video

```
{
	"type":     "video",
	"name":     "name",
	"label":    "Text",
	"settings": [

	],
	"info":     "Text"
}
```

### File

```
{
	"type":     "file",
	"name":     "name",
	"label":    "Text",
	"settings": [

	],
	"info":     "Text"
}
```
