<?php
/**
 * @var array $users, $hotel
 * @var string $error, $success, $csrf, $title
 */
$roleBadges = [
  'admin'       => 'bg-gold-400/15 text-gold-600',
  'manager'     => 'bg-blue-100 text-blue-700',
  'reception'   => 'bg-purple-100 text-purple-700',
  'housekeeping'=> 'bg-amber-100 text-amber-700',
  'kitchen'     => 'bg-orange-100 text-orange-700',
  'agent'       => 'bg-gray-100 text-gray-700',
];
$roleLabels = [
  'admin'       => 'Administrateur',
  'manager'     => 'Manager',
  'reception'   => 'Réception',
  'housekeeping'=> 'Gouvernante',
  'kitchen'     => 'Cuisine',
  'agent'       => 'Agent',
];
$activeTab = 'users';
$userCount = count($users);
?>
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-900">Utilisateurs</h1>
  <p class="text-sm text-gray-500 mt-1">
    <?= $userCount ?> / <?= (int) $hotel['max_users'] ?> utilisateurs · Plan <?= ucfirst(e($hotel['plan'])) ?>
  </p>
</div>

<!-- Tabs -->
<div class="flex gap-1 mb-6 bg-white p-1 rounded-xl shadow-sm border border-gray-100 w-fit">
  <a href="/settings/hotel" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Hôtel</a>
  <a href="/settings/users" class="px-4 py-2 text-sm font-semibold rounded-lg <?= $activeTab === 'users' ? 'bg-navy-700 text-white' : 'text-gray-500 hover:text-navy-700' ?>">Utilisateurs</a>
  <a href="/settings/plan" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Abonnement</a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- Users list -->
  <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="text-sm font-bold text-navy-900">Équipe</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-gray-400 uppercase border-b border-gray-100 bg-gray-50">
            <th class="px-6 py-3 font-semibold">Nom</th>
            <th class="px-6 py-3 font-semibold">Email</th>
            <th class="px-6 py-3 font-semibold">Rôle</th>
            <th class="px-6 py-3 font-semibold">Créé le</th>
            <th class="px-6 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php if (empty($users)): ?>
            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aucun utilisateur.</td></tr>
          <?php else: ?>
            <?php foreach ($users as $u): ?>
              <?php $isMe = ((int) $u['id']) === (int) ($_SESSION['user']['id'] ?? 0); ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-navy-700 text-white flex items-center justify-center text-xs font-bold shrink-0">
                      <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="font-medium text-gray-800"><?= e($u['name']) ?> <?= $isMe ? '<span class="text-xs text-gray-400">(vous)</span>' : '' ?></div>
                      <?php if (!$u['is_active']): ?>
                        <div class="text-xs text-red-500">inactif</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-3 text-gray-600"><?= e($u['email']) ?></td>
                <td class="px-6 py-3">
                  <span class="inline-block px-2.5 py-1 text-xs rounded-full font-medium <?= $roleBadges[$u['role']] ?? $roleBadges['agent'] ?>">
                    <?= $roleLabels[$u['role']] ?? ucfirst($u['role']) ?>
                  </span>
                </td>
                <td class="px-6 py-3 text-gray-500 text-xs"><?= format_date($u['created_at']) ?></td>
                <td class="px-6 py-3 text-right">
                  <?php if ($isMe || $u['role'] === 'admin'): ?>
                    <span class="text-xs text-gray-300">—</span>
                  <?php else: ?>
                    <form method="POST" action="/settings/userDelete/<?= (int) $u['id'] ?>" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
                      <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Supprimer</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create form -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Nouvel utilisateur</h2>

    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-xs"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($userCount >= (int) $hotel['max_users']): ?>
      <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-xs">
        Limite d'utilisateurs atteinte. <a href="/settings/plan" class="font-semibold underline">Améliorer mon plan</a>.
      </div>
    <?php else: ?>
      <form method="POST" action="/settings/users" class="space-y-3">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Nom complet</label>
          <input type="text" name="name" required class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Rôle</label>
          <select name="role" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
            <?php foreach ($roleLabels as $key => $label): ?>
              <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Mot de passe</label>
          <input type="password" name="password" required minlength="<?= PASSWORD_MIN_LENGTH ?>" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
          <p class="text-[10px] text-gray-400 mt-1">Min <?= PASSWORD_MIN_LENGTH ?> caractères.</p>
        </div>
        <button type="submit" class="w-full py-2.5 rounded-lg bg-gold-400 text-white text-sm font-semibold hover:bg-gold-500 transition">Créer</button>
      </form>
    <?php endif; ?>
  </div>
</div>
