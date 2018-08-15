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

	it('Should treat html entities in "max_length_without_html" as single characters', () => {
		const
			entities = [
				// Printable ASCII (it seems &#32; or the space character has a length of 0 in DOMParser)
				'#33', '#34', '#35', '#36', '#37', 'amp', '#39', '#40', '#41', '#42', '#43', '#44', '#45', '#46', '#47', '#48', '#49', '#50', '#51', '#52', '#53', '#54', '#55', '#56', '#57', '#58', '#59', 'lt', '#61', 'gt', '#63', '#64', '#65', '#66', '#67', '#68', '#69', '#70', '#71', '#72', '#73', '#74', '#75', '#76', '#77', '#78', '#79', '#80', '#81', '#82', '#83', '#84', '#85', '#86', '#87', '#88', '#89', '#90', '#91', '#92', '#93', '#94', '#95', '#96', '#97', '#98', '#99', '#100', '#101', '#102', '#103', '#104', '#105', '#106', '#107', '#108', '#109', '#110', '#111', '#112', '#113', '#114', '#115', '#116', '#117', '#118', '#119', '#120', '#121', '#122', '#123', '#124', '#125', '#126',
				// ISO-8859-1
				'Agrave', 'Aacute', 'Acirc', 'Atilde', 'Auml', 'Aring', 'AElig', 'Ccedil', 'Egrave', 'Eacute', 'Ecirc', 'Euml', 'Igrave', 'Iacute', 'Icirc', 'Iuml', 'ETH', 'Ntilde', 'Ograve', 'Oacute', 'Ocirc', 'Otilde', 'Ouml', 'Oslash', 'Ugrave', 'Uacute', 'Ucirc', 'Uuml', 'Yacute', 'THORN', 'szlig', 'agrave', 'aacute', 'acirc', 'atilde', 'auml', 'aring', 'aelig', 'ccedil', 'egrave', 'eacute', 'ecirc', 'euml', 'igrave', 'iacute', 'icirc', 'iuml', 'eth', 'ntilde', 'ograve', 'oacute', 'ocirc', 'otilde', 'ouml', 'oslash', 'ugrave', 'uacute', 'ucirc', 'uuml', 'yacute', 'thorn', 'yuml',
				// ISO-8859-1 Symbols
				'nbsp', 'iexcl', 'cent', 'pound', 'curren', 'yen', 'brvbar', 'sect', 'uml', 'copy', 'ordf', 'laquo', 'not', 'shy', 'reg', 'macr', 'deg', 'plusmn', 'sup2', 'sup3', 'acute', 'micro', 'para', 'cedil', 'sup1', 'ordm', 'raquo', 'frac14', 'frac12', 'frac34', 'iquest', 'times', 'divide',
				// Math
				'forall', 'part', 'exist', 'empty', 'nabla', 'isin', 'notin', 'ni', 'prod', 'sum', 'minus', 'lowast', 'radic', 'prop', 'infin', 'ang', 'and', 'or', 'cap', 'cup', 'int', 'there4', 'sim', 'cong', 'asymp', 'ne', 'equiv', 'le', 'ge', 'sub', 'sup', 'nsub', 'sube', 'supe', 'oplus', 'otimes', 'perp', 'sdot',
				// Greek
				'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa', 'Lambda', 'Mu', 'Nu', 'Xi', 'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'Upsilon', 'Phi', 'Chi', 'Psi', 'Omega', 'alpha', 'beta', 'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'theta', 'iota', 'kappa', 'lambda', 'mu', 'nu', 'xi', 'omicron', 'pi', 'rho', 'sigmaf', 'sigma', 'tau', 'upsilon', 'phi', 'chi', 'psi', 'omega', 'thetasym', 'upsih', 'piv',
				// Misc
				'OElig', 'oelig', 'Scaron', 'scaron', 'Yuml', 'fnof', 'circ', 'tilde', 'ensp', 'emsp', 'thinsp', 'zwnj', 'zwj', 'lrm', 'rlm', 'ndash', 'mdash', 'lsquo', 'rsquo', 'sbquo', 'ldquo', 'rdquo', 'bdquo', 'dagger', 'Dagger', 'bull', 'hellip', 'permil', 'prime', 'Prime', 'lsaquo', 'rsaquo', 'oline', 'euro', 'trade', 'larr', 'uarr', 'rarr', 'darr', 'harr', 'crarr', 'lceil', 'rceil', 'lfloor', 'rfloor', 'loz', 'spades', 'clubs', 'hearts', 'diams'
			],
			input = { 'test': `&${entities.join(';&')};` },
			successful = new Schema({
				'test' : Validation.transform('max_length_without_html:' + entities.length)
			}),
			failing = new Schema({
				'test' : Validation.transform('max_length_without_html:' + (entities.length - 1))
			});

		successful.validate(
			input,
			errors => {
				expect(errors).to.be.null;
			}
		);

		failing.validate(
			input,
			errors => {
				expect(errors).to.not.be.null;
			}
		);
	});

});
