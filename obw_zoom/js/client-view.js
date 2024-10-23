let meetingSDKElement = document.getElementById('meetingSDKElement');
var header = document.getElementById("header");
const zoomMeetingSDK = document.getElementById('zmmtg-root');
const subscribe = document.getElementById('subscribe');
const footer = document.getElementById('footer');


window.addEventListener('DOMContentLoaded', function(event) {
  console.log('DOM fully loaded and parsed');
  websdkready();
});

function websdkready() {
  var testTool = window.testTool;
  // get meeting args from url
  //var tmpArgs = testTool.parseQuery();  
  var tmpArgs = drupalSettings;
  console.log(tmpArgs);
  if (tmpArgs.url_node == null) {
    var leaveroom = '/events/meeting-zoom';
  } else {
    var leaveroom = tmpArgs.url_node;
  }
  var meetingConfig = {    
    apiKey: tmpArgs.apiKey,
    apiSecret: tmpArgs.apiSecret,
    meetingNumber: tmpArgs.mn,

    userName: (function () {
      if (tmpArgs.name) {
        try {
          return testTool.b64DecodeUnicode(tmpArgs.name);
        } catch (e) {
          return tmpArgs.name;
        }
      }
      return (
        "CDN#" +
        tmpArgs.version +
        "#" +
        testTool.detectOS() +
        "#" +
        testTool.getBrowserInfo()
      );
    })(),
    passWord: tmpArgs.pwd,
    leaveUrl: leaveroom,
    role: parseInt(tmpArgs.role, 10),
    userEmail: (function () { 
      try {
        return testTool.b64DecodeUnicode(tmpArgs.email);
      } catch (e) {
        return tmpArgs.email;
      }
    })(),
    lang: tmpArgs.lang,
    //signature: tmpArgs.signature || "",    
    china: tmpArgs.china === "1",
  };
 

  // a tool use debug mobile device
  if (testTool.isMobileDevice()) {
    vConsole = new VConsole();
  }
  console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

  subscribe.style.display = 'none';
  footer.style.display = 'none';
  zoomMeetingSDK.style.display = 'block';
  
  ZoomMtg.preLoadWasm();
  ZoomMtg.prepareJssdk();
  ZoomMtg.setZoomJSLib('https://source.zoom.us/2.4.5/lib', '/av')   


  var signature = ZoomMtg.generateSignature({
    meetingNumber: tmpArgs.mn,
    apiKey: tmpArgs.apiKey,
    apiSecret: tmpArgs.apiSecret,
    role: tmpArgs.role,
    success: function(res){
        console.log(res.result);
    }
  });
  //function beginJoin(signature) {
    ZoomMtg.init({
      leaveUrl: meetingConfig.leaveUrl,
      webEndpoint: meetingConfig.webEndpoint,
      disableCORP: !window.crossOriginIsolated, // default true
      // disablePreview: false, // default false
      success: function () {
        console.log(meetingConfig);
        console.log("signature", signature);
        ZoomMtg.i18n.load(meetingConfig.lang);
        ZoomMtg.i18n.reload(meetingConfig.lang);
        ZoomMtg.join({
          meetingNumber: meetingConfig.meetingNumber,
          userName: meetingConfig.userName,
          signature: signature,
          apiKey: meetingConfig.apiKey,
          userEmail: meetingConfig.userEmail,
          passWord: meetingConfig.passWord,

          success: function (res) {
            console.log("join meeting success");
            console.log("get attendeelist");
            ZoomMtg.getAttendeeslist({});
            ZoomMtg.getCurrentUser({
              success: function (res) {
                console.log("success getCurrentUser", res.result.currentUser);
                var full_screen_widget = document.querySelector('.full-screen-widget');
                full_screen_widget.addEventListener("click", function(e) {      
                  header.classList.toggle("hidden");
                });
              },
            });
          },
          error: function (res) {
            console.log(res);
          },
        });
      },
      error: function (res) {
        console.log(res);
      },
    });

    ZoomMtg.inMeetingServiceListener('onUserJoin', function (data) {
      console.log('inMeetingServiceListener onUserJoin', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onUserLeave', function (data) {
      console.log('inMeetingServiceListener onUserLeave', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onUserIsInWaitingRoom', function (data) {
      console.log('inMeetingServiceListener onUserIsInWaitingRoom', data);
    });
  
    ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
      console.log('inMeetingServiceListener onMeetingStatus', data);
    });
  //}

  //beginJoin(meetingConfig.signature);
};


document.addEventListener("click keydown keyup onkeydown onkeyup", function(){
  if(e.keyCode===27 || e.keyCode===122 || e.keyCode===116 ){
    e.preventDefault();     
  }
});
document.addEventListener('fullscreenchange', event => {
  if (document.fullscreenElement) {
    console.log(`Element: ${document.fullscreenElement.id} entered fullscreen mode.`);
  } else {        
    header.classList.remove("hidden");
  }
});

document.onkeydown = function(e) {
  if(e.keyCode == 123) {
      return false;
  }
  if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
      return false;
  }
  if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
      return false;
  }
  if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
      return false;
  }
  if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
      return false;
  }
}