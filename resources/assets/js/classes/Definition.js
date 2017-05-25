export default class Definition {

	static typeMap = {
		text       : 'string',
		textarea   : 'string',
		richtext   : 'string',
		switch     : 'boolean',
		checkbox   : 'array',
		select     : '*',
		multiselect: 'array',
		radio      : '*',
		buttongroup: '*',
		link       : '*',
		image      : 'object',
		file       : 'object',
		number     : 'number',
		slider     : 'integer',
		date       : 'date',
		time       : '*',
		datetime   : '*',
		nested     : 'array'
	};

	static definitions = {};

	static set(definition) {
		const type = Definition.getType(definition);

		if(type === null) {
			return;
		}

		if(Definition.definitions[type] === void 0) {
			Definition.definitions[type] = definition;
		}
	}

	static get(type) {
		if(Definition.definitions[type]) {
			return Definition.definitions[type];
		}

		return null;
	}

	static getType(definition) {

		if(definition.name && definition.version) {
			return `${definition.name}-v${definition.version}`;
		}

		return null;
	}

	static initialiseField(fieldType, defaultValue = null) {
		let type;

		if(defaultValue !== null) {
			return defaultValue;
		}

		switch(Definition.getFieldType(fieldType)) {
			case 'string':
				type = '';
				break;
			case 'boolean':
				type = false;
				break;
			case 'array':
				type = [];
				break;
			case '*':
			default:
				type = null;
		}

		return type;
	}

	static getFieldType(type = false) {

		if(type && Definition.typeMap[type]) {
			return Definition.typeMap[type];
		}

		return null;
	}

	static fillBlockFields(block) {
		const type = Definition.getType({
			name   : block.definition_name,
			version: block.definition_version
		});

		if(type && Definition.get(type)) {

			Definition.get(type).fields.forEach(field => {

				if(block.fields[field.name] === void 0) {
					const value = Definition.initialiseField(
						field.type || 'text', field.default
					);

					block.fields[field.name] = value;
				}

			});

		}
	}

	static transformValidation(field) {
		const validation = field.validation || [];
		let rules = [];

		validation.forEach(rule => {
			rules.push(Definition.transformValidationRule(rule, field));
		});

		if(!rules.length) {
			return {};
		}

		return rules;
	}

	static transformValidationRule(validationRule, { type }) {
		let tranformedRule;

		let [rule, value] = validationRule.split(':');

		if(
			[
				'min_value', 'max_value',
				'min_length', 'max_length',
				'length'
			].indexOf(rule) !== -1
		) {
			value = parseFloat(value, 2);
		}

		switch(rule) {
			case 'required':
			case 'present':
				tranformedRule = {
					required: true,
					message: 'This field is required.'
				};
				break;

			case 'string':
				tranformedRule = { type: 'string' };
				break;

			case 'integer':
				tranformedRule = { type: 'integer' };
				break;

			// These are possible client-side but don't currently
			// have a corresponding implementation server-side.

			// case 'number':
			// 	tranformedRule = { type: 'number' };
			// 	break;

			// case 'boolean':
			// 	tranformedRule = { type: 'boolean' };
			// 	break;

			// case 'float':
			// 	tranformedRule = { type: 'float' };
			// 	break;

			// case 'array':
			// 	tranformedRule = { type: 'array' };
			// 	break;

			// case 'object':
			// 	tranformedRule = { type: 'object' };
			// 	break;

			case 'min_value':
			case 'min_length':
				tranformedRule = { min: value };
				break;

			case 'max_value':
			case 'max_length':
				tranformedRule = { max: value };
				break;

			case 'length':
				tranformedRule = { len: value };
				break;

			case 'regex':
				tranformedRule = { regexp: value };
				break;

			case 'in':
				tranformedRule = { type: 'enum', enum: value.split(',') };
				break;
		}

		// only infer type validation if it's not explicitly defined
		if(!tranformedRule.type) {
			let fieldType = Definition.getFieldType(type);

			if(fieldType && fieldType !== '*') {
				tranformedRule = { ...tranformedRule, type: fieldType }
			}
		}

		return tranformedRule;
	}

	static getRules(definition) {
		let rules = {};

		definition.fields.forEach(field => {
			rules[field.name] = Definition.transformValidation(field);
		});

		return rules;
	}

}
