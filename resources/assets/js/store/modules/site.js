import api from '../../plugins/http/api';

const state = {
	pages: [],
	site: 1,
	layout:[]
};

const getters ={};

const actions = {
	fetchSite({ commit, state }) {
		api
			.get(`site/${state.site}/tree`)
			.then((response) => {
				commit('setSite', response.data.data);
			});

	},

	fetchLayouts({commit}) {
		api
			.get('layout/definitions')
			.then((response) => {
				commit('setLayouts', response.data.data)
			})
	},

	deletePage({commit, state}, page){
		api
			.delete(`page/${page.id}`)
			.then((response) => {
				actions.fetchSite({commit,state})
			});
	},

	createPage({commit, state}, page){
		api
			.post('page', page)
			.then((response) => {
				actions.fetchSite({commit, state})
			})
	},

	updatePage({commit,state}, page){
		api
			.patch(`page/${page.page_id}`, page)
			.then((response) => {
				actions.fetchSite({commit,state})
			})
	},

	fakePage({commit, state}, page){
		commit('addPage', page)
	},

	removeFakePage({commit, state}){
		commit('removePage')
	}

};

const mutations = {
	setSite(state, pages) {
		state.pages = pages;
	},

	setLayouts(state, layout) {
		state.layout = layout
	},

	addPage(state, parent_page, page) {
		loopInsert(state.pages[0], parent_page)
	},

	removePage(state) {
		loopRemove(state.pages[0])
	}

};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations
};

function loopFind(target, id){

	if (target.id == id){
		return target;
	}
	else {
		for (var i = 0 ; i < target.children.length; i++) {
			var result = loopFind(target.children[i], id)

			if (result != false){
				return result
			}
		}
		return false
	}
}

function loopInsert(pages, parent_page) {
	if(pages.id == parent_page.id){
		pages.children.push({
			children:[],
			'depth':0,
			'id':12345,
			'is_canonical':true,
			'page_id':1,
			'parent_id':null,
			'path':'/',
			'site_id':1,
			'slug':null,
		})

	}
	else {
		for (var i = 0; i < pages.children.length; i++){
			loopInsert(pages.children[i], parent_page)
		}
	}

}

function loopRemove(pages) {
	pages = pages.children;
	for (var x = 0; x < pages.length; x++){
		for (var i = 0; i < pages[x].children.length; i++) {
			if(pages[x].children[i].id == 12345) {
				console.log(pages[x].children[i])
				pages[x].children.splice(i, 1);
			}
			else {
				loopRemove(pages[x].children[i])
			}
		}
	}

}