
$('html ').on('click ','#video',function(){

 if (video.paused == true) {
    // Play the video
    video.play();

    // Update the button text to 'Pause'
    playButton.innerHTML = "Pause";
  } else {
    // Pause the video
    video.pause();

    // Update the button text to 'Play'
    playButton.innerHTML = "Play";
  }
  });

////



$('html ').on('click ','#video',function(){
   $('#video').removeClass('selected');
    $(this).addClass('selected');
        var Id = $(this).attr('id');

 if (video.paused == true) {
    // Play the video
    video.play();

    // Update the button text to 'Pause'
    playButton.innerHTML = "Pause";
  } else {
    // Pause the video
    video.pause();

    // Update the button text to 'Play'
    playButton.innerHTML = "Play";
  }
  });

$('html ').on('click ','#video.selected',function(){
    $(this).removeClass('selected');
   $('#video').removeClass('selected');

  
    });

$(document).on("click", "img", function(){
  video = '<embed src="'+ $(this).attr('data-video') +'" showcontrols="0" showcontrols="false"  autoplay="1"></embed>';
        $(this).replaceWith(video);

 });