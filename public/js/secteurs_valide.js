        
 // Delete alert 
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Empêche la soumission immédiate

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Soumet le formulaire si confirmé
                }
            });
        });
    });


// Tri de table 

document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('sortableTable');
    const headers = table.querySelectorAll('thead th');
    const tbody = table.querySelector('tbody');
    const sortDirections = new Array(headers.length).fill(1);

    headers.forEach((header, index) => {
        // Ignore colonne actions
        if (header.textContent.trim().toLowerCase() === 'actions') return;

        header.style.cursor = 'pointer';

        header.addEventListener('click', () => {
            const rowsArray = Array.from(tbody.querySelectorAll('tr'));

            rowsArray.sort((rowA, rowB) => {
                const cellA = rowA.children[index].textContent.trim();
                const cellB = rowB.children[index].textContent.trim();

                // Essayer de comparer comme des nombres
                const aVal = parseFloat(cellA.replace(',', '.'));
                const bVal = parseFloat(cellB.replace(',', '.'));

                const isNumeric = !isNaN(aVal) && !isNaN(bVal);

                if (isNumeric) {
                    return (aVal - bVal) * sortDirections[index];
                } else {
                    return cellA.localeCompare(cellB) * sortDirections[index];
                }
            });

            // Appliquer le tri dans le DOM
            rowsArray.forEach(row => tbody.appendChild(row));

            // Inverser la direction pour la prochaine fois
            sortDirections[index] *= -1;
        });
    });
});


//Bar de recherche de table 

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('columnSelect');
    const searchInputWrapper = document.getElementById('searchColumnWrapper');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('sortableTable');
    const tbody = table.querySelector('tbody');

    let selectedColumn = null;

    select.addEventListener('change', function () {
        selectedColumn = this.value;
        if (selectedColumn !== "") {
            searchInputWrapper.classList.remove('d-none');
        } else {
            searchInputWrapper.classList.add('d-none');
            searchInput.value = "";
            showAllRows();
        }
    });

    searchInput.addEventListener('input', function () {
        const filter = this.value.toLowerCase();

        Array.from(tbody.rows).forEach(row => {
            const cell = row.cells[selectedColumn];
            if (!cell) return;

            const text = cell.textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    function showAllRows() {
        Array.from(tbody.rows).forEach(row => {
            row.style.display = "";
        });
    }
});


//colonne select  

    document.getElementById('columnSelect').addEventListener('change', function () {
        const inputWrapper = document.getElementById('searchColumnWrapper');
        if (this.value) {
            inputWrapper.classList.remove('d-none');
        } else {
            inputWrapper.classList.add('d-none');
        }
    });

// Modal pour les actions de la table
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('customModal');
    const modalContent = document.getElementById('modalDetails');
    const closeModal = document.getElementById('closeModal');

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Pour chaque cellule déclencheuse
    document.querySelectorAll('.modal-trigger').forEach(cell => {
        cell.addEventListener('click', function () {
            const row = cell.closest('tr');

            // Vérifie si l'élément <tr> a bien les attributs data-*
            if (!row || !row.dataset) {
                console.error("Impossible de récupérer les données de la ligne");
                return;
            }

            const matricule = row.dataset.matricule || 'N/A';
            const created = row.dataset.created || 'N/A';
            const updated = row.dataset.updated || 'N/A';
            const updatedBy = row.dataset.updatedby || 'N/A';
            const createdBy = row.dataset.createdby || 'N/A';

            // On prépare la partie "Dernière modification" uniquement si updated et updatedBy sont valides
            let updatedSection = '';
            if (updated.trim() !== '' && updated.trim() !== 'N/A') {
                updatedSection = `
                    <p><strong>Dernière modification le :</strong> ${updated}
                    <strong>par :</strong> ${updatedBy}</p>
                `;
            }

            modalContent.innerHTML = `
                <p><strong>Matricule :</strong> ${matricule}</p>
                <p><strong>Créé le :</strong> ${created} <strong>par :</strong> ${createdBy}</p>
                ${updatedSection}
            `;

            modal.style.display = 'block';
        });
    });

    // Fermeture si clic hors modal
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

//Pagination
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('sortableTable');
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const rowsPerPage = 10;
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    let currentPage = 1;
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination mt-3 d-flex justify-content-center flex-wrap gap-1';
    const prevButton = document.createElement('button');
    prevButton.textContent = 'Précédent';
    prevButton.className = 'btn btn-sm btn-secondary';
    prevButton.disabled = true;
    const nextButton = document.createElement('button');
    nextButton.textContent = 'Suivant';
    nextButton.className = 'btn btn-sm btn-secondary';
    const pageButtons = [];
    function showPage(page) {
        currentPage = page;
        rows.forEach((row, index) => {
            row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
        });
        pageButtons.forEach((btn, i) => {
            btn.classList.toggle('active', i + 1 === page);
        });
        prevButton.disabled = page === 1;
        nextButton.disabled = page === totalPages;
    }
    function createPagination() {
        paginationContainer.appendChild(prevButton);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'btn btn-sm btn-outline-primary';
            btn.addEventListener('click', () => showPage(i));
            paginationContainer.appendChild(btn);
            pageButtons.push(btn);
        }
        paginationContainer.appendChild(nextButton);
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) showPage(currentPage - 1);
        });
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) showPage(currentPage + 1);
        });

       const wrapper = document.getElementById('pagination-wrapper');
        if (wrapper) {
            wrapper.innerHTML = ''; // vide au cas où
            wrapper.appendChild(paginationContainer);
        }
            }
        if (rows.length > rowsPerPage) {
            createPagination();
            showPage(1);

            const wrapper = document.getElementById('pagination-wrapper');
            if (wrapper) {
                wrapper.innerHTML = '';
                wrapper.appendChild(paginationContainer);
            }
        }
        });
