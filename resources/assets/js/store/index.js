import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';
import shareMutations from '../plugins/share-mutations';
import shareTimeTravel from '../plugins/share-time-travel';

import page from './modules/page';
import site from './modules/site';
import auth from './modules/auth';
import media from './modules/media';
import definition from './modules/definition';
import permissions from './modules/permissions';
import contenteditor from './modules/contenteditor';

import Config from 'classes/Config';
import { addThemeStoreModules } from 'helpers/themeExports';
import { Definition } from 'classes/helpers';

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
		errors: {
			blocks: {}
		},
		undoRedo: {
			canUndo: false,
			canRedo: false
		},
		blockPicker: {
			visible: false,
			insertIndex: 0,
			insertRegion: null,
			insertSection: null,
			allowedBlocks: null,	// the constraints on what blocks can be added
			deprecatedBlocks: null, // blocks to hide the block picker
			maxSelectableBlocks: null, // the maximum number of blocks that can be selected
			replaceBlocks: false //whether or not to replace the blocks in the current section
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
		},

	getters: {

		/**
		 * Remove any empty errors and format them in a way that can be used
		 * directly in the sidebar.
		 *
		 * @return     {array}  Our error messages in a nicer format.
		 */
		getAllBlockErrors(state) {
			let messages = {};

			Object.keys(state.errors.blocks).forEach(blockId => {
				const blockDefinition = Definition.get(state.errors.blocks[blockId].definitionName);

				Object.keys(state.errors.blocks[blockId].errors).forEach(fieldName => {
					if(state.errors.blocks[blockId].errors[fieldName].length) {
						if(!messages[blockId]) {
							messages[blockId] = {
								label: blockDefinition.label,
								errors: []
							};
						}

						const { label } = getFieldDefinition(
							blockDefinition,
							fieldName
						);

						messages[blockId].errors.push({
							label,
							field: fieldName
						});
					}
				})
			});

			return messages;
		},

		getAllBlockErrorsCount(state) {
			let count = 0;
			const blockErrors = state.errors.blocks;

			Object.keys(blockErrors).forEach(blockId => {
				Object
					.keys(blockErrors[blockId].errors)
					.forEach(fieldName => {
						if(Array.isArray(blockErrors[blockId].errors[fieldName])) {
							count += blockErrors[blockId].errors[fieldName].length;
						}
					});
			});

			return count;
		}
	},

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

		addBlockErrors(state, { block: { id: blockId }, blockInfo, definitionName, errors }) {
			const value = { definitionName, blockInfo, errors };

			if(state.errors.blocks[blockId]) {
				state.errors.blocks[blockId] = value;
			}
			else {
				state.errors.blocks = {
					...state.errors.blocks,
					[blockId]: value
				};
			}
		},

		resetFieldErrors(state, { blockId }) {
			if(state.errors.blocks[blockId]) {
				Object.keys(state.errors.blocks[blockId].errors).forEach(fieldName =>
					state.errors.blocks[blockId].errors[fieldName] = []
				);
			}
		},

		deleteBlockValidationIssue(state, blockId) {
			if(state.errors.blocks[`${blockId}`]) {
				Vue.delete(state.errors.blocks, `${blockId}`);
			}
		},

		clearBlockErrors(state) {
			state.errors.blocks = {};
		},

		addFieldError(state, { blockId, fieldName, errors }) {
			if(state.errors.blocks[blockId].errors[fieldName]) {
				state.errors.blocks[blockId].errors[fieldName] = errors;
			}
			else {
				state.errors.blocks[blockId].errors = {
					...state.errors.blocks[blockId].errors,
					[fieldName]: errors
				};
			}
		},

		updateBlockErrorIndex(state, { blockId, blockIndex }) {
			if(state.errors.blocks[blockId]) {
				state.errors.blocks[blockId].blockInfo.blockIndex = blockIndex;
			}
		},

		/**
		 * Display the block picker.
		 * @param state
		 * @param {string} regionName - The name of the region to add any blocks to.
		 * @param {number} sectionIndex - The index of the section within the region to add any blocks to.
		 * @param {number} insertIndex - The index within the section to add any blocks to.
		 * @param {Object} blocks - List of allowed block names.
		 * @param {Object} deprecatedBlocks - List of blocks to hide from picker.
		 * @param {number} maxSelectableBlocks - the maximum number of blocks that can be selected
		 * @param {boolean} replaceBlocks - Replace the blocks in the section with the new one
		 */
		showBlockPicker(state, { regionName, sectionIndex, insertIndex, blocks, deprecatedBlocks, maxSelectableBlocks, replaceBlocks }) {
			state.blockPicker.insertRegion = regionName;
			state.blockPicker.insertIndex = insertIndex;
			state.blockPicker.insertSection = sectionIndex;
			state.blockPicker.allowedBlocks = blocks;
			state.blockPicker.deprecatedBlocks = deprecatedBlocks;
			state.blockPicker.maxSelectableBlocks = maxSelectableBlocks;
			state.blockPicker.replaceBlocks = replaceBlocks
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

	modules: addThemeStoreModules({
		page,
		definition,
		contenteditor,
		site,
		media,
		permissions,
		auth
	}),

	plugins: [
		shareMutations,
		undoRedo,
		...(Config.get('debug', false) ? [shareTimeTravel] : [])
	],

	strict: process.env.NODE_ENV !== 'production'

});

const getFieldDefinition = (definition, fieldName) => {
	// Remove any digits from field name, to easily find our field definition
	const path = fieldName.replace(/\.\d+\./g, '.').split('.');

	for(var i = 0, length = path.length; definition && i < length; i++) {
		definition = definition.fields.find(field => field.name === path[i]);
	}

	return i && i === length ? definition : null;
};

export default store;
