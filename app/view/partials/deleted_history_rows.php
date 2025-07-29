<?php $allRecords = $allRecords ?? []; ?>
<?php if (count($allRecords) > 0): ?>
    <?php $count = 1; ?>
    <?php foreach ($allRecords as $record): ?>
        <tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
            <td class="px-3 py-2 align-middle"><?= $count++ ?></td>
            <td class="px-3 py-2 align-middle">
                <?php if ($record['record_type'] === 'employee'): ?>
                    <?php if (!empty($record['photo_path'])): ?>
                        <img src="../public/<?= htmlspecialchars($record['photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                    <?php else: ?>
                        <?php
                            $defaultImage = ($record['sex'] === 'Female')
                                ? '../public/assets/image/default_women.png'
                                : '../public/assets/image/default_men.png';
                        ?>
                        <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (!empty($record['m_photo_path'])): ?>
                        <img src="../public/<?= htmlspecialchars($record['m_photo_path']) ?>" alt="Photo" class="h-10 w-10 rounded-full object-cover">
                    <?php else: ?>
                        <?php
                            $defaultImage = ($record['m_sex'] === 'Female')
                                ? '../public/assets/image/default_women.png'
                                : '../public/assets/image/default_men.png';
                        ?>
                        <img src="<?= $defaultImage ?>" alt="Default Photo" class="h-10 w-10 rounded-full object-cover">
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td class="px-3 py-2 align-middle">
                <?php if ($record['record_type'] === 'employee'): ?>
                    <?= htmlspecialchars(
                        ucwords(strtolower($record['first_name'])) . ' ' .
                        (!empty($record['middle_name']) ? strtoupper(substr($record['middle_name'], 0, 1)) . '. ' : '') .
                        ucwords(strtolower($record['last_name']))
                    ) ?>
                <?php else: ?>
                    <?= htmlspecialchars(
                        ucwords(strtolower($record['m_first_name'])) . ' ' .
                        (!empty($record['m_middle_name']) ? strtoupper(substr($record['m_middle_name'], 0, 1)) . '. ' : '') .
                        ucwords(strtolower($record['m_last_name']))
                    ) ?>
                <?php endif; ?>
            </td>
            <td class="px-3 py-2 align-middle">
                <?= $record['record_type'] === 'employee' ? htmlspecialchars($record['employee_no']) : htmlspecialchars($record['m_employee_id']) ?>
            </td>
            <td class="px-3 py-2 align-middle">
                <?= $record['record_type'] === 'employee' ? htmlspecialchars($record['rfid_number']) : htmlspecialchars($record['m_rfid_number']) ?>
            </td>
            <td class="px-3 py-2 align-middle">
                <?php 
                    $position = $record['record_type'] === 'employee' ? $record['position'] : $record['m_position'];
                    $bgColor = '';
                    switch ($position) {
                        case 'Manager':
                            $bgColor = 'bg-green-600 text-white';
                            break;
                        case 'Human Resources':
                            $bgColor = 'bg-blue-600 text-white';
                            break;
                        case 'Staff':
                            $bgColor = 'bg-yellow-600 text-white';
                            break;
                        case 'Driver':
                            $bgColor = 'bg-red-600 text-white';
                            break;
                        default:
                            $bgColor = 'bg-gray-500 text-white';
                            break;
                    }
                ?>
                <div class="inline-flex items-center rounded-full border border-transparent <?= $bgColor ?> px-2.5 py-0.5 text-xs font-semibold">
                    <?= htmlspecialchars($position) ?>
                </div>
            </td>
            <td class="px-3 py-2 align-middle text-center">
                <div class="flex justify-center items-center gap-2">
                    <!-- View Details Button -->
                    <button 
                        type="button"
                        class="view-deleted-record inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-blue-500 hover:text-white" 
                        title="View Details"
                        data-id="<?= $record['id'] ?>"
                        data-type="<?= $record['record_type'] ?>"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                    <!-- Restore Button -->
                    <button 
                        type="button"
                        class="restore-button inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white"
                        title="Restore"
                        data-id="<?= $record['id'] ?>"
                        data-type="<?= $record['record_type'] ?>"
                    >
                        <i class="fas fa-trash-restore"></i>
                    </button>

                    <!-- Delete Permanently Button -->
                    <button 
                        type="button"
                        class="delete-btn inline-flex h-8 w-8 md:h-8 md:w-8 items-center justify-center rounded-md font-medium px-2 py-1 transition duration-100 transform hover:scale-105 hover:bg-red-600 hover:text-white"
                        title="Delete Permanently"
                        data-id="<?= $record['id'] ?>"
                        data-type="<?= $record['record_type'] ?>"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="px-4 py-6 text-center text-secondary fst-italic bg-light fade-in-slide">
            <i class="bi bi-trash fs-4 me-2"></i> No deleted records found.
        </td>
    </tr>
<?php endif; ?>
