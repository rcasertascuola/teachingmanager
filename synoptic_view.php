<?php
// synoptic_view.php
include 'header.php';
?>

<style>
    .tree-view ul {
        list-style-type: none;
        padding-left: 20px;
    }
    .tree-view li {
        position: relative;
    }
    .tree-view .toggler {
        cursor: pointer;
        position: absolute;
        left: -15px;
        font-weight: bold;
        padding-right: 5px;
    }
    .tree-view .toggler::before {
        content: "+";
    }
    .tree-view .open > .toggler::before {
        content: "-";
    }
    .tree-view .children {
        display: none;
    }
    .tree-view .open > .children {
        display: block;
    }
    .tree-view .leaf-node {
        padding-left: 15px;
    }
    .tree-view .link-icon {
        margin-left: 8px;
        color: #007bff;
        text-decoration: none;
    }
    .tree-view .link-icon:hover {
        color: #0056b3;
    }
    .tree-item-content {
        display: flex;
        align-items: center;
    }
</style>
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

            <div id="synoptic-tree-container" class="tree-view">
                <!-- Tree will be loaded here -->
            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const synopticTreeContainer = document.getElementById('synoptic-tree-container');
            const annoCorsoFilter = document.getElementById('anno-corso-filter');

            function fetchData(annoCorso = '') {
                let url = 'synoptic_data.php';
                if (annoCorso) {
                    url += '?anno_corso=' + annoCorso;
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        renderTree(data);
                    });
            }

            function parseAndRenderHeadings(content, container, lessonId) {
                const headings = [];
                const lines = content.split('\n');
                lines.forEach(line => {
                    const match = line.match(/^(={1,})\s*(.*?)\s*\1/);
                    if (match) {
                        headings.push({
                            level: match[1].length,
                            title: match[2],
                            children: []
                        });
                    }
                });

                if (headings.length === 0) return;

                const root = { level: 0, children: [] };
                const stack = [root];

                headings.forEach(heading => {
                    while (stack.length > 0 && stack[stack.length - 1].level >= heading.level) {
                        stack.pop();
                    }
                    stack[stack.length - 1].children.push(heading);
                    stack.push(heading);
                });

                function render(nodes, parentUl) {
                    nodes.forEach(node => {
                        const li = document.createElement('li');
                        const hasChildren = node.children.length > 0;
                        const slug = slugify(node.title);
                        const link = `lessons/view.php?id=${lessonId}#${slug}`;
                        li.innerHTML = `${hasChildren ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><span>${node.title}</span><a href="${link}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                        if (hasChildren) {
                            const ul = document.createElement('ul');
                            ul.className = 'children';
                            render(node.children, ul);
                            li.appendChild(ul);
                        }
                        parentUl.appendChild(li);
                    });
                }

                render(root.children, container);
            }
            function slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
            }

            function renderTree(data) {
                synopticTreeContainer.innerHTML = '';
                const rootUl = document.createElement('ul');

                data.forEach(module => {
                    const moduleLi = document.createElement('li');
                    const hasUdas = module.udas.length > 0;
                    let moduleInfo = '';
                    if (module.disciplina_name || module.anno_corso) {
                        const infoParts = [];
                        if (module.disciplina_name) infoParts.push(module.disciplina_name);
                        if (module.anno_corso) infoParts.push(`Anno ${module.anno_corso}`);
                        moduleInfo = ` <small>(${infoParts.join(', ')})</small>`;
                    }
                    moduleLi.innerHTML = `${hasUdas ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><h5><i class="fas fa-puzzle-piece"></i> ${module.name}${moduleInfo}</h5><a href="modules/view.php?id=${module.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;

                    if (hasUdas) {
                        const moduleUl = document.createElement('ul');
                        moduleUl.className = 'children';

                        module.udas.forEach(uda => {
                            const udaLi = document.createElement('li');
                            const hasLessons = uda.lessons.length > 0;
                            udaLi.innerHTML = `${hasLessons ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><h6><i class="fas fa-book"></i> ${uda.name}</h6><a href="udas/view.php?id=${uda.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;

                            if (hasLessons) {
                                const udaUl = document.createElement('ul');
                                udaUl.className = 'children';

                                uda.lessons.forEach(lesson => {
                                    const lessonLi = document.createElement('li');
                                    const hasContent = lesson.content || lesson.conoscenze.length > 0 || lesson.abilita.length > 0 || lesson.exercises.length > 0;
                                    lessonLi.innerHTML = `${hasContent ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><strong><i class="fas fa-chalkboard-teacher"></i> ${lesson.title}</strong><a href="lessons/view.php?id=${lesson.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;

                                    if (hasContent) {
                                        const lessonUl = document.createElement('ul');
                                        lessonUl.className = 'children';

                                        if (lesson.content) {
                                            parseAndRenderHeadings(lesson.content, lessonUl, lesson.id);
                                        }

                                        if (lesson.conoscenze.length > 0) {
                                            const conoscenzeLi = document.createElement('li');
                                            conoscenzeLi.innerHTML = `<span class="toggler"></span><div class="tree-item-content"><strong><i class="fas fa-lightbulb"></i> Conoscenze</strong></div>`;
                                            const conoscenzeUl = document.createElement('ul');
                                            conoscenzeUl.className = 'children';
                                            lesson.conoscenze.forEach(conoscenza => {
                                                const li = document.createElement('li');
                                                const hasCompetenze = conoscenza.competenze && conoscenza.competenze.length > 0;
                                                li.innerHTML = `${hasCompetenze ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><span>${conoscenza.nome}</span><a href="conoscenze/view.php?id=${conoscenza.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                if (hasCompetenze) {
                                                    const ul = document.createElement('ul');
                                                    ul.className = 'children';
                                                    conoscenza.competenze.forEach(competenza => {
                                                        const compLi = document.createElement('li');
                                                        const hasDiscipline = competenza.discipline && competenza.discipline.length > 0;
                                                        compLi.innerHTML = `${hasDiscipline ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><span><i class="fas fa-graduation-cap"></i> ${competenza.nome}</span><a href="competenze/view.php?id=${competenza.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                        if (hasDiscipline) {
                                                            const discUl = document.createElement('ul');
                                                            discUl.className = 'children';
                                                            competenza.discipline.forEach(disciplina => {
                                                                const discLi = document.createElement('li');
                                                                discLi.className = 'leaf-node';
                                                                discLi.innerHTML = `<div class="tree-item-content"><span><i class="fas fa-atom"></i> ${disciplina.nome}</span><a href="discipline/view.php?id=${disciplina.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                                discUl.appendChild(discLi);
                                                            });
                                                            compLi.appendChild(discUl);
                                                        }
                                                        ul.appendChild(compLi);
                                                    });
                                                    li.appendChild(ul);
                                                }
                                                conoscenzeUl.appendChild(li);
                                            });
                                            conoscenzeLi.appendChild(conoscenzeUl);
                                            lessonUl.appendChild(conoscenzeLi);
                                        }

                                        if (lesson.abilita.length > 0) {
                                            const abilitaLi = document.createElement('li');
                                            abilitaLi.innerHTML = `<span class="toggler"></span><div class="tree-item-content"><strong><i class="fas fa-cogs"></i> Abilità</strong></div>`;
                                            const abilitaUl = document.createElement('ul');
                                            abilitaUl.className = 'children';
                                            lesson.abilita.forEach(skill => {
                                                const li = document.createElement('li');
                                                const hasCompetenze = skill.competenze && skill.competenze.length > 0;
                                                li.innerHTML = `${hasCompetenze ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><span>${skill.nome}</span><a href="abilita/view.php?id=${skill.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                if (hasCompetenze) {
                                                    const ul = document.createElement('ul');
                                                    ul.className = 'children';
                                                    skill.competenze.forEach(competenza => {
                                                        const compLi = document.createElement('li');
                                                        const hasDiscipline = competenza.discipline && competenza.discipline.length > 0;
                                                        compLi.innerHTML = `${hasDiscipline ? '<span class="toggler"></span>' : '<span class="leaf-node"></span>'}<div class="tree-item-content"><span><i class="fas fa-graduation-cap"></i> ${competenza.nome}</span><a href="competenze/view.php?id=${competenza.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                        if (hasDiscipline) {
                                                            const discUl = document.createElement('ul');
                                                            discUl.className = 'children';
                                                            competenza.discipline.forEach(disciplina => {
                                                                const discLi = document.createElement('li');
                                                                discLi.className = 'leaf-node';
                                                                discLi.innerHTML = `<div class="tree-item-content"><span><i class="fas fa-atom"></i> ${disciplina.nome}</span><a href="discipline/view.php?id=${disciplina.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                                discUl.appendChild(discLi);
                                                            });
                                                            compLi.appendChild(discUl);
                                                        }
                                                        ul.appendChild(compLi);
                                                    });
                                                    li.appendChild(ul);
                                                }
                                                abilitaUl.appendChild(li);
                                            });
                                            abilitaLi.appendChild(abilitaUl);
                                            lessonUl.appendChild(abilitaLi);
                                        }

                                        if (lesson.exercises.length > 0) {
                                            const exercisesLi = document.createElement('li');
                                            exercisesLi.innerHTML = `<span class="toggler"></span><div class="tree-item-content"><strong><i class="fas fa-pencil-ruler"></i> Exercises</strong></div>`;
                                            const exercisesUl = document.createElement('ul');
                                            exercisesUl.className = 'children';
                                            lesson.exercises.forEach(exercise => {
                                                const li = document.createElement('li');
                                                li.className = 'leaf-node';
                                                li.innerHTML = `<div class="tree-item-content"><span>${exercise.title}</span><a href="exercises/view.php?id=${exercise.id}" class="link-icon" target="_blank"><i class="fas fa-external-link-alt"></i></a></div>`;
                                                exercisesUl.appendChild(li);
                                            });
                                            exercisesLi.appendChild(exercisesUl);
                                            lessonUl.appendChild(exercisesLi);
                                        }

                                        lessonLi.appendChild(lessonUl);
                                    }
                                    udaUl.appendChild(lessonLi);
                                });
                                udaLi.appendChild(udaUl);
                            }
                            moduleUl.appendChild(udaLi);
                        });
                        moduleLi.appendChild(moduleUl);
                    }
                    rootUl.appendChild(moduleLi);
                });

                synopticTreeContainer.appendChild(rootUl);
            }

            fetchData();

            annoCorsoFilter.addEventListener('change', function () {
                fetchData(this.value);
            });

            synopticTreeContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('toggler')) {
                    event.target.parentElement.classList.toggle('open');
                }
            });
        });
    </script>
<?php include 'footer.php'; ?>
