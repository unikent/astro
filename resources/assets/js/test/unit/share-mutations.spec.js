import Vue from 'vue';
import Vuex from 'vuex';
import _ from 'lodash';
import sinon from 'sinon';
import { expect } from 'chai';
import { iframeContext } from '../helpers';
import {
	shareMutationsMain,
	shareMutationsIframe
} from 'plugins/share-mutations';

Vue.use(Vuex);

/* global setTimeout */

describe('Share Mutations Plugin', () => {
	const storeObject = {
		state: {
			a: 0
		},
		getters: {
			a: state => state.a > 0 ? 'hasAny' : 'none'
		},
		mutations: {
			test(state, n) {
				state.a += n
			}
		},
		actions: {
			check({ getters }, value) {
				// check for exposing getters into actions
				expect(getters.a).to.equal(value)
			},
			testSync({ commit }, n) {
				commit('test', n)
			},
			testAsync({ commit }, n) {
				setTimeout(() => commit('test', n), 200);
			}
		}
	};

	let mainStore, iframeStore, clock;

	beforeEach(() => {
		storeObject.plugins = [shareMutationsMain];
		mainStore = new Vuex.Store(_.cloneDeep(storeObject));

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			iframeStore = new Vuex.Store(_.cloneDeep(storeObject));
		});
	});

	before(() => {
		clock = sinon.useFakeTimers();
	});

	after(() => {
		clock.restore();
	})

	it('Should sync mutations', () => {
		mainStore.commit('test', 2);

		expect(iframeStore.state.a).to.equal(2);

		iframeStore.commit('test', 6);

		expect(mainStore.state.a).to.equal(8);

		mainStore.commit('test', 2);
		iframeStore.commit('test', 2);

		expect(mainStore.state.a)
			.to.equal(iframeStore.state.a)
			.to.equal(12);
	});

	it('Should sync previous mutations after delayed iframe load', () => {
		iframeStore = null;

		for(var i = 0; i < 10; i++) {
			mainStore.commit('test', 2);
		}

		iframeContext(() => {
			iframeStore = new Vuex.Store(_.cloneDeep(storeObject));
		});

		expect(iframeStore.state.a).to.equal(20);
	});

	it('Should sync mutations over time, in "random" order', (done) => {
		let
			wait,
			fifthCounter = 0,
			tenthCounter = 0,
			waitTimes = {
				// random numbers between 0 - 500
				fifth: [
					208, 321, 291, 225, 14, 137, 88,
					418, 7, 249, 490, 76, 273, 101,
					269, 479, 484, 24, 490, 406
				],
				// random numbers between 0 - 1000
				tenth: [
					945, 265, 429, 547, 772, 172, 212, 591, 429, 614
				]
			};

		for(var i = 1; i <= 100; i++) {

			if(i % 2 === 0) {
				iframeStore.commit('test', 10);
			}
			else {
				mainStore.commit('test', 10);
			}

			if(i % 5 === 0) {
				wait = waitTimes.fifth[fifthCounter++];
				setTimeout(() => {
					mainStore.commit('test', wait);
					expect(mainStore.state.a).to.equal(iframeStore.state.a);
				}, wait);
			}

			if(i % 10 === 0) {
				wait = waitTimes.tenth[tenthCounter++];
				setTimeout(() => {
					iframeStore.commit('test', wait);
					expect(mainStore.state.a).to.equal(iframeStore.state.a);
				}, wait);
			}

			if(i === 100) {
				setTimeout(done, 1000);
			}

			expect(mainStore.state.a).to.equal(iframeStore.state.a);
		}

		clock.tick(1000);
	});

	it('Should sync actions (sync & asyc)', (done) => {
		mainStore.dispatch('testSync', 2);
		expect(iframeStore.state.a).to.equal(2);

		iframeStore.dispatch('testAsync', 2);

		setTimeout(() => {
			expect(mainStore.state.a).to.equal(4)
			done()
		}, 200);

		clock.tick(200);
	});

	it('Should not affect getters retrieving correct state', () => {
		expect(mainStore.getters.a).to.equal('none');
		mainStore.dispatch('check', 'none');

		iframeStore.commit('test', 1);

		expect(mainStore.getters.a).to.equal('hasAny');
		mainStore.dispatch('check', 'hasAny');

		mainStore.commit('test', -1);

		expect(iframeStore.getters.a).to.equal('none');
		iframeStore.dispatch('check', 'none');
	});

});
