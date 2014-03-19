<!-- 	Result Page of Proj2 with implementation of proj3, proj4, and proj 5
		Created by Timothy Mar and Qiang Wang
-->

<html>
<!DOCTYPE html>
	<form name = "ResultPage">
	  <head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<title>CSC 336 - Project 5 - PHP/MySQL & Google Maps Example</title>
		<link type="text/css" rel="StyleSheet" href="lib/css/bluecurve/bluecurve.css" />
		<style type="text/css">
		<!------------ Settings for Google Map ------------->
		  html { height: 100% }
		  body { height: 100%; margin: 0; padding: 0 } 
		  #map_canvas { height: 100% }
		  
		<!-------------- Settings for Sliders --------------->
		  .dynamic-slider-control {
			width:		400px;
			height:		20px;
			margin:		0;
			}
		</style>
		<!-- Load pictures for the slider in the lib folder -->
		<script type="text/javascript" src="lib/range.js"></script>
		<script type="text/javascript" src="lib/timer.js"></script>
		<script type="text/javascript" src="lib/slider.js"></script>
		
		<!---------------Load Map using the key--------------->
		<script type="text/javascript"
		  src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCw_rIkXj9GxBHHST7vhxFHsIbx581Pi4c&sensor=false">
		</script>
		
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
		
		<script type="text/javascript">
		//***************************************************************************************************************************************
		// Declaring the variables
		//***************************************************************************************************************************************
			var map;															// Variable for google map									
			var geocoder;														// Variable for Geocoder to converting string to geecode
			var boundedMarkers;													// Array to hold all marker and info in bounded region
			var numberOfRemoved = 0;											// Variable for holding number of removed rows
			var lineArray;														// Array to hold all lines shown on Map for deleting purpose
			var customIcons = {													// Variable for different Icons according to type
			  museum: {
				icon: 'http://labs.google.com/ridefinder/images/mm_20_blue.png',
				shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
			  },
			  historic_site: {
				icon: 'http://labs.google.com/ridefinder/images/mm_20_red.png',
				shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
			  },
			  information: {
				icon: 'http://labs.google.com/ridefinder/images/mm_20_yellow.png',
				shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
			  },
			  eatery: {
				icon: 'http://labs.google.com/ridefinder/images/mm_20_green.png',
				shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
			  },
			  shop: {
				icon: 'http://labs.google.com/ridefinder/images/mm_20_purple.png',
				shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
			  }
		  
			}
		//***************************************************************************************************************************************
		// Initialize the map and overlays
		//***************************************************************************************************************************************		
		function initialize() {
			var infoWindow = new google.maps.InfoWindow;
			boundedMarkers = [];
			lineArray = [];
			
			// set settings for the map
			var mapOptions = {
			  center: new google.maps.LatLng(40.81959,-73.95057),
			  <?php 
				//set zoom according to user input
				if ($_POST["zoom"] == 12) 		echo "zoom: 12,";
				else if ($_POST["zoom"] == 10)	echo "zoom: 10,";
				else if ($_POST["zoom"] == 14)	echo "zoom: 14,";
			  ?>
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			
			//initialize the map
			map = new google.maps.Map(document.getElementById("map"),mapOptions);
			var location = new google.maps.LatLng(<?php echo $_POST["lat"] . "," . $_POST["lng"];?>);
			map.setCenter(location);
			
			//Display marker on center
			var marker = new google.maps.Marker({
				map: map,
				position: location
			});
			
			// setting hidden areas to pass out the later post operation.
			document.getElementById("lat").value = location.lat();
			document.getElementById("lng").value = location.lng();
			
			//Add info box for center
			google.maps.event.addListener(marker, 'click', function() {
			infoWindow.setContent(address);
			infoWindow.open(map, marker);
			});
			
			//Display Rectangle around center
 			var miles 	= (<?php echo $_POST["mile"]; ?> )/69.172;			//set the miles in degrees that define a square boundary apart from center
			var swLat 	= location.lat() + miles;							//set the sw latitude for the rectangle bound
			var swLng 	= location.lng() - miles;							//set the sw longitude for the rectangle bound
			var neLat 	= location.lat() - miles;							//set the ne latitude for the rectangle bound
			var neLng 	= location.lng() + miles;							//set the ne longitude for the rectangle bound
			var swCoor 	= new google.maps.LatLng(swLat, swLng);				//set the sw coordinate for the rectangle bound
			var neCoor 	= new google.maps.LatLng(neLat, neLng);				//set the ne coordinate for the rectangle bound

			rect = new google.maps.Rectangle({								//set the Rectangle
				map: map,
				bounds: new google.maps.LatLngBounds(swCoor,neCoor),
				fillOpacity: 0.0,
				strokeWeight:2.0	
			
			}); 
			
			//get data from database to put markers on map
			downloadUrl("genxml.php", function(data) {							//genxml.php is file containing generated data
				var xml = data.responseXML;
				var markers = xml.documentElement.getElementsByTagName("marker");
				for (var i = 0; i < markers.length; i++) {
					var id 		= markers[i].getAttribute("id");
					var name 	= markers[i].getAttribute("name");
					var address = markers[i].getAttribute("address");
					var type 	= markers[i].getAttribute("type");
					var expense = markers[i].getAttribute("expenses");
					var point 	= new google.maps.LatLng(
						parseFloat(markers[i].getAttribute("lat")),
						parseFloat(markers[i].getAttribute("lng"))); 
					var html 	= "<b>" + name + "</b>" + "<br/>"
								+ "ID: " + id + "<br/>" 
								+ "Address: " + address + "<br/>"
								+ "Costs: $" + expense;
					var icon 	= customIcons[type] || {};
					var miles 	= (<?php echo $_POST["mile"]; ?> )/69.172;		//convert miles to degrees lat/lng
					
					<?php //function for checking if checkbox was checked or not in php
					function IsChecked($chkname,$value)
					{
						if(!empty($_POST[$chkname]))
						{
							foreach($_POST[$chkname] as $chkval)
							{
								if($chkval == $value)
								{
									return true;
								}
							}
						}
						return false;
					}
					?>
					
					//Check if selected type matches type
					if(false					
						<?php //Check if each type is checked of or not
						if(IsChecked('type','museum')) 			echo "|| type == \"museum\"";
						if(IsChecked('type','historic_site')) 	echo "|| type == \"historic_site\"";
						if(IsChecked('type','information')) 	echo "|| type == \"information\""; 
						if(IsChecked('type','shop')) 			echo "|| type == \"shop\"";
						if(IsChecked('type','eatery')) 			echo "|| type == \"eatery\"";
						?> 
					){
						//Check location and markers and make sure they are in the bounded region before we place them on map
						if ((map.getCenter().lat() - miles) < point.lat() 			//bound the bottom
						&& 	(map.getCenter().lat() + miles) > point.lat()			//bound the top
						&& 	(map.getCenter().lng() - miles) < point.lng()			//bound the left
						&&  (map.getCenter().lng() + miles) > point.lng()			//bound the right
						)			
						{
							var marker 	= new google.maps.Marker({
								map: 		map,
								position: 	point,
								title: 		name,
								icon: 		icon.icon,
								shadow: 	icon.shadow
							});
						  var markerInfo = {										//Create a temp storage array for all info of markers
							marker:   marker,
							id: 	  id,
							name: 	  name,
							address:  address,
							type: 	  type,
							expenses: expense,
							point:    point
							}
						  boundedMarkers.push(markerInfo);							//store the marker info in the array
						  bindInfoWindow(marker, map, infoWindow, html);			//Create info box for each point
						}
					
					}
				}
			});														
		}
		//***************************************************************************************************************************************
		// Functions for clearing lines on map
		//***************************************************************************************************************************************
		function clearLines(){
			for (var i = 0; i < lineArray.length; i++)
			lineArray[i].setMap(null);
		}
		
		//***************************************************************************************************************************************
		// Functions for putting markers on map
		//***************************************************************************************************************************************
		function bindInfoWindow(marker, map, infoWindow, html) {
			google.maps.event.addListener(marker, 'click', function() {
				infoWindow.setContent(html);
				infoWindow.open(map, marker);
			});
		}

		function downloadUrl(url, callback) {
			var request = window.ActiveXObject ?
				new ActiveXObject('Microsoft.XMLHTTP') :
				new XMLHttpRequest;
			
			request.onreadystatechange = function() {
				if (request.readyState == 4) {
					request.onreadystatechange = doNothing;
					callback(request, request.status);
				}
				showResults();													//Show the list results
				setExpenses()													//Set max expenses
			};
			
			request.open('GET', url, true);
			request.send(null);
		}

		function doNothing() {}
		//***************************************************************************************************************************************
		// Function to show results list on start
		//***************************************************************************************************************************************
		function showResults()
		{
			//set header to be display on result list
			var listResults = "<tr>"
							+ "<td align=\"center\">	Select	</td>"
							+ "<td align=\"center\">	Start 	</td>"
							+ "<td align=\"center\">	End 	</td>"
							+ "<td align=\"center\">	ID  	</td>"
							+ "<td align=\"Left\">		Name 	</td>"
							+ "<td align=\"center\">	Cost 	</td>"
							+"</tr>"; 
							
			//display options, id and name for each marker on map
			var i;
			for (i = 0; i < boundedMarkers.length; i++)
			{
				var checkbox 	= "<input type=\"checkbox\" name=\"select\" id=\"select\"  checked=\"yes\" value=\"" + i + "\">";
				var radio1 		= "<input type=\"radio\" name=\"start\" id=\"start\" value=\"" + boundedMarkers[i].id + "\">";
				var radio2		= "<input type=\"radio\" name=\"end\" id=\"end\" checked=\"yes\" value=\"" + boundedMarkers[i].id + "\">";
				
				listResults += "<tr>" 
							+ "<td align = \"Center\">" + checkbox + "</td>"
							+ "<td align = \"Center\">" + radio1 + "</td>"
							+ "<td align = \"Center\">" + radio2 + "</td>"
							+ "<td align = \"Left\">" 	+ boundedMarkers[i].id + "</td>"
							+ "<td align = \"Left\">" 	+ boundedMarkers[i].name + "</td>"
							+ "<td align = \"Left\"> $" + boundedMarkers[i].expenses + "</td>" 
							+"</tr>";
					
			}
			numberOfResults = i;
			document.getElementById('results').innerHTML=(listResults);
			document.ResultPage.start[0].checked=true;
			
		}
		//***************************************************************************************************************************************
		// Function to refine results list
		//***************************************************************************************************************************************
		function refineResults()
		{
		
			// Set header to be display on result list
			var refinedResults = "<tr>"
							+ "<td align=\"center\">	Select	</td>"
							+ "<td align=\"center\">	Start 	</td>"
							+ "<td align=\"center\">	End 	</td>"
							+ "<td align=\"center\">	ID  	</td>"
							+ "<td align=\"Left\">		Name 	</td>"
							+ "<td align=\"center\">	Cost 	</td>"
							+ "</tr>"; 
							
			// Display options, id and name for each marker on map	
			var i;
			// Array for which markers to remove from map
			var removedId = [];
			// Variable to prevent for loop going into undefined (deleted) checkboxes
			var counter = boundedMarkers.length - numberOfRemoved;
			for (i = 0; i < counter; i++)
			{
				if(document.ResultPage.select[i].checked==false)
				{
					numberOfRemoved = numberOfRemoved + 1;
					removedId.push(i);
					//alert("Checkbox at index "+i+" is not checked!");					//for debugging
					boundedMarkers[document.ResultPage.select[i].value].marker.setMap(null);
				}
			}
			// Don't want to delete rows while it's still looping 
			// so create a new loop just for unselected boxes
			while(removedId.length !== 0)
			{
				document.getElementById("results").deleteRow(removedId.pop()+1);
			}
			clearLines();
			setExpenses();
		}
		//***************************************************************************************************************************************
		// Function to set expenses
		//***************************************************************************************************************************************
		function setExpenses()
		{
			var max = 0;
			var counter = boundedMarkers.length - numberOfRemoved;
			for (i = 0; i < counter; i++)
			{
				if(document.ResultPage.select[i].checked==true)
				{
					max = max + parseInt(boundedMarkers[document.ResultPage.select[i].value].expenses);
				}
			}
			document.getElementById('max').innerHTML=(max);
			document.getElementById("maxExpense").value = max;
			allowance.setMaximum(max); 
			allowance.setValue(max);
		}
		//***************************************************************************************************************************************
		// Knapsack Function
		//***************************************************************************************************************************************
		function initKnapsack(){
			var maxExpenses = document.getElementById("maxExpense").value;
			var allowanceInput = document.getElementById("allowance-input").value;
			knapsack(maxExpenses, allowanceInput);
			refineResults();
		}		
		
		function knapsack(total, userAllowance)
		{
			if(total === userAllowance){
				return;
			}
			var max 		= 0; 	// Largest expense, Pmax
			var max2 		= 0; 	// Pmax'
			var maxIndex 	= 0; 	// Store the index of max
			var max2Index 	= 0; 	// Store the index of max2
			var diff;
			var counter = boundedMarkers.length - numberOfRemoved;
			for (i = 0; i < counter; i++){
				if(document.ResultPage.select[i].checked===true){
					if(max < parseInt(boundedMarkers[document.ResultPage.select[i].value].expenses)){
						max = parseInt(boundedMarkers[document.ResultPage.select[i].value].expenses);
						maxIndex = i;
					}
				}
			}
			if((total - max) > userAllowance) {
				document.ResultPage.select[maxIndex].checked = false; // Removes the marker after refine search
				total = total - max;
				knapsack(total, userAllowance);
			}
			else{
				diff = total - userAllowance;
				for (i = 0; i < counter; i++){
					if(document.ResultPage.select[i].checked===true){
						if(max2 < parseInt(boundedMarkers[document.ResultPage.select[i].value].expenses)){
							if((max2 > diff) && (max2 < max)){
								max2 = parseInt(boundedMarkers[document.ResultPage.select[i].value].expenses);
								max2Index = i;
							}
						}
					}
				}
				if(max2 !== 0){
					document.ResultPage.select[max2Index].checked = false; // Removes the marker after refine search
					total = total - max2;
				}
				else{
					document.ResultPage.select[maxIndex].checked = false; // Removes the marker after refine search
					total = total - max;
				}
			}
		}
		//***************************************************************************************************************************************
		// MST Function
		//***************************************************************************************************************************************
		function initMST(){
			// Getting the set of IDs
			var counter = boundedMarkers.length - numberOfRemoved;
			var pSetString = "" // Store converted IDs
			var mstResults 		= "Results of MST using Kruskal Algorithm: \n"; // Create string to hold results of MST
			var fromResultList 	= [];											// Holds all the "from" data from result table
			var toResultList 	= [];											// Holds all the "to" data from result table
			var eSetString = "24:33,46:186,116:202";
			
			for (i = 0; i < counter; i++){
				if(document.ResultPage.select[i].checked===true){
					pSetString = pSetString + boundedMarkers[document.ResultPage.select[i].value].id
						.slice(1,boundedMarkers[document.ResultPage.select[i].value].id.length);
					pSetString = pSetString + ",";
				}
			}
			pSetString = pSetString.slice(0, pSetString.length-1);
			
			
			$.ajax({
				async: false,
				url: "sendpSet.php",
				type: "post",
				data: {pSet: pSetString}
			}).done(function(data){
				eSetString = data;
				alert(eSetString);
			});
			
			var i = 0;

			// iterate through the string and extract all edge and put them into the from and to list
			while(i < eSetString.length){
				var from = "";
				var to   = "";
				
				// First part of the string is the 'from' of the edge
				while(eSetString.charAt(i) != ':'){
					from += eSetString.charAt(i);
					i++;	
				}
				// After finding all char before ':' add 'from' to result list
				fromResultList.push(parseInt(from));
				
				// Next part is the 'to' of the edge
				i++;
				while(eSetString.charAt(i) != ',' && i < eSetString.length){
					to += eSetString.charAt(i);
					i++;
				}
				
				// After finding all char before ',' add 'to' to result list
				toResultList.push(parseInt(to));
				i++;
			}
			
			// Connect the markers with a line according to MST results
			if(fromResultList.length != toResultList.length) alert("Error: from and to list length dont match"); 
			else{
				clearLines();
				for (i = 0; i < fromResultList.length; i++)
				{
					var fromCoor 	= 0;
					var toCoor 		= 0;
					for (j = 0; j < boundedMarkers.length; j++)
					{
						if(fromResultList[i] == boundedMarkers[j].id) 	fromCoor = boundedMarkers[j].point;
						if(toResultList[i] == boundedMarkers[j].id) 	toCoor = boundedMarkers[j].point;
					}
					if (fromCoor != 0 & toCoor != 0)
					{
						var lineCoor = [
							fromCoor,
							toCoor
						];
						var pathLine = new google.maps.Polyline({
							map: 			map,
							path: 			lineCoor,
							strokeColor: 	'#FF0000',
							strokeOpacity: 	1.0,
							strokeWeight: 	2
						});
						lineArray.push(pathLine);
					}
				}
			}
	
			
		}
		//***************************************************************************************************************************************
		// Dijkstra
		//***************************************************************************************************************************************
		function DijkstraAlg(){	
			var counter = boundedMarkers.length - numberOfRemoved;	// Counts the number of markers left
			var startPoint = -1;									// Variable to store start point
			var endPoint = -1;										// Variable to store end point
			var results;
			
			// Use for loop to find start and end point checked off
			for (i = 0; i < counter; i++){
			
				// Find the id marked start
				if(document.ResultPage.start[i].checked===true){
					startPoint = document.ResultPage.start[i].value;
				}
				
				// Find the id marked end
				if(document.ResultPage.end[i].checked===true){
					endPoint = document.ResultPage.end[i].value;
				}
			}
				
			// Error Checking
			if(startPoint == -1){alert("Start Point Not Checked off");return;}
			if(endPoint == -1){alert("End Point Not Checked off");return;}
			
			$.ajax({
				async: false,
				url: "sendStartEnd.php",
				type: "post",
				data: {start: startPoint,
					   end	: endPoint
				}
			}).done(function(data){
				results = data;
				alert(results);
			});
			
			clearLines();
		}		
		</script>

	  </head>
	  <body onload="initialize()">
		<table width="1000" border="1" cellpadding="1" cellspacing="0" align = "Center">
			<tr><td align="center" 	colspan="2"> <b>Search Result</b>
			<tr><td align="left" 	colspan="2">
				<img src="http://labs.google.com/ridefinder/images/mm_20_blue.png"   alt="blue"  /> - Musuem			<br/>
				<img src="http://labs.google.com/ridefinder/images/mm_20_red.png"    alt="red"   /> - Historic Site		<br/>
				<img src="http://labs.google.com/ridefinder/images/mm_20_yellow.png" alt="yellow"/> - Information		<br/>	
				<img src="http://labs.google.com/ridefinder/images/mm_20_green.png"  alt="green" /> - Eatery			<br/>
				<img src="http://labs.google.com/ridefinder/images/mm_20_purple.png" alt="purple"/> - Shop				<br/>
			<tr><td align="left" valign = top>
				<?php
					//Set size of map according to user input
					if 		($_POST["size"] == "600") echo "<div id=\"map\" style=\"width:600; height:600\"></div>";
					else if ($_POST["size"] == "800") echo "<div id=\"map\" style=\"width:800; height:800\"></div>";
					else if ($_POST["size"] == "400") echo "<div id=\"map\" style=\"width:400; height:400\"></div>";
				?>
				<td>
					<table id = "results"></table>	
					<br/>
					<!-- Expense Slider -->
					<table class="expense-slider" cellspacing="2" cellpadding="0" border="0">
						<tr>
							<td><label for="allowance-slider"> Allowance:</label>
								<input id="allowance-input" type="text" size="4" />
								out of $<b id = "max"></b>
								<div class="slider" id="allowance-slider" tabIndex="1">
									<input class="slider-input" id="allowance-slider-input" />
								</div>
							</td>
						</tr>
					</table>
					
					<!-- Distance Slider -->
					<table class="distance-slider" cellspacing="2" cellpadding="0" border="0">
						<tr>
							<td><label for="distance-slider"> Distance:</label>
								<input id="distance-input" type="text" size="4" />
								out of <b id = "maxDist">5</b>
								<div class="slider" id="distance-slider" tabIndex="1">
									<input class="slider-input" id="distance-slider-input" />
								</div>
							</td>
						</tr>
					</table>
					
					<input type="button" value ="Refine Search" onclick = "refineResults()">
					<input type="button" value ="Search Again" 	onclick = "location.href='index.php'">
					<br/>					
					<input type="button" value ="Project 3"   	onclick = "initKnapsack()">
					<input type="button" value ="Project 4"   	onclick = "initMST()">
					<input type="button" value ="Project 5"   	onclick = "DijkstraAlg()">
					
					<!--Hidden Variables used for storing -->
					<input type="hidden" name="maxExpense" 	id="maxExpense" value="0" />
					<input type="hidden" name="expense" 	id="expense" 	value="0" />
					<input type="hidden" name="lat" 		id="lat" 		value="0" />
					<input type="hidden" name="lng" 		id="lng" 		value="0" />
					<input type="hidden" name="zoom" 		id="zoom" 		value="<?php echo $_POST["zoom"];?>" />
					<input type="hidden" name="size" 		id="size" 		value="<?php echo $_POST["size"];?>" />
					<input type="hidden" name="miles" 		id="miles" 		value="<?php echo $_POST["mile"]/69.172; ?>" />
				</td>			
		</table>
		<script type="text/javascript">
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Functions for Expenses Slider
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		// init code
		var allowance = new Slider(document.getElementById("allowance-slider"),
			document.getElementById("allowance-slider-input"));
			
		allowance.setMaximum(300); 

		var allowance_input = document.getElementById("allowance-input");

		allowance_input.onchange = function() {
			allowance.setValue(parseInt(this.value));
		}

		allowance.onchange = function() {
			allowance_input.value = allowance.getValue();
			document.getElementById("expense").value = allowance_input.value;
			if(typeof window.onchange == "function")
				window.onchange();
		};

		allowance.setValue(100); // Initial value

		// end init
	
		function setAllowance(amount) {
			allowance.setValue(amount);
		}

		function getAmount(){
			return {
				allowance: allowance.getValue()
			};
		}


		// This isn't really useful but I put this here anyways
		// It resizes everything if the window is at a different size
		function fixSize() {
			allowance.recalculate();
		}

		window.onresize = fixSize;
		fixSize();
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Functions for Distance Slider
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		// init code
		var distance = new Slider(document.getElementById("distance-slider"),
			document.getElementById("distance-slider-input"));
			
		distance.setMaximum(5); 

		var distance_input = document.getElementById("distance-input");

		distance_input.onchange = function() {
			distance.setValue(parseInt(this.value));
		}

		distance.onchange = function() {
			distance_input.value = distance.getValue();
			//document.getElementById("expense").value = allowance_input.value;
			if(typeof window.onchange == "function")
				window.onchange();
		};

		distance.setValue(5); // Initial value

		// end init
	
		function setAllowance(amount) {
			distance.setValue(amount);
		}

		function getAmount(){
			return {
				distance: distance.getValue()
			};
		}
		</script>
	  </body>
	</form>
</html>