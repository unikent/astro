/**
 * tests for permissions part of the vuex store
 */
import { expect } from 'chai';
import permissions  from 'store/modules/permissions';



describe('Store Permissions - canUser', () => {
	var state;

	beforeEach(() => {
		state = {
			permissions : [
				{
					'name': 'Create Subsites',
					'slug': 'subsite.create',
					'roles': [
						'site.owner'
					]
				},
				{
					'name': 'Edit Subsites',
					'slug': 'subsite.edit',
					'roles': [
						'site.owner',
						'site.editor',
						'site.contributor'
					]
				},
				{
					'name': 'Delete Subsites',
					'slug': 'subsite.delete',
					'roles': [
						'site.owner'
					]
				}
			], 
	
			currentRole : '',
			globalRole: 'user'
		}
	});

	it('a normal user can do a thing they are permitted to by their role in the site', () => {
		let action = 'subsite.create';
		state.currentRole = 'site.owner';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(true);
	}),

	it('a normal user can not do a thing they are not permitted to by their role in the site', () => {  
		let action = 'subsite.create';
		state.currentRole = 'site.contributor';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(false);
	}),


	it('a normal user cannot do a thing which does not exist', () => {
		let action = 'subsite.thisdoesnotexist';
		state.currentRole = 'site.contributor';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(false);
	}),
		
	it('a normal user cannot do a thing if they have no role in the site', () => {
		let action = 'subsite.create';
		state.currentRole = null;
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(false);
	}),


	// admin override check 
	it('an admin user can do a thing they are permitted to by their role in the site', () => {
		let action = 'subsite.create';
		state.currentRole = 'site.owner';
		state.globalRole = 'admin';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(true);
	}),

	it('an admin user can do a thing they are not permitted to by their role in the site', () => {  
		let action = 'subsite.create';
		state.currentRole = 'site.contributor';
		state.globalRole = 'admin';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(true);
	}),


	it('an admin user can do a thing which does not exist', () => {
		let action = 'subsite.thisdoesnotexist';
		state.currentRole = 'site.contributor';
		state.globalRole = 'admin';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(true);
	}),
		
	it('an admin user can do a thing if they have no role in the site', () => {
		let action = 'subsite.create';
		state.currentRole = null;
		state.globalRole = 'admin';
		let result = permissions.getters.canUser(state)(action);
		expect(result).to.equal(true);
	})
});