import { expect } from 'chai';
import { allowedOperations } from 'classes/SectionConstraints';

describe('Region section Constraints', () => {

	const
		fakeBlockNames = (num) => Array(num).fill(''),
		// allowed blocks is required so always pass it in
		fakeConstraints = (options) => ({
			allowedBlocks: ['fake-block-name-v1', 'fake-block-name-v1'],
			...options
		});

	describe('allowedOperations function', () => {

		it('Should return default operation constraints if invalid data is passed in', () => {
			const constraint = allowedOperations();

			expect(constraint).to.deep.equal({
				allowedBlocks: null,
				canAddBlocks: true,
				canRemoveBlocks: true,
				canSwapBlocks: false
			});
		});

		it('Should allow adding blocks if section block count is below minimum', () => {
			const constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ min: 4 })
			);
			expect(constraint).to.have.property('canAddBlocks', true);
		});

		it('Should disallow removing blocks if section block count is minimum or less', () => {
			let constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ min: 4 })

			);
			expect(constraint).to.have.property('canRemoveBlocks', false);
			constraint = allowedOperations(
				fakeBlockNames(3),
				fakeConstraints({ min: 4 })

			);
			expect(constraint).to.have.property('canRemoveBlocks', false);
		});

		it('Should allow adding blocks if section block count is below maximum', () => {
			const constraint = allowedOperations(
				fakeBlockNames(3),
				fakeConstraints({ max: 4 })

			);
			expect(constraint).to.have.property('canRemoveBlocks', true);
		});

		it('Should disallow adding blocks if section block count is maximum or more', () => {
			let constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ max: 4 })

			);
			expect(constraint).to.have.property('canRemoveBlocks', true);
			constraint = allowedOperations(
				fakeBlockNames(5),
				fakeConstraints({ max: 4 })

			);
			expect(constraint).to.have.property('canRemoveBlocks', true);
		});

		it('Should allow removing blocks if section is optional, even if below minimum block count', () => {
			const constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ min: 4, optional: true })

			);
			expect(constraint).to.have.property('canRemoveBlocks', true);
		});

		it('Should allow blocks to be swapped if none can be added or removed', () => {
			const constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ size: 4 })

			);
			expect(constraint).to.have.property('canSwapBlocks', true);
		});

		it('Should disallow adding and removing blocks if section blocks count is equal to size, but allow swapping', () => {
			const constraint = allowedOperations(
				fakeBlockNames(4),
				fakeConstraints({ size: 4 })

			);
			expect(constraint).to.include({
				canAddBlocks: false,
				canRemoveBlocks: false,
				canSwapBlocks: true
			});
		});

		it('Should disallow swapping if count is equal to one, and only one block type allowed', () => {
			const constraint = allowedOperations(
				fakeBlockNames(1),
				{ allowedBlocks: ['fake-block-name-v1'], size: 1 }
			);
			expect(constraint).to.have.property('canSwapBlocks', false);
		});

		it('Should allow swapping if count is equal to one, but several block types are allowed', () => {
			const constraint = allowedOperations(
				fakeBlockNames(1),
				fakeConstraints({ size: 1 })
			);
			expect(constraint).to.have.property('canSwapBlocks', true);
		});

		it('Should disallow swapping if min and max are equal to one, and only one block type allowed', () => {
			const constraint = allowedOperations(
				fakeBlockNames(1),
				{ allowedBlocks: ['fake-block-name-v1'],  min: 1, max: 1 }
			);
			expect(constraint).to.have.property('canSwapBlocks', false);
		});

		it('Should allow swapping if min and max are equal to one, but several block types are allowed', () => {
			const constraint = allowedOperations(
				fakeBlockNames(1),
				fakeConstraints({ min: 1, max: 1 })
			);
			expect(constraint).to.have.property('canSwapBlocks', true);
		});

	});

});
