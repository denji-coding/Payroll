<?php
$title = "Daily Time Record";
require_once views_path("partials/header");
require_once "../app/core/database.php";

// Add Bootstrap Icons CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

$db = (new Database)->getConnection();
$currentPage = $_GET['payroll'] ?? 'user_dashboard';

// Resolve authenticated user context (employee, manager with access, or HR with access)
$userContext = null;

if (!empty($_SESSION['employee_id']) || !empty($_SESSION['employee_no'])) {
    $employeeId = $_SESSION['employee_id'] ?? null;

    if (!$employeeId && !empty($_SESSION['employee_no'])) {
        $stmtEmployeeId = $db->prepare("SELECT id FROM employees WHERE employee_no = :emp_no LIMIT 1");
        $stmtEmployeeId->execute([':emp_no' => $_SESSION['employee_no']]);
        $employeeId = $stmtEmployeeId->fetchColumn() ?: null;
    }

    if ($employeeId) {
        $userContext = [
            'type' => 'employee',
            'id' => (int)$employeeId,
            'attendance_column' => 'employee_id',
            'schedule_table' => 'employee_schedules',
            'schedule_fk' => 'employee_id'
        ];
    }
}

if (!$userContext && !empty($_SESSION['manager_id']) && !empty($_SESSION['can_access_employee_portal'])) {
    $userContext = [
        'type' => 'manager',
        'id' => (int)$_SESSION['manager_id'],
        'attendance_column' => 'manager_id',
        'schedule_table' => 'manager_schedules',
        'schedule_fk' => 'manager_id'
    ];
}

if (!$userContext && !empty($_SESSION['SESSION_USER_ID']) && !empty($_SESSION['can_access_employee_portal'])) {
    $userContext = [
        'type' => 'hr',
        'id' => (int)$_SESSION['SESSION_USER_ID'],
        'attendance_column' => 'hr_id',
        'schedule_table' => 'hr_schedules',
        'schedule_fk' => 'hr_id'
    ];
}

if (!$userContext) {
    if (isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    http_response_code(403);
    require_once '../app/Error/unauthorized.php';
    exit;
}

echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

// Month and Year arrays
$months = [
    '01' => 'January', '02' => 'February', '03' => 'March',
    '04' => 'April', '05' => 'May', '06' => 'June',
    '07' => 'July', '08' => 'August', '09' => 'September',
    '10' => 'October', '11' => 'November', '12' => 'December',
];

$currentYear = date('Y');
$years = array_reverse(range($currentYear - 5, $currentYear));

$selectedMonth = $_GET['month'] ?? null;
$selectedYear = $_GET['year'] ?? $currentYear;

// === Fetch DTR Records ===
$filteredRecords = [];

if ($selectedMonth && $selectedYear) {
    $attendanceColumn = $userContext['attendance_column'];
    $query = "SELECT date, morning_in, morning_out, afternoon_in, afternoon_out, status 
              FROM attendance 
              WHERE {$attendanceColumn} = :user_id 
                AND MONTH(date) = :month 
                AND YEAR(date) = :year 
              ORDER BY date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $userContext['id'],
        ':month' => $selectedMonth,
        ':year' => $selectedYear
    ]);
    $filteredRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === Get User Name ===
$nameQuery = null;
switch ($userContext['type']) {
    case 'manager':
        $nameQuery = "SELECT m_first_name AS first_name, m_middle_name AS middle_name, m_last_name AS last_name FROM managers WHERE id = :id LIMIT 1";
        break;
    case 'hr':
        $nameQuery = "SELECT hr_first_name AS first_name, hr_middle_name AS middle_name, hr_last_name AS last_name FROM admins WHERE id = :id LIMIT 1";
        break;
    default:
        $nameQuery = "SELECT first_name, middle_name, last_name FROM employees WHERE id = :id LIMIT 1";
        break;
}

$userName = "Unknown User";
if ($nameQuery) {
    $stmtName = $db->prepare($nameQuery);
    $stmtName->execute([':id' => $userContext['id']]);
    $nameRecord = $stmtName->fetch(PDO::FETCH_ASSOC);

    if ($nameRecord) {
        $middle = trim($nameRecord['middle_name'] ?? '');
        $middleInitial = $middle ? strtoupper(substr($middle, 0, 1)) . '. ' : '';
        $userName = trim(($nameRecord['first_name'] ?? '') . ' ' . $middleInitial . ($nameRecord['last_name'] ?? '')) ?: "Unknown User";
    }
}

// === Get Employee Creation Date ===
$employeeCreatedAt = null;
if ($userContext['type'] === 'employee') {
    $createdStmt = $db->prepare("SELECT created_at FROM employees WHERE id = :id LIMIT 1");
    $createdStmt->execute([':id' => $userContext['id']]);
    $createdRecord = $createdStmt->fetch(PDO::FETCH_ASSOC);
    $employeeCreatedAt = $createdRecord ? $createdRecord['created_at'] : null;
} elseif ($userContext['type'] === 'manager') {
    $createdStmt = $db->prepare("SELECT created_at FROM managers WHERE id = :id LIMIT 1");
    $createdStmt->execute([':id' => $userContext['id']]);
    $createdRecord = $createdStmt->fetch(PDO::FETCH_ASSOC);
    $employeeCreatedAt = $createdRecord ? $createdRecord['created_at'] : null;
} elseif ($userContext['type'] === 'hr') {
    $createdStmt = $db->prepare("SELECT hr_created_at as created_at FROM admins WHERE id = :id LIMIT 1");
    $createdStmt->execute([':id' => $userContext['id']]);
    $createdRecord = $createdStmt->fetch(PDO::FETCH_ASSOC);
    $employeeCreatedAt = $createdRecord ? $createdRecord['created_at'] : null;
}

// === Get Employee Schedule ===
$officialMorningIn = '-';
$officialMorningOut = '-';
$officialAfternoonIn = '-';
$officialAfternoonOut = '-';
$scheduleTimes = [
    'morning_in' => null,
    'morning_out' => null,
    'afternoon_in' => null,
    'afternoon_out' => null,
];

$scheduleStmt = $db->prepare(
    "SELECT schedule_id FROM {$userContext['schedule_table']} WHERE {$userContext['schedule_fk']} = :id LIMIT 1"
);
$scheduleStmt->execute([':id' => $userContext['id']]);
$assignedSchedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

if ($assignedSchedule) {
    $scheduleId = $assignedSchedule['schedule_id'];

    $stmt2 = $db->prepare("SELECT sched_morning_in, sched_morning_out, sched_afternoon_in, sched_afternoon_out FROM schedules WHERE id = :schedule_id");
    $stmt2->execute(['schedule_id' => $scheduleId]);
    $schedule = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($schedule) {
        $scheduleTimes = [
            'morning_in' => $schedule['sched_morning_in'] ?? null,
            'morning_out' => $schedule['sched_morning_out'] ?? null,
            'afternoon_in' => $schedule['sched_afternoon_in'] ?? null,
            'afternoon_out' => $schedule['sched_afternoon_out'] ?? null,
        ];
        $officialMorningIn = isset($schedule['sched_morning_in']) ? date("g:i A", strtotime($schedule['sched_morning_in'])) : '-';
        $officialMorningOut = isset($schedule['sched_morning_out']) ? date("g:i A", strtotime($schedule['sched_morning_out'])) : '-';
        $officialAfternoonIn = isset($schedule['sched_afternoon_in']) ? date("g:i A", strtotime($schedule['sched_afternoon_in'])) : '-';   
        $officialAfternoonOut = isset($schedule['sched_afternoon_out']) ? date("g:i A", strtotime($schedule['sched_afternoon_out'])) : '-';
    }
}

$graceMinutes = 5;
$graceSeconds = $graceMinutes * 60;
$timeIsValid = static function ($time) {
    return !empty($time) && $time !== '01:00:00';
};
?>

<div class="flex min-h-screen overflow-hidden bg-gray-100">    
    <main id="mainContent" class="flex-1 p-3 sm:p-4 md:p-6 bg-gray-100 transition-margin duration-300 ease-in-out md:ml-64">
        <?php require_once views_path("partials/user_sidebar"); ?>

        <div class="mb-4 md:mb-6 pt-2 sm:pt-4">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-gray-800">Daily Time Record</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1 md:mt-2">Here's a quick overview of your daily logs.</p>
        </div>

        <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
            <!-- Filter Form -->
            <div class="flex items-center justify-between mb-6 gap-4">
                <form method="GET" class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <input type="hidden" name="payroll" value="user_dtr" />
                    <input type="hidden" name="month" id="month" value="<?= $selectedMonth ? $selectedMonth : '' ?>">
                    <input type="hidden" name="year" id="year" value="<?= $selectedYear ? $selectedYear : '' ?>">
                    
                    <label for="monthYearPicker" class="font-medium text-gray-700">Select Month & Year:</label>
                    <div class="relative" style="position: relative;">
                        <input type="text" id="monthYearPicker" class="border rounded p-1 flatpickr-month-year-input w-100" 
                               value="<?= ($selectedMonth && $selectedYear) ? date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) : '' ?>" 
                               placeholder="Select Month & Year" 
                               readonly 
                               required
                               style="width: 180px; cursor: pointer; background-color: white;">
                    </div>

                    <button id="filterBtn" type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition-colors ">
                        <span class="text-sm">Filter</span>
                        <i class="bi bi-funnel fs-7 ml-2"></i>
                    </button>
                </form>

                <?php if ($selectedMonth && $selectedYear): ?>
                    <div class="flex gap-2">
                        <?php if (count($filteredRecords) > 0): ?>
                            <button onclick="downloadDTR()" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition-colors duration-200">
                                <i class="bi bi-download mr-2"></i><span class="text-sm">Download DTR</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" onclick="clearFilter()" class="bg-gray-600 text-white px-4 py-1 rounded hover:bg-gray-700 transition-colors duration-200">
                            <span class="text-sm">Clear Filter</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($selectedMonth && $selectedYear): ?>
    <?php if (count($filteredRecords) > 0): ?>
        <!-- DTR Table -->
        <div class="overflow-x-auto w-full" style="margin: 0; padding: 0;">
            <div id="dtrSection" class="text-xs leading-tight" style="margin: 0 auto; padding: 5px;">
                <!-- Header Section (Same as Payslip) -->
                <div class="header-section">
                    <div class="header-top">
                        <div class="header-left">
                            <div class="header-left-content">
                                <div class="logo-container">
                                    <img src="../public/assets/image/test_logo_cropted.png" alt="Company Logo" class="logo-image">
                                </div>
                                <div class="company-info">
                                    <div class="company-name">Migrants Venture Corporation</div>
                                    <div class="company-address">Lapu-Lapu St. Tagum City, Davao Del Norte</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="header-divider">
                
                <p class="text-center font-semibold dtr-title" style="margin-top: 10px;">DAILY TIME RECORD</p>
                <p class="text-left mb-2">
                    Name: <strong><span class="underline"><?= ucwords(htmlspecialchars($userName)) ?></span></strong>
                </p>
                <div class="mb-2 text-xs">
                    <p>For the month of: <strong><?= $months[$selectedMonth] ?> <?= $selectedYear ?></strong></p>
                    <p>Official Hours: <?= htmlspecialchars($officialMorningIn) ?> - <?= htmlspecialchars($officialMorningOut) ?> | <?= htmlspecialchars($officialAfternoonIn) ?> - <?= htmlspecialchars($officialAfternoonOut) ?></p>
                </div>

                <table class="table-fixed w-full border border-black text-center text-xs">
                    <thead>
                        <tr>
        <th class="border border-black align-middle" rowspan="2">Day</th>
        <th class="border border-black" colspan="3">Morning</th>
        <th class="border border-black" colspan="3">Afternoon</th>
        <th class="border border-black align-middle" rowspan="2">Hours Worked</th>
        <th class="border border-black align-middle" rowspan="2">Daily Remarks</th>
    </tr>
    <tr>
        <th class="border border-black">AM In</th>
        <th class="border border-black">AM Out</th>
        <th class="border border-black">AM Remark</th>
        <th class="border border-black">PM In</th>
        <th class="border border-black">PM Out</th>
        <th class="border border-black">PM Remark</th>
    </tr>
                    </thead>
                    <tbody>
                        <?php
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$totalHoursWorked = 0;
$currentDate = date('Y-m-d');

for ($day = 1; $day <= $daysInMonth; $day++):
    $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
    $entry = null;
    $weekTimestamp = strtotime($dateStr);
    
    // Validate timestamp
    if ($weekTimestamp === false) {
        $weekTimestamp = mktime(0, 0, 0, $selectedMonth, $day, $selectedYear);
    }
    
    $weekday = (int)date('N', $weekTimestamp); // 1 (Mon) - 7 (Sun)
    $weekdayName = date('l', $weekTimestamp);
    $isWeekend = in_array($weekday, [6, 7], true);

    foreach ($filteredRecords as $rec) {
        if ($rec['date'] === $dateStr) {
            $entry = $rec;
            break;
        }
    }

$morningInRaw = $morningOutRaw = $afternoonInRaw = $afternoonOutRaw = null;
// Default to "--:--" for all time displays when no data
$morningInDisplay = $morningOutDisplay = $afternoonInDisplay = $afternoonOutDisplay = '--:--';

// Skip processing database entries for weekends - they should always show "--:--"
if (!$isWeekend && $entry) {
    $morningInRaw = $timeIsValid($entry['morning_in']) ? $entry['morning_in'] : null;
    $morningOutRaw = $timeIsValid($entry['morning_out']) ? $entry['morning_out'] : null;
    $afternoonInRaw = $timeIsValid($entry['afternoon_in']) ? $entry['afternoon_in'] : null;
    $afternoonOutRaw = $timeIsValid($entry['afternoon_out']) ? $entry['afternoon_out'] : null;

    if ($morningInRaw) {
        $morningInDisplay = date("h:i:s A", strtotime($morningInRaw));
    } else {
        $morningInDisplay = '--:--';
    }
    if ($morningOutRaw) {
        $morningOutDisplay = date("h:i:s A", strtotime($morningOutRaw));
    } else {
        $morningOutDisplay = '--:--';
    }
    if ($afternoonInRaw) {
        $afternoonInDisplay = date("h:i:s A", strtotime($afternoonInRaw));
    } else {
        $afternoonInDisplay = '--:--';
    }
    if ($afternoonOutRaw) {
        $afternoonOutDisplay = date("h:i:s A", strtotime($afternoonOutRaw));
    } else {
        $afternoonOutDisplay = '--:--';
    }
}

$morningHours = $afternoonHours = 0;
$worked = '';
$dailyRemarks = [];
$morningRemarks = [];
$afternoonRemarks = [];

if ($isWeekend) {
    $worked = '';
    $dailyRemarks[] = 'No Worked';
    $morningRemarks[] = '—';
    $afternoonRemarks[] = '—';
    // Set weekend time displays to "--:--" format
    $morningInDisplay = '--:--';
    $morningOutDisplay = '--:--';
    $afternoonInDisplay = '--:--';
    $afternoonOutDisplay = '--:--';
} elseif ($dateStr <= $currentDate) {
    // Check if date is before employee creation date (new employee)
    $isNewEmployee = $employeeCreatedAt && $dateStr < date('Y-m-d', strtotime($employeeCreatedAt));
    
    if ($morningInRaw && $morningOutRaw) {
        $morningHours = (strtotime($morningOutRaw) - strtotime($morningInRaw)) / 3600;
        $morningRemarks[] = 'Present';
        if ($scheduleTimes['morning_in'] && strtotime($morningInRaw) > strtotime($scheduleTimes['morning_in']) + $graceSeconds) {
            $morningRemarks[] = 'Late';
        }
        if ($scheduleTimes['morning_out'] && strtotime($morningOutRaw) < strtotime($scheduleTimes['morning_out'])) {
            $morningRemarks[] = 'Early Out';
        }
    } elseif ($morningInRaw || $morningOutRaw) {
        $morningRemarks[] = 'Incomplete';
    }
    if (empty($morningRemarks) && !$isNewEmployee) {
        $morningRemarks[] = 'Absent';
    }

    if ($afternoonInRaw && $afternoonOutRaw) {
        $afternoonHours = (strtotime($afternoonOutRaw) - strtotime($afternoonInRaw)) / 3600;
        $afternoonRemarks[] = 'Present';
        if ($scheduleTimes['afternoon_in'] && strtotime($afternoonInRaw) > strtotime($scheduleTimes['afternoon_in']) + $graceSeconds) {
            $afternoonRemarks[] = 'Late';
        }
        if ($scheduleTimes['afternoon_out'] && strtotime($afternoonOutRaw) < strtotime($scheduleTimes['afternoon_out'])) {
            $afternoonRemarks[] = 'Early Out';
        }
    } elseif ($afternoonInRaw || $afternoonOutRaw) {
        $afternoonRemarks[] = 'Incomplete';
    }
    if (empty($afternoonRemarks) && !$isNewEmployee) {
        $afternoonRemarks[] = 'Absent';
    }

    $totalDayHours = $morningHours + $afternoonHours;

    if ($totalDayHours >= 8) {
        $worked = 8;
        $dailyRemarks[] = 'Present';
    } elseif ($totalDayHours >= 4) {
        $worked = 4;
        if ($morningHours && !$afternoonHours) {
            $dailyRemarks[] = 'Halfday – AM Only';
        } elseif (!$morningHours && $afternoonHours) {
            $dailyRemarks[] = 'Halfday – PM Only';
        } else {
            $dailyRemarks[] = 'Halfday';
        }
    } elseif ($totalDayHours > 0) {
        $worked = 0;
        $dailyRemarks[] = 'Incomplete Day';
    } else {
        $worked = 0;
        if (empty($dailyRemarks) && !$isNewEmployee) {
            $dailyRemarks[] = 'Absent';
        }
    }

    $totalHoursWorked += (int)$worked;
}

$morningRemarkText = $isWeekend ? '—' : (($dateStr > $currentDate) ? '—' : (empty($morningRemarks) ? '—' : implode(', ', array_unique($morningRemarks))));
$afternoonRemarkText = $isWeekend ? '—' : (($dateStr > $currentDate) ? '—' : (empty($afternoonRemarks) ? '—' : implode(', ', array_unique($afternoonRemarks))));
$remarks = !empty($dailyRemarks) ? implode('; ', array_unique($dailyRemarks)) : (($isWeekend) ? 'No Worked' : (($dateStr > $currentDate) ? '' : ''));
$workedDisplay = ($isWeekend || $worked === '' ? ($isWeekend ? '—' : '') : $worked);
// Remove weekend styling - make weekends same as weekdays
$weekendRowStyle = '';
$weekendDayStyle = '';
?>
<tr>
    <td class="border border-black">
        <div class="font-semibold" style="font-weight: 600; font-size: 12px;"><?= $day ?></div>
        <div class="text-[10px] uppercase tracking-wide" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;"><?= substr($weekdayName, 0, 3) ?></div>
    </td>
    <td class="border border-black"><?= $morningInDisplay ?></td>
    <td class="border border-black"><?= $morningOutDisplay ?></td>
    <td class="border border-black"><?= $morningRemarkText ?></td>
    <td class="border border-black"><?= $afternoonInDisplay ?></td>
    <td class="border border-black"><?= $afternoonOutDisplay ?></td>
    <td class="border border-black"><?= $afternoonRemarkText ?></td>
    <td class="border border-black"><?= $workedDisplay ?></td>
    <td class="border border-black"><?= $remarks !== '' ? $remarks : '' ?></td>
</tr>
<?php endfor; ?>
<tr>
    <td colspan="7" class="border border-black font-bold text-right pr-2">Total</td>
    <td class="border border-black font-bold"><?= $totalHoursWorked ?></td>
    <td class="border border-black"></td>
</tr>
                    </tbody>
                </table>

                <!-- Certification Section -->
                <div class="certification-section" style="display: table !important; height: 100px !important; width: 100% !important; margin-top: 15px !important; visibility: visible !important; opacity: 1 !important; page-break-inside: avoid !important;">
                    <div style="display: table-row !important; width: 100% !important;">
                        <div style="display: table-cell !important; width: 65% !important; vertical-align: top !important; padding-right: 15px !important;">
                            <p style="font-size: 9px !important; line-height: 1.4 !important; text-align: justify !important; margin: 0 !important; padding: 0 !important; display: block !important; visibility: visible !important; opacity: 1 !important; word-wrap: break-word !important; overflow-wrap: break-word !important;">
                                I certify on my honor that the above is a true and correct record of the hours of work performed, and that I have not falsified or misrepresented any information contained herein. I understand that any misrepresentation or omission of time-in or time-out entries may result in disciplinary action and/or legal consequences.
                            </p>
                        </div>
                        <div style="display: table-cell !important; width: 35% !important; vertical-align: top !important; text-align: right !important; padding-left: 10px !important;">
                            <p style="font-size: 9px !important; line-height: 1.4 !important; text-align: right !important; margin: 0 !important; padding: 0 !important; display: block !important; visibility: visible !important; opacity: 1 !important;">
                                ______________________________<br>
                                Signature over printed name
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-600 mt-10">No DTR records found for <?= $months[$selectedMonth] ?> <?= $selectedYear ?>.</p>
    <?php endif; ?>
<?php endif; ?>

        </div>
    </main>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadDTR() {
    const element = document.getElementById("dtrSection");

    if (!element) {
        Swal.fire({
            icon: 'error',
            title: 'No DTR Found',
            text: 'There is no Daily Time Record available to download.'
        });
        return;
    }

    Swal.fire({
        title: 'Downloading...',
        text: 'Please wait while we generate your DTR.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // Scroll to top to avoid clipping
    window.scrollTo(0, 0);

    // Ensure element is visible and properly positioned (centered)
    // Reduce width to fit within page margins (Letter size: 8.5" = 816px, but we need margins)
    element.style.display = 'block';
    element.style.visibility = 'visible';
    element.style.position = 'relative';
    element.style.margin = '0 auto';
    element.style.width = '750px'; // Reduced from 816px to fit with margins
    element.style.maxWidth = '750px';
    element.style.boxSizing = 'border-box';
    
    // Add class for PDF optimization
    element.classList.add('pdf-mode');

    // Calculate proper margins for centering
    // Letter size: 8.5" x 11" = 215.9mm x 279.4mm
    // Content width: 816px = 215.9mm at 96 DPI
    // To center: equal left/right margins
    const pageWidth = 215.9; // mm (letter width)
    const contentWidth = 215.9; // mm (816px at 96 DPI)
    const leftRightMargin = (pageWidth - contentWidth) / 2; // Should be 0, but we'll add small margins
    
    const opt = {
        margin: [5, 5, 5, 5], // Equal margins all around (Top, Right, Bottom, Left)
        filename: <?= json_encode(($selectedMonth && isset($months[$selectedMonth])) ? 'DTR_' . $months[$selectedMonth] . '_' . $selectedYear . '.pdf' : 'DTR_Report.pdf') ?>,
        image: { type: 'jpeg', quality: 0.95 },
        html2canvas: {
            scale: 2,
            scrollY: 0,
            scrollX: 0,
            useCORS: true,
            allowTaint: true,
            windowWidth: 750, // Reduced to fit within page margins
            windowHeight: 1056, // Letter height in pixels (11" at 96 DPI)
            x: 0,
            y: 0,
            onclone: function(clonedDoc) {
                const clonedElement = clonedDoc.getElementById('dtrSection');
                if (clonedElement) {
                    // Center the element in the cloned document
                    clonedElement.style.margin = '0 auto';
                    clonedElement.style.width = '750px'; // Reduced to fit with margins
                    clonedElement.style.maxWidth = '750px';
                    clonedElement.style.boxSizing = 'border-box';
                    clonedElement.style.position = 'relative';
                    clonedElement.style.left = 'auto';
                    clonedElement.style.right = 'auto';
                    
                    // Center the table within the element and ensure it fits
                    const table = clonedElement.querySelector('table');
                    if (table) {
                        table.style.width = '100%';
                        table.style.maxWidth = '100%';
                        table.style.marginLeft = 'auto';
                        table.style.marginRight = 'auto';
                        table.style.tableLayout = 'fixed';
                        table.style.wordWrap = 'break-word';
                    }
                    
                    // Ensure certification section is visible
                    const certSection = clonedElement.querySelector('.certification-section');
                    if (certSection) {
                        certSection.style.display = 'table';
                        certSection.style.visibility = 'visible';
                        certSection.style.opacity = '1';
                        certSection.style.marginTop = '15px';
                        certSection.style.width = '100%';
                        certSection.style.pageBreakInside = 'avoid';
                    }
                    
                    // Ensure all elements in certification are visible
                    const certCells = clonedElement.querySelectorAll('.certification-section div[style*="table-cell"]');
                    certCells.forEach(cell => {
                        cell.style.display = 'table-cell';
                        cell.style.visibility = 'visible';
                        cell.style.opacity = '1';
                    });
                    
                    const certParagraphs = clonedElement.querySelectorAll('.certification-section p');
                    certParagraphs.forEach(p => {
                        p.style.display = 'block';
                        p.style.visibility = 'visible';
                        p.style.opacity = '1';
                    });
                }
            }
        },
        jsPDF: {
            unit: 'mm',
            format: 'letter', // Changed from 'a4' to 'letter' (8.5" x 11")
            orientation: 'portrait',
            compress: true
        },
        pagebreak: {
            mode: 'avoid-all'
        }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        // Remove PDF class after download
        element.classList.remove('pdf-mode');
        Swal.close();
    }).catch(error => {
        element.classList.remove('pdf-mode');
        Swal.fire('Error', 'Something went wrong while generating the PDF.', 'error');
        console.error(error);
    });
}

function clearFilter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('month');
    url.searchParams.delete('year');
    if (!url.searchParams.has('payroll')) {
        url.searchParams.set('payroll', 'user_dtr');
    }
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function () {
    const monthInput = document.getElementById('month');
    const yearInput = document.getElementById('year');
    const monthYearPicker = document.getElementById('monthYearPicker');
    const filterForm = document.querySelector('form');
    
    let monthYearFlatpickr = null;
    
    // Initialize Month & Year Flatpickr with monthSelect plugin
    if (monthYearPicker) {
        const selectedYear = <?= $selectedYear !== null ? json_encode($selectedYear) : 'null' ?>;
        const selectedMonth = <?= $selectedMonth !== null ? (intval($selectedMonth) - 1) : 'null' ?>;
        const year = selectedYear !== null && selectedYear !== undefined ? selectedYear : new Date().getFullYear();
        const month = selectedMonth !== null && selectedMonth !== undefined ? selectedMonth : new Date().getMonth();
        const defaultDate = (monthInput.value && yearInput.value) ? new Date(year, month, 1) : new Date();
        
        monthYearFlatpickr = flatpickr(monthYearPicker, {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            dateFormat: "Y-m",
            altInput: true,
            altFormat: "F Y",
            defaultDate: (monthInput.value && yearInput.value) ? defaultDate : null,
            disableMobile: true,
            clickOpens: true,
            allowInput: false,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    // Extract month and year from selected date
                    const monthNum = String(selectedDates[0].getMonth() + 1).padStart(2, '0');
                    const yearNum = String(selectedDates[0].getFullYear());
                    
                    // Update hidden inputs for form submission
                    monthInput.value = monthNum;
                    yearInput.value = yearNum;
                    
                    // Remove empty state attribute
                    if (instance.altInput) {
                        instance.altInput.removeAttribute('data-empty');
                    }
                } else {
                    // Clear values if no date selected
                    monthInput.value = '';
                    yearInput.value = '';
                    if (instance.altInput) {
                        instance.altInput.value = '';
                        instance.altInput.placeholder = 'Select Month & Year';
                        instance.altInput.setAttribute('data-empty', 'true');
                    }
                }
            }
        });
        
        // Set initial value if exists, otherwise show placeholder
        if (monthInput.value && yearInput.value) {
            const monthNum = parseInt(monthInput.value);
            const yearNum = parseInt(yearInput.value);
            monthYearFlatpickr.setDate(new Date(yearNum, monthNum - 1, 1), false);
        } else {
            // Ensure placeholder is visible when no value
            setTimeout(() => {
                if (monthYearFlatpickr.altInput) {
                    monthYearFlatpickr.altInput.value = '';
                    monthYearFlatpickr.altInput.placeholder = 'Select Month & Year';
                    // Set a data attribute to track empty state
                    monthYearFlatpickr.altInput.setAttribute('data-empty', 'true');
                }
                monthYearPicker.value = '';
                monthYearPicker.placeholder = 'Select Month & Year';
            }, 100);
        }
    }

    filterForm.addEventListener('submit', function (e) {
        if (!monthInput.value || !yearInput.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Filter',
                text: 'Please select month and year before filtering.'
            });
        }
    });
});
</script>

<style>
/* Responsive adjustments for DTR page */
@media (max-width: 767px) {
    main#mainContent {
        margin-left: 0 !important;
        padding-top: calc(var(--mobile-navbar-height, 3.5rem) + 0.5rem) !important;
    }
    
    /* Ensure heading is visible on mobile */
    main#mainContent > div:first-of-type {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
}

@media (min-width: 768px) {
    main#mainContent:not(.ml-\[64px\]) {
        margin-left: 256px;
    }
}

/* Make sure heading is always visible */
h1.text-xl {
    display: block !important;
    visibility: visible !important;
}

#dtrSection {
    width: 100%;
    max-width: 750px; /* Reduced to fit within page margins */
    padding: 5px;
    margin: 0 auto;
    background-color: white;
    overflow-x: auto;
    box-sizing: border-box;
    position: relative;
}

/* Ensure header is always visible */
#dtrSection .header-section {
    display: block !important;
    visibility: visible !important;
    width: 100% !important;
    position: relative !important;
}

/* Header section styles */
#dtrSection .header-section {
    width: 100% !important;
    margin-bottom: 8px;
    display: block !important;
    visibility: visible !important;
    position: relative !important;
    left: 0 !important;
    right: 0 !important;
}

#dtrSection .header-top {
    display: table !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection .header-left {
    display: table-cell !important;
    vertical-align: top;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection .header-left-content {
    display: table !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection .logo-container {
    display: table-cell;
    vertical-align: top;
    width: 70px;
    padding-right: 15px;
}

#dtrSection .logo-image {
    width: 100%;
    height: auto;
}

#dtrSection .company-info {
    display: table-cell;
    vertical-align: top;
}

#dtrSection .company-name {
    font-size: 18px;
    margin-top: 4px;
    font-weight: 700;
    color: #000;
    margin-bottom: 2px;
    line-height: 1.2;
}

#dtrSection .company-address {
    font-size: 10px;
    color: #478547;
    line-height: 1.2;
}

#dtrSection .header-divider {
    border: none;
    border-top: 1px solid #dee2e6;
    margin: 8px 0;
}

#dtrSection .dtr-title {
    text-align: center;
    font-weight: 600;
    margin-top: 10px;
    margin-bottom: 4px;
    font-size: 11px;
}

#dtrSection table {
    width: 100% !important;
    max-width: 100% !important;
    table-layout: fixed !important;
    border-collapse: collapse !important;
    font-size: 8px;
    word-break: break-word !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    margin-top: 2px;
    page-break-inside: avoid;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

#dtrSection th,
#dtrSection td {
    padding: 2px 1px;
    border: 1px solid black;
    text-align: center;
    font-size: 8px;
    line-height: 1.2;
    vertical-align: middle;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    hyphens: auto;
}

/* Day number styling */
#dtrSection td:first-child .font-semibold {
    font-size: 12px !important; /* Day number size */
}

/* Header adjustments for PDF */
#dtrSection .header-section {
    margin-bottom: 10px !important; /* Reduced from 20px */
}

#dtrSection .company-name {
    font-size: 18px !important; /* Reduced from 22px */
    margin-top: 8px !important; /* Reduced from 12px */
}

#dtrSection .logo-container {
    width: 70px !important; /* Reduced from 90px */
}

#dtrSection .logo-image {
    width: 70px !important;
    height: auto;
}

/* Reduce spacing in header */
#dtrSection .header-left-content {
    margin-bottom: 0 !important;
}

/* Reduce spacing for DTR title */
#dtrSection p.text-center {
    margin-top: 5px !important; /* Reduced from mt-8 */
    margin-bottom: 5px !important;
    font-size: 12px !important; /* Increased from 11px */
}

/* Reduce spacing for name and info */
#dtrSection p.text-left {
    margin-bottom: 3px !important;
    font-size: 11px !important; /* Increased from 10px */
}

#dtrSection .mb-2 {
    margin-bottom: 5px !important;
}

#dtrSection .mb-2 p {
    font-size: 10px !important; /* Increased from 9px */
    margin: 2px 0 !important;
    line-height: 1.3 !important; /* Improved readability */
}

/* Certification section adjustments */
#dtrSection .flex.justify-between {
    margin-top: 5px !important; /* Reduced spacing */
}

#dtrSection .flex.justify-between p {
    font-size: 9px !important; /* Increased from 8px */
    line-height: 1.4 !important; /* Improved readability */
}

/* HR line adjustments */
#dtrSection hr {
    margin: 8px 0 !important; /* Reduced from 15px */
}

/* PDF mode specific styles - optimized for single page (Letter size) */
#dtrSection.pdf-mode {
    padding: 3px !important;
    width: 750px !important; /* Reduced to fit within page margins */
    max-width: 750px !important;
    box-sizing: border-box !important;
    page-break-inside: avoid !important;
    margin: 0 auto !important;
    position: relative !important;
    left: auto !important;
    right: auto !important;
    display: block !important;
}

/* Ensure header is visible in PDF mode */
#dtrSection.pdf-mode .header-section {
    display: block !important;
    visibility: visible !important;
    width: 100% !important;
    position: relative !important;
    margin-bottom: 3px !important;
    left: 0 !important;
    right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

#dtrSection.pdf-mode .header-top {
    display: table !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection.pdf-mode .header-left {
    display: table-cell !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection.pdf-mode .header-left-content {
    display: table !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Fix table alignment in PDF mode - centered and fit within container */
#dtrSection.pdf-mode table {
    width: 100% !important;
    max-width: 100% !important;
    margin-left: auto !important;
    margin-right: auto !important;
    margin-top: 2px !important;
    table-layout: fixed !important;
    word-wrap: break-word !important;
    border-collapse: collapse !important;
}

#dtrSection.pdf-mode .header-section {
    margin-bottom: 3px !important;
}

#dtrSection.pdf-mode .logo-container {
    width: 50px !important;
    padding-right: 10px !important;
}

#dtrSection.pdf-mode .company-name {
    font-size: 12px !important;
    margin-top: 2px !important;
    line-height: 1.1 !important;
}

#dtrSection.pdf-mode .company-address {
    font-size: 8px !important;
    line-height: 1.1 !important;
}

#dtrSection.pdf-mode .header-divider {
    margin: 3px 0 !important;
    border-top-width: 0.5px !important;
}

#dtrSection.pdf-mode .dtr-title {
    margin-top: 10px !important;
    margin-bottom: 2px !important;
    font-size: 9px !important;
}

#dtrSection.pdf-mode table {
    font-size: 7px !important;
    margin-top: 2px !important;
    page-break-inside: avoid !important;
}

#dtrSection.pdf-mode th,
#dtrSection.pdf-mode td {
    padding: 1px 0.5px !important;
    font-size: 7px !important;
    line-height: 1.1 !important;
}

/* Day number in PDF mode */
#dtrSection.pdf-mode td:first-child .font-semibold {
    font-size: 8px !important;
}

#dtrSection.pdf-mode .header-section {
    margin-bottom: 2px !important;
}

#dtrSection.pdf-mode .company-name {
    font-size: 14px !important;
    margin-top: 2px !important;
    line-height: 1.1 !important;
}

#dtrSection.pdf-mode .logo-container {
    width: 50px !important;
}

#dtrSection.pdf-mode .logo-image {
    width: 50px !important;
}

#dtrSection.pdf-mode p.text-center {
    margin-top: 2px !important;
    margin-bottom: 2px !important;
    font-size: 8px !important;
    line-height: 1.2 !important;
}

#dtrSection.pdf-mode p.text-left {
    margin-bottom: 1px !important;
    font-size: 7px !important;
    line-height: 1.2 !important;
}

#dtrSection.pdf-mode .mb-2 {
    margin-bottom: 2px !important;
}

#dtrSection.pdf-mode .mb-2 p {
    font-size: 7px !important;
    margin: 1px 0 !important;
    line-height: 1.2 !important;
}

/* Certification section in PDF mode */
#dtrSection.pdf-mode .certification-section {
    margin-top: 15px !important;
    display: table !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 100% !important;
    page-break-inside: avoid !important;
}

#dtrSection.pdf-mode .certification-section > div {
    display: table-row !important;
    width: 100% !important;
}

#dtrSection.pdf-mode .certification-section > div > div {
    display: table-cell !important;
    vertical-align: top !important;
}

#dtrSection.pdf-mode .certification-section p {
    font-size: 8px !important;
    line-height: 1.4 !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    margin: 0 !important;
    padding: 0 !important;
}

#dtrSection.pdf-mode hr {
    margin: 3px 0 !important;
    border-top-width: 0.5px !important;
}

@media (max-width: 768px) {
    #dtrSection {
        padding: 10px;
    }

    #dtrSection table {
        font-size: 10px; /* Increased from 9px */
    }

    #dtrSection p,
    #dtrSection span,
    #dtrSection strong {
        font-size: 11px; /* Increased from 10px */
    }
}

@media print {
    body {
        margin: 0;
    }

    #dtrSection {
        width: 750px; /* Reduced to fit within page margins */
        padding: 8px;
        page-break-inside: avoid;
        font-size: 10px;
    }

    #dtrSection table {
        font-size: 10px; /* Increased from 9px */
    }

    #dtrSection th,
    #dtrSection td {
        padding: 4px; /* Increased from 3px */
        font-size: 10px; /* Increased from 9px */
    }

    #dtrSection p,
    #dtrSection span,
    #dtrSection strong {
        font-size: 10px; /* Increased from 9px */
    }

    table, tr, td, th {
        page-break-inside: avoid !important;
    }

    html, body {
        width: 8.5in; /* Letter width (8.5 inches) */
        height: 11in; /* Letter height (11 inches) */
    }
}
/* Flatpickr input styling */
.flatpickr-month-year-input {
    min-width: 180px;
}

.flatpickr-month-year-input.flatpickr-input {
    cursor: pointer;
}

.flatpickr-month-year-input::placeholder {
    color: #9ca3af;
    opacity: 1;
}

.flatpickr-month-year-input:placeholder-shown {
    color: #9ca3af;
}

/* Style for empty state in altInput */
.flatpickr-month-year-input[data-empty="true"] {
    color: #9ca3af;
}

/* Ensure altInput shows placeholder when empty */
.flatpickr-month-year-input[data-empty="true"]::placeholder {
    color: #9ca3af;
    opacity: 1;
}

/* Ensure Flatpickr calendar appears above other elements */
.flatpickr-calendar {
    z-index: 9999 !important;
}
</style>
