<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - M.A.T</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-content {
            max-width: 600px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-message">🎯 Objectif non trouvé !</h1>
            <p>La page que vous recherchez semble avoir été évacuée du terrain...</p>
            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn btn-primary btn-lg">Retour à la base</a>
            </div>
        </div>
    </div>
</body>
</html>
