<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../core/database.php';
require_once '../Model/Employees.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();
$employeeModel = new Employees($conn);

// === GET: fetch available employees (not scheduled) ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['available_employees'])) {
    $allEmployees = $employeeModel->getAllEmployees();
    $scheduledIds = $conn->query("SELECT employee_id FROM employee_schedules")->fetchAll(PDO::FETCH_COLUMN);

    $available = array_filter($allEmployees, function ($emp) use ($scheduledIds) {
        return !in_array($emp['id'], $scheduledIds);
    });

    echo json_encode(array_values($available));
    exit;
}

// === GET: fetch a specific schedule with employee name ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_schedule'])) {
    $id = (int) $_GET['fetch_schedule'];
    $stmt = $conn->prepare("
        SELECT 
            s.*,
            CONCAT(e.first_name, ' ',
                   IFNULL(CONCAT(UPPER(LEFT(e.middle_name, 1)), '. '), ''),
                   e.last_name) AS employee_name
        FROM schedules s
        LEFT JOIN employee_schedules es ON es.schedule_id = s.id
        LEFT JOIN employees e ON e.id = es.employee_id
        WHERE s.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($schedule
        ? ['status' => 'success', 'data' => $schedule]
        : ['status' => 'error', 'message' => 'Schedule not found.']);
    exit;
}

// === GET: fetch table HTML for dynamic refresh ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_table'])) {
    $stmt = $conn->query("SELECT * FROM schedules ORDER BY id DESC");
    ob_start();
    $i = 1;
    if ($stmt->rowCount() > 0):
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
<tr class="hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
  <td class="px-3 md:px-6 py-2"><?= $i++ ?></td>
  <td class="px-2 md:px-6 py-2"><?= htmlspecialchars($row['name']) ?></td>
  <td class="px-2 md:px-6 py-2"><?= date("g:i A", strtotime($row['sched_morning_in'])) ?></td>
  <td class="px-2 md:px-6 py-2"><?= date("g:i A", strtotime($row['sched_morning_out'])) ?></td>
  <td class="px-2 md:px-6 py-2"><?= date("g:i A", strtotime($row['sched_afternoon_in'])) ?></td>
  <td class="px-2 md:px-6 py-2"><?= date("g:i A", strtotime($row['sched_afternoon_out'])) ?></td>
  <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap"><?= (int)$row['grace_period'] ?> mins</td>
  <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap">
    <button type="button" class="edit-btn inline-flex h-8 w-8 items-center justify-center rounded-md transition duration-150 ease-in-out hover:bg-[#478547] hover:text-white transform hover:scale-105"
      data-id="<?= $row['id'] ?>"
      data-name="<?= htmlspecialchars($row['name']) ?>"
      data-morningin="<?= $row['sched_morning_in'] ?>"
      data-morningout="<?= $row['sched_morning_out'] ?>"
      data-afternoonin="<?= $row['sched_afternoon_in'] ?>"
      data-afternoonout="<?= $row['sched_afternoon_out'] ?>"
      data-grace="<?= $row['grace_period'] ?>"
      data-bs-toggle="modal"
      data-bs-target="#editScheduleModal"
      onclick="populateEditSchedule(<?= $row['id'] ?>)">
      <i class="bi bi-pencil-square"></i>
    </button>

    <form id="deleteScheduleForm-<?= $row['id'] ?>" class="inline-block m-0 p-0">
      <input type="hidden" name="schedule_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="action" value="delete">
      <button type="button"
        data-action="delete-schedule"
        data-id="<?= $row['id'] ?>"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-[#b91c1c] hover:text-white ml-1">
        <i class="bi bi-trash"></i>
      </button>
    </form>
  </td>
</tr>
<?php
        endwhile;
    else:
?>
<tr>
  <td colspan="8" class="px-4 py-4 text-center text-gray-500">No Schedule Found</td>
</tr>
<?php
    endif;
    echo ob_get_clean();
    exit;
}

// === POST: Add new schedule ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = filter_var($_POST['employee_id'], FILTER_SANITIZE_NUMBER_INT);
    $fields = ['sched_morning_in', 'sched_morning_out', 'sched_afternoon_in', 'sched_afternoon_out', 'grace_period'];

    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "Field {$field} is required.", 'icon' => 'warning']);
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE id = :id");
        $stmt->execute(['id' => $employee_id]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);

        $fullName = $emp
            ? ucwords($emp['first_name']) . ' ' .
              (isset($emp['middle_name'][0]) ? strtoupper($emp['middle_name'][0]) . '. ' : '') .
              ucwords($emp['last_name'])
            : 'Unknown Employee';

        $conn->prepare("INSERT INTO schedules (name, sched_morning_in, sched_morning_out, sched_afternoon_in, sched_afternoon_out, grace_period)
            VALUES (:name, :min, :mout, :ain, :aout, :grace)")
            ->execute([
                'name' => $fullName,
                'min' => $_POST['sched_morning_in'],
                'mout' => $_POST['sched_morning_out'],
                'ain' => $_POST['sched_afternoon_in'],
                'aout' => $_POST['sched_afternoon_out'],
                'grace' => $_POST['grace_period']
            ]);

        $schedule_id = $conn->lastInsertId();

        $conn->prepare("INSERT INTO employee_schedules (employee_id, schedule_id) VALUES (:eid, :sid)")
            ->execute(['eid' => $employee_id, 'sid' => $schedule_id]);

        // Get inserted row to return HTML
        $stmt = $conn->prepare("SELECT * FROM schedules WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $schedule_id]);
        $newRow = $stmt->fetch(PDO::FETCH_ASSOC);

        ob_start(); ?>
            <?php $i = 1; ?>
            <tr class="hover:bg-[#f2f8f2] even:bg-[#cde4cd]">
            <td class="px-3 md:px-6 py-2">
                <?= $i++ ?>
            </td>
            <td class="px-2 md:px-6 py-2"><?= htmlspecialchars($newRow['name']) ?></td>
            <td class="px-2 md:px-6 py-2 text-center"><?= date("g:i A", strtotime($newRow['sched_morning_in'])) ?></td>
            <td class="px-2 md:px-6 py-2 text-center"><?= date("g:i A", strtotime($newRow['sched_morning_out'])) ?></td>
            <td class="px-2 md:px-6 py-2 text-center"><?= date("g:i A", strtotime($newRow['sched_afternoon_in'])) ?></td>
            <td class="px-2 md:px-6 py-2 text-center"><?= date("g:i A", strtotime($newRow['sched_afternoon_out'])) ?></td>
            <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap"><?= (int)$newRow['grace_period'] ?> mins</td>
            <td class="px-2 md:px-6 py-2 text-center whitespace-nowrap">
                <!-- Edit Button -->
                <button 
                type="button"
                class="edit-btn inline-flex h-8 w-8 items-center justify-center rounded-md transition duration-150 ease-in-out hover:bg-[#478547] hover:text-white transform hover:scale-105"
                data-id="<?= $newRow['id'] ?>"
                data-name="<?= htmlspecialchars($newRow['name']) ?>"
                data-morningin="<?= htmlspecialchars($newRow['sched_morning_in']) ?>"
                data-morningout="<?= htmlspecialchars($newRow['sched_morning_out']) ?>"
                data-afternoonin="<?= htmlspecialchars($newRow['sched_afternoon_in']) ?>"
                data-afternoonout="<?= htmlspecialchars($newRow['sched_afternoon_out']) ?>"
                data-grace="<?= htmlspecialchars($newRow['grace_period']) ?>"
                data-bs-toggle="modal"
                data-bs-target="#editScheduleModal"
                onclick="populateEditSchedule(<?= $newRow['id'] ?>)">
                <i class="bi bi-pencil-square"></i>
                </button>

                <!-- Delete Button -->
                <form id="deleteScheduleForm-<?= $newRow['id'] ?>" class="inline-block m-0 p-0">
                <input type="hidden" name="schedule_id" value="<?= $newRow['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button 
                    type="button"
                    data-action="delete-schedule"
                    data-id="<?= $newRow['id'] ?>"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-[#b91c1c] hover:text-white ml-1">
                    <i class="bi bi-trash"></i>
                </button>
                </form>
            </td>
            </tr>

            <?php
        $newRowHtml = ob_get_clean();

        echo json_encode([
            'status' => 'success',
            'message' => 'Schedule added.',
            'icon' => 'success',
            'new_row_html' => $newRowHtml
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'icon' => 'error']);
    }
    exit;
}

// === POST: Update schedule ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['employee_id'])) {
    $required = ['id', 'sched_morning_in', 'sched_morning_out', 'sched_afternoon_in', 'sched_afternoon_out', 'grace_period'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => "Field {$field} is required.", 'icon' => 'warning']);
            exit;
        }
    }

    try {
        $conn->prepare("UPDATE schedules SET 
            sched_morning_in = :min, 
            sched_morning_out = :mout,
            sched_afternoon_in = :ain,
            sched_afternoon_out = :aout,
            grace_period = :grace
            WHERE id = :id")
            ->execute([
                'min' => $_POST['sched_morning_in'],
                'mout' => $_POST['sched_morning_out'],
                'ain' => $_POST['sched_afternoon_in'],
                'aout' => $_POST['sched_afternoon_out'],
                'grace' => $_POST['grace_period'],
                'id' => $_POST['id']
            ]);

        echo json_encode(['status' => 'success', 'message' => 'Schedule updated.', 'icon' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'icon' => 'error']);
    }
    exit;
}

// === POST: Delete schedule ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['schedule_id']) && $_POST['action'] === 'delete') {
    $schedule_id = filter_var($_POST['schedule_id'], FILTER_SANITIZE_NUMBER_INT);
    try {
        $conn->prepare("DELETE FROM employee_schedules WHERE schedule_id = :id")->execute(['id' => $schedule_id]);
        $conn->prepare("DELETE FROM schedules WHERE id = :id")->execute(['id' => $schedule_id]);

        echo json_encode(['status' => 'success', 'message' => 'Schedule deleted.', 'icon' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'icon' => 'error']);
    }
    exit;
}

// === Invalid Request Fallback ===
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
