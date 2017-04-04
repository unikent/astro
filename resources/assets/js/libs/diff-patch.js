const diffPatch = jsondiffpatch.create({
	objectHash(obj, index) {
		// try to find an id, otherwise just use the index in the array
		return obj.name || obj.id || obj._id || '$$index:' + index;
	}
});

export default diffPatch;