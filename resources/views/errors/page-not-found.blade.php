<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            line-height: 1;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 12px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .button-secondary {
            display: inline-block;
            margin-left: 10px;
            padding: 12px 40px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .button-secondary:hover {
            background: #e0e0e0;
        }

        .button-group {
            margin-top: 30px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 20px;
            }

            .error-code {
                font-size: 80px;
            }

            h1 {
                font-size: 24px;
            }

            .button,
            .button-secondary {
                display: block;
                margin: 10px auto;
                width: 100%;
                max-width: 300px;
            }

            .button-secondary {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔍</div>
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>Sorry, the page you are looking for does not exist. The login route is not available. Please use the customer or admin login options instead.</p>
        <div class="button-group">
            <a href="{{ route('home') }}" class="button">← Go Home</a>
            <a href="{{ route('admin.login') }}" class="button-secondary">Admin Login</a>
        </div>
    </div>
</body>
</html>
