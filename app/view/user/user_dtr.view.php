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
    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-all duration-300 ease-in-out">
        <?php require_once views_path("partials/user_sidebar"); ?>

        <div class="mt-6">
            <span class="text-2xl font-bold tracking-tight">Daily Time Record</span>
            <p class="text-gray-600">Here's a quick overview of your daily logs.</p>
        </div>

        <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
            <!-- Filter Form -->
            <div class="flex items-center justify-between mb-6 gap-4">
                <form method="GET" class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <input type="hidden" name="payroll" value="user_dtr" />
                    <label for="month" class="font-medium text-gray-700">Month:</label>
                    <select name="month" id="month" class="border rounded p-1" required>
                        <option disabled <?= is_null($selectedMonth) ? 'selected' : '' ?>>-- Select Month --</option>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= ($num === $selectedMonth) ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="year" class="font-medium text-gray-700">Year:</label>
                    <select name="year" id="year" class="border rounded p-1" required>
                        <option disabled <?= is_null($selectedYear) ? 'selected' : '' ?>>-- Select Year --</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button id="filterBtn" type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition">
                        <span class="text-sm">Filter</span>
                        <i class="bi bi-funnel fs-7 ml-2"></i>
                    </button>
                </form>

                <?php if ($selectedMonth && $selectedYear): ?>
                    <div class="flex gap-2">
                        <?php if (count($filteredRecords) > 0): ?>
                            <button onclick="downloadDTR()" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition">
                                <i class="bi bi-download mr-2"></i><span class="text-sm">Download DTR</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" onclick="clearFilter()" class="bg-gray-600 text-white px-4 py-1 rounded hover:bg-gray-700 transition">
                            <span class="text-sm">Clear Filter</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($selectedMonth && $selectedYear): ?>
    <?php if (count($filteredRecords) > 0): ?>
        <!-- DTR Table -->
        <div class="overflow-x-auto w-full">
            <div id="dtrSection" class="text-xs leading-tight">
                <p class="text-center font-semibold text-sm">DAILY TIME RECORD</p>
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
$morningInDisplay = $morningOutDisplay = $afternoonInDisplay = $afternoonOutDisplay = '-';

// Skip processing database entries for weekends - they should always show "No Worked"
if (!$isWeekend && $entry) {
    $morningInRaw = $timeIsValid($entry['morning_in']) ? $entry['morning_in'] : null;
    $morningOutRaw = $timeIsValid($entry['morning_out']) ? $entry['morning_out'] : null;
    $afternoonInRaw = $timeIsValid($entry['afternoon_in']) ? $entry['afternoon_in'] : null;
    $afternoonOutRaw = $timeIsValid($entry['afternoon_out']) ? $entry['afternoon_out'] : null;

    if ($morningInRaw) {
        $morningInDisplay = date("h:i:s A", strtotime($morningInRaw));
    }
    if ($morningOutRaw) {
        $morningOutDisplay = date("h:i:s A", strtotime($morningOutRaw));
    }
    if ($afternoonInRaw) {
        $afternoonInDisplay = date("h:i:s A", strtotime($afternoonInRaw));
    }
    if ($afternoonOutRaw) {
        $afternoonOutDisplay = date("h:i:s A", strtotime($afternoonOutRaw));
    }
}

$morningHours = $afternoonHours = 0;
$worked = '';
$dailyRemarks = [];
$morningRemarks = [];
$afternoonRemarks = [];

if ($isWeekend) {
    $worked = '--';
    $dailyRemarks[] = 'No Worked';
    $morningRemarks[] = '-';
    $afternoonRemarks[] = '-';
} elseif ($dateStr <= $currentDate) {
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
    if (empty($morningRemarks)) {
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
    if (empty($afternoonRemarks)) {
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
        if (empty($dailyRemarks)) {
            $dailyRemarks[] = 'Absent';
        }
    }

    $totalHoursWorked += (int)$worked;
}

// Set "No Worked" for all weekend days (Sat/Sun), including future dates
if ($isWeekend) {
    $morningInDisplay = 'No Worked';
    $morningOutDisplay = 'No Worked';
    $afternoonInDisplay = 'No Worked';
    $afternoonOutDisplay = 'No Worked';
}

$morningRemarkText = $isWeekend ? 'No Worked' : (($dateStr > $currentDate) ? '-' : implode(', ', array_unique($morningRemarks)));
$afternoonRemarkText = $isWeekend ? 'No Worked' : (($dateStr > $currentDate) ? '-' : implode(', ', array_unique($afternoonRemarks)));
$morningRemarkText = $morningRemarkText !== '' ? $morningRemarkText : '--';
$afternoonRemarkText = $afternoonRemarkText !== '' ? $afternoonRemarkText : '-';
$remarks = !empty($dailyRemarks) ? implode('; ', array_unique($dailyRemarks)) : (($isWeekend) ? 'No Worked' : (($dateStr > $currentDate) ? '' : ($worked === '' ? '' : 'Absent')));
$workedDisplay = ($isWeekend || $worked === '' ? ($isWeekend ? '—' : '-') : $worked);
$weekendRowStyle = $isWeekend ? 'background-color: #fef2f2; color: #dc2626;' : '';
$weekendDayStyle = $isWeekend ? 'color: #dc2626; font-weight: 600;' : '';
?>
<tr class="<?= $isWeekend ? 'weekend-row' : '' ?>" style="<?= $weekendRowStyle ?>">
    <td class="border border-black <?= $isWeekend ? 'weekend-day' : '' ?>" style="<?= $weekendDayStyle ?>">
        <div class="font-semibold" style="font-weight: 600;"><?= $day ?></div>
        <div class="text-[10px] uppercase tracking-wide" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;"><?= substr($weekdayName, 0, 3) ?></div>
        <?php if ($isWeekend): ?>
            <div class="text-[10px] mt-1 font-semibold" style="font-size: 9px; margin-top: 2px; font-weight: 600; color: #dc2626;">No Worked</div>
        <?php endif; ?>
    </td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $morningInDisplay ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $morningOutDisplay ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $morningRemarkText ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $afternoonInDisplay ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $afternoonOutDisplay ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $afternoonRemarkText ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $workedDisplay ?></td>
    <td class="border border-black" style="<?= $weekendRowStyle ?>"><?= $remarks !== '' ? $remarks : '-' ?></td>
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
                <div class="flex justify-between items-center mt-4 text-[10px] leading-snug">
                    <p class="max-w-[70%] text-justify">
                        I certify on my honor that the above is a true and correct record of the hours of work performed, and that I have not falsified or misrepresented any information contained herein.
                        I understand that any misrepresentation or omission of time-in or time-out entries may result in disciplinary action and/or legal consequences.
                    </p>
                    <p class="text-right max-w-[35%] pr-10">
                        ______________________________<br>
                        Signature over printed name
                    </p>
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

    const opt = {
        margin: [10, 10, 10, 10], // top, left, bottom, right
        filename: 'DTR_<?= $months[$selectedMonth] . "_" . $selectedYear ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2, // balance between clarity and performance
            scrollY: 0
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        },
        pagebreak: {
            mode: ['avoid-all', 'css', 'legacy']
        }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        Swal.close();
    }).catch(error => {
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
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');
    const filterForm = document.querySelector('form');

    filterForm.addEventListener('submit', function (e) {
        if (!monthSelect.value || !yearSelect.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Filter',
                text: 'Please select both month and year before filtering.'
            });
        }
    });
});
</script>

<style>
#dtrSection {
    width: 100%;
    max-width: 794px;
    padding: 20px;
    margin: auto;
    background-color: white;
    overflow-x: auto;
}

#dtrSection table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    font-size: 10px;
    word-break: break-word;
    margin-top: 10px;
}

#dtrSection th,
#dtrSection td {
    padding: 4px;
    border: 1px solid black;
    text-align: center;
}

.weekend-row td {
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}

.weekend-day {
    color: #b91c1c !important;
    font-weight: 600;
}

@media (max-width: 768px) {
    #dtrSection {
        padding: 10px;
    }

    #dtrSection table {
        font-size: 9px;
    }

    #dtrSection p,
    #dtrSection span,
    #dtrSection strong {
        font-size: 10px;
    }
}

@media print {
    body {
        margin: 0;
    }

    #dtrSection {
        width: 794px;
        padding: 20px;
        page-break-inside: avoid;
    }

    table, tr, td, th {
        page-break-inside: avoid !important;
    }

    html, body {
        width: 210mm;
        height: 297mm;
    }
}
</style>
