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

function openEditModal(id, nama, nip, username, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_nip').value = nip;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    openModal('modalEdit');
}

function openDeleteModal(id, nama) {
    document.getElementById('hapus_id').value = id;
    document.getElementById('hapus_nama_text').innerText = nama;
    openModal('modalHapus');
}