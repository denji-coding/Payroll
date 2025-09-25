<?php
$title = "Admin Profile Settings";
require_once views_path("partials/header");
require_once views_path("partials/sidebar");
require_once views_path("partials/nav");

$adminName = $_SESSION['USERNAME'] ?? 'Admin User';
$adminEmail = $_SESSION['SESSION_EMAIL'] ?? '';
$adminPhone = $_SESSION['phone'] ?? '';
$adminRole = $_SESSION['ROLE'] ?? 'Administrator';
$photoPath = $_SESSION['photo_path'] ?? '';
// If stored path is already relative like 'upload/xyz.jpg', use it from public root
if ($photoPath !== '') {
  $isAbsoluteUrl = preg_match('/^https?:\\/\\//i', $photoPath);
  $isFromPublic = preg_match('/^(upload\\/|assets\\/|public\\/)/i', $photoPath);
  if ($isAbsoluteUrl) {
    $avatar = $photoPath;
  } elseif ($isFromPublic) {
    // build relative to current view location
    $avatar = (strpos($photoPath, 'public/') === 0) ? '../' . $photoPath : '../public/' . $photoPath;
  } else {
    // fallback assume it's an upload file name or path under upload/
    $avatar = '../public/upload/' . basename($photoPath);
  }
} else {
  $avatar = 'public/assets/image/default_user_image.svg';
}
?>

<main class="flex-1 h-[calc(100vh-3rem)] p-4 md:p-6 ml-[255px] mt-12 bg-[#f8fbf8]">
  <div class="max-w-5xl mx-auto">
    <?php $pf = $_SESSION['profile_flash'] ?? null; ?>
    <div class="flex items-center justify-between mb-6">
      <div>
        <span class="text-2xl font-bold tracking-tight text-[#133913]">Profile Settings</span>
        <p class="text-[#478547]">Manage your account information and avatar.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Header Card -->
      <div class="lg:col-span-1">
        <div class="rounded-lg border-2 border-green-200 bg-white shadow-sm p-6 transition-transform duration-200 hover:-translate-y-[2px]">
          <div class="flex flex-col items-center text-center gap-3">
            <img id="avatarPreview" src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="w-24 h-24 rounded-full object-cover border border-gray-200" onerror="this.onerror=null;this.src='public/assets/image/default_user_image.svg';" />
            <div>
              <div class="text-lg font-semibold text-[#133913]"><?= htmlspecialchars(ucwords(strtolower($adminName))) ?></div>
              <div class="text-sm text-gray-500"><?= htmlspecialchars($adminRole) ?></div>
            </div>
            <div class="mt-2">
              <label for="profile_picture" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md border border-[#cde4cd] bg-[#f8fbf8] text-sm font-medium text-[#133913] hover:bg-[#eaf7ea] transition-colors cursor-pointer">
                <i class="bi bi-upload"></i> Change Avatar
              </label>
              <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" form="adminProfileForm" />
              <p class="text-xs text-gray-500 mt-2">PNG, JPG up to 2MB.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Form Card -->
      <div class="lg:col-span-2">
        <form id="adminProfileForm" method="post" action="../app/api/admin_profile-api.php" enctype="multipart/form-data" class="rounded-lg border-2 border-green-200 bg-white shadow-sm p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="full_name" class="block text-sm font-medium text-[#133913] mb-1">Full Name</label>
              <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($adminName) ?>" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" required />
            </div>
            <div>
              <label for="email" class="block text-sm font-medium text-[#133913] mb-1">Email Address</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($adminEmail) ?>" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" required />
            </div>
            <div>
              <label for="phone" class="block text-sm font-medium text-[#133913] mb-1">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($adminPhone) ?>" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="window.history.back()" class="inline-flex items-center justify-center gap-2 rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium text-[#133913] hover:bg-[#eaf7ea] transition-colors">
              <i class="bi bi-x"></i> Cancel
            </button>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#16a249] px-4 py-2 text-sm font-medium text-white hover:bg-[#158a40] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition-transform hover:-translate-y-[1px] active:translate-y-0">
              <i class="bi bi-check-circle"></i> Save Changes
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password Card -->
      <div class="lg:col-span-3 mb-4">
        <form id="adminPasswordForm" method="post" action="../app/api/admin_profile-api.php?action=change_password" class="rounded-lg border-2 border-green-200 bg-white shadow-sm p-6 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-[#133913]">Change Password</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="current_password" class="block text-sm font-medium text-[#133913] mb-1">Current Password</label>
              <input type="password" id="current_password" name="current_password" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" placeholder="Enter current password" required />
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-[#133913] mb-1">New Password</label>
              <input type="password" id="password" name="password" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" placeholder="Enter new password" required />
            </div>
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-[#133913] mb-1">Confirm New Password</label>
              <input type="password" id="confirm_password" name="confirm_password" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-[#133913] placeholder:text-gray-400 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition" placeholder="Confirm new password" required />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="reset" class="inline-flex items-center justify-center gap-2 rounded-md border border-[#cde4cd] bg-[#f8fbf8] px-4 py-2 text-sm font-medium text-[#133913] hover:bg-[#eaf7ea] transition-colors">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#16a249] px-4 py-2 text-sm font-medium text-white hover:bg-[#158a40] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#16a249] focus-visible:ring-offset-2 transition-transform hover:-translate-y-[1px] active:translate-y-0">
              <i class="bi bi-shield-lock"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
// Show SweetAlert for profile flash
(function () {
  try {
    const flash = <?php echo json_encode($pf ?? null); ?>;
    <?php if (!empty($_SESSION['profile_flash'])) { unset($_SESSION['profile_flash']); } ?>
    if (flash && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: flash.ok ? 'success' : 'error',
        title: flash.ok ? 'Saved' : 'Error',
        text: flash.message || '',
        timer: 1600,
        showConfirmButton: false
      });
    }
  } catch (e) { /* no-op */ }
})();

document.getElementById('profile_picture')?.addEventListener('change', function (e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function (ev) {
    const img = document.getElementById('avatarPreview');
    if (img) img.src = ev.target.result;
  };
  reader.readAsDataURL(file);
});

document.getElementById('adminPasswordForm')?.addEventListener('submit', function (e) {
  const pwd = document.getElementById('password')?.value.trim() || '';
  const cpwd = document.getElementById('confirm_password')?.value.trim() || '';
  if (pwd !== cpwd) {
    e.preventDefault();
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Passwords do not match', timer: 1500, showConfirmButton: false });
    } else {
      alert('Passwords do not match');
    }
  }
});

// AJAX submit for profile info
document.getElementById('adminProfileForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.currentTarget;
  const fd = new FormData(form);
  fd.append('ajax', '1');
  try {
    const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    if (data.ok) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'success', title: 'Saved', text: data.message || 'Profile updated', timer: 1400, showConfirmButton: false });
      }
      // Update displayed name, email and avatar if returned
      if (data.data) {
        const nameEl = document.querySelector('#adminProfileForm #full_name');
        if (nameEl && data.data.full_name) nameEl.value = data.data.full_name;
        const emailEl = document.querySelector('#adminProfileForm #email');
        if (emailEl && data.data.email) emailEl.value = data.data.email;
        const phoneEl = document.querySelector('#adminProfileForm #phone');
        if (phoneEl && data.data.phone) phoneEl.value = data.data.phone;
        if (data.data.photo_path) {
          const img = document.getElementById('avatarPreview');
          if (img) {
            const path = data.data.photo_path.startsWith('public/') ? ('../' + data.data.photo_path) : ('../public/' + data.data.photo_path);
            img.src = path;
          }
        }
      }
    } else {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update profile' });
      }
    }
  } catch (err) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed. Please try again.' });
    }
  }
});

// AJAX submit for password change
document.getElementById('adminPasswordForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.currentTarget;
  const fd = new FormData(form);
  fd.append('ajax', '1');
  try {
    const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    if (data.ok) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'success', title: 'Updated', text: data.message || 'Password updated', timer: 1400, showConfirmButton: false });
      }
      form.reset();
    } else {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update password' });
      }
    }
  } catch (err) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed. Please try again.' });
    }
  }
});
</script>

<?php require_once views_path("partials/footer"); ?>


