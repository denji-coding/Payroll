<?php
$title = "Owner Branches";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");
?>

<style>
    /* Ensure close button is visible */
.btn-close {
    background: transparent;
    border: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    cursor: pointer;
    padding: 0;
    width: auto;
    height: auto;
}

.btn-close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

.btn-close:focus {
    outline: none;
    box-shadow: none;
}
</style>
<!-- h-[calc(100vh-3rem)] overflow-hidden p-4 md:p-6 sm:ml-64 mt-12 bg-[#f8fbf8] -->
<main id="mainContent" class="h-[calc(100vh-3rem)] overflow-hidden p-4 md:p-6 sm:ml-64 bg-[#f8fbf8] min-h-screen">
    <header class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-2xl font-bold tracking-tight text-[#133913]">Branches</span>
                <p class="text-[#478547]">Manage company branches: add, update, delete.</p>
            </div>
            <button id="addBranchBtn" class="btn btn-success d-inline-flex align-items-center h-10 px-4 py-2 " data-bs-toggle="modal" data-bs-target="#addBranchModal">
              <i class="fas fa-plus me-2"></i> 
              <span class="font-semibold">Add Branch</span>
            </button>
        </div>
    </header>

    <section class="bg-white border-2 border-green-200 rounded-lg p-4">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
            <thead class="sticky top-0 z-10">
                <tr class="border-b hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
                        <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white w-[8%]">No.</th>
                        <th class="h-12 px-2 md:px-4 text-center text-sm font-bold text-[#478547] bg-white ">Branch Name</th>
                        <th class="h-12 px-2 md:px-4 text-center text-sm font-bold  text-[#478547] bg-white ">Branch Address</th>
                        <th class="h-12 px-2 md:px-4 text-sm font-bold text-[#478547] bg-white w-[15%] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="branchesTbody" class="divide-y">
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once views_path("partials/footer"); ?>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-[#16a249]" id="addModalLabel">Add Branch</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addBranchForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Branch Name</label>
            <input type="text" class="form-control" id="addName" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Branch Address</label>
            <input type="text" class="form-control" id="addAddress" name="address">
          </div>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
          <button type="submit" id="addBranchSubmit" class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">Add Branch</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <h1 class="modal-title fs-5 text-[#16a249]" id="editModalLabel">Edit Branch</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editBranchForm">
        <input type="hidden" id="editId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Branch Name</label>
            <input type="text" class="form-control" id="editName" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Branch Address</label>
            <input type="text" class="form-control" id="editAddress" name="address">
          </div>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
          <button type="submit" id="editBranchSubmit" class="px-6 py-2 btn btn-success transition-colors duration-200 font-semibold">Update Branch</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<script>
// Build absolute API URL; strip trailing '/public' if present
(function(){
    try {
        var scriptName = '<?= addslashes($_SERVER['SCRIPT_NAME'] ?? '') ?>';
        var baseDir = scriptName.substring(0, scriptName.lastIndexOf('/'));
        if (/\/public$/.test(baseDir)) { baseDir = baseDir.replace(/\/public$/, ''); }
        window.BRANCHES_API_URL = (window.location.origin || '') + baseDir + '/app/api/branches-api.php';
    } catch (_) {
        window.BRANCHES_API_URL = (window.location.origin || '') + '/app/api/branches-api.php';
    }
})();
function fetchBranches() {
    fetch(window.BRANCHES_API_URL)
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
            const tbody = document.getElementById('branchesTbody');
            tbody.innerHTML = '';
            res.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.classList.add('fade-in-slide', 'border-b-0', 'hover:bg-[#f2f8f2]', 'even:bg-[#cde4cd]');
                tr.innerHTML = `
                    <td class="px-3 md:px-6 text-center py-2">${row.id}</td>
                    <td class="px-2 md:px-6 py-2 text-center">${row.name ? row.name.replace(/</g,'&lt;') : ''}</td>
                    <td class="px-2 md:px-6 text-center py-2">${row.address ? row.address.replace(/</g,'&lt;') : ''}</td>
                    <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap">
                        <button class="edit-btn inline-flex h-8 w-8 items-center justify-center rounded-md transition duration-150 ease-in-out hover:bg-[#478547] hover:text-white transform hover:scale-105"
                         data-action="edit" data-id="${row.id}"><i class="bi bi-pencil-square"></i></button>
                        <button class="inline-flex h-8 w-8 items-center justify-center rounded-md transition duration-150 ease-in-out hover:bg-[#b91c1c] hover:text-white transform hover:scale-105 ml-1"
                         data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                    </td>`;
                tbody.appendChild(tr);
            });
        }
      })
      .catch(() => {});
}

function promptBranch(initial = {}) {
    return Swal.fire({
        title: initial.id ? 'Edit Branch' : 'Add Branch',
        html: `
            <div class="text-left">
                <label class="block text-sm font-semibold mb-1">Name</label>
                <input id="bName" class="swal2-input" style="width:100%" value="${(initial.name||'').replace(/"/g,'&quot;')}">
                <label class="block text-sm font-semibold mt-2 mb-1">Address</label>
                <input id="bAddress" class="swal2-input" style="width:100%" value="${(initial.address||'').replace(/"/g,'&quot;')}">
            </div>`,
        focusConfirm: false,
        showCancelButton: true,
        preConfirm: () => {
            const name = document.getElementById('bName').value.trim();
            const address = document.getElementById('bAddress').value.trim();
            if (!name) {
                Swal.showValidationMessage('Name is required');
                return false;
            }
            return { name, address };
        }
    });
}

// Handle Add Branch form submit (Bootstrap modal)
document.getElementById('addBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('addBranchSubmit');
    submitBtn.disabled = true;
    const fd = new FormData(this);
    fetch(window.BRANCHES_API_URL, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          Swal.fire({ icon: 'success', title: 'Added', timer: 1200, showConfirmButton: false, toast: true, position: 'top-end' });
          // Hide modal and reset form (robust across environments)
          (function(){
            const el = document.getElementById('addBranchModal');
            try {
              if (window.bootstrap && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                inst.hide();
                return;
              }
            } catch (_) {}
            // Fallback: click a dismiss button or force-hide
            const dismissBtn = el?.querySelector('[data-bs-dismiss="modal"]');
            if (dismissBtn) { try { dismissBtn.click(); return; } catch (_) {} }
            if (el && el.classList.contains('show')) {
              el.classList.remove('show'); el.style.display = 'none';
              document.body.classList.remove('modal-open');
              document.querySelector('.modal-backdrop')?.remove();
            }
          })();
          document.getElementById('addBranchForm').reset();
          fetchBranches();
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' });
        }
      })
      .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed' }))
      .finally(() => { submitBtn.disabled = false; });
});

document.getElementById('branchesTbody').addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (action === 'delete') {
        Swal.fire({
            title: 'Delete branch?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(r => {
            if (r.isConfirmed) {
                const body = new URLSearchParams();
                body.set('id', id);
                fetch(window.BRANCHES_API_URL + '?method=DELETE', { method: 'POST', body })
                  .then(r => r.json())
                  .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false, toast: true, position: 'top-end' });
                        fetchBranches();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' });
                    }
                  });
            }
        });
    } else if (action === 'edit') {
        // Populate edit modal and show
        const tr = btn.closest('tr');
        const name = tr.children[1].textContent.trim();
        const address = tr.children[2].textContent.trim();
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editAddress').value = address;
        try {
          const modal = bootstrap.Modal.getInstance(document.getElementById('editBranchModal')) || new bootstrap.Modal(document.getElementById('editBranchModal'));
          modal.show();
        } catch (_) {
          document.getElementById('editBranchModal')?.classList.add('show');
          document.getElementById('editBranchModal').style.display = 'block';
          document.body.classList.add('modal-open');
        }
    }
});

document.addEventListener('DOMContentLoaded', fetchBranches);

// Handle Edit Branch submit
document.getElementById('editBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('editBranchSubmit');
    submitBtn.disabled = true;
    const params = new URLSearchParams();
    params.set('id', document.getElementById('editId').value);
    params.set('name', document.getElementById('editName').value.trim());
    params.set('address', document.getElementById('editAddress').value.trim());
    fetch(window.BRANCHES_API_URL + '?method=PATCH', { method: 'POST', body: params })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          Swal.fire({ icon: 'success', title: 'Updated', timer: 1200, showConfirmButton: false, toast: true, position: 'top-end' });
          // Close modal
          (function(){
            const el = document.getElementById('editBranchModal');
            try {
              if (window.bootstrap && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                inst.hide();
                return;
              }
            } catch (_) {}
            const dismissBtn = el?.querySelector('[data-bs-dismiss="modal"]');
            if (dismissBtn) { try { dismissBtn.click(); return; } catch (_) {} }
            if (el && el.classList.contains('show')) {
              el.classList.remove('show'); el.style.display = 'none';
              document.body.classList.remove('modal-open');
              document.querySelector('.modal-backdrop')?.remove();
            }
          })();
          // Also reset the edit form after closing
          try { document.getElementById('editBranchForm').reset(); } catch(_) {}
          fetchBranches();
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' });
        }
      })
      .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed' }))
      .finally(() => { submitBtn.disabled = false; });
});

// Reset forms when modals are closed (handles manual closes too)
(function(){
    try {
        var addModalEl = document.getElementById('addBranchModal');
        var editModalEl = document.getElementById('editBranchModal');
        if (addModalEl) {
            addModalEl.addEventListener('hidden.bs.modal', function(){
                try { document.getElementById('addBranchForm').reset(); } catch(_) {}
            });
        }
        if (editModalEl) {
            editModalEl.addEventListener('hidden.bs.modal', function(){
                try { document.getElementById('editBranchForm').reset(); } catch(_) {}
            });
        }
    } catch(_) {}
})();
</script>

