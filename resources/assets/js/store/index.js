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
		sidebarCollapsed: false,
		blockPicker: {
			visible: false,
			insertIndex: 0,
			insertRegion: 'main'
		},
		currentView: 'desktop',
		publishModal: {
			visible: false
		},
		menu: {
			active: "pages"
		}
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
		},

		showBlockPicker(state) {
			state.blockPicker.visible = true;
		},

		hideBlockPicker(state) {
			state.blockPicker.visible = false;
		},

		updateInsertIndex(state, val) {
			state.blockPicker.insertIndex = val;
		},

		updateInsertRegion(state, val) {
			state.blockPicker.insertRegion = val;
		},

		showPublishModal(state) {
			state.publishModal.visible = true;
		},

		hidePublishModal(state) {
			state.publishModal.visible = false;
		},

		updateMenuActive(state, id) {
			state.menu.active = id;
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
