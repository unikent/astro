import Vue from 'vue';
import Vuex from 'vuex';
import page from '../tests/stubs/page';
import undoRedo from './undo-redo';
import { Message } from 'element-ui';
import api from '../plugins/http/api';

const debug = process.env.NODE_ENV !== 'production';

Vue.use(Vuex);

const store = (
	window.self === window.top ? {

		state: {
			blockIndex: null,
			blockDef: null,
			blockList: null,
			page,
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
			meta: {
				blocks: []
			},
			baseUrl: window.Laravel.base
		},

		getters: {
			getFieldValue: (state, getters) => (index, name) => {
				return state.page.blocks[index].fields[name];
			},

			getCurrentFieldValue: (state, getters) => (name) => {
				return getters.getFieldValue(state.blockIndex, name);
			}
		},

		mutations: {
			SET_BLOCKLIST(state, list) {
				state.blockList = list;
			},

			EDIT_BLOCK(state, { index, type } = { index: null, type: null }) {
				state.blockIndex = state.blockIndex ? null : index;
				state.blockDef = state.blockDef ? null : state.blockList[type];
			},

			UPDATE_VALUE(state, { name, value, index }) {
				let idx = typeof index !== 'undefined' ? index : state.blockIndex;
				console.log(name, value, idx, state.blockIndex, typeof index);
				const block = state.page.blocks[idx];
				block.fields[name] = value;
				state.page.blocks.splice(idx, 1, block);
			},

			UPDATE_OVER(state, position) {
				state.over = position;
			},

			CHANGE_PAGE(state, name) {
				state.pageName = name;
			},

			CHANGE_PREVIEW(state, val) {
				state.preview = val;
			},

			REORDER_BLOCKS(state, { type, from, to, value }) {
				state.page.blocks.splice(from, 1);
				state.page.blocks.splice(to, 0, value);
			},

			UPDATE_BLOCK_DATA_ORDER(state, { type, from, to, value }) {
				state.blockInfo[type].splice(from, 1);
				state.blockInfo[type].splice(to, 0, value);
			},

			UPDATE_BLOCK_DATA(state, { type, index, value }) {
				state.blockInfo[type].splice(index, 1, value);
			}
		},

		actions: {
			fetchBlockList({ commit }) {
				api
					.get('definitions')
					.then((response) => {
						commit('SET_BLOCKLIST', response.data.data);
					});
			},

			editBlock({ commit }, data) {
				commit('EDIT_BLOCK', data);
			},

			updateValue({ commit }, data) {
				commit('UPDATE_VALUE', data);
			},

			updateOver({ commit }, data) {
				commit('UPDATE_OVER', data);
			},

			changePage({ commit }, data) {
				commit('CHANGE_PAGE', data);
			},

			changePreview({ commit }, data) {
				commit('CHANGE_PREVIEW', data);
			},

			updateBlockData({ commit }, data) {
				commit('UPDATE_BLOCK_DATA', data);
			},

			updateBlockDataOrder({ commit }, data) {
				commit('UPDATE_BLOCK_DATA_ORDER', data);
			},

			reorderBlocks({ commit }, data) {
				commit('REORDER_BLOCKS', data);
			}

			// TODO: add these actions?
			// addRow(row, index) // uuid.v1?
			// updateRows(index, row)
			// deleteRow(row, index)
			// sortRows(to, from)
		},

		plugins: [undoRedo]

	}

	:

	window.top.store
);

window.store = store;

export default new Vuex.Store(store);
