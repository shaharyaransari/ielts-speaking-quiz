// For Recorder
let audioRecorder = null;
let recordingTime = 0;
let allowedTime = null;

// Modify Live Text From Input Field Somewhere on Page 
// Target Text Should be an HTML Element 
function modifyTargetTxt(targetID){
    let target = document.getElementById(targetID);
    target.innerHTML = this.event.target.value;
}

// Triggers Validation messages and return a boolean 
function is_IsqFormValid(formEl){
    let formValid = true;
    // let's loop through all required fields using for..of loop
    for (const field of formEl.elements) {
        // check if field is required or not 
        // We have added some 'required attribues in our HTML'
        if (field.required === true) {
            // if field is required do validation 
            // if field type is text or text area 
            if(field.type==='text' || field.type==='textarea' || field.type==='number' || field.type==='url'){
             // check if this field is empty or not
             if(field.value.trim()===''){
                // If field has error add error class to field 
                formEl.querySelector(`#${field.id}`).classList.add('error');
                formEl.querySelector(`#${field.id}`).onfocus = function(e){
                    e.target.classList.remove('error');
                }
                formValid = false;
             }
            }
            // validate email field 
            if(field.type==='email'){
                // Using Regular Expressions to Validate Email Value 
                let emailRegex = /^w+([.-]?w+)*@w+([.-]?w+)*(.w{2,3})+$/;
                if(!(field.value.match(emailRegex)) || (field.value.trim()==='')){
                    // If field has error add error class to field 
                    document.querySelector(`#${field.id}`).classList.add('error');
                    document.querySelector(`#${field.id}`).onfocus = function(e){
                    e.target.classList.remove('error');
                }
                    formValid = false;
                }
            }
          

            if(field.type === 'file'){
              if(field.value == ''){
                formValid = false;
              }
            }
        }
    }
    return formValid;
}

function showMiniNotice(){
    document.querySelector('.mini-notice-bar').classList.add('active');
}
function hideMiniNotice(){
    document.querySelector('.mini-notice-bar').classList.remove('active');
}
function display_isq_msg(text,type = 'info') {
    let notificationBox = document.querySelector(".notification-box");
    const alerts = {
      info: {
        icon: `<svg class="" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>`,
      },
      error: {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>`,
      },
      warning: {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>`,
      },
      success: {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>`,
      }
    };
    let component = document.createElement("div");
    component.className = `ielts-notification ielts-notification-${type}`;
    component.innerHTML = `${alerts[type].icon}<p>${text}</p>`;
    notificationBox.appendChild(component);
    setTimeout(() => {
      component.classList.add("active");
    }, 1); //1ms For fixing opacity on new element
    setTimeout(() => {
      component.classList.remove("active");
    }, 5000);
    setTimeout(() => {
      component.style.setProperty("height", "0", "important");
    }, 5100);
    setTimeout(() => {
      notificationBox.removeChild(component);
    }, 5700);
  }

// Audio Recording Function 

// let recorder = null;
// let totalRecordingTime = 0;
// let isqTimer = null;
// let maxRecordingTime = null; // in Seconds or null
// const updateQuizTimer = (time)=>{
//   renderTime(time,'recording-log');
// }
// function doRecordAudio( triggerEl, fileName , maxTimeAllowed = null){
//   return new Promise(resolve =>{
//     // Get User Audio Recording Device 
//     navigator.mediaDevices.getUserMedia({audio:true})
//     .then(stream => {
//       // Create Media Recorder Object
//       const mediaRecorder = new MediaRecorder(stream);

//       // Save Audio Chunks in Array
//       const audioChunks = [];
//       mediaRecorder.ondataavailable = (e) => {
//         audioChunks.push(e.data);
//       }

//       // Start Function 
//       const start = () => {

//         // Do additional Actions Here When Recording Starts
//         display_isq_msg('Recording Started','success');
//         let logEl = triggerEl.parentElement.querySelector('.recording-log');
//         logEl.classList.add('recording-effect');
//         let iconEl = triggerEl.parentElement.querySelector('.question-audio-trigger');
//         iconEl.classList.add('recording');
//         isqTimer = setInterval(countTimer, 1000,updateQuizTimer);
//         let quizActionBtns = document.querySelectorAll('.quiz-action');
//         quizActionBtns.forEach(action =>{
//           action.classList.add('disabled');
//         });
//         logEl.innerHTML = 'Recording...';
//         // console.log(triggerEl);
//         triggerEl.classList.add('recording-active');
//         triggerEl.removeAttribute('onclick');
//         // Change Event Listner for Current Trigger to Stop Recording
//         triggerEl.onclick = async function(){
//           // stop Recorder
//           if(recorder !== null){
//             const audio = await recorder.stop();
//             triggerEl.dataset.fileName = fileName.replace('.webm', '');
//             let logEl = triggerEl.parentElement.querySelector('.recording-log');
//             logEl.classList.remove('recording-effect');
//             // logEl.innerHTML = 'Recording Preview Ready Click to Play (Retry If needed)';
//             let iconEl = triggerEl.parentElement.querySelector('.question-audio-trigger');
//             iconEl.classList.remove('recording');
//             let quizActionBtns = document.querySelectorAll('.quiz-action');
//               quizActionBtns.forEach(action =>{
//                 action.classList.remove('disabled');
//               });
//             let audioPreview = triggerEl.parentElement.querySelector('.recording-preview audio');
//             let audioInput = triggerEl.parentElement.querySelector('input.question_audio_self');
//             if(audioInput.type == 'hidden'){
//               audioInput.type = 'file';
//               audioInput.value = '';
//             }
//             audioPreview.src = audio.audio.getAttribute('src');
//             audioPreview.load();

//             let audioFile = new File([audio.audioBlob], fileName, {type:"audio/webm", lastModified:new Date().getTime()})
//             let container = new DataTransfer();
//             container.items.add(audioFile);
//             let fileInputElement = triggerEl.parentElement.querySelector('input');
//             fileInputElement.files = container.files;
//           }
//           if(document.querySelector('#submit-answer')){
//             submitAnswer();
//           }
//         }
//         // start media recorder
//         mediaRecorder.start();
//       }

//       // Stop Function 

//       const stop = ()=>{
//         return new Promise(resolve => {
          
//           mediaRecorder.addEventListener('stop', () => {
//             // Do additional Actions Here When Recording Stops
//             display_isq_msg('Recording Stopped','success');
//             // console.log(triggerEl);
//             // We also need to reset the event listner back
//             clearInterval(isqTimer);
//             // console.log(totalRecordingTime);
//             renderTime(totalRecordingTime,'recording-log');
//             totalRecordingTime = 0;
//             triggerEl.classList.remove('recording-active');
//             // Change Event Listner for Current Trigger to Stop Recording
//             triggerEl.onclick = async function(){
//               // start Recorder
//                 recordAudio();
//             }


//             // Convert Audio Chunks into blob
//             const audioBlob = new Blob(audioChunks);
//             // Audio URL
//             const audioUrl = URL.createObjectURL(audioBlob);
//             // Create an Audio Object to Play
//             const audio = new Audio(audioUrl);
//             // console.log(audio);
//             const play = function(){
//               audio.play();
//               // console.log('audio playging');
//             }

//             // Send Values Back to Promise
//             resolve({
//               audioBlob,
//               audio,
//               play
//             });

//          });
//          // Stop Recording
//           mediaRecorder.stop();
//         });
//       }

//       // Send Value Back to Promise
//       resolve({
//         start,
//         stop
//       })

//     })
//   })
// }

// async function recordAudio(fileName = ''){
//   let triggerEl = this.event.target;
//   if(fileName == ''){
//     let newFileName = triggerEl.dataset.fileName;
//     if(newFileName && newFileName != ''){
//       fileName = newFileName;
//     }else{
//       fileName = 'your_audio';
//     }
//   }


//   fileName = fileName + '-' + Date.now() + '.webm';
//   this.event.stopPropagation();
//   // Get Permissions to Access Microphone
//   navigator.permissions.query({name: 'microphone'})
//   .then(permissionObj => {
//     console.log(permissionObj.state);

//   }).catch(error=>{
//     console.log('Got Error: ', error);
//     display_isq_msg('Something Went Wrong','error');
//   });

//   // Get Recorder Object
//   recorder = await doRecordAudio(triggerEl, fileName);
//   recorder.start();
// }

function RecordAudio(startedCallback = null, stoppedCallback = null, visualizerFunction= null){
  async function toggleRecording(started = null, stopped = null , visualizerFunc = null){
     if(!audioRecorder){
      // Start Recording
      const chunks = []; // array of blob objects
      const stream = await window.navigator.mediaDevices.getUserMedia({audio:true});
      audioRecorder = new MediaRecorder(stream);

      // This Event Will be Triggered when Recording is stopped 
      audioRecorder.ondataavailable = function(event){
          chunks.push(event.data);
          const audioBlob = new Blob(chunks);
          audioRecorder.stream.getTracks().forEach(t => t.stop());
          audioRecorder = null
          // Run Callback on Recording Stop
          if(stopped != null){
              stopped(audioBlob); // stopped callback
          }
      }

      audioRecorder.onstart = function(){
          if(started != null){
              started(); // started callback
          }
      }

      audioRecorder.start();
      // Run Callback on Recording Start

      // Run Visiualization Effect with Visualizer Callback
      if(visualizerFunc != null){
          analyseAudio(audioRecorder.stream, visualizerFunc)
      }
     }else{
      // Stop Recording 
      audioRecorder.stop();
     }
  }
  toggleRecording(startedCallback, stoppedCallback, visualizerFunction);
}

// Realtime Audio Visualization Helper Function for RecordAudio()
function analyseAudio(stream,visualizerFunction = null){
  const audioContext = new (window.AudioContext || window.webkitAudioContext)();
  const analyser = audioContext.createAnalyser();
  const source = audioContext.createMediaStreamSource(stream);
  analyser.fftSize = 128;
  const AudioDataArr = new Uint8Array(analyser.frequencyBinCount);
  source.connect(analyser);
  // Report Function to Acually Visualize Data 
  function reportAudioOutput(){
      analyser.getByteFrequencyData(AudioDataArr);
      let animationFrameID = null;
      visualizerFunction(AudioDataArr); // callback Handles Canvas Drawing Part
      if (audioRecorder){
          animationFrameID = requestAnimationFrame(reportAudioOutput);
      }
      else {
          audioContext.close();
          cancelAnimationFrame(animationFrameID);
      }
  }
  // Initiate reporting
  reportAudioOutput();
}

/**
 * Onclick event helper to copy content inside that element
 */
function copyElementContent(){
  let button = this.event.target;
  copyText = button.innerText;
  navigator.clipboard.writeText(copyText);
  display_isq_msg('Copied to Clipboard');
}

/**
 * Helper Function to Play and Pause Audio
 * Audio Element Should be Wrapped inside the Trigger.
 */
function playQuestionAudio(){
  let trigger = this.event.target;
  let audio = trigger.parentElement.querySelector('audio');
  let src = audio.getAttribute('src');
  let isPlaying = trigger.dataset.playing;

  if( !src ){
      display_isq_msg('No Audio Found');
      return;
  }
  
  if(isPlaying == 'false'){
      trigger.dataset.playing = 'true';
      audio.play();
      trigger.classList.add('playing');
      audio.onended = function(){
          trigger.dataset.playing = 'false';
          trigger.classList.remove('playing');
      }
  }else{
      trigger.dataset.playing = 'false';
      audio.pause();
      trigger.classList.remove('playing');
  }
}

function countTimer(callback) {
  ++totalRecordingTime;
  if(document.getElementById("time_counter")){
  document.getElementById("time_counter").value = totalRecordingTime;
  }
  callback(totalRecordingTime);
}


/**
 * Helper Function to render Time Inside an Element
 * @param {*} totalSeconds Number of Seconds
 * @param {*} elementID Id of element
 */
function renderTime(totalSeconds,elementID){
  totalSeconds = parseInt(totalSeconds);
  var hour = Math.floor(totalSeconds /3600);
  var minute = Math.floor((totalSeconds - hour*3600)/60);
  var seconds = totalSeconds - (hour*3600 + minute*60);
  if(hour < 10)
    hour = "0"+hour;
  if(minute < 10)
    minute = "0"+minute;
  if(seconds < 10)
    seconds = "0"+seconds;
  document.getElementById(elementID).innerHTML = hour + ":" + minute + ":" + seconds;
}
/**
 * @param {int} totalSeconds 
 * @returns Time String in 00:00:00 Format
 */
function getTimeString(totalSeconds){
  totalSeconds = parseInt(totalSeconds);
  var hour = Math.floor(totalSeconds /3600);
  var minute = Math.floor((totalSeconds - hour*3600)/60);
  var seconds = totalSeconds - (hour*3600 + minute*60);
  if(hour < 10)
    hour = "0"+hour;
  if(minute < 10)
    minute = "0"+minute;
  if(seconds < 10)
    seconds = "0"+seconds;
  return hour + ":" + minute + ":" + seconds;
}

// Function to Adjust Error popup position Triggered on Mouse over event
// function positionGError(){
//   let currentEl = this.event.target;
//   let containerEl = document.querySelector('.isq-quiz-content-wrap');
//   this.event.stopPropagation();
//   // Get .ktooltiptext sibling
//   if(! currentEl.querySelector(".g-error-popup")){
//       return ;
//   }
//   let tooltip = currentEl.querySelector(".g-error-popup");
//   console.log(currentEl);
//   tooltip_rect = tooltip.getBoundingClientRect();
//   //   console.log(tooltip_rect);
//   console.log(tooltip_rect.x + tooltip_rect.width);
//   if ((tooltip_rect.x + tooltip_rect.width) > window.innerWidth){
//     let newXposition = tooltip_rect.width - 20;
//     tooltip.style.left = `-${newXposition}px`;
//   }

// }
function positionGError() {
  this.event.stopPropagation();

  let currentEl = this.event.target;
  let containerEl = document.querySelector('.isq-quiz-content-wrap');
  if(!containerEl){
    containerEl = document.querySelector('.result-left-wrapper');
    if(!containerEl){
      return;
    }
  }
  // Get .g-error-popup sibling
  let tooltip = currentEl.querySelector(".g-error-popup");

  if (!tooltip) {
    return;
  }

  let tooltip_rect = tooltip.getBoundingClientRect();
  let container_rect = containerEl.getBoundingClientRect();
  // Check if the tooltip goes beyond the right edge of the container
  if (tooltip_rect.right > container_rect.right) {
    let newXposition = tooltip_rect.width - 20;
    tooltip.style.left = `-${newXposition}px`;
  }

  // You can add more conditions for other edges (left, top, bottom) if needed
}


/**
 * 
 * @returns Recorder Module HTML Element.
 */
function getRecorderMarkup(){
  let markup = document.createElement('div');
  markup.classList.add('recorder-module-inner-wrap');
  markup.innerHTML = 
  `<div class="recorder-log">
      <span class="recording-active-msg">
          <span class="recording-dot"></span>
          Recording...
      </span>
  </div>
  <input type="file" id="recorded-audio">
  <div class="recorder-module">
      <canvas class="recorder-module-canvas"></canvas>
      <div class="recorder-module-trigger" onclick="doRecording()" data-file-name="your-audio">
          <div class="recorder-icon recorder-icon-start">
              <svg xmlns="http://www.w3.org/2000/svg" fill="#12c99b" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
          </div>
          <div class="recorder-icon recorder-icon-stop">
              <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>   
          </div>
      </div>
  </div>`;
  return markup;
}

/**
 * 
 * @param {string} title any string or question title
 * @param {string} ext support extension is only webm for now
 * @returns Modified Title
 */
function createFileNameFromTitle(title, ext = 'webm'){
  let date = Date.now();
  title.trim();
  title.toLowerCase();
  title.replace(/ /g,"-");
  title = `${title}-${date}.${ext}`;
  return title;
}