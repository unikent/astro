/**
 * tests for permissions part of the vuex store
 */
import { expect } from 'chai';
import Vue from 'vue';
import { mapState, mapGetters } from 'vuex';
// import getters  from 'store/modules/permissions';
import permissions  from 'store/modules/permissions';


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

            currentRole : 'Happy Camper',
            globalRole: 'admin'
        }
        
        // jumping thru hoops to get the argument passing working here
        // better ideas/syntax welcome here :-)
        const result = permissions.getters.canUser(state)('subsite.create');
        console.log(result);
        // console.log(result);
    })
});