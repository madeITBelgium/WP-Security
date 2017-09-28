jQuery(document).ready(function($) {
   $('#madeit-tab').tabs({
        active: 0,
        activate: function(event, ui) {
            $('#active-tab').val(ui.newTab.index());
        }
    });
});