import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';
import shareMutations from '../plugins/share-mutations';
import shareTimeTravel from '../plugins/share-time-travel';
import page from './modules/page';
import site from './modules/site';
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
		displayIframeOverlay: false,
		errors: [],
		undoRedo: {
			canUndo: false,
			canRedo: false
		},
		sidebarCollapsed: false
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

		showIframeOverlay(state, show = true) {
			state.displayIframeOverlay = show;
		},

		updateErrors(state, errors) {
			state.errors = errors;
		},

		updateUndoRedo(state, canUndoRedo) {
			state.undoRedo = canUndoRedo;
		},

		collapseSidebar(state) {
			state.sidebarCollapsed = true;
		},

		revealSidebar(state) {
			state.sidebarCollapsed = false;
		}
	},

	actions: {},

	modules: {
		page,
		definition,
		site
	},

	plugins: [
		shareMutations,
		undoRedo,
		...(Config.get('debug', false) ? [shareTimeTravel] : [])
	],

	strict: process.env.NODE_ENV !== 'production'

});

export default store;
