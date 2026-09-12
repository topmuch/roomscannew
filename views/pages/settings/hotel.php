<?php
/**
 * @var array $hotel
 * @var string $error, $success, $csrf, $title
 */
$activeTab = 'hotel';
?>
<div class="mb-6">
  <h1 class="text-2xl font-bold text-navy-900">Paramètres</h1>
  <p class="text-sm text-gray-500 mt-1">Configurez votre établissement</p>
</div>

<!-- Tabs -->
<div class="flex gap-1 mb-6 bg-white p-1 rounded-xl shadow-sm border border-gray-100 w-fit">
  <a href="/settings/hotel" class="px-4 py-2 text-sm font-semibold rounded-lg <?= $activeTab === 'hotel' ? 'bg-navy-700 text-white' : 'text-gray-500 hover:text-navy-700' ?>">Hôtel</a>
  <a href="/settings/users" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Utilisateurs</a>
  <a href="/settings/plan" class="px-4 py-2 text-sm font-semibold rounded-lg text-gray-500 hover:text-navy-700">Abonnement</a>
</div>

<?php if ($error): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm"><?= e($success) ?></div>
<?php endif; ?>

<form method="POST" action="/settings/hotel" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">

  <!-- Identity -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Identité</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nom de l'hôtel *</label>
        <input type="text" name="name" required value="<?= e($hotel['name']) ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
        <input type="email" name="email" required value="<?= e($hotel['email']) ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
        <input type="text" name="phone" value="<?= e($hotel['phone'] ?? '') ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Logo (jpg/png/webp, max 2 Mo)</label>
        <div class="flex items-center gap-3">
          <?php if (!empty($hotel['logo'])): ?>
            <img src="<?= e($hotel['logo']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
          <?php endif; ?>
          <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                 class="text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-navy-50 file:text-navy-700 file:font-semibold file:cursor-pointer">
        </div>
      </div>
    </div>
  </div>

  <!-- Location -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Adresse</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
        <textarea name="address" rows="2" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none"><?= e($hotel['address'] ?? '') ?></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Ville</label>
          <input type="text" name="city" value="<?= e($hotel['city'] ?? '') ?>"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Pays</label>
          <input type="text" name="country" value="<?= e($hotel['country'] ?? 'Sénégal') ?>"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Devise</label>
        <select name="currency" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
          <?php foreach (['XOF' => 'FCFA (XOF)', 'XAF' => 'FCFA (XAF)', 'EUR' => 'Euro (EUR)', 'USD' => 'Dollar US (USD)', 'MAD' => 'Dirham (MAD)', 'GBP' => 'Livre (GBP)'] as $code => $label): ?>
            <option value="<?= $code ?>" <?= $hotel['currency'] === $code ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Guest info -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-sm font-bold text-navy-900 mb-4">Infos client (QR)</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">WiFi — SSID</label>
        <input type="text" name="wifi_ssid" value="<?= e($hotel['wifi_ssid'] ?? '') ?>" placeholder="Hotel WiFi"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">WiFi — Mot de passe</label>
        <input type="text" name="wifi_password" value="<?= e($hotel['wifi_password'] ?? '') ?>"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Check-in</label>
          <input type="time" name="checkin_time" value="<?= e($hotel['checkin_time'] ?? '14:00') ?>"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Check-out</label>
          <input type="time" name="checkout_time" value="<?= e($hotel['checkout_time'] ?? '12:00') ?>"
                 class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-navy-700 focus:border-transparent outline-none">
        </div>
      </div>
      <p class="text-xs text-gray-400">Ces infos s'affichent sur la page QR code vue par vos clients en chambre.</p>
    </div>
  </div>

  <!-- Submit -->
  <div class="lg:col-span-3 flex justify-end">
    <button type="submit" class="px-6 py-2.5 rounded-lg bg-gold-400 text-white font-semibold hover:bg-gold-500 transition shadow-sm">
      Enregistrer
    </button>
  </div>
</form>
