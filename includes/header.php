<header class="dashboard-header">

    <div class="welcome">

        <h1>Bonjour, <?php echo htmlspecialchars($user['nom_complet']); ?></h1>
        <p>Système de facturation - Supermarché</p>

    </div>

    <div class="user-info">

        <span class="badge"><?php echo htmlspecialchars($role); ?></span>
        <span class="date"><?php echo $dateJour; ?></span>

        <a href="auth/logout.php" class="btn-logout">Déconnexion</a>

    </div>

</header>