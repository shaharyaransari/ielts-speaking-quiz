let results_obj = {
    try_number: parseInt(wpdata.try_number),
    total_tries: 1,
    time: Date.now(),
    ending_time : null,
    quiz_id : parseInt(wpdata.quiz_id),
    result_elements: wpdata.quiz_elements
};

// For Quiz Progress Tracking current context
let currentSpeakingPart = null; // Data Object
let currentQuestion = null; // Data Object
let currentSpIndex = 0;
let currentQuestionIndex = 0;

// Some Additional Flags
let loadingSP = false;
let loadingQuestion = false;
function enableLoadingEffect(){
    let wrapper = document.querySelector('#isq-quiz-content-wrap');
    wrapper.classList.add('loading');
}
function disableLoadingEffect(){
    let quizContentWrap = document.querySelector('.isq-quiz-content-wrap');
    quizContentWrap.classList.remove('loading');
}
// Function for Getting Next Speaking Part Index 
function getNextSpIndex(){
    let nextIndex = currentSpIndex + 1;
    if(wpdata.quiz_elements[nextIndex]){
        return nextIndex;
    }else{
        return null;
    }
}

/**
 * Helper Function get Total Recorded Time for Current Speaking Part
 * @param {*} spIndex 
 * @returns 
 */
function getSpRecordedTime(spIndex){
    let totalaudiolength = 0;
    results_obj.result_elements[spIndex]['questions'].forEach(question => {
        if(question.audio_length){
            totalaudiolength += parseInt(question.audio_length);
        }
    });
    return totalaudiolength;
}

// Function For Combining Transcript 
function getSpeakingPartTranscript(spIndex){
    let combinedTranscript = '';
    results_obj.result_elements[spIndex]['questions'].forEach(question => {
        if(question.transcript){
            combinedTranscript += question.transcript;
        }
    });
    return combinedTranscript;
}

// Function for Getting Speaking Part Audio Files 
function getSpeakingPartAudioFiles(spIndex){
    let audio_files = [];
    results_obj.result_elements[spIndex]['questions'].forEach(question => {
        if(question.audio_length){
            audio_files.push(question.audio_url);
        }
    });
    return audio_files;
}

/**
 * Helper Function to get Next Question Index With Respect to current active Question
 * @returns Null on failure and index Object on Success
 */
function getNextQuestionIndex(){
    let nextQuestionIndex = currentQuestionIndex + 1;
    if(wpdata.quiz_elements[currentSpIndex]['questions'][nextQuestionIndex]){
        return {
            'question_index' : nextQuestionIndex,
            'sp_index' : currentSpIndex
        };
    }else{
        let nextSpIndex = getNextSpIndex();
        if(nextSpIndex){
            return {
                'question_index' : 0,
                'sp_index' : nextSpIndex
            };
        }else{
            return null;
        }
    }
}

/**
 * 
 * @param {*} spIndex Index of Speaking Part in Quiz Elements Object
 * @returns Id of Speaking Part
 */
function getSpeakingPartIdByIndex(spIndex){
    return parseInt(wpdata.quiz_elements[spIndex]['speaking_part_id']);
}

/**
 * 
 * @param {*} spIndex index of Speaking Part in Quiz Elements Object
 * @param {*} questionIndex index of Question in Quiz Elements Object
 * @returns Id of Question
 */
function getQuestionIdByIndex(spIndex,questionIndex){
    return parseInt(wpdata.quiz_elements[spIndex]['questions'][questionIndex]['question_id']);
}

/**
 * Helper Function to load Speaking Part Data
 * @param {*} speakingPartID Id of Speaking Part
 * @returns JSON Object
 */
async function loadSpeakingPartData(speakingPartID){
    loadingSP = true;
    enableLoadingEffect();
    let data = await fetch(wpdata.root +'ielts-speaking-quiz/v2/speaking-part/'+speakingPartID)
    .then(res=>res.json())
    .then(response => {
        return response;
    });
    disableLoadingEffect();
    loadingSP = false;
    return data;
}

/**
 * Helper Function to load Question Data
 * @param {*} questionId Id of Question
 * @returns JSON Object
 */
async function loadQuestionData(questionId){
    loadingQuestion = true;
    enableLoadingEffect();
    let data = await fetch(wpdata.root +'ielts-speaking-quiz/v2/question/'+questionId)
    .then(res=>res.json())
    .then(response => {
        return response;
    });
    disableLoadingEffect();
    loadingQuestion = false;
    return data;
}
// Recorder Code Ended

// Do Recording Function Started Can be Customized According to needs
function doRecording(){
    let trigger = this.event.target;
    // Should be taken from DOM
    let fileName = trigger.dataset.fileName;
    let fileElement = document.querySelector('#recorded-audio');
    let logger = document.querySelector('.recorder-module-inner-wrap .recorder-log');
    // Question Response Wrapper
    let responseWrap = document.querySelector('#question-response-wrap');
    let responseEl = responseWrap.querySelector('.question-response-content');
    let improveAnsTrigger = document.querySelector('#improve-response-trigger');
    let retryBtn = document.querySelector('#retry-btn');
    let submitBtnInner = document.querySelector('#submit-quiz-btn-inner');
    let nextQuizElBtn = document.querySelector('#next-quiz-el-btn');
    // Settings 
    let timer = null;
    let recordingTime = 0;
    let spAllowedTime = currentSpeakingPart.allowed_rec_time;
    let allowedTime = null;
    let questionRTime = 0;
    let spRecordedTime = 0;
    
    // callback function when recording is stopped by recorder
    function recordingStopped(blob){
        // stop timer
        clearInterval(timer);
        // do whatever with audio blob
        // Temp URL 
        // let tempURL = URL.createObjectURL(blob); // Can Use If needed

        // Creating File For attaching with input type file element 
        let audioFile = new File([blob], fileName, {type:"audio/webm", lastModified:new Date().getTime()})
        let container = new DataTransfer();
        container.items.add(audioFile);
        fileElement.files = container.files;
        // Remove Recording Message 
        logger.classList.remove('active');
        trigger.classList.remove('recording');
        display_isq_msg('Recording Ready', 'success');
        responseEl.innerHTML = 'Analysing Audio..';
        // Send Audio to Whisper And Grammer APIs
        // Preparing Data
        let audioData = new FormData();
        audioData.append('action', 'get_audio_transcript');
        audioData.append('nonce', wpdata.transcript_nonce);
        audioData.append('recorded_audio', fileElement.files[0]);
        fetch(wpdata.ajaxurl,{
            method: 'post',
            body: audioData
        }).then(res=>res.json())
        .then(response => {
            if(response.success){
                if(response.data.success){
                    if(response.data.transcript == null || response.data.transcript == 'null'){
                        display_isq_msg("Error Occured in Loading Transcript", 'error');
                        return;
                    }
                    // Everything Fine Proceed
                    let transcript = response.data.transcript;
                    // Save Data in Result Object
                    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attachment_id = response.data.attachment_id;
                    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_url = response.data.url;
                    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_length = recordingTime;
                    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].transcript = transcript;
                    responseEl.innerHTML = `${transcript} <br> <span class='temp-quiz-msg'>Analysing Grammar Issues...</span>`;
                    spRecordedTime = getSpRecordedTime(currentSpIndex);
                    // Save Combined Speaking Part Data 
                    results_obj.result_elements[currentSpIndex]['audio_length'] = spRecordedTime;
                    results_obj.result_elements[currentSpIndex]['audio_files'] = getSpeakingPartAudioFiles(currentSpIndex);
                    results_obj.result_elements[currentSpIndex]['transcript'] = getSpeakingPartTranscript(currentSpIndex);
                    // Send Data to Grammer API
                    let GrammerApiData = new FormData();
                    GrammerApiData.append('transcript', transcript);
                    GrammerApiData.append('action', 'get_grammer_corrections');
                    fetch(wpdata.ajaxurl,{
                        method: 'post',
                        body: GrammerApiData
                    }).then(res=> res.json())
                    .then(correctionData => {
                        if(correctionData.success){
                            if(!(correctionData.data)){
                                display_isq_msg('Something Went Wrong', 'error');
                                console.log(correctionData);
                                return;
                            };
                            if(correctionData.data.corrections === null){
                                display_isq_msg('Something Went Wrong', 'error');
                                return;
                            }
                            let corrections = correctionData.data.corrections.matches;
                            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].corrections = corrections;
                            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].gpt_input  = formatCorrectionsForChatGPT(corrections);
                            let formattedTranscript = transcript;
                            if(corrections.length > 0){
                                for(let i=0; i < corrections.length ; i++){
                                
									let offset = corrections[i].offset;
									let length = corrections[i].length;
// 									console.log(offset,length);
                                    let placehoder = `PLACEHOLDER_${i}`;
                                    let incorrectText = transcript.substr(offset,length);
// 									console.log(offset,length,incorrectText);
                                   
                                    formattedTranscript = formattedTranscript.replace(incorrectText, placehoder);
                                }
                            }
							if(corrections.length > 0){
								for(let i=0; i < corrections.length ; i++){
								let shortMessage = corrections[i].shortMessage;
								let message = corrections[i].message;
								let replacements = corrections[i].replacements;
								let replacementsHtml = '';
								let offset = corrections[i].offset;
								let length = corrections[i].length;
								let incorrectText = transcript.substr(offset,length);
								if(replacements.length > 0){
                                        for(let x=0; x < replacements.length; x++){
                                            replacementsHtml += `<span>${replacements[x].value}</span>`;
                                        }
                                    }
								 let errorId = `error-${i}`;
									let placehoder = `PLACEHOLDER_${i}`;
                                    let errorPopup = `<span class="g-error-wrap" data-id="${errorId}" onmouseover="positionGError()">
                                    <span class="g-error">${incorrectText}</span>
                                        <span class="g-error-popup">
                                            <span class="g-error-short-msg">${shortMessage}</span>
                                            <span class="g-error-long-msg">${message}</span>
                                            <span class="g-error-replacements">${replacementsHtml}</span>
                                        </span>
                                    </span>`;
								formattedTranscript = formattedTranscript.replace(placehoder, errorPopup);
								}
							}
                            formattedTranscript = formattedTranscript.replaceAll('\\', '');
                            // Save Data in Data Object
                            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attempted = true;
                            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].formatted_transcript = formattedTranscript;
                            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].improved_answer = '';
                            responseEl.innerHTML = `${formattedTranscript}`;
                            improveAnsTrigger.classList.remove('action-disabled');
                            retryBtn.classList.remove('action-disabled');
                            submitBtnInner.classList.remove('action-disabled');
                            nextQuizElBtn.classList.remove('action-disabled');
                            // Update Navigation Elements
                            markQuestionAsComplete();
                        }
                    });
                }else{
                    display_isq_msg('Something Went Wrong');
                    console.log(response);
                }
            }else{
                display_isq_msg('Something Went Wrong');
                console.log(response);
            }
        })
    }
    // callback function when recording is Started by recorder
    function recordingStarted(){
        // start timer
        recordingTime = 0;
        timer = setInterval(countRecordingTime,1000);
        logger.classList.add('active');
        trigger.classList.add('recording');
        retryBtn.classList.add('action-disabled');
        submitBtnInner.classList.add('action-disabled');
        nextQuizElBtn.classList.add('action-disabled');
        display_isq_msg('Recording Started', 'success');
        // Reset Question Time 
        results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_length = 0;
        spRecordedTime = getSpRecordedTime(currentSpIndex);
        results_obj.result_elements[currentSpIndex]['audio_length'] = spRecordedTime;
        allowedTime = parseInt(spAllowedTime) - parseInt(spRecordedTime);
        // console.log(spAllowedTime, spRecordedTime);
        display_isq_msg(`You can record for Max ${allowedTime} Seconds`);
        let currentQuestion = results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex];
        if("attachment_id" in currentQuestion){ // means Question was Attempted Before

            if(currentQuestion.attachment_id){
                
                // Delete Audio
                let previsousAudio = results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attachment_id;
                deleteAudio(previsousAudio);
                results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attachment_id = null;
                // Process For Resetting Question
                resetQuestionObject();
                resetQuestionCompletion();
                // Hide Improved Answer
                let improvedAnswerWrap = document.querySelector('#improved-answer-wrap')
                improvedAnswerWrap.classList.remove('active');
                improvedAnswerWrap.querySelector('.improved-answer-content').innerHTML = 'Thinking...';
            }
        }
    }

    function countRecordingTime(){
        ++recordingTime;
        ++spRecordedTime;
        renderTime(spRecordedTime, 'timeCounter');
        if(allowedTime && recordingTime > allowedTime ){
            // Trigger Same Function Again to stop Recording
            RecordAudio(recordingStarted, recordingStopped);
        }
    }

    // Function to draw the audio waveform on the canvas with live audio input
    function drawWaveform(dataArray) {
        console.log(dataArray);
    }

    RecordAudio(recordingStarted, recordingStopped);
}
// Do Recording Function Ended 

/**
 * Helper Fuctions to Render Speaking Part on Page
 * @param {*} spIndex Index of Current Speaking Part
 */
async function loadSpeakingPart(spIndex){
    let template = document.querySelector('#speaking-part-template');
    let wrapper = document.querySelector('#isq-quiz-content-wrap');
    let srNumber = parseInt(spIndex) + 1;
    let spId = getSpeakingPartIdByIndex(spIndex);
    let spData = await loadSpeakingPartData(spId);
    // Set Tracking Values 
    currentSpeakingPart = spData;
    currentSpIndex = spIndex;
    results_obj.result_elements[spIndex].post_data = spData;
    // Update Navigation
    updateNavigation(spIndex, false);
    // Prepare Wrapper Content 
    let node = template.content.cloneNode(true);
    node.querySelector('#isq-quiz-content-header').innerHTML = `Part ${srNumber}: ${currentSpeakingPart.post_title}`;
    node.querySelector('.isq-quiz-content-body-inner .sp-number').innerHTML = `Instructions`;
    node.querySelector('.isq-quiz-content-body-inner .sp-content').innerHTML = currentSpeakingPart.post_content;;
    
    // Empty Wrapper
    wrapper.innerHTML = '';
    wrapper.appendChild(node);
}

/**
 * Function for Starting Quiz
 */
function startSpeakingQuiz(){
    loadSpeakingPart(0);
    renderQuizNavigation();
}

async function loadQuestion(spIndex,qIndex){
    let template = document.querySelector('#ielts-question-template');
    let wrapper = document.querySelector('#isq-quiz-content-wrap');
    let spRecordedTime = getSpRecordedTime(spIndex);
    let srNumber = parseInt(qIndex) + 1;
    let qId = getQuestionIdByIndex(spIndex,qIndex);
    let qData = await loadQuestionData(qId);
    let isSeenByUser = ('seen_by_user' in results_obj.result_elements[spIndex]['questions'][qIndex]); // False if Question Never Loaded Before

    // Set Tracking Values 
    currentQuestion = qData;
    currentQuestionIndex = qIndex;
    results_obj.result_elements[spIndex]['questions'][qIndex].post_data = qData;
    if(!isSeenByUser){
        results_obj.result_elements[spIndex]['questions'][qIndex].seen_by_user = true; // Set the Seen Flag to true
    }
    // Check if Question is attempted or not
    let isAttempted = results_obj.result_elements[spIndex]['questions'][qIndex].attempted;
    let questionAudio = qData.question_audio.audio_url;
    let playerMarkup = null;
    if(questionAudio){
        playerMarkup = `
            <!-- New  -->
            <div class="recording-preview-wrap">
                <div class="recording-preview">
                    <div class="question-audio-wrap">
                        <audio src="${questionAudio}" controls="" controlslist="nodownload"></audio>
                        <div class="question-audio-trigger" onclick="playQuestionAudio()" data-playing="false">
                            <span class="play-icon audio-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"></path></svg>
                            </span>
                            <span class="recording-icon audio-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"></path></svg>
                            </span>
                            <span class="pause-icon audio-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"></path></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /New  -->
        `;
    }
    // Update Timer
    renderTime(spRecordedTime,'timeCounter');
    // Update Navigation
    updateNavigation(spIndex,qIndex);
    let isLastQuestion = getNextQuestionIndex() ? false : true;
    let recorderMarkup = getRecorderMarkup();
    let audioFileName = createFileNameFromTitle(currentQuestion.post_title);
    recorderMarkup.querySelector('.recorder-module-trigger').dataset.fileName = audioFileName;
    // Prepare Wrapper Content 
    let node = template.content.cloneNode(true);
    node.querySelector('#isq-quiz-content-header').innerHTML = `${currentSpeakingPart.post_title}`;
    node.querySelector('.isq-quiz-content-body-inner .question-number').innerHTML = `Question ${srNumber}`;
    node.querySelector('.isq-quiz-content-body-inner .question-title').innerHTML = currentQuestion.post_title;
    node.querySelector('.isq-quiz-content-body-inner .question-content').innerHTML = currentQuestion.post_content;
    if(questionAudio){
        node.querySelector('.isq-quiz-content-body-inner .question-number-wrap .player-wrap').innerHTML = playerMarkup;
    }
    node.querySelector('.question-recorder-module-wrap').appendChild(recorderMarkup);
    if(isLastQuestion){
        node.querySelector('#next-quiz-el-btn').classList.add('action-hidden');
        node.querySelector('#submit-quiz-btn-inner').classList.remove('action-hidden');
    }else{
        node.querySelector('#next-quiz-el-btn').classList.remove('action-hidden');
        node.querySelector('#submit-quiz-btn-inner').classList.add('action-hidden');
    }
    if(isAttempted){
        let questionData = results_obj.result_elements[spIndex]['questions'][qIndex];
        let responseWrap = node.querySelector('#question-response-wrap');
        let responseEl = responseWrap.querySelector('.question-response-content');
        let improveAnsTrigger = node.querySelector('#improve-response-trigger');
        let improvedAnswerWrap = node.querySelector('#improved-answer-wrap');
        let improvedAnswerEl = improvedAnswerWrap.querySelector('.improved-answer-content');
        // Show Response
        responseEl.innerHTML = questionData.formatted_transcript;
        improveAnsTrigger.classList.remove('action-disabled');
        // Show Improved Answer
        if(questionData.improved_answer){
            improvedAnswerWrap.classList.add('active');
            improvedAnswerEl.innerHTML = questionData.improved_answer;
            improveAnsTrigger.classList.add('action-disabled');
        }
    }
    
    // Empty Wrapper
    wrapper.innerHTML = '';
    wrapper.appendChild(node);
    if(questionAudio && !isSeenByUser){
        wrapper.querySelector('.question-audio-wrap .question-audio-trigger').click();
        // console.log(wrapper.querySelector('.question-audio-wrap .question-audio-trigger'));
    }
}

function getImprovedAnswer(){
    let trigger = this.event.target;
    let improvedAnswerWrap = document.querySelector('#improved-answer-wrap');
    let improvedAnswerEl = improvedAnswerWrap.querySelector('.improved-answer-content');
    let transcript = results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].transcript;
    improvedAnswerEl.innerHTML = 'Thinking...';
    improvedAnswerWrap.classList.add('active');
    trigger.classList.add('action-disabled');

    let formData = new FormData();
    formData.append('transcript', transcript);
    formData.append('action', 'get_improved_answer');
    formData.append('nonce', wpdata.openai_nonce);
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData
    }).then( res => res.json())
    .then(response => {
        if(response.success){
            let improvedAnswer = response.data;
            improvedAnswerEl.innerHTML = response.data;
            results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].improved_answer = improvedAnswer;
        }else{
            improvedAnswerWrap.classList.remove('active');
            display_isq_msg('Somthing Went Wrong', 'error');
        }
    });

}

function resetQuestionData(){
    // let audio_length = results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_length;
    let attachment_id = results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attachment_id;
    deleteAudio(attachment_id);
    resetQuestionObject();
    // Load Question Again
    loadQuestion(currentSpIndex,currentQuestionIndex);
    // Update Navigation Elements
    resetQuestionCompletion();
    display_isq_msg('Question Refreshed');
}

function resetQuestionObject(){
    // results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].improved_answer = '';
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attachment_id = null;
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_url = '';
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].audio_length = null;
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].transcript = '';
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].attempted = false;
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].formatted_transcript = '';
    results_obj.result_elements[currentSpIndex]['questions'][currentQuestionIndex].improved_answer = '';
}

function showNextQuizEl(){
    let nextQuestionIndex = getNextQuestionIndex();
    if(nextQuestionIndex){
        // Current index is greater then next question index so next Element is speaking part
        if(currentQuestionIndex > nextQuestionIndex.question_index){
            loadSpeakingPart(nextQuestionIndex.sp_index);
        }else{
            loadQuestion(nextQuestionIndex.sp_index, nextQuestionIndex.question_index)
        }
    }
}

function showFirstSpEl(){
    loadQuestion(currentSpIndex, 0);
}

function renderQuizNavigation(){
    let quizElments = results_obj.result_elements;
    let navigationEl = document.querySelector('#isq-footer');
    let markup = document.createElement('div');
    markup.classList.add('quiz-naviation');
    let navElements = '';
    for(let x=0;x < quizElments.length; x++){
        let partSrNumber = x+1;
        let questions = quizElments[x]['questions'];
        navElements += `<div class="sp-nav" id="sp-nav-${x}">
            <div class="sp-srnumber">Part ${partSrNumber}</div>
            <div class="part-progress" onclick="loadSpeakingPart(${x})">
             <span id="part-${x}-completed">0</span>
              out of 
             <span id="part-${x}-total">${questions.length}</span>
              Questions
            </div>
            <div class="sp-questions-wrap">
            <div class="sp-questions">`;
            for(let j=0; j< questions.length; j++){
                let qSrNumber = j+1;
                navElements += `<div class="question-nav" id="quiz-question-${j}" onclick="loadQuestion(${x},${j})">${qSrNumber}</div>`;
            }
        navElements += `</div></div></div>`;     
    }
    markup.innerHTML = navElements;
    navigationEl.innerHTML = '';
    navigationEl.appendChild(markup); 
}
function updateNavigation(spIndex,qIndex = null){
    let spNavWrapper = document.querySelector(`.quiz-naviation #sp-nav-${spIndex}`);
    questionWrapper = null;
    if(qIndex === 0 || qIndex){
        questionWrapper = spNavWrapper.querySelector(`#quiz-question-${qIndex}`);
    }
    let allSpNavWrappers =document.querySelectorAll(`.quiz-naviation .sp-nav`);
    allSpNavWrappers.forEach(sp => {
        sp.classList.remove('active');
        if(qIndex === 0 || qIndex){
            let allQuestionWrappers = sp.querySelectorAll(`.question-nav`);
            allQuestionWrappers.forEach(question =>{
                question.classList.remove('active');
            });
        }
    })
    spNavWrapper.classList.add('active');
    if(qIndex === 0 || qIndex){
        questionWrapper.classList.add('active');
    }
}

function markQuestionAsComplete(){
    let spProgressCounterEl = document.querySelector(`#sp-nav-${currentSpIndex} .part-progress #part-${currentSpIndex}-completed`);
    let spQuestionEl = document.querySelector(`#sp-nav-${currentSpIndex} .sp-questions-wrap #quiz-question-${currentQuestionIndex}`);
    let spProgress = parseInt(spProgressCounterEl.innerText);
    spProgress++;
    spProgressCounterEl.innerHTML = spProgress;
    spQuestionEl.classList.add('completed');
}
function resetQuestionCompletion(){
    let spProgressCounterEl = document.querySelector(`#sp-nav-${currentSpIndex} .part-progress #part-${currentSpIndex}-completed`);
    let spQuestionEl = document.querySelector(`#sp-nav-${currentSpIndex} .sp-questions-wrap #quiz-question-${currentQuestionIndex}`);
    let spProgress = countCompletedQuestions(currentSpIndex);
    spProgressCounterEl.innerHTML = spProgress;
    spQuestionEl.classList.remove('completed');
}

function countCompletedQuestions(spIndex){
    let count = 0;
    results_obj.result_elements[spIndex]['questions'].forEach(question => {
        if(question.attempted){
            count ++;
        }
    });
    return count;
}

function countQuizCompletedQuestions(){
    let completedQuestionsCount = 0;
    let resultElements = results_obj.result_elements;
    for(let i = 0; i < resultElements.length; i++){
        completedQuestionsCount += countCompletedQuestions(i);
    }
    return completedQuestionsCount;
}

async function submitQuiz(){
    let trigger = this.event.target;
    let nonce = trigger.dataset.nonce;
    let completedQuestionsCount = countQuizCompletedQuestions();
    let ld_quiz = document.querySelector('#ld_quiz').value;
    let ld_course = document.querySelector('#ld_course').value;
    results_obj.ending_time = Date.now();
    results_obj.ld_quiz = ld_quiz;
    results_obj.ld_course = ld_course;
    if(!(completedQuestionsCount > 0)){
        display_isq_msg('Please Attempt At Least One Question');
        return;
    }
    let formData = new FormData();
    formData.append('result_obj', JSON.stringify(results_obj));
    formData.append('action', 'submit_quiz');
    formData.append('nonce', nonce);
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData
    }).then(res => res.json())
    .then(response => {
        if(response.success){
            display_isq_msg('Quiz Submitted', 'success');
            // console.log(response.data);
            window.location.href = response.data.result_url;
        }
    })
}

function formatCorrectionsForChatGPT(errors) {
    let resultString = '';
    if(!(errors.length > 0)){
        return resultString;
    }
    errors.forEach((error, index) => {
        const originalError = error.message;
        const correction = error.replacements.map(rep => rep.value).join(' or ');
        const errorType = error.type.typeName;
        const shortExplanation = error.shortMessage;

        resultString += `${index + 1}. Original error -> ${originalError}: ${correction} Error type: ${errorType} [${shortExplanation}]\n`;
    });
    return resultString;
}

// Helper function to deletAssociated Question Audio 
function deleteAudio(attachment_id){
    let formData = new FormData();
    formData.append('action', 'delete_question_audio');
    formData.append('attachment_id', attachment_id);
    formData.append('nonce', wpdata.delete_audio_nonce);
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData
    }).then(res => res.json())
    .then(response => {
        console.log(response);
    })
}