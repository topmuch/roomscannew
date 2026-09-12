<?php /** @var string $title, $error, $csrf */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link rel="icon" href="/public/favicon.ico">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: {
        navy: { 50:'#f0f4ff',900:'#0c1f4a',800:'#14306e',700:'#1e3a8a'},
        gold: { 400:'#ca8a04',500:'#d4a017',600:'#b8860b'}
      }}}
    }
  </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-navy-900 via-navy-800 to-navy-900 px-4">
  <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 25% 25%, #ca8a04 1px, transparent 1px);background-size:30px 30px"></div>

  <div class="relative z-10 w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-lg mb-4">
        <span class="text-3xl font-bold text-navy-900">R</span>
      </div>
      <h1 class="text-2xl font-bold text-white">RoomScan</h1>
      <p class="text-sm text-white/60 mt-1">SaaS de gestion hôtelière</p>
    </div>

    <!-- Form card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/login" class="space-y-4">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required autofocus
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none transition"
            placeholder="admin@hotel.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input type="password" name="password" required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none transition"
            placeholder="••••••••">
        </div>
        <button type="submit"
          class="w-full py-2.5 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-lg">
          Se connecter
        </button>
      </form>

      <div class="mt-6 text-center text-sm text-gray-600">
        Pas encore de compte ?
        <a href="/register" class="text-navy-700 font-semibold hover:underline">Créer un compte</a>
      </div>
    </div>

    <p class="text-center text-xs text-white/40 mt-6">© <?= date('Y') ?> RoomScan — Gestion hôtelière SaaS</p>
  </div>
</body>
</html>
