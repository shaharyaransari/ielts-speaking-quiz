let filterData = {
    page_number : 1,
    posts_per_page : 10,
}
function toggleCheckboxes(){
    let button = this.event.target;
    let checkboxes = document.querySelectorAll('.quiz-action-input input');
    if(button.dataset.checked == 'true'){
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        button.dataset.checked = 'false';
        button.innerText = 'Select All';
    }else{
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        button.dataset.checked = 'true';
        button.innerText = 'Unselect All';
    }
}
function deleteQuizzes(){
    let button = this.event.target;
    let nonce = button.dataset.nonce;
    let quizIds = getSelectedCheckboxes();
    if(! (quizIds.length > 0)){
        display_isq_msg('No Checkboxes Selected');
        return;
    }else{
        quizIds = JSON.stringify(quizIds);
        let confirmation = confirm('Do You Really want to delete quizzes? This action cannot be undone');
        if(! confirmation ){
            return;
        }
    }
    let formData = new FormData();

    formData.append('action', 'delete_ielts_quizzes');
    formData.append('nonce', nonce);
    formData.append('quizzes', quizIds);
    button.innerText = 'Deleting...';
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData,
    }).then(res => res.json())
    .then(data => {
        if(data.success){
            display_isq_msg('Selected Quizzes Deleted', 'success');
            updateQuizList(filterData);
            button.innerText = 'Delete Selected';
        }
    });
}

function getSelectedCheckboxes(){
    let checkboxes = document.querySelectorAll('.quiz-action-input input');
    let quizIds = [];
    checkboxes.forEach(checkbox => {
        if(checkbox.checked){
            quizIds.push(checkbox.id);
        }
    })
    return quizIds;
}
  setAjaxPagination();
  function setAjaxPagination(){
    let paginationLinks = document.querySelectorAll('#quiz_pagination .page-numbers');
  paginationLinks.forEach(paginationLink => {
    paginationLink.onclick = function(event){
      event.preventDefault();
      let pageLink = event.target.href;
      let pageNumber = 1;
      let PageIndex = 0;
      if(pageLink){
        if( pageLink.includes('/page/')){
        urlParts = pageLink.split('/');
        pageIndex = urlParts.length - 2;
        pageNumber = urlParts[pageIndex];
        console.log(pageNumber);
        }else{
        console.log(pageNumber);
        }
        filterData.page_number = pageNumber;
        updateQuizList(filterData);
      }
    }
  });
}

function updateQuizList(data){
    let listWrapper = document.querySelector('#ielts-speaking-quizzes-list');
    let pagination = document.querySelector('#quiz_pagination');
    let formData = new FormData();
    formData.append('data', JSON.stringify(data));
    formData.append('action', 'fetch_quizzes_list');
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData
    }).then(res=>res.json())
    .then(response =>{
        if(response.success){
            listWrapper.innerHTML = response.data.html;
            pagination.innerHTML = response.data.pagination;
            setAjaxPagination();
        }
    })
}

function updateQuizStatus(){
    let button = this.event.target;
    let newStatus = button.dataset.newStatus;
    let quizId = button.dataset.quizId;
    let formData = new FormData();
    formData.append('action', 'change_quiz_status');
    formData.append('new_status', newStatus);
    formData.append('quiz_id', quizId);
    if(newStatus == 'draft'){
        button.innerText = 'Drafting...';
    }else{
        button.innerText = 'Publishing...';
    }
    fetch(wpdata.ajaxurl, {
        method: 'post',
        body: formData
    })
    .then(res => res.json())
    .then(response => {
        if(response.success){
            if(newStatus == 'draft'){
                display_isq_msg('Quiz Drafted Successfully');
                button.dataset.newStatus = 'publish'; 
            }else{
                display_isq_msg('Quiz Published Successfully', 'success');
                button.dataset.newStatus = 'draft';
            }
            updateQuizList(filterData);
        }
    });
}

function deleteQuiz(){
    let button = this.event.target;
    let quizId = JSON.stringify([button.dataset.quizId]);
    let nonce = button.dataset.nonce;
    let confirmation = confirm('Do you Really Want to Delete The Quiz?');
    if(! confirmation){
        display_isq_msg('Action Cancelled');
        return;
    }
    let formData = new FormData();
    formData.append('action', 'delete_ielts_quizzes');
    formData.append('nonce', `${nonce}`);
    formData.append('quizzes', quizId);
    button.innerText = 'Deleting...';
    fetch(wpdata.ajaxurl,{
        method: 'post',
        body: formData,
    }).then(res => res.json())
    .then(response => {
        if(response.success){
            display_isq_msg('Quiz Deleted', 'success');
            updateQuizList(filterData);
        }
    })
}