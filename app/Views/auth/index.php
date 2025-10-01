<?php
$errors = $errors ?? [];
$success = $success ?? null;
$old = $old ?? [];
$activeTab = $activeTab ?? 'login';
$user = $user ?? null;

$oldValue = static function (string $key, string $default = '') use ($old): string {
    return e($old[$key] ?? $default);
};

$hasError = static function (string $key) use ($errors): bool {
    return array_key_exists($key, $errors);
};

$errorText = static function (string $key) use ($errors): string {
    return $errors[$key] ?? '';
};

$roleValue = $old['role'] ?? 'estudiante';
$roleValue = in_array($roleValue, ['estudiante', 'director'], true) ? $roleValue : 'estudiante';
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestor de Titulacion - Acceso</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      color-scheme: light;
    }

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
      box-shadow: 0 24px 60px -36px rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(12px);
    }

    .tab-active {
      background-color: rgba(255, 255, 255, 0.95);
      box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.35);
      color: #0f172a !important;
    }

    .fade-enter {
      opacity: 0;
      transform: translateY(6px);
    }

    .fade-enter-active {
      opacity: 1;
      transform: none;
      transition: opacity 0.18s ease, transform 0.18s ease;
    }

    @media (max-width: 375px) {
      .text-4xl-mobile {
        font-size: 2.25rem;
        line-height: 2.5rem;
      }
    }

    @media (min-width: 768px) and (max-width: 1023px) and (orientation: portrait) {
      .tablet-portrait-stack {
        flex-direction: column;
      }
    }
  </style>
</head>
<body class="min-h-full">
  <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
    <div class="grid w-full max-w-6xl items-center gap-10 lg:grid-cols-[1.05fr_1fr]">
      <aside class="surface-card relative isolate overflow-hidden px-6 py-10 text-slate-900 sm:px-8 lg:px-12">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500/15 via-sky-500/10 to-transparent"></div>
        <div class="flex flex-col gap-10">
          <div class="flex items-center gap-3">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600/90 text-lg font-semibold text-white">GT</span>
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Gestor de Titulación</p>
              <p class="mt-1 text-sm font-medium text-slate-600">Tecnológico de Mérida</p>
            </div>
          </div>

          <div class="space-y-6">
            <p class="text-3xl font-semibold leading-[1.05] text-slate-900 sm:text-4xl text-4xl-mobile">
              Un acceso claro para dar seguimiento a tus proyectos.
            </p>
            <p class="max-w-md text-sm leading-relaxed text-slate-600">
              Organiza entregables, colabora con tu director y avanza paso a paso con una experiencia ligera y ordenada.
            </p>
          </div>

          <ul class="space-y-3 text-sm text-slate-600">
            <li class="flex items-center gap-3">
              <span class="inline-flex h-2.5 w-2.5 flex-none rounded-full bg-indigo-500"></span>
              Seguimiento en tiempo real de hitos y avances.
            </li>
            <li class="flex items-center gap-3">
              <span class="inline-flex h-2.5 w-2.5 flex-none rounded-full bg-indigo-500"></span>
              Comunicación directa entre estudiantes y directores.
            </li>
            <li class="flex items-center gap-3">
              <span class="inline-flex h-2.5 w-2.5 flex-none rounded-full bg-indigo-500"></span>
              Documentación centralizada para cada proyecto.
            </li>
          </ul>
        </div>
      </aside>

      <main class="surface-card px-5 py-8 sm:px-7 sm:py-9 lg:px-8">
        <div class="space-y-6">
          <?php if ($user): ?>
            <div class="rounded-2xl border border-slate-200/70 bg-white/90 px-5 py-4 text-sm text-slate-600">
              <p class="font-medium text-slate-500">Sesión activa</p>
              <div class="mt-2 text-lg font-semibold text-slate-800"><?= e($user['full_name']); ?></div>
              <div class="text-sm text-slate-500"><?= e(strtolower($user['email'])); ?> &bull; <?= e(ucfirst($user['role'])); ?></div>
              <form class="mt-4" method="post" action="<?= e(url('/logout')); ?>">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200">
                  Cerrar sesión
                </button>
              </form>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-5 py-4 text-sm text-emerald-700">
              <?= e($success); ?>
            </div>
          <?php endif; ?>

          <div>
            <div id="auth-tabs" role="tablist" aria-label="Cambiar vista" data-active-tab="<?= e($activeTab); ?>" class="grid grid-cols-2 gap-1 rounded-full bg-slate-100/80 p-1.5 text-sm font-medium text-slate-500">
              <button id="tab-login" type="button" role="tab" aria-selected="<?= $activeTab === 'login' ? 'true' : 'false'; ?>" aria-controls="panel-login" class="tab inline-flex items-center justify-center rounded-full px-4 py-2.5 text-sm font-medium transition-colors duration-200 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200 <?= $activeTab === 'login' ? 'tab-active' : ''; ?>">
                Iniciar sesión
              </button>
              <button id="tab-register" type="button" role="tab" aria-selected="<?= $activeTab === 'register' ? 'true' : 'false'; ?>" aria-controls="panel-register" class="tab inline-flex items-center justify-center rounded-full px-4 py-2.5 text-sm font-medium transition-colors duration-200 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200 <?= $activeTab === 'register' ? 'tab-active' : ''; ?>">
                Crear cuenta
              </button>
            </div>
          </div>

          <div id="panel-container" class="relative mt-6 transition-[height] duration-300 ease-out">
            <section id="panel-login" class="panel <?= $activeTab === 'register' ? 'hidden' : ''; ?>" role="tabpanel" aria-hidden="<?= $activeTab === 'register' ? 'true' : 'false'; ?>">
              <div class="rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm">
                <form class="space-y-5" method="post" action="<?= e(url('/login')); ?>">
                  <?php if ($hasError('login_general')): ?>
                    <div class="rounded-xl border border-rose-200/80 bg-rose-50/80 px-4 py-3 text-sm text-rose-600">
                      <?= e($errorText('login_general')); ?>
                    </div>
                  <?php endif; ?>

                  <div class="space-y-2">
                    <label for="login-email" class="text-sm font-semibold text-slate-700">Correo institucional</label>
                    <input id="login-email" type="email" name="email" required value="<?= $oldValue('login_email'); ?>" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('login_email') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="correo@itmerida.edu.mx" autocomplete="email" />
                    <?php if ($hasError('login_email')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('login_email')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="space-y-2">
                    <div class="flex items-center justify-between">
                      <label for="password" class="text-sm font-semibold text-slate-700">Contraseña</label>
                    </div>
                    <div class="relative">
                      <input id="password" type="password" name="password" required class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 pr-12 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('login_password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Tu contraseña" autocomplete="current-password" />
                      <button type="button" id="togglePassBtn" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600" aria-label="Mostrar contraseña">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                    <?php if ($hasError('login_password')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('login_password')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="flex justify-end">
                    <a href="<?= e(url('/password/change')); ?>" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200">
                      ¿Olvidaste tu contraseña?
                    </a>
                  </div>

                  <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200">
                    Ingresar
                  </button>
                </form>
              </div>
            </section>

            <section id="panel-register" class="panel <?= $activeTab === 'register' ? '' : 'hidden'; ?>" role="tabpanel" aria-hidden="<?= $activeTab === 'register' ? 'false' : 'true'; ?>">
              <div class="rounded-2xl border border-slate-200/70 bg-white/95 p-5 shadow-sm">
                <form class="space-y-5" method="post" action="<?= e(url('/register')); ?>">
                  <?php if ($hasError('register_general')): ?>
                    <div class="rounded-xl border border-rose-200/80 bg-rose-50/80 px-4 py-3 text-sm text-rose-600">
                      <?= e($errorText('register_general')); ?>
                    </div>
                  <?php endif; ?>

                  <div class="space-y-2">
                    <label for="full_name" class="text-sm font-semibold text-slate-700">Nombre completo</label>
                    <input id="full_name" type="text" name="full_name" required value="<?= $oldValue('full_name'); ?>" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('full_name') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Nombre y apellidos" autocomplete="name" />
                    <?php if ($hasError('full_name')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('full_name')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="space-y-2">
                    <label for="register-email" class="text-sm font-semibold text-slate-700">Correo institucional</label>
                    <input id="register-email" type="email" name="email" required value="<?= $oldValue('email'); ?>" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('email') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="correo@itmerida.edu.mx" autocomplete="email" />
                    <?php if ($hasError('email')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('email')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="space-y-2">
                    <label for="role" class="text-sm font-semibold text-slate-700">Rol en el sistema</label>
                    <select id="role" name="role" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                      <option value="estudiante" <?= $roleValue === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                      <option value="director" <?= $roleValue === 'director' ? 'selected' : ''; ?>>Director</option>
                    </select>
                  </div>

                  <div id="field-matricula" class="space-y-2 <?= $roleValue === 'director' ? 'hidden' : ''; ?>">
                    <label for="matricula" class="text-sm font-semibold text-slate-700">Matricula</label>
                    <input id="matricula" type="text" name="matricula" value="<?= $oldValue('matricula'); ?>" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('matricula') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Ej. B12345" />
                    <?php if ($hasError('matricula')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('matricula')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div id="field-depto" class="space-y-2 <?= $roleValue === 'director' ? '' : 'hidden'; ?>">
                    <label for="department" class="text-sm font-semibold text-slate-700">Departamento</label>
                    <input id="department" type="text" name="department" value="<?= $oldValue('department'); ?>" class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('department') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Ej. Ciencias Básicas" />
                    <?php if ($hasError('department')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('department')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="space-y-2">
                    <label for="register-password" class="text-sm font-semibold text-slate-700">Contraseña</label>
                    <div class="relative">
                      <input id="register-password" type="password" name="password" required class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 pr-12 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Mínimo 8 caracteres" autocomplete="new-password" />
                      <button type="button" class="toggle-eye absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600" data-toggle-for="register-password" aria-label="Mostrar contraseña">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                    <?php if ($hasError('password')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('password')); ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                    <div class="relative">
                      <input id="password_confirmation" type="password" name="password_confirmation" required class="block w-full rounded-xl border border-slate-200/80 bg-white px-4 py-3 pr-12 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 <?= $hasError('password_confirmation') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-300' : ''; ?>" placeholder="Repite la contraseña" autocomplete="new-password" />
                      <button type="button" class="toggle-eye absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600" data-toggle-for="password_confirmation" aria-label="Mostrar contraseña">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                    <?php if ($hasError('password_confirmation')): ?>
                      <p class="text-xs font-medium text-rose-600"><?= e($errorText('password_confirmation')); ?></p>
                    <?php endif; ?>
                  </div>

                  <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200">
                    Crear cuenta
                  </button>
                </form>
              </div>
            </section>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    const authTabs = document.getElementById('auth-tabs');
    const panelContainer = document.getElementById('panel-container');

    function setContainerHeightFor(panel) {
      if (!panel || !panelContainer) return;
      const clone = panel.cloneNode(true);
      clone.style.height = 'auto';
      clone.classList.remove('hidden');
      clone.style.position = 'absolute';
      clone.style.visibility = 'hidden';
      panelContainer.appendChild(clone);
      const height = clone.getBoundingClientRect().height;
      panelContainer.style.height = height + 'px';
      panelContainer.removeChild(clone);
    }

    if (authTabs && panelContainer) {
      const tabButtons = Array.from(authTabs.querySelectorAll('[role="tab"]'));
      const panels = Array.from(panelContainer.querySelectorAll('[role="tabpanel"]'));

      function activateTab(button) {
        const targetId = button.getAttribute('aria-controls');
        const nextPanel = document.getElementById(targetId);
        if (!nextPanel) return;

        tabButtons.forEach((btn) => {
          btn.classList.remove('tab-active');
          btn.setAttribute('aria-selected', 'false');
        });

        panels.forEach((panel) => {
          panel.classList.add('hidden');
          panel.setAttribute('aria-hidden', 'true');
        });

        button.classList.add('tab-active');
        button.setAttribute('aria-selected', 'true');

        nextPanel.classList.remove('hidden');
        nextPanel.setAttribute('aria-hidden', 'false');
        nextPanel.classList.add('fade-enter');
        requestAnimationFrame(() => nextPanel.classList.add('fade-enter-active'));
        setTimeout(() => nextPanel.classList.remove('fade-enter', 'fade-enter-active'), 200);
        setContainerHeightFor(nextPanel);
      }

      window.addEventListener('load', () => {
        const requestedTab = authTabs.dataset.activeTab || 'login';
        const defaultButton = requestedTab === 'register' ? document.getElementById('tab-register') : document.getElementById('tab-login');
        if (defaultButton) {
          activateTab(defaultButton);
        }
      });

      tabButtons.forEach((btn) => btn.addEventListener('click', () => activateTab(btn)));

      authTabs.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
        event.preventDefault();
        const index = tabButtons.indexOf(document.activeElement);
        if (index === -1) return;
        const nextIndex = event.key === 'ArrowRight' ? (index + 1) % tabButtons.length : (index - 1 + tabButtons.length) % tabButtons.length;
        tabButtons[nextIndex].focus();
        activateTab(tabButtons[nextIndex]);
      });
    }

    function eyeSvg(open = true) {
      return open
        ? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
        : '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.8 21.8 0 0 1 5.06-6.06"></path><path d="M1 1l22 22"></path><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.73 21.73 0 0 1-3.17 4.26"></path><path d="M12 12a3 3 0 0 0-3-3"></path></svg>';
    }

    const togglePassBtn = document.getElementById('togglePassBtn');
    const passInput = document.getElementById('password');
    if (togglePassBtn && passInput) {
      let open = false;
      togglePassBtn.addEventListener('click', () => {
        open = !open;
        passInput.type = open ? 'text' : 'password';
        togglePassBtn.innerHTML = eyeSvg(open);
        togglePassBtn.setAttribute('aria-label', open ? 'Ocultar contraseña' : 'Mostrar contraseña');
      });
    }

    document.querySelectorAll('.toggle-eye').forEach((btn) => {
      const input = document.getElementById(btn.dataset.toggleFor);
      if (!input) return;
      let open = false;
      btn.addEventListener('click', () => {
        open = !open;
        input.type = open ? 'text' : 'password';
        btn.innerHTML = eyeSvg(open);
        btn.setAttribute('aria-label', open ? 'Ocultar contraseña' : 'Mostrar contraseña');
      });
    });

    const roleSelect = document.getElementById('role');
    const fieldMat = document.getElementById('field-matricula');
    const fieldDept = document.getElementById('field-depto');

    function syncRoleFields() {
      if (!roleSelect) return;
      const isDirector = roleSelect.value === 'director';
      if (fieldMat && fieldDept) {
        if (isDirector) {
          fieldMat.classList.add('hidden');
          fieldDept.classList.remove('hidden');
        } else {
          fieldDept.classList.add('hidden');
          fieldMat.classList.remove('hidden');
        }
        setTimeout(() => {
          const activePanel = document.querySelector('.panel:not(.hidden)');
          setContainerHeightFor(activePanel);
        }, 150);
      }
    }

    if (roleSelect) {
      roleSelect.addEventListener('change', syncRoleFields);
      syncRoleFields();
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const active = document.querySelector('.panel:not(.hidden)');
        if (active) setContainerHeightFor(active);
      }, 150);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Tab') {
        document.body.classList.add('using-keyboard');
      }
    });

    document.addEventListener('mousedown', () => {
      document.body.classList.remove('using-keyboard');
    });
  </script>

  <style>
    .using-keyboard *:focus {
      outline: 2px solid #1E3A8A !important;
      outline-offset: 2px !important;
    }

    input:focus,
    select:focus,
    button:focus:not(.tab) {
      box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.15);
    }
  </style>
</body>
</html>
