import { expect } from 'chai';
import sinon from 'sinon';

import * as helpers from 'classes/helpers';
import { iframeContext } from '../helpers';
import Config from 'classes/Config';

/* global console */
/* eslint-disable no-console */

describe('Helpers', () => {
	let sandbox = sinon.sandbox.create();

	describe('isIframe check', () => {

		it('Should return false if in top level window', () => {
			expect(helpers.isIframe).to.equal(false);
		});

		it('Should return true if in iframe', () => {
			iframeContext(() => {
				expect(helpers.inIframeContext()).to.equal(true);
			});
		});
	});

	describe('debug function', () => {

		it('Should log to console if debug is set to true', () => {
			sandbox.stub(console, 'info');
			Config.init({ debug: true });
			helpers.debug('debug info here');
			sinon.assert.calledOnce(console.info);
			sandbox.restore();
		});

		it('Should do nothing if debug is set to false', () => {
			sandbox.stub(console, 'info');
			Config.init({ debug: false });
			helpers.debug('debug info here');
			sinon.assert.notCalled(console.info);
			sandbox.restore();
		});
	});

	describe('Definition class export', () => {

		it('Should define shared definition class on window object', () => {
			expect(helpers.win.astroDefinition).to.exist;
		});
	});

});
