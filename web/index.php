<?php
require 'auth.php';
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Air Conditioner Control Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:100" rel="stylesheet" type="text/css" />
  <link href="css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

  <div id="app">

    <div id="clicker">
      <div id="bulb" data-state="OFF"></div>
    </div>

    <h1>Air Conditioner Control</h1>

    <div id="aircon">
      <div id="temp-control" class="inactive">
        <p class="temperature">22</p>
        <div id="up-down">
          <img class="arrowUp" src="images/chevron-up.svg" />
          <img class="arrowDown" src="images/chevron-down.svg" />
        </div>
      </div>

      <div id="mode">
        <svg width="88" height="75" id="FAN" class="active">
          <circle cx="50%" cy="50%" r="35" fill="white" stroke="#FF7D83" stroke-width="4" />
          <circle cx="50%" cy="50%" r="22" fill="#FF7D83" class="inner" />
        </svg>
        <!--svg width="88" height="75" id="HEAT" class="active">
          <circle cx="50%" cy="50%" r="35" fill="white" stroke="#FF7D83" stroke-width="4" />
          <circle cx="50%" cy="50%" r="22" fill="#FF7D83" class="inner" />
        </svg-->
        <svg width="88" height="75" id="COOL">
          <circle cx="50%" cy="50%" r="35" fill="white" stroke="#78D3FF" stroke-width="4" />
          <circle cx="50%" cy="50%" r="22" fill="#78D3FF" class="inner" />
        </svg>
        <!--svg width="88" height="75">
          <circle cx="50%" cy="50%" r="35" fill="white" stroke="#F1F1BD" stroke-width="4" />
          <circle cx="50%" cy="50%" r="22" fill="#F1F1BD" class="inner" />
        </svg-->
      </div>

      <div id="fan-speed">
        <div id="password-input" style="display: none">
          <input type="password" id="password" vaule="" />
          <img id="unlock" src="images/lock-open.svg" width="30" height="30" />
        </div>

        <img id="power-icon" data-powered="ON" src="images/power.svg" width="75" height="75" />
        <img id="fan-icon" src="images/fan.svg" width="75" height="75" data-speed="LOW" class="ON" />
        <img id="lock" src="images/lock.svg" width="30" height="30" />
      </div>

      <div id="timer">
        <label>Timer On:</label>
        <input type="number" id="timer-on" min="0" max="24" /> h
      </div>

    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>
  <script src="js/json2.js" type="text/javascript"></script>
  <script src="js/automation.js" type="text/javascript"></script>

</body>

</html>