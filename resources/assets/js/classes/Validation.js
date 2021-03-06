import Schema from 'async-validator';

/* global DOMParser */

export default class Validation {

	static messages = {
		'min':        (val) => `This field needs at least ${val} items.`,
		'max':        (val) => `This field can't have more than ${val} items.`,
		'min_value':  (val) => `This number must be more than ${val}.`,
		'max_value':  (val) => `This number must be less than ${val}.`,
		'min_length': (val) => `This field must be at least ${val} characters long.`,
		'max_length': (val) => `This field can't be more than ${val} characters long.`
	};

	static transform(validationRule, message = null) {
		let
			[rule, value] = validationRule.split(/:(.*)/, 2),
			tranformedRule = {};

		if(
			[
				'min_value', 'max_value',
				'min_length', 'max_length',
				'min', 'max', 'length'
			].indexOf(rule) !== -1
		) {
			value = Number.parseFloat(value, 2);
		}

		switch(rule) {
			case 'string':
				tranformedRule = { type: 'string' };
				break;

			case 'integer':
				tranformedRule = { type: 'integer' };
				break;

			case 'in':
				tranformedRule = {
					type: 'enum',
					enum: value.split(',').map(value => {
						const number = Number(value);
						return isNaN(number) ? value : number;
					})
				};
				break;

			case 'required':
			case 'present':
				tranformedRule = {
					required: true,
					message: 'This field is required.'
				};
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

			case 'min':
			case 'min_value':
			case 'min_length':
				tranformedRule = { min: value };
				break;

			case 'max':
			case 'max_value':
			case 'max_length':
				tranformedRule = { max: value };
				break;

			case 'length':
				tranformedRule = { len: value };
				break;

			case 'regex':
				tranformedRule = {
					regex: value,
					message: 'The format of this field is invalid.',

					validator(rule, value, cb) {
						if(
							value &&
							value.length &&
							!value.match(new RegExp(rule.regex))
						) {
							return cb(rule.message);
						}

						return cb();
					}
				};
				break;

			case 'slug':
				tranformedRule = {
					message: 'This field should only have lowercase alphanumeric characters (a-z and 0-9). Separate keywords with dashes.',

					validator(rule, value, cb) {
						if(
							value &&
							value.length &&
							!value.match(new RegExp('^[a-z0-9]+(?:-[a-z0-9]+)*$'))
						) {
							return cb(rule.message);
						}

						return cb();
					}
				};
				break;

			case 'max_length_without_html': {
				const maxLength = Number.parseFloat(value, 2);

				tranformedRule = {
					message: `This field should not be more than ${maxLength} characters.`,

					validator(rule, value, cb) {
						const html = new DOMParser().parseFromString(value || '', 'text/html');

						if(value && html.body.textContent.length > maxLength) {
							return cb(rule.message);
						}

						return cb();
					}
				};
				break;
			}
		}

		if(message) {
			tranformedRule.message = message;
		}
		else if(Validation.messages[rule]) {
			tranformedRule.message = Validation.messages[rule](value);
		}

		return tranformedRule;
	}

	static createSchema(rules) {
		return new Schema(rules);
	}

	static flattenRules(rules, parent = '') {
		let fields = [];

		Object.keys(rules).forEach(field => {
			if(rules[field] && rules[field].fields) {
				fields = [
					...fields,
					...Validation.flattenRules(rules[field].fields, `${field}.`)
				];
			}
			else if(Array.isArray(rules[field])) {
				fields.push(parent + field);
			}
		});

		return fields;
	}

}
