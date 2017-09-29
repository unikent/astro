import Vue from 'vue';
import Vuex from 'vuex';
import undoRedo from '../plugins/undo-redo';
import shareMutations from '../plugins/share-mutations';
import shareTimeTravel from '../plugins/share-time-travel';
import page from './modules/page';
import site from './modules/site';
import media from './modules/media';
import definition from './modules/definition';
import Config from 'classes/Config';


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
			insertRegion: 'main'
		},
		currentView: 'desktop',
		publishModal: {
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

		/**
		 * Mutates a page title, both in the pages list and in the editor if it is the page being edited.
		 * @param state
		 * @param {string} title - The new title.
		 */
		setPageTitle: function(state, { id, title} ) {
			if(state.page.pageData && state.page.pageData.id === id){
				state.page.pageData.title = title;
			}
			const pg = state.site.findPageById(state.site.pages, id);
			if(pg){
				pg.title = title;
			}
		},

		/**
		 * Mutates a page slug, both in the pages list and in the editor if it is the page being edited.
		 * As a side-effect of this, path must also be updated.
		 * @todo Cascade the updated path to all the subpages (this is done in the API, but we haven't reloaded the data).
		 * @param state
		 * @param {string} slug - The new slug.
		 */
		setPageSlug: function(state, { id, slug} ) {
			if(state.page.pageData && state.page.pageData.id === id){
				let path = state.page.pageData.path;
				path = path.substr(0, path.lastIndexOf(state.page.pageData.slug)) + slug;
				state.page.pageData.path = path;
				state.page.pageData.slug = slug;
			}
			const pg = state.site.findPageById(state.site.pages, id);
			if(pg){
				pg.slug = slug;
			}
		},

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

	actions: {},

	modules: {
		page,
		definition,
		site,
		media
	},

	plugins: [
		shareMutations,
		undoRedo,
		...(Config.get('debug', false) ? [shareTimeTravel] : [])
	],

	strict: process.env.NODE_ENV !== 'production'

});


export default store;
