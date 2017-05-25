import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';
import shareMutations from '../plugins/share-mutations';
import shareDevTools from '../plugins/share-devtools';
import page from './modules/page';
import definition from './modules/definition';
import Config from 'classes/Config';

/* global process */

Vue.use(Vuex);

let store = new Vuex.Store({

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
		showIframeOverlay: false,
		errors: []
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
		},

		updateErrors(state, errors) {
			state.errors = errors;
		}
	},

	actions: {},

	modules: {
		page,
		definition
	},

	plugins: [
		shareMutations,
		undoRedo,
		...(Config.get('debug', false) ? [shareDevTools] : [])
	],

	strict: process.env.NODE_ENV !== 'production'

});

export default store;
