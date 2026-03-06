<html>
<head>
    <title>Template</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="main-wrapper">
                <h1>Template</h1>
                <div class="nav-login">
                    <?= "Hello, " . htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') . "!" ?>
                </div>
            </div>
        </nav>
    </header>
</html>
