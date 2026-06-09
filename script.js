console.log("File script.js sudah berhasil dimuat oleh browser!");
//Sidebar Toggle Script
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar'); 
    
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        if (toggleBtn) {
            if (sidebar.classList.contains('collapsed')) {
                toggleBtn.classList.replace('fa-angle-left', 'fa-angle-right');
            } else {
                toggleBtn.classList.replace('fa-angle-right', 'fa-angle-left');
            }
        }
    }
};

// Modal Script
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    let form = document.querySelector(`#${modalId} form`);
    if (form) { form.reset(); }
}

function openDeleteModal(id, nama) {
    document.getElementById('hapus_id').value = id;
    document.getElementById('hapus_nama_text').innerText = nama;
    openModal('modalHapus');
}

// Search Tabel Script
function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#mapelTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }