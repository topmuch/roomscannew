<?php
/** @var string $title */
$user = current_user();
$nav = [
  ['href' => '/dashboard', 'label' => 'Tableau de bord', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
  ['href' => '/reservations', 'label' => 'Réservations', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
  ['href' => '/rooms', 'label' => 'Chambres', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3'],
  ['href' => '/guests', 'label' => 'Clients', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H5a2 2 0 01-2-2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
  ['href' => '/housekeeping', 'label' => 'Gouvernante', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 9h-6L8 4z'],
  ['href' => '/roomservice', 'label' => 'Room Service', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
  ['href' => '/menu', 'label' => 'Menu', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
  ['href' => '/lostfound', 'label' => 'Objets trouvés', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
  ['href' => '/invoices', 'label' => 'Factures', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
  ['href' => '/settings', 'label' => 'Paramètres', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
];
$currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
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
        navy: { 50:'#f0f4ff',100:'#e0e7ff',900:'#0c1f4a',800:'#14306e',700:'#1e3a8a'},
        gold: { 400:'#ca8a04',500:'#d4a017',600:'#b8860b'}
      }}}
    }
  </script>
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="hidden lg:flex w-64 flex-col bg-navy-900 text-white shrink-0">
      <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shrink-0">
          <span class="text-xl font-bold text-navy-900">R</span>
        </div>
        <div>
          <div class="font-bold text-sm">RoomScan</div>
          <div class="text-[10px] text-white/50 uppercase tracking-wide"><?= e($user['hotel_name'] ?? '') ?></div>
        </div>
      </div>
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <?php foreach ($nav as $item): ?>
          <a href="<?= $item['href'] ?>"
             class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
             <?= str_starts_with($currentPage, $item['href']) && $item['href'] !== '/dashboard'
                ? 'bg-white/15 text-white'
                : ($currentPage === $item['href']
                  ? 'bg-white/15 text-white'
                  : 'text-white/60 hover:text-white hover:bg-white/10') ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
            </svg>
            <?= e($item['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <div class="p-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg bg-white/5 mb-2">
          <div class="w-8 h-8 rounded-full bg-gold-400 flex items-center justify-center text-xs font-bold">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-medium truncate"><?= e($user['name'] ?? '') ?></div>
            <div class="text-[10px] text-white/50 truncate"><?= e($user['email'] ?? '') ?></div>
          </div>
        </div>
        <a href="/logout" class="block px-3 py-2 text-sm text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition">
          Déconnexion
        </a>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Mobile header -->
      <div class="lg:hidden bg-navy-900 text-white p-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center"><span class="font-bold text-navy-900 text-sm">R</span></div>
          <span class="font-bold">RoomScan</span>
        </div>
        <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
      <div id="mobile-nav" class="hidden lg:hidden bg-navy-900 text-white p-3 space-y-1">
        <?php foreach ($nav as $item): ?>
          <a href="<?= $item['href'] ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10"><?= e($item['label']) ?></a>
        <?php endforeach; ?>
        <a href="/logout" class="block px-3 py-2 rounded-lg text-sm text-red-400 hover:bg-red-500/10">Déconnexion</a>
      </div>

      <!-- Page content -->
      <main class="flex-1 p-6 lg:p-8 max-w-7xl mx-auto w-full">
