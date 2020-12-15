/* jshint undef: false, unused:false */
// AJAX Functions
var jq = jQuery;
                               // TODO implement this. Global variable to prevent multiple AJAX requests

jq(document).ready(function trsLike() {
    "use strict";
    jq('.fp_promote,.fp_unpromote').live('click', function() {

      var id, type;
        id = jq(this).attr('id');
        if(jq(this).hasClass('fp_promote')){
            var period_in_min  =parseInt( prompt("Please enter the promotion in mintus", "1"));
            if(isNaN(period_in_min)) return false;
            type = 'activity_fp_promote';
        }else  if(jq(this).hasClass('fp_unpromote')){
          type = 'activity_fp_unpromote';
        }
                               // Used to get the id of the entity liked or unliked

        jq(this).addClass('loading');

        jq.post(ajaxurl, {
            action: 'promote_process_ajax',                            // TODO this could be named clearer
            'type':type ,
            'id': id,
            'period_in_min':period_in_min
        },
            function( data ) {
                // jq('#' + id).fadeOut(100, function() {
                    jq('#' + id).html(data).removeClass('loading');
                // });
                if(data == 'Promote'){
                  jq('#' + id).removeClass('fp_unpromote').addClass('fp_promote');
                }else if(data == 'Unpromote'){
                  jq('#' + id).addClass('fp_unpromote').removeClass('fp_promote');
                }
                if(!jq('#' + id).hasClass('multiaction')){
                  jq('#' + id).removeClass('fp_unpromote').removeClass('fp_promote').addClass("disabled");
                }
                console.log(data);
            });

        return false;
    });

});
