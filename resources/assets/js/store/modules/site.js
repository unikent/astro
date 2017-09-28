import _ from 'lodash';
import api from 'plugins/http/api';
import Vue from 'vue';

const vue = new Vue();

/**
 * Representation of a Site as returned by the API
 * @typedef {Object} Site
 * @property {number} id - Unique ID for this Site.
 * @property {string} name - The human-readable name for this Site.
 * @property {string} host - The domain name for this Site, eg. www.example.com/
 * @property {string} path - The path from the domain name for this site. Can be empty, or e.g. /sub/folder/names.
 * @property {string} created_at - The YYYY-MM-DD HH:MM:SS timestamp for when this site was created.
 * @property {string} updated_at - The YYYY-MM-DD HH:MM:SS timestamp for when this site was updated.
 * @property {string} created_by - The username of the user who created this Site.
 * @property {string} updated_by - The username of the user who last updated this Site.
 * @property {PublishingGroup} publishing_group - The Publishing Group this site is a member of. May be null if data not loaded.
 * @property {Page} homepage - Representation of the Page which is the home page for this site. May be null if data not loaded.
 */

/**
 * Store module containing site-level data.
 * @example <caption>Access site data from within a component.</caption>
 * this.$store.site.layouts; // get the layouts
 * @namespace state/site
 * @property {Array} pages - Array of Pages in the current Site.
 * @property {number} site - ID of the current Site.
 * @property {Site} currentSite - Representation of the currently selected Site in the editor, as returned by the API.
 */
const state = {
	pages: [],
	site: 1,
	layouts: [],
	maxDepth: 3,
	pageModal: {
		visible: false,
		parentId: null
	},
	editPageModal: {
		visible: false,
		title: '',
		slug: '',
		id: 0
	}
};

const mutations = {

	/**
	 * Set the current site id stored in the store.
	 * @function
	 * @memberof state/site#
	 * @param {object} state
	 * @param {int} id The id of the site to set as current site.
	 */
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
	},

	setEditPageModalVisibility(state, visible) {
		state.editPageModal.visible = visible;
	},

	setEditPageModalParent(state, pageId) {
		state.editPageModal.parentId = pageId;
	},

	/**
	 * Sets the title, slug and id for the edit page settings modal.
	 * @param state
	 * @param {Object} page The Page to use data from.
	 */
	setPageMeta(state, page) {
		state.editPageModal.title = page.title;
		state.editPageModal.slug = page.slug;
		state.editPageModal.id = page.id;
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

	updatePageMeta({ dispatch }, page) {
		api
			.put(`pages/${page.id}`, {
				title: page.title,
				options: {}
			})
			.then(() => {
				alert(title);
				dispatch('fetchSite');
			})
			// .then(() => {
			// 	api
			// 		.put(`pages/${page.id}/slug`, {
			// 			slug: page.slug
			// 		})
			// 		.then(() => {
			// 			dispatch('fetchSite');
			// 		});
			// })
			.catch(() => {});
	},

	movePageApi({ dispatch }, move) {
		// If-Unmodified-Since
		api
			.patch(`sites/${state.site}/tree`, move)
			.then(() => {
				dispatch('fetchSite');
			});
	},


	movePage({ dispatch, commit, state }, { toPath, fromPath }) {
		const
			newPage = getPageInfo(toPath),
			oldPage = getPageInfo(fromPath),
			canDrop = newPage.parent.depth + getDepth(oldPage.data) <= state.maxDepth,
			newPos = Number.parseInt(toPath.substr(toPath.lastIndexOf('.') + 1, toPath.length));

		if(canDrop) {

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
	},

	/**
	 * Display the edit page settings modal.
	 * @param commit
	 * @param {object} page - The Page object.
	 */
	showEditPageModal({ commit }, page) {
		commit('setPageMeta', page);
		commit('setEditPageModalVisibility', true);
	},

	hideEditPageModal({ commit }) {
		commit('setEditPageModalVisibility', false);
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