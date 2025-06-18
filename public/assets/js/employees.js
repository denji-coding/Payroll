 
// Preview employee photo before submission
function previewEmployeePhoto(event) {
    const input = event.target;
    const preview = document.getElementById('employeePhotoPreview');
    const placeholder = document.getElementById('photoPlaceholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Display the uploaded photo file name
function displayFileName(input) {
    const fileNameSpan = document.getElementById('photoFileName');
    fileNameSpan.textContent = input.files?.[0]?.name || '';
}

function resetAddEmployeeForm() {
    const form = document.getElementById('addEmployeeForm');
    if (!form) return;

    // Reset all form fields
    form.reset();

    // Reset photo preview and placeholder
    const preview = document.getElementById('employeePhotoPreview');
    const placeholder = document.getElementById('photoPlaceholder');
    const fileName = document.getElementById('photoFileName');

    if (preview) {
        preview.style.display = 'none'; // Hide image preview
        preview.src = '';               // Clear the src
    }

    if (placeholder) {
        placeholder.style.display = 'flex'; // Show the original placeholder
    }

    if (fileName) {
        fileName.textContent = ''; // Clear filename display
    }

    // Clear validation messages and red borders
    form.querySelectorAll('.validation-message').forEach(el => el.textContent = '');
    form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
}

// Add employee form submission
document.addEventListener('DOMContentLoaded', function () {

//     AOS.init({
//     duration: 800,       // Animation duration (ms)
//     easing: 'ease-in-out', // Animation easing
//     once: true           // Animate only once
//   });

    const generateIdBtn = document.getElementById('generateIdBtn');
    const employeeIdInput = document.getElementById('employeeId');

    function generateUniqueEmployeeId() {
    const randomDigits = Math.floor(100000 + Math.random() * 900000);
    return `EMP-${randomDigits}`;
    }

    // Generate only when button is clicked
    // generateIdBtn.addEventListener('click', () => {
    // const generatedId = generateUniqueEmployeeId();
    // employeeIdInput.value = generatedId;
    // employeeIdInput.focus();
    // });


    const form = document.getElementById('addEmployeeForm');
    if (!form) return;

    // Reset form when modal is closed
    const modalEl = document.getElementById('addProducts');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', resetAddEmployeeForm);
    }

    // Reset form on cancel button click (if present)
    document.querySelectorAll('.cancelBtn').forEach(btn => {
        btn.addEventListener('click', resetAddEmployeeForm);
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Invalid JSON:", text);
                throw new Error("The server did not return JSON.");
            }

            if (data.status === 'success') {
                await Swal.fire({
                    icon: data.icon || 'success',
                    title: data.title || 'Success!',
                    text: data.message || 'Employee added successfully!',
                    timer: 1000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                const modal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
                if (modal) modal.hide();
                window.location.reload();

            } else if (data.status === 'error') {
                await Swal.fire({
                    icon: data.icon || 'error',
                    title: data.title || 'Error',
                    text: data.message || 'Some required fields are duplicated.',
                    confirmButtonColor: '#d33'
                });
            }

        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: error.message || 'Something went wrong.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// Validate RFID number (digits only)
function validateRfidNumber(input) {
    input.value = input.value.replace(/\D/g, '');
    const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
    if (messageDiv) {
        messageDiv.textContent = '';
        input.classList.remove("border-red-500");
    }
}

// Validate contact number
function validateContactNumber(input) {
    const pattern = /^09\d{9}$/;
    const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
    if (!messageDiv) return;

    input.value = input.value.replace(/\D/g, '');

    if (input.value === "") {
        messageDiv.textContent = "";
        input.classList.remove("border-red-500");
        return;
    }

    input.addEventListener('blur', () => {
        if (!pattern.test(input.value)) {
            messageDiv.textContent = "Phone number must start with '09' and be exactly 11 digits.";
            input.classList.add("border-red-500");

            Swal.fire({
                icon: 'error',
                title: 'Invalid Contact Number',
                text: messageDiv.textContent,
                confirmButtonColor: '#d33'
            });
        } else {
            messageDiv.textContent = "";
            input.classList.remove("border-red-500");
        }
    });
}

// Validate SSS, Pag-IBIG, PhilHealth, etc.
function validateInput(input) {
    const field = input.name;
    const raw = input.value.replace(/\D/g, '');
    input.value = raw;

    const messageDiv = input.closest('div.flex')?.querySelector('.validation-message');
    if (!messageDiv) return;

    let valid = false;
    let errorMessage = '';

    const requirements = {
        sssNumber: { length: 12, message: "SSS Number must be exactly 12 digits." },
        pagibigNumber: { length: 12, message: "PAG-IBIG Number must be exactly 12 digits." },
        philhealthNumber: { length: 12, message: "PhilHealth Number must be exactly 12 digits." }
    };

    if (requirements[field]) {
        const { length, message } = requirements[field];
        valid = raw.length === length;
        errorMessage = message;

        if (raw === "") {
            messageDiv.textContent = "";
            input.classList.remove("border-red-500");
        } else if (!valid) {
            messageDiv.textContent = errorMessage;
            input.classList.add("border-red-500");

            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: errorMessage,
                confirmButtonColor: '#d33'
            });
        } else {
            messageDiv.textContent = "";
            input.classList.remove("border-red-500");
        }

    } else if (input.type === "date" || input.tagName === "SELECT") {
        if (!input.value) {
            messageDiv.textContent = "This field is required.";
            input.classList.add("border-red-500");
        } else {
            messageDiv.textContent = "";
            input.classList.remove("border-red-500");
        }
    } else {
        messageDiv.textContent = "";
        input.classList.remove("border-red-500");
    }
}





