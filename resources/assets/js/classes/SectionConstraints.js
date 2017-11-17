/**
 * Generate an object that describes what operations are allowed in a section,
 * at a particular moment in time, based on the current section data and an
 * initial constraint object (part of a region's schema).
 *
 * @param      {Object}  sectionBlocks  The section data.
 * @param      {Object}  constraints    The constraint object, e.g.
 * 	{
 * 		allowedBlocks: ["block-name-v1", "block-name-v1"],
 * 		min: 1,
 * 		max: 5,
 * 		size: 10, // (overrides min and max)
 * 		optional: true
 * 	}
 *
 * @return     {Object}  {
 *		canAddBlocks: true,
 *		canRemoveBlocks: false,
 *		canSwapBlocks: false
 *	}
 * }
 */
export const allowedOperations = (
	sectionBlocks,
	{ allowedBlocks, min, max, size, optional } = { allowedBlocks: null }
) => {
	let
		canRemoveBlocks = true,
		canAddBlocks = true;

	if(allowedBlocks) {
		// set min and max to specific length if size is supplied
		if(size) {
			max = min = size;
		}

		if(min && sectionBlocks.length <= min) {
			canRemoveBlocks = false;
		}

		if(max && sectionBlocks.length >= max) {
			canAddBlocks = false;
		}

		if(optional) {
			canRemoveBlocks = true;
		}
	}

	return {
		canAddBlocks,
		canRemoveBlocks,
		canSwapBlocks: (
			// swapping should be allowed if we can't add or remove blocks
			!canAddBlocks && !canRemoveBlocks &&
			// but if we only have one block type allowed and a size of 1
			// it makes no sense to be able to swap a block for itself
			!(
				Array.isArray(allowedBlocks) &&
				allowedBlocks.length === 1
				&& min === 1 && max === 1
			)
		)
	};
};
