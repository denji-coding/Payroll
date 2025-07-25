<?php $deletedEmployees = $deletedEmployees ?? []; ?>
<?php if (count($deletedEmployees) > 0): ?>
    <?php $count = 1; ?>
    <?php foreach ($deletedEmployees as $deletedEmployee): ?>
        <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
            <td class="px-3 py-2 align-middle"><?= $count++ ?></td>
            <td class="px-3 py-2 align-middle">
                <?php if (!empty($deletedEmployee['photo_path'])): ?>
                    <img src="<?= htmlspecialchars($deletedEmployee['photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                <?php else: ?>
                    <?php
                        $defaultImage = ($deletedEmployee['sex'] === 'Female')
                            ? '../public/assets/image/default_women.png'
                            : '../public/assets/image/default_men.png';
                    ?>
                    <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                <?php endif; ?>
            </td>
            <td class="px-3 py-2 align-middle">
                <?= htmlspecialchars(
                    ucwords(strtolower($deletedEmployee['first_name'])) . ' ' .
                    (!empty($deletedEmployee['middle_name']) ? strtoupper(substr($deletedEmployee['middle_name'], 0, 1)) . '. ' : '') .
                    ucwords(strtolower($deletedEmployee['last_name']))
                ) ?>
            </td>

            <td class="px-3 py-2 align-middle"><?= htmlspecialchars($deletedEmployee['employee_no']) ?></td>
            <td class="px-3 py-2 align-middle"><?= htmlspecialchars($deletedEmployee['rfid_number']) ?></td>
            <td class="px-3 py-2 align-middle"><?= htmlspecialchars($deletedEmployee['position']) ?></td>
            <td class="px-3 py-2 align-middle text-center">
                <div class="flex justify-center items-center gap-2">
                    <!-- View Details Button -->
                    <button 
                        type="button"
                        class="view-deleted-employee inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white" 
                        title="View Details"
                        data-id="<?= $deletedEmployee['id'] ?>"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                    <!-- Restore Button -->
                    <button 
                        type="button"
                        class="restore-button inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                        title="Restore"
                        data-id="<?= $deletedEmployee['id'] ?>"
                    >
                        <i class="fas fa-trash-restore"></i>
                    </button>

                    <!-- Delete Permanently Button -->
                    <button 
                        type="button"
                        class="delete-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white"
                        title="Delete Permanently"
                        data-id="<?= $deletedEmployee['id'] ?>"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="8" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-person-x fs-4 me-2"></i> No deleted employee records found.
        </td>
    </tr>
<?php endif; ?>
