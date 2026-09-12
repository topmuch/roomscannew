<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RoomScan — SaaS de Gestion Hôtelière</title>
  <meta name="description" content="RoomScan — Gérez votre hôtel avec QR codes chambre, réservations, gouvernante, room service et objets trouvés.">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: {
      navy: { 50:'#f0f4ff',100:'#e0e7ff',900:'#0c1f4a',800:'#14306e',700:'#1e3a8a'},
      gold: { 400:'#ca8a04',500:'#d4a017',600:'#b8860b'}
    }}}
  </script>
</head>
<body class="bg-white">

  <!-- Nav -->
  <nav class="bg-navy-900 text-white py-4 px-6 sticky top-0 z-50 shadow-lg">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shrink-0">
          <span class="text-xl font-bold text-navy-900">R</span>
        </div>
        <span class="font-bold text-lg">RoomScan</span>
      </div>
      <div class="hidden md:flex items-center gap-6 text-sm">
        <a href="#features" class="text-white/70 hover:text-white transition">Fonctionnalités</a>
        <a href="#pricing" class="text-white/70 hover:text-white transition">Tarifs</a>
        <a href="/login" class="text-white/70 hover:text-white transition">Connexion</a>
        <a href="/register" class="px-4 py-2 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition">Essai gratuit</a>
      </div>
      <a href="/register" class="md:hidden px-3 py-1.5 rounded-lg bg-gold-400 text-white text-sm font-semibold">Essai</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="bg-navy-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 25% 25%, #ca8a04 1px, transparent 1px);background-size:30px 30px"></div>
    <div class="max-w-6xl mx-auto px-6 py-20 lg:py-28 relative z-10 text-center">
      <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium text-white/80 mb-6 backdrop-blur-sm border border-white/20">
        🏨 SaaS de gestion hôtelière multi-tenant
      </div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
        Gérez votre hôtel<br>
        <span class="text-gold-400">avec des QR codes</span>
      </h1>
      <p class="mt-6 text-lg text-white/70 max-w-2xl mx-auto leading-relaxed">
        Réservations, chambres, gouvernante, room service, objets trouvés —
        le tout piloté par QR code. Vos clients scannent, tout se gère automatiquement.
      </p>
      <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/register" class="px-8 py-3.5 rounded-xl bg-gold-400 text-white font-bold hover:bg-gold-500 transition shadow-lg text-lg">
          Démarrer gratuitement
        </a>
        <a href="#features" class="px-8 py-3.5 rounded-xl bg-white/10 text-white font-semibold hover:bg-white/20 transition border border-white/30 backdrop-blur-sm text-lg">
          Voir les fonctionnalités
        </a>
      </div>
      <div class="mt-12 flex flex-wrap justify-center gap-6 text-sm text-white/50">
        <span>✓ Sans installation</span>
        <span>✓ QR codes chambre inclus</span>
        <span>✓ Multi-hôtel</span>
        <span>✓ FR / EN</span>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold text-navy-900">Tout ce dont votre hôtel a besoin</h2>
        <p class="mt-4 text-gray-500 max-w-2xl mx-auto">Une plateforme complète pour gérer chaque aspect de votre établissement.</p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4 text-2xl">📅</div>
          <h3 class="font-bold text-navy-900 mb-2">Réservations</h3>
          <p class="text-sm text-gray-500">Booking, calendrier, check-in/out, tarifs et disponibilités en temps réel.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center mb-4 text-2xl">🔑</div>
          <h3 class="font-bold text-navy-900 mb-2">Chambres & QR Codes</h3>
          <p class="text-sm text-gray-500">Gestion chambres, types, statuts. QR code unique par chambre pour services.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mb-4 text-2xl">👥</div>
          <h3 class="font-bold text-navy-900 mb-2">CRM Clients</h3>
          <p class="text-sm text-gray-500">Historique séjours, préférences, VIP, total dépensé par client.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mb-4 text-2xl">🧹</div>
          <h3 class="font-bold text-navy-900 mb-2">Gouvernante</h3>
          <p class="text-sm text-gray-500">Femmes de ménage scannent le QR code → checklist de nettoyage par chambre.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center mb-4 text-2xl">🍔</div>
          <h3 class="font-bold text-navy-900 mb-2">Room Service</h3>
          <p class="text-sm text-gray-500">Menu interactif, commandes via QR code, suivi statut en temps réel.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-cyan-50 flex items-center justify-center mb-4 text-2xl">🔍</div>
          <h3 class="font-bold text-navy-900 mb-2">Objets Trouvés</h3>
          <p class="text-sm text-gray-500">Signalement par QR code avec photo. Suivi lost & found complet.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-4 text-2xl">🧾</div>
          <h3 class="font-bold text-navy-900 mb-2">Factures</h3>
          <p class="text-sm text-gray-500">Génération, impression, suivi paiements. Export comptable.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 text-2xl">📊</div>
          <h3 class="font-bold text-navy-900 mb-2">Statistiques</h3>
          <p class="text-sm text-gray-500">Taux d'occupation, revenus, check-ins du jour, performance.</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all">
          <div class="w-12 h-12 rounded-xl bg-gold-400/10 flex items-center justify-center mb-4 text-2xl">🌐</div>
          <h3 class="font-bold text-navy-900 mb-2">Multi-Hôtel</h3>
          <p class="text-sm text-gray-500">Gérez plusieurs établissements depuis un seul compte. SaaS multi-tenant.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- QR Code highlight -->
  <section class="py-20 bg-navy-900 text-white">
    <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <h2 class="text-3xl sm:text-4xl font-bold mb-6">3 QR codes par chambre</h2>
        <p class="text-white/70 leading-relaxed mb-8">
          Chaque chambre dispose de 3 QR codes à imprimer et afficher :
          le client scanne pour commander, la femme de ménage scanne pour nettoyer,
          et tout le monde peut signaler un objet trouvé.
        </p>
        <div class="space-y-4">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-gold-400 flex items-center justify-center shrink-0 text-lg">📱</div>
            <div>
              <h3 class="font-semibold">QR Code Client</h3>
              <p class="text-sm text-white/60">Room service, ménage, maintenance, infos hôtel (WiFi, check-in/out)</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center shrink-0 text-lg">🧹</div>
            <div>
              <h3 class="font-semibold">QR Code Nettoyage (Staff)</h3>
              <p class="text-sm text-white/60">La femme de ménage scanne → checklist de 8 tâches → chambre disponible</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-cyan-500 flex items-center justify-center shrink-0 text-lg">🔍</div>
            <div>
              <h3 class="font-semibold">QR Code Objets Trouvés</h3>
              <p class="text-sm text-white/60">Signaler un objet perdu/trouvé avec photo directement depuis la chambre</p>
            </div>
          </div>
        </div>
      </div>
      <div class="flex justify-center gap-6">
        <div class="text-center">
          <div class="bg-white p-3 rounded-2xl shadow-xl">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=roomscan.dev/room/demo" alt="QR Client" class="rounded-lg">
          </div>
          <p class="text-xs text-white/50 mt-2">Client</p>
        </div>
        <div class="text-center">
          <div class="bg-white p-3 rounded-2xl shadow-xl">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=roomscan.dev/clean/demo" alt="QR Clean" class="rounded-lg">
          </div>
          <p class="text-xs text-white/50 mt-2">Nettoyage</p>
        </div>
        <div class="text-center">
          <div class="bg-white p-3 rounded-2xl shadow-xl">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=roomscan.dev/found/demo" alt="QR Found" class="rounded-lg">
          </div>
          <p class="text-xs text-white/50 mt-2">Objets</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing -->
  <section id="pricing" class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold text-navy-900">Tarifs simples</h2>
        <p class="mt-4 text-gray-500">Commencez gratuitement. Évoluez quand vous voulez.</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <!-- Starter -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
          <h3 class="font-bold text-navy-900 text-lg">Starter</h3>
          <div class="mt-4"><span class="text-4xl font-bold text-navy-900">15€</span><span class="text-gray-400">/mois</span></div>
          <ul class="mt-6 space-y-3 text-sm text-gray-600">
            <li>✓ 10 chambres max</li>
            <li>✓ 2 utilisateurs</li>
            <li>✓ Réservations & clients</li>
            <li>✗ QR codes chambre</li>
            <li>✗ Room service</li>
          </ul>
          <a href="/register" class="mt-8 block text-center py-3 rounded-xl bg-gray-100 text-navy-700 font-semibold hover:bg-gray-200 transition">Commencer</a>
        </div>
        <!-- Pro (highlighted) -->
        <div class="bg-white rounded-2xl p-8 shadow-xl border-2 border-gold-400 relative lg:scale-105">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gold-400 text-white text-xs font-bold">★ POPULAIRE</div>
          <h3 class="font-bold text-navy-900 text-lg">Pro</h3>
          <div class="mt-4"><span class="text-4xl font-bold text-navy-900">39€</span><span class="text-gray-400">/mois</span></div>
          <ul class="mt-6 space-y-3 text-sm text-gray-600">
            <li>✓ 50 chambres max</li>
            <li>✓ 5 utilisateurs</li>
            <li>✓ Réservations & clients</li>
            <li>✓ QR codes chambre (3 par chambre)</li>
            <li>✓ Room service + menu</li>
            <li>✓ Gouvernante + objets trouvés</li>
          </ul>
          <a href="/register" class="mt-8 block text-center py-3 rounded-xl bg-gold-400 text-white font-bold hover:bg-gold-500 transition shadow-lg">Commencer</a>
        </div>
        <!-- Business -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
          <h3 class="font-bold text-navy-900 text-lg">Business</h3>
          <div class="mt-4"><span class="text-4xl font-bold text-navy-900">89€</span><span class="text-gray-400">/mois</span></div>
          <ul class="mt-6 space-y-3 text-sm text-gray-600">
            <li>✓ Chambres illimitées</li>
            <li>✓ 20 utilisateurs</li>
            <li>✓ Tout le plan Pro</li>
            <li>✓ Multi-hôtel</li>
            <li>✓ Factures avancées</li>
            <li>✓ Support prioritaire</li>
          </ul>
          <a href="/register" class="mt-8 block text-center py-3 rounded-xl bg-gray-100 text-navy-700 font-semibold hover:bg-gray-200 transition">Commencer</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-16 bg-navy-900 text-white text-center">
    <div class="max-w-2xl mx-auto px-6">
      <h2 class="text-3xl font-bold">Prêt à digitaliser votre hôtel ?</h2>
      <p class="mt-4 text-white/70">Rejoignez les hôtels qui ont déjà choisi RoomScan.</p>
      <a href="/register" class="mt-8 inline-block px-8 py-3.5 rounded-xl bg-gold-400 text-white font-bold hover:bg-gold-500 transition shadow-lg text-lg">
        Créer mon compte gratuit
      </a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-navy-900 text-white/60 py-8 px-6 border-t border-white/10">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center"><span class="font-bold text-navy-900 text-sm">R</span></div>
        <span class="font-bold text-white">RoomScan</span>
      </div>
      <p class="text-xs">© <?= date('Y') ?> RoomScan — SaaS de gestion hôtelière</p>
      <div class="flex gap-4 text-sm">
        <a href="/login" class="hover:text-white transition">Connexion</a>
        <a href="/register" class="hover:text-white transition">Inscription</a>
      </div>
    </div>
  </footer>
</body>
</html>
