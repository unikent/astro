import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';

import page from './modules/page';
import definition from './modules/definition';

/* global window */

Vue.use(Vuex);

const store = (
	window.self === window.top ? {

		state: {
			over: {
				x: 0,
				y: 0
			},
			preview: {
				visible: false,
				url: ''
			},
			wrapperStyles: {},
			showIframeOverlay: false
		},

		getters: {},

		mutations: {

			updateOver(state, position) {
				state.over = position;
			},

			changePreview(state, value) {
				state.preview = value;
			},

			updateWrapperStyle(state, { prop, value }) {
				state.wrapperStyles = { ...state.wrapperStyles, [prop]: value };
			},

			showIframeOverlay(state, yes) {
				state.showIframeOverlay = yes;
			}
		},

		actions: {},

		modules: {
			page,
			definition
		},

		plugins: [undoRedo]

	}

	:

	window.top.store
);

window.store = store;

export default new Vuex.Store(store);
