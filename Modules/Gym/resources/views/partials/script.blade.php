<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Package type toggle
    const individualOption = document.getElementById('individualOption');
    const coupleOption = document.getElementById('coupleOption');
    const packageType = document.getElementById('packageType');
    const member2Section = document.getElementById('member2');

    individualOption.addEventListener('click', () => {
        individualOption.classList.add('active');
        coupleOption.classList.remove('active');
        packageType.value = 'individual';
        member2Section.classList.add('hidden');
    });

    coupleOption.addEventListener('click', () => {
        coupleOption.classList.add('active');
        individualOption.classList.remove('active');
        packageType.value = 'couple';
        member2Section.classList.remove('hidden');
    });

    // Personal Trainer and Sessions toggle
    const personalTrainerYes = document.getElementById('personalTrainerYes');
    const personalTrainerNo = document.getElementById('personalTrainerNo');
    const sessionsSection = document.getElementById('sessionsSection');

    if (personalTrainerYes && personalTrainerNo && sessionsSection) {
        personalTrainerYes.addEventListener('change', () => {
            sessionsSection.style.display = personalTrainerYes.checked ? 'block' : 'none';
        });
        personalTrainerNo.addEventListener('change', () => {
            sessionsSection.style.display = personalTrainerNo.checked ? 'none' : 'block';
        });
    }

    // Medical Conditions toggle for Member 1
    const medicalConditionsYes1 = document.getElementById('medical_conditions_yes_1');
    const medicalConditionsNo1 = document.getElementById('medical_conditions_no_1');
    const medicalConditionsDetails1 = document.getElementById('medical_conditions_details_1');

    if (medicalConditionsYes1 && medicalConditionsNo1 && medicalConditionsDetails1) {
        medicalConditionsYes1.addEventListener('change', () => {
            medicalConditionsDetails1.style.display = medicalConditionsYes1.checked ? 'block' : 'none';
        });
        medicalConditionsNo1.addEventListener('change', () => {
            medicalConditionsDetails1.style.display = medicalConditionsNo1.checked ? 'none' : 'block';
        });
    }

    // Medical Conditions toggle for Member 2
    const medicalConditionsYes2 = document.getElementById('medical_conditions_yes_2');
    const medicalConditionsNo2 = document.getElementById('medical_conditions_no_2');
    const medicalConditionsDetails2 = document.getElementById('medical_conditions_details_2');

    if (medicalConditionsYes2 && medicalConditionsNo2 && medicalConditionsDetails2) {
        medicalConditionsYes2.addEventListener('change', () => {
            medicalConditionsDetails2.style.display = medicalConditionsYes2.checked ? 'block' : 'none';
        });
        medicalConditionsNo2.addEventListener('change', () => {
            medicalConditionsDetails2.style.display = medicalConditionsNo2.checked ? 'none' : 'block';
        });
    }

    // Fitness Goals "Other" toggle for Member 1
    const fitnessGoalsOther1 = document.getElementById('fitness_goals_other_1');
    const fitnessGoalsOtherDetails1 = document.getElementById('fitness_goals_other_details_1');

    if (fitnessGoalsOther1 && fitnessGoalsOtherDetails1) {
        fitnessGoalsOther1.addEventListener('change', () => {
            fitnessGoalsOtherDetails1.style.display = fitnessGoalsOther1.checked ? 'block' : 'none';
        });
    }

    // Fitness Goals "Other" toggle for Member 2
    const fitnessGoalsOther2 = document.getElementById('fitness_goals_other_2');
    const fitnessGoalsOtherDetails2 = document.getElementById('fitness_goals_other_details_2');

    if (fitnessGoalsOther2 && fitnessGoalsOtherDetails2) {
        fitnessGoalsOther2.addEventListener('change', () => {
            fitnessGoalsOtherDetails2.style.display = fitnessGoalsOther2.checked ? 'block' : 'none';
        });
    }
</script>
 <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#gymMemberTable').DataTable({
                responsive: true,
                columnDefs: [{
                        orderable: false,
                        targets: [4, 7, 10]
                    },
                    {
                        searchable: false,
                        targets: [4, 7, 10]
                    }
                ]
            });
        });
    </script>
    