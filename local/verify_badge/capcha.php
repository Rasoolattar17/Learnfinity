<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invisible reCAPTCHA Verification</title>
    <script src="https://www.google.com/recaptcha/api.js?render=YOUR_PUBLIC_KEY"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        .status {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Invisible reCAPTCHA Verification</h2>
    <p>Verifying reCAPTCHA on page load...</p>
    <div id="status" class="status">Processing...</div>

    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfkOQArAAAAAKgMDfo8fFQrEMIgbJMZGiuoOReB', { action: 'verify' }).then(function(token) {
                fetch('verify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'token=' + token
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('status').innerText = data.success ? "Verified Successfully!" : "Verification Failed!";
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>
