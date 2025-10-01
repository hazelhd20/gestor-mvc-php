<?php
$status = $status ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$tokenStatus = $tokenStatus ?? null;
$tokenErrors = $tokenErrors ?? [];
$tokenOld = $tokenOld ?? [];
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recuperar contrase&ntilde;a</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    html {
      font-family: Inter, system-ui, ui-sans-serif;
      background-color: #f8fafc;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-50">
  <div class="flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-slate-800">Recuperar contrase&ntilde;a</h1>
        <p class="mt-2 text-sm text-slate-600">Sigue los pasos para solicitar un token de recuperaci&oacute;n y establecer tu nueva contrase&ntilde;a.</p>
      </div>

      <?php if ($status): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <?= e($status); ?>
        </div>
      <?php endif; ?>

      <div class="space-y-6">
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
          <h2 class="text-base font-semibold text-slate-800">1. Solicita tu token</h2>
          <p class="mt-1 text-sm text-slate-500">Ingresa el correo con el que te registraste para recibir un token de recuperaci&oacute;n.</p>

          <?php if ($tokenStatus): ?>
            <div class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
              <?= e($tokenStatus); ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= e(url('/password/token')); ?>" class="mt-4 space-y-4">
            <div>
              <label for="token_email" class="mb-2 block text-sm font-semibold text-slate-700">Correo institucional</label>
              <input
                id="token_email"
                type="email"
                name="email"
                value="<?= e($tokenOld['email'] ?? ''); ?>"
                required
                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200"
                placeholder="correo@itmerida.edu.mx"
                autocomplete="email"
              />
              <?php if (!empty($tokenErrors['email'])): ?>
                <p class="mt-2 text-xs font-medium text-red-600"><?= e($tokenErrors['email']); ?></p>
              <?php endif; ?>
            </div>

            <button type="submit" class="w-full rounded-xl bg-[#1869db] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#155cc1] focus:outline-none focus:ring-2 focus:ring-indigo-200">
              Enviar token
            </button>
          </form>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
          <h2 class="text-base font-semibold text-slate-800">2. Restablece tu contrase&ntilde;a</h2>
          <p class="mt-1 text-sm text-slate-500">Copia el token recibido y escribe tu nueva contrase&ntilde;a.</p>

          <form method="post" action="<?= e(url('/password/change')); ?>" class="mt-4 space-y-4">
            <div>
              <label for="token" class="mb-2 block text-sm font-semibold text-slate-700">Token de recuperaci&oacute;n</label>
              <input
                id="token"
                type="text"
                name="token"
                value="<?= e($old['token'] ?? ''); ?>"
                required
                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200"
                placeholder="Ingresa el token recibido"
                autocomplete="one-time-code"
              />
              <?php if (!empty($errors['token'])): ?>
                <p class="mt-2 text-xs font-medium text-red-600"><?= e($errors['token']); ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contrase&ntilde;a</label>
              <input
                id="password"
                type="password"
                name="password"
                required
                minlength="8"
                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200"
                placeholder="M&iacute;nimo 8 caracteres"
                autocomplete="new-password"
              />
              <?php if (!empty($errors['password'])): ?>
                <p class="mt-2 text-xs font-medium text-red-600"><?= e($errors['password']); ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirma tu nueva contrase&ntilde;a</label>
              <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200"
                placeholder="Repite la contrase&ntilde;a"
                autocomplete="new-password"
              />
              <?php if (!empty($errors['password_confirmation'])): ?>
                <p class="mt-2 text-xs font-medium text-red-600"><?= e($errors['password_confirmation']); ?></p>
              <?php endif; ?>
            </div>

            <button type="submit" class="w-full rounded-xl bg-[#1869db] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#155cc1] focus:outline-none focus:ring-2 focus:ring-indigo-200">
              Actualizar contrase&ntilde;a
            </button>
          </form>
        </section>
      </div>

      <div class="mt-6 text-center">
        <a href="<?= e(url('/')); ?>" class="text-sm font-semibold text-[#1869db] hover:text-[#155cc1]">Volver a iniciar sesi&oacute;n</a>
      </div>
    </div>
  </div>
</body>
</html>
