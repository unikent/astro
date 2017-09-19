import _ from 'lodash';
import api from 'plugins/http/api';
import Vue from 'vue';

const vue = new Vue();

const state = {
	pages: [],
	site: 1,
	layouts: [],
	maxDepth: 3,
	pageModal: {
		visible: false,
		parentId: null
	},
	sitePath: 'site',
	siteDomain: 'https://webtools-test.kent.ac.uk/'
};

const mutations = {

	updateCurrentSiteID(state, id) {
		state.site = id;
	},

	setSite(state, pages) {
		state.pages = pages;
	},

	setLayouts(state, layouts) {
		state.layouts = layouts;
	},

	addPage(state, { parent, index, page, push = false }) {
		if(push) {
			parent.children.push(page);
		}
		else {
			parent.children.splice(index, 0, page);
		}
	},

	removePage(state, { parent, index }) {
		parent.children.splice(index, 1);
	},

	updatePageDepth(state, { page, depth }) {
		page.depth = depth;
	},

	setPageModalVisibility(state, visible) {
		state.pageModal.visible = visible;
	},

	setPageModalParent(state, pageId) {
		state.pageModal.parentId = pageId;
	}
};

const actions = {

	fetchSite({ commit, state }) {
		api
			.get(`sites/${state.site}/tree?include=revision`)
			.then((response) => {
				commit('setSite', [response.data.data]);
			});
	},

	fetchLayouts({ commit }) {
		api
			.get('layouts/definitions')
			.then((response) => {
				commit('setLayouts', response.data.data)
			})
	},

	deletePage({ dispatch }, page) {
		api
			.delete(`pages/${page.id}`)
			.then(() => {
				dispatch('fetchSite');
			});
	},

	createPage({ dispatch }, page) {
		api
			.post('pages', {
				parent_id: page.route.parent_id,
				slug: page.route.slug,
				layout: {
					name: page.layout_name,
					version: page.layout_version,
				},
				title: page.title
			})
			.then((response) => {
				page.id = response.data.data.id;
				dispatch('updatePage', page);
			})
	},

	updatePage({ dispatch }, page) {
		api
            .put(`pages/${page.id}/content`, {
                blocks: page.blocks
            })
			.then(() => {
				dispatch('fetchSite');
			})
	},

	movePageApi({ dispatch }, move) {
		// If-Unmodified-Since
		api
		 	.patch(`sites/${state.site}/tree`, move)
		 	.then(() => {
		 		dispatch('fetchSite');
		 	});
		console.log(move);
	},


	movePage({ dispatch, commit, state }, { toPath, fromPath }) {
		const
			newPage = getPageInfo(toPath),
			oldPage = getPageInfo(fromPath),
			canDrop = newPage.parent.depth + getDepth(oldPage.data) <= state.maxDepth,
			newPos = Number.parseInt(toPath.substr(toPath.lastIndexOf('.') + 1, toPath.length));

		if(canDrop) {

			// Reordering logic is a bit different as we are not adding a new item into the parent collection,
			if(newPage.parent.id == oldPage.parent.id){

			}

			const page = _.cloneDeep(oldPage.data);

            // remove old page
			commit('removePage', oldPage);
			// update current and child page depths
			updateDepths(page, newPage.parent.depth + 1);
			// splice page in if page already exists in new position otherwise add it
			commit('addPage', { ...newPage, page, push: !newPage.data });
			const next_id = newPos+1 < newPage.parent.children.length ? newPage.parent.children[newPos+1].id : null;
			dispatch('movePageApi', {
				page_id: page.id,
				parent_id: newPage.parent.id,
				next_id: next_id //newPage.data && newPage.parent.children[newPos+1] ? newPage.parent.children[newPos+1].id : null
			});
		}
		else {
			vue.$snackbar.open({
				message: `
					Unable to drop page(s) here.
					The site structure must be less than ${state.maxDepth} levels deep.
				`
			});
		}
	},

	showPageModal({ commit }, { id }) {
		commit('setPageModalParent', id);
		commit('setPageModalVisibility', true);
	},

	hidePageModal({ commit }) {
		commit('setPageModalVisibility', false);
	}

};

const getters = {};

const
	getPageInfo = (path) => {
		return {
			data: getPage(state.pages, path),
			parent: getPage(state.pages, path.substr(0, path.lastIndexOf('.'))),
			index: Number.parseInt(path.substr(path.lastIndexOf('.') + 1, path.length))
		}
	},

	getPage = (page, fullPath) => {
		const path = Array.isArray(fullPath) ? fullPath : fullPath.split('.');

		for(var i = 0, length = path.length; page !== void 0 && i < length; i++) {
			page = i > 0 ? page.children[path[i]] : page[path[i]];
		}

		return i && i === length ? page : false;
	},

	getDepth = (page, fromPage = true) => {
		let depth = fromPage ? 1 : page.depth;

		while(page !== void 0 && page.children && page.children.length) {
			page = page.children[0];
			depth++;
		}

		return depth;
	},

	updateDepths = (currPage, depth) => {
		currPage.depth = depth;

		if(currPage.children && currPage.children.length) {
			currPage.children.forEach(page => updateDepths(page, depth + 1));
		}
	};

export default {
	namespaced: true,
	state,
	mutations,
	actions,
	getters
};
