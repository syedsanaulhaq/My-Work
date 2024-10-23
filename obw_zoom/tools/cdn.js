/* eslint-disable no-undef */
window.addEventListener('DOMContentLoaded', function (event) {
  console.log('DOM fully loaded and parsed');
  websdkready();
});

function websdkready() {
  var testTool = window.testTool;
  // get meeting args from url
  //var tmpArgs = testTool.parseQuery();
  var tmpArgs = drupalSettings;

  var meetingConfig = {
    apiKey: tmpArgs.apiKey,
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
    leaveUrl: "/events/webinar-zoom",
    role: parseInt(tmpArgs.role, 10),
    userEmail: (function () {
      try {
        return testTool.b64DecodeUnicode(tmpArgs.email);
      } catch (e) {
        return tmpArgs.email;
      }
    })(),
    lang: tmpArgs.lang,
    signature: tmpArgs.signature || "",
    china: tmpArgs.china === "1",
  };

  // a tool use debug mobile device
  if (testTool.isMobileDevice()) {
    vConsole = new VConsole();
  }

  // var signature = ZoomMtg.generateSignature({
  //   meetingNumber: tmpArgs.mn,
  //   apiKey: tmpArgs.apiKey,
  //   apiSecret: tmpArgs.apiSecret,
  //   role: tmpArgs.role,
  //   success: function(res){
  //       console.log(res.result);
  //   }
  // });
  // if (!meetingConfig.signature) {
  //   window.location.href = "./meeting.html";
  // }
  // WebSDK Embedded init 
  var rootElement = document.getElementById('meetingSDKElement');
  var meetingSDKChatElement = document.getElementById('meetingSDKChatElement');
  var zmClient = ZoomMtgEmbedded.createClient();
  var header = $("#header").outerHeight();
  var discordW = $("#discord-height").innerWidth();
  let h = window.innerHeight;
  let w = window.innerWidth;
  if (w > 1025) {
    var discordH = (h - header) - 32;
    var zoomW = 600;
  } else {
    var zoomW = w;
    var discordH = (h - header) / 2;
  }
  console.log("apiKey" + meetingConfig.apiKey);

  zmClient.init({
    debug: true,
    zoomAppRoot: rootElement,
    webEndpoint: meetingConfig.webEndpoint,
    language: meetingConfig.lang,
    customize: {
      video: {
        isResizable: false,
        viewSizes: {
          default: {
            width: zoomW,
            height: discordH
          },
          ribbon: {
            width: 300,
            height: 300
          }
        },
        popper: {
          disableDraggable: true
        }
      },

      chat: {
        popper: {
          disableDraggable: true,
          anchorElement: meetingSDKChatElement,
          PopperPlacementType: 'right-end'
        }
      },
      meetingInfo: ['topic', 'host', 'mn', 'pwd', 'telPwd', 'invite', 'participant', 'dc', 'enctype'],
      toolbar: {
        buttons: [{
          text: 'CustomizeButton',
          className: 'CustomizeButton',
          onClick: () => {
            console.log('click Customer Button');
          }
        }]
      }
    }
  }).then((e) => {
    console.log('success', e);
  }).catch((e) => {
    console.log('error', e);
  });

  // WebSDK Embedded join 
  zmClient.join({
    apiKey: meetingConfig.apiKey,
    signature: meetingConfig.signature,
    meetingNumber: meetingConfig.meetingNumber,
    userName: meetingConfig.userName,
    password: meetingConfig.passWord,
    userEmail: meetingConfig.userEmail,
  }).then((e) => {
    console.log('success', e);
  }).catch((e) => {
    console.log('error', e);
  });
};


$(document).ready(function () {
  var clickbtn = setInterval(function () {
    var isExsist = $("#suspension-view-tab-thumbnail-gallery");
    if (isExsist) {
      $("#suspension-view-tab-thumbnail-gallery").trigger("click");
    }
    clearInterval(clickbtn);
  }, 100);
  // var checkExist = setInterval(function () {
  //   let h = window.innerHeight;
  //   let w = window.innerWidth;
  //   let btnsBot = $(".zmwebsdk-MuiPaper-root.zmwebsdk-makeStyles-root-50.zmwebsdk-MuiPaper-elevation1.zmwebsdk-MuiPaper-rounded").outerHeight();
  //   let btnsTop = $(".zmwebsdk-MuiToolbar-root.zmwebsdk-MuiToolbar-regular.zmwebsdk-makeStyles-topbar-23.zmwebsdk-makeStyles-root-24.zoommtg-drag-video").outerHeight();
  //   var header = $("#header").outerHeight();
  //   var discordH = h - header;
  //   if (w > 1025) {
  //     $(".zmwebsdk-makeStyles-wrap-175").css("height", (discordH - 200) + "px");
  //     // $(window).resize(function () {
  //     //   location.reload();
  //     // });
  //     $("#meetzoom").css("max-height", discordH + "px");
  //     $("#discord-height").css("max-height", discordH + "px");
  //     $("#meetingSDKElement").css("max-height", discordH + "px");

  //     $("#discord-height iframe").css("height", discordH + "px");
  //     $("#meetingSDKElement").css("height", discordH + "px");
  //     $("#meetingSDKElement .zmwebsdk-MuiBox-root.zmwebsdk-MuiBox-root.zmwebsdk-makeStyles-root-34.zmwebsdk-makeStyles-root-40").css("height", discordH + "px");
  //     $("#meetingSDKElement .zmwebsdk-MuiBox-root.zmwebsdk-MuiBox-root.zmwebsdk-makeStyles-root-170.zmwebsdk-makeStyles-root-174").css("height", (discordH) + "px");
  //     console.log("checking size");
  //   } else {
  //     console.log("checking size");
  //     var discordH = h - header;
  //     console.log(discordH);
  //     $("#meetzoom").css("max-height", discordH / 2 + "px");
  //     $("#discord-height").css("max-height", discordH / 2 + "px");
  //     $("#discord-height iframe").css("height", discordH / 2 + "px");
  //     $('div[aria-labelledby="suspension-view-tab-speaker"]').css("height", discordH / 2 + "px");
  //     $('div[aria-labelledby="suspension-view-tab-ribbon"]').css("height", discordH / 2 + "px");

  //     $("#zoommeetcontainer #meetingSDKElement .zmwebsdk-makeStyles-root-40").click(function () {
  //       $(this).toggleClass("show-visiter");
  //       $("#meetingSDKElement .zmwebsdk-MuiPaper-root.zmwebsdk-makeStyles-root-50.zmwebsdk-MuiPaper-elevation1.zmwebsdk-MuiPaper-rounded").toggleClass("show-bar");
  //     });
  //   }
  // }, 500);

});
