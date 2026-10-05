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
        document.getElementById('emailForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(document.getElementById('emailForm'));
            const messageDiv = document.getElementById('message');

            try {
                const response = await fetch('submit.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Response Status:', response.status);
                console.log('Response OK:', response.ok);

                const responseText = await response.text();
                console.log('Response Text:', responseText);

                if (!response.ok) {
                    console.error('HTTP Error:', response.status, response.statusText);
                    messageDiv.className = 'message error';
                    messageDiv.textContent = `HTTP Error ${response.status}: ${response.statusText}`;
                    return;
                }

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Invalid JSON response:', responseText);
                    messageDiv.className = 'message error';
                    messageDiv.textContent = 'Invalid server response. Check console for details.';
                    return;
                }

                console.log('Parsed Data:', data);
                messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
                messageDiv.textContent = data.message;

                if (data.success) {
                    document.getElementById('emailForm').reset();
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                messageDiv.className = 'message error';
                messageDiv.textContent = 'An error occurred. Check console for details.';
            }
        });
    </script>
</body>
</html>