<div class="menuadmin">
  <!-- Top Bar -->
  <header class="menuadmin__header">
    <div class="menutoolbar">
      <div class="menutoolbar__left">
        <button class="menubutton menubutton--primary"></button>
      </div>
      <div class="menutoolbar__center">
        <button class="menubutton menubutton--primary">
          <menutoptitle  style="font-size: 20px; font-weight: bold; text-transform: uppercase;"><?php echo $stationlocation; ?>  WEATHER STATION   <img src="./img/flags/<?php echo $flag?>.svg" width="20"></menutoptitle>
            
        </button>
      </div>
      <div class="menutoolbar__right">
          <input type="button" style="background: rgba(39, 123, 70, .8); color: white; border-radius: 2px; border-color: rgba(39, 123, 70, .8);" value="Tablet Mode" onclick="updateButton()"/>
      </div>
    </div>

    <script>
function updateButton() 
          {
  var x = document.getElementById("tablet");
          const button = document.querySelector("input");
          
  if (x.style.display === "none" && button.value === "Dashboard Mode") {
    x.style.display = "block";
  button.value = "Tablet Mode";
   
  } else {
    x.style.display = "none";
  button.value = "Dashboard Mode";
    
  }
          }
</script>