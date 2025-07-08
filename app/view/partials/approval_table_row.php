
<!-- qpprovals_request refresh table -->
<tr class="border-b transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
    <td class="px-3 py-2 align-middle"><?= $index++ ?></td>
    <td class="px-3 py-2 align-middle">
        <?php if (!empty($emp['photo_path'])): ?>
            <img src="<?= htmlspecialchars($emp['photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
        <?php else: ?>
            <?php
                $defaultImage = ($emp['sex'] === 'Female')
                    ? '../public/assets/image/default_women.png'
                    : '../public/assets/image/default_men.png';
            ?>
            <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
        <?php endif; ?>
    </td>
    <td class="px-3 py-2 align-middle">
        <?= htmlspecialchars(
            ucwords(strtolower($emp['first_name'])) . ' ' .
            (!empty($emp['middle_name']) ? strtoupper(substr($emp['middle_name'], 0, 1)) . '. ' : '') .
            ucwords(strtolower($emp['last_name']))
        ) ?>
    </td>
    <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['employee_no']) ?></td>
    <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['rfid_number']) ?></td>
    <td class="px-3 py-2 align-middle"><?= htmlspecialchars($emp['position']) ?></td>
    <td class="px-3 py-2 align-middle text-center">
        <?php
            if ($emp['approved_by_manager'] == 1) {
                $badgeClass = 'bg-green-100 text-green-800';
                $badgeText = 'Approved';
            } elseif ($emp['approved_by_manager'] == -1) {
                $badgeClass = 'bg-red-100 text-red-800';
                $badgeText = 'Rejected';
            } else {
                $badgeClass = 'bg-yellow-100 text-yellow-800';
                $badgeText = 'Pending';
            }
        ?>
        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $badgeClass ?>">
            <?= $badgeText ?>
        </span>
    </td>
    <td class="px-3 py-2 align-middle text-center">
        <?php if ($emp['approved_by_manager'] === -1): ?>
            <button 
                type="button" 
                class="inline-flex h-8 w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white resend-approval-btn" 
                data-id="<?= htmlspecialchars($emp['id']) ?>" 
                title="Resend Approval"
            >
                <i class="bi bi-send"></i>
            </button>
        <?php endif; ?>

        <button 
            type="button" 
            class="inline-flex h-8 w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white approval-view-btn" 
            data-id="<?= htmlspecialchars($emp['id']) ?>"
            data-bs-toggle="modal" 
            data-bs-target="#approvalViewModal" 
            title="View"
        >
            <i class="bi bi-eye"></i>
        </button>

        <?php if ($emp['approved_by_manager'] !== 1): ?>
            <button 
                type="button" 
                class="inline-flex h-8 w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white delete-btn" 
                data-id="<?= htmlspecialchars($emp['id']) ?>" 
                title="Delete"
            >
                <i class="bi bi-trash"></i>
            </button>
        <?php else: ?>
            <span class="text-gray-500 italic">No Action</span>
        <?php endif; ?>
    </td>
</tr>
