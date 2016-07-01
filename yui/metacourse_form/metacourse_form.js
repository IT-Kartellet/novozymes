YUI.add('moodle-block_metacourse-metacourse_form', function(Y) {
    M.block_metacourse = M.block_metacourse || {};

    M.block_metacourse.metacourse_form = {
        init: function (settings) {
            this.handleSelectAll();
        },

        handleSelectAll: function () {
            var checkboxes = Y.all('.checkboxgroup1');
			if(Y.one('.selectallornone')){
					Y.one('.selectallornone').on('click', function (e) {
                if (checkboxes.some(function (checkbox) {
                        return checkbox.get('checked');
                    })) {
                    checkboxes.set('checked', false);
                } else {
                    checkboxes.set('checked', 'checked');
                }

                e.preventDefault();
            });
			}
        }
    }
}, '@VERSION@', {
    requires: []
});