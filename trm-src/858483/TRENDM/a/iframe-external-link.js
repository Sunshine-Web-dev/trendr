
jQuery(document).ready(function(){
    $('a[href^="https://"]').not('a[href*=gusdecool]').attr('target','blah');
    //$('a').setAttribute('target','blah');
})
