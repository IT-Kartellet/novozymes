YUI.add('moodle-block_metacourse-dateform', function(Y) {
    M.block_metacourse = M.block_metacourse || {};

    M.block_metacourse.dateform = {
        init: function (settings) {
            var nodes = Y.all('input.elearning');

            nodes.each(this.handleChecked);
            nodes.on('change', function (e) {
                this.handleChecked(e.currentTarget);
            }.bind(this));
        },

        handleChecked: function (target) {
            var id = target.get('id').match(/id_datecourse_([0-9]+)_elearning/)[1],
                checked = target.get('checked');

            ['timestart', 'timeend', 'places', 'location', 'price'].forEach(function (name) {
                Y.all('div#fitem_id_datecourse_' + id + '_' + name + ' select, div#fitem_id_datecourse_' + id + '_' + name + ' input').set('disabled', checked);
            });

            ['price', 'places'].forEach(function (name) {
                var element = Y.one('div#fitem_id_datecourse_' + id + '_' + name + ' input');
                if (!checked && element.get('value') == -1) {
                    element.set('value', '');
                } else if (checked) {
                    element.set('value', -1);
                }
            });

        }
    }
}, '@VERSION@', {
    requires: []
});