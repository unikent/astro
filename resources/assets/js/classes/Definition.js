import Validation from './Validation';

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
		nested     : 'array',
		collection : 'array',
		group      : 'object'
	};

	static definitions = {};
	static rules = {};

	static set(definition) {
		const type = Definition.getType(definition);

		if(type === null) {
			return;
		}

		if(Definition.definitions[type] === void 0) {
			Definition.definitions[type] = definition;
			Definition.rules[type] = this.getRules(definition);
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

	static fillBlockFields(block, definition = null) {
		const type = Definition.getType({
			name   : block.definition_name,
			version: block.definition_version
		});

		if(type && (Definition.get(type) || definition)) {

			(Definition.get(type) || definition).fields.forEach(field => {

				if(block.fields[field.name] === void 0) {
					let value;

					if(field.type === 'collection') {
						const validation = Definition.transformValidation(field);
						let initialise = false;

						value = [];

						if(Array.isArray(validation)) {
							validation.some((rule) => {
								if(rule.min !== void 0) {
									initialise = rule.min;
									return true;
								}
							});
						}

						if(initialise) {

							for(var i = 0; i < initialise; i++) {
								value.push({});

								field.fields.forEach((collection) => {
									value[i][collection.name] = Definition.initialiseField(
										collection.type || 'text', collection.default
									);
								});
							}
						}
					}
					else if(field.type === 'group' || field.nested) {
						value = {};
						field.fields.forEach(nested => {
							value[nested.name] = Definition.initialiseField(
								nested.type || 'text', nested.default
							);
						});
					}
					else {
						value = Definition.initialiseField(
							field.type || 'text', field.default
						);
					}

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
			let ret = {};
			let fieldType = Definition.getFieldType(field.type);

			if(fieldType && fieldType !== '*') {
				ret.type = fieldType;
			}

			return ret;
		}

		return rules;
	}

	static transformValidationRule(validationRule, { type }) {
		let
			tranformedRule = {},
			[rule, value] = validationRule.split(':');

		tranformedRule = Validation.transform(rule, value);

		// only infer type validation if it's not explicitly defined
		if(tranformedRule.type === void 0) {
			let fieldType = Definition.getFieldType(type);

			if(fieldType && fieldType !== '*') {
				tranformedRule = { ...tranformedRule, type: fieldType }
			}
		}

		return tranformedRule;
	}

	static getRules(definition, includeNestedRules = true) {
		// const type = Definition.getType(definition);

		// if(Definition.rules[type]) {
		// 	return Definition.rules[type];
		// }

		let rules = {};

		definition.fields.forEach(field => {
			rules[field.name] = Definition.transformValidation(field);
			Definition.addNestedRules(field, rules, includeNestedRules);
		});

		return rules;
	}

	static addNestedRules(field, rules, includeNestedRules) {
		if(field.fields !== void 0 && ['collection', 'group'].indexOf(field.type) > -1) {
			let fields = {};

			if(includeNestedRules) {
				field.fields.forEach(nestedField => {
					fields[nestedField.name] = Definition.transformValidation(nestedField);
				});
			}

			if(Array.isArray(rules[field.name])) {
				rules[field.name].push({ type: rules[field.name][0].type, fields });
			}
			else {
				rules[field.name] = { ...rules[field.name], fields };
			}
		}
	}

}
