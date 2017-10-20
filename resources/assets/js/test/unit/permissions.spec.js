import {expect} from 'chai'
import Vue from 'vue';
import { mapState, mapGetters } from 'vuex';
// import Vuex from 'vuex';

// import * as WhatIsThis from 'store/modules/permissions'
import * as permissions from 'store/modules/permissions'

// Vue.use(Vuex);

describe('Store Permissions', () => {
    it('userCan can match permissions to a role', () => {
        // mock the state
        const state = {
            roles : [
                {
                    "name": "Create Subsites",
                    "slug": "subsite.create",
                    "roles": [
                        "site.owner"
                    ]
                },
                {
                    "name": "Edit Subsites",
                    "slug": "subsite.edit",
                    "roles": [
                        "site.owner",
                        "site.editor",
                        "site.contributor"
                    ]
                },
                {
                    "name": "Delete Subsites",
                    "slug": "subsite.delete",
                    "roles": [
                        "site.owner"
                    ]
                }
            ], 

            currentRole : 'Happy Camper'
        }
        
        const action = 'subsite.edit';
        const result = permissions.default.getters.userCan('subsite.create');
        // const result = getters.userCan(state, 'admin');
        console.log(result());
    })
});