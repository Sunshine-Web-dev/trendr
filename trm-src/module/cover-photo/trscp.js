jQuery(document).ready(function () {

    var jq = jQuery;
    jq("#cover_edit").on('click', '#trscp-del-image', function () {
        var $this = jq(this);

        jq.post(ajaxurl, {
                action: 'trscp_delete_cover',
                cookie: encodeURIComponent(document.cookie),
                buid: $this.data('buid'),
                _key: jq($this.parents('form').get(0)).find('#_key').val()
            },
            function (response) {
                //remove the current image
                jq("div#message").remove();
                $this.parent().before(jq("<div id='message' class='update'>" + response + "</div>"));
                $this.prev('.current-cover').fadeOut(100);//hide current image
                $this.parent().remove();//remove from dom the delete link
                //give feedback
                //remove the body class
                jq('body').removeClass('user-page');
            }
        );
        return false;

    })

});