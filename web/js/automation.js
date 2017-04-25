
var STATE_TIMEOUT = null;

var state = {
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
  state['powerStatus'] = $('#power-icon').attr('data-powered');
  state['mode'] = $('#mode svg.active').attr('id');
  state['fanSpeed'] = $('#fan-icon').attr('data-speed');
  state['temperature'] = $('#temp-control .temperature').text().trim();
  state['timerOn'] = $('#timer-on').val();

  //console.log(state);

  $.ajax({
    url: 'action/action.php?r=' + (Math.floor(Math.random() * 10000)),
    data: {
      state: JSON.stringify(state)
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

function updateUI(state) {
  $('#power-icon').attr('data-powered', state.powerStatus);
  $('#fan-icon').attr('data-speed', state.fanSpeed).attr('class', state.powerStatus);
  $('#temp-control .temperature').text(state.temperature);
  $('#timer-on').val(state.timerOn);

  $('#mode svg#' + state.mode).siblings().removeClass('active');
  $('#mode svg#' + state.mode).addClass('active');

  if (state.mode == 'COOL') {
    $('#temp-control').attr('class', 'active');
  } else {
    $('#temp-control').attr('class', 'inactive');
  }
}

function init() {
  $.ajax({
    url: 'action/action.php?r=' + (Math.floor(Math.random() * 10000)),
    dataType: 'JSON',
    success: function(data) {
      updateUI(data);

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
  var ele = $('#temp-control .temperature');
  var cur = parseInt(ele.text().trim());

  if (btn == 'arrowUp' && cur < 30) {
    cur++;
  } else if (btn == 'arrowDown' && cur > 17) {
    cur--;
  }

  ele.text(cur);
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
  $(this).attr('data-state', getNextBulbState($(this).attr('data-state')));
  //setStateTimeout();
});

$('#fan-icon').click(function() {
  if ($(this).hasClass('ON')) {
    $(this).attr('data-speed', getNexFanSpeed($(this).attr('data-speed')));
    setStateTimeout();
  }
});

$('#power-icon').click(function() {
  var ps = getNextPowerState($(this).attr('data-powered'));
  $(this).attr('data-powered', ps);
  $(this).next().attr('class', ps);
  setStateTimeout();
});

$('#mode svg').click(function() {
  $(this).siblings().removeClass('active');
  $(this).addClass('active');

  if ($(this).attr('id') == 'COOL') {
    $('#temp-control').attr('class', 'active');
  } else {
    $('#temp-control').attr('class', 'inactive');
  }

  setStateTimeout();
});

$('#up-down img').click(function() {
  if ($(this).closest('#temp-control').hasClass('active')) {
    getNextTemp($(this).attr('class'));
    setStateTimeout();
  }
});

$('#timer-on').change(function() {
  setStateTimeout();
});

init();