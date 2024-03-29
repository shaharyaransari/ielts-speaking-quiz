let results_obj = wpdata.result; // Get Result Data 

// Tracking variables 
let currentSpeakingPart = null;
let currentSpIndex = null;
// let isResultReady = results_obj.result_ready; 
let isResultReady = false; 
// let isPronunDataReady = results_obj.pronun_data_ready;
let isPronunDataReady = false;
let currentActiveTab = null;
// console.log(isResultReady);
// Create an instance of the AudioPlayer
// Initialize Page
initResultPage();

/**
 * Function to initalize Result page functions
 */
async function initResultPage() {
    // Load First Speaking Part
    // showResultPageLoading();
    // Prepare Data 
    if(!isResultReady){
    // updateLoadingText('<br> - Getting Grammer Suggesstions', true);
    await prepareSuggestions();
    }

    // Render Navigation
    renderQuizNavigation(); // Doesn't return a promise
    loadSpResult(0); // Doesn't return a promise

    if(!isResultReady){
        await prepareScores(); // Returns a Promise
    }
    if(!isPronunDataReady){
        // Prepare Pronunciation Data
        // updateLoadingText('<br> - Grammer Suggestions Ready <br>  - Analyzing Pronunciation.. <br> - This Usually Takes Time Please Do not leave page', true);
        await preparePronunciationData();
        // updateLoadingText('<br> - Almost Done...', true);
    }

    // Render scores after prepareScores is complete
    renderCurrentSpScores(currentSpIndex); // Doesn't return a promise
    
    if(isPronunDataReady){
    // Save Result Data in Usermeta
    calculateTalkingSpeed();
    loadSpResultParts(currentActiveTab);
    }
    if(!isResultReady && isPronunDataReady){
        saveResultData();
    }
    // hideResultPageLoading();
}

function spAttemptedQuestionsCount(spIndex){
    let count = 0;
    results_obj.result_elements[spIndex].questions.forEach(q => {
        if(q.attempted){
            count++;
        }
    });
    return count;
}
function saveResultData(){
    let formData = new FormData();
    formData.append('action', 'save_result');
    formData.append('nonce', wpdata.save_result_nonce);
    formData.append('user_id',wpdata.user_id);
    formData.append('result', JSON.stringify(results_obj));
    fetch(wpdata.ajaxurl, {
        method : 'post',
        body: formData
    }).then(res => res.json())
    .then(response => {
        if(response.success){
            display_isq_msg('Result Saved');
        }else{
            display_isq_msg('Error Saving Result', 'error');
        }
    });
}
/**
 * Helper Function to Prepare Scores and Store Scores in result_obj
 */
async function preparePronunciationData(){
    let pronunData =  await Promise.all(results_obj.result_elements.map(async (sp, index) => {
        let data = await getPronunciationData(sp, index);
        console.log(data);
        if(data){
            results_obj.pronun_data_ready = true;
            isPronunDataReady = results_obj.pronun_data_ready;
            return data;
        }else{
            display_isq_msg('Pronunciation Data Can\'t be Loaded');
            results_obj.pronun_data_ready = false;
            isPronunDataReady = results_obj.pronun_data_ready;
        }
    }));
    return pronunData;
}

async function getPronunciationData(sp, spIndex) {
    return Promise.all(sp.questions.map(async (question, qIndex) => {
        if (!question.transcript) {
            results_obj.result_elements[spIndex]['questions'][qIndex].pronunciation_data = [];
            return [];
        }

        let formData = new FormData();
        formData.append('action', 'get_pronunciation_data');
        formData.append('nonce', wpdata.pronunciation_nonce);
        formData.append('transcript', question.transcript);
        formData.append('audio_url', question.audio_url);

        try {
            let response = await fetch(wpdata.ajaxurl, {
                method: 'post',
                body: formData
            }).then(res => res.json());

            // console.log(response);
            let pronunciationData = Array.isArray(response.data) && response.data.length > 0 ? response.data[0] : [];  // response.data[0] is an array of objects
            results_obj.result_elements[spIndex]['questions'][qIndex].pronunciation_data = pronunciationData;
            // console.log(pronunciationData);
            return pronunciationData;
        } catch (error) {
            console.error('Error fetching pronunciation data:', error);
            // Consider what to do in case of an error. Maybe set pronunciation_data to null or an empty array.
            results_obj.result_elements[spIndex]['questions'][qIndex].pronunciation_data = [];
            return [];
        }
    }));
}
function prepareScores() {
    return Promise.all(results_obj.result_elements.map(async (sp, index) => {
        if(!sp.transcript){ // Transcript is Empty i.e no questions attempted
            // Save Scores 
            results_obj.result_elements[index].vocabulary_score = 0;
            results_obj.result_elements[index].grammer_score = 0;
            return index;
        }
        let errors = '';
        sp.questions.forEach(q => {
            errors += `${q.gpt_input}\n`;
        });
        sp.gpt_input_errors = errors;
        let vocabScore = 0;
        let grammerScore = 0;
        
        // Vocab Scores
        vocabScore = await getVocabScore(sp);
        grammerScore = await getGrammerScore(sp);
        vocabScore = (typeof vocabScore === 'string') ? extractNumbersFromString(vocabScore) : vocabScore;
        grammerScore = (typeof grammerScore === 'string') ? extractNumbersFromString(grammerScore) : grammerScore;
        // Save Scores 
        results_obj.result_elements[index].vocabulary_score = parseInt(vocabScore);
        results_obj.result_elements[index].grammer_score = parseInt(grammerScore);

        return index;
    }));
}
/**
 * Helper Function to Prepare Suggestions and Store Suggestions in result_obj
 */
async function prepareSuggestions(){
    const promises = results_obj.result_elements.map(async (sp, index) => {
        let errors = '';
        sp.questions.forEach(q => {
            errors += `${q.gpt_input}\n`;
        });
        sp.gpt_input_errors = errors;
        await getVocabularySuggestionsData(sp.transcript, sp, index);
        return true;
    });
    return await Promise.all(promises);
}

/**
 * Helper Function to Extract Number from A String
 * @param {string} inputString 
 * @returns String Containg Numbers Only
 */
function extractNumbersFromString(inputString) {
    // Use a regular expression to match digits
    const numbersArray = inputString.match(/\d+/g);

    // Join the matched digits into a single string
    const resultString = numbersArray ? numbersArray.join('') : '';

    return resultString;
}

async function getVocabScore(sp){
    if(sp.vocabulary_score){
        return sp.vocabulary_score;
    }else{
        let formData = new FormData();
        formData.append('nonce', wpdata.openai_nonce);
        formData.append('action', 'get_vocabulary_score');
        formData.append('transcript', sp.transcript);
        let vocabScore = await fetch(wpdata.ajaxurl,{
            method : 'post',
            body : formData
        }).then(res => res.json())
        .then(response => {
            if(response.success){
                return response.data;
            }
        });
        return vocabScore;
    }
}
async function getGrammerScore(sp){
        if(sp.grammer_score){
            return sp.grammer_score;
        }else{
            let formData = new FormData();
            formData.append('nonce', wpdata.openai_nonce);
            formData.append('action', 'get_grammer_score');
            formData.append('transcript', sp.transcript);
            formData.append('error', sp.gpt_input_errors);
            let grammerScore = await fetch(wpdata.ajaxurl,{
                method : 'post',
                body : formData
            }).then(res => res.json())
            .then(response => {
                if(response.success){
                    return response.data;
                }
            });
            return grammerScore;
        }
}
/**
 * Helper Function to count completed question inside a speaking part
 */
function countCompletedQuestions(spIndex){
    let count = 0;
    results_obj.result_elements[spIndex]['questions'].forEach(question => {
        if(question.attempted){
            count ++;
        }
    });
    return count;
}



/**
 * Helper Function to render quiz navigation
 */
function renderQuizNavigation(){
    let quizElments = results_obj.result_elements;
    let navigationEl = document.querySelector('#quiz-result-footer');
    let markup = document.createElement('div');
    markup.classList.add('quiz-naviation');
    let navElements = '';
    for(let x=0;x < quizElments.length; x++){
        let partSrNumber = x+1;
        let spRecordedTime = getTimeString(quizElments[x]['audio_length']);
        let questions = quizElments[x]['questions'];
        navElements += `
        <div class="sp-nav" id="sp-nav-${x}">
            <div class="sp-srnumber">Part ${partSrNumber}</div>
            <div class="part-progress" onclick="loadSpResult(${x})">
             <span id="part-${x}-total">${questions.length}</span> Questions
            </div>
            <div class="part-audio">
                Total Recorded Time: ${spRecordedTime}
            </div>
        </div>`; 
    }
    markup.innerHTML = navElements;
    navigationEl.innerHTML = '';
    navigationEl.appendChild(markup); 
}
/**
 * 
 * Helper Function to load Speaking Part Result Elements
 * @param {*} spIndex 
 */
function loadSpResult(spIndex){
    // Update Sp Tracking Variable
    currentSpeakingPart = results_obj.result_elements[spIndex];
    currentSpIndex = spIndex;
    
    // Prepare Variables 
    let spId = currentSpeakingPart['speaking_part_id'];
    let spData = null;
    let spTitleEl = document.querySelector('#speaking-part-title');
    let category =  'vocabulary';
    // Update Navigation
    updateResultNavigation(spIndex);

    // Load Post Data
    spData = currentSpeakingPart.post_data;
    // Update Title
    spTitleEl.innerHTML = spData.post_title;

    // Render Speaking Part Result Sections i.e Question List,
    loadSpResultParts(category);
    let vocabScore = currentSpeakingPart.vocabulary_score;
    let grammerScore = currentSpeakingPart.grammer_score;
    // Render Scores
    if((vocabScore || vocabScore==0) && (grammerScore || grammerScore == 0)){
        renderCurrentSpScores(currentSpIndex);
    }
}

function renderCurrentSpScores(spIndex){
    let vocabScoreEl = document.querySelector('.score-box.vocabulary .score-box-score');
    vocabScoreEl.innerHTML = '...';
    let grammerScoreEl = document.querySelector('.score-box.grammer .score-box-score');
    grammerScoreEl.innerHTML = '...';

    let fluencyScoreEl = document.querySelector('.score-box.fluency .score-box-score');
    let pronunciationScoreEl = document.querySelector('.score-box.pronunciation .score-box-score');
    if(! results_obj.result_elements[spIndex].transcript){ // No Questions Attempted
        vocabScoreEl.innerHTML = `0.0`;
        grammerScoreEl.innerHTML = `0.0`;
        pronunciationScoreEl.innerHTML = `0.0`;
        fluencyScoreEl.innerHTML = `0.0`;
        return;
    }
    let vocabScore = results_obj.result_elements[spIndex].vocabulary_score;
    let grammerScore = results_obj.result_elements[spIndex].grammer_score;
    let pronunciationScore = results_obj.result_elements[spIndex].pronunciation_score;
    let fluencyScore = results_obj.result_elements[spIndex].fluency_score;
    // Render on Frontend 
    if(vocabScore && grammerScore){
        vocabScoreEl.innerHTML = `${vocabScore}.0`;
        grammerScoreEl.innerHTML = `${grammerScore}.0`;
    }

    if(pronunciationScore && isPronunDataReady){
        pronunciationScoreEl.innerHTML = `${pronunciationScore}`;
    }else{
     pronunciationScore = getPronunciationScore(spIndex);
     if(pronunciationScore && isPronunDataReady){
         pronunciationScoreEl.innerHTML = `${pronunciationScore}`;
     }
    }
    if(fluencyScore && isPronunDataReady){
        fluencyScoreEl.innerHTML = `${fluencyScore}`;
    }else{
        fluencyScore = getFluencyScore(spIndex);
        if(fluencyScore && isPronunDataReady){
            fluencyScoreEl.innerHTML = `${fluencyScore}`;
        }
    }
}
function getPronunciationScore(spIndex){
    if(!isPronunDataReady){
        return null;
    }
    // Wait until isPronunDataReady becomes true
    let score = 0;
    let totalWordCount = 0;
    let totalAccXWords = 0;
    let questions = results_obj.result_elements[spIndex].questions;
    questions.forEach(question => {
        let dataArray = question.pronunciation_data;
        // Pronunciation Data is Null 
        if(!dataArray){
            let sentenceAccuracy = 0;
            let sentenceWordCount = 0;
            totalWordCount += sentenceWordCount;
            let senctencAccXWords = sentenceAccuracy * sentenceWordCount;
            totalAccXWords += senctencAccXWords;
        }else{
            dataArray.forEach(data => { // Each Sentence
                let sentenceAccuracy = data.NBest[0].PronunciationAssessment.PronScore;
                let sentenceWordCount = data.NBest[0].Words.length;
                totalWordCount += sentenceWordCount;
                let senctencAccXWords = sentenceAccuracy * sentenceWordCount;
                totalAccXWords += senctencAccXWords;
            });
        }
    });
    score = totalAccXWords / totalWordCount;
    score = Math.round(score * 100) / 100;
    results_obj.result_elements[spIndex].pronunciation_score = score;
    return score;
}

function getFluencyScore(spIndex){
    if(!isPronunDataReady){
        return null;
    }
    let score = 0;
    let totalWordCount = 0;
    let totalFluencyXWords = 0;
    let questions = results_obj.result_elements[spIndex].questions;
    questions.forEach(question => {
        let dataArray = question.pronunciation_data; // Array of Sentences in a Question
        if(!dataArray){
            let sentenceFluency = 0;
            let sentenceWordCount = 0;
            totalWordCount += sentenceWordCount;
            let senctencFluencyXWords = sentenceFluency * sentenceWordCount;
            totalFluencyXWords += senctencFluencyXWords;
        }
        dataArray.forEach(data => { // Each Sentence
            let sentenceFluency = data.NBest[0].PronunciationAssessment.FluencyScore;
            let sentenceWordCount = data.NBest[0].Words.length;
            totalWordCount += sentenceWordCount;
            let senctencFluencyXWords = sentenceFluency * sentenceWordCount;
            totalFluencyXWords += senctencFluencyXWords;
        });
    });
    score = totalFluencyXWords / totalWordCount;
    score = Math.round(score * 100) / 100;
    results_obj.result_elements[spIndex].fluency_score = score;
    return score;
}

/**
 * Helper Functions to Update Navigation Element
 * @param {*} spIndex 
 */
function updateResultNavigation(spIndex){
    let navElement = document.querySelector(`#sp-nav-${spIndex}`);
    let navElements =  document.querySelectorAll(`.sp-nav`);
    navElements.forEach(element =>{
        element.classList.remove('active');
    });
    // Update Current Element 
    navElement.classList.add('active');
}

/**
 * Helper Function to load Question List
 * @param {*} category grammer | vocabulary | pronunciation | fluency
 */
function loadQuestionList(category){
    let wrapper = document.querySelector('#result-questions-list-wrap');

    if(!currentSpeakingPart.transcript){ // No Questions Were Attempted
        wrapper.innerHTML = 'No Questions Were Attempted in Current Part';
        return;
    }

    let playerTemp = document.querySelector('#audio-player');  
    if(category == 'grammer' || category == 'vocabulary'){
        let selector = 'grammer-vocabulary';
        // Load Grammer Questions List
        let questionTemplate = document.querySelector(`#${selector}-question`);
        let questionListWrapper = document.createElement('div');
        questionListWrapper.classList.add('result-questions-list');
        let questions = currentSpeakingPart['questions'];
        // Prepare Question Template
        for(let x = 0; x < questions.length; x++){
            // Prepate Question Data
            let qNode = questionTemplate.content.cloneNode(true);
            let qData = questions[x].post_data;
            let player = playerTemp.content.cloneNode(true);
            let questionSr = x + 1;
            let attempted = questions[x]['formatted_transcript'];
            let fTranscript = questions[x]['formatted_transcript'] ?? '';
            let transcript = questions[x]['transcript'] ?? '';
            let qId = questions[x]['question_id'];
            let audio_length = questions[x]['audio_length'];
            let timeString = getTimeString(audio_length)
            let audio_url = questions[x]['audio_url'];
            player.querySelector('audio').src = audio_url;
            player.querySelector('.recording-log').innerText = timeString;
            qNode.querySelector('.question-title').innerHTML = `Question ${questionSr} : ${qData.post_content}`;
            qNode.querySelector('.question-response').innerHTML = `${fTranscript}`;
            qNode.querySelector('.player').innerHTML = '';
            qNode.querySelector('.player').appendChild(player);
            questionListWrapper.appendChild(qNode);
        }

        // Update UI
        wrapper.innerHTML = '';
        wrapper.appendChild(questionListWrapper);
        
    }else if(category == 'pronunciation'){
        
        // Load pronunciation Questions List
        let questionTemplate = document.querySelector(`#${category}-question`);
        let questionListWrapper = document.createElement('div');
        questionListWrapper.classList.add('result-questions-list');
        let questions = currentSpeakingPart['questions'];
        if(!isPronunDataReady){
            questionListWrapper.innerHTML = `<div class="result-questions-list">
            <div class="result-question">
                <div class="question-title">
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
                <!-- Response Wrap  -->
                <div class="question-response-wrap">
                    <!-- Player  -->
                    <div class="player">
                        <!-- New  -->
                        <div class="recording-preview-wrap">
                            <div class="recording-preview">
                                <div class="question-audio-wrap">
                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                    <div class="question-audio-trigger" data-playing="false">
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
                            <div class="recording-log"><div class="skeleton skeleton-text"></div></div>
                        </div>
                        <!-- /New  -->
                    </div>
                    <!-- /Player  -->

                    <!-- Transcript  -->
                    <div class="question-response">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                    <!-- Transcript  -->
                </div>
                <!-- /Response Wrap  -->
            </div>
        
            <div class="result-question">
                <div class="question-title">
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
                <!-- Response Wrap  -->
                <div class="question-response-wrap">

                    <!-- Player  -->
                    <div class="player">
                        <!-- New  -->
                        <div class="recording-preview-wrap">
                            <div class="recording-preview">
                                <div class="question-audio-wrap">
                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                    <div class="question-audio-trigger" data-playing="false">
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
                            <div class="recording-log">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /New  -->
                    </div>
                    <!-- /Player  -->

                    <!-- Transcript  -->
                    <div class="question-response">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                    <!-- Transcript  -->
                </div>
                <!-- /Response Wrap  -->
            </div>
        </div>`;
        }else{
            // Prepare Question Template
            for(let x = 0; x < questions.length; x++){
                // Prepate Question Data
                let qNode = questionTemplate.content.cloneNode(true);
                let qData = questions[x].post_data;
                let player = playerTemp.content.cloneNode(true);
                let questionSr = x + 1;
                let attempted = questions[x]['formatted_transcript'];
                let transcript = questions[x]['transcript'] ?? '';
                // Pronunciation Errors Highlighted
                let pErrors = markPErrors(questions[x]);
                let pErrorsTranscript = pErrors.pErrorsTranscript;
                currentSpeakingPart['questions'][x].pronunciation_errors_data = pErrors.errorsData;
                // console.log(currentSpeakingPart);
                let qId = questions[x]['question_id'];
                let audio_length = questions[x]['audio_length'];
                let timeString = getTimeString(audio_length)
                let audio_url = questions[x]['audio_url'];
                player.querySelector('audio').src = audio_url;
                player.querySelector('.recording-log').innerText = timeString;
                qNode.querySelector('.question-title').innerHTML = `Question ${questionSr} : ${qData.post_content}`;
                qNode.querySelector('.question-response').innerHTML = `<span>${pErrorsTranscript}</span>`;
                qNode.querySelector('.player').innerHTML = '';
                qNode.querySelector('.player').appendChild(player);
                questionListWrapper.appendChild(qNode);
            }
        }

        // Update UI
        wrapper.innerHTML = '';
        wrapper.appendChild(questionListWrapper);

    }else if(category == 'fluency'){
        // Load fluency Questions List
        let questionTemplate = document.querySelector(`#${category}-question`);
        let questionListWrapper = document.createElement('div');
        questionListWrapper.classList.add('result-questions-list');
        let questions = currentSpeakingPart['questions'];
        if(!isPronunDataReady){
            questionListWrapper.innerHTML = `<div class="result-questions-list">
            <div class="result-question">
                <div class="question-title">
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
                <!-- Response Wrap  -->
                <div class="question-response-wrap">
                    <!-- Player  -->
                    <div class="player">
                        <!-- New  -->
                        <div class="recording-preview-wrap">
                            <div class="recording-preview">
                                <div class="question-audio-wrap">
                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                    <div class="question-audio-trigger" data-playing="false">
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
                            <div class="recording-log"><div class="skeleton skeleton-text"></div></div>
                        </div>
                        <!-- /New  -->
                    </div>
                    <!-- /Player  -->

                    <!-- Transcript  -->
                    <div class="question-response">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                    <!-- Transcript  -->
                </div>
                <!-- /Response Wrap  -->
            </div>
        
            <div class="result-question">
                <div class="question-title">
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
                <!-- Response Wrap  -->
                <div class="question-response-wrap">

                    <!-- Player  -->
                    <div class="player">
                        <!-- New  -->
                        <div class="recording-preview-wrap">
                            <div class="recording-preview">
                                <div class="question-audio-wrap">
                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                    <div class="question-audio-trigger" data-playing="false">
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
                            <div class="recording-log">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /New  -->
                    </div>
                    <!-- /Player  -->

                    <!-- Transcript  -->
                    <div class="question-response">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                    <!-- Transcript  -->
                </div>
                <!-- /Response Wrap  -->
            </div>
        </div>`;
        }else{
            // Prepare Question Template
            for(let x = 0; x < questions.length; x++){
                // Prepate Question Data
                let qNode = questionTemplate.content.cloneNode(true);
                let qData = questions[x].post_data;
                let player = playerTemp.content.cloneNode(true);
                let questionSr = x + 1;
                let attempted = questions[x]['formatted_transcript'];
                let transcript = questions[x]['transcript'] ?? '';
                // Fluency Errors Highlighted
                let fluencyErrors = markFluencyErrors(questions[x]);
                let fluencyErrorsTranscript = fluencyErrors.fluencyErrorsTranscript;
                // console.log(currentSpeakingPart);
                let qId = questions[x]['question_id'];
                let audio_length = questions[x]['audio_length'];
                let timeString = getTimeString(audio_length)
                let audio_url = questions[x]['audio_url'];
                player.querySelector('audio').src = audio_url;
                player.querySelector('.recording-log').innerText = timeString;
                qNode.querySelector('.question-title').innerHTML = `Question ${questionSr} : ${qData.post_content}`;
                qNode.querySelector('.question-response').innerHTML = `${fluencyErrorsTranscript}`;
                qNode.querySelector('.player').innerHTML = '';
                qNode.querySelector('.player').appendChild(player);
                questionListWrapper.appendChild(qNode);
            }
        }

        // Update UI
        wrapper.innerHTML = '';
        wrapper.appendChild(questionListWrapper);
    }
}
/**
 * Helper Function to Load Result Parts i.e Questions, Suggestions etc.
 * @param {*} category 
 */
function loadSpResultParts(category){
    let scoreBox = document.querySelector(`.score-box.${category}`);
    let scoreBoxes = document.querySelectorAll('.score-box');
    currentActiveTab = category;
    scoreBoxes.forEach(box => {
        box.classList.remove('active');
    });
    scoreBox.classList.add('active');

    if(category == 'fluency'){
        document.querySelector('.fluency-legend-wrap').style.opacity = 1;
    }else{
        document.querySelector('.fluency-legend-wrap').style.opacity = 0;
    }
    // Load Questions
    loadQuestionList(category); // It is important and should be excuted first as it prepares Suggestion Data for Pronunciation as well
    // Load Suggestions 
    loadSuggestions(category);
}

/**
 * Helper Function to load Question List
 * @param {*} category grammer | vocabulary | pronunciation | fluency
 */
async function loadSuggestions(category){
    let wrapper = document.querySelector('#result-suggestions-wrapper');
    if(!currentSpeakingPart.transcript){
        wrapper.innerHTML = 'No Questions Were Attempted in Current Part';
        return;
    }
    let wrapperNode = document.querySelector(`#${category}-suggestions`);
    let suggestionNode = document.querySelector(`#${category}-suggestion-temp`);
    if(category == 'grammer' || category == 'vocabulary'){
        // Load Vocbulary Suggestions List
        let sWrap = wrapperNode.content.cloneNode(true);
        let suggestions = await getVocabularySuggestionsData(currentSpeakingPart.transcript);
        // console.log(suggestions);
        if(suggestions.length > 0){
            suggestions.forEach(suggestion => {
                let sNode = suggestionNode.content.cloneNode(true);
                sNode.querySelector('.orignal-txt').innerText = suggestion.original;
                sNode.querySelector('.suggestion-txt').innerText = suggestion.suggestion;
                sNode.querySelector('.suggestion-exp').innerText = suggestion.explanation;
                sWrap.querySelector('.result-suggestions-wrap-inner').appendChild(sNode);
            });
        }else{
            sWrap.innerHTML = 'No Suggestions Found';
        }
        wrapper.innerHTML = '';
        wrapper.appendChild(sWrap);
        
    }else if(category == 'pronunciation'){
        
        // Load pronunciation Suggestions List
        // let sWrap = wrapperNode.content.cloneNode(true);
        // let sNode = suggestionNode.content.cloneNode(true);
        // let suggestionsData = await getPronSuggestionsData();
        // console.log(suggestionsData);
        // Load Grammer Suggestions List
        let sWrap = wrapperNode.content.cloneNode(true);
        if(!isPronunDataReady){
            sWrap.innerHTML = ``;

            wrapper.innerHTML = ` <div class="result-suggestions-desc">
            Errors and Corrections: 
        </div>
        <div class="result-suggestions-wrap-inner">
            <!-- Single Suggestion  -->
            <div class="result-suggestion pronun-error-wrap skeleton">
            </div>
            <!-- /Single Suggestion  -->
        
            
            <!-- Single Suggestion  -->
            <div class="result-suggestion pronun-error-wrap skeleton">
            </div>
            <!-- /Single Suggestion  -->
        </div>`;
            // wrapper.appendChild(sWrap);
            return;
        }
        let pronunErrors =  getPronSuggestionsData(currentSpeakingPart);
        // console.log(pronunErrors);
        if(pronunErrors.length > 0){
            pronunErrors.forEach(pronunError => {
                if(! pronunError){
                    return;
                }
                let sNode = suggestionNode.content.cloneNode(true); 
                let markup = `<img class="pronun-popup-action" onclick="playAudioSegment('${pronunError.audioUrl}', ${pronunError.wordStartTime}, ${pronunError.wordDuration});" src="${wpdata.plugin_dir}Inc/assets/images/icon-speaker.svg">
                <img class="pronun-popup-action" onclick="playCorrectPronunciation('${pronunError.word}')" src="${wpdata.plugin_dir}Inc/assets/images/icon-listen.svg">`;
                sNode.querySelector('.orignal-txt').innerText = pronunError.word;
                sNode.querySelector('.suggestion-txt').innerHTML = pronunError.ipa;
                sNode.querySelector('.suggestion-exp').innerHTML = markup;
                sWrap.querySelector('.result-suggestions-wrap-inner').appendChild(sNode);
            });
        }else{
            sWrap.querySelector('.result-suggestions-wrap-inner').innerHTML = 'No Errors Found';
        }
        wrapper.innerHTML = '';
        wrapper.appendChild(sWrap);
    }else if(category == 'fluency'){
        // Load fluency Suggestions List
        let sWrap = wrapperNode.content.cloneNode(true);
        if(!isPronunDataReady){
            sWrap.querySelector('.result-suggestions-wrap-inner').innerHTML = `
            <div class="wpm-speed-meter-wrap">
            <div class="skeleton skeleton-text"></div>
                <div class="wpm-meter-speed">
                    <div class="skeleton skeleton-text"></div>
                <div class="wpm-meter-text">
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
            </div>
            `;
            wrapper.innerHTML = '';
        wrapper.appendChild(sWrap);
        return;
        }
        let spWpm = currentSpeakingPart.sp_wpm;
        let speedText = "Too Slow";
        let color = "rgba(235, 87, 87, 1);";
        if(spWpm < 110){ // Too Slow
            speedText = 'Too Slow';
            color = "rgba(235, 87, 87, 1);"
        }else if(spWpm >= 120 && spWpm <= 150){ // Fine
            speedText = "Conversational";
            color = "green";
        }else{ // Too Fast
            speedText = "Good";
            color = "green";
        }
        spWpm = Math.round(spWpm);

        sWrap.querySelector('.result-suggestions-wrap-inner').innerHTML = `
        <div class="wpm-speed-meter-wrap">
            <div class="wpm-meter-speed">Your Speed Is <span class="speed-number" style="color: ${color}">${spWpm} WPM</span></speed>
            <div class="wpm-meter-text">
                Your Speed in this Speaking Part is <span class="speed-text" style="color: ${color}">${speedText}</span>
            </div>
        </div>
        `;
        wrapper.innerHTML = '';
        wrapper.appendChild(sWrap);
    }
}

/**
 * Helper Function to Send Transcript to Open Ai to get Suggestions
 * @param {*} transcript
 * @returns 
 */
async function getVocabularySuggestionsData(transcript, speakingPart = currentSpeakingPart, spIndex = currentSpIndex){
    if(!transcript){
        display_isq_msg('No Questions Were Attempted in This part');
        return [];
    }
    if(speakingPart.grammer_suggestions){
        return speakingPart.grammer_suggestions;
    }else{
        // Should be Loaded by Open AI
        let formData = new FormData();
        formData.append('transcript', transcript);
        formData.append('action', 'get_openai_vocab_suggestions');
        formData.append('nonce', wpdata.openai_nonce);
        OpenAiResponse = await fetch(wpdata.ajaxurl,{
            method: 'post',
            body: formData
        }).then( res => res.json())
        .then(response => {
            if(response.success){
                return response.data;
            }else{
                console.log(response);
                display_isq_msg('Something Went Wrong', 'error');
            }
        });
        let suggestionsObj = processGrammerSuggestions(OpenAiResponse);
        // Save Suggestion 
        results_obj.result_elements[spIndex].grammer_suggestions = suggestionsObj;
        return suggestionsObj;
    }
}
/**
 * Function to Process Response From Open Ai to Extract Grammer Suggestions Data
 * @param {*} apiResponse 
 * @returns 
 */
function processGrammerSuggestions(inputText) {
    const suggestions = [];
    
    const lines = inputText.split('\n').filter(line => line.trim() !== '');
    
    lines.forEach((line, index) => {
        const match = line.match(/"([^"]+)" -> "([^"]+)"/);
        if (match) {
            const suggestionNumber = suggestions.length + 1;
            const original = match[1].trim();
            const suggestion = match[2].trim();
            
            const explanationLine = lines[index + 1];
            const replacements = extractGrammerReplacements(explanationLine);

            suggestions.push({
                suggestionNumber,
                original,
                suggestion,
                explanation: explanationLine ? explanationLine.trim().substring(12) : '',
                replacements,
            });
        }
    });

    return suggestions;
}

function extractGrammerReplacements(explanationLine) {
    const replacements = [];
    const regex = /"([^"]+)"[^"]+"([^"]+)"/;
    const match = explanationLine.match(regex);

    if (match) {
        const replacement = match[1].trim();
        const suggested = match[2].trim();
        replacements.push({ replacement, suggested });
    }

    return replacements;
}

function getPronSuggestionsData(sp) {
    let pronunErrors = [];
    let words = new Set(); // Using a Set for efficient lookups and ensuring uniqueness

    sp.questions.forEach(q => {
        q.pronunciation_errors_data.forEach(errorData => {
            // Check if the word from the errorData hasn't been processed yet
            if (!words.has(errorData.word)) {
                words.add(errorData.word);
                pronunErrors.push(errorData); // Assuming you want to collect the entire errorData object
            }
        });
    });

    console.log('Pronunciation Errors', pronunErrors);
    return pronunErrors;
}

/**
 * Helper Function to return Markup with Pronunciation Errors Highlighted
 * @param {*} q Question Object
 */

function markPErrors(q){
    let pData = q.pronunciation_data; // Array of Sentences/Parts in a Question

    if(!pData){
        return {
            pErrorsTranscript : q.transcript,
            errorsData : []
        };
    }
    let errorsData = [];
    let audioUrl = q.audio_url; 
    // let audioUrl = 'http://wordpress4all.com/wp-content/uploads/2024/03/wrong-pronun-1.mp3'; // Temp Audio URL
    let transcript = q.transcript;
    let pErrorsTranscript = transcript;
    let replacements = [];
    pData.forEach(data => {
        let words = data.NBest[0].Words;
        let errorId = 0;
        let processedWords = [];
        words.forEach((word, index) => {
            let occurence_number = 0;
            processedWords.push(word.Word);
            // Counting Occrances of a Word
            processedWords.forEach(w => {
                if(w == (word.Word)){
                    occurence_number++;
                }
            });
            let hasError = (word.PronunciationAssessment.ErrorType != 'None');
            if(hasError){
                replacements.push({
                    error_obj : word,
                    word: word.Word,
                    occurence_number: occurence_number,
                    error_id : errorId,
                    word_index: index,
                });
            }
        });
    });
    if(replacements.length > 0){
        replacements.forEach(replacement => {
            // replacement.word = replacement.word.toLowerCase();
            let regex = new RegExp(`\\b${replacement.word}\\b`, 'i');
            // console.log(replacement);
            let matchNumber=0;
            pErrorsTranscript = pErrorsTranscript.replace(regex,(match) => {
                matchNumber++;
                let placeholder = `PlaceHolder__${match}_${replacement.occurence_number}`;
                return placeholder;
              });
        });
    };

    if(replacements.length > 0){
        replacements.forEach(replacement => {
            // replacement.word = replacement.word.toLowerCase();
            let regex = new RegExp(`\\bPlaceHolder__${replacement.word}_${replacement.occurence_number}\\b`, 'i');
            // console.log(replacement);
            let matchNumber=0;
            pErrorsTranscript = pErrorsTranscript.replace(regex,(match) => {
                matchNumber++;
                let ipa = "";
                let tableRows = "";
                replacement.error_obj.Phonemes.forEach(p => {
                    let color = (p.PronunciationAssessment.AccuracyScore < 50) ? "red" : "green";
                    ipa+= `${p.Phoneme} `;
                    tableRows += `<span class="syllable-table-row">
                                        <span>/${p.Phoneme}/</span>
                                        <span style="color:${color}">${p.PronunciationAssessment.AccuracyScore}</span>
                                    </span>`;
                });
                ipa = `/${ipa}/`;
                let wordStartTime = replacement.error_obj.Offset;
                let wordDuration = replacement.error_obj.Duration;
                let markup = `
                <span class="pronun-error-wrap" data-id="${replacement.error_id}" onmouseover="positionGError()">
                        <span class="pronun-error">${replacement.word}</span>
                            <span class="pronun-error-popup">

                            <span class="pronun-poup-header">
                                <span class="pronun-header-actions">
                                    <img class="pronun-popup-action" onclick="playAudioSegment('${audioUrl}', ${wordStartTime}, ${wordDuration});" src="${wpdata.plugin_dir}Inc/assets/images/icon-speaker.svg">
                                    <img class="pronun-popup-action" onclick="playCorrectPronunciation('${replacement.word}')" src="${wpdata.plugin_dir}Inc/assets/images/icon-listen.svg">
                                </span>
                                <span class="pronun-error-word">${replacement.word}</span>
                                <span class="pronun-error-syllable">${ipa}</span>
                            </span>

                            <span class="pronun-error-accuracy-bar-wrap">
                                <span>${replacement.error_obj.PronunciationAssessment.AccuracyScore}%</span>
                                <span class="pronun-error-accuracy-bar">
                                    <span style="width:${replacement.error_obj.PronunciationAssessment.AccuracyScore}%" class="pronun-error-accuracy-bar-inner"></span>
                                </span>
                            </span>

                            <span class="pronun-error-syllable-table">
                                <span class="syllable-table-header">
                                    <span>Sound</span>
                                    <span>You Said</span>
                                </span>
                                <span class="syllable-table-body">
                                    ${tableRows}
                                </span>
                            </span>

                            </span>
                        </span>`;
                // Prepare Errors Data 
                if(replacement.occurence_number < 2){ // Only One time
                    errorsData.push({
                        word: replacement.word,
                        wordStartTime : wordStartTime,
                        wordDuration : wordDuration,
                        audioUrl : audioUrl,
                        error_obj : replacement.error_obj,
                        ipa : ipa
                    });
                }
                return markup;
              });
        });
    };
    return {
        pErrorsTranscript : pErrorsTranscript,
        errorsData : errorsData
    };
}

function markFluencyErrors(q){
    let pData = q.pronunciation_data; // Array of Sentences/Parts in a Question
    if(!pData){
        return {
            fluencyErrorsTranscript : q.transcript
        }
    }
    // let errorsData = [];
    let audioUrl = q.audio_url; 
    // let audioUrl = 'http://wordpress4all.com/wp-content/uploads/2024/03/wrong-pronun-1.mp3'; // Temp Audio URL
    let transcript = q.transcript;
    let fluencyErrorsTranscript = transcript;
    let replacements = [];
    
    pData.forEach(data => {
        let words = data.NBest[0].Words;
        let errorId = 0;
        let processedWords = [];
        words.forEach((word, index) => {
            let occurence_number = 0;
            let prosody = word.PronunciationAssessment.Feedback.Prosody;
            processedWords.push(word.Word);
            // Counting Occrances of a Word
            processedWords.forEach(w => {
                if(w == (word.Word)){
                    occurence_number++;
                }
            });
            if(!("MissingBreak" in prosody.Break)){
                return;
            }
            let missingBreakConfidence = prosody.Break.MissingBreak.Confidence;
            let hasMissingBreakError = (prosody.Break.ErrorTypes[0] == 'MissingBreak');
            let unexpectedBreakConfidence = prosody.Break.UnexpectedBreak.Confidence;
            let hasError = ((hasMissingBreakError && (missingBreakConfidence > 0.7)) || (unexpectedBreakConfidence > 0.7));
            if(hasError){
                errorId++;
                // console.log(`errorID : ${errorId} word: ${word.Word}`);
                replacements.push({
                    error_obj : word,
                    word: word.Word,
                    occurence_number: occurence_number,
                    error_id : errorId,
                    word_index: index,
                    missed_pause: missingBreakConfidence,
                    bad_pause: unexpectedBreakConfidence,
                    break_length: prosody.Break.BreakLength
                });
            }
        });
    });

    if(replacements.length > 0){
        // console.log(replacements);
        replacements.forEach(replacement => {
            let regex = new RegExp(`\\b${replacement.word}\\b`, 'gi');
            let matchNumber=0;
            // console.log(replacement.error_obj);
            // let regex = new RegExp(`\\bher\\b`, 'gi');
            fluencyErrorsTranscript = fluencyErrorsTranscript.replace(regex,(match) => {
                matchNumber++;
                let isBadPause = (replacement.bad_pause > 0.7);
                let isMissedPause = (replacement.missed_pause > 0.7);
                let markup = `${replacement.word}`;
                if(isBadPause){
                    markup = `<span class="f-error-bad-pause"><span class="bad-pause-symbol"><span></span><span></span><span></span></span></span> ${match}</span>`;
                }else if(isMissedPause){
                    markup  = `<span class="f-error-missed-pause"><span class="missed-pause-symbol"></span> ${match}</span>`;
                }
                if(matchNumber == replacement.occurence_number){
                    return markup;
                }else{
                    return match;
                }
              });
        });
    };
    return {
        fluencyErrorsTranscript : fluencyErrorsTranscript
    };
}

function playAudioSegment(audioUrl, offset, duration) {
    const audio = new Audio(audioUrl);
  
    // Convert to seconds
    const startTime = offset / 10000000;
    // console.log(startTime);
    audio.currentTime = startTime;
    audio.play();
    audio.onplay = function(){
        let durationInMiliSec = duration /  10000;
        durationInMiliSec = durationInMiliSec + 200;
        // console.log(durationInSec);
        // Stop the audio after the specified duration
        setTimeout(() => {
        audio.pause();
        }, durationInMiliSec);
    }
  }

async function playCorrectPronunciation(word){
    display_isq_msg('Loading Pronunciation');
    let apiUrl = `https://api.dictionaryapi.dev/api/v2/entries/en/${word}`;
    let audioUrl = await fetch(apiUrl).then(res=>res.json()).then(response => {
        if(response.phonetics != 'undefinded'){
            return response[0].phonetics[0].audio
        }else{
            console.log(response);
            display_isq_msg('No Pronuciation Exists');
        }
        }).catch(error=>{
            console.log(error);
            display_isq_msg('No Pronuciation Exists');
        });
    if(audioUrl){
        const audio = new Audio(audioUrl);
        audio.play();
    }
}

function calculateTalkingSpeed(){
    for(let i=0; i < results_obj.result_elements.length; i++){
        let spQuestionsCount = results_obj.result_elements[i].questions.length;
        let spWpm = 0;
        if(results_obj.result_elements[i].transcript){ // If any Question is Attempted
            for(j=0; j<results_obj.result_elements[i].questions.length; j++){
                let pronunciation_data = results_obj.result_elements[i].questions[j].pronunciation_data;
                if(!pronunciation_data){ // It is Null
                    results_obj.result_elements[i].sp_wpm = 0;
                }
                let sentencesCount = pronunciation_data.length;
                let questionWpm = 0;
                pronunciation_data.forEach(data => {
                    // console.log(data.NBest[0]);
                    let duration = data.Duration / 10000000;
                    let wordCount = data.NBest[0].Words.length;
                    let durationMinutes = duration / 60;
                    let sentenceWpm = wordCount / durationMinutes;
                    questionWpm += sentenceWpm;
                })
                let questionAvgWpm = questionWpm / sentencesCount;
                spWpm += questionAvgWpm;
            }
            let spAvgWpm = spWpm / spQuestionsCount;
            results_obj.result_elements[i].sp_wpm = spAvgWpm;
        }else{
            results_obj.result_elements[i].sp_wpm = 0;
        }
    }
}