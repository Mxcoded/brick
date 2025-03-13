document.addEventListener('DOMContentLoaded', function () {
    // Employment History
    document.getElementById('add-employment-history').addEventListener('click', function () {
        console.log('Add Employment History button clicked'); // Debugging
        const container = document.getElementById('employment-history-container');
        const index = container.children.length;
        const newForm = document.createElement('div');
        newForm.classList.add('employment-history-form', 'mb-4');
        newForm.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employer_name">Employer Name</label>
                        <input type="text" name="employment_history[${index}][employer_name]" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employer_contact">Employer Contact</label>
                        <input type="text" name="employment_history[${index}][employer_contact]" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="position_held">Position Held</label>
                        <input type="text" name="employment_history[${index}][position_held]" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="duration">Duration</label>
                        <input type="text" name="employment_history[${index}][duration]" class="form-control">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newForm);
    });

    // Educational Background
    document.getElementById('add-educational-background').addEventListener('click', function () {
        console.log('Add Educational Background button clicked'); // Debugging
        const container = document.getElementById('educational-background-container');
        const index = container.children.length;
        const newForm = document.createElement('div');
        newForm.classList.add('educational-background-form', 'mb-4');
        newForm.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="school_name">School Name</label>
                        <input type="text" name="educational_background[${index}][school_name]" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" name="educational_background[${index}][qualification]" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="educational_background[${index}][start_date]" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="educational_background[${index}][end_date]" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="certificate_path">Upload Certificate</label>
                        <input type="file" name="educational_background[${index}][certificate_path]" class="form-control">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newForm);
    });
});