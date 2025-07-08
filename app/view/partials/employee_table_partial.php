<style>
    .employee-row {
  transition: opacity 0.5s ease;
  opacity: 1;
}
</style>

<tr class="employee-row transition-opacity duration-500 opacity-100" data-id="<?= $employee['id'] ?>">

    <td class="p-3 align-middle font-medium"><?= $count++ ?></td>
    <td class="p-3 align-middle font-medium">
        <div class="flex items-center space-x-2">
            <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                <?php if (!empty($employee['photo_path'])): ?>
                    <img class="aspect-square h-full w-full" src="<?= htmlspecialchars($employee['photo_path']) ?>" alt="Employee Photo">
                <?php else: ?>
                    <?php
                        $defaultImage = ($employee['sex'] === 'Female') 
                            ? '../public/assets/image/default_women.png' 
                            : '../public/assets/image/default_men.png';
                    ?>
                    <img class="aspect-square h-full w-full" src="<?= $defaultImage ?>" alt="Default Photo">
                <?php endif; ?>
            </span>
        </div>
    </td>
    <td class="p-3 align-middle font-medium">
        <div class="flex items-center space-x-2">
            <span>
                <?= ucwords(strtolower($employee['first_name'])) ?>
                <?= !empty($employee['middle_name']) ? strtoupper(substr($employee['middle_name'], 0, 1)) . '.' : '' ?>
                <?= ucwords(strtolower($employee['last_name'])) ?>
            </span>
        </div>
    </td>
    <td class="p-3 align-middle"><?= htmlspecialchars($employee['employee_no']) ?></td>
    <td class="p-3 align-middle"><?= htmlspecialchars($employee['rfid_number']) ?></td>

    <?php
        $position = $employee['position'] ?? '';
        switch ($position) {
            case 'Manager':
                $bgColor = 'bg-green-600 text-white'; break;
            case 'Human Resources':
                $bgColor = 'bg-blue-600 text-white'; break;
            case 'Staff':
                $bgColor = 'bg-yellow-600 text-white'; break;
            case 'Driver':
                $bgColor = 'bg-red-600 text-white'; break;
            default:
                $bgColor = 'bg-gray-500 text-white'; break;
        }
    ?>

    <td class="p-3 align-middle text-center">
        <div class="inline-flex items-center rounded-full border border-transparent <?= $bgColor ?> px-2.5 py-0.5 text-xs font-semibold">
            <?= htmlspecialchars($employee['position']) ?>
        </div>
    </td>
    <td class="p-3 align-middle text-right">
        <div class="flex gap-2">
            <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md font-medium px-2 py-1 hover:scale-105 hover:bg-[#478547] hover:text-white"
                    data-bs-toggle="modal"
                    data-bs-target="#viewEmployeeModal"
                    onclick="viewEmployee('<?= htmlspecialchars($employee['employee_no']) ?>')">
                <i class="bi bi-eye text-lg"></i>
            </button>

            <button type="button"
                    class="deleteBtn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium px-2 py-1 hover:scale-105 hover:bg-red-600 hover:text-white"
                    data-id="<?= $employee['id'] ?>"
                    title="Delete">
                <i class="bi bi-trash text-lg"></i>
            </button>

            <div class="dropdown relative inline-block">
                <button class="dropdown-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium hover:scale-105 hover:bg-[#478547] hover:text-white"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="bi bi-person-gear text-lg"></i>
                </button>
                <ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <li>
                        <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewAttendanceModal', <?= $employee['id'] ?>)">
                            <i class="bi bi-calendar-check h-4 w-4"></i>
                            <span>View Attendance</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal('viewSlipsModal', <?= $employee['id'] ?>)">
                            <i class="bi bi-receipt h-4 w-4"></i>
                            <span>View Slips</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </td>
</tr>
