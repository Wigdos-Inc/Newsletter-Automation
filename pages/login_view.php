<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styling/main.css">
</head>
<body>
    <div class="container text-center m-5">
        <h2 class="mb-5 fw-bold">Login</h2>
        <?php if(isset($_GET['err'])){ ?>
            <h3 id="error" class="text-danger">Error: <?=$_GET['err']?></h3>
        <?php }?>
        <form class="rounded p-4 bg-purple-dark w-50 d-inline-block shadow" action="../scripts/db/account/login.php" method="post">
            <div class="form-floating mb-3 mt-5">
                <input class="form-control" type="text" name="username" id="username" placeholder="Username ...">
                <label for="username">Username ...</label>
            </div>
            <div class="form-floating mb-5">
                <input class="form-control" type="password" name="password" id="password" placeholder="Password ...">
                <label for="password">Password ...</label>
            </div>
            <input class="btn btn-secondary mb-3 mt-5 fw-bold" type="submit" value="Login">
        </form>
    </div>

    <script src="../bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Listen for theme changes from parent window
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'themeChange') {
                const theme = event.data.theme;
                if (theme === 'light') {
                    document.body.classList.add('light-mode');
                } else {
                    document.body.classList.remove('light-mode');
                }
            }
        });

        // Apply saved theme on load
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
        }
    </script>
</body>
</html>
