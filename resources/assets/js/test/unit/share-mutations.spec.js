import Vue from 'vue';
import Vuex from 'vuex';
import _ from 'lodash';
import { expect } from 'chai';
import { iframeContext } from '../helpers';
import {
	shareMutationsMain,
	shareMutationsIframe
} from 'plugins/share-mutations';

Vue.use(Vuex);

/* global setTimeout */

describe('Share Mutations Plugin', () => {

	it('Syncing mutations', () => {

		const storeObject = {
			state: {
				a: 1
			},
			mutations: {
				test(state, n) {
					state.a += n;
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		store1.commit('test', 2);

		expect(store2.state.a).to.equal(3);

		store2.commit('test', 5);

		expect(store1.state.a).to.equal(8);

		store1.commit('test', 2);
		store2.commit('test', 2);

		expect(store1.state.a)
			.to.equal(store2.state.a)
			.to.equal(12);
	});

	it('Syncing mutations with object syntax', () => {

		const storeObject = {
			state: {
				a: 1
			},
			mutations: {
				test(state, payload) {
					state.a += payload.amount;
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		store1.commit({
			type: 'test',
			amount: 2
		});

		expect(store2.state.a).to.equal(3);

		store2.commit({
			type: 'test',
			amount: 5
		});

		expect(store1.state.a).to.equal(8);

		store1.commit({
			type: 'test',
			amount: 2
		});

		store2.commit({
			type: 'test',
			amount: 2
		});

		expect(store1.state.a)
			.to.equal(store2.state.a)
			.to.equal(12);
	});

	it('Syncing actions (sync & asyc)', () => {

		const storeObject = {
			state: {
				a: 1
			},
			mutations: {
				test(state, n) {
					state.a += n;
				}
			},
			actions: {
				testSync({ commit }, n) {
					commit('test', n)
				},
				testAsync({ commit }, n) {
					setTimeout(() => commit('test', n), 200);
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		store1.dispatch('testSync', 2);
		expect(store2.state.a).to.equal(3);

		store2.dispatch('testAsync', 2);

		setTimeout(() => expect(store1.state.a).to.equal(5), 200);
	});

	it('Syncing mutations after delayed iframe load', () => {

		const storeObject = {
			state: {
				a: 1
			},
			mutations: {
				test(state, n) {
					state.a += n;
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		store1.commit('test', 2);
		store1.commit('test', 2);
		store1.commit('test', 2);

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		expect(store2.state.a).to.equal(7);
	});

	it('Syncing mutations out of order', () => {

		const storeObject = {
			state: {
				a: 1
			},
			mutations: {
				test(state, n) {
					state.a += n;
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		for(var i = 1; i <= 100; i++) {

			if(i % 2 === 0) {
				store2.commit('test', 10);
			}
			else {
				store1.commit('test', 10);
			}

			if(i % 5 === 0) {
				setTimeout(() => {
					const store2a = store2.state.a;
					store1.commit('test', 10);
					expect(store2a + 10).to.equal(store1.state.a);
				}, 200);

				// periodically test states are equal
				expect(store1.state.a).to.equal(store2.state.a);
			}

			if(i % 10 === 0) {
				setTimeout(() => {
					const store1a = store1.state.a;
					store2.commit('test', 10);
					expect(store1a + 10).to.equal(store2.state.a);
				}, 300);
			}
		}

		setTimeout(() => {
			expect(store1.state.a)
				.to.equal(store2.state.a)
				.to.equal(1300)
		}, 300);
	});

	it('Getters retrieving state', () => {
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
				check ({ getters }, value) {
					// check for exposing getters into actions
					expect(getters.a).to.equal(value)
				}
			},
			plugins: [shareMutationsMain]
		};

		let
			store1 = new Vuex.Store(_.cloneDeep(storeObject)),
			store2;

		iframeContext(() => {
			storeObject.plugins = [shareMutationsIframe];
			store2 = new Vuex.Store(_.cloneDeep(storeObject));
		});

		expect(store1.getters.a).to.equal('none');
		store1.dispatch('check', 'none');

		store2.commit('test', 1);

		expect(store1.getters.a).to.equal('hasAny');
		store1.dispatch('check', 'hasAny');

		store1.commit('test', -1);

		expect(store2.getters.a).to.equal('none');
		store2.dispatch('check', 'none');
	});

});
