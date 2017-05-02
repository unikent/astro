import Vue from 'vue';
import Vuex from 'vuex';
import page from '../tests/stubs/page';
import definitions from '../tests/stubs/definitions.json';
import undoRedo from '../plugins/undo-redo';
import api from '../plugins/http/api';

/* global window, console */

Vue.use(Vuex);

const store = (
	window.self === window.top ? {

		state: {
			blockIndex: null,
			blockDef: null,
			blockList: null,
			region: 'main_content',
			page: {},
			over: {
				x: 0,
				y: 0
			},
			pageName: '',
			drag: {
				dragging: false,
				el: null,
				e: null
			},
			preview: {
				visible: false,
				url: ''
			},
			blockInfo: {
				sizes: [],
				offsets: [],
				moved: null
			},
			wrapperStyles: {},
			showIframeOverlay: false,
			pageScale: .4
		},

		getters: {
			getFieldValue: (state) => (index, name) => {
				return state.page.regions[state.region][index].fields[name];
			},

			getCurrentFieldValue: (state, getters) => (name) => {
				return getters.getFieldValue(state.blockIndex, name);
			}
		},

		mutations: {

			updateValue(state, { name, value, index }) {
				let idx = typeof index !== 'undefined' ? index : state.blockIndex;
				console.log(name, value, idx, state.blockIndex, typeof index);
				const block = state.page.regions[state.region][idx];
				block.fields[name] = value;
				state.page.regions[state.region].splice(idx, 1, block);
			},

			updateOver(state, position) {
				state.over = position;
			},

			changePage(state, name) {
				state.pageName = name;
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

			setPage(state, page) {
				state.page = page;
			},

			// block mutations

			setBlocklist(state, list) {
				state.blockList = list;
			},

			setBlock(state, { index, type } = { index: null, type: null }) {
				state.blockIndex = index;
				state.blockDef = state.blockList[type] || null;
			},

			reorderBlocks(state, { from, to, value }) {
				state.page.regions[state.region].splice(from, 1);
				state.page.regions[state.region].splice(to, 0, value);
			},

			updateBlockPositionsOrder(state, { type, from, to, value }) {
				state.blockInfo[type].splice(from, 1);
				state.blockInfo[type].splice(to, 0, value);
			},

			updateBlockPositions(state, { type, index, value }) {
				state.blockInfo[type].splice(index, 1, value);
			},

			// addBlock(state, { index, row }) {},
			// deleteBlock(state, { index, row }) {}
		},

		actions: {
			fetchPage({ commit }, id) {
				api
					.get(`page/${id}?include=blocks`)
					.then((response) => {
						commit('setPage', page);
					});
			},

			fetchBlockList({ commit }) {
				api
					.get('block/definitions')
					.then((response) => {
						// commit('setBlocklist', response.data.data);
					});

				let defs = {};

				Object.keys(definitions).forEach(
					name => {
						const def = definitions[name];

						if(def) {
							defs[`${def.name}-v${def.version}`] = def;
						}
					}
				);

				commit('setBlocklist', defs);
			}
		},

		modules: {},

		plugins: [undoRedo]

	}

	:

	window.top.store
);

window.store = store;

export default new Vuex.Store(store);
