// IIFE - Immediately Invoked Function Expression
(function($, window, document) {


  // Listen for the jQuery ready event on the document
  $(function() {
      $('.trmb-rpt-settings-submit').click(function(e){
          var data = {};
          var enable = true;
          $('.select_type_input').val(JSON.stringify($('.select_type').val()));
          
          if(!jQuery.isNumeric($('.cachetime').val()))
          {
            $('.cachetime').css('border-color','red');
            enable = false;
          }
          if(!jQuery.isNumeric($('.cacheminutes').val()))
          {
            $('.cacheminutes').css('border-color','red');
            enable = false;
          }
          if(!jQuery.isNumeric($('.cachesec').val()))
          {
            $('.cachesec').css('border-color','red');
            enable = false;
          }
          $('#priority').find('input[type="checkbox"]').each(function(){
            if($(this)[0]['checked'])
            {
              var field = $(this).attr('field');
              
              var length = $(this).parent().parent().find('input').length;
              if(length > 2)
              {
                data[field] = {};
                $(this).parent().parent().find('input').each(function(){
                  if($(this).attr('type') == 'number')
                  {
                    if($(this).val() && jQuery.isNumeric($(this).val()))
                    {
                      data[field][$(this).attr('field')] = $(this).val();
                    }
                    else
                    {
                      $(this).css('border-color','red');
                      enable = false;
                    }  
                  }
                  else if($(this).attr('type') == 'text')
                  {
                    data[field][$(this).attr('field')] = $(this).val();
                  }
                  
                })

                $(this).parent().parent().find('select').each(function(){
                    data[field][$(this).attr('field')] = $(this).val();
                })
              }

              else
              {
                var value = $(this).parent().parent().find('input').eq(1).val();
                if(value && jQuery.isNumeric(value))
                {
                  data[field] = value;
                }
                else
                {
                  $(this).parent().parent().find('input').eq(1).css('border-color','red');
                  enable = false;
                }
              }

            }
          })
          if(enable)
          {
            console.log(data);
            $('.priority_values').val(JSON.stringify(data));
            $('#related-posts-thumbnails').submit();
          }
      })

      $('#clear_cache').click(function($event){
        jQuery.ajax({
          url:'admin-ajax.php',
          type:'post',
          data:{action:'cache_clear'},
          success:function(){
            alert('cache is cleared successfully');
          }
        })
      })

  }); // End of document ready

  // The rest of the code goes here!

}(window.jQuery, window, document));
