import _ from 'lodash';
import api from 'plugins/http/api';
import Vue from 'vue';
import { notify, pageHasBeenPublished } from 'classes/helpers';

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
 * @property {Page} homepage - Representation of the Page which is the home page for this site. May be null if data not loaded.
 */

/**
 * Maintains the state of the Site being edited, including available Layouts and site page hierarchy.
 * @example <caption>Access site data from within a component.</caption>
 * this.$store.site.layouts; // get the layouts
 *
 * @namespace state/site
 *
 * @property {Array} pages - Array of Pages in the current Site.
 * @property {number} site - ID of the current Site.
 * @property {Site} siteData - Representation of the currently selected Site in the editor, as returned by the API.
 * @property {Object} editPageModal - Configuration for the edit page modal dialog.
 * @property {boolean} editPageModal.visible - Whether the modal is visible or not.
 * @property {string} editPageModal.title - The page title field value for the modal.
 * @property {string} editPageModal.slug - The page slug field value for the modal.
 * @property {number} editPageModal.id - The id of the page who's settings are being edited.
 * @property {boolean} editPageModal.editSlug - Whether or not to allow editing of the page slug in the modal (root pages cannot have their slug changed).
 */
const state = {
	pages: [],
	site: 1,
	siteData: null,
	layouts: [],
	siteDefinitions: {},
	maxDepth: 3,
	pageModal: {
		visible: false,
		parentId: null
	},
	editPageModal: {
		visible: false,
		title: '',
		slug: '',
		id: 0,
		editSlug: false
	},
	copyPageModal: {
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
		if(state.siteData && state.siteData.id !== id) {
			state.siteData = null;
		}
	},

	/**
	 * Set the site data that represents the site
	 * @param state
	 * @param data
	 */
	setSiteData(state, data) {
		// we ignore data if it doesn't match the current site id stored in state.site
		// in case site has changed since we requested the data...
		if(!state.siteData || !data || data.id === state.site) {
			state.siteData = data;
		}
	},

	setSite(state, pages) {
		state.pages = pages;
	},

	setLayouts(state, layouts) {
		state.layouts = layouts;
	},

	/**
	 * Sets the available site template definitions that can be selected from when creating a site.
	 * @param state
	 * @param {Object} definitions - { <name>-v<version>: {SiteDefinition}, ... }
	 */
	setSiteDefinitions(state, definitions) {
		state.siteDefinitions = definitions;
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

	setCopyPageModalVisibility(state, visible) {
		state.copyPageModal.visible = visible;
	},

	setEditPageModalParent(state, pageId) {
		state.editPageModal.parentId = pageId;
	},

	/**
	 * Sets the title, slug and id for the edit page settings modal.
	 *
	 * @param state
	 * @param {Object} page The Page to use data from.
	 */
	setPageMeta(state, page) {
		state.editPageModal.title = page.title;
		state.editPageModal.slug = page.slug;
		state.editPageModal.id = page.id;
		state.editPageModal.editSlug = !!page.parent_id;
	},

	/**
	 * Sets the title, slug and id for the copy page modal.
	 *
	 * @param state
	 * @param {Object} page The Page to use data from.
	 */
	setCopyPageMeta(state, page) {
		state.copyPageModal.title = page.title;
		state.copyPageModal.slug = page.slug;
		state.copyPageModal.id = page.id;
	},

	/**
	 * Mutates a page title, both in the pages list and in the editor if it is the page being edited.
	 *
	 * @param state
	 * @param {string} title - The new title.
	 */
	setPageTitleInPagesList(state, { id, title }) {
		const page = findPageById(state.pages, id);

		if(page) {
			page.title = title;
		}
	},

	/**
	 * Updates the slug for a page and also updates its path.
	 *
	 * @param state
	 * @param {string} slug - The new slug.
	 * @param {Object} page - Page data object.
	 *
	 */
	setPageSlugAndPathsInPagesList(state, { id, slug }) {
		const page = findPageById(state.pages, id);

		if(page) {
			// get the parent's path before setting the slug
			// as the old slug is used for substr
			const parentPath = page.path.substr(
				0,
				page.path.lastIndexOf(page.slug)
			);

			updatePaths(page, parentPath + slug);

			page.slug = slug;
		}
	},

	/**
	 * Updates the status for a page.
	 *
	 * @param state
	 * @param {string} arrayPath - The array path to the page in the page list
	 * eg. "0.0.1" for pagelist[0][0][1]
	 * @param {string} id - The page id.
	 * @param {string} status - The new status.
	 *
	 */
	setPageStatusInPagesList(state, { arrayPath, id, status }) {
		let page;

		if(arrayPath) {
			page = getPage(state.pages, arrayPath);
		}
		else if(id) {
			page = findPageById(state.pages, id);
		}

		if(page) {
			// propagate "new" status to all child pages.
			if(status === 'new') {
				updateStatuses(page, status);
			}
			else {
				page.status = status;
			}
		}
	}
};

const responseError = response => {
	console.warn('request failed');
};

const actions = {

	/**
	 * load the data that represents the current site
	 * @param commit
	 * @param state
	 * @param bool refresh - Whether or not to refresh the site data if it is already loaded. Defaults to false.
	 * @returns {Promise.<T>}
	 */
	loadSiteData({ commit, state}, refresh = false) {
		if(refresh || !state.siteData || (state.siteData.id !== state.site)) {
			return api
				.get(`sites/${state.site}`)
				.then((response) => {
					commit('setSiteData', response.data.data);
				});
		}
		else {
			return Promise.resolve(state.sitedata);
		}
	},

	fetchSite({ commit, state }) {
		return api
			.get(`sites/${state.site}/tree?include=revision`)
			.then((response) => {
				commit('setSite', [response.data.data]);
			})
			.catch(responseError);
	},

	/**
	 * Initialise the list of layouts available for use.
	 * @param commit
	 */
	fetchLayouts({ commit }) {
		api
			.get('layouts/definitions')
			.then((response) => {
				commit('setLayouts', response.data.data)
			})
			.catch(responseError);
	},

	/**
	 * Initialise the list of site definitions available for use.
	 * @param commit
	 */
	fetchSiteDefinitions({ commit }) {
		api
			.get('sitedefinitions')
			.then((response) => {
				commit('setSiteDefinitions', response.data.data)
			})
			.catch(responseError);
	},

	deletePage({ dispatch }, page) {
		api
			.delete(`pages/${page.id}`)
			.then(() => {
				dispatch('fetchSite');
			})
			.catch(responseError);
	},

	createPage({ dispatch }, page) {
		api
			.post('pages', {
				/* eslint-disable camelcase */
				parent_id: page.parent_id,
				/* eslint-enable camelcase */
				slug: page.slug,
				layout: {
					name: page.layout.name,
					version: page.layout.version,
				},
				title: page.title
			})
			.then((response) => {
				page = response.data.data;
				dispatch('fetchSite');
			})
			.catch(() => {
				vue.$notify({
					title: 'Page not added',
					message: 'Please ensure that there is not already a page with the same slug',
					type: 'error',
					duration: 0
				});
			})
	},

	copyPage({ dispatch }, data) {
		api
			.post(`pages/${data.id}/copy`, {
				/* eslint-enable camelcase */
				new_title: data.title,
				new_slug: data.slug
			})
			.then((response) => {
				dispatch('fetchSite');
				vue.$message({
					message: 'Page successfully copied.',
					type: 'success',
					duration: 2000
				});
			})
			.catch(() => {
				vue.$notify({
					title: 'Page not copied',
					message: 'Please ensure that there is not already a page with the same slug',
					type: 'error',
					duration: 0
				});
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
			.catch(responseError);
	},

	/**
	 * Updates the Page meta details for the specified Page.
	 *
	 * @param dispatch
	 * @param page
	 * @returns {Promise<R>|Promise.<TResult>|Promise<R2|R1>}
	 */
	updatePageMeta({ dispatch }, page) {
		return api
			.put(`pages/${page.id}`, {
				title: page.title,
				options: {}
			})
			.then((response) => {
				dispatch('setPageTitleGlobally', response.data.data, { root: true });
			})
			.then(() => {
				if(state.editPageModal.editSlug) {
					return api.put(`pages/${page.id}/slug`, {
						slug: page.slug
					})
					.then((response) => {
						dispatch(
							'setPageSlugAndPathGlobally',
							{ ...response.data.data, id: page.id },
							{ root: true }
						);
					})
					.catch(responseError);
				}
			}).catch((err) => {
				throw(err.response);
			});
	},

	movePageApi({ dispatch }, move) {
		// If-Unmodified-Since
		return api
			.patch(`sites/${state.site}/tree`, move)
			.then(() => {
				dispatch('fetchSite');
			})
			.catch(responseError);
	},

	movePage({ dispatch, commit, state }, { toPath, fromPath }) {
		const
			newLocation = getLocationInfo(toPath),
			oldLocation = getLocationInfo(fromPath),
			withinDepthLimit = newLocation.parent.depth + getDepth(oldLocation.page) <= state.maxDepth;

		// don't allow moving published pages beneath unpublished parents
		if(pageHasBeenPublished(oldLocation.page) && newLocation.parent.status === 'new') {
			vue.$snackbar.open({
				message: `
					Unable to drop page(s) here.
					Published pages can't be moved under unpublished pages.
				`
			});
			return;
		}

		if(withinDepthLimit) {

			const
				page = _.cloneDeep(oldLocation.page),
				pagesListClone = _.cloneDeep(state.pages),
				newPagePath = (
					newLocation.parent.path.replace(/\/$/, '') +
					'/' +
					page.slug
				);

			// remove old page
			commit('removePage', oldLocation);

			// if it's the currently selected page, update the its path
			commit(
				'setPagePath',
				{ id: page.id, path: newPagePath },
				{ root: true }
			);

			// update current and child page depths
			updateDepths(page, newLocation.parent.depth + 1);

			// update current and child page paths
			updatePaths(page, newPagePath);

			// splice page in if a page already exists in new location otherwise add it
			commit('addPage', { ...newLocation, page, push: !newLocation.page });

			const
				newPos = newLocation.index + 1,
				nextSiblingId = (
					newPos < newLocation.parent.children.length ?
						newLocation.parent.children[newPos].id : null
				);

			dispatch('movePageApi', {
				/* eslint-disable camelcase */
				page_id: page.id,
				parent_id: newLocation.parent.id,
				next_id: nextSiblingId
				/* eslint-enable camelcase */
			})
			.catch(() => {
				// restore the page list to previous state
				commit('setSite', pagesListClone);

				// TODO: Update error message based on the error type
				notify({
					title: 'Unable to move page',
					type: 'error'
				});
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
	},

	showCopyPageModal({ commit }, page) {
		commit('setCopyPageMeta', page);
		commit('setCopyPageModalVisibility', true);
	},

	hideCopyPageModal({ commit }) {
		commit('setCopyPageModalVisibility', false);
	}
};

const getters = {

	/**
     * Get a Page referenced by either its id or array path ( eg. 0.3.1 )
	 * @param state
	 */
	getPage: (state) => ({ arrayPath, id }) => {
		if(arrayPath === null || id === null) {
			return null;
		}
		else if(arrayPath) {
			return getPage(state.pages, arrayPath);
		}
		return findPageById(state.pages, id);
	},
};

const
	getLocationInfo = (path) => {
		return {
			page: getPage(state.pages, path),
			parent: getPage(state.pages, path.substr(0, path.lastIndexOf('.'))),
			index: Number.parseInt(path.substr(path.lastIndexOf('.') + 1, path.length))
		}
	},

	/**
	 * Get the Page matching the specified array path.(e.g. 0.3.1)
	 * @param {Array} page - Array of Pages.
	 * @param {string|Array} arrayPath - Path representing the page in the hierarchy or children arrays, eg. 0.3.2 for
	 * page[0].children[3].children[2]
	 */
	getPage = (page, arrayPath) => {
		const path = Array.isArray(arrayPath) ? arrayPath : arrayPath.split('.');

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

	/**
	 * Finds and returns the page with the specified id if it is present in the pages tree.
	 *
	 * @param {Array} pages - Array of Pages to search.
	 * @param {number} id - The page id to search for.
	 * @returns {Object|null}
	 */
	findPageById = (pages, id) => {
		for(var i in pages) {
			let page = pages[i];
			if(page.id === Number.parseInt(id)) {
				return page;
			}
			if(page.children && page.children.length) {
				let result = findPageById(page.children, id);
				if(result) {
					return result;
				}
			}
		}
		return null;
	},

	updateDepths = (currentPage, depth) => {
		updatePageAndSubPages(
			currentPage, 'depth', depth, ({ value }) => value + 1
		);
	},

	updatePaths = (currentPage, path) => {
		updatePageAndSubPages(
			currentPage, 'path', path,
			({ page, value }) => value + '/' + page.slug
		);
	},

	updateStatuses = (currentPage, status) => {
		updatePageAndSubPages(currentPage, 'status', status);
	},

	/**
	 * Update a property of a page in the pages list and do the same for all
	 * its children, based on a transform callback (by default it just updates
	 * all children's properties to the same value).
	 *
	 * @param      {object}    currentPage  The current page we're updating.
	 * @param      {string}    key          The key of the property to update.
	 * @param      {*}         value        The value to update the property to.
	 * @param      {Function}  transform    The callback to run for modifying our value after each iteration.
	 */
	updatePageAndSubPages = (
		currentPage,
		key,
		value,
		transform = ({ value }) => value
	) => {
		currentPage[key] = value;

		if(currentPage.children && currentPage.children.length) {
			currentPage.children.forEach(
				page => updatePageAndSubPages(page, key, transform({ page, value }), transform)
			);
		}
	};

export default {
	namespaced: true,
	state,
	mutations,
	actions,
	getters
};
