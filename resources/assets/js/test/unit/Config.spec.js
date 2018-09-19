import { expect } from 'chai';

import Config from 'classes/Config';

describe('Config class', () => {
	/* eslint-disable camelcase */
	const sampleOptions = {
		csrf_token: 'CSRF_TOKEN',
		base_url  : '',
		api_url   : '/api/v1/',
		api_token : 'API_TOKEN',
		debug     : false
	};
	/* eslint-enable camelcase */

	afterEach(() => {
		// reset config for other tests
		Config.reset();
	});

	describe('#init()', () => {

		it('Should set options to provided object', () => {
			Config.init(sampleOptions);
			expect(Config.options).to.eql(sampleOptions);
		});

		it('Should ignore call if provided options aren\'t an object', () => {
			expect(() => Config.init(null)).to.throw(Error);
			expect(Config.options).to.eql({});

			expect(() => Config.init([])).to.throw(Error);
			expect(Config.options).to.eql({});

			expect(() => Config.init(true)).to.throw(Error);
			expect(Config.options).to.eql({});

			expect(() => Config.init(42)).to.throw(Error);
			expect(Config.options).to.eql({});

			expect(() => Config.init('wait, what?')).to.throw(Error);
			expect(Config.options).to.eql({});
		});
	});

	describe('#get()', () => {

		beforeEach(() => {
			Config.init(sampleOptions);
		});

		it('Should return value of requested config option', () => {
			expect(Config.get('csrf_token')).to.equal('CSRF_TOKEN');
			expect(Config.get('base_url')).to.equal('');
			expect(Config.get('api_url')).to.equal('/api/v1/');
			expect(Config.get('api_token')).to.equal('API_TOKEN');
			expect(Config.get('debug')).to.equal(false);
		});

		it('Should return null if requested config option isn\'t present', () => {
			expect(Config.get('should_not_exist')).to.equal(null);
			expect(Config.get('same_here')).to.equal(null);
			expect(Config.get('and_again')).to.equal(null);
		});

		it('Should return fallback if one is supplied', () => {
			expect(Config.get('should_not_exist', 'test1')).to.equal('test1');
			expect(Config.get('same_here', 'test2')).to.equal('test2');
			expect(Config.get('and_again', 'test3')).to.equal('test3');
		});
	});

	describe('#set()', () => {
		beforeEach(() => {
			Config.init(sampleOptions);
		});

		it('Should set an option', () => {
			Config.set('test1', 'test_value_1');
			expect(Config.get('test1', 'test_value_1')).to.equal('test_value_1');

			Config.set('test2', { hello : 'world' });
			expect(Config.get('test2', { hello : 'world' })).to.eql({ hello : 'world' });

			Config.set('test3', ['test', 'array', {}]);
			expect(Config.get('test3', ['test', 'array', {}])).to.eql(['test', 'array', {}]);

			Config.set('test4', false);
			expect(Config.get('test4', false)).to.equal(false);
		});

		it('Should return the value set', () => {
			expect(Config.set('test1', 'test_value_1')).to.equal(Config.get('test1'));
			expect(Config.set('test2', { hello : 'world' })).to.eql(Config.get('test2'));
			expect(Config.set('test3', ['test', 'array', {}])).to.eql(Config.get('test3'));
			expect(Config.set('test4', false)).to.equal(Config.get('test4'));
		});

		it('Should overwrite old options', () => {
			expect(Config.set('csrf_token', 'test1')).to.equal( 'test1');
			expect(Config.set('base_url', '/base')).to.equal('/base');
			expect(Config.set('api_url', '/api/')).to.equal('/api/');
			expect(Config.set('api_token', 'none')).to.equal('none');
			expect(Config.set('debug', true)).to.equal(true);
		});
	});

	describe('#remove()', () => {
		it('Should remove options', () => {
			Config.init(sampleOptions);

			[
				'csrf_token',
				'base_url',
				'api_url',
				'api_token',
				'debug',
			]
			.forEach((key) => {
				Config.remove(key);
				expect(Config.get(key)).to.equal(null);
			});

			expect(Config.options).to.eql({});
		});
	});

	describe('#reset()', () => {
		it('Should reset config', () => {
			expect(Config.reset());
			expect(Config.options).to.eql({});
		});
	});
});
