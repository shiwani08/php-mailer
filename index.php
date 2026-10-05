<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Sender</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="email-form-wrapper">
            <h1>Send Email</h1>
            <form id="emailForm" method="POST" action="submit.php">
                <input
                    type="email"
                    name="recipient_email"
                    class="email-input"
                    placeholder="Enter recipient email address"
                    required
                >
                <button type="submit" class="submit-btn">Send Email</button>
            </form>
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
        const form = document.getElementById('emailForm');
        const messageDiv = document.getElementById('message');

        function showMessage(text, isSuccess) {
            messageDiv.textContent = text;
            messageDiv.className = `message ${isSuccess ? 'success' : 'error'}`;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const response = await fetch('submit.php', {
                    method: 'POST',
                    body: new FormData(form)
                });

                console.log(`Response: ${response.status} ${response.statusText}`);

                const responseText = await response.text();
                console.log('Response:', responseText);

                if (!response.ok) {
                    showMessage(`HTTP Error ${response.status}`, false);
                    return;
                }

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Invalid JSON:', responseText);
                    showMessage('Invalid server response. Check console.', false);
                    return;
                }

                showMessage(data.message, data.success);

                if (data.success) {
                    form.reset();
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('An error occurred. Check console.', false);
            }
        });
    </script>
</body>
</html>