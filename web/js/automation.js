
var STATE_TIMEOUT = null;

var STATE = {
  'powerStatus': '',
  'mode': '',
  'fanSpeed': '',
  'temperature': '',
  'timerOn': '',
  'timerOff': ''
};

function setStateTimeout() {
  clearTimeout(STATE_TIMEOUT);
  STATE_TIMEOUT = setTimeout(setState, 1000);
}

function setState() {
  //console.log(STATE);
  
  $.ajax({
    url: 'action/action.php?r=' + (Math.floor(Math.random() * 10000)),
    data: {
      state: JSON.stringify(STATE)
    },
    type: 'POST',
    dataType: 'JSON',
    success: function(data) {
      //console.log(data);
      if (data.status != 'success') {
        alert('Error in transmitting state! ' + data.msg);
        init();
      }
    },
    error: function(data) {
      alert('Error in transmitting state!');
    }
  });
}

function updateUI() {
  $('#power-icon').attr('data-powered', STATE.powerStatus).attr('title', STATE.powerStatus);
  $('#fan-icon').attr('data-speed', STATE.fanSpeed).attr('title', STATE.fanSpeed);
  $('#temp-control .temperature').text(STATE.temperature);
  $('#timer-on').val(STATE.timerOn);

  $('#mode svg#' + STATE.mode).siblings().removeClass('active');
  $('#mode svg#' + STATE.mode).addClass('active');

  if (STATE.mode != 'FAN') {
    $('#temp-control').attr('class', 'active');
  } else {
    $('#temp-control').attr('class', 'inactive');
  }

  // check timer is reached
  var now = (new Date()).getTime();
  var modifiedDate = parseInt(STATE['modifiedDate']);
  var timerOn = parseFloat(STATE.timerOn) || 0;
  if (timerOn > 0 && now < (modifiedDate + timerOn*3600000)) {
    $('#fan-icon').attr('class', 'OFF');
  }
  else {
    $('#fan-icon').attr('class', STATE.powerStatus);
    $('#timer-on').val('');
  }
}

function init() {
  $.ajax({
    url: 'action/action.php?r=' + (Math.floor(Math.random() * 10000)),
    dataType: 'JSON',
    success: function(data) {
      STATE = data;
      updateUI();

      $('#app').animate({
        opacity: 1
      });
    },
    error: function() {
      alert('Error! try again.');
    }
  });
}

function getNexFanSpeed(cur) {
  switch (cur) {
    case 'LOW':
      return 'MED';
    case 'MED':
      return 'HIGH';
    case 'HIGH':
      return 'LOW';
  }
}

function getNextPowerState(cur) {
  switch (cur) {
    case 'ON':
      return 'OFF';
    case 'OFF':
      return 'ON';
  }
}

function getNextTemp(btn) {
  var cur = parseInt(ele.text().trim());

  if (btn == 'arrowUp' && cur < 30) {
    cur++;
  } else if (btn == 'arrowDown' && cur > 17) {
    cur--;
  }

  return cur;
}

function getNextBulbState(cur) {
  switch (cur) {
    case 'ON':
      return 'OFF';
    case 'OFF':
      return 'ON';
  }
}

$('#bulb').click(function() {
  //STATE['bulb'] = getNextBulbState($(this).attr('data-state'));
  //updateUI(STATE);
  //setStateTimeout();
});

$('#fan-icon').click(function() {
  STATE['fanSpeed'] = getNexFanSpeed($(this).attr('data-speed'));

  updateUI();
  setStateTimeout();
});

$('#power-icon').click(function() {
  STATE['powerStatus'] = getNextPowerState($(this).attr('data-powered'));

  updateUI();
  setStateTimeout();
});

$('#mode svg').click(function() {
  STATE['mode'] = $(this).attr('id');

  $(this).siblings().removeClass('active');
  $(this).addClass('active');

  if ($(this).attr('id') == 'COOL') {
    $('#temp-control').attr('class', 'active');
  } else {
    $('#temp-control').attr('class', 'inactive');
  }

  updateUI();
  setStateTimeout();
});

$('#up-down img').click(function() {
  if ($(this).closest('#temp-control').hasClass('active')) {
    STATE['temperature'] = getNextTemp($(this).attr('class'));

    updateUI();
    setStateTimeout();
  }
});

$('#timer-on').change(function() {
  STATE['timerOn'] = this.value;

  if (this.value != '' && this.value > 0) {
    STATE['powerStatus'] = 'ON';
  } else {
    STATE['powerStatus'] = 'OFF';
  }
  
  updateUI();
  setStateTimeout();
});

init();