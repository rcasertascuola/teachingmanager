<?php
// synoptic_view.php
include 'header.php';
?>

    <div class="container-fluid">
        <main class="px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Synoptic View</h1>
                <div class="col-md-3">
                    <select id="anno-corso-filter" class="form-select">
                        <option value="">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                        <option value="5">Year 5</option>
                    </select>
                </div>
            </div>

            <div id="synoptic-content">
                <!-- Content will be loaded here -->
            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const synopticContent = document.getElementById('synoptic-content');
            const annoCorsoFilter = document.getElementById('anno-corso-filter');

            function fetchData(annoCorso = '') {
                let url = 'synoptic_data.php';
                if (annoCorso) {
                    url += '?anno_corso=' + annoCorso;
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        renderData(data);
                    });
            }

            function renderData(data) {
                synopticContent.innerHTML = '';
                data.forEach(uda => {
                    const udaCard = document.createElement('div');
                    udaCard.className = 'card mb-3';
                    udaCard.innerHTML = `
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#uda-${uda.id}" style="cursor: pointer;">
                            <h5><i class="fas fa-book"></i> ${uda.name}</h5>
                        </div>
                        <div id="uda-${uda.id}" class="collapse">
                            <div class="card-body">
                                <p>${uda.description}</p>
                                <div id="uda-${uda.id}-modules"></div>
                            </div>
                        </div>
                    `;
                    synopticContent.appendChild(udaCard);

                    const modulesContainer = document.getElementById(`uda-${uda.id}-modules`);
                    uda.modules.forEach(module => {
                        const moduleCard = document.createElement('div');
                        moduleCard.className = 'card mb-3';
                        moduleCard.innerHTML = `
                            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#module-${module.id}" style="cursor: pointer;">
                                <h6><i class="fas fa-puzzle-piece"></i> ${module.name}</h6>
                            </div>
                            <div id="module-${module.id}" class="collapse">
                                <div class="card-body">
                                    <p>${module.description}</p>
                                    <div id="module-${module.id}-lessons"></div>
                                </div>
                            </div>
                        `;
                        modulesContainer.appendChild(moduleCard);

                        const lessonsContainer = document.getElementById(`module-${module.id}-lessons`);
                        module.lessons.forEach(lesson => {
                            const lessonCard = document.createElement('div');
                            lessonCard.className = 'card mb-3';
                            lessonCard.innerHTML = `
                                <div class="card-header" data-bs-toggle="collapse" data-bs-target="#lesson-${lesson.id}" style="cursor: pointer;">
                                    <strong><i class="fas fa-chalkboard-teacher"></i> ${lesson.title}</strong>
                                </div>
                                <div id="lesson-${lesson.id}" class="collapse">
                                    <div class="card-body">
                                        <p>${lesson.content}</p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-lightbulb"></i> Knowledge:</strong>
                                                <ul id="lesson-${lesson.id}-conoscenze"></ul>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-tools"></i> Skills:</strong>
                                                <ul id="lesson-${lesson.id}-abilita"></ul>
                                            </div>
                                        </div>
                                        <hr>
                                        <strong><i class="fas fa-pencil-ruler"></i> Exercises:</strong>
                                        <ul id="lesson-${lesson.id}-exercises"></ul>
                                    </div>
                                </div>
                            `;
                            lessonsContainer.appendChild(lessonCard);

                            // Populate Knowledge
                            const conoscenzeList = document.getElementById(`lesson-${lesson.id}-conoscenze`);
                            if (lesson.conoscenze.length > 0) {
                                lesson.conoscenze.forEach(conoscenza => {
                                    const li = document.createElement('li');
                                    li.innerHTML = `<span>${conoscenza.nome}</span>`;
                                    const competenzaList = document.createElement('ul');
                                    conoscenza.competenze.forEach(competenza => {
                                        const compLi = document.createElement('li');
                                        compLi.className = 'competenza';
                                        compLi.innerHTML = `<span><i class="fas fa-graduation-cap"></i> ${competenza.nome}</span>`;
                                        const disciplinaList = document.createElement('ul');
                                        competenza.discipline.forEach(disciplina => {
                                            const discLi = document.createElement('li');
                                            discLi.className = 'disciplina';
                                            discLi.innerHTML = `<span><i class="fas fa-atom"></i> ${disciplina.nome}</span>`;
                                            disciplinaList.appendChild(discLi);
                                        });
                                        compLi.appendChild(disciplinaList);
                                        competenzaList.appendChild(compLi);
                                    });
                                    li.appendChild(competenzaList);
                                    conoscenzeList.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.textContent = 'No knowledge points for this lesson.';
                                conoscenzeList.appendChild(li);
                            }

                            // Populate Skills
                            const abilitaList = document.getElementById(`lesson-${lesson.id}-abilita`);
                            if (lesson.abilita.length > 0) {
                                lesson.abilita.forEach(abilita => {
                                    const li = document.createElement('li');
                                    li.innerHTML = `<span>${abilita.nome}</span>`;
                                    const competenzaList = document.createElement('ul');
                                    abilita.competenze.forEach(competenza => {
                                        const compLi = document.createElement('li');
                                        compLi.className = 'competenza';
                                        compLi.innerHTML = `<span><i class="fas fa-graduation-cap"></i> ${competenza.nome}</span>`;
                                        const disciplinaList = document.createElement('ul');
                                        competenza.discipline.forEach(disciplina => {
                                            const discLi = document.createElement('li');
                                            discLi.className = 'disciplina';
                                            discLi.innerHTML = `<span><i class="fas fa-atom"></i> ${disciplina.nome}</span>`;
                                            disciplinaList.appendChild(discLi);
                                        });
                                        compLi.appendChild(disciplinaList);
                                        competenzaList.appendChild(compLi);
                                    });
                                    li.appendChild(competenzaList);
                                    abilitaList.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.textContent = 'No skills for this lesson.';
                                abilitaList.appendChild(li);
                            }

                            // Populate Exercises
                            const exercisesList = document.getElementById(`lesson-${lesson.id}-exercises`);
                            if (lesson.exercises.length > 0) {
                                lesson.exercises.forEach(exercise => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item';
                                    li.textContent = exercise.title;
                                    exercisesList.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.className = 'list-group-item';
                                li.textContent = 'No exercises for this lesson.';
                                exercisesList.appendChild(li);
                            }
                        });
                    });
                });
            }

            fetchData();

            annoCorsoFilter.addEventListener('change', function () {
                fetchData(this.value);
            });
        });
    </script>
<?php include 'footer.php'; ?>
