export default {

	methods: {
		getErrors(fieldPath) {
			const
				blockErrors = this.$store.state.errors.blocks,
				blockId = this.$store.state.contenteditor.currentBlockId;

			if(
				blockErrors[blockId] &&
				blockErrors[blockId].errors[fieldPath] &&
				blockErrors[blockId].errors[fieldPath].length
			) {
				return blockErrors[blockId].errors[fieldPath].join(', ');
			}

			return null;
		}
	}

};
