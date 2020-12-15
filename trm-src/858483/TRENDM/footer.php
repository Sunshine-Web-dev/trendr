		</div> <!-- #container -->

		<?php do_action( 'trs_after_container' ) ?>
		<?php do_action( 'trs_before_footer' ) ?>

		<div id="footer">
<p><?php printf( __( ' <a href="/about">About</a> . <a href="/feedback">Feedback</a> . <a href="/terms">Terms & Privacy</a> .  &copy; trendr 2018' ), get_bloginfo( 'name' ) ); ?></p>
			<?php do_action( 'trs_footer' ) ?>
		</div><!-- #footer -->

		<?php do_action( 'trs_after_footer' ) ?>


<script>



function getLocation() {
  //x.innerHTML = "Geolocation is not supported by this browser.";
  //var x = document.getElementById("demo");
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
	
  } else { 
   // x.innerHTML = "Geolocation is not supported by this browser.";
  }
}


function showPosition(position) {
  //x.innerHTML = "Latitude: " + position.coords.latitude + 
  //"<br>Longitude: " + position.coords.longitude; 
  var lata = position.coords.latitude;
  var longa = position.coords.longitude;
  console.log('Lat', lata);
  console.log('Long', longa);
  document.getElementById("latitude").value = lata;
  document.getElementById("longitude").value = longa;
  fetch('http://135.181.74.24:4000/v1/reverse?point.lon=' + longa + '&point.lat=' + lata + '&size=40&layers=venue')
    .then(res => res.json())
    .then((out) => {
        console.log('Output: ', out.features);
		document.getElementById("address_location").value = out.features[0].properties.label;
    document.getElementById("myInput").value = out.features[0].properties.label;


		var select = document.getElementById("select"),
        arr = out.features;
		arr2 = Array.from(arr, x => x.properties.label);
		countries = arr2;
		test = arr2;
		console.log('test' , countries);
        autocomplete(document.getElementById("myInput"), countries);     
             for(var i = 0; i < arr.length; i++)
             {	
				
                 var option = document.createElement("OPTION"),
                 txt = document.createTextNode(arr[i].properties.label);
                 option.appendChild(txt);
                 option.setAttribute("value",arr[i].properties.label);
                 select.insertBefore(option,select.lastChild);
             }
 }).catch(function() {
        console.log("error");
    });
}

function changeValue(){
	//console.log('Output: ', 1);
	document.getElementById("address_location").value = document.getElementById("select").value;
	document.getElementById("myInput").value = document.getElementById("select").value;
}



function openForm() {
  document.getElementById("myForm").style.display = "block";
  //autocomplete(document.getElementById("myInput"));
}

function closeForm() {
  document.getElementById("myForm").style.display = "none";
}


$('html').on('click scroll touchmove touchend touchcancel touchleave touch','li#f0',function(){
	getLocation();
});












function autocomplete(inp, arr) {
  //geo = navigator.geolocation.getCurrentPosition;
  navigator.geolocation.getCurrentPosition(position => {
  lata = position.coords.latitude;
  longa = position.coords.longitude;
  document.getElementById("latitude").value = lata;
  document.getElementById("longitude").value = longa;
  console.log('Lat1', lata);
  console.log('Long1', longa);
	})
  lata = document.getElementById("latitude").value;
  longa = document.getElementById("longitude").value;
  console.log('Lat2', lata);
  console.log('Long2', longa);

  var currentFocus;
 
 
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
   
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
     
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
     
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
       
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
         
          b = document.createElement("DIV");
          
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
       
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          
          b.addEventListener("click", function(e) {
            
              inp.value = this.getElementsByTagName("input")[0].value;
          
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
  
        currentFocus++;
      
        addActive(x);
      } else if (e.keyCode == 38) { //up
       
        currentFocus--;
      
        addActive(x);
      } else if (e.keyCode == 13) {

        e.preventDefault();
        if (currentFocus > -1) {

          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
   
    if (!x) return false;
   
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
   
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
   
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
	document.getElementById("select").value = document.getElementById("myInput").value;
	document.getElementById("address_location").value = document.getElementById("select").value;
  }
  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
  });
}


		
		
		
		























</script>
	</body>
</html>		<?php trm_footer(); ?>
