<?php
/**
 * @var array $hotel, $plans, $currentPlan, $roomsCount, $usersCount, $title
 */
$activeTab = 'plan';
$currentPlanInfo = $plans[$currentPlan] ?? null;

$planFeatures = [
  'starter'  => ['10 chambres', '2 utilisateurs', 'QR codes désactivés', 'Support email'],
  'pro'      => ['50 chambres', '5 utilisateurs', 'QR codes chambres', 'Support prioritaire'],
  'business' => ['Chambres illimitées', '20 utilisateurs', 'QR codes + branding', 'Support dédié'],
];
$planIcons = ['starter' => '🌱', 'pro' => '⭐', 'business' => '🏢'];
?>
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-900">Abonnement</h1>
  <p class="text-sm text-gray-500 mt-1">Gérez votre plan RoomScan</p>
</div>

<!-- Tabs -->
<div class="flex gap-1 mb-6 bg-white p-1 rounded-xl shadow-sm border border-gray-100 w-fit">
  <a href="/settings/hotel" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Hôtel</a>
  <a href="/settings/users" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Utilisateurs</a>
  <a href="/settings/plan" class="px-4 py-2 text-sm font-semibold rounded-lg <?= $activeTab === 'plan' ? 'bg-navy-700 text-white' : 'text-gray-500 hover:text-navy-700' ?>">Abonnement</a>
</div>

<!-- Current plan card -->
<div class="bg-gradient-to-br from-navy-900 to-navy-700 rounded-2xl p-6 text-white mb-6 shadow-sm">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <div class="text-xs uppercase tracking-wide text-white/60">Plan actuel</div>
      <div class="text-3xl font-bold mt-1 flex items-center gap-2">
        <span><?= $planIcons[$currentPlan] ?? '•' ?></span>
        <?= ucfirst(e($currentPlan)) ?>
      </div>
      <div class="text-sm text-white/70 mt-1">
        <?php if ($currentPlanInfo): ?>
          <?= $currentPlanInfo['price'] ?> € / mois
        <?php endif; ?>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div class="bg-white/10 rounded-xl p-3 min-w-[120px]">
        <div class="text-xs text-white/60 uppercase tracking-wide">Chambres</div>
        <div class="text-xl font-bold mt-1"><?= $roomsCount ?> / <?= (int) $hotel['max_rooms'] ?></div>
        <div class="mt-2 h-1.5 bg-white/20 rounded-full overflow-hidden">
          <div class="h-full bg-gold-400 rounded-full" style="width: <?= $hotel['max_rooms'] > 0 ? min(100, ($roomsCount / max(1, $hotel['max_rooms'])) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="bg-white/10 rounded-xl p-3 min-w-[120px]">
        <div class="text-xs text-white/60 uppercase tracking-wide">Utilisateurs</div>
        <div class="text-xl font-bold mt-1"><?= $usersCount ?> / <?= (int) $hotel['max_users'] ?></div>
        <div class="mt-2 h-1.5 bg-white/20 rounded-full overflow-hidden">
          <div class="h-full bg-gold-400 rounded-full" style="width: <?= $hotel['max_users'] > 0 ? min(100, ($usersCount / max(1, $hotel['max_users'])) * 100) : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Plan comparison -->
<div>
  <h2 class="text-sm font-bold text-navy-900 mb-4">Comparer les plans</h2>
  <div class="grid md:grid-cols-3 gap-4">
    <?php foreach ($plans as $key => $plan): ?>
      <?php $isCurrent = ($key === $currentPlan); ?>
      <div class="bg-white rounded-2xl p-6 shadow-sm border-2 <?= $isCurrent ? 'border-gold-400' : 'border-gray-100' ?> relative">
        <?php if ($isCurrent): ?>
          <div class="absolute -top-2 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-gold-400 text-white text-[10px] uppercase tracking-wide font-bold rounded-full">Actuel</div>
        <?php endif; ?>
        <div class="text-3xl mb-2"><?= $planIcons[$key] ?? '•' ?></div>
        <div class="text-lg font-bold text-navy-900"><?= ucfirst($key) ?></div>
        <div class="text-2xl font-bold text-navy-900 mt-1"><?= $plan['price'] ?> €<span class="text-sm font-normal text-gray-400"> / mois</span></div>

        <ul class="mt-4 space-y-2 text-sm text-gray-600">
          <?php foreach ($planFeatures[$key] ?? [] as $feature): ?>
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <?= e($feature) ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <button type="button"
                onclick="alert('Contactez-nous pour upgrader votre plan.\\nEmail: contact@roomscan.app')"
                class="w-full mt-6 py-2.5 rounded-lg text-sm font-semibold transition <?= $isCurrent ? 'bg-gray-100 text-gray-400 cursor-default' : 'bg-gold-400 text-white hover:bg-gold-500' ?>"
                <?= $isCurrent ? 'disabled' : '' ?>>
          <?= $isCurrent ? 'Plan actuel' : 'Upgrader' ?>
        </button>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
    <div class="font-semibold mb-1">Besoin d'aide pour upgrader ?</div>
    Contactez-nous pour upgrader — <a href="mailto:contact@roomscan.app" class="underline font-medium">contact@roomscan.app</a>
  </div>
</div>
