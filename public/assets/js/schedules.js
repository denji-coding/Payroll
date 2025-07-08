// document.addEventListener('DOMContentLoaded', () => {
//   const addForm = document.getElementById('addScheduleForm');
//   const editForm = document.getElementById('editScheduleForm');

//   // === ADD SCHEDULE ===
//   if (addForm) {
//     addForm.addEventListener('submit', async (e) => {
//       e.preventDefault();
//       const formData = new FormData(addForm);

//       const res = await fetch('../app/api/schedules-api.php', {
//         method: 'POST',
//         body: formData
//       });

//       const result = await res.json();
//       Swal.fire(result.message, '', result.icon);

//       if (result.status === 'success') {
//         addForm.reset();
//         bootstrap.Modal.getInstance(document.getElementById('addScheduleModal')).hide();
//         refreshScheduleTable();
//       }
//     });
//   }

//   // === EDIT SCHEDULE ===
//   if (editForm) {
//     editForm.addEventListener('submit', async (e) => {
//       e.preventDefault();
//       const formData = new FormData(editForm);

//       const res = await fetch('../app/api/schedules-api.php', {
//         method: 'POST',
//         body: formData
//       });

//       const result = await res.json();
//       Swal.fire(result.message, '', result.icon);

//       if (result.status === 'success') {
//         bootstrap.Modal.getInstance(document.getElementById('editScheduleModal')).hide();
//         refreshScheduleTable();
//       }
//     });
//   }

//   // === FILL EDIT MODAL ===
//   window.populateEditSchedule = async (id) => {
//     try {
//       const res = await fetch(`../app/api/schedules-api.php?fetch_schedule=${id}`);
//       const result = await res.json();

//       if (result.status === 'success') {
//         const s = result.data;
//         document.getElementById('editScheduleId').value = s.id;
//         document.getElementById('editScheduleName').value = s.employee_name ?? s.name;
//         document.getElementById('editMorningIn').value = s.sched_morning_in;
//         document.getElementById('editMorningOut').value = s.sched_morning_out;
//         document.getElementById('editAfternoonIn').value = s.sched_afternoon_in;
//         document.getElementById('editAfternoonOut').value = s.sched_afternoon_out;
//         document.getElementById('editGracePeriod').value = s.grace_period;

//         const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
//         modal.show();
//       } else {
//         Swal.fire('Error', result.message, 'error');
//       }
//     } catch (err) {
//       Swal.fire('Error', 'Failed to fetch schedule info.', 'error');
//       console.error(err);
//     }
//   };

//   // === DELETE SCHEDULE ===
//   document.addEventListener('click', async (e) => {
//     const btn = e.target.closest('.deleteScheduleBtn');
//     if (btn) {
//       const scheduleId = btn.getAttribute('data-id');

//       const confirm = await Swal.fire({
//         title: 'Are you sure?',
//         text: 'This schedule will be permanently deleted.',
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Yes, delete it!'
//       });

//       if (confirm.isConfirmed) {
//         const formData = new FormData();
//         formData.append('action', 'delete');
//         formData.append('schedule_id', scheduleId);

//         const res = await fetch('../app/api/schedules-api.php', {
//           method: 'POST',
//           body: formData
//         });

//         const result = await res.json();
//         Swal.fire(result.message, '', result.icon);

//         if (result.status === 'success') {
//           refreshScheduleTable();
//         }
//       }
//     }
//   });

//   // === REFRESH TABLE BODY ===
//   window.refreshScheduleTable = async () => {
//     try {
//       const res = await fetch('../app/api/schedules-api.php?fetch_table=1');
//       const html = await res.text();
//       document.getElementById('scheduleTableBody').innerHTML = html;
//     } catch (err) {
//       console.error("Failed to refresh schedule table", err);
//     }
//   };
// });
