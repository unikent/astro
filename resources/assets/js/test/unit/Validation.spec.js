import { expect } from 'chai';
import Validation from 'classes/Validation';
import Schema from 'async-validator';

describe('Validation transformations', () => {

	const
		validationRules = {
			required: 'required',
			string: 'string',
			integer: 'integer',
			in: 'in:args',
			min_value: 'min_value:args',
			max_value: 'max_value:args',
			min_length: 'min_length:args',
			max_length: 'max_length:args',
			min: 'min:args',
			max: 'max:args',
			length: 'length:args',
			regex: 'regex:args',
			slug: 'slug',
			max_length_without_html: 'max_length_without_html:args'
		},
		args = {
			in: 'these,are,possible,values,3',
			min_value: '42',
			max_value: '42',
			min_length: '42',
			max_length: '42',
			min: '42',
			max: '42',
			length: '42',
			// Time format HH:MM 12-hour, optional leading 0
			regex: '^(?:0?[1-9]|1[0-2]):[0-5][0-9]$',
			max_length_without_html: '42'
		},
		expected = {
			required: { required: true, message: 'This field is required.' },
			string: { type: 'string' },
			integer: { type: 'integer' },
			in: { type: 'enum', enum: ['these', 'are', 'possible', 'values', 3] },
			min_value: { min: 42, message: 'This number must be more than 42.' },
			max_value: { max: 42, message: 'This number must be less than 42.' },
			min_length: { min: 42, message: 'This field must be at least 42 characters long.' },
			max_length: { max: 42, message: 'This field can\'t be more than 42 characters long.' },
			min: { min: 42, message: 'This field needs at least 42 items.' },
			max: { max: 42, message: 'This field can\'t have more than 42 items.' },
			length: { len: 42 },
			// these three custom validators need to be checked later
			regex: { regex:'^(?:0?[1-9]|1[0-2]):[0-5][0-9]$', message: 'The format of this field is invalid.' },
			slug: { message: 'This field should only have lowercase alphanumeric characters (a-z and 0-9). Separate keywords with dashes.' },
			max_length_without_html: { message: 'This field should not be more than 42 characters.' }
		},
		customValidators = [
			'regex',
			'slug',
			'max_length_without_html'
		],
		customInput = {
			'regex': {
				valid: '12:03',
				invalid: '13:67'
			},
			'slug': {
				valid: 'this-is-a-valid-slug',
				invalid: '-this_is_an invalid slug &'
			},
			'max_length_without_html': {
				valid: '<p>Valid string once the HTML is removed.</p>',
				invalid: '<p>This is an invalid string as it is above the 42 character limit set.</p>'
			}
		},
		transformRule = (type) => Validation.transform(
			args[type] ?
				validationRules[type].replace('args', args[type]) :
				validationRules[type]
		);

	Object.keys(validationRules).forEach(type => {
		it(`Should correctly transform the "${type}" validation rule`, () => {
			const checkType = customValidators.includes(type) ? 'include' : 'equal';

			expect(transformRule(type)).to.deep[checkType](expected[type]);
		});
	});

	customValidators.forEach(type => {
		it(`Should add a custom validator for "${type}" validation rules`, () => {
			const transformedRule = transformRule(type);
			expect(transformedRule.validator).to.be.a('function');
		});

		it(`Should create a "${type}" validator that validates correct input and rejects bad input`, () => {
			const validator = new Schema({
				[type]: transformRule(type)
			});

			validator.validate(
				{ [type]: customInput[type].valid },
				errors => {
					expect(errors).to.be.null;
				}
			);

			validator.validate(
				{ [type]: customInput[type].invalid },
				errors => {
					expect(errors).to.not.be.null;
				}
			);
		});
	});

});
