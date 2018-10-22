import Validation from './Validation';

export default class Definition {

	/**
	 * Mappings between field "types" and underlying data types.
	 */
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
		link       : 'string',
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

	/**
	 * Store/cache async-validator schemas for validating each block.
	 *
	 * @type {Schema}
	 */
	static validators = {};

	/**
	 * @type {{string}} Map region definition by name
	 */
	static regionDefinitions = {};

	/**
	 * Add a region definition indexed by name
	 * @param {Object} regionDefinition - The Region definition to add.
	 */
	static addRegionDefinition(regionDefinition) {
		Definition.regionDefinitions[Definition.getType(regionDefinition)] = regionDefinition;
	}

	/**
	 * Get a section definition by region name and index.
	 * @param {string} regionId - The name and version (type) of the region containing the section.
	 * @param {number} sectionIndex - The index of the section in the region.
	 * @returns {null|Object} - Section definition if found, otherwise null.
	 */
	static getRegionSectionDefinition(regionId, sectionIndex) {
		if(Definition.regionDefinitions[regionId] !== void 0) {
			if(sectionIndex < Definition.regionDefinitions[regionId].sections.length) {
				return Definition.regionDefinitions[regionId].sections[sectionIndex];
			}
		}
		return null;
	}

	static set(definition) {
		const type = Definition.getType(definition);

		if(type === null) {
			return;
		}

		if(Definition.definitions[type] === void 0) {
			Definition.definitions[type] = definition;
			Definition.rules[type] = this.getRules(definition);
			Definition.validators[type] = Validation.createSchema(
				Definition.rules[type]
			);
		}
	}

	static get(type) {
		if(type && Definition.definitions[type]) {
			return Definition.definitions[type];
		}

		return null;
	}

	static getType(definition) {
		if(definition && definition.name && definition.version) {
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

	static addFieldType(name, type) {
		if(type && !Definition.typeMap[type]) {
			Definition.typeMap[name] =  type;
		}
	}

	static fillFields(item, definition = null) {
		const matchingDefinition = Definition.get(
			Definition.getType({
				name   : item.definition_name,
				version: item.definition_version
			})
		) || definition;

		if(matchingDefinition) {

			matchingDefinition.fields.forEach(field => {

				if(item.fields[field.name] === void 0) {
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

					item.fields[field.name] = value;
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
			const type = Definition.getFieldType(field.type);

			if(type && type !== '*') {
				return { type };
			}
		}

		return rules;
	}

	static transformValidationRule(validationRule, { type }) {
		let tranformedRule = Validation.transform(validationRule);

		// only infer type validation if it's not explicitly defined
		if(tranformedRule.type === void 0) {
			let fieldType = Definition.getFieldType(type);

			if(fieldType && fieldType !== '*') {
				tranformedRule = { ...tranformedRule, type: fieldType };
			}
		}

		return tranformedRule;
	}

	static getRules(definition, includeNestedRules = true) {
		const type = Definition.getType(definition);

		if(!type) {
			return {};
		}

		// return cached definition if it exists
		if(Definition.rules[type]) {
			return Definition.rules[type];
		}

		let rules = {};

		definition.fields.forEach(field => {
			rules[field.name] = Definition.transformValidation(field);
			Definition.addNestedRules(field, rules, includeNestedRules);
		});

		return rules;
	}

	static getValidator(definition) {
		const type = Definition.getType(definition);

		if(Definition.validators[type]) {
			return Definition.validators[type];
		}

		return null;
	}

	static addNestedRules(field, rules, includeNestedRules) {
		if(field.fields !== void 0 && ['collection', 'group'].includes(field.type)) {
			let fields = {};

			field.fields.forEach(nestedField => {
				fields[nestedField.name] = Definition.transformValidation(nestedField);
			});

			if(Array.isArray(rules[field.name])) {
				rules[field.name].push({
					type: rules[field.name][0].type,
					...(
						includeNestedRules ?
							{
								defaultField: {
									type: 'object',
									fields
								}
							} :
							{ fields }
					)
				});
			}
			else {
				rules[field.name] = {
					...rules[field.name],
					...(
						includeNestedRules && field.type === 'collection' ?
							{
								defaultField: {
									type: 'object',
									fields
								}
							} :
							{ fields }
					)
				};
			}
		}
	}

}
