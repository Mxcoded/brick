// Employment History
document.getElementById('add-employment-history').addEventListener('click', function () {
    const container = document.getElementById('employment-history-container');
    const index = container.children.length;
    const newForm = document.createElement('div');
    newForm.innerHTML = `@include('staff::partials.employment_history_form', ['index' => 'INDEX'])`
        .replace(/INDEX/g, index);
    container.appendChild(newForm);
});

// Educational Background
document.getElementById('add-educational-background').addEventListener('click', function () {
    const container = document.getElementById('educational-background-container');
    const index = container.children.length;
    const newForm = document.createElement('div');
    newForm.innerHTML = `@include('staff::partials.educational_background_form', ['index' => 'INDEX'])`
        .replace(/INDEX/g, index);
    container.appendChild(newForm);
});