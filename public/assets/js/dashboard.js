    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const closeNotificationBtn = document.getElementById('closeNotificationDropdown');

    notificationBtn.addEventListener('click', () => {
        notificationDropdown.classList.toggle('hidden');
    });

    closeNotificationBtn.addEventListener('click', () => {
        notificationDropdown.classList.add('hidden');
    });

    document.addEventListener('click', (e) => {
        if (
            !notificationBtn.contains(e.target) &&
            !notificationDropdown.contains(e.target)
        ) {
            notificationDropdown.classList.add('hidden');
        }
    });

    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    const closeProfileBtn = document.getElementById('closeProfileDropdown');

    profileBtn.addEventListener('click', () => {
        profileDropdown.classList.toggle('hidden');
    });

    closeProfileBtn.addEventListener('click', () => {
        profileDropdown.classList.add('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });

    document.getElementById('dateString').textContent = new Date().toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    const holidaysByYear = {
        2026: {
            '2025-12-31': "New Year's Day",
            '2026-01-28': 'Chinese New Year',
            '2026-04-08': 'Araw ng Kagitingan',
            '2026-04-16': 'Maundy Thursday',
            '2026-04-17': 'Good Friday',
            '2026-04-18': 'Black Saturday',
            '2026-04-30': 'Labor Day',
            '2026-06-11': 'Independence Day',
            '2026-08-24': 'National Heroes Day',
            '2026-11-29': 'Bonifacio Day',
            '2026-12-07': 'Feast of the Immaculate Conception',
            '2026-12-23': 'Christmas Eve',
            '2026-12-24': 'Christmas Day',
            '2026-12-29': 'Rizal Day',
            '2026-12-30': "New Year's Eve"
        },
        2025: {
            '2024-12-31': "New Year's Day",
            '2025-01-28': 'Chinese New Year',
            '2025-02-24': 'People Power Anniversary',
            '2025-04-08': 'Araw ng Kagitingan',
            '2025-04-16': 'Maundy Thursday',
            '2025-04-17': 'Good Friday',
            '2025-04-18': 'Black Saturday',
            '2025-04-30': 'Labor Day',
            '2025-06-11': 'Independence Day',
            '2025-08-24': 'National Heroes Day',
            '2025-11-29': 'Bonifacio Day',
            '2025-12-07': 'Feast of the Immaculate Conception',
            '2025-12-23': 'Christmas Eve',
            '2025-12-24': 'Christmas Day',
            '2025-12-29': 'Rizal Day',
            '2025-12-30': "New Year's Eve"
        },
        2024: {
            '2023-12-31': "New Year's Day",
            '2024-01-28': 'Chinese New Year',
            '2024-04-08': 'Araw ng Kagitingan',
            '2024-04-16': 'Maundy Thursday',
            '2024-04-17': 'Good Friday',
            '2024-04-18': 'Black Saturday',
            '2024-04-30': 'Labor Day',
            '2024-06-11': 'Independence Day',
            '2024-08-24': 'National Heroes Day',
            '2024-11-29': 'Bonifacio Day',
            '2024-12-07': 'Feast of the Immaculate Conception',
            '2024-12-23': 'Christmas Eve',
            '2024-12-24': 'Christmas Day',
            '2024-12-29': 'Rizal Day',
            '2024-12-30': "New Year's Eve"
        }
    };

    let currentDate = new Date();

    function renderCalendar(date) {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        const calendarMonth = document.getElementById('calendarMonth');
        const calendarDays = document.getElementById('calendarDays');

        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();

        calendarMonth.textContent = `${monthNames[month]} ${year}`;
        calendarDays.innerHTML = '';

        const holidays = holidaysByYear[year] || {};
        const totalCells = 42; // 6 rows * 7 days = 42 cells

        // Calculate the number of days from previous month to show
        const daysFromPrevMonth = firstDay;
        // Calculate the number of days from next month to show
        const daysFromNextMonth = totalCells - (daysFromPrevMonth + daysInMonth);

        for (let i = 0; i < totalCells; i++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('calendar-day');
            dayCell.style.border = '1px solid #aeeec8';
            dayCell.style.padding = '2px';
            dayCell.style.margin = '2px';
            dayCell.style.fontSize = '14px';
            dayCell.style.textAlign = 'center';
            dayCell.style.height = '60px';
            dayCell.style.width = '100%';
            dayCell.style.display = 'flex';
            dayCell.style.alignItems = 'center';
            dayCell.style.justifyContent = 'center';
            dayCell.style.position = 'relative';

            let dayNumber;
            let cellDate;
            let currentMonth = true;

            if (i < daysFromPrevMonth) {
                // Previous month days
                dayNumber = prevMonthDays - daysFromPrevMonth + 1 + i;
                cellDate = new Date(year, month - 1, dayNumber);
                dayCell.classList.add('text-gray-400');
                dayCell.style.opacity = '0.4';
                currentMonth = false;
                dayCell.style.pointerEvents = 'none';
            } else if (i >= daysFromPrevMonth + daysInMonth) {
                // Next month days
                dayNumber = i - (daysFromPrevMonth + daysInMonth) + 1;
                cellDate = new Date(year, month + 1, dayNumber);
                dayCell.classList.add('text-gray-400');
                dayCell.style.opacity = '0.4';
                currentMonth = false;
                dayCell.style.pointerEvents = 'none';
            } else {
                // Current month days
                dayNumber = i - daysFromPrevMonth + 1;
                cellDate = new Date(year, month, dayNumber);
            }

            const dateString = cellDate.toISOString().split('T')[0];
            
            dayCell.setAttribute('data-date', dateString);
            
            const dayNumberElem = document.createElement('div');
            dayNumberElem.textContent = dayNumber;
            dayNumberElem.style.fontWeight = 'bold';
            dayNumberElem.style.position = 'absolute';
            dayNumberElem.style.top = '4px';
            dayNumberElem.style.left = '8px';

            dayCell.appendChild(dayNumberElem);

            if (cellDate.toDateString() === new Date().toDateString()) {
                dayCell.classList.add('bg-green-200', 'text-green-800');
            }

            if (holidays[dateString] && currentMonth) {
                dayCell.classList.add('holiday', 'bg-red-100', 'text-red-700', 'rounded', 'cursor-pointer');
                dayCell.setAttribute('data-holiday', holidays[dateString]);

                const holidayLabel = document.createElement('div');
                holidayLabel.textContent = holidays[dateString];
                holidayLabel.title = holidays[dateString];
                holidayLabel.style.fontSize = '12px';
                holidayLabel.style.whiteSpace = 'nowrap';
                holidayLabel.style.overflow = 'hidden';
                holidayLabel.style.textOverflow = 'ellipsis';
                holidayLabel.style.maxWidth = '80px';
                holidayLabel.style.marginTop = '15px';
                holidayLabel.style.color = '#c53030';

                dayCell.appendChild(holidayLabel);
            } else {
                dayCell.classList.add('hover:bg-green-100', 'rounded', 'cursor-pointer');
            }

            dayCell.addEventListener('click', function() {
                if (currentMonth) {
                    openCalendarModal(this);
                }
            });

            calendarDays.appendChild(dayCell);
        }
    }

    // Add event listeners for calendar navigation
        document.getElementById('prevMonth').addEventListener('click', () => {
        currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            renderCalendar(currentDate);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
        currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
        renderCalendar(currentDate);
    });

    // Initial calendar render
            renderCalendar(currentDate);

    function openCalendarModal(cell) {
        const modal = document.getElementById('calendarCellModal');
        const overlay = document.getElementById('calendarCellModalOverlay');
        const date = cell.getAttribute('data-date');
        const isHoliday = cell.classList.contains('holiday');
        const holidayText = cell.getAttribute('data-holiday') || '';
        
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        document.getElementById('modalDateTitle').textContent = `Attendance Records - ${formattedDate}`;
        document.getElementById('modalHolidayText').textContent = holidayText;

        if (isHoliday) {
            document.getElementById('holidayMessage').classList.remove('hidden');
            document.getElementById('attendanceStats').classList.add('hidden');
            document.getElementById('attendanceDetailsSection').classList.add('hidden');
            document.getElementById('noRecordsMessage').classList.add('hidden');
            document.getElementById('holidayDescription').textContent = holidayText;
        } else {
            document.getElementById('holidayMessage').classList.add('hidden');
            document.getElementById('attendanceStats').classList.remove('hidden');
            document.getElementById('attendanceDetailsSection').classList.remove('hidden');
            document.getElementById('noRecordsMessage').classList.add('hidden');

            const attendanceData = {
                timeIn: 5,
                timeOut: 3,
                late: 2,
                details: [
                    { employee: 'John Doe', timeIn: '08:00 AM', timeOut: '05:00 PM', status: 'On Time' },
                    { employee: 'Jane Smith', timeIn: '08:30 AM', timeOut: '05:00 PM', status: 'Late' },
                    { employee: 'Mike Johnson', timeIn: '08:00 AM', timeOut: '05:00 PM', status: 'On Time' }
                ]
            };

            document.getElementById('timeInCount').textContent = attendanceData.timeIn;
            document.getElementById('timeOutCount').textContent = attendanceData.timeOut;
            document.getElementById('lateCount').textContent = attendanceData.late;

            const tbody = document.getElementById('attendanceDetails');
            tbody.innerHTML = '';
            
            if (attendanceData.details.length > 0) {
                attendanceData.details.forEach(detail => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="py-2 px-4">${detail.employee}</td>
                        <td class="py-2 px-4">${detail.timeIn}</td>
                        <td class="py-2 px-4">${detail.timeOut}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded-full text-xs ${
                                detail.status === 'On Time' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }">
                                ${detail.status}
                            </span>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                document.getElementById('attendanceStats').classList.add('hidden');
                document.getElementById('attendanceDetailsSection').classList.add('hidden');
                document.getElementById('noRecordsMessage').classList.remove('hidden');
            }
        }

        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0', 'scale-75');
            modal.classList.add('opacity-100', 'scale-100');
            overlay.classList.add('opacity-100');
        }, 10);
    }

    function closeCalendarModal() {
        const modal = document.getElementById('calendarCellModal');
        const overlay = document.getElementById('calendarCellModalOverlay');
        
        modal.classList.add('opacity-0', 'scale-75');
        modal.classList.remove('opacity-100', 'scale-100');
        overlay.classList.remove('opacity-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
        }, 300);
    }

    document.getElementById('closeCalendarModal').addEventListener('click', closeCalendarModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCalendarModal();
        }
    });

        