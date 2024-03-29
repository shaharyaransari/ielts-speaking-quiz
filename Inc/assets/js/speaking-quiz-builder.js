
let quiz_elements = {
    speaking_parts: [],
    questions:[],
    post_status: null,
    quiz_contents: null
}
let changesSaved = true;



// Check if something updated on page 
window.onbeforeunload = function (e){
    e = e || window.event;
    if( changesSaved ){
        return null;
    }
    //old browsers
    if (e) { e.returnValue = 'Sure?'; }
    //safari, chrome(chrome ignores text) 
    return 'Sure?';
}
// Needs to Run every time when something changes on page 
function update_quiz_elements_obj(is_loadingFirstTime = false){
    let speakingParts = document.querySelectorAll('.speaking-parts > .speaking-part');
    let questions = document.querySelectorAll('.ielts-questions > .ielts-question');
    let defaultPostStatus = document.querySelector('#quiz_default_status').value;
    let partsArray = [];
    let questionsArray = [];
    
    quiz_elements.post_status = defaultPostStatus;
    quiz_elements.quiz_contents = buildItemData('quiz',null,false);

    speakingParts.forEach(part => {
        let partID = parseInt(part.dataset.speakingPartId);
        partsArray.push(partID);
    });
    quiz_elements.speaking_parts = partsArray;
    questions.forEach(part => {
        let questionId = parseInt(part.dataset.questionId);
        questionsArray.push(questionId);
    });
    quiz_elements.questions = questionsArray;

    if(is_loadingFirstTime == false ){ // If not loading page first time
        showMiniNotice();
        changesSaved = false;
    }
}
// Show Save Changes bar

// Expand Functionality For All Draggables
function draggables_expand_events(){
    let expandTriggers = document.querySelectorAll('.expand-draggable-icon');
    expandTriggers.forEach(trigger=>{
        trigger.onclick = function(event) {
            let triggerElement = event.target;
            let triggerTarget = triggerElement.parentElement.parentElement.parentElement.nextElementSibling;
            triggerTarget.classList.toggle('expanded');
        }
    });
}

function update_display_order(){
    let speakingParts = document.querySelector('.speaking-parts').children;
    for(let i=0; i < speakingParts.length; i++){
        speakingParts[i].dataset.displayOrder = i+1;
        let questions = speakingParts[i].querySelector('.ielts-questions').children;
        for( let j=0; j < questions.length; j++  ){
            questions[j].dataset.displayOrder = j+1;
        }
    }
}
// update_display_order();

function init_drag_triggers(){
    let draggables = document.querySelectorAll('.ielts-draggables > .ielts-draggable');
    for(let i=0; i < draggables.length; i++){
        let trigger = draggables[i].querySelector('.draggable-icon-area')
        trigger.onmousedown = function(){
            draggables[i].setAttribute('draggable', 'true');
            // console.log(trigger);
        }
        trigger.onmouseout = function(){
            draggables[i].removeAttribute('draggable');
        }
        // console.log(draggables[i]);
    }

}

function init_conditinal_display_fields(){
    let dDisplayContainers = document.querySelectorAll('.c-display-container'); // outer wrapper containing both field and triggers
    dDisplayContainers.forEach(container => {
        let fieldWrappers = container.querySelectorAll('.c-display');
        fieldWrappers.forEach(wrapper => {
            wrapper.classList.remove('active');
        });
        let triggers = container.querySelectorAll('.c-display-trigger'); // trigger select/radio field
        triggers.forEach(trigger => {
            let fieldWrapper = container.querySelector(`.c-display.${trigger.value}`);
            fieldWrapper.classList.add('active');
            let field = fieldWrapper.querySelector('input');
            field.setAttribute('required',true);
        });
    });
}
init_conditinal_display_fields();

function toggleConditionalFields(){
    let trigger = this.event.target; 
    let parentContainer = trigger.parentElement;
    let fieldWrappers =  parentContainer.querySelectorAll('.c-display');
    fieldWrappers.forEach(wrapper => {
        wrapper.classList.remove('active');
        wrapper.querySelector('input').removeAttribute('required');
    });
    // Active selected Field
    parentContainer.querySelector(`.c-display.${trigger.value}`).classList.add('active');
    
    // Make the field Required
    parentContainer.querySelector(`.c-display.${trigger.value} input`).setAttribute('required', 'true');

}



// Adding Dragging/Dropping Functionality to All Draggables 
function draggables_dragging_events(){
    // Drag and Drop Functionality
    let ieltsDraggables = document.querySelectorAll('.ielts-draggable');
    let speakingParts = document.querySelectorAll('.speaking-parts > .speaking-part');
    let partsContainer = document.querySelector('.speaking-parts');
    let questionContainers = document.querySelectorAll('.ielts-questions');
    let ExpandableElements = document.querySelectorAll('.ielts-dropable');
    // Common Event For all Draggables 
    ieltsDraggables.forEach(item=>{
        item.addEventListener('dragstart', (event)=>{
            let currentElement = event.target;
            currentElement.classList.add('dragging-active');
            event.dataTransfer.setDragImage(new Image(), 0, 0);
        });

        item.addEventListener('dragend', (event)=>{
            event.stopPropagation();
            let currentElement = event.target;
            console.log(currentElement);
            currentElement.classList.remove('dragging-active');
            update_display_order();
            update_quiz_elements_obj();
            // if(currentElement.classList.contains('speaking-part')){
            //     let orderedElements = partsContainer.children; // Speaking Parts
            //     if(orderedElements.length > 0){
            //         for(let i=0; i < orderedElements.length; i++){
            //             orderedElements[i].dataset.displayOrder = i+1;
            //         }
            //     }
            // }else if(currentElement.classList.contains('ielts-question')){
            //     let orderedElements = currentElement.parentElement.children; // Questions
            //     // Save New Order Array in Speaking Part Post Meta
            //     if(orderedElements.length > 0){
            //         for(let i=0; i < orderedElements.length; i++){
            //             orderedElements[i].dataset.displayOrder = i+1;
            //         }
            //     }else{
            //         currentElement.parentElement.classList.add('empty-draggable');
            //     }
            // }
        });
    });
    // Drop Functionality For Speaking Part Containers 
    partsContainer.addEventListener('dragover',(event)=>{
        event.stopPropagation();
        let draggable = document.querySelector('.dragging-active');
        if(draggable.classList.contains('speaking-part')){
            event.preventDefault();
            let afterElement = getDragAfterElement(partsContainer, event.clientY, 'sp-draggable');
            if(afterElement == null){
                partsContainer.appendChild(draggable);
            }else{
                partsContainer.insertBefore(draggable, afterElement);
            }
        }
    })

    // Drop Functionality For Question Containers
    questionContainers.forEach(container=>{
        container.addEventListener('dragover',(event)=>{
            event.stopPropagation();
            let draggable = document.querySelector('.dragging-active');
            if(draggable.classList.contains('ielts-question')){
                event.preventDefault();
                let afterElement = getDragAfterElement(container, event.clientY, 'iq-draggable');
                if(afterElement == null){
                    container.appendChild(draggable);
                }else{
                    container.insertBefore(draggable, afterElement);
                }
            }
        });
    });
}

// Popup Eventlistners
function draggables_popup_events(){
    let popupTriggers = document.querySelectorAll('.draggable-ptrigger');
    popupTriggers.forEach(trigger=>{
        trigger.addEventListener('click',()=>{
            let popup = trigger.parentElement.lastElementChild;
            popup.classList.add('active');
        });
        let closeBtn = trigger.parentElement.lastElementChild.firstElementChild;
        closeBtn.addEventListener('click',()=>{
            let popup = closeBtn.parentElement;
            popup.classList.remove('active');
        });
    });
}
init_builder_events(true);


/**
 * Utility Function to Init Builder Events This function Needs to run when a new element is added
 */
function init_builder_events( is_loadingFirstTime = false ){
    update_display_order(); // updates display order
    draggables_expand_events(); // Inits expand functionality
    draggables_dragging_events(); // inits dragging functionality
    init_drag_triggers(); // add event listners to drag triggers
    draggables_popup_events(); // add popup triggers event listners
    update_quiz_elements_obj(is_loadingFirstTime);
}

// Utility Function to Detect Droping Postion 
function getDragAfterElement(container, y, containerClass ){
    const draggableElements = [...container.querySelectorAll(`.${containerClass}:not(.dragging-active)`)]; // Converting NodeList to Array
    return draggableElements.reduce((closest, currentChild )=>{
        const box = currentChild.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if(offset < 0 && offset > closest.offset){
            return {offset: offset, element: currentChild}
        }else{
            return closest;
        }
    },{ offset : Number.NEGATIVE_INFINITY }).element;
}

/**
 * Utility Function to Build Array of Sub elements
 * @param {string} buildFor - Which Element we want to build for Default is Quiz possible values are 'quiz','speaking-part'
 * @param {container element} itemsContainer - Pass Items Parent Container Selector
 * @param {Boolean} returnJson - Will return JavaScript Object if set to false, Default is true;
 * @return Json Object Or JavaScript Object Depending on returnJson Param
 */
function buildItemData( buildFor = 'quiz', itemsContainer = null , returnJson = true){
    if(buildFor == 'quiz'){
        let builder = document.querySelector('#speaking-quiz-builder');
        let speakingParts = builder.querySelectorAll('.speaking-parts > .speaking-part');
        let quizID = parseInt(builder.dataset.quizId);
        let quizContents = {
            quiz_id: quizID,
            contents: []
        }
        // if Quiz has Speaking Parts 
        if(speakingParts.length > 0){
            speakingParts.forEach(speakingPart => {
                // Speaking Part Information 
                let partId = speakingPart.dataset.speakingPartId;
                let pdisplayOrder = speakingPart.dataset.displayOrder;
                // console.log(speakingPart.dataset);

                // Get Questions
                let questions = [];
                let questionElements = speakingPart.querySelectorAll('.ielts-questions > .ielts-question');
                // if Speaking Part Has Questions 
                if(questionElements.length > 0){
                    questionElements.forEach(question=>{
                        let questionId = question.dataset.questionId;
                        let displayOrder = question.dataset.displayOrder;
                        // Add Data to Questions Array 
                        questions.push({
                            question_id:questionId,
                            display_order:displayOrder
                        });
                    });
                }

                // Add Speaking Part Data to Quiz Contents 
                quizContents.contents.push({
                    speaking_part_id: partId,
                    display_order:pdisplayOrder,
                    questions: questions
                });

            });

        }
        // Add contents to global quiz object 
        quiz_elements.quiz_contents = quizContents;

        if(returnJson){
            return JSON.stringify(quizContents);
        }else{
            return quizContents;
        }

    }

    if(buildFor == 'speaking-part'){
        // Get Questions
        let questions = [];
        let speakingPartId = itemsContainer.dataset.speakingPartId;
        let quizId = itemsContainer.dataset.quizId;
        // console.log(itemsContainer);
        let sp_contents = {
            speaking_part_id : speakingPartId,
            quiz_id : quizId,
            questions: questions
        }
        // console.log(sp_contents);
        let questionElements = itemsContainer.querySelectorAll('.ielts-questions .ielts-question');
        // if Speaking Part Has Questions 
        if(questionElements.length > 0){
            questionElements.forEach(question=>{
                let questionId = question.dataset.questionId;
                let displayOrder = question.dataset.displayOrder;
                // Add Data to Questions Array 
                sp_contents.questions.push({
                    question_id:questionId,
                    display_order:displayOrder
                });
            });
        }

        if(returnJson){
            return JSON.stringify(sp_contents);
        }else{
            return sp_contents;
        }
    
    }
}

/**
 * 
 * @param {string} status 'draft','publish' 
 * @returns Promise containing Ajax Response Data
 */
function save_quiz(status){
    let quizContents = JSON.stringify(quiz_elements.quiz_contents);
    quiz_elements.post_status = status;
    let form = document.querySelector('#speaking-quiz-info');
    if(! is_IsqFormValid(form)){ 
        display_isq_msg('Fix Errors');
        return;
    }
    let data = new FormData(form);
    data.append('quiz_contents_json', quizContents);
    data.append('action', `${status}_s_quiz`);
    return fetch(wpdata.ajaxurl, {
        method: 'post',
        body: data
    })
    .then(res => res.json());
}

// Triggered by onlick event
function publishSpeakingQuiz(savenew = false){
    let button = this.event.target;
    let nonce = button.dataset.nonce;
    let newId = button.dataset.quizId;
    let editUrl = `${wpdata.builderUrl}?action=edit&qid=${newId}&nonce=${nonce}`;
    button.innerText = 'Saving...';
    save_quiz('publish').then(data => {
        if(data.success) {
            display_isq_msg('Quiz Saved Successfully', 'success');
            hideMiniNotice();
            changesSaved = true;
            button.innerText = 'Save Quiz';
        }
        if(savenew){
            window.location.href = editUrl;
        }
    });
}
// Triggered by onlick event
function saveSpeakingQuizDraft(savenew = false){
    let button = this.event.target;
    let nonce = button.dataset.nonce;
    let newId = button.dataset.quizId;
    let editUrl = `${wpdata.builderUrl}?action=edit&qid=${newId}&nonce=${nonce}`;
    save_quiz('draft').then(data => {
        if(data.success){ 
            display_isq_msg('Quiz Saved as Draft', 'success');
            hideMiniNotice();
            changesSaved = true;
        }
        if(savenew){
            window.location.href = editUrl;
        }
    });
}

function saveSpeakingPart(status,formEl){
    let data = new FormData(formEl);
    data.append('action', `${status}_speaking_part`);
    return fetch(wpdata.ajaxurl, {
        method: 'post',
        body: data
    })
    .then(res => res.json());
}

async function publishSpeakingPart(){
    tinymce.triggerSave();
    let button = this.event.target;
    let parentContainer = document.querySelector('.speaking-parts');
    let form = this.event.target.parentElement.parentElement // Form
    if(! is_IsqFormValid(form)){ 
        display_isq_msg('Fix Errors', 'error');
        return;
    }
    button.innerText = 'Adding...';
    let data = await saveSpeakingPart('publish',form)
    .then(data=>{
        form.reset();
        let formPopup = document.querySelector('.add-sp-popup');
        formPopup.classList.remove('active');
        return data;
    });
    if(data.success){
        let Parser = new DOMParser();
        let newElement = Parser.parseFromString(data.data.html, 'text/html');
        parentContainer.appendChild(newElement.body.firstElementChild);
        init_builder_events();
        display_isq_msg('Speaking Part Published Successfully', 'success');
    }
    button.innerText = 'Add Speaking Part';
}

async function updateSpeakingPart(){
    console.log('Before triggerSave');
    tinymce.triggerSave();
    console.log('After triggerSave');
    let button = this.event.target;
    let form = this.event.target.parentElement.parentElement // Form
    console.log(form.querySelector('textarea').value);
    // return;
    if(! is_IsqFormValid(form)){ 
        display_isq_msg('Fix Errors', 'error');
        return;
    }
    button.innerText = 'Updating...';
    let data = await saveSpeakingPart('publish',form)
    .then(data=>{
        let formPopup = document.querySelector('.sp-setting-popup.active');
        formPopup.classList.remove('active');
        return data;
    });
    if(data.success){
        display_isq_msg('Speaking Part Updated Successfully', 'success');
    }
    button.innerText = 'Update Settings';
}


function saveSpeakingQuestion(status,formEl){
    let data = new FormData(formEl);
    data.append('action', `${status}_speaking_q`);
    return fetch(wpdata.ajaxurl, {
        method: 'post',
        body: data
    })
    .then(res => res.json());
}


async function publishSpeakingQuestion(){
    let button = this.event.target;
    let speakingPartID = button.dataset.speakingPartId;
    let parentContainer = document.querySelector(`#speaking-part-${speakingPartID} .ielts-questions`);
    let form = button.parentElement.parentElement // Form - to Avoid multiple ids confilict there will be different forms with 
    tinymce.triggerSave();
    if(! is_IsqFormValid(form)){ 
        display_isq_msg('Fix Errors', 'error');
        return;
    }
    button.innerText = 'Adding...';
    let data = await saveSpeakingQuestion('publish',form)
    .then(data=>{
        form.reset();
        let formPopup = document.querySelector('.add-iq-popup.active');
        formPopup.classList.remove('active');
        return data;
    });
    if(data.success){
        let Parser = new DOMParser();
        let newElement = Parser.parseFromString(data.data.html, 'text/html');
        parentContainer.appendChild(newElement.body.firstElementChild);
        // quiz_elements.questions.push(data.data.id);
        display_isq_msg('Question Published Successfully', 'success');
        init_builder_events();
        init_conditinal_display_fields();
        button.innerText = 'Add New Question';
    }
}

async function updateSpeakingQuestion(){
    let button = this.event.target;
    let speakingPartID = button.dataset.speakingPartId;
    let parentContainer = document.querySelector(`#speaking-part-${speakingPartID} .ielts-questions`);
    let form = button.parentElement.parentElement // Form
    tinymce.triggerSave();
    if(! is_IsqFormValid(form)){ 
        display_isq_msg('Fix Errors', 'error');
        return;
    }
    button.innerText = 'Updating...';
    let data = await saveSpeakingQuestion('publish',form)
    .then(data=>{
        return data;
    });
    if(data.success){
        display_isq_msg('Question Updated Successfully', 'success');
    }
    button.innerText = 'Update Question';
}

function loadQuestions(){
    this.event.preventDefault();
    let form = this.event.target;
    let searchField = form.search_term;
    let speakingPartID = form.sp_id.value;
    let parentElSelector = `#speaking-part-${speakingPartID} .ielts-questions`;
    let action = 'search_ielts_cpt';
    // return;
    let resultsWrapper = form.parentElement.lastElementChild;
    searchField.oninput = () => {
        resultsWrapper.innerHTML = '';
    }
    let formData = new FormData(form);
    formData.append('action', action);
    if(! is_IsqFormValid(form)){
        display_isq_msg('Please Provide Search Term', 'error');
        return;
    }
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData
    })
    .then( res => res.json() )
    .then( response => {
        if(response.success){
            if(response.data.length > 0){
                let html = '';
                response.data.forEach(result => {
                    let resultID = parseInt(result['id']);
                    let triggerEl = `<div class="result-load-trigger" data-question-id="${resultID}" onclick="loadExistingQuestionEl('${parentElSelector}')">Add</div>`;
                    if(quiz_elements.questions.includes(resultID)){
                        triggerEl = `<div class="result-load-trigger exists">Already Exists</div>`;
                    }
                    let resultLabel = result['title'];
                    let resultEl = result['element'];
                    html = html + `
                    <div class="single-result-el">
                        <div class="single-result-trigger">
                            <div class="result-label">${resultLabel} (ID:${resultID})</div>
                            ${triggerEl}
                            <div class="result-element" style="display:none">${resultEl}</div>
                        </div>
                    </div>`;
                });
                resultsWrapper.innerHTML = html;
            }
        }else{
            resultsWrapper.innerHTML = `<p>${response.data}</p>`;
            console.log(response.data);
        }
    } );
}

function loadExistingQuestionEl(parentSelector){
    let trigger = this.event.target;
    let loadContent = trigger.nextElementSibling;
    let parentEl = document.querySelector(`${parentSelector}`);
    if(loadContent.firstElementChild){ // Check if content exists
        parentEl.appendChild(loadContent.firstElementChild);
        trigger.classList.add('exists');
        trigger.innerHTML = 'Added';
    }
    init_builder_events();
    init_conditinal_display_fields();
    tinymce.triggerSave();
}

async function deleteQuestion(){
    let button = this.event.target;
    let sp_id = button.dataset.speakingPartId;
    let quiz_id = button.dataset.quizId;
    let iq_id = button.dataset.questionId;
    let nonce = button.dataset.nonce;
    questionElement = document.querySelector(`#speaking-part-${sp_id} .ielts-questions #ielts-question-${iq_id}`);
    // console.log(questionElement);
    let confirmation = confirm('Do You Really want to Delete Question? Action Cannot be undone');
    if(confirmation){
        questionElement.classList.add('deleting');
        let response = await deleteIeltsPost(iq_id,nonce);
        if(response.success){
            // console.log(response);
            questionElement.remove();
            display_isq_msg('Question Deleted', 'success');
            update_display_order();
            update_quiz_elements_obj();
            init_conditinal_display_fields();
        }else{
            questionElement.classList.remove('deleting');
            display_isq_msg('Error Occured While Deleting the Question', 'error');
        }
    }
    update_display_order();
    update_quiz_elements_obj();
}

function deleteIeltsPost(post_id,nonce){
    let formData = new FormData();
    formData.append('post_id', post_id);
    formData.append('nonce', nonce);
    formData.append('action', 'delete_ielts_post');
    if(post_id && nonce){
        return fetch(wpdata.ajaxurl,{
            method: 'post',
            body: formData
        }).then(res => res.json());
    }
    return new Promise(0);
}

function removeQuestion(){
    let button = this.event.target;
    let sp_id = button.dataset.speakingPartId;
    let quiz_id = button.dataset.quizId;
    let iq_id = button.dataset.questionId;
    let nonce = button.dataset.nonce;
    console.log(sp_id,quiz_id,iq_id);
    questionElement = document.querySelector(`#speaking-part-${sp_id} .ielts-questions #ielts-question-${iq_id}`);
    spParentEl = document.querySelector(`.speaking-parts #speaking-part-${sp_id}`);
    // console.log(questionElement);
    let confirmation = confirm('Do You Really want to Remove Question? Question Can be loaded Again');
    if(confirmation){
        questionElement.classList.add('deleting');
        questionElement.remove();
        display_isq_msg('Question Removed', 'success');
        update_display_order();
            update_quiz_elements_obj();
    }
}

async function deleteSpeakingPart(){
    let button = this.event.target;
    let sp_id = button.dataset.speakingPartId;
    let quiz_id = button.dataset.quizId;
    let iq_id = button.dataset.questionId;
    let nonce = button.dataset.nonce;
    // console.log(sp_id,quiz_id,iq_id);
    speakingPartEl = document.querySelector(`.speaking-parts #speaking-part-${sp_id}`);
    // console.log(speakingPartEl);
    let confirmation = confirm('Do You want to delete Speaking Part? Action Cannot be Undone. Questions Inside this part can be loaded again');
    if(confirmation){
        speakingPartEl.classList.add('deleting');
        let response = await deleteIeltsPost(sp_id,nonce);
        if(response.success){
            // console.log(response);
            speakingPartEl.remove();
            display_isq_msg('Question Deleted', 'success');
            update_display_order();
            update_quiz_elements_obj();
            init_conditinal_display_fields();
        }else{
            speakingPartEl.classList.remove('deleting');
            display_isq_msg('Error Occured While Deleting the Question', 'error');
        }
    }
}


// Do Recording Function Started Can be Customized According to needs
function doRecording(){
    let trigger = this.event.target;
    // Should be taken from DOM
    let fileName = createFileNameFromTitle('Instructor-audio');
    let fileElement = trigger.parentElement.parentElement.querySelector('input');
    let logger = trigger.parentElement.parentElement.querySelector('.recorder-log');
    // Settings 
    let timer = null;
    let recordingTime = 0;
    
    // callback function when recording is stopped by recorder
    function recordingStopped(blob){
        // stop timer
        clearInterval(timer);
        // do whatever with audio blob
        // Temp URL 
        let tempURL = URL.createObjectURL(blob);
        audioEl = trigger.parentElement.parentElement.parentElement.parentElement.querySelector('audio');
        trigger.parentElement.parentElement.parentElement.parentElement.querySelector('.recording-log').innerHTML = 'Recording Ready';
        audioEl.src = tempURL;
        audioEl.load();
        if(fileElement.type != 'file'){
            fileElement.type = 'file';
            fileElement.removeAttribute('value');
        }
        // Creating File For attaching with input type file element 
        let audioFile = new File([blob], fileName, {type:"audio/webm", lastModified:new Date().getTime()})
        let container = new DataTransfer();
        container.items.add(audioFile);
        fileElement.files = container.files;

        // Remove Recording Message 
        logger.classList.remove('active');
        trigger.classList.remove('recording');
        display_isq_msg('Recording Ready', 'success');
    }
    // callback function when recording is Started by recorder
    function recordingStarted(){
        // start timer
        recordingTime = 0;
        timer = setInterval(countRecordingTime,1000);
        logger.classList.add('active');
        trigger.classList.add('recording');
        display_isq_msg('Recording Started', 'success');
        if(trigger.parentElement.parentElement.querySelector('audio')){
            trigger.parentElement.parentElement.querySelector('audio').remove();
        }
    }

    function countRecordingTime(){
        ++recordingTime;
        // renderTime(spRecordedTime, 'timeCounter');
    }

    // Function to draw the audio waveform on the canvas with live audio input
    function drawWaveform(dataArray) {
        console.log(dataArray);
    }

    RecordAudio(recordingStarted, recordingStopped);
}

// function convertTextareaToTinyMCE() {
//     // Ensure TinyMCE is loaded
//     console.log('worked');
//     if (typeof tinymce !== 'undefined') {
//         console.log(tinymce);
//         // Initialize TinyMCE on all textareas with a specific class
//         tinymce.init({
//             selector: 'textarea', // Change this to your textarea's class
//             // Add your TinyMCE configuration options here
//         });
//     } else {
//         console.error('TinyMCE is not loaded');
//     }
// }
// window.addEventListener('load',()=>{
//     convertTextareaToTinyMCE();
// });

// Onclick Event Handler to Toggle Advanced Editor
function enableAdvancedEditor(){
    let trigger = this.event.target;
    this.event.preventDefault();
    let textarea = trigger.parentElement.querySelector('textarea');
    let textarea_id = textarea.id;
    let contentPreview = trigger.parentElement.querySelector('.instructions-content');
    console.log(trigger.dataset.enabled);
    
    if(trigger.dataset.enabled === 'true'){
        trigger.dataset.enabled = 'false';
        console.log(trigger.dataset.enabled);
        tinymce.triggerSave();
        tinymce.get(textarea_id).remove();
        contentPreview.style.display = "block";
        if(textarea.value.trim()==''){
            contentPreview.innerHTML = 'No Content Added';
        }else{
            contentPreview.innerHTML = textarea.value;
        }
        trigger.innerText = 'Advanced Editor';
    }else{
        trigger.dataset.enabled = 'true';
        contentPreview.style.display = "none";
        trigger.innerText = 'Update Content!';
        if (typeof tinymce !== 'undefined') {
            console.log(tinymce);
            // Initialize TinyMCE on all textareas with a specific class
            tinymce.init({
                selector: `#${textarea_id}`, // Change this to your textarea's class
            });
        } else {
            console.error('TinyMCE is not loaded');
        }
    }
}