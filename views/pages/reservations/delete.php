<?php /** @var array $reservation, $title */ ?>
<div class="max-w-lg mx-auto">
  <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
    <div class="text-center mb-6">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/></svg>
      </div>
      <h1 class="text-xl font-bold text-navy-900">Supprimer la réservation ?</h1>
      <p class="text-sm text-gray-500 mt-2">Cette action est irréversible.</p>
    </div>

    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm space-y-2">
      <div class="flex justify-between"><span class="text-gray-500">Client</span><span class="font-medium text-navy-900"><?= e($reservation['guest_name']) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500">Chambre</span><span class="font-medium">N° <?= e($reservation['room_number']) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500">Arrivée</span><span class="font-medium"><?= format_date($reservation['check_in']) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500">Départ</span><span class="font-medium"><?= format_date($reservation['check_out']) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500">Total</span><span class="font-bold text-gold-500"><?= format_price((float) $reservation['total']) ?></span></div>
    </div>

    <form method="POST" action="/reservations/delete/<?= (int) $reservation['id'] ?>">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <div class="flex gap-3">
        <a href="/reservations/view/<?= (int) $reservation['id'] ?>" class="flex-1 text-center py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">Annuler</a>
        <button type="submit" class="flex-1 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">Supprimer</button>
      </div>
    </form>
  </div>
</div>
