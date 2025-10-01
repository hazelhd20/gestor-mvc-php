<?php
$status = $status ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
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

    body {
      background-color: #f8fafc;
    }

    .surface-card {
      border-radius: 24px;
      border: 1px solid rgba(15, 23, 42, 0.08);
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 24px 60px -36px rgba(15, 23, 42, 0.4);
      backdrop-filter: blur(12px);
    }
  </style>
</head>
<body class="min-h-screen">
  <div class="flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg space-y-8">
      <div class="text-center">
        <h1 class="text-3xl font-semibold text-slate-900">Recuperar contrase&ntilde;a</h1>
        <p class="mt-2 text-sm text-slate-600">Ingresa tu correo institucional y establece una nueva contrase&ntilde;a para tu cuenta.</p>
      </div>

      <?php if ($status): ?>
        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-5 py-4 text-sm text-emerald-700">
          <?= e($status); ?>
        </div>
      <?php endif; ?>

      <div class="surface-card px-6 py-8 sm:px-8">
        <form method="post" action="<?= e(url('/password/change')); ?>" class="space-y-5">
          <div class="space-y-2">
            <label for="email" class="text-sm font-semibold text-slate-700">Correo institucional</label>
            <input
              id="email"
              type="email"
              name="email"
              value="<?= e($old['email'] ?? ''); ?>"
              required
              class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              placeholder="correo@itmerida.edu.mx"
              autocomplete="email"
            />
            <?php if (!empty($errors['email'])): ?>
              <p class="text-xs font-medium text-rose-600"><?= e($errors['email']); ?></p>
            <?php endif; ?>
          </div>

          <div class="space-y-2">
            <label for="password" class="text-sm font-semibold text-slate-700">Nueva contrase&ntilde;a</label>
            <input
              id="password"
              type="password"
              name="password"
              required
              minlength="8"
              class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              placeholder="M&iacute;nimo 8 caracteres"
              autocomplete="new-password"
            />
            <?php if (!empty($errors['password'])): ?>
              <p class="text-xs font-medium text-rose-600"><?= e($errors['password']); ?></p>
            <?php endif; ?>
          </div>

          <div class="space-y-2">
            <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirma tu nueva contrase&ntilde;a</label>
            <input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              required
              class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              placeholder="Repite la contrase&ntilde;a"
              autocomplete="new-password"
            />
            <?php if (!empty($errors['password_confirmation'])): ?>
              <p class="text-xs font-medium text-rose-600"><?= e($errors['password_confirmation']); ?></p>
            <?php endif; ?>
          </div>

          <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200">
            Actualizar contrase&ntilde;a
          </button>
        </form>
      </div>

      <div class="text-center">
        <a href="<?= e(url('/')); ?>" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">Volver a iniciar sesi&oacute;n</a>
      </div>
    </div>
  </div>
</body>
</html>