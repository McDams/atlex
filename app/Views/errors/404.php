<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page introuvable | ATLÉX-SPORT</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#0a0e1a; color:#fff; font-family:'Montserrat',sans-serif; text-align:center; }
        h1 { font-family:'Bebas Neue',sans-serif; font-size:8rem; color:#E53935; margin:0; letter-spacing:.1em; }
        a { color:#fff; background:#E53935; padding:.75rem 2rem; text-decoration:none; text-transform:uppercase; font-weight:700; letter-spacing:.1em; display:inline-block; margin-top:1.5rem; }
    </style>
</head>
<body>
    <div>
        <h1>404</h1>
        <p>Cette page n'existe pas ou a été déplacée.</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>">Retour à l'accueil</a>
    </div>
</body>
</html>
