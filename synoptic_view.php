<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'src/User.php';
$user = User::findById($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synoptic View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            margin-bottom: 1rem;
        }
        .card-header {
            cursor: pointer;
        }
        .competenza {
            margin-left: 20px;
        }
        .disciplina {
            margin-left: 40px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="udas/index.php">
                                <i class="fas fa-book"></i> UDA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="modules/index.php">
                                <i class="fas fa-puzzle-piece"></i> Modules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="lessons/index.php">
                                <i class="fas fa-chalkboard-teacher"></i> Lessons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="exercises/index.php">
                                <i class="fas fa-pencil-ruler"></i> Exercises
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="conoscenze/index.php">
                                <i class="fas fa-lightbulb"></i> Knowledge
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="abilita/index.php">
                                <i class="fas fa-tools"></i> Skills
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="competenze/index.php">
                                <i class="fas fa-graduation-cap"></i> Competencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="discipline/index.php">
                                <i class="fas fa-atom"></i> Disciplines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="synoptic_view.php">
                                <i class="fas fa-sitemap"></i> Synoptic View
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    udaCard.className = 'card';
                    udaCard.innerHTML = `
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#uda-${uda.id}">
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
                        moduleCard.className = 'card';
                        moduleCard.innerHTML = `
                            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#module-${module.id}">
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
                            lessonCard.className = 'card';
                            lessonCard.innerHTML = `
                                <div class="card-header" data-bs-toggle="collapse" data-bs-target="#lesson-${lesson.id}">
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
                                    </div>
                                </div>
                            `;
                            lessonsContainer.appendChild(lessonCard);

                            const conoscenzeList = document.getElementById(`lesson-${lesson.id}-conoscenze`);
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

                            const abilitaList = document.getElementById(`lesson-${lesson.id}-abilita`);
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
</body>
</html>
