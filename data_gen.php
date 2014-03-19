<!-- 	Index Page of Proj2 
		Created by Timothy Mar and Qiang Wang
-->
<html>
<!DOCTYPE html>
	<FORM method = "post" action ="result.php" onsubmit="return verifyForm();">
	  <head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<title>CSC 336 - Project 5 - PHP/MySQL & Google Maps Example</title>
		<style type="text/css">
		  html { height: 100% }
		  body { height: 100%; margin: 0; padding: 0 } 
		  #map_canvas { height: 100% }
		</style>
		<script type="text/javascript"
		  src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCw_rIkXj9GxBHHST7vhxFHsIbx581Pi4c&sensor=false">
		</script>
		<script type="text/javascript">
				var map;							//Variable for map
				var geocoder;						//Variable for geocoding an address
				var rectArray 	= [];				//Array to hold all Rect drawn to map
				var markerArray = [];				//Array to hold all marker drawn to map
			
			//Initialize the map
			function initialize() 
			{
				geocoder = new google.maps.Geocoder();
				var mapOptions = {
				  center: new google.maps.LatLng(40.81959,-73.95057),
				  zoom: 12,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				map = new google.maps.Map(document.getElementById("map"),mapOptions);
				
		   
			};
			
			//Function to clear all markers and rectangle drawn to the map
			function clearOverlays(){
				for (var i = 0; i < markerArray.length; i++)
				markerArray[i].setMap(null);
				for (var i = 0; i < rectArray.length; i++)
				rectArray[i].setMap(null);
			}
			
			//Function to convert string to geopoint on the map and set it to center
			function codeAddress() 
			{
				var address = document.getElementById("address").value;
				clearOverlays();
				geocoder.geocode( { 'address': address}, function(results, status) {
				  if (status == google.maps.GeocoderStatus.OK) {
					var location = results[0].geometry.location;
					map.setCenter(location);
					document.getElementById("lat").value = location.lat();
					document.getElementById("lng").value = location.lng();
					
					//Display Marker at center
					marker = new google.maps.Marker({
						map: map,
						position: location
					});
					markerArray.push(marker);
					
					//Display Rectangle around center
					var miles 	= (document.getElementById("mile").value)/69.172;	//set the miles in degrees that define a square boundary apart from center
					var swLat 	= location.lat() + miles;							//set the sw latitude for the rectangle bound
					var swLng 	= location.lng() - miles;							//set the sw longitude for the rectangle bound
					var neLat 	= location.lat() - miles;							//set the ne latitude for the rectangle bound
					var neLng 	= location.lng() + miles;							//set the ne longitude for the rectangle bound
					var swCoor 	= new google.maps.LatLng(swLat, swLng);				//set the sw coordinate for the rectangle bound
					var neCoor 	= new google.maps.LatLng(neLat, neLng);				//set the ne coordinate for the rectangle bound

					rect = new google.maps.Rectangle({
						map: map,
						bounds: new google.maps.LatLngBounds(swCoor,neCoor),
						fillOpacity: 0.0,
						strokeWeight:2.0
					});
					rectArray.push(rect);
					
				  } else {
					alert("Geocode was not successful for the following reason: " + status);
				  }
				});
			} 
			// Verify form to check for valid lat lng first
		    function verifyForm() {
				if (document.getElementById("lat").value == "0" 
				||  document.getElementById("lng").value == "0") {
				alert("Please find center point first");		  
				return false;		  
				}
				return true;		  
		    }	
		</script>
	  </head>
	  <body onload="initialize()">
	  <table width="1000" border="1" cellpadding="10" cellspacing="0" align = "Center">
			<tr><td align="left">
				<b>Roadmap size:</b> 
				<select name="size" id = "size"> 
					<option value = "800">						large:	800&times;800</option>
					<option value = "600" selected="selected">	medium:	600&times;600</option>
					<option value = "400">						small:	400&times;400</option>
				</select>
				
				<b>Initial zoom:</b>
				<select name="zoom" id = "zoom"> 
					<option value = 10>						large:10	</option>
					<option value = 12 selected="selected">	medium:12</option>
					<option value = 14>						small:14</option>
				</select>
				<br/><br/>
				
				<b>Central address:</b> 
				<input type="text" name="address" id="address" size="50" value="Central Park, New York, NY" />
				
				<b>Surrounded by:</b>
				<select name="mile" id="mile"> 
					<option value = .5	>					0.5 mile</option>
					<option value = 1	>					1 mile</option>
					<option value = 1.5 selected="selected">1.5 mile</option>
					<option value = 2	>					2 miles</option>
					<option value = 2.5	>					2.5 miles</option>
				</select>
				<br/><br/>
				
				<b>Types:</b>
				<input type="checkbox" name="type[]" checked="yes" value="museum"> 			Museum 
				<input type="checkbox" name="type[]" checked="yes" value="historic_site"> 	Historic Site
				<input type="checkbox" name="type[]" checked="yes" value="information"> 	Information
				<input type="checkbox" name="type[]" checked="yes" value="shop"> 			Shop
				<input type="checkbox" name="type[]" checked="yes" value="eatery"> 			Eatery

				<div align="right">
					<input type="button" value ="Find Center" onclick = "codeAddress()">
					<input type="submit" value ="Find Points">
					
					<input type="hidden" name="pset" 		id="pset" 		value="0"/>
				</div>
			</td></tr>
			<tr><td align="center">
				<div id="map" style="width:800; height:800"></div> 
		</table>
		<!--------Hidden Variables used for storing -------->
		<input type="hidden" name="lat" id="lat" value="0" />
		<input type="hidden" name="lng" id="lng" value="0" />
	  </body>
	</form>
</html>