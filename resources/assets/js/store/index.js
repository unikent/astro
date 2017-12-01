import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';
import shareMutations from '../plugins/share-mutations';
import shareTimeTravel from '../plugins/share-time-travel';

import page from './modules/page';
import site from './modules/site';
import media from './modules/media';
import permissions from './modules/permissions';
import definition from './modules/definition';
import contenteditor from './modules/contenteditor';

import Config from 'classes/Config';

/* global process */

Vue.use(Vuex);

let store = new Vuex.Store({

	/**
	 * The global editor state
	 * @namespace state
	 * @property {Object} over - What coordinates the mouse is over???
	 * @property {number} over.x - X Coordinate
	 * @property {number} over.y - Y Coordinate
	 * @property {Object} WrapperStyles - The styles for the wrapper.
	 */
	state: {
		over: {
			x: 0,
			y: 0
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
			insertRegion: null,
			insertSection: null,
			allowedBlocks: null,	// the constraints on what blocks can be added
			maxSelectableBlocks: null // the maximum number of blocks that can be selected 
		},
		currentView: 'desktop',
		publishModal: {
			pagePath: null,
			visible: false
		},
		unpublishModal: {
			pagePath: null,
			visible: false
		},
		publishValidationWarningModal: {
			visible: false
		},
		menu: {
			active: 'pages',
			flash: ''
		}
	},

	getters: {},

	mutations: {

		changeView(state, currentView) {
			state.currentView = currentView;
		},

		updateOver(state, position) {
			state.over = position;
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

		/**
		 * Display the block picker.
		 * @param state
		 * @param {string} regionName - The name of the region to add any blocks to.
		 * @param {number} sectionIndex - The index of the section within the region to add any blocks to.
		 * @param {number} insertIndex - The index within the section to add any blocks to.
		 * @param {Object} blocks - List of allowed block names.
		 * @param {number} maxSelectableBlocks - the maximum number of blocks that can be selected
		 */
		showBlockPicker(state, { regionName, sectionIndex, insertIndex, blocks, maxSelectableBlocks }) {
			state.blockPicker.insertRegion = regionName;
			state.blockPicker.insertIndex = insertIndex;
			state.blockPicker.insertSection = sectionIndex;
			state.blockPicker.allowedBlocks = blocks;
			state.blockPicker.maxSelectableBlocks = maxSelectableBlocks;
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

		showPublishModal(state, arrayPath) {
			if(arrayPath) {
				// update the page path in the store
				state.publishModal.pagePath = arrayPath;
			}
			state.publishModal.visible = true;
		},

		hidePublishModal(state) {
			// remove the page path in the store here
			state.publishModal.pagePath = null;
			state.publishModal.visible = false;
		},

		showUnpublishModal(state, arrayPath) {
			if(arrayPath) {
				// update the page path in the store
				state.unpublishModal.pagePath = arrayPath;
			}
			state.unpublishModal.visible = true;
		},

		hideUnpublishModal(state) {
			// remove the page path in the store here
			state.unpublishModal.pagePath = null;
			state.unpublishModal.visible = false;
		},

		showPublishValidationWarningModal(state) {
			state.publishValidationWarningModal.visible = true;
		},

		hidePublishValidationWarningModal(state) {
			state.publishValidationWarningModal.visible = false;
		},

		updateMenuActive(state, id) {
			state.menu.active = id;
		},

		updateMenuFlash(state, id) {
			state.menu.flash = id;
		}
	},

	actions: {

		/**
		 * Mutates a page title, both in the pages list and in the editor if it is the page being edited.
		 *
		 * @param context
		 * @param {string} id - The page id.
		 * @param {string} title - The new title.
		 */
		setPageTitleGlobally({ commit }, { id, title }) {
			commit('setPageTitle', { id, title }, { root: true });
			commit('site/setPageTitleInPagesList', { id, title });
		},

		/**
		 * Mutates a page slug, both in the pages list and in the editor if it is the page being edited.
		 * As a side-effect of this, path must also be updated.
		 *
		 * @param context
		 * @param {string} id - The page id.
		 * @param {string} slug - The new slug.
		 */
		setPageSlugAndPathGlobally({ commit }, { id, slug }) {
			commit('setPageSlugAndPath', { id, slug }, { root: true });
			commit('site/setPageSlugAndPathsInPagesList', { id, slug });
		},

		/**
		 * Mutates a page's status, both in the pages list and
		 * in the editor if it is the page being edited.
		 *
		 * @param context
		 * @param {string} id - The page id.
		 * @param {string} arrayPath - The array path to the page in the page list
		 * eg. "0.0.1" for pagelist[0][0][1]
		 * @param {string} status - The new status.
		 */
		setPageStatusGlobally({ commit, dispatch }, { id, arrayPath, status }) {
			// uses action as we need access to the getPage getter
			dispatch('setPageStatus', { id, arrayPath, status }, { root: true });
			commit('site/setPageStatusInPagesList', { id, arrayPath, status });
		}
	},

	modules: {
		page,
		definition,
		contenteditor,
		site,
		media,
		permissions
	},

	plugins: [
		shareMutations,
		undoRedo,
		...(Config.get('debug', false) ? [shareTimeTravel] : [])
	],

	strict: process.env.NODE_ENV !== 'production'

});


export default store;
