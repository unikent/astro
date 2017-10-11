import { expect } from 'chai'
import  permissions  from 'store/modules/permissions'


describe('Store Permissions', () => {
    it('userCan', () => {
        // mock the state
        const state = {
            permissions: {
                'createSubsite': ['admin', 'site owner'],
                'editSubsite': ['admin', 'site owner',	'editor', 'contributor'],
                'deleteSubsite': ['admin', 'site owner'],
                'editMenu': ['admin', 'site owner',	'editor'],
                'moveSubsite': ['admin', 'site owner',	'editor'],
            },

            role: 'admin',
        }
        
        userCan =  permissions.userCan(state);
        result = userCan('admin');
        console.log(result);
    })

    // it('helloWorld', () => {
    //     const result = getters.helloWorld(state);
    // });
});