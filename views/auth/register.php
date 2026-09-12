<?php /** @var string $title, $error, $csrf */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
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

  <div class="relative z-10 w-full max-w-lg">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-lg mb-4">
        <span class="text-3xl font-bold text-navy-900">R</span>
      </div>
      <h1 class="text-2xl font-bold text-white">Créer votre compte</h1>
      <p class="text-sm text-white/60 mt-1">Démarrez gratuitement — Plan Starter offert</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/register" class="space-y-4">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'hôtel</label>
          <input type="text" name="hotel_name" required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 outline-none transition"
            placeholder="Ex: Hôtel Teranga">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Votre nom</label>
          <input type="text" name="name" required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 outline-none transition"
            placeholder="Ex: Awa Diop">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 outline-none transition"
            placeholder="admin@hotel.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe (min 6 caractères)</label>
          <input type="password" name="password" required minlength="6"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-navy-700 outline-none transition"
            placeholder="••••••••">
        </div>

        <!-- Plan info -->
        <div class="p-4 rounded-lg bg-navy-50 border border-navy-100">
          <div class="flex items-center justify-between">
            <div>
              <span class="font-semibold text-navy-900">Plan Starter</span>
              <span class="text-sm text-gray-500 ml-2">Gratuit</span>
            </div>
            <span class="text-sm text-gray-600">10 chambres · 2 utilisateurs</span>
          </div>
        </div>

        <button type="submit"
          class="w-full py-2.5 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-lg">
          Créer mon compte
        </button>
      </form>

      <div class="mt-6 text-center text-sm text-gray-600">
        Déjà inscrit ?
        <a href="/login" class="text-navy-700 font-semibold hover:underline">Se connecter</a>
      </div>
    </div>
  </div>
</body>
</html>
