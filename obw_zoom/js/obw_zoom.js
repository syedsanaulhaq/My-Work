ZoomMtg.preLoadWasm();
ZoomMtg.prepareJssdk();
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
var apiKey = '3xoEkwMjSAqIspyNtmetyQ';
var signature = 'M3hvRWt3TWpTQXFJc3B5TnRtZXR5US45Mjg3NDk1MjE0OS4xNjQwODUyMzc1MDAwLjAuQlpjMFovR2tWYWt3NVhKSUVVekRIL2t3MkJRWVIycmNQR1VIdzZ0cGdpWT0';
var meetingNumber = '92874952149';
var userName = 'teast';
var password = 'clh4WUVUbDBIYXlaR2VXK2trSTJIdz09';

ZoomMtg.join({
  apiKey: apiKey, 
  signature: signature,
  meetingNumber: meetingNumber,
  password: password,
  userName: userName
});
