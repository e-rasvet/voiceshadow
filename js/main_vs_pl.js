window.recordmark = 0;

insertAtCaret = function(areaId,text) {
        var txtarea = document.getElementById(areaId);
        var scrollPos = txtarea.scrollTop;
        var strPos = 0;
        
        strPos = txtarea.selectionStart;

        var front = (txtarea.value).substring(0,strPos);
        var back = (txtarea.value).substring(strPos,txtarea.value.length);
        txtarea.value=front+text+back;
        strPos = strPos + text.length;
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
        txtarea.scrollTop = scrollPos;
};

$.fn.getCursorPosition = function() {
        var el = $(this).get(0);
        var pos = 0;
        if('selectionStart' in el) {
            pos = el.selectionStart;
        } else if('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
};

$.fn.setCursorPosition = function(pos) {
        if ($(this).get(0).setSelectionRange) {
            $(this).get(0).setSelectionRange(pos, pos);
        } else if ($(this).get(0).createTextRange) {
            var range = $(this).get(0).createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
}


try {
    var recognition = new webkitSpeechRecognition();
} catch(e) {
    var recognition = Object;
}
recognition.continuous = true;
recognition.interimResults = true;
recognition.lang = "en";

var interimResult = '';

recognition.onresult = function (event) {
    //console.log(event);
    
    var pos = $('#speechtext').getCursorPosition() - interimResult.length;
    $('#speechtext').val($('#speechtext').val().replace(interimResult, ''));
    interimResult = '';
    $('#speechtext').setCursorPosition(pos);
    for (var i = event.resultIndex; i < event.results.length; ++i) {
      if (event.results[i].isFinal) {
          insertAtCaret('speechtext', event.results[i][0].transcript);
      } else {
          isFinished = false;
          insertAtCaret('speechtext', event.results[i][0].transcript + '\u200B');
          interimResult += event.results[i][0].transcript + '\u200B';
      }
    }
    
    var textarea = document.getElementById('speechtext');
    textarea.scrollTop = textarea.scrollHeight;
        
    window.recordmark = 1;
};

recognition.onstart = function() {
    $('#p-start-record').html("Stop transcribing");
    $('#p-rec-notice').text("Now recording.");
    $('#p-rec-notice').addClass("p-mic-active");
    $('#speechtext').focus();
    window.recordmark = 1;
};

recognition.onend = function() {
    $('#p-start-record').html("Start transcribing");
    $('#p-rec-notice').text("Click \"Start transcribing\" button when you are ready.");
    $('#p-rec-notice').removeClass("p-mic-active");
    window.recordmark = 0;
    
    var id = 'transcript_'+$('.selectaudiomodel').val();
    //console.log($('.selectaudiomodel').val()+"/"+$("input[name='instanceid']").val()+"-selected");
    //console.log($('#'+id).text()+"/"+$("#speechtext").val());
    $.post( "ajax-score.php", { text1: $('#'+id).text(), text2: $("#speechtext").val() }, function( data ) {
      $('#p-rec-notice').text("computerized score: "+data+"%");
    });
};


$( document ).ready(function() {
    //var audioElement = document.createElement('audio');
    
    $('#p-start-record').click(function() {
      if (window.recordmark == 0) {
        recognition.start();
        window.recordmark = 1;
      } else {
        recognition.stop();
        window.recordmark = 0;
        setTimeout('$("#speechtext").val(function(i, text) {return text + " "});', 1300);
      }
    });
    
    $('#p-clear-text').click(function() {
      $('#speechtext').val("");
    });
    
    $('#p-speech-text').click(function() {
      if ($('#speechtext').val().length > 100)
        window.open("http://tts-api.com/tts.mp3?q="+encodeURIComponent($('#speechtext').val()),'_new');
      else
        window.open("http://translate.google.com/translate_tts?ie=utf-8&tl=en&q="+encodeURIComponent($('#speechtext').val()),'_new');
        
      //audioElement.setAttribute('src', "getmp3.php?t="+encodeURIComponent($('#speechtext').val()));
      //audioElement.load();
      //audioElement.play();
    });
    
    $('.p-header').click(function() {
      $('.p-content').toggle();
    });
    
    $('audio.startSST').on('playing', function() {
      if ($('.p-content').length > 0) {
        //$('#speechtext').val("");
        $('.p-content').show();
        if (window.recordmark == 0) {
          recognition.start();
          window.recordmark = 1;
        }
      }
    });
    
    $('audio.startSST').on('ended', function() {
      if ($('.p-content').length > 0) {
        if (window.recordmark == 1) {
          recognition.stop();
          window.recordmark = 0;
          setTimeout('$("#speechtext").val(function(i, text) {return text + " "});', 1300);
        }
      }
    });
    
    $('audio.startSST').on('pause', function() {
      if ($('.p-content').length > 0) {
        if (window.recordmark == 1) {
          recognition.stop();
          window.recordmark = 0;
          setTimeout('$("#speechtext").val(function(i, text) {return text + " "});', 1300);
        }
      }
    });

});


