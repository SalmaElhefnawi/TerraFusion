document.addEventListener('DOMContentLoaded', function() {
    
    // Select the reservation form
    const bookingForm = document.querySelector('form[action*="book-a-table.php"]');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default PHP submission

            const submitBtn = bookingForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            const loadingDiv = bookingForm.querySelector('.loading');
            const sentMessageDiv = bookingForm.querySelector('.sent-message');
            const errorMessageDiv = bookingForm.querySelector('.error-message');

            // Reset messages
            if(sentMessageDiv) sentMessageDiv.style.display = 'none';
            if(errorMessageDiv) errorMessageDiv.style.display = 'none';
            if(loadingDiv) loadingDiv.style.display = 'block';

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Booking...';

            const formData = new FormData(bookingForm);

            fetch(bookingForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Success! Show SweetAlert
                    Swal.fire({
                        title: 'Reservation Confirmed!',
                        html: '<p>Your table has been successfully booked.</p><p style="font-size: 0.9em; color: #555;">Please check your email (<strong>' + formData.get('email') + '</strong>) for your confirmation and QR Code.</p>',
                        icon: 'success',
                        confirmButtonColor: '#cda45e',
                        confirmButtonText: 'Great!'
                    });

                    // Reset form
                    bookingForm.reset();
                    if(loadingDiv) loadingDiv.style.display = 'none';
                    if(sentMessageDiv) {
                        sentMessageDiv.textContent = data.message;
                        sentMessageDiv.style.display = 'block';
                    }
                } else {
                    // Backend reported error
                    throw new Error(data.message || 'Form submission failed.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show Error Alert
                Swal.fire({
                    title: 'Booking Failed',
                    text: error.message || 'Something went wrong. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });

                if(loadingDiv) loadingDiv.style.display = 'none';
                if(errorMessageDiv) {
                    errorMessageDiv.textContent = error.message;
                    errorMessageDiv.style.display = 'block';
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
});
